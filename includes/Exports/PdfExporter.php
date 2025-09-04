<?php
/**
 * PDF export utilities.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Exports;

/**
 * PDF exporter with graceful fallback.
 */
class PdfExporter {
	/**
	 * Render an entry to PDF or HTML fallback.
	 *
	 * @param array $entry  Entry data.
	 * @param array $schema Field schema (unused).
	 * @return array{filename:string,content_type:string,body:string}
	 */
	public static function render_entry( array $entry, array $schema ): array {
			unset( $schema );
			$id   = isset( $entry['id'] ) ? (int) $entry['id'] : 0;
			$date = gmdate( 'Ymd' );
			$base = 'entry-' . $id . '-' . $date;
			$html = '<h1>Entry</h1><pre>' . esc_html( wp_json_encode( $entry, JSON_PRETTY_PRINT ) ) . '</pre>';
		if ( class_exists( '\\Mpdf\\Mpdf' ) ) {
			$pdf = new \Mpdf\Mpdf();
			$pdf->WriteHTML( $html );
			return array(
				'filename'     => sanitize_file_name( $base . '.pdf' ),
				'content_type' => 'application/pdf',
				'body'         => (string) $pdf->Output( '', 'S' ),
			);
		}
		if ( class_exists( '\\TCPDF' ) ) {
			$pdf = new \TCPDF();
			$pdf->AddPage();
			$pdf->writeHTML( $html );
			return array(
				'filename'     => sanitize_file_name( $base . '.pdf' ),
				'content_type' => 'application/pdf',
				'body'         => (string) $pdf->Output( '', 'S' ),
			);
		}
			$msg      = esc_html__( 'PDF engine not installed — printed HTML provided', 'foodbank-manager' );
			$fallback = '<div class="notice notice-error"><p>' . $msg . '</p></div>' . $html;
			return array(
				'filename'     => sanitize_file_name( $base . '.html' ),
				'content_type' => 'text/html',
				'body'         => $fallback,
			);
	}
}
