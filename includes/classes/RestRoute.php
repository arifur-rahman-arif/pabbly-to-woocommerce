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

        $requestBody = json_decode($requestBody);

        $organizedData = $this->parseData($requestBody->payload);
        $header = $requestBody->orderID;

        preg_match('/#\w+/i', $header, $orderID);

        if (!is_array($orderID) || !$orderID[0]) {
            return;
        }

        $orderID = $orderID[0];

        $orderID = preg_replace('/#/i', '', $orderID);

        $this->createOrder([
            'organizedData' => $organizedData,
            'orderID'       => $orderID
        ]);

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

        $products = [];

        $products = $this->findProductBySkU($data['organizedData']);

        if (!is_array($products) || count($products) < 1) {
            $products = $this->findProductID($data['organizedData']);
        }

        if (!is_array($products) || count($products) < 1) {
            return;
        }

        $firstName = isset($data['organizedData']['billingAddress']) ? explode(" ", $data['organizedData']['billingAddress'][0])[0] : '';
        $lastName = isset($data['organizedData']['billingAddress']) ? explode(" ", $data['organizedData']['billingAddress'][0])[1] : '';
        $company = isset($data['organizedData']['billingAddress']) ? $data['organizedData']['billingAddress'][1] : '';
        $email = isset($data['organizedData']['billingAddress']) ? $data['organizedData']['billingAddress'][6] : '';
        $phone = isset($data['organizedData']['billingAddress']) ? $data['organizedData']['billingAddress'][2] : '';
        $address_1 = isset($data['organizedData']['billingAddress']) ? $data['organizedData']['billingAddress'][3] : '';
        $city = isset($data['organizedData']['billingAddress']) ? explode(" ", $data['organizedData']['billingAddress'][4])[0] : '';
        $state = isset($data['organizedData']['billingAddress']) ? explode(" ", $data['organizedData']['billingAddress'][4])[1] : '';
        $postcode = isset($data['organizedData']['billingAddress']) ? explode(" ", $data['organizedData']['billingAddress'][4])[2] : '';
        $country = 'USA';
        $importedOrderID = $data['orderID'];

        $address = [
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
        ];

        // Now we create the order
        $order = wc_create_order();

        foreach ($products as $key => $product) {
            $order->add_product(wc_get_product($product['productID']), intval($product['quantity']));
        }

        $order->set_address(
            [
                'first_name' => "Brother's Global, Inc",
                // 'last_name'  => $lastName,
                // 'company'    => $company,
                'email'      => 'cs@uscd.com',
                // 'phone'      => $phone,
                'address_1'  => '2065 Baker Way',
                'city'       => 'KENNESAW',
                'state'      => 'GA',
                'postcode'   => '30144',
                'country'    => 'USA'
            ],
            'billing'
        );
        $order->set_address($address, 'shipping');

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

        $order->update_status("processing", 'Imported order', true);

        update_post_meta($orderID, 'custom_pabbly_order', $importedOrderID);

        if ($orderID) {
            do_action('ptw_custom_order_created', $orderID);
        }
    }

    /**
     * @param $data
     */
    public function findProductBySkU($data) {

        if (count($data['items']) < 1) {
            return [];
        }

        $items = $data['items'];

        $matchProductID = [];

        foreach ($items as $key => $item) {
            $args = [
                'post_type'      => 'product',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                's'              => $item['id']
            ];

            $posts = get_posts($args);

            if ($posts) {
                foreach ($posts as $key => $post) {

                    $product = wc_get_product($post->ID);

                    array_push($matchProductID, [
                        'productID' => $post->ID,
                        'quantity'  => $item['quantity'],
                        'price'     => $product->get_price()
                    ]);
                }
            }

        }

        return $matchProductID;
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