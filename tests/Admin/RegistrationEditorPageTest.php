<?php
// phpcs:ignoreFile
/**
 * Registration editor admin page tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Admin;

use FoodBankManager\Admin\RegistrationEditorPage;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @covers \FoodBankManager\Admin\RegistrationEditorPage
 */
final class RegistrationEditorPageTest extends TestCase {
	protected function setUp(): void {
			parent::setUp();

			$GLOBALS['fbm_settings_errors'] = array();
	}

	protected function tearDown(): void {
			$GLOBALS['fbm_settings_errors'] = array();

			parent::tearDown();
	}

	public function test_sanitize_template_strips_disallowed_markup(): void {
			$template = '<div class="wrap" data-info="example">[text* first-name]</div><script>alert(1)</script>';

			$sanitized = RegistrationEditorPage::sanitize_template( $template );

			$this->assertStringContainsString( 'data-info="example"', $sanitized );
			$this->assertStringNotContainsString( '<script', $sanitized );
	}

	public function test_sanitize_settings_normalizes_uploads_and_messages(): void {
			$raw = array(
				'uploads'  => array(
					'max_size_mb'        => '3',
					'allowed_mime_types' => 'application/pdf, image/gif , image/jpeg',
				),
				'honeypot' => '0',
				'editor'   => array(
					'theme' => 'dark',
				),
				'messages' => array(
					'success_auto' => '<strong>Thank you!</strong>',
				),
			);

			$settings = RegistrationEditorPage::sanitize_settings( $raw );

			$this->assertSame( 3 * 1048576, $settings['uploads']['max_size'] );
			$this->assertSame(
				array( 'application/pdf', 'image/gif', 'image/jpeg' ),
				$settings['uploads']['allowed_mime_types']
			);
			$this->assertFalse( $settings['honeypot'] );
			$this->assertSame( 'dark', $settings['editor']['theme'] );
			$this->assertSame(
				'<strong>Thank you!</strong>',
				$settings['messages']['success_auto']
			);
	}

	public function test_sanitize_template_adds_warnings_for_missing_fields(): void {
			$template = '<div>[text optional-field]</div>';

			RegistrationEditorPage::sanitize_template( $template );

			$this->assertNotEmpty( $GLOBALS['fbm_settings_errors'] );
	}

	public function test_toolbar_snippets_cover_supported_tags(): void {
			$method = new ReflectionMethod( RegistrationEditorPage::class, 'toolbar_snippets' );
			$method->setAccessible( true );

			/** @var array<int,array<string,string>> $snippets */
			$snippets = $method->invoke( null );

			$this->assertNotEmpty( $snippets );

			$catalogue = array();

		foreach ( $snippets as $snippet ) {
				$this->assertArrayHasKey( 'label', $snippet );
				$this->assertArrayHasKey( 'snippet', $snippet );

				$catalogue[] = $snippet['snippet'];
		}

			$expected = array(
				'[text* fbm_first_name placeholder "Enter your first name" autocomplete "given-name"]',
				'[email* fbm_email placeholder "name@example.com" autocomplete "email"]',
				'[tel fbm_phone placeholder "+44 7123 456789" autocomplete "tel"]',
				'[date fbm_preferred_date min:2024-01-01 max:2030-12-31]',
				'[number* fbm_household_size min:1 max:12 step:1]',
				'[textarea fbm_additional_notes placeholder "Share any additional information"]',
				'[radio* fbm_contact_method "Email|email" "Phone|phone" "SMS|sms"]',
				'[checkbox fbm_support_needs use_label_element "Delivery|delivery" "Dietary requirements|dietary" "Accessibility support|access"]',
				'[checkbox fbm_registration_consent "Yes, I consent to service updates.|consent"]',
				'[select* fbm_collection_day "Thursday|thu" "Friday|fri" "Saturday|sat"]',
				'[file fbm_proof_of_address]',
				'[submit fbm_submit_button "Submit registration"]',
			);

			foreach ( $expected as $tag ) {
					$this->assertContains( $tag, $catalogue );
			}
	}
}
