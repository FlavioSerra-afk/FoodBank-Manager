<?php
// phpcs:ignoreFile
/**
 * Manager dashboard shortcode.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Shortcodes;

use FoodBankManager\Attendance\AttendanceRepo;
use FoodBankManager\UI\Theme;
use function current_time;
use function current_user_can;
use function esc_html__;
use function get_current_user_id;
use function get_transient;
use function sanitize_key;
use function set_transient;
use function shortcode_atts;
use function wp_enqueue_style;

/**
 * Dashboard shortcode.
 */
final class Dashboard {
/**
 * Render dashboard cards.
 *
 * @param array<string,string> $atts Attributes.
 * @return string
 */
public static function render( array $atts = array() ): string {
if ( ! current_user_can( 'fb_manage_dashboard' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
return '<div class="fbm-no-permission">' . esc_html__( 'You do not have permission to view the dashboard.', 'foodbank-manager' ) . '</div>';
}

Theme::enqueue_front();
wp_enqueue_style( 'fbm-frontend-dashboard' );

$atts   = shortcode_atts( array( 'period' => '7d' ), $atts, 'fbm_dashboard' );
$period = self::sanitize_period( $atts['period'] );
$since  = self::since_from_period( $period );

$user = get_current_user_id();
$key  = 'fbm_dash_' . $user . '_' . $period;
$data = get_transient( $key );
if ( ! is_array( $data ) ) {
$data = array(
'present'    => AttendanceRepo::count_present( $since ),
'households' => AttendanceRepo::count_unique_households( $since ),
'noshows'    => AttendanceRepo::count_no_shows( $since ),
'types'      => AttendanceRepo::count_by_type( $since ),
'voided'     => AttendanceRepo::count_voided( $since ),
);
set_transient( $key, $data, 60 );
}

$updated_at = current_time( 'mysql', true );

ob_start();
$counts      = $data; // local vars for template.
$period_attr = $period;
$updated     = $updated_at;
include dirname( __DIR__, 2 ) . '/templates/public/dashboard.php';
return (string) ob_get_clean();
}

/**
 * Sanitize period string.
 *
 * @param string $period Raw period.
 * @return string
 */
public static function sanitize_period( string $period ): string {
$period = sanitize_key( $period );
return in_array( $period, array( 'today', '7d', '30d' ), true ) ? $period : '7d';
}

/**
 * Convert period to since timestamp.
 *
 * @param string $period Period key.
 * @return string
 */
private static function since_from_period( string $period ): string {
$day = defined( 'DAY_IN_SECONDS' ) ? (int) DAY_IN_SECONDS : 86400;
$now = time();
switch ( $period ) {
case 'today':
$ts = strtotime( 'today UTC', $now );
break;
case '30d':
$ts = strtotime( 'today UTC', $now - ( 29 * $day ) );
break;
default:
$ts = strtotime( 'today UTC', $now - ( 6 * $day ) );
}
return gmdate( 'Y-m-d H:i:s', $ts );
}
}
