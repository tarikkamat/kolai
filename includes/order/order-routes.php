<?php
/**
 * Order Routes - REST API route definitions for orders
 *
 * @package    Kolai
 * @subpackage Kolai/includes/order
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Order Routes class
 */
class Kolai_Order_Routes extends Kolai_Route_Base {

    /**
     * Order service instance
     *
     * @var Kolai_Order_Service
     */
    private $order_service;

    /**
     * Constructor
     */
    public function __construct() {
        $this->order_service = new Kolai_Order_Service();
    }

    /**
     * Register order routes
     */
    public function register_routes() {
        register_rest_route('kolai/v1', '/orders', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_order'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Create order endpoint handler
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function create_order($request) {
        return $this->handle(function() use ($request) {
            $params = $request->get_json_params();
            return $this->order_service->create_order($params);
        });
    }
}
