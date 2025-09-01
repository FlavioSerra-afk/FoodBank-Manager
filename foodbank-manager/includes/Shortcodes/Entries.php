<?php

declare(strict_types=1);

namespace FoodBankManager\Shortcodes;

use FoodBankManager\Auth\Permissions;

class Entries
{
    public static function render(array $atts = []): string
    {
        if (! Permissions::user_can('fb_read_entries')) {
            return '';
        }
        ob_start();
        echo '<div>' . esc_html__('Entries list placeholder.', 'foodbank-manager') . '</div>';
        return (string) ob_get_clean();
    }
}
