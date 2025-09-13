<?php
if (!defined('FBM_PATH')) {
    define('FBM_PATH', dirname(__DIR__, 2) . '/');
}

final class PackagingGuardTest extends \PHPUnit\Framework\TestCase {
    public function test_production_autoloader_excludes_tests(): void {
        exec('composer dump-autoload --no-dev --classmap-authoritative');
        $map = (string) file_get_contents(FBM_PATH . 'vendor/composer/autoload_classmap.php');
        $this->assertStringNotContainsString('tests/Support/WPStubs.php', $map);
        exec('composer dump-autoload');
    }
}
