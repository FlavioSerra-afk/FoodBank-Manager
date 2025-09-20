<?php // phpcs:ignoreFile WordPress.Files.FileName
/**
 * Registration editor REST controller.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Rest;

use FoodBankManager\Admin\RegistrationEditorPage;
use FoodBankManager\Registration\Editor\Conditions;
use FoodBankManager\Registration\Editor\EditorState;
use FoodBankManager\Registration\Editor\TemplateDefaults;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use function current_user_can;
use function esc_html__;
use function get_option;
use function is_array;
use function is_string;
use function json_decode;
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

                register_rest_route(
                        'fbm/v1',
                        '/registration/editor/conditions/preview',
                        array(
                                array(
                                        'methods'             => 'POST',
                                        'callback'            => array( __CLASS__, 'handle_conditions_preview' ),
                                        'permission_callback' => array( __CLASS__, 'permissions_check' ),
                                        'args'                => array(
                                                'payload' => array(
                                                        'type'     => 'string',
                                                        'required' => true,
                                                ),
                                        ),
                                ),
                        )
                );

                register_rest_route(
                        'fbm/v1',
                        '/registration/editor/conditions/diff',
                        array(
                                array(
                                        'methods'             => 'POST',
                                        'callback'            => array( __CLASS__, 'handle_conditions_diff' ),
                                        'permission_callback' => array( __CLASS__, 'permissions_check' ),
                                        'args'                => array(
                                                'original' => array(
                                                        'type'     => 'object',
                                                        'required' => true,
                                                ),
                                                'mapping'  => array(
                                                        'type'     => 'object',
                                                        'required' => true,
                                                ),
                                        ),
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

        /**
         * Provide a preview summary for rule imports.
         *
         * @param WP_REST_Request $request Incoming request.
         *
         * @return WP_REST_Response|WP_Error
         */
        public static function handle_conditions_preview( WP_REST_Request $request ) {
                $payload_raw = (string) $request->get_param( 'payload' );
                $decoded     = json_decode( $payload_raw, true );

                if ( ! is_array( $decoded ) ) {
                        return rest_ensure_response(
                                new WP_Error( 'fbm_invalid_import', esc_html__( 'Unable to parse import payload.', 'foodbank-manager' ), array( 'status' => 400 ) )
                        );
                }

                $template = get_option( 'fbm_registration_template', TemplateDefaults::template() );
                if ( ! is_string( $template ) ) {
                        $template = TemplateDefaults::template();
                }

                $fields  = RegistrationEditorPage::field_catalog( $template );
                $preview = Conditions::preview_import( $decoded, $fields );

                return rest_ensure_response(
                        array(
                                'schemaVersion' => $preview['schemaVersion'],
                                'currentSchema' => Conditions::SCHEMA_VERSION,
                                'enabled'       => $preview['enabled'],
                                'groups'        => $preview['groups'],
                                'fields'        => $preview['fields'],
                                'analysis'      => $preview['analysis'],
                        )
                );
        }

        /**
         * Provide a diff summary for import payloads with a mapping.
         *
         * @param WP_REST_Request $request Incoming request.
         *
         * @return WP_REST_Response|WP_Error
         */
    public static function handle_conditions_diff( WP_REST_Request $request ): WP_REST_Response|WP_Error {
                $original = $request->get_param( 'original' );
                $mapping  = $request->get_param( 'mapping' );

                if ( ! is_array( $original ) || ! is_array( $mapping ) ) {
                        return rest_ensure_response(
                                new WP_Error( 'fbm_invalid_import', esc_html__( 'Import payload is invalid.', 'foodbank-manager' ), array( 'status' => 400 ) )
                        );
                }

                $schema_version = isset( $original['schema']['version'] ) ? (int) $original['schema']['version'] : 0;
                if ( Conditions::SCHEMA_VERSION !== $schema_version ) {
                        return rest_ensure_response(
                                new WP_Error( 'fbm_invalid_schema', esc_html__( 'Import failed. The export file is from an incompatible version.', 'foodbank-manager' ), array( 'status' => 400 ) )
                        );
                }

                $template = get_option( 'fbm_registration_template', TemplateDefaults::template() );
                if ( ! is_string( $template ) ) {
                        $template = TemplateDefaults::template();
                }

                $fields      = RegistrationEditorPage::field_catalog( $template );
                $preview     = Conditions::preview_import( $original, $fields );
                $normalized  = array();
                foreach ( $mapping as $incoming => $target ) {
                        if ( is_array( $target ) || is_object( $target ) ) {
                                continue;
                        }

                        $source = sanitize_key( (string) $incoming );
                        $mapped = sanitize_key( (string) $target );

                        if ( '' === $source || '' === $mapped ) {
                                continue;
                        }

                        $normalized[ $source ] = $mapped;
                }

        $result         = Conditions::apply_import( $original, $normalized, $fields );
        $preview_groups = array_values( (array) $preview['groups'] );
        $skipped_index  = array();
        $skipped_list   = (array) $result['skipped'];

        foreach ( $skipped_list as $skip ) {
                if ( ! is_array( $skip ) ) {
                        continue;
                }

                $position = (int) $skip['position'];
                if ( $position <= 0 ) {
                        continue;
                }

                $missing = array_values( (array) $skip['missing'] );
                $reason  = (string) $skip['reason'];

                $skipped_index[ $position ] = array(
                        'reason'  => $reason,
                        'missing' => $missing,
                );
        }

                $diff           = array();
                $summary_import = array();
                $summary_skip   = array();
                $resolved_index = 0;

                foreach ( $preview_groups as $index => $group ) {
                        $position = $index + 1;
                        /* translators: %d: Group number displayed in the import diff. */
                        $label    = sprintf( esc_html__( 'Group %d', 'foodbank-manager' ), $position );

                        if ( isset( $skipped_index[ $position ] ) ) {
                                $skip_data = $skipped_index[ $position ];
                                $diff[]    = array(
                                        'index'    => $index,
                                        'status'   => 'skip',
                                        'reason'   => $skip_data['reason'],
                                        'missing'  => $skip_data['missing'],
                                        'original' => $group,
                                        'resolved' => array(),
                                );

                                $summary_skip[] = array(
                                        'label'   => $label,
                                        'reason'  => $skip_data['reason'],
                                        'missing' => $skip_data['missing'],
                                );
                                continue;
                        }

        $resolved_group = $result['groups'][ $resolved_index ] ?? array(
                'conditions' => array(),
                'actions'    => array(),
        );
        ++$resolved_index;

        $diff[] = array(
                                'index'    => $index,
                                'status'   => 'import',
                                'reason'   => '',
                                'missing'  => array(),
                                'original' => $group,
                                'resolved' => $resolved_group,
                        );

        $conditions_count = count( $resolved_group['conditions'] );
        $actions_count    = count( $resolved_group['actions'] );

        $summary_import[] = array(
                'label'      => $label,
                'conditions' => $conditions_count,
                'actions'    => $actions_count,
        );
                }

                return rest_ensure_response(
                        array(
                                'schemaVersion' => $preview['schemaVersion'],
                                'currentSchema' => Conditions::SCHEMA_VERSION,
                                'enabled'       => array(
                                        'incoming' => ! empty( $preview['enabled'] ),
                                        'resolved' => ! empty( $result['enabled'] ),
                                ),
                                'diff'          => $diff,
                                'summary'       => array(
                                        'import' => $summary_import,
                                        'skip'   => $summary_skip,
                                ),
                        )
                );
        }
}
