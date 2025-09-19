<?php
/**
 * Uninstall cleanup.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);



if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		exit;
}

$allow_option      = function_exists( 'get_option' ) ? (bool) get_option( 'fbm_allow_destructive_uninstall', false ) : false;
$allow_constant    = defined( 'FBM_ALLOW_DESTRUCTIVE_UNINSTALL' ) ? (bool) FBM_ALLOW_DESTRUCTIVE_UNINSTALL : false;
$allow_destructive = $allow_option || $allow_constant;

if ( function_exists( 'delete_option' ) ) {
		delete_option( 'fbm_allow_destructive_uninstall' );
}

if ( function_exists( 'delete_site_option' ) ) {
		delete_site_option( 'fbm_allow_destructive_uninstall' );
}

if ( ! $allow_destructive ) {
		return;
}

$option_keys = array(
	'fbm_db_version',
	'fbm_settings',
	'fbm_theme',
	'fbm_db_migration_summary',
	'fbm_schedule_window',
	'fbm_schedule_window_overrides',
	'fbm_token_signing_key',
	'fbm_token_storage_key',
	'fbm_mail_failures',
	'fbm_members_action_audit',
	'fbm_allow_destructive_uninstall',
);

foreach ( $option_keys as $option_key ) {
	if ( function_exists( 'delete_option' ) ) {
			delete_option( $option_key );
	}

	if ( function_exists( 'delete_site_option' ) ) {
			delete_site_option( $option_key );
	}
}

global $wpdb;

if ( $wpdb instanceof wpdb ) {
		fbm_drop_tables( $wpdb );
		fbm_clear_transients( $wpdb );
		fbm_unschedule_events();
}

/**
 * Drop all FoodBank Manager managed tables.
 *
 * @param \wpdb $wpdb WordPress database abstraction.
 */
function fbm_drop_tables( \wpdb $wpdb ): void {
		$tables = array(
			$wpdb->prefix . 'fbm_attendance_overrides',
			$wpdb->prefix . 'fbm_attendance',
			$wpdb->prefix . 'fbm_tokens',
			$wpdb->prefix . 'fbm_members',
		);

        if ( defined( 'ABSPATH' ) ) {
                $upgrade_file = ABSPATH . 'wp-admin/includes/upgrade.php';
                if ( is_string( $upgrade_file ) && is_readable( $upgrade_file ) ) {
                                require_once $upgrade_file;
                }
        }

		foreach ( $tables as $table ) {
			if ( ! preg_match( '/^[A-Za-z0-9_]+$/', $table ) ) {
					continue;
			}

			if ( function_exists( 'maybe_drop_table' ) ) {
					maybe_drop_table( $table );
					continue;
			}

// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Table name validated above.
			$wpdb->query( 'DROP TABLE IF EXISTS `' . $table . '`' );
		}
}

/**
 * Remove FoodBank Manager transients from the options store.
 *
 * @param \wpdb $wpdb WordPress database abstraction.
 */
function fbm_clear_transients( \wpdb $wpdb ): void {
		$locations = array(
			array(
				'table'  => isset( $wpdb->options ) ? $wpdb->options : $wpdb->prefix . 'options',
				'column' => 'option_name',
				'delete' => 'delete_option',
			),
		);

		if ( isset( $wpdb->sitemeta ) && is_string( $wpdb->sitemeta ) && '' !== $wpdb->sitemeta ) {
				$locations[] = array(
					'table'  => $wpdb->sitemeta,
					'column' => 'meta_key',
					'delete' => null,
				);
		}

		$patterns = array(
			'_transient_fbm%',
			'_transient_timeout_fbm%',
			'_site_transient_fbm%',
			'_site_transient_timeout_fbm%',
		);

		foreach ( $locations as $location ) {
			$table  = isset( $location['table'] ) ? preg_replace( '/[^A-Za-z0-9_]/', '', (string) $location['table'] ) : '';
			$column = isset( $location['column'] ) ? preg_replace( '/[^A-Za-z0-9_]/', '', (string) $location['column'] ) : '';

			if ( '' === $table || '' === $column ) {
				continue;
			}

			foreach ( $patterns as $pattern ) {
				$prepared = $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Identifiers sanitized above.
					sprintf(
						'SELECT `%s` FROM `%s` WHERE `%s` LIKE %%s',
						esc_sql( $column ),
						esc_sql( $table ),
						esc_sql( $column )
					),
					$pattern
				);

				if ( ! is_string( $prepared ) ) {
					continue;
				}

				$option_names = $wpdb->get_col( $prepared ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared

				if ( ! is_array( $option_names ) ) {
					continue;
				}

				foreach ( $option_names as $option_name ) {
					if ( ! is_string( $option_name ) || '' === $option_name ) {
						continue;
					}

					if ( ! empty( $location['delete'] ) && function_exists( $location['delete'] ) ) {
						call_user_func( $location['delete'], $option_name );
					}

					if ( str_starts_with( $option_name, '_site_transient_' ) ) {
						$transient = substr( $option_name, strlen( '_site_transient_' ) );

						if ( function_exists( 'delete_site_transient' ) ) {
							delete_site_transient( $transient );
						}

						if ( isset( $GLOBALS['fbm_transients'] ) && is_array( $GLOBALS['fbm_transients'] ) ) {
							foreach ( array_keys( $GLOBALS['fbm_transients'] ) as $transient_name ) {
								if ( ! is_string( $transient_name ) || ! str_starts_with( $transient_name, 'fbm_' ) ) {
											continue;
								}

								if ( function_exists( 'delete_transient' ) ) {
									delete_transient( $transient_name );
								} else {
												unset( $GLOBALS['fbm_transients'][ $transient_name ] );
								}
							}
						}
					}

					if ( str_starts_with( $option_name, '_transient_' ) ) {
						$transient = substr( $option_name, strlen( '_transient_' ) );

						if ( function_exists( 'delete_transient' ) ) {
							delete_transient( $transient );
						}
					}
				}
			}
		}
}

/**
 * Unschedule cron events registered by FoodBank Manager.
 */
function fbm_unschedule_events(): void {
	if ( ! function_exists( '_get_cron_array' ) ) {
			return;
	}

		$cron = _get_cron_array();

	if ( ! is_array( $cron ) ) {
			return;
	}

	foreach ( $cron as $timestamp => $hooks ) {
			unset( $timestamp );

		if ( ! is_array( $hooks ) ) {
				continue;
		}

		foreach ( array_keys( $hooks ) as $hook ) {
			if ( is_string( $hook ) && str_starts_with( $hook, 'fbm_' ) ) {
				if ( function_exists( 'wp_unschedule_hook' ) ) {
					wp_unschedule_hook( $hook );
				}
			}
		}
	}
}
