
var table;

$(document).ready(function () {
    console.log('ssss');
    $.fn.dataTable.render.ellipsis = function () {

        return function ( data, type, row ) {
            return type === 'display' && data.length > 50 ?
                data.substr( 0, 50 ) +'…' :
                data;
        }
    };

    table = $('#datatables').DataTable({
        processing: true,
        serverSide: true,
        stateSave: true,
        autoWidth: false,
        dom: '<"datatable-header"Bf><"datatable-scroll-wrap"t><"datatable-footer"lip>',
        language: locales['dt_language'],
        lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        ajax: {
            url: links['dtHandler'],
            type: 'post',
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
                        text: locales['buttons']['export2pdf']
                    }
                ]
            },
            {
                extend: 'colvis',
                text: '<i class="icon-grid3"></i>',
                className: 'btn btn-default btn-colvis',
                columns: [0, 1, 2, 3, 4]
            }
        ],
        "order": [[1, "asc"]],
        columns: [
            {
                xxtitle: `<input type="checkbox" class="styled" onchange='toggleSelectAll(this);'>`,
                orderable: false,
                data: "product_id",
                width: "50px"
            },
            {
                data: "image",
                render: (d, t, r) => {
                    return `<img src="${d}" class="img-thumbnail" alt="${r.name}" />`;
                },
            },
            {
                data: "name",
                render: (d, t, r) => {
                    return `<a href="${r.product_url}" target="_blank">${d}</a>`
                }
            },
            {data: "ebay_product_id",},
            {
                data: "status",
                render: (d, t, r) => {
                    return `<div class="${d == 1 ? 'text-success' : 'text-danger' }">
                        ${d == 1 ? locales['text_enabled'] : locales['text_disabled']}
                    </div>`;
                }
            },
            {data: "date_added",},

            {
                data: "id",
                render: (d, t, r) => {
                    return `<a href="${r.update_url}" class="btn btn-primary" target="_blank"
                        data-toggle="tooltip">
                        ${locales['button_update']}
                    </a>`;
                }
            },
            {
                data: "id",
                render: (d, t, r) => {
                    return `<a href="${r.edit}" class="btn btn-primary" target="_blank">
                        <i class="fa fa-edit"></i>
                    </a>`;
                }

            }
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
            },
            {
                targets: 3,
                orderable: false,
            },
            {
                targets: 5,
                orderable: true,
            },
            {
                targets: 6,
                orderable: false,
                selectable: false,
            },
            {
                targets: 7,
                orderable: false,
            }
        ]
    });

    $(".bulk-delete").attr({
        "data-popup": "tooltip",
        "title": locales['bulk_delete']
    });
    $(".btn-export").attr({
        "data-popup": "tooltip",
        "title": locales['export']
    });
    $(".btn-colvis").attr({
        "data-popup": "tooltip",
        "title": locales['colvis']
    });

    $('#delete-form').on('click', function() {
        confirmation = confirm(locales['action_confirm']);
        if(confirmation) {
            $('#products-form').attr('action', 'ebay/ebay_product/delete');
            $('#products-form').submit();
        }
    })

    $('#submit-form').on('click', function() {
        confirmation = confirm(locales['action_confirm']);
        if(confirmation) {
            $('#products-form').submit();
        }
    });

    $('[data-popup="tooltip"]').tooltip();
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
        // $(".switch").bootstrapSwitch();
        $('[data-popup="tooltip"]').tooltip();
    });

    $(".styled, .multiselect-container input").uniform({
        radioClass: 'choice'
    });

    table.on('select', function (e, objDT, type, indexes) {
        if (table.rows('.selected').any()) $(".bulk-delete").removeClass('disabled');
        else $(".bulk-delete").addClass('disabled');

        let _id = objDT.data().product_id;
        $('#products-form').append(
            `<input type="hidden" name="selected[]" value="${_id}" id="ebay_seletec_${_id}" />`
        );
    }).on('deselect', function (e, objDT, type, indexes) {
        if (table.rows('.selected').any()) $(".bulk-delete").removeClass('disabled');
        else $(".bulk-delete").addClass('disabled');

        let _id = objDT.data().id;
        $('#products-form').find(`#ebay_seletec_${_id}`).remove();
    }).on('search.dt', function (e, objDT) {
        if (table.rows('.selected').any()) $(".bulk-delete").removeClass('disabled');
        else $(".bulk-delete").addClass('disabled');
    });

    // $('#toggleSelectAll').click(function () {
    //     var checkbox = this;
    //     if (checkbox.checked == true) {
    //         table.rows().select();
    //     } else {
    //         table.rows().deselect();
    //     }
    // });

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
    return `<a data-toggle="modal" data-target="#modal_update" data-remotelink="` + links['update'] + `?category_id=` + id + `" title="` + locales['button_edit'] + `">
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
                    <input type="checkbox" onchange="changeStatus(` + id + `, this.checked);" class="switchery" ` + status + `>
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

function toggleSelectAll(checkbox) {
    if (checkbox.checked == true) {
        table.rows().select();
    } else {
        table.rows().deselect();
    }
}