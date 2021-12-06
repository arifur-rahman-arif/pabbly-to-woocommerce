<?php

namespace PTW\includes\classes;

class Hooks {
    public function __construct() {
        add_filter('woocommerce_order_number', [$this, 'modifyPabblyOrderNumber'], 99);
    }

    /**
     * @param  $orderID
     * @return mixed
     */
    public function modifyPabblyOrderNumber($orderID) {

        $importedID = get_post_meta($orderID, 'custom_pabbly_order', true);

        if (!is_array($importedID)) {
            return $orderID . ' ' . $importedID;
        }

        return $orderID;
    }
}