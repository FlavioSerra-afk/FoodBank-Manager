<?php
declare(strict_types=1);


final class JobsPanelTest extends \BaseTestCase {
    public function testPanelRenders(): void {
        fbm_seed_nonce('unit');
        $jobs = array(
            array('id'=>1,'type'=>'attendance_export','format'=>'csv','status'=>'done'),
        );
        ob_start();
        require FBM_PATH . 'templates/admin/jobs.php';
        $html = ob_get_clean();
        $this->assertStringContainsString('Export Jobs', $html);
        $this->assertStringContainsString('fbm_export_download', $html);
    }
}
