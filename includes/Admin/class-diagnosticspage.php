<?php
/**
 * Diagnostics admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Diagnostics\MailFailureLog;
use FoodBankManager\Email\WelcomeMailer;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use RuntimeException;
use wpdb;
use function __;
use function add_action;
use function add_menu_page;
use function add_query_arg;
use function admin_url;
use function check_admin_referer;
use function current_user_can;
use function esc_html__;
use function filter_input;
use function gmdate;
use function is_array;
use function is_readable;
use function sanitize_key;
use function sanitize_text_field;
use function sprintf;
use function wp_die;
use function wp_nonce_url;
use function wp_safe_redirect;
use function wp_unslash;
use const FILTER_UNSAFE_RAW;
use const INPUT_GET;

/**
 * Presents diagnostic information including mail failures.
 */
final class DiagnosticsPage {
	private const MENU_SLUG     = 'fbm-diagnostics';
	private const TEMPLATE      = 'templates/admin/diagnostics-page.php';
	private const ACTION_PARAM  = 'fbm_diag_action';
	private const ENTRY_PARAM   = 'fbm_diag_entry';
	private const STATUS_PARAM  = 'fbm_diag_status';
	private const CODE_PARAM    = 'fbm_diag_code';
	private const MEMBER_PARAM  = 'fbm_diag_member';
	private const ACTION_RESEND = 'resend';

		/**
		 * Register WordPress hooks.
		 */
	public static function register(): void {
			add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
			add_action( 'admin_init', array( __CLASS__, 'handle_actions' ) );
	}

		/**
		 * Register the diagnostics admin menu entry.
		 */
	public static function register_menu(): void {
			add_menu_page(
				__( 'Food Bank Diagnostics', 'foodbank-manager' ),
				__( 'Food Bank Diagnostics', 'foodbank-manager' ),
				'fbm_diagnostics', // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered during activation.
				self::MENU_SLUG,
				array( __CLASS__, 'render' ),
				'dashicons-shield-alt'
			);
	}

		/**
		 * Render the diagnostics dashboard.
		 */
	public static function render(): void {
		if ( ! current_user_can( 'fbm_diagnostics' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
		}

			$log     = new MailFailureLog();
			$raw     = $log->entries();
			$entries = array();
			$notices = self::collect_notices();
			$rate    = MailFailureLog::rate_limit_interval();

		foreach ( $raw as $entry ) {
			if ( ! is_array( $entry ) ) {
					continue;
			}

				$entry_id    = isset( $entry['id'] ) ? (string) $entry['id'] : '';
				$recorded_at = isset( $entry['recorded_at'] ) ? (int) $entry['recorded_at'] : 0;
				$blocked     = $log->next_attempt_at( $entry );

				$entries[] = array(
					'id'               => $entry_id,
					'member_reference' => isset( $entry['member_reference'] ) ? (string) $entry['member_reference'] : '',
					'email'            => isset( $entry['email'] ) ? (string) $entry['email'] : '',
					'context'          => self::context_label( isset( $entry['context'] ) ? (string) $entry['context'] : '' ),
					'error'            => self::error_label( isset( $entry['error'] ) ? (string) $entry['error'] : '' ),
					'recorded_at'      => $recorded_at > 0 ? gmdate( 'Y-m-d H:i:s \U\T\C', $recorded_at ) : '',
					'attempts'         => isset( $entry['attempts'] ) ? (int) $entry['attempts'] : 0,
					'can_resend'       => '' !== $entry_id && $log->can_attempt( $entry ),
					'resend_url'       => '' !== $entry_id ? self::build_action_url( $entry_id ) : '',
					'blocked_until'    => is_int( $blocked ) && $blocked > 0 ? gmdate( 'Y-m-d H:i:s \U\T\C', $blocked ) : null,
				);
		}

			$template = FBM_PATH . self::TEMPLATE;

		if ( ! is_readable( $template ) ) {
				wp_die( esc_html__( 'Diagnostics template is missing.', 'foodbank-manager' ) );
		}

			$data = array(
				'entries'            => $entries,
				'notices'            => $notices,
				'rate_limit_seconds' => $rate,
				'page_slug'          => self::MENU_SLUG,
			);

			include $template;
	}

		/**
		 * Handle resend attempts triggered from the diagnostics view.
		 */
	public static function handle_actions(): void {
		if ( ! current_user_can( 'fbm_diagnostics' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
				return;
		}

			$page_param = filter_input( INPUT_GET, 'page', FILTER_UNSAFE_RAW );
			$page       = is_string( $page_param ) ? sanitize_key( wp_unslash( $page_param ) ) : '';

		if ( self::MENU_SLUG !== $page ) {
				return;
		}

			$action_param = filter_input( INPUT_GET, self::ACTION_PARAM, FILTER_UNSAFE_RAW );
			$action       = is_string( $action_param ) ? sanitize_key( wp_unslash( $action_param ) ) : '';

		if ( self::ACTION_RESEND !== $action ) {
				return;
		}

			$entry_param = filter_input( INPUT_GET, self::ENTRY_PARAM, FILTER_UNSAFE_RAW );
			$entry_id    = is_string( $entry_param ) ? sanitize_text_field( wp_unslash( $entry_param ) ) : '';

		if ( '' === $entry_id ) {
				self::redirect_with_outcome(
					array(
						'status' => false,
						'code'   => 'entry-missing',
					)
				);

				return;
		}

			check_admin_referer( self::resend_nonce_action( $entry_id ) );

			$outcome = self::process_resend( $entry_id );

			self::redirect_with_outcome( $outcome );
	}

		/**
		 * Collect queued notice messages from query parameters.
		 *
		 * @return array<int, array{type:string,message:string}>
		 */
	private static function collect_notices(): array {
			$notices = array();

			$status_param = filter_input( INPUT_GET, self::STATUS_PARAM, FILTER_UNSAFE_RAW );
			$code_param   = filter_input( INPUT_GET, self::CODE_PARAM, FILTER_UNSAFE_RAW );
			$member_param = filter_input( INPUT_GET, self::MEMBER_PARAM, FILTER_UNSAFE_RAW );

			$status = is_string( $status_param ) ? sanitize_key( wp_unslash( $status_param ) ) : '';
			$code   = is_string( $code_param ) ? sanitize_key( wp_unslash( $code_param ) ) : '';
			$member = is_string( $member_param ) ? sanitize_text_field( wp_unslash( $member_param ) ) : '';

		if ( '' === $status || '' === $code ) {
				return $notices;
		}

		switch ( $code ) {
			case 'resent':
					$message = '' !== $member
							? sprintf(
									/* translators: %s: Member reference. */
								__( 'Resent the welcome email for %s.', 'foodbank-manager' ),
								$member
							)
							: __( 'Resent the welcome email.', 'foodbank-manager' );
					$notices[] = array(
						'type'    => 'success',
						'message' => $message,
					);
				break;
			case 'rate-limited':
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'Resend is temporarily rate-limited. Please try again later.', 'foodbank-manager' ),
					);
				break;
			case 'member-missing':
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'The associated member record could not be found.', 'foodbank-manager' ),
					);
				break;
			case 'database':
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'Database connection unavailable. Please retry later.', 'foodbank-manager' ),
					);
				break;
			case 'token':
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'Unable to issue a new token for the member.', 'foodbank-manager' ),
					);
				break;
			case 'mail':
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'The mail provider rejected the resend attempt.', 'foodbank-manager' ),
					);
				break;
			case 'entry-missing':
			default:
					$notices[] = array(
						'type'    => 'error',
						'message' => __( 'The selected mail failure entry could not be processed.', 'foodbank-manager' ),
					);
				break;
		}

			return $notices;
	}

		/**
		 * Process the resend action for a specific failure entry.
		 *
		 * @param string $entry_id Failure entry identifier.
		 *
		 * @return array{status:bool,code:string,member_reference?:string}
		 */
	private static function process_resend( string $entry_id ): array {
			$log   = new MailFailureLog();
			$entry = $log->find( $entry_id );

		if ( null === $entry ) {
				return array(
					'status' => false,
					'code'   => 'entry-missing',
				);
		}

		if ( ! $log->can_attempt( $entry ) ) {
				return array(
					'status' => false,
					'code'   => 'rate-limited',
				);
		}

			global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
				return array(
					'status' => false,
					'code'   => 'database',
				);
		}

			$repository = new MembersRepository( $wpdb );
			$member     = $repository->find( isset( $entry['member_id'] ) ? (int) $entry['member_id'] : 0 );

		if ( null === $member ) {
				$log->resolve( $entry_id );

				return array(
					'status' => false,
					'code'   => 'member-missing',
				);
		}

			$tokens = new TokenService( new TokenRepository( $wpdb ) );

		try {
				$token = $tokens->issue( (int) $member['id'] );
		} catch ( RuntimeException $exception ) {
				unset( $exception );

				return array(
					'status' => false,
					'code'   => 'token',
				);
		}

			$log->note_attempt( $entry_id );

			$mailer = new WelcomeMailer();

		if ( ! $mailer->send( $member['email'], $member['first_name'], $member['member_reference'], $token ) ) {
				$log->note_failure( $entry_id, MailFailureLog::ERROR_MAIL, MailFailureLog::CONTEXT_DIAGNOSTICS_RESEND );

				return array(
					'status' => false,
					'code'   => 'mail',
				);
		}

			$log->resolve( $entry_id );

			return array(
				'status'           => true,
				'code'             => 'resent',
				'member_reference' => $member['member_reference'],
			);
	}

		/**
		 * Redirect back to the diagnostics page with an encoded outcome.
		 *
		 * @param array{status:bool,code:string,member_reference?:string} $outcome Outcome descriptor.
		 */
	private static function redirect_with_outcome( array $outcome ): void {
			$args = array(
				'page'             => self::MENU_SLUG,
				self::STATUS_PARAM => $outcome['status'] ? 'success' : 'error',
				self::CODE_PARAM   => $outcome['code'],
			);

			if ( isset( $outcome['member_reference'] ) && '' !== $outcome['member_reference'] ) {
					$args[ self::MEMBER_PARAM ] = $outcome['member_reference'];
			}

			$location = add_query_arg( $args, admin_url( 'admin.php' ) );

			wp_safe_redirect( $location );
			exit;
	}

		/**
		 * Build a nonce-protected resend URL.
		 *
		 * @param string $entry_id Failure entry identifier.
		 */
	private static function build_action_url( string $entry_id ): string {
			$url = add_query_arg(
				array(
					'page'             => self::MENU_SLUG,
					self::ACTION_PARAM => self::ACTION_RESEND,
					self::ENTRY_PARAM  => $entry_id,
				),
				admin_url( 'admin.php' )
			);

			return wp_nonce_url( $url, self::resend_nonce_action( $entry_id ) );
	}

		/**
		 * Compute the nonce action string for a resend request.
		 *
		 * @param string $entry_id Failure entry identifier.
		 */
	private static function resend_nonce_action( string $entry_id ): string {
			return 'fbm_diag_resend_' . $entry_id;
	}

		/**
		 * Map context identifiers to localized labels.
		 *
		 * @param string $context Context key.
		 */
	private static function context_label( string $context ): string {
		switch ( $context ) {
			case MailFailureLog::CONTEXT_ADMIN_RESEND:
				return __( 'Admin resend', 'foodbank-manager' );
			case MailFailureLog::CONTEXT_DIAGNOSTICS_RESEND:
				return __( 'Diagnostics resend', 'foodbank-manager' );
			case MailFailureLog::CONTEXT_REGISTRATION:
				return __( 'Registration', 'foodbank-manager' );
			default:
				return __( 'Email delivery', 'foodbank-manager' );
		}
	}

		/**
		 * Map error identifiers to localized labels.
		 *
		 * @param string $error Error key.
		 */
	private static function error_label( string $error ): string {
		switch ( $error ) {
			case MailFailureLog::ERROR_TOKEN:
				return __( 'Token issuance failure', 'foodbank-manager' );
			case MailFailureLog::ERROR_MEMBER:
				return __( 'Missing member record', 'foodbank-manager' );
			case MailFailureLog::ERROR_MAIL:
				return __( 'Mail transport failure', 'foodbank-manager' );
			default:
				return __( 'Unknown error', 'foodbank-manager' );
		}
	}
}
