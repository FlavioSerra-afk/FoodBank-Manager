<?php
namespace FoodBankManager\Emails;

if (! defined('ABSPATH')) {
    exit;
}
?>
<p><?php \esc_html_e('Thank you for your application.', 'foodbank-manager'); ?></p>
<?php if (! empty( $qr_code_url )) : ?>
    <?php if ( strpos( $qr_code_url, 'data:image' ) === 0 ) : ?>
        <p><img src="<?php echo esc_url( $qr_code_url ); ?>" alt="<?php \esc_attr_e('QR Code', 'foodbank-manager'); ?>" /></p>
    <?php else : ?>
        <p><?php echo esc_html( $qr_code_url ); ?></p>
    <?php endif; ?>
<?php endif; ?>
