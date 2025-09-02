<?php
/**
 * Email template rendering.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Mail;

/**
 * Email template renderer.
 */
class Templates {
	private const OPTION_KEY = 'fbm_email_templates';

	/**
	 * Default templates if none saved.
	 *
	 * @return array<string,array{subject:string,body:string}>
	 */
	private static function defaults(): array {
		return array(
			'applicant_confirmation' => array(
				'subject' => 'We received your application — Ref {{reference}}',
				'body'    => '<p>Hi {{first_name}},</p>'
						. '<p>We received your application. Reference: {{reference}}</p>'
						. '{{summary_table}}',
			),
			'admin_notification'     => array(
				'subject' => 'New application received (Ref {{reference}})',
				'body'    => '<p>New application from {{first_name}} {{last_name}}</p>'
						. '{{summary_table}}'
						. '<p><a href="{{application_link}}">View entry</a></p>',
			),
		);
	}

	/**
	 * Get templates merged with defaults.
	 *
	 * @return array<string,array{subject:string,body:string}>
	 */
	public static function getAll(): array {
		$saved = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return array_replace_recursive( self::defaults(), $saved );
	}

	/**
	 * Save templates.
	 *
	 * @param array<string,array{subject:string,body:string}> $new_templates Templates.
	 * @return bool
	 */
	public static function saveAll( array $new_templates ): bool {
			$templates = self::getAll();
		foreach ( $new_templates as $key => $tpl ) {
			$key = sanitize_key( $key );
			if ( ! isset( $templates[ $key ] ) ) {
				continue;
			}
			$subject           = sanitize_text_field( $tpl['subject'] );
			$body              = wp_kses_post( $tpl['body'] );
			$templates[ $key ] = array(
				'subject' => $subject,
				'body'    => $body,
			);
		}
			return update_option( self::OPTION_KEY, $templates );
	}

	/**
	 * Render template with variables.
	 *
	 * @param string               $templateKey Template key.
	 * @param array<string,string> $vars Variables.
	 * @return array{subject:string,body_html:string}
	 */
	public static function render( string $templateKey, array $vars ): array {
		$templates = self::getAll();
		$tpl       = $templates[ $templateKey ] ?? array(
			'subject' => '',
			'body'    => '',
		);
		$subject   = self::replace( $tpl['subject'], $vars );
		$body      = self::replace( $tpl['body'], $vars );
		return array(
			'subject'   => $subject,
			'body_html' => $body,
		);
	}

	/**
	 * Token replacement helper.
	 *
	 * @param string               $text Raw text.
	 * @param array<string,string> $vars Tokens.
	 * @return string
	 */
	private static function replace( string $text, array $vars ): string {
		foreach ( $vars as $k => $v ) {
			$text = str_replace( '{{' . $k . '}}', (string) $v, $text );
		}
		return $text;
	}
}
