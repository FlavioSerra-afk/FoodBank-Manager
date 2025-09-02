<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Attendance\AttendanceRepo;
use FoodBankManager\Exports\CsvExporter;
use FoodBankManager\Security\Crypto;
use FoodBankManager\Security\Helpers;
use wpdb;

final class AttendancePage {
    public static function route(): void {
        if ( ! current_user_can('attendance_view') && ! current_user_can('manage_options') ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array('response' => 403) );
        }

        $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['action'] ) ) : '';
        if ( $action === 'fbm_att_export' ) {
            self::handleExport();
            return;
        }

        self::renderList();
    }

    private static function parseFilters(): array {
        $preset = isset( $_GET['preset'] ) ? Helpers::sanitize_text( wp_unslash( (string) $_GET['preset'] ) ) : (string) get_user_meta( get_current_user_id(), 'fbm_att_preset', true );
        if ( $preset === '' ) {
            $preset = '7d';
        }
        $now    = current_time( 'timestamp', true ); // UTC
        switch ( $preset ) {
            case '30d':
                $from = strtotime( '-30 days', $now );
                break;
            case '6m':
                $from = strtotime( '-6 months', $now );
                break;
            case '12m':
                $from = strtotime( '-12 months', $now );
                break;
            case 'custom':
                $from_in = isset( $_GET['range_from'] ) ? Helpers::sanitize_text( wp_unslash( (string) $_GET['range_from'] ) ) : '';
                $to_in   = isset( $_GET['range_to'] ) ? Helpers::sanitize_text( wp_unslash( (string) $_GET['range_to'] ) ) : '';
                $from    = $from_in ? strtotime( $from_in ) : strtotime( '-7 days', $now );
                $now     = $to_in ? strtotime( $to_in ) : $now;
                break;
            case '7d':
            default:
                $from   = strtotime( '-7 days', $now );
                $preset = '7d';
        }

        $range_from = gmdate( 'Y-m-d H:i:s', $from );
        $range_to   = gmdate( 'Y-m-d H:i:s', $now );

        $args               = array();
        $args['range_from'] = $range_from;
        $args['range_to']   = $range_to;
        $args['preset']     = $preset;
        if ( isset( $_GET['preset'] ) ) {
            update_user_meta( get_current_user_id(), 'fbm_att_preset', $preset );
        }

        if ( isset( $_GET['event_id'] ) ) {
            $args['event_id'] = (int) $_GET['event_id'];
        }
        if ( isset( $_GET['status'] ) ) {
            $args['status'] = array_map( static fn( $v ) => Helpers::sanitize_text( (string) $v ), (array) $_GET['status'] );
        }
        if ( isset( $_GET['type'] ) ) {
            $args['type'] = array_map( static fn( $v ) => Helpers::sanitize_text( (string) $v ), (array) $_GET['type'] );
        }
        if ( isset( $_GET['manager_id'] ) ) {
            $args['manager_id'] = (int) $_GET['manager_id'];
        }
        if ( ! empty( $_GET['policy_only'] ) ) {
            $args['policy_only'] = true;
        }
        if ( ! empty( $_GET['include_voided'] ) ) {
            $args['include_voided'] = true;
        }

        $args['page'] = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : ( isset( $_GET['page'] ) ? max( 1, (int) $_GET['page'] ) : 1 );

        $user_per_page    = (int) get_user_meta( get_current_user_id(), 'fbm_att_per_page', true );
        $args['per_page'] = $user_per_page > 0 ? $user_per_page : 25;
        if ( isset( $_GET['per_page'] ) ) {
            $per = (int) $_GET['per_page'];
            if ( in_array( $per, array( 25, 50, 100 ), true ) ) {
                $args['per_page'] = $per;
                update_user_meta( get_current_user_id(), 'fbm_att_per_page', $per );
            }
        }

        $allowed = array( 'last_attended', 'visits_range', 'noshows_range', 'visits_12m', 'application_id' );
        $args['orderby'] = isset( $_GET['orderby'] ) ? Helpers::sanitize_text( wp_unslash( (string) $_GET['orderby'] ) ) : 'last_attended';
        if ( ! in_array( $args['orderby'], $allowed, true ) ) {
            $args['orderby'] = 'last_attended';
        }
        $args['order'] = isset( $_GET['order'] ) && strtoupper( (string) $_GET['order'] ) === 'ASC' ? 'ASC' : 'DESC';

        return $args;
    }

    private static function decorateRows( array $rows, bool $mask ): array {
        global $wpdb;
        $ids = array_map( 'intval', array_column( $rows, 'application_id' ) );
        $apps = array();
        if ( $ids ) {
            $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
            $sql         = "SELECT id, data_json, pii_encrypted_blob FROM {$wpdb->prefix}fb_applications WHERE id IN ($placeholders)";
            $app_rows    = $wpdb->get_results( $wpdb->prepare( $sql, $ids ), 'ARRAY_A' );
            foreach ( $app_rows as $ar ) {
                $apps[ (int) $ar['id'] ] = $ar;
            }
        }

        $out = array();
        foreach ( $rows as $row ) {
            $app   = $apps[ (int) $row['application_id'] ] ?? array();
            $data  = json_decode( (string) ( $app['data_json'] ?? '' ), true ) ?: array();
            $pii   = Crypto::decryptSensitive( (string) ( $app['pii_encrypted_blob'] ?? '' ) );
            $email = $pii['email'] ?? '';
            $pc    = $data['postcode'] ?? '';
            if ( $mask ) {
                $email = Helpers::mask_email( $email );
                $pc    = Helpers::mask_postcode( $pc );
            }
            $out[] = array(
                'application_id' => (int) $row['application_id'],
                'name'           => (string) ( $data['first_name'] ?? '' ),
                'email'          => $email,
                'postcode'       => $pc,
                'last_attended'  => (string) $row['last_attended'],
                'visits_range'   => (int) $row['visits_range'],
                'noshows_range'  => (int) $row['noshows_range'],
                'visits_12m'     => (int) $row['visits_12m'],
                'policy_badge'   => ! empty( $row['policy_breach'] ) ? 'warning' : '',
            );
        }
        return $out;
    }

    private static function handleExport(): void {
        if ( ! current_user_can( 'attendance_export' ) ) {
            wp_die( '', '', array( 'response' => 403 ) );
        }
        Helpers::require_nonce( 'fbm_att_export' );
        $filters              = self::parseFilters();
        $filters['page']      = 1;
        $filters['per_page']  = 1000;
        $data                 = AttendanceRepo::peopleSummary( $filters );
        $can_sensitive        = current_user_can( 'read_sensitive' );
        $mask                 = ! ( $can_sensitive && isset( $_REQUEST['unmask'] ) );
        $rows                 = self::decorateRows( $data['rows'], ! $can_sensitive || $mask );
        CsvExporter::streamAttendancePeople( $rows, $mask, ! empty( $filters['include_voided'] ) );
        exit;
    }

    private static function renderList(): void {
        $filters       = self::parseFilters();
        $data          = AttendanceRepo::peopleSummary( $filters );
        $rows          = self::decorateRows( $data['rows'], true );
        $total         = $data['total'];
        $page          = $filters['page'];
        $per_page      = $filters['per_page'];
        $preset        = $filters['preset'];
        $range_from    = $filters['range_from'];
        $range_to      = $filters['range_to'];
        $include_voided = ! empty( $filters['include_voided'] );
        $can_sensitive = current_user_can( 'read_sensitive' );
        require FBM_PATH . 'templates/admin/attendance.php';
    }
}

