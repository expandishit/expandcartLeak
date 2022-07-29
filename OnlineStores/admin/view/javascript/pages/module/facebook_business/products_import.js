var table;
var productCount = 0;
var start = 0;
var next = '';
var previous = '';

$(document).ready(function() {
    // dataTable initialize
    table = $('#productsTable').DataTable({
        ordering	: false,
        serverSide	: true,
        searching	: false,
        pagingType	: "simple",
        dom			: 'lpftrip',
        language	: {
						...locales['dt_language'],
						search: ''
					 },
        columnDefs	: [
			{
                orderable: false,
                className: 'select-checkbox text-center',
                targets: 0
            },
            {
                targets: 2,
                className: "product-name-column"
            },
            {
                orderable: false,
                className: ' text-center',
                targets: 5
            }
        ],
        select: {
            style: 'multi',
            selector: 'td:first-child'
        },
        buttons: [],
        ajax: {
            url: links['dtHandler'],
            type: 'post',
            data: function(d) {

                if (d.start > start) {
                    d.next_cursor = next;
                } else if (d.start < start) {
                    d.previous_cursor = previous;
                }
            },
            beforeSend: function() {
                $('#productsTable').before('<div class="text-center loading" style="position: absolute;width: 100%;height: 100%;"><img src="view/image/blue-loading-gif-transparent-9.gif"  style="width: 100px" /></div>');
            },
            complete: function() {
                $('.loading').remove();
            },

        },
        columns: [
			{ //0
                orderable: false,
                data: 'id',
                render: function(data, type, row) {
                    return `<input type="checkbox" class="styled dtSelectAll" name="product_id" id='${row['id']}' onchange='toggleSelectAll(this);'>`;
                }
            },
            { //1
                data: "image_url",
                render: function(data, type, row) {
                    return `<img src="${row['image_url']}" height="50px">`;
                },
            },
            { //2
                data: "name",
                render: function(data, type, row) {
                    return "<span>" + row['name'] + "</span>";
                }
            },
            { //3
                data: "price",
                name: 'price'
            },
            { //4
                data: "currency",
                name: 'currency'
            },
            { //5
                data: "brand",
                name: 'brand'
            },
            { //6
                data: "is_imported",
                render: function(data, type, row) {
                    if (row['is_imported']) {
                        return '<i class="imported_mark fas fa-check-circle"></i>';
                    } else {
                        return ".";
                    }
                },
            },
        ],
        columnDefs: [
			{
                orderable: false,
                className: 'select-checkbox text-center',
                targets: 0
            },
			{
				targets: [0, 1, 2, 3, 4, 5, 6],
				orderable: false,
			}, 
		],

    });

    table.on('xhr', function(e, settings, json, xhr) {
        $('.totalHeading').text(json.heading);
        next = json.next_cursor;
        previous = json.previous_cursor;
        start = json.start;
    });

    table.on('select', function(e, objDT, type, indexes) {
        if (table.rows('.selected').any()) $(".btn-has-disabled").removeClass('disabled');
        else $(".btn-has-disabled").addClass('disabled');
        var count = table.rows('.selected').count()
        $(".white-bg").text(count);
    }).on('deselect', function(e, objDT, type, indexes) {
        if (table.rows('.selected').any()) $(".btn-has-disabled").removeClass('disabled');
        else $(".btn-has-disabled").addClass('disabled');
        var count = table.rows('.selected').count()
        $(".white-bg").text(count);
    }).on('search.dt', function(e, objDT) {
        if (table.rows('.selected').any()) $(".btn-has-disabled").removeClass('disabled');
        else $(".btn-has-disabled").addClass('disabled');
        var count = table.rows('.selected').count()
        $(".white-bg").text(count);
    });

    $('.product-name-column').css({
        width: '270px'
    });

    $('.product-name-column span').removeAttr('style').css({
        whiteSpace: "break-spaces",
        width: "270px",
        display: "block"
    });

    $('#productsTable_wrapper select').select2({
        minimumResultsForSearch: Infinity,
        width: 'auto'
    });

});

function toggleSelectAll(checkbox){  

    if (checkbox.checked == true) {
        table.rows().select();
    } else {
        table.rows().deselect();
    }
}