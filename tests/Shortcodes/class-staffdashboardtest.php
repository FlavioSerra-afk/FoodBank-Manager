<?php
/**
 * Staff dashboard shortcode integration tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Shortcodes;

use FoodBankManager\Core\Assets;
use FoodBankManager\Shortcodes\StaffDashboard;
use PHPUnit\Framework\TestCase;

/**
 * Ensures the staff dashboard shortcode renders and localizes assets.
 *
 * @covers \FoodBankManager\Shortcodes\StaffDashboard
 * @covers \FoodBankManager\Core\Assets::maybe_enqueue_staff_dashboard
 */
final class StaffDashboardTest extends TestCase {
		/**
		 * Reset global fixtures before each test.
		 */
	protected function setUp(): void {
			parent::setUp();

		$GLOBALS['fbm_user_logged_in']      = false;
		$GLOBALS['fbm_current_caps']        = array();
		$GLOBALS['fbm_status_header']       = null;
		$GLOBALS['fbm_registered_styles']   = array();
		$GLOBALS['fbm_enqueued_styles']     = array();
		$GLOBALS['fbm_registered_scripts']  = array();
		$GLOBALS['fbm_localized_scripts']   = array();
		$GLOBALS['fbm_enqueued_scripts']    = array();
		$GLOBALS['fbm_script_translations'] = array();
	}

		/**
		 * Denies access to users without staff capabilities.
		 */
	public function test_render_denies_access_when_user_not_authorised(): void {
			$output = StaffDashboard::render();

		$this->assertStringContainsString(
			'Staff dashboard is available to authorised team members only.',
			$output
		);
		$this->assertSame( 403, $GLOBALS['fbm_status_header'] );
	}

		/**
		 * Loads template markup and enqueues assets for authorised viewers.
		 */
	public function test_render_outputs_template_and_enqueues_assets(): void {
			$GLOBALS['fbm_user_logged_in'] = true;
		$GLOBALS['fbm_current_caps']       = array(
			'fbm_view'    => true,
			'fbm_checkin' => true,
		);

		$output = StaffDashboard::render();
		$this->assertStringContainsString( 'data-fbm-staff-dashboard="1"', $output );

		Assets::maybe_enqueue_staff_dashboard();

		$this->assertArrayHasKey( 'fbm-staff-dashboard', $GLOBALS['fbm_registered_styles'] );
		$this->assertContains( 'fbm-staff-dashboard', $GLOBALS['fbm_enqueued_styles'] );

		$this->assertArrayHasKey( 'fbm-staff-dashboard', $GLOBALS['fbm_registered_scripts'] );
		$this->assertContains( 'fbm-staff-dashboard', $GLOBALS['fbm_enqueued_scripts'] );

		$localized = $GLOBALS['fbm_localized_scripts']['fbm-staff-dashboard'];
		$this->assertArrayHasKey( 'strings', $localized['data'] );
		$this->assertArrayHasKey( 'scanner_active', $localized['data']['strings'] );
		$this->assertArrayHasKey( 'override_success', $localized['data']['strings'] );
	}

		/**
		 * Provides localized status messages for success, duplicates, and overrides.
		 */
	public function test_localized_strings_cover_status_messages(): void {
			$GLOBALS['fbm_user_logged_in'] = true;
		$GLOBALS['fbm_current_caps']       = array(
			'fbm_view'    => true,
			'fbm_checkin' => true,
		);

		StaffDashboard::render();
		Assets::maybe_enqueue_staff_dashboard();

		$strings = $GLOBALS['fbm_localized_scripts']['fbm-staff-dashboard']['data']['strings'];

		$this->assertArrayHasKey( 'success', $strings );
		$this->assertArrayHasKey( 'duplicate_day', $strings );
		$this->assertArrayHasKey( 'override_success', $strings );
		$this->assertSame( 'Collection recorded.', $strings['success'] );
		$this->assertSame( 'Member already collected today.', $strings['duplicate_day'] );
		$this->assertSame( 'Override recorded successfully.', $strings['override_success'] );
	}
}
