<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Shortcodes;

use FoodBankManager\Auth\Permissions;

class Entries {

	public static function render( array $atts = array() ): string {
		if ( ! Permissions::user_can( 'fb_read_entries' ) ) {
			return '';
		}
		ob_start();
		echo '<form method="get" class="fbm-entries-filters">';
		echo '<label>' . esc_html__( 'Status', 'foodbank-manager' ) . ' <select name="status"><option value="">' . esc_html__( 'All', 'foodbank-manager' ) . '</option><option value="new">' . esc_html__( 'New', 'foodbank-manager' ) . '</option></select></label>';
		echo '<button>' . esc_html__( 'Filter', 'foodbank-manager' ) . '</button>';
		echo '</form>';
		echo '<div class="fbm-entries-table">' . esc_html__( 'Entries table placeholder.', 'foodbank-manager' ) . '</div>';
		echo '<p><a href="#" class="fbm-export-csv">' . esc_html__( 'Export CSV', 'foodbank-manager' ) . '</a></p>';
		// TODO(PRD §5.5): implement filters and CSV export.
		return (string) ob_get_clean();
	}
}
