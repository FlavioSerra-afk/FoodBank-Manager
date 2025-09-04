<?php
declare(strict_types=1);

namespace {
use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\DiagnosticsPage;

if ( ! function_exists( 'wp_unslash' ) ) {
    function wp_unslash( $value ) {
        return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( (string) $value );
    }
}
if ( ! function_exists( 'current_user_can' ) ) {
    function current_user_can( string $cap ): bool {
        return DiagnosticsPageTest::$can;
    }
}
if ( ! function_exists( 'check_admin_referer' ) ) {
    function check_admin_referer( string $action, string $name = '_fbm_nonce' ): void {
        if ( empty( $_POST[ $name ] ) ) {
            throw new RuntimeException( 'missing nonce' );
        }
    }
}
if ( ! function_exists( 'wp_die' ) ) {
    function wp_die( $message = '' ) {
        throw new RuntimeException( (string) $message );
    }
}
if ( ! function_exists( 'menu_page_url' ) ) {
    function menu_page_url( string $slug, bool $echo = true ): string {
        return 'admin.php?page=' . $slug;
    }
}
if ( ! function_exists( 'add_query_arg' ) ) {
    function add_query_arg( array $args, string $url ): string {
        return $url . '?' . http_build_query( $args );
    }
}
if ( ! function_exists( 'wp_safe_redirect' ) ) {
    function wp_safe_redirect( string $url, int $status = 302 ): void {
        if ( class_exists( '\\DiagnosticsPageTest', false ) ) {
            \DiagnosticsPageTest::$redirect = $url;
        }
        if ( class_exists( '\\SettingsPageTest', false ) ) {
            \SettingsPageTest::$redirect = $url;
        }
        throw new RuntimeException( 'redirect' );
    }
}
if ( ! function_exists( 'wp_get_current_user' ) ) {
    function wp_get_current_user() {
        return (object) array( 'user_email' => 'user@example.com' );
    }
}
if ( ! function_exists( 'wp_mail' ) ) {
    function wp_mail( $to, $subject, $message ): bool {
        return DiagnosticsPageTest::$mail_result;
    }
}
if ( ! function_exists( 'esc_html__' ) ) {
    function esc_html__( string $text, string $domain = 'default' ): string {
        return $text;
    }
}
if ( ! function_exists( 'esc_html_e' ) ) {
    function esc_html_e( string $text, string $domain = 'default' ): void {
        echo $text;
    }
}
if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $text ) {
        return (string) $text;
    }
}
if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return (string) $text;
    }
}
if ( ! function_exists( 'esc_url_raw' ) ) {
    function esc_url_raw( $url ) {
        return (string) $url;
    }
}
if ( ! function_exists( 'wp_next_scheduled' ) ) {
    function wp_next_scheduled( $hook ) {
        return false;
    }
}
if ( ! function_exists( 'get_bloginfo' ) ) {
    function get_bloginfo( $show ) {
        return '6.5.0';
    }
}
if ( ! function_exists( 'sanitize_key' ) ) {
    function sanitize_key( $key ) {
        return preg_replace( '/[^a-z0-9_]/', '', strtolower( (string) $key ) );
    }
}
if ( ! function_exists( 'wp_nonce_field' ) ) {
    function wp_nonce_field( $action, $name ) {}
}
if ( ! function_exists( 'is_email' ) ) {
    function is_email( $email ) {
        return (bool) filter_var( $email, FILTER_VALIDATE_EMAIL );
    }
}
if ( ! function_exists( 'get_current_screen' ) ) {
    function get_current_screen() {
        return (object) array( 'id' => 'foodbank_page_fbm_diagnostics' );
    }
}
if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $tag, $function_to_add ) {}
}
if ( ! function_exists( 'remove_filter' ) ) {
    function remove_filter( $tag, $function_to_remove ) {}
}
if ( ! function_exists( '__' ) ) {
    function __( string $text, string $domain = 'default' ): string {
        return $text;
    }
}

}

namespace FoodBankManager\Auth {
class Roles {
    public static bool $installed = false;
    public static bool $ensured  = false;
    public static function install(): void {
        self::$installed = true;
    }
    public static function ensure_admin_caps(): void {
        self::$ensured = true;
    }
}

}

namespace {
    use PHPUnit\Framework\TestCase;
    use FoodBankManager\Admin\DiagnosticsPage;

    final class DiagnosticsPageTest extends TestCase {
        public static bool $can        = true;
        public static string $redirect = '';
        public static bool $mail_result = true;

        protected function setUp(): void {
            self::$can        = true;
            self::$redirect   = '';
            self::$mail_result = true;
            \FoodBankManager\Auth\Roles::$installed = false;
            \FoodBankManager\Auth\Roles::$ensured  = false;
            $_POST   = array();
            $_SERVER = array();
            $GLOBALS['fbm_test_options'] = array(
                'emails' => array(
                    'from_name'  => 'FoodBank',
                    'from_email' => 'from@example.com',
                ),
            );
        }

        public function testMissingNonceBlocked(): void {
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action']       = 'repair_caps';
            $this->expectException( RuntimeException::class );
            DiagnosticsPage::route();
        }

        public function testUserWithoutCapabilityBlocked(): void {
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action']       = 'repair_caps';
            $_POST['_fbm_nonce']       = 'nonce';
            self::$can                 = false;
            $this->expectException( RuntimeException::class );
            DiagnosticsPage::route();
        }

        public function testSendTestEmailSuccess(): void {
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['_fbm_nonce']       = 'nonce';
            $_POST['fbm_action']       = 'send_test_email';
            try {
                DiagnosticsPage::route();
            } catch ( RuntimeException $e ) {
                $this->assertSame( 'redirect', $e->getMessage() );
            }
            $this->assertStringContainsString( 'notice=sent', self::$redirect );
        }

        public function testRepairCapsCallsRoles(): void {
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['_fbm_nonce']       = 'nonce';
            $_POST['fbm_action']       = 'repair_caps';
            try {
                DiagnosticsPage::route();
            } catch ( RuntimeException $e ) {
                $this->assertSame( 'redirect', $e->getMessage() );
            }
            $this->assertTrue( \FoodBankManager\Auth\Roles::$installed );
            $this->assertTrue( \FoodBankManager\Auth\Roles::$ensured );
            $this->assertStringContainsString( 'notice=repaired', self::$redirect );
        }

        public function testTemplateRenders(): void {
            if ( ! defined( 'ABSPATH' ) ) {
                define( 'ABSPATH', __DIR__ );
            }
            if ( ! defined( 'FBM_PATH' ) ) {
                define( 'FBM_PATH', dirname( __DIR__, 1 ) . '/../' );
            }
            ob_start();
            include FBM_PATH . 'templates/admin/diagnostics.php';
            $html = ob_get_clean();
            $this->assertStringContainsString( 'Diagnostics', $html );
        }
    }
}
