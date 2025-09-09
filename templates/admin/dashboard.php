<?php
if (!defined('ABSPATH')) { exit; }
?>
<div class="wrap fbm-admin">
<?php \FBM\Core\Trace::mark('admin:dashboard'); ?>
<?php
$registrations_total = (int) ($registrations_total ?? 0);
$summary = $summary ?? array('today'=>0,'week'=>0,'month'=>0);
$recent = (int) ($recent ?? 0);
$events_active = (int) ($events_active ?? 0);
$forms_7d = (int) ($forms_7d ?? 0);
$jobs_pending = (int) ($jobs_pending ?? 0);
?>
<h1><?php esc_html_e('Dashboard', 'foodbank-manager'); ?></h1>
<div class="fbm-grid">
    <div class="fbm-tile fbm-card--glass" tabindex="0" data-testid="fbm-dashboard-tile-registrations" aria-label="<?php echo esc_attr( sprintf( __( 'Total registrations: %s', 'foodbank-manager' ), number_format_i18n( $registrations_total ) ) ); ?>">
        <span class="dashicons dashicons-admin-users" aria-hidden="true"></span>
        <div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $registrations_total ) ); ?></div>
        <div class="fbm-tile-label"><?php esc_html_e('Total registrations', 'foodbank-manager'); ?></div>
    </div>
    <div class="fbm-tile fbm-card--glass" tabindex="0" data-testid="fbm-dashboard-tile-today" aria-label="<?php echo esc_attr( sprintf( __( 'Check-ins Today: %s', 'foodbank-manager' ), number_format_i18n( $summary['today'] ?? 0 ) ) ); ?>">
        <span class="dashicons dashicons-yes" aria-hidden="true"></span>
        <div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $summary['today'] ?? 0 ) ); ?></div>
        <div class="fbm-tile-label"><?php esc_html_e('Check-ins Today', 'foodbank-manager'); ?></div>
    </div>
    <div class="fbm-tile fbm-card--glass" tabindex="0" data-testid="fbm-dashboard-tile-week" aria-label="<?php echo esc_attr( sprintf( __( 'Check-ins This Week: %s', 'foodbank-manager' ), number_format_i18n( $summary['week'] ?? 0 ) ) ); ?>">
        <span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span>
        <div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $summary['week'] ?? 0 ) ); ?></div>
        <div class="fbm-tile-label"><?php esc_html_e('Check-ins This Week', 'foodbank-manager'); ?></div>
    </div>
    <div class="fbm-tile fbm-card--glass" tabindex="0" data-testid="fbm-dashboard-tile-month" aria-label="<?php echo esc_attr( sprintf( __( 'Check-ins This Month: %s', 'foodbank-manager' ), number_format_i18n( $summary['month'] ?? 0 ) ) ); ?>">
        <span class="dashicons dashicons-calendar" aria-hidden="true"></span>
        <div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $summary['month'] ?? 0 ) ); ?></div>
        <div class="fbm-tile-label"><?php esc_html_e('Check-ins This Month', 'foodbank-manager'); ?></div>
    </div>
    <div class="fbm-tile fbm-card--glass" tabindex="0" data-testid="fbm-dashboard-tile-recent" aria-label="<?php echo esc_attr( sprintf( __( 'Tickets scanned (7d): %s', 'foodbank-manager' ), number_format_i18n( $recent ) ) ); ?>">
        <span class="dashicons dashicons-tickets-alt" aria-hidden="true"></span>
        <div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $recent ) ); ?></div>
        <div class="fbm-tile-label"><?php esc_html_e('Tickets scanned (7d)', 'foodbank-manager'); ?></div>
    </div>
    <div class="fbm-tile fbm-card--glass" tabindex="0" data-testid="fbm-dashboard-tile-events" aria-label="<?php echo esc_attr( sprintf( __( 'Active events: %s', 'foodbank-manager' ), number_format_i18n( $events_active ) ) ); ?>">
        <span class="dashicons dashicons-megaphone" aria-hidden="true"></span>
        <div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $events_active ) ); ?></div>
        <div class="fbm-tile-label"><?php esc_html_e('Active events', 'foodbank-manager'); ?></div>
    </div>
    <div class="fbm-tile fbm-card--glass" tabindex="0" data-testid="fbm-dashboard-tile-forms" aria-label="<?php echo esc_attr( sprintf( __( 'Forms submitted (7d): %s', 'foodbank-manager' ), number_format_i18n( $forms_7d ) ) ); ?>">
        <span class="dashicons dashicons-feedback" aria-hidden="true"></span>
        <div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $forms_7d ) ); ?></div>
        <div class="fbm-tile-label"><?php esc_html_e('Forms submitted (7d)', 'foodbank-manager'); ?></div>
    </div>
    <div class="fbm-tile fbm-card--glass" tabindex="0" data-testid="fbm-dashboard-tile-jobs" aria-label="<?php echo esc_attr( sprintf( __( 'Pending export jobs: %s', 'foodbank-manager' ), number_format_i18n( $jobs_pending ) ) ); ?>">
        <span class="dashicons dashicons-download" aria-hidden="true"></span>
        <div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $jobs_pending ) ); ?></div>
        <div class="fbm-tile-label"><?php esc_html_e('Pending export jobs', 'foodbank-manager'); ?></div>
    </div>
    <div class="fbm-tile fbm-card--glass" data-testid="fbm-dashboard-tile-shortcuts" tabindex="0" aria-label="<?php esc_attr_e('Shortcuts', 'foodbank-manager'); ?>">
        <span class="dashicons dashicons-admin-links" aria-hidden="true"></span>
        <div class="fbm-tile-label"><?php esc_html_e('Shortcuts', 'foodbank-manager'); ?></div>
        <div class="fbm-shortcuts">
            <a class="fbm-button--glass" href="<?php echo esc_url( admin_url('post-new.php?post_type=fb_form') ); ?>"><?php esc_html_e('Create Form', 'foodbank-manager'); ?></a>
            <a class="fbm-button--glass" href="<?php echo esc_url( admin_url('edit.php?post_type=fb_form') ); ?>"><?php esc_html_e('Submissions', 'foodbank-manager'); ?></a>
            <a class="fbm-button--glass" href="<?php echo esc_url( admin_url('admin.php?page=fbm_attendance&tab=scan') ); ?>"><?php esc_html_e('Start Scan', 'foodbank-manager'); ?></a>
            <a class="fbm-button--glass" href="<?php echo esc_url( admin_url('admin.php?page=fbm_reports') ); ?>"><?php esc_html_e('Reports', 'foodbank-manager'); ?></a>
        </div>
    </div>
</div>
</div>
