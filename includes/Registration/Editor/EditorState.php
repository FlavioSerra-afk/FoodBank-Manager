<?php // phpcs:disable WordPress.Files.FileName.InvalidClassFileName,WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Registration editor autosave and revision state management.
 *
 * @package FoodBankManager
 */

declare( strict_types=1 );

namespace FoodBankManager\Registration\Editor;

use WP_User;
use function array_slice;
use function delete_transient;
use function get_current_user_id;
use function get_option;
use function get_transient;
use function is_array;
use function is_numeric;
use function sanitize_text_field;
use function set_transient;
use function time;
use function update_option;
use function wp_get_current_user;

/**
 * Persists autosave payloads and revision history for the registration editor.
 */
final class EditorState {
	private const REVISION_OPTION = 'fbm_registration_editor_revisions';
	private const AUTOSAVE_PREFIX = 'fbm_registration_editor_autosave_';
	private const MAX_REVISIONS   = 5;
	private const AUTOSAVE_TTL    = 3600;

	/**
	 * Record a revision entry and ensure history bounds are enforced.
	 *
	 * @param int                 $user_id   Authoring user ID.
	 * @param string              $user_name Display name for the author.
	 * @param string              $template  Sanitized template markup.
	 * @param array<string,mixed> $settings  Sanitized settings payload.
	 *
	 * @return array<string,mixed> Stored revision entry.
	 */
	public static function record_revision( int $user_id, string $user_name, string $template, array $settings ): array {
		$revisions = get_option( self::REVISION_OPTION, array() );
		if ( ! is_array( $revisions ) ) {
			$revisions = array();
		}

		$entry = array(
			'id'        => uniqid( 'fbm_rev_', true ),
			'timestamp' => time(),
			'user_id'   => $user_id,
			'user_name' => $user_name,
			'template'  => $template,
			'settings'  => $settings,
		);

		array_unshift( $revisions, $entry );
		$revisions = array_slice( $revisions, 0, self::MAX_REVISIONS );

		update_option( self::REVISION_OPTION, $revisions, false );

		return $entry;
	}

	/**
	 * Retrieve stored revisions ordered by most recent first.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function list_revisions(): array {
		$revisions = get_option( self::REVISION_OPTION, array() );

		return is_array( $revisions ) ? $revisions : array();
	}

	/**
	 * Locate a revision entry by identifier.
	 *
	 * @param string $revision_id Revision identifier.
	 */
	public static function find_revision( string $revision_id ): ?array {
		foreach ( self::list_revisions() as $revision ) {
			if ( ! is_array( $revision ) ) {
				continue;
			}

			if ( isset( $revision['id'] ) && $revision_id === (string) $revision['id'] ) {
				return $revision;
			}
		}

		return null;
	}

	/**
	 * Persist an autosave payload for the current user.
	 *
	 * @param int                 $user_id Current user ID.
	 * @param array<string,mixed> $payload Autosave payload.
	 */
	public static function set_autosave( int $user_id, array $payload ): void {
		if ( $user_id <= 0 ) {
			return;
		}

		$timestamp = isset( $payload['timestamp'] ) && is_numeric( $payload['timestamp'] )
			? (int) $payload['timestamp']
			: time();

		set_transient(
			self::autosave_key( $user_id ),
			array(
				'template'  => isset( $payload['template'] ) ? (string) $payload['template'] : '',
				'settings'  => isset( $payload['settings'] ) && is_array( $payload['settings'] ) ? $payload['settings'] : array(),
				'timestamp' => $timestamp,
			),
			self::AUTOSAVE_TTL
		);
	}

	/**
	 * Retrieve the current user's autosave payload if available.
	 *
	 * @param int $user_id Current user ID.
	 *
	 * @return array<string,mixed>|null
	 */
	public static function get_autosave( int $user_id ): ?array {
		if ( $user_id <= 0 ) {
			return null;
		}

		$payload = get_transient( self::autosave_key( $user_id ) );

		return is_array( $payload ) ? $payload : null;
	}

	/**
	 * Remove the autosave payload for the current user.
	 *
	 * @param int $user_id Current user ID.
	 */
	public static function clear_autosave( int $user_id ): void {
		if ( $user_id <= 0 ) {
			return;
		}

		delete_transient( self::autosave_key( $user_id ) );
	}

	/**
	 * Resolve the autosave key for a user.
	 *
	 * @param int $user_id Current user ID.
	 */
	private static function autosave_key( int $user_id ): string {
		return self::AUTOSAVE_PREFIX . $user_id;
	}

	/**
	 * Capture the current user context for revision author attribution.
	 *
	 * @return array{user_id:int,user_name:string}
	 */
	public static function current_user_context(): array {
		$user    = wp_get_current_user();
		$user_id = (int) get_current_user_id();
		$name    = '';

		if ( $user instanceof WP_User ) {
			$name = '' !== $user->display_name ? $user->display_name : $user->user_login;
		}

		return array(
			'user_id'   => $user_id,
			'user_name' => sanitize_text_field( $name ),
		);
	}
}

// phpcs:enable WordPress.Files.FileName.InvalidClassFileName,WordPress.Files.FileName.NotHyphenatedLowercase
