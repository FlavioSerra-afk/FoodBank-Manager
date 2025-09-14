<?php
/**
 * REST error normalization helper.
 *
 * @package FBM\Rest
 */

declare(strict_types=1);

namespace FBM\Rest;

use WP_Error;
use WP_REST_Response;
use function add_filter;
use function is_wp_error;

/**
 * Helper for consistent REST error responses.
 */
final class ErrorHelper {
	/** Register filters. */
	public static function register(): void {
		add_filter( 'rest_request_after_callbacks', array( self::class, 'convert_error' ), 10, 3 );
	}

	/**
	 * Convert WP_Error responses to our contract.
	 *
	 * @param mixed           $response Response.
	 * @param array           $handler  Handler.
	 * @param \WP_REST_Request $request Request.
	 * @return mixed
	 */
	public static function convert_error( $response, $handler, $request ) {
		if ( is_wp_error( $response ) ) {
			$err = self::from_wp_error( $response );
			return new WP_REST_Response( $err['body'], $err['status'] );
		}
		return $response;
	}

	/**
	 * Build error array from WP_Error.
	 *
	 * @param WP_Error $e               Error object.
	 * @param int      $fallback_status Fallback status.
	 * @return array{status:int, body:array}
	 */
	public static function from_wp_error( WP_Error $e, int $fallback_status = 400 ): array {
		$data    = method_exists( $e, 'get_error_data' ) ? $e->get_error_data() : null;
		$details = is_array( $data ) ? ( $data['details'] ?? null ) : null;
		$status  = is_array( $data ) && isset( $data['status'] ) ? (int) $data['status'] : $fallback_status;
		$code    = $e->get_error_code();
		switch ( $code ) {
			case 'rest_invalid_param':
				$code   = 'invalid_param';
				$status = 422;
				break;
			case 'rest_not_logged_in':
				$code   = 'unauthorized';
				$status = 401;
				break;
			case 'rest_forbidden':
				$code   = 'forbidden';
				$status = 403;
				break;
			case 'rest_no_route':
			case 'rest_post_invalid_id':
				$code   = 'not_found';
				$status = 404;
				break;
		}
		return array(
			'status' => $status,
			'body'   => array(
				'success' => false,
				'error'   => array(
					'code'    => $code,
					'message' => $e->get_error_message(),
					'details' => $details,
				),
			),
		);
	}
}
