<?php
declare(strict_types=1);

use FoodBankManager\Mail\FailureLog;

if ( ! class_exists( 'WP_Error' ) ) {
    class WP_Error {
        private string $msg;
        public function __construct( string $code = '', string $message = '' ) {
            $this->msg = $message;
        }
        public function get_error_message(): string {
            return $this->msg;
        }
    }
}

final class FailureLogTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        update_option('fbm_mail_failures', array());
        FailureLog::init();
    }

    public function testCaptureAndRetry(): void {
        // Simulate failure
        $err = new \WP_Error('test', 'Boom');
        do_action('wp_mail_failed', $err, array(
            'to' => 'fail@example.com',
            'subject' => 'Subj',
            'message' => '<p>Body</p>',
            'headers' => array(),
        ));
        $logs = FailureLog::recent();
        $this->assertNotEmpty( $logs );
        $this->assertSame( 'Boom', $logs[0]['error'] );
        // Retry
        FailureLog::retry(0);
        $mail = $GLOBALS['fbm_last_mail'];
        $this->assertSame( array( 'fail@example.com' ), $mail[0] );
        $headers = is_array( $mail[3] ) ? implode(',', $mail[3]) : (string) $mail[3];
        $this->assertStringContainsString( 'text/html', $headers );
    }
}
