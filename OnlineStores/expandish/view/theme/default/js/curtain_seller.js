function curtainSellerCalculateCostAndSave(close_modal) {
    calculateCurtainSellerCost(function(data) {
        displayCurtainSellerCost(data.cost_display);
        populuateHiddenFields(data);
        if (close_modal == true) {
            var symLeft  = $('#currency_symbols_left').val();
            var symRight = $('#currency_symbols_right').val();
            $('#price_value').val(data.cost_display);
            $('#price_value_view').html(symLeft + data.cost_display + symRight);
            price_without_tax = $('#price_without_tax').val();
            if(!price_without_tax){
                    $.ajax({
                    url: "index.php?route=product/product/calculateTax",
                    type: "POST",
                    data: 'cost='+data.cost,
                    dataType: 'json',
                    success: function(resp) {
                        $('#tax_value').val(resp.tax_value);
                        $('#tax_value_display').html(resp.tax);
                    }
                });
            }
            $('#curtainSellerModal').modal('hide');

        }
    });
}

function displayCurtainSellerCost(cost) {
    $('.curtain_seller_final_total').text(cost);
}

function populuateHiddenFields(data) {
    var form_options = '';

    for (var index in data.form_options) {
        if (data.form_options.hasOwnProperty(index)) {
            const element = data.form_options[index];

            form_options += "<input type='hidden' name='curtain_seller[options]["+index+"]' value='"+element+"'>";
            form_options += "<input type='hidden' name='"+index+"' value='"+element+"'>";
        }
    }

    form_options += '<input type="hidden" id="curtain_seller_permission_to_add_to_cart" value="goodtogo">';

    $('.product-add-form form, form.product-add-form ,.product-add-form').append(form_options);
}