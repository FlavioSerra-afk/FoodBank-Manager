<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\EmailsPage;

final class EmailsPageTest extends TestCase {
    protected function setUp(): void {
        if ( ! defined( 'FBM_PATH' ) ) {
            define( 'FBM_PATH', dirname( __DIR__, 1 ) . '/../' );
        }
        if ( ! defined( 'ABSPATH' ) ) {
            define( 'ABSPATH', __DIR__ );
        }
        $_GET = array();
        \ShortcodesPageTest::$can = true;
    }

    public function testCapabilityRequired(): void {
        \ShortcodesPageTest::$can = false;
        $this->expectException( RuntimeException::class );
        EmailsPage::route();
    }

    public function testListRenders(): void {
        ob_start();
        EmailsPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString( 'tpl=applicant_confirmation', $html );
        $this->assertStringContainsString( 'tpl=admin_notification', $html );
    }
}
