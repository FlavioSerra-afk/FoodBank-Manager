<?php // phpcs:ignoreFile
/**
 * Database listing template.
 *
 * @package FoodBankManager
 * @since 0.1.1
 */

use FoodBankManager\Security\Helpers;
use FoodBankManager\Security\Crypto;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
?>
<div class="wrap">
<h1><?php esc_html_e( 'Database', 'foodbank-manager' ); ?></h1>
<form method="get">
	<input type="hidden" name="page" value="fbm-database" />
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
<table class="wp-list-table widefat fixed striped">
<thead><tr>
<th><?php esc_html_e( 'ID', 'foodbank-manager' ); ?></th>
<th><?php esc_html_e( 'Created', 'foodbank-manager' ); ?></th>
<th><?php esc_html_e( 'Name', 'foodbank-manager' ); ?></th>
<th><?php esc_html_e( 'Email', 'foodbank-manager' ); ?></th>
<th><?php esc_html_e( 'Postcode', 'foodbank-manager' ); ?></th>
<th><?php esc_html_e( 'Status', 'foodbank-manager' ); ?></th>
<th><?php esc_html_e( 'Has Files', 'foodbank-manager' ); ?></th>
<th><?php esc_html_e( 'Actions', 'foodbank-manager' ); ?></th>
</tr></thead>
<tbody>
<?php if ( empty( $rows ) ) : ?>
<tr><td colspan="8"><?php esc_html_e( 'No entries found.', 'foodbank-manager' ); ?></td></tr>
	<?php
else :
	foreach ( $rows as $r ) :
		$data = json_decode( (string) ( $r['data_json'] ?? '' ), true );
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		$pii      = Crypto::decryptSensitive( (string) ( $r['pii_encrypted_blob'] ?? '' ) );
		$name     = trim( ( $data['first_name'] ?? '' ) . ' ' . ( $pii['last_name'] ?? '' ) );
		$email    = $pii['email'] ?? '';
		$postcode = $data['postcode'] ?? '';
		if ( ! $unmask ) {
			$email    = Helpers::mask_email( $email );
			$postcode = Helpers::mask_postcode( $postcode );
		}
		$created = get_date_from_gmt( (string) $r['created_at'] );
		?>
<tr>
<td><?php echo esc_html( (string) $r['id'] ); ?></td>
<td><?php echo esc_html( $created ); ?></td>
<td><?php echo esc_html( $name ); ?></td>
<td><?php echo esc_html( $email ); ?></td>
<td><?php echo esc_html( $postcode ); ?></td>
<td><?php echo esc_html( (string) $r['status'] ); ?></td>
<td><?php echo $r['has_files'] ? esc_html__( 'Yes', 'foodbank-manager' ) : esc_html__( 'No', 'foodbank-manager' ); ?></td>
<td>
	<a href="
		<?php
		echo esc_url(
			add_query_arg(
				array(
					'page' => 'fbm-database',
					'view' => $r['id'],
				)
			)
		);
		?>
				"><?php esc_html_e( 'View', 'foodbank-manager' ); ?></a>
		<?php
// phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
            if ( current_user_can( 'fb_manage_database' ) ) :
			?>
		| <form method="post" style="display:inline">
				<input type="hidden" name="action" value="fbm_export_single" />
				<input type="hidden" name="id" value="<?php echo esc_attr( (string) $r['id'] ); ?>" />
						<?php wp_nonce_field( 'fbm_db_single_export_' . $r['id'], '_wpnonce' ); ?>
								<button type="submit" class="button-link"><?php esc_html_e( 'CSV', 'foodbank-manager' ); ?></button>
				</form>
			<?php
endif;
// phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
            if ( current_user_can( 'fb_manage_database' ) ) :
			?>
| <form method="post" style="display:inline"
onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure?', 'foodbank-manager' ) ); ?>');">
				<input type="hidden" name="action" value="fbm_delete_entry" />
				<input type="hidden" name="id" value="<?php echo esc_attr( (string) $r['id'] ); ?>" />
						<?php wp_nonce_field( 'fbm_db_delete_' . $r['id'], '_wpnonce' ); ?>
				<button type="submit" class="button-link"><?php esc_html_e( 'Delete', 'foodbank-manager' ); ?></button>
				</form>
			<?php
endif;
		?>
</td>
</tr>
		<?php
	endforeach;
endif;
?>
</tbody>
</table>
<?php
$total_pages = max( 1, ceil( $total / $per_page ) );
$base_url    = remove_query_arg( 'paged' );
?>
<div class="tablenav">
	<div class="tablenav-pages">
	<?php if ( $page > 1 ) : ?>
		<a class="prev-page" href="<?php echo esc_url( add_query_arg( 'paged', $page - 1, $base_url ) ); ?>">&laquo;</a>
	<?php endif; ?>
	<span class="paging-input"><?php echo esc_html( $page ) . ' / ' . esc_html( $total_pages ); ?></span>
	<?php if ( $page < $total_pages ) : ?>
		<a class="next-page" href="<?php echo esc_url( add_query_arg( 'paged', $page + 1, $base_url ) ); ?>">&raquo;</a>
	<?php endif; ?>
	</div>
	<div class="alignleft actions">
	<form method="get" id="fbm-perpage-form">
		<input type="hidden" name="page" value="fbm-database" />
		<select name="per_page" onchange="document.getElementById('fbm-perpage-form').submit();">
		<option value="25" <?php selected( $per_page, 25 ); ?>>25</option>
		<option value="50" <?php selected( $per_page, 50 ); ?>>50</option>
		<option value="100" <?php selected( $per_page, 100 ); ?>>100</option>
		</select>
	</form>
	</div>
</div>
<?php if ( current_user_can( 'fb_manage_database' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability. ?>
	<?php
	$export_base = add_query_arg(
		array(
			'action' => 'fbm_export_entries',
		),
		$base_url
	);
	$export_url  = wp_nonce_url( $export_base, 'fbm_db_export', '_wpnonce' );
	?>
<p><a class="button" href="<?php echo esc_url( $export_url ); ?>"><?php esc_html_e( 'Export CSV', 'foodbank-manager' ); ?></a></p>
<?php endif; ?>
</div>
