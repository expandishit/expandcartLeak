
var table;

$(document).ready(function () {

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
                data: "order_id",
                width: "50px"
            },
            {data: "customer"},
            {data: "total"},
            {data: "date_added"},
            {data: "date_modified"},
            {data: "aliexpress_order_status"},
            {
                data: "place_order_url",
                render: (d, t, r) => {
                    return `<a href="${d}" class="btn btn-primary" target="_blank">
                        <i class="fa fa-shopping-cart"></i>
                    </a>`;
                }
            }
        ],
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
            `<input type="hidden" name="selected[]" value="${_id}" id="ali_express_seletec_${_id}" />`
        );
    }).on('deselect', function (e, objDT, type, indexes) {
        if (table.rows('.selected').any()) $(".bulk-delete").removeClass('disabled');
        else $(".bulk-delete").addClass('disabled');

        let _id = objDT.data().id;
        $('#products-form').find(`#ali_express_seletec_${_id}`).remove();
    }).on('search.dt', function (e, objDT) {
        if (table.rows('.selected').any()) $(".bulk-delete").removeClass('disabled');
        else $(".bulk-delete").addClass('disabled');
    });

    $('select').select2({
        minimumResultsForSearch: Infinity,
        width: 'auto'
    });
});