var table;

$(document).ready(function () {

    table = $('#datatableGrid').DataTable({
        autoWidth: false,
        dom: '<"datatable-header"fB><"datatable-scroll"t><"datatable-footer"lip>',
        language: {
            ...locales['dt_language'],
            search: ''
        },
        lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        ajax: {
            url: links['dtHandler'],
            type: 'post',
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
                columns: [1,2]
            },// cols
            {
                extend: 'collection',
                text: `<i class="fas fa-file-download"></i> ${locales['dtb_export_table']}`,
                className: `btn-export ${freePlan ? "disabled plan-lock-btn" : ""}`,
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
                className: 'bulk-delete disabled',
                action: function (e, dt, node, config) {
                    var selectedRows = dt.rows('.selected').data();
                    var selectedIds = [];
                    selectedRows.each(function (item) {
                        selectedIds.push(item.manufacturer_id);
                    });

                    if (selectedIds.length > 0) {
                        confirmMessage(function () {
                            ajax(links['dtDelete'], {selected: selectedIds}, function (res) {
                                var response = JSON.parse(res);
                                if (response.success == '1') {
                                    dt.rows('.selected').remove().draw();
                                }
                            });
                        });
                    }
                }
            },// delete
        ],
        order: [[1, "asc"]],
        columns: [
            {
                title: `<input type="checkbox" class="styled" id="toggleSelectAll">`,
                orderable: false,
                data: "manufacturer_id",
                width: "50px"
            },
            {
                data: 'name',
                render: function (data, type, row) {
                    return `<a href="`+ links['update'] +`?manufacturer_id=${row['manufacturer_id']}">${row['name']}</a>`;
                }
            },
            {data: 'sort_order'},
            {
                width: "30px",
                data: 'manufacturer_id'
            },
            {
                width: "10px",
                data: 'manufacturer_id'
            },
        ],
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
                targets: 3,
                orderable: false,
                selectable: false,
                render: function (data, type, row) {
                    return `<a data-popup="tooltip" title="${locales['text_preview']}" target="_blank" href="${links['preview']}${row['manufacturer_id']}" class='text-default'><i class='fa fa-eye fa-lg valign-middle'></i></a>`;
                }
            },
            {
                sortable: false,
                searchable: false,
                targets: 4,
                render: function (data, type, row) {
                    return `<ul class="icons-list pull-right">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="icon-menu9"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="` + links['update'] + `?manufacturer_id=${row['manufacturer_id']}"><i class='icon-pencil7'></i> ${locales['button_edit']}</a></li>
                                    <li><a onclick="removeItem(this)" data-rowid="${row['manufacturer_id']}"><i class='far fa-trash-alt'></i> ${locales['button_delete']}</a></li>
                                </ul>
                            </li>
                        </ul>
                    `;
                }
            }
        ],
    });

    

    $(".styled, .multiselect-container input").uniform({
        radioClass: 'choice'
    });

    $('[data-popup="tooltip"]').tooltip();
    $('[data-toggle="tooltip"]').tooltip();

    $('select').select2({
        minimumResultsForSearch: Infinity,
        width: 'auto'
    });

    table.on('draw', function () {
        $('[data-popup="tooltip"]').tooltip();
    $('[data-toggle="tooltip"]').tooltip();
    });

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