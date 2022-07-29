// var table;

$(document).ready(function () {

    table1 = $('#ListTransactions').DataTable({
        processing: true,
        serverSide: true,
        stateSave: false,
        autoWidth: false,
        // lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        ajax: {
            url: links['dtTransactionHandler'],
            type: 'post',
            beforeSend: function() {
                $('#ListTransactions').before('<div class="text-center loading" style="position: absolute;width: 100%;height: 100%;"><img src="view/image/blue-loading-gif-transparent-9.gif"  style="width: 100px" /></div>');
            },
            complete: function(res) {
                $('.loading').remove();
                res.responseJSON.recordsTotal == 0 ? 0 : '';
                $('.refund_button').click(function(e){
                    $("input").parent().removeClass('has-error')
                    $("input").next().empty()
                    $("textarea").parent().removeClass('has-error')
                    $("textarea").next().empty()
                    $('#refund_error').hide()
                    var amountCurrency = $(this).closest('tr').children()[6].innerHTML.split(' ')
                    var amount = amountCurrency[0]
                    var currency = amountCurrency[1]
                    var transaction_id = $(this).closest('tr').children()[1].innerHTML
                    $('input[name=transaction_amount]').val(amount);
                    $('input[name=amount]').val(amount);
                    $('input[name=currency]').val(currency);
                    $('input[name=transaction_id]').val(transaction_id);
                    $('#refund_success').hide();
                })
            
            },
            error: function (e, m, l) {
                $(".datatables_country-error").html("");
                $("#datatables_country").append('<tbody class="datatables_country-error">' +
                    '<tr><th colspan="3">' + locales['no_data_found'] + '</th></tr></tbody>');
                $("#datatables_country_processing").css("display", "none");
            },
        },
        dom: '<"datatable-header"fB><"datatable-scroll-wrap"t><"datatable-footer"lip>',
        language: locales['dt_language'],
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

        buttons: [
            {
                extend: 'collection',
                text: '<i class="icon-drawer-out"></i>',
                className: 'btn btn-default btn-export',
                buttons: [
                    {
                        extend: 'copyHtml5',
                        text: locales['buttons']['copy'],
                        exportOptions: {
                            columns: ':visible(:not(.select-checkbox))'
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: locales['buttons']['export2csv'],
                        fieldSeparator: ',',
                        extension: '.csv',
                        bom: "true",
                        exportOptions: {
                            columns: ':visible(:not(.select-checkbox))'
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: locales['buttons']['export2excel'],
                        exportOptions: {
                            columns: ':visible(:not(.select-checkbox))'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        exportOptions: {
                            columns: ':visible(:not(.select-checkbox))'
                        },
                        text: locales['buttons']['export2pdfexcel']
                    }
                ]
            },
            {
                extend: 'colvis',
                text: '<i class="icon-grid3"></i>',
                className: 'btn btn-default btn-colvis',
                columns: [6, 7, 8, 9,10]
            }
        ],
        columns: [
            {
                title:`<input type="checkbox" class="styled" id="toggleSelectAll_Transactions">`,
                orderable: false,
                width: "50px",
                data: 'id',
                render: function(data, type, row) {
                   return `<input type="checkbox" row-id="${row['id']}" class="select-checkbox">`;
                }
            },
            {data:'transaction_id'},
            {
                data: 'type',
                render:function(data,type,row){
                    if(row['type'] == 'refund')
                        return `<span class='label label-warning'>${lang['type_refund']}</span>`
                    else
                        return `<span class='label label-success'>${lang['type_payment']}</span>`

                }

            },
            {
                render:function(data,type,row){
                    if(row['status'] == 'successed')
                        return `<span class='label label-success'>${lang['status_successed']}</span>`
                    else if(row['status'] == 'failed')
                        return `<span class='label label-danger'>${lang['status_failed']}</span>`
                    else if(row['status'] == 'refused')
                        return `<span class='label label-danger'>${lang['status_refused']}</span>`
                    else
                        return `<span class='label label-warning'>${lang['status_pending']}</span>`

                }

            },
            {
                render:function(data,type,row){
                    if(row['custom_fields']['customer_refrence'] == undefined)
                        return "N/A"

                    var id =  row['custom_fields']['customer_refrence'];
                    return `<a href='${window.location.origin}/admin/sale/order/info?order_id=${id}'>#${id}</a>`;
                }

            },
            {
                data:'amount',
                render:function(data,type,row){
                    if(row['custom_fields']['order_amount'] == undefined)
                        return "N/A"

                    return row['custom_fields']['order_amount'] + ' ' +row['custom_fields']['order_currency'];

                }

            },
            {
                data:'amount',
                render:function(data,type,row){
                    if(row['amount'] == undefined)
                        return "N/A"

                    return row['amount'];

                }

            },
            {
                data:'amount',
                render:function(data,type,row){
                    return parseFloat(row['fees']);

                }
            },
            {
                data:'amount',
                render:function(data,type,row){
                    var net = row['amount'] - row['fees']
                    return parseFloat(net).toFixed(3);
                }
            },
            // {data:'custom_fields.payment_method',"defaultContent": "<i>N/A</i>"},
            // {data:'custom_fields.customer_name',"defaultContent": "<i>N/A</i>"},
            {
                data:'created_at',
                render:function(data,type,row){
                    return convertTZ(row.created_at,links['time_zone'])
                }
            },
            {
                render: function(data, type, row) {
                    if(row.type == 'refund' || row.status == 'pending')
                        return `<a data-toggle="modal" data-target="#Refund" class='refund_button' id='refund_button' style='display:none'>${links['column_refund']}</a>`;
                    else
                        return `<a data-toggle="modal" data-target="#Refund" class='refund_button' id='refund_button'>${links['column_refund']}</a>`;
                } 
            }
            

        ],

        order: [[1, "desc"]],
        columnDefs: [
            {
                targets: [0,1,2,3,4,5,6,7,8,9,10],
                orderable: false,
            },
            {
                targets: 2,
                createdCell: function (td, cellData, rowData, row, col) {
                    if ( rowData['type'] == 'payment' ) {
                        $(td).addClass('payment-text-td')
                    }else{
                        $(td).addClass('refund-text-td')
                    }
                }
            },
            {
                targets: [5,6,7,8],
                createdCell: function (td, cellData, rowData, row, col) {
                    $(td).addClass('dir-ltr');
                }
            },
        ],
    });

    table2 = $('#AccountStatement').DataTable({
        processing: true,
        serverSide: true,
        stateSave: false,
        autoWidth: false,
        dom: '<"datatable-header"fB><"datatable-scroll-wrap"t><"datatable-footer"lip>',
        language: locales['dt_language'],
        // lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        // drawCallback: function () {
        //     $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
        // },
        // preDrawCallback: function() {
        //     $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
        // },
        ajax: {
            url: links['dtWithDrawHandler'],
            type: 'post',
            beforeSend: function() {
                $('#AccountStatement').before('<div class="text-center loading" style="position: absolute;width: 100%;height: 100%;"><img src="view/image/blue-loading-gif-transparent-9.gif"  style="width: 100px" /></div>');
            },
            complete: function(res) {
                $('.loading').remove();
                res.responseJSON.recordsTotal == 0 ? 0 : '';
            },
            error: function (e, m, l) {
                $(".datatables_country-error").html("");
                $("#datatables_country").append('<tbody class="datatables_country-error">' +
                    '<tr><th colspan="3">' + locales['no_data_found'] + '</th></tr></tbody>');
                $("#datatables_country_processing").css("display", "none");
            }
        },
        select: {
            style: 'multi',
            selector: 'td:first-child'
        },
        buttons: [
            {
                extend: 'collection',
                text: '<i class="icon-drawer-out"></i>',
                className: 'btn btn-default btn-export',
                buttons: [
                    {
                        extend: 'copyHtml5',
                        text: locales['buttons']['copy'],
                        exportOptions: {
                            columns: ':visible(:not(.select-checkbox))'
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: locales['buttons']['export2csv'],
                        fieldSeparator: ',',
                        extension: '.csv',
                        bom: "true",
                        exportOptions: {
                            columns: ':visible(:not(.select-checkbox))'
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: locales['buttons']['export2excel'],
                        exportOptions: {
                            columns: ':visible(:not(.select-checkbox))'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        exportOptions: {
                            columns: ':visible(:not(.select-checkbox))'
                        },
                        text: locales['buttons']['export2pdfexcel']
                    }
                ]
            },
            {
                extend: 'colvis',
                text: '<i class="icon-grid3"></i>',
                className: 'btn btn-default btn-colvis',
                columns: [1, 2, 3, 4]
            },
            {
                text: "request withdraw",
                className: 'btn btn-primary btn-withdraw hide-withdraw',
            }
        ],
        columns:[
            {
                title:`<input type="checkbox" class="styled" id="toggleSelectAll_Statement">`,
                orderable: false,
                width: "50px",
                data: 'id',
                render: function(data, type, row) {
                   return `<input type="checkbox" row-id="${row['id']}" class="select-checkbox">`;
                }

            },
            {data:'Withdrawal_id'},
            {
                render:function(data,type,row){
                    if(row['status'] == 'successed')
                        return `<span class='label label-success'>${lang['status_successed']}</span>`
                    else if(row['status'] == 'failed')
                        return `<span class='label label-danger'>${lang['status_failed']}</span>`
                    else if(row['status'] == 'refused')
                        return `<span class='label label-danger'>${lang['status_refused']}</span>`
                    else
                        return `<span class='label label-warning'>${lang['status_pending']}</span>`

                }

            },
            {
                data:'amount',
                render:function(data,type,row){
                    return row['amount'];
                }
            },
            // {data:'currency'},
            {
                data:'created_at',
                render:function(data,type,row){
                    return convertTZ(row.created_at,links['time_zone'])
                }

            },
            // {data:'send_to',"defaultContent": "<i>N/A</i>"}

        ],
        "order": [[1, "asc"]],
        columnDefs: [
            {
                orderable: false,
                targets: [0,1,2,3,4]
            },
            {
                targets: 3,
                createdCell: function (td, cellData, rowData, row, col) {
                    $(td).addClass('dir-ltr');
                }
            },
        ],
    });

    $('.dataTables_filter').append(`<a  class="btn-sidebar-filter btn btn-default"><i class='icon-filter3'></i> </a>`)

    $('.btn-sidebar-filter').on('click', function (e) {
        $(this).closest('.has-adv-search').toggleClass('sidebar-filter-open');
    });

    $('#resetList').on('click', function (e) {
        $(this).closest('.has-adv-search').removeClass('sidebar-filter-open');
    });

    $(".bulk-delete").attr({
        "data-popup": "tooltip",
        // "title": locales['bulk_delete']
    });
    $(".btn-export").attr({
        "data-popup": "tooltip",
        "title": locales['export']
    });
    $(".btn-colvis").attr({
        "data-popup": "tooltip",
        "title": locales['colvis']
    });

    $('[data-popup="tooltip"]').tooltip();
    // table.on('draw', function () {
    //     if (Array.prototype.forEach) {
    //         var elems = Array.prototype.slice.call(document.querySelectorAll('.switchery'));
    //         elems.forEach(function(html) {
    //             var switchery = new Switchery(html);
    //             //debugger;
    //         });
    //     }
    //     else {
    //         var elems = document.querySelectorAll('.switchery');
    //         for (var i = 0; i < elems.length; i++) {
    //             var switchery = new Switchery(elems[i]);
    //         }
    //     }
    //     $(".switchery").on('change', function(e) {
    //         var status = this.checked ? locales['switch_text_enabled'] : locales['switch_text_disabled'];
    //         $(this).parent().children(".switchery-status").text(status);
    //     });
    //     // $(".switch").bootstrapSwitch();
    // });

    // $(".styled, .multiselect-container input").uniform({
    //     radioClass: 'choice'
    // });

    // table.on('select', function (e, objDT, type, indexes) {
    //     if (table.rows('.selected').any()) $(".bulk-delete").removeClass('disabled');
    //     else $(".bulk-delete").addClass('disabled');
    // }).on('deselect', function (e, objDT, type, indexes) {
    //     if (table.rows('.selected').any()) $(".bulk-delete").removeClass('disabled');
    //     else $(".bulk-delete").addClass('disabled');
    // }).on('search.dt', function (e, objDT) {
    //     if (table.rows('.selected').any()) $(".bulk-delete").removeClass('disabled');
    //     else $(".bulk-delete").addClass('disabled');
    // });

    $('#toggleSelectAll_Transactions').click(function () {
        var checkbox = this;
        if (checkbox.checked == true) {
            table1.rows().select();
        } else {
            table1.rows().deselect();
        }
    });

    $('#toggleSelectAll_Statement').click(function () {
        var checkbox = this;
        if (checkbox.checked == true) {
            table2.rows().select();
        } else {
            table2.rows().deselect();
        }
    });

    

    $('#modal_insert').on('show.bs.modal', function () {
        $(this).find('.modal-body').load(links['insert'], function () {
            $('select').select2({
                minimumResultsForSearch: Infinity
            });

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
                            $('#modal_insert').modal('hide');

                            new PNotify({
                                title: response.title,
                                text: response.message,
                                addclass: 'bg-success stack-top-right',
                                stack: {"dir1": "down", "dir2": "right", "push": "top"}
                            });
                        }
                    }
                });
            });
        });
    });

    $('#modal_update').on('show.bs.modal', function (e) {

        var remoteLink = $(e.relatedTarget).data('remotelink');

        $(this).find('.modal-body').load(remoteLink, function (response) {
            $('select').select2({
                minimumResultsForSearch: Infinity
            });

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
                            $('#modal_update').modal('hide');

                            new PNotify({
                                title: response.title,
                                text: response.message,
                                addclass: 'bg-success stack-top-right',
                                stack: {"dir1": "down", "dir2": "right", "push": "top"}
                            });
                        }
                    }
                });
            });

        });
    });

    $('#modal_update, #modal_insert').on('hidden.bs.modal', function () {
        table.draw();
    });

    $('select').select2({
        minimumResultsForSearch: Infinity,
        width: 'auto'
    });

    if($('html').attr('lang') == 'ar') {
        $('.btn-withdraw').html('طلب سحب ');
    }else {
        $('.btn-withdraw').html('Request Withdraw'); } 

    if($('html').attr('lang') == 'ar') {
        $('.btn-refund').html('استرداد');
    }else {
        $('.btn-refund').html('Refund'); } 
});

function changeStatus(id, status) {
    var newStatus = (status ? 1 : 0);
    $.ajax({
        url: links['dtUpdateStatus'],
        data: {id: id, status: newStatus},
        dataType: 'JSON',
        method: 'POST',
        success: function (response) {
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

function btnEditItem(id) {
    return `<a data-toggle="modal" data-target="#modal_update" data-remotelink="` + links['update'] + `?language_id=` + id + `" title="` + locales['button_edit'] + `">
                <i class="icon-pencil7 position-left"></i> ${locales['button_edit']}
            </a>`;
}

function btnDeleteItem(id) {
    return `<a title="` + locales['button_delete'] + `" onclick="removeItem(this);" data-rowid="` + id + `">
                <i class="icon-trash position-left"></i> ${locales['button_delete']}
            </a>`;
}

function statusSwitch(id, status) {
    return `<div class="checkbox checkbox-switchery  no-margin">
                <label>
                    <input type="checkbox" data-on-text="sameh" data-off-text="Amr" onchange="changeStatus(` + id + `, this.checked);" class="switchery" ` + status + `>
                    <span class="switchery-status">` + (status ? locales['switch_text_enabled'] : locales['switch_text_disabled']) + `</span>
                </label>
            </div>`;
    // return '<div class="checkbox checkbox-switch">' +
    //     '<label>' +
    //     '<input type="checkbox" class="switch" data-on-color="success" ' +
    //     'onchange="changeStatus(' + id + ', this.checked);" data-on-text="' + locales['switch_text_enabled'] + '" ' +
    //     'data-off-text="' + locales['switch_text_disabled'] + '" ' + status + ' >' +
    //     '</label>' +
    //     '</div>';
}

function removeItem(row) {
    var that = $(row);
    var rowId = that.data('rowid');

    confirmMessage(function () {
        $.ajax({
            url: links['dtDelete'],
            data: {selected: [rowId]},
            dataType: 'JSON',
            method: 'POST',
            success: function (response) {
                table.row(that.parents('tr')).remove().draw();

                if (response.status == 'success') {
                    new PNotify({
                        title: response.title,
                        text: response.message,
                        addclass: 'bg-success stack-top-right',
                        stack: {"dir1": "down", "dir2": "right", "push": "top"}
                    });
                } else {
                    new PNotify({
                        title: response.title,
                        text: response.message,
                    addclass: 'bg-danger stack-top-right',
                        stack: {"dir1": "down", "dir2": "right", "push": "top"}
                    });
                }
            }
        });
    });
}

function toggleSelectAll(checkbox) {
    if (checkbox.checked == true) {
        table.rows().select();
    } else {
        table.rows().deselect();
    }
}
// timeZone converter
function convertTZ(date, tzString) {
    var date=  new Date((typeof date === "string" ? new Date(date) : date).toLocaleString( {timeZone: tzString}));   
    return date.toLocaleString();
}
