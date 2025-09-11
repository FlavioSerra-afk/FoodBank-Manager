<?php
/**
 * Diagnostics PDF panel controller.
 *
 * @package FoodBankManager\Admin
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Exports\PdfRenderer;
use FoodBankManager\Core\Options;
use function check_admin_referer;
use function current_user_can;
use function esc_html__;
use function sanitize_hex_color;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function wp_die;
use function wp_get_attachment_url;

/**
 * Controller for Diagnostics → PDF panel.
 */
final class DiagnosticsPdf {
    public const ACTION_PREVIEW = 'fbm_diag_pdf_preview';

    /**
     * Handle preview action.
     */
    public static function preview(): void {
        check_admin_referer( self::ACTION_PREVIEW );
        if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
            wp_die( esc_html__( 'Insufficient permissions', 'foodbank-manager' ) );
        }
        $settings = self::sanitize_settings( $_POST );
        Options::set( 'pdf.brand', $settings );
        $brand             = $settings;
        $brand['logo_url'] = $settings['logo'] ? (string) wp_get_attachment_url( $settings['logo'] ) : '';
        $letter            = require FBM_PATH . 'templates/pdf/letterhead.php';
        $html              = '<p>' . esc_html__( 'PDF preview body', 'foodbank-manager' ) . '</p>';
        $pdf = PdfRenderer::render( $html, array(
            'paper'       => $settings['page_size'],
            'orientation' => $settings['orientation'],
            'header_html' => $letter['header_html'] ?? '',
            'footer_html' => $letter['footer_html'] ?? '',
        ) );
        header( 'Content-Type: application/pdf' );
        echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        exit;
    }

    /**
     * Sanitize settings from request.
     *
     * @param array $raw Raw input.
     * @return array<string,mixed>
     */
    public static function sanitize_settings( array $raw ): array {
        return array(
            'logo'        => isset( $raw['logo'] ) ? (int) $raw['logo'] : 0,
            'org_name'    => sanitize_text_field( (string) ( $raw['org_name'] ?? '' ) ),
            'org_address' => sanitize_textarea_field( (string) ( $raw['org_address'] ?? '' ) ),
            'primary_color' => sanitize_hex_color( (string) ( $raw['primary_color'] ?? '#000000' ) ) ?: '#000000',
            'footer_text' => sanitize_text_field( (string) ( $raw['footer_text'] ?? '' ) ),
            'page_size'   => sanitize_text_field( (string) ( $raw['page_size'] ?? 'A4' ) ),
            'orientation' => 'landscape' === ( $raw['orientation'] ?? 'portrait' ) ? 'landscape' : 'portrait',
        );
    }

    /**
     * Render panel.
     */
    public static function render_panel(): void {
        $brand = Options::get( 'pdf.brand', array() );
        /* @psalm-suppress UnresolvableInclude */
        require FBM_PATH . 'templates/admin/diagnostics-pdf.php';
    }
}

