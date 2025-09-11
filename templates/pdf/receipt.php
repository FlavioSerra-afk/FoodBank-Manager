<?php // phpcs:ignoreFile
/**
 * Receipt body template.
 *
 * @var array $entry
 * @var array $brand
 */
?>
<h1><?php esc_html_e( 'Receipt', 'foodbank-manager' ); ?></h1>
<?php if ( ! empty( $entry['donor'] ) ) : ?>
<p><?php echo esc_html( $entry['donor'] ); ?></p>
<?php endif; ?>
<?php if ( ! empty( $entry['amount'] ) ) : ?>
<p><?php echo esc_html( (string) $entry['amount'] ); ?></p>
<?php endif; ?>
