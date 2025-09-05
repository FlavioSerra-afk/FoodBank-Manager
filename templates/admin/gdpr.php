<?php // phpcs:ignoreFile
/**
 * GDPR SAR template.
 *
 * @package FBM
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
    wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
}

$email = isset( $_GET['email'] ) ? sanitize_email( wp_unslash( (string) $_GET['email'] ) ) : '';
$app_id = isset( $_GET['app_id'] ) ? absint( $_GET['app_id'] ) : 0;
$results = array();
if ( $email !== '' ) {
    $results = \FoodBankManager\Database\ApplicationsRepo::find_by_email( $email );
} elseif ( $app_id > 0 ) {
    $app     = \FoodBankManager\Database\ApplicationsRepo::get( $app_id );
    $results = $app ? array( $app ) : array();
}
$counts = array(
    'applications' => count( $results ),
    'files'        => 0,
    'attendance'   => 0,
    'emails'       => 0,
);
if ( $app_id > 0 && ! empty( $results ) ) {
    $counts['files']      = count( \FoodBankManager\Database\ApplicationsRepo::get_files_for_application( $app_id ) );
    $counts['attendance'] = count( \FoodBankManager\Attendance\AttendanceRepo::find_by_application_id( $app_id ) );
    $counts['emails']     = count( \FBM\Mail\LogRepo::find_by_application_id( $app_id ) );
}
?>
<div class="wrap fbm-admin">
    <h1><?php esc_html_e( 'GDPR Export', 'foodbank-manager' ); ?></h1>
    <form method="get" action="">
        <p>
            <label><?php esc_html_e( 'Email', 'foodbank-manager' ); ?>
                <input type="email" name="email" value="<?php echo esc_attr( $email ); ?>" />
            </label>
        </p>
        <p>
            <label><?php esc_html_e( 'Application ID', 'foodbank-manager' ); ?>
                <input type="number" name="app_id" value="<?php echo esc_attr( $app_id ); ?>" />
            </label>
        </p>
        <?php wp_nonce_field( 'fbm_gdpr_search', '_fbm_nonce' ); ?>
        <p><button type="submit" class="button"><?php esc_html_e( 'Search', 'foodbank-manager' ); ?></button></p>
    </form>
    <?php if ( $counts['applications'] > 0 ) : ?>
        <h2><?php esc_html_e( 'Preview', 'foodbank-manager' ); ?></h2>
        <ul>
            <li><?php echo esc_html( sprintf( __( 'Applications: %d', 'foodbank-manager' ), $counts['applications'] ) ); ?></li>
            <li><?php echo esc_html( sprintf( __( 'Attachments: %d', 'foodbank-manager' ), $counts['files'] ) ); ?></li>
            <li><?php echo esc_html( sprintf( __( 'Attendance: %d', 'foodbank-manager' ), $counts['attendance'] ) ); ?></li>
            <li><?php echo esc_html( sprintf( __( 'Email logs: %d', 'foodbank-manager' ), $counts['emails'] ) ); ?></li>
        </ul>
        <form method="post" action="">
            <?php wp_nonce_field( 'fbm_gdpr_export', '_fbm_nonce' ); ?>
            <input type="hidden" name="fbm_action" value="export" />
            <input type="hidden" name="app_id" value="<?php echo esc_attr( $app_id ); ?>" />
            <p><button type="submit" class="button button-primary"><?php esc_html_e( 'Generate SAR', 'foodbank-manager' ); ?></button></p>
        </form>
    <?php endif; ?>
</div>
