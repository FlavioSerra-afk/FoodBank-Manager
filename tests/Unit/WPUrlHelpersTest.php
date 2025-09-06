<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class WPUrlHelpersTest extends TestCase {
    public function testAddQueryArgFormsAndFragments(): void {
        $url = 'https://example.test/path?one=1#frag';
        $step1 = add_query_arg(['two' => '2'], $url);
        $this->assertSame('https://example.test/path?one=1&two=2#frag', $step1);
        $step2 = add_query_arg('three', '3', $step1);
        $this->assertSame('https://example.test/path?one=1&two=2&three=3#frag', $step2);
    }

    public function testRemoveQueryArgPreservesFragment(): void {
        $url = 'https://example.test/path?one=1&two=2#frag';
        $this->assertSame('https://example.test/path?one=1#frag', remove_query_arg('two', $url));
        $this->assertSame('https://example.test/path#frag', remove_query_arg(['one', 'two'], $url));
    }

    public function testNonceUrlAppendsNonce(): void {
        $url = 'https://example.test/path';
        $nonce = wp_create_nonce('a');
        $this->assertSame($url . '?_wpnonce=' . $nonce, wp_nonce_url($url, 'a'));
    }
}
