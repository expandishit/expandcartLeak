var table, sellers;

$(document).ready(function () {

    table = $('#datatableGrid').DataTable({
        processing: true,
        serverSide: true,
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
            },
            dataSrc: function (response) {

                sellers = response.sellers;

                return response.data;
            }
        },
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
                        selectedIds.push(item.product_id);
                    });
                    if (selectedIds.length > 0) {
                        confirmMessage(function () {
                            $.ajax({
                                url: links['dtDelete'],
                                type: 'post',
                                data: {selected: selectedIds},
                                success: function (resp) {
                                    var response = JSON.parse(res);
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
                columns: [1, 2, 3, 4,5,6]
            }
        ],
        order: [[1, "asc"]],
        columns: [
            {
                title: `<input type="checkbox" class="styled" id="toggleSelectAll">`,
                orderable: false,
                data: "product_id",
                width: "50px"
            },
            {
                data: 'image',
                render: function (data, type, row) {
                    return `<img src="${data}" />`;
                }
            },
            {
                data: 'name',
                render: function (data, type, row) {
                    return `<a href='` + links['update'] + `?product_id=${row['product_id']}'>${data}</a>`;
                }
            },
            {
                data: 'seller',
                render: function (d,t,r) {

                    var $div = $("<div></div>", {
                        'class': 'assign-seller'
                    });

                    var $select = $("<select data-seller='" + d + "'></select>", {
                        "id": r[0]+"start",
                        "value": d
                    });

                    $select.append($('<option></option>', {
                        'text': locales['no_seller'],
                        'value': '0',
                    }));

                    $.each(sellers, function(k,v){
                        var $option = $("<option></option>", {
                            "text": v['ms.nickname'].replace("&amp;", "&"),
                            "value": v['seller_id'],
                            "selected": (v['seller_id'] == d ? true : false)
                        });
                        if(d === v){
                            $option.attr("selected", "selected")
                        }
                        $select.append($option);
                    });

                    $div.append($select);

                    $div.append("<span class='ms-assign-seller' data-pid='"+r['product_id']+"' title='Save' />");

                    return $div.prop("outerHTML");
                }
            },
            {data: 'seller_name',name : 'seller_name'},
            {data: 'category'},
            {
                data: 'status',
            },
            {data: 'date_added', name: 'date_added'},
            {data: 'date_modified', name: 'date_modified'},
        ],
        columnDefs: [
            {
                orderable: false,
                className: 'select-checkbox',
                targets: 0
            },
            {
                visible: false,
                searchable:true,
                targets: 4
            },
            {
                sortable: false,
                searchable: false,
                targets: 9,
                render: function (data, type, row) {
                    return `<ul class="icons-list pull-right">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href='` + links['update'] + `?product_id=${row['product_id']}'><i class='icon-pencil7'></i> Edit</a></li>
                                    <li><a onclick="removeItem(${row['product_id']})"><i class='icon-trash'></i> Delete</a></li>
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
            elems.forEach(function (html) {
                var switchery = new Switchery(html);
            });
        }
        else {
            var elems = document.querySelectorAll('.switchery');
            for (var i = 0; i < elems.length; i++) {
                var switchery = new Switchery(elems[i]);
            }
        }
        $(".switchery").on('change', function (e) {
            var status = this.checked ? locales['switch_text_enabled'] : locales['switch_text_disabled'];
            $(this).parent().children(".switchery-status").text(status);
        });

        $(".switch").bootstrapSwitch();

        $('select').select2({
            minimumResultsForSearch: Infinity,
            width: '90%'
        });
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

    $(document).on('click', '.ms-assign-seller', function () {
        var button = $(this);
        var parent = button.parent();
        var product_id = button.data('pid');
        var seller_id = $('select', parent).find('option:selected').val();

        if (seller_id == null || typeof seller_id == 'undefined') {
            return;
        }

        // $(this).hide().before('<img src="view/image/loading.gif" alt="" />');
        $.ajax({
            type: "POST",
            dataType: "json",
            url: 'multiseller/product/jxProductSeller?product_id='+ product_id +'&seller_id='+ seller_id,
            success: function(response) {
                if (response.status == 'success') {
                    button.parents('td').next('td').html(response.product_status);
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
                /*
                 * Commented to prevent reseting pagination.
                 *
                 * table.ajax.reload();
                 */

            }
        });
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
        </div>`;
}

function removeItem(row) {
    var that = $(row);
    var rowId = that.data('rowid');

    confirmMessage(function () {
        ajax(links['dtDelete'], {selected: [row]}, function (res) {
            var response = JSON.parse(res);
            if (response.success == '1') {
                table.row(that.parents('tr')).remove().draw();
            }
        });
    });
}
