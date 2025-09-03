<?php
/**
 * Permissions admin page controller.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Auth\Capabilities;
use FoodBankManager\Auth\Roles;
use FoodBankManager\Core\Options;

/**
 * Permissions admin page.
 */
final class PermissionsPage {
	/**
	 * Route handler wrapper.
	 */
	public static function route(): void {
		( new self() )->render();
	}
	/**
	 * Render permissions management page.
	 */
	public function render(): void {
		if ( ! current_user_can( 'fb_manage_permissions' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'foodbank-manager' ), 403 );
		}

		$action = isset( $_POST['fbm_action'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['fbm_action'] ) ) : '';
		if ( '' !== $action ) {
			check_admin_referer( 'fbm_permissions_' . $action );
			switch ( $action ) {
				case 'update_caps':
					$this->handle_update_caps();
					break;
				case 'import':
					$this->handle_import();
					break;
				case 'reset':
					$this->handle_reset();
					break;
				case 'user_override':
					$this->handle_user_override();
					break;
				default:
					$this->redirect_with_notice( 'invalid_action', 'error' );
			}
			exit;
		}

		$allowed_tabs = array( 'roles', 'users', 'import', 'export', 'reset' );
		$tab          = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( (string) $_GET['tab'] ) ) : 'roles';
		if ( ! in_array( $tab, $allowed_tabs, true ) ) {
			$tab = 'roles';
		}

		if ( 'import' === $tab && isset( $_GET['export'] ) ) {
			check_admin_referer( 'fbm_permissions_export' );
			$roles_data = Options::get( 'permissions_roles', array() );
			$users_data = array();
			$users      = get_users(
				array(
					'meta_key' => 'fbm_user_caps',
					'fields'   => array( 'ID' ),
				)
			);
			foreach ( $users as $u ) {
				$meta = get_user_meta( $u->ID, 'fbm_user_caps', true );
				if ( is_array( $meta ) && ! empty( $meta ) ) {
					$users_data[ $u->ID ] = $meta;
				}
			}
			wp_send_json(
				array(
					'roles' => $roles_data,
					'users' => $users_data,
				)
			);
		}

		$paged           = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$orderby         = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( (string) $_GET['orderby'] ) ) : 'user_login';
		$allowed_orderby = array( 'user_login', 'display_name', 'ID' );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'user_login';
		}
		$order = isset( $_GET['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( (string) $_GET['order'] ) ) ) : 'ASC';
		$order = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'ASC';

		$notice = isset( $_GET['notice'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['notice'] ) ) : '';
		$type   = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['type'] ) ) : 'success';
		if ( '' !== $notice ) {
			printf(
				'<div class="notice notice-%1$s"><p>%2$s</p></div>',
				esc_attr( 'error' === $type ? 'error' : 'success' ),
				esc_html( $this->translate_notice( $notice ) )
			);
		}

		$roles     = get_editable_roles();
		$role_caps = Options::get( 'permissions_roles', array() );
		if ( ! is_array( $role_caps ) ) {
			$role_caps = array();
		}
		$caps       = $this->known_caps();
		$cap_labels = $this->cap_labels();
		$search     = isset( $_GET['user_search'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['user_search'] ) ) : '';

		$users = array();
		if ( 'users' === $tab && '' !== $search ) {
			$args  = array(
				'search'  => '*' . $search . '*',
				'number'  => 20,
				'offset'  => 20 * ( $paged - 1 ),
				'orderby' => $orderby,
				'order'   => $order,
			);
			$users = get_users( $args );
		}

		require \FBM_PATH . 'templates/admin/permissions.php';
	}

	/**
	 * Handle role capability updates.
	 */
	private function handle_update_caps(): void {
		$raw_input = filter_input( INPUT_POST, 'caps', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$caps      = is_array( $raw_input ) ? (array) wp_unslash( $raw_input ) : array();
		/**
		 * Caps map.
		 *
		 * @var array<string,array<mixed>> $caps
		 */

		$known   = $this->known_caps();
		$mapping = array();
		foreach ( $caps as $role => $role_caps ) {
			$role     = sanitize_key( wp_unslash( (string) $role ) );
			$role_obj = get_role( $role );
			if ( ! $role_obj ) {
				continue;
			}
			$caps_for_role = array();
			foreach ( $role_caps as $cap => $val ) {
				$cap = sanitize_key( wp_unslash( (string) $cap ) );
				if ( ! in_array( $cap, $known, true ) ) {
					continue;
				}
				$granted               = (bool) intval( $val );
				$caps_for_role[ $cap ] = $granted;
				if ( $granted && ! $role_obj->has_cap( $cap ) ) {
					$role_obj->add_cap( $cap );
				} elseif ( ! $granted && $role_obj->has_cap( $cap ) ) {
					$role_obj->remove_cap( $cap );
				}
			}
			$mapping[ $role ] = $caps_for_role;
		}

		Options::update( 'permissions_roles', $mapping );
		Roles::ensure_admin_caps();
		$this->redirect_with_notice( 'updated' );
	}

	/**
	 * Handle permissions import.
	 */
	private function handle_import(): void {
			$json = (string) filter_input( INPUT_POST, 'json', FILTER_UNSAFE_RAW );
		$data     = json_decode( $json, true );
		if ( ! is_array( $data ) ) {
			$this->redirect_with_notice( 'invalid_payload', 'error' );
		}

		$known = $this->known_caps();
		$roles = is_array( $data['roles'] ?? null ) ? $data['roles'] : array();
		$users = is_array( $data['users'] ?? null ) ? $data['users'] : array();

		foreach ( $roles as $role => $caps ) {
			$role     = sanitize_key( (string) $role );
			$role_obj = get_role( $role );
			if ( ! $role_obj || ! is_array( $caps ) ) {
				continue;
			}
			foreach ( $caps as $cap => $granted ) {
				$cap = sanitize_key( (string) $cap );
				if ( ! in_array( $cap, $known, true ) ) {
					continue;
				}
				$granted = (bool) $granted;
				if ( $granted && ! $role_obj->has_cap( $cap ) ) {
					$role_obj->add_cap( $cap );
				} elseif ( ! $granted && $role_obj->has_cap( $cap ) ) {
					$role_obj->remove_cap( $cap );
				}
			}
		}

		foreach ( $users as $user_id => $caps ) {
			$user_id = absint( $user_id );
			if ( $user_id <= 0 || ! is_array( $caps ) ) {
				continue;
			}
			$meta = array();
			foreach ( $caps as $cap => $granted ) {
				$cap = sanitize_key( (string) $cap );
				if ( ! in_array( $cap, $known, true ) || ! $granted ) {
					continue;
				}
				$meta[ $cap ] = true;
			}
			if ( empty( $meta ) ) {
				delete_user_meta( $user_id, 'fbm_user_caps' );
			} else {
				update_user_meta( $user_id, 'fbm_user_caps', $meta );
			}
		}

		Options::update( 'permissions_roles', $roles );
		Roles::ensure_admin_caps();
		$this->redirect_with_notice( 'imported' );
	}

	/**
	 * Handle reset to defaults.
	 */
	private function handle_reset(): void {
		delete_option( 'fbm_permissions_roles' );
		$users = get_users(
			array(
				'meta_key' => 'fbm_user_caps',
				'fields'   => 'ID',
			)
		);
		foreach ( $users as $u ) {
			delete_user_meta( $u->ID, 'fbm_user_caps' );
		}
		Roles::install();
		Roles::ensure_admin_caps();
		$this->redirect_with_notice( 'reset' );
	}

	/**
	 * Handle per-user overrides.
	 */
	private function handle_user_override(): void {
		$raw_input = filter_input( INPUT_POST, 'overrides', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data      = is_array( $raw_input ) ? (array) wp_unslash( $raw_input ) : array();
		/**
		 * Overrides map.
		 *
		 * @var array<string,array<mixed>> $data
		 */
		$known = $this->known_caps();
		foreach ( $data as $user_id => $caps_raw ) {
			$user_id = absint( $user_id );
			if ( $user_id <= 0 ) {
				continue;
			}
			$meta = array();
			foreach ( $caps_raw as $cap => $val ) {
				$cap = sanitize_key( wp_unslash( (string) $cap ) );
				if ( ! in_array( $cap, $known, true ) ) {
					continue;
				}
				if ( (bool) intval( $val ) ) {
					$meta[ $cap ] = true;
				}
			}
			if ( get_current_user_id() === $user_id && ! current_user_can( 'manage_options' ) ) {
				$meta['fb_manage_permissions'] = true;
			}
			if ( empty( $meta ) ) {
				delete_user_meta( $user_id, 'fbm_user_caps' );
			} else {
				update_user_meta( $user_id, 'fbm_user_caps', $meta );
			}
		}
		$this->redirect_with_notice( 'updated' );
	}

	/**
	 * Known capabilities list.
	 *
	 * @return string[]
	 */
	private function known_caps(): array {
		return Capabilities::all();
	}

	/**
	 * Map codes to human messages.
	 *
	 * @param string $code Notice code.
	 * @return string
	 */
	private function translate_notice( string $code ): string {
		switch ( $code ) {
			case 'updated':
				return __( 'Permissions updated.', 'foodbank-manager' );
			case 'imported':
				return __( 'Permissions imported.', 'foodbank-manager' );
			case 'reset':
				return __( 'Permissions reset to defaults.', 'foodbank-manager' );
			case 'invalid_action':
				return __( 'Invalid action.', 'foodbank-manager' );
			case 'invalid_nonce':
				return __( 'Security check failed.', 'foodbank-manager' );
			case 'invalid_payload':
				return __( 'Invalid or malformed data.', 'foodbank-manager' );
			default:
				return __( 'Operation completed.', 'foodbank-manager' );
		}
	}

	/**
	 * Redirect helper with notice.
	 *
	 * @param string               $code Notice code.
	 * @param string               $type Notice type.
	 * @param array<string,string> $args Extra args.
	 * @return void
	 */
	private function redirect_with_notice( string $code, string $type = 'success', array $args = array() ): void {
		$base = menu_page_url( 'fbm-permissions', false );
		$args = array_merge(
			array(
				'notice' => $code,
				'type'   => $type,
			),
			$args
		);
		wp_safe_redirect( add_query_arg( array_map( 'rawurlencode', $args ), $base ), 303 );
		exit;
	}

	/**
	 * Capability labels.
	 *
	 * @return array<string,string>
	 */
	private function cap_labels(): array {
		return array(
			'fb_manage_dashboard'   => __( 'Dashboard', 'foodbank-manager' ),
			'fb_manage_attendance'  => __( 'Manage attendance', 'foodbank-manager' ),
			'fb_manage_database'    => __( 'Manage database', 'foodbank-manager' ),
			'fb_manage_forms'       => __( 'Manage forms', 'foodbank-manager' ),
			'fb_manage_settings'    => __( 'Manage settings', 'foodbank-manager' ),
			'fb_manage_diagnostics' => __( 'Diagnostics', 'foodbank-manager' ),
			'fb_manage_permissions' => __( 'Manage permissions', 'foodbank-manager' ),
			'fb_manage_theme'       => __( 'Manage theme', 'foodbank-manager' ),
			'fb_view_sensitive'     => __( 'View sensitive data', 'foodbank-manager' ),
		);
	}
}
