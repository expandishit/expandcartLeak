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
		
		<a onclick="$('form').submit();" class="button btn btn-primary"><?php echo $button_delete; ?></a></div>
    </div>
      
    </div>
    <div class="content">
      <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="table table-hover dataTable no-footer">
          <thead>
            <tr>
              <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>
             
              	<td class="left"><?php if ($sort == 'b.ip_address') { ?>
                <a href="<?php echo $sort_ip; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_names; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_ip; ?>"><?php echo $column_names; ?></a>
                <?php } ?></td>
            
              <td class="left"><?php if ($sort == 'b.date_added') { ?>
                <a href="<?php echo $sort_date; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_bid_date_end; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_date; ?>"><?php echo $column_bid_date_end; ?></a>
                <?php } ?></td>
						
				 <td class="left"><?php if ($sort == 'b.status') { ?>
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
			  
              <td><input type="text" name="filter_name" value="<?php echo $filter_ip; ?>" /></td>
              <td><input type="text" name="filter_names" value="<?php echo $filter_date; ?>" /></td>
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
            <?php if (!empty($ipsresult)) { ?>
            <?php foreach ($ipsresult as $ipblock) { ?>
            <tr>
			  <td style="text-align: center;"><?php if ($ipblock['selected']) { ?>

                <input type="checkbox" name="selected[]" value="<?php echo $ipblock['ipblock_id']; ?>" checked="checked" />

                <?php } else { ?>

                <input type="checkbox" name="selected[]" value="<?php echo $ipblock['ipblock_id']; ?>" />

                <?php } ?></td>

               <td class="left"><?php echo $ipblock['name']; ?></td>			   
               <td class="left"><?php echo $ipblock['dated_added']; ?></td>
			   <td class="left"><?php echo $ipblock['status']; ?></td>
			  
			  <td class="right"><?php foreach ($ipblock['action'] as $action) { ?>

                [ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]

                <?php } ?></td>
              
			 
             
              
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
	url = 'index.php?route=catalog/ipblock&token=<?php echo $token; ?>';
	
	var filter_ip = $('input[name=\'filter_ip\']').val();
	
	if (filter_ip) {
		url += '&filter_ip=' + encodeURIComponent(filter_ip);
	}
	
	var filter_date = $('input[name=\'filter_date\']').val();
	
	if (filter_date) {
		url += '&filter_date=' + encodeURIComponent(filter_date);
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