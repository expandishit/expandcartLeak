$(document).ready(function () {
    $('.touchspinney').TouchSpin({});
    $(".styled, .multiselect-container input").uniform({radioClass: 'choice'});

    $('select, .autocomplete').not(".thumb-option-selector").select2({
        minimumResultsForSearch: 6,
    });
    
    $.ajax({
        url: links['categories_autocomplete'] + '?filter_name=',
        type: 'GET',
        dataType: 'json',
        success: function (resp) {
            let data = [];
            resp.map(function(item, index) {
                data.push({id: item.category_id, text: item.name})
            })
            $(".categories-autocomplete").select2({
                tokenSeparators: [','],
                closeOnSelect: false,
                data: data
            })
        },
        error: function(err) {
            console.log("err")
        }
    });
    

    $("#downloads-autocomplete").select2({
        tokenSeparators: [','],
        closeOnSelect: false,
        ajax: {
            url: links['downloads_autocomplete'],
            dataType: 'json',
            type: 'GET',
            delay: 250,
            data: function (params) {
                return {
                    filter_name: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item, index) {
                        return {
                            id: item.download_id,
                            text: item.name
                        }
                    })
                };
            },
            cache: true
        }
    });
    $(".related-autocomplete").select2({
        tokenSeparators: [','],
        closeOnSelect: false,
        ajax: {
            url: links['products_autocomplete'],
            dataType: 'json',
            type: 'GET',
            delay: 250,
            data: function (params) {
                return {
                    filter_name: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item, index) {
                        return {
                            id: item.product_id,
                            text: item.name
                        }
                    })
                };
            },
            cache: true
        }
    });

    // this block of code does not work fine with the text search in dropdown .
    // it search on frontendend results not the serverside .
    // $.ajax({
    //     url: links['manufacturers_autocomplete'] + '?filter_name=',
    //     type: 'GET',
    //     dataType: 'json',
    //     success: function (resp) {
    //         let data = [];
    //         resp.map(function(item, index) {
    //             data.push({id: item.manufacturer_id, text: item.name})
    //         })
    //         console.log(resp)
    //         $("#manufacturers-autocomplete").select2({
    //             minimumResultsForSearch: 5,
    //             data: data
    //         })
    //     },
    //     error: function(err) {
    //         console.log("err")
    //     }
    // });

    $("#manufacturers-autocomplete").select2({
        minimumResultsForSearch: 5,
        // minimumInputLength: 1,
        ajax: {
            url: links['manufacturers_autocomplete'] + '?filter_name=',
            dataType: 'json',
            type: 'GET',
            delay: 250,
            data: function (params) {
                return {
                    filter_name: params.term,
                    add_default: 'y',
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item, index) {
                        return {
                            id: item.manufacturer_id,
                            text: item.name
                        }
                    })
                };
            },
            cache: true
        }
    });

    $(".datepicker").datepicker({
        showButtonPanel: true,
        dateFormat: "yy-mm-dd"
    });
    
    $(".status-datepicker").datepicker({
        showButtonPanel: true,
        dateFormat: "yy-mm-dd"
    });

    $('.with-limit').bind('input propertychange', function() {
        $($(this).data('length')).find('.count').html($(this).val().length)
    });

    //$('.datepicker').pickadate({
    //    'format': 'yyyy-mm-dd',
    //    'formatSubmit': 'yyyy-mm-dd'
    //});

    $("#datetimepicker").AnyTime_picker({
        format: "%Y-%m-%d %H:%i",
    });

    $('.timepicker').pickatime({});

    $('.touchspin-money').TouchSpin({
        'postfix': defaultCurrency,
        step: 1,
        forcestepdivisibility: 'none',
        min: -Infinity,
        max: Infinity,
        // forcestepdivisibility: 'round',
        decimals:  currency_decimal_places ,
        buttondown_class: "hide",
        buttonup_class: "hide",
    });

    $('.touchspin-quantity').TouchSpin({
        min: 0,
        max: Infinity
    });


    // $('#attributeTable tbody tr').each(function (index, element) {
    //     attributeautocomplete(index);
    // });

    attributeautocomplete();

    $("#attribute-autocomplete").on('select2:select', function (event) {
        var data = event.params.data;

        console.log(data)

        addAttribute({
            'name': data['text'],
            'id': data['id']
        }, function (template) {
            $('#attributeTable tbody').append(template);
            $('#attributeTable').removeClass('hidden');
        });
    });

    $("#attribute-autocomplete").on('select2:unselect', function (event) {
        var data = event.params.data;

        $('#attribute-row-' + data.id).remove();

        if($('#attributeTable tr').length < 2){
            $('#attributeTable').addClass('hidden');
        }
    });

    $('.attributes-group').click(function () {

        // var $groupId =
        $(this).children("option").prop("selected", "selected");
    });

    $('body').on("change",".brand_select",function () {
        var self = this;
        var brand = $(self).val();
        var textData = "<option disabled>"+locales['select_model']+"</option>";
        $.ajax({
            url: links['get_brand_models'],
            method: 'POST',
            dataType: 'JSON',
            data: {brand: brand},
            success: function (response) {
                if (response['status'] == 'success') {
                    var models = response['models'];
                    models.forEach(function (model) {
                        textData += '<option value="' + model['pc_model_id'] + '">' + model['name'] + '</option>';
                    })
                    $(self).closest('.pc_tr').find('.model_select').html(textData);
                }
            }
        });
    });

    $('body').on("change",".model_select",function () {
        var self = this;
        var model_id = $(self).val();
        var textData = '';
        $.ajax({
            url: links['get_model_years'],
            method: 'POST',
            dataType: 'JSON',
            data: {model: model_id},
            success: function (response) {
                if (response['status'] == 'success') {
                    var years = response['years'];
                    years.forEach(function (year) {
                        textData += '<option value="' + year['pc_year_id'] + '">' + year['name'] + '</option>';
                    })
                    $(self).closest('.pc_tr').find('.year_select').html(textData);

                }
            }
        });
    });

});

function __addAttribute() {
    attributeKey++;

    html = '<tr id="attribute-row' + attributeKey + '">';
    html += '<td class="col-md-4">';
    html += '<select class="attribute-autocomplete" ';
    html += 'name="product_attribute[' + attributeKey + '][attribute_id]"></select>';
    html += '</td>';
    html += '<td class="col-md-6">';
    for (language in languages) {
        var lng = languages[language];
        html += '<div class="input-group">' +
            '<span class="input-group-addon"><img src="view/image/flags/' + lng['image'] + '" ' +
            'title="' + lng['name'] + '" align="top" /></span>' +
            '<textarea class="form-control" ' +
            'name="product_attribute[' + attributeKey + '][product_attribute_description][' + lng['language_id'] + '][text]" ' +
            'cols="30" rows="2"></textarea></div><br />';
    }
    html += '</td>';
    html += '<td class="col-md-2">' +
        '<a onclick="$(\'#attribute-row' + attributeKey + '\').remove();" ' +
        'class="button btn btn-primary">' + locales['button_remove'] + '</a></td>';
    html += '</tr>';

    $('#attributeTable tfoot').before(html);

    attributeautocomplete(attributeKey);
}

function addAttribute(data, callback) {

    html = '<tr id="attribute-row-' + data.id + '">';
    html += '<td class="col-md-4">';
    html += data['name'];
    html += '<input type="hidden" name="product_attribute[' + data.id + '][attribute_id]" ' +
        'value="' + data['id'] + '" />';
    html += '</td>';
    html += '<td class="col-md-2">';
    html += '</td>';
    html += '<td class="col-md-4">';

    html += '<div class="tabbable nav-tabs-vertical nav-tabs-right">' +
        '<div class="tab-content">';

    var first = true;

    for (lngId in languages) {

        var lng = languages[lngId];

        html += '<div class="tab-pane has-padding' + (first ? ' active' : '') + '" ' +
            'id="attributeLangTab-' + data.id + '-' + lng['language_id'] + '">' +
            '<div class="form-group" ' +
            'id="opt-name-group_' + lng['language_id'] + '">' +
            '<label class="control-label"></label>' +
            '<input type="text" class="form-control" ' +
            'name="product_attribute[' + data.id + '][product_attribute_description][' + lng['language_id'] + '][text]" />' +
            '<span class="help-block"></span>' +
            '<span class="text-muted"></span>' +
            '</div>' +
            '</div>';

        first = false;
    }
    html += '</div>' +
        '<ul class="nav nav-tabs nav-tabs-highlight nav-tabs-lang">';

    first = true;

    for (lngId in languages) {

        var lng = languages[lngId];

        html += '<li class="' + (first ? 'active' : '') + '">' +
            '<a href="#attributeLangTab-' + data.id + '-' + lng['language_id'] + '" data-toggle="tab" ' +
            'aria-expanded="false">' +
            '<img src="view/image/flags/' + lng['image'] + '" ' +
            'title="' + lng['name'] + '" class="pull-right">' +
            '<div> ' + lng['name'] + '</div>' +
            '</a>' +
            '</li>';

        first = false;
    }

    html += '</ul>' +
        '</div>';

    html += '</td>';
    html += '<td class="col-md-2">' +
        '<a onclick="$(\'#attribute-row-' + data.id + '\').remove();" ' +
        'class="button btn btn-danger"><i class="icon-trash"></i></a></td>';
    html += '</tr>';

    if (typeof callback != 'undefined' && typeof callback == 'function') {
        callback(html);
    }

    return html;
}

function attributeautocomplete(row) {
    $("#attribute-autocomplete").select2({
        minimumResultsForSearch: 5,
        width: '100%',
        ajax: {
            url: links['attributes_autocomplete'] + '?filter_name=',
            dataType: 'json',
            type: 'GET',
            delay: 250,
            data: function (params) {
                return {
                    filter_name: params.term
                };
            },
            cache: true
        }
    });
}

function addSpecial(row) {

    specialKey++;

    var temp = ddTemp = cgTemp = '';

    for (cg in customerGroups) {
        var group = customerGroups[cg];

        cgTemp += '<option value="' + group['customer_group_id'] + '">' + group['name'] + '</option>';
    }

    temp = '<tr id="special-row' + specialKey + '">' +
        '<td>' +
        '<select name="product_special[' + specialKey + '][customer_group_id]">' + cgTemp + '</select>' +
        '</td>' +
        '<td>' +
        '<input name="product_special[' + specialKey + '][priority]" size="2" type="text">' +
        '</td>' +
        '<td>' +
        '<input name="product_special[' + specialKey + '][price]" type="text" size="6">' +
        '</td>' +
        '<td>' +
        '<input name="product_special[' + specialKey + '][date_start]" class="datepicker" size="9">' +
        '</td>' +
        '<td>' +
        '<input name="product_special[' + specialKey + '][date_end]" class="datepicker" size="9">' +
        '</td>';

    if (typeof dedicatedDomains['status'] !== 'undefined') {
        ddTemp += '<td>' +
            '<select value="0" name="product_special[' + specialKey + '][dedicated_domains]" ' +
            'class="specialDedicatedCurrency">';
        ddTemp += '<option value="0">' + locales['all_domains'] + '</option>';
        for (domainId in dedicatedDomains['domains']) {
            var domain = dedicatedDomains['domains'][domainId];
            ddTemp += '<option data-currency="' + domain['currency'] + '" value="' + domain['domain_id'] + '">' +
                domain['domain'] +
                '</option>';
        }

        ddTemp += '</select></td>';
    }

    temp += ddTemp;

    temp += '<td>' +
        '<a onclick="$(\'#special-row' + specialKey + '\').remove();" ' +
        'class="button btn btn-primary">' + locales['button_remove'] + '</a>' +
        '</td>' +
        '</tr>';

    $('#specialsTable tbody').append(temp);

    $('.specialDedicatedCurrency').change(function (event) {
        var that = $(this);

        var parent = that.parent();

        if (that.val() == 0) {
            $('.specialDedicatedCurrencySpan', parent).text($('.specialDedicatedCurrencySpan').data('default'));
        } else {
            $('.specialDedicatedCurrencySpan', parent).text(that.find(':selected').attr('data-currency'));
        }
    });


    $('#special-row' + specialKey + ' .datepicker').pickadate({
        'format': 'yyyy-mm-dd',
        'formatSubmit': 'yyyy-mm-dd'
    });
}

function addSpecialOrDiscount(row, type) {
    $('#specialDiscountsTable').removeClass('hidden');
    specialDiscountKey++;

    var temp = ddTemp = cgTemp = '';

    for (cg in customerGroups) {
        var group = customerGroups[cg];

        cgTemp += '<option value="' + group['customer_group_id'] + '">' + group['name'] + '</option>';
    }

    temp = '<tr id="special-discount-row' + specialDiscountKey + '" data-type="' + type + '">' +
        '<td>' +
        '<select name="product_special_discount[' + specialDiscountKey + '][customer_group_id]">' + cgTemp + '</select>' +
        '</td>' +
        '<td>' +
        '<input name="product_special_discount[' + specialDiscountKey + '][quantity]" size="2" type="text" class="specialDiscountQuantity">' +
        '</td>' +
        '<td>' +
        '<input name="product_special_discount[' + specialDiscountKey + '][priority]" size="2" type="text" class="discount-touchspin">' +
        '</td>' +
        '<td>' +
        '<input name="product_special_discount[' + specialDiscountKey + '][price]" type="text" size="6" class="form-control touchspin-money">' +
        '<input name="product_special_discount[' + specialDiscountKey + '][quick_discount]" type="hidden" size="6" class="form-control touchspin-money" value="' + (type == "quick_discount" ? 1 : 0) + '">' +
        '</td>' +
        '<td>' +
        '<div class="datepicker-cover"><input type="text" name="product_special_discount[' + specialDiscountKey + '][date_start]" class="form-control  datepickerdynamic"/></div>' +
        '</td>' +
        '<td>' +
        '<div class="datepicker-cover"><input type="text" name="product_special_discount[' + specialDiscountKey + '][date_end]" class="form-control  datepickerdynamic"/></div>' +
        '</td>';

    if (typeof dedicatedDomains['status'] !== 'undefined') {
        ddTemp += '<td>' +
            '<select value="0" name="product_special_discount[' + specialDiscountKey + '][dedicated_domains]" ' +
            'class="specialDiscountDedicatedCurrency">';
        ddTemp += '<option value="0">' + locales['all_domains'] + '</option>';
        for (domainId in dedicatedDomains['domains']) {
            var domain = dedicatedDomains['domains'][domainId];
            ddTemp += '<option data-currency="' + domain['currency'] + '" value="' + domain['domain_id'] + '">' +
                domain['domain'] +
                '</option>';
        }

        ddTemp += '</select></td>';
    }

    temp += ddTemp;

    temp += '<td>' +
        '<a onclick="$(\'#special-discount-row' + specialDiscountKey + '\').remove();" ' +
        'class="button btn btn-danger"><i class="icon-trash"></i></a>' +
        '</td>' +
        '</tr>';
        
    if (type == "quick_discount") {
        $('#specialDiscountsTable > tbody').prepend(temp);
    } else {
        $('#specialDiscountsTable > tbody').append(temp);
    }

    $('.specialDiscountDedicatedCurrency').change(function (event) {
        var that = $(this);

        var parent = that.parent();

        if (that.val() == 0) {
            $('.specialDiscountDedicatedCurrencySpan', parent).text($('.specialDiscountDedicatedCurrencySpan').data('default'));
        } else {
            $('.specialDiscountDedicatedCurrencySpan', parent).text(that.find(':selected').attr('data-currency'));
        }
    });

    $('.specialDiscountQuantity').TouchSpin({
        min: 1,
        max: Infinity,
        initval: 1,
    });

    $('.discount-touchspin').TouchSpin({
        min: 1,
        max: Infinity,
        initval: 1,
    });

    // $('.touchspin-money').TouchSpin({
    //     'postfix': defaultCurrency,
    //     'decimals': 2,
    //     step: 0.01,
    //     min: -Infinity,
    //     max: Infinity
    // });

    locales['drp_locale']['format'] = 'YYYY-MM-DD';

    $('.date-available').daterangepicker({
        autoApply: true,
        ranges: locales['drp_ranges'],
        locale: locales['drp_locale'],
        opens: locales['drp_direction']
    }, function (start, end, label) {
        $(this.element).parents('td').find('.date-available-start').val(start.format('YYYY-MM-DD'));
        $(this.element).parents('td').find('.date-available-end').val(end.format('YYYY-MM-DD'));
    });

    $('#specialDiscountsTable select').select2({
        minimumResultsForSearch: 500
    });

    $(".datepickerdynamic").datepicker({
        showButtonPanel: true,
        dateFormat: "yy-mm-dd"
    });

    //$('.datepicker').pickadate({
    //    'format': 'yyyy-mm-dd',
    //    'formatSubmit': 'yyyy-mm-dd'
    //});
}

function addDiscount(row) {

    discountKey++;

    var temp = ddTemp = cgTemp = '';

    for (cg in customerGroups) {
        var group = customerGroups[cg];

        cgTemp += '<option value="' + group['customer_group_id'] + '">' + group['name'] + '</option>';
    }

    temp = '<tr id="discount-row' + discountKey + '">' +
        '<td>' +
        '<select name="product_discount[' + discountKey + '][customer_group_id]">' + cgTemp + '</select>' +
        '</td>' +
        '<td>' +
        '<input name="product_discount[' + discountKey + '][quantity]" size="2" type="text">' +
        '</td>' +
        '<td>' +
        '<input name="product_discount[' + discountKey + '][priority]" size="2" type="text">' +
        '</td>' +
        '<td>' +
        '<input name="product_discount[' + discountKey + '][price]" type="text" size="6">' +
        '</td>' +
        '<td>' +
        '<input name="product_discount[' + discountKey + '][date_start]" class="datepicker" size="9">' +
        '</td>' +
        '<td>' +
        '<input name="product_discount[' + discountKey + '][date_end]" class="datepicker" size="9">' +
        '</td>';

    if (typeof dedicatedDomains['status'] !== 'undefined') {
        ddTemp += '<td>' +
            '<select value="0" name="product_discount[' + discountKey + '][dedicated_domains]" ' +
            'class="discountDedicatedCurrency">';
        ddTemp += '<option value="0">' + locales['all_domains'] + '</option>';
        for (domainId in dedicatedDomains['domains']) {
            var domain = dedicatedDomains['domains'][domainId];
            ddTemp += '<option data-currency="' + domain['currency'] + '" value="' + domain['domain_id'] + '">' +
                domain['domain'] +
                '</option>';
        }

        ddTemp += '</select></td>';
    }

    temp += ddTemp;

    temp += '<td>' +
        '<a onclick="$(\'#discount-row' + discountKey + '\').remove();" ' +
        'class="button btn btn-primary">' + locales['button_remove'] + '</a>' +
        '</td>' +
        '</tr>';

    $('#discountsTable tbody').append(temp);

    $('.discountDedicatedCurrency').change(function (event) {
        var that = $(this);

        var parent = that.parent();

        if (that.val() == 0) {
            $('.discountDedicatedCurrencySpan', parent).text($('.discountDedicatedCurrencySpan').data('default'));
        } else {
            $('.discountDedicatedCurrencySpan', parent).text(that.find(':selected').attr('data-currency'));
        }
    });

    $('.datepicker').pickadate({
        'format': 'yyyy-mm-dd',
        'formatSubmit': 'yyyy-mm-dd'
    });
}


function addProductClassification(row) {

    productClassificationKey++;

    var temp = ddTemp =  '';

   var cgTemp = "<option >"+locales['select_brand']+"</option>";

    for (cg in pc_brands) {
        var pc_brand = pc_brands[cg];

        cgTemp += '<option value="' + pc_brand['brand_id'] + '">' + pc_brand['name'] + '</option>';
    }

    temp = '<tr class="pc_tr" id="product-classification-row' + productClassificationKey + '">' +
        '<td>' +
        '<select class="form-control select brand_select" name="product_classification[' + productClassificationKey + '][brand_id]">' + cgTemp + '</select>' +
        '</td>' +
        '<td>' +
        '<select  name="product_classification[' + productClassificationKey + '][model][]" multiple class="form-control model_select"></select>' +
        '</td>' +
        '<td>' +
        '<select  name="product_classification[' + productClassificationKey + '][year][]" multiple class="form-control select year_select"></select>' +
        '</td>' ;


    temp += ddTemp;

    temp += '<td>' +
        '<a onclick="$(\'#product-classification-row' + productClassificationKey + '\').remove();" ' +
        'class="button btn btn-danger"><i class="icon-trash"></i></a>' +
        '</td>' +
        '</tr>';

    $('#productClassificationTable > tbody').append(temp);

}



