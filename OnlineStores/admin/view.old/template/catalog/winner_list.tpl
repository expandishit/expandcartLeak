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
      <h1><?php echo $heading_title; ?></h1>
	  
	   <div class="buttons"><a onclick="$('form').submit();" class="button btn btn-primary"><?php echo $button_delete; ?></a>
	  </div>
    </div>
      
    </div>
    <div class="content">
      <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="table table-hover dataTable no-footer">
          <thead>
            <tr>
              <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>
             
              <td class="left"><?php if ($sort == 'pd.name') { ?>
                <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_name; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_name; ?>"><?php echo $column_name; ?></a>
                <?php } ?></td>
		<td class="left"><?php if ($sort == 'w.name') { ?>
                <a href="<?php echo $sort_names; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_names; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_names; ?>"><?php echo $column_names; ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'w.nickname') { ?>
                <a href="<?php echo $sort_nickname; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_nickname; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_nickname; ?>"><?php echo $column_nickname; ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'w.date_added') { ?>
                <a href="<?php echo $sort_bid_date_end; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_bid_date_end; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_bid_date_end; ?>"><?php echo $column_bid_date_end; ?></a>
                <?php } ?></td>
				
				<td class="left"><?php if ($sort == 'w.price_bid') { ?>
                <a href="<?php echo $sort_price; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_bid_price; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_price; ?>"><?php echo $column_bid_price; ?></a>
                <?php } ?></td>
				
             
				
				 <td class="left"><?php if ($sort == 'w.status') { ?>
                <a href="<?php echo $sort_status; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_status; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_status; ?>"><?php echo $column_status; ?></a>
                <?php } ?></td>
				
				<td class="right"><?php echo $column_action; ?></td>
				
            </tr>
          </thead>
          <tbody>
            <tr class="filter">
              <td></td>
              <td><input type="text" name="filter_name" value="<?php echo $filter_name; ?>" /></td>
              <td><input type="text" name="filter_names" value="<?php echo $filter_names; ?>" /></td>
              <td align="left"><input type="text" name="filter_nickname" value="<?php echo $filter_nickname; ?>"/></td>
              <td align="right"></td>
			   <td align="right"><input type="text" name="filter_price" value="<?php echo $filter_price; ?>" /></td>
			   
              <td></td>
              <td align="right"><a onclick="filter();" class="button btn btn-primary"><?php echo $button_filter; ?></a></td>
            </tr>
            <?php if ($products) { ?>
            <?php foreach ($products as $product) { ?>
            <tr>
              <td style="text-align: center;"><?php if ($product['selected']) { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $product['winner_id']; ?>" checked="checked" />
                <?php } else { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $product['winner_id']; ?>" />
                <?php } ?></td>
             
              <td class="left"><?php echo $product['productname']; ?></td>
			   <td class="left"><?php echo $product['name']; ?></td>			   
              <td class="left"><?php echo $product['nickname']; ?></td>
              <td class="left"><?php echo $product['dated_added']; ?></td>
			  <td class="left"><?php echo $product['bid_price']; ?></td>
			  
			   <td class="left"><?php echo $product['status']; ?></td>
			   
			   
			   <?php if($product['status_id']!= $this->config->get('config_complete_status_id')){?>
			    <td class="right"><?php foreach ($product['action'] as $action) { ?>

                [ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]

                <?php } ?></td>
				<?php } ?>

				
              
			  <td></td>
             
              
            </tr>
            <?php } ?>
            <?php } else { ?>
            <tr>
              <td class="center" colspan="9"><?php echo $text_no_results; ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </form>
      <div class="pagination"><?php echo $pagination; ?></div>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
function filter() {
	url = 'index.php?route=catalog/winner&token=<?php echo $token; ?>';
	
	var filter_name = $('input[name=\'filter_name\']').val();
	
	if (filter_name) {
		url += '&filter_name=' + encodeURIComponent(filter_name);
	}
	
	var filter_names = $('input[name=\'filter_names\']').val();
	
	if (filter_name) {
		url += '&filter_names=' + encodeURIComponent(filter_names);
	}
	
	var filter_price = $('input[name=\'filter_price\']').val();
	
	if (filter_price) {
		url += '&filter_price=' + encodeURIComponent(filter_price);
	}
	
	var filter_nickname = $('input[name=\'filter_nickname\']').val();
	
	if (filter_nickname) {
		url += '&filter_nickname=' + encodeURIComponent(filter_nickname);
	}
	
	var filter_bid_date_start = $('input[name=\'filter_bid_date_start\']').val();
	
	if (filter_bid_date_start) {
		url += '&filter_bid_date_start=' + encodeURIComponent(filter_bid_date_start);
	}
	
	var filter_bid_date_end = $('input[name=\'filter_bid_date_end\']').val();
	
	if (filter_bid_date_end) {
		url += '&filter_bid_date_end=' + encodeURIComponent(filter_bid_date_end);
	}
	
	var filter_status = $('select[name=\'filter_status\']').val();
	
	if (filter_status != '*') {
		url += '&filter_status=' + encodeURIComponent(filter_status);
	}	

	location = url;
}
//--></script> 
<script type="text/javascript"><!--
$('#form input').keydown(function(e) {
	if (e.keyCode == 13) {
		filter();
	}
});
//--></script> 
<script type="text/javascript"><!--
$('input[name=\'filter_name\']').autocomplete({
	delay: 0,
	source: function(request, response) {
		$.ajax({
			url: 'index.php?route=catalog/winner/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
			dataType: 'json',
			success: function(json) {		
				response($.map(json, function(item) {
					return {
						label: item.productname,
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
//--></script> 
<?php echo $footer; ?>