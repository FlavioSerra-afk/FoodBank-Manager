<?php
/**
 * Upload helper tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Registration;

use FoodBankManager\Registration\Uploads;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Registration\Uploads
 */
final class UploadsTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();

                $GLOBALS['fbm_upload_stub'] = array();
        }

        protected function tearDown(): void {
                $GLOBALS['fbm_upload_stub'] = array();

                parent::tearDown();
        }

        public function test_process_rejects_disallowed_mime(): void {
                $GLOBALS['fbm_upload_stub']['filetype_result'] = array(
                        'type' => 'text/plain',
                        'ext'  => 'txt',
                );

                $file = array(
                        'name'     => 'note.txt',
                        'type'     => 'text/plain',
                        'tmp_name' => '/tmp/note.txt',
                        'error'    => 0,
                        'size'     => 512,
                );

                $settings = array(
                        'max_size'           => 2048,
                        'allowed_mime_types' => array( 'application/pdf' ),
                );

                $result = Uploads::process( 'fbm_file', $file, $settings );

                $this->assertSame( 'error', $result['status'] );
                $this->assertSame( 0, $result['attachment_id'] );
                $this->assertSame( '', $result['url'] );
                $this->assertSame( '', $result['path'] );
                $this->assertSame( 'File type is not permitted.', $result['error'] );
        }

        public function test_process_returns_error_when_upload_handler_fails(): void {
                $GLOBALS['fbm_upload_stub']['filetype_result'] = array(
                        'type' => 'application/pdf',
                        'ext'  => 'pdf',
                );

                $GLOBALS['fbm_upload_stub']['handle_upload_result'] = array(
                        'error' => 'Upload failed',
                );

                $file = array(
                        'name'     => 'document.pdf',
                        'type'     => 'application/pdf',
                        'tmp_name' => '/tmp/document.pdf',
                        'error'    => 0,
                        'size'     => 512,
                );

                $settings = array(
                        'max_size'           => 2048,
                        'allowed_mime_types' => array( 'application/pdf' ),
                );

                $result = Uploads::process( 'fbm_file', $file, $settings );

                $this->assertSame( 'error', $result['status'] );
                $this->assertSame( 'Upload failed', $result['error'] );
        }

        public function test_process_returns_stored_attachment_metadata(): void {
                $GLOBALS['fbm_upload_stub']['filetype_result'] = array(
                        'type' => 'application/pdf',
                        'ext'  => 'pdf',
                );

                $GLOBALS['fbm_upload_stub']['handle_upload_result'] = array(
                        'file' => '/tmp/receipt.pdf',
                        'url'  => 'https://example.test/uploads/receipt.pdf',
                        'type' => 'application/pdf',
                );

                $GLOBALS['fbm_upload_stub']['insert_attachment_result'] = 321;

                $file = array(
                        'name'     => 'receipt.pdf',
                        'type'     => 'application/pdf',
                        'tmp_name' => '/tmp/receipt.pdf',
                        'error'    => 0,
                        'size'     => 1024,
                );

                $settings = array(
                        'max_size'           => 2048,
                        'allowed_mime_types' => array( 'application/pdf' ),
                );

                $result = Uploads::process( 'fbm_file', $file, $settings );

                $this->assertSame( 'stored', $result['status'] );
                $this->assertSame( 321, $result['attachment_id'] );
                $this->assertSame( 'https://example.test/uploads/receipt.pdf', $result['url'] );
                $this->assertSame( 'application/pdf', $result['type'] );
                $this->assertSame( '/tmp/receipt.pdf', $result['path'] );

                $this->assertNotEmpty( $GLOBALS['fbm_upload_stub']['insert_attachment_calls'] ?? array() );
                $attachment = $GLOBALS['fbm_upload_stub']['insert_attachment_calls'][0]['attachment'] ?? array();
                $this->assertSame( 'private', $attachment['post_status'] ?? '' );
        }

        public function test_cleanup_deletes_attachment_and_files_without_ids(): void {
                Uploads::cleanup(
                        array(
                                array(
                                        'attachment_id' => 42,
                                        'path'          => '/tmp/has-id.pdf',
                                ),
                                array(
                                        'attachment_id' => 0,
                                        'path'          => '/tmp/orphan.pdf',
                                ),
                        )
                );

                $deleted = $GLOBALS['fbm_upload_stub']['deleted_attachments'] ?? array();
                $this->assertCount( 1, $deleted );
                $this->assertSame( 42, $deleted[0]['attachment_id'] );
                $this->assertTrue( $deleted[0]['force'] );

                $deleted_files = $GLOBALS['fbm_upload_stub']['deleted_files'] ?? array();
                $this->assertSame( array( '/tmp/orphan.pdf' ), $deleted_files );
        }
}

