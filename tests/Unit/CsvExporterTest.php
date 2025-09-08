<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Exports\CsvExporter;

/** @runTestsInSeparateProcesses */
final class CsvExporterTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        header_remove();
        error_reporting( E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED );
    }

    public function testStreamListMasksSensitiveByDefault(): void {
        $rows = array(
            array(
                'id' => 1,
                'created_at' => '2025-09-01',
                'name' => 'John',
                'email' => 'john@example.com',
                'postcode' => 'AB1 2CD',
                'status' => 'new',
            ),
        );
        ob_start();
        CsvExporter::stream_list( $rows );
        $output  = ob_get_clean();
        header_remove();

        $this->assertStringStartsWith( "\xEF\xBB\xBF", $output );
        $output = preg_replace( '/^\xEF\xBB\xBF/', '', $output );
        $lines  = array_values( array_filter( array_map( 'trim', explode( "\n", $output ) ), 'strlen' ) );
        $this->assertSame( 'ID,"Created At",Name,Email,Postcode,Status', $lines[0] );
        $this->assertSame( '1,2025-09-01,John,j***@example.com,"AB* 2**",new', $lines[1] );
    }

    public function testStreamListUnmaskedWhenAllowed(): void {
        $rows = array(
            array(
                'id' => 1,
                'created_at' => '2025-09-01',
                'name' => 'John',
                'email' => 'john@example.com',
                'postcode' => 'AB1 2CD',
                'status' => 'new',
            ),
            array(
                'id' => 2,
                'created_at' => '2025-09-02',
                'name' => 'Jane',
                'email' => 'jane@example.com',
                'postcode' => 'EF3 4GH',
                'status' => 'approved',
            ),
        );
        ob_start();
        CsvExporter::stream_list( $rows, false );
        $output = ob_get_clean();
        header_remove();
        $output = preg_replace( '/^\xEF\xBB\xBF/', '', $output );
        $lines  = array_values( array_filter( array_map( 'trim', explode( "\n", $output ) ), 'strlen' ) );
        $this->assertCount( 3, $lines );
        $this->assertStringContainsString( 'john@example.com', $lines[1] );
        $this->assertStringContainsString( 'jane@example.com', $lines[2] );
    }

    public function testStreamListEmptyRowsOutputsOnlyBom(): void {
        ob_start();
        CsvExporter::stream_list( array() );
        $output = ob_get_clean();
        header_remove();
        $this->assertSame( "\xEF\xBB\xBF", $output );
    }
}
