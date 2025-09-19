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
$success         = isset( $data['success'] ) ? (bool) $data['success'] : false;
$form_errors     = isset( $data['errors'] ) && is_array( $data['errors'] ) ? array_map( 'strval', $data['errors'] ) : array();
$message         = isset( $data['message'] ) && is_string( $data['message'] ) ? $data['message'] : '';
$form_html       = isset( $data['form_html'] ) && is_string( $data['form_html'] ) ? $data['form_html'] : '';
$nonce_field     = isset( $data['nonce_field'] ) && is_string( $data['nonce_field'] ) ? $data['nonce_field'] : '';
$honeypot_name   = isset( $data['honeypot_field'] ) && is_string( $data['honeypot_field'] ) ? $data['honeypot_field'] : 'fbm_registration_hp';
$time_field      = isset( $data['time_field'] ) && is_string( $data['time_field'] ) ? $data['time_field'] : 'fbm_registration_time';
$timestamp       = isset( $data['timestamp'] ) ? (int) $data['timestamp'] : time();
$form_action     = isset( $data['action'] ) && is_string( $data['action'] ) ? $data['action'] : '';
$settings        = isset( $data['settings'] ) && is_array( $data['settings'] ) ? $data['settings'] : array();
$honeypot        = isset( $settings['honeypot'] ) ? (bool) $settings['honeypot'] : true;
$form_variant    = isset( $data['variant'] ) && is_string( $data['variant'] ) ? $data['variant'] : 'pending';
$submit_field    = isset( $data['submit_field'] ) && is_string( $data['submit_field'] ) ? $data['submit_field'] : 'fbm_registration_submitted';
?>
<div class="fbm-registration-form" data-fbm-component="registration-form" data-fbm-form-variant="<?php echo esc_attr( $form_variant ); ?>">
        <?php if ( $success ) : ?>
                <div class="fbm-registration-form__notice fbm-registration-form__notice--success" role="status">
                        <?php echo wp_kses_post( $message ); ?>
                </div>
        <?php else : ?>
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
