<?php
/**
 * Minimal WP-CLI stub for unit testing.
 */

declare(strict_types=1);

if ( ! defined( 'WP_CLI' ) ) {
        define( 'WP_CLI', true );
}

if ( class_exists( 'WP_CLI', false ) ) {
        return;
}

class WP_CLI {
        /**
         * Registered commands keyed by name.
         *
         * @var array<string, callable>
         */
        public static array $commands = array();

        /**
         * Logged output messages.
         *
         * @var array<int, string>
         */
        public static array $logs = array();

        /**
         * Successful command outputs.
         *
         * @var array<int, string>
         */
        public static array $successes = array();

        /**
         * Register a command.
         *
         * @param string   $name     Command signature.
         * @param callable $callable Command handler.
         */
        public static function add_command( string $name, $callable ): void {
                self::$commands[ $name ] = $callable;
        }

        /**
         * Record an informational log message.
         *
         * @param string $message Message to log.
         */
        public static function log( string $message ): void {
                self::$logs[] = $message;
        }

        /**
         * Record a success message.
         *
         * @param string $message Message to record.
         */
        public static function success( string $message ): void {
                self::$successes[] = $message;
        }

        /**
         * Simulate a WP-CLI error by throwing an exception.
         *
         * @param string $message Error message.
         */
        public static function error( string $message ): void {
                throw new \RuntimeException( $message );
        }
}
