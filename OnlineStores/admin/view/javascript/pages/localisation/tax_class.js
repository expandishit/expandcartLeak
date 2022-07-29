var table;

$(document).ready(function () {

    table = $('#datatableGrid').DataTable({
        stateSave: true,
        autoWidth: false,
        dom: '<"datatable-header"fB><"datatable-scroll-wrap"t><"datatable-footer"lip>',
        language: locales['dt_language'],
        lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        data: data,
        select: {
            style: 'multi',
            selector: 'td:first-child'
        },
        initComplete: function () {
            $(".switch").bootstrapSwitch();
        },
        drawCallback: function () {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
        },
        preDrawCallback: function() {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
        },
        buttons: [
            {
                text: "<i class='icon-trash'></i>",
                className: 'btn btn-default bulk-delete disabled',
                action: function (e, dt, node, config) {
                    var selectedRows = dt.rows('.selected').data();
                    var selectedIds = [];
                    selectedRows.each(function (item) {
                        selectedIds.push(item.tax_class_id);
                    });
                    if (selectedIds.length > 0) {
                        confirmMessage(function () {
                            $.ajax({
                                url: links['dtDelete'],
                                type: 'post',
                                data: {selected: selectedIds},
                                success: function (res) {
                                    var response = JSON.parse(res);

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
                        });
                    }
                }
            },
            {
                extend: 'collection',
                text: '<i class="icon-drawer-out"></i>',
                className: 'btn btn-default',
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
                        exportOptions: {
                            columns: ':visible'
                        },
                        text: locales['buttons']['export2pdfexcel']
                    }
                ]
            },
            {
                extend: 'colvis',
                text: '<i class="icon-grid3"></i>',
                className: 'btn btn-default btn-icon',
                columns: [1]
            }
        ],
        "order": [[1, "asc"]],
        columns: [
            {
                title: `<input type="checkbox" class="styled" id="toggleSelectAll">`,
                orderable: false,
                data: "tax_class_id",
                width: "50px"
            },
            {
                data: 'title',
                render: function (data, type, row) {
                    return `<a href="${links['update']}?tax_class_id=${row['tax_class_id']}">${row['title']}</a>`;
                }
            },
            {data: 'tax_class_id'},
        ],
        columnDefs: [
            {
                orderable: false,
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
                                    <i class="icon-menu9"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href='${links['update']}?tax_class_id=${row['tax_class_id']}'><i class='icon-pencil7'></i> ${locales['button_edit']}</a></li>
                                    <li><a onclick="removeItem(this);" data-rowid="${row['tax_class_id']}"><i class='icon-trash'></i> ${locales['button_delete']}</a></li>
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
        $.ajax({
            url: links['dtDelete'],
            data: {selected: [rowId]},
            dataType: 'JSON',
            method: 'POST',
            success: function(response) {
                table.row(that.parents('tr')).remove().draw();

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
    });
}