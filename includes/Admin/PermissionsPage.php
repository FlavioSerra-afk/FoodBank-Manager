<?php
/**
 * Permissions admin page controller.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FBM\Core\Capabilities;
use FoodBankManager\Auth\Roles;
use FoodBankManager\Core\Options;
use FoodBankManager\Admin\PermissionsAudit;
use function add_action;
use function register_setting;
use function get_role;
use function get_user_by;
use function wp_die;
use function wp_json_encode;
use function get_option;
use function update_option;
use function delete_user_meta;
use function sanitize_key;

/**
 * Permissions admin page.
 */
final class PermissionsPage {
        /**
         * Register hooks.
         */
        public static function boot(): void {
                add_action( 'admin_post_fbm_perms_role_toggle', array( __CLASS__, 'handle_role_toggle' ) );
                add_action(
                        'admin_init',
                        static function (): void {
                                register_setting(
                                        'fbm',
                                        'fbm_permissions_defaults',
                                        array(
                                                'sanitize_callback' => array( __CLASS__, 'sanitize_defaults' ),
                                                'type'              => 'array',
                                        )
                                );
                        }
                );
        }
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
				case 'perm_export':
					$this->handle_export();
					break;
				case 'perm_import':
						$this->handle_import();
					break;
				case 'perm_reset':
						$this->handle_reset();
					break;
				case 'perm_user_override_add':
						$this->handle_user_override_add();
					break;
				case 'perm_user_override_remove':
						$this->handle_user_override_remove();
					break;
				default:
						$this->redirect_with_notice( 'invalid_action', 'error' );
			}
				exit;
		}

				$allowed_tabs = array( 'roles', 'users', 'import', 'reset' );
		$tab                  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( (string) $_GET['tab'] ) ) : 'roles';
		if ( ! in_array( $tab, $allowed_tabs, true ) ) {
			$tab = 'roles';
		}

				// Export is handled via POST.

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

                $roles      = get_editable_roles();
                $caps       = $this->known_caps();
                $cap_labels = $this->cap_labels();
                $role_caps  = array();
                foreach ( $roles as $r => $_data ) {
                        $obj = get_role( $r );
                        foreach ( $caps as $cap ) {
                                $role_caps[ $r ][ $cap ] = $obj ? $obj->has_cap( $cap ) : false;
                        }
                }
                $search     = isset( $_GET['user_search'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['user_search'] ) ) : '';

				$override_users = array();
		if ( 'users' === $tab ) {
				$override_users = get_users(
					array(
						'meta_key' => 'fbm_user_caps',
						'fields'   => array( 'ID', 'user_login', 'user_email' ),
					)
				);
				$users          = array();
			if ( '' !== $search ) {
				$args  = array(
					'search'  => '*' . $search . '*',
					'number'  => 50,
					'orderby' => $orderby,
					'order'   => $order,
				);
				$users = get_users( $args );
			}
		}

                $audit = PermissionsAudit::all();
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
						$mapping[ $role ] = array_keys( array_filter( $caps_for_role ) );
		}

			Options::update( 'permissions_roles', $mapping );
		Roles::ensure_admin_caps();
			$this->redirect_with_notice( 'updated' );
	}

		/**
		 * Export permissions to JSON download.
		 */
	private function handle_export(): void {
			nocache_headers();
			$roles_export = array();
		foreach ( get_editable_roles() as $role_key => $role_data ) {
				$role_obj = get_role( $role_key );
			if ( ! $role_obj ) {
				continue;
			}
				$caps_list = array();
			foreach ( $this->known_caps() as $cap ) {
				if ( $role_obj->has_cap( $cap ) ) {
					$caps_list[] = $cap;
				}
			}
				$roles_export[ $role_key ] = $caps_list;
		}

			$overrides = array();
			$users     = get_users(
				array(
					'meta_key' => 'fbm_user_caps',
					'fields'   => array( 'ID', 'user_email' ),
				)
			);
		foreach ( $users as $u ) {
				$meta = UsersMeta::get_user_caps( $u->ID );
			if ( empty( $meta ) ) {
				continue;
			}
				$key               = is_email( $u->user_email ) ? $u->user_email : (string) $u->ID;
				$overrides[ $key ] = array_keys( $meta );
		}

			$payload  = array(
				'roles'     => $roles_export,
				'overrides' => $overrides,
			);
			$filename = 'fbm-permissions-' . gmdate( 'Ymd' ) . '.json';
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=' . $filename );
			wp_send_json( $payload );
	}

	/**
	 * Handle permissions import.
	 */
	private function handle_import(): void {
			check_admin_referer( 'fbm_permissions_perm_import' );
			$json = '';
		if ( ! empty( $_FILES['import_file']['tmp_name'] ) ) {
				$tmp = sanitize_text_field( wp_unslash( $_FILES['import_file']['tmp_name'] ) );
			if ( '' !== $tmp && is_readable( $tmp ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				\WP_Filesystem();
				global $wp_filesystem;
				$json = (string) $wp_filesystem->get_contents( $tmp );
			}
		} else {
				$json = (string) filter_input( INPUT_POST, 'json', FILTER_UNSAFE_RAW );
				$json = sanitize_text_field( $json );
		}
			$data = json_decode( $json, true );
		if ( ! is_array( $data ) ) {
				$this->redirect_with_notice( 'invalid_payload', 'error' );
		}

			$known         = $this->known_caps();
			$roles         = is_array( $data['roles'] ?? null ) ? $data['roles'] : array();
			$overrides_raw = is_array( $data['overrides'] ?? null ) ? $data['overrides'] : array();
			$dry_run       = (bool) filter_input( INPUT_POST, 'dry_run', FILTER_VALIDATE_BOOLEAN );

			$roles_option = array();
		foreach ( $roles as $role => $caps_list ) {
			$role     = sanitize_key( (string) $role );
			$role_obj = get_role( $role );
			if ( ! $role_obj || ! is_array( $caps_list ) ) {
				continue;
			}
			$clean = array();
			foreach ( $caps_list as $cap ) {
				$cap = sanitize_key( (string) $cap );
				if ( in_array( $cap, $known, true ) ) {
					$clean[ $cap ] = true;
				}
			}
			if ( ! $dry_run ) {
				foreach ( $known as $cap ) {
					if ( isset( $clean[ $cap ] ) ) {
						$role_obj->add_cap( $cap );
					} else {
						$role_obj->remove_cap( $cap );
					}
				}
			}
			$roles_option[ $role ] = array_keys( $clean );
		}

			$override_count = 0;
		foreach ( $overrides_raw as $user_key => $caps_list ) {
			if ( is_numeric( $user_key ) ) {
				$user_id = absint( $user_key );
				$user    = get_user_by( 'id', $user_id );
			} else {
				$user    = get_user_by( 'email', sanitize_email( (string) $user_key ) );
				$user_id = $user ? $user->ID : 0;
			}
			if ( ! $user || ! is_array( $caps_list ) ) {
				continue;
			}
			$clean = array();
			foreach ( $caps_list as $cap ) {
				$cap = sanitize_key( (string) $cap );
				if ( in_array( $cap, $known, true ) ) {
					$clean[] = $cap;
				}
			}
			if ( ! $dry_run ) {
				UsersMeta::set_user_caps( $user_id, $clean );
			}
			++$override_count;
		}

		if ( ! $dry_run ) {
			Options::update( 'permissions_roles', $roles_option );
			Roles::ensure_admin_caps();
			$this->redirect_with_notice(
				'imported',
				'success',
				array(
					'roles' => (string) count( $roles_option ),
					'users' => (string) $override_count,
				)
			);
		}
			$this->redirect_with_notice(
				'dry_run',
				'success',
				array(
					'roles' => (string) count( $roles_option ),
					'users' => (string) $override_count,
				)
			);
	}

	/**
	 * Handle reset to defaults.
	 */
        private function handle_reset(): void {
                $defaults = get_option( 'fbm_permissions_defaults', array() );
                $roles    = get_editable_roles();
                foreach ( $roles as $slug => $_data ) {
                        $role = get_role( $slug );
                        if ( ! $role ) {
                                continue;
                        }
                        foreach ( $this->known_caps() as $cap ) {
                                $role->remove_cap( $cap );
                        }
                        foreach ( (array) ( $defaults[ $slug ] ?? array() ) as $cap ) {
                                $role->add_cap( $cap );
                        }
                }
                $users = get_users( array( 'fields' => 'ID' ) );
                foreach ( $users as $u ) {
                        $uid  = is_object( $u ) ? (int) $u->ID : (int) $u;
                        $user = get_user_by( 'ID', $uid );
                        if ( $user && method_exists( $user, 'remove_cap' ) ) {
                                foreach ( $this->known_caps() as $cap ) {
                                        $user->remove_cap( $cap );
                                }
                        }
                        delete_user_meta( $uid, 'fbm_user_caps' );
                }
                Roles::ensure_admin_caps();
                PermissionsAudit::add( 'reset permissions' );
                $this->redirect_with_notice( 'reset' );
        }

		/**
		 * Add or update a per-user override.
		 */
	private function handle_user_override_add(): void {
				check_admin_referer( 'fbm_permissions_perm_user_override_add' );
				$user_id  = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
				$caps_raw = filter_input( INPUT_POST, 'caps', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) ?? array();
		if ( $user_id <= 0 ) {
				$this->redirect_with_notice( 'invalid_payload', 'error' );
		}
                        $caps  = array_map( 'sanitize_key', $caps_raw );
                        $clean = array();
                        $known = $this->known_caps();
                        $user  = get_user_by( 'ID', $user_id );
                        if ( ! $user ) {
                                $this->redirect_with_notice( 'invalid_payload', 'error' );
                        }
                foreach ( $known as $cap ) {
                        if ( in_array( $cap, $caps, true ) ) {
                                $clean[] = $cap;
                                $user->add_cap( $cap );
                        } else {
                                $user->remove_cap( $cap );
                        }
                }
                        UsersMeta::set_user_caps( $user_id, $clean );
                        PermissionsAudit::add( 'user override ' . $user_id );
                        $this->redirect_with_notice( 'updated' );
        }

		/**
		 * Remove per-user override.
		 */
	private function handle_user_override_remove(): void {
			check_admin_referer( 'fbm_permissions_perm_user_override_remove' );
			$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		if ( $user_id <= 0 ) {
				$this->redirect_with_notice( 'invalid_payload', 'error' );
		}
                        $user = get_user_by( 'ID', $user_id );
                        if ( $user ) {
                                foreach ( $this->known_caps() as $cap ) {
                                        $user->remove_cap( $cap );
                                }
                        }
                        UsersMeta::set_user_caps( $user_id, array() );
                        PermissionsAudit::add( 'user override removed ' . $user_id );
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
			case 'dry_run':
				return __( 'Dry run complete. No changes made.', 'foodbank-manager' );
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
			$base = menu_page_url( 'fbm_permissions', false );
		$args     = array_merge(
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
         * Sanitize defaults option.
         *
         * @param mixed $value Raw value.
         * @return array<string,array<int,string>>
         */
        public static function sanitize_defaults( $value ): array {
                $out   = array();
                $roles = get_editable_roles();
                $known = Capabilities::all();
                if ( is_array( $value ) ) {
                        foreach ( $value as $role => $caps ) {
                                $role = sanitize_key( (string) $role );
                                if ( ! isset( $roles[ $role ] ) || ! is_array( $caps ) ) {
                                        continue;
                                }
                                foreach ( $caps as $cap ) {
                                        $cap = sanitize_key( (string) $cap );
                                        if ( in_array( $cap, $known, true ) ) {
                                                $out[ $role ][] = $cap;
                                        }
                                }
                        }
                }
                return $out;
        }

        /**
         * Handle role capability toggle via AJAX.
         */
        public static function handle_role_toggle(): void {
                if ( ! current_user_can( 'fb_manage_permissions' ) ) {
                        wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'foodbank-manager' ), 403 );
                }
                check_admin_referer( 'fbm_perms_role_toggle' );
                $role_slug = isset( $_POST['role'] ) ? sanitize_key( wp_unslash( (string) $_POST['role'] ) ) : '';
                $cap       = isset( $_POST['cap'] ) ? sanitize_key( wp_unslash( (string) $_POST['cap'] ) ) : '';
                $grant     = ! empty( $_POST['grant'] );
                $known     = Capabilities::all();
                $role      = get_role( $role_slug );
                if ( ! $role || ! in_array( $cap, $known, true ) ) {
                        wp_die( wp_json_encode( array( 'success' => false ) ) );
                }
                $defaults = get_option( 'fbm_permissions_defaults', array() );
                if ( $grant ) {
                        $role->add_cap( $cap );
                        $defaults[ $role_slug ][] = $cap;
                        PermissionsAudit::add( 'grant ' . $cap . ' to role ' . $role_slug );
                } else {
                        $role->remove_cap( $cap );
                        if ( isset( $defaults[ $role_slug ] ) ) {
                                $defaults[ $role_slug ] = array_values( array_diff( $defaults[ $role_slug ], array( $cap ) ) );
                        }
                        PermissionsAudit::add( 'revoke ' . $cap . ' from role ' . $role_slug );
                }
                update_option( 'fbm_permissions_defaults', self::sanitize_defaults( $defaults ) );
                wp_die( wp_json_encode( array( 'success' => true ) ) );
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
