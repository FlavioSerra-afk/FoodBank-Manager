<?php
/**
 * Minimal XLSX writer.
 *
 * @package FBM\Exports
 */

declare(strict_types=1);

namespace FBM\Exports;

use function sanitize_file_name;
use function apply_filters;
use function class_exists;
use function file_get_contents;
use function unlink;
use function tempnam;
use function sys_get_temp_dir;
use function htmlspecialchars;

/**
 * Build simple XLSX packages.
 */
final class XlsxWriter {
	/**
	 * @param array<int,string> $columns ordered list of column headers (strings)
	 * @param iterable $rows each row is an ordered list of scalar values (masked beforehand)
	 * @param array $options ['filename'=>'dashboard-YYYYMMDD.xlsx']
	 * @return array{headers:array<int,string>,body:string}
	 */
	public static function build( array $columns, iterable $rows, array $options = array() ): array {
		$now      = (int) apply_filters( 'fbm_now', time() );
		$filename = (string) ( $options['filename'] ?? ( 'dashboard-' . gmdate( 'Ymd', $now ) . '.xlsx' ) );
		$sheet    = '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
		$sheet   .= '<row r="1">';
		foreach ( $columns as $i => $col ) {
			$cell   = self::cellRef( $i + 1, 1 );
			$sheet .= '<c r="' . $cell . '" t="inlineStr"><is><t>' . self::esc( (string) $col ) . '</t></is></c>';
		}
		$sheet .= '</row>';
		$r      = 1;
		foreach ( $rows as $row ) {
			++$r;
			$sheet .= '<row r="' . $r . '">';
			foreach ( $row as $i => $val ) {
				$cell   = self::cellRef( $i + 1, $r );
				$sheet .= '<c r="' . $cell . '" t="inlineStr"><is><t>' . self::esc( (string) $val ) . '</t></is></c>';
			}
			$sheet .= '</row>';
		}
		$sheet .= '</sheetData></worksheet>';
		$files  = array(
			'[Content_Types].xml'        => self::contentTypes(),
			'_rels/.rels'                => self::rels(),
			'xl/workbook.xml'            => self::workbook(),
			'xl/worksheets/sheet1.xml'   => $sheet,
			'xl/styles.xml'              => self::styles(),
			'xl/_rels/workbook.xml.rels' => self::workbookRels(),
		);
		if ( class_exists( '\ZipArchive' ) ) {
			$tmp = tempnam( sys_get_temp_dir(), 'fbm_xlsx' );
			$zip = new \ZipArchive();
			$zip->open( $tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );
			foreach ( $files as $name => $content ) {
				$zip->addFromString( $name, $content );
			}
			$zip->close();
			$body = (string) file_get_contents( $tmp );
			if ( file_exists( $tmp ) ) {
				unlink( $tmp );
			}
		} else {
			$body = self::simpleZip( $files );
		}
		$headers = array(
			'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"',
		);
		return array(
			'headers' => $headers,
			'body'    => $body,
		);
	}

	private static function cellRef( int $col, int $row ): string {
		$letters = '';
		while ( $col > 0 ) {
			--$col;
			$letters = chr( 65 + ( $col % 26 ) ) . $letters;
			$col     = intdiv( $col, 26 );
		}
		return $letters . $row;
	}

	private static function esc( string $v ): string {
		return htmlspecialchars( $v, ENT_XML1 );
	}

	private static function contentTypes(): string {
		return '<?xml version="1.0" encoding="UTF-8"?>'
			. '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
			. '<Default Extension="xml" ContentType="application/xml"/>'
			. '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
			. '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
			. '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
			. '<Override PartName="/_rels/.rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
			. '<Override PartName="/xl/_rels/workbook.xml.rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
			. '</Types>';
	}

	private static function rels(): string {
		return '<?xml version="1.0" encoding="UTF-8"?>'
			. '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
			. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
			. '</Relationships>';
	}

	private static function workbook(): string {
		return '<?xml version="1.0" encoding="UTF-8"?>'
			. '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheets>'
			. '<sheet name="Sheet1" sheetId="1" r:id="rId1" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/>'
			. '</sheets></workbook>';
	}

	private static function workbookRels(): string {
		return '<?xml version="1.0" encoding="UTF-8"?>'
			. '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
			. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
			. '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
			. '</Relationships>';
	}

	private static function styles(): string {
		return '<?xml version="1.0" encoding="UTF-8"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"/>';
	}

	/**
	 * @param array<string,string> $files
	 */
	private static function simpleZip( array $files ): string {
		$data    = '';
		$central = '';
		$offset  = 0;
		foreach ( $files as $name => $content ) {
			$name       = str_replace( '\\', '/', $name );
			$compressed = gzcompress( $content );
			if ( $compressed === false ) {
				$compressed = $content;
				$method     = 0;
			} else {
				$compressed = substr( $compressed, 2, -4 );
				$method     = 8;
			}
			$crc      = crc32( $content );
			$len      = strlen( $content );
			$zlen     = strlen( $compressed );
			$data    .= pack( 'VvvvVVVvv', 0x04034b50, 20, 0, $method, 0, $crc, $zlen, $len, strlen( $name ), 0 )
				. $name . $compressed;
			$central .= pack( 'VvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, $method, 0, $crc, $zlen, $len, strlen( $name ), 0, 0, 0, 0, 0, $offset )
				. $name;
			$offset  += 30 + strlen( $name ) + $zlen;
		}
		$end = pack( 'VvvvvVVv', 0x06054b50, 0, 0, count( $files ), count( $files ), strlen( $central ), strlen( $data ), 0 );
		return $data . $central . $end;
	}
}
