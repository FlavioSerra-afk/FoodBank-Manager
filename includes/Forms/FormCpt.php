<?php
/**
 * Form custom post type registration.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Forms;

use function register_post_type;
use function current_user_can;
use function get_post_type_object;
use function add_action;

/**
 * Registers the fb_form custom post type.
 */
final class FormCpt {
	/**
	 * Hook registration.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'init', array( self::class, 'register_type' ) );
	}

	/**
	 * Register CPT.
	 *
	 * @return void
	 */
	public static function register_type(): void {
		register_post_type(
			'fb_form',
			array(
				'labels'          => array(
					'name'          => 'Forms',
					'singular_name' => 'Form',
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => false,
				'capability_type' => 'fb_form',
				'map_meta_cap'    => true,
				'capabilities'    => array(
					'create_posts'           => 'fbm_manage_forms',
					'edit_post'              => 'fbm_manage_forms',
					'read_post'              => 'fbm_manage_forms',
					'delete_post'            => 'fbm_manage_forms',
					'edit_posts'             => 'fbm_manage_forms',
					'edit_others_posts'      => 'fbm_manage_forms',
					'publish_posts'          => 'fbm_manage_forms',
					'read_private_posts'     => 'fbm_manage_forms',
					'delete_posts'           => 'fbm_manage_forms',
					'delete_private_posts'   => 'fbm_manage_forms',
					'delete_published_posts' => 'fbm_manage_forms',
					'delete_others_posts'    => 'fbm_manage_forms',
				),
				'supports'        => array( 'title', 'revisions' ),
			)
		);
	}
}
