<?php

declare(strict_types=1);

namespace FoodBankManager\Core;

class Options
{
    private const PREFIX = 'fbm_';

    public static function get(string $key, $default = null)
    {
        return get_option(self::PREFIX . $key, $default);
    }

    public static function update(string $key, $value): bool
    {
        return update_option(self::PREFIX . $key, $value);
    }
}
