<?php // phpcs:disable WordPress.Files.FileName.InvalidClassFileName,WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Registration template defaults.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Registration\Editor;

use function __;

/**
 * Provides default template and settings payloads.
 */
final class TemplateDefaults {
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
