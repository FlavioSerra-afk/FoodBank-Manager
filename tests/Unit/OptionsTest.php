<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Core\Options;

final class OptionsTest extends TestCase {
    public function setUp(): void {
        global $fbm_test_options;
        $fbm_test_options = [];
    }

    public function testDefaultsAndSetGet(): void {
        $this->assertSame(7, Options::get('attendance.policy_days'));
        Options::set('attendance.policy_days', 3);
        $this->assertSame(3, Options::get('attendance.policy_days'));
    }

    public function testSaveAllSanitizes(): void {
        Options::saveAll([
            'emails' => ['from_email' => ' test@example.com ', 'from_name' => ' Test '],
            'attendance' => ['types' => 'in_person,delivery'],
        ]);
        $this->assertSame('test@example.com', Options::get('emails.from_email'));
        $types = Options::get('attendance.types');
        $this->assertContains('in_person', $types);
        $this->assertContains('delivery', $types);
    }
}
