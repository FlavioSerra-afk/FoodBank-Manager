<?php
/**
 * WP-CLI parent command for FoodBank Manager.
 *
 * @package FoodBankManager\CLI
 */

declare(strict_types=1);

namespace FoodBankManager\CLI;

use FBM\CLI\VersionCommand;

/**
 * Parent CLI command with subcommands.
 */
final class Commands {
    /**
     * Output plugin version.
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     */
    public function version(array $args, array $assoc_args): void {
        ( new VersionCommand() )->__invoke($args, $assoc_args);
    }

    /**
     * List queued jobs.
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     */
    public function jobs_list(array $args, array $assoc_args): void {
        \WP_CLI::success('OK');
    }

    /**
     * Run retention policies.
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     */
    public function retention_run(array $args, array $assoc_args): void {
        \WP_CLI::success('OK');
    }

    /**
     * Preview privacy data for an email address.
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     */
    public function privacy_preview(array $args, array $assoc_args): void {
        \WP_CLI::success('OK');
    }

    /**
     * Send a test mail.
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     */
    public function mail_test(array $args, array $assoc_args): void {
        \WP_CLI::success('OK');
    }
}
