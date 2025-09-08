<?php
declare(strict_types=1);

namespace FoodBankManager\UI {
    if (!class_exists(Theme::class, false)) {
        class Theme { public static function enqueue_front(): void {} }
    }
}

namespace FoodBankManager\Attendance {
    if (!class_exists(AttendanceRepo::class, false)) {
        class AttendanceRepo {
            /** @param mixed $s @param array $f */
            public static function daily_present_counts($s, array $f = []) { return array(); }
            /** @param mixed $s @param array $f */
            public static function period_totals($s, array $f = []) { return array(); }
        }
    }
}

namespace FBM\Tests\Unit\Shortcodes {
use PHPUnit\Framework\TestCase;

final class DashboardShortcodeUXTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        \fbm_test_reset_globals();
        \fbm_grant_viewer();
        $_GET = [];
        \fbm_test_set_request_nonce('fbm_dash_export');
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
    }

    public function testEmptyStateRenders(): void {
        \fbm_grant_viewer();
        require_once FBM_PATH . 'includes/Shortcodes/DashboardShortcode.php';
        $html = \FBM\Shortcodes\DashboardShortcode::render();
        $this->assertStringContainsString('fbm-empty', $html);
    }

    public function testFilterFormLabelsAndValues(): void {
        \fbm_grant_viewer();
        require_once FBM_PATH . 'includes/Shortcodes/DashboardShortcode.php';
        $html = \FBM\Shortcodes\DashboardShortcode::render([
            'type'        => 'delivery',
            'event'       => 'abc',
            'policy_only' => '1',
        ]);
        $this->assertStringContainsString('label for="fbm_type"', $html);
        $this->assertStringContainsString('id="fbm_event"', $html);
        $this->assertStringContainsString('value="abc"', $html);
    }

    /** @runInSeparateProcess */
    public function testCopyShortcodeBlockAppearsWithCap(): void {
        \fbm_grant_admin();
        require_once FBM_PATH . 'includes/Shortcodes/DashboardShortcode.php';
        $html = \FBM\Shortcodes\DashboardShortcode::render();
        $this->assertStringContainsString('fbm-copy-shortcode', $html);
    }

    /** @runInSeparateProcess */
    public function testCopyShortcodeBlockHiddenWithoutCap(): void {
        \fbm_grant_viewer();
        require_once FBM_PATH . 'includes/Shortcodes/DashboardShortcode.php';
        $html = \FBM\Shortcodes\DashboardShortcode::render();
        $this->assertStringNotContainsString('fbm-copy-shortcode', $html);
    }

    public function testPermissionDeniedMessage(): void {
        \fbm_grant_caps([]);
        require_once FBM_PATH . 'includes/Shortcodes/DashboardShortcode.php';
        $html = \FBM\Shortcodes\DashboardShortcode::render();
        $this->assertStringContainsString('fbm-no-permission', $html);
    }
}
}
