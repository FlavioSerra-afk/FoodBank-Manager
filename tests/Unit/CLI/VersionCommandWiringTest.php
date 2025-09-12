<?php
declare(strict_types=1);

namespace Tests\Unit\CLI;

use PHPUnit\Framework\TestCase;

final class VersionCommandWiringTest extends TestCase {
    private bool $registered = false;

    protected function setUp(): void {
        parent::setUp();
        if (!defined('WP_CLI_ROOT')) {
            define('WP_CLI_ROOT', __DIR__ . '/../../../vendor/wp-cli/wp-cli');
        }
        if (!function_exists('WP_CLI\\Utils\\load_command')) {
            require WP_CLI_ROOT . '/php/utils.php';
        }
        if (!function_exists('WP_CLI\\Dispatcher\\get_path')) {
            require WP_CLI_ROOT . '/php/dispatcher.php';
        }
        if (!defined('WP_CLI')) {
            define('WP_CLI', true);
        }
        \WP_CLI::add_hook('before_add_command:fbm', function (): void {
            $this->registered = true;
        });
    }

    protected function tearDown(): void {
        if (function_exists('remove_all_actions')) {
            remove_all_actions('before_add_command:fbm');
        }
        parent::tearDown();
    }

    public function testParentAndVersionRegistered(): void {
        \FoodBankManager\Core\Plugin::boot();
        $this->assertTrue($this->registered);
        $root = \WP_CLI::get_root_command();
        $fbm  = $root->get_subcommands()['fbm'] ?? null;
        $this->assertNotNull($fbm);
        $this->assertArrayHasKey('version', $fbm->get_subcommands());
    }
}
