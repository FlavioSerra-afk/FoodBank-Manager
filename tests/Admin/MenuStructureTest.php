<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FBM\Tests\Admin;

use FoodBankManager\Admin\DashboardPage;
use FoodBankManager\Admin\DiagnosticsPage;
use FoodBankManager\Admin\Menu;
use FoodBankManager\Admin\MembersPage;
use FoodBankManager\Admin\RegistrationFormPage;
use FoodBankManager\Admin\ReportsPage;
use FoodBankManager\Admin\SchedulePage;
use FoodBankManager\Admin\SettingsPage;
use FoodBankManager\Admin\ThemePage;
use PHPUnit\Framework\TestCase;

final class MenuStructureTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		$GLOBALS['fbm_admin_menu']    = array();
		$GLOBALS['fbm_admin_submenu'] = array();
	}

	protected function tearDown(): void {
		unset( $GLOBALS['fbm_admin_menu'], $GLOBALS['fbm_admin_submenu'] );

		parent::tearDown();
	}

	public function testRegistersTopLevelFoodBankMenu(): void {
		Menu::register_menu();

		$menu = $GLOBALS['fbm_admin_menu'] ?? array();
		$this->assertCount( 1, $menu );

		$entry = $menu[0];
		$this->assertSame( 'Food Bank', $entry['menu_title'] );
		$this->assertSame( 'Food Bank', $entry['page_title'] );
		$this->assertSame( 'fbm_manage', $entry['capability'] );
		$this->assertSame( Menu::SLUG, $entry['menu_slug'] );
	}

	public function testSubmenusAttachToFoodBankMenu(): void {
		Menu::register_menu();
		SettingsPage::register_menu();
		ThemePage::register_menu();
		MembersPage::register_menu();
		ReportsPage::register_menu();
		SchedulePage::register_menu();
		DiagnosticsPage::register_menu();
		DashboardPage::register_menu();
		RegistrationFormPage::register_menu();

		$submenu = $GLOBALS['fbm_admin_submenu'][ Menu::SLUG ] ?? array();
		$this->assertNotEmpty( $submenu );

		$capabilities = array();
		foreach ( $submenu as $entry ) {
			$capabilities[ $entry['menu_slug'] ] = $entry['capability'];
		}

		$expected = array(
			'fbm-settings'                 => 'fbm_manage',
			'fbm-theme'                    => 'fbm_manage',
			'fbm-members'                  => 'fbm_manage',
			'fbm-reports'                  => 'fbm_export',
			'fbm-schedule'                 => 'fbm_manage',
			'fbm-diagnostics'              => 'fbm_manage',
			'fbm-staff-dashboard-settings' => 'fbm_manage',
			'fbm-registration-form'        => 'fbm_manage',
		);

		foreach ( $expected as $slug => $capability ) {
			$this->assertArrayHasKey( $slug, $capabilities );
			$this->assertSame( $capability, $capabilities[ $slug ] );
		}

		$this->assertCount( count( $expected ), $submenu );
	}
}
