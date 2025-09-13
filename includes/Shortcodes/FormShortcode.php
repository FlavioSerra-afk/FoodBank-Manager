<?php // phpcs:ignoreFile
/**
 * Form shortcode renderer.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Shortcodes;

use FoodBankManager\Forms\PresetsRepo;
use FoodBankManager\Forms\Schema;
use FoodBankManager\UI\Theme;
use FoodBankManager\Core\Plugin;
use function sanitize_key;
use function shortcode_atts;
use function wp_nonce_field;
use function get_post_type;
use function wp_register_style;
use function wp_add_inline_style;
use function wp_enqueue_style;
use function add_filter;
use function explode;
use function wp_unslash;

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
        $theme = Theme::get();
        $front         = $theme['front'];
                if ( ! empty( $front['enabled'] ) ) {
        wp_enqueue_style( 'fbm-public', plugins_url( 'assets/css/public.css', FBM_FILE ), array(), Plugin::VERSION ); // @phpstan-ignore-line
                                               wp_add_inline_style( 'fbm-public', Theme::css_variables_scoped() );
                        }
			$atts = shortcode_atts(
				array(
					'id'     => '',
					'preset' => '',
				),
				$atts,
				'fbm_form'
			);
		$id       = (int) $atts['id'];
		$slug     = '';
		if ( $id > 0 && function_exists( 'get_post_type' ) && 'fb_form' === get_post_type( $id ) ) {
				$form = \FBM\Forms\FormRepo::get( $id );
			if ( ! $form ) {
					return '';
			}
				$schema = $form['schema'];
		} else {
				$slug = sanitize_key( (string) $atts['preset'] );
			if ( '' === $slug ) {
					return '';
			}
				$schema = PresetsRepo::get_by_slug( $slug );
			if ( ! $schema ) {
					return '';
			}
		}
		try {
						$schema = Schema::normalize( $schema );
		} catch ( \InvalidArgumentException $e ) {
						return '';
		}
			$captcha_enabled = ( $schema['meta']['captcha'] ?? false ) === true;
			$errors          = array();
		if ( isset( $_GET['fbm_err'] ) ) {
				$raw    = explode( ',', (string) wp_unslash( $_GET['fbm_err'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only
				$errors = array_map( 'sanitize_key', $raw );
		}
			$first_error = $errors[0] ?? '';
                        $classes     = 'fbm-scope fbm-app fbm-public fbm-theme--' . $front['style'] . ' fbm-preset--' . $front['preset'];
                if ( ! empty( $theme['apply_front_menus'] ) && 'glass' === $front['style'] ) {
                                $classes .= ' fbm-menus--glass';
                }
			ob_start();
			echo '<div class="' . esc_attr( $classes ) . '">';
			echo '<div class="fbm-status" role="status" aria-live="polite" aria-atomic="true"></div>';
			echo '<div class="fbm-errors" role="alert">';
		if ( $errors ) {
				echo '<ul>';
			foreach ( $schema['fields'] as $field ) {
					$fid = (string) $field['id'];
				if ( in_array( $fid, $errors, true ) ) {
					$eid = 'fbm_err_' . $fid;
					echo '<li><a href="#' . esc_attr( $eid ) . '">' . esc_html( (string) $field['label'] ) . '</a></li>';
				}
			}
				echo '</ul>';
		}
			echo '</div>';
			echo '<form class="fbm-form fbm-card fbm-card--glass" method="post" action="' .
			esc_url( admin_url( 'admin-post.php' ) ) . '">';
			echo '<input type="hidden" name="action" value="fbm_submit" />';
		if ( '' !== $slug ) {
				echo '<input type="hidden" name="preset" value="' . esc_attr( $slug ) . '" />';
		}
			wp_nonce_field( 'fbm_submit_form', '_fbm_nonce', false );
		foreach ( $schema['fields'] as $field ) {
				self::render_field( $field, $errors, $first_error );
		}
		if ( $captcha_enabled ) {
				echo '<p><label>' . esc_html__( 'Captcha', 'foodbank-manager' ) . ' <input type="text" name="captcha" required></label></p>';
		}
			echo '<p><button class="fbm-button--glass" type="submit">' . esc_html__( 'Submit', 'foodbank-manager' ) . '</button></p>';
			echo '</form></div>';
			return (string) ob_get_clean();
	}

		/**
		 * Render a field.
		 *
		 * @param array<string,mixed> $field Field config.
		 * @param string[]            $errors Error IDs.
		 * @param string              $first_error First error field ID.
		 * @return void
		 */
	private static function render_field( array $field, array $errors, string $first_error ): void {
			$id        = (string) $field['id'];
			$label     = (string) $field['label'];
			$required  = ! empty( $field['required'] );
			$type      = (string) $field['type'];
			$html_id   = 'fbm_' . $id;
			$error     = in_array( $id, $errors, true );
			$err_id    = 'fbm_err_' . $id;
			$auto      = self::autocomplete_attr( $id );
			$auto_attr = $auto ? ' autocomplete="' . esc_attr( $auto ) . '"' : '';
			$aria      = $error ? ' aria-invalid="true" aria-describedby="' . esc_attr( $err_id ) . '"' : '';
			$class     = $error ? ' class="is-invalid"' : '';
			$focus     = $error && $first_error === $id ? ' autofocus' : '';
		if ( in_array( $type, array( 'text', 'email', 'tel', 'date' ), true ) ) {
				echo '<p><label for="' . esc_attr( $html_id ) . '">' . esc_html( $label ) . '</label> ';
				echo '<input type="' . esc_attr( $type ) . '" id="' . esc_attr( $html_id ) . '" name="' . esc_attr( $id ) . '"' . $class;
			if ( $required ) {
				echo ' required';
			}
				echo $aria . $auto_attr . $focus . ' />';
			if ( $error ) {
					echo ' <span class="fbm-error"><span class="fbm-error-icon" aria-hidden="true">!</span> <span id="' . esc_attr( $err_id ) . '">' . esc_html__( 'Required', 'foodbank-manager' ) . '</span></span>';
			}
				echo '</p>';
				return;
		}
		if ( 'textarea' === $type ) {
				echo '<p><label for="' . esc_attr( $html_id ) . '">' . esc_html( $label ) . '</label>';
				echo '<textarea id="' . esc_attr( $html_id ) . '" name="' . esc_attr( $id ) . '" rows="4"' . ( $required ? ' required' : '' ) . $class . $aria . $auto_attr . $focus . '></textarea>';
			if ( $error ) {
					echo ' <span class="fbm-error"><span class="fbm-error-icon" aria-hidden="true">!</span> <span id="' . esc_attr( $err_id ) . '">' . esc_html__( 'Required', 'foodbank-manager' ) . '</span></span>';
			}
				echo '</p>';
				return;
		}
		if ( in_array( $type, array( 'select', 'radio' ), true ) ) {
				$opts = is_array( $field['options'] ?? null ) ? $field['options'] : array();
			if ( 'select' === $type ) {
					echo '<p><label for="' . esc_attr( $html_id ) . '">' . esc_html( $label ) . '</label> ';
					echo '<select id="' . esc_attr( $html_id ) . '" name="' . esc_attr( $id ) . '"' . ( $required ? ' required' : '' ) . $class . $aria . $auto_attr . $focus . '>';
				foreach ( $opts as $opt ) {
					echo '<option value="' . esc_attr( (string) $opt ) . '">' . esc_html( (string) $opt ) . '</option>';
				}
					echo '</select>';
				if ( $error ) {
						echo ' <span class="fbm-error"><span class="fbm-error-icon" aria-hidden="true">!</span> <span id="' . esc_attr( $err_id ) . '">' . esc_html__( 'Required', 'foodbank-manager' ) . '</span></span>';
				}
					echo '</p>';
			} else {
					echo '<fieldset><legend>' . esc_html( $label ) . '</legend>';
				foreach ( $opts as $opt ) {
						$oid = $html_id . '_' . sanitize_key( (string) $opt );
						echo '<label for="' . esc_attr( $oid ) . '"><input type="radio" id="' . esc_attr( $oid ) . '" name="' . esc_attr( $id ) . '" value="';
						echo esc_attr( (string) $opt ) . '"';
					if ( $required ) {
						echo ' required';
					}
						echo $class . $aria . $auto_attr;
					if ( $error && $first_error === $id ) {
							echo ' autofocus';
					}
						echo ' /> ' . esc_html( (string) $opt ) . '</label>';
				}
				if ( $error ) {
						echo ' <span class="fbm-error"><span class="fbm-error-icon" aria-hidden="true">!</span> <span id="' . esc_attr( $err_id ) . '">' . esc_html__( 'Required', 'foodbank-manager' ) . '</span></span>';
				}
					echo '</fieldset>';
			}
				return;
		}
		if ( 'checkbox' === $type ) {
				echo '<p><label><input type="checkbox" name="' . esc_attr( $id ) . '" value="1"' . $class;
			if ( $required ) {
					echo ' required';
			}
				echo $aria . $auto_attr . $focus . ' /> ' . esc_html( $label ) . '</label>';
			if ( $error ) {
					echo ' <span class="fbm-error"><span class="fbm-error-icon" aria-hidden="true">!</span> <span id="' . esc_attr( $err_id ) . '">' . esc_html__( 'Required', 'foodbank-manager' ) . '</span></span>';
			}
				echo '</p>';
		}
	}

		/**
		 * Map field name to autocomplete token.
		 */
	private static function autocomplete_attr( string $name ): string {
			$map = array(
				'name'          => 'name',
				'first_name'    => 'given-name',
				'last_name'     => 'family-name',
				'email'         => 'email',
				'phone'         => 'tel',
				'tel'           => 'tel',
				'address'       => 'street-address',
				'address_line1' => 'address-line1',
				'address-line1' => 'address-line1',
				'address_line2' => 'address-line2',
				'address-line2' => 'address-line2',
				'postal_code'   => 'postal-code',
				'postal-code'   => 'postal-code',
				'country'       => 'country',
				'organization'  => 'organization',
				'url'           => 'url',
			);
			$key = str_replace( '-', '_', sanitize_key( $name ) );
			return $map[ $key ] ?? '';
	}
}
