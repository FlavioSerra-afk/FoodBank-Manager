<?php
/**
 * WP-CLI IO implementation.
 *
 * @package FBM\CLI
 */

declare(strict_types=1);

namespace FBM\CLI;

use WP_CLI;

final class WpCliIO implements IO {
	public function line( string $message ): void {
		if ( class_exists( WP_CLI::class ) ) {
			WP_CLI::line( $message );
		}
	}

	public function success( string $message ): void {
		if ( class_exists( WP_CLI::class ) ) {
			WP_CLI::success( $message );
		}
	}

	public function error( string $message ): void {
		if ( class_exists( WP_CLI::class ) ) {
			WP_CLI::error( $message );
		}
	}
}
