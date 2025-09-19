<?php
/**
 * Ensure JavaScript handles register translations.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\I18n;

use FoodBankManager\Core\Assets;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Core\Assets
 */
final class JsI18nTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();

                $GLOBALS['fbm_user_logged_in']     = true;
                $GLOBALS['fbm_current_caps']       = array( 'fbm_view' => true );
                $GLOBALS['fbm_registered_scripts'] = array();
                $GLOBALS['fbm_enqueued_scripts']   = array();
                $GLOBALS['fbm_script_translations'] = array();
        }

        protected function tearDown(): void {
                unset( $GLOBALS['fbm_user_logged_in'], $GLOBALS['fbm_registered_scripts'], $GLOBALS['fbm_enqueued_scripts'], $GLOBALS['fbm_script_translations'] );
                parent::tearDown();
        }

        public function test_staff_dashboard_handles_include_i18n_dependencies(): void {
                Assets::mark_staff_dashboard();
                Assets::maybe_enqueue_staff_dashboard();

                $registered = $GLOBALS['fbm_registered_scripts'] ?? array();
                $this->assertArrayHasKey( 'fbm-staff-dashboard', $registered );
                $this->assertContains( 'wp-i18n', $registered['fbm-staff-dashboard']['deps'] );

                $this->assertArrayHasKey( 'fbm-scanner', $registered );
                $this->assertContains( 'wp-i18n', $registered['fbm-scanner']['deps'] );

                $translations = $GLOBALS['fbm_script_translations'] ?? array();
                $this->assertArrayHasKey( 'fbm-staff-dashboard', $translations );
                $this->assertArrayHasKey( 'fbm-scanner', $translations );

                $expected_path = FBM_PATH . 'languages';
                $this->assertSame( 'foodbank-manager', $translations['fbm-staff-dashboard']['domain'] );
                $this->assertSame( $expected_path, $translations['fbm-staff-dashboard']['path'] );
                $this->assertSame( 'foodbank-manager', $translations['fbm-scanner']['domain'] );
                $this->assertSame( $expected_path, $translations['fbm-scanner']['path'] );
        }
}
