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

    <!--  <div class="buttons"><a onclick="$('#form').attr('action', '<?php echo $delete; ?>'); $('#form').attr('target', '_self'); $('#form').submit();" class="button btn btn-primary"><?php echo $button_delete; ?></a></div>--->

    </div>

    <div class="content">

      <form action="" method="post" enctype="multipart/form-data" id="form">

        <table class="table table-hover dataTable no-footer">

          <thead>

            <tr>

              <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>

            

              <td class="left"><?php if ($sort == 'customer') { ?>

                <a href="<?php echo $sort_customer; ?>" class="<?php echo strtolower($auctionpayment); ?>"><?php echo $column_customer; ?></a>

                <?php } else { ?>

                <a href="<?php echo $sort_customer; ?>"><?php echo $column_customer; ?></a>

                <?php } ?></td>
				
				
				 <td class="left"><?php if ($sort == 'w.productname') { ?>

                <a href="<?php echo $sort_pname; ?>" class="<?php echo strtolower($auctionpayment); ?>"><?php echo $column_product; ?></a>

                <?php } else { ?>

                <a href="<?php echo $sort_pname; ?>"><?php echo $column_product; ?></a>

                <?php } ?></td>
				
				
				
				

              <td class="left"><?php echo $column_status; ?>

                </td>
				

              <td class="right"><?php if ($sort == 'w.price_bid') { ?>

                <a href="<?php echo $sort_total; ?>" class="<?php echo strtolower($auctionpayment); ?>"><?php echo $column_total; ?></a>

                <?php } else { ?>

                <a href="<?php echo $sort_total; ?>"><?php echo $column_total; ?></a>

                <?php } ?></td>
				

              <td class="left"><?php if ($sort == 'w.date_added') { ?>

                <a href="<?php echo $sort_date_added; ?>" class="<?php echo strtolower($auctionpayment); ?>"><?php echo $column_date_added; ?></a>

                <?php } else { ?>

                <a href="<?php echo $sort_date_added; ?>"><?php echo $column_date_added; ?></a>

                <?php } ?></td>

             

              <td class="right"><?php echo $column_action; ?></td>

            </tr>

          </thead>

          <tbody>

            <tr class="filter">

              <td></td>

             

              <td><input type="text" name="filter_customer" value="<?php echo $filter_customer; ?>" /></td>
			  
			   <td><input type="text" name="filter_pname" value="<?php echo $filter_pname; ?>" /></td>

              <td><select name="filter_auctionpayment_status_id">

                  <option value="*"></option>

                  <?php if ($filter_auctionpayment_status_id == '0') { ?>

                  <option value="0" selected="selected"><?php echo $text_missing; ?></option>

                  <?php } else { ?>

                  <option value="0"><?php echo $text_missing; ?></option>

                  <?php } ?>

                  <?php foreach ($auctionpayment_statuses as $auctionpayment_status) { ?>

                  <?php if ($auctionpayment_status['auctionpayment_status_id'] == $filter_auctionpayment_status_id) { ?>

                  <option value="<?php echo $auctionpayment_status['auctionpayment_status_id']; ?>" selected="selected"><?php echo $auctionpayment_status['name']; ?></option>

                  <?php } else { ?>

                  <option value="<?php echo $auctionpayment_status['auctionpayment_status_id']; ?>"><?php echo $auctionpayment_status['name']; ?></option>

                  <?php } ?>

                  <?php } ?>

                </select></td>

              <td align="right"><input type="text" name="filter_total" value="<?php echo $filter_total; ?>" size="4" style="text-align: right;" /></td>

              <td><input type="text" name="filter_date_added" value="<?php echo $filter_date_added; ?>" size="12" class="date" /></td>

          

              <td align="right"><a onclick="filter();" class="button btn btn-primary"><?php echo $button_filter; ?></a></td>

            </tr>

            <?php if ($auctionpayments) { ?>

            <?php foreach ($auctionpayments as $auctionpayment) { ?>

            <tr>

              <td style="text-align: center;"><?php if ($auctionpayment['selected']) { ?>

                <input type="checkbox" name="selected[]" value="<?php echo $auctionpayment['auctionpayment_id']; ?>" checked="checked" />

                <?php } else { ?>

                <input type="checkbox" name="selected[]" value="<?php echo $auctionpayment['auctionpayment_id']; ?>" />

                <?php } ?></td>

             

              <td class="left"><?php echo $auctionpayment['customer']; ?></td>
			  
			   <td class="left"><?php echo $auctionpayment['pname']; ?></td>

              <td class="left"><?php echo $auctionpayment['status']; ?></td>

              <td class="right"><?php echo $auctionpayment['total']; ?></td>

              <td class="left"><?php echo $auctionpayment['date_added']; ?></td>

            
             <td></td>

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

	url = 'index.php?route=sale/auctionpayment&token=<?php echo $token; ?>';

	

	var filter_auctionpayment_id = $('input[name=\'filter_auctionpayment_id\']').val();

	

	if (filter_auctionpayment_id) {

		url += '&filter_auctionpayment_id=' + encodeURIComponent(filter_auctionpayment_id);

	}

	

	var filter_customer = $('input[name=\'filter_customer\']').val();

	

	if (filter_customer) {

		url += '&filter_customer=' + encodeURIComponent(filter_customer);

	}

	

	var filter_auctionpayment_status_id = $('select[name=\'filter_auctionpayment_status_id\']').val();

	

	if (filter_auctionpayment_status_id != '*') {

		url += '&filter_auctionpayment_status_id=' + encodeURIComponent(filter_auctionpayment_status_id);

	}	



	var filter_total = $('input[name=\'filter_total\']').val();



	if (filter_total) {

		url += '&filter_total=' + encodeURIComponent(filter_total);

	}	

	

	var filter_date_added = $('input[name=\'filter_date_added\']').val();

	

	if (filter_date_added) {

		url += '&filter_date_added=' + encodeURIComponent(filter_date_added);

	}

	

	var filter_date_modified = $('input[name=\'filter_date_modified\']').val();

	

	if (filter_date_modified) {

		url += '&filter_date_modified=' + encodeURIComponent(filter_date_modified);

	}

				

	location = url;

}

//--></script>  

<script type="text/javascript"><!--

$(document).ready(function() {

	$('.date').datepicker({dateFormat: 'yy-mm-dd'});

});

//--></script> 

<script type="text/javascript"><!--

$('#form input').keydown(function(e) {

	if (e.keyCode == 13) {

		filter();

	}

});

//--></script> 

<script type="text/javascript"><!--

$.widget('custom.catcomplete', $.ui.autocomplete, {

	_renderMenu: function(ul, items) {

		var self = this, currentCategory = '';

		

		$.each(items, function(index, item) {

			if (item.category != currentCategory) {

				ul.append('<li class="ui-autocomplete-category">' + item.category + '</li>');

				

				currentCategory = item.category;

			}

			

			self._renderItem(ul, item);

		});

	}

});



$('input[name=\'filter_customer\']').catcomplete({

	delay: 0,

	source: function(request, response) {

		$.ajax({

			url: 'index.php?route=sale/customer/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),

			dataType: 'json',

			success: function(json) {		

				response($.map(json, function(item) {

					return {

						category: item.customer_group,

						label: item.name,

						value: item.customer_id

					}

				}));

			}

		});

	}, 

	select: function(event, ui) {

		$('input[name=\'filter_customer\']').val(ui.item.label);

						

		return false;

	},

	focus: function(event, ui) {

      	return false;

   	}

});

//--></script> 

<?php echo $footer; ?>