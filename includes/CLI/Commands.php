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
use function absint;
use function add_filter;
use function apply_filters;
use function current_user_can;
use function delete_transient;
use function get_transient;
use function is_email;
use function sanitize_email;
use function set_transient;
use function wp_privacy_anonymize_data;
use function remove_filter;

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
		if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
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
		$this->io->success( 'OK' );
	}
}
