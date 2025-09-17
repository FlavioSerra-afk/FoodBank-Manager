<?php
/**
 * Reports admin template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

use function esc_attr;
use function esc_html;
use function esc_html_e;
use function is_array;
use function number_format_i18n;
use function wp_kses_post;

$summary      = array();
$start        = '';
$end          = '';
$page_slug    = '';
$start_param  = '';
$end_param    = '';
$action_param = '';
$action_value = '';
$nonce_field  = '';

if ( isset( $data['summary'] ) && is_array( $data['summary'] ) ) {
        $summary = $data['summary'];
}

if ( isset( $data['start'] ) ) {
        $start = (string) $data['start'];
}

if ( isset( $data['end'] ) ) {
        $end = (string) $data['end'];
}

if ( isset( $data['page_slug'] ) ) {
        $page_slug = (string) $data['page_slug'];
}

if ( isset( $data['start_param'] ) ) {
        $start_param = (string) $data['start_param'];
}

if ( isset( $data['end_param'] ) ) {
        $end_param = (string) $data['end_param'];
}

if ( isset( $data['action_param'] ) ) {
        $action_param = (string) $data['action_param'];
}

if ( isset( $data['action_value'] ) ) {
        $action_value = (string) $data['action_value'];
}

if ( isset( $data['nonce_field'] ) ) {
        $nonce_field = (string) $data['nonce_field'];
}
?>
<div class="wrap">
        <h1 class="wp-heading-inline">
                <?php esc_html_e( 'Food Bank Reports', 'foodbank-manager' ); ?>
        </h1>

        <form method="get" class="fbm-report-range">
                <input type="hidden" name="page" value="<?php echo esc_attr( $page_slug ); ?>" />
                <table class="form-table">
                        <tbody>
                                <tr>
                                        <th scope="row">
                                                <label for="fbm-report-start"><?php esc_html_e( 'Start date', 'foodbank-manager' ); ?></label>
                                        </th>
                                        <td>
                                                <input type="date" id="fbm-report-start" name="<?php echo esc_attr( $start_param ); ?>" value="<?php echo esc_attr( $start ); ?>" class="regular-text" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">
                                                <label for="fbm-report-end"><?php esc_html_e( 'End date', 'foodbank-manager' ); ?></label>
                                        </th>
                                        <td>
                                                <input type="date" id="fbm-report-end" name="<?php echo esc_attr( $end_param ); ?>" value="<?php echo esc_attr( $end ); ?>" class="regular-text" />
                                        </td>
                                </tr>
                        </tbody>
                </table>
                <p class="submit">
                        <button type="submit" class="button button-primary">
                                <?php esc_html_e( 'Update summary', 'foodbank-manager' ); ?>
                        </button>
                </p>
        </form>

        <h2><?php esc_html_e( 'Summary', 'foodbank-manager' ); ?></h2>
        <table class="widefat striped">
                <tbody>
                        <tr>
                                <th scope="row"><?php esc_html_e( 'Total collections', 'foodbank-manager' ); ?></th>
                                <td><?php echo esc_html( number_format_i18n( $summary['total'] ?? 0 ) ); ?></td>
                        </tr>
                        <tr>
                                <th scope="row"><?php esc_html_e( 'Active members', 'foodbank-manager' ); ?></th>
                                <td><?php echo esc_html( number_format_i18n( $summary['active'] ?? 0 ) ); ?></td>
                        </tr>
                        <tr>
                                <th scope="row"><?php esc_html_e( 'Revoked members', 'foodbank-manager' ); ?></th>
                                <td><?php echo esc_html( number_format_i18n( $summary['revoked'] ?? 0 ) ); ?></td>
                        </tr>
                        <tr>
                                <th scope="row"><?php esc_html_e( 'Other statuses', 'foodbank-manager' ); ?></th>
                                <td><?php echo esc_html( number_format_i18n( $summary['other'] ?? 0 ) ); ?></td>
                        </tr>
                </tbody>
        </table>

        <h2><?php esc_html_e( 'Export', 'foodbank-manager' ); ?></h2>
        <form method="get" class="fbm-report-export">
                <input type="hidden" name="page" value="<?php echo esc_attr( $page_slug ); ?>" />
                <input type="hidden" name="<?php echo esc_attr( $action_param ); ?>" value="<?php echo esc_attr( $action_value ); ?>" />
                <input type="hidden" name="<?php echo esc_attr( $start_param ); ?>" value="<?php echo esc_attr( $start ); ?>" />
                <input type="hidden" name="<?php echo esc_attr( $end_param ); ?>" value="<?php echo esc_attr( $end ); ?>" />
                <?php echo wp_kses_post( $nonce_field ); ?>
                <p class="submit">
                        <button type="submit" class="button button-secondary">
                                <?php esc_html_e( 'Download CSV', 'foodbank-manager' ); ?>
                        </button>
                </p>
        </form>
</div>
