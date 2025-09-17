<?php
/**
 * Schedule helper.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Core;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use function array_key_exists;
use function constant;
use function defined;
use function get_option;
use function is_array;
use function is_int;
use function is_numeric;
use function is_string;
use function strtolower;
use function trim;

/**
 * Resolve the current attendance schedule window.
 */
final class Schedule {
	private const DEFAULT_DAY      = 'thursday';
	private const DEFAULT_START    = '11:00';
	private const DEFAULT_END      = '14:30';
	private const DEFAULT_TIMEZONE = 'Europe/London';

	/**
	 * Lookup table for canonical day names.
	 *
	 * @var array<string,string>
	 */
	private const DAY_ALIASES = array(
		'monday'    => 'monday',
		'mon'       => 'monday',
		'tuesday'   => 'tuesday',
		'tue'       => 'tuesday',
		'tues'      => 'tuesday',
		'wednesday' => 'wednesday',
		'wed'       => 'wednesday',
		'thursday'  => 'thursday',
		'thu'       => 'thursday',
		'thur'      => 'thursday',
		'thurs'     => 'thursday',
		'friday'    => 'friday',
		'fri'       => 'friday',
		'saturday'  => 'saturday',
		'sat'       => 'saturday',
		'sunday'    => 'sunday',
		'sun'       => 'sunday',
	);

	/**
	 * Lookup table for numeric day representations.
	 *
	 * @var array<int|string,string>
	 */
	private const DAY_NUMERIC = array(
		'1' => 'monday',
		'2' => 'tuesday',
		'3' => 'wednesday',
		'4' => 'thursday',
		'5' => 'friday',
		'6' => 'saturday',
		'7' => 'sunday',
	);

	/**
	 * Numeric index for canonical day names.
	 *
	 * @var array<string,int>
	 */
	private const DAY_INDICES = array(
		'monday'    => 1,
		'tuesday'   => 2,
		'wednesday' => 3,
		'thursday'  => 4,
		'friday'    => 5,
		'saturday'  => 6,
		'sunday'    => 7,
	);

	/**
	 * Override for deterministic testing.
	 *
	 * @var array{day:string,start:string,end:string,timezone:string}|null
	 */
	private static ?array $window_override = null;

	/**
	 * Return the current schedule window.
	 *
	 * @return array{day:string,start:string,end:string,timezone:string}
	 */
	public function current_window(): array {
		if ( is_array( self::$window_override ) ) {
			return self::$window_override;
		}

		$defaults = self::default_window();
		$option   = get_option( 'fbm_schedule_window' );

		if ( ! is_array( $option ) ) {
			return $defaults;
		}

		return self::normalize_window( $option, $defaults );
	}

	/**
	 * Override the current window for deterministic testing.
	 *
	 * @param array{day:string,start:string,end:string,timezone:string}|null $window Override window.
	 */
	public static function set_current_window_override( ?array $window ): void {
		if ( null === $window ) {
			self::$window_override = null;

			return;
		}

		self::$window_override = self::normalize_window( $window, self::default_window() );
	}

	/**
	 * Resolve the numeric index for a canonical day name.
	 *
	 * @param string $day Raw day value.
	 */
	public static function day_to_index( string $day ): int {
		$canonical = self::canonical_day( $day );

		if ( null === $canonical ) {
			$canonical = self::default_day();
		}

		return self::DAY_INDICES[ $canonical ] ?? self::DAY_INDICES[ self::DEFAULT_DAY ];
	}

	/**
	 * Compose the default window from constants or sensible fallbacks.
	 *
	 * @return array{day:string,start:string,end:string,timezone:string}
	 */
	private static function default_window(): array {
		return array(
			'day'      => self::default_day(),
			'start'    => self::default_start(),
			'end'      => self::default_end(),
			'timezone' => self::default_timezone(),
		);
	}

	/**
	 * Determine the default day.
	 */
	private static function default_day(): string {
		$day = self::DEFAULT_DAY;

		if ( defined( 'FBM_SCHEDULE_DAY' ) ) {
			$day = (string) constant( 'FBM_SCHEDULE_DAY' );
		}

		$canonical = self::canonical_day( $day );

		return $canonical ?? self::DEFAULT_DAY;
	}

	/**
	 * Determine the default start time.
	 */
	private static function default_start(): string {
		$start = self::DEFAULT_START;

		if ( defined( 'FBM_SCHEDULE_START' ) ) {
			$start = (string) constant( 'FBM_SCHEDULE_START' );
		}

		return self::normalize_time( $start, self::DEFAULT_START );
	}

	/**
	 * Determine the default end time.
	 */
	private static function default_end(): string {
		$end = self::DEFAULT_END;

		if ( defined( 'FBM_SCHEDULE_END' ) ) {
			$end = (string) constant( 'FBM_SCHEDULE_END' );
		}

		return self::normalize_time( $end, self::DEFAULT_END );
	}

	/**
	 * Determine the default timezone.
	 */
	private static function default_timezone(): string {
		$timezone = self::DEFAULT_TIMEZONE;

		if ( defined( 'FBM_SCHEDULE_TIMEZONE' ) ) {
			$timezone = (string) constant( 'FBM_SCHEDULE_TIMEZONE' );
		} elseif ( defined( 'FBM_SCHEDULE_TZ' ) ) {
			$timezone = (string) constant( 'FBM_SCHEDULE_TZ' );
		}

		return self::normalize_timezone( $timezone, self::DEFAULT_TIMEZONE );
	}

	/**
	 * Normalize a window configuration.
	 *
	 * @param array{day?:mixed,start?:mixed,end?:mixed,timezone?:mixed} $window Window configuration.
	 * @param array{day:string,start:string,end:string,timezone:string} $defaults Default configuration.
	 *
	 * @return array{day:string,start:string,end:string,timezone:string}
	 */
	private static function normalize_window( array $window, array $defaults ): array {
		$day      = array_key_exists( 'day', $window ) ? self::normalize_day( $window['day'], $defaults['day'] ) : $defaults['day'];
		$start    = array_key_exists( 'start', $window ) ? self::normalize_time( $window['start'], $defaults['start'] ) : $defaults['start'];
		$end      = array_key_exists( 'end', $window ) ? self::normalize_time( $window['end'], $defaults['end'] ) : $defaults['end'];
		$timezone = array_key_exists( 'timezone', $window ) ? self::normalize_timezone( $window['timezone'], $defaults['timezone'] ) : $defaults['timezone'];

		return array(
			'day'      => $day,
			'start'    => $start,
			'end'      => $end,
			'timezone' => $timezone,
		);
	}

	/**
	 * Normalize the provided day to a canonical value.
	 *
	 * @param mixed  $day      Provided day value.
	 * @param string $fallback Fallback day.
	 */
	private static function normalize_day( $day, string $fallback ): string {
		if ( is_string( $day ) || is_int( $day ) ) {
			$canonical = self::canonical_day( (string) $day );

			if ( null !== $canonical ) {
				return $canonical;
			}
		}

		return $fallback;
	}

	/**
	 * Convert a string representation of a day to its canonical form.
	 *
	 * @param string $day Raw day value.
	 */
	private static function canonical_day( string $day ): ?string {
		$day = strtolower( trim( $day ) );

		if ( '' === $day ) {
			return null;
		}

		if ( isset( self::DAY_ALIASES[ $day ] ) ) {
			return self::DAY_ALIASES[ $day ];
		}

		if ( is_numeric( $day ) ) {
			$numeric = (string) (int) $day;

			if ( isset( self::DAY_NUMERIC[ $numeric ] ) ) {
				return self::DAY_NUMERIC[ $numeric ];
			}
		}

		return null;
	}

	/**
	 * Normalize a time value to 24-hour HH:MM format.
	 *
	 * @param mixed  $time     Provided time value.
	 * @param string $fallback Fallback time.
	 */
	private static function normalize_time( $time, string $fallback ): string {
		if ( is_string( $time ) || is_int( $time ) ) {
			$time = trim( (string) $time );

			if ( '' !== $time ) {
				$timezone = new DateTimeZone( 'UTC' );
				$formats  = array( '!H:i', '!H:i:s' );

				foreach ( $formats as $format ) {
					$date = DateTimeImmutable::createFromFormat( $format, $time, $timezone );

					if ( $date instanceof DateTimeImmutable ) {
						return $date->format( 'H:i' );
					}
				}
			}
		}

		return $fallback;
	}

	/**
	 * Normalize a timezone identifier.
	 *
	 * @param mixed  $timezone Provided timezone value.
	 * @param string $fallback Fallback timezone.
	 */
	private static function normalize_timezone( $timezone, string $fallback ): string {
		if ( is_string( $timezone ) ) {
			$timezone = trim( $timezone );

			if ( '' !== $timezone ) {
				try {
					$tz = new DateTimeZone( $timezone );

					return $tz->getName();
				} catch ( Exception $exception ) {
					return $fallback;
				}
			}
		}

		return $fallback;
	}
}
