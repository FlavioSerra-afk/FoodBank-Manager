<?php

declare(strict_types=1);

namespace FoodBankManager\Http;

use FoodBankManager\Security\Helpers;
use FoodBankManager\Security\Crypto;
use FoodBankManager\Core\Options;
use FoodBankManager\Mail\Templates;

class FormSubmitController {
    /**
     * Handle public form submissions.
     */
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

            $provider = Options::get( 'forms.captcha_provider', 'off' );
            if ( $provider !== 'off' ) {
                $response_key = $provider === 'turnstile' ? 'cf-turnstile-response' : 'g-recaptcha-response';
                $response = sanitize_text_field( (string) ( $_POST[ $response_key ] ?? '' ) );
                $secret   = Options::get( 'forms.captcha_secret' );
                if ( $secret && $response ) {
                    $url       = $provider === 'turnstile' ? 'https://challenges.cloudflare.com/turnstile/v0/siteverify' : 'https://www.google.com/recaptcha/api/siteverify';
                    $remote_ip = sanitize_text_field( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
                    $verify    = wp_remote_post( $url, array( 'body' => array( 'secret' => $secret, 'response' => $response, 'remoteip' => $remote_ip ) ) );
                    $body = wp_remote_retrieve_body( $verify );
                    $ok   = false;
                    if ( $body ) {
                        $json = json_decode( $body, true );
                        $ok   = is_array( $json ) && ! empty( $json['success'] );
                    }
                    if ( ! $ok ) {
                        $errors[] = 'captcha';
                    }
                }
            }

            if ( $errors ) {
                $redirect = add_query_arg( array( 'fbm_err' => implode( ',', $errors ) ), $referer );
                wp_safe_redirect( $redirect );
                exit;
            }

            $file_meta = null;
            if ( isset( $_FILES['upload'] ) && is_array( $_FILES['upload'] ) ) {
                $file = $_FILES['upload'];
                $max_mb = (int) Options::get( 'files.max_size_mb', 5 );
                $allowed_exts = Options::get( 'files.allowed_mimes', array() );
                $allowed_mimes = array();
                if ( is_array( $allowed_exts ) ) {
                    $all_mimes = get_allowed_mime_types();
                    foreach ( $allowed_exts as $ext ) {
                        if ( isset( $all_mimes[ $ext ] ) ) {
                            $allowed_mimes[ $ext ] = $all_mimes[ $ext ];
                        }
                    }
                }
                if ( UPLOAD_ERR_OK === (int) $file['error'] && (int) $file['size'] <= $max_mb * 1024 * 1024 ) {
                    $type = wp_check_filetype( $file['name'], $allowed_mimes );
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
            $ip           = sanitize_text_field( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
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
                'reference'      => 'FBM-' . $app_id,
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

            $admin_tokens  = $tokens;
            $admin_tokens['application_link'] = admin_url( 'admin.php?page=fbm_application&id=' . $app_id );

            $headers = array( 'Content-Type: text/html; charset=UTF-8' );
            $from_email = Options::get( 'emails.from_email' );
            if ( $from_email ) {
                $from_name = Options::get( 'emails.from_name' );
                $headers[] = 'From: ' . ( $from_name ? $from_name : $from_email ) . ' <' . $from_email . '>';
            }
            $reply = Options::get( 'emails.reply_to' );
            if ( $reply ) {
                $headers[] = 'Reply-To: ' . $reply;
            }

            $rendered = Templates::render( 'applicant_confirmation', $tokens );
            wp_mail( $email, $rendered['subject'], $rendered['body_html'], $headers );

            $admin_rendered = Templates::render( 'admin_notification', $admin_tokens );
            $recipients = Options::get( 'emails.admin_recipients' );
            $to_admin = $recipients !== '' ? array_map( 'trim', explode( ',', $recipients ) ) : (string) get_option( 'admin_email' );
            wp_mail( $to_admin, $admin_rendered['subject'], $admin_rendered['body_html'], $headers );

            $redirect = $referer;
            $success_page = (int) Options::get( 'forms.success_redirect_page_id' );
            if ( $success_page ) {
                $page_url = get_permalink( $success_page );
                if ( $page_url ) {
                    $redirect = $page_url;
                }
            }
            $redirect = add_query_arg( array( 'fbm_success' => 1, 'fbm_ref' => $app_id ), $redirect );
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

    /**
     * Fetch consent text from options.
     */
    private static function consent_text(): string {
        $text = (string) Options::get( 'forms.consent_text', __( 'I consent to the processing of my data as described.', 'foodbank-manager' ) );
        $text = Helpers::sanitize_text( $text );
        return $text !== '' ? $text : __( 'I consent to the processing of my data as described.', 'foodbank-manager' );
    }
}
