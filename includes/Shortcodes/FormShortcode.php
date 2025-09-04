<?php
/**
 * Form shortcode renderer.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Shortcodes;

use FoodBankManager\Forms\PresetsRepo;
use FoodBankManager\UI\Theme;
use function sanitize_key;
use function shortcode_atts;
use function get_option;

/**
 * Form shortcode.
 */
final class FormShortcode {
	/**
	 * Render shortcode.
	 *
	 * @param array<string,string> $atts Attributes.
	 * @return string
	 */
	public static function render( array $atts = array() ): string {
		Theme::enqueue_front();
		$atts = shortcode_atts( array( 'preset' => '' ), $atts, 'fbm_form' );
		$slug = sanitize_key( (string) $atts['preset'] );
		if ( '' === $slug ) {
			return '';
		}
		$schema = PresetsRepo::get_by_slug( $slug );
		if ( ! $schema ) {
			return '';
		}
		$captcha_enabled = ! empty( $schema['meta']['captcha'] );
		ob_start();
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="fbm_submit" />';
		echo '<input type="hidden" name="preset" value="' . esc_attr( $slug ) . '" />';
		echo '<input type="hidden" name="_fbm_nonce" value="' . esc_attr( wp_create_nonce( 'fbm_submit_form' ) ) . '" />';
		foreach ( $schema['fields'] as $field ) {
			self::render_field( $field );
		}
		if ( $captcha_enabled ) {
			echo '<p><label>' . esc_html__( 'Captcha', 'foodbank-manager' ) . ' <input type="text" name="captcha" required></label></p>';
		}
		echo '<p><button type="submit">' . esc_html__( 'Submit', 'foodbank-manager' ) . '</button></p>';
		echo '</form>';
		return (string) ob_get_clean();
	}

	/**
	 * Render a field.
	 *
	 * @param array<string,mixed> $field Field config.
	 * @return void
	 */
	private static function render_field( array $field ): void {
		$id       = (string) $field['id'];
		$label    = (string) $field['label'];
		$required = ! empty( $field['required'] );
		$type     = (string) $field['type'];
		$html_id  = 'fbm_' . $id;
		if ( in_array( $type, array( 'text', 'email', 'tel', 'date' ), true ) ) {
			echo '<p><label for="' . esc_attr( $html_id ) . '">' . esc_html( $label ) . '</label> ';
			echo '<input type="' . esc_attr( $type ) . '" id="' . esc_attr( $html_id ) . '" name="' . esc_attr( $id ) . '"';
			if ( $required ) {
				echo ' required';
			}
			echo ' />';
			echo '</p>';
			return;
		}
		if ( 'textarea' === $type ) {
			echo '<p><label for="' . esc_attr( $html_id ) . '">' . esc_html( $label ) . '</label>';
			echo '<textarea id="' . esc_attr( $html_id ) . '" name="' . esc_attr( $id ) . '" rows="4"' . ( $required ? ' required' : '' ) . '></textarea>';
			echo '</p>';
			return;
		}
		if ( in_array( $type, array( 'select', 'radio' ), true ) ) {
			$opts = is_array( $field['options'] ?? null ) ? $field['options'] : array();
			if ( 'select' === $type ) {
				echo '<p><label for="' . esc_attr( $html_id ) . '">' . esc_html( $label ) . '</label> ';
				echo '<select id="' . esc_attr( $html_id ) . '" name="' . esc_attr( $id ) . '"' . ( $required ? ' required' : '' ) . '>';
				foreach ( $opts as $opt ) {
					echo '<option value="' . esc_attr( (string) $opt ) . '">' . esc_html( (string) $opt ) . '</option>';
				}
				echo '</select></p>';
			} else {
				echo '<fieldset><legend>' . esc_html( $label ) . '</legend>';
				foreach ( $opts as $opt ) {
					$oid = $html_id . '_' . sanitize_key( (string) $opt );
					echo '<label for="' . esc_attr( $oid ) . '"><input type="radio" id="' . esc_attr( $oid ) . '" name="' . esc_attr( $id ) . '" value="';
					echo esc_attr( (string) $opt ) . '"';
					if ( $required ) {
						echo ' required';
					}
					echo ' /> ' . esc_html( (string) $opt ) . '</label>';
				}
				echo '</fieldset>';
			}
			return;
		}
		if ( 'checkbox' === $type ) {
			echo '<p><label><input type="checkbox" name="' . esc_attr( $id ) . '" value="1"';
			if ( $required ) {
				echo ' required';
			}
			echo ' /> ' . esc_html( $label ) . '</label></p>';
		}
	}
}
