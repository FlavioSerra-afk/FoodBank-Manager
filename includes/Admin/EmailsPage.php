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
use FBM\Mail\LogRepo;

/**
 * Emails admin page.
 */
final class EmailsPage {
	private const CAP = 'fb_manage_emails';
	/**
	 * Route the emails page.
	 *
	 * @return void
	 */
	public static function route(): void {
		if ( ! current_user_can( self::CAP ) ) {
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
			if ( $saved['body_html'] ) {
							$tpl['body_html'] = $saved['body_html'];
			} else {
				$tpl['body_html'] = $tpl['body'];
			}
		}
			unset( $tpl );

															$preview = array(
																'subject'   => '',
																'body_html' => '',
															);

															$method = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) );
															if ( 'POST' === $method ) {
																			$action = sanitize_key( wp_unslash( $_POST['fbm_action'] ?? '' ) );
																if ( in_array( $action, array( 'emails_save', 'emails_preview', 'emails_reset' ), true ) ) {
																				check_admin_referer( 'fbm_' . $action, '_fbm_nonce' );
																	if ( 'emails_save' === $action ) {
																					self::handle_save( $templates );
																	} elseif ( 'emails_preview' === $action ) {
																							$preview = self::handle_preview( $templates );
																		if ( isset( $_POST['fbm_ajax'] ) ) {
																			\wp_send_json( $preview );
																		}
																	} elseif ( 'emails_reset' === $action ) {
																					self::handle_reset( $templates );
																	}
																}
															}

															$current = isset( $_GET['tpl'] ) ? sanitize_key( (string) $_GET['tpl'] ) : '';

															$allowed_tokens = Templates::tokens();
															$logs           = LogRepo::find_by_application_id( 0 );

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
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}

			$tpl = sanitize_key( wp_unslash( $_POST['tpl'] ?? '' ) );
		if ( ! isset( $templates[ $tpl ] ) ) {
					wp_die( esc_html__( 'Invalid template.', 'foodbank-manager' ) );
		}

				$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['subject'] ) ) : '';
				$subject = trim( $subject );
		if ( mb_strlen( $subject ) > 255 ) {
				$subject = mb_substr( $subject, 0, 255 );
		}
				$body_html = isset( $_POST['body_html'] ) ? wp_kses_post( wp_unslash( (string) $_POST['body_html'] ) ) : '';
				$body_html = trim( $body_html );
		if ( mb_strlen( $body_html ) > 32768 ) {
				$body_html = mb_substr( $body_html, 0, 32768 );
		}

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
				menu_page_url( 'fbm_emails', false )
			);
			wp_safe_redirect( esc_url_raw( $url ), 303 );
			exit;
	}

		/**
		 * Handle template reset.
		 *
		 * @param array<string,array<string,string>> $templates Templates.
		 * @return void
		 */
	private static function handle_reset( array $templates ): void {
			check_admin_referer( 'fbm_emails_reset', '_fbm_nonce' );
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}

			$tpl = sanitize_key( wp_unslash( $_POST['tpl'] ?? '' ) );
		if ( ! isset( $templates[ $tpl ] ) ) {
					wp_die( esc_html__( 'Invalid template.', 'foodbank-manager' ) );
		}

			Options::reset_template( $tpl );

			$url = add_query_arg(
				array(
					'notice' => 'reset',
					'tpl'    => $tpl,
				),
				menu_page_url( 'fbm_emails', false )
			);
			wp_safe_redirect( esc_url_raw( $url ), 303 );
			exit;
	}

		/**
		 * Handle preview request.
		 *
		 * @param array<string,array<string,string>> $templates Templates.
		 * @return array{subject:string,body_html:string}
		 */
	private static function handle_preview( array $templates ): array {
			check_admin_referer( 'fbm_emails_preview', '_fbm_nonce' );
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}

			$tpl = sanitize_key( wp_unslash( $_POST['tpl'] ?? '' ) );
		if ( ! isset( $templates[ $tpl ] ) ) {
					wp_die( esc_html__( 'Invalid template.', 'foodbank-manager' ) );
		}

				$subject   = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['subject'] ) ) : ( $templates[ $tpl ]['subject'] ?? '' );
				$subject   = trim( $subject );
				$body_html = isset( $_POST['body_html'] ) ? wp_kses_post( wp_unslash( (string) $_POST['body_html'] ) ) : ( $templates[ $tpl ]['body_html'] ?? '' );
				$body_html = trim( $body_html );

				$vars = array(
					'first_name'       => '***',
					'last_name'        => '***',
					'application_id'   => '***',
					'site_name'        => get_bloginfo( 'name' ),
					'appointment_time' => '***',
				);

				$subject = Templates::apply_tokens( $subject, $vars, false );
				$subject = wp_strip_all_tags( $subject );
				if ( mb_strlen( $subject ) > 255 ) {
								$subject = mb_substr( $subject, 0, 255 );
				}

				$body_html = Templates::apply_tokens( $body_html, $vars, true );
				$body_html = wp_kses_post( $body_html );
				if ( mb_strlen( $body_html ) > 32768 ) {
								$body_html = mb_substr( $body_html, 0, 32768 );
				}

				return array(
					'subject'   => $subject,
					'body_html' => $body_html,
				);
	}
}
