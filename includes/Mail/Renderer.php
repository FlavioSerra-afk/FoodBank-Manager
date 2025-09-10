<?php
/**
 * Email renderer.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Mail;

use function wp_kses_post;
use function sanitize_text_field;
use function esc_html;
use function preg_replace_callback;
use function wp_mail;
use function sanitize_email;

/**
 * Render and send email templates.
 */
class Renderer {
    /**
     * Render template with tokens.
     *
     * @param array<string,mixed> $template Template data.
     * @param array<string,string> $data Token data.
     * @return array{subject:string,body:string}
     */
    public static function render( array $template, array $data ): array {
        $subject = self::replace_tokens( (string) ( $template['subject'] ?? '' ), $data, false );
        $subject = sanitize_text_field( $subject );
        $body    = self::replace_tokens( (string) ( $template['body'] ?? '' ), $data, true );
        $body    = wp_kses_post( $body );
        return array(
            'subject' => $subject,
            'body'    => $body,
        );
    }

    /**
     * Replace tokens.
     *
     * @param string               $text Text.
     * @param array<string,string> $data Data.
     * @param bool                 $html Escape for HTML?
     * @return string
     */
    private static function replace_tokens( string $text, array $data, bool $html ): string {
        return (string) preg_replace_callback(
            '/{{([a-zA-Z0-9_]+)}}/',
            static function ( array $m ) use ( $data, $html ): string {
                $key = $m[1];
                if ( ! array_key_exists( $key, $data ) ) {
                    return $m[0];
                }
                $val = (string) $data[ $key ];
                return $html ? esc_html( $val ) : $val;
            },
            $text
        );
    }

    /**
     * Send email.
     *
     * @param array<string,mixed>  $template Template.
     * @param array<string,string> $data Data.
     * @param array<int,string>    $to Recipients.
     * @return bool
     */
    public static function send( array $template, array $data, array $to ): bool {
        $rendered = self::render( $template, $data );
        $to       = array_filter( array_map( 'sanitize_email', $to ) );
        $headers  = array( 'Content-Type: text/html; charset=UTF-8' );
        return (bool) wp_mail( $to, $rendered['subject'], $rendered['body'], $headers );
    }
}
