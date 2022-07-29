/* ------------------------------------------------------------------------------
*
*  # Buttons extension for Datatables. HTML5 examples
*
*  Specific JS code additions for datatable_extension_buttons_html5.html page
*
*  Version: 1.1
*  Latest update: Mar 6, 2016
*
* ---------------------------------------------------------------------------- */

$(function() {


    // Table setup
    // ------------------------------

    // Setting datatable defaults
    $.extend( $.fn.dataTable.defaults, {
        autoWidth: false,
        dom: '<"datatable-header"fBl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
        language: {
            search: '<span>' + locales['filter'] + '</span> _INPUT_',
            searchPlaceholder: locales['filter_placeholder'],
            lengthMenu: '<span>' + locales['show'] + '</span> _MENU_',
            paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' }
        }
    });


    // Column selectors
    var t = $('.datatable-button-html5-columns').DataTable({
        colReorder: true,
        select :true,
        select: {
            style: 'multi'
        },
        columnDefs: [
            { 'visible': false, 'targets': [4,5,6,7] },
            {
                orderable: false,
                className: 'select-checkbox',
                targets: 0
            },
            {
                orderable: false,
                width: '100px',
                targets: 6
            }
        ],
        select: {
            style: 'os',
            selector: 'td:first-child'
        },
        order: [[1, 'asc']],
        buttons: {            
            buttons: [
                {
                    extend: 'copyHtml5',
                    className: 'btn btn-default',
                    exportOptions: {
                        columns: [ 0, ':visible' ]
                    }
                },
                {
                    extend: 'excelHtml5',
                    className: 'btn btn-default',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    className: 'btn btn-default',
                    exportOptions: {
                        columns: [0, 1, 2, 5]
                    }
                },
                {
                    extend: 'colvis',
                    text: '<i class="icon-three-bars"></i> <span class="caret"></span>',
                    className: 'btn bg-blue btn-icon'
                }
            ]
        }
    });




    // External table additions
    // ------------------------------

    // Enable Select2 select for the length option
    $('.dataTables_length select').select2({
        minimumResultsForSearch: Infinity,
        width: 'auto'
    });
    
    $('.heading-elements').fadeOut();
    t.on( 'deselect', function ( e, dt, type, indexes ) { $('.heading-elements').fadeOut()});
    t.on( 'select', function ( e, dt, type, indexes ) { $('.heading-elements').fadeIn()});
    
});
