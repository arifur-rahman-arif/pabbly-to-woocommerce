<?php

namespace PTW\includes\classes;

class RestRoute {

    /**
     * @var mixed
     */
    public $payload;

    public function __construct() {
        add_action('rest_api_init', [$this, 'registerRoute']);
    }

    public function registerRoute() {
        // https://718c-118-179-187-165.ngrok.io/wp-json/pabbly-to-woocommerce/v1/create-order
        register_rest_route('pabbly-to-woocommerce/v1', 'create-order', array(
            'methods'  => \WP_REST_Server::EDITABLE,
            'callback' => [$this, 'initRequest']
        ), true);
    }

    /**
     * @param $request
     */
    public function initRequest($request) {
        // $this->payload = file_get_contents("php://input");

        wp_console_log($request->get_body());

        return json_encode([
            'status'   => 200,
            'response' => 'Order created'
        ]);
    }
}