<?php
// phpcs:ignoreFile
/**
 * Public application form shortcode.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Shortcodes;

use FoodBankManager\Security\Helpers;
use FoodBankManager\UI\Theme;
use FoodBankManager\Core\Options;
use FoodBankManager\Forms\Presets;

/**
 * Form shortcode.
 */
class Form {
        /**
         * Render the application form.
         *
         * @param array<string,string> $atts Shortcode attributes.
         *
         * @return string
         */
        public static function render( array $atts = array() ): string {
                Theme::enqueue_front();

                $atts    = shortcode_atts(
                        array(
                                'id'     => '1',
                                'preset' => 'basic_intake',
                        ),
                        $atts,
                        'fbm_form'
                );
                $form_id = Helpers::sanitize_text( (string) $atts['id'] );
                $preset  = sanitize_key( (string) $atts['preset'] );
                if ( '' === $preset ) {
                        $preset = 'basic_intake';
                }

                $invalid = ! Presets::exists( $preset );
                $fields  = Presets::resolve( $preset );
                if ( $invalid ) {
                        $fields = Presets::resolve( 'fallback' );
                }

                // Success screen.
                $ref = '';
                if ( isset( $_GET['fbm_ref'], $_GET['fbm_success'] ) ) {
                        $ref = sanitize_text_field( wp_unslash( (string) $_GET['fbm_ref'] ) );
                }
                if ( $ref !== '' ) {
                        /* translators: %s: application reference */
                        return '<div class="fbm-success">' . esc_html( sprintf( __( 'Thank you — your reference is FBM-%s. We\'ve emailed a confirmation.', 'foodbank-manager' ), $ref ) ) . '</div>';
                }

                $errors = array();
                if ( isset( $_GET['fbm_err'] ) ) {
                        $raw_param = (string) wp_unslash( $_GET['fbm_err'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized via sanitize_key.
                        $raw       = explode( ',', $raw_param );
                        $errors    = array_map( 'sanitize_key', $raw );
                }
                $has_error = false;
                if ( get_transient( 'fbm_form_error' ) ) {
                        $has_error = true;
                        delete_transient( 'fbm_form_error' );
                }
                if ( isset( $_GET['fbm_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only error flag.
                        $has_error = true;
                }

                ob_start();
                if ( $has_error ) {
                        echo '<div class="fbm-error">' . esc_html__( 'There was a problem. Please check the highlighted fields and try again.', 'foodbank-manager' ) . '</div>';
                }
                if ( $invalid && current_user_can( 'fb_manage_forms' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom cap.
                        echo '<div class="fbm-error">' . esc_html__( 'Invalid form preset. Using fallback.', 'foodbank-manager' ) . '</div>';
                }
                echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
                echo '<input type="hidden" name="action" value="fbm_submit" />';
                echo '<input type="hidden" name="_fbm_nonce" value="' . esc_attr( wp_create_nonce( 'fbm_submit_form' ) ) . '" />';
                echo '<input type="hidden" name="form_id" value="' . esc_attr( $form_id ) . '" />';

                foreach ( $fields as $field ) {
                        self::render_field( $field, $errors );
                }

                echo '<p><button type="submit">' . esc_html__( 'Submit', 'foodbank-manager' ) . '</button></p>';
                echo '</form>';
                $content = (string) ob_get_clean();
                $density = Options::get( 'theme.frontend.density', 'comfortable' );
                $dark    = Options::get( 'theme.frontend.dark_mode', 'auto' );
                $dark_cl = $dark === 'on' ? ' fbm-dark' : ( $dark === 'off' ? ' fbm-light' : '' );
                return '<div class="fbm-scope fbm-density-' . esc_attr( $density ) . $dark_cl . '">' . $content . '</div>';
        }

        /**
         * Render a field based on its config.
         *
         * @param array<string,mixed> $field  Field config.
         * @param string[]            $errors Error codes.
         */
        private static function render_field( array $field, array $errors ): void {
                $name     = (string) $field['name'];
                $label    = (string) $field['label'];
                $required = ! empty( $field['required'] );
                $type     = (string) $field['type'];
                $id       = 'fbm_' . $name;
                $error    = in_array( $name, $errors, true );
                if ( in_array( $type, array( 'text', 'email', 'tel' ), true ) ) {
                        echo '<p><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label> ';
                        echo '<input type="' . esc_attr( $type ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . ( $required ? ' required' : '' ) . ( $error ? ' aria-invalid="true"' : '' ) . ' />';
                        if ( $error ) {
                                echo ' <span class="fbm-error" role="alert">' . esc_html__( 'Required', 'foodbank-manager' ) . '</span>';
                        }
                        echo '</p>';
                        return;
                }
                if ( 'textarea' === $type ) {
                        echo '<p><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
                        echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="4"' . ( $required ? ' required' : '' ) . ( $error ? ' aria-invalid="true"' : '' ) . '></textarea>';
                        if ( $error ) {
                                echo ' <span class="fbm-error" role="alert">' . esc_html__( 'Required', 'foodbank-manager' ) . '</span>';
                        }
                        echo '</p>';
                        return;
                }
                if ( 'select' === $type ) {
                        echo '<p><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label> ';
                        echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . ( $required ? ' required' : '' ) . ( $error ? ' aria-invalid="true"' : '' ) . '>';
                        $opts = $field['options'] ?? array();
                        if ( is_array( $opts ) ) {
                                foreach ( $opts as $opt ) {
                                        echo '<option value="' . esc_attr( (string) $opt ) . '">' . esc_html( (string) $opt ) . '</option>';
                                }
                        }
                        echo '</select>';
                        if ( $error ) {
                                echo ' <span class="fbm-error" role="alert">' . esc_html__( 'Required', 'foodbank-manager' ) . '</span>';
                        }
                        echo '</p>';
                        return;
                }
                if ( 'checkbox' === $type ) {
                        echo '<p><label><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="1"' . ( $required ? ' required' : '' ) . ( $error ? ' aria-invalid="true"' : '' ) . ' /> ' . esc_html( $label ) . '</label>';
                        if ( $error ) {
                                echo ' <span class="fbm-error" role="alert">' . esc_html__( 'Required', 'foodbank-manager' ) . '</span>';
                        }
                        echo '</p>';
                }
        }
}
