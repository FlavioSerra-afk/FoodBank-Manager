<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Db\Migrations;
use FoodBankManager\Auth\Roles;

final class Plugin {

    public const FBM_VERSION = '0.1.1';


	private static ?Plugin $instance = null;

	public static function get_instance(): self {
		return self::$instance ??= new self();
	}

        public function init(): void {
                ( new Hooks() )->register();
                ( new Assets() )->register();
        }

        public function boot(): void {
                add_action(
                        'init',
                        static function (): void {
                                load_plugin_textdomain( 'foodbank-manager', false, dirname( plugin_basename( \FBM_FILE ) ) . '/languages' );
                        }
                );

                if ( is_admin() ) {
                        \FoodBankManager\Admin\Menu::register();
                }

                add_action(
                        'admin_init',
                        static function (): void {
                                if ( function_exists( '\\FoodBankManager\\Auth\\Roles::grantCapsToAdmin' ) ) {
                                        \FoodBankManager\Auth\Roles::grantCapsToAdmin();
                                }
                                if ( ! defined( 'FBM_KEK_BASE64' ) || empty( constant( 'FBM_KEK_BASE64' ) ) ) {
                                        add_action(
                                                'admin_notices',
                                                static function (): void {
                                                        echo '<div class="notice notice-warning"><p>' . esc_html__( 'FoodBank Manager: Encryption key (FBM_KEK_BASE64) not set. Some features are degraded.', 'foodbank-manager' ) . '</p></div>';
                                                }
                                        );
                                }
                        }
                );

                $this->init();
        }

        public function activate(): void {
                ( new Migrations() )->maybe_migrate();
                Roles::activate();
        }

	public function deactivate(): void {
		// Placeholder for future deactivation routines.
	}
}
