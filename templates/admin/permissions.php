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
<div class="wrap">
	<h1><?php \esc_html_e( 'Permissions', 'foodbank-manager' ); ?></h1>
	<?php if ( ! empty( $notice ) ) : ?>
		<div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
	<?php endif; ?>
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
		<?php wp_nonce_field( 'fbm_perm_roles', '_wpnonce' ); ?>
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
name="role_caps[<?php echo esc_attr( $role_key ); ?>][<?php echo esc_attr( $cap ); ?>]"
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
		<form method="get">
			<input type="hidden" name="page" value="fbm-permissions" />
			<input type="hidden" name="tab" value="users" />
			<p><input type="search" name="user_search" value="<?php echo esc_attr( $search ); ?>" />
						<?php submit_button( esc_html__( 'Search', 'foodbank-manager' ), 'secondary', '', false ); ?></p>
		</form>
		<?php if ( ! empty( $users ) ) : ?>
			<form method="post">
			<?php wp_nonce_field( 'fbm_perm_users', '_wpnonce' ); ?>
				<table class="widefat">
					<thead>
						<tr>
							<th><?php \esc_html_e( 'User', 'foodbank-manager' ); ?></th>
							<?php foreach ( $caps as $cap ) : ?>
								<th><?php echo esc_html( $cap_labels[ $cap ] ?? $cap ); ?></th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
			<?php foreach ( $users as $u ) : ?>
				<?php $meta = get_user_meta( $u->ID, 'fbm_user_caps', true ); ?>
<tr>
<td><?php echo esc_html( $u->user_login ); ?></td>
				<?php foreach ( $caps as $cap ) : ?>
<td>
<input type="checkbox"
name="user_caps[<?php echo esc_attr( (string) $u->ID ); ?>][<?php echo esc_attr( $cap ); ?>]"
value="1" <?php checked( isset( $meta[ $cap ] ) ? (bool) $meta[ $cap ] : false ); ?> />
</td>
<?php endforeach; ?>
</tr>
<?php endforeach; ?>
					</tbody>
				</table>
								<?php submit_button( esc_html__( 'Save', 'foodbank-manager' ) ); ?>
			</form>
		<?php endif; ?>
	<?php elseif ( $tab === 'import' ) : ?>
<h3><?php \esc_html_e( 'Export', 'foodbank-manager' ); ?></h3>
		<?php $export_url = esc_url( wp_nonce_url( '?page=fbm-permissions&tab=import&export=1', 'fbm_perm_import' ) ); ?>
<p>
<a href="<?php echo esc_url( $export_url ); ?>" class="button"><?php \esc_html_e( 'Download JSON', 'foodbank-manager' ); ?></a>
</p>
<h3><?php \esc_html_e( 'Import', 'foodbank-manager' ); ?></h3>
		<form method="post" enctype="multipart/form-data">
		<?php wp_nonce_field( 'fbm_perm_import', '_wpnonce' ); ?>
			<input type="file" name="import_json" accept="application/json" />
						<?php submit_button( esc_html__( 'Import', 'foodbank-manager' ) ); ?>
		</form>
	<?php else : ?>
		<form method="post">
		<?php wp_nonce_field( 'fbm_perm_reset', '_wpnonce' ); ?>
			<p><?php \esc_html_e( 'This will remove all role mappings and user overrides.', 'foodbank-manager' ); ?></p>
						<?php submit_button( esc_html__( 'Reset to defaults', 'foodbank-manager' ), 'delete' ); ?>
		</form>
	<?php endif; ?>
</div>
