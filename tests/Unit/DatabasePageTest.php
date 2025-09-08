<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\DatabasePage;

final class DatabasePageTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_test_reset_globals();
        fbm_grant_for_page('fbm_database');
    }
    private function getFilters(): array {
        $ref    = new ReflectionClass( DatabasePage::class );
        $method = $ref->getMethod( 'get_filters' );
        $method->setAccessible( true );
        return $method->invoke( null );
    }

    protected function tearDown(): void {
        parent::tearDown();
        $_GET    = array();
        $_POST   = array();
        $_SERVER = array();
    }

    public function testFilterSanitizationAndWhitelist(): void {
        $_SERVER['QUERY_STRING'] = http_build_query(
            array(
                'search'    => '<b>hello</b>',
                'status'    => 'invalid',
                'has_file'  => '1',
                'consent'   => '1',
                'date_from' => '2025-09-01',
                'date_to'   => 'bad-date',
                'paged'     => '2',
                'per_page'  => '999',
                'orderby'   => 'bad',
                'order'     => 'drop',
            )
        );
        $filters = $this->getFilters();
        $this->assertSame( 'hello', $filters['search'] );
        $this->assertSame( '', $filters['status'] );
        $this->assertTrue( $filters['has_file'] );
        $this->assertTrue( $filters['consent'] );
        $this->assertSame( '2025-09-01', $filters['date_from'] );
        $this->assertSame( '', $filters['date_to'] );
        $this->assertSame( 2, $filters['page'] );
        $this->assertSame( 500, $filters['per_page'] );
        $this->assertSame( 'created_at', $filters['orderby'] );
        $this->assertSame( 'DESC', $filters['order'] );
    }

    public function testValidOrderbyMapping(): void {
        $_SERVER['QUERY_STRING'] = http_build_query(
            array(
                'orderby' => 'status',
                'order'   => 'asc',
            )
        );
        $filters = $this->getFilters();
        $this->assertSame( 'status', $filters['orderby'] );
        $this->assertSame( 'ASC', $filters['order'] );
    }

    public function testDefaultFilters(): void {
        $_SERVER['QUERY_STRING'] = '';
        $filters = $this->getFilters();
        $this->assertSame( '', $filters['search'] );
        $this->assertSame( '', $filters['status'] );
        $this->assertNull( $filters['has_file'] );
        $this->assertNull( $filters['consent'] );
        $this->assertSame( '', $filters['date_from'] );
        $this->assertSame( '', $filters['date_to'] );
        $this->assertSame( 1, $filters['page'] );
        $this->assertSame( 20, $filters['per_page'] );
        $this->assertSame( 'created_at', $filters['orderby'] );
        $this->assertSame( 'DESC', $filters['order'] );
    }

    public function testExportRequiresNonce(): void {
        fbm_test_trust_nonces(false);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_REQUEST = $_POST = array( 'fbm_action' => 'export_entries' );
        $this->expectException( RuntimeException::class );
        DatabasePage::route();
    }
}
