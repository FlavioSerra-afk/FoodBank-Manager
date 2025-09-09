<?php
/**
 * Dashboard admin page.
 *
 * @package FBM\Admin
 */

declare(strict_types=1);

namespace FBM\Admin;

use FBM\Attendance\ReportsService;
use FBM\Attendance\EventsRepo;
use FBM\Core\Jobs\JobsRepo;
use FoodBankManager\Core\Options;
use FoodBankManager\Database\ApplicationsRepo;
use function current_user_can;
use function esc_html__;

final class DashboardPage {
    public static function route(): void {
        if (!current_user_can('fb_manage_dashboard') && !current_user_can('manage_options')) {
            echo '<div class="wrap fbm-admin"><p>' . esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) . '</p></div>';
            return;
        }
        $registrations = ApplicationsRepo::list(array('per_page' => 1));
        $registrations_total = (int) $registrations['total'];
        $summary = ReportsService::period_summary();
        $recent = array_sum(array_map('intval', array_column(ReportsService::daily_counts(7)['days'], 'total')));
        $events_active = 0;
        if (Options::get('modules.events', false)) {
            $events_active = (int) EventsRepo::list(array('status' => 'active'), array('limit' => 1))['total'];
        }
        $forms_7d = (int) ApplicationsRepo::list(
            array('date_from' => gmdate('Y-m-d', strtotime('-7 days')),
                'per_page' => 1)
        )['total'];
        $jobs_pending = JobsRepo::pending_count();
        require FBM_PATH . 'templates/admin/dashboard.php';
    }
}
