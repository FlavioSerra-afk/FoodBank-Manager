<?php
/**
 * Members admin template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

use function do_action;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_url;
use function is_array;
use function trim;

$members = array();
$notices = array();

if ( isset( $data['members'] ) && is_array( $data['members'] ) ) {
		$members = $data['members'];
}

if ( isset( $data['notices'] ) && is_array( $data['notices'] ) ) {
		$notices = $data['notices'];
}
?>
<div class="wrap">
		<h1 class="wp-heading-inline">
				<?php esc_html_e( 'Food Bank Members', 'foodbank-manager' ); ?>
		</h1>

		<?php if ( ! empty( $notices ) ) : ?>
			<?php foreach ( $notices as $notice ) : ?>
				<?php
				$notice_type = isset( $notice['type'] ) ? (string) $notice['type'] : 'info';
				$message     = isset( $notice['message'] ) ? (string) $notice['message'] : '';

				if ( '' === $message ) {
					continue;
				}
				?>
<div class="notice notice-<?php echo esc_attr( $notice_type ); ?> is-dismissible">
<p><?php echo esc_html( $message ); ?></p>
</div>
<?php endforeach; ?>
		<?php endif; ?>

		<?php do_action( 'fbm_members_page_before_table', $members ); ?>

		<?php if ( empty( $members ) ) : ?>
				<p><?php esc_html_e( 'No members found.', 'foodbank-manager' ); ?></p>
		<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
						<thead>
								<tr>
										<th scope="col"><?php esc_html_e( 'Reference', 'foodbank-manager' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Name', 'foodbank-manager' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Email', 'foodbank-manager' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Status', 'foodbank-manager' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Activated', 'foodbank-manager' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Actions', 'foodbank-manager' ); ?></th>
								</tr>
						</thead>
						<tbody>
			<?php foreach ( $members as $member ) : ?>
				<?php
				$reference     = isset( $member['member_reference'] ) ? (string) $member['member_reference'] : '';
				$first_name    = isset( $member['first_name'] ) ? (string) $member['first_name'] : '';
				$last_init     = isset( $member['last_initial'] ) ? (string) $member['last_initial'] : '';
				$email         = isset( $member['email'] ) ? (string) $member['email'] : '';
				$member_status = isset( $member['status'] ) ? (string) $member['status'] : '';
				$activated     = isset( $member['activated_at'] ) && null !== $member['activated_at'] ? (string) $member['activated_at'] : '';
				$resend_url    = isset( $member['resend_url'] ) ? (string) $member['resend_url'] : '';
				$revoke_url    = isset( $member['revoke_url'] ) ? (string) $member['revoke_url'] : '';
				$name          = trim( $first_name . ' ' . $last_init );
				?>
<tr>
<td><?php echo esc_html( $reference ); ?></td>
<td><?php echo esc_html( $name ); ?></td>
<td><?php echo esc_html( $email ); ?></td>
<td><?php echo esc_html( $member_status ); ?></td>
												<td><?php echo esc_html( $activated ); ?></td>
												<td>
														<div class="row-actions">
																<span class="resend">
																		<a href="<?php echo esc_url( $resend_url ); ?>">
																				<?php esc_html_e( 'Resend QR', 'foodbank-manager' ); ?>
																		</a>
																</span>
																|
																<span class="revoke">
																		<a href="<?php echo esc_url( $revoke_url ); ?>">
																				<?php esc_html_e( 'Revoke/Regenerate', 'foodbank-manager' ); ?>
																		</a>
																</span>
														</div>
												</td>
										</tr>
								<?php endforeach; ?>
						</tbody>
				</table>
		<?php endif; ?>

		<?php do_action( 'fbm_members_page_after_table', $members ); ?>
</div>
