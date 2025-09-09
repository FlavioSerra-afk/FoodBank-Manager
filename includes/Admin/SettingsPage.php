<?php
/**
 * Settings admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Core\Options;
use FBM\Core\Retention;
use FBM\Core\RetentionConfig;

/**
 * Settings admin page.
 */
class SettingsPage {
				/**
				 * Route the settings page.
				 *
				 * @return void
				 */
	public static function route(): void {
		if ( ! current_user_can( 'fb_manage_settings' ) ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}

			$method = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only server var
		if ( 'POST' !== $method ) {
				/* @psalm-suppress UnresolvableInclude */
				require FBM_PATH . 'templates/admin/settings.php';
				return;
		}

				$action = sanitize_key( wp_unslash( $_POST['fbm_action'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- validated in handler
		if ( 'branding_save' === $action ) {
										self::handle_branding();
		} elseif ( 'email_save' === $action ) {
										self::handle_email();
		} elseif ( 'theme_save' === $action ) {
										self::handle_theme();
		} elseif ( 'retention_save' === $action ) {
										self::handle_retention_save();
		} elseif ( 'retention_run' === $action ) {
										self::handle_retention_run( false );
		} elseif ( 'retention_dryrun' === $action ) {
										self::handle_retention_run( true );
		}
	}

				/**
				 * Handle branding settings save.
				 *
				 * @return void
				 */
	private static function handle_branding(): void {
		check_admin_referer( 'fbm_branding_save', '_fbm_nonce' );
		if ( ! current_user_can( 'fb_manage_settings' ) ) {
				wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}

		$raw      = filter_input( INPUT_POST, 'branding', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$raw      = is_array( $raw ) ? array_map( 'wp_unslash', $raw ) : array();
			$data = array();
		if ( isset( $raw['site_name'] ) ) {
				$data['site_name'] = sanitize_text_field( (string) $raw['site_name'] );
		}
		if ( isset( $raw['logo_url'] ) ) {
				$data['logo_url'] = esc_url_raw( trim( (string) $raw['logo_url'] ) );
		}
		if ( isset( $raw['color'] ) ) {
				$color         = sanitize_key( (string) $raw['color'] );
				$allowed       = array( 'default', 'blue', 'green', 'red', 'orange', 'purple' );
				$data['color'] = in_array( $color, $allowed, true ) ? $color : 'default';
		}
			Options::save( array( 'branding' => $data ) );

		$url = add_query_arg(
			array(
				'notice' => 'saved',
				'tab'    => 'branding',
			),
			menu_page_url( 'fbm_settings', false )
		);
		wp_safe_redirect( esc_url_raw( $url ), 303 );
		exit;
	}

				/**
				 * Handle email settings save.
				 *
				 * @return void
				 */
	private static function handle_email(): void {
			check_admin_referer( 'fbm_email_save', '_fbm_nonce' );
		if ( ! current_user_can( 'fb_manage_settings' ) ) {
						wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}

			$raw  = filter_input( INPUT_POST, 'emails', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			$raw  = is_array( $raw ) ? array_map( 'wp_unslash', $raw ) : array();
			$data = array();
		if ( isset( $raw['from_name'] ) ) {
			$data['from_name'] = sanitize_text_field( (string) $raw['from_name'] );
		}
		if ( isset( $raw['from_address'] ) ) {
			$data['from_address'] = sanitize_email( (string) $raw['from_address'] );
		}
		if ( isset( $raw['reply_to'] ) ) {
			$data['reply_to'] = sanitize_email( (string) $raw['reply_to'] );
		}
			Options::save( array( 'emails' => $data ) );

			$url = add_query_arg(
				array(
					'notice' => 'saved',
					'tab'    => 'email',
				),
				menu_page_url( 'fbm_settings', false )
			);
			wp_safe_redirect( esc_url_raw( $url ), 303 );
			exit;
	}

								/**
								 * Handle theme settings save.
								 *
								 * @return void
								 */
	private static function handle_theme(): void {
			check_admin_referer( 'fbm_theme_save', '_fbm_nonce' );
		if ( ! current_user_can( 'fb_manage_settings' ) ) {
						wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}

			$raw = filter_input( INPUT_POST, 'theme', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			$raw = is_array( $raw ) ? array_map( 'wp_unslash', $raw ) : array();

			$preset_raw = sanitize_key( (string) ( $raw['preset'] ?? '' ) );
			$allowed_p  = array( 'system', 'light', 'dark', 'high_contrast' );
			$preset     = in_array( $preset_raw, $allowed_p, true ) ? $preset_raw : 'system';

			$rtl_raw   = sanitize_key( (string) ( $raw['rtl'] ?? '' ) );
			$allowed_r = array( 'auto', 'force_on', 'force_off' );
			$rtl       = in_array( $rtl_raw, $allowed_r, true ) ? $rtl_raw : 'auto';

			Options::save(
				array(
					'theme' => array(
						'preset' => $preset,
						'rtl'    => $rtl,
					),
				)
			);

			$url = add_query_arg(
				array(
					'notice' => 'saved',
					'tab'    => 'appearance',
				),
				menu_page_url( 'fbm_settings', false )
			);
			wp_safe_redirect( esc_url_raw( $url ), 303 );
			exit;
	}

				/**
				 * Handle retention settings save.
				 *
				 * @return void
				 */
	private static function handle_retention_save(): void {
			check_admin_referer( 'fbm_retention_save', '_fbm_nonce' );
		if ( ! current_user_can( 'fb_manage_settings' ) ) {
						wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}

			$raw_data = filter_input( INPUT_POST, 'retention', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) ?? array();
			$raw_data = is_array( $raw_data ) ? array_map( 'wp_unslash', $raw_data ) : array();
			$out      = RetentionConfig::normalize( $raw_data );
					Options::set( 'privacy.retention', $out );
		$url = add_query_arg(
			array(
				'notice' => 'saved',
				'tab'    => 'privacy',
			),
			menu_page_url( 'fbm_settings', false )
		);
		wp_safe_redirect( esc_url_raw( $url ), 303 );
		exit;
	}

				/**
				 * Run retention job immediately.
				 *
				 * @param bool $dry_run Dry run only.
				 * @return void
				 */
	private static function handle_retention_run( bool $dry_run ): void {
		check_admin_referer( $dry_run ? 'fbm_retention_dryrun' : 'fbm_retention_run', '_fbm_nonce' );
		if ( ! current_user_can( 'fb_manage_settings' ) ) {
				wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}
		$summary = Retention::run( $dry_run );
		$args    = array(
			'notice' => 'saved',
			'tab'    => 'privacy',
		);
		if ( $dry_run ) {
					$args['dryrun'] = '1';
		}
		$url = add_query_arg( $args, menu_page_url( 'fbm_settings', false ) );
		wp_safe_redirect( esc_url_raw( $url ), 303 );
		exit;
	}
}
