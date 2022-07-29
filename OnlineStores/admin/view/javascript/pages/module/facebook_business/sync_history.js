var table;

$(document).ready(function() {
    // dataTable initialize
    table = $('#productsTable').DataTable({
        searching	: false,
		ordering	: false,
        serverSide	: true,
        paging		: true,
        dom			: 'Rfrtlip',
        language	: {
						...locales['dt_language'],
						search: ''
					 },
        columnDefs	: [
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
            },

        },
        columns: [
            { //0
                data: "job_id",
                name: 'job_id'
            },
            { //1
                data: "catalog_id",
                name: 'catalog_id'
            },
            { //2
                data: "status",
                render: function(data, type, row) {
				var _class = '';

				
				if(row["operation"] == "export" ){
					//in case of export we will use the status of batch 
					var fb_status = row["fb_status"] ?? 'dispatched';
					var locale_index = 'batch_status_' + fb_status;
					if (fb_status =='finished')
							_class = 'success';
						else if (fb_status == 'canceled' || fb_status == 'error')
							_class = 'danger';
						else 
							_class = 'warning';
					
				} else {
						//in case of import we will use the status of job 
						if (row['status'] == 'completed')
							_class = 'success';
						else if (row['status'] == 'failed' )
							_class = 'danger';
						else 
							_class = 'warning';
					var locale_index = 'queue_' +row["status"];
				}
					var status_text = locales[locale_index];
                    return '<span class="label label-'+_class+'">'+status_text+'</span>';
                }
            },
            { //3
                data: "created_at",
                name: 'created_at'
            },
            { //4
                data: "finished_at",
                name: 'finished_at'
            },
            { //5
                data: "product_count",
				render: function(data, type, row) {
					if(row["operation"] == "export" ){
						
						return row["products_total_count"];
					}
					
					return row["product_count"];
                }
            },
            { //6
                data: "operation",
               render: function(data, type, row) {
                    var locale_index = 'queue_' +row["operation"];
					return '<span class="label label-primary">'+locales[locale_index]+'</span>';
                }
            },
            
        ],
        columnDefs: [
			{
				targets: [0, 1, 2, 3, 4, 5, 6],
				orderable: false,
			}, 
		],

    });

    table.on('xhr', function(e, settings, json, xhr) {
        $('.totalHeading').text(json.heading);
       
    });

});
