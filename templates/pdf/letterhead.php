<?php // phpcs:ignoreFile
/**
 * Letterhead template.
 *
 * @var array{logo_url:string,org_name:string,org_address:string,footer_text:string} $brand
 */

$logo_url    = $brand['logo_url'] ?? '';
$org_name    = $brand['org_name'] ?? '';
$org_address = $brand['org_address'] ?? '';
$footer_text = $brand['footer_text'] ?? '';

$style = '<style>@page{header: html_fbm_header; footer: html_fbm_footer;}</style>';
$header_html = $style . '<htmlpageheader name="fbm_header"><div style="border-bottom:1px solid #ccc;padding-bottom:10px;display:flex;align-items:center;">';
if ( $logo_url !== '' ) {
    $header_html .= '<img src="' . esc_url( $logo_url ) . '" style="height:80px;margin-right:10px;" alt="" />';
}
$header_html .= '<div><strong>' . esc_html( $org_name ) . '</strong><br>' . esc_html( $org_address ) . '</div></div></htmlpageheader>';

$footer_html = '<htmlpagefooter name="fbm_footer"><div style="text-align:center;border-top:1px solid #ccc;margin-top:10px;padding-top:5px;font-size:10pt;">'
    . esc_html( $footer_text ) . ' — {PAGENO} / {nbpg}</div></htmlpagefooter>';

return array(
    'header_html' => $header_html,
    'footer_html' => $footer_html,
);
