<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( ! is_plugin_active( 'foodbank-manager/foodbank-manager.php' ) ) {
        activate_plugin( 'foodbank-manager/foodbank-manager.php' );
}

update_option(
        'fbm_settings',
        array(
                'registration' => array(
                        'auto_approve' => true,
                ),
        )
);

fbm_ensure_page( 'registration', 'Registration', '[fbm_registration_form]' );
fbm_ensure_page( 'staff-dashboard', 'Staff Dashboard', '[fbm_staff_dashboard]' );

/**
 * Ensure a WordPress page exists with the provided shortcode.
 */
function fbm_ensure_page( string $slug, string $title, string $content ): void {
        $existing = get_page_by_path( $slug );

        $payload = array(
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => $content,
        );

        if ( $existing ) {
                $payload['ID'] = $existing->ID;
                wp_update_post( $payload );

                return;
        }

        wp_insert_post( $payload );
}
