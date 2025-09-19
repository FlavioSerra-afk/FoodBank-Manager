<?php
// phpcs:ignoreFile
/**
 * Registration notification mailer tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Email;

use FoodBankManager\Email\RegistrationNotificationMailer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Email\RegistrationNotificationMailer
 */
final class RegistrationNotificationMailerTest extends TestCase {
	protected function setUp(): void {
			parent::setUp();

			$GLOBALS['fbm_options']  = array();
			$GLOBALS['fbm_mail_log'] = array();
	}

	protected function tearDown(): void {
			unset( $GLOBALS['fbm_options'], $GLOBALS['fbm_mail_log'] );

			parent::tearDown();
	}

	public function test_send_dispatches_notification_without_member_email(): void {
			$GLOBALS['fbm_options']['admin_email'] = 'admin@example.com';

			$mailer = new RegistrationNotificationMailer();
			$mailer->send( 'FBM-200', 'Jordan', 'L', 'jordan@example.com', 'active' );

			$this->assertNotEmpty( $GLOBALS['fbm_mail_log'] );

			$email = $GLOBALS['fbm_mail_log'][0];
			$this->assertSame( 'admin@example.com', $email['to'] );
			$this->assertSame( 'New food bank registration submitted', $email['subject'] );
			$this->assertStringContainsString( 'Member reference: FBM-200', $email['message'] );
			$this->assertStringContainsString( 'Status: Auto-approved', $email['message'] );
			$this->assertStringContainsString( 'https://example.org/wp-admin/admin.php?page=fbm-members', $email['message'] );
			$this->assertStringNotContainsString( 'jordan@example.com', $email['message'] );
	}

	public function test_send_skips_when_admin_email_invalid(): void {
			$GLOBALS['fbm_options']['admin_email'] = 'not-an-email';

			$mailer = new RegistrationNotificationMailer();
			$mailer->send( 'FBM-300', 'Sky', 'B', 'sky@example.com', 'pending' );

			$this->assertEmpty( $GLOBALS['fbm_mail_log'] );
	}
}
