<?php
if (!defined('ABSPATH')) { exit; }
?>
<div class="wrap fbm-admin">
<?php \FBM\Core\Trace::mark('admin:dashboard'); ?>
<?php
$registrations_total = (int) ($registrations_total ?? 0);
$summary = $summary ?? array('today'=>0,'week'=>0,'month'=>0);
$recent = (int) ($recent ?? 0);
$series = $series ?? array();
?>
<h1><?php esc_html_e('Dashboard', 'foodbank-manager'); ?></h1>
<div class="fbm-tiles">
    <div class="fbm-tile fbm-card--glass fbm-tile--glass" data-testid="fbm-dashboard-tile-registrations">
        <span class="dashicons dashicons-admin-users" aria-hidden="true"></span>
        <div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $registrations_total ) ); ?></div>
        <div class="fbm-tile-label"><?php esc_html_e('Total registrations', 'foodbank-manager'); ?></div>
    </div>
    <div class="fbm-tile fbm-card--glass fbm-tile--glass" data-testid="fbm-dashboard-tile-today">
        <span class="dashicons dashicons-yes" aria-hidden="true"></span>
        <div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $summary['today'] ?? 0 ) ); ?></div>
        <div class="fbm-tile-label"><?php esc_html_e('Check-ins Today', 'foodbank-manager'); ?></div>
    </div>
    <div class="fbm-tile fbm-card--glass fbm-tile--glass" data-testid="fbm-dashboard-tile-week">
        <span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span>
        <div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $summary['week'] ?? 0 ) ); ?></div>
        <div class="fbm-tile-label"><?php esc_html_e('Check-ins This Week', 'foodbank-manager'); ?></div>
    </div>
    <div class="fbm-tile fbm-card--glass fbm-tile--glass" data-testid="fbm-dashboard-tile-month">
        <span class="dashicons dashicons-calendar" aria-hidden="true"></span>
        <div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $summary['month'] ?? 0 ) ); ?></div>
        <div class="fbm-tile-label"><?php esc_html_e('Check-ins This Month', 'foodbank-manager'); ?></div>
    </div>
    <div class="fbm-tile fbm-card--glass fbm-tile--glass" data-testid="fbm-dashboard-tile-recent">
        <span class="dashicons dashicons-tickets-alt" aria-hidden="true"></span>
        <div class="fbm-tile-value"><?php echo esc_html( number_format_i18n( $recent ) ); ?></div>
        <div class="fbm-tile-label"><?php esc_html_e('Tickets scanned (7d)', 'foodbank-manager'); ?></div>
    </div>
</div>
<div class="fbm-card--glass fbm-sparkline" data-testid="fbm-dashboard-sparkline" role="img" aria-label="<?php esc_attr_e('Attendance trend last 6 months', 'foodbank-manager'); ?>">
<?php
    $series = array_values($series);
    if (empty($series)) { $series = array(0); }
    $max = max($series);
    $min = min($series);
    $count = count($series);
    $points = array();
    for ($i=0; $i<$count; $i++) {
        $x = $count > 1 ? ($i/($count-1))*100 : 0;
        $y = $max > $min ? 30 - (($series[$i]-$min)/($max-$min)*30) : 30;
        $points[] = $x . ',' . $y;
    }
    $last_x = $count > 1 ? 100 : 0;
    $last_y = $max > $min ? 30 - (($series[$count-1]-$min)/($max-$min)*30) : 30;
?>
    <svg viewBox="0 0 100 30" xmlns="http://www.w3.org/2000/svg">
        <polyline fill="none" stroke="currentColor" stroke-width="2" points="<?php echo esc_attr( implode(' ', $points) ); ?>" />
        <circle cx="<?php echo esc_attr( (string) $last_x ); ?>" cy="<?php echo esc_attr( (string) $last_y ); ?>" r="2" />
    </svg>
</div>
<ul class="fbm-shortcuts" data-testid="fbm-dashboard-shortcuts">
    <li><a class="fbm-button fbm-button--glass" href="<?php echo esc_url( admin_url('post-new.php?post_type=fb_form') ); ?>"><?php esc_html_e('Create Form', 'foodbank-manager'); ?></a></li>
    <li><a class="fbm-button fbm-button--glass" href="<?php echo esc_url( admin_url('edit.php?post_type=fb_form') ); ?>"><?php esc_html_e('Open Submissions', 'foodbank-manager'); ?></a></li>
    <li><a class="fbm-button fbm-button--glass" href="<?php echo esc_url( admin_url('admin.php?page=fbm_attendance&tab=scan') ); ?>"><?php esc_html_e('Start Scan', 'foodbank-manager'); ?></a></li>
    <li><a class="fbm-button fbm-button--glass" href="<?php echo esc_url( admin_url('admin.php?page=fbm_reports') ); ?>"><?php esc_html_e('Export Reports', 'foodbank-manager'); ?></a></li>
</ul>
</div>
