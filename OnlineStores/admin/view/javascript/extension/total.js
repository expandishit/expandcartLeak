
function updateStatus(switchControl) {

    var status = 0;

    if (switchControl.checked) {
        status = 1;
    }

    var extension = $(switchControl).data('extension');

    $.ajax({
        url: 'extension/total/updateStatus',
        method: 'POST',
        dataType: 'JSON',
        data: {'status': status, 'name': extension},
        success: function (response)
        {
            if (response.success == '1')
            {
                notify('Sucess', 'success', response.success_msg);
            }
        }
    });
}

$(function () {

    $.extend($.fn.dataTable.defaults, {
        autoWidth: false,
        dom: '<"datatable-header"><"datatable-scroll"t><"datatable-footer">',
        language: locales['dt_language'],
        drawCallback: function () {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
        },
        preDrawCallback: function () {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
        }
    });

    // Reorder events
    var table = $('.extension_total').DataTable({
        rowReorder: {
            selector: 'td.sortable'
        },
        columnDefs: [
            {
                sortable: false,
                searchable: false,
                targets: [0, 1, 2, 3]
            },
            {
                targets: [0, 2, 3],
                'width': '30px'
            }
        ],
        'pageLength': 25,
        'lengthMenu': [10, 25, 50, 100],
        'sDom': 'rt'
    });

    // Setup event
    table.on('row-reorder', function (e, diff, edit) {

        var data = {};

        for (var i = 0, ien = diff.length; i < ien; i++) {
            var rowData = {};

            var rowId = table.row(diff[i].node).selector.rows.id;

            var extensionId = $('.extension_id', $('#' + rowId)).data('extension');
            var uniqueName = $('.unique_name', $('#' + rowId)).data('unique');

            rowData['id'] = extensionId;
            rowData['name'] = uniqueName;
            rowData['was'] = diff[i].oldData;
            rowData['now'] = diff[i].newData;

            data[extensionId] = rowData;
        }

        var dataLength = Object.keys(data).length;

        if (dataLength > 0) {
            $.ajax({
                url: 'extension/total/updateSortOrder',
                method: 'POST',
                dataType: 'JSON',
                data: {'data': data},
                success: function (response)
                {
                    if (response.success == '1')
                    {
                        notify('Sucess', 'success', response.success_msg);
                    }
                }
            });
        }
    });

});