<?php
/**
 * Registration form template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) && ! defined( 'FBM_TESTING' ) ) {
		exit;
}

/**
 * Registration form context values.
 *
 * @var array<string, mixed> $data
 */
$success       = isset( $data['success'] ) ? (bool) $data['success'] : false;
$form_errors   = isset( $data['errors'] ) && is_array( $data['errors'] ) ? array_map( 'strval', $data['errors'] ) : array();
$field_errors  = isset( $data['field_errors'] ) && is_array( $data['field_errors'] ) ? $data['field_errors'] : array();
$fields_schema = isset( $data['fields_schema'] ) && is_array( $data['fields_schema'] ) ? $data['fields_schema'] : array();
$message       = isset( $data['message'] ) && is_string( $data['message'] ) ? $data['message'] : '';
$form_html     = isset( $data['form_html'] ) && is_string( $data['form_html'] ) ? $data['form_html'] : '';
$nonce_field   = isset( $data['nonce_field'] ) && is_string( $data['nonce_field'] ) ? $data['nonce_field'] : '';
$honeypot_name = isset( $data['honeypot_field'] ) && is_string( $data['honeypot_field'] ) ? $data['honeypot_field'] : 'fbm_registration_hp';
$time_field    = isset( $data['time_field'] ) && is_string( $data['time_field'] ) ? $data['time_field'] : 'fbm_registration_time';
$timestamp     = isset( $data['timestamp'] ) ? (int) $data['timestamp'] : time();
$form_action   = isset( $data['action'] ) && is_string( $data['action'] ) ? $data['action'] : '';
$settings      = isset( $data['settings'] ) && is_array( $data['settings'] ) ? $data['settings'] : array();
$honeypot      = isset( $settings['honeypot'] ) ? (bool) $settings['honeypot'] : true;
$form_variant  = isset( $data['variant'] ) && is_string( $data['variant'] ) ? $data['variant'] : 'pending';
$submit_field  = isset( $data['submit_field'] ) && is_string( $data['submit_field'] ) ? $data['submit_field'] : 'fbm_registration_submitted';

$summary_items = array();

foreach ( $field_errors as $field_name => $field_messages ) {
	if ( empty( $field_messages ) ) {
			continue;
	}

		$definition = isset( $fields_schema[ $field_name ] ) && is_array( $fields_schema[ $field_name ] ) ? $fields_schema[ $field_name ] : array();
		$label      = isset( $definition['label'] ) && is_string( $definition['label'] ) ? $definition['label'] : $field_name;
		$message    = is_array( $field_messages ) && ! empty( $field_messages ) ? (string) $field_messages[0] : '';
		$target_id  = 'fbm-field-' . sanitize_html_class( (string) $field_name );

		$summary_items[] = array(
			'target'  => $target_id,
			'label'   => $label,
			'message' => $message,
		);
}
?>
<div class="fbm-registration-form" data-fbm-component="registration-form" data-fbm-form-variant="<?php echo esc_attr( $form_variant ); ?>">
		<?php if ( $success ) : ?>
				<div class="fbm-registration-form__notice fbm-registration-form__notice--success" role="status">
						<?php echo wp_kses_post( $message ); ?>
				</div>
		<?php else : ?>
				<?php if ( ! empty( $summary_items ) ) : ?>
						<div class="fbm-registration-form__error-summary" role="alert" tabindex="-1" data-fbm-error-summary aria-live="assertive">
								<p class="fbm-registration-form__error-summary-intro"><?php esc_html_e( 'Please review these fields:', 'foodbank-manager' ); ?></p>
								<ul>
										<?php foreach ( $summary_items as $item ) : ?>
												<li>
														<a href="#<?php echo esc_attr( $item['target'] ); ?>" data-fbm-error-link>
																<span class="fbm-registration-form__error-summary-label"><?php echo esc_html( (string) $item['label'] ); ?></span>
																<?php if ( '' !== $item['message'] ) : ?>
																		<span class="fbm-registration-form__error-summary-message"><?php echo esc_html( $item['message'] ); ?></span>
																<?php endif; ?>
														</a>
												</li>
										<?php endforeach; ?>
								</ul>
						</div>
				<?php endif; ?>

				<?php if ( '' !== $message && empty( $form_errors ) ) : ?>
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

				<form method="post" action="<?php echo esc_url( $form_action ); ?>" class="fbm-registration-form__form" enctype="multipart/form-data" novalidate>
						<?php echo wp_kses_post( $nonce_field ); ?>
						<input type="hidden" name="<?php echo esc_attr( $submit_field ); ?>" value="1" />
						<input type="hidden" name="<?php echo esc_attr( $time_field ); ?>" value="<?php echo esc_attr( (string) $timestamp ); ?>" />

						<?php if ( $honeypot ) : ?>
								<div class="fbm-registration-form__honeypot" aria-hidden="true">
										<label for="<?php echo esc_attr( $honeypot_name ); ?>" class="screen-reader-text">
												<?php esc_html_e( 'If you are human, leave this field empty.', 'foodbank-manager' ); ?>
										</label>
										<input type="text" name="<?php echo esc_attr( $honeypot_name ); ?>" id="<?php echo esc_attr( $honeypot_name ); ?>" tabindex="-1" autocomplete="off" />
								</div>
						<?php endif; ?>

						<div class="fbm-registration-form__fields">
								<?php echo $form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML composed by TemplateRenderer with escaping. ?>
						</div>
				</form>
		<?php endif; ?>
</div>
