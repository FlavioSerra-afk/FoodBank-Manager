<?php // phpcs:ignoreFile
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

if ( ! defined( 'FBM_PATH' ) ) {
        define( 'FBM_PATH', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'FBM_FILE' ) ) {
        define( 'FBM_FILE', FBM_PATH . 'foodbank-manager.php' );
}

spl_autoload_register(
        static function ( string $fqcn ): void {
                if ( strpos( $fqcn, 'FoodBankManager\\' ) !== 0 ) {
                        return;
                }

                $relative = substr( $fqcn, strlen( 'FoodBankManager\\' ) );
                $path     = str_replace( '\\', '/', $relative );
                $standard = FBM_PATH . 'includes/' . $path . '.php';

                if ( is_readable( $standard ) ) {
                        require_once $standard;

                        return;
                }

                $segments       = explode( '/', $path );
                $class          = array_pop( $segments );
                $dir            = FBM_PATH . 'includes/' . ( $segments ? implode( '/', $segments ) . '/' : '' );
                $pattern_hyphen = strtolower( preg_replace( '/([a-z0-9])([A-Z])/', '$1-$2', $class ) );
                $wp_hyphen      = $dir . 'class-' . $pattern_hyphen . '.php';

                if ( is_readable( $wp_hyphen ) ) {
                        require_once $wp_hyphen;

                        return;
                }

                $pattern_compact = strtolower( $class );
                $wp_compact      = $dir . 'class-' . $pattern_compact . '.php';

                if ( is_readable( $wp_compact ) ) {
                        require_once $wp_compact;
                }
        }
);

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

if ( ! defined( 'FBM_TESTING' ) ) {
                define( 'FBM_TESTING', true );
}

if ( ! defined( 'AUTH_KEY' ) ) {
        define( 'AUTH_KEY', 'fbm-test-auth-key' );
}

if ( ! defined( 'SECURE_AUTH_KEY' ) ) {
        define( 'SECURE_AUTH_KEY', 'fbm-test-secure-key' );
}

if ( ! function_exists( 'site_url' ) ) {
        function site_url(): string {
                return 'https://example.test';
        }
}

if ( ! isset( $GLOBALS['fbm_current_caps'] ) ) {
		$GLOBALS['fbm_current_caps'] = array(
			'fbm_checkin' => true,
			'fbm_manage'  => false,
		);
}

if ( ! class_exists( 'wpdb', false ) ) {
	/**
	 * Minimal wpdb stand-in for unit tests.
	 */
	class wpdb {
			/**
			 * WordPress table prefix.
			 *
			 * @var string
			 */
		public string $prefix = 'wp_';

			/**
			 * Captured token rows keyed by member ID.
			 *
			 * @var array<int,array{member_id:int,token_hash:string,issued_at:string,version:string,revoked_at:?string}>
			 */
		public array $tokens = array();

			/**
			 * Captured member rows keyed by member ID.
			 *
			 * @var array<int,array<string,mixed>>
			 */
		public array $members = array();

		/**
		 * Captured attendance rows keyed by record ID.
		 *
		 * @var array<int,array<string,mixed>>
		 */
		public array $attendance = array();

			/**
			 * Captured attendance override audit rows keyed by record ID.
			 *
			 * @var array<int,array<string,mixed>>
			 */
		public array $attendance_overrides = array();

			/**
			 * Name of the options table.
			 *
			 * @var string
			 */
		public string $options = 'wp_options';

			/**
			 * Captured raw SQL queries executed via query().
			 *
			 * @var array<int,string>
			 */
		public array $queries = array();

			/**
			 * Last auto-increment identifier.
			 *
			 * @var int
			 */
		public int $insert_id = 0;

			/**
			 * Arguments and query from the most recent prepare() call.
			 *
			 * @var array{query:string,args:array<int|string,mixed>}|null
			 */
		private ?array $last_prepare = null;

			/**
			 * Next identifier for inserted members.
			 *
			 * @var int
			 */
		private int $next_member_id = 1;

		/**
		 * Next identifier for attendance records.
		 *
		 * @var int
		 */
		private int $next_attendance_id = 1;

		/**
		 * Next identifier for attendance override audit rows.
		 *
		 * @var int
		 */
		private int $next_override_id = 1;

		/**
		 * Locate a member record by reference.
		 *
		 * @param string $reference Member reference string.
		 *
		 * @return array<string,mixed>|null
		 */
		private function find_member_by_reference( string $reference ): ?array {
			foreach ( $this->members as $member ) {
				if ( ! isset( $member['member_reference'] ) ) {
					continue;
				}

				if ( (string) $member['member_reference'] === $reference ) {
					return $member;
				}
			}

			return null;
		}

		/**
		 * Normalize a stored member row into the format returned by wpdb.
		 *
		 * @param array<string,mixed> $member Member payload.
		 *
		 * @return array<string,mixed>
		 */
                private function normalize_member_row( array $member ): array {
                        return array(
                                'id'                  => (int) ( $member['id'] ?? 0 ),
                                'member_reference'    => (string) ( $member['member_reference'] ?? '' ),
                                'first_name'          => (string) ( $member['first_name'] ?? '' ),
                                'last_initial'        => (string) ( $member['last_initial'] ?? '' ),
                                'email'               => (string) ( $member['email'] ?? '' ),
                                'status'              => (string) ( $member['status'] ?? '' ),
                                'household_size'      => (int) ( $member['household_size'] ?? 0 ),
                                'created_at'          => $member['created_at'] ?? null,
                                'updated_at'          => $member['updated_at'] ?? null,
                                'activated_at'        => $member['activated_at'] ?? null,
                                'consent_recorded_at' => $member['consent_recorded_at'] ?? null,
                        );
                }

                /**
                 * Determine whether a stored value is an encryption envelope.
                 */
                private function is_envelope_value( string $value ): bool {
                        return str_starts_with( $value, '{"v":"1","alg":"AES-256-GCM"' );
                }

		public function get_charset_collate(): string {
				return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci';
		}

		public function replace( string $table, array $data, array $format ) {
				unset( $format );
				unset( $table );

				$member_id                  = (int) $data['member_id'];
				$this->tokens[ $member_id ] = array(
					'member_id'  => $member_id,
					'token_hash' => (string) $data['token_hash'],
					'issued_at'  => (string) $data['issued_at'],
					'version'    => (string) ( $data['version'] ?? 'v1' ),
					'revoked_at' => null,
					'meta'       => isset( $data['meta'] ) ? (string) $data['meta'] : '{}',
				);

				return 1;
		}

		public function prepare( string $query, ...$args ) {
			if ( 1 === count( $args ) && is_array( $args[0] ) ) {
					$args = array_values( $args[0] );
			}

				$this->last_prepare = array(
					'query' => $query,
					'args'  => $args,
				);

				return $query;
		}

		public function get_last_prepare(): ?array {
				return $this->last_prepare;
		}

		public function insert( string $table, array $data, array $format ) {
				unset( $format );

			if ( str_contains( $table, 'fbm_attendance_overrides' ) ) {
					$record_id                                = $this->next_override_id++;
					$data['id']                               = $record_id;
					$this->attendance_overrides[ $record_id ] = $data;

					return 1;
			}

			if ( str_contains( $table, 'fbm_attendance' ) ) {
					$record_id                      = $this->next_attendance_id++;
					$data['id']                     = $record_id;
					$this->attendance[ $record_id ] = $data;
					$this->insert_id                = $record_id;

					return 1;
			}

				unset( $table );

				$member_id                   = $this->next_member_id++;
				$data['id']                  = $member_id;
				$this->members[ $member_id ] = $data;
				$this->insert_id             = $member_id;

				return 1;
		}

		public function get_row( string $query, $output = ARRAY_A ) {
				unset( $query );
				unset( $output );

			if ( ! is_array( $this->last_prepare ) ) {
					return null;
			}

                        $sql  = $this->last_prepare['query'];
                        $args = $this->last_prepare['args'];

                        if (
                                str_contains( $sql, 'fbm_members' )
                                && str_contains( $sql, 'SUM(CASE WHEN first_name LIKE %s AND last_initial LIKE %s' )
                        ) {
                                $total     = count( $this->members );
                                $encrypted = 0;

                                foreach ( $this->members as $member ) {
                                        $first = (string) ( $member['first_name'] ?? '' );
                                        $last  = (string) ( $member['last_initial'] ?? '' );

                                        if ( $this->is_envelope_value( $first ) && $this->is_envelope_value( $last ) ) {
                                                ++$encrypted;
                                        }
                                }

                                return array(
                                        'total'     => $total,
                                        'encrypted' => $encrypted,
                                );
                        }

			if (
						str_contains( $sql, 'fbm_tokens' )
						&& str_contains( $sql, 'member_id = %d' )
				) {
					$member_id = (int) ( $args[0] ?? 0 );

				if ( isset( $this->tokens[ $member_id ] ) ) {
						$record = $this->tokens[ $member_id ];

						$result = array(
							'member_id'  => $record['member_id'],
							'token_hash' => $record['token_hash'],
							'version'    => $record['version'] ?? 'v1',
							'issued_at'  => $record['issued_at'] ?? gmdate( 'Y-m-d H:i:s' ),
							'meta'       => $record['meta'] ?? '{}',
						);

						if ( array_key_exists( 'revoked_at', $record ) ) {
								$result['revoked_at'] = $record['revoked_at'];
						}

						return $result;
				}

					return null;
			}

                        if ( str_contains( $sql, 'token_hash = %s' ) && str_contains( $sql, 'fbm_tokens' ) ) {
                                        $hash            = (string) ( $args[0] ?? '' );
                                        $GLOBALS['fbm_last_token_lookup_hash'] = $hash;
					$include_revoked = str_contains( $sql, 'revoked_at' );

				foreach ( $this->tokens as $record ) {
					if ( $record['token_hash'] !== $hash ) {
						continue;
					}

					if ( ! $include_revoked && null !== $record['revoked_at'] ) {
								continue;
					}

							$result = array(
								'member_id'  => $record['member_id'],
								'token_hash' => $record['token_hash'],
								'version'    => $record['version'] ?? 'v1',
								'meta'       => $record['meta'] ?? '{}',
							);

							if ( $include_revoked ) {
									$result['revoked_at'] = $record['revoked_at'];
							}

							return $result;
				}

						return null;
			}

			if ( str_contains( $sql, 'fbm_members' ) && str_contains( $sql, 'WHERE email = %s' ) ) {
					$email = (string) ( $args[0] ?? '' );

				foreach ( $this->members as $member ) {
					if ( $member['email'] === $email ) {
						return $this->normalize_member_row( $member );
					}
				}

					return null;
			}

			if ( str_contains( $sql, 'fbm_members' ) && str_contains( $sql, 'WHERE member_reference = %s' ) ) {
					$reference = (string) ( $args[0] ?? '' );

				foreach ( $this->members as $member ) {
					if ( $member['member_reference'] === $reference ) {
						return $this->normalize_member_row( $member );
					}
				}

					return null;
			}

			if ( str_contains( $sql, 'fbm_members' ) && str_contains( $sql, 'WHERE id = %d' ) ) {
					$member_id = (int) ( $args[0] ?? 0 );

				if ( isset( $this->members[ $member_id ] ) ) {
						return $this->normalize_member_row( $this->members[ $member_id ] );
				}

					return null;
			}

				return null;
		}

		public function get_var( string $query ) {
				unset( $query );

			if ( ! is_array( $this->last_prepare ) ) {
					return null;
			}

				$sql  = $this->last_prepare['query'];
				$args = $this->last_prepare['args'];

			if ( str_contains( $sql, 'ORDER BY collected_at DESC' ) && str_contains( $sql, 'fbm_attendance' ) ) {
					$reference = (string) ( $args[0] ?? '' );

					$latest = null;

				foreach ( $this->attendance as $record ) {
					if ( $record['member_reference'] !== $reference ) {
						continue;
					}

					if ( null === $latest || $record['collected_at'] > $latest ) {
							$latest = $record['collected_at'];
					}
				}

					return $latest;
			}

                        if (
                                str_contains( $sql, 'fbm_members' )
                                && str_contains( $sql, 'first_name NOT LIKE %s OR last_initial NOT LIKE %s' )
                        ) {
                                $cursor = (int) ( $args[0] ?? 0 );

                                foreach ( $this->members as $member ) {
                                        $member_id = (int) ( $member['id'] ?? 0 );

                                        if ( $member_id <= $cursor ) {
                                                continue;
                                        }

                                        $first = (string) ( $member['first_name'] ?? '' );
                                        $last  = (string) ( $member['last_initial'] ?? '' );

                                        if ( ! $this->is_envelope_value( $first ) || ! $this->is_envelope_value( $last ) ) {
                                                return $member_id;
                                        }
                                }

                                return null;
                        }

                        if (
                                str_contains( $sql, 'fbm_members' )
                                && str_contains( $sql, 'first_name LIKE %s AND last_initial LIKE %s' )
                        ) {
                                $cursor = (int) ( $args[0] ?? 0 );

                                foreach ( $this->members as $member ) {
                                        $member_id = (int) ( $member['id'] ?? 0 );

                                        if ( $member_id <= $cursor ) {
                                                continue;
                                        }

                                        $first = (string) ( $member['first_name'] ?? '' );
                                        $last  = (string) ( $member['last_initial'] ?? '' );

                                        if ( $this->is_envelope_value( $first ) && $this->is_envelope_value( $last ) ) {
                                                return $member_id;
                                        }
                                }

                                return null;
                        }

                        if (
                                                str_contains( $sql, 'fbm_attendance' )
                                                && str_contains( $sql, 'member_reference = %s' )
                                                && str_contains( $sql, 'collected_date = %s' )
                                ) {
					$reference = (string) ( $args[0] ?? '' );
					$date      = (string) ( $args[1] ?? '' );

				foreach ( $this->attendance as $record ) {
					if (
								$record['member_reference'] === $reference
								&& $record['collected_date'] === $date
						) {
							return $record['id'];
					}
				}

					return null;
			}

			if (
						str_contains( $sql, 'COUNT(*) AS total' )
						&& str_contains( $sql, 'fbm_attendance' )
				) {
					$active_status  = (string) ( $args[0] ?? 'active' );
					$revoked_status = (string) ( $args[1] ?? 'revoked' );
					$start          = (string) ( $args[2] ?? '' );
					$end            = (string) ( $args[3] ?? '' );

					$total   = 0;
					$active  = 0;
					$revoked = 0;

				foreach ( $this->attendance as $record ) {
						$collected_date = (string) ( $record['collected_date'] ?? '' );

					if ( '' !== $start && $collected_date < $start ) {
						continue;
					}

					if ( '' !== $end && $collected_date > $end ) {
							continue;
					}

						++$total;

						$member = $this->find_member_by_reference( (string) $record['member_reference'] );
						$status = isset( $member['status'] ) ? (string) $member['status'] : '';

					if ( $status === $active_status ) {
							++$active;
							continue;
					}

					if ( $status === $revoked_status ) {
							++$revoked;
					}
				}

					return array(
						'total'         => $total,
						'active_total'  => $active,
						'revoked_total' => $revoked,
					);
			}

			if ( str_contains( $sql, 'fbm_members' ) && str_contains( $sql, 'member_reference = %s' ) ) {
					$reference = (string) ( $args[0] ?? '' );

				foreach ( $this->members as $member ) {
					if ( $member['member_reference'] === $reference ) {
						return $member['id'];
					}
				}
			}

			if (
						str_contains( $sql, 'SELECT COUNT(*)' )
						&& str_contains( $sql, 'fbm_attendance' )
						&& ! str_contains( $sql, 'member_reference = %s' )
				) {
					$start    = (string) ( $args[0] ?? '' );
					$end      = (string) ( $args[1] ?? '' );
					$statuses = array();

				if ( count( $args ) > 2 ) {
						$statuses = array_map( 'strval', array_slice( $args, 2 ) );
				}

					return count( $this->collect_attendance_rows( $start, $end, $statuses ) );
			}

				return null;
		}

		public function get_results( string $query, $output = ARRAY_A ) {
				unset( $query );
				unset( $output );

			if ( ! is_array( $this->last_prepare ) ) {
					return array();
			}

                        $sql  = $this->last_prepare['query'];
                        $args = $this->last_prepare['args'];

                       if (
                               str_contains( $sql, 'fbm_members' )
                               && str_contains( $sql, 'WHERE id > %d' )
                               && str_contains( $sql, 'first_name NOT LIKE %s OR last_initial NOT LIKE %s' )
                       ) {
                                $cursor = (int) ( $args[0] ?? 0 );
                                $limit  = (int) ( $args[3] ?? 0 );
                                $rows   = array();

                                foreach ( $this->members as $member ) {
                                        $member_id = (int) ( $member['id'] ?? 0 );

                                        if ( $member_id <= $cursor ) {
                                                continue;
                                        }

                                        $first = (string) ( $member['first_name'] ?? '' );
                                        $last  = (string) ( $member['last_initial'] ?? '' );

                                        if ( $this->is_envelope_value( $first ) && $this->is_envelope_value( $last ) ) {
                                                continue;
                                        }

                                        $rows[] = array(
                                                'id'           => $member_id,
                                                'first_name'   => $first,
                                                'last_initial' => $last,
                                        );

                                        if ( $limit > 0 && count( $rows ) >= $limit ) {
                                                break;
                                        }
                                }

                                usort(
                                        $rows,
                                        static function ( array $a, array $b ): int {
                                                return ( $a['id'] ?? 0 ) <=> ( $b['id'] ?? 0 );
                                        }
                                );

                                return $rows;
                        }

                       if (
                               str_contains( $sql, 'fbm_members' )
                               && str_contains( $sql, 'WHERE id > %d' )
                               && str_contains( $sql, 'first_name LIKE %s AND last_initial LIKE %s' )
                       ) {
                                $cursor = (int) ( $args[0] ?? 0 );
                                $limit  = (int) ( $args[3] ?? 0 );
                                $rows   = array();

                                foreach ( $this->members as $member ) {
                                        $member_id = (int) ( $member['id'] ?? 0 );

                                        if ( $member_id <= $cursor ) {
                                                continue;
                                        }

                                        $first = (string) ( $member['first_name'] ?? '' );
                                        $last  = (string) ( $member['last_initial'] ?? '' );

                                        if ( ! $this->is_envelope_value( $first ) || ! $this->is_envelope_value( $last ) ) {
                                                continue;
                                        }

                                        $rows[] = array(
                                                'id'           => $member_id,
                                                'first_name'   => $first,
                                                'last_initial' => $last,
                                        );

                                        if ( $limit > 0 && count( $rows ) >= $limit ) {
                                                break;
                                        }
                                }

                                usort(
                                        $rows,
                                        static function ( array $a, array $b ): int {
                                                return ( $a['id'] ?? 0 ) <=> ( $b['id'] ?? 0 );
                                        }
                                );

                                return $rows;
                        }

                       if (
                               str_contains( $sql, 'fbm_attendance' )
                               && ! str_contains( $sql, 'fbm_attendance_overrides' )
                               && str_contains( $sql, 'WHERE member_reference = %s' )
                       ) {
				$reference = (string) ( $args[0] ?? '' );
				$rows      = array();

				foreach ( $this->attendance as $record ) {
					if ( (string) ( $record['member_reference'] ?? '' ) !== $reference ) {
							continue;
					}

					$rows[] = array(
						'collected_date' => $record['collected_date'] ?? '',
						'collected_at'   => $record['collected_at'] ?? '',
						'method'         => $record['method'] ?? '',
						'note'           => $record['note'] ?? '',
						'recorded_by'    => $record['recorded_by'] ?? null,
					);
				}

				usort(
					$rows,
					static function ( array $a, array $b ): int {
						return strcmp( (string) ( $a['collected_at'] ?? '' ), (string) ( $b['collected_at'] ?? '' ) );
					}
				);

				return $rows;
			}

                       if (
                               str_contains( $sql, 'FROM `' )
                               && str_contains( $sql, 'fbm_attendance' )
                               && ! str_contains( $sql, 'fbm_attendance_overrides' )
                               && str_contains( $sql, 'ORDER BY' )
                       ) {
				$start = (string) ( $args[0] ?? '' );
				$end   = (string) ( $args[1] ?? '' );
				$total = count( $args );

					$limit        = $total >= 4 ? (int) $args[ $total - 2 ] : 0;
					$offset       = $total >= 4 ? (int) $args[ $total - 1 ] : 0;
					$status_count = max( 0, $total - 4 );
					$statuses     = array();

				if ( $status_count > 0 ) {
						$statuses = array_map( 'strval', array_slice( $args, 2, $status_count ) );
				}

					$rows = $this->collect_attendance_rows( $start, $end, $statuses );

				if ( $offset > 0 ) {
						$rows = array_slice( $rows, $offset );
				}

				if ( $limit > 0 ) {
						$rows = array_slice( $rows, 0, $limit );
				}

					return $rows;
			}

			if ( str_contains( $sql, 'fbm_attendance_overrides' ) && str_contains( $sql, 'WHERE member_reference = %s' ) ) {
				$reference = (string) ( $args[0] ?? '' );
				$rows      = array();

				foreach ( $this->attendance_overrides as $record ) {
					if ( (string) ( $record['member_reference'] ?? '' ) !== $reference ) {
						continue;
					}

						$rows[] = array(
							'attendance_id' => $record['attendance_id'] ?? 0,
							'override_by'   => $record['override_by'] ?? 0,
							'override_note' => $record['override_note'] ?? '',
							'override_at'   => $record['override_at'] ?? '',
						);
				}

					usort(
						$rows,
						static function ( array $a, array $b ): int {
									return strcmp( (string) ( $a['override_at'] ?? '' ), (string) ( $b['override_at'] ?? '' ) );
						}
					);

								return $rows;
			}

				return array();
		}

		public function get_col( string $query, int $column_offset = 0 ) {
				unset( $query );
				unset( $column_offset );

			if ( ! is_array( $this->last_prepare ) ) {
					return array();
			}

				$sql      = $this->last_prepare['query'];
				$args     = $this->last_prepare['args'];
				$pattern  = (string) ( $args[0] ?? '' );
				$registry = array();

			if ( str_contains( $sql, 'option_name' ) && str_contains( $sql, 'LIKE %s' ) ) {
					$prefix = rtrim( $pattern, '%' );

					$options = $GLOBALS['fbm_options'] ?? array();

				foreach ( array_keys( $options ) as $name ) {
					if ( is_string( $name ) && str_starts_with( $name, $prefix ) ) {
						$registry[] = $name;
					}
				}

					return $registry;
			}

				return array();
		}

			/**
			 * Filter and normalize attendance rows for the provided range and statuses.
			 *
			 * @param string            $start    Inclusive start date (Y-m-d).
			 * @param string            $end      Inclusive end date (Y-m-d).
			 * @param array<int,string> $statuses Optional statuses to include.
			 *
			 * @return array<int,array<string,mixed>>
			 */
		private function collect_attendance_rows( string $start, string $end, array $statuses = array() ): array {
				$rows = array();

			foreach ( $this->attendance as $record ) {
					$collected_date = (string) ( $record['collected_date'] ?? '' );

				if ( '' !== $start && $collected_date < $start ) {
					continue;
				}

				if ( '' !== $end && $collected_date > $end ) {
						continue;
				}

					$member = $this->find_member_by_reference( (string) $record['member_reference'] );
					$status = isset( $member['status'] ) ? (string) $member['status'] : '';

				if ( ! empty( $statuses ) && ! in_array( $status, $statuses, true ) ) {
						continue;
				}

					$rows[] = array(
						'member_reference' => (string) $record['member_reference'],
						'collected_at'     => (string) $record['collected_at'],
						'collected_date'   => $collected_date,
						'method'           => (string) $record['method'],
						'note'             => $record['note'] ?? null,
						'recorded_by'      => $record['recorded_by'] ?? null,
						'status'           => $status,
					);
			}

				usort(
					$rows,
					static function ( array $a, array $b ): int {
								return strcmp( (string) ( $a['collected_at'] ?? '' ), (string) ( $b['collected_at'] ?? '' ) );
					}
				);

				return $rows;
		}

		public function query( string $query ) {
				$this->queries[] = $query;

			if ( str_contains( $query, 'fbm_members' ) && str_contains( $query, 'activated_at = NULL' ) ) {
				$member_id = null;

				if ( preg_match( '/WHERE id = (\d+)/', $query, $matches ) ) {
					$member_id = (int) $matches[1];
				} elseif ( is_array( $this->last_prepare ) && isset( $this->last_prepare['args'][0] ) ) {
					$member_id = (int) $this->last_prepare['args'][0];
				}

				if ( null !== $member_id && isset( $this->members[ $member_id ] ) ) {
					$this->members[ $member_id ]['activated_at']        = null;
					$this->members[ $member_id ]['consent_recorded_at'] = null;
				}
			}

				return 0;
		}

		public function update( string $table, array $data, array $where, array $format, array $where_format ) {
				unset( $format );
				unset( $where_format );

			if ( str_contains( $table, 'fbm_tokens' ) && isset( $where['member_id'] ) ) {
					$member_id = (int) $where['member_id'];

				if ( ! isset( $this->tokens[ $member_id ] ) ) {
					return 0;
				}

					$this->tokens[ $member_id ]['revoked_at'] = $data['revoked_at'] ?? null;

					return 1;
			}

			if ( str_contains( $table, 'fbm_members' ) && isset( $where['id'] ) ) {
					$member_id = (int) $where['id'];

				if ( ! isset( $this->members[ $member_id ] ) ) {
						return 0;
				}

					$this->members[ $member_id ] = array_merge( $this->members[ $member_id ], $data );

					return 1;
			}

			if ( str_contains( $table, 'fbm_attendance_overrides' ) && isset( $where['member_reference'] ) ) {
					$reference = (string) $where['member_reference'];
					$updated   = 0;

				foreach ( $this->attendance_overrides as &$record ) {
					if ( (string) ( $record['member_reference'] ?? '' ) !== $reference ) {
						continue;
					}

						$record = array_merge( $record, $data );
						++$updated;
				}

					unset( $record );

					return $updated;
			}

			if ( str_contains( $table, 'fbm_attendance' ) && isset( $where['member_reference'] ) ) {
					$reference = (string) $where['member_reference'];
					$updated   = 0;

				foreach ( $this->attendance as &$record ) {
					if ( (string) ( $record['member_reference'] ?? '' ) !== $reference ) {
						continue;
					}

						$record = array_merge( $record, $data );
						++$updated;
				}

					unset( $record );

					return $updated;
			}

				return 0;
		}

		public function delete( string $table, array $where, array $where_format = array() ) {
				unset( $where_format );

			if ( str_contains( $table, 'fbm_tokens' ) && isset( $where['member_id'] ) ) {
					$member_id = (int) $where['member_id'];

				if ( isset( $this->tokens[ $member_id ] ) ) {
					unset( $this->tokens[ $member_id ] );

					return 1;
				}

					return 0;
			}

			if ( str_contains( $table, 'fbm_attendance' ) && isset( $where['id'] ) ) {
					$attendance_id = (int) $where['id'];

				if ( isset( $this->attendance[ $attendance_id ] ) ) {
						unset( $this->attendance[ $attendance_id ] );

						return 1;
				}

					return 0;
			}

			if ( str_contains( $table, 'fbm_attendance_overrides' ) && isset( $where['id'] ) ) {
					$override_id = (int) $where['id'];

				if ( isset( $this->attendance_overrides[ $override_id ] ) ) {
						unset( $this->attendance_overrides[ $override_id ] );

						return 1;
				}

					return 0;
			}

				return 0;
		}
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
		/**
		 * Minimal WP_Error implementation for unit tests.
		 */
	class WP_Error {
		private string $code;
		private string $message;
			private $data;

		public function __construct( string $code = '', string $message = '', $data = array() ) {
				$this->code    = $code;
				$this->message = $message;
				$this->data    = $data;
		}

		public function get_error_code(): string {
				return $this->code;
		}

		public function get_error_message(): string {
				return $this->message;
		}

		public function get_error_data( string $code = '' ) {
				unset( $code );

				return $this->data;
		}
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ): bool {
			return $thing instanceof WP_Error;
	}
}

if ( ! class_exists( 'WP_Role' ) ) {
		/**
		 * Minimal WP_Role stand-in for capability assignment tests.
		 */
	class WP_Role {
		public string $name;

			/**
			 * @var array<string,bool>
			 */
		public array $capabilities;

		public string $display_name;

		public function __construct( string $role, array $capabilities = array(), string $display_name = '' ) {
				$this->name         = $role;
				$this->capabilities = $capabilities;
				$this->display_name = $display_name;
		}

		public function add_cap( string $capability, bool $grant = true ): void {
			if ( $grant ) {
					$this->capabilities[ $capability ] = true;
			} else {
					unset( $this->capabilities[ $capability ] );
			}

			if ( isset( $GLOBALS['fbm_roles'][ $this->name ] ) ) {
					$GLOBALS['fbm_roles'][ $this->name ] = $this;
			}
		}
	}
}

if ( ! class_exists( 'WP_User' ) ) {
		/**
		 * Minimal WP_User stand-in for registration flows.
		 */
	class WP_User {
		public int $ID = 0;

			/**
			 * @var array<int,string>
			 */
		public array $roles = array();

		public string $user_email   = '';
		public string $user_login   = '';
		public string $first_name   = '';
		public string $last_name    = '';
		public string $display_name = '';

		public function __construct( $id = 0, array $data = array() ) {
			if ( is_numeric( $id ) && (int) $id > 0 ) {
				$record = $GLOBALS['fbm_users'][ (int) $id ] ?? null;
				if ( is_array( $record ) ) {
						$this->hydrate( $record );

						return;
				}

				$this->ID = (int) $id;
			}

			if ( ! empty( $data ) ) {
					$this->hydrate( $data );
			}
		}

			/**
			 * Populate the user object from the shared store.
			 *
			 * @param array<string,mixed> $data User row data.
			 */
		private function hydrate( array $data ): void {
				$this->ID           = (int) ( $data['ID'] ?? $this->ID );
				$this->user_email   = (string) ( $data['user_email'] ?? '' );
				$this->user_login   = (string) ( $data['user_login'] ?? '' );
				$this->first_name   = (string) ( $data['first_name'] ?? '' );
				$this->last_name    = (string) ( $data['last_name'] ?? '' );
				$this->display_name = (string) ( $data['display_name'] ?? '' );

				$roles = $data['roles'] ?? array();
			if ( is_string( $roles ) ) {
					$roles = array( $roles );
			}

				$this->roles = array_values( array_unique( array_map( 'strval', (array) $roles ) ) );
		}

		public function add_role( string $role ): void {
			if ( '' === $role ) {
					return;
			}

			if ( ! in_array( $role, $this->roles, true ) ) {
					$this->roles[] = $role;
			}

			if ( ! isset( $GLOBALS['fbm_users'][ $this->ID ] ) || ! is_array( $GLOBALS['fbm_users'][ $this->ID ] ) ) {
					$GLOBALS['fbm_users'][ $this->ID ] = array();
			}

				$GLOBALS['fbm_users'][ $this->ID ]['roles'] = $this->roles;
		}
	}
}

if ( ! function_exists( 'add_role' ) ) {
	function add_role( string $role, string $display_name, array $capabilities = array() ) {
		if ( ! isset( $GLOBALS['fbm_roles'] ) || ! is_array( $GLOBALS['fbm_roles'] ) ) {
				$GLOBALS['fbm_roles'] = array();
		}

			$role_object                   = new WP_Role( $role, $capabilities, $display_name );
			$GLOBALS['fbm_roles'][ $role ] = $role_object;

			return $role_object;
	}
}

if ( ! function_exists( 'get_role' ) ) {
	function get_role( string $role ) {
			$roles = $GLOBALS['fbm_roles'] ?? array();

			return $roles[ $role ] ?? null;
	}
}

if ( ! function_exists( 'get_user_by' ) ) {
	function get_user_by( string $field, $value ) {
			$users = $GLOBALS['fbm_users'] ?? array();

		switch ( strtolower( $field ) ) {
			case 'id':
				$user_id = (int) $value;
				if ( $user_id > 0 && isset( $users[ $user_id ] ) ) {
						return new WP_User( $user_id, $users[ $user_id ] );
				}

				return false;
			case 'email':
					$email = strtolower( (string) $value );

				foreach ( $users as $id => $user ) {
					$stored = strtolower( (string) ( $user['user_email'] ?? '' ) );
					if ( '' !== $stored && $stored === $email ) {
							return new WP_User( (int) $id, $user );
					}
				}

				return false;
			case 'login':
					$login = (string) $value;

				foreach ( $users as $id => $user ) {
					if ( (string) ( $user['user_login'] ?? '' ) === $login ) {
						return new WP_User( (int) $id, $user );
					}
				}

				return false;
			default:
				return false;
		}
	}
}

if ( ! function_exists( 'wp_insert_user' ) ) {
	function wp_insert_user( array $userdata ) {
		if ( ! isset( $GLOBALS['fbm_users'] ) || ! is_array( $GLOBALS['fbm_users'] ) ) {
				$GLOBALS['fbm_users'] = array();
		}

		if ( ! isset( $GLOBALS['fbm_next_user_id'] ) ) {
				$GLOBALS['fbm_next_user_id'] = 1;
		}

			$user = array_merge(
				array(
					'user_login'   => '',
					'user_pass'    => '',
					'user_email'   => '',
					'first_name'   => '',
					'last_name'    => '',
					'display_name' => '',
					'roles'        => array(),
					'role'         => '',
				),
				$userdata
			);

		if ( '' === $user['user_login'] ) {
			$user['user_login'] = $user['user_email'] ?: 'fbm_user_' . (string) $GLOBALS['fbm_next_user_id'];
		}

			$user['roles'] = array_values( array_filter( array_unique( array_map( 'strval', (array) $user['roles'] ) ) ) );

		if ( '' !== (string) $user['role'] ) {
				$user['roles'][] = (string) $user['role'];
				unset( $user['role'] );
		}

			$id = isset( $user['ID'] ) ? (int) $user['ID'] : 0;

		if ( $id > 0 && isset( $GLOBALS['fbm_users'][ $id ] ) ) {
				$user['ID']                  = $id;
				$GLOBALS['fbm_users'][ $id ] = array_merge( $GLOBALS['fbm_users'][ $id ], $user );

				return $id;
		}

			$id                          = (int) $GLOBALS['fbm_next_user_id'];
			$user['ID']                  = $id;
			$GLOBALS['fbm_users'][ $id ] = $user;
			++$GLOBALS['fbm_next_user_id'];

			return $id;
	}
}

if ( ! class_exists( 'WP_REST_Response' ) ) {
		/**
		 * Minimal WP_REST_Response stand-in.
		 */
	class WP_REST_Response {
			private $data;
		private int $status = 200;

		public function __construct( $data = null ) {
				$this->data = $data;
		}

		public function get_data() {
				return $this->data;
		}

		public function set_status( int $status ): void {
				$this->status = $status;
		}

		public function get_status(): int {
				return $this->status;
		}
	}
}

if ( ! class_exists( 'WP_REST_Request' ) ) {
		/**
		 * Minimal WP_REST_Request for controller tests.
		 */
	class WP_REST_Request {
		private array $params;
		private array $headers;

		public function __construct( array $params = array(), array $headers = array() ) {
				$this->params  = $params;
				$this->headers = array();

			foreach ( $headers as $name => $value ) {
				$this->headers[ strtolower( (string) $name ) ] = $value;
			}
		}

		public function get_param( string $key ) {
				return $this->params[ $key ] ?? null;
		}

		public function set_param( string $key, $value ): void {
				$this->params[ $key ] = $value;
		}

		public function get_header( string $key ) {
				$lookup = strtolower( $key );

				return $this->headers[ $lookup ] ?? '';
		}

		public function set_header( string $key, $value ): void {
				$this->headers[ strtolower( $key ) ] = $value;
		}
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( string $cap ): bool {
			$caps = $GLOBALS['fbm_current_caps'] ?? array();

			return (bool) ( $caps[ $cap ] ?? false );
	}
}

if ( ! function_exists( 'rest_ensure_response' ) ) {
	function rest_ensure_response( $response ) {
		if ( $response instanceof WP_REST_Response || $response instanceof WP_Error ) {
				return $response;
		}

			return new WP_REST_Response( $response );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $value ): string {
			$value = strtolower( (string) $value );

			return preg_replace( '/[^a-z0-9_]/', '', $value );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $value ): string {
			$value = (string) $value;
			$value = trim( $value );

			return preg_replace( '/[\r\n\t]+/', ' ', $value );
	}
}

if ( ! function_exists( 'sanitize_textarea_field' ) ) {
	function sanitize_textarea_field( $value ): string {
			$value = (string) $value;

			return trim( $value );
	}
}

if ( ! function_exists( 'sanitize_file_name' ) ) {
	function sanitize_file_name( $filename ): string {
			$filename = (string) $filename;
			$filename = trim( $filename );
			$filename = preg_replace( '/[^A-Za-z0-9\-_. ]+/', '', $filename );
			$filename = preg_replace( '/\s+/', '-', $filename );
			$filename = trim( (string) $filename, '-_.' );

			return strtolower( (string) $filename );
	}
}

if ( ! isset( $GLOBALS['fbm_upload_stub'] ) || ! is_array( $GLOBALS['fbm_upload_stub'] ) ) {
        $GLOBALS['fbm_upload_stub'] = array();
}

if ( ! function_exists( 'wp_check_filetype_and_ext' ) ) {
        function wp_check_filetype_and_ext( string $file, string $filename, array $mimes = array(), string $real_mime = '' ) {
                unset( $file );
                unset( $filename );
                unset( $real_mime );

                if ( isset( $GLOBALS['fbm_upload_stub']['filetype_result'] ) ) {
                        return (array) $GLOBALS['fbm_upload_stub']['filetype_result'];
                }

                if ( ! empty( $mimes ) ) {
                        $first_extension = array_key_first( $mimes );

                        if ( null !== $first_extension && isset( $mimes[ $first_extension ] ) ) {
                                return array(
                                        'type' => (string) $mimes[ $first_extension ],
                                        'ext'  => (string) $first_extension,
                                );
                        }
                }

                return array(
                        'type' => '',
                        'ext'  => '',
                );
        }
}

if ( ! function_exists( 'wp_handle_upload' ) ) {
        function wp_handle_upload( array $file, array $overrides = array() ) {
                $GLOBALS['fbm_upload_stub']['handle_upload_calls'][] = array(
                        'file'      => $file,
                        'overrides' => $overrides,
                );

                if ( isset( $GLOBALS['fbm_upload_stub']['handle_upload_result'] ) ) {
                        return $GLOBALS['fbm_upload_stub']['handle_upload_result'];
                }

                $name = isset( $file['name'] ) ? (string) $file['name'] : 'upload.bin';

                return array(
                        'file' => '/tmp/' . $name,
                        'url'  => 'https://example.test/uploads/' . $name,
                        'type' => isset( $file['type'] ) ? (string) $file['type'] : '',
                );
        }
}

if ( ! function_exists( 'wp_insert_attachment' ) ) {
        function wp_insert_attachment( array $attachment, string $file = '' ) {
                $GLOBALS['fbm_upload_stub']['insert_attachment_calls'][] = array(
                        'attachment' => $attachment,
                        'file'       => $file,
                );

                if ( isset( $GLOBALS['fbm_upload_stub']['insert_attachment_result'] ) ) {
                        return $GLOBALS['fbm_upload_stub']['insert_attachment_result'];
                }

                if ( ! isset( $GLOBALS['fbm_upload_stub']['next_attachment_id'] ) ) {
                        $GLOBALS['fbm_upload_stub']['next_attachment_id'] = 1000;
                }

                $next = (int) $GLOBALS['fbm_upload_stub']['next_attachment_id'];
                $GLOBALS['fbm_upload_stub']['next_attachment_id'] = $next + 1;

                return $next;
        }
}

if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
        function wp_generate_attachment_metadata( int $attachment_id, string $file ): array {
                $GLOBALS['fbm_upload_stub']['generate_metadata_calls'][] = array(
                        'attachment_id' => $attachment_id,
                        'file'          => $file,
                );

                return isset( $GLOBALS['fbm_upload_stub']['generate_metadata_result'] )
                        ? (array) $GLOBALS['fbm_upload_stub']['generate_metadata_result']
                        : array();
        }
}

if ( ! function_exists( 'wp_update_attachment_metadata' ) ) {
        function wp_update_attachment_metadata( int $attachment_id, array $metadata ): bool {
                $GLOBALS['fbm_upload_stub']['update_metadata_calls'][] = array(
                        'attachment_id' => $attachment_id,
                        'metadata'      => $metadata,
                );

                return true;
        }
}

if ( ! function_exists( 'wp_delete_attachment' ) ) {
        function wp_delete_attachment( int $attachment_id, bool $force_delete = false ): bool {
                $GLOBALS['fbm_upload_stub']['deleted_attachments'][] = array(
                        'attachment_id' => $attachment_id,
                        'force'         => $force_delete,
                );

                return true;
        }
}

if ( ! function_exists( 'wp_delete_file' ) ) {
        function wp_delete_file( string $file ): void {
                $GLOBALS['fbm_upload_stub']['deleted_files'][] = $file;
        }
}


if ( ! function_exists( 'sanitize_email' ) ) {
	function sanitize_email( $value ): string {
			$value = (string) $value;

			return filter_var( $value, FILTER_SANITIZE_EMAIL ) ?: '';
	}
}

if ( ! function_exists( 'is_email' ) ) {
	function is_email( $value ): bool {
			$value = (string) $value;

			return false !== filter_var( $value, FILTER_VALIDATE_EMAIL );
	}
}

if ( ! function_exists( 'wp_rand' ) ) {
	function wp_rand( int $min = 0, int $max = 0 ): int {
		if ( $max <= $min ) {
				return random_int( $min, $min );
		}

			return random_int( $min, $max );
	}
}

if ( ! function_exists( 'wp_generate_password' ) ) {
	function wp_generate_password( int $length = 12, bool $special_chars = true, bool $extra_special_chars = false ): string {
			unset( $special_chars, $extra_special_chars );

			$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			$password = '';
			$max      = strlen( $alphabet ) - 1;

		for ( $i = 0; $i < $length; $i++ ) {
				$password .= $alphabet[ random_int( 0, $max ) ];
		}

			return $password;
	}
}

if ( ! function_exists( 'get_current_user_id' ) ) {
	function get_current_user_id(): int {
			return 1;
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( string $name, $default = false ) {
			$options = $GLOBALS['fbm_options'] ?? array();

			return $options[ $name ] ?? $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( string $name, $value, $autoload = null ) {
			unset( $autoload );

		if ( ! isset( $GLOBALS['fbm_options'] ) || ! is_array( $GLOBALS['fbm_options'] ) ) {
				$GLOBALS['fbm_options'] = array();
		}

			$GLOBALS['fbm_options'][ $name ] = $value;

			return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
        function delete_option( string $name ): bool {
                if ( ! isset( $GLOBALS['fbm_deleted_options'] ) || ! is_array( $GLOBALS['fbm_deleted_options'] ) ) {
                                $GLOBALS['fbm_deleted_options'] = array();
                }

                if ( ! in_array( $name, $GLOBALS['fbm_deleted_options'], true ) ) {
                        $GLOBALS['fbm_deleted_options'][] = $name;
                }

                if ( isset( $GLOBALS['fbm_options'] ) && is_array( $GLOBALS['fbm_options'] ) ) {
                                unset( $GLOBALS['fbm_options'][ $name ] );
                }

                        return true;
        }
}

if ( ! function_exists( 'delete_site_option' ) ) {
        function delete_site_option( string $name ): bool {
                return delete_option( $name );
        }
}

if ( ! function_exists( 'get_transient' ) ) {
	function get_transient( string $name ) {
			$transients = $GLOBALS['fbm_transients'] ?? array();

		if ( ! isset( $transients[ $name ] ) ) {
				return false;
		}

			$record  = $transients[ $name ];
			$expires = isset( $record['expires'] ) ? (int) $record['expires'] : 0;

		if ( $expires > 0 && $expires < time() ) {
				unset( $GLOBALS['fbm_transients'][ $name ] );

				return false;
		}

			return $record['value'] ?? false;
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	function set_transient( string $name, $value, int $expiration = 0 ): bool {
		if ( ! isset( $GLOBALS['fbm_transients'] ) || ! is_array( $GLOBALS['fbm_transients'] ) ) {
				$GLOBALS['fbm_transients'] = array();
		}

			$expires = $expiration > 0 ? time() + $expiration : 0;

			$GLOBALS['fbm_transients'][ $name ] = array(
				'value'   => $value,
				'expires' => $expires,
			);

			return true;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	function delete_transient( string $name ): bool {
		if ( isset( $GLOBALS['fbm_transients'][ $name ] ) ) {
			unset( $GLOBALS['fbm_transients'][ $name ] );
		}

		return true;
	}
}

if ( ! function_exists( 'esc_sql' ) ) {
	function esc_sql( string $value ): string {
		return $value;
	}
}

if ( ! function_exists( '_get_cron_array' ) ) {
	function _get_cron_array(): array {
			return $GLOBALS['fbm_cron'] ?? array();
	}
}

if ( ! function_exists( 'wp_unschedule_hook' ) ) {
	function wp_unschedule_hook( string $hook ): void {
		if ( ! isset( $GLOBALS['fbm_unscheduled_hooks'] ) || ! is_array( $GLOBALS['fbm_unscheduled_hooks'] ) ) {
				$GLOBALS['fbm_unscheduled_hooks'] = array();
		}

			$GLOBALS['fbm_unscheduled_hooks'][] = $hook;

		if ( isset( $GLOBALS['fbm_cron'] ) && is_array( $GLOBALS['fbm_cron'] ) ) {
			foreach ( $GLOBALS['fbm_cron'] as &$events ) {
				if ( isset( $events[ $hook ] ) ) {
					unset( $events[ $hook ] );
				}
			}

				unset( $events );
		}
	}
}

if ( ! function_exists( 'delete_site_transient' ) ) {
	function delete_site_transient( string $name ): bool {
			return delete_transient( $name );
	}
}

require_once __DIR__ . '/../includes/Auth/class-capabilities.php';
require_once __DIR__ . '/../includes/Core/class-plugin.php';
require_once __DIR__ . '/../includes/Core/class-install.php';
require_once __DIR__ . '/../includes/Core/class-assets.php';
require_once __DIR__ . '/../includes/Core/class-cache.php';
require_once __DIR__ . '/../includes/Core/class-schedule.php';
require_once __DIR__ . '/../includes/Crypto/class-crypto.php';
require_once __DIR__ . '/../includes/Crypto/class-encryptionadapter.php';
require_once __DIR__ . '/../includes/Crypto/class-encryptionmanager.php';
require_once __DIR__ . '/../includes/Crypto/class-encryptionsettings.php';
require_once __DIR__ . '/../includes/Crypto/Adapters/class-memberspiiadapter.php';
require_once __DIR__ . '/../includes/Crypto/Adapters/class-mailfaillogadapter.php';
require_once __DIR__ . '/../includes/Attendance/class-attendancerepository.php';
require_once __DIR__ . '/../includes/Attendance/class-checkinservice.php';
require_once __DIR__ . '/../includes/Token/class-tokenrepository.php';
require_once __DIR__ . '/../includes/Token/class-tokenservice.php';
require_once __DIR__ . '/../includes/Registration/class-membersrepository.php';
require_once __DIR__ . '/../includes/Registration/class-registrationsettings.php';
require_once __DIR__ . '/../includes/Registration/class-registrationservice.php';
require_once __DIR__ . '/../includes/Email/class-welcomemailer.php';
require_once __DIR__ . '/../includes/Shortcodes/class-registrationform.php';
require_once __DIR__ . '/../includes/Shortcodes/class-staffdashboard.php';
require_once __DIR__ . '/../includes/Rest/class-checkincontroller.php';
require_once __DIR__ . '/../includes/Admin/class-memberspage.php';
require_once __DIR__ . '/../includes/Admin/class-reportspage.php';
require_once __DIR__ . '/../includes/Admin/class-schedulepage.php';
require_once __DIR__ . '/../includes/Admin/class-themepage.php';
require_once __DIR__ . '/../includes/Admin/class-settingspage.php';
require_once __DIR__ . '/../includes/Diagnostics/class-mailfailurelog.php';
require_once __DIR__ . '/../includes/Reports/class-reportsrepository.php';
require_once __DIR__ . '/../includes/Reports/class-summarybuilder.php';
require_once __DIR__ . '/../includes/Reports/class-csvexporter.php';
require_once __DIR__ . '/../includes/Privacy/class-privacy.php';
require_once __DIR__ . '/../includes/Privacy/class-exporter.php';
require_once __DIR__ . '/../includes/Privacy/class-eraser.php';

if ( ! function_exists( 'add_action' ) ) {
        function add_action( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
                $registry                         = $GLOBALS['fbm_actions'] ?? array();
                $registry[ $hook ][ $priority ][] = array(
                        'callback' => $callback,
                        'args'     => $accepted_args,
                );
                $GLOBALS['fbm_actions']           = $registry;
        }
}

if ( ! function_exists( 'add_filter' ) ) {
        function add_filter( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
                $registry                         = $GLOBALS['fbm_filters'] ?? array();
                $registry[ $hook ][ $priority ][] = array(
                        'callback' => $callback,
                        'args'     => $accepted_args,
                );
                $GLOBALS['fbm_filters']           = $registry;
        }
}

if ( ! function_exists( 'add_menu_page' ) ) {
        function add_menu_page(
                string $page_title,
                string $menu_title,
                string $capability,
                string $menu_slug,
                $callback = '',
                string $icon_url = '',
                $position = null
        ) {
                $GLOBALS['fbm_admin_menu'][] = array(
                        'page_title' => $page_title,
                        'menu_title' => $menu_title,
                        'capability' => $capability,
                        'menu_slug'  => $menu_slug,
                        'callback'   => $callback,
                        'icon_url'   => $icon_url,
                        'position'   => $position,
                );

                return $menu_slug;
        }
}

if ( ! function_exists( 'add_submenu_page' ) ) {
        function add_submenu_page(
                string $parent_slug,
                string $page_title,
                string $menu_title,
                string $capability,
                string $menu_slug,
                $callback = ''
        ) {
                if ( ! isset( $GLOBALS['fbm_admin_submenu'][ $parent_slug ] ) ) {
                        $GLOBALS['fbm_admin_submenu'][ $parent_slug ] = array();
                }

                $GLOBALS['fbm_admin_submenu'][ $parent_slug ][] = array(
                        'parent_slug' => $parent_slug,
                        'page_title'  => $page_title,
                        'menu_title'  => $menu_title,
                        'capability'  => $capability,
                        'menu_slug'   => $menu_slug,
                        'callback'    => $callback,
                );

                return $menu_slug;
        }
}

if ( ! function_exists( 'do_action' ) ) {
	function do_action( string $hook, ...$args ): void {
			unset( $hook );
			unset( $args );
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( string $hook, $value, ...$args ) {
			$registry = $GLOBALS['fbm_filters'][ $hook ] ?? array();

		if ( empty( $registry ) ) {
				return $value;
		}

			ksort( $registry );

		foreach ( $registry as $callbacks ) {
			foreach ( $callbacks as $callback ) {
					$callable      = $callback['callback'];
					$accepted_args = (int) $callback['args'];
					$params        = array_merge( array( $value ), $args );
					$arguments     = array_slice( $params, 0, max( 1, $accepted_args ) );

					$value = call_user_func_array( $callable, $arguments );
			}
		}

			return $value;
	}
}

if ( ! function_exists( '__' ) ) {
	function __( string $text, string $domain = '' ): string {
			unset( $domain );

			return $text;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( string $text, string $domain = '' ): string {
			unset( $domain );

			return $text;
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ): string {
			return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'number_format_i18n' ) ) {
	function number_format_i18n( $number, int $decimals = 0 ): string {
			return number_format( (float) $number, $decimals, '.', ',' );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ): string {
			return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
        function wp_kses_post( $data ) {
                        return strip_tags( (string) $data, '<a><abbr><acronym><b><blockquote><cite><code><del><em><i><q><s><strike><strong>' );
        }
}

if ( ! function_exists( 'wp_kses' ) ) {
        function wp_kses( $data, array $allowed_html = array() ) {
                $allowed = '';

                if ( ! empty( $allowed_html ) ) {
                        $allowed = '<' . implode( '><', array_keys( $allowed_html ) ) . '>';
                }

                return strip_tags( (string) $data, $allowed );
        }
}

if ( ! function_exists( 'esc_html_e' ) ) {
        function esc_html_e( string $text, string $domain = '' ): void {
                echo esc_html( __( $text, $domain ) );
        }
}

if ( ! function_exists( 'esc_attr_e' ) ) {
        function esc_attr_e( string $text, string $domain = '' ): void {
                echo esc_attr( __( $text, $domain ) );
        }
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ): string {
			$value = filter_var( (string) $url, FILTER_SANITIZE_URL );

			return $value ?: '';
	}
}

if ( ! function_exists( 'wp_nonce_field' ) ) {
	function wp_nonce_field( string $action, string $name, bool $referer = true, bool $echo = true ): string {
			unset( $referer );
			unset( $echo );

			return '<input type="hidden" name="' . $name . '" value="' . $action . '-nonce" />';
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		if ( is_array( $value ) ) {
				return array_map( 'wp_unslash', $value );
		}

			return stripslashes( (string) $value );
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data, int $options = 0, int $depth = 512 ) {
			return json_encode( $data, $options | JSON_UNESCAPED_SLASHES, $depth );
	}
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
	function wp_verify_nonce( $nonce, string $action ): bool {
			$nonces = $GLOBALS['fbm_test_nonces'] ?? array();

			return isset( $nonces[ $action ] ) && $nonces[ $action ] === $nonce;
	}
}

if ( ! function_exists( 'wp_die' ) ) {
	function wp_die( $message = '' ): void {
			throw new RuntimeException( (string) $message );
	}
}

if ( ! function_exists( 'check_admin_referer' ) ) {
	function check_admin_referer( string $action, string $query_arg = '_wpnonce' ) {
			$nonce = $_REQUEST[ $query_arg ] ?? '';

		if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_die( 'invalid_nonce' );
		}

			return true;
	}
}

if ( ! function_exists( 'admin_url' ) ) {
	function admin_url( string $path = '' ): string {
			$path = ltrim( $path, '/' );

			return 'https://example.org/wp-admin/' . $path;
	}
}

if ( ! function_exists( 'add_query_arg' ) ) {
	function add_query_arg( array $args, string $url ): string {
			$parts = parse_url( $url );
			$query = array();

		if ( isset( $parts['query'] ) ) {
				parse_str( (string) $parts['query'], $query );
		}

		foreach ( $args as $key => $value ) {
				$query[ $key ] = $value;
		}

			$parts['query'] = http_build_query( $query );

			$scheme   = $parts['scheme'] ?? 'https';
			$host     = $parts['host'] ?? 'example.org';
			$port     = isset( $parts['port'] ) ? ':' . $parts['port'] : '';
			$path     = $parts['path'] ?? '';
			$querystr = $parts['query'] ? '?' . $parts['query'] : '';

			return $scheme . '://' . $host . $port . $path . $querystr;
	}
}

if ( ! function_exists( 'wp_safe_redirect' ) ) {
        function wp_safe_redirect( string $location, int $status = 302 ): bool {
                        $GLOBALS['fbm_last_redirect'] = array(
                                'location' => $location,
                                'status'   => $status,
                        );

                        return true;
        }
}

if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
        function wp_add_privacy_policy_content( string $name, string $content ): void {
                if ( ! isset( $GLOBALS['fbm_privacy_policy'] ) || ! is_array( $GLOBALS['fbm_privacy_policy'] ) ) {
                        $GLOBALS['fbm_privacy_policy'] = array();
                }

                $GLOBALS['fbm_privacy_policy'][ $name ] = $content;
        }
}

if ( ! function_exists( 'wp_privacy_personal_data_erasers' ) ) {
        function wp_privacy_personal_data_erasers(): array {
                return $GLOBALS['fbm_privacy_erasers'] ?? array();
        }
}

if ( ! function_exists( 'wp_privacy_process_personal_data_erasure' ) ) {
        function wp_privacy_process_personal_data_erasure( string $identifier, array $erasers, int $page ): array {
                $items_removed  = false;
                $items_retained = false;
                $messages       = array();
                $done           = true;

                foreach ( $erasers as $eraser ) {
                        if ( ! is_array( $eraser ) || empty( $eraser['callback'] ) || ! is_callable( $eraser['callback'] ) ) {
                                continue;
                        }

                        $response = call_user_func( $eraser['callback'], $identifier, $page );

                        if ( isset( $response['items_removed'] ) && $response['items_removed'] ) {
                                $items_removed = true;
                        }

                        if ( isset( $response['items_retained'] ) && $response['items_retained'] ) {
                                $items_retained = true;
                        }

                        if ( isset( $response['messages'] ) && is_array( $response['messages'] ) ) {
                                $messages = array_merge( $messages, $response['messages'] );
                        }

                        if ( isset( $response['done'] ) && false === $response['done'] ) {
                                $done = false;
                        }
                }

                return array(
                        'items_removed'  => $items_removed,
                        'items_retained' => $items_retained,
                        'messages'       => $messages,
                        'done'           => $done,
                );
        }
}

if ( ! function_exists( 'selected' ) ) {
        function selected( $selected, $current, bool $echo = true ) {
                        $result = (string) $selected === (string) $current ? ' selected="selected"' : '';

                if ( $echo ) {
                                echo $result;
                }

                        return $result;
        }
}

if ( ! function_exists( 'checked' ) ) {
        function checked( $checked, $current = true, bool $echo = true ) {
                $result = (bool) $checked === (bool) $current ? ' checked="checked"' : '';

                if ( $echo ) {
                        echo $result;
                }

                return $result;
        }
}

if ( ! function_exists( 'disabled' ) ) {
        function disabled( $disabled, $current = true, bool $echo = true ) {
                $result = (bool) $disabled === (bool) $current ? ' disabled="disabled"' : '';

                if ( $echo ) {
                        echo $result;
                }

                return $result;
        }
}

if ( ! function_exists( 'submit_button' ) ) {
        function submit_button( string $text = 'Submit', string $type = 'primary', string $name = 'submit', bool $wrap = true, array $other_atts = array() ) {
                unset( $wrap );

                $attributes = '';

                foreach ( $other_atts as $attr_key => $attr_value ) {
                        if ( false === $attr_value ) {
                                continue;
                        }

                        $attributes .= ' ' . $attr_key . '="' . $attr_value . '"';
                }

                $button = '<button type="submit" class="button button-' . $type . '" name="' . $name . '"' . $attributes . '>' . $text . '</button>';

                echo $button; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Test helper output.

                return $button;
        }
}

if ( ! function_exists( 'register_setting' ) ) {
	function register_setting( string $option_group, string $option_name, array $args = array() ): bool {
		if ( ! isset( $GLOBALS['fbm_registered_settings'] ) || ! is_array( $GLOBALS['fbm_registered_settings'] ) ) {
				$GLOBALS['fbm_registered_settings'] = array();
		}

			$GLOBALS['fbm_registered_settings'][ $option_name ] = array(
				'group' => $option_group,
				'args'  => $args,
			);

			return true;
	}
}

if ( ! function_exists( 'add_settings_section' ) ) {
	function add_settings_section( string $id, string $title, $callback, string $page ): void {
		if ( ! isset( $GLOBALS['fbm_settings_sections'] ) || ! is_array( $GLOBALS['fbm_settings_sections'] ) ) {
				$GLOBALS['fbm_settings_sections'] = array();
		}

			$GLOBALS['fbm_settings_sections'][ $page ][ $id ] = array(
				'title'    => $title,
				'callback' => $callback,
			);
	}
}

if ( ! function_exists( 'add_settings_field' ) ) {
        function add_settings_field( string $id, string $title, $callback, string $page, string $section = 'default', array $args = array() ): void {
                if ( ! isset( $GLOBALS['fbm_settings_fields'] ) || ! is_array( $GLOBALS['fbm_settings_fields'] ) ) {
                                $GLOBALS['fbm_settings_fields'] = array();
                }

                        $GLOBALS['fbm_settings_fields'][ $page ][ $section ][ $id ] = array(
                                'title'    => $title,
                                'callback' => $callback,
                                'args'     => $args,
                        );
        }
}

if ( ! function_exists( 'add_settings_error' ) ) {
        function add_settings_error( string $setting, string $code, string $message, string $type = 'error' ): void {
                if ( ! isset( $GLOBALS['fbm_settings_errors'] ) || ! is_array( $GLOBALS['fbm_settings_errors'] ) ) {
                        $GLOBALS['fbm_settings_errors'] = array();
                }

                $GLOBALS['fbm_settings_errors'][] = array(
                        'setting' => $setting,
                        'code'    => $code,
                        'message' => $message,
                        'type'    => $type,
                );
        }
}

if ( ! function_exists( 'wp_mail' ) ) {
        function wp_mail( string $to, string $subject, string $message, $headers = array() ): bool {
                if ( ! isset( $GLOBALS['fbm_mail_log'] ) || ! is_array( $GLOBALS['fbm_mail_log'] ) ) {
                                $GLOBALS['fbm_mail_log'] = array();
                }

                        $GLOBALS['fbm_mail_log'][] = array(
                                'to'      => $to,
                                'subject' => $subject,
                                'message' => $message,
                                'headers' => $headers,
                        );

                        if ( class_exists( '\\FBM\\Tests\\Registration\\Fixtures\\SpyWelcomeMailer', false ) ) {
                                \FBM\Tests\Registration\Fixtures\SpyWelcomeMailer::$sent[] = array(
                                        'email'            => $to,
                                        'first_name'       => '',
                                        'member_reference' => '',
                                        'token'            => '',
                                );
                        }

                        return true;
        }
}

if ( ! function_exists( 'add_shortcode' ) ) {
		/**
		 * Capture shortcode registration during tests.
		 *
		 * @param string   $tag      Shortcode tag.
		 * @param callable $callback Shortcode handler.
		 */
	function add_shortcode( string $tag, callable $callback ): void {
			$GLOBALS['fbm_shortcodes'][ $tag ] = $callback;
	}
}

if ( ! function_exists( 'is_user_logged_in' ) ) {
		/**
		 * Determine if the current test request is authenticated.
		 */
	function is_user_logged_in(): bool {
			return ! empty( $GLOBALS['fbm_user_logged_in'] );
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
		/**
		 * Check the mocked capability store.
		 *
		 * @param string $capability Capability identifier.
		 */
	function current_user_can( string $capability ): bool {
			$caps = $GLOBALS['fbm_current_caps'] ?? array();

			return ! empty( $caps[ $capability ] );
	}
}

if ( ! function_exists( 'status_header' ) ) {
		/**
		 * Record HTTP status codes for assertions.
		 *
		 * @param int $code Status code.
		 */
	function status_header( int $code ): void {
			$GLOBALS['fbm_status_header'] = $code;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
		/**
		 * Simple passthrough translation stub.
		 *
		 * @param string      $text   Text to translate.
		 * @param string|null $domain Text domain.
		 */
	function esc_html__( string $text, ?string $domain = null ): string { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			unset( $domain );

			return $text;
	}
}

if ( ! function_exists( 'esc_html_e' ) ) {
		/**
		 * Echo translation stub.
		 *
		 * @param string      $text   Text to output.
		 * @param string|null $domain Text domain.
		 */
	function esc_html_e( string $text, ?string $domain = null ): void { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			echo esc_html__( $text, $domain );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
		/**
		 * Attribute escaping stub.
		 *
		 * @param string $text Raw attribute text.
		 */
	function esc_attr( string $text ): string { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			return $text;
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
		/**
		 * URL escaping stub.
		 *
		 * @param string $url URL to normalise.
		 */
	function esc_url_raw( string $url ): string { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			return $url;
	}
}

if ( ! function_exists( 'plugins_url' ) ) {
                /**
                 * Generate plugin asset URLs for tests.
                 *
                 * @param string $path        Relative asset path.
                 * @param string $plugin_file Plugin file reference.
                 */
        function plugins_url( string $path, string $plugin_file ): string { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
                        unset( $plugin_file );

                        return 'https://example.test/plugin/' . ltrim( $path, '/' );
        }
}

if ( ! function_exists( 'plugin_dir_path' ) ) {
               /**
                * Provide plugin directory paths during tests.
                *
                * @param string $file File reference.
                */
       function plugin_dir_path( string $file ): string { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
                       $normalized = str_replace( '\\', '/', dirname( $file ) );

                       return rtrim( $normalized, '/' ) . '/';
       }
}

if ( ! function_exists( 'rest_url' ) ) {
		/**
		 * Compose a REST API URL for tests.
		 *
		 * @param string $path Route path.
		 */
	function rest_url( string $path = '' ): string { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			return 'https://example.test/wp-json/' . ltrim( $path, '/' );
	}
}

if ( ! function_exists( 'wp_create_nonce' ) ) {
		/**
		 * Create deterministic nonces for tests.
		 *
		 * @param string $action Action name.
		 */
	function wp_create_nonce( string $action ): string { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			return 'nonce-' . $action;
	}
}

if ( ! function_exists( 'wp_register_style' ) ) {
		/**
		 * Record registered styles.
		 *
		 * @param string      $handle Style handle.
		 * @param string      $src    Source URL.
		 * @param array       $deps   Dependencies.
		 * @param string|bool $ver    Version string.
		 */
	function wp_register_style( string $handle, string $src, array $deps = array(), $ver = false ): void { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			$GLOBALS['fbm_registered_styles'][ $handle ] = compact( 'handle', 'src', 'deps', 'ver' );
	}
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
                /**
                 * Record enqueued styles.
                 *
                 * @param string      $handle Style handle.
                 * @param string|bool $src    Optional source URL.
                 * @param array       $deps   Optional dependencies.
                 * @param string|bool $ver    Optional version string.
                 * @param string|bool $media  Optional media target.
                 */
        function wp_enqueue_style( string $handle, $src = false, array $deps = array(), $ver = false, $media = false ): void { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
                        unset( $src, $deps, $ver, $media );

                        $GLOBALS['fbm_enqueued_styles'][] = $handle;
        }
}

if ( ! function_exists( 'wp_register_script' ) ) {
		/**
		 * Record registered scripts.
		 *
		 * @param string      $handle    Script handle.
		 * @param string      $src       Source URL.
		 * @param array       $deps      Dependencies.
		 * @param string|bool $ver       Version.
		 * @param bool        $in_footer Whether to load in footer.
		 */
	function wp_register_script( string $handle, string $src, array $deps = array(), $ver = false, bool $in_footer = false ): void { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			$GLOBALS['fbm_registered_scripts'][ $handle ] = compact( 'handle', 'src', 'deps', 'ver', 'in_footer' );
	}
}

if ( ! function_exists( 'wp_localize_script' ) ) {
		/**
		 * Record localized script data.
		 *
		 * @param string $handle      Script handle.
		 * @param string $object_name Exposed object name.
		 * @param array  $l10n        Data payload.
		 */
	function wp_localize_script( string $handle, string $object_name, array $l10n ): void { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			$GLOBALS['fbm_localized_scripts'][ $handle ] = array(
				'name' => $object_name,
				'data' => $l10n,
			);
	}
}

if ( ! function_exists( 'wp_set_script_translations' ) ) {
               /**
                * Record translation domain associations.
                *
                * @param string $handle Script handle.
                * @param string $domain Text domain.
                * @param string $path   Language directory path.
                */
       function wp_set_script_translations( string $handle, string $domain, string $path = '' ): void { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
                       $GLOBALS['fbm_script_translations'][ $handle ] = array(
                               'domain' => $domain,
                               'path'   => $path,
                       );
       }
}

if ( ! function_exists( 'wp_enqueue_script' ) ) {
                /**
                 * Record enqueued scripts.
                 *
                 * @param string      $handle Script handle.
                 * @param string|bool $src    Optional source URL.
                 * @param array       $deps   Optional dependencies.
                 * @param string|bool $ver    Optional version string.
                 * @param bool        $in_footer Optional footer flag.
                 */
        function wp_enqueue_script( string $handle, $src = false, array $deps = array(), $ver = false, $in_footer = false ): void { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
                        unset( $src, $deps, $ver, $in_footer );

                        $GLOBALS['fbm_enqueued_scripts'][] = $handle;
        }
}

if ( class_exists( 'php_user_filter' ) && ! class_exists( 'FBM_TempStreamCaptureFilter' ) ) {
        /**
         * Capture php://temp stream output for assertions.
         */
        class FBM_TempStreamCaptureFilter extends php_user_filter {
                /**
                 * @inheritDoc
                 */
                public function filter( $in, $out, &$consumed, bool $closing ): int {
                        unset( $closing );

                        while ( $bucket = stream_bucket_make_writeable( $in ) ) {
                                $consumed += $bucket->datalen;

                                if ( ! isset( $GLOBALS['fbm_last_csv_stream'] ) || ! is_string( $GLOBALS['fbm_last_csv_stream'] ) ) {
                                        $GLOBALS['fbm_last_csv_stream'] = '';
                                }

                                $GLOBALS['fbm_last_csv_stream'] .= $bucket->data;

                                stream_bucket_append( $out, $bucket );
                        }

                        return PSFS_PASS_ON;
                }
        }

        if ( function_exists( 'stream_filter_register' ) ) {
                @stream_filter_register( 'fbm.capture', FBM_TempStreamCaptureFilter::class );
        }
}
