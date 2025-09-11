<?php // phpcs:ignoreFile
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
?>
<div class="wrap fbm-admin">
<?php \FBM\Core\Trace::mark( 'admin:dashboard' ); ?>
<?php
$applications_total    = (int) ( $applications_total ?? 0 );
$applications_today    = (int) ( $applications_today ?? 0 );
$summary               = $summary ?? array(
	'today' => 0,
	'week'  => 0,
	'month' => 0,
);
$events_active         = (int) ( $events_active ?? 0 );
$tickets_issued        = (int) ( $tickets_issued ?? 0 );
$tickets_issued_delta  = (int) ( $tickets_issued_delta ?? 0 );
$tickets_revoked       = (int) ( $tickets_revoked ?? 0 );
$tickets_revoked_delta = (int) ( $tickets_revoked_delta ?? 0 );
$mail_failures_7d      = (int) ( $mail_failures_7d ?? 0 );
?>
<h1><?php esc_html_e( 'Dashboard', 'foodbank-manager' ); ?></h1>
<div class="fbm-grid">
        <div class="fbm-card--glass fbm-tile" tabindex="0" data-testid="fbm-dashboard-tile-applications-total" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: total applications count */ __( 'Total applications: %s', 'foodbank-manager' ), number_format_i18n( $applications_total ) ) ); ?>">
		<span class="dashicons dashicons-admin-users" aria-hidden="true"></span>
		<div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $applications_total ) ); ?></div>
		<div class="fbm-tile-label"><?php esc_html_e( 'Total applications', 'foodbank-manager' ); ?></div>
	</div>
        <div class="fbm-card--glass fbm-tile" tabindex="0" data-testid="fbm-dashboard-tile-applications-today" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: new applications today */ __( 'New today: %s', 'foodbank-manager' ), number_format_i18n( $applications_today ) ) ); ?>">
		<span class="dashicons dashicons-clock" aria-hidden="true"></span>
		<div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $applications_today ) ); ?></div>
		<div class="fbm-tile-label"><?php esc_html_e( 'New today', 'foodbank-manager' ); ?></div>
	</div>
        <div class="fbm-card--glass fbm-tile" tabindex="0" data-testid="fbm-dashboard-tile-checkins-today" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: check-ins today */ __( 'Check-ins today: %s', 'foodbank-manager' ), number_format_i18n( $summary['today'] ?? 0 ) ) ); ?>">
		<span class="dashicons dashicons-yes" aria-hidden="true"></span>
		<div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $summary['today'] ?? 0 ) ); ?></div>
		<div class="fbm-tile-label"><?php esc_html_e( 'Check-ins today', 'foodbank-manager' ); ?></div>
	</div>
        <div class="fbm-card--glass fbm-tile" tabindex="0" data-testid="fbm-dashboard-tile-checkins-week" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: check-ins this week */ __( 'Check-ins this week: %s', 'foodbank-manager' ), number_format_i18n( $summary['week'] ?? 0 ) ) ); ?>">
		<span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span>
		<div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $summary['week'] ?? 0 ) ); ?></div>
		<div class="fbm-tile-label"><?php esc_html_e( 'Check-ins this week', 'foodbank-manager' ); ?></div>
	</div>
        <div class="fbm-card--glass fbm-tile" tabindex="0" data-testid="fbm-dashboard-tile-checkins-month" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: check-ins this month */ __( 'Check-ins this month: %s', 'foodbank-manager' ), number_format_i18n( $summary['month'] ?? 0 ) ) ); ?>">
		<span class="dashicons dashicons-calendar" aria-hidden="true"></span>
		<div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $summary['month'] ?? 0 ) ); ?></div>
		<div class="fbm-tile-label"><?php esc_html_e( 'Check-ins this month', 'foodbank-manager' ); ?></div>
	</div>
        <div class="fbm-card--glass fbm-tile" tabindex="0" data-testid="fbm-dashboard-tile-events-active" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: number of active events */ __( 'Active events: %s', 'foodbank-manager' ), number_format_i18n( $events_active ) ) ); ?>">
		<span class="dashicons dashicons-megaphone" aria-hidden="true"></span>
		<div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $events_active ) ); ?></div>
		<div class="fbm-tile-label"><?php esc_html_e( 'Active events', 'foodbank-manager' ); ?></div>
	</div>
        <div class="fbm-card--glass fbm-tile" tabindex="0" data-testid="fbm-dashboard-tile-tickets-issued" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: number of tickets issued */ __( 'Tickets issued: %s', 'foodbank-manager' ), number_format_i18n( $tickets_issued ) ) ); ?>">
		<span class="dashicons dashicons-tickets-alt" aria-hidden="true"></span>
		<div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $tickets_issued ) ); ?></div>
		<div class="fbm-tile-label"><?php esc_html_e( 'Tickets issued', 'foodbank-manager' ); ?></div>
		<span class="fbm-delta fbm-delta--neutral"><?php echo esc_html( $tickets_issued_delta ); ?></span>
	</div>
        <div class="fbm-card--glass fbm-tile" tabindex="0" data-testid="fbm-dashboard-tile-tickets-revoked" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: number of tickets revoked */ __( 'Tickets revoked: %s', 'foodbank-manager' ), number_format_i18n( $tickets_revoked ) ) ); ?>">
		<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
		<div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $tickets_revoked ) ); ?></div>
		<div class="fbm-tile-label"><?php esc_html_e( 'Tickets revoked', 'foodbank-manager' ); ?></div>
		<span class="fbm-delta fbm-delta--neutral"><?php echo esc_html( $tickets_revoked_delta ); ?></span>
	</div>
        <div class="fbm-card--glass fbm-tile" tabindex="0" data-testid="fbm-dashboard-tile-mail-failures" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: email failures in last 7 days */ __( 'Mail failures (last 7d): %s', 'foodbank-manager' ), number_format_i18n( $mail_failures_7d ) ) ); ?>">
		<span class="dashicons dashicons-email-alt" aria-hidden="true"></span>
		<div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $mail_failures_7d ) ); ?></div>
		<div class="fbm-tile-label"><?php esc_html_e( 'Mail failures (last 7d)', 'foodbank-manager' ); ?></div>
	</div>
</div>
<div class="fbm-card--glass fbm-sparkline" data-testid="fbm-dashboard-sparkline">
	<svg width="100%" height="40" xmlns="http://www.w3.org/2000/svg"></svg>
</div>
<div class="fbm-shortcuts">
	<a class="fbm-button--glass" href="<?php echo esc_url( admin_url( 'admin.php?page=fbm_attendance&tab=scan' ) ); ?>"><?php esc_html_e( 'Scan now', 'foodbank-manager' ); ?></a>
	<a class="fbm-button--glass" href="<?php echo esc_url( admin_url( 'admin.php?page=fbm_attendance&tab=entry' ) ); ?>"><?php esc_html_e( 'Issue tickets', 'foodbank-manager' ); ?></a>
	<a class="fbm-button--glass" href="<?php echo esc_url( admin_url( 'admin.php?page=fbm_reports' ) ); ?>"><?php esc_html_e( 'Export attendance', 'foodbank-manager' ); ?></a>
	<a class="fbm-button--glass" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=fb_form' ) ); ?>"><?php esc_html_e( 'New form', 'foodbank-manager' ); ?></a>
</div>
</div>
