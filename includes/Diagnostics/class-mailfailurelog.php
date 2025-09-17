<?php
/**
 * Mail failure log repository.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Diagnostics;

use Exception;
use function array_slice;
use function array_values;
use function bin2hex;
use function count;
use function delete_option;
use function get_option;
use function is_array;
use function is_numeric;
use function is_string;
use function max;
use function random_bytes;
use function pack;
use function wp_rand;
use function strlen;
use function str_repeat;
use function strpos;
use function strtolower;
use function time;
use function trim;
use function update_option;
use function usort;

/**
 * Persists recent mail failures for diagnostics display.
 */
final class MailFailureLog {
	public const CONTEXT_REGISTRATION       = 'registration';
	public const CONTEXT_ADMIN_RESEND       = 'admin-resend';
	public const CONTEXT_DIAGNOSTICS_RESEND = 'diagnostics-resend';

	public const ERROR_MAIL   = 'mail';
	public const ERROR_TOKEN  = 'token';
	public const ERROR_MEMBER = 'member';

	private const OPTION_KEY          = 'fbm_mail_failures';
	private const MAX_ENTRIES         = 25;
	private const RATE_LIMIT_INTERVAL = 900; // 15 minutes.

		/**
		 * Record a welcome email failure for later review.
		 *
		 * @param int    $member_id        Member identifier.
		 * @param string $member_reference Canonical member reference string.
		 * @param string $email            Recipient email address.
		 * @param string $context          Failure context key.
		 * @param string $error            Error descriptor key.
		 */
	public function record_failure( int $member_id, string $member_reference, string $email, string $context, string $error ): void {
			$entries = $this->load();
			$now     = time();

			$existing_key = null;

		foreach ( $entries as $index => $entry ) {
			if ( isset( $entry['member_id'] ) && (int) $entry['member_id'] === $member_id ) {
				$existing_key = $index;
				break;
			}
		}

		if ( null !== $existing_key ) {
				$entries[ $existing_key ]['recorded_at']      = $now;
				$entries[ $existing_key ]['context']          = $context;
				$entries[ $existing_key ]['error']            = $error;
				$entries[ $existing_key ]['member_reference'] = $member_reference;
				$entries[ $existing_key ]['email']            = self::redact_email( $email );
		} else {
				$entries[] = array(
					'id'               => $this->generate_id(),
					'member_id'        => $member_id,
					'member_reference' => $member_reference,
					'email'            => self::redact_email( $email ),
					'context'          => $context,
					'error'            => $error,
					'recorded_at'      => $now,
					'last_attempt_at'  => null,
					'attempts'         => 0,
				);
		}

			$entries = $this->sort_and_trim( $entries );

			$this->persist( $entries );
	}

		/**
		 * Retrieve a recorded entry by identifier.
		 *
		 * @param string $entry_id Entry identifier.
		 *
		 * @return array<string, mixed>|null
		 */
	public function find( string $entry_id ): ?array {
			$entries = $this->load();

		foreach ( $entries as $entry ) {
			if ( isset( $entry['id'] ) && (string) $entry['id'] === $entry_id ) {
				return $entry;
			}
		}

			return null;
	}

		/**
		 * Retrieve all recorded failures.
		 *
		 * @return array<int, array<string, mixed>>
		 */
	public function entries(): array {
			return $this->sort_and_trim( $this->load() );
	}

		/**
		 * Update bookkeeping for a resend attempt.
		 *
		 * @param string $entry_id Entry identifier.
		 */
	public function note_attempt( string $entry_id ): void {
			$entries = $this->load();
			$changed = false;
			$now     = time();

		foreach ( $entries as &$entry ) {
			if ( isset( $entry['id'] ) && (string) $entry['id'] === $entry_id ) {
				$attempts                 = isset( $entry['attempts'] ) && is_numeric( $entry['attempts'] ) ? (int) $entry['attempts'] : 0;
				$entry['attempts']        = $attempts + 1;
				$entry['last_attempt_at'] = $now;
				$changed                  = true;
				break;
			}
		}

			unset( $entry );

		if ( $changed ) {
				$this->persist( $entries );
		}
	}

		/**
		 * Refresh failure metadata when a resend attempt fails again.
		 *
		 * @param string $entry_id Entry identifier.
		 * @param string $error    Error descriptor key.
		 * @param string $context  Failure context key.
		 */
	public function note_failure( string $entry_id, string $error, string $context ): void {
			$entries = $this->load();
			$changed = false;
			$now     = time();

		foreach ( $entries as &$entry ) {
			if ( isset( $entry['id'] ) && (string) $entry['id'] === $entry_id ) {
				$entry['recorded_at'] = $now;
				$entry['error']       = $error;
				$entry['context']     = $context;
				$changed              = true;
				break;
			}
		}

			unset( $entry );

		if ( $changed ) {
				$entries = $this->sort_and_trim( $entries );
				$this->persist( $entries );
		}
	}

		/**
		 * Remove an entry once the resend succeeds or becomes irrelevant.
		 *
		 * @param string $entry_id Entry identifier.
		 */
	public function resolve( string $entry_id ): void {
			$entries = $this->load();
			$updated = array();

		foreach ( $entries as $entry ) {
			if ( ! isset( $entry['id'] ) || (string) $entry['id'] !== $entry_id ) {
				$updated[] = $entry;
			}
		}

			$this->persist( $updated );
	}

		/**
		 * Remove all entries associated with a member identifier.
		 *
		 * @param int $member_id Member identifier.
		 */
	public function resolve_member( int $member_id ): void {
		$entries = $this->load();
		$updated = array();

		foreach ( $entries as $entry ) {
			if ( ! isset( $entry['member_id'] ) || (int) $entry['member_id'] !== $member_id ) {
				$updated[] = $entry;
			}
		}

		$this->persist( $updated );
	}

		/**
		 * Determine whether a resend attempt is currently permitted.
		 *
		 * @param array<string, mixed> $entry Failure entry record.
		 */
	public function can_attempt( array $entry ): bool {
			$last_attempt = isset( $entry['last_attempt_at'] ) && is_numeric( $entry['last_attempt_at'] ) ? (int) $entry['last_attempt_at'] : 0;

		if ( $last_attempt <= 0 ) {
				return true;
		}

			return ( time() - $last_attempt ) >= self::RATE_LIMIT_INTERVAL;
	}

		/**
		 * Compute the timestamp when the next attempt becomes available.
		 *
		 * @param array<string, mixed> $entry Failure entry record.
		 */
	public function next_attempt_at( array $entry ): ?int {
			$last_attempt = isset( $entry['last_attempt_at'] ) && is_numeric( $entry['last_attempt_at'] ) ? (int) $entry['last_attempt_at'] : 0;

		if ( $last_attempt <= 0 ) {
				return null;
		}

			return $last_attempt + self::RATE_LIMIT_INTERVAL;
	}

		/**
		 * Redact an email address for safe diagnostics display.
		 *
		 * @param string $email Email address to redact.
		 */
	public static function redact_email( string $email ): string {
		$email = trim( strtolower( $email ) );

		if ( '' === $email || false === strpos( $email, '@' ) ) {
				return '';
		}

		list( $local, $domain ) = explode( '@', $email, 2 );
		$local_length           = strlen( $local );

		if ( $local_length <= 1 ) {
				$masked = '*';
		} elseif ( 2 === $local_length ) {
				$masked = $local[0] . '*';
		} else {
				$masked = $local[0] . str_repeat( '*', max( 1, $local_length - 2 ) ) . $local[ $local_length - 1 ];
		}

		return $masked . '@' . $domain;
	}

		/**
		 * Return the resend rate-limit interval in seconds.
		 */
	public static function rate_limit_interval(): int {
			return self::RATE_LIMIT_INTERVAL;
	}

		/**
		 * Load persisted entries.
		 *
		 * @return array<int, array<string, mixed>>
		 */
	private function load(): array {
			$raw = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $raw ) ) {
				return array();
		}

			$sanitized = array();

		foreach ( $raw as $entry ) {
			if ( ! is_array( $entry ) ) {
					continue;
			}

				$sanitized[] = array(
					'id'               => isset( $entry['id'] ) ? (string) $entry['id'] : $this->generate_id(),
					'member_id'        => isset( $entry['member_id'] ) ? (int) $entry['member_id'] : 0,
					'member_reference' => isset( $entry['member_reference'] ) ? (string) $entry['member_reference'] : '',
					'email'            => isset( $entry['email'] ) ? (string) $entry['email'] : '',
					'context'          => isset( $entry['context'] ) ? (string) $entry['context'] : '',
					'error'            => isset( $entry['error'] ) ? (string) $entry['error'] : '',
					'recorded_at'      => isset( $entry['recorded_at'] ) && is_numeric( $entry['recorded_at'] ) ? (int) $entry['recorded_at'] : time(),
					'last_attempt_at'  => isset( $entry['last_attempt_at'] ) && is_numeric( $entry['last_attempt_at'] ) ? (int) $entry['last_attempt_at'] : null,
					'attempts'         => isset( $entry['attempts'] ) && is_numeric( $entry['attempts'] ) ? (int) $entry['attempts'] : 0,
				);
		}

			return $this->sort_and_trim( $sanitized );
	}

		/**
		 * Persist entries to the option store.
		 *
		 * @param array<int, array<string, mixed>> $entries Failure entries.
		 */
	private function persist( array $entries ): void {
			$entries = $this->sort_and_trim( $entries );

		if ( count( $entries ) > 0 ) {
				update_option( self::OPTION_KEY, array_values( $entries ), false );
				return;
		}

			delete_option( self::OPTION_KEY );
	}

		/**
		 * Sort entries by newest first and clamp to the maximum retention size.
		 *
		 * @param array<int, array<string, mixed>> $entries Failure entries.
		 *
		 * @return array<int, array<string, mixed>>
		 */
	private function sort_and_trim( array $entries ): array {
			usort(
				$entries,
				static function ( array $a, array $b ): int {
							$time_a = isset( $a['recorded_at'] ) && is_numeric( $a['recorded_at'] ) ? (int) $a['recorded_at'] : 0;
							$time_b = isset( $b['recorded_at'] ) && is_numeric( $b['recorded_at'] ) ? (int) $b['recorded_at'] : 0;

							return $time_b <=> $time_a;
				}
			);

			return array_slice( $entries, 0, self::MAX_ENTRIES );
	}

		/**
		 * Generate a unique identifier for a failure record.
		 */
	private function generate_id(): string {
		try {
				return bin2hex( random_bytes( 8 ) );
		} catch ( Exception $exception ) {
				unset( $exception );

						return bin2hex( pack( 'Nn', time(), wp_rand( 0, 0xFFFF ) ) );
		}
	}
}
