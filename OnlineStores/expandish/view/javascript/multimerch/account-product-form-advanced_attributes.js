$(function() {
	// load attributes tab
	$('#tab-advanced-attributes').load($('base').attr('href') + 'index.php?route=module/advanced_product_attributes/jxRenderAttributes&product_id=' + msGlobals.product_id, function(data){
		// load existing product attibutes
		if (msGlobals.product_id.length > 0) {
			$.get($('base').attr('href') + 'index.php?route=module/advanced_product_attributes/jxRenderProductAttributes&product_id=' + msGlobals.product_id, function(data) {
				$('div.advanced-attributes').append(data).find('input[name$="[advanced_attribute_id]"]').each(function(index) {
					$(this).closest('.ms-advanced-attributes').find('.select_advanced_attribute option[value="'+ $(this).val() + '"]').attr('disabled', true );
				});

			});
		}

	});

	// delete
	$('body').delegate(".advanced_attribute_delete", "click", function() {
		var attr_id = $(this).closest('.advanced-attribute').find('input[name$="[advanced_attribute_id]"]').val();
		$(this).closest('.advanced-attribute').remove();
		$('#select-advanced-attributes').multiselect('deselect', attr_id , true);
	});
});

	
