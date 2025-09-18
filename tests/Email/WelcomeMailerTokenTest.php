<?php
/**
 * Welcome mailer token tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Email;

use FoodBankManager\Email\WelcomeMailer;
use FoodBankManager\Token\Token;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Email\WelcomeMailer
 */
final class WelcomeMailerTokenTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();

                unset( $GLOBALS['fbm_mail_log'] );
        }

        public function test_qr_payload_matches_fallback_code(): void {
                $mailer = new WelcomeMailer();
                $raw_token = "FBM1:ABCDEF12";

                $result = $mailer->send( 'jordan@example.com', 'Jordan', 'FBM-9876', $raw_token );

                $this->assertTrue( $result );

                $log = $GLOBALS['fbm_mail_log'] ?? array();
                $this->assertCount( 1, $log );

                $message = $log[0]['message'];

                $dom = new \DOMDocument();
                $libxml_previous = libxml_use_internal_errors( true );
                $dom->loadHTML( $message );
                libxml_clear_errors();
                libxml_use_internal_errors( (bool) $libxml_previous );

                $codes = $dom->getElementsByTagName( 'code' );
                $this->assertGreaterThan( 0, $codes->length );
                $fallback_code = $codes->item( 0 )->textContent;

                $images = $dom->getElementsByTagName( 'img' );
                $this->assertGreaterThan( 0, $images->length );
                $qr_payload = $images->item( 0 )->getAttribute( 'data-fbm-token' );

                $canonical = Token::canonicalize( $raw_token );
                $this->assertNotNull( $canonical );

                $this->assertSame( $canonical, $qr_payload );
                $this->assertSame( $qr_payload, $fallback_code );
        }
}
