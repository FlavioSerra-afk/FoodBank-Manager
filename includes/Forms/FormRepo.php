<?php
/**
 * Repository for fb_form CPT.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Forms;

use function sanitize_text_field;
use function wp_insert_post;
use function wp_update_post;
use function get_post;
use function get_post_meta;
use function update_post_meta;
use function wp_json_encode;
use function json_decode;
use function absint;
use function wp_delete_post;
use function get_posts;
use function sanitize_key;
use function wp_list_pluck;
use function sanitize_title;

/**
 * CRUD wrapper for forms.
 */
final class FormRepo {
	private const META_SCHEMA  = '_fbm_form_schema';
	private const META_VERSION = '_fbm_form_version';
	private const META_MASK    = '_fbm_form_mask_sensitive';

	/** @return int new post ID */
	public static function create( array $data ): int {
		$schema  = self::normalize_schema( $data['schema'] ?? array(), (string) ( $data['title'] ?? 'Untitled' ) );
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'fb_form',
				'post_status' => 'publish',
				'post_title'  => sanitize_text_field( (string) ( $data['title'] ?? 'Untitled' ) ),
			)
		);
		update_post_meta( $post_id, self::META_SCHEMA, wp_json_encode( $schema ) );
		update_post_meta( $post_id, self::META_VERSION, 1 );
		$mask = ! empty( $data['mask_sensitive'] );
		update_post_meta( $post_id, self::META_MASK, $mask ? '1' : '0' );
		return (int) $post_id;
	}

	/** @return bool */
	public static function update( int $post_id, array $data ): bool {
		$schema = self::normalize_schema( $data['schema'] ?? array(), (string) ( $data['title'] ?? 'Untitled' ) );
		wp_update_post(
			array(
				'ID'         => $post_id,
				'post_title' => sanitize_text_field( (string) ( $data['title'] ?? 'Untitled' ) ),
			)
		);
		update_post_meta( $post_id, self::META_SCHEMA, wp_json_encode( $schema ) );
		if ( isset( $data['mask_sensitive'] ) ) {
			update_post_meta( $post_id, self::META_MASK, ! empty( $data['mask_sensitive'] ) ? '1' : '0' );
		}
		return true;
	}

	/** @return array|null normalized form {id,title,schema,mask_sensitive,version} */
	public static function get( int $post_id ): ?array {
		$post = get_post( $post_id );
		if ( ! $post || 'fb_form' !== $post->post_type ) {
			return null;
		}
		$schema = json_decode( (string) get_post_meta( $post_id, self::META_SCHEMA, true ), true );
		if ( ! is_array( $schema ) ) {
			$schema = array();
		}
		return array(
			'id'             => $post_id,
			'title'          => (string) $post->post_title,
			'schema'         => $schema,
			'mask_sensitive' => '1' === (string) get_post_meta( $post_id, self::META_MASK, true ),
			'version'        => (int) get_post_meta( $post_id, self::META_VERSION, true ),
		);
	}

	/** @return array<int,array{ id:int, title:string }> */
	public static function list( array $filters = array() ): array {
		$posts = get_posts(
			array(
				'post_type'      => 'fb_form',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'ASC',
			)
		);
		$out   = array();
		foreach ( $posts as $p ) {
			$out[] = array(
				'id'    => (int) $p->ID,
				'title' => (string) $p->post_title,
			);
		}
		return $out;
	}

	/** @return array normalized schema */
	public static function normalize_schema( array $schema, string $title = 'Form' ): array {
		$fields  = array();
		$allowed = array( 'text', 'email', 'tel', 'date', 'select', 'checkbox', 'file' );
		$i       = 1;
		foreach ( $schema as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}
			$type = sanitize_key( (string) ( $field['type'] ?? '' ) );
			if ( ! in_array( $type, $allowed, true ) ) {
				continue;
			}
			$label = sanitize_text_field( (string) ( $field['label'] ?? '' ) );
			if ( '' === $label ) {
				continue;
			}
			$fid      = 'field_' . $i++;
			$fields[] = array(
				'id'       => $fid,
				'type'     => $type,
				'label'    => $label,
				'required' => ! empty( $field['required'] ),
				'options'  => is_array( $field['options'] ?? null ) ? array_values( array_map( 'sanitize_text_field', $field['options'] ) ) : array(),
			);
		}
		$name = sanitize_text_field( $title );
		$slug = sanitize_key( $title !== '' ? sanitize_title( $title ) : 'form' );
		return array(
			'meta'   => array(
				'name'    => $name !== '' ? $name : 'Form',
				'slug'    => $slug !== '' ? $slug : 'form',
				'captcha' => false,
			),
			'fields' => $fields,
		);
	}
}
