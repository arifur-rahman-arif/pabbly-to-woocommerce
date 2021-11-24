<?php

/**
 * @return mixed
 */
function getProducts() {

    $args = [
        'posts_per_page' => -1,
        'post_type'      => 'product'
    ];

    $products = get_posts($args);

    return $products;
}

/**
 * @param  $saveID
 * @return mixed
 */
function getAdministratorsOptionHTML($saveID) {
    $products = getProducts();

    if (!$products || !is_array($products)) {
        return '';
    }

    $optionsHTML = '<option disabled selected>Select a product</option>';

    foreach ($products as $key => $product) {
        $selected = $saveID == $product->ID ? "selected" : null;
        $optionsHTML .= '<option ' . $selected . ' value="' . esc_attr($product->ID) . '" >' . esc_html($product->post_title) . '</option>';
    }

    return $optionsHTML;
}