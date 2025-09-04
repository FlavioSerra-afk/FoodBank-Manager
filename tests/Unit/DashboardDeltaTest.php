<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Shortcodes\Dashboard;

final class DashboardDeltaTest extends TestCase {
    public function testDeltaMath(): void {
        $this->assertSame( 10, Dashboard::delta( 110, 100 ) );
        $this->assertSame( -5, Dashboard::delta( 95, 100 ) );
        $this->assertNull( Dashboard::delta( 10, 0 ) );
    }
}
