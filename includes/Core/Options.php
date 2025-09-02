<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Core;

class Options {

        private const PREFIX = 'fbm_';

        /**
         * Default policy settings pending Settings UI.
         *
         * @todo PRD §5.5/§6: wire to Settings.
         */
        public const DEFAULT_POLICY_DAYS = 7;

        /**
         * Default attendance type options.
         *
         * @todo PRD §5.5/§6: wire to Settings.
         *
         * @var array<int,string>
         */
        public const DEFAULT_TYPES = array( 'in_person', 'delivery', 'other' );

        public static function get( string $key, $default = null ) {
                $defaults = array(
                        'policy_days'      => self::DEFAULT_POLICY_DAYS,
                        'attendance_types' => self::DEFAULT_TYPES,
                );
                if ( array_key_exists( $key, $defaults ) ) {
                        $default = $defaults[ $key ];
                }
                return get_option( self::PREFIX . $key, $default );
        }

        public static function update( string $key, $value ): bool {
                return update_option( self::PREFIX . $key, $value );
        }
}
