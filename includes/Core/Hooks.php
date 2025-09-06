<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Shortcodes\Entries;
use FoodBankManager\Shortcodes\AttendanceManager;
use FoodBankManager\Shortcodes\Dashboard;
use FoodBankManager\Rest\Api;
use FoodBankManager\Mail\Logger;
use FoodBankManager\Admin\Notices;
use FoodBankManager\Core\Install;

class Hooks {

        public function register(): void {
                add_action( 'init', array( $this, 'register_shortcodes' ) );
                add_action( 'rest_api_init', array( Api::class, 'register_routes' ) );
                Logger::init();
                add_action( 'fbm_crypto_missing_kek', array( Notices::class, 'missing_kek' ) );
                add_action( 'fbm_crypto_missing_sodium', array( Notices::class, 'missing_sodium' ) );
                if ( is_admin() ) {
                        add_action( 'admin_init', array( Install::class, 'detect_duplicates' ) );
                        add_action( 'admin_init', array( Notices::class, 'handleCapsRepair' ) );
                        add_action( 'admin_post_fbm_consolidate_plugins', array( Notices::class, 'handle_consolidate_plugins' ) );
                        add_action( 'admin_notices', array( Notices::class, 'render' ), 10 );
                        add_action( 'admin_notices', array( Notices::class, 'render_caps_fix_notice' ), 5 );
                }
        }

       public function register_shortcodes(): void {
               \FBM\Shortcodes\Shortcodes::register();
               add_shortcode( 'foodbank_entries', array( Entries::class, 'render' ) );
               add_shortcode( 'fb_attendance_manager', array( AttendanceManager::class, 'render' ) );
               add_shortcode( 'fbm_dashboard', array( Dashboard::class, 'render' ) );
       }
}
