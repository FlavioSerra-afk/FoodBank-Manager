<?php
namespace FoodBankManager\Admin;

use FoodBankManager\Attendance\AttendanceRepo;
use FoodBankManager\Security\Helpers;
use FoodBankManager\Security\Crypto;

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

if ( ! current_user_can( 'attendance_view' ) && ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
}

$range   = isset( $_GET['range'] ) ? (int) $_GET['range'] : 7;
$now     = current_time( 'mysql' );
$from    = gmdate( 'Y-m-d H:i:s', strtotime( '-' . $range . ' days', strtotime( $now ) ) );
$rows    = AttendanceRepo::peopleSummary( 1, $from, $now, $now, 7, 100, 0 );

if ( isset( $_GET['export'] ) && $_GET['export'] === 'csv' && current_user_can( 'attendance_export' ) ) {
        Helpers::require_nonce( 'fbm_attendance_export' );
        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment; filename="attendance.csv"' );
        $out = fopen( 'php://output', 'w' );
        fputcsv( $out, array( 'Name', 'Email', 'Postcode', 'Last attended', 'Visits', 'No-shows', 'Visits 12m', 'Policy' ) );
        foreach ( $rows as $r ) {
                $data  = json_decode( (string) $r['data_json'], true );
                $pii   = Crypto::decryptSensitive( (string) $r['pii_encrypted_blob'] );
                $name  = ( $data['first_name'] ?? '' ) . ' ' . ( $pii['last_name'] ?? '' );
                $email = $pii['email'] ?? '';
                $pc    = $data['postcode'] ?? '';
                fputcsv( $out, array( $name, $email, $pc, $r['last_attended'], $r['visits_range'], $r['noshows_range'], $r['visits_12m'], $r['policy_breach'] ? 'Warning' : 'OK' ) );
        }
        fclose( $out );
        exit;
}

$mask = ! current_user_can( 'read_sensitive' );

?>
<div class="wrap">
    <h1><?php \esc_html_e( 'Attendance', 'foodbank-manager' ); ?></h1>
    <form method="get" class="fbm-attendance-filters">
        <select name="range">
            <option value="7"<?php selected( $range, 7 ); ?>><?php \esc_html_e( 'Last 7 days', 'foodbank-manager' ); ?></option>
            <option value="30"<?php selected( $range, 30 ); ?>><?php \esc_html_e( 'Last 30 days', 'foodbank-manager' ); ?></option>
            <option value="180"<?php selected( $range, 180 ); ?>><?php \esc_html_e( 'Last 6 months', 'foodbank-manager' ); ?></option>
            <option value="365"<?php selected( $range, 365 ); ?>><?php \esc_html_e( 'Last 12 months', 'foodbank-manager' ); ?></option>
        </select>
        <button class="button"><?php \esc_html_e( 'Apply', 'foodbank-manager' ); ?></button>
    </form>
    <table class="wp-list-table widefat fixed striped">
        <thead><tr><th><?php \esc_html_e( 'Name', 'foodbank-manager' ); ?></th><th><?php \esc_html_e( 'Email', 'foodbank-manager' ); ?></th><th><?php \esc_html_e( 'Postcode', 'foodbank-manager' ); ?></th><th><?php \esc_html_e( 'Last attended', 'foodbank-manager' ); ?></th><th><?php \esc_html_e( 'Visits', 'foodbank-manager' ); ?></th><th><?php \esc_html_e( 'No-shows', 'foodbank-manager' ); ?></th><th><?php \esc_html_e( 'Visits (12m)', 'foodbank-manager' ); ?></th><th><?php \esc_html_e( 'Policy', 'foodbank-manager' ); ?></th></tr></thead>
        <tbody>
            <?php if ( empty( $rows ) ) : ?>
                <tr><td colspan="8"><?php \esc_html_e( 'No attendance records.', 'foodbank-manager' ); ?></td></tr>
            <?php else : ?>
                <?php foreach ( $rows as $r ) :
                        $data  = json_decode( (string) $r['data_json'], true );
                        $pii   = Crypto::decryptSensitive( (string) $r['pii_encrypted_blob'] );
                        $name  = ( $data['first_name'] ?? '' ) . ' ' . ( $pii['last_name'] ?? '' );
                        $email = $pii['email'] ?? '';
                        $pc    = $data['postcode'] ?? '';
                        if ( $mask ) {
                                $email = Helpers::mask_email( $email );
                                $pc    = Helpers::mask_postcode( $pc );
                        }
                        ?>
                        <tr>
                                <td><?php echo esc_html( $name ); ?></td>
                                <td><?php echo esc_html( $email ); ?></td>
                                <td><?php echo esc_html( $pc ); ?></td>
                                <td><?php echo esc_html( (string) $r['last_attended'] ); ?></td>
                                <td><?php echo esc_html( (string) $r['visits_range'] ); ?></td>
                                <td><?php echo esc_html( (string) $r['noshows_range'] ); ?></td>
                                <td><?php echo esc_html( (string) $r['visits_12m'] ); ?></td>
                                <td><?php echo $r['policy_breach'] ? '<span class="badge badge-warning">' . esc_html__( 'Warning', 'foodbank-manager' ) . '</span>' : esc_html__( 'OK', 'foodbank-manager' ); ?></td>
                        </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if ( current_user_can( 'attendance_export' ) ) : ?>
        <p><a href="<?php echo esc_url( add_query_arg( array( 'export' => 'csv', '_wpnonce' => wp_create_nonce( 'fbm_attendance_export' ) ) ) ); ?>" class="button">&nbsp;<?php \esc_html_e( 'Export CSV', 'foodbank-manager' ); ?></a></p>
    <?php endif; ?>
</div>
