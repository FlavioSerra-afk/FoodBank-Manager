<?php
/**
 * Registration form template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

/**
 * Registration form context values.
 *
 * @var array<string, mixed> $data
 */
$success          = isset( $data['success'] ) ? (bool) $data['success'] : false;
$form_errors      = isset( $data['errors'] ) && is_array( $data['errors'] ) ? array_map( 'strval', $data['errors'] ) : array();
$message          = isset( $data['message'] ) && is_string( $data['message'] ) ? $data['message'] : '';
$values           = isset( $data['values'] ) && is_array( $data['values'] ) ? $data['values'] : array();
$fields           = isset( $data['fields'] ) && is_array( $data['fields'] ) ? $data['fields'] : array();
$nonce_field      = isset( $data['nonce_field'] ) && is_string( $data['nonce_field'] ) ? $data['nonce_field'] : '';
$honeypot_name    = isset( $data['honeypot_field'] ) && is_string( $data['honeypot_field'] ) ? $data['honeypot_field'] : 'fbm_registration_hp';
$time_field       = isset( $data['time_field'] ) && is_string( $data['time_field'] ) ? $data['time_field'] : 'fbm_registration_time';
$timestamp        = isset( $data['timestamp'] ) ? (int) $data['timestamp'] : time();
$form_action      = isset( $data['action'] ) && is_string( $data['action'] ) ? $data['action'] : '';
$labels           = isset( $data['labels'] ) && is_array( $data['labels'] ) ? $data['labels'] : array();
$copy             = isset( $data['copy'] ) && is_array( $data['copy'] ) ? $data['copy'] : array();
$settings         = isset( $data['settings'] ) && is_array( $data['settings'] ) ? $data['settings'] : array();
$headline_text    = isset( $labels['headline'] ) ? (string) $labels['headline'] : '';
$submit_label     = isset( $labels['submit'] ) ? (string) $labels['submit'] : __( 'Submit registration', 'foodbank-manager' );
$honeypot_enabled = isset( $settings['honeypot'] ) ? (bool) $settings['honeypot'] : true;
$form_variant     = isset( $data['variant'] ) && is_string( $data['variant'] ) ? $data['variant'] : ( ! empty( $settings['auto_approve'] ) ? 'auto' : 'pending' );

$first_name_key   = isset( $fields['first_name'] ) && is_string( $fields['first_name'] ) ? $fields['first_name'] : 'fbm_first_name';
$last_initial_key = isset( $fields['last_initial'] ) && is_string( $fields['last_initial'] ) ? $fields['last_initial'] : 'fbm_last_initial';
$email_key        = isset( $fields['email'] ) && is_string( $fields['email'] ) ? $fields['email'] : 'fbm_email';
$household_key    = isset( $fields['household_size'] ) && is_string( $fields['household_size'] ) ? $fields['household_size'] : 'fbm_household_size';
$submit_key       = isset( $fields['submit'] ) && is_string( $fields['submit'] ) ? $fields['submit'] : 'fbm_registration_submitted';
$consent_key      = isset( $fields['consent'] ) && is_string( $fields['consent'] ) ? $fields['consent'] : 'fbm_registration_consent';

$first_name_value   = isset( $values['first_name'] ) ? (string) $values['first_name'] : '';
$last_initial_value = isset( $values['last_initial'] ) ? (string) $values['last_initial'] : '';
$email_value        = isset( $values['email'] ) ? (string) $values['email'] : '';
$household_value    = isset( $values['household_size'] ) ? (string) $values['household_size'] : '1';
$consent_value      = isset( $values['consent'] ) ? (string) $values['consent'] : '';
$consent_checked    = '1' === $consent_value;
?>
<div class="fbm-registration-form" data-fbm-component="registration-form" data-fbm-form-variant="<?php echo esc_attr( $form_variant ); ?>">
		<?php if ( '' !== $headline_text ) : ?>
				<h2 class="fbm-registration-form__headline"><?php echo esc_html( $headline_text ); ?></h2>
		<?php endif; ?>
		<?php if ( $success ) : ?>
								<div class="fbm-registration-form__notice fbm-registration-form__notice--success" role="status">
												<?php echo wp_kses_post( $message ); ?>
								</div>
		<?php else : ?>
								<?php if ( '' !== $message ) : ?>
												<div class="fbm-registration-form__notice fbm-registration-form__notice--error" role="alert">
									<?php echo esc_html( $message ); ?>
						</div>
				<?php endif; ?>

				<?php if ( ! empty( $form_errors ) ) : ?>
						<ul class="fbm-registration-form__errors" role="alert">
								<?php foreach ( $form_errors as $error_message ) : ?>
										<li><?php echo esc_html( (string) $error_message ); ?></li>
								<?php endforeach; ?>
						</ul>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( $form_action ); ?>" class="fbm-registration-form__form" novalidate>
						<?php echo wp_kses_post( $nonce_field ); ?>
						<input type="hidden" name="<?php echo esc_attr( $submit_key ); ?>" value="1" />
						<input type="hidden" name="<?php echo esc_attr( $time_field ); ?>" value="<?php echo esc_attr( (string) $timestamp ); ?>" />

						<div class="fbm-registration-form__field">
								<label for="<?php echo esc_attr( $first_name_key ); ?>">
										<?php esc_html_e( 'First name', 'foodbank-manager' ); ?>
								</label>
								<input
										type="text"
										id="<?php echo esc_attr( $first_name_key ); ?>"
										name="<?php echo esc_attr( $first_name_key ); ?>"
										value="<?php echo esc_attr( $first_name_value ); ?>"
										autocomplete="given-name"
										required
								/>
						</div>

						<div class="fbm-registration-form__field">
								<label for="<?php echo esc_attr( $last_initial_key ); ?>">
										<?php esc_html_e( 'Last initial', 'foodbank-manager' ); ?>
								</label>
								<input
										type="text"
										id="<?php echo esc_attr( $last_initial_key ); ?>"
										name="<?php echo esc_attr( $last_initial_key ); ?>"
										value="<?php echo esc_attr( $last_initial_value ); ?>"
										maxlength="1"
										pattern="[A-Za-z]"
										required
								/>
						</div>

						<div class="fbm-registration-form__field">
								<label for="<?php echo esc_attr( $email_key ); ?>">
										<?php esc_html_e( 'Email address', 'foodbank-manager' ); ?>
								</label>
								<input
										type="email"
										id="<?php echo esc_attr( $email_key ); ?>"
										name="<?php echo esc_attr( $email_key ); ?>"
										value="<?php echo esc_attr( $email_value ); ?>"
										autocomplete="email"
										required
								/>
						</div>

												<div class="fbm-registration-form__field">
																<label for="<?php echo esc_attr( $household_key ); ?>">
										<?php esc_html_e( 'Household size', 'foodbank-manager' ); ?>
								</label>
								<input
										type="number"
										id="<?php echo esc_attr( $household_key ); ?>"
										name="<?php echo esc_attr( $household_key ); ?>"
										value="<?php echo esc_attr( $household_value ); ?>"
										min="1"
										max="12"
										inputmode="numeric"
										required
								/>
												</div>

												<div class="fbm-registration-form__field fbm-registration-form__field--consent">
																<p class="fbm-registration-form__consent-copy">
																				<?php esc_html_e( 'You can opt in to receive occasional updates about service hours or urgent notices.', 'foodbank-manager' ); ?>
																</p>
																<label for="<?php echo esc_attr( $consent_key ); ?>" class="fbm-registration-form__consent">
																				<input
																								type="checkbox"
																								id="<?php echo esc_attr( $consent_key ); ?>"
																								name="<?php echo esc_attr( $consent_key ); ?>"
																								value="1"<?php echo $consent_checked ? ' checked' : ''; ?>
																				/>
																				<?php esc_html_e( 'Yes, I consent to Food Bank Manager contacting me with these updates.', 'foodbank-manager' ); ?>
																</label>
												</div>

												<?php if ( $honeypot_enabled ) : ?>
												<div class="fbm-registration-form__honeypot" aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">
																<label for="<?php echo esc_attr( $honeypot_name ); ?>">
																				<?php esc_html_e( 'If you are human, leave this field empty.', 'foodbank-manager' ); ?>
																</label>
																<input
																				type="text"
																				id="<?php echo esc_attr( $honeypot_name ); ?>"
																				name="<?php echo esc_attr( $honeypot_name ); ?>"
																				tabindex="-1"
																				autocomplete="off"
																/>
												</div>
												<?php endif; ?>

												<button type="submit" class="fbm-registration-form__submit">
																<?php echo esc_html( $submit_label ); ?>
												</button>
								</form>
		<?php endif; ?>
</div>
