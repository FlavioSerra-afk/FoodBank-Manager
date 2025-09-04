<?php
/**
 * Settings admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Core\Options;
use FoodBankManager\Core\Retention;

/**
 * Settings admin page.
 */
class SettingsPage {
		/**
		 * Route the settings page.
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
		 */
	private static function handle_branding(): void {
			check_admin_referer( 'fbm_branding_save', '_fbm_nonce' );
		if ( ! current_user_can( 'fb_manage_settings' ) ) {
				wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}

				$data = isset( $_POST['branding'] ) && is_array( $_POST['branding'] )
						? wp_unslash( $_POST['branding'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						: array();
			Options::update( array( 'branding' => $data ) );

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
		 */
        private static function handle_email(): void {
			check_admin_referer( 'fbm_email_save', '_fbm_nonce' );
		if ( ! current_user_can( 'fb_manage_settings' ) ) {
				wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}

				$data = isset( $_POST['emails'] ) && is_array( $_POST['emails'] )
						? wp_unslash( $_POST['emails'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						: array();
			Options::update( array( 'emails' => $data ) );

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
        * Handle retention settings save.
        */
       private static function handle_retention_save(): void {
               check_admin_referer( 'fbm_retention_save', '_fbm_nonce' );
               if ( ! current_user_can( 'fb_manage_settings' ) ) {
                       wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
               }
               $data       = isset( $_POST['retention'] ) && is_array( $_POST['retention'] ) ? wp_unslash( $_POST['retention'] ) : array();
               $out        = array();
               $categories = array( 'applications', 'attendance', 'mail_log' );
               foreach ( $categories as $cat ) {
                       $days   = isset( $data[ $cat ]['days'] ) ? absint( $data[ $cat ]['days'] ) : 0;
                       $policy = isset( $data[ $cat ]['policy'] ) ? sanitize_key( $data[ $cat ]['policy'] ) : 'delete';
                       if ( ! in_array( $policy, array( 'delete', 'anonymise' ), true ) ) {
                               $policy = 'delete';
                       }
                       $out[ $cat ] = array( 'days' => $days, 'policy' => $policy );
               }
               Options::set( 'privacy.retention', $out );
               $url = add_query_arg( array( 'notice' => 'saved', 'tab' => 'privacy' ), menu_page_url( 'fbm_settings', false ) );
               wp_safe_redirect( esc_url_raw( $url ), 303 );
               exit;
       }

       /**
        * Run retention job immediately.
        *
        * @param bool $dry_run Dry run only.
        */
       private static function handle_retention_run( bool $dry_run ): void {
               check_admin_referer( $dry_run ? 'fbm_retention_dryrun' : 'fbm_retention_run', '_fbm_nonce' );
               if ( ! current_user_can( 'fb_manage_settings' ) ) {
                       wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
               }
               $summary = Retention::run( $dry_run );
               $args    = array( 'notice' => 'saved', 'tab' => 'privacy' );
               if ( $dry_run ) {
                       $args['dryrun'] = '1';
               }
               $url = add_query_arg( $args, menu_page_url( 'fbm_settings', false ) );
               wp_safe_redirect( esc_url_raw( $url ), 303 );
               exit;
       }
}
