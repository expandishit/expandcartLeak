  <?php if($clearstartbidprice < 0){?>
  
  <div class="cart">
			<div><?php echo $text_qty; ?>
			  <input type="text" name="quantity" size="2" value="<?php echo $minimum; ?>" />
			  <input type="hidden" name="product_id" size="2" value="<?php echo $product_id; ?>" />
			  &nbsp;
			  <?php if($config_buy_now!=0) {?>
			  <input type="button" value="<?php echo $button_cart; ?>" id="button-cart" class="button" />
			  <?php }else{ ?>
			  <input type="button" value="<?php echo $button_cart; ?>" disabled id="button-cart" class="button" />
			  <?php } ?>
			</div>
        <div><span>&nbsp;&nbsp;&nbsp;<?php echo $text_or; ?>&nbsp;&nbsp;&nbsp;</span></div>
        <div><a onclick="addToWishList('<?php echo $product_id; ?>');"><?php echo $button_wishlist; ?></a><br />
          <a onclick="addToCompare('<?php echo $product_id; ?>');"><?php echo $button_compare; ?></a></div>
			<?php if ($minimum > 1) { ?>
			<div class="minimum"><?php echo $text_minimum; ?></div>
			<?php } ?>
      </div>
	  
	 <?php } ?>
	  
	  
      <?php if ($review_status) { ?>
      <div class="review">
        <div><img src="catalog/view/theme/default/image/stars-<?php echo $rating; ?>.png" alt="<?php echo $reviews; ?>" />&nbsp;&nbsp;<a onclick="$('a[href=\'#tab-review\']').trigger('click');"><?php echo $reviews; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('a[href=\'#tab-review\']').trigger('click');"><?php echo $text_write; ?></a></div>
        <div class="share"><!-- AddThis Button BEGIN -->
          <div class="addthis_default_style"><a class="addthis_button_compact"><?php echo $text_share; ?></a> <a class="addthis_button_email"></a><a class="addthis_button_print"></a> <a class="addthis_button_facebook"></a> <a class="addthis_button_twitter"></a></div>
          <script type="text/javascript" src="//s7.addthis.com/js/250/addthis_widget.js"></script> 
          <!-- AddThis Button END --> 
        </div>
      </div>
      <?php } ?>