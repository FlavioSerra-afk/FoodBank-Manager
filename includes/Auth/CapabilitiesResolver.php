<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Auth;

final class CapabilitiesResolver {
    /** @since 0.1.x */
    public static function boot(): void {
        add_filter('user_has_cap', [self::class, 'grantAdmins'], 9, 4);
        add_filter('user_has_cap', [self::class, 'applyUserOverrides'], 20, 4);
    }

    /**
     * Always grant Administrators all plugin caps.
     *
     * @param array    $allcaps Effective caps being built.
     * @param array    $caps    Requested primitive caps.
     * @param array    $args    [0=>cap,1=>user_id,...]
     * @param \WP_User $user   User.
     * @return array
     */
    public static function grantAdmins(array $allcaps, array $caps, array $args, $user): array {
        if ($user instanceof \WP_User && in_array('administrator', (array) $user->roles, true)) {
            foreach (Capabilities::all() as $cap) {
                $allcaps[$cap] = true;
            }
        }
        return $allcaps;
    }

    /**
     * Apply per-user capability overrides stored in user meta.
     *
     * @param array    $allcaps Effective caps being built.
     * @param array    $caps    Requested primitive caps.
     * @param array    $args    [0=>cap,1=>user_id,...]
     * @param \WP_User $user   User.
     * @return array
     */
    public static function applyUserOverrides(array $allcaps, array $caps, array $args, $user): array {
        $meta = get_user_meta($user->ID, 'fbm_user_caps', true);
        if (is_array($meta)) {
            foreach ($meta as $cap => $val) {
                if (in_array($cap, Capabilities::all(), true)) {
                    $allcaps[$cap] = (bool) $val;
                }
            }
        }
        return $allcaps;
    }
}
