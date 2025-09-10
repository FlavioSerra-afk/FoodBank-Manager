<?php
/**
 * Email template repository.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Mail;

use function get_option;
use function update_option;
use function register_setting;
use function sanitize_key;
use function sanitize_text_field;
use function sanitize_email;
use function is_email;
use function wp_kses_post;
use function is_array;

/**
 * Store email templates in options.
 */
class TemplateRepo {
    private const OPTION = 'fbm_email_templates';

    /**
     * Register setting.
     *
     * @return void
     */
    public static function register_setting(): void {
        if ( function_exists( 'register_setting' ) ) {
            register_setting(
                'fbm',
                self::OPTION,
                array(
                    'type' => 'array',
                    'sanitize_callback' => array( self::class, 'sanitize_all' ),
                    'default' => array(),
                )
            );
        }
    }

    /**
     * Get all templates.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function all(): array {
        $templates = get_option( self::OPTION, array() );
        if ( ! is_array( $templates ) ) {
            return array();
        }
        $out = array();
        foreach ( $templates as $slug => $tpl ) {
            $slug       = sanitize_key( (string) $slug );
            $out[ $slug ] = self::sanitize_template( $tpl );
        }
        return $out;
    }

    /**
     * Get a template.
     *
     * @param string $slug Template slug.
     * @return array<string,mixed>
     */
    public static function get( string $slug ): array {
        $all = self::all();
        return $all[ sanitize_key( $slug ) ] ?? array();
    }

    /**
     * Save a template.
     *
     * @param string               $slug Template slug.
     * @param array<string,mixed>  $data Template data.
     * @return void
     */
    public static function save( string $slug, array $data ): void {
        $all             = self::all();
        $slug            = sanitize_key( $slug );
        $all[ $slug ]    = self::sanitize_template( $data );
        update_option( self::OPTION, $all );
    }

    /**
     * Delete template.
     *
     * @param string $slug Template slug.
     * @return void
     */
    public static function delete( string $slug ): void {
        $all = self::all();
        $slug = sanitize_key( $slug );
        unset( $all[ $slug ] );
        update_option( self::OPTION, $all );
    }

    /**
     * Sanitize entire array of templates.
     *
     * @param mixed $templates Templates.
     * @return array<string,array<string,mixed>>
     */
    public static function sanitize_all( $templates ): array {
        if ( ! is_array( $templates ) ) {
            return array();
        }
        $out = array();
        foreach ( $templates as $slug => $tpl ) {
            $slug       = sanitize_key( (string) $slug );
            $out[ $slug ] = self::sanitize_template( $tpl );
        }
        return $out;
    }

    /**
     * Sanitize a template structure.
     *
     * @param mixed $tpl Template.
     * @return array<string,mixed>
     */
    private static function sanitize_template( $tpl ): array {
        if ( ! is_array( $tpl ) ) {
            $tpl = array();
        }
        $subject = isset( $tpl['subject'] ) ? sanitize_text_field( (string) $tpl['subject'] ) : '';
        if ( mb_strlen( $subject ) > 255 ) {
            $subject = mb_substr( $subject, 0, 255 );
        }
        $body = isset( $tpl['body'] ) ? wp_kses_post( (string) $tpl['body'] ) : '';
        if ( mb_strlen( $body ) > 65536 ) {
            $body = mb_substr( $body, 0, 65536 );
        }
        $recipients = array( 'to' => array(), 'cc' => array(), 'bcc' => array() );
        foreach ( array( 'to', 'cc', 'bcc' ) as $field ) {
            if ( ! empty( $tpl[ $field ] ) && is_array( $tpl[ $field ] ) ) {
                foreach ( $tpl[ $field ] as $email ) {
                    $email = sanitize_email( (string) $email );
                    if ( $email && is_email( $email ) ) {
                        $recipients[ $field ][] = $email;
                    }
                }
            }
        }
        $enabled = ! empty( $tpl['enabled'] );
        return array(
            'subject' => $subject,
            'body'    => $body,
            'to'      => $recipients['to'],
            'cc'      => $recipients['cc'],
            'bcc'     => $recipients['bcc'],
            'enabled' => $enabled,
        );
    }
}
