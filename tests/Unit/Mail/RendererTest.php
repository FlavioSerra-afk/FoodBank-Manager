<?php
declare(strict_types=1);

use FoodBankManager\Mail\Renderer;

final class RendererTest extends \BaseTestCase {
    public function testRendersTokensEscaped(): void {
        $tpl = array(
            'subject' => 'Hi {{name}}',
            'body'    => '<p>Hello {{name}}</p>',
        );
        $data = array( 'name' => '<b>Alice</b>' );
        $out  = Renderer::render( $tpl, $data );
        $this->assertSame( 'Hi Alice', $out['subject'] );
        $this->assertSame( '<p>Hello &lt;b&gt;Alice&lt;/b&gt;</p>', $out['body'] );
    }

    public function testSendAddsHtmlHeader(): void {
        $tpl = array( 'subject' => 'Test', 'body' => '<p>Hi</p>' );
        Renderer::send( $tpl, array(), array( 'test@example.com' ) );
        $mail = $GLOBALS['fbm_last_mail'];
        $headers = is_array( $mail[3] ) ? implode( ',', $mail[3] ) : (string) $mail[3];
        $this->assertStringContainsString( 'text/html', $headers );
    }
}
