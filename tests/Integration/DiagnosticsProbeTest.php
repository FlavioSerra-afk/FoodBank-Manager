<?php
/**
 * Integration coverage for diagnostics tooling.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Integration;

use FoodBankManager\Admin\DiagnosticsPage;
use FoodBankManager\Diagnostics\MailFailureLog;
use FoodBankManager\Diagnostics\TokenProbeService;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Token\Token;
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \FoodBankManager\Diagnostics\TokenProbeService
 * @covers \FoodBankManager\Admin\DiagnosticsPage
 */
final class DiagnosticsProbeTest extends TestCase {
        private \wpdb $wpdb;

        private MembersRepository $members;

        private TokenService $tokens;

        protected function setUp(): void {
                parent::setUp();

                $this->wpdb              = new \wpdb();
                $GLOBALS['wpdb']         = $this->wpdb;
                $GLOBALS['fbm_options']  = array();
                $GLOBALS['fbm_transients'] = array();

                $this->members = new MembersRepository( $this->wpdb );
                $this->tokens  = new TokenService( new TokenRepository( $this->wpdb ) );
        }

        protected function tearDown(): void {
                unset( $GLOBALS['wpdb'], $GLOBALS['fbm_options'], $GLOBALS['fbm_transients'] );

                parent::tearDown();
        }

        public function test_token_probe_returns_redacted_diagnostics(): void {
                $member = $this->createMember( 'FBM-DIAG1', 'diagnostics@example.com' );
                $issuance = $this->tokens->issue_with_details( $member['id'], array( 'context' => 'diagnostics' ) );

                $probe = new TokenProbeService( new Token( new TokenRepository( $this->wpdb ) ) );

                $valid = $probe->probe( $issuance['token'] );
                $this->assertSame( '1', $valid['version'] );
                $this->assertTrue( $valid['hmac_match'] );
                $this->assertFalse( $valid['revoked'] );
                $this->assertSame( array( 'version', 'hmac_match', 'revoked' ), array_keys( $valid ) );

                $this->tokens->revoke( $member['id'] );
                $revoked = $probe->probe( $issuance['token'] );
                $this->assertTrue( $revoked['hmac_match'] );
                $this->assertTrue( $revoked['revoked'] );

                $invalid = $probe->probe( 'invalid-token' );
                $this->assertNull( $invalid['version'] );
                $this->assertFalse( $invalid['hmac_match'] );
                $this->assertFalse( $invalid['revoked'] );
        }

        public function test_diagnostics_resend_respects_rate_limit(): void {
                $member = $this->createMember( 'FBM-DIAG2', 'mail@example.com' );

                $log = new MailFailureLog();
                $log->record_failure(
                        $member['id'],
                        $member['reference'],
                        'mail@example.com',
                        MailFailureLog::CONTEXT_DIAGNOSTICS_RESEND,
                        MailFailureLog::ERROR_MAIL
                );

                $entries = $log->entries();
                $this->assertNotEmpty( $entries );
                $entry_id = (string) $entries[0]['id'];
                $log->note_attempt( $entry_id );

                $reflection = new ReflectionClass( DiagnosticsPage::class );
                $method     = $reflection->getMethod( 'process_resend' );
                $method->setAccessible( true );

                /** @var array{status:bool,code:string} $outcome */
                $outcome = $method->invoke( null, $entry_id );

                $this->assertFalse( $outcome['status'] );
                $this->assertSame( 'rate-limited', $outcome['code'] );
        }

        /**
         * Create a member for diagnostics assertions.
         *
         * @return array{id:int,reference:string}
         */
        private function createMember( string $reference, string $email ): array {
                $member_id = $this->members->insert_active_member( $reference, 'Elliot', 'D', $email, 2, null );

                return array(
                        'id'        => $member_id,
                        'reference' => $reference,
                );
        }
}
