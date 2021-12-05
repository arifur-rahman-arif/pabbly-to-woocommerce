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

        $this->createOrder($organizedData);

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
            ]
        ];

        $data['billingAddress'][] = 'uscd@hypemill.com';

        $itemTable = $dom->find('div[size=Letter] table')[3]->find('tr');

        if ($itemTable) {
            foreach ($itemTable as $key => $itemRow) {
                if (isset($itemRow->find('td')[1])) {
                    $quantity = $itemRow->find('td')[0]->innerHTML;
                    $itemID = $itemRow->find('td')[1]->innerHTML;
                    $price = $itemRow->find('td')[3]->innerHTML;
                    $price = floatval(preg_replace('/[^0-9^\.]/', '', $price));
                    array_push($data['items'], [
                        'id'       => trim($itemID),
                        'quantity' => intval($quantity),
                        'price'    => $price
                    ]);
                }
            }
        }

        return $data;
    }

    /**
     * @param $data
     */
    public function createOrder($data) {

        $products = $this->findProductID($data);

        if (!is_array($products) || count($products) < 1) {
            return;
        }

        $firstName = isset($data['billingAddress']) ? explode(" ", $data['billingAddress'][0])[0] : '';
        $lastName = isset($data['billingAddress']) ? explode(" ", $data['billingAddress'][0])[1] : '';
        $company = isset($data['billingAddress']) ? $data['billingAddress'][1] : '';
        $email = isset($data['billingAddress']) ? $data['billingAddress'][6] : '';
        $phone = isset($data['billingAddress']) ? $data['billingAddress'][2] : '';
        $address_1 = isset($data['billingAddress']) ? $data['billingAddress'][3] : '';
        $city = isset($data['billingAddress']) ? explode(" ", $data['billingAddress'][4])[0] : '';
        $state = isset($data['billingAddress']) ? explode(" ", $data['billingAddress'][4])[1] : '';
        $postcode = isset($data['billingAddress']) ? explode(" ", $data['billingAddress'][4])[2] : '';
        $country = isset($data['billingAddress']) ? $data['billingAddress'][5] : '';

        $address = array(
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'company'    => $company,
            'email'      => $email,
            'phone'      => $phone,
            'address_1'  => $address_1,
            'city'       => $city,
            'state'      => $state,
            'postcode'   => $postcode,
            'country'    => $country
        );

        // Now we create the order
        $order = wc_create_order();

        foreach ($products as $key => $product) {
            $order->add_product(wc_get_product($product['productID']), intval($product['quantity']));
        }

        $order->set_address($address, 'billing');

        $order->calculate_totals();

        $orderID = $order->get_id();

        $order = wc_get_order($orderID); // The WC_Order object instance

        $i = 0;
        foreach ($order->get_items() as $item_id => $item) {

            $productPrice = $products[$i]['price'];

            $quantity = (int) $item->get_quantity(); // product Quantity

            // The new line item price
            $newPrice = $productPrice * $quantity;

            // Set the new price
            $item->set_subtotal($newPrice);
            $item->set_total($newPrice);

            // Make new taxes calculations
            $item->calculate_taxes();

            $item->save(); // Save line item data
            $i++;
        }
        // Make the calculations  for the order and SAVE
        $order->calculate_totals();

        $order->update_status("completed", 'Imported order', TRUE);

        if ($orderID) {
            do_action('ptw_custom_order_created', $orderID);
        }
    }

    /**
     * @param  $data
     * @return null
     */
    public function findProductID($data) {

        if (!isset($data['items']) || count($data['items']) < 1) {
            return [];
        }

        $items = $data['items'];

        $ptwOptions = get_option('ptwOptions');

        $matchProductID = [];

        foreach ($ptwOptions as $key => $option) {
            foreach ($items as $key => $item) {
                if (trim($option['ptw_item_id']) == trim($item['id'])) {
                    array_push($matchProductID, [
                        'productID' => $option['ptw_wc_product_id'],
                        'quantity'  => $item['quantity'],
                        'price'     => $item['price']
                    ]);
                }
            }
        }

        return $matchProductID;
    }
}