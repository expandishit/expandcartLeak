function loadNextSub(parent, slct_lbl) {
	parent = parseInt(parent);

	var droplist_levels = parseInt($('#droplist_levels').val());
	var next_sub = parent + 1;
	var category = $('#category_level_'+parent).val();
	$('#category_category').val(category);

	if(parent <= droplist_levels){
		var lable = $('#category_lable_'+next_sub).val();
		$('#category_level_'+next_sub).html('<option value="0">--- '+slct_lbl+' '+lable+' ---</option>');

		$.ajax({
			url: 'index.php?route=product/category/getSubCategories',
			type: 'get',
			data: 'parent=' + category +'&json=1&show_droplist=1',
			dataType: 'json',
			success: function(json) {

				if(json.status == 'success'){

					var data_list = json.data;
					var new_list = '';

					for(var i = 0; i < data_list.length; i++) {
					    new_list += '<option value="'+data_list[i]['id']+'">'+data_list[i]['name']+'</option>';
					}
					
					$('#category_level_'+ next_sub).append(new_list);
				}
			}
		});
	}
}

function categoryDroplistSubmit(){
	var category = $('#category_category').val();
	var url = $('base').attr('href') + "index.php?route=product/category&path=";
	var submitURL = url+category;

	if (!url.includes('route=product/category')) {
		submitURL = url + category;
	}

	if(category != 0) {
		window.location.href = submitURL;
	}
}