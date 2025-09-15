<?php
/**
 * Database migration for FoodBank Manager tables.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Database;

use wpdb;
use const ARRAY_A;
use function array_merge;
use function count;
use function dbDelta;
use function gmdate;
use function in_array;
use function is_array;
use function strtolower;
use function strtotime;
use function update_option;

/**
 * Ensure required tables exist and migrate legacy data.
 */
final class Migration
{
    private const VERSION = '2024091501';

    /**
     * Entry point triggered on plugin activation/upgrade.
     */
    public static function run(): void
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $tables = self::table_names($wpdb);

        self::create_tables($wpdb, $tables);
        $summary = self::soft_migrate($wpdb, $tables);

        update_option('fbm_db_version', self::VERSION, false);
        update_option('fbm_db_migration_summary', $summary, false);
    }

    /**
     * Build canonical table names (prefixed).
     *
     * @return array<string,string>
     */
    private static function table_names(wpdb $wpdb): array
    {
        $prefix = $wpdb->prefix;

        return [
            'members'           => $prefix . 'fbm_members',
            'tokens'            => $prefix . 'fbm_tokens',
            'attendance'        => $prefix . 'fbm_attendance',
            'legacy_attendance' => $prefix . 'fb_attendance',
            'legacy_checkins'   => $prefix . 'fb_checkins',
            'legacy_events'     => $prefix . 'fb_events',
            'legacy_tickets'    => $prefix . 'fb_tickets',
        ];
    }

    /**
     * Create required tables if they do not yet exist.
     *
     * @param array<string,string> $tables Table map from table_names().
     */
    private static function create_tables(wpdb $wpdb, array $tables): void
    {
        $charset = $wpdb->get_charset_collate();

        $memberSql = 'CREATE TABLE ' . self::esc_identifier($tables['members']) . ' (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            reference VARCHAR(64) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT "pending",
            legacy_application_id BIGINT UNSIGNED NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY uq_reference (reference),
            UNIQUE KEY uq_legacy_application (legacy_application_id),
            KEY idx_status (status)
        ) ' . $charset . ';';

        $tokenSql = 'CREATE TABLE ' . self::esc_identifier($tables['tokens']) . ' (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id BIGINT UNSIGNED NOT NULL,
            token_hash CHAR(64) NOT NULL,
            version SMALLINT UNSIGNED NOT NULL DEFAULT 1,
            issued_at DATETIME NOT NULL,
            revoked_at DATETIME NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY uq_token_hash (token_hash),
            KEY idx_member (member_id)
        ) ' . $charset . ';';

        $attendanceSql = 'CREATE TABLE ' . self::esc_identifier($tables['attendance']) . ' (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id BIGINT UNSIGNED NOT NULL,
            collected_at DATETIME NOT NULL,
            collected_date DATE NOT NULL,
            method VARCHAR(10) NOT NULL,
            recorded_by BIGINT UNSIGNED NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY uq_member_day (member_id, collected_date),
            KEY idx_member_collected (member_id, collected_at)
        ) ' . $charset . ';';

        dbDelta($memberSql);
        dbDelta($tokenSql);
        dbDelta($attendanceSql);
    }

    /**
     * Rename legacy tables and migrate attendance data.
     *
     * @param array<string,string> $tables Table map from table_names().
     *
     * @return array<string,int>
     */
    private static function soft_migrate(wpdb $wpdb, array $tables): array
    {
        $summary = [
            'legacy_attendance_rows' => 0,
            'attendance_migrated'    => 0,
            'attendance_skipped'     => 0,
            'legacy_checkins_rows'   => 0,
        ];

        $legacyAttendance = $tables['legacy_attendance'] . '_deprecated';
        $legacyCheckins   = $tables['legacy_checkins'] . '_deprecated';
        $legacyEvents     = $tables['legacy_events'] . '_deprecated';
        $legacyTickets    = $tables['legacy_tickets'] . '_deprecated';

        self::rename_table($wpdb, $tables['legacy_attendance'], $legacyAttendance);
        self::rename_table($wpdb, $tables['legacy_checkins'], $legacyCheckins);
        self::rename_table($wpdb, $tables['legacy_events'], $legacyEvents);
        self::rename_table($wpdb, $tables['legacy_tickets'], $legacyTickets);

        if (self::table_exists($wpdb, $legacyAttendance)) {
            $summary['legacy_attendance_rows'] = (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . self::esc_identifier($legacyAttendance));
            $summary = array_merge(
                $summary,
                self::migrate_attendance($wpdb, $legacyAttendance, $tables['members'], $tables['attendance'])
            );
        }

        if (self::table_exists($wpdb, $legacyCheckins)) {
            $summary['legacy_checkins_rows'] = (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . self::esc_identifier($legacyCheckins));
        }

        return $summary;
    }

    /**
     * Migrate rows from the legacy attendance table into the new schema.
     *
     * @return array<string,int>{attendance_migrated:int,attendance_skipped:int}
     */
    private static function migrate_attendance(wpdb $wpdb, string $legacyTable, string $membersTable, string $attendanceTable): array
    {
        $out = [
            'attendance_migrated' => 0,
            'attendance_skipped'  => 0,
        ];

        self::ensure_members_from_attendance($wpdb, $legacyTable, $membersTable);
        $memberMap = self::legacy_member_map($wpdb, $membersTable);

        $rows = $wpdb->get_results('SELECT application_id, attendance_at, method, recorded_by_user_id FROM ' . self::esc_identifier($legacyTable), ARRAY_A);
        if (!is_array($rows) || count($rows) === 0) {
            return $out;
        }

        foreach ($rows as $row) {
            $applicationId = isset($row['application_id']) ? (int) $row['application_id'] : 0;
            if ($applicationId <= 0) {
                $out['attendance_skipped']++;
                continue;
            }

            $memberId = $memberMap[$applicationId] ?? 0;
            if ($memberId <= 0) {
                $out['attendance_skipped']++;
                continue;
            }

            $rawTime = (string) ($row['attendance_at'] ?? '');
            $timestamp = strtotime($rawTime);
            if (false === $timestamp) {
                $out['attendance_skipped']++;
                continue;
            }

            $collectedAt   = gmdate('Y-m-d H:i:s', $timestamp);
            $collectedDate = gmdate('Y-m-d', $timestamp);

            $method = strtolower((string) ($row['method'] ?? ''));
            if (!in_array($method, ['qr', 'manual'], true)) {
                $method = 'manual';
            }

            $recordedBy = isset($row['recorded_by_user_id']) ? (int) $row['recorded_by_user_id'] : 0;
            $recordedBy = $recordedBy > 0 ? $recordedBy : null;

            $values = '%d, %s, %s, %s';
            $args   = [$memberId, $collectedAt, $collectedDate, $method];
            if (null === $recordedBy) {
                $values .= ', NULL';
            } else {
                $values .= ', %d';
                $args[] = $recordedBy;
            }

            $sql = 'INSERT INTO ' . self::esc_identifier($attendanceTable)
                . ' (member_id, collected_at, collected_date, method, recorded_by)'
                . ' VALUES (' . $values . ')'
                . ' ON DUPLICATE KEY UPDATE'
                . ' collected_at = LEAST(collected_at, VALUES(collected_at)),'
                . ' method = VALUES(method),'
                . ' recorded_by = CASE WHEN recorded_by IS NULL THEN VALUES(recorded_by) ELSE recorded_by END';

            $result = $wpdb->query($wpdb->prepare($sql, $args));

            if (false === $result) {
                $out['attendance_skipped']++;
                continue;
            }

            if ($result >= 1) {
                $out['attendance_migrated']++;
            }
        }

        return $out;
    }

    /**
     * Ensure member records exist for legacy application IDs.
     */
    private static function ensure_members_from_attendance(wpdb $wpdb, string $legacyTable, string $membersTable): void
    {
        $now   = gmdate('Y-m-d H:i:s');
        $ids   = (array) $wpdb->get_col('SELECT DISTINCT application_id FROM ' . self::esc_identifier($legacyTable) . ' WHERE application_id IS NOT NULL AND application_id > 0');

        foreach ($ids as $legacyId) {
            $applicationId = (int) $legacyId;
            if ($applicationId <= 0) {
                continue;
            }

            $reference = 'legacy-' . $applicationId;

            $sql = $wpdb->prepare(
                'INSERT INTO ' . self::esc_identifier($membersTable)
                . ' (reference, status, legacy_application_id, created_at, updated_at)'
                . ' VALUES (%s, %s, %d, %s, %s)'
                . ' ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at)',
                $reference,
                'active',
                $applicationId,
                $now,
                $now
            );

            $wpdb->query($sql);
        }
    }

    /**
     * Map legacy application IDs to new member IDs.
     *
     * @return array<int,int>
     */
    private static function legacy_member_map(wpdb $wpdb, string $membersTable): array
    {
        $map   = [];
        $rows  = (array) $wpdb->get_results('SELECT id, legacy_application_id FROM ' . self::esc_identifier($membersTable) . ' WHERE legacy_application_id IS NOT NULL', ARRAY_A);

        foreach ($rows as $row) {
            $legacyId = isset($row['legacy_application_id']) ? (int) $row['legacy_application_id'] : 0;
            $memberId = isset($row['id']) ? (int) $row['id'] : 0;

            if ($legacyId > 0 && $memberId > 0) {
                $map[$legacyId] = $memberId;
            }
        }

        return $map;
    }

    /**
     * Rename a table if it exists and the target name is unused.
     */
    private static function rename_table(wpdb $wpdb, string $current, string $target): void
    {
        if (!self::table_exists($wpdb, $current) || self::table_exists($wpdb, $target)) {
            return;
        }

        $wpdb->query('RENAME TABLE ' . self::esc_identifier($current) . ' TO ' . self::esc_identifier($target));
    }

    /**
     * Determine whether a table exists in the database.
     */
    private static function table_exists(wpdb $wpdb, string $table): bool
    {
        $sql = $wpdb->prepare('SHOW TABLES LIKE %s', $table);

        return (bool) $wpdb->get_var($sql);
    }

    /**
     * Escape a table or column identifier for use in SQL statements.
     */
    private static function esc_identifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}

