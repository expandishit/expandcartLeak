$(document).ready(function(){
    var tableProducts =
        $('#table-products')
        .dataTable({
            "processing": true,
            "serverSide": true,
            "pageLength": 5,
            "pagingType": "simple",
            "lengthChange": false,
            "ordering": false,
            "initComplete" : function() {
                var firstDiv = $("#table-products_filter").parent().prev();
                $("#table-products_filter").parent().removeClass("col-xs-6").addClass("col-xs-11");
                firstDiv.removeClass("col-xs-6").addClass("col-xs-1").addClass("gridLoading");
                $("#table-products_processing").detach().appendTo('#table-products_wrapper .gridLoading');
            },
            "ajax": products_grid_service,
            "language": {
                "processing": "<span class='server-loading' style='display:block;'><i class='fa fa-refresh fa-spin'></i></span>",
                "lengthMenu": text_lengthMenu,
                "zeroRecords": text_zeroRecords,
                "info": text_info,
                "infoEmpty": text_infoEmpty,
                "infoFiltered": text_infoFiltered,
                "search": text_search
            },
            "columns": [
                { "data": "image",
                    "render": function ( data, type, row ) {
                        return '<img src="' + row['image'] + '" alt="' + row['name'] + '" />';
                    },
                    "sortable": false
                },
                { "data": "name",
                    "sortable": false },
                { "data": "model",
                    "sortable": false },
                { "data": "price",
                    "sortable": false }
            ]
        });

    $('#table-products tbody').on('click', 'tr', function () {
        var product_id = tableProducts.fnGetData(this).product_id;
        var src_obj = $("#" + $(".modal-body").data("srcId"));

        src_obj.find('.href_type').val('product');
        src_obj.find('.href_id').val(product_id);

        $('.md-close').click();
    });


    var tableCategory =
        $('#table-category')
            .dataTable({
                "processing": true,
                "serverSide": true,
                "pageLength": 5,
                "pagingType": "simple",
                "lengthChange": false,
                "ordering": false,
                "initComplete" : function() {
                    var firstDiv = $("#table-category_filter").parent().prev();
                    $("#table-category_filter").parent().removeClass("col-xs-6").addClass("col-xs-11");
                    firstDiv.removeClass("col-xs-6").addClass("col-xs-1").addClass("gridLoading");
                    $("#table-category_processing").detach().appendTo('#table-category_wrapper .gridLoading');
                },
                "ajax": category_grid_service,
                "language": {
                    "processing": "<span class='server-loading' style='display:block;'><i class='fa fa-refresh fa-spin'></i></span>",
                    "lengthMenu": text_lengthMenu,
                    "zeroRecords": text_zeroRecords,
                    "info": text_info,
                    "infoEmpty": text_infoEmpty,
                    "infoFiltered": text_infoFiltered,
                    "search": text_search
                },
                "columns": [
                    { "data": "image",
                        "render": function ( data, type, row ) {
                            return '<img src="' + row['image'] + '" alt="' + row['name'] + '" />';
                        },
                        "sortable": false
                    },
                    { "data": "name",
                        "sortable": false }
                ]
            });

    $('#table-category tbody').on('click', 'tr', function () {
        var category_id = tableCategory.fnGetData(this).category_id;
        var src_obj = $("#" + $(".modal-body").data("srcId"));

        src_obj.find('.href_type').val('category');
        src_obj.find('.href_id').val(category_id);

        $('.md-close').click();
    });
});