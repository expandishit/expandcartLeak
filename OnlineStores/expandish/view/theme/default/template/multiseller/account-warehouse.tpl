<?php echo $header; ?>

<div id="content" class="ms-account-profile row">
	<?php echo $content_top; ?>
	
	<div class="breadcrumb col-md-12">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?>
		<a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	
	<h2 class="heading-profile"> <?php echo $ms_account_warehouse_heading; ?> :</h2>
	
	<?php if (isset($success) && ($success)) { ?>
		<div class="success"><?php echo $success; ?></div>
	<?php } ?>
	
	<?php if (isset($statustext) && ($statustext)) { ?>
		<div class="<?php echo $statusclass; ?>"><?php echo $statustext; ?></div>
	<?php } ?>

	<p class="warning main"></p>
	
	<form id="ms-warehouseinfo" class="ms-form">
		<input type="hidden" name="action" id="ms_action" />
		
		<div class="content">
			<!-- todo status check update -->
			<table class="ms-product">
				<tr>
					<td><span class="required">*</span> <?php echo $ms_account_warehouse_name; ?></td>
					<td>
						<input type="text" name="warehouse[name]" value="<?php echo $warehouse['name'] ? $warehouse['name'] : ''; ?>" class="form-control" style='width:400px;'/>
						<p class="ms-note"><?php echo $ms_account_warehouse_name_note; ?></p>
					</td>
				</tr>

				<tr>
					<td><span class="required">*</span> <?php echo $ms_account_sellerinfo_rates; ?></td>
					<td>
						<div id="general-tabs" class="htabs" style="display: inline-block;">
							<a href="#tab-geo_zone_0"><?php echo $ms_geo_zone_general ?></a>
							<?php foreach ($geo_zones as $geo_zone) { ?>
				     		<a href="#tab-geo_zone_<?php echo $geo_zone['geo_zone_id'] ?>"><?php echo $geo_zone['name'] ?></a>
				     		<?php } ?>
				     	</div>
				     	<div id="tab-geo_zone_0">
				     		<label><?php echo $ms_account_sellerinfo_rates_label; ?></label>
				     		<textarea name="warehouse[rates][0]"
                                          cols="40" class="form-control"
                                          id="rates_0"
                                          rows="5"><?php echo $rates[0] ?></textarea>
                                <p class="ms-note"><?php echo $ms_account_sellerinfo_rates_note; ?></p>
                              <br/>
                              <label><?php echo $ms_account_sellerinfo_duration_label; ?></label>
                              <input name="warehouse[duration][0]" class="form-control" id="duration_0" value="<?php echo $duration[0] ?>" />
                                <p class="ms-note"><?php echo $ms_account_sellerinfo_duration_note; ?></p>
				     	</div>
				     	<?php foreach ($geo_zones as $geo_zone) { ?>
				     		<div id="tab-geo_zone_<?php echo $geo_zone['geo_zone_id'] ?>">
				     			<label><?php echo $ms_account_sellerinfo_rates_label; ?></label>
				     			<textarea name="warehouse[rates][<?php echo $geo_zone['geo_zone_id'] ?>]"
                                          cols="40" class="form-control"
                                          id="rates_<?php echo $geo_zone['geo_zone_id'] ?>"
                                          rows="5"><?php echo $rates[$geo_zone['geo_zone_id']] ?></textarea>
                                 <p class="ms-note"><?php echo $ms_account_sellerinfo_rates_note; ?></p>
                                 <br/>
                                 <label><?php echo $ms_account_sellerinfo_duration_label; ?></label>
                                 <input name="warehouse[duration][<?php echo $geo_zone['geo_zone_id'] ?>]" class="form-control" id="duration_<?php echo $geo_zone['geo_zone_id'] ?>"
                                          value="<?php echo $duration[$geo_zone['geo_zone_id']] ?>">
                                <p class="ms-note"><?php echo $ms_account_sellerinfo_duration_note; ?></p>
				     		</div>
				     	<?php } ?>
					</td>
				</tr>

				<!-- <tr>
					<td><?php echo $ms_account_warehouse_address; ?></td>
					<td>
						<input type="text" name="warehouse[address]" value="<?php echo $warehouse['address'] ? $warehouse['address'] : ''; ?>" class="form-control" style='width:400px;'/>
						<p class="ms-note"><?php echo $ms_account_warehouse_address_note; ?></p>
					</td>
				</tr>

				<tr>
					<td><span class="required">*</span> <?php echo $ms_account_warehouse_charge; ?> (<?php echo $currency?>)</td>
					<td>
						<input type="text" name="warehouse[charge]" value="<?php echo $warehouse['seller_charge'] ? $warehouse['seller_charge'] : ''; ?>" class="form-control" />
						<p class="ms-note"><?php echo $ms_account_warehouse_charge_note; ?></p>
					</td>
				</tr>

				<tr>
					<td><span class="required">*</span> <?php echo $ms_account_sellerinfo_country; ?></td>
					<td>
						<select name="warehouse[country]" class="form-control">
							<option value="0" selected="selected"><?php echo $ms_select; ?></option>
							<?php foreach ($countries as $country) { ?>
							<option value="<?php echo $country['country_id']; ?>" <?php if ($warehouse['country_id'] == $country['country_id']) { ?>selected="selected"<?php } ?>><?php echo $country['name']; ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>

				<tr>
					<td><span class="required">*</span> <?php echo $ms_account_sellerinfo_zone; ?></td>
					<td>
						<select name="warehouse[zone]" class="form-control">
						</select>
					</td>
				</tr> -->
			</table>
		</div>
		</form>
		
		
		<div class="buttons">
			<div class="left">
				<a href="<?php echo $link_back; ?>" class="button">
					<span><?php echo $button_back; ?></span>
				</a>
			</div>
			
			<div class="right">
				<a class="button" id="wr-submit-button">
					<span><?php echo $ms_button_save; ?></span>
				</a>
			</div>
		</div>
	<?php echo $content_bottom; ?>
</div>

<?php $timestamp = time(); ?>
<script>
	
var msGlobals = {
	zone_id: '<?php echo $warehouse['zone_id'] ?>',
	select: '<?php echo $ms_select ?>',
};

$(function() {
	$('#general-tabs a').tabs();

	$("#wr-submit-button").click(function() {
		$('.success').remove();
		var button = $(this);
		var id = $(this).attr('id');
		
		$.ajax({
			type: "POST",
			dataType: "json",
			url: $('base').attr('href') + 'index.php?route=seller/account-warehouse/jxSaveWarehouseInfo',
			data: $("form#ms-warehouseinfo").serialize(),
			beforeSend: function() {
				button.hide().before('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
				$('p.error').remove();
			},
			complete: function(jqXHR, textStatus) {
				if (textStatus != 'success') {
					button.show().prev('span.wait').remove();
					$(".warning.main").text(msGlobals.formError).show();
					window.scrollTo(0,0);
				}
			},
			success: function(jsonData) {
				if (!jQuery.isEmptyObject(jsonData.errors)) {
					$('#wr-submit-button').show().prev('span.wait').remove();
					$('.error').text('');
					for (error in jsonData.errors) {
						if ($('[name="'+error+'"]').length > 0)
							$('[name="'+error+'"]').parents('td').append('<p class="error">' + jsonData.errors[error] + '</p>');
						else if ($('#error_'+error).length > 0)
							$('#error_'+error).text(jsonData.errors[error]);
						else
							$(".warning.main").text(jsonData.errors[error]).show();
					}
					window.scrollTo(0,0);
				} else {
					window.location = jsonData.redirect;
				}
	       	}
		});
	});
	
	$("select[name='warehouse[country]']").bind('change', function() {
		$.ajax({
			url: $('base').attr('href') + 'index.php?route=seller/register-seller/country&country_id=' + this.value,
			dataType: 'json',
			beforeSend: function() {
				$("select[name='warehouse[country]']").after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
			},
			complete: function() {
				$('.wait').remove();
			},
			success: function(json) {
				html = '<option value="0">' + msGlobals.select + '</option>';

				if (json['zone']) {
					for (i = 0; i < json['zone'].length; i++) {
						html += '<option value="' + json['zone'][i]['zone_id'] + '"';
						
						if (json['zone'][i]['zone_id'] == msGlobals.zone_id) {
							html += ' selected="selected"';
						}
		
						html += '>' + json['zone'][i]['name'] + '</option>';
					}
				} /*else {
					html += '<option value="0" selected="selected">' + msGlobals.zoneNotSelectedError + '</option>';
				}*/
				
				$("select[name='warehouse[zone]']").html(html);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}).trigger('change');
});

</script>

<?php echo $footer; ?>