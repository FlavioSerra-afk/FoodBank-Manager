<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Support/JobsDbStub.php';

use BaseTestCase;
use FBM\Http\ExportJobsController;
use FBM\Core\Jobs\JobsRepo;
use Tests\Support\JobsDbStub;
use Tests\Support\Rbac;
use Tests\Support\Exceptions\FbmDieException;

final class ExportJobsControllerTest extends BaseTestCase {
    private JobsDbStub $db;

    protected function setUp(): void {
        parent::setUp();
        Rbac::grantManager();
        $this->db = new JobsDbStub();
        $GLOBALS['wpdb'] = $this->db;
        fbm_seed_nonce('unit');
        if (!function_exists('fbm_return_false')) { function fbm_return_false() { return false; } }
        add_filter('fbm_http_exit', 'fbm_return_false');
        $GLOBALS['wp_upload_dir'] = array('basedir' => sys_get_temp_dir());
        if (!function_exists('wp_upload_dir')) { function wp_upload_dir(){ return $GLOBALS['wp_upload_dir']; } }
        if (!function_exists('wp_mkdir_p')) { function wp_mkdir_p($d){ return mkdir($d,0777,true); } }
    }

    protected function tearDown(): void {
        remove_filter('fbm_http_exit', 'fbm_return_false');
        parent::tearDown();
    }

    public function testQueue(): void {
        $_POST = array(
            'format' => 'csv',
            'filters' => array('from' => '2024-01-01'),
            '_wpnonce' => wp_create_nonce('fbm_export_queue'),
        );
        $this->expectException(FbmDieException::class);
        ExportJobsController::queue();
        $loc = $GLOBALS['__last_redirect'] ?? '';
        $this->assertStringContainsString('notice=export_queued', $loc);
    }

    public function testDownload(): void {
        $file = sys_get_temp_dir() . '/fbm-test.csv';
        file_put_contents($file, 'a,b');
        $id = JobsRepo::create('attendance_export', 'csv', array(), true);
        JobsRepo::mark_done($id, $file);
        $_GET = array(
            'id' => $id,
            '_wpnonce' => fbm_nonce('fbm_export_download_' . $id),
        );
        ob_start();
        ExportJobsController::download();
        $body = ob_get_clean();
        $this->assertSame('a,b', $body);
        $this->assertStringContainsString('text/csv', $GLOBALS['__fbm_sent_headers'][0]);
    }

    public function testDownloadRedirectWhenPending(): void {
        $id = JobsRepo::create('attendance_export', 'csv', array(), true);
        $_GET = array(
            'id' => $id,
            '_wpnonce' => fbm_nonce('fbm_export_download_' . $id),
        );
        $this->expectException(FbmDieException::class);
        ExportJobsController::download();
        $loc = $GLOBALS['__last_redirect'] ?? '';
        $this->assertStringContainsString('export_pending', $loc);
    }
}
