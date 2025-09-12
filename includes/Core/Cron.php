<?php
/**
 * WP-Cron helpers.
 *
 * @package FoodBankManager\Core
 */

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Diagnostics\RetentionRunner;

use function add_action;
use function apply_filters;
use function delete_transient;
use function get_transient;
use function set_transient;
use function time;
use function wp_next_scheduled;
use function wp_schedule_event;

/**
 * Cron utilities.
 */
final class Cron {
	public const RETENTION_HOOK = 'fbm_retention_hourly';
	private const LOCK_KEY      = 'fbm_retention_lock';

	/** Register cron handlers. */
	public static function init(): void {
		add_action( self::RETENTION_HOOK, array( self::class, 'run_retention' ) );
	}

	/** Ensure retention event is scheduled. */
	public static function maybe_schedule_retention(): void {
		if ( ! wp_next_scheduled( self::RETENTION_HOOK ) ) {
			$hour = defined( 'HOUR_IN_SECONDS' ) ? HOUR_IN_SECONDS : 3600;
			wp_schedule_event( time() + $hour, 'hourly', self::RETENTION_HOOK );
		}
	}

	/** Cron handler for retention policies. */
	public static function run_retention(): void {
		if ( get_transient( self::LOCK_KEY ) ) {
			return;
		}
		$ttl = ( defined( 'MINUTE_IN_SECONDS' ) ? MINUTE_IN_SECONDS : 60 ) * 5;
		set_transient( self::LOCK_KEY, 1, $ttl );
		try {
			$runner = apply_filters( 'fbm_retention_runner', new RetentionRunner() );
			$runner->run( false );
		} finally {
			delete_transient( self::LOCK_KEY );
		}
	}
}
