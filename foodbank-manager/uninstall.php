<?php

declare(strict_types=1);

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) { require __DIR__ . '/vendor/autoload.php'; }

FoodBankManager\Auth\Roles::uninstall();

