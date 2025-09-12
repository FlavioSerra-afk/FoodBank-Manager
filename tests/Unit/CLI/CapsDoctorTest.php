<?php
declare(strict_types=1);

namespace Tests\Unit\CLI;

require_once __DIR__ . '/../../Support/FakeIO.php';

use FoodBankManager\CLI\Commands;
use Tests\Support\FakeIO;

final class CapsDoctorTest extends \BaseTestCase {
    public function testDoctorReportsAndFixes(): void {
        $GLOBALS['fbm_is_multisite'] = true;
        $GLOBALS['fbm_sites']       = array((object) ['blog_id' => 1]);
        \fbm_grant_caps(['manage_options']);
        $role = \get_role('administrator');
        $role?->remove_cap('fbm_manage_jobs');

        $io  = new FakeIO();
        $cmd = new Commands($io);
        $cmd->caps_doctor([], []);
        $this->assertNotEmpty($io->errors);

        $io2  = new FakeIO();
        $cmd2 = new Commands($io2);
        $cmd2->caps_doctor([], ['fix' => true]);
        $this->assertTrue(\get_role('administrator')->has_cap('fbm_manage_jobs'));
    }
}

