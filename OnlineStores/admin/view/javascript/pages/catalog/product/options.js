$(document).ready(function () {

    $("#options-autocomplete").select2({
        minimumResultsForSearch: 5,
        width: '100%',
        ajax: {
            url: links['options_autocomplete'] + '?filter_name=',
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
                            id: item.option_id,
                            text: item.name,
                            category: item.category,
                            label: item.name,
                            type: item.type,
                            option_value: item.option_value
                        }
                    })
                };
            },
            cache: true
        },
    });

    $(document).on('click','.newOption', function () {
        $('#newOption').modal('toggle')
        $('#newOption').on('show.bs.modal', function () {
        });

        $('#newOption').on('hidden.bs.modal', function () {
            optionsTable.clear().draw();
            $('.modal-errors').html('');
        });
    });
    // var selectedOptionValues = {};
    // var unSelectedOptionValues = {};
    var data = {};

    $('#newOptionValue').on('show.bs.modal', function (event) {

        var $optionId = $(event.relatedTarget).attr('data-option');

        if (typeof $optionId == 'undefined') {
            var $optionId = $('#newOptionValueForm #option_id').val();
        }

        if (allOptions[$optionId]['type'] == 'image') {
            $('.entry-image-td').show();
        } else {
            $('.entry-image-td').hide();
        }

        $(event.currentTarget).find('#option_id').val($optionId);

        if (typeof selectedOptionValues[$optionId] == 'undefined') {
            selectedOptionValues[$optionId] = {};
        }

        data[$optionId] = $.map(allOptions[$optionId]['values'], function (obj) {

            obj.id = obj.option_value_id;
            obj.text = obj.name;
            obj.option_id = $optionId;

            if (
                typeof allOptions[$optionId]['product_values'][obj.option_value_id] != 'undefined' &&
                allOptions[$optionId]['product_values'][obj.option_value_id] != null
            ) {
                selectedOptionValues[$optionId][obj.option_value_id] = obj;
                selectedOptionValues[$optionId][obj.option_value_id]['values'] =
                    allOptions[$optionId]['product_values'][obj.option_value_id];
            }

            if (
                typeof selectedOptionValues[$optionId][obj.option_value_id] != 'undefined'
            ) {
                obj.selected = true;
            } else {
                obj.selected = false;
            }

            return obj;
        });

        $('#selectedOptionValues').select2({
            data: data[$optionId],
            closeOnSelect: false,
        });

        $('#selectedOptionValues').on('select2:select', function (event) {
            var data = event.params.data;

            selectedOptionValues[$optionId][data.id] = data;

            if (
                typeof unSelectedOptionValues[$optionId] != 'undefined' &&
                typeof unSelectedOptionValues[$optionId][data.id] != 'undefined'
            ) {
                delete unSelectedOptionValues[$optionId][data.id];
            }
        });

        unSelectedOptionValues[$optionId] = {};

        $('#selectedOptionValues').on('select2:unselect', function (event) {

            var data = event.params.data;

            unSelectedOptionValues[$optionId][data.id] = true;

            $('.thumb-option-selector')
                .find('*[data-option-value-id="' + data.id + '"][data-option-id="' + $optionId + '"]')
                .remove();

            delete selectedOptionValues[$optionId][data.id];

            if (typeof allOptions[$optionId]['product_values'][data.id] != 'undefined') {
                delete allOptions[$optionId]['product_values'][data.id];
                allOptions[$optionId]['product_values'][data.id] = null;
            }
        });
    });

    $('#newOptionValue').on('hide.bs.modal', function (event) {

        $('.modal-errors').html('');

        $('#selectedOptionValues').select2().empty();

        optionsValuesTable.clear().draw();
    });

    $("#options-autocomplete").on('select2:select', function (event) {
        var data = event.params.data;
        var $tmpl = '<div class="selection" data-oid="' + data.id + '" id="selection-' + data.id + '" ' +
            'data-val="' + data.text + '">' +
            '<span class="selection-remove" role="presentation">×</span>' +
            data.text +
            '</div>';

        selectedOptionValues[data.id] = {};

        for (ov in data['option_value']) {
            // selectedOptionValues[data.id][data['option_value'][ov]['option_value_id']] = data['option_value'][ov];
        }

        // $('.optionsRows .option-row').addClass('hide');

        $('.optionsResults').append($tmpl);

        $('.optionsRows').append(getOptionsTemplate(data));

        if (
            data.type == 'select' ||
            data.type == 'checkbox' ||
            data.type == 'radio' ||
            data.type == 'image' ||
            data.type == 'product'
        ) {
            $('#newOptionValueForm #option_id').val(data.id);

            $('#newOptionValue').modal('show');
        }

        $('.datepicker').pickadate({
            'format': 'yyyy-mm-dd',
            'formatSubmit': 'yyyy-mm-dd'
        });

        $("#datetimepicker").AnyTime_picker({
            format: "%Y-%m-%d %H:%i",
        });

        $('.timepicker').pickatime({});

        $('.optionsRows select').select2({
            minimumResultsForSearch: 5
        });


        $(".touchspin-quantity-control").TouchSpin({
            min: 0,
            max: Infinity,
            initval: 1
        });

        $(".touchspin-minus-control").TouchSpin({
            min: -Infinity,
            max: Infinity,
            initval: 0
        });

        $(".touchspin-money-minus").TouchSpin({
            'postfix': defaultCurrency,
            min: -Infinity,
            max: Infinity,
            initval: 0,
            step: 0.01,
            decimals: 2,
        });

        if (Array.prototype.forEach) {
            var elems = Array.prototype.slice.call(document.querySelectorAll('.switchery'));
            elems.forEach(function (html) {
                var switchery = new Switchery(html);
                //debugger;
            });
        } else {
            var elems = document.querySelectorAll('.switchery');
            for (var i = 0; i < elems.length; i++) {
                var switchery = new Switchery(elems[i]);
            }
        }

        $('.newOptionValue').click(function () {
            $('#newOptionValue').modal('show');

            var $optionId = $(this).attr('data-option');

            $('#newOptionValue').on('shown.bs.modal', function (event) {

                $(event.currentTarget).find('#option_id').val($optionId);

                var data = $.map(allOptions[$optionId]['values'], function (obj) {
                    obj.id = obj.option_value_id;
                    obj.text = obj.name;

                    return obj;
                });

                $('#selectedOptionValues').select2({
                    data: data,
                });

            });

            $('#newOptionValue').on('hidden.bs.modal', function (event) {
                optionsValuesTable.clear().draw();

                $('#selectedOptionValues').select2().empty();
            });
        });

        // $(".switchery").on('change', function (e) {
        //     var status = this.checked ? locales['switch_text_enabled'] : locales['switch_text_disabled'];
        //     $(this).parent().children(".switchery-status").text(status);
        // });

        $('.optionsResults .selection-remove').click(function () {
            $(this).parent().remove();

            var $id = $(this).parent().data('oid');
            var $val = $(this).parent().data('oid');
            $('#option-row-' + $id).remove();

            $("#options-autocomplete option[value='" + $val + "']").remove();

            $('.optionsRows .option-row').addClass('hide');
            $('.optionsRows .option-row:first-child').removeClass('hide');
        });

        $('.optionsResults .selection').click(function (e) {

            var oid = $(this).data('oid');

            // $('.option-row').addClass('hide');

            $('#option-row-' + oid).removeClass('hide');

        });

        optionKey++;
    });

    $("#options-autocomplete").on('select2:unselecting', function (event) {
        var data = event.params.args.data;
        // find option div
        var optionElement = $(".option-row[data-option-id='"+ data.id +"']");
        // is not submitted ?
        if (!optionElement.attr('data-option-is-submitted')){
            // delete element
            $('#options-autocomplete option[value="'+data.id+'"]').removeAttr('selected').change();

            delete selectedOptionValues[data.id];

            $('.thumb-option-selector').find('*[data-option-id=' + data.id + ']').remove();

            $('#selection-' + data.id).remove();
            $('#option-row-' + data.id).remove();

            // $('.optionsRows .option-row').addClass('hide');
            $('.optionsRows .option-row:first-child').removeClass('hide');
        }else{
            // hide dropdown until confirm or cancel
            $("<style type='text/css'> .select2-dropdown{ visibility: hidden; } </style>").appendTo("head");
            optionElement.find('.remove-option-row').trigger('click');
            event.preventDefault();
        }
    });

    $('#newOptionValueSubmitButton').click(function () {

        var $form = $('#newOptionValueForm');

        var $optionId = $('#option_id').val();

        var optionValueInputLength = $form.find('tbody').find('.optionValueInput').length;

        var closeModal = true;

        if (optionValueInputLength >= 1) {
            var $action = $form.attr('action');
            var $data = $form.serialize();
            var poc_type = allOptions[$('#option_id').val()]['type'];
            $data += `&option_type=${poc_type}`;
            $.ajax({
                url: $action,
                data: $data,
                dataType: 'JSON',
                method: 'POST',
                async: false,
                success: function (response) {
                    if (response.success == 1) {

                        var optionData = response.data;

                        for (optionDataId in optionData) {
                            var od = optionData[optionDataId];

                            selectedOptionValues[$optionId][od['option_value_id']] = od;

                            allOptions[$optionId]['values'].push(od);
                        }

                    } else {
                        closeModal = false;

                        let errorContext = '';

                        for (let key of Object.keys(response.error.option_value)) {
                            for (let _key of Object.keys(response.error.option_value[key])) {
                                if (_key === undefined) {
                                    continue;
                                }

                                errorContext += `<li>${response.error.option_value[key][_key]}</li>`;                            }
                        }
                        const errorTemplate = '<div class="alert alert-danger alert-styled-left alert-bordered">' +
                            '<button type="button" class="close" data-dismiss="alert"><span>&times;</span>' +
                            '<span class="sr-only">Close</span>' +
                            '</button><ul>' + errorContext + '</ul></div>';

                        $('.modal-errors').html(errorTemplate);
                    }
                }
            });
        }

        for (optionValueId in selectedOptionValues[$optionId]) {

            var selectedOptionValue = selectedOptionValues[$optionId][optionValueId];

            if (
                (typeof allOptions[$optionId]['product_values'][optionValueId] == 'undefined' ||
                    allOptions[$optionId]['product_values'][optionValueId] == null)
                || $('#option-value-row-' + optionValueId).length == 0
            ) {


                if ($('#option-value-row-' + optionValueId).length == 0) {

                    addOptionValueRow({
                        data: selectedOptionValue,
                        optionId: $optionId,
                        optionValueId: optionValueId,
                    }, function (template) {
                      //  $('#option-row-' + $optionId).find('.optionValuesTable tbody').append(template);
                        // add this row for options rows
                      //  $('#optionValuesTable tbody').append($t);

                      //work with rows exists option exist before and with new options
                      $("input[name='product_option[" +$optionId +"][product_option_id]']").parent().find('.optionValuesTable tbody').append(template);

                    });
                }

            }
        }

        bootstrap();

        for (optionValueId in unSelectedOptionValues[$optionId]) {
            $('#option-value-row-' + optionValueId).remove();
        }

        if (closeModal) {
            $('#newOptionValue').modal('toggle');
        }
    });

    $('.option-view-more').click(function () {
        let moreOptionId = $(this).parents('.option-row').data('option-id');
        let moreStart = $(this).parents('.option-row').find('tbody tr').not('[id*="hidden-option-value-row"]').length;
        let viewMore = viewMoreLink.replace("moreOptionId", moreOptionId).replace("moreStart", moreStart);
        $(this).addClass('load-btn');
        $.ajax({
            url: viewMore,
            dataType: 'JSON',
            method: 'POST',
            async: false,
            success: response => {
                for (let i = 0; i < response.data[0].product_option_value.length; i++) {
                    let option = {};
                    option.data = response.data[0].product_option_value[i];
                    option.optionId = response.data[0].option_id;
                    option.optionValueId = response.data[0].product_option_value[i].option_value_id;
                    
                    let index = moreStart + i;

                    addOptionValueRow(option, template => {
                        $(this).parents('.option-row').find('tbody').append(template);
                    }, index);
                }
                bootstrap();
                $(this).parents('.option-row').find('.current').html($(this).parents('.option-row').find('tbody tr').not('[id*="hidden-option-value-row"]').length)
                $(this).removeClass('load-btn');
                if(100 <= $(this).parents('.option-row').find('tbody tr').not('[id*="hidden-option-value-row"]').length) {
                    /* I replaced this value $(this).data('total') with 100 to hide show more button 
                        when we are seeing 100 not all the optios added*/
                    $(this).remove()
                }
            }
        });

    });

    $('.optionsResults .selection-remove').click(function () {
        $(this).parent().remove();

        var $id = $(this).parent().data('oid');
        var $val = $(this).parent().data('oid');
        $('#option-row-' + $id).remove();

        $("#options-autocomplete option[value='" + $val + "']").remove();

        // $('.optionsRows .option-row').addClass('hide');
        $('.optionsRows .option-row:first-child').removeClass('hide');
    });

    $('.optionsResults .selection').click(function (e) {

        var oid = $(this).data('oid');

        // $('.option-row').addClass('hide');

        $('#option-row-' + oid).removeClass('hide');

    });


});

function getOptionsTemplate(data) {

    var $__ = locales['options'];

    var $t = '';

    $t += '<div id="option-row-' + data.id + '" class="panel-white option-row" data-option-id="' + data.id + '" data-option-is-submitted="">';
    // option hidden data inputs
    $t += '<input type="hidden" name="product_option[' + data.id + '][product_option_id]" value="" />';
    $t += '<input type="hidden" name="product_option[' + data.id + '][name]" value="' + data.label + '" />';
    $t += '<input type="hidden" name="product_option[' + data.id + '][option_id]" value="' + data.id + '" />';
    $t += '<input type="hidden" name="product_option[' + data.id + '][type]" value="' + data.type + '" />';
    // option hidden data inputs

    // option head
    $t += '<div class="panel-heading w-100 m-0">';
    $t += '<h6 class="panel-title">';
    $t += '<a data-toggle="collapse" data-parent="#accordion-controls" ';
    $t += 'href="#accordion-controls-group-' + data.id + '" aria-expanded="true">' + data.label + '</a>';
    $t += '<a class="heading-elements-toggle"><i class="icon-more"></i></a>';
    $t += '</h6>';

    $t += '<div class="heading-elements">' +
        '<div class="heading-form">' +
        '<div class="form-group">' +
        '<div class="checkbox checkbox-switchery">' +
        '<label>' +
        '<input type="checkbox" ' +
        'onchange="changeStatusRequired(this)" ' +
        'name="product_option[' + data.id + '][required]" ' +
        'class="switchery"> ' +
        '<span class="switchery-status">' +
        locales['lbl_switch_optional'] +
        '</span>' +
        '</label>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '<ul class="icons-list">' +
        '<li>' +
        '<a class="text-warning-600 remove-option-row" data-popup="tooltip" ' +
        'data-original-title="Remove Option">' +
        '<i class="icon-trash"></i>' +
        '</a>' +
        '</li>' +
        '</ul>' +
        '</div>';

    $t += '</div>';
    
    // option head

    $t += '<div id="accordion-controls-group-' + data.id + '" class="panel-collapse collapse in">';

    // option elements
    if (data.type == 'text') {
        $t += '<div class="panel-body">';
        $t += '<div class="form-group col-md-12">';
        $t += '<label for="model" class="control-label">' + $__['entry_option_value'] + '</label>';
        $t += '<input name="product_option[' + data.id + '][option_value]" class="form-control"/>';
        $t += '</div>';
        $t += '</div>';
    } else if (data.type == 'textarea') {
        $t += '<div class="panel-body">';
        $t += '<div class="form-group col-md-12">';
        $t += '<label for="model" class="control-label">' + $__['entry_option_value'] + '</label>';
        $t += '<textarea name="product_option[' + data.id + '][option_value]" class="form-control"></textarea>';
        $t += '</div>';
        $t += '</div>';
    } else if (data.type == 'file') {
        $t += '<div class="panel-body">';
        $t += '<div class="form-group col-md-12">';
        $t += '<label for="model" class="control-label">' + $__['entry_option_value'] + '</label>';
        $t += '<input name="product_option[' + data.id + '][option_value]" class="form-control"/>';
        $t += '</div>';
        $t += '</div>';
    } else if (data.type == 'date') {
        $t += '<div class="panel-body">';
        $t += '<div class="form-group col-md-12">';
        $t += '<label for="model" class="control-label">' + $__['entry_option_value'] + '</label>';
        $t += '<input name="product_option[' + data.id + '][option_value]" class="form-control datepicker"/>';
        $t += '</div>';
        $t += '</div>';
    } else if (data.type == 'datetime') {
        $t += '<div class="panel-body">';
        $t += '<div class="form-group col-md-12">';
        $t += '<label for="model" class="control-label">' + $__['entry_option_value'] + '</label>';
        $t += '<input name="product_option[' + data.id + '][option_value]" class="form-control" id="datetimepicker"/>';
        $t += '</div>';
        $t += '</div>';
    } else if (data.type == 'time') {
        $t += '<div class="panel-body">';
        $t += '<div class="form-group col-md-12">';
        $t += '<label for="model" class="control-label">' + $__['entry_option_value'] + '</label>';
        $t += '<input name="product_option[' + data.id + '][option_value]" class="form-control timepicker"/>';
        $t += '</div>';
        $t += '</div>';
    } else if (
        data.type == 'select' ||
        data.type == 'checkbox' ||
        data.type == 'radio' ||
        data.type == 'image' ||
        data.type == 'product' 
    ) {
        $t += '<div class="table-responsive">';
        $t += getOptionsTableTemplate(data);
        $t += '</div>';
    }
    // option elements

    // add option btn
    if (
        data.type == 'select' ||
        data.type == 'checkbox' ||
        data.type == 'radio' ||
        data.type == 'image' ||
        data.type == 'product'
    ) {
        $t += '<div class="panel-body">' +
            '<div class="row">';

        $t += '<div class="mt-10 mb-10">' +
            '<a data-option="' + data.id + '" ' +
            'data-toggle="modal" data-target="#newOptionValue" ' +
            'class="btn btn-xs border-blue text-blue btn-flat no-margin">' +
            locales['button_add_option_value'] +
            '</a>' +
            '</div>';

        $t += '</div>' +
            '</div>';
    }
    // add option btn
    $t += '</div>';
    $t += '</div>';

    return $t;

}

function _backUp_getOptionsTableTemplate(data) {
    var $__ = locales['options'];

    var $t = '';

    $t += '<table class="table table-hover datatable-highlight">';
    $t += '<thead><tr>';
    $t += '<th>' + $__['entry_option_value'] + '</th>';
    $t += '<th>' + $__['entry_quantity'] + '</th>';
    $t += '<th>' + $__['entry_subtract'] + '</th>';
    $t += '<th>' + $__['entry_price_diff'] + '</th>';
    $t += '<th>' + $__['entry_option_points'] + '</th>';
    $t += '<th>' + $__['entry_weight_diff'] + '</th>';
    if (poip_installed == 1) {
        $t += '<th>' + $__['entry_image'] + '</th>';
    }
    $t += '<th></th>';
    $t += '</tr></thead>';
    $t += '<tbody><tr id="option-value-row-' + optionKey + '">';
    $t += '<td><select name="product_option[' + optionKey + '][product_option_value][0][option_value_id]">';

    for (option in data.option_value) {
        var optionValue = data.option_value[option];

        $t += '<option value="' + optionValue['option_value_id'] + '">' + optionValue['name'] + '</option>';
    }
    $t += '</select>';
    $t += '<input type="hidden" ' +
        'name="product_option[' + optionKey + '][product_option_value][0][product_option_value_id]" /></td>';
    $t += '<td>';
    $t += '<input type="text" class="form-control" ' +
        'name="product_option[' + optionKey + '][product_option_value][0][quantity]" size="3"/>';
    $t += '</td>';
    $t += '<td>';
    $t += '<select name="product_option[' + optionKey + '][product_option_value][0][subtract]">' +
        '<option value="1">' + $__['text_yes'] + '</option><option value="0">' + $__['text_no'] + '</option>' +
        '</select>';
    $t += '</td>';
    $t += '<td>';
    $t += '<select name="product_option[' + optionKey + '][product_option_value][0][price_prefix]">' +
        '<option value="+">+</option><option value="-">-</option>' +
        '</select>';
    $t += '<input type="text" class="form-control" ' +
        'name="product_option[' + optionKey + '][product_option_value][0][price]" size="5"/>';
    $t += '</td>';
    $t += '<td>';
    $t += '<select name="product_option[' + optionKey + '][product_option_value][0][points_prefix]">' +
        '<option value="+">+</option><option value="-">-</option>' +
        '</select>';
    $t += '<input type="text" class="form-control" ' +
        'name="product_option[' + optionKey + '][product_option_value][0][points]" size="5"/>';
    $t += '</td>';
    $t += '<td>';
    $t += '<select name="product_option[' + optionKey + '][product_option_value][0][weight_prefix]">' +
        '<option value="+">+</option><option value="-">-</option>' +
        '</select>';
    $t += '<input type="text" class="form-control" ' +
        'name="product_option[' + optionKey + '][product_option_value][0][weight]" size="5"/>';
    $t += '</td>';
    if (poip_installed == 1) {
        $t += '<td>POIP</td>';
    }
    $t += '<td>';
    $t += '<a onclick="$(this).parents(\'.table\').find(\'tbody tr\').length > 1 ? $(\'#option-value-row-' + optionKey + '\').remove() : null" ' +
        'class="button btn btn-primary">' + $__['button_remove'] + '</a>';
    $t += '</td>';
    $t += '</tr></tbody>';
    // $t += '<tfoot><tr><td colspan="5">';
    // $t += '<a onclick="addOptionValue(this);" class="button btn btn-primary">' + $__['button_add_option_value'] + '</a>';
    // $t += '</td></tr></tfoot>';
    $t += '</table>';

    return $t;
}

function getOptionsTableTemplate(data) {
    var $__ = locales['options'];

    var $t = '';

    $t += '<table class="table table-hover datatable-highlight optionValuesTable">';
    $t += '<thead><tr>';
    $t += '<th>' + $__['entry_option_value'] + '</th>';
    $t += '<th>' + $__['entry_quantity'] + '</th>';
    $t += '<th>' + $__['entry_subtract'] + '</th>';
    $t += '<th>' + $__['entry_price_diff'] + '</th>';
    $t += '<th>' + $__['entry_weight_diff'] + '</th>';
    $t += '<th>'+''+'</th>';
    $t += '</tr></thead>';
    $t += '<tbody>';
    if (typeof data.appendToTable != 'undefined' && data.appendToTable == true) {
        for (optionId in data.option_value) {
            $t += addOptionValueRow({
                data: data.option_value[optionId],
                optionId: data.id,
                optionValueId: data.option_value[optionId]['option_value_id'],
            });
        }
    }
    $t += '</tbody>';
    $t += '</table>';

    return $t;
}

function addOptionValueRow(option, callback, index) {
    var $__ = locales['options'];


    var optionValue = option['data'];
    var optionId = option['optionId'];
    var optionValueId = option['optionValueId'];
    $t = '';

    $t += '<tr id="option-value-row-' + optionValueId + '" class="' + (index && 'sort-tr') + '">';

    $t += '<td class="td-sort">';

    $t += optionValue['name'];

    $t += '<input type="hidden" value="' + optionValue['option_value_id'] + '" ' +
        'name="product_option[' + optionId + '][product_option_value][' + optionValueId + '][option_value_id]" />';

    $t += '<input type="hidden" ' +
        'name="product_option[' + optionId + '][product_option_value][' + optionValueId + '][product_option_value_id]" />';
    $t += '</td>';

    $t += '<td>';
    $t += '<input type="text"  size="3" class="form-control touchspin-quantity-control" ' +
        'name="product_option[' + optionId + '][product_option_value][' + optionValueId + '][quantity]" value="'+optionValue.quantity+'" />';
    $t += '</td>';

    $t += '<td>';
    $t += '<div class="checkbox checkbox-switchery"> ' +
        '<label>' +
        '<input type="checkbox" ' +
        'onchange="changeStatusYesNo(this)" ' +
        'name="product_option[' + optionId + '][product_option_value][' + optionValueId + '][subtract]" ' +
        'class="switchery" '+((optionValue.subtract == 1)?'checked':'') +'> ' +
        '<span class="switchery-status"> ' +
        ( optionValue.subtract == 1 ? locales['lbl_switch_yes'] : locales['lbl_switch_no']) +
        '</span>' +
        '</label>' +
        '</div>';
    $t += '</td>';

    $t += '<td>';
    $t += '<input type="text" class="form-control touchspin-money-minus" ' +
        'name="product_option[' + optionId + '][product_option_value][' + optionValueId + '][price]" value="'+ optionValue.price +'" size="5"/>';
    $t += '</td>';

    $t += '<td>';
    $t += '<input type="text" class="form-control touchspin-minus-control" ' +
        'name="product_option[' + optionId + '][product_option_value][' + optionValueId + '][weight]" value="'+ optionValue.weight +'" size="5"/>';
    $t += '</td>';
    if(index) {
        $t += '<input type="hidden" name="product_option[' + optionId + '][product_option_value][' + optionValueId + '][sort_order]" value="' + index + '" class="product_option_value_sort_order"/>';
    }
    $t += '<td>';
    $t += '<a data-id="' + optionValueId +'" data-product-id="'+currentProductId+'" class="button btn btn-danger removeOptionValue"><i class="icon-trash"></i></a>';
    $t += '</td>';
    $t += '</tr>';

    newThumbOptionSelector({
        data: optionValue,
        optionId: optionId,
        optionValueId: optionValueId,
        selected: false,
        valueName: optionValue['name'],
        optionName: allOptions[optionId]['name']
    });

    if (typeof callback != 'undefined' && typeof callback == 'function') {
        callback($t);
    }

    return $t;
}

function bootstrap() {
    $('.optionsRows select').select2({
        minimumResultsForSearch: 5
    });

    $(".touchspin-quantity-control").TouchSpin({
        min: 0,
        max: Infinity,
        initval: 1
    });

    $(".touchspin-minus-control").TouchSpin({
        min: -Infinity,
        max: Infinity,
        initval: 0
    });

    $(".touchspin-money-minus").TouchSpin({
        'postfix': defaultCurrency,
        min: -Infinity,
        max: Infinity,
        initval: 0,
        step: 0.01,
        decimals: 2,
    });

    if (Array.prototype.forEach) {
        var elems = Array.prototype.slice.call(document.querySelectorAll('.switchery'));
        elems.forEach(function (html) {
            var switchery = new Switchery(html);
            //debugger;
        });
    } else {
        var elems = document.querySelectorAll('.switchery');
        for (var i = 0; i < elems.length; i++) {
            var switchery = new Switchery(elems[i]);
        }
    }

    // $(".switchery").on('change', function (e) {
    //     var status = this.checked ? locales['switch_text_enabled'] : locales['switch_text_disabled'];
    //     $(this).parent().children(".switchery-status").text(status);
    // });

/*    $('.remove-option-row').click(function () {
        var parent = $(this).parents('.option-row');

        parent.remove();

        var optionId = parent.attr('data-option-id');

        $('#options-autocomplete option[value="'+optionId+'"]').removeAttr('selected').change();
    });*/

    $(".sortable-heading-table").sortable({
        connectWith: '.heading-sortable-table',
        items: '.sort-tr',
        helper: 'original',
        cursor: 'move',
        handle: '.td-sort, [data-action=move]',
        revert: 100,
        containment: '.content-wrapper',
        forceHelperSize: true,
        placeholder: 'sortable-placeholder',
        forcePlaceholderSize: true,
        tolerance: 'pointer',
        start: function (e, ui) {
            ui.placeholder.height(ui.item.outerHeight());
        },
        update: function (event, ui) {
            $('.product_option_value_sort_order').each(function (i) {
                var numbering = i + 1;
                $(this).val(numbering);
            });
        }
    })
}

function addProductOptionValue(elem) {
    var $table = $(elem).next('table');

    var $lastElem = $table.find('tbody tr:last-child');

    var $newElem = $lastElem.clone();

    $newElem.find('[name]').each(function () {

        var $name = this.name.replace(/\[product_option_value\]\[(\d+)\]/g, function (match, i) {
            var i = parseInt(i) + 1;
            return '[product_option_value][' + i + ']';
        });

        this.value = '';
        this.name = $name;


    });

    $newElem.find('.select2').remove();

    $lastElem.after($newElem);

    $('select', $newElem).select2();
}
