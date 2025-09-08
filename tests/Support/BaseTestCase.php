<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\Notices;

// Stubs for WordPress admin menu functions used in tests.
if (!function_exists('add_menu_page')) {
    function add_menu_page(
        $page_title,
        $menu_title,
        $capability,
        $menu_slug,
        $callback = '',
        $icon_url = '',
        $position = null
    ) {
        $GLOBALS['fbm_test_calls']['add_menu_page'][] = [
            'cap'  => (string) $capability,
            'slug' => (string) $menu_slug,
        ];
        return true;
    }
}

if (!function_exists('add_submenu_page')) {
    function add_submenu_page(
        $parent_slug,
        $page_title,
        $menu_title,
        $capability,
        $menu_slug,
        $callback = ''
    ) {
        $GLOBALS['fbm_test_calls']['add_submenu_page'][] = [
            'cap'  => (string) $capability,
            'slug' => (string) $menu_slug,
        ];
        return true;
    }
}

abstract class BaseTestCase extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_test_reset_globals();
        fbm_grant_viewer();
        fbm_test_set_request_nonce();
        $GLOBALS['fbm_test_calls'] = [
            'add_menu_page'    => [],
            'add_submenu_page' => [],
        ];
        $this->resetNotices();
    }

    protected function tearDown(): void {
        $this->resetNotices();
        fbm_test_reset_globals();
        parent::tearDown();
    }

    private function resetNotices(): void {
        if (!class_exists(Notices::class)) {
            return;
        }
        $ref = new ReflectionClass(Notices::class);
        foreach ([
            'missingKek'    => false,
            'missingSodium' => false,
            'renderCount'   => 0,
        ] as $prop => $value) {
            if ($ref->hasProperty($prop)) {
                $p = $ref->getProperty($prop);
                $p->setAccessible(true);
                $p->setValue(null, $value);
            }
        }
    }
}
