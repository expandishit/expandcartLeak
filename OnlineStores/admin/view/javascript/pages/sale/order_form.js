var requiredSpanDom="<span class='required' style='color:red'>*</span>";

function fillFields( arrays )
{
    for (var i = arrays.length - 1; i >= 0; i--)
    {
        $( '#' + arrays[i].destID ).val( $('#' + arrays[i].sourceID).val() );
    }
}

function reloadProductsSelect()
{
    $('#products-products').select2('destroy');

    let groupId = 0;
    if (isNaN(parseInt($('#customer_group_id_hidden').val())) == false) {
        groupId = $('#customer_group_id_hidden').val();
    }


    function format(item) {
        return $(`<span><img class='flag' src='${item.image}' height="30"/> ${item.name}</span>`);
    }

    function formatSelection (item) {
        return item.name;
    }

    $('#products-products').select2({
        ajax: {
            url: links['productsAutocomplete'],
            data:
                function (params) {
                    return {
                        filter_name: params.term,
                        filter_status: 1,
                        customer_group_id: groupId
                    };
                },
            dataType: 'json',
            processResults:

                function (data, params) {
                    return {
                        results: $.map(data, function (item, index) {
                            return {
                                id: item.product_id,
                                // text: item.name,
                                product_id: item.product_id,
                                name: item.name,
                                option: item.option,
                                model: item.model,
                                price: item.price,
                                total: item.total,
                                image: item.image,
                                discount_price: item.discount_price,
                                discount_quantity: item.discount_quantity,
                                quantity:item.quantity,
                                unlimited:item.unlimited
                            }
                        })
                    };
                }

            ,
            cache: true
        },
        templateResult: format,
        templateSelection: formatSelection,
    });
}

function loadZonesForCountry(country_id, dest_id, selected_value)
{
    $.ajax({
        url: "sale/order/country",
        type: "GET",
        data: {'country_id': country_id},
        success: function (resp)
        {
            var resp = JSON.parse(resp);
            var html = '';

            for (var i = resp.zone.length - 1; i >= 0; i--)
            {
                html += '<option value="' + resp.zone[i].zone_id + '">' + resp.zone[i].name + '</option>';
            }

            $('#' + dest_id).html(html);
            $('#' + dest_id).select2();

            if ( selected_value === undefined )
            {
                selected_value = $('#' + dest_id + ' option:first-child').val();
            }

            $('#' + dest_id).val(selected_value).trigger('change');
        }
    });
}

function loadAreasForZone(zone_id, dest_id, selected_value)
{
    $.ajax({
        url: "sale/order/zone",
        type: "GET",
        data: {'zone_id': zone_id},
        success: function (resp)
        {
            var resp = JSON.parse(resp);
            var html = '';
            if(resp.lenth > 0){
                for (var i = resp.area.length - 1; i >= 0; i--)
                {
                    html += '<option value="' + resp.area[i].area_id + '">' + resp.area[i].name + '</option>';
                }

                $('#' + dest_id).html(html);
                $('#' + dest_id).select2();

                if ( selected_value === 'undefined' )
                {
                    selected_value = $('#' + dest_id + ' option:first-child').val();
                }

                $('#' + dest_id).val(selected_value).trigger('change');

            }

        }
    });
}


$(document).ready(function ()
{

    // Saving wizard state
    $(".steps-state-saving").steps({
        headerTag: "h6",
        bodyTag: "fieldset",
        titleTemplate: '<span class="number">#index#</span> #title#',
        autoFocus: true,
        labels: {
            next: locales['btn_next'],
            previous: locales['btn_previous'],
            finish: locales['btn_finish']
        },
        onStepChanging: function (event, current, next) {
            if (current > next) {
                return true;
            }

            var $parent = $(".steps-state-saving fieldset:eq(" + current + ")");

            if (current == 0) {

                validation = syncValidation('personal', $parent);

                if ( validation == true )
                {
                    fillFields([
                        {'destID': 'payment_firstname', 'sourceID': 'firstname'},
                        { 'destID': 'payment_lastname', 'sourceID': 'lastname' },
                        { 'destID': 'payment_telephone', 'sourceID': 'telephone' }
                    ]);
                }

                return validation;

            } else if (current == 1) {

                validation = syncValidation('payment', $parent);

                if ( validation == true )
                {
                    fillFields([
                        {'destID': 'shipping_firstname', 'sourceID': 'payment_firstname'},
                        { 'destID': 'shipping_lastname', 'sourceID': 'payment_lastname' },
                        { 'destID': 'shipping_address_1', 'sourceID': 'payment_address_1' },
                        { 'destID': 'shipping_address_2', 'sourceID': 'payment_address_2' },
                        { 'destID': 'shipping_company', 'sourceID': 'payment_company' },
                        { 'destID': 'shipping_postcode', 'sourceID': 'payment_postcode' },
                        { 'destID': 'shipping_city', 'sourceID': 'payment_city' },
                    ]);

                    $('#shipping_country_id').val( $('#payment_country_id').val() ).trigger('change');
                    $('#shipping_zone_id').val( $('#payment_zone_id').val() ).trigger('change');

                    loadZonesForCountry( $('#shipping_country_id').val() , 'shipping_zone_id', $('#payment_zone_id').val());

                    loadAreasForZone( $('#shipping_zone_id').val() , 'payment_area_id', $('#shipping_area_id').val());
                }

                return validation;

            } else if (current == 2) {
                return syncValidation('shipping', $parent);
            } else if (current == 3) {
                return calculateTotal();
                reloadProductsSelect();
            } else if (current == 4) {
                return calculateTotal();
                reloadProductsSelect();
            }
        },
        onFinished: function (event, currentIndex) {
            return calculateTotal('submit');
        }
    });

    var switchery_after = document.querySelector('.switchery-after');
    if(isset(switchery_after)){
        var switchery = new Switchery(switchery_after);
    }

    $('select').select2();

    $('.touchspinney').TouchSpin();
    $('#products-quantity').TouchSpin({
        max: Infinity
    });

    $('#customer-customer').select2({
        minimumResultsForSearch: 10,
        ajax: {
            url: links['customerAutocomplete'],
            data:

                function (params) {
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

        $('#customer_id_hidden').val( customerData.id );
        $('#customer_group_id_hidden').val( customerData.customer_group_id );

        $('#firstname').val(customerData.firstname);
        $('#lastname').val(customerData.lastname);
        $('#email').val(customerData.email);
        $('#telephone').val(customerData.telephone);
        $('#fax').val(customerData.fax);
        $('#customer_group_id').val(customerData.customer_group_id).trigger('change');

        customerAddresses = customerData.addresses;

        var data = $.map(customerAddresses, function (obj) {
            obj.id = obj.address_id;
            obj.text = obj.firstname + ' ' + obj.lastname + ' ' + obj.address_1 + ' ' + obj.zone + ' ' + obj.country;

            return obj;
        });

        $('#payment_address, #shipping_address').select2().empty();

        var defaultData = {
            id: '',
            text: locales['text_select']
        };

        $('#payment_address').select2({
            minimumResultsForSearch: 10,
            data: data
        });

        $('#shipping_address').select2({
            minimumResultsForSearch: 10,
            data: data
        });

        $('#payment_address').prepend(new Option(defaultData.text, defaultData.id, true, true)).trigger('change');
        $('#shipping_address').prepend(new Option(defaultData.text, defaultData.id, true, true)).trigger('change');

        $('#payment_address').on('select2:select', function (pEvent) {
            var paymentAddress = pEvent.params.data;

            $('#shipping_address').val( $(this).val() ).trigger('change');
            $('#payment_firstname').val(paymentAddress.firstname);
            $('#payment_lastname').val(paymentAddress.lastname);
            $('#payment_address_1').val(paymentAddress.address_1);
            $('#payment_address_2').val(paymentAddress.address_2);
            $('#payment_company').val(paymentAddress.company);
            $('#payment_company_id').val(paymentAddress.company_id);
            $('#payment_tax_id').val(paymentAddress.tax_id);
            $('#payment_telephone').val(paymentAddress.telephone);
            $('#payment_postcode').val(paymentAddress.postcode);
            $('#payment_city').val(paymentAddress.city);
            $('#payment_country_id').val(paymentAddress.country_id).trigger('change');
            $('#payment_zone_id').val(paymentAddress.zone_id).trigger('change');
            $('#payment_zone_id').prepend(
                new Option(paymentAddress.zone, paymentAddress.zone_id, true, false)
            );
            $('#payment_area_id').val(paymentAddress.area_id).trigger('change');
            $('#payment_area_id').prepend(
                new Option(paymentAddress.area, paymentAddress.area_id, true, false)
            );
        });

        $('#shipping_address').on('select2:select', function (sEvent) {
            var shippingAddress = sEvent.params.data;

            $('#shipping_firstname').val(shippingAddress.firstname);
            $('#shipping_lastname').val(shippingAddress.lastname);
            $('#shipping_address_1').val(shippingAddress.address_1);
            $('#shipping_address_2').val(shippingAddress.address_2);
            $('#shipping_company').val(shippingAddress.company);
            $('#shipping_company_id').val(shippingAddress.company_id);
            $('#shipping_tax_id').val(shippingAddress.tax_id);
            $('#shipping_postcode').val(shippingAddress.postcode);
            $('#shipping_city').val(shippingAddress.city);
            $('#shipping_country_id').val(shippingAddress.country_id).trigger('change');
            $('#shipping_zone_id').val(shippingAddress.zone_id).trigger('change');
            $('#shipping_zone_id').prepend(
                new Option(shippingAddress.zone, shippingAddress.zone_id, true, false)
            );
            $('#shipping_area_id').val(shippingAddress.area_id).trigger('change');
            $('#shipping_area_id').prepend(
                new Option(shippingAddress.area, shippingAddress.area_id, true, false)
            );
        });
    });

    loadZonesForCountry( $('#payment_country_id').val(), 'payment_zone_id', values['payment_zone_id'] );

    loadAreasForZone( $('#payment_zone_id').val(), 'payment_area_id', values['payment_area_id'] );

    $('#shipping_zone_id').select2({
        ajax: {
            url: 'sale/order/country',
            data: function (params) {
                var $value = $('#shipping_country_id').val();

                return {
                    country_id: $value
                };
            },
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: $.map(data.zone, function (item, index) {
                        return {
                            id: item.zone_id,
                            text: item.name
                        };
                    })
                };
            },
            cache: true
        }
    });

    $('#shipping_area_id').select2({
        ajax: {
            url: 'sale/order/zone',
            data: function (params) {
                var $value = $('#shipping_zone_id').val();

                return {
                    zone_id: $value
                };
            },
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: $.map(data.area, function (item, index) {
                        return {
                            id: item.area_id,
                            text: item.name
                        };
                    })
                };
            },
            cache: true
        }
    });

    $(".clearCustomer").on("click", function () {
        $("#customer-customer").val(null).trigger("change");

        $('#firstname').val('');
        $('#lastname').val('');
        $('#email').val('');
        $('#telephone').val('');
        $('#fax').val('');
        $('#customer_group_id').val(null).trigger('change');

        customerAddresses = null;

        $('#payment_address, #shipping_address').select2().empty();
    });

    reloadProductsSelect();

    var selectedProduct = null;

    $('#products-products').on('select2:select', function (event) {
        selectedProduct = event.params.data;

        $('#options-container').html('');

        $('#div-quantity').show();
        q = selectedProduct['quantity'];
        if (q != 0){
            $( "#products-quantity" ).val(1);
            $('#error_product_or_quantity').fadeOut(0);
        }else {
            $('#error_product_or_quantity').fadeIn(300);
        }
        if(selectedProduct['unlimited']==1)
        {
            $( "#products-quantity" ).trigger("touchspin.updatesettings", {max: Infinity});
        }
        else
        {
            $( "#products-quantity" ).trigger("touchspin.updatesettings", {max: q});
        }

        if (selectedProduct['option'].length != '') {

            var optionsTemplate = getOptionsTemplate(selectedProduct['option']);

            $('#options-container').html(optionsTemplate);
            $('#options-container select').select2({
                minimumResultsForSearch: 10
            });
        }
    });

    $('#products-add-product').click(function (e) {
        e.preventDefault();

        $('#error_product_or_quantity').fadeOut(0);

        var product_id = $('#products-products').children(':selected').val();
        var qty = $('#products-quantity').val();

        if (product_id == '-1' || selectedProduct == null || parseInt(qty) <= 0 || ! $.isNumeric( qty ))
        {
            $('#error_product_or_quantity').fadeIn(300);
            return;
        }

        var validOptions = true;

        var optionsObject = [];
        var optionsCollection = [];
        var option_number = 1;
        var optionsTotals = 0;
        var optionValue = [];
        var optionName = [];
        var options_arr =[];
        var options_arr_final =[];
        $('#options-container').find('input,select,textarea').each(function () {

            var optionObject = {};

            var optionId = this.name.match(/option\[(\d+)]/);

            if(option_number == 1 || optionsCollection.indexOf(optionId[1]) < 0){
                optionsCollection.push(optionId[1]);
                option_number++;
            }
            else{
                option_number++;
                return;
            }

            var isRequired = 0;
            for (op in selectedProduct['option']) {
                var option = selectedProduct['option'][op];

                if (optionId[1] == option['product_option_id']) {
                    isRequired = option['required'];
                    optionObject['name'] = option['name'];
                    optionObject['type'] = option['type'];
                    optionObject['product_option_id'] = option['product_option_id'];
                }
            }
            var name=this.name;
            if(( $(this).is(":radio") || $(this).is(":checkbox")) && $("input[name='"+name+"']:checked").length == 0 && isRequired == 1){

                validOptions = false;
                return false;
            }
            else if (this.value == '' && isRequired == 1) {
                validOptions = false;
                return false;
            }

            // Push checked option values if the option is checkbox or radio
            if($(this).is(":checkbox") || $(this).is(":radio")){
                optionObject['product_option_value_id']=[];
                $("input[name='"+name+"']:checked").each(function(){
                    optionObject['product_option_value_id'].push(this.value);
                    optionsTotals += parseFloat($(this).data('price_prefix') + $(this).data('price'));
                });
            }else if($(this).is("select")){
                optionObject['product_option_value_id'] = this.value;
                var selectedOption =  $("option:selected", this);
                optionsTotals += parseFloat(selectedOption.data('price_prefix') + selectedOption.data('price'));
            }
            else{
                optionObject['product_option_value_id'] = this.value;
            }

            if($(this).is(":radio")){
                optionObject['value']=$("input[name='"+name+"']:checked").parent().text();
            }
            if($(this).is(":checkbox")){
                if( $("input[name='"+name+"']:checked").length > 1){
                    optionObject['value']=[];
                    $("input[name='"+name+"']:checked").each(function(){
                        optionObject['value'].push($(this).parent().text());
                    });
                }
                else{
                    optionObject['value']=$("input[name='"+name+"']:checked").parent().text();
                }
            }
            if($(this).is(":text") || $(this).is(":file") || $(this).is("textarea")){
                optionObject['value'] = $(this).val();
            }

            if($(this).is("select")){
                optionObject['value'] = $('option:selected', $(this)).text();
            }


            if(isRequired == 1){
                optionsObject.push(optionObject);
            }
            else if( ($(this).is(":radio") || $(this).is(":checkbox"))){
                if($("input[name='"+name+"']:checked").length != 0 )
                    optionsObject.push(optionObject);
            }
            else if(this.value != ""){
                optionsObject.push(optionObject);
            }

            optionName += optionObject['name']+",";
            optionValue += optionObject['product_option_value_id']+",";
            options_arr = optionName + optionValue;
            options_arr_final =  options_arr.split(",");
            options_arr_final.sort();
            options_arr_final = options_arr_final.map(function(x){ return x.toUpperCase(); })
            options_arr_final = options_arr_final.filter(function(v){return v!==''});
        });
        if (!validOptions) {
            console.log('invalid options');
            return false;
        }

        var $table = $('#products-grid');

        var firstToBeRemoved = false;
        var product_id_new = selectedProduct['product_id'];
        var $tr = $table.find('tbody tr:last-child');
        var find = 0;
        var text_array = '';
        var myArray = [];
        $("#"+product_id_new).find('td .product-options small.optionValue').each(function(index,total) {
            text_array += $(this).attr("data-name")+":"+$(this).attr("data-id")+":";
        });
        myArray =  text_array.trim().split(":");
        myArray = myArray.sort();
        myArray = myArray.map(function(x){ return x.toUpperCase(); })
        myArray = myArray.filter(function(v){return v!==''});

        $table.find('tbody tr').each(function() {
            options_arr_final.sort();
            let myArrayString = myArray.toString();
            myArray = myArray.filter(function(v){return v!==''});
            myArray = myArrayString.trim().split(",");
            myArray = myArray.sort();
            myArray = myArray.map(function(x){ return x.toUpperCase(); })
            myArray = myArray.filter(function(v){return v!==''});
            console.log(myArray,"11111");
            console.log(options_arr_final,"2222");
            var result = 0;
            if(JSON.stringify(myArray) == JSON.stringify(options_arr_final)){
                result = 0;
            }else{
                result = 1;

            }


            if(this.id == selectedProduct['product_id'] && result == 0) {

                find++;
            }
        });

        if(find == 0) {

            if ($table.find('tbody tr').length < 1) {

                firstToBeRemoved = true;

                var $_tr = $('<tr id="' + selectedProduct['product_id'] + '">' +
                    '<td class="no-padding-right" style="width: 45px;">' +
                    '<a href="#">' +
                    '<img src="" height="60" class="product-thumb">' +
                    '</a>' +
                    '</td>' +
                    '<td>' +
                    '<a href="#" class="text-semibold product-name"></a>' +
                    '<div class="product-options"></div>' +
                    '<div class="hide product-downloads"></div>' +
                    '</td>' +
                    '<td>' +
                    '<h6 class="no-margin text-semibold product-model"></h6>' +
                    '</td>' +
                    '<td>' +
                    '<h6 class="no-margin text-semibold product-quantity"></h6>' +
                    '</td>' +
                    '<td>' +
                    '<label class="label label-primary product-price"></label>' +
                    '</td>' +
                    '<td style="display: none;">' +
                    '<label class="label label-primary product-total"></label>' +
                    '</td>' +
                    '<td>' +
                    '<button onclick="$(this).parents(\'tr\').remove();" class="btn btn-danger products-remove-product" data-prod-id="">' +
                    '<i class="icon-trash-alt"></i>' +
                    '</button>' +
                    '<div class="productInputs">' +
                    '<input type="hidden" name="order_product[0][order_product_id]" ' +
                    'value="" class="order_product_id"/>' +
                    '<input type="hidden" name="order_product[0][product_id]" ' +
                    'value="" class="product_id"/>' +
                    '<input id="order_product_name" type="hidden" name="order_product[0][name]" ' +
                    'value="" class="name"/>' +
                    '<input type="hidden" name="order_product[0][product_status]" ' +
                    'value="" class="product_status"/>' +

                    '<input type="hidden" name="order_product[0][soft_delete_status]" ' +
                    'value="0" class="soft_delete_status"/>' +

                    '<input type="hidden" name="order_product[0][total]" ' +
                    'value="" class="total"/>' +
                    '<input type="hidden" name="order_product[0][tax]" ' +
                    'value="" class="tax"/>' +
                    '<input type="hidden" name="order_product[0][reward]" ' +
                    'value="" class="reward"/>' +
                    '<input type="hidden" name="order_product[0][price]" ' +
                    'value="" class="price"/>' +
                    '<input type="hidden" name="order_product[0][quantity]" ' +
                    'value="" class="quantity"/>' +
                    '<input type="hidden" name="order_product[0][model]" ' +
                    'value="" class="model"/>' +
                    '<input type="hidden" name="order_product[0][added_by_user_type]" ' +
                    'value="admin" class="added_by_user_type"/>' +
                '<input type="hidden" name="order_product[0][old]" ' +
                'value="1" class="old"/>' +
                    '</div>' +
                    '</td>' +
                    '</tr>');

                $table.find('tbody').append($_tr);
            }
            if ($tr.length == 0){
                $tr = $table.find('tbody tr:last-child');
            }
            var $copy = $tr.clone();
            $copy.attr('id',selectedProduct['product_id']);
            var inputIndex;

            $copy.css("background", "rgb(226, 241, 239)");

            $copy.find('[name]').each(function (el, index) {
                var name;

                name = this.name.replace(/order_product\[(\d+)]/g, function (m0, m1) {
                    inputIndex = parseInt(m1) + 1;

                    return 'order_product[' + inputIndex + ']';
                });

                this.name = name;
                this.value = '';

            });

            $copy.find('.productInputs input.added_by_user_type').val('admin');

            $copy.find('.product-thumb').each(function (el, index) {
                $(this).attr('src', selectedProduct['image']);
            });

            $copy.find('.product-name').each(function (el, index) {
                $(this).text(selectedProduct['name']);
            });

            $copy.find('.product-model').each(function (el, index) {
                $(this).text(selectedProduct['model']);
            });

            $copy.find('.product-quantity').each(function (el, index) {
                $(this).text($('#products-quantity').val());
            });
            $copy.find('.delete_status').each(function (el, index) {
                $(this).val('not_deleted');
            });

            $copy.find('.product-price').each(function (el, index) {
                let price = 0;
                if (
                    typeof selectedProduct['discount_price'] != 'undefined' &&
                    parseFloat(selectedProduct['discount_price']) > 0
                ) {
                    if (
                        selectedProduct['discount_quantity'] > 1 &&
                        parseInt($('#products-quantity').val()) > selectedProduct['discount_quantity']
                    ) {
                        price = parseFloat(selectedProduct['discount_price']);
                    } else {
                        price = parseFloat(selectedProduct['price']);
                    }
                } else {
                    price = parseFloat(selectedProduct['price']);
                }
                // product price with it is options
            price  = parseFloat(price) + parseFloat(optionsTotals);
            selectedProduct['price'] = price;
            $(this).text(price);
        });

        $copy.find('.productInputs input').each(function (el, index) {
            var hiddenInputValue = 0;

            if (typeof selectedProduct[$(this).attr('class')] != 'undefined') {
                hiddenInputValue = selectedProduct[$(this).attr('class')];
            }

            if ($(this).attr('class') == 'quantity') {
                $(this).val($('#products-quantity').val());
            } else {
                $(this).val(hiddenInputValue);
            }
            });

            $copy.find('.product-total').each(function (el, index) {
                $(this).text(parseFloat(selectedProduct['total']*$('#products-quantity').val()));
            });

            $copy.find('.product-options').html(function () {
                var tmp = '';
                for (i in optionsObject) {
                    var option = optionsObject[i];

                    tmp += '- <small data-name="'+option['name']+'" data-id="'+option['product_option_value_id']+'" class="optionValue">' + option['name'] + ': ' + option['value'] + '</small><br />'
                    tmp += '<input type="hidden" ' +
                        'name="order_product[' + inputIndex + '][order_option][' + i + '][order_option_id]" ' +
                        'value="' + option['order_option_id'] + '"/>' +
                        '<input type="hidden" ' +
                        'name="order_product[' + inputIndex + '][order_option][' + i + '][product_option_id]" ' +
                        'value="' + option['product_option_id'] + '"/>' +
                        '<input type="hidden" ' +
                        'name="order_product[' + inputIndex + '][order_option][' + i + '][product_option_value_id]" ' +
                        'value="' + option['product_option_value_id'] + '"/>' +
                        '<input type="hidden" ' +
                        'name="order_product[' + inputIndex + '][order_option][' + i + '][name]" ' +
                        'value="' + option['name'] + '"/>' +
                        '<input type="hidden" ' +
                        'name="order_product[' + inputIndex + '][order_option][' + i + '][value]" ' +
                        'value="' + option['value'] + '"/>' +
                        '<input type="hidden" ' +
                        'name="order_product[' + inputIndex + '][order_option][' + i + '][type]" ' +
                        'value="' + option['type'] + '" />';
                }

                return tmp;
            });


            $tr.after($copy);

            if (firstToBeRemoved == true) {
                $tr.remove();
            }
        }
        else {
            var New_val =  parseFloat($("#"+selectedProduct['product_id']).find('.product-quantity').html());
            var old_Val =  parseFloat($("#products-quantity").val());
            New_val +=  old_Val;
            $("#"+selectedProduct['product_id']).find(".productInputs .quantity").val(New_val);
            $("#"+selectedProduct['product_id']).find('.product-quantity').html(New_val);
        }
        find = 0;




        // var $copy = $tr.clone();
        // $copy.attr('id',selectedProduct['product_id']);


//            $totalCopy = $copy.clone();
//
//            $totalCopy.find('td:first-child').remove();
//            $totalCopy.find('td:last-child').remove();
//
//            $('#total-products tbody tr:last-child').after($totalCopy);

        $('#div-quantity').hide();
        $('#options-container').html('');
        $('#products-products').val('')

    });

    var getOptionsTemplate = function (options) {
        html = '';

        for (i = 0; i < options.length; i++) {
            option = options[i];

            if (option['type'] == 'select') {
                html += '<div id="option-' + option['product_option_id'] + '">';

                html += '<label class="control-label">' + option['name'];
                if (option['required'] == 1) {
                    html += requiredSpanDom;
                }
                html+= '</label>';
                html += '<select class="options" data-name="'+option['name']+'" name="option[' + option['product_option_id'] + ']">';
                html += '<option value=""><?php echo $text_select; ?></option>';

                for (j = 0; j < option['option_value'].length; j++) {
                    option_value = option['option_value'][j];
                    let option_price = 0 ;
                    if(option_value['price']){
                        priceMatch =option_value['price'].match(/\d+(?:\.\d+)?/g);
                        if(priceMatch.length > 0)
                            option_price = priceMatch[0];
                    }

                    html += '<option data-price ="'+ option_price +'" data-price_prefix="' + option_value['price_prefix'] + '"   data-quantity="'+option_value['quantity']+'" value="' + option_value['product_option_value_id'] + '">' + option_value['name'];

                    if (option_value['price']) {
                        html += ' (' + option_value['price_prefix'] + option_value['price'] + ')';
                    }

                    html += '</option>';
                }

                html += '</select>';
                html += '</div>';
                html += '<br />';
            }

            if (option['type'] == 'radio') {
                html += '<div id="option-' + option['product_option_id'] + '">';

                html += '<label class="control-label">' + option['name'];
                if (option['required'] == 1) {
                    html += requiredSpanDom;
                }
                html+= '</label> <br>';
                for (j = 0; j < option['option_value'].length; j++) {
                    option_value = option['option_value'][j];

                    let option_price = 0 ;
                    if(option_value['price']){
                        priceMatch =option_value['price'].match(/\d+(?:\.\d+)?/g);
                        if(priceMatch.length > 0)
                            option_price = priceMatch[0];
                    }

                    html += '<div class="radio">';
                    html += '<label for="option-value-' + option_value['product_option_value_id']+'"><input class="options" data-price ="'+ option_price +'" data-price_prefix="' + option_value['price_prefix'] + '" data-name="'+option['name']+'" data-quantity="'+option_value['quantity']+'"  type="radio" name="option[' + option['product_option_id'] + '][]" value="' + option_value['product_option_value_id'] + '" id="option-value-' + option_value['product_option_value_id'] + '" />';
                    html += option_value['name'];

                    if (option_value['price']) {
                        html += ' (' + option_value['price_prefix'] + option_value['price'] + ')';
                    }

                    html += '</label>';
                    html += '</div>';
                }

                html += '</div>';
                html += '<br />';
            }

            if (option['type'] == 'checkbox') {
                html += '<div id="option-' + option['product_option_id'] + '">';
                html += '<label class="control-label">' + option['name'];
                if (option['required'] == 1) {
                    html += requiredSpanDom;
                }
                html+= '</label> <br>';

                for (j = 0; j < option['option_value'].length; j++) {
                    option_value = option['option_value'][j];

                    html += '<div class="checkbox">';
                    let option_price = 0 ;
                    if(option_value['price']){
                        priceMatch =option_value['price'].match(/\d+(?:\.\d+)?/g);
                        if(priceMatch.length > 0)
                            option_price = priceMatch[0];
                    }
                    html += '<label for="option-value-' + option_value['product_option_value_id']+'"><input class="options" data-quantity="'+option_value['quantity']+'" type="checkbox" name="option[' + option['product_option_id'] + '][]" value="' + option_value['product_option_value_id'] + '" id="option-value-' + option_value['product_option_value_id'] + '" data-price ="'+ option_price +'" data-price_prefix="' + option_value['price_prefix'] + '"  />';
                    html += option_value['name'];

                    if (option_value['price']) {
                        html += ' (' + option_value['price_prefix'] + option_value['price'] + ')';
                    }

                    html += '</label>';
                    html += '</div>';
                }

                html += '</div>';
                html += '<br />';
            }

            if (option['type'] == 'image') {
                html += '<div id="option-' + option['product_option_id'] + '">';
                html += '<label class="control-label">' + option['name'];
                if (option['required'] == 1) {
                    html += requiredSpanDom;
                }
                html+= '</label>';

                html += '<select class="options" data-name="'+option['name']+'" name="option[' + option['product_option_id'] + ']">';
                html += '<option value=""><?php echo $text_select; ?></option>';

                for (j = 0; j < option['option_value'].length; j++) {
                    option_value = option['option_value'][j];

                    html += '<option data-quantity="'+option_value['quantity']+'" value="' + option_value['product_option_value_id'] + '">' + option_value['name'];

                    if (option_value['price']) {
                        html += ' (' + option_value['price_prefix'] + option_value['price'] + ')';
                    }

                    html += '</option>';
                }

                html += '</select>';
                html += '</div>';
                html += '<br />';
            }

            if (option['type'] == 'text') {
                html += '<div id="option-' + option['product_option_id'] + '">';


                html += '<label class="control-label">' + option['name'];
                if (option['required'] == 1) {
                    html += requiredSpanDom;
                }
                html+= '</label>';
                html += '<input type="text" class="form-control" name="option[' + option['product_option_id'] + ']" value="' + option['option_value'] + '" />';
                html += '</div>';
                html += '<br />';
            }

            if (option['type'] == 'textarea') {
                html += '<div id="option-' + option['product_option_id'] + '">';

                html += '<label class="control-label">' + option['name'];
                if (option['required'] == 1) {
                    html += requiredSpanDom;
                }
                html+= '</label>';
                html += '<textarea name="option[' + option['product_option_id'] + ']" cols="40" rows="5">' + option['option_value'] + '</textarea>';
                html += '</div>';
                html += '<br />';
            }

            if (option['type'] == 'file') {
                html += '<div id="option-' + option['product_option_id'] + '">';
                html += '<label class="control-label">' + option['name'];
                if (option['required'] == 1) {
                    html += requiredSpanDom;
                }
                html+= '</label>';
                html += '<a id="button-option-' + option['product_option_id'] + '" class="button btn btn-primary"><?php echo $button_upload; ?></a>';
                html += '<input type="hidden" name="option[' + option['product_option_id'] + ']" value="' + option['option_value'] + '" />';
                html += '</div>';
                html += '<br />';
            }

            if (option['type'] == 'date') {
                html += '<div id="option-' + option['product_option_id'] + '">';

                html += '<label class="control-label">' + option['name'];
                if (option['required'] == 1) {
                    html += requiredSpanDom;
                }
                html+= '</label>';
                html += '<input type="text" name="option[' + option['product_option_id'] + ']" value="' + option['option_value'] + '" class="date" />';
                html += '</div>';
                html += '<br />';
            }

            if (option['type'] == 'datetime') {
                html += '<div id="option-' + option['product_option_id'] + '">';

                html += '<label class="control-label">' + option['name'];
                if (option['required'] == 1) {
                    html += requiredSpanDom;
                }
                html+= '</label>';

                html += '<input type="text" name="option[' + option['product_option_id'] + ']" value="' + option['option_value'] + '" class="datetime" />';
                html += '</div>';
                html += '<br />';
            }

            if (option['type'] == 'time') {
                html += '<div id="option-' + option['product_option_id'] + '">';
                html += '<label class="control-label">' + option['name'];
                if (option['required'] == 1) {
                    html += requiredSpanDom;
                }
                html+= '</label>';
                html += '<input type="text" name="option[' + option['product_option_id'] + ']" value="' + option['option_value'] + '" class="time" />';
                html += '</div>';
                html += '<br />';
            }
        }

        return html;
    };

    $(document).on('change', '.options', function(){

        let q = $(this).find(':selected').data('quantity');
        if (q == null){
            q = $(this).data('quantity');
        }
        if (q != 0 && !($( "#products-quantity" ).val() == 0 && $("#products-quantity").data('name') != $(this).data('name')) ){
            $( "#products-quantity" ).val(1);
            $('#error_product_or_quantity').fadeOut(0);
        }else {
            $('#error_product_or_quantity').fadeIn(300);
            q = 0;
        }

        $('#products-quantity').attr('data-name', $(this).data('name'));

        $( "#products-quantity" ).trigger("touchspin.updatesettings", {max: q});

    });



    $('#coupon, #voucher, #shipping, #reward').change(function () {
//            calculateTotal();
    });

// we comment this lines because it's make conflict when update payment in order form
//    $(function() {
//        var selecty = $('select[name=payment]');
//        $('input[name=payment_method]').val(selecty.find('option:selected').text());
//        $('input[name=payment_code]').val(selecty.val());
//    });

    $('select[name=\'payment\']').bind('change', function () {
        if (this.value) {
            $('input[name=\'payment_method\']').attr('value', $('select[name=\'payment\'] option:selected').text());
        } else {
            $('input[name=\'payment_method\']').attr('value', '');
        }

        $('input[name=\'payment_code\']').attr('value', this.value);
    });

    $('select[name=\'shipping\']').bind('change', function () {
        if (this.value) {
            $('input[name=\'shipping_method\']').attr('value', $('select[name=\'shipping\'] option:selected').text());
        } else {
            $('input[name=\'shipping_method\']').attr('value', '');
        }

        $('input[name=\'shipping_code\']').attr('value', this.value);
    });


}).on('change', '#payment_country_id', function() {
    loadZonesForCountry($('#payment_country_id').val(), 'payment_zone_id');
}).on('change', '#shipping_country_id', function() {
    loadZonesForCountry($('#shipping_country_id').val(), 'shipping_zone_id');
}).on('change','#payment_zone_id',function(){
    loadAreasForZone($('#payment_zone_id').val(), 'payment_area_id');
}).on('change','#shipping_zone_id',function(){
    loadAreasForZone($('#shipping_zone_id').val(), 'shipping_area_id');
});

function calculateTotal(submit) {

    if (typeof submit == 'undefined') {
        submit = false;
    }

    var $formData = $('#orderForm :input[id!=\'order_product_name\']').serialize();

    var totalState = 0;

    var url = links['catalog_url'] + 'index.php?route=checkout/manual&no_multicountry_redirection=true';



    $.ajax({
        url: url,
        data: $formData,
        dataType: 'json',
        type: 'post',
        async: false,
        success: function (json)
        {
            if (json.error)
            {
                $('#errors-fallback').show();
                $('#errors-fallback').html('');

                if ( json.error['product_has_error'] != undefined )
                {
                    var anchor_name = $('tr#' + json.error['product_has_error']['product_id']).find('a.product-name');
                    anchor_name.html( anchor_name.text() + '<span class="text-danger">***</span>' );
                }

                for ( err in json.error )
                {
                    if ( typeof json.error[err] == 'object')
                    {
                        if ( err != 'product_has_error' )
                        {
                            for ( ierr in json.error[err] )
                            {
                                $('#errors-fallback').append(json.error[err][ierr] + "<br>");
                            }
                        }
                    }
                    else
                    {
                        $('#errors-fallback').append(json.error[err] + "<br>");
                    }
                }

                totalState = 0;
                return;
            }
            else
            {

                $('#errors-fallback').hide();

                totalState = 1;

                if ($('select[name=\'shipping\']').data('select2')) {
                    $('select[name=\'shipping\']').select2('destroy');
                }

                if ($('select[name=\'payment\']').data('select2')) {
                    $('select[name=\'payment\']').select2('destroy');
                }

                if (json['shipping_method']) {
                    html = '';

                    for (i in json['shipping_method']) {
                        html += '<optgroup label="' + json['shipping_method'][i]['title'] + '">';

                        if (!json['shipping_method'][i]['error']) {
                            for (j in json['shipping_method'][i]['quote']) {
                                if (json['shipping_method'][i]['quote'][j]['code'] == $('input[name=\'shipping_code\']').attr('value')) {
                                    html += '<option value="' + json['shipping_method'][i]['quote'][j]['code'] + '" selected="selected">' + json['shipping_method'][i]['quote'][j]['title'] + '</option>';
                                } else {
                                    html += '<option value="' + json['shipping_method'][i]['quote'][j]['code'] + '">' + json['shipping_method'][i]['quote'][j]['title'] + '</option>';
                                }
                            }
                        } else {
                            html += '<option value="" style="color: #F00;" disabled="disabled">' + json['shipping_method'][i]['error'] + '</option>';
                        }

                        html += '</optgroup>';
                    }


                    $('select[name=\'shipping\']').html(html);

//                        $('select[name=\'shipping\']').select2();


                    if ($('select[name=\'shipping\'] option:selected').attr('value')) {
                        $('input[name=\'shipping_method\']').attr('value', $('select[name=\'shipping\'] option:selected').text());
                    } else {
                        $('input[name=\'shipping_method\']').attr('value', '');
                    }

                    $('input[name=\'shipping_code\']').attr('value', $('select[name=\'shipping\'] option:selected').attr('value'));
                }

                if (json['payment_method']) {
                    html = '';

                    for (i in json['payment_method']) {
                        var title;
                        if(json['payment_method'][i]['code'] == "tap")
                            title=json['payment_method'][i]['str_title'];
                        else
                            title = json['payment_method'][i]['title'] ;
                        if (json['payment_method'][i]['code'] == $('input[name=\'payment_code\']').attr('value')) {
                            html += '<option value="' + json['payment_method'][i]['code'] + '" selected="selected">' + title + '</option>';
                        } else {
                            html += '<option value="' + json['payment_method'][i]['code'] + '">' + title + '</option>';
                        }
                    }

                    $('select[name=\'payment\']').html(html);

//                        $('select[name=\'payment\']').select2();

                    if ($('select[name=\'payment\'] option:selected').attr('value')) {
                        $('input[name=\'payment_method\']').attr('value', $('select[name=\'payment\'] option:selected').text());
                    } else {
                        $('input[name=\'payment_method\']').attr('value', '');
                    }

                    $('input[name=\'payment_code\']').attr('value', $('select[name=\'payment\'] option:selected').attr('value'));
                }

                if (json['order_product'] != '' || json['order_voucher'] != '' || json['order_total'] != '') {
                    html  = '<thead>';
                    html += '     <tr>';
                    html += '          <th>' + locales['column_product'] + '</th>';
                    html += '          <th>' + locales['column_model'] + '</th>';
                    html += '          <th>' + locales['column_quantity'] + '</th>';
                    html += '          <th>' + locales['column_price'] + '</th>';
                    html += '          <th>' + locales['column_total'] + '</th>';
                    html += '     </tr>';
                    html += '</thead>';

                    if (json['order_product'] != '') {
                        for (i = 0; i < json['order_product'].length; i++) {
                            product = json['order_product'][i];

                            html += '<tr '+ (product['added_by_user_type'] == 'admin' ? 'style="background: rgb(226, 241, 239)"' : '') +'>';
                            html += '  <td class="left">' + product['name'] + '<br />';

                            if (product['option']) {
                                for (j = 0; j < product['option'].length; j++) {
                                    option = product['option'][j];

                                    html += '  - <small data-id="'+option['product_option_value_id']+'" class="optionValue">' + option['name'] + ': ' + option['value'] + '</small><br />';
                                }
                            }

                            html += '  </td>';
                            html += '  <td class="left">' + product['model'] + '</td>';
                            html += '  <td class="right">' + product['quantity'] + '</td>';
                            html += '  <td class="right">' + product['price'] + '</td>';
                            html += '  <td class="right">' + product['total'] + '</td>';
                            html += '</tr>';
                        }
                    }

                    if (json['order_voucher'] != '') {
                        var voucher_row = 0;
                        for (i in json['order_voucher']) {
                            voucher = json['order_voucher'][i];

                            html += '  <input type="hidden" name="order_voucher[' + voucher_row + '][order_voucher_id]" value="" />';
                            html += '  <input type="hidden" name="order_voucher[' + voucher_row + '][voucher_id]" value="' + voucher['voucher_id'] + '" />';
                            html += '  <input type="hidden" name="order_voucher[' + voucher_row + '][description]" value="' + voucher['description'] + '" />';
                            html += '  <input type="hidden" name="order_voucher[' + voucher_row + '][code]" value="' + voucher['code'] + '" />';
                            html += '  <input type="hidden" name="order_voucher[' + voucher_row + '][from_name]" value="' + voucher['from_name'] + '" />';
                            html += '  <input type="hidden" name="order_voucher[' + voucher_row + '][from_email]" value="' + voucher['from_email'] + '" />';
                            html += '  <input type="hidden" name="order_voucher[' + voucher_row + '][to_name]" value="' + voucher['to_name'] + '" />';
                            html += '  <input type="hidden" name="order_voucher[' + voucher_row + '][to_email]" value="' + voucher['to_email'] + '" />';
                            html += '  <input type="hidden" name="order_voucher[' + voucher_row + '][voucher_theme_id]" value="' + voucher['voucher_theme_id'] + '" />';
                            html += '  <input type="hidden" name="order_voucher[' + voucher_row + '][message]" value="' + voucher['message'] + '" />';
                            html += '  <input type="hidden" name="order_voucher[' + voucher_row + '][amount]" value="' + voucher['amount'] + '" />';

                            html += '<tr>';
                            html += '  <td class="left">' + voucher['description'] + '</td>';
                            html += '  <td class="left"></td>';
                            html += '  <td class="right">1</td>';
                            html += '  <td class="right">' + voucher['amount'] + '</td>';
                            html += '  <td class="right">' + voucher['amount'] + '</td>';
                            html += '</tr>';
                        }
                    }

                    var total_row = 0;
                    var orderTotalValue = 0;

                    for (i in json['order_total']) {
                        total = json['order_total'][i];

                        html += '<tr id="total-row' + total_row + '">';
                        html += '  <td class="right" colspan="4"><input type="hidden" name="order_total[' + total_row + '][order_total_id]" value="" /><input type="hidden" name="order_total[' + total_row + '][code]" value="' + total['code'] + '" /><input type="hidden" name="order_total[' + total_row + '][title]" value="' + total['title'] + '" /><input type="hidden" name="order_total[' + total_row + '][text]" value="' + total['text'] + '" /><input type="hidden" name="order_total[' + total_row + '][value]" value="' + total['value'] + '" /><input type="hidden" name="order_total[' + total_row + '][sort_order]" value="' + total['sort_order'] + '" />' + total['title'] + '</td>';
                        html += '  <td class="right">' + total['value'] + '</td>';
                        html += '</tr>';

                        orderTotalValue = total['value'];

                        total_row++;
                    }

                    $('.orderTotalValue').text(orderTotalValue);

                    $('#total-products').html(html);
                }

            }
        }
    });

    if (submit == 'submit') {
        $('.loading').fadeIn(100);

        // $('#orderForm').submit();

        let form = $('#orderForm');
        $.ajax({
            url: form.attr('action'),
            data: form.serialize(),
            dataType: 'json',
            type: 'post',
            async: false,
            success: function (json) {
                var returnResult = json;

                if ( returnResult.redirect == '1' )
                {
                    if ( returnResult.success == '1' )
                    {
                        notify('', 'success', returnResult.success_msg);
                    }

                    window.location.href = returnResult.to;
                    return;
                }

                if (returnResult.success == '1') {
                    notify('', 'success', returnResult.success_msg);
                }
                else{
                    if(returnResult.errors){
                        errorsObj = returnResult.errors;
                    }else if(returnResult.error){
                        errorsObj = returnResult.error;
                    }

                    if(errorsObj.error) {
                        displayErrors(errorsObj.error);
                    } else {
                        var errorMsg = errorsObj.warning;
                        for(var el in errorsObj) {
                            if($('#' + el + '-group').length <= 0 && el != "warning" && el != "error") {
                                errorMsg += "<br/> - " + errorsObj[el];
                            }
                        }
                        if (errorMsg && errorMsg != "") {
                            displayErrors(errorMsg);
                        }
                        applyFormErrors(errorsObj);
                    }
                }
            }
        });
        $('.loading').fadeOut(100);
        return;
    }

    $('select').select2();
    reloadProductsSelect();

    return totalState;
}

function syncValidation($target, $parent) {
    var state = false;

    $.ajax({
        url: links['validate'] + $target,
        data:
            $parent.find('select,input,textarea,radio,checkbox').serialize(),
        method:
            'POST',
        dataType:
            'JSON',
        async:
            false,
        success:

            function (response) {

                $parent.find('.has-error').removeClass('has-error');

                if (response.hasErrors === false) {
                    state = true;

                    $parent.find('.has-error').removeClass('has-error');
                } else {
                    var errors = response.errors;
                    for (err in errors)
                    {
                        var error = errors[err];
                        $('#' + err).parent().addClass('has-error');
                        $('#' + err).parent().find('.help-block').html(error);
                    }

                    state = false;
                }
            }
    })
    ;
    return state;
}


function updateCustomerFields(customer_id) {
    startLoadingScreen();

    $.ajax({
        url: links['getCustomerInfo'],
        type: "POST",
        data: {'customer_id': customer_id},
        success: function (resp) {
            removeLoadingScreen();
            customer = JSON.parse(resp);

            // Update All Fields that relate to the customer.
            $('#firstname').val(customer.firstname);
            $('#lastname').val(customer.lastname);
            $('#email').val(customer.email);
            $('#telephone').val(customer.telephone);
            $('#fax').val(customer.fax);
            $('#customer_group_id').val(customer.customer_group_id).trigger('change');

        }
    });

}

$(function () {
    var paymentUrl = "sale/order/updatePaymentMethodsList",
        paymentCountryDropdown = $('#payment_country_id'),
        paymentZoneDropdown = $('#payment_zone_id'),
        paymentCodeHiddenInput = $("input[name='payment_code']"),
        paymentDropdown = $('#payment'),
        paymentCode = '';

    function fillPaymentMethods() {
        $.ajax({
            url: paymentUrl,
            type: 'POST',
            dataType: 'json',
            data: {country_id: $(this).val(), zone_id: paymentZoneDropdown.find(':selected').val()},
            success: function (data)
            {
                var options = data.map(payment => () => `<option value='${payment.code}' ${($.trim(payment.code) === $.trim(paymentDropdown.find(':selected').val())) ? 'selected' : ''}>${payment.name}</option>`);
                paymentDropdown.html(options);
                paymentCode = (paymentDropdown.find(':selected').val() < 0) ? $.trim(data[0].code) : $.trim(paymentDropdown.find(':selected').val());
            }
        });
        paymentCodeHiddenInput.val(paymentCode);
    }

    paymentCountryDropdown.on('change',fillPaymentMethods);

    /* ---------------------------------------------------------------------- */
    if ($('#rewardPointCheck').data('check-result') == 1)
    {
        //                             order points                                //

        let orderPointsDiv = $('#orderPointsDiv'),
            customerDropDownList = $('#customer-customer');

        function displayPointsOrderFiled() {

            let _this = $(this),
                customerId = _this.val();
            if (customerId.length > 0) {
                orderPointsDiv.show();
            } else {
                orderPointsDiv.hide();
            }

        }

        customerDropDownList.on('change', displayPointsOrderFiled);
    }
});