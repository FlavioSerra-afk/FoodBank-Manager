<?php // phpcs:ignoreFile
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
?>
<?php echo '<div id="fbm-ui" class="fbm-scope fbm-app">'; ?>
<div class="wrap fbm-admin">
<?php \FBM\Core\Trace::mark( 'admin:reports' ); ?>
<h1><?php esc_html_e( 'Reports', 'foodbank-manager' ); ?></h1>
<form method="get" class="fbm-filters">
	<input type="hidden" name="page" value="fbm_reports" />
	<label><?php esc_html_e( 'From', 'foodbank-manager' ); ?>
		<input type="date" name="from" value="<?php echo esc_attr( $filters['from'] ); ?>" />
	</label>
	<label><?php esc_html_e( 'To', 'foodbank-manager' ); ?>
		<input type="date" name="to" value="<?php echo esc_attr( $filters['to'] ); ?>" />
	</label>
	<label><?php esc_html_e( 'Method', 'foodbank-manager' ); ?>
		<select name="method">
			<option value="" <?php selected( $filters['method'], '' ); ?>><?php esc_html_e( 'All', 'foodbank-manager' ); ?></option>
			<option value="qr" <?php selected( $filters['method'], 'qr' ); ?>><?php esc_html_e( 'QR', 'foodbank-manager' ); ?></option>
			<option value="manual" <?php selected( $filters['method'], 'manual' ); ?>><?php esc_html_e( 'Manual', 'foodbank-manager' ); ?></option>
		</select>
	</label>
	<button class="button"><?php esc_html_e( 'Filter', 'foodbank-manager' ); ?></button>
</form>
<div data-testid="fbm-reports-summary">
	<strong><?php esc_html_e( 'Today', 'foodbank-manager' ); ?>:</strong> <?php echo esc_html( (string) $summary['today'] ); ?>
	<strong><?php esc_html_e( 'Week', 'foodbank-manager' ); ?>:</strong> <?php echo esc_html( (string) $summary['week'] ); ?>
	<strong><?php esc_html_e( 'Month', 'foodbank-manager' ); ?>:</strong> <?php echo esc_html( (string) $summary['month'] ); ?>
</div>
<div data-testid="fbm-reports-unique">
	<strong><?php esc_html_e( 'Unique today', 'foodbank-manager' ); ?>:</strong> <?php echo esc_html( (string) $summary['unique_today'] ); ?>
	<strong><?php esc_html_e( 'Unique week', 'foodbank-manager' ); ?>:</strong> <?php echo esc_html( (string) $summary['unique_week'] ); ?>
	<strong><?php esc_html_e( 'Unique month', 'foodbank-manager' ); ?>:</strong> <?php echo esc_html( (string) $summary['unique_month'] ); ?>
</div>
<table class="widefat" data-testid="fbm-reports-daily-table">
	<thead><tr><th><?php esc_html_e( 'Date', 'foodbank-manager' ); ?></th><th><?php esc_html_e( 'Total', 'foodbank-manager' ); ?></th><th><?php esc_html_e( 'Unique', 'foodbank-manager' ); ?></th></tr></thead>
	<tbody>
	<?php foreach ( $daily['days'] as $d ) : ?>
		<tr><td><?php echo esc_html( $d['date'] ); ?></td><td><?php echo esc_html( (string) $d['total'] ); ?></td><td><?php echo esc_html( (string) $d['unique'] ); ?></td></tr>
	<?php endforeach; ?>
	</tbody>
</table>
<p class="fbm-export-buttons">
        <a class="button" href="<?php echo esc_url( $csv_url ); ?>">CSV</a>
        <a class="button" href="<?php echo esc_url( $xlsx_url ); ?>">XLSX</a>
        <a class="button" href="<?php echo esc_url( $pdf_url ); ?>">PDF</a>
</p>
</div>
<?php echo '</div>'; ?>
