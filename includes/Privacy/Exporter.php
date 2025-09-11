<?php
/**
 * GDPR Personal Data Exporter.
 *
 * @package FoodBankManager\Privacy
 */

declare(strict_types=1);

namespace FBM\Privacy;

use wpdb;
use function sanitize_email;
use function sanitize_key;
use function sanitize_text_field;

/**
 * Export FBM data for WordPress privacy exporter.
 */
final class Exporter {
    private const PER_PAGE = 50;

    /**
     * Export data for a given email.
     *
     * @param string $email Email address.
     * @param int    $page  Page number.
     * @return array{data:list<array{group_id:string,group_label:string,item_id:string,data:list<array{name:string,value:string}>}>,done:bool}
     */
    public static function export(string $email, int $page): array {
        global $wpdb;
        $email  = sanitize_email($email);
        $page   = max(1, (int) $page);
        $limit  = self::PER_PAGE;
        $offset = ( $page - 1 ) * $limit;

        $groups = array(
            array(
                'id'    => 'fbm_submissions',
                'label' => 'FBM Submissions',
                'table' => $wpdb->prefix . 'fb_submissions',
            ),
            array(
                'id'    => 'fbm_attendance',
                'label' => 'FBM Attendance',
                'table' => $wpdb->prefix . 'fb_attendance',
            ),
            array(
                'id'    => 'fbm_tickets',
                'label' => 'FBM Tickets',
                'table' => $wpdb->prefix . 'fb_tickets',
            ),
            array(
                'id'    => 'fbm_emails',
                'label' => 'FBM Emails',
                'table' => $wpdb->prefix . 'fb_emails',
            ),
        );

        $data = array();
        $done = true;

        foreach ( $groups as $group ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is constant.
            $sql  = "SELECT * FROM {$group['table']} WHERE email = %s LIMIT %d OFFSET %d";
            $rows = $wpdb->get_results( $wpdb->prepare( $sql, $email, $limit, $offset ), ARRAY_A );
            if ( $rows ) {
                foreach ( $rows as $row ) {
                    $item_id = isset( $row['id'] ) ? $group['id'] . '-' . (int) $row['id'] : $group['id'];
                    $item    = array(
                        'group_id'    => $group['id'],
                        'group_label' => $group['label'],
                        'item_id'     => $item_id,
                        'data'        => array(),
                    );
                    foreach ( $row as $col => $val ) {
                        if ( 'id' === $col || 'email' === $col ) {
                            continue;
                        }
                        $item['data'][] = array(
                            'name'  => sanitize_key( (string) $col ),
                            'value' => sanitize_text_field( (string) $val ),
                        );
                    }
                    $data[] = $item;
                }
            }
            if ( ! empty( $rows ) && count( $rows ) === $limit ) {
                $done = false;
            }
        }

        return array(
            'data' => $data,
            'done' => $done,
        );
    }
}
