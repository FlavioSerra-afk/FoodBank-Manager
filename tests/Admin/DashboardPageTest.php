<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FBM\Tests\Admin;

use FoodBankManager\Admin\DashboardPage;
use FoodBankManager\Shortcodes\StaffDashboard;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DashboardPageTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		$GLOBALS['fbm_options']                    = array();
		$GLOBALS['fbm_last_redirect']              = null;
		$GLOBALS['fbm_test_nonces']                = array();
		$GLOBALS['fbm_current_caps']['fbm_manage'] = false;

		$_POST    = array();
		$_GET     = array();
		$_REQUEST = array();
	}

	protected function tearDown(): void {
		$_POST    = array();
		$_GET     = array();
		$_REQUEST = array();

		$GLOBALS['fbm_options']                    = array();
		$GLOBALS['fbm_last_redirect']              = null;
		$GLOBALS['fbm_test_nonces']                = array();
		$GLOBALS['fbm_current_caps']['fbm_manage'] = false;

		parent::tearDown();
	}

	public function testRenderDisplaysShortcode(): void {
		$GLOBALS['fbm_current_caps']['fbm_manage'] = true;
		update_option( 'fbm_staff_dashboard_settings', StaffDashboard::default_settings() );

		ob_start();
		DashboardPage::render();
		$output = ob_get_clean();

		$this->assertIsString( $output );
		$this->assertStringContainsString( '[fbm_staff_dashboard]', $output );
	}

	public function testHandleSaveRequiresNonce(): void {
		$GLOBALS['fbm_current_caps']['fbm_manage'] = true;

		$_POST    = array(
			'fbm_staff_dashboard'       => array(
				'show_counters' => '1',
			),
			'fbm_staff_dashboard_nonce' => 'missing',
		);
		$_REQUEST = $_POST;

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'invalid_nonce' );

		DashboardPage::handle_save();
	}

	public function testHandleSavePersistsSettings(): void {
		$GLOBALS['fbm_current_caps']['fbm_manage'] = true;
		$GLOBALS['fbm_test_nonces']                = array(
			'fbm_staff_dashboard_save' => 'valid-nonce',
		);

		$_POST    = array(
			'fbm_staff_dashboard'       => array(
				'show_counters'  => '0',
				'allow_override' => '0',
				'scanner'        => array(
					'prefer_torch'    => '1',
					'roi'             => '120',
					'decode_debounce' => '75',
				),
			),
			'fbm_staff_dashboard_nonce' => 'valid-nonce',
		);
		$_REQUEST = $_POST;

		DashboardPage::handle_save();

		$stored = get_option( 'fbm_staff_dashboard_settings' );
		$this->assertSame(
			array(
				'show_counters'  => false,
				'allow_override' => false,
				'scanner'        => array(
					'prefer_torch'    => true,
					'roi'             => 100,
					'decode_debounce' => 75,
				),
			),
			$stored
		);

		$resolved = StaffDashboard::settings();
		$this->assertFalse( $resolved['show_counters'] );
		$this->assertFalse( $resolved['allow_override'] );
		$this->assertTrue( $resolved['scanner']['prefer_torch'] );
		$this->assertSame( 100, $resolved['scanner']['roi'] );
		$this->assertSame( 75, $resolved['scanner']['decode_debounce'] );

		$redirect = $GLOBALS['fbm_last_redirect'] ?? null;
		$this->assertIsArray( $redirect );
		$parts = parse_url( $redirect['location'] ?? '' );
		$this->assertIsArray( $parts );
		$query = array();
		parse_str( $parts['query'] ?? '', $query );
		$this->assertSame( 'success', $query['fbm_staff_dashboard_status'] ?? null );
	}
}
