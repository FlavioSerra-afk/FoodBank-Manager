<?php

declare(strict_types=1);

namespace FoodBankManager\Core;

class Assets
{
    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin']);
    }

    public function enqueue_front(): void
    {
        // Placeholder for front-end assets.
    }

    public function enqueue_admin(): void
    {
        // Placeholder for admin assets.
    }
}
