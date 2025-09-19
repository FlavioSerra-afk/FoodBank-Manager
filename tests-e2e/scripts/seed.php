<?php
declare(strict_types=1);

use DateTimeImmutable;
use DateTimeZone;
use FoodBankManager\Attendance\AttendanceRepository;
use FoodBankManager\Diagnostics\MailFailureLog;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

global $wpdb;

$members    = new MembersRepository( $wpdb );
$tokens     = new TokenService( new TokenRepository( $wpdb ) );
$attendance = new AttendanceRepository( $wpdb );
$log        = new MailFailureLog();

$active_ref = 'FBM-E2E123';
seed_member( $members, $tokens, $attendance, $active_ref, 'casey.e2e@example.com', '-10 days', 'Seeded registration' );
$override_ref = 'FBM-E2EOVR';
seed_member( $members, $tokens, $attendance, $override_ref, 'robin.override@example.com', '-3 days', 'Recent collection' );

$active_member   = $members->find_by_reference( $active_ref );
$override_member = $members->find_by_reference( $override_ref );

if ( null !== $active_member ) {
        $details = $tokens->find_active_for_member( (int) $active_member['id'] );
        if ( isset( $details['meta']['payload'] ) ) {
                update_option( 'fbm_e2e_token_active', (string) $details['meta']['payload'], false );
        } elseif ( isset( $details['token_hash'] ) ) {
                $issued = $tokens->issue_with_details( (int) $active_member['id'], array( 'context' => 'e2e-refresh' ) );
                update_option( 'fbm_e2e_token_active', $issued['token'], false );
        }
}

if ( null !== $override_member ) {
        update_option( 'fbm_e2e_override_reference', (string) $override_member['member_reference'], false );
}

$failure_ref = 'FBM-E2EFAIL';
$failure      = $members->find_by_reference( $failure_ref );
if ( null === $failure ) {
        $failure_id = $members->insert_active_member( $failure_ref, 'Drew', 'F', 'drew.failure@example.com', 2, null );
} else {
        $failure_id = (int) $failure['id'];
}

if ( $failure_id > 0 ) {
        $log->record_failure(
                $failure_id,
                $failure_ref,
                'drew.failure@example.com',
                MailFailureLog::CONTEXT_DIAGNOSTICS_RESEND,
                MailFailureLog::ERROR_MAIL
        );

        $entries = $log->entries();
        if ( ! empty( $entries ) ) {
                $entry_id = (string) $entries[0]['id'];
                $log->note_attempt( $entry_id );
                update_option( 'fbm_e2e_failure_entry', $entry_id, false );
        }
}

echo "Seeded environment for FoodBank Manager e2e tests\n";

/**
 * Seed a member with attendance and a persisted token.
 */
function seed_member( MembersRepository $members, TokenService $tokens, AttendanceRepository $attendance, string $reference, string $email, string $offset, string $note ): void {
        $existing = $members->find_by_reference( $reference );

        if ( null === $existing ) {
                $member_id = $members->insert_active_member( $reference, 'Casey', 'E', $email, 3, null );
        } else {
                $member_id = (int) $existing['id'];
        }

        if ( $member_id <= 0 ) {
                return;
        }

        $tokens->issue_with_details( $member_id, array( 'context' => 'e2e-seed' ) );

        $attendance->record(
                $reference,
                'qr',
                1,
                new DateTimeImmutable( $offset, new DateTimeZone( 'UTC' ) ),
                $note
        );
}
