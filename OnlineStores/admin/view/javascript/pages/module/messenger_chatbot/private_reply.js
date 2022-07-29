var table;

$(document).ready(function () {
    var privateReplyAdmincolumns = [
        {
            title: `<input type="checkbox" class="styled" id="toggleSelectAll">`,
            orderable: false,
            data: "private_reply_id",
            width: "50px"
        },
        {
                data: 'name',
                render: function (data, type, row) {
                    return `<a href='`+ links['update'] +`?private_reply_id=${row['private_reply_id']}'>${row['name']}</a>`;
                }
            },
            {data: 'type'},
            {data: 'created_at'},
            {data: 'status'},
            {data: 'private_reply_id'},
    ];
    var statusColumnInex = 4 ;
    var EditColumnIndex = 5;


    table = $('#datatableGrid').DataTable({
        autoWidth: false,
        dom: '<"datatable-header"fB><"datatable-scroll-wrap"t><"datatable-footer"lip>',
        language: {
            ...locales['dt_language'],
            search: ""
        },
        lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        ajax: {
            url: links['dtHandler'],
            type: 'post',
            complete: function(res) {
//                res.responseJSON.recordsTotal == 0 ? location.reload() : ''; // comment this line because in case no data it's keeps reload
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
        createdRow: function (row, data, dataIndex) {
            $(row).attr('data-rowid', data['private_reply_id']);
        },
        buttons: [
            {
                text: `<i class='far fa-trash-alt'></i> ${locales['dtb_delete']}`,
                className: 'bulk-delete disabled',
                action: function (e, dt, node, config) {
                    var selectedRows = dt.rows('.selected').data();
                    var selectedIds = [];
                    selectedRows.each(function (item) {
                        selectedIds.push(item.private_reply_id);
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
            {
                extend: 'colvis',
                text: `<i class="fas fa-columns"></i> ${locales['dtb_custom_col']}`,
                className: 'dt-list btn-colvis',
                columns: [1,2,3,4,5]
            },// cols
            {
                extend: 'collection',
                text: `<i class="fas fa-file-download"></i> ${locales['dtb_export_table']}`,
                buttons: [
                    {
                        extend: 'copyHtml5',
                        text: locales['buttons']['copy'],
                        exportOptions: {
                            columns: ':visible:not(:last-child)'
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: locales['buttons']['export2csv'],
                        fieldSeparator: ',',
                        extension: '.csv',
                        bom: "true",
                        exportOptions: {
                            columns: ':visible:not(:last-child)'
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: locales['buttons']['export2excel'],
                        exportOptions: {
                            columns: ':visible:not(:last-child)'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: locales['buttons']['export2pdf'],
                        exportOptions: {
                            columns: ':visible:not(:last-child)'
                        }
                    }
                ]
            }// export
            
        ],
        order: [[1, "asc"]],
        columns: privateReplyAdmincolumns,
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
                targets: statusColumnInex,
                orderable: false,
                render: function (data, type, row) {
                    // var status = (data == "1" ? 'checked="checked"' : '');
                    // return statusSwitch(row.private_reply_id, status);
                    return data == "1" ? '<span class="badge badge-success">' + locales['active'] + '</span>' : '<span class="badge badge-default">' + locales['inactive'] + '</span>';
                }
            },
            {
                sortable: false,
                searchable: false,
                targets: EditColumnIndex,
                render: function (data, type, row) {
                    return `<ul class="icons-list pull-right">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="icon-menu9"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href='`+ links['update'] +`?private_reply_id=${row['private_reply_id']}'><i class='icon-pencil7'></i> ` + locales['dtb_edit'] + `</a></li>
                                    <li><a onclick="removeItem(${row['private_reply_id']})"><i class='far fa-trash-alt'></i> ` + locales['dtb_delete'] + `</a></li>
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
    
    $('[data-popup="tooltip"]').tooltip();
    $('[data-toggle="tooltip"]').tooltip();

    table.on('select', function (e, objDT, type, indexes) {
        if (table.rows('.selected').any()) $(".bulk-delete, .btn-copy, .bulk-coupon-update").removeClass('disabled');
        else $(".bulk-delete, .btn-copy, .bulk-coupon-update").addClass('disabled');
    }).on('deselect', function (e, objDT, type, indexes) {
        if (table.rows('.selected').any()) $(".bulk-delete, .btn-copy, .bulk-coupon-update").removeClass('disabled');
        else $(".bulk-delete, .btn-copy,.bulk-coupon-update").addClass('disabled');
    }).on('search.dt', function (e, objDT) {
        if (table.rows('.selected').any()) $(".bulk-delete, .btn-copy, .bulk-coupon-update").removeClass('disabled');
        else $(".bulk-delete, .btn-copy, .bulk-coupon-update").addClass('disabled');
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

    $('#type').select2({
        minimumResultsForSearch: Infinity,
    });

    $('.touchspinney').TouchSpin({
        'postfix': $(this).attr('data-postfix'),
        'forcestepdivisibility': 'none',
        'decimals': 2,
        'max': Infinity,
        'min': -Infinity
    });
});


function changeStatus(id, status) {
    var newStatus = (status ? 1 : 0);
    ajax(links['dtUpdateStatus'], {id: id, status: newStatus}, function(response){
        if(!response.success){
            $('input[data-id="checkbox-' + id + '"]').siblings('.switchery').setPosition(false);
        }
    });
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
    confirmMessage(function () {
        ajax(links['dtDelete'], {selected: [row]}, function (res) {
            var response = JSON.parse(res);
            if (response.success == '1') {
                table.ajax.reload()
            }
        });
    });
}