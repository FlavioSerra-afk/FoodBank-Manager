<?php
/**
 * mPDF-backed PDF renderer.
 *
 * @package FoodBankManager\Exports
 */

declare(strict_types=1);

namespace FoodBankManager\Exports;

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

/**
 * Thin wrapper around mPDF.
 */
final class PdfRenderer {
    /**
     * Render HTML to PDF binary.
     *
     * @param string $html HTML body.
     * @param array $opts Options: paper, orientation, margin, header_html, footer_html, fonts.
     * @return string Binary PDF.
     */
    public static function render( string $html, array $opts = [] ): string {
        $paper       = $opts['paper'] ?? 'A4';
        $orientation = $opts['orientation'] ?? 'portrait';
        $margin      = (int) ( $opts['margin'] ?? 15 );
        $header      = $opts['header_html'] ?? '';
        $footer      = $opts['footer_html'] ?? '';
        $fonts       = $opts['fonts'] ?? [];

        $cfgVars  = new ConfigVariables();
        $fontVars = new FontVariables();
        $config   = [
            'mode'          => 'utf-8',
            'format'        => $paper,
            'orientation'   => 'landscape' === $orientation ? 'L' : 'P',
            'margin_left'   => $margin,
            'margin_right'  => $margin,
            'margin_top'    => $margin,
            'margin_bottom' => $margin,
            'fontDir'       => $cfgVars->getDefaults()['fontDir'],
            'fontdata'      => $fontVars->getDefaults()['fontdata'],
        ];
        if ( ! empty( $fonts ) && is_array( $fonts ) ) {
            foreach ( $fonts as $name => $path ) {
                $config['fontdata'][ $name ] = [ 'R' => $path ];
            }
        }
        if ( ! isset( $_SERVER['PHP_SELF'] ) ) {
            $_SERVER['PHP_SELF'] = '';
        }
        $mpdf = new Mpdf( $config );
        if ( $header !== '' ) {
            $mpdf->SetHTMLHeader( $header );
        }
        if ( $footer !== '' ) {
            $mpdf->SetHTMLFooter( $footer );
        }
        $mpdf->WriteHTML( $html );
        return (string) $mpdf->Output( '', 'S' );
    }
}

