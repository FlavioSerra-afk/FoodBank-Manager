<?php
/**
 * Email templates page.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use FoodBankManager\Mail\Templates;
$templates = Templates::getAll();
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Email Templates', 'foodbank-manager' ); ?></h1>
    <?php settings_errors( 'fbm-emails' ); ?>
    <form method="post" action="">
        <?php wp_nonce_field( 'fbm_emails_save', 'fbm_emails_nonce' ); ?>
        <h2><?php esc_html_e( 'Applicant Confirmation', 'foodbank-manager' ); ?></h2>
        <p><label><?php esc_html_e( 'Subject', 'foodbank-manager' ); ?><br />
            <input type="text" name="templates[applicant_confirmation][subject]" value="<?php echo esc_attr( $templates['applicant_confirmation']['subject'] ); ?>" class="regular-text" /></label></p>
        <?php wp_editor( $templates['applicant_confirmation']['body'], 'fbm_applicant_body', array( 'textarea_name' => 'templates[applicant_confirmation][body]' ) ); ?>
        <p><a class="button" href="<?php echo esc_url( add_query_arg( array( 'preview' => 1, 'template' => 'applicant_confirmation' ) ) ); ?>" target="_blank"><?php esc_html_e( 'Preview', 'foodbank-manager' ); ?></a></p>
        <hr />
        <h2><?php esc_html_e( 'Admin Notification', 'foodbank-manager' ); ?></h2>
        <p><label><?php esc_html_e( 'Subject', 'foodbank-manager' ); ?><br />
            <input type="text" name="templates[admin_notification][subject]" value="<?php echo esc_attr( $templates['admin_notification']['subject'] ); ?>" class="regular-text" /></label></p>
        <?php wp_editor( $templates['admin_notification']['body'], 'fbm_admin_body', array( 'textarea_name' => 'templates[admin_notification][body]' ) ); ?>
        <p><a class="button" href="<?php echo esc_url( add_query_arg( array( 'preview' => 1, 'template' => 'admin_notification' ) ) ); ?>" target="_blank"><?php esc_html_e( 'Preview', 'foodbank-manager' ); ?></a></p>
        <?php submit_button( __( 'Save Templates', 'foodbank-manager' ) ); ?>
    </form>
    <hr />
    <h2><?php esc_html_e( 'Send Test', 'foodbank-manager' ); ?></h2>
    <form method="post">
        <?php wp_nonce_field( 'fbm_emails_test', 'fbm_emails_test_nonce' ); ?>
        <input type="hidden" name="fbm_email_action" value="send_test" />
        <p>
            <label><?php esc_html_e( 'Template', 'foodbank-manager' ); ?>
                <select name="test_template">
                    <option value="applicant_confirmation">Applicant Confirmation</option>
                    <option value="admin_notification">Admin Notification</option>
                </select>
            </label>
        </p>
        <p>
            <label><?php esc_html_e( 'Email', 'foodbank-manager' ); ?>
                <input type="email" name="test_email" value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>" class="regular-text" />
            </label>
        </p>
        <?php submit_button( __( 'Send Test', 'foodbank-manager' ) ); ?>
    </form>
    <hr />
    <h2><?php esc_html_e( 'Variables', 'foodbank-manager' ); ?></h2>
    <ul>
        <li><code>{{application_id}}</code></li>
        <li><code>{{first_name}}</code></li>
        <li><code>{{last_name}}</code></li>
        <li><code>{{created_at}}</code></li>
        <li><code>{{summary_table}}</code></li>
        <li><code>{{qr_code_url}}</code></li>
        <li><code>{{reference}}</code></li>
        <li><code>{{application_link}}</code></li>
    </ul>
</div>
