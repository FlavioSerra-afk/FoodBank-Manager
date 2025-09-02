<?php
namespace FoodBankManager\Emails;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<p><?php printf( esc_html__( 'Hi %s,', 'foodbank-manager' ), esc_html( $first_name ) ); ?></p>
<p><?php printf( esc_html__( 'We received your application on %s. Your reference is FBM-%d.', 'foodbank-manager' ), esc_html( $created_at ), (int) $application_id ); ?></p>
<?php if ( ! empty( $qr_code_url ) ) : ?>
    <?php if ( strpos( $qr_code_url, 'data:image' ) === 0 ) : ?>
        <p><img src="<?php echo esc_url( $qr_code_url ); ?>" alt="<?php esc_attr_e( 'QR Code', 'foodbank-manager' ); ?>" /></p>
    <?php else : ?>
        <p><?php echo esc_html( $qr_code_url ); ?></p>
    <?php endif; ?>
<?php endif; ?>
<p><?php esc_html_e( 'Summary:', 'foodbank-manager' ); ?></p>
<?php echo $summary_table; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
