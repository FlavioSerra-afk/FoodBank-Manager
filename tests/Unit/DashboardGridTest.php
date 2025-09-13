<?php
final class DashboardGridTest extends \BaseTestCase {
    public function test_dashboard_grid_css(): void {
        $admin = (string) file_get_contents(FBM_PATH . 'assets/css/admin.css');
        $public = (string) file_get_contents(FBM_PATH . 'assets/css/public.css');
        $this->assertStringContainsString('.fbm-dashboard{display:grid', $public);
        $this->assertStringContainsString('grid-template-columns:repeat(3,minmax(0,1fr))', $public);
        $this->assertStringContainsString('.fbm-themed .fbm-dashboard', $admin);
        $this->assertStringContainsString('grid-template-columns:repeat(3,minmax(0,1fr))', $admin);
    }
}
