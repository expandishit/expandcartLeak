$(function() {
	// load attributes tab
	$('#tab-attributes').load($('base').attr('href') + 'index.php?route=seller/account-product/jxRenderAttributes&product_id=' + msGlobals.product_id, function(data){
		// load existing product attibutes
		if (msGlobals.product_id.length > 0) {
			$.get($('base').attr('href') + 'index.php?route=seller/account-product/jxRenderProductAttributes&product_id=' + msGlobals.product_id, function(data) {
				$('div.attributes').append(data).find('input[name$="[attribute_id]"]').each(function(index) {
					$(this).closest('.ms-attributes').find('.select_attribute option[value="'+ $(this).val() + '"]').attr('disabled', true );
				});

			});
		}

	});

	// add

	$('body').delegate(".select_attribute", "change", function() {
		$(this).children(':selected').attr('disabled', 'disabled');
		var attribute_id = $(this).children(':selected').val();
		var select = this;

		$.get($('base').attr('href') + 'index.php?route=seller/account-product/jxRenderAttributesValues&attribute_id=' + attribute_id, function(data) {
			var lastRow = $(select).parents('.ms-attributes').find('.attribute:last input:last').attr('name');

			if (typeof lastRow == "undefined") {
				var newRowNum = 1;
			} else {
				var newRowNum = parseInt(lastRow.match(/[0-9]+/g).shift()) + 1;
			}

			var data = $(data);
			data.find('input,select').attr('name', function(i,name) {
				if (name) return name.replace('product_attribute_data[0]','product_attribute_data[' + newRowNum + ']');
			});
			$('div.attributes').append(data);
		});
		$('#attributes_language-tabs a.lang').tabs();

		$(this).val(0);
	});

	// delete
	$('body').delegate(".attribute_delete", "click", function() {
		var attr_id = $(this).closest('.attribute').find('input[name$="[attribute_id]"]').val();
		$('.select_attribute option[value="'+ attr_id + '"]').attr('disabled', false);
		$(this).closest('.attribute').remove();
	});

});