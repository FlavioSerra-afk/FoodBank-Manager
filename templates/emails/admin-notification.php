<?php
/**
 * Admin notification email template.
 *
 * @package FoodBankManager
 * @since 0.1.1
 */

namespace FoodBankManager\Emails;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p><?php esc_html_e( 'New application received.', 'foodbank-manager' ); ?></p>
<?php // translators: %d: Application ID. ?>
<p><?php printf( esc_html__( 'Reference: FBM-%d', 'foodbank-manager' ), (int) $application_id ); ?></p>
<?php // translators: %s: Submission time. ?>
<p><?php printf( esc_html__( 'Submitted at %s', 'foodbank-manager' ), esc_html( $created_at ) ); ?></p>
<p><?php esc_html_e( 'Entry URL:', 'foodbank-manager' ); ?> <?php echo esc_url( $entry_url ); ?></p>
<p><?php esc_html_e( 'Summary:', 'foodbank-manager' ); ?></p>
<?php
$safe_summary = wp_kses_post( $summary_table );
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $safe_summary is sanitized via wp_kses_post().
echo $safe_summary;
?>
