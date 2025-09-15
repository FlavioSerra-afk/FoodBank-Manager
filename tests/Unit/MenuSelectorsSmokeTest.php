<?php
if (!defined('FBM_PATH')) {
    define('FBM_PATH', dirname(__DIR__, 2) . '/');
}

final class MenuSelectorsSmokeTest extends \BaseTestCase {
    public function test_dashboard_template_has_menu_markup(): void {
        $applications_total = 0;
        $applications_today = 0;
        $summary = array('today'=>0,'week'=>0,'month'=>0);
        $events_active = 0;
        $tickets_issued = 0;
        $tickets_issued_delta = 0;
        $tickets_revoked = 0;
        $tickets_revoked_delta = 0;
        $mail_failures_7d = 0;
        ob_start();
        include FBM_PATH . 'templates/admin/dashboard.php';
        $html = ob_get_clean();
        $this->assertStringContainsString('fbm-menu', $html);
        $this->assertStringContainsString('fbm-menu__item', $html);
        $this->assertStringContainsString('fbm-menu__icon', $html);
        $this->assertStringContainsString('is-active', $html);
        update_option('fbm_theme', fbm_theme_defaults());
        $css = \FoodBankManager\UI\Theme::css_variables_scoped();
        $this->assertStringContainsString('--fbm-menu-hover-bg', $css);
        $this->assertStringContainsString('--fbm-menu-active-bg', $css);
    }
}
