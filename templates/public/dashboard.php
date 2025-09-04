<?php // phpcs:ignoreFile
/**
 * Dashboard cards template.
 *
 * @var array<string,mixed> $counts
 * @var string               $updated
 * @var string               $period_attr
 *
 * @package FoodBankManager
 */
?>
<div class="fbm-dashboard">
<div class="fbm-dashboard-grid">
<div class="fbm-card">
<div class="fbm-card-value"><?php echo esc_html( (string) $counts['present'] ); ?></div>
<div class="fbm-card-label"><?php esc_html_e( 'Total Check-ins', 'foodbank-manager' ); ?></div>
<div class="fbm-card-delta">–</div>
</div>
<div class="fbm-card">
<div class="fbm-card-value"><?php echo esc_html( (string) $counts['households'] ); ?></div>
<div class="fbm-card-label"><?php esc_html_e( 'Unique Households Served', 'foodbank-manager' ); ?></div>
<div class="fbm-card-delta">–</div>
</div>
<div class="fbm-card">
<div class="fbm-card-value"><?php echo esc_html( (string) $counts['noshows'] ); ?></div>
<div class="fbm-card-label"><?php esc_html_e( 'No-shows', 'foodbank-manager' ); ?></div>
<div class="fbm-card-delta">–</div>
</div>
<?php
$in    = (int) ( $counts['types']['in_person'] ?? 0 );
$del   = (int) ( $counts['types']['delivery'] ?? 0 );
$tot   = $in + $del;
$p_del = $tot ? round( $del / $tot * 100 ) : 0;
$p_in  = $tot ? 100 - $p_del : 0;
?>
<div class="fbm-card">
<div class="fbm-card-value"><?php echo esc_html( $p_del . '% / ' . $p_in . '%' ); ?></div>
<div class="fbm-card-label"><?php printf( esc_html__( '%1$s deliveries / %2$s in-person', 'foodbank-manager' ), esc_html( (string) $del ), esc_html( (string) $in ) ); ?></div>
<div class="fbm-card-delta">–</div>
</div>
<?php if ( (int) $counts['voided'] > 0 ) : ?>
<div class="fbm-card">
<div class="fbm-card-value"><?php echo esc_html( (string) $counts['voided'] ); ?></div>
<div class="fbm-card-label"><?php esc_html_e( 'Voided', 'foodbank-manager' ); ?></div>
<div class="fbm-card-delta">–</div>
</div>
<?php endif; ?>
</div>
<div class="fbm-dashboard-meta">
<span class="fbm-updated"><?php printf( esc_html__( 'Last updated %s', 'foodbank-manager' ), esc_html( $updated ) ); ?></span>
<a class="fbm-refresh" href="<?php echo esc_url( add_query_arg( array() ) ); ?>"><?php esc_html_e( 'Refresh', 'foodbank-manager' ); ?></a>
</div>
</div>
