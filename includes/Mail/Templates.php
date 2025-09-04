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
	private const TEMPLATE_IDS = array(
		'applicant_confirmation',
		'admin_notification',
	);

	private const TOKEN_WHITELIST = array(
		'first_name',
		'last_name',
		'application_id',
		'site_name',
		'appointment_time',
	);

		/**
		 * Get allowed token placeholders.
		 *
		 * @return string[]
		 */
	public static function tokens(): array {
			return self::TOKEN_WHITELIST;
	}

	/**
	 * Default templates.
	 *
	 * @return array<string,array{subject:string,body:string}>
	 */
	public static function defaults(): array {
		return array(
			'applicant_confirmation' => array(
				'subject' => 'We received your application — Ref {application_id}',
				'body'    => '<p>Hi {first_name},</p>'
					. '<p>Your application was received. Reference: {application_id}.</p>'
					. '<p>Appointment: {appointment_time}</p>'
					. '<p>Regards, {site_name}</p>',
			),
			'admin_notification'     => array(
				'subject' => 'New application received (Ref {application_id})',
				'body'    => '<p>New application from {first_name} {last_name}.</p>'
					. '<p>Appointment: {appointment_time}</p>',
			),
		);
	}

		/**
		 * Back-compat alias for defaults.
		 *
		 * @return array<string,array{subject:string,body:string}>
		 */
	public static function getAll(): array { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
			return self::defaults();
	}

		/**
		 * Back-compat stub; saving not yet implemented.
		 *
		 * @param array<string,array{subject:string,body:string}> $new_templates Templates.
		 * @return bool
		 */
	public static function saveAll( array $new_templates ): bool { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
			unset( $new_templates );
			return false;
	}

	/**
	 * Back-compat render wrapper.
	 *
	 * @param string               $id   Template ID.
	 * @param array<string,string> $vars Variables.
	 * @return array{subject:string,body_html:string}
	 */
	public static function render( string $id, array $vars ): array {
		return array(
			'subject'   => self::render_subject( $id, $vars ),
			'body_html' => self::render_body( $id, $vars ),
		);
	}

	/**
	 * Render subject.
	 *
	 * @param string               $id   Template ID.
	 * @param array<string,string> $vars Variables.
	 * @return string
	 */
	public static function render_subject( string $id, array $vars ): string {
		if ( ! in_array( $id, self::TEMPLATE_IDS, true ) ) {
			return '';
		}
		$templates = self::defaults();
		$subject   = $templates[ $id ]['subject'] ?? '';

		$subject = self::replace_tokens( $subject, $vars, false );
		$subject = wp_strip_all_tags( $subject );
		if ( strlen( $subject ) > 255 ) {
			$subject = mb_substr( $subject, 0, 255 );
		}
		return $subject;
	}

	/**
	 * Render body HTML.
	 *
	 * @param string               $id   Template ID.
	 * @param array<string,string> $vars Variables.
	 * @return string
	 */
	public static function render_body( string $id, array $vars ): string {
		if ( ! in_array( $id, self::TEMPLATE_IDS, true ) ) {
			return '';
		}
		$templates = self::defaults();
		$body      = $templates[ $id ]['body'] ?? '';

		$body = self::replace_tokens( $body, $vars, true );
		return wp_kses_post( $body );
	}

	/**
	 * Replace tokens.
	 *
	 * @param string               $text   Text with tokens.
	 * @param array<string,string> $vars   Variables.
	 * @param bool                 $escape Whether to escape values.
	 * @return string
	 */
	private static function replace_tokens( string $text, array $vars, bool $escape ): string {
		foreach ( self::TOKEN_WHITELIST as $token ) {
			if ( ! array_key_exists( $token, $vars ) ) {
				continue;
			}
			$value = $escape ? esc_html( (string) $vars[ $token ] ) : (string) $vars[ $token ];
			$text  = str_replace( '{' . $token . '}', $value, $text );
		}
		return $text;
	}
}
