<?php
declare(strict_types=1);

namespace Tests\Unit\Permissions;

use FoodBankManager\Admin\PermissionsAudit;

final class AuditTest extends \BaseTestCase {
    public function testAddRead(): void {
        PermissionsAudit::add('test entry');
        $log = PermissionsAudit::all();
        $this->assertNotEmpty($log);
        $this->assertSame('test entry', $log[0]['message']);
    }
}
