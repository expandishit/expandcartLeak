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
	  	 <div class="buttons">
		<a onclick="$('form').submit();" class="button btn btn-primary"><?php echo $button_delete; ?></a></div>
    </div>
      
    </div>
    <div class="content">
      <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="table table-hover dataTable no-footer">
          <thead>
            <tr>
              <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>
              	
				
				<td class="left"><?php if ($sort == 'c.firstname') { ?>
                <a href="<?php echo $sort_firstname; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_firstname; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_firstname; ?>"><?php echo $column_firstname; ?></a>
                <?php } ?></td>
				
				<td class="left"><?php if ($sort == 'c.lastname') { ?>
                <a href="<?php echo $sort_lastname; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_lastname; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_firstname; ?>"><?php echo $column_lastname; ?></a>
                <?php } ?></td>
				
				<td class="left"><?php if ($sort == 'c.email') { ?>
                <a href="<?php echo $sort_email; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_email; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_email; ?>"><?php echo $column_email; ?></a>
                <?php } ?></td>
            
                <td class="left"><?php if ($sort == 'b.date_added') { ?>
                <a href="<?php echo $sort_date; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_bid_date_end; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_date; ?>"><?php echo $column_bid_date_end; ?></a>
                <?php } ?></td>
				
				<td class="left"><?php if ($sort == 'c.ip_address') { ?>
                <a href="<?php echo $sort_ip; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_names; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_ip; ?>"><?php echo $column_names; ?></a>
                <?php } ?></td>
						
						
				<td class="left"><a href="<?php echo $sort_bids; ?>"><?php echo $column_bids; ?></a></td>
				
				<td class="left"><?php echo $column_status; ?></td>
				 
				
				<td class="right"><?php echo $column_action; ?></td>
				
            </tr>
          </thead>
          <tbody>
            <tr class="filter">
              <td></td>
			  
              <td><input type="text" name="filter_firstname" value="<?php echo $filter_firstname; ?>" /></td>
			  <td><input type="text" name="filter_lastname" value="<?php echo $filter_lastname; ?>" /></td>
              <td><input type="text" name="filter_email" value="<?php echo $filter_email; ?>" /></td>
			   <td><input type="text" name="filter_date" value="<?php echo $filter_date; ?>" /></td>
			  <td><input type="text" name="filter_ip" value="<?php echo $filter_ip; ?>" /></td>
			  <td></td>
			  <td></td>
              
              <td align="right"><a onclick="filter();" class="button btn btn-primary"><?php echo $button_filter; ?></a></td>
            </tr>
            <?php if (!empty($blacklist)) { ?>
            <?php foreach ($blacklist as $ipblock) { ?>
            <tr>
			  <td style="text-align: center;"><?php if ($ipblock['selected']) { ?>

                <input type="checkbox" name="selected[]" value="<?php echo $ipblock['ban_id']; ?>" checked="checked" />

                <?php } else { ?>

                <input type="checkbox" name="selected[]" value="<?php echo $ipblock['ban_id']; ?>" />

                <?php } ?></td>
				<td class="left"><?php echo $ipblock['firstname']; ?></td>
				<td class="left"><?php echo $ipblock['lastname']; ?></td>
               <td class="left"><?php echo $ipblock['email']; ?></td>			   
               <td class="left"><?php echo $ipblock['dated_added']; ?></td>
			   <td class="left"><?php echo $ipblock['ip_address']; ?></td>
			   
			    <td class="left"><?php echo $ipblock['bids']; ?></td>
				 <td class="left"><?php echo $ipblock['status']; ?></td>
			   
			  
			  <td class="right"></td>
              
			 
             
              
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
	url = 'index.php?route=catalog/blacklist&token=<?php echo $token; ?>';
	
	var filter_ip = $('input[name=\'filter_ip\']').val();
	
	if (filter_ip) {
		url += '&filter_ip=' + encodeURIComponent(filter_ip);
	}
	
	var filter_firstname = $('input[name=\'filter_firstname\']').val();
	
	if (filter_firstname) {
		url += '&filter_firstname=' + encodeURIComponent(filter_firstname);
	}
	
	var filter_lastname = $('input[name=\'filter_lastname\']').val();
	
	if (filter_lastname) {
		url += '&filter_lastname=' + encodeURIComponent(filter_lastname);
	}
	
	var filter_email = $('input[name=\'filter_email\']').val();
	
	if (filter_email) {
		url += '&filter_email=' + encodeURIComponent(filter_email);
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