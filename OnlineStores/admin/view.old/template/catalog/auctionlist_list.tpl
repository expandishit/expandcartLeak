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
      <div class="buttons"><a onclick="location = '<?php echo $insert; ?>'" class="button btn btn-primary"><?php echo $button_insert; ?></a>
	  <a onclick="location = '<?php echo $winner; ?>'" class="button btn btn-primary"><?php echo $button_auction; ?></a>
	  <a onclick="$('form').submit();" class="button btn btn-primary"><?php echo $button_delete; ?></a>
	   <a onclick="location = '<?php echo $blockip; ?>'" class="button btn btn-primary"><?php echo $button_block; ?></a>
	     <a onclick="location = '<?php echo $blacklist; ?>'" class="button btn btn-primary"><?php echo $button_blacklist; ?></a>
		 
	  </div>
    </div>
    <div class="content">
      <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="table table-hover dataTable no-footer">
          <thead>
            <tr>
              <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>
              <td class="center"><?php echo $column_image; ?></td>
              <td class="left"><?php if ($sort == 'pd.name') { ?>
                <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_name; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_name; ?>"><?php echo $column_name; ?></a>
                <?php } ?></td>
		<td class="left"><?php if ($sort == 'pb.bid_start_price') { ?>
                <a href="<?php echo $sort_bid_start_price; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_bid_start_price; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_bid_start_price; ?>"><?php echo $column_bid_start_price; ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'pb.bid_date_start') { ?>
                <a href="<?php echo $sort_bid_date_start; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_bid_date_start; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_bid_date_start; ?>"><?php echo $column_bid_date_start; ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'pb.bid_date_end') { ?>
                <a href="<?php echo $sort_bid_date_end; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_bid_date_end; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_bid_date_end; ?>"><?php echo $column_bid_date_end; ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'p.status') { ?>
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
              <td></td>
              <td><input type="text" name="filter_name" value="<?php echo $filter_name; ?>" /></td>
              <td><input type="text" name="filter_bid_start_price" value="<?php echo $filter_bid_start_price; ?>" /></td>
              <td align="left"><input type="text" name="filter_bid_date_start" value="<?php echo $filter_bid_date_start; ?>" size="8"/></td>
              <td align="right"><input type="text" name="filter_bid_date_end" value="<?php echo $filter_bid_date_end; ?>" style="text-align: right;" /></td>
              <td><select name="filter_status">
                  <option value="*"></option>
                  <?php if ($filter_status) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <?php } ?>
                  <?php if (!is_null($filter_status) && !$filter_status) { ?>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select></td>
              <td align="right"><a onclick="filter();" class="button btn btn-primary"><?php echo $button_filter; ?></a></td>
            </tr>
            <?php if ($products) { ?>
            <?php foreach ($products as $product) { ?>
            <tr>
              <td style="text-align: center;"><?php if ($product['selected']) { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $product['product_id']; ?>" checked="checked" />
                <?php } else { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $product['product_id']; ?>" />
                <?php } ?></td>
              <td class="center"><img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" style="padding: 1px; border: 1px solid #DDDDDD;" /></td>
              <td class="left"><?php echo $product['name']; ?></td>
              <td class="left"><?php echo $product['bid_start_price']; ?></td>
              <td class="left"><?php echo $product['bid_date_start']; ?></td>
              <td class="left"><?php echo $product['bid_date_end']; ?></td>
              <td class="left"><?php echo $product['status']; ?></td>
              <td class="right"><?php foreach ($product['action'] as $action) { ?>
                [ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]
                <?php } ?></td>
            </tr>
            <?php } ?>
            <?php } else { ?>
            <tr>
              <td class="center" colspan="8"><?php echo $text_no_results; ?></td>
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
	url = 'index.php?route=catalog/auctionlist&token=<?php echo $token; ?>';
	
	var filter_name = $('input[name=\'filter_name\']').val();
	
	if (filter_name) {
		url += '&filter_name=' + encodeURIComponent(filter_name);
	}
	
	var filter_bid_start_price = $('input[name=\'filter_bid_start_price\']').val();
	
	if (filter_bid_start_price) {
		url += '&filter_bid_start_price=' + encodeURIComponent(filter_bid_start_price);
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
//--></script> 
<?php echo $footer; ?>