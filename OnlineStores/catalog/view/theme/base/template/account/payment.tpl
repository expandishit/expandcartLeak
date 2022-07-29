<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <p><?php echo $text_total; ?></p>
  <table class="list">
    <thead>
      <tr>
	     <td class="left"><?php echo $column_description; ?></td>
		 <td class="left"><?php echo $column_amount; ?></td>
        <td class="left"><?php echo $column_date_added; ?></td>
       <td class="left"><?php echo $column_status; ?></td>
	    <td class="left">Action</td>
        
      </tr>
    </thead>
    <tbody>
      <?php if ($payments) { ?>
      <?php foreach ($payments  as $payment) { ?>
      <tr>
	    <td class="left"><?php echo $payment['productname']; ?></td>
		<td class="left"><?php echo $payment['amount']; ?></td>
        <td class="left"><?php echo $payment['date_added']; ?></td>
       
        <td class="left"><?php echo $payment['status']; ?></td>
		<td class="left">
			<?php if($payment['status']!= 'Complete'){ ?>
				<input type="button" value="Add to cart" id="button-cart<?php echo $payment['winner_id']; ?>" class="button" />
			<?php } ?>
			</td>
      </tr>
	  
	  <tr>
	   <td class="product-info<?php echo $payment['winner_id']; ?>" style="display:none">
	  <input type="hidden" value="<?php echo $payment['product_id']; ?>" id="product_id" name="product_id" />
	  <input type="hidden" value="1" id="quantity" name="quantity" />
	  <input type="hidden" value="<?php echo $payment['winner_id']; ?>" id="winner_id" name="winner_id" />
	  </td>
	  </tr>
	  
	  <script>
  $('#button-cart<?php echo $payment['winner_id']; ?>').bind('click', function() {
	$.ajax({
		url: 'index.php?route=checkout/cart/add1',
		type: 'post',
		data: $('.product-info<?php echo $payment['winner_id']; ?> input[type=\'hidden\']'),
		dataType: 'json',
		success: function(json) {
			$('.success, .warning, .attention, information, .error').remove();
			
			if (json['error']) {
				if (json['error']['option']) {
					for (i in json['error']['option']) {
						$('#option-' + i).after('<span class="error">' + json['error']['option'][i] + '</span>');
					}
				}
			} 
			
			if (json['success']) {
				$('#notification').html('<div class="success" style="display: none;">' + json['success'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>');
					
				$('.success').fadeIn('slow');
					
				$('#cart-total').html(json['total']);

                if (json['enable_order_popup'] != '1')
                    $('html, body').animate({ scrollTop: 0 }, 'slow');

                if (json['enable_order_popup'] == '1') {
                    $('head').append("<style type='text/css'>.customAddtoCart .ui-dialog-titlebar.ui-widget-header.ui-corner-all.ui-helper-clearfix {display: none;} .customAddtoCart div#add-to-cart-dialog {min-height: 1px !important;} [dir=rtl] .ui-widget {font-family: 'Droid Arabic Kufi', 'droid_serifregular' !important;}</style>");

                    $('body').append('<div id="add-to-cart-dialog" style="display:none;"><div style="margin: 13px 0;">' + json['text_cart_dialog'] + '</div></div>');

                    $("#add-to-cart-dialog").dialog({
                        modal: true,
                        draggable: false,
                        resizable: false,
                        position: ['center', 'center'],
                        show: 'blind',
                        hide: 'blind',
                        width: 500,
                        dialogClass: 'ui-dialog-osx customAddtoCart',
                        buttons: [{
                            text: json['text_cart_dialog_continue'],
                            click: function() {
                                $(this).dialog("close");
                            }
                        },
                            {
                                text: json['text_cart_dialog_cart'],
                                click: function() {
                                    window.location.href = json['cart_link'];
                                }
                            }
                        ]
                    });
                }
			}	
		}
	});
});
</script>
	 
      <?php } ?>
      <?php } else { ?>
      <tr>
        <td class="center" colspan="5"><?php echo $text_empty; ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  
  <div class="pagination"><?php echo $pagination; ?></div>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?>