<?php
/**
 * Ticket management controller.
 *
 * @package FBM\Http
 */

declare(strict_types=1);

namespace FBM\Http;

use FBM\Attendance\EventsRepo;
use FBM\Attendance\TicketService;
use FBM\Attendance\TicketsRepo;
use FoodBankManager\Logging\Audit;
use FBM\Mail\LogRepo;
use FBM\Mail\Templates\TicketTemplate;
use function absint;
use function add_query_arg;
use function apply_filters;
use function check_admin_referer;
use function current_user_can;
use function esc_url_raw;
use function admin_url;
use function get_current_user_id;
use function sanitize_email;
use function sanitize_text_field;
use function strtotime;
use function time;
use function wp_mail;
use function wp_safe_redirect;
use function wp_unslash;

/**
 * Handle ticket actions via admin-post.
 */
final class TicketsController {
    /** Issue a ticket */
    public static function issue(): void {
        if (!current_user_can('fbm_manage_events')) {
            wp_die('forbidden');
        }
        check_admin_referer('fbm_tickets_issue');
        $event_id = absint(wp_unslash($_POST['event_id'] ?? 0));
        $recipient = sanitize_email((string) wp_unslash($_POST['recipient'] ?? ''));
        $event = EventsRepo::get($event_id);
        if (!$event || ! $recipient) {
            self::redirect($event_id, 'error');
        }
        $exp = strtotime('+1 day', strtotime((string) ($event['starts_at'] ?? ''))) ?: ((int) apply_filters('fbm_now', time())  + 86400);
        $token = TicketService::createToken($event_id, $recipient, $exp);
        TicketsRepo::issue($event_id, $recipient, $exp, $token['payload']['nonce'], $token['token_hash']);
        Audit::log('ticket_issue', 'event', $event_id, get_current_user_id(), array('recipient' => $recipient));
        self::redirect($event_id, 'ticket_issued');
    }

    /** Regenerate */
    public static function regenerate(): void {
        if (!current_user_can('fbm_manage_events')) {
            wp_die('forbidden');
        }
        check_admin_referer('fbm_tickets_regen');
        $id = absint(wp_unslash($_POST['id'] ?? 0));
        $row = TicketsRepo::get($id);
        if (!$row) {
            self::redirect(0, 'error');
        }
        $exp = strtotime('+1 day', strtotime((string) $row['exp'])) ?: ((int) apply_filters('fbm_now', time())  + 86400);
        $token = TicketService::createToken($row['event_id'], $row['recipient'], $exp);
        $new_id = TicketsRepo::regenerate($id, $exp, $token['payload']['nonce'], $token['token_hash']);
        Audit::log('ticket_regen', 'ticket', $id, get_current_user_id(), array('new_id' => $new_id));
        self::redirect($row['event_id'], 'ticket_regenerated');
    }

    /** Revoke */
    public static function revoke(): void {
        if (!current_user_can('fbm_manage_events')) {
            wp_die('forbidden');
        }
        check_admin_referer('fbm_tickets_revoke');
        $id = absint(wp_unslash($_POST['id'] ?? 0));
        $row = TicketsRepo::get($id);
        if ($row) {
            TicketsRepo::revoke($id);
            Audit::log('ticket_revoke', 'ticket', $id, get_current_user_id());
            self::redirect($row['event_id'], 'ticket_revoked');
        }
        self::redirect(0, 'error');
    }

    /** Send email */
    public static function send(): void {
        if (!current_user_can('fbm_manage_events')) {
            wp_die('forbidden');
        }
        check_admin_referer('fbm_tickets_send');
        $id = absint(wp_unslash($_POST['id'] ?? 0));
        $row = TicketsRepo::get($id);
        if (!$row) {
            self::redirect(0, 'error');
        }
        $payload = TicketService::fromPayload($row['event_id'], $row['recipient'], strtotime((string) $row['exp']), $row['nonce']);
        if (!TicketService::verifyAgainstHash($payload['token'], $row['token_hash'])) {
            self::redirect($row['event_id'], 'error');
        }
        $event = EventsRepo::get($row['event_id']);
        $url = TicketService::ticketUrl($payload['token']);
        $mail = TicketTemplate::render($event['title'] ?? '', $url);
        $ok = wp_mail($row['recipient'], $mail['subject'], $mail['body']);
        LogRepo::append(array(
            'type'        => 'ticket_send',
            'original_id' => $id,
            'by'          => get_current_user_id(),
            'result'      => $ok ? 'sent' : 'error',
            'at'          => (int) apply_filters('fbm_now', time()),
        ));
        self::redirect($row['event_id'], $ok ? 'ticket_sent' : 'error');
    }

    /** Redirect helper */
    private static function redirect(int $event_id, string $notice): void {
        $url = add_query_arg(array('page' => 'fbm_events', 'id' => $event_id, 'notice' => $notice), admin_url('admin.php'));
        wp_safe_redirect(esc_url_raw($url), 303);
        if (apply_filters('fbm_http_exit', true)) {
            exit;
        }
    }
}
