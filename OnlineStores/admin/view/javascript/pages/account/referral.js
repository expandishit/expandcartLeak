var table;

$(document).ready(function () {
    var ReferralHistoryAdmincolumns = [
        {data: 'store_name'},
        {data: 'product_name'},
        {data: 'created_at'},
        {data: 'reward_amount'},
    ];

    var redeemCodesAdmincolumns = [
        {data: 'amount'},
        {data: 'code'},
        {data: 'created_at'},
        {data: 'status'},
    ];

    var redeemCodesStatusColumnInex = 3;

    // balance table
    table = $('#historyDatatableGrid').DataTable({
        autoWidth: false,
        dom: '<"datatable-header"fB><"datatable-scroll-wrap"t><"datatable-footer"lip>',
        language: {
            ...locales['dt_language'],
            search: ""
        },
        lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        ajax: {
            url: links['historyDtHandler'],
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
        createdRow: function (row, data, dataIndex) {
            $(row).attr('data-rowid', data['id']);
        },
        buttons: [
            {
                text: locales['redeem'],
                className: 'theme-btn green-white-empty except-style m-bold',
                action: function(){
                    if(current_balance < min_redeem_amount){
                        notify(locales['error_no_enough_balance'], 'error', '');
                    } else {
                        $('#redeem-modal').modal('show');
                    }
                }
            }
        ],
        order: [[1, "asc"]],
        columns: ReferralHistoryAdmincolumns,
        columnDefs: [
            {
                width: '30%',
                targets: [1]
            }
        ],
    });

    // show redeem modal 
    $('#redeem_btn').click(function(e) {
        e.preventDefault()
        if(current_balance < min_redeem_amount){
            notify(locales['error_no_enough_balance'], 'error', '');
        } else {
            $('#redeem-modal').modal('show');
        }
    })

    // redeem table
    table = $('#requestsDatatableGrid').DataTable({
        autoWidth: false,
        dom: '<"datatable-header"fB><"datatable-scroll-wrap"t><"datatable-footer"lip>',
        language: {
            ...locales['dt_language'],
            search: ""
        },
        lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        ajax: {
            url: links['requestsDtHandler'],
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
        createdRow: function (row, data, dataIndex) {
            $(row).attr('data-rowid', data['id']);
        },
        buttons: [],
        order: [[1, "asc"]],
        columns: redeemCodesAdmincolumns,
        columnDefs: [
            {
                width: '30%',
                targets: [1]
            },
            {
                targets: redeemCodesStatusColumnInex,
                orderable: true,
                render: function (data, type, row) {
                    if(data == '0'){
                        return '<span class="badge badge-success">' + locales['available'] + '</span>';
                    }
                    else if(data == '1'){
                        return '<span class="badge badge-default">' + locales['used'] + '</span>';
                    }
                }
            }
        ],
    });
});