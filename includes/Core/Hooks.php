<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Shortcodes\Form;
use FoodBankManager\Shortcodes\Entries;
use FoodBankManager\Rest\Api;
use FoodBankManager\Mail\Logger;
use FoodBankManager\Admin\Notices;

class Hooks {

	public function register(): void {
		add_action( 'init', array( $this, 'register_shortcodes' ) );
		add_action( 'rest_api_init', array( Api::class, 'register_routes' ) );
		Logger::init();
		add_action( 'fbm_crypto_missing_kek', array( Notices::class, 'missing_kek' ) );
	}

	public function register_shortcodes(): void {
		add_shortcode( 'pcc_fb_form', array( Form::class, 'render' ) );
		add_shortcode( 'foodbank_entries', array( Entries::class, 'render' ) );
	}
}
