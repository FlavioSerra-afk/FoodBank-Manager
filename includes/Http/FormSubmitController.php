<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Http;

use FoodBankManager\Security\Helpers;
use FoodBankManager\Security\Crypto;

class FormSubmitController {
    public static function handle(): void {
        try {
            if ( ! Helpers::verify_nonce( 'fbm_submit_form', '_fbm_nonce' ) ) {
                wp_die( esc_html__( 'Invalid request', 'foodbank-manager' ), esc_html__( 'Error', 'foodbank-manager' ), array( 'response' => 403 ) );
            }

            $referer = wp_get_referer() ?: home_url( '/' );

            $first    = Helpers::sanitize_text( (string) ( $_POST['first_name'] ?? '' ) );
            $last     = Helpers::sanitize_text( (string) ( $_POST['last_name'] ?? '' ) );
            $email    = sanitize_email( (string) ( $_POST['email'] ?? '' ) );
            $phone    = Helpers::sanitize_text( (string) ( $_POST['phone'] ?? '' ) );
            $postcode = Helpers::sanitize_text( (string) ( $_POST['postcode'] ?? '' ) );
            $notes    = isset( $_POST['notes'] ) ? sanitize_textarea_field( (string) $_POST['notes'] ) : '';
            $consent  = Helpers::sanitize_text( (string) ( $_POST['consent'] ?? '' ) );
            $form_id  = (int) ( $_POST['form_id'] ?? 1 );

            $errors = array();
            if ( '' === $first ) {
                $errors[] = 'first_name';
            }
            if ( '' === $last ) {
                $errors[] = 'last_name';
            }
            if ( '' === $email || ! is_email( $email ) ) {
                $errors[] = 'email';
            }
            if ( '' === $postcode ) {
                $errors[] = 'postcode';
            }
            if ( '1' !== $consent ) {
                $errors[] = 'consent';
            }

            if ( $errors ) {
                $redirect = add_query_arg( array( 'fbm_err' => implode( ',', $errors ) ), $referer );
                wp_safe_redirect( $redirect );
                exit;
            }

            $file_meta = null;
            if ( isset( $_FILES['upload'] ) && is_array( $_FILES['upload'] ) ) {
                $file = $_FILES['upload'];
                // Hardcoded defaults: 5 MB limit and basic MIME whitelist until settings are implemented.
                if ( UPLOAD_ERR_OK === (int) $file['error'] && (int) $file['size'] <= 5 * 1024 * 1024 ) { // 5MB limit
                    $allowed = array( 'pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png' );
                    $type    = wp_check_filetype( $file['name'], $allowed );
                    if ( ! empty( $type['ext'] ) && ! empty( $type['type'] ) ) {
                        $override = array(
                            'test_form' => false,
                            'unique_filename_callback' => function ( $dir, $name, $ext ) {
                                return wp_unique_filename( $dir, wp_generate_password( 12, false ) . $ext );
                            },
                        );
                        $uploaded = wp_handle_upload( $file, $override );
                        if ( isset( $uploaded['file'] ) ) {
                            $file_meta = array(
                                'stored_path'   => $uploaded['file'],
                                'original_name' => sanitize_file_name( $file['name'] ),
                                'mime'          => $uploaded['type'] ?? '',
                                'size_bytes'    => (int) $file['size'],
                                'sha256'        => hash_file( 'sha256', $uploaded['file'] ),
                            );
                        }
                    }
                }
            }

            $data = array(
                'first_name' => $first,
                'last_name'  => $last,
                'postcode'   => $postcode,
                'notes'      => $notes,
            );

            $pii_blob = Crypto::encryptSensitive(
                array(
                    'email' => $email,
                    'phone' => $phone,
                )
            );

            $consent_text = self::consent_text();
            $consent_hash = hash( 'sha256', $consent_text );
            $ip           = $_SERVER['REMOTE_ADDR'] ?? '';
            $ip_bin       = $ip !== '' ? @inet_pton( $ip ) : null; // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
            $now          = current_time( 'mysql', true );

            global $wpdb;
            $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO {$wpdb->prefix}fb_applications (form_id, status, data_json, pii_encrypted_blob, consent_text_hash, consent_timestamp, consent_ip, created_at, updated_at) VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s)",
                    $form_id,
                    'new',
                    wp_json_encode( $data ),
                    $pii_blob,
                    $consent_hash,
                    $now,
                    $ip_bin,
                    $now,
                    $now
                )
            );
            $app_id = (int) $wpdb->insert_id;

            if ( $file_meta ) {
                $wpdb->query(
                    $wpdb->prepare(
                        "INSERT INTO {$wpdb->prefix}fb_files (application_id, stored_path, original_name, mime, size_bytes, sha256, created_at) VALUES (%d, %s, %s, %s, %d, %s, %s)",
                        $app_id,
                        $file_meta['stored_path'],
                        $file_meta['original_name'],
                        $file_meta['mime'],
                        $file_meta['size_bytes'],
                        $file_meta['sha256'],
                        $now
                    )
                );
            }

            $summary_table = '<table><tbody>';
            foreach ( array( 'first_name' => $first, 'last_name' => $last, 'postcode' => $postcode, 'notes' => $notes ) as $label => $val ) {
                $summary_table .= '<tr><th>' . esc_html( ucfirst( str_replace( '_', ' ', $label ) ) ) . '</th><td>' . esc_html( $val ) . '</td></tr>';
            }
            $summary_table .= '</tbody></table>';

            $tokens = array(
                'application_id' => $app_id,
                'first_name'     => $first,
                'last_name'      => $last,
                'created_at'     => $now,
                'summary_table'  => $summary_table,
            );

            if ( class_exists( '\\FoodBankManager\\Attendance\\TokenService' ) ) {
                $token = \FoodBankManager\Attendance\TokenService::generate( $app_id );
                if ( class_exists( '\\Endroid\\QrCode\\QrCode' ) && class_exists( '\\Endroid\\QrCode\\Writer\\PngWriter' ) ) {
                    $qr     = new \Endroid\QrCode\QrCode( $token );
                    $writer = new \Endroid\QrCode\Writer\PngWriter();
                    $tokens['qr_code_url'] = 'data:image/png;base64,' . base64_encode( $writer->write( $qr )->getString() );
                } else {
                    $tokens['qr_code_url'] = $token;
                }
            }

            $subject = sprintf( __( 'We received your application — Ref %d', 'foodbank-manager' ), $app_id );
            ob_start();
            extract( $tokens, EXTR_SKIP );
            include plugin_dir_path( \FBM_FILE ) . 'templates/emails/applicant-confirmation.php';
            $message = ob_get_clean();
            wp_mail( $email, $subject, $message, array( 'Content-Type: text/html; charset=UTF-8' ) );

            $admin_subject = sprintf( __( 'New application received (Ref %d)', 'foodbank-manager' ), $app_id );
            $admin_tokens  = $tokens;
            $admin_tokens['entry_url'] = admin_url( 'admin.php?page=fbm_application&id=' . $app_id );
            ob_start();
            extract( $admin_tokens, EXTR_SKIP );
            include plugin_dir_path( \FBM_FILE ) . 'templates/emails/admin-notification.php';
            $admin_message = ob_get_clean();
            wp_mail( (string) get_option( 'admin_email' ), $admin_subject, $admin_message, array( 'Content-Type: text/html; charset=UTF-8' ) );

            $redirect = add_query_arg( array( 'fbm_success' => 1, 'fbm_ref' => $app_id ), $referer );
            wp_safe_redirect( $redirect );
            exit;
        } catch ( \Throwable $e ) {
            $expiry = defined( 'MINUTE_IN_SECONDS' ) ? (int) MINUTE_IN_SECONDS : 60;
            set_transient( 'fbm_form_error', 1, $expiry );
            $ref = wp_get_referer() ?: home_url( '/' );
            wp_safe_redirect( add_query_arg( 'fbm_error', 1, $ref ) );
            exit;
        }
    }

    private static function consent_text(): string {
        $preset_path = plugin_dir_path( \FBM_FILE ) . 'templates/forms/presets/foodbank-intake.json';
        $default     = __( 'I consent to the processing of my data as described.', 'foodbank-manager' );
        if ( file_exists( $preset_path ) ) {
            $json = file_get_contents( $preset_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
            $cfg  = json_decode( (string) $json, true );
            if ( is_array( $cfg ) && isset( $cfg['consent_text'] ) ) {
                $text = Helpers::sanitize_text( (string) $cfg['consent_text'] );
                if ( '' !== $text ) {
                    return $text;
                }
            }
        }
        return $default;
    }
}
