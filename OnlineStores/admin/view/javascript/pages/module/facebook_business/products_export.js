var table = null;
var url = "/admin/module/facebook_business/getXHRList?view=list";
var view_url = "/admin/catalog/product/update?product_id=";
$(document).ready(function() {
    // dataTable initialize
    table = $('#productsTable').DataTable({
        ordering	: false,
        serverSide	: true,
        paging		: true,
        dom			: "Rfrtlip",
        language	: {
						...locales['dt_language'],
						search: ''
					 },
        columnDefs	: [
			{
                orderable: false,
                className: 'text-center',
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
            url: url,
            type: 'post',
            data: {},
            beforeSend: function() {
                $('#productsTable').before('<div class="text-center loading" style="position: absolute;width: 100%;height: 100%;"><img src="view/image/blue-loading-gif-transparent-9.gif"  style="width: 100px" /></div>');
            },
            complete: function() {
                $('.loading').remove();
				$('[data-toggle="tooltip"]').tooltip();
            },

        },
        columns: [
			{ //0
                orderable: false,
                data: 'product_id',
                render: function(data, type, row) {
                    return `<input type="checkbox" class="select-checkbox styled " name="product_id" id='${row['product_id']}'>`;
                }
            },
			{
				"render": function ( data, type, full, meta ) {
					console.log(meta.settings._iDisplayStart);
					return  (parseInt(meta.settings._iDisplayStart) + parseInt(meta.row) +1 );
				
				}
			},
            { //2
                data: "name",
                render: function(data, type, row) {
                    return '<span><a href='+view_url+ row['product_id']+' target="_blank">' + row['name'] + '</a></span>';
                }
            },
            { //3
                data: "price",
                name: "price"
            },
            { //4
                data: "quantity",
                name: "quantity"
            },
            { //5
                data: "status",
				render: function(data, type, row) {
					status_text = locales["text_no"] ?? "no";
					if (row['status']  )
						status_text = locales["text_yes"] ?? "yes";
					
					return status_text;
				}
            },
            { //6
                name: "push_status",
				render: function(data, type, row) {
					//'pushed','push_failed','approved','rejected'
					var _class = '';
					if (row['push_status'] == 'pushed' )
						_class = 'default';
					else if (row['push_status'] == 'approved' )
						_class = 'success';
					else 
						_class = 'danger';
					
					var locale_index = '';
					var status_text  = '';
					
					if(row["push_status"]){
						locale_index = 'status_' +row["push_status"];
						status_text = locales[locale_index];
					}
						
					
					
					var rejection_tooltip = '';
					
					if(row['rejection_reason'])
						rejection_tooltip = 'data-toggle="tooltip" data-placement="top" title="' + row["rejection_reason"].replace(/['"]+/g, '') +'"';
						
					return '<span class="label label-'+_class+'"  '+ rejection_tooltip +'>'+status_text+'</span>';
                }
            }
            
        ],

    });

    table.on('xhr', function(e, settings, json, xhr) {
        $('.totalHeading').text(json.heading);
        next = json.next_cursor;
        previous = json.previous_cursor;
        start = json.start;
    });

    table.on('select', function(e, objDT, type, indexes) {
        if (table.rows('.selected').any()){
			$(".btn-has-disabled").removeClass('disabled');
			$(".btn-has-disabled").prop("disabled", false);
		}
        else{
			$(".btn-has-disabled").addClass('disabled');
			$(".btn-has-disabled").attr("disabled", true);
		}
        var count = table.rows('.selected').count()
        $(".white-bg").text(count);
    }).on('deselect', function(e, objDT, type, indexes) {
        if (table.rows('.selected').any()) {
			$(".btn-has-disabled").removeClass('disabled');
			$(".btn-has-disabled").prop("disabled", false);
		}
        else {
			$(".btn-has-disabled").addClass('disabled');
			$(".btn-has-disabled").attr("disabled", true);
		}
        var count = table.rows('.selected').count()
        $(".white-bg").text(count);
    }).on('search.dt', function(e, objDT) {
        if (table.rows('.selected').any()){
			$(".btn-has-disabled").removeClass('disabled');
			$(".btn-has-disabled").prop("disabled", false);
		}
        else {
			$(".btn-has-disabled").addClass('disabled');
			$(".btn-has-disabled").prop("disabled", true);
		}
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