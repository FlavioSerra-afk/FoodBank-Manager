<?php
declare(strict_types=1);

namespace {
use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\PermissionsPage;
use FoodBankManager\Admin\UsersMeta;
use FoodBankManager\Auth\Capabilities;

// capability handled via $GLOBALS['fbm_user_caps']
if ( ! function_exists( 'check_admin_referer' ) ) {
    function check_admin_referer( string $action, string $name = '_fbm_nonce' ): void {
        if ( empty( $_POST[ $name ] ) ) {
            throw new \RuntimeException( 'missing nonce' );
        }
    }
}
if ( ! function_exists( 'wp_die' ) ) {
    function wp_die( $message = '' ) {
        throw new \RuntimeException( (string) $message );
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
    function wp_safe_redirect( string $url, int $status = 303 ): void {
        PermissionsPageTest::$redirect = $url;
        throw new \RuntimeException( 'redirect' );
    }
}
if ( ! function_exists( 'is_email' ) ) {
    function is_email( $email ) {
        return (bool) filter_var( $email, FILTER_VALIDATE_EMAIL );
    }
}
if ( ! function_exists( 'sanitize_key' ) ) {
    function sanitize_key( $key ) {
        return preg_replace( '/[^a-z0-9_]/', '', strtolower( (string) $key ) );
    }
}
if ( ! function_exists( 'sanitize_email' ) ) {
    function sanitize_email( $email ) {
        return filter_var( $email, FILTER_SANITIZE_EMAIL );
    }
}
if ( ! function_exists( '__' ) ) {
    function __( string $text, string $domain = 'default' ): string {
        return $text;
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
if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode( $data ) {
        return json_encode( $data );
    }
}
if ( ! function_exists( 'wp_send_json' ) ) {
    function wp_send_json( $data ) {
        echo json_encode( $data );
        wp_die();
    }
}
if ( ! function_exists( 'nocache_headers' ) ) {
    function nocache_headers() {}
}
if ( ! function_exists( 'header' ) ) {
    function header( $string ) {}
}
if ( ! function_exists( 'get_editable_roles' ) ) {
    function get_editable_roles() {
        return array(
            'administrator' => array( 'name' => 'Administrator' ),
            'editor'        => array( 'name' => 'Editor' ),
        );
    }
}
class WP_Role {
    public $caps = array();
    public function add_cap( $cap ) { $this->caps[ $cap ] = true; }
    public function remove_cap( $cap ) { unset( $this->caps[ $cap ] ); }
    public function has_cap( $cap ) { return isset( $this->caps[ $cap ] ); }
}
if ( ! function_exists( 'get_role' ) ) {
    function get_role( $role ) {
        static $roles = array();
        if ( ! isset( $roles[ $role ] ) ) {
            $roles[ $role ] = new WP_Role();
        }
        return $roles[ $role ];
    }
}
if ( ! function_exists( 'get_user_by' ) ) {
    function get_user_by( $field, $value ) {
        if ( 'email' === $field ) {
            return (object) array( 'ID' => 1, 'user_email' => $value, 'user_login' => 'user1' );
        }
        if ( 'id' === $field ) {
            return (object) array( 'ID' => $value, 'user_email' => 'u' . $value . '@example.com', 'user_login' => 'user' . $value );
        }
        return false;
    }
}
if ( ! function_exists( 'get_users' ) ) {
    function get_users( $args = array() ) {
        global $fbm_test_user_meta;
        $out = array();
        foreach ( $fbm_test_user_meta as $id => $meta ) {
            if ( isset( $meta['fbm_user_caps'] ) ) {
                $out[] = (object) array( 'ID' => $id, 'user_login' => 'user' . $id, 'user_email' => 'u' . $id . '@example.com' );
            }
        }
        return $out;
    }
}
if ( ! function_exists( 'get_user_meta' ) ) {
    function get_user_meta( $user_id, $key, $single ) {
        global $fbm_test_user_meta;
        return $fbm_test_user_meta[ $user_id ][ $key ] ?? array();
    }
}
if ( ! function_exists( 'update_user_meta' ) ) {
    function update_user_meta( $user_id, $key, $value ) {
        global $fbm_test_user_meta;
        $fbm_test_user_meta[ $user_id ][ $key ] = $value;
        return true;
    }
}
if ( ! function_exists( 'delete_user_meta' ) ) {
    function delete_user_meta( $user_id, $key ) {
        global $fbm_test_user_meta;
        unset( $fbm_test_user_meta[ $user_id ][ $key ] );
        return true;
    }
}
if ( ! function_exists( 'update_option' ) ) {
    function update_option( $key, $value ) {
        global $fbm_test_options;
        $fbm_test_options[ $key ] = $value;
        return true;
    }
}
if ( ! function_exists( 'get_option' ) ) {
    function get_option( $key, $default = array() ) {
        global $fbm_test_options;
        return $fbm_test_options[ $key ] ?? $default;
    }
}
if ( ! function_exists( 'delete_option' ) ) {
    function delete_option( $key ) {
        global $fbm_test_options;
        unset( $fbm_test_options[ $key ] );
        return true;
    }
}
}

namespace {
    final class PermissionsPageTest extends \PHPUnit\Framework\TestCase {
        public static string $redirect = '';

        protected function setUp(): void {
            fbm_reset_globals();
            $GLOBALS['fbm_user_caps'] = ['fb_manage_permissions' => true];
            self::$redirect = '';
            $_POST          = array();
            $_FILES         = array();
            global $fbm_test_user_meta, $fbm_test_options;
            $fbm_test_user_meta = array( 1 => array() );
            $fbm_test_options   = array();
        }

        public function test_import_rejects_bad_json(): void {
            $_POST['fbm_action'] = 'perm_import';
            $_POST['_fbm_nonce'] = '1';
            $_POST['json']       = 'bad';
            $page = new \FoodBankManager\Admin\PermissionsPage();
            $ref  = new \ReflectionMethod( \FoodBankManager\Admin\PermissionsPage::class, 'handle_import' );
            $ref->setAccessible( true );
            $this->expectException( \RuntimeException::class );
            $ref->invoke( $page );
        }

        public function test_user_override_add_and_remove(): void {
            $_POST['_fbm_nonce'] = '1';
            $_POST['_wpnonce']   = '1';
            $_POST['user_id']    = 1;
            $_POST['caps']       = array( 'fb_manage_dashboard' );
            $add = new \ReflectionMethod( \FoodBankManager\Admin\PermissionsPage::class, 'handle_user_override_add' );
            $add->setAccessible( true );
            try {
                $add->invoke( new \FoodBankManager\Admin\PermissionsPage() );
            } catch ( \RuntimeException $e ) {
            }
            global $fbm_test_user_meta;
            $this->assertArrayHasKey( 'fbm_user_caps', $fbm_test_user_meta[1] );

            $_POST['_fbm_nonce'] = '1';
            $_POST['_wpnonce']   = '1';
            $_POST['user_id']    = 1;
            $rm = new \ReflectionMethod( \FoodBankManager\Admin\PermissionsPage::class, 'handle_user_override_remove' );
            $rm->setAccessible( true );
            try {
                $rm->invoke( new \FoodBankManager\Admin\PermissionsPage() );
            } catch ( \RuntimeException $e ) {
            }
            $this->assertArrayNotHasKey( 'fbm_user_caps', $fbm_test_user_meta[1] );
        }

        public function test_export_json(): void {
            global $fbm_test_user_meta;
            $fbm_test_user_meta[1]['fbm_user_caps'] = array( 'fb_manage_dashboard' => true );
            $_POST['_fbm_nonce'] = '1';
            $page = new \FoodBankManager\Admin\PermissionsPage();
            $ref  = new \ReflectionMethod( \FoodBankManager\Admin\PermissionsPage::class, 'handle_export' );
            $ref->setAccessible( true );
            ob_start();
            try {
                $ref->invoke( $page );
            } catch ( \RuntimeException $e ) {
            }
            $out  = ob_get_clean();
            $data = json_decode( (string) $out, true );
            $this->assertIsArray( $data );
            $this->assertArrayHasKey( 'roles', $data );
            $this->assertArrayHasKey( 'overrides', $data );
        }
    }
}
