<?php
/**
 * Database listing template.
 *
 * @package FoodBankManager
 * @since 0.1.1
 */

use FoodBankManager\Admin\UsersMeta;
use FoodBankManager\Database\Columns;

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}
?>
<div class="wrap fbm-admin">
<?php \FBM\Core\Trace::mark( 'admin:database' ); ?>
<h1><?php esc_html_e( 'Database', 'foodbank-manager' ); ?></h1>
<div class="fbm-preset-bar">
<form method="post" style="display:inline">
		<input type="hidden" name="fbm_action" value="db_presets_save" />
		<?php wp_nonce_field( 'fbm_database_db_presets_save' ); ?>
		<input type="text" name="preset_name" placeholder="<?php esc_attr_e( 'Preset name', 'foodbank-manager' ); ?>" maxlength="50" />
		<button class="button" type="submit"><?php esc_html_e( 'Save Preset', 'foodbank-manager' ); ?></button>
</form>
<form method="get" style="display:inline;margin-left:10px;">
			<input type="hidden" name="page" value="fbm_database" />
		<select name="preset">
				<option value=""><?php esc_html_e( 'Select preset', 'foodbank-manager' ); ?></option>
				<?php foreach ( $presets as $p ) : ?>
												<option value="<?php echo esc_attr( $p['name'] ); ?>"
														<?php selected( $current_preset, $p['name'] ); ?>>
														<?php echo esc_html( $p['name'] ); ?>
												</option>
				<?php endforeach; ?>
		</select>
		<button class="button" type="submit"><?php esc_html_e( 'Apply', 'foodbank-manager' ); ?></button>
</form>
<form method="post" style="display:inline;margin-left:10px;">
		<input type="hidden" name="fbm_action" value="db_presets_delete" />
		<?php wp_nonce_field( 'fbm_database_db_presets_delete' ); ?>
		<select name="preset_name">
				<?php foreach ( $presets as $p ) : ?>
						<option value="<?php echo esc_attr( $p['name'] ); ?>"><?php echo esc_html( $p['name'] ); ?></option>
				<?php endforeach; ?>
		</select>
				<button class="button" type="submit"
						onclick="return confirm('<?php echo esc_js( __( 'Delete preset?', 'foodbank-manager' ) ); ?>');">
						<?php esc_html_e( 'Delete', 'foodbank-manager' ); ?>
				</button>
</form>
</div>
<form method="get">
			<input type="hidden" name="page" value="fbm_database" />
		<div class="fbm-filters">
		<label>
				<?php esc_html_e( 'From', 'foodbank-manager' ); ?>
				<input type="date" name="date_from" value="<?php echo esc_attr( $filters['date_from'] ?? '' ); ?>" />
		</label>
		<label>
				<?php esc_html_e( 'To', 'foodbank-manager' ); ?>
				<input type="date" name="date_to" value="<?php echo esc_attr( $filters['date_to'] ?? '' ); ?>" />
		</label>
		<label><?php esc_html_e( 'Status', 'foodbank-manager' ); ?>
				<select name="status">
				<option value=""><?php esc_html_e( 'All', 'foodbank-manager' ); ?></option>
				<option value="new" <?php selected( $filters['status'] ?? '', 'new' ); ?>>
						<?php esc_html_e( 'New', 'foodbank-manager' ); ?>
				</option>
				<option value="approved" <?php selected( $filters['status'] ?? '', 'approved' ); ?>>
						<?php esc_html_e( 'Approved', 'foodbank-manager' ); ?>
				</option>
				<option value="archived" <?php selected( $filters['status'] ?? '', 'archived' ); ?>>
						<?php esc_html_e( 'Archived', 'foodbank-manager' ); ?>
				</option>
				</select>
		</label>
		<label>
				<input type="checkbox" name="has_file" value="1" <?php checked( $filters['has_file'] ); ?> />
				<?php esc_html_e( 'Has file', 'foodbank-manager' ); ?>
		</label>
		<label>
				<input type="checkbox" name="consent" value="1" <?php checked( $filters['consent'] ); ?> />
				<?php esc_html_e( 'Consent', 'foodbank-manager' ); ?>
		</label>
		<label>
				<?php esc_html_e( 'Search', 'foodbank-manager' ); ?>
				<input type="text" name="search" value="<?php echo esc_attr( $filters['search'] ?? '' ); ?>" />
		</label>
	<?php if ( $can_sensitive ) : ?>
				<label>
						<input type="checkbox" name="unmask" value="1" <?php checked( $unmask ); ?> />
						<?php esc_html_e( 'Unmask sensitive fields', 'foodbank-manager' ); ?>
				</label>
				<?php wp_nonce_field( 'fbm_db_unmask', '_wpnonce' ); ?>
	<?php endif; ?>
		<button class="button"><?php esc_html_e( 'Filter', 'foodbank-manager' ); ?></button>
		</div>
</form>
<form method="post" class="fbm-columns" style="margin:10px 0;">
		<input type="hidden" name="fbm_action" value="db_columns_save" />
		<?php wp_nonce_field( 'fbm_database_db_columns_save' ); ?>
                <?php foreach ( $column_defs as $col_id => $def ) : $label = $def['label']; ?>
                                <label class="fbm-column-toggle">
                                                                                                <input type="checkbox"
                                                                                                                name="columns[]"
                                                                                                                value="<?php echo esc_attr( $col_id ); ?>"
                                                                                                                <?php checked( in_array( $col_id, (array) $columns, true ) ); ?>
                                                                                                />
                                                <?php echo esc_html( $label ); ?>
                                </label>
                <?php endforeach; ?>
                <button class="button" type="submit"><?php esc_html_e( 'Save Columns', 'foodbank-manager' ); ?></button>
</form>
        <?php if ( isset( $table ) ) { $table->display(); } ?>
<?php if ( current_user_can( 'fb_manage_database' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability. ?>
<form method="post">
                <input type="hidden" name="fbm_action" value="export_entries" />
                <?php wp_nonce_field( 'fbm_export_entries', 'fbm_nonce' ); ?>
                <?php if ( current_user_can( 'fb_view_sensitive' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown ?>
                <label>
                        <input type="checkbox" name="unmask" value="1" />
                        <?php esc_html_e( 'Unmask sensitive fields', 'foodbank-manager' ); ?>
                </label>
                <?php wp_nonce_field( 'fbm_export_unmask', '_wpnonce_unmask' ); ?>
                <?php endif; ?>
                <button class="button" type="submit"><?php esc_html_e( 'Export CSV', 'foodbank-manager' ); ?></button>
</form>
<?php endif; ?>
<script>
document.querySelectorAll('.fbm-column-toggle input[type="checkbox"]').forEach(function(cb){
		cb.addEventListener('change', function(){
				var col = this.value;
				var display = this.checked ? '' : 'none';
				document.querySelectorAll('.column-' + col).forEach(function(el){
						el.style.display = display;
				});
		});
});
</script>
</div>
