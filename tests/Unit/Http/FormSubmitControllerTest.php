<?php
declare(strict_types=1);

namespace {
    if ( ! function_exists( 'sanitize_text_field' ) ) {
        function sanitize_text_field( $str ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return trim( strip_tags( (string) $str ) );
        }
    }
    if ( ! function_exists( 'sanitize_key' ) ) {
        function sanitize_key( $key ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return preg_replace( '/[^a-z0-9_]/', '', strtolower( (string) $key ) );
        }
    }
}

namespace FoodBankManager\Tests\Unit\Http {

use FoodBankManager\Forms\PresetsRepo;
use FoodBankManager\Http\FormSubmitController;
use PHPUnit\Framework\TestCase;

final class FormSubmitControllerTest extends TestCase {
    private array $schema;

    protected function setUp(): void {
        parent::setUp();
        \fbm_test_reset_globals();
        $this->schema = array(
            'meta'   => array( 'name' => 'Test', 'slug' => 'test', 'captcha' => true ),
            'fields' => array(
                array( 'id' => 'first', 'type' => 'text', 'label' => 'First', 'required' => true ),
                array( 'id' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => false ),
            ),
        );
    }

    public function testHappyPath(): void {
        $post = array( 'first' => 'Alice', 'email' => 'a@example.com', 'captcha' => 'ok' );
        $data = FormSubmitController::validate_against_schema( $this->schema, $post );
        $this->assertSame( 'Alice', $data['first'] );
    }

    public function testUnknownFieldRejected(): void {
        $this->expectException( \RuntimeException::class );
        $post = array( 'first' => 'A', 'email' => 'x@x.com', 'unknown' => '1', 'captcha' => 'ok' );
        FormSubmitController::validate_against_schema( $this->schema, $post );
    }

    public function testCaptchaFailure(): void {
        $this->expectException( \RuntimeException::class );
        $post = array( 'first' => 'A', 'email' => 'x@x.com' );
        FormSubmitController::validate_against_schema( $this->schema, $post );
    }

    public function testHandleRejectsInvalidNonce(): void {
        PresetsRepo::upsert( $this->schema );
        \fbm_test_trust_nonces( false );
        $_POST = array(
            'action' => 'fbm_submit',
            'preset' => 'test',
            'first'  => 'Alice',
            'captcha'=> 'ok',
            '_fbm_nonce' => 'bad',
        );
        $_REQUEST = $_POST;
        $this->expectException( \RuntimeException::class );
        FormSubmitController::handle();
    }

    public function testHandleRequiresCaptcha(): void {
        PresetsRepo::upsert( $this->schema );
        \fbm_test_set_request_nonce( 'fbm_submit_form', '_fbm_nonce' );
        $_POST = array(
            'action' => 'fbm_submit',
            'preset' => 'test',
            'first'  => 'Alice',
            '_fbm_nonce' => $_REQUEST['_fbm_nonce'],
        );
        $_REQUEST = $_POST;
        $this->expectException( \RuntimeException::class );
        FormSubmitController::handle();
    }

    public function testHandleSucceedsWithValidNonceAndCaptcha(): void {
        PresetsRepo::upsert( $this->schema );
        \fbm_test_trust_nonces( false );
        \fbm_test_set_request_nonce( 'fbm_submit_form', '_fbm_nonce' );
        $_POST = array(
            'action' => 'fbm_submit',
            'preset' => 'test',
            'first'  => 'Alice',
            'email'  => 'a@example.com',
            'captcha'=> 'ok',
            '_fbm_nonce' => $_REQUEST['_fbm_nonce'],
        );
        $_REQUEST = $_POST;
        FormSubmitController::handle();
        $this->assertTrue( true );
    }
}
}
