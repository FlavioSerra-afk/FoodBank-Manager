<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Core\Options;

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
        function wp_strip_all_tags( string $text ): string {
                return strip_tags( $text );
        }
}

final class OptionsTest extends TestCase {
	public function setUp(): void {
		global $fbm_test_options;
		$fbm_test_options = array();
	}

	public function testDefaultsAndSetGet(): void {
		$this->assertSame( 7, Options::get( 'attendance.policy_days' ) );
		Options::set( 'attendance.policy_days', 3 );
		$this->assertSame( 3, Options::get( 'attendance.policy_days' ) );
	}

        public function testSaveAllSanitizes(): void {
                Options::saveAll(
                        array(
                                'emails'     => array(
                                        'from_email' => ' test@example.com ',
                                        'from_name'  => ' Test ',
                                ),
                                'attendance' => array( 'types' => 'in_person,delivery' ),
                        )
                );
                $this->assertSame( 'test@example.com', Options::get( 'emails.from_email' ) );
                $types = Options::get( 'attendance.types' );
                $this->assertContains( 'in_person', $types );
                $this->assertContains( 'delivery', $types );
        }

       public function testUpdateValidatesSchema(): void {
               Options::update(
                       array(
                               'branding' => array(
                                       'site_name' => ' <b>My Site</b> ',
                                       'logo_url'  => 'https://example.com/logo.png ',
                                       'color'     => 'purple',
                                       'extra'     => 'ignore',
                               ),
                               'emails'   => array(
                                       'from_name'  => ' Admin ',
                                       'from_email' => 'invalid',
                                       'reply_to'   => 'reply@example.com',
                               ),
                       )
               );
               $this->assertSame( 'My Site', Options::get( 'branding.site_name' ) );
               $this->assertSame( 'https://example.com/logo.png', Options::get( 'branding.logo_url' ) );
               $this->assertSame( 'purple', Options::get( 'branding.color' ) );
               $this->assertSame( 'Admin', Options::get( 'emails.from_name' ) );
               $this->assertSame( '', Options::get( 'emails.from_email' ) );
               $this->assertSame( 'reply@example.com', Options::get( 'emails.reply_to' ) );
               $this->assertNull( Options::get( 'branding.extra' ) );
       }

       public function testOversizeRejected(): void {
               Options::update( array( 'emails' => array( 'from_name' => 'Ok' ) ) );
               $this->assertSame( 'Ok', Options::get( 'emails.from_name' ) );
               Options::update( array( 'emails' => array( 'from_name' => str_repeat( 'a', 2001 ) ) ) );
               $this->assertSame( 'Ok', Options::get( 'emails.from_name' ) );
       }

       public function testTemplateRoundTrip(): void {
               $id = 'applicant_confirmation';
               $ok  = Options::set_template(
                       $id,
                       array(
                               'subject'   => '<b>Hello</b>',
                               'body_html' => '<p>Hi</p>',
                       )
               );
               $this->assertTrue( $ok );

               $data = Options::get_template( $id );
               $this->assertSame( 'Hello', $data['subject'] );
               $this->assertSame( '<p>Hi</p>', $data['body_html'] );
               $this->assertNotSame( '', $data['updated_at'] );

               Options::reset_template( $id );
               $reset = Options::get_template( $id );
               $this->assertSame( '', $reset['subject'] );
               $this->assertSame( '', $reset['body_html'] );
       }

       public function testRejectsUnknownTemplateId(): void {
               $this->assertFalse( Options::set_template( 'unknown', array() ) );
               $this->assertFalse( Options::reset_template( 'unknown' ) );
               $unknown = Options::get_template( 'unknown' );
               $this->assertSame( '', $unknown['subject'] );
               $this->assertSame( '', $unknown['body_html'] );
       }
}
