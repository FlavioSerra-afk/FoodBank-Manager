<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class CliRegistrationTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        if (!defined('WP_CLI_ROOT')) {
            define('WP_CLI_ROOT', __DIR__ . '/../../../vendor/wp-cli/wp-cli');
        }
        if (!function_exists('WP_CLI\\Utils\\load_command')) {
            require WP_CLI_ROOT . '/php/utils.php';
        }
        if (!defined('WP_CLI')) {
            define('WP_CLI', true);
        }
    }

    public function test_version_command_registered(): void {
        $called = false;
        \WP_CLI::add_hook('before_add_command:fbm version', function () use (&$called): void {
            $called = true;
        });
        \FoodBankManager\Core\Plugin::boot();
        $this->assertTrue($called);
    }
}
