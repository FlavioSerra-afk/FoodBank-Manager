<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Attendance;

final class Policy {
    /**
     * Determine if a visit breaches the frequency rule.
     *
     * @param string|null $last_attended UTC datetime of last 'present'.
     * @param int         $days         Policy window in days.
     * @param string      $now          Current time in UTC 'Y-m-d H:i:s'.
     */
    public static function is_breach(?string $last_attended, int $days, string $now): bool {
        if ($last_attended === null) {
            return false;
        }
        $last = strtotime($last_attended . ' UTC');
        $current = strtotime($now . ' UTC');
        if ($last === false || $current === false) {
            return false;
        }
        $day = defined( 'DAY_IN_SECONDS' ) ? DAY_IN_SECONDS : 86400;
        return ( $current - $last ) < ( $days * $day );
    }
}
