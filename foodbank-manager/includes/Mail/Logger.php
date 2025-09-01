<?php

declare(strict_types=1);

namespace FoodBankManager\Mail;

class Logger
{
    public static function init(): void
    {
        add_action('wp_mail_succeeded', [self::class, 'log_succeeded']);
        add_action('wp_mail_failed', [self::class, 'log_failed'], 10, 2);
    }

    public static function log_succeeded(array $mail): void
    {
        self::insert_log($mail, 'succeeded');
    }

    public static function log_failed(\WP_Error $error, array $mail): void
    {
        self::insert_log($mail, 'failed', $error->get_error_message());
    }

    private static function insert_log(array $mail, string $status, string $provider_msg = ''): void
    {
        global $wpdb;
        $to = is_array($mail['to']) ? implode(',', $mail['to']) : (string) $mail['to'];
        $subject = (string) ($mail['subject'] ?? '');
        $headers = is_array($mail['headers']) ? implode("\n", $mail['headers']) : (string) ($mail['headers'] ?? '');
        $body_hash = hash('sha256', (string) ($mail['message'] ?? ''));
        $wpdb->insert(
            $wpdb->prefix . 'fb_mail_log',
            [
                'to_email'    => $to,
                'subject'     => $subject,
                'headers'     => $headers,
                'body_hash'   => $body_hash,
                'status'      => $status,
                'provider_msg'=> $provider_msg,
                'timestamp'   => current_time('mysql'),
            ],
            ['%s','%s','%s','%s','%s','%s','%s']
        );
    }
}
