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
				'id' => '1',
			),
			$atts,
			'pcc_fb_form'
		);
		$form_id = Helpers::sanitize_text( (string) $atts['id'] );

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

		$preset_path   = dirname( __DIR__, 2 ) . '/templates/forms/presets/foodbank-intake.json';
		$consent_label = __( 'I consent to the processing of my data as described.', 'foodbank-manager' );
		if ( file_exists( $preset_path ) ) {
			$json = file_get_contents( $preset_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local preset file.
			$cfg  = json_decode( (string) $json, true );
			if ( is_array( $cfg ) && isset( $cfg['consent_text'] ) ) {
				$label = Helpers::sanitize_text( (string) $cfg['consent_text'] );
				if ( $label !== '' ) {
					$consent_label = $label;
				}
			}
		}

		ob_start();
		if ( $has_error ) {
			echo '<div class="fbm-error">' . esc_html__( 'There was a problem. Please check the highlighted fields and try again.', 'foodbank-manager' ) . '</div>';
		}
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" enctype="multipart/form-data">';
		echo '<input type="hidden" name="action" value="fbm_submit" />';
		echo '<input type="hidden" name="_fbm_nonce" value="' . esc_attr( wp_create_nonce( 'fbm_submit_form' ) ) . '" />';
		echo '<input type="hidden" name="form_id" value="' . esc_attr( $form_id ) . '" />';

		self::text_field( 'first_name', __( 'First Name', 'foodbank-manager' ), true, $errors );
		self::text_field( 'last_name', __( 'Last Name', 'foodbank-manager' ), true, $errors );
		self::email_field( 'email', __( 'Email', 'foodbank-manager' ), true, $errors );
		self::text_field( 'phone', __( 'Phone', 'foodbank-manager' ), false, $errors );
		self::text_field( 'postcode', __( 'Postcode', 'foodbank-manager' ), true, $errors );
		self::textarea_field( 'notes', __( 'Notes', 'foodbank-manager' ), false, $errors );
		self::file_field( 'upload', __( 'Supporting document (optional)', 'foodbank-manager' ), $errors );
		self::consent_field( $consent_label, $errors );

		// TODO(PRD §5.1 Anti-spam): integrate CAPTCHA.
		echo '<p><button type="submit">' . esc_html__( 'Submit', 'foodbank-manager' ) . '</button></p>';
		echo '</form>';
		$content = (string) ob_get_clean();
		$density = Options::get( 'theme.frontend.density', 'comfortable' );
		$dark    = Options::get( 'theme.frontend.dark_mode', 'auto' );
		$dark_cl = $dark === 'on' ? ' fbm-dark' : ( $dark === 'off' ? ' fbm-light' : '' );
		return '<div class="fbm-scope fbm-density-' . esc_attr( $density ) . $dark_cl . '">' . $content . '</div>';
	}

	/**
	 * Render a text input field.
	 *
	 * @param string   $name     Field name.
	 * @param string   $label    Label text.
	 * @param bool     $required Whether field is required.
	 * @param string[] $errors   Error codes.
	 */
	private static function text_field( string $name, string $label, bool $required, array $errors ): void {
		$id    = 'fbm_' . $name;
		$error = in_array( $name, $errors, true );
		echo '<p><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label> ';
		echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . ( $required ? ' required' : '' ) . ( $error ? ' aria-invalid="true"' : '' ) . ' />';
		if ( $error ) {
			echo ' <span class="fbm-error" role="alert">' . esc_html__( 'Required', 'foodbank-manager' ) . '</span>';
		}
		echo '</p>';
	}

	/**
	 * Render an email input field.
	 *
	 * @param string   $name     Field name.
	 * @param string   $label    Label text.
	 * @param bool     $required Whether field is required.
	 * @param string[] $errors   Error codes.
	 */
	private static function email_field( string $name, string $label, bool $required, array $errors ): void {
		$id    = 'fbm_' . $name;
		$error = in_array( $name, $errors, true );
		echo '<p><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label> ';
		echo '<input type="email" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . ( $required ? ' required' : '' ) . ( $error ? ' aria-invalid="true"' : '' ) . ' />';
		if ( $error ) {
			echo ' <span class="fbm-error" role="alert">' . esc_html__( 'Valid email required', 'foodbank-manager' ) . '</span>';
		}
		echo '</p>';
	}

	/**
	 * Render a textarea field.
	 *
	 * @param string   $name     Field name.
	 * @param string   $label    Label text.
	 * @param bool     $required Whether field is required.
	 * @param string[] $errors   Error codes.
	 */
	private static function textarea_field( string $name, string $label, bool $required, array $errors ): void {
		$id    = 'fbm_' . $name;
		$error = in_array( $name, $errors, true );
		echo '<p><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
		echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="4"' . ( $required ? ' required' : '' ) . ( $error ? ' aria-invalid="true"' : '' ) . '></textarea>';
		if ( $error ) {
			echo ' <span class="fbm-error" role="alert">' . esc_html__( 'Required', 'foodbank-manager' ) . '</span>';
		}
		echo '</p>';
	}

	/**
	 * Render a file upload field.
	 *
	 * @param string   $name   Field name.
	 * @param string   $label  Label text.
	 * @param string[] $errors Error codes.
	 */
	private static function file_field( string $name, string $label, array $errors ): void {
		$id    = 'fbm_' . $name;
		$error = in_array( $name, $errors, true );
		echo '<p><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label> ';
		echo '<input type="file" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . ( $error ? ' aria-invalid="true"' : '' ) . ' />';
		if ( $error ) {
			echo ' <span class="fbm-error" role="alert">' . esc_html__( 'File not allowed', 'foodbank-manager' ) . '</span>';
		}
		echo '</p>';
	}

	/**
	 * Render a consent checkbox field.
	 *
	 * @param string   $label  Consent label.
	 * @param string[] $errors Error codes.
	 */
	private static function consent_field( string $label, array $errors ): void {
		$id    = 'fbm_consent';
		$error = in_array( 'consent', $errors, true );
		echo '<p><label><input type="checkbox" id="' . esc_attr( $id ) . '" name="consent" value="1" required' . ( $error ? ' aria-invalid="true"' : '' ) . ' /> ' . esc_html( $label ) . '</label>';
		if ( $error ) {
			echo ' <span class="fbm-error" role="alert">' . esc_html__( 'Required', 'foodbank-manager' ) . '</span>';
		}
		echo '</p>';
	}
}
