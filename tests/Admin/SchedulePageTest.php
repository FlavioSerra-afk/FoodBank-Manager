<?php

declare(strict_types=1);

namespace FBM\Tests\Admin;

use FoodBankManager\Admin\SchedulePage;
use FoodBankManager\Core\Schedule;
use PHPUnit\Framework\TestCase;
use WP_Error;

final class SchedulePageTest extends TestCase {

        protected function setUp(): void {
                parent::setUp();

                $GLOBALS['fbm_options']       = array();
                $GLOBALS['fbm_last_redirect'] = null;
                $GLOBALS['fbm_test_nonces']   = array();
                $GLOBALS['fbm_current_caps']['fbm_manage'] = false;

                $schedule = new Schedule();
                update_option( 'fbm_schedule_window', $schedule->current_window() );

                $_POST    = array();
                $_GET     = array();
                $_REQUEST = array();
        }

        protected function tearDown(): void {
                $_POST    = array();
                $_GET     = array();
                $_REQUEST = array();

                $GLOBALS['fbm_last_redirect'] = null;
                $GLOBALS['fbm_test_nonces']   = array();
                $GLOBALS['fbm_current_caps']['fbm_manage'] = false;

                parent::tearDown();
        }

        public function testSanitizeRejectsInvalidDay(): void {
                $result = SchedulePage::sanitize(
                        array(
                                'day'      => 'noday',
                                'start'    => '09:00',
                                'end'      => '11:00',
                                'timezone' => 'Europe/London',
                        )
                );

                $this->assertInstanceOf( WP_Error::class, $result );
                $this->assertSame( 'fbm_schedule_invalid_day', $result->get_error_code() );
        }

        public function testHandleSavePersistsValidPayload(): void {
                $GLOBALS['fbm_current_caps']['fbm_manage'] = true;

                $GLOBALS['fbm_test_nonces'] = array(
                        'fbm_schedule_save' => 'valid-schedule',
                );

                $payload = array(
                        'day'      => 'wednesday',
                        'start'    => '09:15',
                        'end'      => '12:30',
                        'timezone' => 'America/New_York',
                );

                $_POST = array(
                        'fbm_schedule'      => $payload,
                        'fbm_schedule_nonce' => 'valid-schedule',
                );

                $_REQUEST = $_POST;

                SchedulePage::handle_save();

                $expected = SchedulePage::sanitize( $payload );
                $this->assertIsArray( $expected );

                $stored = get_option( 'fbm_schedule_window' );
                $this->assertSame( $expected, $stored );

                $redirect = $GLOBALS['fbm_last_redirect'] ?? null;
                $this->assertIsArray( $redirect );

                $parts = parse_url( $redirect['location'] ?? '' );
                $this->assertIsArray( $parts );

                $query = array();
                parse_str( $parts['query'] ?? '', $query );

                $this->assertSame( 'success', $query['fbm_schedule_status'] ?? null );
                $this->assertSame( 'Schedule saved.', $query['fbm_schedule_message'] ?? null );
        }

        public function testHandleSaveRejectsInvalidTime(): void {
                $GLOBALS['fbm_current_caps']['fbm_manage'] = true;

                $GLOBALS['fbm_test_nonces'] = array(
                        'fbm_schedule_save' => 'invalid-time',
                );

                $before = get_option( 'fbm_schedule_window' );

                $_POST = array(
                        'fbm_schedule'      => array(
                                'day'      => 'monday',
                                'start'    => 'not-a-time',
                                'end'      => '12:00',
                                'timezone' => 'Europe/London',
                        ),
                        'fbm_schedule_nonce' => 'invalid-time',
                );

                $_REQUEST = $_POST;

                SchedulePage::handle_save();

                $stored = get_option( 'fbm_schedule_window' );
                $this->assertSame( $before, $stored );

                $redirect = $GLOBALS['fbm_last_redirect'] ?? null;
                $this->assertIsArray( $redirect );

                $parts = parse_url( $redirect['location'] ?? '' );
                $query = array();
                parse_str( $parts['query'] ?? '', $query );

                $this->assertSame( 'error', $query['fbm_schedule_status'] ?? null );
                $this->assertSame( 'Start time must be a valid 24-hour time.', $query['fbm_schedule_message'] ?? null );
        }

        public function testRenderOutputsCurrentSchedule(): void {
                $GLOBALS['fbm_current_caps']['fbm_manage'] = true;

                update_option(
                        'fbm_schedule_window',
                        array(
                                'day'      => 'friday',
                                'start'    => '08:15',
                                'end'      => '10:45',
                                'timezone' => 'America/Chicago',
                        )
                );

                ob_start();
                SchedulePage::render();
                $output = ob_get_clean();

                $this->assertIsString( $output );
                $this->assertStringContainsString( 'value="08:15"', $output );
                $this->assertStringContainsString( 'value="10:45"', $output );
                $this->assertStringContainsString( 'America/Chicago', $output );
                $this->assertStringContainsString( 'value="friday"', $output );
        }
}
