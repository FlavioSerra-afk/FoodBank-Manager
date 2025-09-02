<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Shortcodes;

use FoodBankManager\Core\Options;
use FoodBankManager\UI\Theme;

class AttendanceManager {
    public static function render( array $atts = array() ): string {
        Theme::enqueue_front();

        if ( ! current_user_can( 'attendance_checkin' ) ) {
            return '<div class="fbm-no-permission">' . esc_html__( 'You do not have permission to check in attendees.', 'foodbank-manager' ) . '</div>';
        }
        $nonce = wp_create_nonce( 'wp_rest' );
        $types = (array) Options::get( 'attendance.types' );
        ob_start();
        $nonce_var = $nonce; // local var for template scope
        $type_opts = $types;
        include dirname( __DIR__, 2 ) . '/templates/public/attendance-manager.php';
        $content = (string) ob_get_clean();
        $density = Options::get( 'theme.frontend.density', 'comfortable' );
        $dark    = Options::get( 'theme.frontend.dark_mode', 'auto' );
        $dark_cl = $dark === 'on' ? ' fbm-dark' : ( $dark === 'off' ? ' fbm-light' : '' );
        return '<div class="fbm-scope fbm-density-' . esc_attr( $density ) . $dark_cl . '">' . $content . '</div>';
    }
}
