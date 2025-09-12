<?php
/**
 * Jobs REST controller.
 *
 * @package FBM\Rest
 */

declare(strict_types=1);

namespace FBM\Rest;

use FBM\Core\Jobs\JobsRepo;
use WP_REST_Request;
use WP_REST_Response;
use function absint;
use function current_user_can;
use function register_rest_route;

/**
 * REST endpoints for job queue.
 */
final class JobsController {
    /** Register routes. */
    public function register_routes(): void {
        register_rest_route(
            'fbm/v1',
            '/jobs',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'index' ),
                'permission_callback' => static fn(): bool => current_user_can( 'fbm_manage_jobs' ),
                'args'                => array(
                    'limit' => array(
                        'type'              => 'integer',
                        'default'           => 20,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => static fn( $v ): bool => is_int( $v ) && $v > 0 && $v <= 1000,
                    ),
                ),
            )
        );
    }

/**
 * List jobs.
 *
 * @param WP_REST_Request $request Request.
 *
 * @return WP_REST_Response
 */
    public function index( WP_REST_Request $request ): WP_REST_Response {
        $limit = absint( $request->get_param( 'limit' ) );
        $jobs  = JobsRepo::list( array( 'limit' => $limit ) );
        return new WP_REST_Response( array( 'jobs' => $jobs ), 200 );
    }
}

