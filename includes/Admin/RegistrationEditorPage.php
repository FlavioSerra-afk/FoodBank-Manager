<?php // phpcs:ignoreFile WordPress.Files.FileName
/**
 * Registration form editor admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Core\Plugin;
use FoodBankManager\Registration\Editor\Conditions;
use FoodBankManager\Registration\Editor\EditorState;
use FoodBankManager\Registration\Editor\TemplateDefaults;
use FoodBankManager\Registration\Editor\TemplateRenderer;
use FoodBankManager\Registration\Editor\TagParser;
use FoodBankManager\Registration\Uploads;
use function __;
use function add_action;
use function add_query_arg;
use function add_settings_error;
use function add_submenu_page;
use function admin_url;
use function check_admin_referer;
use function current_user_can;
use function esc_html__;
use function esc_url;
use function explode;
use function get_current_screen;
use function get_current_user_id;
use function get_option;
use function in_array;
use function is_array;
use function is_readable;
use function is_string;
use function json_decode;
use function nocache_headers;
use function plugins_url;
use function rest_url;
use function sanitize_key;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function settings_errors;
use function settings_fields;
use function submit_button;
use function update_option;
use function wp_create_nonce;
use function wp_die;
use function wp_enqueue_code_editor;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_json_encode;
use function wp_kses_post;
use function wp_localize_script;
use function wp_nonce_field;
use function wp_parse_args;
use function wp_register_script;
use function wp_safe_redirect;
use function wp_unslash;
use function wp_verify_nonce;
use const FILTER_SANITIZE_FULL_SPECIAL_CHARS;
use const FILTER_UNSAFE_RAW;
use const INPUT_GET;
use const INPUT_POST;

/**
 * Admin page for editing the registration form template.
 */
final class RegistrationEditorPage {
	private const MENU_SLUG       = 'fbm-registration-form';
	private const OPTION_GROUP    = 'fbm_registration_editor';
	private const TEMPLATE_OPTION = 'fbm_registration_template';
	private const SETTINGS_OPTION = 'fbm_registration_settings';
	private const TEMPLATE_FIELD  = 'fbm_registration_template_field';
	private const SETTINGS_FIELD  = 'fbm_registration_settings_field';
	private const TEMPLATE_FILE   = 'templates/admin/registration-editor.php';
	private const RESET_ACTION    = 'fbm_registration_editor_reset';
	private const EXPORT_ACTION   = 'fbm_registration_editor_export';
	private const IMPORT_ACTION   = 'fbm_registration_editor_import';
	private const NOTICE_PARAM    = 'fbm_registration_editor_notice';
	private const MESSAGE_PARAM   = 'fbm_registration_editor_message';
	private const PREVIEW_NONCE   = 'fbm_registration_preview';

		/**
		 * Register WordPress hooks.
		 */
	public static function register(): void {
			add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
			add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
			add_action( 'admin_post_' . self::RESET_ACTION, array( __CLASS__, 'handle_reset' ) );
			add_action( 'admin_post_' . self::EXPORT_ACTION, array( __CLASS__, 'handle_export' ) );
			add_action( 'admin_post_' . self::IMPORT_ACTION, array( __CLASS__, 'handle_import' ) );
	}

		/**
		 * Register the submenu entry.
		 */
	public static function register_menu(): void {
			add_submenu_page(
				Menu::SLUG,
				__( 'Registration Form', 'foodbank-manager' ),
				__( 'Registration Form', 'foodbank-manager' ),
				'fbm_manage', // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
				self::MENU_SLUG,
				array( __CLASS__, 'render' )
			);
	}

		/**
		 * Register settings and fields for the editor.
		 */
	public static function register_settings(): void {
			register_setting(
				self::OPTION_GROUP,
				self::TEMPLATE_OPTION,
				array(
					'type'              => 'string',
					'sanitize_callback' => array( __CLASS__, 'sanitize_template' ),
					'default'           => TemplateDefaults::template(),
				)
			);

			register_setting(
				self::OPTION_GROUP,
				self::SETTINGS_OPTION,
				array(
					'type'              => 'array',
					'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
					'default'           => TemplateDefaults::settings(),
				)
			);
	}

		/**
		 * Render the registration editor screen.
		 */
	public static function render(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
		}

			$template_file = FBM_PATH . self::TEMPLATE_FILE;
		if ( ! is_readable( $template_file ) ) {
				wp_die( esc_html__( 'Registration editor template is missing.', 'foodbank-manager' ) );
		}

                        $template = get_option( self::TEMPLATE_OPTION, TemplateDefaults::template() );
                if ( ! is_string( $template ) ) {
                                $template = TemplateDefaults::template();
                }

                        $settings = get_option( self::SETTINGS_OPTION, TemplateDefaults::settings() );
                if ( ! is_array( $settings ) ) {
                                $settings = TemplateDefaults::settings();
                }

                        $fields         = self::field_catalog( $template );
                        $current_user_id = function_exists( 'get_current_user_id' ) ? (int) get_current_user_id() : 0;
                        $autosave_state  = EditorState::get_autosave( $current_user_id );
                        $revisions       = EditorState::list_revisions();

                        $query_notice  = filter_input( INPUT_GET, self::NOTICE_PARAM, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$query_message = filter_input( INPUT_GET, self::MESSAGE_PARAM, FILTER_UNSAFE_RAW );

			$notice  = is_string( $query_notice ) ? sanitize_key( $query_notice ) : '';
			$message = is_string( $query_message ) ? sanitize_text_field( $query_message ) : '';

			$data = array(
				'option_group'    => self::OPTION_GROUP,
				'template_option' => self::TEMPLATE_OPTION,
				'settings_option' => self::SETTINGS_OPTION,
				'template'        => $template,
                                'settings'        => wp_parse_args( $settings, TemplateDefaults::settings() ),
                                'menu_slug'       => self::MENU_SLUG,
                                'reset_action'    => self::RESET_ACTION,
                                'export_action'   => self::EXPORT_ACTION,
                                'import_action'   => self::IMPORT_ACTION,
                                'reset_nonce'     => wp_create_nonce( self::RESET_ACTION ),
                                'import_nonce'    => wp_create_nonce( self::IMPORT_ACTION ),
                                'export_nonce'    => wp_create_nonce( self::EXPORT_ACTION ),
                                'preview_nonce'   => wp_create_nonce( self::PREVIEW_NONCE ),
                                'notice'          => $notice,
                                'message'         => $message,
                                'template_field'  => self::TEMPLATE_FIELD,
                                'settings_field'  => self::SETTINGS_FIELD,
                                'snippets'        => self::toolbar_snippets(),
                                'fields'          => $fields,
                                'matrix_url'      => plugins_url( 'Docs/Registration-Template-Matrix.md', FBM_FILE ),
                                'revisions'       => $revisions,
                                'autosave'        => $autosave_state,
                        );

			settings_errors( self::OPTION_GROUP );

			$context = $data;
			include $template_file;
	}

		/**
		 * Enqueue assets for the editor screen.
		 *
		 * @param string $hook_suffix Current admin page hook.
		 */
	public static function enqueue_assets( string $hook_suffix ): void {
			unset( $hook_suffix );

		if ( ! function_exists( 'get_current_screen' ) ) {
				return;
		}

			$screen = get_current_screen();
		if ( ! $screen || self::screen_id() !== $screen->id ) {
				return;
		}

			$editor_settings = wp_enqueue_code_editor( array( 'type' => 'text/html' ) );

		if ( ! empty( $editor_settings ) ) {
				wp_enqueue_script( 'code-editor' );
				wp_enqueue_style( 'code-editor' );
		} else {
				$editor_settings = array();
		}

                        $stored_settings = get_option( self::SETTINGS_OPTION, TemplateDefaults::settings() );
                        $theme           = 'light';
                if ( is_array( $stored_settings ) && isset( $stored_settings['editor']['theme'] ) && is_string( $stored_settings['editor']['theme'] ) ) {
                                $candidate = sanitize_key( $stored_settings['editor']['theme'] );
                        if ( in_array( $candidate, array( 'light', 'dark' ), true ) ) {
                                        $theme = $candidate;
                        }
                }

                        $template = get_option( self::TEMPLATE_OPTION, TemplateDefaults::template() );
                if ( ! is_string( $template ) ) {
                                $template = TemplateDefaults::template();
                }

                        $fields         = self::field_catalog( $template );
                        $settings       = wp_parse_args( is_array( $stored_settings ) ? $stored_settings : array(), TemplateDefaults::settings() );
                        $conditions     = isset( $settings['conditions'] ) && is_array( $settings['conditions'] ) ? $settings['conditions'] : TemplateDefaults::settings()['conditions'];
                        $groups         = isset( $conditions['groups'] ) && is_array( $conditions['groups'] ) ? array_values( $conditions['groups'] ) : array();
                        $current_user_id = function_exists( 'get_current_user_id' ) ? (int) get_current_user_id() : 0;
                        $autosave_state  = EditorState::get_autosave( $current_user_id );
                        $revisions       = EditorState::list_revisions();

                        $conditions_payload = array(
                                'enabled' => ! empty( $conditions['enabled'] ) && ! empty( $groups ),
                                'groups'  => $groups,
                        );

                        $matrix_url = plugins_url( 'Docs/Registration-Template-Matrix.md', FBM_FILE );

                        $version = defined( 'FBM_VER' ) ? FBM_VER : Plugin::VERSION;
                        wp_enqueue_script( 'jquery' );

        $conditions_handle = 'fbm-registration-conditions';
        $conditions_script = plugins_url( 'assets/js/registration-conditions.js', FBM_FILE );
        wp_register_script( $conditions_handle, $conditions_script, array(), $version, true );

        $trace_handle = 'fbm-registration-debugger';
        $trace_script = plugins_url( 'assets/js/registration-debugger-perf.js', FBM_FILE );
        wp_register_script( $trace_handle, $trace_script, array(), $version, true );

        $handle       = 'fbm-registration-editor';
        $script       = plugins_url( 'assets/js/registration-editor.js', FBM_FILE );
        $dependencies = array( 'jquery', $conditions_handle, $trace_handle );
                if ( ! empty( $editor_settings ) ) {
                        $dependencies[] = 'code-editor';
                }
                        wp_register_script( $handle, $script, $dependencies, $version, true );
                        $rest_nonce = wp_create_nonce( 'wp_rest' );
                        wp_localize_script(
                                $handle,
                                'fbmRegistrationEditor',
                                array(
                                        'previewNonce'      => $rest_nonce,
                                        'restNonce'         => $rest_nonce,
                                        'previewUrl'        => esc_url_raw( rest_url( 'fbm/v1/registration/preview' ) ),
                                        'importPreviewUrl'  => esc_url_raw( rest_url( 'fbm/v1/registration/editor/conditions/preview' ) ),
                                        'importDiffUrl'     => esc_url_raw( rest_url( 'fbm/v1/registration/editor/conditions/diff' ) ),
                                        'textareaId'        => self::TEMPLATE_FIELD,
                                        'settingsField'     => self::SETTINGS_FIELD,
                                        'codeEditor'        => $editor_settings,
                                        'editorTheme'       => $theme,
                                        'conditionSchema'   => Conditions::SCHEMA_VERSION,
                                        'fields'            => Conditions::normalize_fields( $fields ),
                                        'conditions'        => $conditions_payload,
                                        'presets'           => TemplateDefaults::presets(),
                                        'matrixUrl'         => esc_url( $matrix_url ),
                                        'autosave'          => array(
                                                'endpoint'          => esc_url_raw( rest_url( 'fbm/v1/registration/editor/autosave' ) ),
                                                'revisions'         => $revisions,
                                                'payload'           => $autosave_state,
                                                'restoreBase'       => esc_url_raw( rest_url( 'fbm/v1/registration/editor/revisions/' ) ),
                                                'revisionsEndpoint' => esc_url_raw( rest_url( 'fbm/v1/registration/editor/revisions' ) ),
                                                'interval'          => 30000,
                                        ),
                                        'i18n'              => array(
                                                'previewError'     => esc_html__( 'Unable to load the preview. Please save first or try again.', 'foodbank-manager' ),
                                                'closeLabel'       => esc_html__( 'Close preview', 'foodbank-manager' ),
                                                'modalDescription' => esc_html__( 'Preview only. Form controls are disabled.', 'foodbank-manager' ),
/* translators: %s: Group index. */
'groupLabel'        => esc_html__( 'Group %s', 'foodbank-manager' ),
                                                'groupOperatorLabel' => esc_html__( 'Match when', 'foodbank-manager' ),
                                                'groupOperatorAnd'  => esc_html__( 'All conditions match', 'foodbank-manager' ),
                                                'groupOperatorOr'   => esc_html__( 'Any condition matches', 'foodbank-manager' ),
                                                'addCondition'      => esc_html__( 'Add condition', 'foodbank-manager' ),
                                                'addAction'         => esc_html__( 'Add action', 'foodbank-manager' ),
                                                'removeGroup'       => esc_html__( 'Remove group', 'foodbank-manager' ),
                                                'conditionFieldLabel' => esc_html__( 'Field', 'foodbank-manager' ),
                                                'conditionOperatorLabel' => esc_html__( 'Operator', 'foodbank-manager' ),
                                                'conditionValueLabel' => esc_html__( 'Value', 'foodbank-manager' ),
                                                'conditionValuePlaceholder' => esc_html__( 'Enter a value', 'foodbank-manager' ),
                                                'removeCondition'   => esc_html__( 'Remove condition', 'foodbank-manager' ),
                                                'actionTypeLabel'   => esc_html__( 'Action', 'foodbank-manager' ),
                                                'actionShow'        => esc_html__( 'Show', 'foodbank-manager' ),
                                                'actionHide'        => esc_html__( 'Hide', 'foodbank-manager' ),
                                                'actionRequire'     => esc_html__( 'Require', 'foodbank-manager' ),
                                                'actionOptional'    => esc_html__( 'Optional', 'foodbank-manager' ),
                                                'actionTargetLabel' => esc_html__( 'Field', 'foodbank-manager' ),
                                                'removeAction'      => esc_html__( 'Remove action', 'foodbank-manager' ),
                                                'validationEmpty'   => esc_html__( 'No groups defined yet.', 'foodbank-manager' ),
/* translators: 1: Group label, 2: missing field slug. */
'validationMissingField' => esc_html__( '%1$s references a missing field (%2$s).', 'foodbank-manager' ),
/* translators: 1: Group label, 2: missing target field slug. */
'validationMissingTarget' => esc_html__( '%1$s targets an unknown field (%2$s).', 'foodbank-manager' ),
/* translators: 1: Group label, 2: field slug causing a circular reference. */
'validationCircular' => esc_html__( '%1$s both listens to and targets %2$s.', 'foodbank-manager' ),
/* translators: 1: Group label, 2: field slug involved in mutually exclusive conditions. */
'validationUnreachable' => esc_html__( '%1$s contains conditions that cannot all be true for %2$s.', 'foodbank-manager' ),
'validationPassed'  => esc_html__( 'No issues found.', 'foodbank-manager' ),
/* translators: %s: Issue count. */
'validationHasIssues' => esc_html__( '%s issues found.', 'foodbank-manager' ),
'revisionPlaceholder' => esc_html__( 'Restore revision…', 'foodbank-manager' ),
/* translators: %s: Autosave timestamp. */
'revisionAutosave'  => esc_html__( 'Autosave — %s', 'foodbank-manager' ),
'revisionUnknown'   => esc_html__( 'Autosave', 'foodbank-manager' ),
                                                'autosaveSaving'    => esc_html__( 'Saving…', 'foodbank-manager' ),
                                                'autosaveSaved'     => esc_html__( 'Saved.', 'foodbank-manager' ),
                                                'autosaveError'     => esc_html__( 'Autosave failed.', 'foodbank-manager' ),
                                                'autosaveRestored'  => esc_html__( 'Revision restored.', 'foodbank-manager' ),
                                                'autosaveRestoring' => esc_html__( 'Restoring…', 'foodbank-manager' ),
                                                'debugToggleShow'   => esc_html__( 'Show rule debugger', 'foodbank-manager' ),
                                                'debugToggleHide'   => esc_html__( 'Hide rule debugger', 'foodbank-manager' ),
/* translators: 1: Group index, 2: operator label. */
'debugGroupTitle'   => esc_html__( 'Group %1$s (%2$s)', 'foodbank-manager' ),
                                                'debugOperatorAnd'  => esc_html__( 'All conditions match', 'foodbank-manager' ),
                                                'debugOperatorOr'   => esc_html__( 'Any condition matches', 'foodbank-manager' ),
                                                'debugGroupMatched' => esc_html__( 'Matched', 'foodbank-manager' ),
                                                'debugGroupNotMatched' => esc_html__( 'Not matched', 'foodbank-manager' ),
/* translators: 1: Field label, 2: operator label, 3: comparison value, 4: match result. */
'debugConditionRow' => esc_html__( '%1$s %2$s %3$s — %4$s', 'foodbank-manager' ),
                                                'debugConditionMatched' => esc_html__( 'matched', 'foodbank-manager' ),
                                                'debugConditionNotMatched' => esc_html__( 'not matched', 'foodbank-manager' ),
                                                'debugActionsHeading' => esc_html__( 'Actions', 'foodbank-manager' ),
/* translators: 1: Action label, 2: target field label, 3: resulting state. */
'debugActionRow'    => esc_html__( '%1$s — %2$s (%3$s)', 'foodbank-manager' ),
                                                'debugAction_show'  => esc_html__( 'Show', 'foodbank-manager' ),
                                                'debugAction_hide'  => esc_html__( 'Hide', 'foodbank-manager' ),
                                                'debugAction_require' => esc_html__( 'Require', 'foodbank-manager' ),
                                                'debugAction_optional' => esc_html__( 'Optional', 'foodbank-manager' ),
                                                'debugFinalVisible' => esc_html__( 'final state: visible', 'foodbank-manager' ),
                                                'debugFinalHidden'  => esc_html__( 'final state: hidden', 'foodbank-manager' ),
                                                'debugFinalRequired' => esc_html__( 'final state: required', 'foodbank-manager' ),
                                                'debugFinalOptional' => esc_html__( 'final state: optional', 'foodbank-manager' ),
                                                'debugTraceToggle'   => esc_html__( 'Record timings', 'foodbank-manager' ),
                                                'debugTraceExport'   => esc_html__( 'Export trace (JSON)', 'foodbank-manager' ),
                                                'debugTraceHeading'  => esc_html__( 'Recent timing averages', 'foodbank-manager' ),
                                                'debugTraceEmpty'    => esc_html__( 'No timing data captured yet.', 'foodbank-manager' ),
                                                'debugTracePhase'    => esc_html__( 'Phase', 'foodbank-manager' ),
                                                'debugTraceAverage'  => esc_html__( 'Average (ms)', 'foodbank-manager' ),
                                                'debugTraceMin'      => esc_html__( 'Fastest (ms)', 'foodbank-manager' ),
                                                'debugTraceMax'      => esc_html__( 'Slowest (ms)', 'foodbank-manager' ),
                                                'debugTraceCount'    => esc_html__( 'Runs', 'foodbank-manager' ),
                                                'debugTraceAnnouncement' => esc_html__( 'Timing sample recorded.', 'foodbank-manager' ),
                                                'debugTraceExportFilename' => esc_html__( 'fbm-debug-trace.json', 'foodbank-manager' ),
                                                'debugOp_equals'    => esc_html__( 'is', 'foodbank-manager' ),
                                                'debugOp_not_equals' => esc_html__( 'is not', 'foodbank-manager' ),
                                                'debugOp_contains'  => esc_html__( 'contains', 'foodbank-manager' ),
                                                'debugOp_empty'     => esc_html__( 'is empty', 'foodbank-manager' ),
                                                'debugOp_not_empty' => esc_html__( 'is not empty', 'foodbank-manager' ),
                                                'debugOp_lt'        => esc_html__( 'is less than', 'foodbank-manager' ),
                                                'debugOp_lte'       => esc_html__( 'is less than or equal to', 'foodbank-manager' ),
                                                'debugOp_gt'        => esc_html__( 'is greater than', 'foodbank-manager' ),
                                                'debugOp_gte'       => esc_html__( 'is greater than or equal to', 'foodbank-manager' ),
                                                'operatorEquals'    => esc_html__( 'is', 'foodbank-manager' ),
                                                'operatorNotEquals' => esc_html__( 'is not', 'foodbank-manager' ),
                                                'operatorContains'  => esc_html__( 'contains', 'foodbank-manager' ),
                                                'operatorEmpty'     => esc_html__( 'is empty', 'foodbank-manager' ),
                                                'operatorNotEmpty'  => esc_html__( 'is not empty', 'foodbank-manager' ),
                                                'operatorLt'        => esc_html__( 'is less than', 'foodbank-manager' ),
                                                'operatorLte'       => esc_html__( 'is less than or equal to', 'foodbank-manager' ),
                                                'operatorGt'        => esc_html__( 'is greater than', 'foodbank-manager' ),
                                                'operatorGte'       => esc_html__( 'is greater than or equal to', 'foodbank-manager' ),
                                                'importInvalid'     => esc_html__( 'Unable to parse import JSON.', 'foodbank-manager' ),
                                                'importEmpty'       => esc_html__( 'No groups were found in the import file.', 'foodbank-manager' ),
                                                        'importPreviewHeading' => esc_html__( 'Preview', 'foodbank-manager' ),
                                                        /* translators: %d: number of groups that will be imported. */
                                                        'importSummaryReady'   => esc_html__( '%1$d groups will be imported.', 'foodbank-manager' ),
                                                        /* translators: %d: number of groups that require manual field mapping. */
                                                        'importSummaryMissing' => esc_html__( '%1$d groups need field mapping.', 'foodbank-manager' ),
                                                        'importSchemaMismatch' => esc_html__( 'Import failed. The export file is from an incompatible version.', 'foodbank-manager' ),
                                                        /* translators: 1: group number in the import file, 2: comma-separated missing field slugs. */
                                                        'importGroupMissing'   => esc_html__( 'Group %1$d missing: %2$s', 'foodbank-manager' ),
                                                'importDiffHeading'    => esc_html__( 'Import diff', 'foodbank-manager' ),
                                                'importDiffIncoming'   => esc_html__( 'Incoming groups', 'foodbank-manager' ),
                                                'importDiffResolved'   => esc_html__( 'Resolved mapping', 'foodbank-manager' ),
                                                'importDiffImportList' => esc_html__( 'Will import', 'foodbank-manager' ),
                                                'importDiffSkipList'   => esc_html__( 'Will skip', 'foodbank-manager' ),
                                                /* translators: %s: Comma-separated list of missing field slugs. */
                                                'importDiffSkipMissing' => esc_html__( 'Missing fields: %s', 'foodbank-manager' ),
                                                'importDiffSkipEmpty'  => esc_html__( 'Group has no valid conditions or actions.', 'foodbank-manager' ),
                                                'importDiffSkipUnknown' => esc_html__( 'Skipped for an unknown reason.', 'foodbank-manager' ),
                                                'importDiffNoneImport' => esc_html__( 'No groups ready to import yet.', 'foodbank-manager' ),
                                                'importDiffNoneSkip'   => esc_html__( 'No groups will be skipped.', 'foodbank-manager' ),
                                                'importDiffError'      => esc_html__( 'Unable to load diff. Check mappings and try again.', 'foodbank-manager' ),
                                                'importDiffLoading'    => esc_html__( 'Calculating diff…', 'foodbank-manager' ),
                                                'importDiffWaiting'    => esc_html__( 'Provide mappings to generate a diff.', 'foodbank-manager' ),
                                                /* translators: 1: group label, 2: condition count, 3: action count. */
                                                'importDiffImportEntry' => esc_html__( '%1$s — %2$d conditions, %3$d actions', 'foodbank-manager' ),
                                                /* translators: 1: group label, 2: skip reason. */
                                                'importDiffSkipEntry'   => esc_html__( '%1$s — %2$s', 'foodbank-manager' ),
                                                'importSelectPlaceholder' => esc_html__( 'Select a field…', 'foodbank-manager' ),
                                                'importNoFields'       => esc_html__( 'Add form fields before importing rules.', 'foodbank-manager' ),
                                                /* translators: %s: Number of groups skipped during import. */
                                                'importSkippedNotice'   => esc_html__( '%s groups could not be imported because their fields were not mapped.', 'foodbank-manager' ),
                                                'importConfirm'        => esc_html__( 'Apply import', 'foodbank-manager' ),
                                                'importAutoMap'        => esc_html__( 'Auto-map fields', 'foodbank-manager' ),
                                                'importAnnouncement'   => esc_html__( 'Rules imported. Remember to save changes.', 'foodbank-manager' ),
                                                'presetAnnouncement'   => esc_html__( 'Preset added to the rule editor.', 'foodbank-manager' ),
                                                'presetMissingFields'  => esc_html__( 'Add form fields before inserting a preset.', 'foodbank-manager' ),
                                                'presetsEmpty'         => esc_html__( 'No presets available yet.', 'foodbank-manager' ),
                                                'moveGroupUp'          => esc_html__( 'Move group up', 'foodbank-manager' ),
                                                'moveGroupDown'        => esc_html__( 'Move group down', 'foodbank-manager' ),
                                                'moveConditionUp'      => esc_html__( 'Move condition up', 'foodbank-manager' ),
                                                'moveConditionDown'    => esc_html__( 'Move condition down', 'foodbank-manager' ),
                                                'moveActionUp'         => esc_html__( 'Move action up', 'foodbank-manager' ),
                                                'moveActionDown'       => esc_html__( 'Move action down', 'foodbank-manager' ),
                                                'exportAnnouncement'   => esc_html__( 'Export ready. Download should begin shortly.', 'foodbank-manager' ),
                                        ),
                                )
                        );
                        wp_enqueue_script( $conditions_handle );
                        wp_enqueue_script( $handle );
	}

		/**
		 * Sanitize the template payload.
		 *
		 * @param mixed $value Raw template value.
		 */
	public static function sanitize_template( $value ): string {
			$template = is_string( $value ) ? $value : '';
			$template = TemplateRenderer::sanitize_template( $template );

			$parser   = new TagParser();
			$parsed   = $parser->parse( $template );
			$fields   = $parsed['fields'];
			$warnings = $parsed['warnings'];

		foreach ( $warnings as $warning ) {
			if ( '' === $warning ) {
				continue;
			}
				add_settings_error( self::OPTION_GROUP, 'fbm_registration_template_warning', sanitize_text_field( $warning ), 'notice-warning' );
		}

			self::validate_canonical_fields( $fields );

			return $template;
	}

		/**
		 * Sanitize registration settings payload.
		 *
		 * @param mixed $value Raw settings array.
		 *
		 * @return array<string,mixed>
		 */
        public static function sanitize_settings( $value ): array {
                        $defaults = TemplateDefaults::settings();

                if ( ! is_array( $value ) ) {
                                $value = array();
                }

                        $uploads    = isset( $value['uploads'] ) && is_array( $value['uploads'] ) ? $value['uploads'] : array();
                        $max_size   = isset( $uploads['max_size_mb'] ) ? (int) $uploads['max_size_mb'] : (int) ( $defaults['uploads']['max_size'] / 1048576 );
                        $max_size   = $max_size > 0 ? $max_size : (int) ( $defaults['uploads']['max_size'] / 1048576 );
                        $mime_raw   = isset( $uploads['allowed_mime_types'] ) ? sanitize_textarea_field( (string) $uploads['allowed_mime_types'] ) : '';
                        $mime_parts = array();
                if ( '' !== $mime_raw ) {
                                $candidates = array_map( 'trim', explode( ',', $mime_raw ) );
                        foreach ( $candidates as $candidate ) {
                                if ( '' !== $candidate ) {
                                        $mime_parts[] = strtolower( $candidate );
                                }
                        }
                }

                        $uploads_settings = array(
                                'max_size'           => max( 1, $max_size ) * 1048576,
                                'allowed_mime_types' => ! empty( $mime_parts ) ? $mime_parts : $defaults['uploads']['allowed_mime_types'],
                        );

                        $conditions         = isset( $value['conditions'] ) && is_array( $value['conditions'] ) ? $value['conditions'] : array();
                        $groups_source      = array();
                        if ( isset( $conditions['groups'] ) ) {
                                $groups_source = $conditions['groups'];
                        } elseif ( isset( $conditions['rules'] ) ) {
                                $groups_source = $conditions['rules'];
                        }

                        $condition_groups  = Conditions::sanitize_groups( $groups_source );
                        $conditions_enabled = isset( $conditions['enabled'] ) ? self::to_bool( $conditions['enabled'] ) : (bool) $defaults['conditions']['enabled'];

                        if ( empty( $condition_groups ) ) {
                                $conditions_enabled = false;
                        }

                        $editor_theme = isset( $value['editor']['theme'] ) && is_string( $value['editor']['theme'] ) ? sanitize_key( $value['editor']['theme'] ) : 'light';
                        if ( ! in_array( $editor_theme, array( 'light', 'dark' ), true ) ) {
                                        $editor_theme = 'light';
                        }

                        $messages = isset( $value['messages'] ) && is_array( $value['messages'] ) ? $value['messages'] : array();
                        $messages = array_merge(
                                $defaults['messages'],
                                array(
                                        'success_auto'    => isset( $messages['success_auto'] ) ? wp_kses_post( (string) $messages['success_auto'] ) : $defaults['messages']['success_auto'],
                                        'success_pending' => isset( $messages['success_pending'] ) ? wp_kses_post( (string) $messages['success_pending'] ) : $defaults['messages']['success_pending'],
                                )
                        );

                        return array(
                                'uploads'    => Uploads::normalize_settings( $uploads_settings ),
                                'conditions' => array(
                                        'enabled' => $conditions_enabled,
                                        'groups'  => $condition_groups,
                                ),
                                'editor'     => array(
                                        'theme' => $editor_theme,
                                ),
                                'honeypot'   => isset( $value['honeypot'] ) ? self::to_bool( $value['honeypot'] ) : (bool) $defaults['honeypot'],
                                'messages'   => $messages,
                        );
        }

        

		/**
		 * Handle reset to default action.
		 */
	public static function handle_reset(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
				wp_die( esc_html__( 'You do not have permission to modify the registration form.', 'foodbank-manager' ) );
		}

			check_admin_referer( self::RESET_ACTION );

			update_option( self::TEMPLATE_OPTION, TemplateDefaults::template() );
			update_option( self::SETTINGS_OPTION, TemplateDefaults::settings() );

			$redirect = add_query_arg(
				array(
					'page'              => self::MENU_SLUG,
					self::NOTICE_PARAM  => 'success',
					self::MESSAGE_PARAM => sanitize_text_field( __( 'Registration form reset to defaults.', 'foodbank-manager' ) ),
				),
				admin_url( 'admin.php' )
			);

			wp_safe_redirect( $redirect );
			exit;
	}

		/**
		 * Handle export action.
		 */
        public static function handle_export(): void {
                if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
                                wp_die( esc_html__( 'You do not have permission to export the registration form.', 'foodbank-manager' ) );
                }

                        check_admin_referer( self::EXPORT_ACTION );

                        $template = get_option( self::TEMPLATE_OPTION, TemplateDefaults::template() );
                if ( ! is_string( $template ) ) {
                                $template = TemplateDefaults::template();
                }

                        $settings = get_option( self::SETTINGS_OPTION, TemplateDefaults::settings() );
                if ( ! is_array( $settings ) ) {
                                $settings = TemplateDefaults::settings();
                }

                        $fields     = self::field_catalog( $template );
                        $conditions = isset( $settings['conditions'] ) && is_array( $settings['conditions'] ) ? $settings['conditions'] : array();
                        $payload    = Conditions::export_payload( $fields, $conditions );

                        $json = wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
                        if ( ! is_string( $json ) ) {
                                        wp_die( esc_html__( 'Unable to export registration form.', 'foodbank-manager' ) );
                        }

                        nocache_headers();
                        header( 'Content-Type: application/json; charset=utf-8' );
                        header( 'Content-Disposition: attachment; filename="fbm-registration-conditions.json"' );
                        echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON output.
                        exit;
        }

		/**
		 * Handle import action.
		 */
        public static function handle_import(): void {
                if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
                                wp_die( esc_html__( 'You do not have permission to import the registration form.', 'foodbank-manager' ) );
                }

                        check_admin_referer( self::IMPORT_ACTION );

                        $payload = array();
                if ( isset( $_POST['fbm_registration_import'] ) ) {
                                $raw     = wp_unslash( $_POST['fbm_registration_import'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below.
                                $payload = json_decode( sanitize_textarea_field( (string) $raw ), true );
                }

                if ( ! is_array( $payload ) || empty( $payload['original'] ) || empty( $payload['mapping'] ) ) {
                                $redirect = add_query_arg(
                                        array(
                                                'page'              => self::MENU_SLUG,
                                                self::NOTICE_PARAM  => 'error',
                                                self::MESSAGE_PARAM => sanitize_text_field( __( 'Import failed. Ensure the JSON file is valid.', 'foodbank-manager' ) ),
                                        ),
                                        admin_url( 'admin.php' )
                                );
                                wp_safe_redirect( $redirect );
                                exit;
                }

                        $template = get_option( self::TEMPLATE_OPTION, TemplateDefaults::template() );
                if ( ! is_string( $template ) ) {
                                $template = TemplateDefaults::template();
                }

                        $settings = get_option( self::SETTINGS_OPTION, TemplateDefaults::settings() );
                if ( ! is_array( $settings ) ) {
                                $settings = TemplateDefaults::settings();
                }

                        $fields = self::field_catalog( $template );

        $original = is_array( $payload['original'] ) ? $payload['original'] : array();
        $mapping     = array();
        $raw_mapping = is_array( $payload['mapping'] ) ? $payload['mapping'] : array();

        foreach ( $raw_mapping as $incoming => $target ) {
                if ( is_array( $target ) || is_object( $target ) ) {
                        continue;
                }

                $source_key = sanitize_key( (string) $incoming );
                $target_key = sanitize_key( (string) $target );

                if ( '' === $source_key || '' === $target_key ) {
                        continue;
                }

                $mapping[ $source_key ] = $target_key;
        }

                        $schema_version = isset( $original['schema']['version'] ) ? (int) $original['schema']['version'] : 0;

                if ( Conditions::SCHEMA_VERSION !== $schema_version ) {
                                $redirect = add_query_arg(
                                        array(
                                                'page'              => self::MENU_SLUG,
                                                self::NOTICE_PARAM  => 'error',
                                                self::MESSAGE_PARAM => sanitize_text_field( __( 'Import failed. The export file is from an incompatible version.', 'foodbank-manager' ) ),
                                        ),
                                        admin_url( 'admin.php' )
                                );
                                wp_safe_redirect( $redirect );
                                exit;
                }

        $result = Conditions::apply_import( $original, $mapping, $fields );

                        $settings['conditions'] = array(
                                'enabled' => $result['enabled'],
                                'groups'  => $result['groups'],
                        );

                        update_option( self::SETTINGS_OPTION, self::sanitize_settings( $settings ) );

                        $imported = count( $result['groups'] );
                        $skipped  = count( $result['skipped'] );
                        $message  = sprintf(
                                /* translators: 1: Imported group count, 2: Skipped group count. */
                                __( 'Imported %1$d rule groups. %2$d skipped.', 'foodbank-manager' ),
                                $imported,
                                $skipped
                        );

                        $redirect = add_query_arg(
                                array(
                                        'page'              => self::MENU_SLUG,
                                        self::NOTICE_PARAM  => 'success',
                                        self::MESSAGE_PARAM => sanitize_text_field( $message ),
                                ),
                                admin_url( 'admin.php' )
                        );

			wp_safe_redirect( $redirect );
			exit;
	}

		/**
		 * Convert checkbox-style values to booleans.
		 *
		 * @param mixed $value Raw value.
		 */
	private static function to_bool( $value ): bool {
		if ( is_bool( $value ) ) {
				return $value;
		}

		if ( is_numeric( $value ) ) {
				return (bool) (int) $value;
		}

		if ( is_string( $value ) ) {
				$value = strtolower( trim( $value ) );

				return in_array( $value, array( '1', 'true', 'yes', 'on' ), true );
		}

			return false;
	}

		/**
		 * Validate presence of canonical fields and add admin warnings.
		 *
		 * @param array<string,array<string,mixed>> $fields Field map from parser.
		 */
	private static function validate_canonical_fields( array $fields ): void {
			$required = array( 'first_name', 'last_initial', 'email', 'household_size' );
			$present  = array();

		foreach ( $fields as $definition ) {
			if ( ! is_array( $definition ) ) {
				continue;
			}

				$name = isset( $definition['name'] ) ? (string) $definition['name'] : '';
				$key  = self::canonical_key( $name );
			if ( null !== $key ) {
					$present[] = $key;
			}
		}

		foreach ( $required as $key ) {
			if ( ! in_array( $key, $present, true ) ) {
					$label = ucfirst( str_replace( '_', ' ', $key ) );
					add_settings_error(
						self::OPTION_GROUP,
						'fbm_registration_template_missing_' . $key,
						sprintf(
									/* translators: %s: Missing canonical field label. */
							__( 'Template is missing the required %s field.', 'foodbank-manager' ),
							$label
						),
						'notice-warning'
					);
			}
		}
	}

		/**
		 * Map field names to canonical keys.
		 *
		 * @param string $name Field name.
		 */
        private static function canonical_key( string $name ): ?string {
                        $normalized = strtolower( preg_replace( '/[^a-z0-9]+/', '_', $name ) ?? '' );

                switch ( $normalized ) {
			case 'fbm_first_name':
			case 'first_name':
				return 'first_name';
			case 'fbm_last_initial':
			case 'last_initial':
				return 'last_initial';
			case 'fbm_email':
			case 'email_address':
			case 'email':
				return 'email';
			case 'fbm_household_size':
			case 'household_size':
			case 'household':
				return 'household_size';
			default:
				return null;
                }
        }

                /**
                 * Build a simplified field catalogue for UI components.
                 *
                 * @param string $template Template markup.
                 *
                 * @return array<int,array<string,string>>
                 */
        public static function field_catalog( string $template ): array {
			$parser = new TagParser();
			$parsed = $parser->parse( $template );
			$fields = $parsed['fields'];

			$catalog = array();

			foreach ( $fields as $name => $definition ) {
				if ( ! is_array( $definition ) ) {
					continue;
				}

				$type = isset( $definition['type'] ) ? (string) $definition['type'] : '';
				if ( 'submit' === $type ) {
					continue;
				}

				$label = isset( $definition['label'] ) ? (string) $definition['label'] : (string) $name;

				$catalog[] = array(
					'name'  => (string) $name,
					'label' => $label,
					'type'  => $type,
				);
			}

			return $catalog;
		}

                /**
                 * Toolbar snippets for quick insertion.
                 *
                 * @return array<int,array<string,string>>
                 */
        private static function toolbar_snippets(): array {
			return array(
				array(
					'label'   => __( 'Text field', 'foodbank-manager' ),
					'snippet' => self::build_snippet(
						'text',
						'fbm_first_name',
						array( 'placeholder', '"Enter your first name"', 'autocomplete', '"given-name"' ),
						true
					),
				),
				array(
					'label'   => __( 'Email field', 'foodbank-manager' ),
					'snippet' => self::build_snippet(
						'email',
						'fbm_email',
						array( 'placeholder', '"name@example.com"', 'autocomplete', '"email"' ),
						true
					),
				),
				array(
					'label'   => __( 'Telephone field', 'foodbank-manager' ),
					'snippet' => self::build_snippet(
						'tel',
						'fbm_phone',
						array( 'placeholder', '"+44 7123 456789"', 'autocomplete', '"tel"' )
					),
				),
				array(
					'label'   => __( 'Date field', 'foodbank-manager' ),
					'snippet' => self::build_snippet(
						'date',
						'fbm_preferred_date',
						array( 'min:2024-01-01', 'max:2030-12-31' )
					),
				),
				array(
					'label'   => __( 'Number field', 'foodbank-manager' ),
					'snippet' => self::build_snippet(
						'number',
						'fbm_household_size',
						array( 'min:1', 'max:12', 'step:1' ),
						true
					),
				),
				array(
					'label'   => __( 'Textarea', 'foodbank-manager' ),
					'snippet' => self::build_snippet(
						'textarea',
						'fbm_additional_notes',
						array( 'placeholder', '"Share any additional information"' )
					),
				),
				array(
					'label'   => __( 'Radio group', 'foodbank-manager' ),
					'snippet' => self::build_snippet(
						'radio',
						'fbm_contact_method',
						array( '"Email|email"', '"Phone|phone"', '"SMS|sms"' ),
						true
					),
				),
				array(
					'label'   => __( 'Checkbox group', 'foodbank-manager' ),
					'snippet' => self::build_snippet(
						'checkbox',
						'fbm_support_needs',
						array(
							'use_label_element',
							'"Delivery|delivery"',
							'"Dietary requirements|dietary"',
							'"Accessibility support|access"',
						)
					),
				),
				array(
					'label'   => __( 'Consent checkbox', 'foodbank-manager' ),
					'snippet' => self::build_snippet(
						'checkbox',
						'fbm_registration_consent',
						array( '"Yes, I consent to service updates.|consent"' )
					),
				),
				array(
					'label'   => __( 'Select field', 'foodbank-manager' ),
					'snippet' => self::build_snippet(
						'select',
						'fbm_collection_day',
						array( '"Thursday|thu"', '"Friday|fri"', '"Saturday|sat"' ),
						true
					),
				),
				array(
					'label'   => __( 'File upload', 'foodbank-manager' ),
					'snippet' => self::build_snippet( 'file', 'fbm_proof_of_address' ),
				),
				array(
					'label'   => __( 'Submit button', 'foodbank-manager' ),
					'snippet' => self::build_snippet(
						'submit',
						'fbm_submit_button',
						array( '"Submit registration"' )
					),
				),
			);
	}

		/**
		 * Compose a CF7-style snippet with required markers and tokens.
		 *
		 * @param string            $type     Field type token.
		 * @param string            $name     Field name token.
		 * @param array<int,string> $tokens   Additional tag tokens.
		 * @param bool              $required Flag indicating whether the field is required.
		 */
	private static function build_snippet( string $type, string $name, array $tokens = array(), bool $required = false ): string {
			$parts   = array();
			$parts[] = $required ? $type . '*' : $type;

		if ( '' !== $name ) {
				$parts[] = $name;
		}

		foreach ( $tokens as $token ) {
				$token = trim( $token );

			if ( '' === $token ) {
					continue;
			}

				$parts[] = $token;
		}

			return '[' . implode( ' ', $parts ) . ']';
	}

		/**
		 * Compute the screen ID for the editor page.
		 */
	private static function screen_id(): string {
			return Menu::SLUG . '_page_' . self::MENU_SLUG;
	}
}
