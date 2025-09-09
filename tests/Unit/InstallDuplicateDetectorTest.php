<?php
declare(strict_types=1);

use FoodBankManager\Core\Install;
use FoodBankManager\Admin\Notices;
use Tests\Support\Exceptions\FbmDieException;

final class InstallDuplicateDetectorTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        $GLOBALS['fbm_test_screen_id'] = 'toplevel_page_fbm';
        fbm_grant_admin();
        $GLOBALS['fbm_user_caps']['delete_plugins'] = true;
        $GLOBALS['fbm_test_plugins'] = [
            'foodbank-manager/foodbank-manager.php' => ['Name' => 'FoodBank Manager'],
            'FoodBank-Manager-1.2.12/foodbank-manager.php' => ['Name' => 'FoodBank Manager'],
        ];
        $GLOBALS['fbm_test_deactivated'] = [];
        $GLOBALS['fbm_test_deleted'] = [];
        $GLOBALS['__deactivated_plugins'] = [];
    }

    public function testNoticeRendersForDuplicates(): void {
        Install::detect_duplicates();
        ob_start();
        Notices::render();
        $out = ob_get_clean();
        $this->assertStringContainsString('Multiple FoodBank Manager copies detected', $out);
    }

    public function testConsolidateActionDeactivatesDuplicates(): void {
        Install::detect_duplicates();
        fbm_test_set_request_nonce('fbm_consolidate');
        try {
            Notices::handle_consolidate_plugins();
            $this->fail('No redirect');
        } catch (FbmDieException $e) {
            $this->assertSame('redirect', $e->getMessage());
        }
        $this->assertSame(['FoodBank-Manager-1.2.12/foodbank-manager.php'], $GLOBALS['__deactivated_plugins']);
        $url = (string) $GLOBALS['__last_redirect'];
        $this->assertStringContainsString('https://example.test/wp-admin/plugins.php', $url);
        $this->assertStringContainsString('fbm_consolidated=1', $url);
        $this->assertStringContainsString('deleted=1', $url);
        $opt = get_option('fbm_last_consolidation');
        $this->assertSame(1, $opt['count']);
    }

    public function testConsolidateActionNoOp(): void {
        fbm_test_set_request_nonce('fbm_consolidate');
        try {
            Notices::handle_consolidate_plugins();
            $this->fail('No redirect');
        } catch (FbmDieException $e) {
            $this->assertSame('redirect', $e->getMessage());
        }
        $url = (string) $GLOBALS['__last_redirect'];
        $this->assertStringContainsString('https://example.test/wp-admin/plugins.php', $url);
        $this->assertStringContainsString('fbm_consolidated=1', $url);
        $this->assertStringContainsString('deleted=0', $url);
    }
}
