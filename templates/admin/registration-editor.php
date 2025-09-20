<?php
/**
 * Registration form editor template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

use function checked;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_html_e;
use function esc_textarea;
use function esc_url;
use function implode;
use function selected;
use function settings_fields;
use function submit_button;
use function wp_nonce_field;
use function wp_json_encode;

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

$template_value   = isset( $data['template'] ) && is_string( $data['template'] ) ? $data['template'] : '';
$settings         = isset( $data['settings'] ) && is_array( $data['settings'] ) ? $data['settings'] : array();
$uploads          = isset( $settings['uploads'] ) && is_array( $settings['uploads'] ) ? $settings['uploads'] : array();
$max_size_bytes   = isset( $uploads['max_size'] ) ? (int) $uploads['max_size'] : 5242880;
$max_size_mb      = max( 1, (int) ceil( $max_size_bytes / 1048576 ) );
$allowed_mime     = isset( $uploads['allowed_mime_types'] ) && is_array( $uploads['allowed_mime_types'] ) ? implode( ', ', $uploads['allowed_mime_types'] ) : 'application/pdf, image/jpeg, image/png';
$honeypot_enabled = ! empty( $settings['honeypot'] );
$messages         = isset( $settings['messages'] ) && is_array( $settings['messages'] ) ? $settings['messages'] : array();
$success_auto     = isset( $messages['success_auto'] ) ? (string) $messages['success_auto'] : esc_html__( 'Thank you for registering. We have emailed your QR code.', 'foodbank-manager' );
$success_pending  = isset( $messages['success_pending'] ) ? (string) $messages['success_pending'] : esc_html__( 'Thank you for registering. Our team will review your application and send your QR code once approved.', 'foodbank-manager' );
$editor_theme     = isset( $settings['editor']['theme'] ) ? (string) $settings['editor']['theme'] : 'light';
$snippets         = isset( $data['snippets'] ) && is_array( $data['snippets'] ) ? $data['snippets'] : array();
$option_group     = isset( $data['option_group'] ) ? (string) $data['option_group'] : '';
$template_option  = isset( $data['template_option'] ) ? (string) $data['template_option'] : '';
$settings_option  = isset( $data['settings_option'] ) ? (string) $data['settings_option'] : '';
$template_field   = isset( $data['template_field'] ) ? (string) $data['template_field'] : 'fbm-registration-template';
$notice_type      = isset( $data['notice'] ) ? (string) $data['notice'] : '';
$notice_message   = isset( $data['message'] ) ? (string) $data['message'] : '';
$reset_action     = isset( $data['reset_action'] ) ? (string) $data['reset_action'] : '';
$export_action    = isset( $data['export_action'] ) ? (string) $data['export_action'] : '';
$import_action    = isset( $data['import_action'] ) ? (string) $data['import_action'] : '';
$fields_list      = isset( $data['fields'] ) && is_array( $data['fields'] ) ? $data['fields'] : array();
$matrix_url       = isset( $data['matrix_url'] ) ? (string) $data['matrix_url'] : '';

$conditions_settings    = isset( $settings['conditions'] ) && is_array( $settings['conditions'] ) ? $settings['conditions'] : array();
$conditions_enabled     = ! empty( $conditions_settings['enabled'] );
$conditions_groups      = isset( $conditions_settings['groups'] ) && is_array( $conditions_settings['groups'] ) ? array_values( $conditions_settings['groups'] ) : array();
$conditions_groups_json = wp_json_encode( $conditions_groups );
if ( ! is_string( $conditions_groups_json ) ) {
		$conditions_groups_json = '[]';
}
$revisions      = isset( $data['revisions'] ) && is_array( $data['revisions'] ) ? $data['revisions'] : array();
$autosave_state = isset( $data['autosave'] ) && is_array( $data['autosave'] ) ? $data['autosave'] : null;
?>
<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Registration Form', 'foodbank-manager' ); ?></h1>

		<?php if ( '' !== $notice_message ) : ?>
				<?php
				$class = 'notice';
				if ( 'success' === $notice_type ) {
						$class .= ' notice-success';
				} elseif ( 'error' === $notice_type ) {
						$class .= ' notice-error';
				} else {
						$class .= ' notice-info';
				}
				?>
				<div class="<?php echo esc_attr( $class ); ?>" role="status">
						<p><?php echo esc_html( $notice_message ); ?></p>
				</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" class="fbm-registration-editor__form">
				<?php settings_fields( $option_group ); ?>

				<div class="fbm-registration-editor__layout">
						<div class="fbm-registration-editor__canvas">
								<div class="fbm-registration-editor__toolbar">
										<?php
										foreach ( $snippets as $snippet ) :
											if ( ! is_array( $snippet ) ) {
													continue;
											}
												$label   = isset( $snippet['label'] ) ? (string) $snippet['label'] : '';
												$content = isset( $snippet['snippet'] ) ? (string) $snippet['snippet'] : '';
											if ( '' === $label || '' === $content ) {
													continue;
											}
											?>
												<button type="button" class="button button-secondary fbm-registration-editor__snippet" data-fbm-snippet="<?php echo esc_attr( $content ); ?>"><?php echo esc_html( $label ); ?></button>
										<?php endforeach; ?>
										<button type="button" class="button button-primary fbm-registration-editor__preview" data-fbm-preview="1"><?php esc_html_e( 'Preview', 'foodbank-manager' ); ?></button>
								</div>
								<div class="fbm-registration-editor__status-bar" data-fbm-status-bar>
										<div class="fbm-registration-editor__status-left">
												<span class="fbm-registration-editor__status-text" data-fbm-autosave-status aria-live="polite"></span>
										</div>
										<div class="fbm-registration-editor__status-right">
												<label class="fbm-registration-editor__revisions" for="fbm-registration-editor-revisions">
														<span class="screen-reader-text"><?php esc_html_e( 'Restore revision', 'foodbank-manager' ); ?></span>
														<select id="fbm-registration-editor-revisions" data-fbm-revision-select>
																<option value=""><?php esc_html_e( 'Restore revision…', 'foodbank-manager' ); ?></option>
																<?php
																foreach ( $revisions as $revision ) :
																	if ( ! is_array( $revision ) ) {
																			continue;
																	}

																		$revision_id = isset( $revision['id'] ) ? (string) $revision['id'] : '';
																	if ( '' === $revision_id ) {
																			continue;
																	}

																		$timestamp = isset( $revision['timestamp'] ) ? (int) $revision['timestamp'] : 0;
																		$user_name = isset( $revision['user_name'] ) ? (string) $revision['user_name'] : '';
																		$label     = gmdate( 'Y-m-d H:i', 0 === $timestamp ? time() : $timestamp );
																	if ( '' !== $user_name ) {
																			$label .= ' – ' . $user_name;
																	}
																	?>
																		<option value="<?php echo esc_attr( $revision_id ); ?>" data-timestamp="<?php echo esc_attr( (string) $timestamp ); ?>" data-user="<?php echo esc_attr( $user_name ); ?>"><?php echo esc_html( $label ); ?></option>
																<?php endforeach; ?>
														</select>
												</label>
												<button type="button" class="button fbm-registration-editor__revision-restore" data-fbm-revision-restore disabled><?php esc_html_e( 'Restore', 'foodbank-manager' ); ?></button>
												<button type="button" class="fbm-registration-editor__shortcuts-toggle" data-fbm-shortcuts-toggle aria-expanded="false" aria-controls="fbm-registration-editor-shortcuts">
														<span aria-hidden="true">?</span>
														<span class="screen-reader-text"><?php esc_html_e( 'Keyboard shortcuts', 'foodbank-manager' ); ?></span>
												</button>
										</div>
								</div>
								<div class="fbm-registration-editor__shortcuts-popover" id="fbm-registration-editor-shortcuts" data-fbm-shortcuts-popover hidden>
										<h3><?php esc_html_e( 'Keyboard shortcuts', 'foodbank-manager' ); ?></h3>
										<ul>
												<li><kbd>Ctrl</kbd>/<kbd>&#8984;</kbd> + <kbd>S</kbd> — <?php esc_html_e( 'Save changes', 'foodbank-manager' ); ?></li>
												<li><kbd>Ctrl</kbd>/<kbd>&#8984;</kbd> + <kbd>Enter</kbd> — <?php esc_html_e( 'Open preview', 'foodbank-manager' ); ?></li>
												<li><kbd>Ctrl</kbd>/<kbd>&#8984;</kbd> + <kbd>I</kbd> — <?php esc_html_e( 'Focus field palette', 'foodbank-manager' ); ?></li>
										</ul>
								</div>
								<textarea id="<?php echo esc_attr( $template_field ); ?>" name="<?php echo esc_attr( $template_option ); ?>" rows="20" class="fbm-registration-editor__textarea"><?php echo esc_textarea( $template_value ); ?></textarea>
						</div>

						<div class="fbm-registration-editor__sidebar">
								<h2><?php esc_html_e( 'Form settings', 'foodbank-manager' ); ?></h2>
								<table class="form-table">
										<tbody>
												<tr>
														<th scope="row"><label for="fbm-upload-max-size"><?php esc_html_e( 'File upload size (MB)', 'foodbank-manager' ); ?></label></th>
														<td>
																<input type="number" id="fbm-upload-max-size" name="<?php echo esc_attr( $settings_option ); ?>[uploads][max_size_mb]" min="1" step="1" value="<?php echo esc_attr( (string) $max_size_mb ); ?>" class="small-text" />
														</td>
												</tr>
												<tr>
														<th scope="row"><label for="fbm-upload-mimes"><?php esc_html_e( 'Allowed file types', 'foodbank-manager' ); ?></label></th>
														<td>
																<input type="text" id="fbm-upload-mimes" name="<?php echo esc_attr( $settings_option ); ?>[uploads][allowed_mime_types]" value="<?php echo esc_attr( $allowed_mime ); ?>" class="regular-text" />
																<p class="description"><?php esc_html_e( 'Comma separated list of MIME types, e.g. application/pdf, image/jpeg.', 'foodbank-manager' ); ?></p>
														</td>
												</tr>
												<tr>
														<th scope="row"><?php esc_html_e( 'Honeypot field', 'foodbank-manager' ); ?></th>
														<td>
																<label>
																		<input type="checkbox" name="<?php echo esc_attr( $settings_option ); ?>[honeypot]" value="1"<?php checked( $honeypot_enabled ); ?> />
																		<?php esc_html_e( 'Enable spam honeypot field', 'foodbank-manager' ); ?>
																</label>
														</td>
												</tr>
												<tr>
														<th scope="row"><label for="fbm-editor-theme"><?php esc_html_e( 'Editor theme', 'foodbank-manager' ); ?></label></th>
														<td>
																<select id="fbm-editor-theme" name="<?php echo esc_attr( $settings_option ); ?>[editor][theme]">
																		<option value="light"<?php selected( $editor_theme, 'light' ); ?>><?php esc_html_e( 'Light', 'foodbank-manager' ); ?></option>
																		<option value="dark"<?php selected( $editor_theme, 'dark' ); ?>><?php esc_html_e( 'Dark', 'foodbank-manager' ); ?></option>
																</select>
														</td>
												</tr>
												<tr>
														<th scope="row"><label for="fbm-success-auto"><?php esc_html_e( 'Success message (auto-approved)', 'foodbank-manager' ); ?></label></th>
														<td>
																<textarea id="fbm-success-auto" name="<?php echo esc_attr( $settings_option ); ?>[messages][success_auto]" rows="4" class="large-text"><?php echo esc_textarea( $success_auto ); ?></textarea>
														</td>
												</tr>
												<tr>
														<th scope="row"><label for="fbm-success-pending"><?php esc_html_e( 'Success message (pending)', 'foodbank-manager' ); ?></label></th>
														<td>
												<textarea id="fbm-success-pending" name="<?php echo esc_attr( $settings_option ); ?>[messages][success_pending]" rows="4" class="large-text"><?php echo esc_textarea( $success_pending ); ?></textarea>
												</td>
										</tr>
								</tbody>
								</table>

								<details class="fbm-registration-editor__conditions" data-fbm-conditions
								<?php
								if ( $conditions_enabled ) :
									?>
									open<?php endif; ?>>
										<summary class="fbm-registration-editor__conditions-summary"><?php esc_html_e( 'Conditional Visibility (Beta)', 'foodbank-manager' ); ?></summary>
										<div class="fbm-registration-editor__conditions-body">
												<p class="description">
														<?php esc_html_e( 'Show or hide fields when another field matches a value.', 'foodbank-manager' ); ?>
														<?php if ( '' !== $matrix_url ) : ?>
																<a href="<?php echo esc_url( $matrix_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Registration template matrix', 'foodbank-manager' ); ?></a>
														<?php endif; ?>
												</p>
												<label class="fbm-registration-editor__conditions-toggle">
														<input type="checkbox" name="<?php echo esc_attr( $settings_option ); ?>[conditions][enabled]" value="1" data-fbm-conditions-enabled<?php checked( $conditions_enabled ); ?> />
														<?php esc_html_e( 'Enable conditional visibility rules', 'foodbank-manager' ); ?>
												</label>
																								<div class="fbm-registration-editor__conditions-toolbar">
																												<div class="fbm-registration-editor__conditions-actions">
																																<button type="button" class="button fbm-registration-editor__conditions-add-group" data-fbm-conditions-add-group><?php esc_html_e( 'Add group', 'foodbank-manager' ); ?></button>
																																<button type="button" class="button fbm-registration-editor__conditions-validate" data-fbm-conditions-validate><?php esc_html_e( 'Validate rules', 'foodbank-manager' ); ?></button>
																																<div class="fbm-registration-editor__conditions-status" data-fbm-conditions-status aria-live="polite"></div>
																												</div>
																												<div class="fbm-registration-editor__conditions-tools">
																																<div class="fbm-registration-editor__conditions-announcer" data-fbm-conditions-announcer aria-live="polite"></div>
																																<button type="button" class="button button-secondary" data-fbm-conditions-import><?php esc_html_e( 'Import', 'foodbank-manager' ); ?></button>
																																<button type="button" class="button button-secondary" data-fbm-conditions-export><?php esc_html_e( 'Export', 'foodbank-manager' ); ?></button>
																																<div class="fbm-registration-editor__presets" data-fbm-presets>
																																				<button type="button" class="button button-secondary" data-fbm-presets-toggle aria-haspopup="true" aria-expanded="false" aria-controls="fbm-registration-presets-menu"><?php esc_html_e( 'Presets', 'foodbank-manager' ); ?></button>
																																				<div class="fbm-registration-editor__presets-menu" id="fbm-registration-presets-menu" data-fbm-presets-menu role="menu" hidden></div>
																																</div>
																												</div>
																								</div>
																								<div class="fbm-registration-editor__conditions-report" data-fbm-conditions-report hidden>
																												<h3><?php esc_html_e( 'Validation results', 'foodbank-manager' ); ?></h3>
																												<ul></ul>
												</div>
												<div class="fbm-registration-editor__conditions-groups" data-fbm-conditions-groups>
														<div class="fbm-registration-editor__conditions-empty" data-fbm-conditions-empty
														<?php
														if ( ! empty( $conditions_groups ) && ! empty( $fields_list ) ) :
															?>
															hidden<?php endif; ?>>
																<?php if ( empty( $fields_list ) ) : ?>
																		<?php esc_html_e( 'Add form fields to build conditional rules.', 'foodbank-manager' ); ?>
																<?php else : ?>
																		<?php esc_html_e( 'No condition groups defined yet.', 'foodbank-manager' ); ?>
																<?php endif; ?>
														</div>
												</div>
												<input type="hidden" name="<?php echo esc_attr( $settings_option ); ?>[conditions][groups]" value="<?php echo esc_attr( $conditions_groups_json ); ?>" data-fbm-conditions-storage />
												<noscript>
														<p class="description"><?php esc_html_e( 'Conditional visibility editing requires JavaScript.', 'foodbank-manager' ); ?></p>
												</noscript>
										</div>
								</details>
						</div>
				</div>

				<?php submit_button( esc_html__( 'Save changes', 'foodbank-manager' ) ); ?>
		</form>

				<div class="fbm-registration-editor__secondary-actions">
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="fbm-registration-editor__secondary-form">
												<?php wp_nonce_field( $reset_action, '_wpnonce', false ); ?>
												<input type="hidden" name="action" value="<?php echo esc_attr( $reset_action ); ?>" />
												<?php submit_button( esc_html__( 'Reset to default', 'foodbank-manager' ), 'secondary', 'submit', false ); ?>
								</form>
				</div>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="fbm-registration-conditions-export" class="fbm-registration-editor__hidden-form" hidden>
								<?php wp_nonce_field( $export_action, '_wpnonce', false ); ?>
								<input type="hidden" name="action" value="<?php echo esc_attr( $export_action ); ?>" />
				</form>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="fbm-registration-conditions-import" class="fbm-registration-editor__hidden-form" hidden>
								<?php wp_nonce_field( $import_action, '_wpnonce', false ); ?>
								<input type="hidden" name="action" value="<?php echo esc_attr( $import_action ); ?>" />
								<input type="hidden" name="fbm_registration_import" value="" data-fbm-import-field />
				</form>

				<div class="fbm-registration-editor__import-modal" data-fbm-import-modal hidden>
					<div class="fbm-registration-editor__import-backdrop" data-fbm-import-close></div>
					<div class="fbm-registration-editor__import-dialog" role="dialog" aria-modal="true" aria-labelledby="fbm-registration-import-title" tabindex="-1" data-fbm-import-dialog>
						<div class="fbm-registration-editor__import-header">
							<h2 id="fbm-registration-import-title"><?php esc_html_e( 'Import rules', 'foodbank-manager' ); ?></h2>
							<button type="button" class="fbm-registration-editor__import-close button-link" data-fbm-import-close aria-label="<?php esc_attr_e( 'Close import dialog', 'foodbank-manager' ); ?>">
								<span aria-hidden="true">&times;</span>
								<span class="screen-reader-text"><?php esc_html_e( 'Close import dialog', 'foodbank-manager' ); ?></span>
							</button>
						</div>
						<div class="fbm-registration-editor__import-body">
							<div class="fbm-registration-editor__import-step" data-fbm-import-step>
								<p class="description"><?php esc_html_e( 'Paste the JSON export generated by the editor to review rule mappings before applying them.', 'foodbank-manager' ); ?></p>
								<textarea rows="8" class="large-text" data-fbm-import-input></textarea>
								<div class="fbm-registration-editor__import-actions">
									<button type="button" class="button button-primary" data-fbm-import-preview><?php esc_html_e( 'Preview import', 'foodbank-manager' ); ?></button>
									<button type="button" class="button" data-fbm-import-cancel><?php esc_html_e( 'Cancel', 'foodbank-manager' ); ?></button>
								</div>
							</div>
							<div class="fbm-registration-editor__import-results" data-fbm-import-results hidden>
								<div class="fbm-registration-editor__import-summary" data-fbm-import-summary aria-live="polite"></div>
								<div class="fbm-registration-editor__import-diff" data-fbm-import-diff hidden>
									<div class="fbm-registration-editor__import-diff-status" data-fbm-import-diff-status aria-live="polite"></div>
									<div class="fbm-registration-editor__import-diff-columns">
										<div class="fbm-registration-editor__import-diff-column">
											<h3><?php esc_html_e( 'Incoming groups', 'foodbank-manager' ); ?></h3>
											<pre data-fbm-import-original aria-live="polite"></pre>
										</div>
										<div class="fbm-registration-editor__import-diff-column">
											<h3><?php esc_html_e( 'Resolved mapping', 'foodbank-manager' ); ?></h3>
											<pre data-fbm-import-resolved aria-live="polite"></pre>
										</div>
									</div>
									<div class="fbm-registration-editor__import-diff-summary">
										<div>
											<h4><?php esc_html_e( 'Will import', 'foodbank-manager' ); ?></h4>
											<ul data-fbm-import-summary-import></ul>
										</div>
										<div>
											<h4><?php esc_html_e( 'Will skip', 'foodbank-manager' ); ?></h4>
											<ul data-fbm-import-summary-skip></ul>
										</div>
									</div>
								</div>
								<div class="fbm-registration-editor__import-mapping" data-fbm-import-mapping></div>
								<div class="fbm-registration-editor__import-analysis" data-fbm-import-analysis></div>
							</div>
						</div>
						<div class="fbm-registration-editor__import-footer">
							<button type="button" class="button button-primary" data-fbm-import-confirm disabled><?php esc_html_e( 'Apply import', 'foodbank-manager' ); ?></button>
							<button type="button" class="button" data-fbm-import-autofill><?php esc_html_e( 'Auto-map fields', 'foodbank-manager' ); ?></button>
							<button type="button" class="button button-secondary" data-fbm-import-cancel><?php esc_html_e( 'Close', 'foodbank-manager' ); ?></button>
						</div>
					</div>
				</div>
				<div class="fbm-registration-editor__preset-modal" data-fbm-preset-modal hidden>
								<div class="fbm-registration-editor__preset-backdrop" data-fbm-preset-close></div>
								<div class="fbm-registration-editor__preset-dialog" role="dialog" aria-modal="true" aria-labelledby="fbm-registration-preset-title" tabindex="-1" data-fbm-preset-dialog>
												<div class="fbm-registration-editor__preset-header">
																<h2 id="fbm-registration-preset-title"><?php esc_html_e( 'Insert preset', 'foodbank-manager' ); ?></h2>
																<button type="button" class="fbm-registration-editor__preset-close button-link" data-fbm-preset-close aria-label="<?php esc_attr_e( 'Close presets dialog', 'foodbank-manager' ); ?>">
																				<span aria-hidden="true">&times;</span>
																				<span class="screen-reader-text"><?php esc_html_e( 'Close presets dialog', 'foodbank-manager' ); ?></span>
																</button>
												</div>
												<div class="fbm-registration-editor__preset-body">
																<p class="description" data-fbm-preset-description></p>
																<form data-fbm-preset-form></form>
												</div>
												<div class="fbm-registration-editor__preset-footer">
																<button type="button" class="button button-primary" data-fbm-preset-apply disabled><?php esc_html_e( 'Add preset', 'foodbank-manager' ); ?></button>
																<button type="button" class="button button-secondary" data-fbm-preset-close><?php esc_html_e( 'Cancel', 'foodbank-manager' ); ?></button>
												</div>
								</div>
				</div>

		<div class="fbm-registration-editor__preview-modal" data-fbm-preview-modal hidden>
				<div class="fbm-registration-editor__preview-backdrop" data-fbm-preview-close></div>
				<div class="fbm-registration-editor__preview-dialog" role="dialog" aria-modal="true" aria-labelledby="fbm-registration-editor-preview-title" tabindex="-1" data-fbm-preview-dialog>
						<div class="fbm-registration-editor__preview-header">
								<h2 id="fbm-registration-editor-preview-title"><?php esc_html_e( 'Template Preview', 'foodbank-manager' ); ?></h2>
								<button type="button" class="fbm-registration-editor__preview-close button-link" data-fbm-preview-close aria-label="<?php esc_attr_e( 'Close preview', 'foodbank-manager' ); ?>">
										<span aria-hidden="true">&times;</span>
										<span class="screen-reader-text"><?php esc_html_e( 'Close preview', 'foodbank-manager' ); ?></span>
								</button>
						</div>
						<div class="fbm-registration-editor__preview-body" data-fbm-preview-body>
								<p class="fbm-registration-editor__preview-note" data-fbm-preview-note aria-live="polite"><?php esc_html_e( 'Preview only. Form controls are disabled.', 'foodbank-manager' ); ?></p>
								<div class="fbm-registration-editor__preview-content" data-fbm-preview-content></div>
						</div>
						<div class="fbm-registration-editor__preview-warnings" data-fbm-preview-warnings hidden>
								<h3><?php esc_html_e( 'Template warnings', 'foodbank-manager' ); ?></h3>
								<ul></ul>
						</div>
						<div class="fbm-registration-editor__preview-footer">
								<button type="button" class="button fbm-registration-editor__preview-debug-toggle" data-fbm-preview-debug-toggle aria-expanded="false" aria-controls="fbm-registration-preview-debug"><?php esc_html_e( 'Show rule debugger', 'foodbank-manager' ); ?></button>
						</div>
												<div class="fbm-registration-editor__preview-debug" id="fbm-registration-preview-debug" data-fbm-preview-debug hidden>
																<h3><?php esc_html_e( 'Rule debugger', 'foodbank-manager' ); ?></h3>
																<div class="fbm-registration-editor__preview-trace-controls">
																				<label>
																								<input type="checkbox" data-fbm-preview-trace-toggle />
																								<span><?php esc_html_e( 'Record timings', 'foodbank-manager' ); ?></span>
																				</label>
																				<button type="button" class="button" data-fbm-preview-trace-export disabled><?php esc_html_e( 'Export trace (JSON)', 'foodbank-manager' ); ?></button>
																</div>
																<div class="fbm-registration-editor__preview-trace" data-fbm-preview-trace hidden>
																				<h4><?php esc_html_e( 'Recent timing averages', 'foodbank-manager' ); ?></h4>
																				<p class="fbm-registration-editor__preview-trace-empty" data-fbm-preview-trace-empty><?php esc_html_e( 'No timing data captured yet.', 'foodbank-manager' ); ?></p>
																				<table class="widefat striped fbm-registration-editor__preview-trace-table" data-fbm-preview-trace-table>
																								<thead>
																												<tr>
																																<th scope="col"><?php esc_html_e( 'Phase', 'foodbank-manager' ); ?></th>
																																<th scope="col"><?php esc_html_e( 'Average (ms)', 'foodbank-manager' ); ?></th>
																																<th scope="col"><?php esc_html_e( 'Fastest (ms)', 'foodbank-manager' ); ?></th>
																																<th scope="col"><?php esc_html_e( 'Slowest (ms)', 'foodbank-manager' ); ?></th>
																																<th scope="col"><?php esc_html_e( 'Runs', 'foodbank-manager' ); ?></th>
																												</tr>
																								</thead>
																								<tbody></tbody>
																				</table>
																</div>
																<p class="fbm-registration-editor__preview-debug-empty" data-fbm-preview-debug-empty><?php esc_html_e( 'No rules evaluated for the current preview state.', 'foodbank-manager' ); ?></p>
																<ul data-fbm-preview-debug-groups></ul>
												</div>
				</div>
		</div>
</div>

