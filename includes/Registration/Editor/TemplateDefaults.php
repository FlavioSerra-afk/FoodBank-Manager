<?php // phpcs:disable WordPress.Files.FileName.InvalidClassFileName,WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Registration template defaults.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Registration\Editor;

use function __;
use function in_array;
use function is_array;
use function json_decode;
use function sanitize_key;
use function sanitize_text_field;

/**
 * Provides default template and settings payloads.
 */
final class TemplateDefaults {
	private const PRESETS_JSON = <<<'JSON'
[
    {
        "id": "require-proof-outside-borough",
        "label": "Require proof upload when outside borough",
        "description": "Require households outside your service borough to upload proof of address.",
        "placeholders": [
            {"key": "boroughField", "type": "field", "label": "Borough or area field"},
            {"key": "proofField", "type": "field", "label": "Proof upload field"},
            {"key": "boroughValue", "type": "value", "label": "Served borough value", "default": "Local borough"}
        ],
        "groups": [
            {
                "operator": "and",
                "conditions": [
                    {"field": "{{boroughField}}", "operator": "not_equals", "value": "{{boroughValue}}"}
                ],
                "actions": [
                    {"type": "require", "target": "{{proofField}}"},
                    {"type": "show", "target": "{{proofField}}"}
                ]
            }
        ]
    },
    {
        "id": "show-children-ages",
        "label": "Show children ages when children count > 0",
        "description": "Reveal the children ages field when households report children in the home.",
        "placeholders": [
            {"key": "childrenCountField", "type": "field", "label": "Children count field"},
            {"key": "childrenAgesField", "type": "field", "label": "Children ages field"}
        ],
        "groups": [
            {
                "operator": "and",
                "conditions": [
                    {"field": "{{childrenCountField}}", "operator": "gt", "value": "0"}
                ],
                "actions": [
                    {"type": "show", "target": "{{childrenAgesField}}"},
                    {"type": "optional", "target": "{{childrenAgesField}}"}
                ]
            }
        ]
    },
    {
        "id": "dietary-details",
        "label": "Ask dietary details when restrictions = Yes",
        "description": "Collect dietary restriction details only when the household marks the checkbox.",
        "placeholders": [
            {"key": "dietaryToggleField", "type": "field", "label": "Dietary restriction toggle field"},
            {"key": "dietaryDetailsField", "type": "field", "label": "Dietary details field"}
        ],
        "groups": [
            {
                "operator": "and",
                "conditions": [
                    {"field": "{{dietaryToggleField}}", "operator": "equals", "value": "Yes"}
                ],
                "actions": [
                    {"type": "show", "target": "{{dietaryDetailsField}}"},
                    {"type": "require", "target": "{{dietaryDetailsField}}"}
                ]
            }
        ]
    },
    {
        "id": "contact-phone-required",
        "label": "Require phone when contact preference is Phone",
        "description": "Ensure phone number is provided for neighbours who prefer phone communication.",
        "placeholders": [
            {"key": "contactPreferenceField", "type": "field", "label": "Contact preference field"},
            {"key": "phoneField", "type": "field", "label": "Phone number field"},
            {"key": "preferenceValue", "type": "value", "label": "Phone preference value", "default": "Phone"}
        ],
        "groups": [
            {
                "operator": "and",
                "conditions": [
                    {"field": "{{contactPreferenceField}}", "operator": "equals", "value": "{{preferenceValue}}"}
                ],
                "actions": [
                    {"type": "show", "target": "{{phoneField}}"},
                    {"type": "require", "target": "{{phoneField}}"}
                ]
            }
        ]
    },
    {
        "id": "emergency-contact",
        "label": "Show emergency contact when health concerns = Yes",
        "description": "Prompt for an emergency contact when applicants report health considerations.",
        "placeholders": [
            {"key": "healthFlagField", "type": "field", "label": "Health concerns field"},
            {"key": "emergencyContactField", "type": "field", "label": "Emergency contact field"}
        ],
        "groups": [
            {
                "operator": "and",
                "conditions": [
                    {"field": "{{healthFlagField}}", "operator": "equals", "value": "Yes"}
                ],
                "actions": [
                    {"type": "show", "target": "{{emergencyContactField}}"},
                    {"type": "require", "target": "{{emergencyContactField}}"}
                ]
            }
        ]
    },
    {
        "id": "hide-delivery-notes",
        "label": "Hide delivery notes for in-person pickup",
        "description": "Hide delivery instructions when the visitor chooses an in-person pickup option.",
        "placeholders": [
            {"key": "pickupMethodField", "type": "field", "label": "Pickup method field"},
            {"key": "deliveryNotesField", "type": "field", "label": "Delivery notes field"},
            {"key": "pickupValue", "type": "value", "label": "In-person value", "default": "Pickup"}
        ],
        "groups": [
            {
                "operator": "and",
                "conditions": [
                    {"field": "{{pickupMethodField}}", "operator": "equals", "value": "{{pickupValue}}"}
                ],
                "actions": [
                    {"type": "hide", "target": "{{deliveryNotesField}}"},
                    {"type": "optional", "target": "{{deliveryNotesField}}"}
                ]
            }
        ]
    }
]
JSON;

		/**
		 * Current rule schema version.
		 */
	public static function condition_schema_version(): int {
			return Conditions::SCHEMA_VERSION;
	}

		/**
		 * Preset rule catalog.
		 *
		 * @return array<int,array<string,mixed>>
		 */
	public static function presets(): array {
		$decoded = json_decode( self::PRESETS_JSON, true );

		if ( ! is_array( $decoded ) ) {
			return array();
		}

		/**
		 * Normalized preset definitions.
		 *
		 * @var array<int,array<string,mixed>> $decoded
		 */
		$decoded = array_values( $decoded );
		$presets = array();

		foreach ( $decoded as $preset ) {
			if ( ! is_array( $preset ) ) {
				continue;
			}

			/**
			 * Sanitized preset payload.
			 *
			 * @var array<string,mixed> $preset_data
			 */
			$preset_data = $preset;

			$id     = isset( $preset_data['id'] ) ? sanitize_key( (string) $preset_data['id'] ) : '';
			$groups = isset( $preset_data['groups'] ) && is_array( $preset_data['groups'] ) ? $preset_data['groups'] : array();

			if ( '' === $id || empty( $groups ) ) {
				continue;
			}

			$label       = isset( $preset_data['label'] ) ? sanitize_text_field( (string) $preset_data['label'] ) : $id;
			$description = isset( $preset_data['description'] ) ? sanitize_text_field( (string) $preset_data['description'] ) : '';

			$placeholders = array();
			if ( isset( $preset_data['placeholders'] ) && is_array( $preset_data['placeholders'] ) ) {
				foreach ( $preset_data['placeholders'] as $placeholder ) {
					if ( ! is_array( $placeholder ) ) {
						continue;
					}

					/**
					 * Placeholder definition payload.
					 *
					 * @var array<string,mixed> $placeholder_data
					 */
					$placeholder_data = $placeholder;

					$key  = isset( $placeholder_data['key'] ) ? sanitize_key( (string) $placeholder_data['key'] ) : '';
					$type = isset( $placeholder_data['type'] ) ? sanitize_key( (string) $placeholder_data['type'] ) : '';

					if ( '' === $key || ! in_array( $type, array( 'field', 'value' ), true ) ) {
						continue;
					}

					$placeholders[] = array(
						'key'     => $key,
						'type'    => $type,
						'label'   => isset( $placeholder_data['label'] ) ? sanitize_text_field( (string) $placeholder_data['label'] ) : $key,
						'default' => isset( $placeholder_data['default'] ) ? sanitize_text_field( (string) $placeholder_data['default'] ) : '',
					);
				}
			}

			$presets[] = array(
				'id'           => $id,
				'label'        => $label,
				'description'  => $description,
				'placeholders' => $placeholders,
				'groups'       => $groups,
			);
		}

		return $presets;
	}

				/**
				 * Default HTML+tag template for the registration form.
				 */
	public static function template(): string {
			return <<<'HTML'
<div class="fbm-registration-editor__wrap">
        <fieldset class="fbm-registration-editor__section">
                <legend class="fbm-registration-editor__legend">Register for weekly collection</legend>
                <p class="fbm-registration-editor__intro">Tell us how to contact you. Required fields are marked with an asterisk.</p>
                <div class="fbm-registration-editor__grid">
                        <label class="fbm-registration-editor__label">First name</label>
                        [text* fbm_first_name placeholder "Enter your first name" autocomplete "given-name"]
                        <label class="fbm-registration-editor__label">Last initial</label>
                        [text* fbm_last_initial placeholder "Enter the first letter of your last name" autocomplete "family-name"]
                        <label class="fbm-registration-editor__label">Email address</label>
                        [email* fbm_email placeholder "name@example.com" autocomplete "email"]
                        <label class="fbm-registration-editor__label">Household size</label>
                        [number* fbm_household_size min:1 max:12 step:1 placeholder "1"]
                </div>
        </fieldset>
        <fieldset class="fbm-registration-editor__section">
                <legend class="fbm-registration-editor__legend">Supporting information</legend>
                <p class="fbm-registration-editor__intro">Upload a proof of address if available. This helps speed up approvals.</p>
                [file fbm_proof_of_address class:fbm-input-file]
                <div class="fbm-registration-editor__consent">
                        [checkbox fbm_registration_consent "Yes, I consent to occasional service updates by email."]
                </div>
        </fieldset>
        <div class="fbm-registration-editor__actions">
                [submit fbm_submit_button "Submit registration"]
        </div>
</div>
HTML;
	}

		/**
		 * Default registration editor settings payload.
		 *
		 * @return array<string,mixed>
		 */
	public static function settings(): array {
			return array(
				'uploads'    => array(
					'max_size'           => 5242880,
					'allowed_mime_types' => array(
						'application/pdf',
						'image/jpeg',
						'image/png',
					),
				),
				'conditions' => array(
					'enabled' => false,
					'groups'  => array(),
				),
				'editor'     => array(
					'theme' => 'light',
				),
				'honeypot'   => true,
				'messages'   => array(
					'success_auto'    => __( 'Thank you for registering. We have emailed your check-in QR code.', 'foodbank-manager' ),
					'success_pending' => __( 'Thank you for registering. Our team will review your application and send your QR code once approved.', 'foodbank-manager' ),
				),
			);
	}
}

// phpcs:enable WordPress.Files.FileName.InvalidClassFileName,WordPress.Files.FileName.NotHyphenatedLowercase
