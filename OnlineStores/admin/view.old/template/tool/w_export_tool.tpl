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
    <?php if ($success) { ?>
    <script>
        var notificationString = '<?php echo $success; ?>';
        var notificationType = 'success';
    </script>
    <?php } ?>
  <div class="box">
    <div class="heading">
      <h1>Excel Export</h1>
    </div>
    <div class="content">
	 	<div class="content">
		   <div id="tab-export">
			  <div id="vtabs" class="vtabs">
				<a href="#customer_export">Customers</a>
				<a href="#order_export">Orders</a>
			  </div>
			  <div id="customer_export" class="vtabs-content">
				<?php require_once('export/customerexport.tpl'); ?>
			  </div>
			  <div id="order_export" class="vtabs-content">
				<?php require_once('export/orderexport.tpl'); ?>
			  </div>
			</div>
		</div>
	</div>
  </div>
</div>
<script type="text/javascript"><!--
$('#button-order_export').on('click', function(){
	url = 'index.php?route=tool/w_export_tool/exportOrder&token=<?php echo $token; ?>';
	
	var filter_to_order_id = $('#order_export input[name=\'filter_to_order_id\']').val();

	if(filter_to_order_id != ''){
		url += '&filter_to_order_id=' + encodeURIComponent(filter_to_order_id);
	}
	
	var filter_from_order_id = $('#order_export input[name=\'filter_from_order_id\']').val();

	if(filter_from_order_id != ''){
		url += '&filter_from_order_id=' + encodeURIComponent(filter_from_order_id);
	}
	
	var filter_order_status = $('#order_export select[name=\'filter_order_status\']').val();

	if(filter_order_status != '*'){
		url += '&filter_order_status=' + encodeURIComponent(filter_order_status);
	}
	
	var filter_total = $('#order_export input[name=\'filter_total\']').val();

	if(filter_total != ''){
		url += '&filter_total=' + encodeURIComponent(filter_total);
	}
	
	var filter_to_date_added = $('#order_export input[name=\'filter_to_date_added\']').val();

	if(filter_to_date_added != ''){
		url += '&filter_to_date_added=' + encodeURIComponent(filter_to_date_added);
	}
	
	var filter_from_date_added = $('#order_export input[name=\'filter_from_date_added\']').val();

	if(filter_from_date_added != ''){
		url += '&filter_from_date_added=' + encodeURIComponent(filter_from_date_added);
	}
	
	var filter_to_date_modified = $('#order_export input[name=\'filter_to_date_modified\']').val();

	if(filter_to_date_modified != ''){
		url += '&filter_to_date_modified=' + encodeURIComponent(filter_to_date_modified);
	}
	
	var filter_form_date_modified = $('#order_export input[name=\'filter_form_date_modified\']').val();

	if(filter_form_date_modified != ''){
		url += '&filter_form_date_modified=' + encodeURIComponent(filter_form_date_modified);
	}
	
	var filter_start = $('#order_export input[name=\'filter_start\']').val();

	if(filter_start != ''){
		url += '&filter_start=' + encodeURIComponent(filter_start);
	}

	var filter_limit = $('#order_export input[name=\'filter_limit\']').val();

	if(filter_limit != ''){
		url += '&filter_limit=' + encodeURIComponent(filter_limit);
	}
	
	location = url;
});

$('#button-customer_export').on('click', function(){
	url = 'index.php?route=tool/w_export_tool/exportCustomer&token=<?php echo $token; ?>';
	
	var filter_name = $('#customer_export input[name=\'filter_name\']').val();
	
	if (filter_name) {
		url += '&filter_name=' + encodeURIComponent(filter_name);
	}
	
	var filter_email = $('#customer_export input[name=\'filter_email\']').val();
	
	if (filter_email) {
		url += '&filter_email=' + encodeURIComponent(filter_email);
	}
	
	var filter_start = $('#customer_export input[name=\'filter_start\']').val();
	
	if (filter_start) {
		url += '&filter_start=' + encodeURIComponent(filter_start);
	}
	
	var filter_limit = $('#customer_export input[name=\'filter_limit\']').val();
	
	if (filter_limit) {
		url += '&filter_limit=' + encodeURIComponent(filter_limit);
	}
	
	var filter_customer_group_id = $('#customer_export select[name=\'filter_customer_group_id\']').val();
	
	if (filter_customer_group_id != '*') {
		url += '&filter_customer_group_id=' + encodeURIComponent(filter_customer_group_id);
	}	
	
	var filter_status = $('#customer_export select[name=\'filter_status\']').val();
	
	if (filter_status != '*') {
		url += '&filter_status=' + encodeURIComponent(filter_status); 
	}	
	
	var filter_approved = $('#customer_export select[name=\'filter_approved\']').val();
	
	if (filter_approved != '*') {
		url += '&filter_approved=' + encodeURIComponent(filter_approved);
	}	
	
	var filter_ip = $('#customer_export input[name=\'filter_ip\']').val();
	
	if (filter_ip) {
		url += '&filter_ip=' + encodeURIComponent(filter_ip);
	}
		
	var filter_date_added = $('#customer_export input[name=\'filter_date_added\']').val();
	
	if(filter_date_added){
		url += '&filter_date_added=' + encodeURIComponent(filter_date_added);
	}
	location = url;
});
//--></script>
<script type="text/javascript"><!--
$('input[name=\'customer_name\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
			url: 'index.php?route=sale/customer/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
			dataType: 'json',			
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item['name'],
						value: item['customer_id']
					}
				}));
			}
		});
	},
	'select': function(item) {
		$('input[name=\'customer_name\']').val(item['label']);
	}	
});


$('input[name=\'filter_email\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
			url: 'index.php?route=sale/customer/autocomplete&token=<?php echo $token; ?>&filter_email=' +  encodeURIComponent(request),
			dataType: 'json',			
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item['email'],
						value: item['customer_id']
					}
				}));
			}
		});
	},
	'select': function(item) {
		$('input[name=\'filter_email\']').val(item['label']);
	}	
});
//--></script>
<script type="text/javascript"><!--
$(document).ready(function() {
	$('#date').datepicker({dateFormat: 'yy-mm-dd'});
	$('#to_date_addedd').datepicker({dateFormat: 'yy-mm-dd'});
	$('#from_date_addedd').datepicker({dateFormat: 'yy-mm-dd'});
	$('#filter_to_date_modified').datepicker({dateFormat: 'yy-mm-dd'});
	$('#filter_form_date_modified').datepicker({dateFormat: 'yy-mm-dd'});
});
//--></script>
<script type="text/javascript"><!--
$('.htabs a').tabs();
$('.vtabs a').tabs();
//--></script>
<style>
.button_export{
	background: skyblue none repeat scroll 0 0;
    border: medium none;
    color: #fff;
    padding: 7px;
}
</style>
<?php echo $footer; ?>