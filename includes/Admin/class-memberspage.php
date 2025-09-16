<?php
/**
 * Members admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Email\WelcomeMailer;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use RuntimeException;
use wpdb;
use function __;
use function absint;
use function add_action;
use function add_menu_page;
use function add_query_arg;
use function admin_url;
use function apply_filters;
use function check_admin_referer;
use function current_user_can;
use function do_action;
use function esc_html__;
use function is_readable;
use function sanitize_key;
use function sanitize_text_field;
use function sprintf;
use function filter_input;
use function wp_die;
use function wp_nonce_url;
use function wp_safe_redirect;
use function wp_unslash;
use const FILTER_UNSAFE_RAW;
use const INPUT_GET;

/**
 * Renders the Food Bank members management page.
 */
final class MembersPage {

	private const MENU_SLUG    = 'fbm-members';
	private const TEMPLATE     = 'templates/admin/members-page.php';
	private const ACTION_PARAM = 'fbm-action';
	private const NOTICE_PARAM = 'fbm_notice';
	private const MEMBER_PARAM = 'fbm_member';
	private const STATUS_PARAM = 'fbm_status';
	private const ERROR_PARAM  = 'fbm_error';

	private const ACTION_RESEND = 'resend';
	private const ACTION_REVOKE = 'revoke';

		/**
		 * Register WordPress hooks.
		 */
	public static function register(): void {
			add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
			add_action( 'admin_init', array( __CLASS__, 'handle_actions' ) );
	}

		/**
		 * Register the admin menu entry.
		 */
	public static function register_menu(): void {
			add_menu_page(
				__( 'Food Bank Members', 'foodbank-manager' ),
				__( 'Food Bank Members', 'foodbank-manager' ),
				'fbm_manage', // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered during activation.
				self::MENU_SLUG,
				array( __CLASS__, 'render' ),
				'dashicons-groups'
			);
	}

		/**
		 * Render the members page.
		 */
	public static function render(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
		}

			global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
				wp_die( esc_html__( 'Database connection unavailable.', 'foodbank-manager' ) );
		}

			$repository = new MembersRepository( $wpdb );
			$members    = $repository->all();

		foreach ( $members as $index => $member ) {
				$members[ $index ]['resend_url'] = self::build_action_url( self::ACTION_RESEND, $member['id'] );
				$members[ $index ]['revoke_url'] = self::build_action_url( self::ACTION_REVOKE, $member['id'] );
		}

			$members = apply_filters( 'fbm_members_page_members', $members );

			$notices = self::resolve_notices();

			do_action( 'fbm_members_page_admin_notices', $notices );

			$context = array(
				'members' => $members,
				'notices' => $notices,
			);

			$template = FBM_PATH . self::TEMPLATE;

			if ( ! is_readable( $template ) ) {
					wp_die( esc_html__( 'Members admin template is missing.', 'foodbank-manager' ) );
			}

			do_action( 'fbm_members_page_before_template', $context );

			$data = $context;
			include $template;

			do_action( 'fbm_members_page_after_template', $context );
	}

		/**
		 * Handle resend and revoke actions.
		 */
	public static function handle_actions(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
				return;
		}

			$page = isset( $_GET['page'] )
					? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin screen routing.
					: '';

		if ( self::MENU_SLUG !== $page ) {
				return;
		}

			$action = isset( $_GET[ self::ACTION_PARAM ] )
					? sanitize_key( wp_unslash( (string) $_GET[ self::ACTION_PARAM ] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Action validated and nonce checked below.
					: '';

		if ( '' === $action ) {
				return;
		}

			$member_id = isset( $_GET['member_id'] )
					? absint( wp_unslash( (string) $_GET['member_id'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified for actionable requests.
					: 0;

		if ( $member_id < 1 ) {
				self::redirect_with_outcome(
					array(
						'notice' => 'member-missing',
						'status' => false,
						'error'  => 'missing',
					)
				);
				return;
		}

		switch ( $action ) {
			case self::ACTION_RESEND:
					check_admin_referer( self::resend_nonce_action( $member_id ) );
					$outcome = self::process_resend( $member_id );
				break;
			case self::ACTION_REVOKE:
					check_admin_referer( self::revoke_nonce_action( $member_id ) );
					$outcome = self::process_revoke( $member_id );
				break;
			default:
				return;
		}

			do_action( 'fbm_members_page_action_processed', $action, $outcome );

			self::redirect_with_outcome( $outcome );
	}

		/**
		 * Resolve notices for the current request.
		 *
		 * @return array<int, array{type:string,message:string}>
		 */
	private static function resolve_notices(): array {
		$raw_notice = filter_input( INPUT_GET, self::NOTICE_PARAM, FILTER_UNSAFE_RAW );
		if ( is_string( $raw_notice ) ) {
			$raw_notice = wp_unslash( $raw_notice );
		} else {
			$raw_notice = '';
		}
		$code = sanitize_key( $raw_notice );

		if ( '' === $code ) {
			return array();
		}

		$raw_member = filter_input( INPUT_GET, self::MEMBER_PARAM, FILTER_UNSAFE_RAW );
		if ( is_string( $raw_member ) ) {
			$raw_member = wp_unslash( $raw_member );
		} else {
			$raw_member = '';
		}
		$member_reference = sanitize_text_field( $raw_member );

			$notices = array();

		switch ( $code ) {
			case 'resent':
				if ( '' !== $member_reference ) {
					$message = sprintf(
						/* translators: %s: Member reference. */
						__( 'Sent a new QR code email to %s.', 'foodbank-manager' ),
						$member_reference
					);
				} else {
						$message = __( 'Sent a new QR code email.', 'foodbank-manager' );
				}

					$notices[] = array(
						'type'    => 'success',
						'message' => $message,
					);
				break;
			case 'resend-mail':
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'Issued a new token, but the email could not be sent.', 'foodbank-manager' ),
					);
				break;
			case 'resend-issue':
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'Unable to issue a new token for the selected member.', 'foodbank-manager' ),
					);
				break;
			case 'revoked':
				if ( '' !== $member_reference ) {
						$message = sprintf(
								/* translators: %s: Member reference. */
							__( 'Revoked active tokens for %s.', 'foodbank-manager' ),
							$member_reference
						);
				} else {
						$message = __( 'Revoked active tokens for the member.', 'foodbank-manager' );
				}

					$notices[] = array(
						'type'    => 'success',
						'message' => $message,
					);
				break;
			case 'member-missing':
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'The requested member could not be found.', 'foodbank-manager' ),
					);
				break;
			case 'revoke-failed':
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'Unable to revoke tokens for the selected member.', 'foodbank-manager' ),
					);
				break;
			default:
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'The requested action could not be completed.', 'foodbank-manager' ),
					);
		}

			$notices = apply_filters( 'fbm_members_page_notices', $notices, $code, $member_reference );

			return $notices;
	}

		/**
		 * Build an action URL for a member.
		 *
		 * @param string $action    Action key.
		 * @param int    $member_id Member identifier.
		 */
	private static function build_action_url( string $action, int $member_id ): string {
			$url = add_query_arg(
				array(
					'page'             => self::MENU_SLUG,
					self::ACTION_PARAM => $action,
					'member_id'        => $member_id,
				),
				admin_url( 'admin.php' )
			);

			$nonce = self::ACTION_RESEND === $action
					? self::resend_nonce_action( $member_id )
					: self::revoke_nonce_action( $member_id );

			return wp_nonce_url( $url, $nonce );
	}

		/**
		 * Compute the resend nonce action for a member.
		 *
		 * @param int $member_id Member identifier.
		 */
	private static function resend_nonce_action( int $member_id ): string {
			return 'fbm_member_resend_' . $member_id;
	}

		/**
		 * Compute the revoke nonce action for a member.
		 *
		 * @param int $member_id Member identifier.
		 */
	private static function revoke_nonce_action( int $member_id ): string {
			return 'fbm_member_revoke_' . $member_id;
	}

		/**
		 * Redirect back to the members page with a status notice.
		 *
		 * @param array{notice?:string,member_reference?:string,status?:bool,error?:string} $outcome Outcome payload.
		 */
	private static function redirect_with_outcome( array $outcome ): void {
		if ( ! isset( $outcome['notice'] ) || '' === $outcome['notice'] ) {
				$outcome['notice'] = 'member-missing';
		}

			$args = array(
				'page'             => self::MENU_SLUG,
				self::NOTICE_PARAM => $outcome['notice'],
			);

			if ( isset( $outcome['member_reference'] ) && '' !== $outcome['member_reference'] ) {
					$args[ self::MEMBER_PARAM ] = $outcome['member_reference'];
			}

			if ( isset( $outcome['status'] ) ) {
					$args[ self::STATUS_PARAM ] = $outcome['status'] ? 'success' : 'error';
			}

			if ( isset( $outcome['error'] ) && '' !== $outcome['error'] ) {
					$args[ self::ERROR_PARAM ] = $outcome['error'];
			}

			$redirect = add_query_arg( $args, admin_url( 'admin.php' ) );

			wp_safe_redirect( $redirect );
			exit;
	}

		/**
		 * Process the resend action.
		 *
		 * @param int $member_id Member identifier.
		 *
		 * @return array{notice:string,member_reference?:string,status:bool,error?:string}
		 */
	private static function process_resend( int $member_id ): array {
			global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
				return array(
					'notice' => 'resend-issue',
					'status' => false,
					'error'  => 'database',
				);
		}

			$repository = new MembersRepository( $wpdb );
			$member     = $repository->find( $member_id );

		if ( null === $member ) {
				return array(
					'notice' => 'member-missing',
					'status' => false,
					'error'  => 'missing',
				);
		}

			$outcome = array(
				'notice'           => 'resend-issue',
				'member_reference' => $member['member_reference'],
				'status'           => false,
				'error'            => 'issue',
			);

			$tokens = new TokenService( new TokenRepository( $wpdb ) );

			try {
					$token = $tokens->issue( $member_id );
			} catch ( RuntimeException $exception ) {
					unset( $exception );

					return $outcome;
			}

			$mailer = new WelcomeMailer();

			if ( ! $mailer->send( $member['email'], $member['first_name'], $member['member_reference'], $token ) ) {
					$outcome['notice'] = 'resend-mail';
					$outcome['error']  = 'mail';

					return $outcome;
			}

			$outcome['notice'] = 'resent';
			$outcome['status'] = true;
			unset( $outcome['error'] );

			do_action( 'fbm_members_page_resend_sent', $member_id, $member );

			return $outcome;
	}

		/**
		 * Process the revoke action.
		 *
		 * @param int $member_id Member identifier.
		 *
		 * @return array{notice:string,member_reference?:string,status:bool,error?:string}
		 */
	private static function process_revoke( int $member_id ): array {
			global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
				return array(
					'notice' => 'revoke-failed',
					'status' => false,
					'error'  => 'database',
				);
		}

			$repository = new MembersRepository( $wpdb );
			$member     = $repository->find( $member_id );

		if ( null === $member ) {
				return array(
					'notice' => 'member-missing',
					'status' => false,
					'error'  => 'missing',
				);
		}

			$outcome = array(
				'notice'           => 'revoke-failed',
				'member_reference' => $member['member_reference'],
				'status'           => false,
				'error'            => 'revoke',
			);

			$tokens = new TokenService( new TokenRepository( $wpdb ) );

			if ( ! $tokens->revoke( $member_id ) ) {
					return $outcome;
			}

			$outcome['notice'] = 'revoked';
			$outcome['status'] = true;
			unset( $outcome['error'] );

			do_action( 'fbm_members_page_tokens_revoked', $member_id, $member );

			return $outcome;
	}
}
