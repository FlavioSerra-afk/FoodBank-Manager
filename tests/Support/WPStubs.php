<?php
declare(strict_types=1);

// ---- Admin namespace shims (Menu registration spies) ----
namespace FoodBankManager\Admin {
    function add_menu_page(...$args) {
        $GLOBALS['fbm_test_calls']['add_menu_page'][] = $args;
        // WP returns a hook_suffix; we mimic a predictable one
        return 'toplevel_page_fbm';
    }

    function add_submenu_page(...$args) {
        $GLOBALS['fbm_test_calls']['add_submenu_page'][] = $args;
        // return predictable hook_suffix
        return 'foodbank_page_' . ($args[4] ?? 'fbm_unknown');
    }

    function filter_input($type, $var_name, $filter = FILTER_DEFAULT, $options = null) {
        if ($type === INPUT_POST) {
            return $_POST[$var_name] ?? null;
        }
        if ($type === INPUT_GET) {
            return $_GET[$var_name] ?? null;
        }
        return null;
    }
}

// ---- Core namespace shim (screen gating) ----
namespace FoodBankManager\Core {
    function get_current_screen() {
        // Allow tests to set a fake screen id via global
        $id = $GLOBALS['fbm_test_screen_id'] ?? null;
        if (!$id) {
            return null;
        }
        $o = new \stdClass();
        $o->id = (string) $id;
        return $o;
    }
}
