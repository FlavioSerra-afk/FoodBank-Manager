<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Db\Migrations;
use FoodBankManager\Auth\Roles;
use FoodBankManager\Http\FormSubmitController;

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

                \FoodBankManager\Auth\CapabilitiesResolver::boot();

                if ( is_admin() ) {
                        \FoodBankManager\Admin\Menu::register();
                        add_action('load-toplevel_page_fbm-dashboard', [\FoodBankManager\Admin\DatabasePage::class, 'route']);
                        add_action('load-foodbank_page_fbm-database', [\FoodBankManager\Admin\DatabasePage::class, 'route']);
                        add_action('load-foodbank_page_fbm-attendance', [\FoodBankManager\Admin\AttendancePage::class, 'route']);
                        add_action('load-foodbank_page_fbm-permissions', [\FoodBankManager\Admin\PermissionsPage::class, 'route']);
                        add_action('load-foodbank_page_fbm-settings', [\FoodBankManager\Admin\SettingsPage::class, 'route']);
                        add_action('load-foodbank_page_fbm-emails', [\FoodBankManager\Admin\EmailsPage::class, 'route']);
                }

                add_action( 'admin_post_nopriv_fbm_submit', array( FormSubmitController::class, 'handle' ) );
                add_action( 'admin_post_fbm_submit', array( FormSubmitController::class, 'handle' ) );

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
                                $from = \FoodBankManager\Core\Options::get('emails.from_email');
                                if ( ! is_email( $from ) ) {
                                        add_action(
                                                'admin_notices',
                                                static function (): void {
                                                        echo '<div class="notice notice-error"><p>' . esc_html__( 'FoodBank Manager: From email is not configured.', 'foodbank-manager' ) . '</p></div>';
                                                }
                                        );
                                }
                                $provider = \FoodBankManager\Core\Options::get('forms.captcha_provider');
                                if ( $provider !== 'off' ) {
                                        $site = \FoodBankManager\Core\Options::get('forms.captcha_site_key');
                                        $secret = \FoodBankManager\Core\Options::get('forms.captcha_secret');
                                        if ( $site === '' || $secret === '' ) {
                                                add_action(
                                                        'admin_notices',
                                                        static function (): void {
                                                                echo '<div class="notice notice-warning"><p>' . esc_html__( 'FoodBank Manager: CAPTCHA keys are missing.', 'foodbank-manager' ) . '</p></div>';
                                                        }
                                                );
                                        }
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
