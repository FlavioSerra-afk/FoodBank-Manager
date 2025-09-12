<?php
/**
 * Throttle settings REST controller.
 *
 * @package FBM\Rest
 */

declare(strict_types=1);

namespace FBM\Rest;

use FBM\Security\ThrottleSettings;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use FBM\Rest\ErrorHelper;
use function current_user_can;
use function get_editable_roles;
use function register_rest_route;
use function update_option;

/**
 * REST endpoints for throttle settings.
 */
final class ThrottleController {
    /** Register routes. */
    public function register_routes(): void {
        register_rest_route(
            'fbm/v1',
            '/throttle',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get' ),
                'permission_callback' => function (): bool {
                    return current_user_can( 'fb_manage_diagnostics' );
                },
            )
        );
        register_rest_route(
            'fbm/v1',
            '/throttle',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'update' ),
                'permission_callback' => function (): bool {
                    return current_user_can( 'fb_manage_diagnostics' );
                },
                'args'                => array(
                    'window_seconds'   => array_merge( \FoodBankManager\Rest\ArgHelper::id( false ), array(
                        'validate_callback' => static fn( $v ): bool => is_int( $v ) && $v >= 5 && $v <= 300,
                    ) ),
                    'base_limit'       => array_merge( \FoodBankManager\Rest\ArgHelper::id( false ), array(
                        'validate_callback' => static fn( $v ): bool => is_int( $v ) && $v >= 1 && $v <= 120,
                    ) ),
                    'role_multipliers' => array(
                        'type'              => 'object',
                        'required'          => false,
                        'validate_callback' => function ( $v ): bool {
                            if ( ! is_array( $v ) ) {
                                return false;
                            }
                            $roles = array_keys( get_editable_roles() );
                            foreach ( $v as $role => $mult ) {
                                if ( ! in_array( $role, $roles, true ) || ! is_numeric( $mult ) ) {
                                    return false;
                                }
                            }
                            return true;
                        },
                    ),
                ),
            )
        );
    }

    /** Handle GET. */
    public function get( WP_REST_Request $request ): WP_REST_Response {
        if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
            $err = ErrorHelper::from_wp_error( new WP_Error( 'forbidden', 'forbidden', array( 'status' => 403 ) ) );
            return new WP_REST_Response( $err['body'], $err['status'] );
        }
        return new WP_REST_Response(
            array(
                'settings' => ThrottleSettings::get(),
                'limits'   => ThrottleSettings::limits(),
            )
        );
    }

    /** Handle POST. */
    public function update( WP_REST_Request $request ): WP_REST_Response {
        if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
            $err = ErrorHelper::from_wp_error( new WP_Error( 'forbidden', 'forbidden', array( 'status' => 403 ) ) );
            return new WP_REST_Response( $err['body'], $err['status'] );
        }
        $roles = array_keys( get_editable_roles() );
        $mults = $request->get_param( 'role_multipliers' );
        if ( null !== $mults && ! is_array( $mults ) ) {
            $err = ErrorHelper::from_wp_error( new WP_Error( 'invalid_param', 'role_multipliers', array( 'status' => 422 ) ) );
            return new WP_REST_Response( $err['body'], $err['status'] );
        }
        if ( is_array( $mults ) ) {
            foreach ( $mults as $role => $mult ) {
                if ( ! in_array( $role, $roles, true ) || ! is_numeric( $mult ) ) {
                    $err = ErrorHelper::from_wp_error( new WP_Error( 'invalid_param', 'role_multipliers', array( 'status' => 422 ) ) );
                    return new WP_REST_Response( $err['body'], $err['status'] );
                }
            }
        }
        $data = array(
            'window_seconds'   => $request->get_param( 'window_seconds' ),
            'base_limit'       => $request->get_param( 'base_limit' ),
            'role_multipliers' => $mults,
        );
        $san = fbm_throttle_sanitize( $data );
        update_option( 'fbm_throttle', $san, false ); // @phpstan-ignore-line
        return new WP_REST_Response(
            array(
                'settings' => $san,
                'limits'   => ThrottleSettings::limits(),
            )
        );
    }
}

