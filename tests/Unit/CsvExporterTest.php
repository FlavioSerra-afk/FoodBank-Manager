<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Exports\CsvExporter;

final class CsvExporterTest extends TestCase {
    protected $backupGlobals = false;
    protected function setUp(): void {
        parent::setUp();
        unset( $GLOBALS['__fbm_sent_headers'], $GLOBALS['__fbm_bom_written'] );
    }

    public function testStreamListMasksSensitiveByDefault(): void {
        $rows = array(
            array(
                'id' => '1',
                'created_at' => '2025-09-01',
                'name' => 'John',
                'email' => 'john@example.com',
                'postcode' => 'AB1 2CD',
                'status' => 'new',
            ),
        );
        $cols = array(
            'id' => 'ID',
            'created_at' => 'Created At',
            'name' => 'Name',
            'email' => 'Email',
            'postcode' => 'Postcode',
            'status' => 'Status',
        );
        ob_start();
        CsvExporter::stream_list( $rows, $cols );
        $output  = ob_get_clean();

        $this->assertStringStartsWith( "\xEF\xBB\xBF", $output );
        $output = preg_replace( '/^\xEF\xBB\xBF/', '', $output );
        $lines  = array_values( array_filter( array_map( 'trim', explode( "\n", $output ) ), 'strlen' ) );
        $this->assertSame( 'ID,"Created At",Name,Email,Postcode,Status', $lines[0] );
        $this->assertSame( '1,2025-09-01,John,j***@example.com,"AB* 2**",new', $lines[1] );
    }

    public function testStreamListUnmaskedWhenAllowed(): void {
        $rows = array(
            array(
                'id' => '1',
                'created_at' => '2025-09-01',
                'name' => 'John',
                'email' => 'john@example.com',
                'postcode' => 'AB1 2CD',
                'status' => 'new',
            ),
            array(
                'id' => '2',
                'created_at' => '2025-09-02',
                'name' => 'Jane',
                'email' => 'jane@example.com',
                'postcode' => 'EF3 4GH',
                'status' => 'approved',
            ),
        );
        $cols = array(
            'id' => 'ID',
            'created_at' => 'Created At',
            'name' => 'Name',
            'email' => 'Email',
            'postcode' => 'Postcode',
            'status' => 'Status',
        );
        ob_start();
        CsvExporter::stream_list( $rows, $cols, false );
        $output = ob_get_clean();
        $this->assertStringStartsWith( "\xEF\xBB\xBF", $output );
        $output = preg_replace( '/^\xEF\xBB\xBF/', '', $output );
        $lines  = array_values( array_filter( array_map( 'trim', explode( "\n", $output ) ), 'strlen' ) );
        $this->assertCount( 3, $lines );
        $this->assertStringContainsString( 'john@example.com', $lines[1] );
        $this->assertStringContainsString( 'jane@example.com', $lines[2] );
    }

    public function testStreamListEmptyRowsOutputsOnlyBom(): void {
        $cols = array( 'id' => 'ID' );
        ob_start();
        CsvExporter::stream_list( array(), $cols );
        $output = ob_get_clean();
        $this->assertSame( "\xEF\xBB\xBF", $output );
    }
}
