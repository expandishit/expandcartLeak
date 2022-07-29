var table;

$(document).ready(function () {

    table = $('#datatableGrid').DataTable({
        processing: true,
        serverSide: true,
        stateSave: true,
        autoWidth: false,
        dom: '<"datatable-header"fB><"datatable-scroll-wrap"t><"datatable-footer"lip>',
        language: {
            ...locales['dt_language'],
            search: ''
        },
        lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        ajax: {
            url: links['dtHandler'],
            type: 'post',
            complete: function(res) {
                res.responseJSON.recordsTotal == 0 ? location.reload() : '';
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
        buttons: [
            {
                extend: 'colvis',
                text: `<i class="fas fa-columns"></i> ${locales['dtb_custom_col']}`,
                className: 'btn-colvis',
                columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
            },// cols
            {
                extend: 'collection',
                text: `<i class="far fa-trash-alt"></i> ${locales['dtb_export_table']}`,
                className: 'btn-export',
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
                text: `<i class="fas fa-file-download"></i> ${locales['dtb_delete']}`,
                className: 'bulk-delete disabled',
                action: function (e, dt, node, config) {
                    var selectedRows = dt.rows('.selected').data();
                    var selectedIds = [];
                    selectedRows.each(function (item) {
                        selectedIds.push(item.voucher_id);
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
                                        dt.ajax.reload()
                                    }
                                }
                            });
                        });
                    }
                }
            },// delete
        ],
        columns: [
            {
                title: `<input type="checkbox" class="styled" id="toggleSelectAll">`,
                data: "voucher_id",
                width: "50px",
                sortable: false,
                orderable: false,
                render: function (data, type, row) {
                    return `<input type="checkbox" class="select-checkbox">`;
                }
            },
            {
                data: 'code',
                render: function (data, type, row) {
                    return `<a href="${links['update']}?voucher_id=${row['voucher_id']}">${row['code']}</a>`;
                }
            },
            {data: 'from_name'},
            {data: 'from_email'},
            {data: 'to_name'},
            {data: 'to_email'},
            {data: 'theme'},
            {data: 'amount'},
            {data: 'status'},
            {data: 'date_added'},
            {data: 'voucher_id'},
        ],
        order: [[1, "asc"]],
        columnDefs: [
            {
                targets: [3, 5, 6, 9],
                visible: false
            },
            {
                targets: 8,
                orderable: false,
                render: function (data, type, row) {
                    var status = (data == "1" ? 'checked="checked"' : '');
                    return statusSwitch(row.voucher_id, status);
                }
            },
            {
                sortable: false,
                searchable: false,
                targets: 10,
                render: function (data, type, row) {
                    return `<ul class="icons-list pull-right">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="icon-menu9"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a onclick="sendItem(this)" data-rowid="${row['voucher_id']}"> <i class="fas fa-envelope"></i> ${locales['button_send']}</a></li>
                                    <li><a href="${links['update']}?voucher_id=${row['voucher_id']}"><i class="icon-pencil7"></i> ${locales['button_edit']}</a></li>
                                    <li><a onclick="removeItem(this);" data-rowid="${row['voucher_id']}"><i class='icon-trash'></i> ${locales['button_delete']}</a></li>
                                </ul>
                            </li>
                        </ul>
                    `;
                }
            }
        ],
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
    });

    $(".styled, .multiselect-container input").uniform({
        radioClass: 'choice'
    });

    $('[data-popup="tooltip"]').tooltip();
    $('[data-toggle="tooltip"]').tooltip();

    table.on('select', function (e, objDT, type, indexes) {
        if (table.rows('.selected').any()) $(".bulk-delete").removeClass('disabled');
        else $(".bulk-delete").addClass('disabled');
    }).on('deselect', function (e, objDT, type, indexes) {
        if (table.rows('.selected').any()) $(".bulk-delete").removeClass('disabled');
        else $(".bulk-delete").addClass('disabled');
    }).on('search.dt', function (e, objDT) {
        if (table.rows('.selected').any()) $(".bulk-delete").removeClass('disabled');
        else $(".bulk-delete").addClass('disabled');
    });

    $('#toggleSelectAll').click(function () {
        var checkbox = this;
        if (checkbox.checked == true) {
            table.rows().select();
        } else {
            table.rows().deselect();
        }
    });

    var historyTable = $('#historyGrid').DataTable({
        dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
        autoWidth: false,
        language: locales['dt_language'],
        lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
    });

    $('select').select2({
        minimumResultsForSearch: Infinity,
        width: 'auto'
    });
});

function changeStatus(id, status) {
    var newStatus = (status ? 1 : 0);
    ajax(links['dtUpdateStatus'], {id: id, status: newStatus});
}


function statusSwitch(id, status) {
    return `<div class="checkbox checkbox-switchery  no-margin">
            <label>
                <input type="checkbox" data-on-text="sameh" data-off-text="Amr" onchange="changeStatus(` + id + `, this.checked);" class="switchery" ` + status + `>
                <span class="switchery-status">` + (status ? locales['switch_text_enabled'] : locales['switch_text_disabled']) + `</span>
            </label>
        </div>
    `;
}

function sendItem(row) {
    var that = $(row);
    var rowId = that.data('rowid');

    $.ajax({
        url: links['send'] + '?voucher_id=' + rowId,
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                new PNotify({
                    title: response.title,
                    text: response.success,
                    addclass: 'bg-success stack-top-right',
                    stack: {"dir1": "down", "dir2": "right", "push": "top"}
                });
            } else if (response.error) {
                new PNotify({
                    title: response.title,
                    text: response.error,
                    addclass: 'bg-danger stack-top-right',
                    stack: {"dir1": "down", "dir2": "right", "push": "top"}
                });
            }
        }
    });
}

function removeItem(row) {
    var that = $(row);
    var rowId = that.data('rowid');

    confirmMessage(function () {
        ajax(links['dtDelete'], {selected: [rowId]}, function (res) {
            var response = JSON.parse(res);
            if (response.success == '1') {
                dt.ajax.reload()
            }
        });
    });
}