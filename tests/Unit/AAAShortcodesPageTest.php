<?php
declare(strict_types=1);

namespace FoodBankManager\Admin {}

namespace {}

namespace {
use BaseTestCase;
use FoodBankManager\Admin\ShortcodesPage;

/** @backupGlobals disabled */
final class ShortcodesPageTest extends BaseTestCase {
    public static string $last_shortcode = '';

    protected function setUp(): void {
        parent::setUp();
        fbm_grant_for_page('fbm_shortcodes');
        self::$last_shortcode = '';
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 2) . '/');
        }
        add_shortcode('fbm_form', function (array $atts): string {
            $id = $atts['id'] ?? '1';
            ShortcodesPageTest::$last_shortcode = sprintf('[fbm_form id="%s" preset="basic_intake" mask_sensitive="true"]', $id);
            return '<div>ok</div><script>alert(1)</script>';
        });
    }

    protected function tearDown(): void {
        $GLOBALS['fbm_shortcodes'] = array();
        parent::tearDown();
    }

    public function testDiscoverShortcodesMetadata(): void {
        $shortcodes = ShortcodesPage::discover();
        $map = array();
        foreach ( $shortcodes as $sc ) {
            $map[ $sc['tag'] ] = $sc['atts'];
        }
        $this->assertArrayHasKey( 'fbm_form', $map );
        $this->assertSame( 'int', $map['fbm_form']['id']['type'] );
        $this->assertSame( '1', $map['fbm_form']['id']['default'] );
    }

    public function testCapabilityRequired(): void {
        fbm_grant_caps([]);
        $this->expectException(\Tests\Support\Exceptions\FbmDieException::class);
        ShortcodesPage::route();
    }

    public function testInvalidNonceBlocksPreview(): void {
        fbm_test_trust_nonces(false);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['fbm_action']       = 'shortcode_preview';
        $_POST['tag']              = 'fbm_form';
        $this->expectException( \RuntimeException::class );
        ShortcodesPage::route();
    }

    public function testUnknownShortcodeRejected(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        fbm_test_set_request_nonce('fbm_shortcodes_preview');
        $_POST['fbm_action'] = 'shortcode_preview';
        $_POST['tag']        = 'fbm_bad';
        $_REQUEST            = $_POST;
        ob_start();
        ShortcodesPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('<div class="wrap fbm-admin">', $html);
        $this->assertStringContainsString('Invalid shortcode', $html);
        $this->assertSame('', self::$last_shortcode);
    }

    public function testAttributeSanitizer(): void {
        $ref    = new \ReflectionClass( ShortcodesPage::class );
        $method = $ref->getMethod( 'sanitize_atts' );
        $method->setAccessible( true );
        $meta = array(
            'foo'    => array( 'type' => 'string', 'default' => 'def' ),
            'num'    => array( 'type' => 'int', 'default' => '1' ),
            'choice' => array( 'type' => 'enum', 'default' => 'a', 'options' => array( 'a', 'b' ) ),
        );
        $raw = array(
            'foo'    => '<b>' . str_repeat( 'x', 300 ),
            'num'    => '7abc',
            'choice' => 'c',
            'bad'    => 'zzz',
        );
        /** @var array<string,string> $res */
        $res = $method->invoke( null, $meta, $raw );
        $this->assertSame( '7', $res['num'] );
        $this->assertArrayNotHasKey( 'choice', $res );
        $this->assertArrayNotHasKey( 'bad', $res );
        $this->assertSame( 256, strlen( $res['foo'] ) );
    }

    public function testPreviewFiltersHtml(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        fbm_test_set_request_nonce('fbm_shortcodes_preview');
        $_POST = array(
            'fbm_action' => 'shortcode_preview',
            'tag'        => 'fbm_form',
            'atts'       => array( 'id' => '1' ),
            '_wpnonce'   => $_POST['_wpnonce'], // retained from helper
        );
        $_REQUEST = $_POST;
        ob_start();
        ShortcodesPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('<div class="wrap fbm-admin">', $html);
        $this->assertStringContainsString('<div class="fbm-preview"><div>ok</div></div>', $html);
        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertSame('[fbm_form id="1" preset="basic_intake" mask_sensitive="true"]', self::$last_shortcode);
    }
}
}
