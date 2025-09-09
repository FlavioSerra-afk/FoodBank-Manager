<?php
/**
 * Ticket email template.
 *
 * @package FBM\Mail\Templates
 */

declare(strict_types=1);

namespace FBM\Mail\Templates;

use function esc_html;

/**
 * Render ticket email.
 */
final class TicketTemplate {
    /**
     * Render subject and body.
     *
     * @param string $event_title Event title.
     * @param string $url         Ticket URL.
     * @return array{subject:string,body:string}
     */
    public static function render(string $event_title, string $url): array {
        $subject = 'Your ticket for ' . $event_title;
        $body    = "Hello,\n\nPlease use this link to access your ticket:\n" . esc_html($url) . "\n";
        return array('subject' => $subject, 'body' => $body);
    }
}
