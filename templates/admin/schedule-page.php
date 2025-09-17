<?php
/**
 * Schedule settings admin template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

use FoodBankManager\Admin\SchedulePage;

$window = $data['window'] ?? array(
	'day'      => 'thursday',
	'start'    => '11:00',
	'end'      => '14:30',
	'timezone' => 'Europe/London',
);

$day_choices = $data['day_choices'] ?? SchedulePage::day_choices();

$day      = $window['day'] ?? 'thursday';
$start    = $window['start'] ?? '';
$end      = $window['end'] ?? '';
$timezone = $window['timezone'] ?? '';

$status_value = $data['status'] ?? '';
$message_text = $data['message'] ?? '';

$notice_class = '';

if ( 'success' === $status_value ) {
	$notice_class = 'notice-success';
} elseif ( '' !== $message_text ) {
	$notice_class = 'notice-error';
}

$form_action  = $data['form_action'] ?? 'fbm_schedule_save';
$nonce_action = $data['nonce_action'] ?? 'fbm_schedule_save';
$nonce_name   = $data['nonce_name'] ?? 'fbm_schedule_nonce';

?>
<div class="wrap">
		<h1 class="wp-heading-inline"><?php \esc_html_e( 'Food Bank Schedule', 'foodbank-manager' ); ?></h1>

<?php if ( '' !== $message_text ) : ?>
<div class="notice <?php echo \esc_attr( $notice_class ); ?> is-dismissible">
<p><?php echo \esc_html( $message_text ); ?></p>
</div>
<?php endif; ?>

		<form method="post" action="<?php echo \esc_url( \admin_url( 'admin-post.php' ) ); ?>">
				<?php \wp_nonce_field( (string) $nonce_action, (string) $nonce_name ); ?>
				<input type="hidden" name="action" value="<?php echo \esc_attr( (string) $form_action ); ?>" />

				<table class="form-table" role="presentation">
						<tbody>
						<tr>
								<th scope="row">
										<label for="fbm_schedule_day"><?php \esc_html_e( 'Schedule day', 'foodbank-manager' ); ?></label>
								</th>
								<td>
										<select id="fbm_schedule_day" name="fbm_schedule[day]">
												<?php foreach ( $day_choices as $value => $label ) : ?>
														<option value="<?php echo \esc_attr( (string) $value ); ?>"<?php \selected( $day, $value ); ?>><?php echo \esc_html( (string) $label ); ?></option>
												<?php endforeach; ?>
										</select>
								</td>
						</tr>
						<tr>
								<th scope="row">
										<label for="fbm_schedule_start"><?php \esc_html_e( 'Start time', 'foodbank-manager' ); ?></label>
								</th>
								<td>
										<input type="time" id="fbm_schedule_start" name="fbm_schedule[start]" value="<?php echo \esc_attr( (string) $start ); ?>" step="60" />
										<p class="description"><?php \esc_html_e( 'Use 24-hour time (HH:MM).', 'foodbank-manager' ); ?></p>
								</td>
						</tr>
						<tr>
								<th scope="row">
										<label for="fbm_schedule_end"><?php \esc_html_e( 'End time', 'foodbank-manager' ); ?></label>
								</th>
								<td>
										<input type="time" id="fbm_schedule_end" name="fbm_schedule[end]" value="<?php echo \esc_attr( (string) $end ); ?>" step="60" />
										<p class="description"><?php \esc_html_e( 'Use 24-hour time (HH:MM).', 'foodbank-manager' ); ?></p>
								</td>
						</tr>
						<tr>
								<th scope="row">
										<label for="fbm_schedule_timezone"><?php \esc_html_e( 'Timezone', 'foodbank-manager' ); ?></label>
								</th>
								<td>
										<input type="text" id="fbm_schedule_timezone" name="fbm_schedule[timezone]" value="<?php echo \esc_attr( (string) $timezone ); ?>" class="regular-text" />
										<p class="description"><?php \esc_html_e( 'Enter a valid PHP timezone identifier (for example, Europe/London).', 'foodbank-manager' ); ?></p>
								</td>
						</tr>
						</tbody>
				</table>

				<p class="submit">
						<button type="submit" class="button button-primary"><?php \esc_html_e( 'Save schedule', 'foodbank-manager' ); ?></button>
				</p>
		</form>
</div>
