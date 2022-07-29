var table;

$(document).ready(function () {

    table = $('#datatableGrid').DataTable({
        data: sellerbased_options,
        stateSave: true,
        autoWidth: false,
        dom: '<"datatable-header"fB><"datatable-scroll"t><"datatable-footer"lip>',
        language: locales['dt_language'],
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
        select: {
            style: 'multi',
            selector: 'td:first-child'
        },
        initComplete: function () {
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
        },
        buttons: [
            {
                text: "<i class='icon-trash'></i>",
                className: 'btn btn-default bulk-delete disabled',
                action: function (e, dt, node, config) {
                    var selectedRows = dt.rows('.selected').data();
                    var selectedIds = [];
                    selectedRows.each(function (item) {
                        selectedIds.push(item.sellerbased_options_id);
                    });
                    if (selectedIds.length > 0) {
                        confirmMessage(function () {
                            $.ajax({
                                url: links['dtDelete'],
                                type: 'post',
                                data: {selected: selectedIds},
                                success: function (resp) {
                                    var response = JSON.parse(resp);

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
                columns: [1,2,3]
            }
        ],
        order: [[1, "asc"]],
        columns: [
            {
                title: `<input type="checkbox" class="styled" id="toggleSelectAll">`,
                orderable: false,
                data: "sellerbased_options_id",
                width: "50px"
            },
            {
                data: 'title',
                render: function (data, type, row) {
                    return `<a href='`+ links['update'] +`?sellerbased_options_id=${row['sellerbased_options_id']}'>${data}</a>`;
                }
            },
            {data: 'sellerbased_options_status'},
        ],
        columnDefs: [
            {
                orderable: false,
                className: 'select-checkbox',
                targets: 0
            },
            {
                targets: 2,
                orderable: false,
                render: function (data, type, row) {
                    var status = (data == "1" ? 'checked="checked"' : '');
                    return statusSwitch(row.sellerbased_options_id, status);
                }
            },
            {
                sortable: false,
                searchable: false,
                targets: 3,
                render: function (data, type, row) {
                    return `<ul class="icons-list pull-right">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href='`+ links['update'] +`?sellerbased_options_id=${row['sellerbased_options_id']}'><i class='icon-pencil7'></i> Edit</a></li>
                                 <!-- <li><a onclick="removeItem(this)" data-rowid="${row['sellerbased_options_id']}"><i class='icon-trash'></i> Delete</a></li> -->
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

function changeStatus(id, status) {
    var newStatus = (status ? 1 : 0);
    ajax(links['dtUpdateStatus'], {id: id, status: newStatus});
}

function statusSwitch(id, status) {
    return (status ? locales['switch_text_enabled'] : locales['switch_text_disabled']);
    return `<div class="checkbox checkbox-switchery  no-margin">
            <label>
                <input type="checkbox" data-on-text="sameh" data-off-text="Amr" onchange="changeStatus(` + id + `, this.checked);" class="switchery" ` + status + `>
                <span class="switchery-status">` + (status ? locales['switch_text_enabled'] : locales['switch_text_disabled']) + `</span>
            </label>
        </div>`;
}

function removeItem(row) {
    var that = $(row);
    var rowId = that.data('rowid');

    confirmMessage(function () {
        $.ajax({
            url: links['dtDelete'],
            data: {selected: [rowId]},
            method: 'POST',
            success: function (res) {
                var response = JSON.parse(res);

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