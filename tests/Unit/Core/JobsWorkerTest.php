<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Support/JobsDbStub.php';

use FBM\Core\Jobs\JobsRepo;
use FBM\Core\Jobs\JobsWorker;
use FBM\Attendance\CheckinsRepo;
use FBM\Attendance\EventsRepo;
use Tests\Support\JobsDbStub;

final class JobsWorkerTest extends \BaseTestCase {
    private JobsDbStub $db;
    private string $dir;

    protected function setUp(): void {
        parent::setUp();
        $this->db = new JobsDbStub();
        $GLOBALS['wpdb'] = $this->db;
        $ref = new \ReflectionClass(CheckinsRepo::class);
        $p = $ref->getProperty('store');
        $p->setAccessible(true);
        $p->setValue(null, array());
        $p2 = $ref->getProperty('next_id');
        $p2->setAccessible(true);
        $p2->setValue(null, 1);
        $this->dir = sys_get_temp_dir() . '/fbm-jobs';
        if (!is_dir($this->dir)) {
            mkdir($this->dir);
        }
        $GLOBALS['wp_upload_dir'] = array('basedir' => $this->dir);
        if (!function_exists('wp_upload_dir')) {
            function wp_upload_dir() { return $GLOBALS['wp_upload_dir']; }
        }
        if (!function_exists('wp_mkdir_p')) {
            function wp_mkdir_p($d) { return mkdir($d, 0777, true); }
        }
        EventsRepo::create(array(
            'title' => 'Event',
            'starts_at' => '2024-01-01 00:00:00',
            'ends_at'   => '2024-01-01 01:00:00',
            'status'    => 'active',
        ));
        CheckinsRepo::record(array(
            'event_id'=>1,
            'recipient'=>'a@example.com',
            'token_hash'=>null,
            'method'=>'qr',
            'note'=>'',
            'by'=>1,
            'verified_at'=>'2024-01-01 00:10:00',
            'created_at'=>'2024-01-01 00:10:00',
        ));
        $GLOBALS['fbm_filters']['fbm_now'][] = fn($v) => 1700000000;
    }

    protected function tearDown(): void {
        $GLOBALS['fbm_filters']['fbm_now'] = array();
        parent::tearDown();
    }

    public function testTickSuccessAndFailure(): void {
        $id = JobsRepo::create('attendance_export', 'csv', array(), true);
        JobsWorker::tick();
        $job = JobsRepo::get($id);
        $this->assertSame('done', $job['status']);
        $this->assertFileExists($job['file_path']);

        $bad = JobsRepo::create('bad', 'csv', array(), true);
        JobsWorker::tick();
        $failed = JobsRepo::get($bad);
        $this->assertSame('failed', $failed['status']);
        $this->assertNotSame('', $failed['last_error']);
    }
}
