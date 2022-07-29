$(document).ready(function(){
    var tableCategories = $('#table-categories')
        .on( 'page.dt',   function () { pageChanged( ); } )
        .dataTable({
        //"ajax": {
        //    "url": "data/objects_root_array.txt",
        //    "dataSrc": ""
        //},
        "data": data,
        "autoWidth": false,
        "order": [[1, "asc"]],
        //"scrollX": true,
        //"width": "300px",
        //"columnDefs": [
        //    { "orderable": false, "targets": 0 }
        //]
        "language": {
            "lengthMenu": text_lengthMenu,
            "zeroRecords": text_zeroRecords,
            "info": text_info,
            "infoEmpty": text_infoEmpty,
            "infoFiltered": text_infoFiltered,
            "search": text_search
        },
        "columns": [
            {
                "width": "3%", "searchable": false, "orderable": false,
                "render": function ( data, type, row ) {
                    if(row['selected'])
                        return '<div class="checkbox-nice"><input type="checkbox" id="chk' + row['category_id'] + '"  name="selected[]" value="' + row['category_id'] + '" checked="checked" /><label for="chk' + row['category_id'] + '"></label>';
                    else
                        return '<div class="checkbox-nice"><input type="checkbox" id="chk' + row['category_id'] + '"  name="selected[]" value="' + row['category_id'] + '" /><label for="chk' + row['category_id'] + '"></label>';
                }
            },
            { "data": "name", "width": "60%" },
            { "data": "sort_order", "width": "10%" },
            {
                "width": "10%", "searchable": false, "orderable": false,
                "render": function ( data, type, row ) {
                    // var ret = '';
                    // row.action.forEach(function(action) {
                    //     ret += '<button type="button" class="btn btn-default" onclick="location.href=\'' + action.href + '\'">' + action.text + '</button>'
                    // });
                    // return ret;
                    return '<button type="button" class="btn btn-default" onclick="location.href=\'' + row['action'][0]['href'] + '\'">' + row['action'][0]['text'] + '</button>' +
                        '<a target="_blank" href="' + row['action'][1]['href'] + '" class="button btn btn-default">' + row['action'][1]['text'] + '</a>';
                    //return '<div><a class="label label-default" href="' + row['action'][0]['href'] + '" data-placement="top" data-toggle="tooltip" data-original-title="' + row['action'][0]['text'] + '"><i class="fa fa-pencil"></i></a></div>';
                }
            }
            //{ "data": "start_date" },
            //{ "data": "salary" }
        ]
    });

    var pageChanged = function() {
        $('#chkAllItems').prop('checked', false);
    }
});