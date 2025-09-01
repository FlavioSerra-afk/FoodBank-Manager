<?php
namespace FoodBankManager\Admin;

if (! defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php \esc_html_e('Attendance', 'foodbank-manager'); ?></h1>
    <form method="get" class="fbm-attendance-filters">
        <select name="range">
            <option value="7"><?php \esc_html_e('Last 7 days', 'foodbank-manager'); ?></option>
            <option value="30"><?php \esc_html_e('Last 30 days', 'foodbank-manager'); ?></option>
            <option value="180"><?php \esc_html_e('Last 6 months', 'foodbank-manager'); ?></option>
            <option value="365"><?php \esc_html_e('Last 12 months', 'foodbank-manager'); ?></option>
            <option value="custom"><?php \esc_html_e('Custom', 'foodbank-manager'); ?></option>
        </select>
        <button class="button"><?php \esc_html_e('Apply', 'foodbank-manager'); ?></button>
    </form>
    <?php if (! class_exists('Dompdf\\Dompdf')) : ?>
        <div class="notice notice-info"><p><?php \esc_html_e('PDF library missing; using HTML view.', 'foodbank-manager'); ?></p></div>
    <?php endif; ?>
    <table class="wp-list-table widefat fixed striped">
        <thead><tr><th><?php \esc_html_e('Name', 'foodbank-manager'); ?></th><th><?php \esc_html_e('Visits', 'foodbank-manager'); ?></th><th><?php \esc_html_e('No-shows', 'foodbank-manager'); ?></th><th><?php \esc_html_e('Visits (12m)', 'foodbank-manager'); ?></th></tr></thead>
        <tbody>
            <tr><td colspan="4"><?php \esc_html_e('No attendance records.', 'foodbank-manager'); ?></td></tr>
        </tbody>
    </table>
    <p><a href="#" class="button">&nbsp;<?php \esc_html_e('Export CSV', 'foodbank-manager'); ?></a></p>
    <div class="fbm-timeline-drawer" style="display:none;"></div>
    <?php // TODO(PRD §5.5): policy badges, timeline, and void action. ?>
</div>
