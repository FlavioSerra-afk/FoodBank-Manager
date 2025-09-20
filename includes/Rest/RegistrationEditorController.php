<?php // phpcs:ignoreFile WordPress.Files.FileName
/**
 * Registration editor REST controller.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Rest;

use FoodBankManager\Admin\RegistrationEditorPage;
use FoodBankManager\Registration\Editor\EditorState;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use function current_user_can;
use function esc_html__;
use function is_array;
use function is_string;
use function register_rest_route;
use function rest_ensure_response;
use function sanitize_key;
use function time;
use function wp_verify_nonce;

/**
 * Provides autosave and revision endpoints for the registration editor.
 */
final class RegistrationEditorController {
        /**
         * Register REST routes.
         */
        public static function register_routes(): void {
                register_rest_route(
                        'fbm/v1',
                        '/registration/editor/autosave',
                        array(
                                array(
                                        'methods'             => 'POST',
                                        'callback'            => array( __CLASS__, 'handle_autosave' ),
                                        'permission_callback' => array( __CLASS__, 'permissions_check' ),
                                        'args'                => array(
                                                'template' => array(
                                                        'type'     => 'string',
                                                        'required' => true,
                                                ),
                                                'settings' => array(
                                                        'type'     => 'object',
                                                        'required' => true,
                                                ),
                                        ),
                                ),
                        )
                );

                register_rest_route(
                        'fbm/v1',
                        '/registration/editor/revisions',
                        array(
                                array(
                                        'methods'             => 'GET',
                                        'callback'            => array( __CLASS__, 'handle_revisions' ),
                                        'permission_callback' => array( __CLASS__, 'permissions_check' ),
                                ),
                        )
                );

                register_rest_route(
                        'fbm/v1',
                        '/registration/editor/revisions/(?P<id>[a-zA-Z0-9_\.\-:]+)',
                        array(
                                array(
                                        'methods'             => 'GET',
                                        'callback'            => array( __CLASS__, 'handle_restore' ),
                                        'permission_callback' => array( __CLASS__, 'permissions_check' ),
                                ),
                        )
                );
        }

        /**
         * Enforce capability and nonce validation for editor endpoints.
         *
         * @param WP_REST_Request $request Incoming request.
         */
        public static function permissions_check( WP_REST_Request $request ): bool {
                $nonce = $request->get_header( 'X-WP-Nonce' );

                if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
                        return false;
                }

                return current_user_can( 'fbm_manage' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Registered capability.
        }

        /**
         * Handle autosave submissions from the editor UI.
         *
         * @param WP_REST_Request $request Incoming request.
         */
        public static function handle_autosave( WP_REST_Request $request ): WP_REST_Response {
                $template_raw  = (string) $request->get_param( 'template' );
                $settings_raw  = $request->get_param( 'settings' );
                $settings_data = is_array( $settings_raw ) ? $settings_raw : array();

                $sanitized_template = RegistrationEditorPage::sanitize_template( $template_raw );
                $sanitized_settings = RegistrationEditorPage::sanitize_settings( $settings_data );

                $context  = EditorState::current_user_context();
                $user_id  = (int) $context['user_id'];
                $user_name = $context['user_name'];

                $timestamp = time();

                EditorState::set_autosave( $user_id, array(
                        'template'  => $sanitized_template,
                        'settings'  => $sanitized_settings,
                        'timestamp' => $timestamp,
                ) );

                $revision = EditorState::record_revision( $user_id, $user_name, $sanitized_template, $sanitized_settings );

                return rest_ensure_response(
                        array(
                                'status'    => 'ok',
                                'timestamp' => $revision['timestamp'],
                                'revision'  => $revision,
                                'revisions' => EditorState::list_revisions(),
                                'payload'   => array(
                                        'template'  => $sanitized_template,
                                        'settings'  => $sanitized_settings,
                                        'timestamp' => $timestamp,
                                ),
                        )
                );
        }

        /**
         * Provide the current revision history.
         */
        public static function handle_revisions(): WP_REST_Response {
                return rest_ensure_response(
                        array(
                                'revisions' => EditorState::list_revisions(),
                        )
                );
        }

        /**
         * Restore a revision payload by identifier.
         *
         * @param WP_REST_Request $request Incoming request.
         *
         * @return WP_REST_Response|WP_Error
         */
        public static function handle_restore( WP_REST_Request $request ) {
                $revision_id = sanitize_key( (string) $request->get_param( 'id' ) );

                if ( '' === $revision_id ) {
                        return rest_ensure_response(
                                new WP_Error( 'fbm_invalid_revision', esc_html__( 'Revision not found.', 'foodbank-manager' ), array( 'status' => 404 ) )
                        );
                }

                $revision = EditorState::find_revision( $revision_id );
                if ( null === $revision ) {
                        return rest_ensure_response(
                                new WP_Error( 'fbm_invalid_revision', esc_html__( 'Revision not found.', 'foodbank-manager' ), array( 'status' => 404 ) )
                        );
                }

                return rest_ensure_response(
                        array(
                                'template' => $revision['template'],
                                'settings' => $revision['settings'],
                        )
                );
        }
}
