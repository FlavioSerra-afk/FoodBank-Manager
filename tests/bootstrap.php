<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
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
                        );

                        return 1;
                }

                public function prepare( string $query, ...$args ) {
                        $this->last_prepare = array(
                                'query' => $query,
                                'args'  => $args,
                        );

                        return $query;
                }

                public function insert( string $table, array $data, array $format ) {
                        unset( $format );

                        if ( str_contains( $table, 'fbm_attendance' ) ) {
                                $record_id                    = $this->next_attendance_id++;
                                $data['id']                   = $record_id;
                                $this->attendance[ $record_id ] = $data;

                                return 1;
                        }

                        unset( $table );

                        $member_id           = $this->next_member_id++;
                        $data['id']          = $member_id;
                        $this->members[$member_id] = $data;
                        $this->insert_id     = $member_id;

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

                        if ( str_contains( $sql, 'token_hash = %s' ) ) {
                                $hash = (string) ( $args[1] ?? '' );

                                foreach ( $this->tokens as $record ) {
                                        if ( $record['token_hash'] === $hash && null === $record['revoked_at'] ) {
                                                return array(
                                                        'member_id'  => $record['member_id'],
                                                        'token_hash' => $record['token_hash'],
                                                        'version'    => $record['version'] ?? 'v1',
                                                );
                                        }
                                }

                                return null;
                        }

                        if ( str_contains( $sql, 'WHERE email = %s' ) ) {
                                $email = (string) ( $args[1] ?? '' );

                                foreach ( $this->members as $member ) {
                                        if ( $member['email'] === $email ) {
                                                return array(
                                                        'id'               => $member['id'],
                                                        'status'           => $member['status'] ?? 'active',
                                                        'member_reference' => $member['member_reference'],
                                                );
                                        }
                                }

                                return null;
                        }

                        if ( str_contains( $sql, 'WHERE member_reference = %s' ) ) {
                                $reference = (string) ( $args[1] ?? '' );

                                foreach ( $this->members as $member ) {
                                        if ( $member['member_reference'] === $reference ) {
                                                return array(
                                                        'id'               => $member['id'],
                                                        'status'           => $member['status'] ?? 'active',
                                                        'member_reference' => $member['member_reference'],
                                                );
                                        }
                                }

                                return null;
                        }

                        if ( str_contains( $sql, 'WHERE id = %d' ) ) {
                                $member_id = (int) ( $args[1] ?? 0 );

                                if ( isset( $this->members[ $member_id ] ) ) {
                                        $member = $this->members[ $member_id ];

                                        return array(
                                                'id'               => $member['id'],
                                                'status'           => $member['status'] ?? 'active',
                                                'member_reference' => $member['member_reference'],
                                                'first_name'       => $member['first_name'],
                                                'email'            => $member['email'],
                                        );
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

                        if ( str_contains( $sql, 'ORDER BY collected_at DESC' ) ) {
                                $table     = (string) ( $args[0] ?? '' );
                                $reference = (string) ( $args[1] ?? '' );

                                if ( str_contains( $table, 'fbm_attendance' ) ) {
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
                        }

                        if ( str_contains( $sql, 'member_reference = %s' ) ) {
                                $table     = (string) ( $args[0] ?? '' );
                                $reference = (string) ( $args[1] ?? '' );

                                if ( str_contains( $table, 'fbm_attendance' ) ) {
                                        $date = (string) ( $args[2] ?? '' );

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

                                foreach ( $this->members as $member ) {
                                        if ( $member['member_reference'] === $reference ) {
                                                return $member['id'];
                                        }
                                }
                        }

                        return null;
                }

                public function update( string $table, array $data, array $where, array $format, array $where_format ) {
                        unset( $table );
                        unset( $format );
                        unset( $where_format );

                        if ( isset( $where['member_id'] ) ) {
                                $member_id = (int) $where['member_id'];

                                if ( ! isset( $this->tokens[ $member_id ] ) ) {
                                        return 0;
                                }

                                $this->tokens[ $member_id ]['revoked_at'] = $data['revoked_at'] ?? null;

                                return 1;
                        }

                        if ( isset( $where['id'] ) ) {
                                $member_id = (int) $where['id'];

                                if ( ! isset( $this->members[ $member_id ] ) ) {
                                        return 0;
                                }

                                $this->members[ $member_id ] = array_merge( $this->members[ $member_id ], $data );

                                return 1;
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

if ( ! class_exists( 'WP_REST_Response' ) ) {
        /**
         * Minimal WP_REST_Response stand-in.
         */
        class WP_REST_Response {
                private $data;

                public function __construct( $data = null ) {
                        $this->data = $data;
                }

                public function get_data() {
                        return $this->data;
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

if ( ! function_exists( 'rest_ensure_response' ) ) {
        function rest_ensure_response( $response ) {
                if ( $response instanceof WP_REST_Response || $response instanceof WP_Error ) {
                        return $response;
                }

                return new WP_REST_Response( $response );
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

if ( ! function_exists( 'get_current_user_id' ) ) {
        function get_current_user_id(): int {
                return 1;
        }
}

require_once __DIR__ . '/../includes/Core/class-install.php';
require_once __DIR__ . '/../includes/Attendance/class-attendancerepository.php';
require_once __DIR__ . '/../includes/Attendance/class-checkinservice.php';
require_once __DIR__ . '/../includes/Token/class-tokenrepository.php';
require_once __DIR__ . '/../includes/Token/class-tokenservice.php';
require_once __DIR__ . '/../includes/Registration/class-membersrepository.php';
require_once __DIR__ . '/../includes/Registration/class-registrationservice.php';
require_once __DIR__ . '/../includes/Admin/class-memberspage.php';

if ( ! function_exists( 'do_action' ) ) {
        function do_action( string $hook, ...$args ): void {
                unset( $hook );
                unset( $args );
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
