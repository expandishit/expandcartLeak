var table;

$(document).ready(function () {

    table = $('#datatableGrid').DataTable({
        processing: true,
        serverSide: true,
        stateSave: true,
        autoWidth: false,
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
        dom: '<fBl><"datatable-scroll"t><"datatable-footer"ip>',
        language: locales['dt_language'],
        "pageLength": 12,
        lengthMenu: [[12, 24, 48, 92, 200], [12, 24, 48, 92, 200]],
        select: {
            style: 'multi',
            selector: 'td:first-child'
        },
        buttons: [
            {
                text: "<i class='fa fa-file position-left'></i> " + locales['button_insert'],
                className: 'btn btn-primary',
                action: function (e, dt, node, congig) {
                    if (insertWizard == true) {
                        $('#insertWizard').modal('show');
                    } else {
                        window.location.href = links['insert'];
                    }
                }
            },
            {
                text: "<i class='fa fa-trash position-left'></i> " + locales['button_delete'],
                className: 'btn btn-danger bulk-delete disabled',
                action: function (e, dt, node, config) {
                    var selectedRows = dt.rows('.selected').data();
                    var selectedIds = [];
                    selectedRows.each(function (item) {
                        selectedIds.push(item.product_id);
                    });
                    if (selectedIds.length > 0) {
                        confirmMessage(function () {
                            $.ajax({
                                url: links['dtDelete'],
                                type: 'post',
                                data: {selected: selectedIds},
                                dataType: 'JSON',
                                success: function (response) {

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
                className: 'btn bg-blue btn-icon',
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
            /*{
                extend: 'colvis',
                text: '<i class="icon-grid3"></i> <span class="caret"></span>',
                className: 'btn bg-indigo-400 btn-icon',
                columns: [1, 2, 3, 4, 5, 6, 7]
            }*/
        ],
        "createdRow": function (row, data, dataIndex) {
            $(row).addClass('col-lg-3 col-sm-6');
        },
        "fnDrawCallback": function (oSettings) {
            $(oSettings.nTHead).hide();
        },
        columns: [
            {data: "product_id"},
        ],
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                render: function (data, type, row) {
                    return bindThumbnailView(data, row);
                }
            },
        ]
    });

    table.on('draw', function () {
        $(".switch").bootstrapSwitch();
    });

    table.on('xhr', function (e, settings, json, xhr) {
        $('.totalHeading').text(json.heading);
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

    $('#insertWizard').on('show.bs.modal', function () {
        $(this).find('.modal-body').load('catalog/product/insert', function () {
            $('select').select2({
                minimumResultsForSearch: Infinity
            });

            $.fn.stepy.defaults.backLabel = '<i class="icon-arrow-left13 position-left"></i> ' + locales['buttons']['prev'];
            $.fn.stepy.defaults.nextLabel = locales['buttons']['next'] + ' <i class="icon-arrow-left13 position-right"></i>';

            $(".insertWizard").stepy({
                description: false
            });

            $('.insertWizard').find('.button-next').addClass('btn btn-primary');
            $('.insertWizard').find('.button-back').addClass('btn btn-default');

            $('.summernote').summernote();

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

    $('#insertWizard').on('hidden.bs.modal', function () {
        table.draw();
    })

    $('#datatableGrid_wrapper select').select2({
        minimumResultsForSearch: Infinity,
        width: 'auto'
    });

    // $('#resetList').click(function () {
    //     table.ajax.reset();
    // });
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
    return '<a class="btn btn-primary btn-xs" data-toggle="modal" data-target="#modal_update" ' +
        'data-remotelink="' + links['update'] + '?product_id=' + id + '" title="' + locales['button_edit'] + '"> ' +
        '<i class="icon-pencil7"></i></a>';
}

function btnDeleteItem(id) {
    return '<a title="' + locales['button_delete'] + '" class="btn btn-danger btn-xs" ' +
        'onclick="removeItem(this);" data-rowid="' + id + '">' +
        '<i class="icon-trash"></i>' +
        '</a>';
}

function statusSwitch(id, status) {
    return '<div class="checkbox checkbox-switch">' +
        '<label>' +
        '<input type="checkbox" class="switch" data-on-color="success" ' +
        'onchange="changeStatus(' + id + ', this.checked);" data-on-text="' + locales['switch_text_enabled'] + '" ' +
        'data-off-text="' + locales['switch_text_disabled'] + '" ' + status + ' >' +
        '</label>' +
        '</div>';
}

function bindThumbnailView(id, row) {

    var rates = function (rateVal) {

        if (typeof rateVal === 'undefined') {
            return '&ensp;';
        }

        var rateTemp = '';

        for (i = 1; i <= 5; i++) {

            if (i >= rateVal) {
                rateTemp += '<i class="icon-star-full2 text-size-base text-warning-300"></i>';
            } else {
                // rateTemp += '<i class="icon-star-half text-size-base text-warning-300"></i>';
                rateTemp += '<i class="icon-star-empty3 text-size-base text-warning-300"></i>';
            }
        }

        return rateTemp;
    };

    var reviews = function (reviews) {

        if (typeof reviews.count === 'undefined') {
            return '<div class="text-muted">&ensp;</div>';
        }

        var temp = '';

        if (reviews.count > 10) {
            return '<div class="text-muted">' + row.reviews.count + ' ' + locales['reviews_label'] + '</div>';
        } else {
            return '<div class="text-muted">' + row.reviews.count + ' ' + locales['review_label'] + '</div>';
        }
    };

    return '<div class="panel col-md-12">' +
        '<div class="panel-body">' +
        '<div class="thumb thumb-fixed">' +
        '<a href="' + row.image + '" data-popup="lightbox">' +
        '<img src="' + row.image + '" alt="">' +
        '<span class="zoom-image"><i class="icon-plus2"></i></span>' +
        '</a>' +
        '</div>' +
        '</div>' +
        '<div class="panel-body panel-body-accent text-center">' +
        '<h6 class="text-semibold no-margin">' +
        '<a href="' + links['update'] + '?product_id=' + row.product_id + '" class="text-default">' + row.name + '</a>' +
        '</h6>' +
        '<ul class="list-inline list-inline-separate mb-10">' +
        '<li><a href="' + links['update'] + '?category_id=' + row.category_id + '" ' +
        'class="text-muted">' + (row.category_name != '' ? row.category_name : null) + '</a></li>' +
        '</ul>' +
        '<h3 class="no-margin text-semibold">' + row.price + '</h3>' +
        '<div class="text-nowrap">' +
        rates(row.reviews.rate) +
        '</div>' +
        reviews(row.reviews) +
        '</div>';
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