$(document).ready(function () {

    $('select').select2({
        minimumResultsForSearch: 10,
    });

    $('#customer-customer').select2({
        minimumResultsForSearch: 10,
        ajax: {
            url: links['customerAutocomplete'],
            data: function (params) {
                return {
                    filter_name: params.term
                };
            }
            ,
            dataType: 'json',
            processResults:

                function (data, params) {
                    return {
                        results: $.map(data, function (item, index) {
                            return {
                                id: item.customer_id,
                                text: item.name,
                                firstname: item.firstname,
                                lastname: item.lastname,
                                email: item.email,
                                customer_group: item.customer_group,
                                customer_group_id: item.customer_group_id,
                                telephone: item.telephone,
                                fax: item.fax,
                                addresses: item.address
                            }
                        })
                    };
                }

            ,
            cache: true
        }
    });

    var customerAddresses = null;

    $('#customer-customer').on('select2:select', function (event) {
        var customerData = event.params.data;

        $('#customer_id').val(customerData.id);
        $('#customer').val(customerData.text);
        $('#firstname').val(customerData.firstname);
        $('#lastname').val(customerData.lastname);
        $('#email').val(customerData.email);
        $('#telephone').val(customerData.telephone);
    });

    $(".clearCustomer").on("click", function () {
        $("#customer-customer").val(null).trigger("change");

        $('#firstname').val('');
        $('#lastname').val('');
        $('#email').val('');
        $('#telephone').val('');
    });

    const select_products = ()=>{
        ids = [];
        for (let i = 0; i < products.length; ++i) {
            ids.push(products[i]['product_id']);

            var newOption = new Option(
            products[i]['name'], 
            products[i]['product_id'], true, true);
            $('#return-products').append(newOption);
            addToTable({
                id: products[i]['product_id'],
                model: products[i]['model'],
                name: products[i]['name'],
                product_id: products[i]['product_id'],
                text: products[i]['name'],
                quantity: products[i]['quantity']
            })
        }
        $('#return-products').val(ids).trigger('change');
    }
    
    select_products();

    $('#customer-customer').val($('#customer_id').val()).trigger('change');


    $('.datepicker').pickadate({
        'format': 'yyyy-mm-dd',
        'formatSubmit': 'yyyy-mm-dd'
    });

    //Reset select dropdown when change Order_id
    $('#order_id').on('change', function(){
        $("#return-products").val('').trigger('change');
        $('#products-details-table tbody').find('tr').remove();
        $('#products-details').addClass('hidden');
    })
    
    //Return products select
    $('#return-products').select2({
        ajax: {
            url: links['getProductsByOrderId'],
            data: function (params) {
                return {
                    order_id: $("#order_id").val()
                };
            },
            dataType: 'json',
            processResults: function (data, params) {
                return {
                    results: $.map(data, function (item, index) {
                        return {
                            id: item.product_id,
                            text: item.name,
                            product_id: item.product_id,
                            name: item.name,
                            option: item.option,
                            model: item.model,
                            price: item.price,
                            total: item.total,
                            image: item.image
                        }
                    })
                };
            },
            cache: true
        }
    });


    $('#return-products').on('select2:select', function (e) {
        selectedProduct = e.params.data;
        addToTable(selectedProduct);
    });

    $('#return-products').on('select2:unselect', function (e) {
        selectedProduct = e.params.data;
        removeFromTable(selectedProduct);
    });


    function addToTable(selectedProduct){
        // console.log(selectedProduct)
        if(selectedProduct){
            //Add new Row
            // selectedProduct.model = selectedProduct.model? selectedProduct.model :'-';
            var template       = $('#product-row').html();
            var templateScript = Handlebars.compile(template);
            var context        = {
                'product_id' : selectedProduct.product_id,
                'name'       : selectedProduct.name,
                'model'      : selectedProduct.model,
                'quantity'   : selectedProduct.quantity,
            };
            var html           = templateScript(context);
            $('#products-details-table tbody').append(html);
        }
        
       if($('#return-products').select2('data').length > 0 && $('#products-details').hasClass('hidden') ){
            $('#products-details').removeClass('hidden');
        }
    }

    function removeFromTable(selectedProduct){
        if(selectedProduct){
            //Remove Row
            $('#product-' + selectedProduct.id).remove();
        }

        if($('#return-products').select2('data').length <= 0 && !$('#products-details').hasClass('hidden')){
            $('#products-details').addClass('hidden');
        } 
    }

    
});
