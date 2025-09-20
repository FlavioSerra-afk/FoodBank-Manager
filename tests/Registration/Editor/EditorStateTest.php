<?php
// phpcs:ignoreFile
/**
 * Editor state tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Registration\Editor;

use FoodBankManager\Registration\Editor\EditorState;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Registration\Editor\EditorState
 */
final class EditorStateTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();

                $GLOBALS['fbm_options']    = array();
                $GLOBALS['fbm_transients'] = array();
                $GLOBALS['fbm_users']      = array(
                        1 => array(
                                'ID'           => 1,
                                'display_name' => '<strong>Site Admin</strong>',
                                'user_login'   => 'admin',
                        ),
                );
        }

        protected function tearDown(): void {
                $GLOBALS['fbm_options']    = array();
                $GLOBALS['fbm_transients'] = array();
                $GLOBALS['fbm_users']      = array();

                parent::tearDown();
        }

        public function test_record_revision_limits_history(): void {
                for ( $i = 0; $i < 7; $i++ ) {
                        EditorState::record_revision(
                                1,
                                'Tester',
                                '<div>' . $i . '</div>',
                                array( 'conditions' => array( 'enabled' => ( $i % 2 ) === 0 ) )
                        );
                }

                $revisions = EditorState::list_revisions();

                $this->assertCount( 5, $revisions );
                $this->assertSame( '<div>6</div>', $revisions[0]['template'] );
                $this->assertSame( '<div>2</div>', $revisions[4]['template'] );
        }

        public function test_autosave_set_get_and_clear(): void {
                $payload = array(
                        'template'  => '<div>draft</div>',
                        'settings'  => array( 'conditions' => array( 'enabled' => true ) ),
                        'timestamp' => 123456,
                );

                EditorState::set_autosave( 1, $payload );

                $stored = EditorState::get_autosave( 1 );

                $this->assertNotNull( $stored );
                $this->assertSame( '<div>draft</div>', $stored['template'] );
                $this->assertSame( 123456, $stored['timestamp'] );

                EditorState::clear_autosave( 1 );

                $this->assertNull( EditorState::get_autosave( 1 ) );
        }

        public function test_find_revision_returns_match(): void {
                $first  = EditorState::record_revision( 1, 'Tester', '<div>A</div>', array() );
                $second = EditorState::record_revision( 1, 'Tester', '<div>B</div>', array() );

                $this->assertSame( '<div>B</div>', EditorState::find_revision( (string) $second['id'] )['template'] );
                $this->assertNull( EditorState::find_revision( 'missing' ) );
                $this->assertSame( '<div>A</div>', EditorState::list_revisions()[1]['template'] );
        }

        public function test_current_user_context_returns_sanitized_user(): void {
                $context = EditorState::current_user_context();

                $this->assertSame( 1, $context['user_id'] );
                $this->assertSame( 'Site Admin', $context['user_name'] );
        }
}
