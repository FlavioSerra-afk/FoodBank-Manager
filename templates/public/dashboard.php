<?php // phpcs:ignoreFile
/**
 * Dashboard cards template.
 *
 * @var array<string,mixed>       $counts
 * @var array<int,int>            $series_attr
 * @var array<string,int|null>    $deltas_attr
 * @var string                    $updated
 * @var string                    $period_attr
 *
 * @package FoodBankManager
 */
?>
<div class="fbm-dashboard">
<div class="fbm-dashboard-grid">
<div class="fbm-card">
<div class="fbm-card-value"><?php echo esc_html( number_format_i18n( (int) $counts['present'] ) ); ?></div>
<div class="fbm-card-label"><?php esc_html_e( 'Total Check-ins', 'foodbank-manager' ); ?></div>
<?php $d = $deltas_attr['present'] ?? null; $cls = is_int( $d ) ? ( $d > 0 ? ' fbm-up' : ( $d < 0 ? ' fbm-down' : '' ) ) : ''; ?>
<div class="fbm-card-delta<?php echo esc_attr( $cls ); ?>"><?php echo esc_html( is_int( $d ) ? sprintf( '%+d%%', $d ) : '–' ); ?></div>
</div>
<div class="fbm-card">
<div class="fbm-card-value"><?php echo esc_html( number_format_i18n( (int) $counts['households'] ) ); ?></div>
<div class="fbm-card-label"><?php esc_html_e( 'Unique Households Served', 'foodbank-manager' ); ?></div>
<?php $d = $deltas_attr['households'] ?? null; $cls = is_int( $d ) ? ( $d > 0 ? ' fbm-up' : ( $d < 0 ? ' fbm-down' : '' ) ) : ''; ?>
<div class="fbm-card-delta<?php echo esc_attr( $cls ); ?>"><?php echo esc_html( is_int( $d ) ? sprintf( '%+d%%', $d ) : '–' ); ?></div>
</div>
<div class="fbm-card">
<div class="fbm-card-value"><?php echo esc_html( number_format_i18n( (int) $counts['no_shows'] ) ); ?></div>
<div class="fbm-card-label"><?php esc_html_e( 'No-shows', 'foodbank-manager' ); ?></div>
<?php $d = $deltas_attr['no_shows'] ?? null; $cls = is_int( $d ) ? ( $d > 0 ? ' fbm-up' : ( $d < 0 ? ' fbm-down' : '' ) ) : ''; ?>
<div class="fbm-card-delta<?php echo esc_attr( $cls ); ?>"><?php echo esc_html( is_int( $d ) ? sprintf( '%+d%%', $d ) : '–' ); ?></div>
</div>
<?php
$in  = (int) ( $counts['in_person'] ?? 0 );
$del = (int) ( $counts['delivery'] ?? 0 );
$tot = $in + $del;
$p_del = $tot ? round( $del / $tot * 100 ) : 0;
$p_in  = $tot ? 100 - $p_del : 0;
$d = $deltas_attr['delivery'] ?? null;
$cls = is_int( $d ) ? ( $d > 0 ? ' fbm-up' : ( $d < 0 ? ' fbm-down' : '' ) ) : '';
?>
<div class="fbm-card">
<div class="fbm-card-value"><?php echo esc_html( $p_del . '% / ' . $p_in . '%' ); ?></div>
<div class="fbm-card-label"><?php printf( esc_html__( '%1$s deliveries / %2$s in-person', 'foodbank-manager' ), esc_html( number_format_i18n( $del ) ), esc_html( number_format_i18n( $in ) ) ); ?></div>
<div class="fbm-card-delta<?php echo esc_attr( $cls ); ?>"><?php echo esc_html( is_int( $d ) ? sprintf( '%+d%%', $d ) : '–' ); ?></div>
</div>
<?php if ( (int) $counts['voided'] > 0 ) : ?>
<?php $d = $deltas_attr['voided'] ?? null; $cls = is_int( $d ) ? ( $d > 0 ? ' fbm-up' : ( $d < 0 ? ' fbm-down' : '' ) ) : ''; ?>
<div class="fbm-card">
<div class="fbm-card-value"><?php echo esc_html( number_format_i18n( (int) $counts['voided'] ) ); ?></div>
<div class="fbm-card-label"><?php esc_html_e( 'Voided', 'foodbank-manager' ); ?></div>
<div class="fbm-card-delta<?php echo esc_attr( $cls ); ?>"><?php echo esc_html( is_int( $d ) ? sprintf( '%+d%%', $d ) : '–' ); ?></div>
</div>
<?php endif; ?>
</div>
<?php if ( ! empty( $series_attr ) ) : ?>
<div class="fbm-sparkline" role="img" aria-label="<?php printf( esc_attr__( 'Daily check-ins for last %d days', 'foodbank-manager' ), count( $series_attr ) ); ?>">
<?php
        $max   = max( $series_attr );
        $min   = min( $series_attr );
        $count = count( $series_attr );
        $points = array();
        for ( $i = 0; $i < $count; $i++ ) {
                $x = $count > 1 ? ( $i / ( $count - 1 ) ) * 100 : 0;
                $y = $max > $min ? 30 - ( ( $series_attr[ $i ] - $min ) / ( $max - $min ) * 30 ) : 30;
                $points[] = $x . ',' . $y;
        }
        $last_x = $count > 1 ? 100 : 0;
        $last_y = $max > $min ? 30 - ( ( end( $series_attr ) - $min ) / ( $max - $min ) * 30 ) : 30;
?>
<svg viewBox="0 0 100 30" xmlns="http://www.w3.org/2000/svg"><polyline fill="none" stroke="currentColor" stroke-width="2" points="<?php echo esc_attr( implode( ' ', $points ) ); ?>" /><circle cx="<?php echo esc_attr( (string) $last_x ); ?>" cy="<?php echo esc_attr( (string) $last_y ); ?>" r="2" /></svg>
</div>
<?php endif; ?>
<div class="fbm-dashboard-meta">
<span class="fbm-updated"><?php printf( esc_html__( 'Last updated %s', 'foodbank-manager' ), esc_html( $updated ) ); ?></span>
<a class="fbm-refresh" href="<?php echo esc_url( add_query_arg( array() ) ); ?>"><?php esc_html_e( 'Refresh', 'foodbank-manager' ); ?></a>
</div>
</div>
