<?php

declare(strict_types=1);

namespace FoodBankManager\Rest;

class Api
{
    public static function register_routes(): void
    {
        (new AttendanceController())->register_routes();
    }
}
