<?php
/**
 * Events admin page.
 *
 * @package FoodBankManager\Admin
 */

declare(strict_types=1);

namespace FBM\Admin;

use FBM\Attendance\EventsRepo;
use function current_user_can;
use function wp_safe_redirect;
use function check_admin_referer;
use function add_query_arg;
use function remove_query_arg;
use function wp_unslash;
use function sanitize_text_field;
use function sanitize_key;
use function esc_url;
use function esc_html;
use function esc_attr;
use function wp_nonce_field;
use function absint;
use function apply_filters;
use function admin_url;

/**
 * Events page controller.
 */
final class EventsPage {
    /**
     * Route handler.
     */
    public static function route(): void {
        if (!current_user_can('fbm_manage_events')) {
            echo '<div class="wrap fbm-admin"><p>' . esc_html__('You do not have permission to access this page.', 'foodbank-manager') . '</p></div>';
            return;
        }

        $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper(sanitize_text_field(wp_unslash((string)$_SERVER['REQUEST_METHOD']))) : 'GET';
        if ('POST' === $method) {
            $action = isset($_POST['fbm_action']) ? sanitize_key(wp_unslash((string)$_POST['fbm_action'])) : '';
            switch ($action) {
                case 'save':
                    check_admin_referer('fbm_events_save', 'fbm_nonce');
                    $id = isset($_POST['id']) ? absint(wp_unslash((string)$_POST['id'])) : 0;
                    $data = self::collect_posted();
                    if ($id) {
                        EventsRepo::update($id, $data);
                    } else {
                        EventsRepo::create($data);
                    }
                    $url = add_query_arg('notice', 'event_saved', remove_query_arg(array('action','id')));
                    wp_safe_redirect($url);
                    exit;
                case 'delete':
                    $id = isset($_POST['id']) ? absint(wp_unslash((string)$_POST['id'])) : 0;
                    check_admin_referer('fbm_events_delete_' . $id, 'fbm_nonce');
                    if ($id) {
                        EventsRepo::delete($id);
                    }
                    $url = add_query_arg('notice', 'event_deleted', remove_query_arg(array('action','id')));
                    wp_safe_redirect($url);
                    exit;
            }
        }

        $filters = self::get_filters();
        $opts    = array(
            'order_by' => $filters['orderby'],
            'order'    => $filters['order'],
            'limit'    => $filters['per_page'],
            'offset'   => ($filters['page'] - 1) * $filters['per_page'],
        );
        $data = EventsRepo::list(
            array(
                'status' => $filters['status'],
                'q'      => $filters['q'],
                'from'   => $filters['from'],
                'to'     => $filters['to'],
            ),
            $opts
        );

        $edit_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
        $event   = $edit_id ? EventsRepo::get($edit_id) : null;
        $notice  = isset($_GET['notice']) ? sanitize_key((string)$_GET['notice']) : '';

        require FBM_PATH . 'templates/admin/events.php';
    }

    /**
     * Collect POSTed fields.
     *
     * @return array<string,mixed>
     */
    private static function collect_posted(): array {
        return array(
            'title'     => sanitize_text_field(wp_unslash((string)($_POST['title'] ?? ''))),
            'starts_at' => sanitize_text_field(wp_unslash((string)($_POST['starts_at'] ?? ''))),
            'ends_at'   => sanitize_text_field(wp_unslash((string)($_POST['ends_at'] ?? ''))),
            'location'  => sanitize_text_field(wp_unslash((string)($_POST['location'] ?? ''))),
            'capacity'  => isset($_POST['capacity']) ? absint(wp_unslash((string)$_POST['capacity'])) : null,
            'notes'     => sanitize_text_field(wp_unslash((string)($_POST['notes'] ?? ''))),
            'status'    => sanitize_key(wp_unslash((string)($_POST['status'] ?? 'active'))),
        );
    }

    /**
     * Parse filters from query vars.
     *
     * @return array<string,mixed>
     */
    private static function get_filters(): array {
        $q        = isset($_GET['q']) ? sanitize_text_field((string)$_GET['q']) : '';
        $status   = isset($_GET['status']) ? sanitize_key((string)$_GET['status']) : '';
        if (!in_array($status, array('active','cancelled'), true)) {
            $status = '';
        }
        $from     = isset($_GET['from']) ? sanitize_text_field((string)$_GET['from']) : '';
        $to       = isset($_GET['to']) ? sanitize_text_field((string)$_GET['to']) : '';
        $orderby  = isset($_GET['orderby']) ? sanitize_key((string)$_GET['orderby']) : 'starts_at';
        $allowed_orderby = array('starts_at','ends_at','title','created_at');
        if (!in_array($orderby, $allowed_orderby, true)) {
            $orderby = 'starts_at';
        }
        $order = isset($_GET['order']) ? strtoupper(sanitize_key((string)$_GET['order'])) : 'ASC';
        if (!in_array($order, array('ASC','DESC'), true)) {
            $order = 'ASC';
        }
        $page     = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $per_page = isset($_GET['per_page']) ? max(1, min(200, absint($_GET['per_page']))) : 20;

        return array(
            'q'        => $q,
            'status'   => $status,
            'from'     => $from,
            'to'       => $to,
            'orderby'  => $orderby,
            'order'    => $order,
            'page'     => $page,
            'per_page' => $per_page,
        );
    }
}
