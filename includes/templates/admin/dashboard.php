<style>
.table_container {
    max-width: 600px;
    margin-top: 2rem;
    margin-left: 1rem;
}

.table_container td,
th {
    text-align: left;
    width: 200px;
}

.table_container td button {
    margin-left: 10px;
}


.action_btn {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    margin: 0 -20px;
    margin-top: 2rem;
}

.action_btn button {
    cursor: pointer;
    margin: 0 20px;
}

.hidden_row {
    display: none;
}
</style>

<div class="table_container">
    <table class="ptw_table" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Item ID</th>
                <th>WC Product ID</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <tr class="hidden_row">
                <td><input type="text" class="ptw_item_id" value="" /></td>
                <td>
                    <select style="min-width: 200px" class="ptw_wc_product_id">
                        <?php echo getProductHTML(null) ?>
                    </select>
                </td>

                <td>
                    <button class="delete_option">Delete</button>
                </td>
            </tr>

            <?php loadOptionsHTML()?>
        </tbody>
    </table>
    <div class="action_btn">
        <button class="ptw_save_data">Save</button>
        <button class="ptw_create_row">Add New</button>
        <button class="ptw_delete_all">Delete All</button>
    </div>
</div>