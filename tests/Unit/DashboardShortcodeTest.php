<?php
declare(strict_types=1);

namespace FoodBankManagerTest;

use PHPUnit\Framework\TestCase;

final class DashboardShortcodeTest extends TestCase {
    public function testSanitizePeriod(): void {
        require_once __DIR__ . '/../../includes/Shortcodes/Dashboard.php';
        $this->assertSame( '7d', \FoodBankManager\Shortcodes\Dashboard::sanitize_period( 'bad' ) );
    }

    /** @runInSeparateProcess */
    public function testUnauthorizedGated(): void {
        $GLOBALS['fbm_can_dashboard'] = false;
        if ( ! function_exists( 'current_user_can' ) ) {
            function current_user_can() { return $GLOBALS['fbm_can_dashboard']; }
        }
        if ( ! function_exists( 'esc_html__' ) ) {
            function esc_html__( $t, $d = null ) { return $t; }
        }
        require_once __DIR__ . '/../../includes/Shortcodes/Dashboard.php';
        $out = \FoodBankManager\Shortcodes\Dashboard::render();
        $this->assertStringContainsString( 'You do not have permission', $out );
    }

    /** @runInSeparateProcess */
    public function testSafeHtmlOutput(): void {
        $GLOBALS['fbm_can_dashboard'] = true;
        if ( ! function_exists( 'current_user_can' ) ) {
            function current_user_can() { return $GLOBALS['fbm_can_dashboard']; }
        }
        if ( ! function_exists( 'esc_html__' ) ) {
            function esc_html__( $t, $d = null ) { return $t; }
        }
        if ( ! function_exists( 'esc_html_e' ) ) {
            function esc_html_e( $t, $d = null ) { echo $t; }
        }
        if ( ! function_exists( 'esc_url' ) ) {
            function esc_url( $u ) { return $u; }
        }
        if ( ! function_exists( 'sanitize_key' ) ) {
            function sanitize_key( $k ) { return $k; }
        }
        if ( ! function_exists( 'shortcode_atts' ) ) {
            function shortcode_atts( $pairs, $atts, $shortcode = '' ) { return array_merge( $pairs, $atts ); }
        }
        if ( ! function_exists( 'get_current_user_id' ) ) {
            function get_current_user_id() { return 1; }
        }
        if ( ! function_exists( 'get_transient' ) ) {
            function get_transient( $k ) { return false; }
        }
        if ( ! function_exists( 'set_transient' ) ) {
            function set_transient( $k, $v, $e ) {}
        }
        if ( ! function_exists( 'current_time' ) ) {
            function current_time( $t, $g = false ) { return '2025-09-04 00:00:00'; }
        }
        if ( ! function_exists( 'wp_enqueue_style' ) ) {
            function wp_enqueue_style( $h ) {}
        }
        if ( ! function_exists( 'add_query_arg' ) ) {
            function add_query_arg( $a ) { return ''; }
        }
        if ( ! function_exists( 'number_format_i18n' ) ) {
            function number_format_i18n( $n ) { return (string) $n; }
        }
        // Stub Theme and AttendanceRepo
        eval('namespace FoodBankManager\\UI; class Theme { public static function enqueue_front(): void {} }');
        eval('namespace FoodBankManager\\Attendance; class AttendanceRepo { public static function daily_present_counts($s){ return array(1,2,3); } public static function period_totals($s){ return array("present"=>3,"households"=>2,"no_shows"=>1,"in_person"=>1,"delivery"=>2,"voided"=>0); } }');
        require_once __DIR__ . '/../../includes/Shortcodes/Dashboard.php';
        $out = \FoodBankManager\Shortcodes\Dashboard::render( array( 'period'=>'today', 'compare'=>'1', 'sparkline'=>'1' ) );
        $this->assertStringContainsString( '<svg', $out );
        $this->assertStringNotContainsString( '<script', $out );
    }
}
