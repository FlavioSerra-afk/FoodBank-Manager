<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Auth\Capabilities;
use FoodBankManager\Auth\Roles;
use FoodBankManager\Core\Options;
use FoodBankManager\Security\Helpers;

final class PermissionsPage {
    public static function route(): void {
        if ( ! current_user_can( 'fb_manage_permissions' ) && ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
        }
        $tab    = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( (string) $_GET['tab'] ) ) : 'roles';
        $notice = '';
        if ( $tab === 'import' && isset( $_GET['export'] ) ) {
            Helpers::require_nonce( 'fbm_perm_import' );
            $roles_data = Options::get( 'permissions_roles', array() );
            $users_data = array();
            $users      = get_users( array( 'meta_key' => 'fbm_user_caps', 'fields' => array( 'ID' ) ) );
            foreach ( $users as $u ) {
                $meta = get_user_meta( $u->ID, 'fbm_user_caps', true );
                if ( ! empty( $meta ) ) {
                    $users_data[ $u->ID ] = $meta;
                }
            }
            $json = wp_json_encode( array( 'roles' => $roles_data, 'users' => $users_data ) );
            header( 'Content-Type: application/json' );
            header( 'Content-Disposition: attachment; filename=fbm-permissions.json' );
            echo (string) $json;
            exit;
        } elseif ( $tab === 'roles' && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            Helpers::require_nonce( 'fbm_perm_roles' );
            $input   = isset( $_POST['role_caps'] ) && is_array( $_POST['role_caps'] ) ? $_POST['role_caps'] : array();
            $mapping = array();
            foreach ( get_editable_roles() as $role_key => $role ) {
                if ( $role_key === 'administrator' ) {
                    continue;
                }
                $caps_for_role = array();
                foreach ( Capabilities::all() as $cap ) {
                    $caps_for_role[ $cap ] = isset( $input[ $role_key ][ $cap ] );
                    $wp_role               = get_role( $role_key );
                    if ( $wp_role ) {
                        if ( $caps_for_role[ $cap ] ) {
                            $wp_role->add_cap( $cap );
                        } else {
                            $wp_role->remove_cap( $cap );
                        }
                    }
                }
                $mapping[ $role_key ] = $caps_for_role;
            }
            Options::update( 'permissions_roles', $mapping );
            Roles::grantCapsToAdmin();
            $notice = __( 'Permissions updated.', 'foodbank-manager' );
        } elseif ( $tab === 'users' && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            Helpers::require_nonce( 'fbm_perm_users' );
            $input = isset( $_POST['user_caps'] ) && is_array( $_POST['user_caps'] ) ? $_POST['user_caps'] : array();
            foreach ( $input as $user_id => $caps ) {
                $user_id = (int) $user_id;
                $meta    = array();
                foreach ( Capabilities::all() as $cap ) {
                    if ( isset( $caps[ $cap ] ) ) {
                        $meta[ $cap ] = true;
                    }
                }
                if ( $user_id === get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
                    $meta['fb_manage_permissions'] = true;
                }
                if ( empty( $meta ) ) {
                    delete_user_meta( $user_id, 'fbm_user_caps' );
                } else {
                    update_user_meta( $user_id, 'fbm_user_caps', $meta );
                }
            }
            $notice = __( 'Permissions updated.', 'foodbank-manager' );
        } elseif ( $tab === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            Helpers::require_nonce( 'fbm_perm_import' );
            if ( ! empty( $_FILES['import_json']['tmp_name'] ) ) {
                $data    = file_get_contents( $_FILES['import_json']['tmp_name'] );
                $decoded = json_decode( (string) $data, true );
                if ( is_array( $decoded ) ) {
                    $roles_data = is_array( $decoded['roles'] ?? null ) ? $decoded['roles'] : array();
                    Options::update( 'permissions_roles', $roles_data );
                    foreach ( $roles_data as $role_key => $caps_data ) {
                        if ( $role_key === 'administrator' ) {
                            continue;
                        }
                        $wp_role = get_role( $role_key );
                        if ( $wp_role ) {
                            foreach ( Capabilities::all() as $cap ) {
                                if ( ! empty( $caps_data[ $cap ] ) ) {
                                    $wp_role->add_cap( $cap );
                                } else {
                                    $wp_role->remove_cap( $cap );
                                }
                            }
                        }
                    }
                    $users_data = is_array( $decoded['users'] ?? null ) ? $decoded['users'] : array();
                    foreach ( $users_data as $user_id => $caps ) {
                        $user_id = (int) $user_id;
                        if ( is_array( $caps ) && ! empty( $caps ) ) {
                            update_user_meta( $user_id, 'fbm_user_caps', $caps );
                        } else {
                            delete_user_meta( $user_id, 'fbm_user_caps' );
                        }
                    }
                    Roles::grantCapsToAdmin();
                    $notice = __( 'Permissions imported.', 'foodbank-manager' );
                } else {
                    $notice = __( 'Invalid JSON file.', 'foodbank-manager' );
                }
            }
        } elseif ( $tab === 'reset' && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            Helpers::require_nonce( 'fbm_perm_reset' );
            delete_option( 'fbm_permissions_roles' );
            $users = get_users( array( 'meta_key' => 'fbm_user_caps', 'fields' => 'ID' ) );
            foreach ( $users as $u ) {
                delete_user_meta( $u->ID, 'fbm_user_caps' );
            }
            Roles::grantCapsToAdmin();
            $notice = __( 'Permissions reset.', 'foodbank-manager' );
        }

        $roles      = get_editable_roles();
        $role_caps  = Options::get( 'permissions_roles', array() );
        $caps       = Capabilities::all();
        $cap_labels = self::capLabels();
        $search     = isset( $_GET['user_search'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['user_search'] ) ) : '';
        $paged      = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
        $users      = array();
        if ( $tab === 'users' && $search !== '' ) {
            $users = get_users( array(
                'search'  => '*' . $search . '*',
                'number'  => 20,
                'offset'  => 20 * ( $paged - 1 ),
                'orderby' => 'ID',
                'order'   => 'ASC',
            ) );
        }

        require \FBM_PATH . 'templates/admin/permissions.php';
    }

    /**
     * @return array<string,string>
     */
    private static function capLabels(): array {
        return array(
            'fb_read_entries'      => __( 'Read entries', 'foodbank-manager' ),
            'fb_edit_entries'      => __( 'Edit entries', 'foodbank-manager' ),
            'fb_delete_entries'    => __( 'Delete entries', 'foodbank-manager' ),
            'fb_export_entries'    => __( 'Export entries', 'foodbank-manager' ),
            'fb_manage_forms'      => __( 'Manage forms', 'foodbank-manager' ),
            'fb_manage_settings'   => __( 'Manage settings', 'foodbank-manager' ),
            'fb_manage_emails'     => __( 'Manage emails', 'foodbank-manager' ),
            'fb_manage_encryption' => __( 'Manage encryption', 'foodbank-manager' ),
            'attendance_checkin'   => __( 'Attendance check-in', 'foodbank-manager' ),
            'attendance_view'      => __( 'View attendance', 'foodbank-manager' ),
            'attendance_export'    => __( 'Export attendance', 'foodbank-manager' ),
            'attendance_admin'     => __( 'Administer attendance', 'foodbank-manager' ),
            'read_sensitive'       => __( 'Read sensitive data', 'foodbank-manager' ),
            'fb_manage_permissions' => __( 'Manage permissions', 'foodbank-manager' ),
        );
    }
}
