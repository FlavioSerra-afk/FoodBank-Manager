<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\DatabasePage;

if ( ! function_exists( 'wp_unslash' ) ) {
    function wp_unslash( $value ) {
        return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( (string) $value );
    }
}

final class DatabasePageTest extends TestCase {
    private function getFilters(): array {
        $ref    = new ReflectionClass( DatabasePage::class );
        $method = $ref->getMethod( 'get_filters' );
        $method->setAccessible( true );
        return $method->invoke( null );
    }

    public function testFilterSanitizationAndWhitelist(): void {
        $_GET = array(
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
        );
        $filters = $this->getFilters();
        $this->assertSame( 'hello', $filters['search'] );
        $this->assertSame( '', $filters['status'] );
        $this->assertTrue( $filters['has_file'] );
        $this->assertTrue( $filters['consent'] );
        $this->assertSame( '2025-09-01', $filters['date_from'] );
        $this->assertSame( '', $filters['date_to'] );
        $this->assertSame( 2, $filters['page'] );
        $this->assertSame( 100, $filters['per_page'] );
        $this->assertSame( 'created_at', $filters['orderby'] );
        $this->assertSame( 'DESC', $filters['order'] );
    }

    public function testValidOrderbyMapping(): void {
        $_GET = array(
            'orderby' => 'status',
            'order'   => 'asc',
        );
        $filters = $this->getFilters();
        $this->assertSame( 'status', $filters['orderby'] );
        $this->assertSame( 'ASC', $filters['order'] );
    }
}
