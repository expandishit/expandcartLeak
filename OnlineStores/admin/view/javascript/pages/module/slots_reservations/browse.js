var table, reserved;

$(document).ready(function () {

    reserved = $('#reserved-slots-table').DataTable({
        autoWidth: false,
        dom: '<"datatable-header"f><"datatable-scroll"t><"datatable-footer"lip>',
        language: locales['dt_language'],
        lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        ajax: {
            type: 'post',
            error: function (e, m, l) {
                $(".datatables_country-error").html("");
                $("#datatables_country").append('<tbody class="datatables_country-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                $("#datatables_country_processing").css("display", "none");
            }
        },
        columns: [
            {data: 'code',},
            {data: 'name'},
            {data: 'email'},
            {data: 'phone'}
        ],
    });

    $('#reserved-slots-modal').on('show.bs.modal', function (e) {
        let r = $(e.relatedTarget), c = $(e.currentTarget);

        reserved.ajax.url(links['reservedSlots'] + '?slot_id=' + r.data('slot')).load();
        console.log(r.data('slot'));
    });

    table = $('#slots-reservations-table').DataTable({
        autoWidth: false,
        dom: '<"datatable-header"f><"datatable-scroll"t><"datatable-footer"lip>',
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
        /*select: {
            style: 'multi',
            selector: 'td:first-child'
        },*/
        __buttons: [
            {
                text: "<i class='icon-trash'></i>",
                className: 'btn btn-default bulk-delete disabled',
                action: function (e, dt, node, config) {
                    var selectedRows = dt.rows('.selected').data();
                    var selectedIds = [];
                    selectedRows.each(function (item) {
                        selectedIds.push(item.id);
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
                columns: [1,3,4,5]
            }
        ],
        order: [[1, "asc"]],
        columns: [
            {data: 'reservation_date',},
            {data: 'slot'},
            {data: 'count'},
            {
                data: 'id',
                render: (d, t, r) => {
                    return `<div data-toggle="modal" data-target="#reserved-slots-modal"
                        data-slot="${d}"
                        class="btn btn-xs btn-success">${locales['view_reservations']}</div>`
                }
            }
        ],
        __columnDefs: [
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
                targets: 2,
                orderable: false,
                visible: false,
                render: function (data, type, row) {
                    var status = (data == "1" ? 'checked="checked"' : '');
                    return statusSwitch(row.id, status);
                }
            },
            {
                targets: 4,
                orderable: false,
                render: function (data, type, row) {
                    var status = (data == "1" ? 'checked="checked"' : '');
                    return statusSwitch(row.id, status);
                }
            },
            {
                sortable: false,
                searchable: false,
                targets: 6,
                render: function (data, type, row) {
                    return `<ul class="icons-list pull-right">
                            <li class="dropdown">
                                <a class="icon-trash" onclick="removeItem(this)" data-rowid="${row['id']}"></a>
                            </li>
                        </ul>
                    `;
                }
            }
        ],
    });
/*
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

    var historyTable = $('#historyGrid').DataTable({
        dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
        autoWidth: false,
        language: locales['dt_language'],
        lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
    });

    $('select').select2({
        minimumResultsForSearch: Infinity,
        width: 'auto'
    });*/
});
/*
function changeStatus(id, status) {
    var newStatus = (status ? 1 : 0);
}

function statusSwitch(id, status) {
    return `<div class="checkbox checkbox-switchery  no-margin">
            <label>
                <input type="checkbox" onchange="changeStatus(` + id + `, this.checked);" class="switchery" ` + status + `>
                <span class="switchery-status">` + (status ? locales['switch_text_enabled'] : locales['switch_text_disabled']) + `</span>
            </label>
        </div>`;
}

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
}*/