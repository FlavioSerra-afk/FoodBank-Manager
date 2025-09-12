<?php
/**
 * WP-CLI parent command for FoodBank Manager.
 *
 * @package FoodBankManager\CLI
 */

declare(strict_types=1);

namespace FoodBankManager\CLI;

use FBM\CLI\IO;
use FBM\CLI\VersionCommand;
use FoodBankManager\Diagnostics\RetentionRunner;
use FBM\Core\Jobs\JobsRepo;
use FoodBankManager\Mail\Renderer;
use FBM\Security\ThrottleSettings;
use function absint;
use function add_filter;
use function apply_filters;
use function current_user_can;
use function delete_transient;
use function get_transient;
use function get_role;
use function get_sites;
use function is_email;
use function is_multisite;
use function is_super_admin;
use function sanitize_email;
use function set_transient;
use function switch_to_blog;
use function wp_privacy_anonymize_data;
use function remove_filter;
use function restore_current_blog;
use function update_option;

/**
 * Parent CLI command with subcommands.
 */
final class Commands {
	private IO $io;

	public function __construct( ?IO $io = null ) {
		$this->io = $io ?? new \FBM\CLI\WpCliIO();
	}
	/**
	 * Output plugin version.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function version( array $args, array $assoc_args ): void {
		( new VersionCommand( $this->io ) )->__invoke( $args, $assoc_args );
	}

	/**
	 * List queued jobs.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function jobs_list( array $args, array $assoc_args ): void {
		$limit = isset( $assoc_args['limit'] ) ? absint( $assoc_args['limit'] ) : 50;
		$limit = max( 1, $limit );
		$rows  = JobsRepo::list( array( 'limit' => $limit ) );
		if ( ! $rows ) {
			$this->io->line( 'No jobs found.' );
			return;
		}
		$this->io->line( "ID\tType\tStatus\tAttempts" );
		foreach ( $rows as $r ) {
			$this->io->line( sprintf( '%d\t%s\t%s\t%d', $r['id'], $r['type'], $r['status'], $r['attempts'] ) );
		}
	}

	/**
	 * Run retention policies.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
        public function jobs_retry( array $args, array $assoc_args ): void {
                if ( ! current_user_can( 'fbm_manage_jobs' ) && ! ( is_multisite() && is_super_admin() ) ) {
                        $this->io->error( 'Forbidden' );
                        return;
                }
                $id = absint( $args[0] ?? 0 );
                if ( ! $id ) {
                        $this->io->error( 'Invalid ID' );
                        return;
                }
		$job = JobsRepo::get( $id );
		if ( ! $job ) {
			$this->io->error( 'Job not found' );
			return;
		}
		JobsRepo::retry( $id );
		$this->io->success( 'Job retried' );
	}

	public function retention_run( array $args, array $assoc_args ): void {
		$dry = isset( $assoc_args['dry-run'] );
		if ( get_transient( 'fbm_retention_lock' ) ) {
			$this->io->error( 'Retention lock active' );
			return;
		}
		$ttl = ( defined( 'MINUTE_IN_SECONDS' ) ? MINUTE_IN_SECONDS : 60 ) * 5;
		set_transient( 'fbm_retention_lock', 1, $ttl );
		try {
			$runner = apply_filters( 'fbm_retention_runner', new RetentionRunner() );
			$res    = $runner->run( $dry );
			$this->io->line( sprintf( 'affected=%d anonymised=%d errors=%d', $res['affected'], $res['anonymised'], $res['errors'] ) );
			$this->io->success( 'Retention complete' );
		} finally {
			delete_transient( 'fbm_retention_lock' );
		}
	}

	/**
	 * Preview privacy data for an email address.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function privacy_preview( array $args, array $assoc_args ): void {
		$email = sanitize_email( $args[0] ?? '' );
		if ( ! $email || ! is_email( $email ) ) {
			$this->io->error( 'Invalid email' );
			return;
		}
		$exporters = apply_filters( 'wp_privacy_personal_data_exporters', array() );
		$cb        = $exporters['foodbank_manager']['callback'] ?? null;
		if ( ! is_callable( $cb ) ) {
			$this->io->error( 'Exporter missing' );
			return;
		}
		$filter = static fn() => 100;
		add_filter( 'fbm_privacy_exporter_page_size', $filter );
		$data   = call_user_func( $cb, $email, 1 );
		remove_filter( 'fbm_privacy_exporter_page_size', $filter );
		foreach ( $data['data'] as $item ) {
			$this->io->line( $item['group_label'] . ' #' . $item['item_id'] );
			foreach ( $item['data'] as $field ) {
				$val = (string) $field['value'];
				if ( ! isset( $assoc_args['unmasked'] ) ) {
					$val = wp_privacy_anonymize_data( 'text', $val );
				}
				$this->io->line( '  ' . $field['name'] . ': ' . $val );
			}
		}
	}

	/**
	 * Send a test mail.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
        public function mail_test( array $args, array $assoc_args ): void {
                $to = sanitize_email( $assoc_args['to'] ?? '' );
                if ( ! $to || ! is_email( $to ) ) {
                        $this->io->error( 'Invalid email' );
                        return;
                }
                $tpl = array(
                        'subject' => 'FoodBank Manager mail test',
                        'body'    => '<p>Test email from FoodBank Manager.</p>',
                );
                $sent = Renderer::send( $tpl, array(), array( $to ) );
                if ( $sent ) {
                        $this->io->success( 'Sent to ' . $to );
                } else {
                        $this->io->error( 'Send failed' );
                }
        }

        /**
         * Diagnose and repair capabilities.
         *
         * @param array $args       Positional arguments.
         * @param array $assoc_args Associative arguments.
         */
        public function caps_doctor( array $args, array $assoc_args ): void {
                $missing = array();
                if ( is_multisite() ) {
                        foreach ( get_sites( array( 'number' => 0 ) ) as $site ) {
                                switch_to_blog( (int) $site->blog_id );
                                $role = get_role( 'administrator' );
                                if ( ! $role || ! $role->has_cap( 'fbm_manage_jobs' ) ) {
                                        $missing[] = (int) $site->blog_id;
                                }
                        }
                        restore_current_blog();
                } else {
                        $role = get_role( 'administrator' );
                        if ( ! $role || ! $role->has_cap( 'fbm_manage_jobs' ) ) {
                                $missing[] = 1;
                        }
                }

                if ( $missing && isset( $assoc_args['fix'] ) ) {
                        if ( ! current_user_can( 'fbm_manage_jobs' ) && ! ( is_multisite() && is_super_admin() ) ) {
                                $this->io->error( 'Forbidden' );
                                return;
                        }
                        if ( is_multisite() ) {
                                foreach ( get_sites( array( 'number' => 0 ) ) as $site ) {
                                        switch_to_blog( (int) $site->blog_id );
                                        $role = get_role( 'administrator' );
                                        if ( $role && ! $role->has_cap( 'fbm_manage_jobs' ) ) {
                                                $role->add_cap( 'fbm_manage_jobs', true );
                                        }
                                }
                                restore_current_blog();
                        } else {
                                $role = get_role( 'administrator' );
                                if ( $role && ! $role->has_cap( 'fbm_manage_jobs' ) ) {
                                        $role->add_cap( 'fbm_manage_jobs', true );
                                }
                        }
                        $this->io->success( 'Capability granted' );
                        return;
                }

        if ( $missing ) {
            $this->io->error( 'fbm_manage_jobs missing for Administrator' );
            return;
        }

        $this->io->success( 'All capabilities present' );
    }

    /**
     * Show throttle settings.
     */
    public function throttle_show( array $args, array $assoc_args ): void {
        if ( ! current_user_can( 'fb_manage_diagnostics' ) && ! ( is_multisite() && is_super_admin() ) ) {
            $this->io->error( 'Forbidden' );
            return;
        }
        $settings = ThrottleSettings::get();
        $limits   = ThrottleSettings::limits();
        $this->io->line( sprintf( 'window=%d base=%d', $settings['window_seconds'], $settings['base_limit'] ) );
        foreach ( $limits as $role => $limit ) {
            $this->io->line( sprintf( '%s=%s', $role, $limit ? (string) $limit : 'unlimited' ) );
        }
    }

    /**
     * Update throttle settings.
     */
    public function throttle_set( array $args, array $assoc_args ): void {
        if ( ! current_user_can( 'fb_manage_diagnostics' ) && ! ( is_multisite() && is_super_admin() ) ) {
            $this->io->error( 'Forbidden' );
            return;
        }
        $current = ThrottleSettings::get();
        $raw     = $current;
        if ( isset( $assoc_args['window'] ) ) {
            $raw['window_seconds'] = (int) $assoc_args['window'];
        }
        if ( isset( $assoc_args['base'] ) ) {
            $raw['base_limit'] = (int) $assoc_args['base'];
        }
        if ( isset( $assoc_args['role'] ) ) {
            $roles = (array) $assoc_args['role'];
            foreach ( $roles as $pair ) {
                if ( strpos( (string) $pair, ':' ) === false ) {
                    continue;
                }
                list( $role, $mult ) = explode( ':', (string) $pair, 2 );
                $raw['role_multipliers'][ $role ] = $mult;
            }
        }
        $san = fbm_throttle_sanitize( $raw );
        update_option( 'fbm_throttle', $san, false ); // @phpstan-ignore-line
        if ( isset( $assoc_args['window'] ) && $san['window_seconds'] !== (int) $assoc_args['window'] ) {
            $this->io->line( 'window clamped to ' . $san['window_seconds'] );
        }
        if ( isset( $assoc_args['base'] ) && $san['base_limit'] !== (int) $assoc_args['base'] ) {
            $this->io->line( 'base clamped to ' . $san['base_limit'] );
        }
        if ( isset( $assoc_args['role'] ) ) {
            foreach ( (array) $assoc_args['role'] as $pair ) {
                if ( strpos( (string) $pair, ':' ) === false ) {
                    continue;
                }
                list( $role, $mult ) = explode( ':', (string) $pair, 2 );
                $m = (float) $mult;
                $final = $san['role_multipliers'][ $role ] ?? null;
                if ( null !== $final && (float) $final !== $m ) {
                    $this->io->line( $role . ' multiplier clamped to ' . $final );
                }
            }
        }
        $this->io->success( 'Throttle updated' );
    }
}
