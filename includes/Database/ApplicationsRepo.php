<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Database;

use wpdb;
use FoodBankManager\Security\Crypto;
use function wp_json_encode;

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

       /**
        * Find applications by email address.
        *
        * @param string $email Email address.
        * @return array<int,array>
        */
       public static function find_by_email( string $email ): array {
               global $wpdb;
               $like   = '%' . $wpdb->esc_like( substr( $email, 0, 128 ) ) . '%';
               $sql    = "SELECT * FROM {$wpdb->prefix}fb_applications WHERE data_json LIKE %s";
               $query  = $wpdb->prepare( $sql, $like );
               $rows   = $wpdb->get_results( $query, 'ARRAY_A' );
               return $rows ? $rows : array();
       }

       /**
        * Get files for a given application ID.
        *
        * @param int $id Application ID.
        * @return array<int,array>
        */
       public static function get_files_for_application( int $id ): array {
               global $wpdb;
               $sql     = "SELECT id, stored_path, original_name, mime FROM {$wpdb->prefix}fb_files WHERE application_id = %d";
               $query   = $wpdb->prepare( $sql, $id );
               $rows    = $wpdb->get_results( $query, 'ARRAY_A' );
               $sanitized = array();
               foreach ( $rows ?: array() as $row ) {
                       $sanitized[] = array(
                               'id'           => (int) ( $row['id'] ?? 0 ),
                               'stored_path'  => sanitize_text_field( (string) ( $row['stored_path'] ?? '' ) ),
                               'original_name'=> sanitize_file_name( (string) ( $row['original_name'] ?? '' ) ),
                               'mime'         => sanitize_text_field( (string) ( $row['mime'] ?? '' ) ),
                       );
               }
               return $sanitized;
        }

       /**
        * Anonymise a batch of applications.
        *
        * @param array<int> $ids IDs to anonymise.
        * @return int Rows affected.
        */
       public static function anonymise_batch( array $ids ): int {
               global $wpdb;
               $ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
               if ( empty( $ids ) ) {
                       return 0;
               }
               $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
               $sql = "UPDATE {$wpdb->prefix}fb_applications SET data_json='{}',pii_encrypted_blob=NULL WHERE id IN ($placeholders)";
               $prepared = $wpdb->prepare( $sql, $ids );
               return (int) $wpdb->query( $prepared );
       }

       /**
        * Insert an application with optional files.
        *
        * @param int                                                       $form_id Form ID.
        * @param array<string,string>                                      $data    Non-sensitive data.
        * @param array<string,string>                                      $pii     Sensitive data.
        * @param array{text_hash?:string,timestamp?:string,ip?:string}     $consent Consent info.
        * @param array<int,array{stored_path:string,original_name:string,mime:string,size:int,sha256:string}> $files Files.
        * @return int Insert ID.
        */
       public static function insert( int $form_id, array $data, array $pii, array $consent, array $files = array() ): int {
               global $wpdb;
               $now     = gmdate( 'Y-m-d H:i:s' );
               $pii_enc = '';
               try {
                       $pii_enc = Crypto::encryptSensitive( $pii );
               } catch ( \Throwable $e ) {
                       $pii_enc = '';
               }
               $wpdb->insert(
                       $wpdb->prefix . 'fb_applications',
                       array(
                               'form_id'            => $form_id,
                               'status'             => 'new',
                               'data_json'          => wp_json_encode( $data ),
                               'pii_encrypted_blob' => $pii_enc,
                               'consent_text_hash'  => $consent['text_hash'] ?? '',
                               'consent_timestamp'  => $consent['timestamp'] ?? '',
                               'consent_ip'         => $consent['ip'] ?? '',
                               'created_at'         => $now,
                               'updated_at'         => $now,
                       ),
                       array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
               );
               $id = (int) $wpdb->insert_id;
               foreach ( $files as $file ) {
                       $wpdb->insert(
                               $wpdb->prefix . 'fb_files',
                               array(
                                       'application_id' => $id,
                                       'stored_path'    => $file['stored_path'],
                                       'original_name'  => $file['original_name'],
                                       'mime'           => $file['mime'],
                                       'size_bytes'     => (int) $file['size'],
                                       'sha256'         => $file['sha256'],
                                       'created_at'     => $now,
                               ),
                               array( '%d', '%s', '%s', '%s', '%d', '%s', '%s' )
                       );
               }
               return $id;
       }
}
