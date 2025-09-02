<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Logging;

final class Audit {
    public static function log(string $action, string $targetType, int $targetId, int $actorUserId, array $details = []): void {
        global $wpdb;
        $table = $wpdb->prefix . 'fb_audit_log';
        $wpdb->insert(
            $table,
            [
                'actor_user_id' => $actorUserId,
                'action'        => $action,
                'target_type'   => $targetType,
                'target_id'     => $targetId,
                'details_json'  => wp_json_encode($details),
                'created_at'    => current_time('mysql', true),
            ],
            ['%d','%s','%s','%d','%s','%s']
        );
    }
}
