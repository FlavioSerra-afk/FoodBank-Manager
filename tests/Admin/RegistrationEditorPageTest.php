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
                        $GLOBALS['fbm_registered_scripts'] = array();
                        $GLOBALS['fbm_enqueued_scripts']  = array();
                        $GLOBALS['fbm_localized_scripts'] = array();
                        $GLOBALS['fbm_inline_scripts']    = array();
                        $GLOBALS['fbm_code_editor_requests'] = array();
                        $GLOBALS['fbm_current_screen']        = null;
                        unset( $GLOBALS['fbm_code_editor_disabled'], $GLOBALS['fbm_code_editor_response'] );
        }

        protected function tearDown(): void {
                        $GLOBALS['fbm_settings_errors'] = array();
                        $GLOBALS['fbm_registered_scripts'] = array();
                        $GLOBALS['fbm_enqueued_scripts']  = array();
                        $GLOBALS['fbm_localized_scripts'] = array();
                        $GLOBALS['fbm_inline_scripts']    = array();
                        $GLOBALS['fbm_code_editor_requests'] = array();
                        $GLOBALS['fbm_current_screen']        = null;
                        unset( $GLOBALS['fbm_code_editor_disabled'], $GLOBALS['fbm_code_editor_response'] );

                        parent::tearDown();
        }

        public function test_enqueue_assets_ignores_other_screens(): void {
                        $GLOBALS['fbm_current_screen'] = (object) array( 'id' => 'fbm-admin_page_other' );

                        RegistrationEditorPage::enqueue_assets( 'fbm-admin_page_other' );

                        $this->assertArrayNotHasKey( 'fbm-registration-editor', $GLOBALS['fbm_registered_scripts'] );
                        $this->assertSame( array(), $GLOBALS['fbm_code_editor_requests'] );
        }

        public function test_enqueue_assets_initializes_code_editor(): void {
                        $GLOBALS['fbm_current_screen'] = (object) array( 'id' => 'fbm-admin_page_fbm-registration-form' );

                        RegistrationEditorPage::enqueue_assets( 'fbm-admin_page_fbm-registration-form' );

                        $this->assertArrayHasKey( 'fbm-registration-editor', $GLOBALS['fbm_registered_scripts'] );
                        $this->assertContains( 'fbm-registration-editor', $GLOBALS['fbm_enqueued_scripts'] );
                        $this->assertNotEmpty( $GLOBALS['fbm_code_editor_requests'] );

                        $this->assertArrayHasKey( 'fbm-registration-editor', $GLOBALS['fbm_localized_scripts'] );
                        $localized = $GLOBALS['fbm_localized_scripts']['fbm-registration-editor'];
                        $this->assertSame( 'fbmRegistrationEditor', $localized['name'] );
                        $this->assertSame( 'nonce-wp_rest', $localized['data']['restNonce'] );
                        $this->assertNotEmpty( $localized['data']['codeEditor'] );

                        $inline = $GLOBALS['fbm_inline_scripts']['fbm-registration-editor']['before'] ?? array();
                        $this->assertNotEmpty( $inline );
                        $this->assertStringContainsString( 'wp.codeEditor.initialize', $inline[0] );
                        $this->assertStringContainsString( 'fbm_registration_template_field', $inline[0] );
        }

        public function test_enqueue_assets_without_code_editor_uses_textarea(): void {
                        $GLOBALS['fbm_code_editor_disabled'] = true;
                        $GLOBALS['fbm_current_screen']        = (object) array( 'id' => 'fbm-admin_page_fbm-registration-form' );

                        RegistrationEditorPage::enqueue_assets( 'fbm-admin_page_fbm-registration-form' );

                        $this->assertNotEmpty( $GLOBALS['fbm_code_editor_requests'] );
                        $this->assertArrayHasKey( 'fbm-registration-editor', $GLOBALS['fbm_localized_scripts'] );
                        $data = $GLOBALS['fbm_localized_scripts']['fbm-registration-editor']['data'];
                        $this->assertSame( array(), $data['codeEditor'] );
                        $this->assertArrayNotHasKey( 'fbm-registration-editor', $GLOBALS['fbm_inline_scripts'] );
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

        public function test_sanitize_settings_enables_conditions_with_valid_groups(): void {
                        $raw = array(
                                'conditions' => array(
                                        'enabled' => '1',
                                        'groups'  => array(
                                                array(
                                                        'operator'   => 'OR',
                                                        'conditions' => array(
                                                                array(
                                                                        'field'    => 'fbm_first_name',
                                                                        'operator' => 'equals',
                                                                        'value'    => 'YES',
                                                                ),
                                                                array(
                                                                        'field'    => 'fbm_email',
                                                                        'operator' => 'contains',
                                                                        'value'    => '@example.com',
                                                                ),
                                                        ),
                                                        'actions'    => array(
                                                                array(
                                                                        'type'   => 'show',
                                                                        'target' => 'fbm_registration_consent',
                                                                ),
                                                                array(
                                                                        'type'   => 'require',
                                                                        'target' => 'fbm_email',
                                                                ),
                                                        ),
                                                ),
                                        ),
                                ),
                        );

                        $settings = RegistrationEditorPage::sanitize_settings( $raw );

                        $this->assertTrue( $settings['conditions']['enabled'] );
                        $this->assertSame(
                                array(
                                        array(
                                                'operator'   => 'or',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'fbm_first_name',
                                                                'operator' => 'equals',
                                                                'value'    => 'YES',
                                                        ),
                                                        array(
                                                                'field'    => 'fbm_email',
                                                                'operator' => 'contains',
                                                                'value'    => '@example.com',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'show',
                                                                'target' => 'fbm_registration_consent',
                                                        ),
                                                        array(
                                                                'type'   => 'require',
                                                                'target' => 'fbm_email',
                                                        ),
                                                ),
                                        ),
                                ),
                                $settings['conditions']['groups']
                        );
        }

        public function test_sanitize_settings_converts_legacy_rules_to_groups(): void {
                        $raw = array(
                                'conditions' => array(
                                        'enabled' => '1',
                                        'rules'   => array(
                                                array(
                                                        'if_field' => 'fbm_first_name',
                                                        'operator' => 'equals',
                                                        'value'    => 'YES',
                                                        'action'   => 'show',
                                                        'target'   => 'fbm_registration_consent',
                                                ),
                                                array(
                                                        'if_field' => '',
                                                        'operator' => 'equals',
                                                        'value'    => 'ignored',
                                                        'action'   => 'hide',
                                                        'target'   => '',
                                                ),
                                        ),
                                ),
                        );

                        $settings = RegistrationEditorPage::sanitize_settings( $raw );

                        $this->assertTrue( $settings['conditions']['enabled'] );
                        $this->assertSame(
                                array(
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'fbm_first_name',
                                                                'operator' => 'equals',
                                                                'value'    => 'YES',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'show',
                                                                'target' => 'fbm_registration_consent',
                                                        ),
                                                ),
                                        ),
                                ),
                                $settings['conditions']['groups']
                        );
        }

        public function test_sanitize_condition_groups_accepts_json_payload(): void {
                        $method = new ReflectionMethod( RegistrationEditorPage::class, 'sanitize_condition_groups' );
                        $method->setAccessible( true );

                        $raw = json_encode(
                                array(
                                        array(
                                                'operator'   => 'or',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'fbm_email',
                                                                'operator' => 'contains',
                                                                'value'    => '@example.com',
                                                        ),
                                                        array(
                                                                'field'    => '',
                                                                'operator' => 'equals',
                                                                'value'    => 'ignored',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'hide',
                                                                'target' => 'fbm_registration_consent',
                                                        ),
                                                ),
                                        ),
                                )
                        );

                        $this->assertIsString( $raw );

                        /** @var array<int,array<string,mixed>> $result */
                        $result = $method->invoke( null, $raw );

                        $this->assertSame(
                                array(
                                        array(
                                                'operator'   => 'or',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'fbm_email',
                                                                'operator' => 'contains',
                                                                'value'    => '@example.com',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'hide',
                                                                'target' => 'fbm_registration_consent',
                                                        ),
                                                ),
                                        ),
                                ),
                                $result
                        );
        }
}
