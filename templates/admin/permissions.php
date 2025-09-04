<?php
// phpcs:ignoreFile
/**
 * Permissions page template.
 *
 * @package FoodBankManager
 * @since 0.1.1
 */

namespace FoodBankManager\Admin;

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}
$perms_base = menu_page_url( 'fbm-permissions', false );
?>
<div class="wrap fbm-admin">
        <h1><?php \esc_html_e( 'Permissions', 'foodbank-manager' ); ?></h1>
<h2 class="nav-tab-wrapper">
<a href="<?php echo esc_url( $perms_base ); ?>"
class="nav-tab <?php echo esc_attr( $tab === 'roles' ? 'nav-tab-active' : '' ); ?>"><?php \esc_html_e( 'Roles', 'foodbank-manager' ); ?></a>
<a href="<?php echo esc_url( add_query_arg( 'tab', 'users', $perms_base ) ); ?>"
class="nav-tab <?php echo esc_attr( $tab === 'users' ? 'nav-tab-active' : '' ); ?>"><?php \esc_html_e( 'Users', 'foodbank-manager' ); ?></a>
<a href="<?php echo esc_url( add_query_arg( 'tab', 'import', $perms_base ) ); ?>"
class="nav-tab <?php echo esc_attr( $tab === 'import' ? 'nav-tab-active' : '' ); ?>">
<?php \esc_html_e( 'Import/Export', 'foodbank-manager' ); ?>
</a>
<a href="<?php echo esc_url( add_query_arg( 'tab', 'reset', $perms_base ) ); ?>"
class="nav-tab <?php echo esc_attr( $tab === 'reset' ? 'nav-tab-active' : '' ); ?>"><?php \esc_html_e( 'Reset', 'foodbank-manager' ); ?></a>
</h2>

        <?php if ( $tab === 'roles' ) : ?>
                <form method="post">
                <?php wp_nonce_field( 'fbm_permissions_update_caps' ); ?>
                <input type="hidden" name="fbm_action" value="update_caps" />
                        <table class="widefat">
				<thead>
					<tr>
						<th><?php \esc_html_e( 'Role', 'foodbank-manager' ); ?></th>
						<?php foreach ( $caps as $cap ) : ?>
							<th><?php echo esc_html( $cap_labels[ $cap ] ?? $cap ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
		<?php foreach ( $roles as $role_key => $role_data ) : ?>
<tr>
<td><?php echo esc_html( $role_data['name'] ); ?></td>
			<?php if ( $role_key === 'administrator' ) : ?>
<td colspan="<?php echo (int) count( $caps ); ?>">
				<?php \esc_html_e( 'Administrator always has all permissions.', 'foodbank-manager' ); ?>
</td>
<?php else : ?>
	<?php foreach ( $caps as $cap ) : ?>
<td>
<input type="checkbox"
name="caps[<?php echo esc_attr( $role_key ); ?>][<?php echo esc_attr( $cap ); ?>]"
value="1" <?php checked( isset( $role_caps[ $role_key ][ $cap ] ) ? (bool) $role_caps[ $role_key ][ $cap ] : false ); ?> />
</td>
<?php endforeach; ?>
<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
						<?php submit_button( esc_html__( 'Save', 'foodbank-manager' ) ); ?>
		</form>
        <?php elseif ( $tab === 'users' ) : ?>
                <h3><?php \esc_html_e( 'Current Overrides', 'foodbank-manager' ); ?></h3>
                <?php if ( ! empty( $override_users ) ) : ?>
                        <table class="widefat">
                                <thead>
                                        <tr>
                                                <th><?php \esc_html_e( 'User', 'foodbank-manager' ); ?></th>
                                                <?php foreach ( $caps as $cap ) : ?>
                                                        <th><?php echo esc_html( $cap_labels[ $cap ] ?? $cap ); ?></th>
                                                <?php endforeach; ?>
                                                <th><?php \esc_html_e( 'Actions', 'foodbank-manager' ); ?></th>
                                        </tr>
                                </thead>
                                <tbody>
                                <?php foreach ( $override_users as $u ) : ?>
                                        <?php $meta = UsersMeta::get_user_caps( $u->ID ); ?>
                                        <form id="ov-<?php echo esc_attr( $u->ID ); ?>" method="post">
                                                <?php wp_nonce_field( 'fbm_permissions_perm_user_override_add' ); ?>
                                                <input type="hidden" name="fbm_action" value="perm_user_override_add" />
                                                <input type="hidden" name="user_id" value="<?php echo esc_attr( $u->ID ); ?>" />
                                        </form>
                                        <tr>
                                                <td><?php echo esc_html( $u->user_login ); ?></td>
                                                <?php foreach ( $caps as $cap ) : ?>
                                                        <td><input type="checkbox" form="ov-<?php echo esc_attr( $u->ID ); ?>" name="caps[]" value="<?php echo esc_attr( $cap ); ?>" <?php checked( isset( $meta[ $cap ] ) ); ?> /></td>
                                                <?php endforeach; ?>
                                                <td>
                                                        <button form="ov-<?php echo esc_attr( $u->ID ); ?>" class="button button-secondary" type="submit"><?php \esc_html_e( 'Save', 'foodbank-manager' ); ?></button>
                                                        <form method="post" style="display:inline">
                                                                <?php wp_nonce_field( 'fbm_permissions_perm_user_override_remove' ); ?>
                                                                <input type="hidden" name="fbm_action" value="perm_user_override_remove" />
                                                                <input type="hidden" name="user_id" value="<?php echo esc_attr( $u->ID ); ?>" />
                                                                <button class="button button-link-delete" type="submit"><?php \esc_html_e( 'Remove', 'foodbank-manager' ); ?></button>
                                                        </form>
                                                </td>
                                        </tr>
                                <?php endforeach; ?>
                                </tbody>
                        </table>
                <?php endif; ?>

                <h3><?php \esc_html_e( 'Search Users', 'foodbank-manager' ); ?></h3>
                <form method="get">
                        <input type="hidden" name="page" value="fbm-permissions" />
                        <input type="hidden" name="tab" value="users" />
                        <p><input type="search" name="user_search" value="<?php echo esc_attr( $search ); ?>" />
                                <?php submit_button( esc_html__( 'Search', 'foodbank-manager' ), 'secondary', '', false ); ?></p>
                </form>
                <?php if ( ! empty( $users ) ) : ?>
                        <table class="widefat">
                                <thead>
                                        <tr>
                                                <th><?php \esc_html_e( 'User', 'foodbank-manager' ); ?></th>
                                                <?php foreach ( $caps as $cap ) : ?>
                                                        <th><?php echo esc_html( $cap_labels[ $cap ] ?? $cap ); ?></th>
                                                <?php endforeach; ?>
                                                <th><?php \esc_html_e( 'Actions', 'foodbank-manager' ); ?></th>
                                        </tr>
                                </thead>
                                <tbody>
                                <?php foreach ( $users as $u ) : ?>
                                        <form id="add-<?php echo esc_attr( $u->ID ); ?>" method="post">
                                                <?php wp_nonce_field( 'fbm_permissions_perm_user_override_add' ); ?>
                                                <input type="hidden" name="fbm_action" value="perm_user_override_add" />
                                                <input type="hidden" name="user_id" value="<?php echo esc_attr( $u->ID ); ?>" />
                                        </form>
                                        <tr>
                                                <td><?php echo esc_html( $u->user_login ); ?></td>
                                                <?php foreach ( $caps as $cap ) : ?>
                                                        <td><input type="checkbox" form="add-<?php echo esc_attr( $u->ID ); ?>" name="caps[]" value="<?php echo esc_attr( $cap ); ?>" /></td>
                                                <?php endforeach; ?>
                                                <td><button form="add-<?php echo esc_attr( $u->ID ); ?>" class="button button-secondary" type="submit"><?php \esc_html_e( 'Add', 'foodbank-manager' ); ?></button></td>
                                        </tr>
                                <?php endforeach; ?>
                                </tbody>
                        </table>
                <?php endif; ?>
        <?php elseif ( $tab === 'import' ) : ?>
<h3><?php \esc_html_e( 'Export', 'foodbank-manager' ); ?></h3>
                <form method="post">
                        <?php wp_nonce_field( 'fbm_permissions_perm_export' ); ?>
                        <input type="hidden" name="fbm_action" value="perm_export" />
                        <?php submit_button( esc_html__( 'Download JSON', 'foodbank-manager' ), 'secondary' ); ?>
                </form>
<h3><?php \esc_html_e( 'Import', 'foodbank-manager' ); ?></h3>
                <form method="post" enctype="multipart/form-data">
                        <?php wp_nonce_field( 'fbm_permissions_perm_import' ); ?>
                        <input type="hidden" name="fbm_action" value="perm_import" />
                        <p><input type="file" name="import_file" accept="application/json" /></p>
                        <p><textarea name="json" rows="10" cols="80"></textarea></p>
                        <p><label><input type="checkbox" name="dry_run" value="1" /> <?php \esc_html_e( 'Dry Run', 'foodbank-manager' ); ?></label></p>
                        <?php submit_button( esc_html__( 'Import', 'foodbank-manager' ) ); ?>
                </form>
        <?php else : ?>
                <form method="post">
                <?php wp_nonce_field( 'fbm_permissions_perm_reset' ); ?>
                <input type="hidden" name="fbm_action" value="perm_reset" />
                        <p><?php \esc_html_e( 'This will remove all role mappings and user overrides.', 'foodbank-manager' ); ?></p>
                                                <?php submit_button( esc_html__( 'Reset to defaults', 'foodbank-manager' ), 'delete' ); ?>
                </form>
        <?php endif; ?>
</div>
