<?php
/**
 * Email templates admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Core\Options;
use FoodBankManager\Mail\Templates;

/**
 * Emails admin page.
 */
final class EmailsPage {
	/**
	 * Route the emails page.
	 *
	 * @return void
	 */
	public static function route(): void {
		if ( ! current_user_can( 'fb_manage_emails' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			wp_die(
				esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ),
				'',
				array( 'response' => 403 )
			);
		}

               $templates = Templates::defaults();
               foreach ( $templates as $id => &$tpl ) {
                       $saved = Options::get_template( $id );
                       if ( $saved['subject'] ) {
                               $tpl['subject'] = $saved['subject'];
                       }
                       $tpl['body_html'] = $saved['body_html'] ?: $tpl['body'];
               }
               unset( $tpl );

               $method = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
               if ( 'POST' === $method ) {
                       $action = sanitize_key( wp_unslash( $_POST['fbm_action'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- validated in handler
                       if ( 'emails_save' === $action ) {
                               self::handle_save( $templates );
                       }
               }

               $current = isset( $_GET['tpl'] ) ? sanitize_key( (string) $_GET['tpl'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

               require FBM_PATH . 'templates/admin/emails.php';
       }

       /**
        * Handle template save.
        *
        * @param array<string,array<string,string>> $templates Templates.
        * @return void
        */
       private static function handle_save( array $templates ): void {
               check_admin_referer( 'fbm_emails_save', '_fbm_nonce' );
               if ( ! current_user_can( 'fb_manage_emails' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
                       wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
               }

               $tpl = sanitize_key( wp_unslash( $_POST['tpl'] ?? '' ) );
               if ( ! isset( $templates[ $tpl ] ) ) {
                       wp_die( esc_html__( 'Invalid template.', 'foodbank-manager' ) );
               }

               $subject   = isset( $_POST['subject'] ) ? wp_unslash( (string) $_POST['subject'] ) : '';
               $body_html = isset( $_POST['body_html'] ) ? wp_unslash( (string) $_POST['body_html'] ) : '';

               Options::set_template(
                       $tpl,
                       array(
                               'subject'   => $subject,
                               'body_html' => $body_html,
                       )
               );

               $url = add_query_arg(
                       array(
                               'notice' => 'saved',
                               'tpl'    => $tpl,
                       ),
                       menu_page_url( 'fbm-emails', false )
               );
               wp_safe_redirect( esc_url_raw( $url ), 303 );
               exit;
       }
}
