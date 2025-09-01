<?php

declare(strict_types=1);

namespace FoodBankManager\Shortcodes;

use FoodBankManager\Security\Helpers;

class Form
{
    public static function render(array $atts = []): string
    {
        $atts = shortcode_atts([
            'id' => '',
        ], $atts, 'pcc_fb_form');
        $id = Helpers::sanitize_text((string) $atts['id']);
        if ($id === '') {
            return '';
        }
        ob_start();
        echo '<form method="post">';
        wp_nonce_field('fbm_form_submit', 'fbm_nonce');
        echo '<input type="hidden" name="form_id" value="' . esc_attr($id) . '" />';
        echo '<p>' . esc_html__('Form placeholder.', 'foodbank-manager') . '</p>';
        echo '<input type="submit" value="' . esc_attr__('Submit', 'foodbank-manager') . '" />';
        echo '</form>';
        return (string) ob_get_clean();
    }
}
