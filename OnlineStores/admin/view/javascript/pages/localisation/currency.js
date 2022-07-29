var table;

$(document).ready(function () {

    $(document).on('change', '.switchery', function(e) {
        var status = this.checked ? locales['switch_text_enabled'] : locales['switch_text_disabled'];
        $(this).parent().children(".switchery-status").text(status);
    });

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
        drawCallback: function () {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
        },
        preDrawCallback: function() {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
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
        },
        buttons: [
            {
                text: "<i class='icon-trash'></i> ",
                className: 'btn btn-default bulk-delete disabled',
                action: function (e, dt, node, config) {
                    var selectedRows = dt.rows('.selected').data();
                    var selectedIds = [];
                    selectedRows.each(function (item) {
                        selectedIds.push(item.currency_id);
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
                    },
                ]
            },
            {
                extend: 'colvis',
                text: '<i class="icon-grid3"></i>',
                className: 'btn btn-default btn-icon',
                columns: [1, 2, 3, 4, 5]
            }
        ],
        "order": [[1, "asc"]],
        columns: [
            {
                title: `<input type="checkbox" class="styled" id="toggleSelectAll">`,
                orderable: false,
                data: "currency_id",
                width: "50px"
            },
            {
                data: 'title',
                render: function (data, type, row) {
                    return `<a href="${links['update']}?currency_id=${row['currency_id']}" title="${locales['button_edit'] }">${row['title']}</a>`;
                }
            },
            {data: 'code'},
            {data: 'value'},
            {data: 'date_modified'},
            {data: 'status'},
            {data: 'currency_id'},
        ],
        columnDefs: [
            {
                orderable: false,
                className: 'select-checkbox',
                targets: 0
            },
            {
                targets: 5,
                render: function (data, type, row) {
                    var status = (data == "1" ? 'checked="checked"' : '');
                    return statusSwitch(row.currency_id, status);
                }
            },
            {
                sortable: false,
                searchable: false,
                targets: 6,
                render: function (data, type, row) {
                    return `<ul class="icons-list pull-right">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="icon-menu9"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="${links['update']}?currency_id=${row['currency_id']}" title="${locales['button_edit'] }"><i class="icon-pencil7"></i> ${locales['button_edit']}</a></li>
                                    <li><a title="${locales['button_delete']}" onclick="removeItem(this);" data-rowid="${row['currency_id']}"> <i class="icon-trash"></i> ${locales['button_delete']}</a></li>
                                </ul>
                            </li>
                        </ul>
                    `;
                }
            }
        ],
    });

    $('[data-popup="tooltip"]').tooltip();

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

    $('#modal_insert').on('show.bs.modal', function () {
        $(this).find('.modal-body').load('localisation/currency/insert', function () {
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
                            window.location.href = links['list'];
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
                            window.location.href = links['list'];
                        }
                    }
                });
            });
        });
    });

    $('#modal_update, #modal_insert').on('hidden.bs.modal', function () {
        table.draw();
    })

    $('select').select2({
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
        success: function(response) {
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

function statusSwitch(id, status)
{
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