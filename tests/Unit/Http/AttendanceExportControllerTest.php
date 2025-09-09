<?php
declare(strict_types=1);

namespace Tests\Unit\Http {

use FBM\Http\AttendanceExportController;
use FBM\Attendance\CheckinsRepo;
use FBM\Attendance\EventsRepo;
use Tests\Support\EventsDbStub;
use Tests\Support\Rbac;
use ZipArchive;

final class AttendanceExportControllerTest extends \BaseTestCase {
    private $oldDb;

    protected function setUp(): void {
        parent::setUp();
        $ref = new \ReflectionClass(CheckinsRepo::class);
        $p = $ref->getProperty('store');
        $p->setAccessible(true);
        $p->setValue(null, array());
        $p2 = $ref->getProperty('next_id');
        $p2->setAccessible(true);
        $p2->setValue(null, 1);
        $this->oldDb = $GLOBALS['wpdb'] ?? null;
        $db = new EventsDbStub();
        $db->prefix = 'wp_';
        $GLOBALS['wpdb'] = $db;
        EventsRepo::create(array(
            'title'=>'Event 1',
            'starts_at'=>'2023-11-14 00:00:00',
            'ends_at'=>'2023-11-14 01:00:00',
            'status'=>'active',
        ));
        CheckinsRepo::record(array(
            'event_id'=>1,
            'recipient'=>'j@example.com',
            'token_hash'=>null,
            'method'=>'qr',
            'note'=>'note',
            'by'=>1,
            'verified_at'=>'2023-11-14 09:00:00',
            'created_at'=>'2023-11-14 09:00:00',
        ));
        fbm_seed_nonce('unit-seed');
        unset($GLOBALS['__fbm_sent_headers']);
    }

    protected function tearDown(): void {
        $GLOBALS['wpdb'] = $this->oldDb;
        parent::tearDown();
    }

    public function testCsvExport(): void {
        Rbac::grantManager();
        $_GET = array('action'=>'fbm_export_attendance_csv','_wpnonce'=>wp_create_nonce('fbm_attendance_export'));
        add_filter('fbm_http_exit', 'fbm_return_false');
        ob_start();
        AttendanceExportController::handle();
        $out = ob_get_clean();
        remove_filter('fbm_http_exit', 'fbm_return_false');
        $this->assertStringContainsString('date,event', $out);
        $this->assertStringContainsString('j***@example.com', $out);
        $this->assertStringContainsString('text/csv', $GLOBALS['__fbm_sent_headers'][0]);
    }

    public function testXlsxExport(): void {
        Rbac::grantManager();
        $_GET = array('action'=>'fbm_export_attendance_xlsx','_wpnonce'=>wp_create_nonce('fbm_attendance_export'));
        add_filter('fbm_http_exit', 'fbm_return_false');
        ob_start();
        AttendanceExportController::handle();
        $out = ob_get_clean();
        remove_filter('fbm_http_exit', 'fbm_return_false');
        $this->assertStringStartsWith('PK', $out);
        $tmp = tempnam(sys_get_temp_dir(), 'fbm');
        file_put_contents($tmp, $out);
        $zip = new ZipArchive();
        $zip->open($tmp);
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $this->assertStringContainsString('j***@example.com', (string)$sheet);
        $zip->close();
        unlink($tmp);
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $GLOBALS['__fbm_sent_headers'][0]);
    }

    public function testPdfExport(): void {
        Rbac::grantManager();
        $_GET = array('action'=>'fbm_export_attendance_pdf','_wpnonce'=>wp_create_nonce('fbm_attendance_export'));
        add_filter('fbm_http_exit', 'fbm_return_false');
        ob_start();
        AttendanceExportController::handle();
        $out = ob_get_clean();
        remove_filter('fbm_http_exit', 'fbm_return_false');
        $this->assertNotEmpty($out);
        $header = $GLOBALS['__fbm_sent_headers'][0];
        $this->assertTrue(strpos($header, 'application/pdf') !== false || strpos($header, 'text/html') !== false);
    }
}
}

namespace {
    function fbm_return_false() { return false; }
}
