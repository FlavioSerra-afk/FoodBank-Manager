<?php
/**
 * Manual check-in service.
 *
 * @package FBM\Attendance
 */

declare(strict_types=1);

namespace FBM\Attendance;

use DomainException;
use function absint;
use function apply_filters;
use function gmdate;
use function sanitize_text_field;
use function sanitize_textarea_field;

/**
 * Validate inputs and record manual check-ins.
 */
final class ManualCheckinService {
    /**
     * Perform a manual check-in.
     *
     * @param int    $event_id    Event ID.
     * @param string $recipient   Recipient identifier.
     * @param string $note        Reason note.
     * @param int    $operator_id Operator user ID.
     * @return int Inserted row ID.
     * @throws DomainException On invalid input.
     */
    public static function check_in(int $event_id, string $recipient, string $note, int $operator_id): int {
        $event = EventsRepo::get($event_id);
        if (!$event || ($event['status'] ?? '') !== 'active') {
            throw new DomainException('invalid_event');
        }
        $recipient = sanitize_text_field(trim($recipient));
        if ('' === $recipient) {
            throw new DomainException('invalid_recipient');
        }
        $note = sanitize_textarea_field(trim($note));
        if (mb_strlen($note) < 3) {
            throw new DomainException('invalid_note');
        }
        $operator_id = absint($operator_id);
        $now_ts = (int) apply_filters('fbm_now', time());
        $now = gmdate('Y-m-d H:i:s', $now_ts);
        return CheckinsRepo::record(array(
            'event_id'    => $event_id,
            'recipient'   => $recipient,
            'token_hash'  => null,
            'method'      => 'manual',
            'note'        => $note,
            'by'          => $operator_id,
            'verified_at' => $now,
            'created_at'  => $now,
        ));
    }
}
