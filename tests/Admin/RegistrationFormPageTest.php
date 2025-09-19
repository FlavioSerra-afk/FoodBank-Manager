<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FBM\Tests\Admin;

use FoodBankManager\Admin\RegistrationFormPage;
use FoodBankManager\Registration\RegistrationSettings;
use FoodBankManager\Shortcodes\RegistrationForm;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RegistrationFormPageTest extends TestCase {
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

		update_option( 'fbm_reg_label_headline', 'My Headline' );
		update_option( 'fbm_reg_label_submit', 'Send' );

		ob_start();
		RegistrationFormPage::render();
		$output = ob_get_clean();

		$this->assertIsString( $output );
		$this->assertStringContainsString( '[fbm_registration_form]', $output );
		$this->assertStringContainsString( 'My Headline', $output );
	}

	public function testHandleSaveRequiresValidNonce(): void {
		$GLOBALS['fbm_current_caps']['fbm_manage'] = true;

		$_POST    = array(
			'fbm_registration_form'       => array(
				'headline'     => 'Example',
				'submit'       => 'Submit',
				'success_auto' => 'Auto',
			),
			'fbm_registration_form_nonce' => 'missing',
		);
		$_REQUEST = $_POST;

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'invalid_nonce' );

		RegistrationFormPage::handle_save();
	}

	public function testHandleSavePersistsOptions(): void {
		$GLOBALS['fbm_current_caps']['fbm_manage'] = true;
		$GLOBALS['fbm_test_nonces']                = array(
			'fbm_registration_form_save' => 'valid-nonce',
		);

		$_POST    = array(
			'fbm_registration_form'       => array(
				'auto_approve'    => '1',
				'honeypot'        => '0',
				'headline'        => '  Custom Headline  ',
				'submit'          => 'Submit Now',
				'success_auto'    => '<strong>Auto Success</strong>',
				'success_pending' => '<em>Pending Success</em>',
			),
			'fbm_registration_form_nonce' => 'valid-nonce',
		);
		$_REQUEST = $_POST;

		RegistrationFormPage::handle_save();

		$headline = get_option( 'fbm_reg_label_headline' );
		$this->assertSame( sanitize_text_field( '  Custom Headline  ' ), $headline );

		$submit_label = get_option( 'fbm_reg_label_submit' );
		$this->assertSame( sanitize_text_field( 'Submit Now' ), $submit_label );

		$auto_copy = get_option( 'fbm_reg_copy_success_auto' );
		$this->assertSame( wp_kses_post( '<strong>Auto Success</strong>' ), $auto_copy );

		$pending_copy = get_option( 'fbm_reg_copy_success_pending' );
		$this->assertSame( wp_kses_post( '<em>Pending Success</em>' ), $pending_copy );

		$honeypot_enabled = get_option( 'fbm_reg_enable_honeypot' );
		$this->assertSame( 0, $honeypot_enabled );

		$settings = get_option( 'fbm_settings' );
		$this->assertIsArray( $settings );
		$registration = RegistrationSettings::normalize_registration_settings( $settings['registration'] ?? array() );
		$this->assertTrue( $registration['auto_approve'] );

		$redirect = $GLOBALS['fbm_last_redirect'] ?? null;
		$this->assertIsArray( $redirect );
		$parts = parse_url( $redirect['location'] ?? '' );
		$this->assertIsArray( $parts );
		$query = array();
		parse_str( $parts['query'] ?? '', $query );
		$this->assertSame( 'success', $query['fbm_registration_form_status'] ?? null );
	}

	public function testSavedOptionsReflectedInShortcode(): void {
		update_option( 'fbm_reg_label_headline', 'Updated Headline' );
		update_option( 'fbm_reg_label_submit', 'Send Application' );
		update_option( 'fbm_reg_copy_success_auto', 'Custom success copy' );
		update_option( 'fbm_reg_copy_success_pending', 'Pending copy' );
		update_option( 'fbm_reg_enable_honeypot', 0 );

		$output = RegistrationForm::render();

		$this->assertStringContainsString( 'Updated Headline', $output );
		$this->assertStringContainsString( 'Send Application', $output );
		$this->assertStringContainsString( 'Custom success copy', $output );
	}
}
