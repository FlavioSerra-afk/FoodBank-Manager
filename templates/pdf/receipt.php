<?php // phpcs:ignoreFile
/**
 * Receipt body template.
 *
 * @var array $entry
 * @var array $brand
 */
?>
<h1><?php esc_html_e( 'Receipt', 'foodbank-manager' ); ?></h1>
<?php if ( ! empty( $entry['name'] ) ) : ?>
<p class="name"><?php echo esc_html( (string) $entry['name'] ); ?></p>
<?php endif; ?>
<?php if ( ! empty( $entry['email'] ) ) : ?>
<p class="email"><?php echo esc_html( (string) $entry['email'] ); ?></p>
<?php endif; ?>
<?php if ( ! empty( $entry['phone'] ) ) : ?>
<p class="phone"><?php echo esc_html( (string) $entry['phone'] ); ?></p>
<?php endif; ?>
<?php if ( ! empty( $entry['address'] ) ) : ?>
<p class="address"><?php echo esc_html( (string) $entry['address'] ); ?></p>
<?php endif; ?>
