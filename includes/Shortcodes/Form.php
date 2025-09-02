<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Shortcodes;

use FoodBankManager\Security\Helpers;

class Form {

	public static function render( array $atts = array() ): string {
		$atts            = shortcode_atts(
			array(
				'id'      => '',
				'captcha' => 'turnstile',
			),
			$atts,
			'pcc_fb_form'
		);
				$id      = Helpers::sanitize_text( (string) $atts['id'] );
				$captcha = Helpers::sanitize_text( (string) $atts['captcha'] );
		if ( '' === $id ) {
				return '';
		}

				$ref = isset( $_GET['fbm_ref'] ) ? Helpers::sanitize_text( wp_unslash( (string) $_GET['fbm_ref'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( '' !== $ref ) {
				/* translators: %s: application reference */
				return '<div class="fbm-success">' . esc_html( sprintf( __( 'Application received. Reference %s', 'foodbank-manager' ), $ref ) ) . '</div>';
		}

		$preset_path = dirname( __DIR__, 2 ) . '/templates/forms/presets/foodbank-intake.json';
		$fields      = array();
		if ( file_exists( $preset_path ) ) {
				$json = file_get_contents( $preset_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$cfg      = json_decode( (string) $json, true );
			if ( is_array( $cfg ) && isset( $cfg['fields'] ) && is_array( $cfg['fields'] ) ) {
				$fields = $cfg['fields'];
			}
		}

		ob_start();
				echo '<form method="post" action="' . esc_url( rest_url( 'pcc-fb/v1/applications' ) ) . '" enctype="multipart/form-data">';
		echo '<input type="hidden" name="form_id" value="' . esc_attr( $id ) . '" />';
		echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'wp_rest' ) ) . '" />';
		foreach ( $fields as $field ) {
						$type     = isset( $field['type'] ) ? Helpers::sanitize_text( (string) $field['type'] ) : 'text';
						$name     = isset( $field['name'] ) ? Helpers::sanitize_text( (string) $field['name'] ) : '';
						$label    = isset( $field['label'] ) ? Helpers::sanitize_text( (string) $field['label'] ) : '';
						$required = ! empty( $field['required'] );
			if ( '' === $name ) {
					continue;
			}
						echo '<p><label>' . esc_html( $label ) . '<br /><input type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '"' . ( $required ? ' required' : '' ) . ' /></label></p>';
		}
		echo '<div class="fbm-captcha" data-provider="' . esc_attr( $captcha ) . '"></div>';
		echo '<p><input type="submit" value="' . esc_attr__( 'Submit', 'foodbank-manager' ) . '" /></p>';
		echo '</form>';
		return (string) ob_get_clean();
	}
}
