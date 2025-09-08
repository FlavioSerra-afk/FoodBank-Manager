<?php
/**
 * Dashboard cards template.
 *
 * @var array<string,mixed>       $counts
 * @var array<int,int>            $series_attr
 * @var array<string,int|null>    $deltas_attr
 * @var string                    $updated
 * @var string                    $period_attr
 * @var array{event:string,type:string,policy_only:bool} $filters_attr
 * @var string                    $csv_url_attr
 *
 * @package FoodBankManager
 */

$present    = (int) ( $counts['present'] ?? 0 );
$households = (int) ( $counts['households'] ?? 0 );
$no_shows   = (int) ( $counts['no_shows'] ?? 0 );
$in         = (int) ( $counts['in_person'] ?? 0 );
$del        = (int) ( $counts['delivery'] ?? 0 );
$voided     = (int) ( $counts['voided'] ?? 0 );
$has_totals = ( $present + $households + $no_shows + $in + $del + $voided ) > 0;
$tot        = $in + $del;
$p_del      = $tot ? round( $del / $tot * 100 ) : 0;
$p_in       = $tot ? 100 - $p_del : 0;
?>
<div class="fbm-dashboard fbm-loading" aria-busy="true">
<?php if ( current_user_can( 'manage_options' ) ) : ?>
<div class="fbm-copy-shortcode"><code>[fbm_dashboard]</code></div>
<?php endif; ?>
<form class="fbm-filter-row" method="get" aria-label="<?php esc_attr_e( 'Dashboard filters', 'foodbank-manager' ); ?>">
	<div class="fbm-field">
		<label for="fbm_type"><?php esc_html_e( 'Type', 'foodbank-manager' ); ?></label>
		<select id="fbm_type" name="fbm_type">
			<option value="all"<?php selected( $filters_attr['type'], 'all' ); ?>><?php esc_html_e( 'All', 'foodbank-manager' ); ?></option>
			<option value="in_person"<?php selected( $filters_attr['type'], 'in_person' ); ?>><?php esc_html_e( 'In person', 'foodbank-manager' ); ?></option>
			<option value="delivery"<?php selected( $filters_attr['type'], 'delivery' ); ?>><?php esc_html_e( 'Delivery', 'foodbank-manager' ); ?></option>
		</select>
	</div>
	<div class="fbm-field">
		<label for="fbm_event"><?php esc_html_e( 'Event', 'foodbank-manager' ); ?></label>
		<input id="fbm_event" type="text" name="fbm_event" value="<?php echo esc_attr( $filters_attr['event'] ); ?>" />
	</div>
	<div class="fbm-field">
		<input id="fbm_policy_only" type="checkbox" name="fbm_policy_only" value="1" <?php checked( $filters_attr['policy_only'] ); ?> />
		<label for="fbm_policy_only"><?php esc_html_e( 'Policy only', 'foodbank-manager' ); ?></label>
	</div>
	<button type="submit"><?php esc_html_e( 'Apply', 'foodbank-manager' ); ?></button>
	<a class="fbm-download" href="<?php echo esc_url( $csv_url_attr ); ?>"><?php esc_html_e( 'Download CSV', 'foodbank-manager' ); ?></a>
</form>
<div class="fbm-results-count" data-testid="fbm-summary" aria-live="polite">
<?php
printf(
		/* translators: %s: result count. */
	esc_html__( '%s results', 'foodbank-manager' ),
	esc_html( number_format_i18n( $present ) )
);
?>
</div>
<div class="fbm-dashboard-grid">
<?php if ( $has_totals ) : ?>
<div class="fbm-card">
<div class="fbm-card-value"><?php echo esc_html( number_format_i18n( $present ) ); ?></div>
<div class="fbm-card-label"><?php esc_html_e( 'Total Check-ins', 'foodbank-manager' ); ?></div>
	<?php
	$d   = $deltas_attr['present'] ?? null;
	$cls = is_int( $d ) ? ( $d > 0 ? ' fbm-up' : ( $d < 0 ? ' fbm-down' : '' ) ) : '';
	?>
<div class="fbm-card-delta<?php echo esc_attr( $cls ); ?>">
	<?php echo esc_html( is_int( $d ) ? sprintf( '%+d%%', $d ) : '–' ); ?>
</div>
</div>
<div class="fbm-card">
<div class="fbm-card-value"><?php echo esc_html( number_format_i18n( $households ) ); ?></div>
<div class="fbm-card-label"><?php esc_html_e( 'Unique Households Served', 'foodbank-manager' ); ?></div>
	<?php
	$d   = $deltas_attr['households'] ?? null;
	$cls = is_int( $d ) ? ( $d > 0 ? ' fbm-up' : ( $d < 0 ? ' fbm-down' : '' ) ) : '';
	?>
<div class="fbm-card-delta<?php echo esc_attr( $cls ); ?>">
	<?php echo esc_html( is_int( $d ) ? sprintf( '%+d%%', $d ) : '–' ); ?>
</div>
</div>
<div class="fbm-card">
<div class="fbm-card-value"><?php echo esc_html( number_format_i18n( $no_shows ) ); ?></div>
<div class="fbm-card-label"><?php esc_html_e( 'No-shows', 'foodbank-manager' ); ?></div>
	<?php
	$d   = $deltas_attr['no_shows'] ?? null;
	$cls = is_int( $d ) ? ( $d > 0 ? ' fbm-up' : ( $d < 0 ? ' fbm-down' : '' ) ) : '';
	?>
<div class="fbm-card-delta<?php echo esc_attr( $cls ); ?>">
	<?php echo esc_html( is_int( $d ) ? sprintf( '%+d%%', $d ) : '–' ); ?>
</div>
</div>
	<?php
	$d   = $deltas_attr['delivery'] ?? null;
	$cls = is_int( $d ) ? ( $d > 0 ? ' fbm-up' : ( $d < 0 ? ' fbm-down' : '' ) ) : '';
	?>
	<?php if ( $tot > 0 ) : ?>
<div class="fbm-card">
<div class="fbm-card-value"><?php echo esc_html( $p_del ); ?>% / <?php echo esc_html( $p_in ); ?>%</div>
<div class="fbm-card-label">
		<?php
		printf(
		/* translators: 1: number of deliveries, 2: number of in-person check-ins. */
			esc_html__( '%1$s deliveries / %2$s in-person', 'foodbank-manager' ),
			esc_html( number_format_i18n( $del ) ),
			esc_html( number_format_i18n( $in ) )
		);
		?>
</div>
<div class="fbm-card-delta<?php echo esc_attr( $cls ); ?>">
		<?php echo esc_html( is_int( $d ) ? sprintf( '%+d%%', $d ) : '–' ); ?>
</div>
</div>
<?php else : ?>
<div class="fbm-card fbm-empty"><?php esc_html_e( 'No data by type.', 'foodbank-manager' ); ?></div>
<?php endif; ?>
	<?php if ( $voided > 0 ) : ?>
		<?php
		$d   = $deltas_attr['voided'] ?? null;
		$cls = is_int( $d ) ? ( $d > 0 ? ' fbm-up' : ( $d < 0 ? ' fbm-down' : '' ) ) : '';
		?>
<div class="fbm-card">
<div class="fbm-card-value"><?php echo esc_html( number_format_i18n( $voided ) ); ?></div>
<div class="fbm-card-label"><?php esc_html_e( 'Voided', 'foodbank-manager' ); ?></div>
<div class="fbm-card-delta<?php echo esc_attr( $cls ); ?>">
		<?php echo esc_html( is_int( $d ) ? sprintf( '%+d%%', $d ) : '–' ); ?>
</div>
</div>
<?php endif; ?>
<?php else : ?>
<div class="fbm-card fbm-empty"><?php esc_html_e( 'No data for selected filters.', 'foodbank-manager' ); ?></div>
<?php endif; ?>
</div>
<?php if ( ! empty( $series_attr ) ) : ?>
<div class="fbm-sparkline" role="img" aria-label="
	<?php
	/* translators: %d: number of days. */
	printf( esc_attr__( 'Daily check-ins for last %d days', 'foodbank-manager' ), count( $series_attr ) );
	?>
">
	<?php
		$max    = max( $series_attr );
		$min    = min( $series_attr );
		$count  = count( $series_attr );
		$points = array();
	for ( $i = 0; $i < $count; $i++ ) {
			$x        = $count > 1 ? ( $i / ( $count - 1 ) ) * 100 : 0;
			$y        = $max > $min ? 30 - ( ( $series_attr[ $i ] - $min ) / ( $max - $min ) * 30 ) : 30;
			$points[] = $x . ',' . $y;
	}
		$last_x = $count > 1 ? 100 : 0;
		$last_y = $max > $min ? 30 - ( ( end( $series_attr ) - $min ) / ( $max - $min ) * 30 ) : 30;
	?>
<svg viewBox="0 0 100 30" xmlns="http://www.w3.org/2000/svg">
		<polyline fill="none" stroke="currentColor" stroke-width="2" points="<?php echo esc_attr( implode( ' ', $points ) ); ?>" />
		<circle cx="<?php echo esc_attr( (string) $last_x ); ?>" cy="<?php echo esc_attr( (string) $last_y ); ?>" r="2" />
</svg>
</div>
<?php else : ?>
<div class="fbm-empty"><?php esc_html_e( 'No trend data.', 'foodbank-manager' ); ?></div>
<?php endif; ?>
<div class="fbm-dashboard-meta">
<span class="fbm-updated">
<?php
/* translators: %s: last updated time. */
printf( esc_html__( 'Last updated %s', 'foodbank-manager' ), esc_html( $updated ) );
?>
</span>
<a class="fbm-refresh" href="<?php echo esc_url( add_query_arg( array() ) ); ?>"><?php esc_html_e( 'Refresh', 'foodbank-manager' ); ?></a>
</div>
</div>
