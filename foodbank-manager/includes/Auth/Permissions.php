<?php

declare(strict_types=1);

namespace FoodBankManager\Auth;

class Permissions
{
    public static function user_can(string $cap): bool
    {
        return current_user_can($cap);
    }
}
