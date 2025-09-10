<?php
/**
 * Public form success template.
 *
 * @package FoodBankManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="fbm-public"><div class="fbm-form-success fbm-card fbm-card--glass">
        <p><?php echo esc_html__( 'Thank you. Your reference ID is', 'foodbank-manager' ); ?>: <?php echo esc_html( $reference ); ?></p>
        <?php if ( ! empty( $summary ) ) : ?>
        <ul>
                <?php foreach ( $summary as $label => $value ) : ?>
                <li><?php echo esc_html( $label ); ?>: <?php echo esc_html( $value ); ?></li>
                <?php endforeach; ?>
        </ul>
        <?php endif; ?>
</div></div>
