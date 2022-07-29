<?php echo $header; ?>
<div id="content">
<ol class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php if ($breadcrumb === end($breadcrumbs)) { ?>
    <li class="active">
        <?php } else { ?>
    <li>
        <?php } ?>
        <a href="<?php echo $breadcrumb['href']; ?>">
            <?php if ($breadcrumb === reset($breadcrumbs)) { ?>
            <?php echo $breadcrumb['text']; ?>
            <?php } else { ?>
            <span><?php echo $breadcrumb['text']; ?></span>
            <?php } ?>
        </a>
    </li>
    <?php } ?>
</ol>
<?php if ($error_warning) { ?>
<script>
    var notificationString = '<?php echo $error_warning; ?>';
    var notificationType = 'warning';
</script>
<?php } ?>
<div class="box">
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
	</div>
      <div class="content">
		<div id="tabs" class="htabs">
		  <a href="#tab-productdata"><?php echo $tab_producttab; ?></a>
		  <a href="#tab-reviewdata"><?php echo $tab_reviewdata; ?></a>
		</div>
		<div id="tab-productdata">
			<table class="form">
				<tr>
					<td><?php echo $entry_store; ?></td>
					<td>
						<select class="form-control" name="filter_store">
						 <option value="0"><?php echo $text_default; ?></option>
						 <?php foreach($stores as $store){ ?>	
							<option value="<?php echo $store['store_id']; ?>"><?php echo $store['name']; ?></option>
						  <?php } ?>
						</select>
					</td>
				</tr>
				<tr>
					<td><?php echo $entry_language; ?></td>
					<td>
						<select class="form-control" name="filter_language_id">
						<?php foreach($languages as $language){ ?>
							<option value="<?php echo $language['language_id']; ?>"><?php echo $language['name']; ?></option>
						<?php } ?>
						</select>
					</td>
				</tr>
				<tr style="display:none;">
					<td><?php echo $filter_pimage; ?></td>
					<td>
						<select name="filter_pimage" id="input-pimage">
						  <option value="no">NO</option>
						  <option value="yes">Yes</option>
						</select>
					</td>
				</tr>
				<tr>
					<td><?php echo $entry_categories; ?></td>
					<td>
					  <select name="filter_categories" id="input-categories" class="form-control">
						<option value="*"></option>
						<?php foreach($categories as $category){ ?>
						<option value="<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></option>
						<?php } ?>
					  </select>
					</td>
				</tr>
				<tr>
					<td><?php echo $entry_manufacturer; ?></td>
					<td>
					  <select name="filter_manufacturer" id="input-categories" class="form-control">
						<option value="*"></option>
						<?php foreach($manufacturers as $manufacturer){ ?>
						<option value="<?php echo $manufacturer['manufacturer_id']; ?>"><?php echo $manufacturer['name']; ?></option>
						<?php } ?>
					</select>
					</td>
				</tr>
				<tr>
					<td><?php echo $entry_name; ?></td>
					<td>
						<input type="text" name="filter_name" value="<?php echo $filter_name; ?>" placeholder="<?php echo $entry_name; ?>" id="input-name" class="form-control" />
					</td>
				</tr>
				<tr>
					<td><?php echo $entry_model; ?></td>
					<td>
					   <input type="text" name="filter_model" value="<?php echo $filter_model; ?>" placeholder="<?php echo $entry_model; ?>" id="input-model" class="form-control" />
					</td>
				</tr>
				<tr>
					<td><?php echo $entry_price; ?></td>
					<td>
					   <input type="text" name="filter_price_to" value="" placeholder="To" id="input-price" class="form-control" /> - <input type="text" name="filter_price_form" value="<?php echo $filter_price; ?>" placeholder="From" id="input-price" class="form-control" />
					</td>
				</tr>
				<tr>
					<td><?php echo $entry_quantity; ?></td>
					<td>
					  <input type="text" name="filter_quantity_to" value="" placeholder="To" id="input-quantity" class="form-control" /> - <input type="text" name="filter_quantity_form" value="" placeholder="From" id="input-quantity" class="form-control" />
					</td>
				</tr>
				<tr>
					<td><?php echo $entry_stock_status; ?></td>
					<td>
					  <select name="filter_stock_status" id="input-status" class="form-control">
						<option value="*"></option>
						<?php foreach($stock_statuses as $stock){ ?>
						<option value="<?php echo $stock['stock_status_id']; ?>"><?php echo $stock['name']; ?></option>
						<?php } ?>
					   </select>
					</td>
				</tr>
				<tr>
					<td><?php echo $entry_product_id; ?></td>
					<td>
					  <input type="text" name="filter_product_id" value="<?php echo $miniproduct_id; ?>" placeholder="To Product ID" id="input-start" class="form-control" />
					  -
					  <input type="text" name="filter_endproduct_id" value="<?php echo $maxproduct_id; ?>" placeholder="From Product ID" id="input-limit" class="form-control" />
					</td>
				</tr>
				<tr>
					<td><?php echo $entry_limit; ?></td>
					<td>
					  <input type="text" name="filter_start" value="0" placeholder="start" id="input-start" class="form-control" />
					  -
					  <input type="text" name="filter_limit" value="<?php echo $filter_limit; ?>" placeholder="<?php echo $entry_limit; ?>" id="input-limit" class="form-control" />
					</td>
				</tr>
				<tr>
					<td><?php echo $entry_status; ?></td>
					<td>
					  <select name="filter_status" id="input-status" class="form-control">
					  <option value="*"></option>
					  <?php if ($filter_status) { ?>
					  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
					  <?php } else { ?>
					  <option value="1"><?php echo $text_enabled; ?></option>
					  <?php } ?>
					  <?php if (!$filter_status && !is_null($filter_status)) { ?>
					  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
					  <?php } else { ?>
					  <option value="0"><?php echo $text_disabled; ?></option>
					  <?php } ?>
					</select>
					</td>
				</tr>
				<tr>
					<td><?php echo $export_format; ?></td>
					<td>
						<select name="filter_eformat" id="input-pimage">
							<option value="xls">XLS</option>
							<option value="csv">CSV</option>
							<option value="xlsx">XLSX</option>
						</select>
					</td>
				</tr>
                <tr>
                    <td colspan="2">
                        <button style="width:70%; margin-top:23px;" id="button-filter" class="button_export"><?php echo $button_export; ?></button>
                    </td>
                </tr>
			</table>
		  </div>
		  <div id="tab-reviewdata">
			<table class="form">
			  <tr>
				<td><?php echo $entry_name; ?></td>
				<td>
					<input type="text" name="filter_name" value="<?php echo $filter_name; ?>" placeholder="<?php echo $entry_name; ?>" id="input-name" class="form-control" />
				</td>
			  </tr>
			  <tr>
				<td><?php echo $entry_product_id; ?></td>
				<td>
				  <input type="text" name="filter_product_id" value="<?php echo $miniproduct_id; ?>" placeholder="To Product ID" id="input-start" class="form-control" />
				  -
				  <input type="text" name="filter_endproduct_id" value="<?php echo $maxproduct_id; ?>" placeholder="From Product ID" id="input-limit" class="form-control" />
				</td>
			  </tr>	
			  <tr>
				<td><?php echo $entry_limit; ?></td>
				<td>
					<input  type="text" name="filter_start" value="0" placeholder="Start" id="input-start" class="form-control"/> -
					<input type="text" name="filter_limit" value="<?php echo $filter_limit; ?>" placeholder="Limit" id="input-limit" class="form-control" />
				</td>
			  </tr>
			  <tr>
				<td><?php echo $entry_status; ?></td>
				<td>
					<select name="filter_status" id="input-status">
					  <option value="*"></option>
					  <option value="1"><?php echo $text_enabled; ?></option>
					  <option value="0"><?php echo $text_disabled; ?></option>
					</select>
				</td>
			  </tr>
			  <tr>
				<td><?php echo $export_format; ?></td>
				<td>
					<select name="filter_eformat" id="input-eformat">
					  <option value="xls">XLS</option>
					  <option value="csv">CSV</option>
					  <option value="xlsx">XLSX</option>
					</select>
				</td>
			  </tr>
			  <tr>
				<td colspan="2">
				  <button style="width:70%; margin-top:23px;" type="button" id="buttonproductreview" class="button_export"><?php echo $button_export; ?></button>
				</td>
			  </tr>
			</table>
		  </div>
		</div>
    </div>
 </div>
<script type="text/javascript"><!--
$('#buttonproductreview').on('click', function(){
	url = 'index.php?route=tool/product_export/exportproductreview&token=<?php echo $token; ?>';
	var filter_start = $('#tab-reviewdata input[name=\'filter_start\']').val();
	if(filter_start){
		url += '&filter_start=' + encodeURIComponent(filter_start);
	}
	
	var filter_limit = $('#tab-reviewdata input[name=\'filter_limit\']').val();
	if(filter_limit){
		url += '&filter_limit=' + encodeURIComponent(filter_limit);
	}
		
	var filter_status = $('#tab-reviewdata select[name=\'filter_status\']').val();
	if(filter_status != '*'){
		url += '&filter_status=' + encodeURIComponent(filter_status); 
	}
	
	var filter_eformat = $('#tab-reviewdata select[name=\'filter_eformat\']').val();
	if(filter_eformat != ''){
		url += '&filter_eformat=' + encodeURIComponent(filter_eformat);
	}
	
	location = url;
});

$('#button-filter').on('click', function() {
	var url = 'index.php?route=tool/product_export/export&token=<?php echo $token; ?>';

	var filter_name = $('#tab-productdata input[name=\'filter_name\']').val();

	if (filter_name) {
		url += '&filter_name=' + encodeURIComponent(filter_name);
	}

	var filter_model = $('#tab-productdata input[name=\'filter_model\']').val();

	if (filter_model) {
		url += '&filter_model=' + encodeURIComponent(filter_model);
	}

	var filter_price_to = $('#tab-productdata input[name=\'filter_price_to\']').val();

	if (filter_price_to) {
		url += '&filter_price_to=' + encodeURIComponent(filter_price_to);
	}
	
	var filter_price_form = $('#tab-productdata input[name=\'filter_price_form\']').val();

	if (filter_price_form) {
		url += '&filter_price_form=' + encodeURIComponent(filter_price_form);
	}

	var filter_quantity_to = $('#tab-productdata input[name=\'filter_quantity_to\']').val();

	if (filter_quantity_to) {
		url += '&filter_quantity_to=' + encodeURIComponent(filter_quantity_to);
	}
	
	var filter_quantity_form = $('#tab-productdata input[name=\'filter_quantity_form\']').val();

	if (filter_quantity_form) {
		url += '&filter_quantity_form=' + encodeURIComponent(filter_quantity_form);
	}
	
	var filter_status = $('#tab-productdata select[name=\'filter_status\']').val();

	if (filter_status != '*') {
		url += '&filter_status=' + encodeURIComponent(filter_status);
	}
	
	var filter_store = $('#tab-productdata select[name=\'filter_store\']').val();

	if (filter_store != '*') {
		url += '&filter_store=' + encodeURIComponent(filter_store);
	}
	
	var filter_language_id = $('#tab-productdata select[name=\'filter_language_id\']').val();

	if (filter_language_id != '*') {
		url += '&filter_language=' + encodeURIComponent(filter_language_id);
	}
	
	var filter_stock_status = $('#tab-productdata select[name=\'filter_stock_status\']').val();

	if (filter_stock_status != '*') {
		url += '&filter_stock_status=' + encodeURIComponent(filter_stock_status);
	}
	
	var filter_start = $('#tab-productdata input[name=\'filter_start\']').val();

	if (filter_start != '*') {
		url += '&filter_start=' + encodeURIComponent(filter_start);
	}
	
	var filter_limit = $('#tab-productdata input[name=\'filter_limit\']').val();

	if (filter_limit != '*') {
		url += '&filter_limit=' + encodeURIComponent(filter_limit);
	}
	
	var filter_categories = $('#tab-productdata select[name=\'filter_categories\']').val();

	if (filter_categories != '*') {
		url += '&filter_categories=' + encodeURIComponent(filter_categories);
	}
	
	var filter_manufacturer = $('#tab-productdata select[name=\'filter_manufacturer\']').val();

	if (filter_manufacturer != '*') {
		url += '&filter_manufacturer=' + encodeURIComponent(filter_manufacturer);
	}
	
	var filter_product_id = $('#tab-productdata input[name=\'filter_product_id\']').val();

	if(filter_product_id != ''){
		url += '&filter_product_id=' + encodeURIComponent(filter_product_id);
	}
	
	var filter_endproduct_id = $('#tab-productdata input[name=\'filter_endproduct_id\']').val();
	if(filter_endproduct_id != ''){
		url += '&filter_endproduct_id=' + encodeURIComponent(filter_endproduct_id);
	}
	
	var filter_eformat = $('#tab-productdata select[name=\'filter_eformat\']').val();
	if(filter_eformat != ''){
		url += '&filter_eformat=' + encodeURIComponent(filter_eformat);
	}
	
	var filter_pimage = $('#tab-productdata select[name=\'filter_pimage\']').val();
	if(filter_pimage != ''){
		url += '&filter_pimage=' + encodeURIComponent(filter_pimage);
	}
	
	location = url;
});
//--></script>
<script type="text/javascript"><!--
$('input[name=\'filter_name\']').autocomplete({
	delay: 500,
	source: function(request, response) {
		$.ajax({
			url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
			dataType: 'json',
			success: function(json) {		
				response($.map(json, function(item) {
					return {
						label: item.name,
						value: item.product_id
					}
				}));
			}
		});
	}, 
	select: function(event, ui) {
		$('input[name=\'filter_name\']').val(ui.item.label);
						
		return false;
	},
	focus: function(event, ui) {
      	return false;
   	}
});

$('input[name=\'filter_model\']').autocomplete({
	delay: 500,
	source: function(request, response) {
		$.ajax({
			url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_model=' +  encodeURIComponent(request.term),
			dataType: 'json',
			success: function(json) {		
				response($.map(json, function(item) {
					return {
						label: item.model,
						value: item.product_id
					}
				}));
			}
		});
	}, 
	select: function(event, ui) {
		$('input[name=\'filter_model\']').val(ui.item.label);
						
		return false;
	},
	focus: function(event, ui) {
      	return false;
   	}
});
//--></script>
<script type="text/javascript"><!--
$('#tabs a').tabs(); 
//--></script> 
<style>
.button_export{
	background:skyblue none repeat scroll 0 0;
    border:medium none;
    color:#fff;
    padding:7px;
	cursor:pointer;
}
</style>
<?php echo $footer; ?>