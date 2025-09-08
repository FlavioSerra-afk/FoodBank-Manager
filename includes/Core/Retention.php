<?php
/**
 * Data retention and anonymisation routines.
 *
 * @package FBM\Core
 */

declare(strict_types=1);

namespace FBM\Core;

use FBM\Core\RetentionConfig;
use FoodBankManager\Core\Options;
use FoodBankManager\Logging\Audit;
use wpdb;

use function array_fill;
use function get_current_user_id;
use function update_option;
use function wp_next_scheduled;
use function wp_schedule_event;

/**
 * Retention handler.
 */
final class Retention {
	public const EVENT             = 'fbm_retention_tick';
	private const BATCH            = 200;
	private const OPT_LAST_RUN     = 'fbm_retention_tick_last_run';
	private const OPT_LAST_SUMMARY = 'fbm_retention_tick_last_summary';

		/**
		 * List cron hooks used by the retention service.
		 *
		 * @return array<int,string> Hooks.
		 */
	public static function events(): array {
			return array( self::EVENT );
	}

		/**
		 * Register cron hook.
		 *
		 * @return void
		 */
	public static function init(): void {
			add_action( self::EVENT, array( self::class, 'tick' ) );
	}

		/**
		 * Cron tick handler.
		 *
		 * @return void
		 */
	public static function tick(): void {
					self::run( false );
	}

		/**
		 * Run retention policies immediately.
		 *
		 * @return array<string,array<string,int>>
		 */
	public static function run_now(): array {
			return self::run( false );
	}

		/**
		 * Simulate retention policies without writing.
		 *
		 * @return array<string,array<string,int>>
		 */
	public static function dry_run(): array {
			return self::run( true );
	}

		/**
		 * Schedule cron event if missing.
		 *
		 * @return void
		 */
	public static function schedule(): void {
		if ( ! wp_next_scheduled( self::EVENT ) ) {
				wp_schedule_event( time() + 300, 'daily', self::EVENT );
		}
	}

	/**
	 * Execute retention policies.
	 *
	 * @param bool $dry_run Whether to simulate.
	 * @return array<string,array<string,int>> Summary counts.
	 */
	public static function run( bool $dry_run = false ): array {
		global $wpdb;
		$summary = array(
			'applications' => array(
				'deleted'    => 0,
				'anonymised' => 0,
			),
			'attendance'   => array(
				'deleted'    => 0,
				'anonymised' => 0,
			),
			'mail'         => array(
				'deleted'    => 0,
				'anonymised' => 0,
			),
		);

								$raw = Options::get( 'privacy.retention' );
								$cfg = RetentionConfig::normalize( $raw );

				$day = defined( 'DAY_IN_SECONDS' ) ? DAY_IN_SECONDS : 86400;

								$categories = array( 'applications', 'attendance', 'mail' );

		foreach ( $categories as $key ) {
						$days   = $cfg[ $key ]['days'];
						$policy = $cfg[ $key ]['policy'];
			if ( $days <= 0 ) {
										continue;
			}
						$cutoff = gmdate( 'Y-m-d H:i:s', time() - $days * $day );

			switch ( $key ) {
				case 'applications':
						$ids  = $wpdb->get_col(
							$wpdb->prepare(
								'SELECT id FROM ' . $wpdb->prefix . 'fb_applications WHERE created_at <= %s LIMIT %d',
								$cutoff,
								self::BATCH
							)
						);
						$repo = '\\FoodBankManager\\Database\\ApplicationsRepo';
					break;
				case 'attendance':
					$ids      = $wpdb->get_col(
						$wpdb->prepare(
							'SELECT id FROM ' . $wpdb->prefix . 'fb_attendance WHERE attendance_at <= %s LIMIT %d',
							$cutoff,
							self::BATCH
						)
					);
						$repo = '\\FoodBankManager\\Attendance\\AttendanceRepo';
					break;
				case 'mail':
								$ids  = $wpdb->get_col(
									$wpdb->prepare(
										'SELECT id FROM ' . $wpdb->prefix . 'fb_mail_log WHERE timestamp <= %s LIMIT %d',
										$cutoff,
										self::BATCH
									)
								);
								$repo = '\\FBM\\Mail\\LogRepo';
					break;
				default:
					$ids  = array();
					$repo = '';
			}

			if ( empty( $ids ) ) {
					continue;
			}
			if ( 'delete' === $policy ) {
				if ( ! $dry_run ) {
					switch ( $key ) {
						case 'applications':
							$wpdb->query(
								$wpdb->prepare(
									'DELETE FROM ' . $wpdb->prefix . 'fb_applications WHERE id IN ('
									. implode( ',', array_fill( 0, count( $ids ), '%d' ) ) . ')',
									$ids
								)
							);
							break;
						case 'attendance':
							$wpdb->query(
								$wpdb->prepare(
									'DELETE FROM ' . $wpdb->prefix . 'fb_attendance WHERE id IN ('
									. implode( ',', array_fill( 0, count( $ids ), '%d' ) ) . ')',
									$ids
								)
							);
							break;
						case 'mail':
									$wpdb->query(
										$wpdb->prepare(
											'DELETE FROM ' . $wpdb->prefix . 'fb_mail_log WHERE id IN ('
													. implode( ',', array_fill( 0, count( $ids ), '%d' ) ) . ')',
											$ids
										)
									);
							break;
					}
				}
				$summary[ $key ]['deleted'] += count( $ids );
			} else {
				if ( ! $dry_run && method_exists( $repo, 'anonymise_batch' ) ) {
								$repo::anonymise_batch( $ids );
				}
				$summary[ $key ]['anonymised'] += count( $ids );
			}
		}

		if ( ! $dry_run ) {
						update_option( self::OPT_LAST_SUMMARY, $summary );
						update_option( self::OPT_LAST_RUN, time() );
						$user_id = (int) get_current_user_id();
						Audit::log( 'retention_run', 'system', 0, $user_id, $summary );
		}

		return $summary;
	}
}
