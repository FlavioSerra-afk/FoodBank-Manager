<?php

declare(strict_types=1);

namespace FoodBankManager\Rest;

use WP_REST_Request;
use WP_REST_Response;
use FoodBankManager\Auth\Permissions;
use FoodBankManager\Security\Helpers;

class AttendanceController
{
    public function register_routes(): void
    {
        register_rest_route('pcc-fb/v1', '/attendance/checkin', [
            'methods'             => 'POST',
            'callback'            => [$this, 'checkin'],
            'permission_callback' => [$this, 'check_permissions'],
            'args'                => [
                'application_id' => [
                    'type'     => 'integer',
                    'required' => false,
                ],
                'token' => [
                    'type'     => 'string',
                    'required' => false,
                ],
            ],
        ]);
    }

    public function check_permissions(): bool
    {
        return Permissions::user_can('attendance_checkin');
    }

    public function checkin(WP_REST_Request $request): WP_REST_Response
    {
        if (! Helpers::verify_nonce('wp_rest', '_wpnonce')) {
            return new WP_REST_Response([
                'error' => [
                    'code'    => 'fbm_invalid_nonce',
                    'message' => __('Invalid nonce', 'foodbank-manager'),
                ],
            ], 403);
        }
        return new WP_REST_Response([
            'status' => 'not_implemented',
        ], 501);
    }
}
