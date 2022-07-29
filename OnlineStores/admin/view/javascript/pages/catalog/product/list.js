var table;

if(is_multiseller)
    var colsVis = [1, 2, 3, 4, 5, 6,7,8,9,10,11,12];
else
    var colsVis = [1, 2, 3, 4, 5, 6,7,8,9, 10, 11];

$(document).ready(function () {

    $(".styled, .multiselect-container input").uniform({
        radioClass: 'choice'
    });

    let supplier_status = 'hidden';
    if(is_supplier){
        supplier_status = '';
    }

    let shouldShow = showOrderProductQuantity === 1 ? true : false;

    table = $('#datatableGrid').DataTable({
        processing: true,
        serverSide: true,
        stateSave: true,
        autoWidth: false,
        "stateLoadParams": function (settings, data) {
            data.length = configs['config_admin_limit'];
        },
        ajax: {
            url: links['dtHandler'],
            type: 'post',
            beforeSend: function() {
                $('#datatableGrid').before('<div class="text-center loading" style="position: absolute;width: 100%;height: 100%;"><img src="view/image/blue-loading-gif-transparent-9.gif"  style="width: 100px" /></div>');
            },
            complete: function() {
                $('.loading').remove();
            },
            error: function (e, m, l) {
                $(".datatables_country-error").html("");
                $("#datatables_country").append('<tbody class="datatables_country-error">' +
                    '<tr><th colspan="3">' + locales['no_data_found'] + '</th></tr></tbody>');
                $("#datatables_country_processing").css("display", "none");
            }
        },
        dom: '<"datatable-header"fB><"datatable-scroll-wrap"t><"datatable-footer"lip>',
        language: {
            ...locales['dt_language'],
            search: ""
        },
        select: {
            style: 'multi',
            selector: 'td:first-child'
        },
        drawCallback: function () {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
        },
        preDrawCallback: function() {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
        },
        lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        buttons: [
            {
                text: `<i class='icon-pencil7'></i> ${locales['dtb_mass_edit']}`,
                className: 'btn-mass-edit disabled',
                action: function (e, dt, node, congig) {
                    var selectedRows = dt.rows('.selected').data();
                    var selectedIds = [];
                    selectedRows.each(function (item) {
                        selectedIds.push(item.product_id);
                    });

                    if (selectedIds.length > 0) {

                        var query = $.param({'product_id': selectedIds});

                        window.location.href = links['mass_edit'] + '?' + query;
                    }
                }
            },// mass edit
            {
                text: `<i class="fa fa-tag"></i> ${locales['dtb_edit_cat']}`,
                className: 'btn-category disabled',
                action: function (e, dt, node, congig) {
                    var selectedRows = dt.rows('.selected').data();
                    table_selected_ids = [];
                    var products = [];
                    var table = $('#mass-category-table-tbody');
                    var product_ids_input = $('#mass-category-product-ids-input');

                    selectedRows.each(function (item) {
                        table_selected_ids.push(item.product_id);
                        products.push({
                            id: item.product_id,
                            category_name: item.category_name,
                            image: item.thumb,
                            name: item.name
                        });
                    });

                    table.html('');

                    products.forEach(function(product, index) {
                        table.append(`
                        <tr>
                            <td>${index + 1}</td>
                            <td>${product.id}</td>
                            <td><img src="${product.image}"/></td>
                            <td>${product.name}</td>
                        </tr>
                        `);
                    });

                    // product_ids_input.val(table_selected_ids);

                    $('#mass-category-modal').modal('show');
                }
            },// category
            {
                    text: `<i class="far fa-copy"></i> ${locales['dtb_copy']}`,
                    className: 'btn-copy disabled',
                action: function (e, dt, node, congig) {
                    var selectedRows = dt.rows('.selected').data();
                    var selectedIds = [];
                    selectedRows.each(function (item) {
                        selectedIds.push(item.product_id);
                    });

                    if (selectedIds.length > 0) {
                        $.ajax({
                            url: links['copy'],
                            type: 'post',
                            data: {selected: selectedIds},
                            dataType: 'JSON',
                            success: function (response) {

                                dt.rows('.selected').remove().draw();

                                if (response.status == '1') {
                                    if(response.new_ids.length > 1)
                                    {
                                        notify('', 'success', response.success_msg);
                                    }
                                    else{
                                        query = $.param({'product_id': response.new_ids[0]});
                                        window.location.href = links['update'] + '?' + query;
                                    }
                                } else {
                                    if(response.errors.error) {
                                        displayErrors(response.errors.error);
                                    } else {
                                        displayErrors(response.errors);
                                    }
                                }
                            }
                        });

                    }
                }
            },// copy
            {
                extend: 'colvis',
                text: `<i class="fas fa-columns"></i> ${locales['dtb_custom_col']}`,
                className: 'dt-menu dt-list btn-colvis',
                columns: colsVis
            },// cols
            {
                text: `<i class="fas fa-percentage"></i> ${locales['dtb_tax']}`,
                className: 'dt-menu btn-tax',
                action: function (e, dt, node, congig) {
                    var selectedRows = dt.rows('.selected').data();
                    var form = $('#apply-tax-class-form');
                    $('#apply-tax-class-total-selected').html(selectedRows.length);

                    selectedRows.each(function (item) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'selected_products_ids[]',
                            value: item.product_id
                        }).appendTo(form);
                    });

                    $('#apply-tax-class-modal').modal('show');
                }
            },// tax
            {
                extend: 'collection',
                text: `<i class="fas fa-file-download"></i> ${locales['dtb_export_table']}`,
                className: `dt-menu dt-list btn-export ${freePlan ? "disabled plan-lock-btn" : ""}`,
                buttons: [
                    {
                        extend: 'copyHtml5',
                        text: locales['buttons']['copy'],
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: locales['buttons']['export2csv'],
                        fieldSeparator: ',',
                        extension: '.csv',
                        bom: "true",
                        exportOptions: {
                            columns: ':visible:not(.do-not-export-csv)',
                            orthogonal: 'export',
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: locales['buttons']['export2excel'],
                        exportOptions: {
                            columns: ':visible:not(.do-not-export-excel)',
                            orthogonal: 'export',
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        exportOptions: {
                            columns: ':visible:not(.do-not-export-pdf)'
                        },
                        text: locales['buttons']['export2pdfexcel']
                    }
                ]
            },// export
            {
                text: `<i class="far fa-copy"></i> ${locales['dtb_dropna_export']}`,
                className: 'dt-menu dropna-export disabled '+supplier_status,
                action: function (e, dt, node, config) {
                    var selectedRows = dt.rows('.selected').data();
                    var selectedIds = [];
                    selectedRows.each(function (item) {
                        selectedIds.push(item.product_id);
                    });
                    if (selectedIds.length > 0) {
                        //confirmMessage(function () {
                            $('#dropna_alert').fadeIn(100);
                            $('#dropna_wait').fadeIn(100);
                            $.ajax({
                                url: links['dropnaExport'],
                                type: 'post',
                                data: {selected: selectedIds},
                                dataType: 'JSON',
                                success: function (response) {

                                    //dt.rows('.selected').remove().draw();
                                    if (response.status == 'success') {

                                        $('#dropna_imported').text(0);
                                        $('#dropna_all').text(selectedIds.length);
                                        $('#dropna_wait').fadeOut(100);
                                        var dropnaTimer = setInterval(dropnaTimerAction, 5000);

                                        new PNotify({
                                            title: response.title,
                                            text: response.message,
                                            addclass: 'bg-success stack-top-right',
                                            stack: {"dir1": "down", "dir2": "right", "push": "top"}
                                        });
                                    } else {
                                        for (error in response.errors) {
                                            new PNotify({
                                                title: response.title,
                                                text: response.errors[error],
                                                addclass: 'bg-danger stack-top-right',
                                                stack: {"dir1": "down", "dir2": "right", "push": "top"}
                                            });
                                        }
                                    }
                                }
                            });
                        //});
                    }
                }
            },// dropna
            {
                text: `<i class='far fa-trash-alt'></i> ${locales['dtb_delete']}`,
                className: 'dt-menu bulk-delete disabled',
                action: function (e, dt, node, config) {
                    var selectedRows = dt.rows('.selected').data();
                    var selectedIds = [];
                    selectedRows.each(function (item) {
                        selectedIds.push(item.product_id);
                    });
                    if (selectedIds.length > 0) {
                        confirmMessage(function () {
                            $.ajax({
                                url: links['dtDelete'],
                                type: 'post',
                                data: {selected: selectedIds},
                                dataType: 'JSON',
                                beforeSend: function() {
                                    $('#datatableGrid').before('<div class="text-center loading" style="position: absolute;width: 100%;height: 100%;"><img src="view/image/blue-loading-gif-transparent-9.gif"  style="width: 100px" /></div>');
                                },
                                success: function (response) {
                                    $('.loading').remove();
                                    if (typeof response.status != 'undefined' && response.status == 'warning') {
                                        if (confirm(response.title)) {
                                            removeItems(selectedIds, 'y', dt);
                                            return;
                                        }
                                        return;
                                    }

                                    dt.rows('.selected').remove().draw();

                                    if (response.status == 'success') {
                                        $("#add_product").removeClass("plan-lock-btn");
                                        $("#add_product i").remove();
                                        new PNotify({
                                            title: response.title,
                                            text: response.message,
                                            addclass: 'bg-success stack-top-right',
                                            stack: {"dir1": "down", "dir2": "right", "push": "top"}
                                        });
                                    } else {
                                        for (error in response.errors) {
                                            new PNotify({
                                                title: response.title,
                                                text: response.errors[error],
                                                addclass: 'bg-danger stack-top-right',
                                                stack: {"dir1": "down", "dir2": "right", "push": "top"}
                                            });
                                        }
                                    }
                                }
                            }).done(function(){
                                    $('.loading').remove();
                                   backgroundDelete(); 
                            });
                        });
                    }
                }
            },// delete
        ],
        "order": [[2, "asc"]],
        columns: [
            {   //0
                title: `<input type="checkbox" class="styled dtSelectAll" onchange='toggleSelectAll(this);'>`,
                orderable: false,
                data: 'product_id',
                width: "50px"
            },
            {   //1
                data: "thumb",
                render: function (data, type, row) {
                    return `<img src="${row['thumb']}" height="50px">`;
                },
            },
            {   //2
                data: "name",
                name: 'pd2.localized_name',
                render: function (data, type, row) {
                    return `<a href='${links['update']}?product_id=${row['product_id']}'> ${row['name']}</a>`;
                }
            },//3
            {
                data: "categories_names", 
                // name: 'p.category_name',
                render: function (data, type, row) {
                    // console.log(row);
                    
                    if(!row.categories_names || !row.categories_ids ) return '';

                    var array_names = row.categories_names.split(',');
                    var array_ids = row.categories_ids.split(',');
                    //split ignore empty images names 
                    var array_images = row.categories_images ? row.categories_images.split(',').filter(function (x) { return x != ""; }) : '';
                    // console.log(data);
                    let template;
                    var templateScript;
                    var context;
                    var categories_html = '';

                    array_names.forEach(function(item , index){
                        template = $('#category-item').html();
                        templateScript = Handlebars.compile(template);
                        context = {
                            'category_name' : decodeHtml(item),
                            'id'            : array_ids[index],
                            'image'         : array_images[index]
                        };
                        categories_html += templateScript(context);
                    });


                    template       = $('#category-panel-template').html();
                    templateScript = Handlebars.compile(template);
                    context        = {"items" : categories_html};
                    var html       = templateScript(context);


                    return html;
                }
            },
            {data: "model", name: 'p.model'},//4
            {data: "barcode", name: 'p.barcode'},//5
            {data: "price", name: 'p.price'},//6
            
            {data: "order_quantity", name: 'p.order_quantity'},//7
            {data: "quantity", name: 'p.quantity'},//8
            
            {data: "status", name: 'p.status'},//9
            
            {data: "sku", name: 'p.sku'},//10
            {data: "date_added", name: 'p.date_added'},//11
            {data: "sellers", name: 'p.sellers'},//12

            {//13
                width: "30px",
                data: "product_id",
            },
            {//14
                data: "product_id",
            },


        ],
        columnDefs: [
            {
                orderable: false,
                className: 'select-checkbox',
                targets: 0
            },
            {
                targets: 1,
                orderable: false,
                render: function (data, type, row) {
                    // console.log(data);
                    // console.log(row);
                    return type == 'export' ? data : bindImage(data, row.image);
                }
            },
            {
                targets: 2,
                className: "product-name-column"
            },
            {
                targets: 9,
                render: function (data, type, row) {
                    var status = (data == "1" ? 'checked="checked"' : '');
                    console.log(row)
                    return statusSwitch(row.product_id, status, row.limit_reached == 1);
                }
            },

            {
                targets: 4,
                visible: false,
                searchable:true
            },
            {
                targets: 5,
                visible: false,
                searchable:true,
                render: function (data, type, row) {
                    if (row.barcode_image != 0) {
                        return '<div style="text-align:center"><img src="data:image/png;base64,'+row.barcode_image+'" style="width:150px;height:50px"/><br>'+row.barcode+'</div>';
                    }
                    return '';
                }
            },
            {
                targets: 7,
                visible: shouldShow
            },
            {
                targets: 10,
                visible: false,
                searchable:true
            },
            {
                targets: 11,
                visible: false,
                searchable:true
            },
            {
                targets: 12,
                visible: false,
                searchable:true
            },
            {
                targets: 13,
                orderable: false,
                selectable: false,
                render: function (data, type, row) {
                    return `<a data-popup="tooltip" title="${locales['text_preview']}" target="_blank" href="${links['preview']}${row['product_id']}" class='text-default'><i class='fa fa-eye fa-lg valign-middle'></i></a>`;
                }
            },
            {
                targets: 14,
                orderable: false,
                selectable: false,
                render: function (data, type, row) {
                    return `
                        <ul class="icons-list pull-right">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="icon-menu9"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href='${links['update']}?product_id=${row['product_id']}'><i class='icon-pencil7'></i> ${locales['button_edit']}</a></li>
                                    <li><a onclick="removeItem(this, 'n')" data-rowid="${row['product_id']}"><i class='far fa-trash-alt'></i> ${locales['button_delete']}</a></li>
                                </ul>
                            </li>
                        </ul>
                    `;
                }
                
            },
        ],
        initComplete: function(settings){
            var api = new $.fn.dataTable.Api( settings );

            // api.columns([10]).visible(true);
        },

    });


    table.on('draw', function () {
        $(".switch").bootstrapSwitch();
        if (Array.prototype.forEach) {
            var elems = Array.prototype.slice.call(document.querySelectorAll('.switchery'));
            elems.forEach(function(html) {
                var switchery = new Switchery(html);
                //debugger;
            });
        }
        else {
            var elems = document.querySelectorAll('.switchery');
            for (var i = 0; i < elems.length; i++) {
                var switchery = new Switchery(elems[i]);
            }
        }
        $(".switchery").on('change', function(e) {
            var status = this.checked ? locales['switch_text_enabled'] : locales['switch_text_disabled'];
            $(this).parent().children(".switchery-status").text(status);
        });

        $('[data-popup="tooltip"]').tooltip();
    $('[data-toggle="tooltip"]').tooltip();
    });

    $('.product-name-column').css({minWidth: '270px'});

    $('#datatableGrid').on( 'column-sizing.dt', function ( e, settings ) {
        $('.product-name-column a').removeAttr('style').css({
            width: $('.product-name-column').outerWidth() + "px",
            display: "inline-block",
            whiteSpace: "nowrap",
            textOverflow: "ellipsis",
            overflow: "hidden"
        });
    });

    $('[data-popup="tooltip"]').tooltip();
    $('[data-toggle="tooltip"]').tooltip();

    table.on('xhr', function (e, settings, json, xhr) {
        $('.totalHeading').text(json.heading);
    });

    $(".switch").bootstrapSwitch();

    $(".styled, .multiselect-container input").uniform({
        radioClass: 'choice'
    });

    table.on('select', function (e, objDT, type, indexes) {
        if (table.rows('.selected').any()) $(".bulk-delete, .btn-mass-edit, .btn-copy, .btn-category, .dropna-export").removeClass('disabled');
        else $(".bulk-delete, .btn-mass-edit, .btn-copy, .btn-category, .dropna-export").addClass('disabled');
    }).on('deselect', function (e, objDT, type, indexes) {
        if (table.rows('.selected').any()) $(".bulk-delete, .btn-mass-edit, .btn-copy, .btn-category, .dropna-export").removeClass('disabled');
        else $(".bulk-delete, .btn-mass-edit, .btn-copy, .btn-category, .dropna-export").addClass('disabled');
    }).on('search.dt', function (e, objDT) {
        if (table.rows('.selected').any()) $(".bulk-delete, .btn-mass-edit, .btn-copy, .btn-category, .dropna-export").removeClass('disabled');
        else $(".bulk-delete, .btn-mass-edit, .btn-copy, .btn-category, .dropna-export").addClass('disabled');
    });

    $('#insertWizard').on('show.bs.modal', function () {
        $(this).find('.modal-body').load('catalog/product/insert', function () {
            $('select').select2({
                minimumResultsForSearch: Infinity
            });

            $.fn.stepy.defaults.backLabel = '<i class="icon-arrow-left13 position-left"></i> ' + locales['buttons']['prev'];
            $.fn.stepy.defaults.nextLabel = locales['buttons']['next'] + ' <i class="icon-arrow-left13 position-right"></i>';

            $(".insertWizard").stepy({
                description: false
            });

            $('.insertWizard').find('.button-next').addClass('btn btn-primary');
            $('.insertWizard').find('.button-back').addClass('btn btn-default');

            $('.summernote').summernote();

            $(".switch").bootstrapSwitch();

            $('.form').submit(function (event) {
                event.preventDefault();

                var $formData = $(this).serialize();
                var $action = $(this).attr('action');

                $.ajax({
                    url: $action,
                    method: 'POST',
                    dataType: 'JSON',
                    data: $formData,
                    success: function (response) {

                        $('.help-block', $formGroup).text('');
                        $('.form-group').removeClass('has-error');

                        if (response.status == 'error') {
                            for (error in response.errors) {
                                var $formGroup = $('#' + error).parents('.form-group');
                                $formGroup.addClass('has-error');
                                $('.help-block', $formGroup).text(response.errors[error]);
                            }
                        }

                        if (response.status == 'success') {
                            window.location.href = links['list'];
                        }
                    }
                });
            });
        });
    });

    $('#insertWizard').on('hidden.bs.modal', function () {
        table.draw();
    });

    $('#datatableGrid_wrapper select').select2({
        minimumResultsForSearch: Infinity,
        width: 'auto'
    });
});

function changeStatus(id, status) {
    var newStatus = (status ? 1 : 0);
    $.ajax({
        url: links['dtUpdateStatus'],
        data: {id: id, status: newStatus},
        dataType: 'JSON',
        method: 'POST',
        success: function (response) {
            console.log(response)
            if (response.status == 'success') {
                new PNotify({
                    title: response.title,
                    text: response.message,
                    addclass: 'bg-success stack-top-right',
                    stack: {"dir1": "down", "dir2": "right", "push": "top"}
                });
            } else {
                for (error in response.errors) {
                    new PNotify({
                        title: response.title,
                        text: response.errors[error],
                        addclass: 'bg-danger stack-top-right',
                        stack: {"dir1": "down", "dir2": "right", "push": "top"}
                    });
                }
            }
        }
    });
}

function toggleSelectAll(checkbox)
{  
    if (checkbox.checked == true) {
        table.rows().select();
    } else {
        table.rows().deselect();
    }
}


function statusSwitch(id, status, limitReached) {
    return `<div class="checkbox checkbox-switchery  no-margin">
                <label>
                    <input type="checkbox" data-on-text="sameh" data-off-text="Amr" onchange="changeStatus(` + id + `, this.checked);" class="switchery  ${limitReached ? 'plan-lock-btn' : ''}" ` + status + `>
                    <span class="switchery-status">` + (status ? locales['switch_text_enabled'] : locales['switch_text_disabled']) + `</span>
                </label>
            </div>`;
}

function bindImage(thumb, image) {
    return '' +
        '<a href="' + image + '" class="media-left" data-popup="lightbox">' +
        '<img src="' + thumb + '">' +
        '<span class="zoom-image"><i class="icon-plus2"></i></span>' +
        '</a>' +
        '';
}

$(document).on('click', '.limit-switch', function() {
    $('#upgrade_plan_modal').modal('show')
})

function removeItem(row, hd) {
    var that = $(row);
    var rowId = that.data('rowid');
    var hardDelete = 'n';

    if (typeof hd != 'undefined' && hd == 'y') {
        hardDelete = 'y';
    }

    var remove = function () {
        $.ajax({
            url: links['dtDelete'],
            data: {selected: [rowId], hard_delete: hardDelete},
            dataType: 'JSON',
            method: 'POST',
            success: function (response) {

                if (typeof response.status != 'undefined' && response.status == 'warning') {
                    if (confirm(response.title)) {
                        removeItem(row, 'y');
                        return;
                    }
                    return;
                }

                table.row(that.parents('tr')).remove().draw();

                if (response.status == 'success') {
                    $("#add_product").removeClass("plan-lock-btn");
                    $("#add_product i").remove();
                    new PNotify({
                        title: response.title,
                        text: response.message,
                        addclass: 'bg-success stack-top-right',
                        stack: {"dir1": "down", "dir2": "right", "push": "top"}
                    });
                } else {
                    for (error in response.errors) {
                        new PNotify({
                            title: response.title,
                            text: response.errors[error],
                            addclass: 'bg-danger stack-top-right',
                            stack: {"dir1": "down", "dir2": "right", "push": "top"}
                        });
                    }
                }
            }
        });
    }

    if (hardDelete == 'n') {
        confirmMessage(function () {
            remove();
        });
    } else if (hardDelete === 'y') {
        remove();
    }
}

function removeItems(rows, hardDelete, dt) {

    $.ajax({
        url: links['dtDelete'],
        data: {selected: rows, hard_delete: hardDelete},
        dataType: 'JSON',
        method: 'POST', 
        beforeSend: function() {
            $('#datatableGrid').before('<div class="text-center loading" style="position: absolute;width: 100%;height: 100%;"><img src="view/image/blue-loading-gif-transparent-9.gif"  style="width: 100px" /></div>');
        },
        complete: function() {
            $('.loading').remove();
        },      
        success: function (response) {
            dt.rows('.selected').remove().draw();
            if (response.status == 'success') {
                new PNotify({
                    title: response.title,
                    text: response.message,
                    addclass: 'bg-success stack-top-right',
                    stack: {"dir1": "down", "dir2": "right", "push": "top"}
                });
            } else {
                for (error in response.errors) {
                    new PNotify({
                        title: response.title,
                        text: response.errors[error],
                        addclass: 'bg-danger stack-top-right',
                        stack: {"dir1": "down", "dir2": "right", "push": "top"}
                    });
                }
            }
        }
    });
}


$(document).on('click', 'button#mass-category-confirm-button', function(e) {
    e.preventDefault();
    $("#mass-category-confirm-button").attr("disabled", true);
    var categories = $('#mass-category-product_category-select').val();
    var overwrite = $('#mass-category-overwrite-categories-switch').is(':checked');

    if ( ! categories ) {
        alert("Please Select Categories!");
        return;
    }

    $.ajax({
        url: links['mass_category_update_link'],
        type: "POST",
        dataType: "JSON",
        data: {
            product_ids: table_selected_ids,
            product_category: categories,
            overwrite: overwrite
        },
        success: function( resp ) {
            notify(resp.title, 'success', resp.message);
            $('#mass-category-modal').modal('hide');
            $("#mass-category-confirm-button").attr("disabled", false);
        }
    });

    return;
});

if(dropna_timer){
    var dropnaTimer = setInterval(dropnaTimerAction, 5000);
}

function dropnaTimerAction() {
    $.ajax({
        url: links['dropnaScheduleDt'],
        data: {},
        dataType: 'JSON',
        method: 'GET',
        success: function (response) {
            if (response.status == '1') {
                $('#dropna_imported').text(response.data.dropna_import_success);
                $('#dropna_all').text(response.data.dropna_import_total);
                if(response.data.dropna_import_wait == '0')
                    clearInterval(dropnaTimer);
            }
            /*else{
                clearInterval(dropnaTimer);
            }*/
        }
    });
}

function updateDropnaAlert(){
    $.ajax({
        url: links['updateDropnaAlert'],
        data: {},
        dataType: 'JSON',
        method: 'GET',
        success: function (response) {}
    });
}

function backgroundDelete() {
    $.ajax({
        url: links['backgroundDelete'],
        data: {},
        type: 'post',
        dataType: 'JSON',
        success: function (response) {
            if (response.status == 'success') {
                // background deletion succeeded 
            }
        }
    });
}


// $(document).ready(function() {
//     // append dropdown
//     let btnsdrop = '';
//     btnsdrop += `<div class="dropdown table-btns-drop">`;
//     btnsdrop += `<button id="dt_btns_drop" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">`;
//     btnsdrop += `<i class="fas fa-ellipsis-h"></i>`;
//     btnsdrop += `</button>`;
//     btnsdrop += `<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dt_btns_drop">`;
//     btnsdrop += `</div>`;
//     btnsdrop += `</div>`;
//     if($('.dt-buttons').length > 0) {
//         $('.dt-buttons').append(btnsdrop);
//         if($(window).width() > 768) {
//             $('.dt-menu').appendTo('.table-btns-drop .dropdown-menu');
//         } else {
//             $('.dt-buttons > *:not(.table-btns-drop)').appendTo('.table-btns-drop .dropdown-menu');
//         }
//     }
// })