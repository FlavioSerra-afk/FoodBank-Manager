<?php
namespace FoodBankManager\Admin;

if (! defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php \esc_html_e('Database', 'foodbank-manager'); ?></h1>
    <form method="get" class="fbm-filters">
        <label><?php \esc_html_e('Date', 'foodbank-manager'); ?> <input type="date" name="filter_date" /></label>
        <label><?php \esc_html_e('Status', 'foodbank-manager'); ?>
            <select name="filter_status">
                <option value=""><?php \esc_html_e('All', 'foodbank-manager'); ?></option>
                <option value="new"><?php \esc_html_e('New', 'foodbank-manager'); ?></option>
                <option value="approved"><?php \esc_html_e('Approved', 'foodbank-manager'); ?></option>
            </select>
        </label>
        <label><?php \esc_html_e('City', 'foodbank-manager'); ?> <input type="text" name="filter_city" /></label>
        <label><?php \esc_html_e('Postcode', 'foodbank-manager'); ?> <input type="text" name="filter_postcode" /></label>
        <label><input type="checkbox" name="filter_has_file" value="1" /> <?php \esc_html_e('Has file', 'foodbank-manager'); ?></label>
        <label><input type="checkbox" name="filter_consent" value="1" /> <?php \esc_html_e('Consent', 'foodbank-manager'); ?></label>
        <button class="button"><?php \esc_html_e('Filter', 'foodbank-manager'); ?></button>
    </form>
    <div class="fbm-columns">
        <p><?php \esc_html_e('Column chooser placeholder.', 'foodbank-manager'); ?></p>
    </div>
    <table class="wp-list-table widefat fixed striped">
        <thead><tr><th><?php \esc_html_e('Reference', 'foodbank-manager'); ?></th><th><?php \esc_html_e('Name', 'foodbank-manager'); ?></th></tr></thead>
        <tbody>
            <tr><td colspan="2"><?php \esc_html_e('No entries found.', 'foodbank-manager'); ?></td></tr>
        </tbody>
    </table>
    <div class="tablenav"><div class="tablenav-pages"><?php \esc_html_e('Pagination placeholder.', 'foodbank-manager'); ?></div></div>
</div>
