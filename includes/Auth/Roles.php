<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Auth;

use FBM\Auth\Capabilities;

final class Roles {
    /** @since 0.1.x */
    public static function install(): void {
        // create/update custom roles if you use them (viewer/manager)
        $mgr = get_role('fb_manager');
        if (! $mgr) {
            $mgr = add_role('fb_manager', 'FoodBank Manager');
        }
        if ($mgr) {
            foreach (Capabilities::managerRoleCaps() as $cap) {
                $mgr->add_cap($cap);
            }
        }

        $viewer = get_role('fb_viewer');
        if (! $viewer) {
            $viewer = add_role('fb_viewer', 'FoodBank Viewer');
        }
        if ($viewer) {
            foreach (Capabilities::viewerRoleCaps() as $cap) {
                $viewer->add_cap($cap);
            }
        }

        self::ensure_admin_caps();
    }

    /** @since 0.1.x */
    public static function ensure_admin_caps(): void {
        Capabilities::ensure_for_admin();
    }
}
