<?php
/**
 * Welcome mailer tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Email;

use FoodBankManager\Email\WelcomeMailer;
use PHPUnit\Framework\TestCase;
use function __;

/**
 * @covers \FoodBankManager\Email\WelcomeMailer
 */
final class WelcomeMailerTest extends TestCase {
	protected function setUp(): void {
			parent::setUp();

			unset( $GLOBALS['fbm_mail_log'] );
	}

		/**
		 * Sending a welcome email should render HTML content with the QR attachment.
		 */
	public function test_send_dispatches_html_email_with_qr_image(): void {
			$mailer = new WelcomeMailer();

			$result = $mailer->send( 'erin@example.com', 'Erin', 'FBM-1234', 'TOKEN-VALUE' );

			$this->assertTrue( $result );

			$log = $GLOBALS['fbm_mail_log'] ?? array();
			$this->assertCount( 1, $log );

			$entry = $log[0];

			$this->assertSame( 'erin@example.com', $entry['to'] );
			$this->assertSame( __( 'Your food bank check-in QR code', 'foodbank-manager' ), $entry['subject'] );
			$this->assertStringContainsString( 'FBM-1234', $entry['message'] );
			$this->assertStringContainsString( 'data:image/png;base64', $entry['message'] );
			$this->assertContains( 'Content-Type: text/html; charset=UTF-8', $entry['headers'] );
	}
}
