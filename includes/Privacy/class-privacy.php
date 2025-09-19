<?php
/**
 * WordPress privacy tooling integration.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Privacy;

use function add_action;
use function add_filter;
use function esc_html;
use function function_exists;
use function wp_add_privacy_policy_content;
use function __;

/**
 * Registers privacy exporters, erasers, and policy text.
 */
final class Privacy {
		/**
		 * Boot the privacy integration hooks.
		 */
	public static function register(): void {
			add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'register_exporter' ) );
			add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'register_eraser' ) );
			add_action( 'admin_init', array( __CLASS__, 'register_policy_content' ) );
	}

		/**
		 * Attach the FoodBank Manager privacy exporter.
		 *
		 * @param array<string,array<string,mixed>> $exporters Registered exporters keyed by identifier.
		 *
		 * @return array<string,array<string,mixed>>
		 */
	public static function register_exporter( array $exporters ): array {
			$exporters[ Exporter::ID ] = array(
				'exporter_friendly_name' => __( 'FoodBank Manager', 'foodbank-manager' ),
				'callback'               => array( Exporter::class, 'export' ),
			);

			return $exporters;
	}

		/**
		 * Attach the FoodBank Manager privacy eraser.
		 *
		 * @param array<string,array<string,mixed>> $erasers Registered erasers keyed by identifier.
		 *
		 * @return array<string,array<string,mixed>>
		 */
	public static function register_eraser( array $erasers ): array {
			$erasers[ Eraser::ID ] = array(
				'eraser_friendly_name' => __( 'FoodBank Manager', 'foodbank-manager' ),
				'callback'             => array( Eraser::class, 'erase' ),
			);

			return $erasers;
	}

		/**
		 * Surface FoodBank Manager privacy policy content in WordPress core.
		 */
	public static function register_policy_content(): void {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
				return;
		}

			$paragraphs = array(
				__( 'FoodBank Manager stores FoodBank member contact details (first name, last initial, and email), household size, status history, and timestamps related to registration and approvals.', 'foodbank-manager' ),
				__( 'Attendance records capture the member reference, visit dates, check-in method, and any staff-entered notes. Administrators can export or erase this information using WordPress privacy tools.', 'foodbank-manager' ),
				__( 'QR codes issued to members are opaque tokens without embedded personal data. Operational data is retained until a manager revokes the member or requests deletion through the privacy eraser.', 'foodbank-manager' ),
			);

			$content = '';

			foreach ( $paragraphs as $paragraph ) {
					$content .= '<p>' . esc_html( $paragraph ) . '</p>';
			}

			wp_add_privacy_policy_content( __( 'FoodBank Manager', 'foodbank-manager' ), $content );
	}
}
