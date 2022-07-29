var table;

$(document).ready(function () {
    var couponAdmincolumns = [
        {
            title: `<input type="checkbox" class="styled" id="toggleSelectAll">`,
            orderable: false,
            data: "coupon_id",
            width: "50px"
        },
        {
            data: 'name',
            render: function (data, type, row) {

                return `<a href='`+ links['update'] +`?coupon_id=${row['coupon_id']}'>${row['name']}</a>`;
            }
        },
        {data: 'code'},
        {data: 'discount'},
        {data: 'date_start'},
        {data: 'date_end'},
        {data: 'type'},
        {data: 'automatic_apply'},
        {data: 'status'},
        {data: 'coupon_id'},
    ];
    var affiliateObj = {
        data:'affiliate' ,
        render: function (data, type, row) {
            if(row['affiliate'] && row['affiliate_id'] )
                return `<a href='`+ links['affiliate_link'] +`?affiliate_id=${row['affiliate_id']}'>${row['affiliate']}</a>`;
            else
                return '#';
        }
    };
    var statusColumnInex = 8 ;
    var EditColumnIndex = 9;
    if ('undefined' !== typeof affiliate_promo && affiliate_promo)
    {
        statusColumnInex = 9 ;
        EditColumnIndex = 10;
        couponAdmincolumns.splice(2, 0, affiliateObj);
    }


    table = $('#datatableGrid').DataTable({
        autoWidth: false,
        dom: '<"datatable-header"fB><"datatable-scroll-wrap"t><"datatable-footer"lip>',
        language: {
            ...locales['dt_language'],
            search: ""
        },
        lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        ajax: {
            url: links['dtHandler'],
            type: 'post',
            complete: function(res) {
//                res.responseJSON.recordsTotal == 0 ? location.reload() : ''; // comment this line because in case no data it's keeps reload
                // console.log(res.responseJSON, 'dsjokdsj')
            },
            error: function (e, m, l) {
                $(".datatables_country-error").html("");
                $("#datatables_country").append('<tbody class="datatables_country-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                $("#datatables_country_processing").css("display", "none");
            }
        },
        drawCallback: function () {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
        },
        preDrawCallback: function() {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
        },
        select: {
            style: 'multi',
            selector: 'td:first-child'
        },
        createdRow: function (row, data, dataIndex) {
            $(row).attr('data-rowid', data['coupon_id']);
        },
        buttons: [
            {
                text: `<i class='icon-copy3'></i> ${locales['dtb_copy']}`,
                className: 'btn-copy disabled',
                action: function (e, dt, node, congig) {
                    var selectedRows = dt.rows('.selected').data();
                    var selectedIds = [];
                    selectedRows.each(function (item) {
                        selectedIds.push(item.coupon_id);
                    });

                    if (selectedIds.length > 0) {
                        $.ajax({
                            url: links['copy'],
                            type: 'post',
                            data: {selected: selectedIds},
                            dataType: 'JSON',
                            success: function (response) {

                                if (response.status == '1') {
                                    if(response.new_ids.length > 1)
                                    {
                                        notify('', 'success', response.success_msg);
                                    }
                                    else{
                                        query = $.param({'coupon_id': response.new_ids[0]});
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
                text: `<i class='icon-pencil7'></i>${locales['dtb_edit']}`,
                className: "bulk-coupon-update disabled",
                action(e, dt, node, config) {
                    $('#bulk-coupon-update').modal('toggle');
                }
            },// edit
            {
                extend: 'colvis',
                text: `<i class="fas fa-columns"></i> ${locales['dtb_custom_col']}`,
                className: 'dt-menu dt-list btn-colvis',
                columns: [1,2,3,4,5]
            },// cols
            {
                extend: 'collection',
                text: `<i class="fas fa-file-download"></i> ${locales['dtb_export_table']}`,
                className: `dt-menu btn-export ${freePlan ? "disabled plan-lock-btn" : ""}`,
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
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: locales['buttons']['export2excel'],
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: locales['buttons']['export2pdf'],
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ]
            },// export
            {
                text: `<i class='far fa-trash-alt'></i> ${locales['dtb_delete']}`,
                className: 'dt-menu bulk-delete disabled',
                action: function (e, dt, node, config) {
                    var selectedRows = dt.rows('.selected').data();
                    var selectedIds = [];
                    selectedRows.each(function (item) {
                        selectedIds.push(item.coupon_id);
                    });
                    if (selectedIds.length > 0) {
                        confirmMessage(function () {
                            $.ajax({
                                url: links['dtDelete'],
                                type: 'post',
                                data: {selected: selectedIds},
                                success: function (resp) {
                                    var response = JSON.parse(resp);

                                    if (response.success == '1') {
                                        $("#add_coupon").removeClass("plan-lock-btn");
                                        $("#add_coupon i").remove();
                                        dt.ajax.reload()
                                    }
                                }
                            });
                        });
                    }
                }
            },// delete
        ],
        order: [[1, "asc"]],
        columns: couponAdmincolumns,
        columnDefs: [
            {
                orderable: false,
                className: 'select-checkbox',
                targets: 0
            },
            {
                width: '30%',
                targets: [1]
            },
            {
                width: '30%',
                targets: [2],
                render: function (data, type, row) {
                    if (row.automatic_apply=="0"){
                        return row.code;
                    }
                    return `<i class="fa fa-minus" style="color: #ddd"></i>`;
                }
            },
            {
                targets: statusColumnInex,
                orderable: false,
                render: function (data, type, row) {
                    var status = (data == "1" ? 'checked="checked"' : '');
                    return statusSwitch(row.coupon_id, status, row.limit_reached == 1);
                }
            },
            {
                targets: [6],
                render: function (data, type, row) {
                    if (row.shipping == 1 && !(row.discount > 0)){
                        return locales['free_shipping'];
                    }else {
                        if (row.type == "F"){
                            return locales['text_amount'];
                        }
                        if (row.type == "P"){
                            return locales['text_percent'];
                        }
                        if (row.type == "B"){
                            return locales['buy_x_get_y'];
                        }
                    }
                }
            },
            {
                width: '30%',
                targets: [7],
                render: function (data, type, row) {
                    if (row.automatic_apply=="1"){
                        return `YES`;
                    }
                    return `NO`;
                }
            },
            {
                sortable: false,
                searchable: false,
                targets: EditColumnIndex,
                render: function (data, type, row) {
                    return `<ul class="icons-list pull-right">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="icon-menu9"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href='`+ links['update'] +`?coupon_id=${row['coupon_id']}'><i class='icon-pencil7'></i> Edit</a></li>
                                    <li><a onclick="removeItem(${row['coupon_id']})"><i class='far fa-trash-alt'></i> Delete</a></li>
                                </ul>
                            </li>
                        </ul>
                    `;
                }
            }
        ],
    });

    table.on('draw', function () {
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

        $(".switch").bootstrapSwitch();
    });

    $(".styled, .multiselect-container input").uniform({
        radioClass: 'choice'
    });
    
    $('[data-popup="tooltip"]').tooltip();
    $('[data-toggle="tooltip"]').tooltip();

    table.on('select', function (e, objDT, type, indexes) {
        if (table.rows('.selected').any()) $(".bulk-delete, .btn-copy, .bulk-coupon-update").removeClass('disabled');
        else $(".bulk-delete, .btn-copy, .bulk-coupon-update").addClass('disabled');
    }).on('deselect', function (e, objDT, type, indexes) {
        if (table.rows('.selected').any()) $(".bulk-delete, .btn-copy, .bulk-coupon-update").removeClass('disabled');
        else $(".bulk-delete, .btn-copy,.bulk-coupon-update").addClass('disabled');
    }).on('search.dt', function (e, objDT) {
        if (table.rows('.selected').any()) $(".bulk-delete, .btn-copy, .bulk-coupon-update").removeClass('disabled');
        else $(".bulk-delete, .btn-copy, .bulk-coupon-update").addClass('disabled');
    });

    $('#toggleSelectAll').click(function () {
        var checkbox = this;
        if (checkbox.checked == true) {
            table.rows().select();
        } else {
            table.rows().deselect();
        }
    });

    var history = $('#historyGrid').DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        dom: '<"datatable-header"><"datatable-scroll"t><"datatable-footer"lip>',
        language: locales['dt_language'],
        lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        ajax: {
            url: links['dtHistoryHandler'],
            type: 'get',
            data: {
                coupon_id: (typeof coupon_id !== 'undefined')?coupon_id:""
            },
            complete: function(res) {
//                res.responseJSON.recordsTotal == 0 ? location.reload() : ''; // comment this line because in case no data it's keeps reload
            },
            error: function (e, m, l) {
                $(".datatables_country-error").html("");
                $("#datatables_country").append('<tbody class="datatables_country-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                $("#datatables_country_processing").css("display", "none");
            }
        },
        order: [[1, "asc"]],
        columns: [
            {
                data: 'order_id',
                render: function (data, type, row) {
                    return `<a class="text-semibold" href="sale/order/info?order_id=${row['order_id']}" target="_blank"># ${row['order_id']}</a>`;
                }
            },
            {data: 'order_products'},
            {data: 'customer_name'},
            {data: 'amount'},
            {data: 'total'},
            {data: 'commission'},
            {data: 'date_added'}
        ]
    });

    $('select').select2({
        minimumResultsForSearch: Infinity,
        width: 'auto'
    });

    $('#type').select2({
        minimumResultsForSearch: Infinity,
    });

    $('.touchspinney').TouchSpin({
        'postfix': $(this).attr('data-postfix'),
        'forcestepdivisibility': 'none',
        'decimals': 2,
        'max': Infinity,
        'min': -Infinity
    });

    $('#bulk-coupon-update').on('show.bs.modal', function (ev) {
        let r = $(ev.relatedTarget), c = $(ev.currentTarget);

        var selectedRows = table.rows('.selected').data();
        selectedRows.each(function(item) {
            if(item.hasOwnProperty("affiliate_id")&&item.affiliate_id && /^\d+$/.test(item.affiliate_id)){
                c.find('form').append(
                    `<input type="hidden" class="coupon_id_for_status" name="affiliate_id[]" value="${item.affiliate_id}" />`
                )
            }
            c.find('form').append(
                `<input type="hidden" class="coupon_id_for_status" name="coupon_id[]" value="${item.coupon_id}" />`
            );
        });
    });

    $('#bulk-coupon-update').on('hide.bs.modal', function (ev) {
        let r = $(ev.relatedTarget), c = $(ev.currentTarget);

        c.find('form').find('.coupon_id_for_status').remove();
    });


    $('#bulk-coupon-status-form-trigger').click(function () {

        let f = $('#bulk-coupon-status-form');
        let d = f.serialize();
        let u = f.attr('action');

        $('#bulk-coupon-update').find('.modal-errors').html('');

        $.ajax({
            url: u,
            data: d,
            method: 'POST',
            dataType: 'JSON',
            success: (r) => {
                if (typeof r.status != 'undefined' && r.status == 'OK') {
                    table.rows('.selected').deselect();
                    table.ajax.reload();
                    $('#bulk-coupon-update').modal('toggle');
                    return;
                }

                for (i = 0; i < r.errors.length; i++) {
                    let e = r.errors[i];
                    $('#bulk-coupon-update').find('.modal-errors').append(
                        `<div class="alert alert-danger">${e}</div>`
                    );
                }
            }
        });

    });
});


function changeStatus(id, status) {
    var newStatus = (status ? 1 : 0);
    ajax(links['dtUpdateStatus'], {id: id, status: newStatus});
}

function statusSwitch(id, status, limitReached) {
    return `<div class="checkbox checkbox-switchery  no-margin">
            <label>
                <input type="checkbox" data-on-text="sameh" data-off-text="Amr" onchange="changeStatus(` + id + `, this.checked);" class="switchery ${limitReached ? 'plan-lock-btn' : ''}" ` + status + `>
                <span class="switchery-status">` + (status ? locales['switch_text_enabled'] : locales['switch_text_disabled']) + `</span>
            </label>
        </div>`;
}

function removeItem(row) {
    confirmMessage(function () {
        ajax(links['dtDelete'], {selected: [row]}, function (res) {
            var response = JSON.parse(res);
            if (response.success == '1') {
                $("#add_coupon").removeClass("plan-lock-btn");
                $("#add_coupon i").remove();
                table.ajax.reload()
            }
        });
    });
}

$(document).on('click', '.limit-switch', function() {
    $('#upgrade_plan_modal').modal('show')
})