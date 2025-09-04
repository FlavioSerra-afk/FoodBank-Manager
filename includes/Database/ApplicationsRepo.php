<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Database;

use wpdb;

class ApplicationsRepo {
        /**
         * List applications with optional filters.
         *
         * @param array{form_id?:int,status?:string,date_from?:string,date_to?:string,city?:string,postcode?:string,has_file?:?bool,consent?:?bool,search?:string,page?:int,per_page?:int,orderby?:string,order?:string} $args Arguments.
         * @return array{rows: array<int,array>, total:int}
         */
        public static function list( array $args ): array {
                global $wpdb;
                $defaults = array(
                        'page'     => 1,
                        'per_page' => 25,
                        'orderby'  => 'created_at',
                        'order'    => 'DESC',
                );
                $args     = array_merge( $defaults, $args );
		$where    = 'WHERE 1=1';
		$params   = array();

		if ( ! empty( $args['form_id'] ) ) {
			$where   .= ' AND a.form_id = %d';
			$params[] = (int) $args['form_id'];
		}
		if ( ! empty( $args['status'] ) ) {
			$where   .= ' AND a.status = %s';
			$params[] = $args['status'];
		}
		if ( ! empty( $args['date_from'] ) ) {
			$where   .= ' AND a.created_at >= %s';
			$params[] = $args['date_from'];
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where   .= ' AND a.created_at <= %s';
			$params[] = $args['date_to'];
		}
		if ( isset( $args['has_file'] ) ) {
			$where .= $args['has_file'] ?
				' AND EXISTS (SELECT 1 FROM ' . $wpdb->prefix . 'fb_files f WHERE f.application_id = a.id)' :
				' AND NOT EXISTS (SELECT 1 FROM ' . $wpdb->prefix . 'fb_files f WHERE f.application_id = a.id)';
		}
		if ( isset( $args['consent'] ) ) {
			$where .= $args['consent'] ? ' AND a.consent_timestamp IS NOT NULL' : ' AND a.consent_timestamp IS NULL';
		}
		if ( ! empty( $args['city'] ) ) {
			$like     = '%' . $wpdb->esc_like( substr( $args['city'], 0, 64 ) ) . '%';
			$where   .= ' AND a.data_json LIKE %s';
			$params[] = $like;
		}
		if ( ! empty( $args['postcode'] ) ) {
			$like     = '%' . $wpdb->esc_like( substr( $args['postcode'], 0, 64 ) ) . '%';
			$where   .= ' AND a.data_json LIKE %s';
			$params[] = $like;
		}
		if ( ! empty( $args['search'] ) ) {
			$like     = '%' . $wpdb->esc_like( substr( $args['search'], 0, 64 ) ) . '%';
			$where   .= ' AND a.data_json LIKE %s';
			$params[] = $like;
		}

                $allowed = array( 'created_at', 'status', 'id' );
                $orderby = in_array( $args['orderby'], $allowed, true ) ? $args['orderby'] : 'created_at';
                $order   = 'ASC' === strtoupper( (string) $args['order'] ) ? 'ASC' : 'DESC';

		$offset = ( (int) $args['page'] - 1 ) * (int) $args['per_page'];
		$limit  = (int) $args['per_page'];

		$sql                    = "SELECT a.*, EXISTS(SELECT 1 FROM {$wpdb->prefix}fb_files f WHERE f.application_id = a.id) AS has_files
                FROM {$wpdb->prefix}fb_applications a
                $where
                ORDER BY $orderby $order
                LIMIT %d OFFSET %d";
		$params_with_pagination = array_merge( $params, array( $limit, $offset ) );
		$prepared               = $wpdb->prepare( $sql, $params_with_pagination );
                $rows                   = $wpdb->get_results( $prepared, 'ARRAY_A' );

               $count_sql   = "SELECT COUNT(*) FROM {$wpdb->prefix}fb_applications a $where";
               $count_query = $wpdb->prepare( $count_sql, $params );
               $total       = (int) $wpdb->get_var( $count_query );

		return array(
			'rows'  => $rows,
			'total' => $total,
		);
	}

        /**
         * Retrieve an application by ID.
         *
         * @return array|null
         */
        public static function get( int $id ): ?array {
		global $wpdb;
               $sql      = "SELECT * FROM {$wpdb->prefix}fb_applications WHERE id = %d";
               $prepared = $wpdb->prepare( $sql, $id );
               $app      = $wpdb->get_row( $prepared, 'ARRAY_A' );
		if ( ! $app ) {
			return null;
		}
               $files_sql    = "SELECT id, original_name, mime, size_bytes, created_at FROM {$wpdb->prefix}fb_files WHERE application_id = %d";
               $files_query  = $wpdb->prepare( $files_sql, $id );
               $files        = $wpdb->get_results( $files_query, 'ARRAY_A' );
		$app['files'] = $files;
		return $app;
	}

        /**
         * Soft-delete an application.
         */
        public static function softDelete( int $id ): bool {
		global $wpdb;
               $sql      = "UPDATE {$wpdb->prefix}fb_applications SET status = 'archived' WHERE id = %d";
               $prepared = $wpdb->prepare( $sql, $id );
               $res      = $wpdb->query( $prepared );
               return (bool) $res;
        }

        /**
         * Get a sanitized entry with decrypted PII and files.
         *
         * @param int $id Entry ID.
         * @return array|null
         */
        public static function get_entry( int $id ): ?array {
                $row = self::get( $id );
                if ( ! $row ) {
                        return null;
                }
                $data = json_decode( (string) ( $row['data_json'] ?? '' ), true );
                if ( ! is_array( $data ) ) {
                        $data = array();
                }
                $data = array_map( 'sanitize_text_field', $data );

                $pii = \FoodBankManager\Security\Crypto::decryptSensitive( (string) ( $row['pii_encrypted_blob'] ?? '' ) );
                if ( ! is_array( $pii ) ) {
                        $pii = array();
                }
                $pii = array_map( 'sanitize_text_field', $pii );

                $files = array();
                foreach ( $row['files'] ?? array() as $f ) {
                        $files[] = array(
                                'id'           => (int) ( $f['id'] ?? 0 ),
                                'original_name'=> sanitize_file_name( (string) ( $f['original_name'] ?? '' ) ),
                                'mime'        => sanitize_text_field( (string) ( $f['mime'] ?? '' ) ),
                                'size_bytes'  => (int) ( $f['size_bytes'] ?? 0 ),
                                'created_at'  => sanitize_text_field( (string) ( $f['created_at'] ?? '' ) ),
                        );
                }

                return array(
                        'id'         => (int) $row['id'],
                        'status'     => sanitize_key( (string) ( $row['status'] ?? '' ) ),
                        'created_at' => sanitize_text_field( (string) ( $row['created_at'] ?? '' ) ),
                        'data'       => $data,
                        'pii'        => $pii,
                        'files'      => $files,
                );
        }
}
