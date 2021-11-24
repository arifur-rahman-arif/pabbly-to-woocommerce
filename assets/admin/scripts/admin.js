var $ = jQuery.noConflict();

(function () {
    let createRow = $(".ptw_create_row");
    let saveData = $(".ptw_save_data");
    let deleteAll = $(".ptw_delete_all");

    events();

    function events() {
        createRow.click(duplicateRow);
        saveData.click(saveOptionsData);
        deleteAll.click(deleteOptionData);
        $(document).on("click", ".delete_option", deleteOptionData);
    }

    function duplicateRow(e) {
        e.preventDefault();
        let table = $(".ptw_table");

        let copyElement = table.find(".hidden_row").clone(true);

        let tableRows = table.find("tbody tr:not(.hidden_row)");
        copyElement.removeClass("hidden_row");
        copyElement.attr("data-id", tableRows.length + 1);
        copyElement.find(".delete_option").attr("data-id", tableRows.length + 1);

        table.find("tbody").append(copyElement);
    }

    function saveOptionsData(e) {
        e.preventDefault();

        let organizedData = organizeData();

        if (!organizedData.length) return alert("There is no data to save.");

        $.ajax({
            type: "POST",
            url: localizeData.ajaxURL,
            data: {
                action: "ptw_save_options",
                organizedData,
            },
            success: function (response) {
                response = JSON.parse(response);

                if (response.response_type === "success") {
                    alert("Data saved successfully");
                } else {
                    alert("Something went wrong");
                    console.error(response.output);
                }
            },
            error: function (error) {
                console.error(error);
            },
        });
    }

    function organizeData() {
        let table = $(".ptw_table");

        let tableRows = table.find("tbody tr:not(.hidden_row)");

        if (!tableRows.length) return [];

        let organizedData = [];

        $.each(tableRows, function (index, rowElement) {
            organizedData.push({
                id: parseInt($(rowElement).attr("data-id")),
                ptw_item_id: $(rowElement).find(".ptw_item_id").val(),
                ptw_wc_product_id: $(rowElement).find(".ptw_wc_product_id").val(),
            });
        });

        return organizedData;
    }

    function deleteOptionData(e) {
        e.preventDefault();

        let target = $(e.currentTarget);

        let id = parseInt(target.attr("data-id"));

        if (!id && target.hasClass("delete_option")) return console.log("No id is found");

        let deleteAction = "single_delete";

        if (target.hasClass("ptw_delete_all")) deleteAction = "delete_all";

        $.ajax({
            type: "POST",
            url: localizeData.ajaxURL,
            data: {
                action: "ptw_delete_option",
                deleteAction,
                id,
            },
            success: function (response) {
                response = JSON.parse(response);

                if (response.response_type === "success") {
                    if (response.type === "delete_all") {
                        let table = $(".ptw_table");

                        let tableRows = table.find("tbody tr:not(.hidden_row)");

                        tableRows.hide();
                    } else {
                        target.parent().parent().hide();
                        alert(response.output);
                    }
                } else {
                    alert(response.output);
                    console.error(response.output);
                }
            },
            error: function (error) {
                console.error(error);
            },
        });
    }
})();
