<?php

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Shortcodes\Form;
use FoodBankManager\Shortcodes\Entries;
use FoodBankManager\Admin\Menu;
use FoodBankManager\Rest\Api;
use FoodBankManager\Mail\Logger;
use FoodBankManager\Admin\Notices;

class Hooks
{
    public function register(): void
    {
        add_action('init', [$this, 'register_shortcodes']);
        add_action('admin_menu', [Menu::class, 'register']);
        add_action('rest_api_init', [Api::class, 'register_routes']);
        Logger::init();
        add_action('fbm_crypto_missing_kek', [Notices::class, 'missing_kek']);
    }

    public function register_shortcodes(): void
    {
        add_shortcode('pcc_fb_form', [Form::class, 'render']);
        add_shortcode('foodbank_entries', [Entries::class, 'render']);
    }
}
