<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

if ( ! defined( 'FBM_PATH' ) ) {
	define( 'FBM_PATH', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'FBM_FILE' ) ) {
	define( 'FBM_FILE', FBM_PATH . 'foodbank-manager.php' );
}

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

if ( ! defined( 'FBM_TESTING' ) ) {
        define( 'FBM_TESTING', true );
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

                public function get_last_prepare(): ?array {
                        return $this->last_prepare;
                }

                public function insert( string $table, array $data, array $format ) {
                        unset( $format );

                        if ( str_contains( $table, 'fbm_attendance_overrides' ) ) {
                                $record_id                         = $this->next_override_id++;
                                $data['id']                        = $record_id;
                                $this->attendance_overrides[$record_id] = $data;

                                return 1;
                        }

                        if ( str_contains( $table, 'fbm_attendance' ) ) {
                                $record_id                    = $this->next_attendance_id++;
                                $data['id']                   = $record_id;
                                $this->attendance[ $record_id ] = $data;
                                $this->insert_id              = $record_id;

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

                        if ( str_contains( $sql, 'token_hash = %s' ) && str_contains( $sql, 'fbm_tokens' ) ) {
                                $hash = (string) ( $args[0] ?? '' );

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

                        if ( str_contains( $sql, 'fbm_members' ) && str_contains( $sql, 'WHERE email = %s' ) ) {
                                $email = (string) ( $args[0] ?? '' );

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

                        if ( str_contains( $sql, 'fbm_members' ) && str_contains( $sql, 'WHERE member_reference = %s' ) ) {
                                $reference = (string) ( $args[0] ?? '' );

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

                        if ( str_contains( $sql, 'fbm_members' ) && str_contains( $sql, 'WHERE id = %d' ) ) {
                                $member_id = (int) ( $args[0] ?? 0 );

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

                                        $total++;

                                        $member = $this->find_member_by_reference( (string) $record['member_reference'] );
                                        $status = isset( $member['status'] ) ? (string) $member['status'] : '';

                                        if ( $status === $active_status ) {
                                                $active++;
                                                continue;
                                        }

                                        if ( $status === $revoked_status ) {
                                                $revoked++;
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
                                str_contains( $sql, 'FROM `' )
                                && str_contains( $sql, 'fbm_attendance' )
                                && str_contains( $sql, 'ORDER BY' )
                        ) {
                                $start = (string) ( $args[0] ?? '' );
                                $end   = (string) ( $args[1] ?? '' );

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

                                        $rows[] = array(
                                                'member_reference' => (string) $record['member_reference'],
                                                'collected_at'     => (string) $record['collected_at'],
                                                'collected_date'   => $collected_date,
                                                'method'           => (string) $record['method'],
                                                'note'             => $record['note'] ?? null,
                                                'recorded_by'      => $record['recorded_by'] ?? null,
                                                'status'           => isset( $member['status'] ) ? (string) $member['status'] : '',
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

                        return array();
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

                public function delete( string $table, array $where, array $where_format = array() ) {
                        unset( $where_format );

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
        function update_option( string $name, $value ) {
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

                $GLOBALS['fbm_deleted_options'][] = $name;

                if ( isset( $GLOBALS['fbm_options'] ) && is_array( $GLOBALS['fbm_options'] ) ) {
                        unset( $GLOBALS['fbm_options'][ $name ] );
                }

                return true;
        }
}

if ( ! function_exists( 'get_transient' ) ) {
        function get_transient( string $name ) {
                $transients = $GLOBALS['fbm_transients'] ?? array();

                if ( ! isset( $transients[ $name ] ) ) {
                        return false;
                }

                $record = $transients[ $name ];
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

require_once __DIR__ . '/../includes/Core/class-install.php';
require_once __DIR__ . '/../includes/Attendance/class-attendancerepository.php';
require_once __DIR__ . '/../includes/Attendance/class-attendancereportservice.php';
require_once __DIR__ . '/../includes/Attendance/class-checkinservice.php';
require_once __DIR__ . '/../includes/Token/class-tokenrepository.php';
require_once __DIR__ . '/../includes/Token/class-tokenservice.php';
require_once __DIR__ . '/../includes/Registration/class-membersrepository.php';
require_once __DIR__ . '/../includes/Registration/class-registrationservice.php';
require_once __DIR__ . '/../includes/Email/class-welcomemailer.php';
require_once __DIR__ . '/../includes/Shortcodes/class-registrationform.php';
require_once __DIR__ . '/../includes/Rest/class-checkincontroller.php';
require_once __DIR__ . '/../includes/Admin/class-memberspage.php';
require_once __DIR__ . '/../includes/Admin/class-reportspage.php';
require_once __DIR__ . '/../includes/Admin/class-themepage.php';

if ( ! function_exists( 'add_action' ) ) {
        function add_action( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
                $registry = $GLOBALS['fbm_actions'] ?? array();
                $registry[ $hook ][ $priority ][] = array(
                        'callback' => $callback,
                        'args'     => $accepted_args,
                );
                $GLOBALS['fbm_actions'] = $registry;
        }
}

if ( ! function_exists( 'add_filter' ) ) {
        function add_filter( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
                $registry = $GLOBALS['fbm_filters'] ?? array();
                $registry[ $hook ][ $priority ][] = array(
                        'callback' => $callback,
                        'args'     => $accepted_args,
                );
                $GLOBALS['fbm_filters'] = $registry;
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
                return $data;
        }
}

if ( ! function_exists( 'esc_html_e' ) ) {
        function esc_html_e( string $text, string $domain = '' ): void {
                echo esc_html( __( $text, $domain ) );
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

if ( ! function_exists( 'selected' ) ) {
        function selected( $selected, $current, bool $echo = true ) {
                $result = (string) $selected === (string) $current ? ' selected="selected"' : '';

                if ( $echo ) {
                        echo $result;
                }

                return $result;
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
         * @param string $handle Style handle.
         */
        function wp_enqueue_style( string $handle ): void { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
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
         */
        function wp_set_script_translations( string $handle, string $domain ): void { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
                $GLOBALS['fbm_script_translations'][ $handle ] = $domain;
        }
}

if ( ! function_exists( 'wp_enqueue_script' ) ) {
        /**
         * Record enqueued scripts.
         *
         * @param string $handle Script handle.
         */
        function wp_enqueue_script( string $handle ): void { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
                $GLOBALS['fbm_enqueued_scripts'][] = $handle;
        }
}
