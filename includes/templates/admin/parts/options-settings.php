<?php settings_errors();?>

<?php

$itemID = get_option('ptw_item_id') ? sanitize_text_field(get_option('ptw_item_id')) : null;
$productID = get_option('ptw_wc_product_id') ? sanitize_text_field(get_option('ptw_wc_product_id')) : null;
?>

<tr>
    <div class="fieldWrapper">

        <td>
            <strong style="font-size: 15px;">
                <label for="ptw_item_id">Item ID :</label>
            </strong>
            <br>
            <input type="text" name="ptw_item_id" value="<?php echo esc_attr($itemID) ?>" />
        </td>

        <td>
            <strong style="font-size: 15px;">
                <label for="ptw_wc_product_id">WC Product ID :</label>
            </strong>
            <br>
            <select style="min-width: 200px" name="ptw_wc_product_id" id="ptw_wc_product_id">
                <?php echo getProductHTML($productID) ?>
            </select>
        </td>

    </div>
</tr>