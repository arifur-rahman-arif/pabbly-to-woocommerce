<?php

namespace PTW\includes\classes;
use PHPHtmlParser\Dom;

class RestRoute {

    /**
     * @var mixed
     */
    public $payload;

    public function __construct() {
        add_action('rest_api_init', [$this, 'registerRoute']);
    }

    public function registerRoute() {
        // {site-url}/wp-json/pabbly-to-woocommerce/v1/create-order
        register_rest_route('pabbly-to-woocommerce/v1', 'create-order', array(
            'methods'  => \WP_REST_Server::EDITABLE,
            'callback' => [$this, 'initRequest']
        ), true);
    }

    /**
     * @param $request
     */
    public function initRequest($request) {

        $requestBody = $request->get_body();

        $organizedData = $this->parseData($requestBody);

        return json_encode([
            'status'   => 200,
            'response' => 'Order created'
        ]);
    }

    /**
     * @param  $htmlBody
     * @return mixed
     */
    public function parseData($htmlBody) {

        $dom = new Dom;
        $dom->loadStr($htmlBody);
        $tr = $dom->find('div[size=Letter] tbody tr')[1];
        $shipTo = $tr->find('td')[1];

        $data = [
            'billingAddress' => explode("<br />", $shipTo->innerHtml),
            'items'          => [
                'ids' => []
            ]
        ];

        $data['billingAddress'][] = 'celigo@uscabinetdepot.com';

        $itemTable = $dom->find('div[size=Letter] table')[3]->find('tr');

        if ($itemTable) {
            foreach ($itemTable as $key => $itemRow) {
                if (isset($itemRow->find('td')[1])) {
                    $itemID = $itemRow->find('td')[1]->innerHTML;
                    $data['items']['ids'][] = trim($itemID);
                }
            }
        }

        return $data;
    }
}