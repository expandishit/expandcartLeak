var table;

$(document).ready(function () {

    table = $('#datatableGrid').DataTable({
        stateSave: true,
        autoWidth: false,
        dom: '<"datatable-header"fB><"datatable-scroll"t><"datatable-footer"lip>',
        language: locales['dt_language'],
        lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        data: dataSource,
        select: {
            style: 'multi',
            selector: 'td:first-child'
        },
        buttons: [
            {
                text: "<i class='icon-trash'></i>",
                className: 'btn btn-default bulk-delete disabled',
                action: function (e, dt, node, config) {
                    var selectedRows = dt.rows('.selected').data();
                    var selectedIds = [];
                    selectedRows.each(function (item) {
                        selectedIds.push(item.voucher_theme_id);
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
                                        dt.rows('.selected').remove().draw();
                                    }
                                }
                            });
                        });
                    }
                }
            },
            {
                extend: 'collection',
                text: '<i class="icon-drawer-out"></i>',
                className: 'btn btn-default btn-export',
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
            },
            {
                extend: 'colvis',
                text: '<i class="icon-grid3"></i>',
                className: 'btn btn-default btn-colvis',
                columns: [1]
            }
        ],
        order: [[1, "asc"]],
        columns: [
            {
                title: `<input type="checkbox" class="styled" id="toggleSelectAll">`,
                orderable: false,
                data: "voucher_theme_id",
                width: "50px"
            },
            {
                data: 'name',
                render: function (data, type, row) {
                    return `<a href=${links['update']}?voucher_theme_id=${row['voucher_theme_id']}>${row['name']}</a>`;
                }
            },
            {data: 'voucher_theme_id'},
        ],
        columnDefs: [
            {
                className: 'select-checkbox',
                targets: 0
            },
            {
                sortable: false,
                searchable: false,
                targets: 2,
                render: function (data, type, row) {
                    return `<ul class="icons-list pull-right">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href='${links['update']}?voucher_theme_id=${row['voucher_theme_id']}'><i class='icon-pencil7'></i> ${locales['button_edit']}</a></li>
                                    <li><a onclick="removeItem(this);" data-rowid="${row['voucher_theme_id']}"><i class='icon-trash'></i> ${locales['button_delete']}</a></li>
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
    });

    $(".styled, .multiselect-container input").uniform({
        radioClass: 'choice'
    });

    $(".bulk-delete").attr({
        "data-popup": "tooltip",
        "title": locales['button_delete']
    });
    $(".btn-export").attr({
        "data-popup": "tooltip",
        "title": locales['button_export']
    });
    $(".btn-colvis").attr({
        "data-popup": "tooltip",
        "title": locales['button_colvis']
    });
    $(".merge-orders").attr({
        "data-popup": "tooltip",
        "title": locales['button_merge']
    });

    $('[data-popup="tooltip"]').tooltip();

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

    $('select').select2({
        minimumResultsForSearch: Infinity,
        width: 'auto'
    });
});

function removeItem(row) {
    var that = $(row);
    var rowId = that.data('rowid');

    confirmMessage(function () {
        ajax(links['dtDelete'], {selected: [rowId]}, function (res) {
            var response = JSON.parse(res);
            if (response.success == '1') {
                table.row(that.parents('tr')).remove().draw();
            }
        });
    });
}