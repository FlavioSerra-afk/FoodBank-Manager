<?php
/**
 * Reports admin template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

use function add_query_arg;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_html_e;
use function esc_js;
use function esc_url;
use function is_array;
use function number_format_i18n;
use function selected;
use function wp_kses_post;

$summary                = isset( $data['summary'] ) && is_array( $data['summary'] ) ? $data['summary'] : array();
$rows                   = isset( $data['rows'] ) && is_array( $data['rows'] ) ? $data['rows'] : array();
$page_slug              = isset( $data['page_slug'] ) ? (string) $data['page_slug'] : '';
$start_param            = isset( $data['start_param'] ) ? (string) $data['start_param'] : 'fbm_report_start';
$end_param              = isset( $data['end_param'] ) ? (string) $data['end_param'] : 'fbm_report_end';
$quick_range_param      = isset( $data['quick_range_param'] ) ? (string) $data['quick_range_param'] : 'fbm_quick_range';
$page_param             = isset( $data['page_param'] ) ? (string) $data['page_param'] : 'fbm_report_page';
$per_page_param         = isset( $data['per_page_param'] ) ? (string) $data['per_page_param'] : 'fbm_report_per_page';
$start_value            = isset( $data['start_value'] ) ? (string) $data['start_value'] : '';
$end_value              = isset( $data['end_value'] ) ? (string) $data['end_value'] : '';
$quick_range            = isset( $data['quick_range'] ) ? (string) $data['quick_range'] : 'last7';
$rows_per_page          = isset( $data['per_page'] ) ? (int) $data['per_page'] : 25;
$per_page_options       = isset( $data['per_page_options'] ) && is_array( $data['per_page_options'] ) ? $data['per_page_options'] : array( 25, 50, 100, 250, 500 );
$total_rows             = isset( $data['total_rows'] ) ? (int) $data['total_rows'] : 0;
$pagination             = isset( $data['pagination'] ) && is_array( $data['pagination'] ) ? $data['pagination'] : array();
$cache_message          = isset( $data['cache_message'] ) ? (string) $data['cache_message'] : '';
$refresh_url            = isset( $data['refresh_url'] ) ? (string) $data['refresh_url'] : '';
$notices                = isset( $data['notices'] ) && is_array( $data['notices'] ) ? $data['notices'] : array();
$schedule_notice        = isset( $data['schedule_notice'] ) ? (string) $data['schedule_notice'] : '';
$window_labels          = isset( $data['window_labels'] ) && is_array( $data['window_labels'] ) ? $data['window_labels'] : array();
$export_params          = isset( $data['export_params'] ) && is_array( $data['export_params'] ) ? $data['export_params'] : array();
$nonce_field            = isset( $data['nonce_field'] ) ? (string) $data['nonce_field'] : '';
$filter_action          = isset( $data['filter_action'] ) ? (string) $data['filter_action'] : '';
$per_page_max           = isset( $data['per_page_max'] ) ? (int) $data['per_page_max'] : 500;
$manager_can_invalidate = ! empty( $data['manager_can_invalidate'] );
$invalidate_form        = isset( $data['invalidate_form'] ) && is_array( $data['invalidate_form'] ) ? $data['invalidate_form'] : array();
$invalidate_action      = isset( $data['invalidate_action'] ) ? (string) $data['invalidate_action'] : '';
$quick_ranges           = isset( $data['quick_ranges'] ) && is_array( $data['quick_ranges'] ) ? $data['quick_ranges'] : array();
$custom_range_label     = isset( $data['custom_range_label'] ) ? (string) $data['custom_range_label'] : esc_html__( 'Custom range', 'foodbank-manager' );
$detail                 = isset( $data['detail'] ) && is_array( $data['detail'] ) ? $data['detail'] : array();
$detail_param           = isset( $detail['param'] ) ? (string) $detail['param'] : 'fbm_report_member';
$detail_base_url        = isset( $detail['base_url'] ) ? (string) $detail['base_url'] : '';
$detail_clear_url       = isset( $detail['clear_url'] ) ? (string) $detail['clear_url'] : '';
$detail_selected        = ! empty( $detail['selected'] );
$detail_reference       = isset( $detail['reference'] ) ? (string) $detail['reference'] : '';
$detail_member          = isset( $detail['member'] ) && is_array( $detail['member'] ) ? $detail['member'] : array();
$detail_rows            = isset( $detail['rows'] ) && is_array( $detail['rows'] ) ? $detail['rows'] : array();

$summary_defaults = array(
	'total'   => 0,
	'active'  => 0,
	'revoked' => 0,
	'other'   => 0,
);

$summary = array_merge( $summary_defaults, $summary );
?>
<div class="wrap">
		<h1 class="wp-heading-inline">
				<?php esc_html_e( 'Food Bank Reports', 'foodbank-manager' ); ?>
		</h1>

		<?php if ( '' !== $schedule_notice ) : ?>
				<p class="description">
						<?php echo esc_html( $schedule_notice ); ?>
				</p>
		<?php endif; ?>

		<?php if ( ! empty( $window_labels['timezone'] ) ) : ?>
				<p class="description">
						<?php
						printf(
								/* translators: 1: Day and time window, 2: Timezone. */
							esc_html__( 'Current window: %1$s (%2$s).', 'foodbank-manager' ),
							esc_html( $window_labels['sentence'] ?? '' ),
							esc_html( $window_labels['timezone'] ?? '' )
						);
						?>
				</p>
		<?php endif; ?>

		<?php if ( ! empty( $notices ) ) : ?>
				<?php
				foreach ( $notices as $notice ) :
					if ( ! is_array( $notice ) ) {
							continue;
					}

						$notice_type = isset( $notice['type'] ) ? (string) $notice['type'] : 'info';
						$text        = isset( $notice['text'] ) ? (string) $notice['text'] : '';
						$class       = 'notice';

					switch ( $notice_type ) {
						case 'error':
								$class .= ' notice-error';
							break;
						case 'success':
								$class .= ' notice-success';
							break;
						case 'warning':
								$class .= ' notice-warning';
							break;
						default:
								$class .= ' notice-info';
					}

					if ( '' === $text ) {
							continue;
					}
					?>
						<div class="<?php echo esc_attr( $class ); ?>" role="status">
								<p><?php echo esc_html( $text ); ?></p>
						</div>
				<?php endforeach; ?>
		<?php endif; ?>

		<div class="fbm-cache-status notice notice-info" role="status" aria-live="polite">
				<p>
						<?php echo esc_html( $cache_message ); ?>
						<?php if ( '' !== $refresh_url ) : ?>
								<a href="<?php echo esc_url( $refresh_url ); ?>" class="button button-secondary" style="margin-left:1em;">
										<?php esc_html_e( 'Refresh', 'foodbank-manager' ); ?>
								</a>
						<?php endif; ?>
				</p>
		</div>

		<div class="fbm-filter-controls">
				<form method="get" action="<?php echo esc_url( $filter_action ); ?>" class="fbm-quick-range">
								<input type="hidden" name="page" value="<?php echo esc_attr( $page_slug ); ?>" />
								<input type="hidden" name="<?php echo esc_attr( $per_page_param ); ?>" value="<?php echo esc_attr( (string) $rows_per_page ); ?>" />
								<input type="hidden" name="<?php echo esc_attr( $page_param ); ?>" value="1" />
								<fieldset>
												<legend class="screen-reader-text"><?php esc_html_e( 'Quick ranges', 'foodbank-manager' ); ?></legend>
												<?php
												foreach ( $quick_ranges as $range_option ) :
													if ( ! is_array( $range_option ) ) {
																	continue;
													}

																$value     = isset( $range_option['value'] ) ? (string) $range_option['value'] : '';
																$label     = isset( $range_option['label'] ) ? (string) $range_option['label'] : '';
																$is_active = $value === $quick_range;
																$class     = $is_active ? 'button button-primary' : 'button button-secondary';
													?>
																<button type="submit" class="<?php echo esc_attr( $class ); ?>" name="<?php echo esc_attr( $quick_range_param ); ?>" value="<?php echo esc_attr( $value ); ?>" aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>">
																				<?php echo esc_html( $label ); ?>
																</button>
												<?php endforeach; ?>
								</fieldset>
				</form>

				<form method="get" action="<?php echo esc_url( $filter_action ); ?>" class="fbm-custom-range">
						<input type="hidden" name="page" value="<?php echo esc_attr( $page_slug ); ?>" />
						<input type="hidden" name="<?php echo esc_attr( $quick_range_param ); ?>" value="custom" />
						<input type="hidden" name="<?php echo esc_attr( $page_param ); ?>" value="1" />

						<fieldset>
								<legend class="screen-reader-text"><?php echo esc_html( $custom_range_label ); ?></legend>
								<label for="fbm-report-start">
										<?php esc_html_e( 'Start date', 'foodbank-manager' ); ?>
										<input type="date" id="fbm-report-start" name="<?php echo esc_attr( $start_param ); ?>" value="<?php echo esc_attr( $start_value ); ?>" />
								</label>
								<label for="fbm-report-end">
										<?php esc_html_e( 'End date', 'foodbank-manager' ); ?>
										<input type="date" id="fbm-report-end" name="<?php echo esc_attr( $end_param ); ?>" value="<?php echo esc_attr( $end_value ); ?>" />
								</label>
								<label for="fbm-report-per-page">
										<?php esc_html_e( 'Rows per page', 'foodbank-manager' ); ?>
										<select id="fbm-report-per-page" name="<?php echo esc_attr( $per_page_param ); ?>">
												<?php
												foreach ( $per_page_options as $option ) :
														$option = (int) $option;
													?>
														<option value="<?php echo esc_attr( (string) $option ); ?>" <?php selected( $rows_per_page, $option ); ?>>
																<?php echo esc_html( number_format_i18n( $option ) ); ?>
														</option>
												<?php endforeach; ?>
										</select>
								</label>
						</fieldset>

						<p class="description">
								<?php
																printf(
																				/* translators: %s: Maximum number of rows per page. */
																	esc_html__( 'Maximum %s rows per page.', 'foodbank-manager' ),
																	esc_html( number_format_i18n( (int) $per_page_max ) )
																);
																?>
						</p>

						<p class="submit">
								<button type="submit" class="button button-primary">
										<?php esc_html_e( 'Update results', 'foodbank-manager' ); ?>
								</button>
						</p>
				</form>
		</div>

		<h2><?php esc_html_e( 'Summary', 'foodbank-manager' ); ?></h2>
				<table class="widefat striped">
								<caption class="screen-reader-text"><?php esc_html_e( 'Attendance summary metrics', 'foodbank-manager' ); ?></caption>
								<tbody>
						<tr>
								<th scope="row"><?php esc_html_e( 'Date range', 'foodbank-manager' ); ?></th>
								<td>
										<?php
										printf(
												/* translators: 1: Start date, 2: End date. */
											esc_html__( '%1$s to %2$s', 'foodbank-manager' ),
											esc_html( $start_value ),
											esc_html( $end_value )
										);
										?>
								</td>
						</tr>
						<tr>
								<th scope="row"><?php esc_html_e( 'Total collections', 'foodbank-manager' ); ?></th>
								<td><?php echo esc_html( number_format_i18n( (int) $summary['total'] ) ); ?></td>
						</tr>
						<tr>
								<th scope="row"><?php esc_html_e( 'Active members', 'foodbank-manager' ); ?></th>
								<td><?php echo esc_html( number_format_i18n( (int) $summary['active'] ) ); ?></td>
						</tr>
						<tr>
								<th scope="row"><?php esc_html_e( 'Revoked members', 'foodbank-manager' ); ?></th>
								<td><?php echo esc_html( number_format_i18n( (int) $summary['revoked'] ) ); ?></td>
						</tr>
						<tr>
								<th scope="row"><?php esc_html_e( 'Other statuses', 'foodbank-manager' ); ?></th>
								<td><?php echo esc_html( number_format_i18n( (int) $summary['other'] ) ); ?></td>
						</tr>
				</tbody>
		</table>

		<h2><?php esc_html_e( 'Attendance records', 'foodbank-manager' ); ?></h2>

		<p>
				<?php
				printf(
						/* translators: 1: Number of rows, 2: Current page, 3: Total pages. */
					esc_html__( '%1$s rows across %2$s of %3$s page(s).', 'foodbank-manager' ),
					esc_html( number_format_i18n( $total_rows ) ),
					esc_html( number_format_i18n( (int) ( $pagination['current'] ?? 1 ) ) ),
					esc_html( number_format_i18n( (int) ( $pagination['total'] ?? 1 ) ) )
				);
				?>
		</p>

		<div class="fbm-report-actions">
				<form method="get" action="<?php echo esc_url( $filter_action ); ?>" class="fbm-export-form">
						<input type="hidden" name="page" value="<?php echo esc_attr( $page_slug ); ?>" />
						<?php foreach ( $export_params as $name => $value ) : ?>
								<input type="hidden" name="<?php echo esc_attr( (string) $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" />
						<?php endforeach; ?>
						<?php echo wp_kses_post( $nonce_field ); ?>
						<p>
								<button type="submit" class="button button-secondary">
										<?php esc_html_e( 'Download CSV', 'foodbank-manager' ); ?>
								</button>
						</p>
				</form>

				<?php if ( $manager_can_invalidate && ! empty( $invalidate_form['action'] ) && '' !== $invalidate_action ) : ?>
						<form method="post" action="<?php echo esc_url( (string) $invalidate_form['action'] ); ?>" class="fbm-invalidate-form">
								<input type="hidden" name="action" value="<?php echo esc_attr( $invalidate_action ); ?>" />
								<?php echo wp_kses_post( $invalidate_form['nonce_field'] ?? '' ); ?>
								<p>
										<button type="submit" class="button button-secondary" onclick="return confirm('<?php echo esc_js( esc_html__( 'Clear cached report results?', 'foodbank-manager' ) ); ?>');">
												<?php esc_html_e( 'Invalidate cache', 'foodbank-manager' ); ?>
										</button>
								</p>
						</form>
				<?php endif; ?>
		</div>

				<table class="widefat striped">
								<caption class="screen-reader-text"><?php esc_html_e( 'Attendance records table', 'foodbank-manager' ); ?></caption>
								<thead>
						<tr>
								<th scope="col"><?php esc_html_e( 'Collected date', 'foodbank-manager' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Collected time', 'foodbank-manager' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Member reference', 'foodbank-manager' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Status', 'foodbank-manager' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Method', 'foodbank-manager' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Note', 'foodbank-manager' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Recorded by', 'foodbank-manager' ); ?></th>
						</tr>
				</thead>
				<tbody>
						<?php if ( empty( $rows ) ) : ?>
								<tr>
										<td colspan="7">
												<?php esc_html_e( 'No attendance records found for this range.', 'foodbank-manager' ); ?>
										</td>
								</tr>
						<?php else : ?>
								<?php
								foreach ( $rows as $row ) :
									if ( ! is_array( $row ) ) {
											continue;
									}

										$collected_date = isset( $row['collected_date'] ) ? (string) $row['collected_date'] : '';
										$collected_time = isset( $row['collected_at'] ) ? (string) $row['collected_at'] : '';
                                                                                $reference      = isset( $row['member_reference'] ) ? (string) $row['member_reference'] : '';
                                                                                $first_name     = isset( $row['first_name'] ) ? (string) $row['first_name'] : '';
                                                                                $last_initial   = isset( $row['last_initial'] ) ? (string) $row['last_initial'] : '';
                                                                                $name_parts     = array();
                                                                                if ( '' !== $first_name ) {
                                                                                        $name_parts[] = $first_name;
                                                                                }
                                                                                if ( '' !== $last_initial ) {
                                                                                        $name_parts[] = rtrim( $last_initial, '.' ) . '.';
                                                                                }
                                                                                $member_label   = trim( implode( ' ', $name_parts ) );
                                                                                $link_label     = '' !== $member_label ? $member_label : $reference;
                                                                                $detail_link    = '' !== $detail_base_url && '' !== $reference ? add_query_arg( $detail_param, $reference, $detail_base_url ) : '';
                                                                                $reference_html = '';
                                                                                if ( '' !== $member_label && '' !== $reference ) {
                                                                                        $reference_html = ' <span class="fbm-report-member-reference">(' . esc_html( $reference ) . ')</span>';
                                                                                }
                                                                                $row_status     = isset( $row['status'] ) ? (string) $row['status'] : '';
                                                                                $method         = isset( $row['method'] ) ? (string) $row['method'] : '';
                                                                                $note           = isset( $row['note'] ) ? (string) $row['note'] : '';
                                                                                $user           = isset( $row['recorded_by'] ) ? (string) $row['recorded_by'] : '';
                                                                        ?>
                                                                                <tr>
                                                                                                <td><?php echo esc_html( $collected_date ); ?></td>
                                                                                                <td><?php echo esc_html( $collected_time ); ?></td>
                                                                                                <td>
                                                                                                        <?php if ( '' !== $detail_link ) : ?>
                                                                                                                <a href="<?php echo esc_url( $detail_link ); ?>" class="fbm-report-member-link"><?php echo esc_html( $link_label ); ?></a><?php echo $reference_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized above ?>
                                                                                                        <?php else : ?>
                                                                                                                <?php echo esc_html( $link_label ); ?><?php if ( '' !== $reference_html ) { echo $reference_html; } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized above ?>
                                                                                                        <?php endif; ?>
                                                                                                </td>
                                                                                                <td><?php echo esc_html( $row_status ); ?></td>
                                                                                                <td><?php echo esc_html( $method ); ?></td>
                                                                                                <td><?php echo esc_html( $note ); ?></td>
                                                                                                <td><?php echo esc_html( $user ); ?></td>
                                                                                </tr>
								<?php endforeach; ?>
						<?php endif; ?>
				</tbody>
                </table>

                <?php if ( $detail_selected ) : ?>
                        <?php
                        $detail_first_name = isset( $detail_member['first_name'] ) ? (string) $detail_member['first_name'] : '';
                        $detail_last       = isset( $detail_member['last_initial'] ) ? (string) $detail_member['last_initial'] : '';
                        $detail_status     = isset( $detail_member['status'] ) ? (string) $detail_member['status'] : '';
                        $detail_name_parts = array();
                        if ( '' !== $detail_first_name ) {
                                $detail_name_parts[] = $detail_first_name;
                        }
                        if ( '' !== $detail_last ) {
                                $detail_name_parts[] = rtrim( $detail_last, '.' ) . '.';
                        }
                        $detail_display_name   = trim( implode( ' ', $detail_name_parts ) );
                        $detail_reference_safe = '' !== $detail_reference ? $detail_reference : ( isset( $detail_member['member_reference'] ) ? (string) $detail_member['member_reference'] : '' );
                        $detail_label          = '' !== $detail_display_name ? $detail_display_name : $detail_reference_safe;
                        $history_count         = count( $detail_rows );
                        ?>
                        <section class="fbm-report-detail">
                                <h2><?php esc_html_e( 'Member check-in history', 'foodbank-manager' ); ?></h2>
                                <p>
                                        <?php
                                        printf(
                                                /* translators: 1: Number of rows, 2: Member label, 3: Member reference. */
                                                esc_html__( 'Showing %1$s check-in(s) for %2$s (%3$s).', 'foodbank-manager' ),
                                                esc_html( number_format_i18n( $history_count ) ),
                                                esc_html( $detail_label ),
                                                esc_html( $detail_reference_safe )
                                        );

                                        if ( '' !== $detail_status ) {
                                                echo ' ';
                                                printf(
                                                        /* translators: %s: Member status label. */
                                                        esc_html__( 'Status: %s.', 'foodbank-manager' ),
                                                        esc_html( ucfirst( $detail_status ) )
                                                );
                                        }
                                        ?>
                                        <?php if ( '' !== $detail_clear_url ) : ?>
                                                <a href="<?php echo esc_url( $detail_clear_url ); ?>" class="button button-secondary" style="margin-left:1em;">
                                                        <?php esc_html_e( 'Clear selection', 'foodbank-manager' ); ?>
                                                </a>
                                        <?php endif; ?>
                                </p>

                                <?php if ( empty( $detail_rows ) ) : ?>
                                        <p><?php esc_html_e( 'No attendance records found for this member.', 'foodbank-manager' ); ?></p>
                                <?php else : ?>
                                        <table class="widefat striped">
                                                <caption class="screen-reader-text"><?php esc_html_e( 'Selected member attendance history', 'foodbank-manager' ); ?></caption>
                                                <thead>
                                                        <tr>
                                                                <th scope="col"><?php esc_html_e( 'Collected date', 'foodbank-manager' ); ?></th>
                                                                <th scope="col"><?php esc_html_e( 'Collected time', 'foodbank-manager' ); ?></th>
                                                                <th scope="col"><?php esc_html_e( 'Method', 'foodbank-manager' ); ?></th>
                                                                <th scope="col"><?php esc_html_e( 'Note', 'foodbank-manager' ); ?></th>
                                                                <th scope="col"><?php esc_html_e( 'Recorded by', 'foodbank-manager' ); ?></th>
                                                        </tr>
                                                </thead>
                                                <tbody>
                                                        <?php foreach ( $detail_rows as $history_row ) :
                                                                if ( ! is_array( $history_row ) ) {
                                                                        continue;
                                                                }

                                                                $history_date = isset( $history_row['collected_date'] ) ? (string) $history_row['collected_date'] : '';
                                                                $history_time = isset( $history_row['collected_at'] ) ? (string) $history_row['collected_at'] : '';
                                                                $history_method = isset( $history_row['method'] ) ? (string) $history_row['method'] : '';
                                                                $history_note   = isset( $history_row['note'] ) ? (string) $history_row['note'] : '';
                                                                $history_user   = isset( $history_row['recorded_by'] ) ? (string) $history_row['recorded_by'] : '';
                                                                ?>
                                                                <tr>
                                                                        <td><?php echo esc_html( $history_date ); ?></td>
                                                                        <td><?php echo esc_html( $history_time ); ?></td>
                                                                        <td><?php echo esc_html( $history_method ); ?></td>
                                                                        <td><?php echo esc_html( $history_note ); ?></td>
                                                                        <td><?php echo esc_html( $history_user ); ?></td>
                                                                </tr>
                                                        <?php endforeach; ?>
                                                </tbody>
                                        </table>
                                <?php endif; ?>
                        </section>
                <?php endif; ?>

                <nav class="tablenav" aria-label="<?php esc_attr_e( 'Pagination', 'foodbank-manager' ); ?>">
				<div class="tablenav-pages">
						<span class="displaying-num">
								<?php
								printf(
										/* translators: 1: First row number, 2: Last row number, 3: Total rows. */
									esc_html__( 'Showing %1$s–%2$s of %3$s rows', 'foodbank-manager' ),
									esc_html( number_format_i18n( (int) ( $pagination['from'] ?? 0 ) ) ),
									esc_html( number_format_i18n( (int) ( $pagination['to'] ?? 0 ) ) ),
									esc_html( number_format_i18n( $total_rows ) )
								);
								?>
						</span>
						<span class="pagination-links">
								<?php if ( ! empty( $pagination['has_prev'] ) && ! empty( $pagination['prev_url'] ) ) : ?>
										<a class="prev-page" href="<?php echo esc_url( (string) $pagination['prev_url'] ); ?>">
												<span class="screen-reader-text"><?php esc_html_e( 'Previous page', 'foodbank-manager' ); ?></span>
												<span aria-hidden="true">&lsaquo;</span>
										</a>
								<?php else : ?>
										<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>
								<?php endif; ?>

								<span class="paging-input">
										<?php echo esc_html( number_format_i18n( (int) ( $pagination['current'] ?? 1 ) ) ); ?>
										<?php esc_html_e( 'of', 'foodbank-manager' ); ?>
										<span class="total-pages"><?php echo esc_html( number_format_i18n( (int) ( $pagination['total'] ?? 1 ) ) ); ?></span>
								</span>

								<?php if ( ! empty( $pagination['has_next'] ) && ! empty( $pagination['next_url'] ) ) : ?>
										<a class="next-page" href="<?php echo esc_url( (string) $pagination['next_url'] ); ?>">
												<span class="screen-reader-text"><?php esc_html_e( 'Next page', 'foodbank-manager' ); ?></span>
												<span aria-hidden="true">&rsaquo;</span>
										</a>
								<?php else : ?>
										<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>
								<?php endif; ?>
						</span>
				</div>
		</nav>
</div>
