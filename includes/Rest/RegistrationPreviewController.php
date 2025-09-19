<?php
/**
 * Registration preview REST controller.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Rest;

use FoodBankManager\Registration\Editor\TemplateRenderer;
use FoodBankManager\Registration\Editor\TagParser;
use WP_REST_Request;
use WP_REST_Response;
use function current_user_can;
use function esc_html__;
use function register_rest_route;
use function rest_ensure_response;
use function wp_create_nonce;
use function wp_verify_nonce;

/**
 * Provides admin-only previews of registration templates.
 */
final class RegistrationPreviewController {
        /**
         * Register REST routes.
         */
        public static function register_routes(): void {
                register_rest_route(
                        'fbm/v1',
                        '/registration/preview',
                        array(
                                array(
                                        'methods'             => 'POST',
                                        'callback'            => array( __CLASS__, 'handle_preview' ),
                                        'permission_callback' => array( __CLASS__, 'can_preview' ),
                                        'args'                => array(
                                                'template' => array(
                                                        'type'     => 'string',
                                                        'required' => true,
                                                ),
                                        ),
                                ),
                        )
                );
        }

        /**
         * Determine whether the current user may request previews.
         *
         * @param WP_REST_Request $request Incoming request.
         */
        public static function can_preview( WP_REST_Request $request ): bool {
                $nonce = $request->get_header( 'X-WP-Nonce' );

                if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
                        return false;
                }

                return current_user_can( 'fbm_manage' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
        }

        /**
         * Handle preview requests.
         *
         * @param WP_REST_Request $request Incoming request.
         */
        public static function handle_preview( WP_REST_Request $request ): WP_REST_Response {
                $template = (string) $request->get_param( 'template' );
                $template = TemplateRenderer::sanitize_template( $template );

                $renderer = new TemplateRenderer( new TagParser() );
                $rendered = $renderer->render( $template, array(), array() );

                $markup = '<div class="fbm-registration-preview">' . $rendered['html'] . '<p class="fbm-preview-note">' . esc_html__( 'Preview only. Form controls are disabled.', 'foodbank-manager' ) . '</p></div>';

                return rest_ensure_response(
                        array(
                                'markup'   => $markup,
                                'warnings' => $rendered['warnings'],
                                'nonce'    => wp_create_nonce( 'fbm_registration_preview_modal' ),
                        )
                );
        }
}
