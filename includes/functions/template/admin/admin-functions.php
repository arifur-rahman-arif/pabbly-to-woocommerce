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
function getProductHTML($productID) {
    $products = getProducts();

    if (!$products || !is_array($products)) {
        return '';
    }

    $optionsHTML = '<option disabled selected>Select a product</option>';

    foreach ($products as $key => $product) {
        $selected = $productID == $product->ID ? "selected" : null;
        $optionsHTML .= '<option ' . $selected . ' value="' . esc_attr($product->ID) . '" >' . esc_html($product->post_title) . '</option>';
    }

    return $optionsHTML;
}

/**
 * @return null
 */
function loadOptionsHTML() {
    $ptwOptions = get_option('ptwOptions');

    if (!$ptwOptions || !is_array($ptwOptions)) {
        return;
    }

    foreach ($ptwOptions as $key => $option) {
        echo '
        <tr data-id="' . ($key + 1) . '">
            <td><input type="text" class="ptw_item_id" value="' . esc_attr($option['ptw_item_id']) . '" /></td>
            <td>
                <select style="min-width: 300px" class="ptw_wc_product_id" id="product_select_box_' . ($key + 1) . '">
                    ' . getProductHTML($option['ptw_wc_product_id']) . '
                </select>
            </td>
            <td>
                <button class="delete_option" data-id="' . ($key + 1) . '">Delete</button>
            </td>
        </tr>
        ';
    }

}