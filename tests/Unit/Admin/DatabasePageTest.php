<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

use BaseTestCase;
use FoodBankManager\Admin\DatabasePage;
use Tests\Support\Rbac;

final class DatabasePageTest extends BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
        if (!class_exists('FoodBankManager\\Database\\ApplicationsRepo', false)) {
            require_once __DIR__ . '/../../Support/ApplicationsRepoStub.php';
        }
    }

    public function testRenderDenied(): void {
        Rbac::revokeAll();
        $this->expectException(\Tests\Support\Exceptions\FbmDieException::class);
        $this->expectExceptionMessage('You do not have permission');
        DatabasePage::route();
    }

    public function testFilterInputsSanitizedAndWhitelisted(): void {
        Rbac::grantManager();
        $_GET = array(
            'search'   => '<script>alert(1)</script>',
            'orderby'  => 'status',
            'order'    => 'asc',
            'paged'    => '2',
            'per_page' => '999',
        );
        $_SERVER['QUERY_STRING'] = http_build_query($_GET);
        ob_start();
        DatabasePage::route();
        $html = (string) ob_get_clean();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $filters = $this->getFilters();
        $this->assertSame('status', $filters['orderby']);
        $this->assertSame('ASC', $filters['order']);
        $this->assertSame(2, $filters['page']);
        $this->assertSame(500, $filters['per_page']);
    }

    /** @return array<string,mixed> */
    private function getFilters(): array {
        $ref = new \ReflectionClass(DatabasePage::class);
        $m = $ref->getMethod('get_filters');
        $m->setAccessible(true);
        return (array) $m->invoke(null);
    }
}
