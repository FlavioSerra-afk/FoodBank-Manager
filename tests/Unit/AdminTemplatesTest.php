<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'esc_html_e' ) ) {
    function esc_html_e( string $text, string $domain = 'default' ): void {
        echo $text;
    }
}
if ( ! function_exists( 'esc_html__' ) ) {
    function esc_html__( string $text, string $domain = 'default' ): string {
        return $text;
    }
}
if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return (string) $text;
    }
}
if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $text ) {
        return (string) $text;
    }
}

final class AdminTemplatesTest extends TestCase {
    /**
     * @return array<int, array{string}>
     */
    public function provider(): array {
        if (! defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 2) . '/');
        }
        $files = glob(FBM_PATH . 'templates/admin/*.php');
        return array_map(static fn($f) => array($f), $files ?: array());
    }

    /**
     * @dataProvider provider
     */
    public function testTemplatesWrapped(string $file): void {
        ob_start();
        include $file;
        $html = trim(ob_get_clean() ?: '');
        $this->assertStringStartsWith('<div class="fbm-admin"><div class="wrap">', $html);
        $this->assertStringEndsWith('</div></div>', $html);
    }
}
