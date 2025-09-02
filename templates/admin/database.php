<?php
namespace FoodBankManager\Admin;

use FoodBankManager\Security\Helpers;
use FoodBankManager\Security\Crypto;

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

if ( isset( $_GET['export'] ) && $_GET['export'] === 'csv' && current_user_can( 'fb_export_entries' ) ) {
        Helpers::require_nonce( 'fbm_db_export' );
        global $wpdb;
        $rows = $wpdb->get_results( "SELECT id, data_json, pii_encrypted_blob FROM {$wpdb->prefix}fb_applications LIMIT 100", ARRAY_A );
        $mask = ! ( isset( $_GET['mask'] ) && $_GET['mask'] === '0' && current_user_can( 'read_sensitive' ) );
        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment; filename=applications.csv' );
        $out = fopen( 'php://output', 'w' );
        fputcsv( $out, array( 'ID', 'First name', 'Last name', 'Email', 'Postcode' ) );
        foreach ( $rows as $r ) {
                $data  = json_decode( (string) $r['data_json'], true );
                $pii   = Crypto::decryptSensitive( (string) $r['pii_encrypted_blob'] );
                $first = $data['first_name'] ?? '';
                $last  = $pii['last_name'] ?? '';
                $email = $pii['email'] ?? '';
                $pc    = $data['postcode'] ?? '';
                if ( $mask ) {
                        $email = Helpers::mask_email( $email );
                        $pc    = Helpers::mask_postcode( $pc );
                }
                fputcsv( $out, array( $r['id'], $first, $last, $email, $pc ) );
        }
        fclose( $out );
        exit;
}
?>
<div class="wrap">
    <h1><?php \esc_html_e( 'Database', 'foodbank-manager' ); ?></h1>
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
    <?php if ( current_user_can( 'fb_export_entries' ) ) : ?>
        <p><a href="<?php echo esc_url( add_query_arg( array( 'export' => 'csv', '_wpnonce' => wp_create_nonce( 'fbm_db_export' ) ) ) ); ?>" class="button">&nbsp;<?php \esc_html_e( 'Export CSV', 'foodbank-manager' ); ?></a></p>
    <?php endif; ?>
</div>
