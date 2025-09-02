<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Logging\Audit;

final class AuditTest extends TestCase {
    public function testLogInsertsRow(): void {
        global $wpdb;
        $wpdb = new class {
            public $prefix = '';
            public $args;
            public function insert($table, $data, $format) { $this->args = compact('table','data','format'); return true; }
        };
        Audit::log('attendance_void','attendance',123,5,['reason'=>'test']);
        $this->assertSame('fb_audit_log', $wpdb->args['table']);
        $this->assertSame('attendance_void', $wpdb->args['data']['action']);
        $this->assertSame(123, $wpdb->args['data']['target_id']);
        $this->assertSame(5, $wpdb->args['data']['actor_user_id']);
    }
}
