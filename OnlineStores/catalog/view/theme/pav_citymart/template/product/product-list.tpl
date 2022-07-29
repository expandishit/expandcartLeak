<?php 
	$themeConfig = $this->config->get('themecontrol');
	$listConfig = array( 
		'cateogry_display_mode' => 'grid', 
		'cateogry_product_row'  => 4,
		'category_pzoom'        => 1
	); 

	$listConfig = array_merge( $listConfig, $themeConfig );

	$DISPLAY_MODE = $listConfig['cateogry_display_mode'];
	$MAX_ITEM_ROW = $listConfig['cateogry_product_row']? $listConfig['cateogry_product_row']:4; 
	$categoryPzoom = $listConfig['category_pzoom'];

?>

<div class="product-list">
		<?php
		$cols = $MAX_ITEM_ROW ;
		$ispan = floor(12/$cols);
		foreach ($products as $i => $product) { $i=$i+1;?>
			<?php if( $i%$cols == 1 ) { ?>
				  <div class="row-fluid box-product">
			<?php } ?>
	<div class="myspan<?php echo $ispan;?> product_block <?php if($i%$cols == 0) { echo "last";} ?>"><div class="product-inner clearfix">
		<?php if ($product['thumb']) { ?>
		<div class="image">
			<?php if( $product['special'] ) {   ?>
			<div class="product-label-special label"><?php echo $this->language->get( 'text_sale' ); ?></div>
			<?php } ?>
			<a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" /></a>
			<?php if( $categoryPzoom ) { $zimage = str_replace( "cache/","", preg_replace("#-\d+x\d+#", "",  $product['thumb'] ));  ?>
			<a href="<?php echo $zimage;?>" class="colorbox product-zoom" rel="colorbox" title="<?php echo $product['name']; ?>"><span class="fa fa-search-plus"></span></a>
			<?php } ?>

		</div>
		<?php } ?>
			<div class="wrap-infor">
			  <div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
			  <div class="description"><?php echo utf8_substr( strip_tags($product['description']),0,200);?></div>
              <?php if(($this->config->get('config_buy_now')=="1" && $product['bidprice'] > 0) || $product['bidprice'] < 0 || $product['bidprice'] === null){ ?>
			  <?php if(!strpos($product['price'], '-1') && $product['price']) { ?>
			  <div class="price">
				<?php if (!$product['special']) { ?>
				<?php echo $product['price']; ?>
				<?php } else { ?>
				<span class="price-old"><?php echo $product['price']; ?></span> <span class="price-new"><?php echo $product['special']; ?></span>
				<?php } ?>
				<?php if ($product['tax']) { ?>
				<br />
				<span class="price-tax"><?php echo $text_tax; ?> <?php echo $product['tax']; ?></span>
				<?php } ?>
			  </div>
              <?php }?>
			  <?php } ?>
			  <?php if ($product['rating']) { ?>
			  <div class="rating"><img src="catalog/view/theme/default/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
			  <?php } ?>
				<div class="action">
					<div class="wishlist"><a onclick="addToWishList('<?php echo $product['product_id']; ?>');" title="<?php echo $button_wishlist; ?>" ><?php echo $button_wishlist; ?></a></div>
					<div class="compare"><a onclick="addToCompare('<?php echo $product['product_id']; ?>');" title="<?php echo $button_compare; ?>"><?php echo $button_compare; ?></a></div>
				</div>
				<div class="cart">
                    <?php $queryAuctionModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'special_count_down'");
                    if ($product['bidprice'] >= 0 && $queryAuctionModule->num_rows) { ?>

                    <a href="<?php echo $product['href']; ?>" class="button"><?php echo $text_bidnow; ?></a>



                    <?php }else{ ?>


					<?php if(!strpos($product['price'], '-1') && $product['price']) { ?>
					<div class="cart"><input type="button" value="<?php echo $button_cart; ?>" onclick="
			ms_addToCart
			('<?php echo $product['product_id']; ?>');" class="button" /></div>
					<?php } else { ?>
					<div class="cart">
						<div id="enquiry-<?php echo $product['product_id']; ?>"  style="display:none"><?php echo $product['name']; ?>:</div><input type="button" value="<?php echo $button_req_quote; ?>" onclick="contact_us('<?php echo $product['product_id']; ?>');" class="button" />
					</div>
					<?php } ?>

                    <?php } ?>
				</div>
			</div>
		</div>
	</div>
	 <?php if( $i%$cols == 0 || $i==count($products) ) { ?>
	 </div>
	 <?php } ?>
				
    <?php } ?>
 
</div>
  <?php 
	$text_list = '';
	$text_grid = '';
	
  ?>
<script type="text/javascript"><!--
function display(view) {
	if (view == 'list') {
		$('.product-grid').attr('class', 'product-list');
		
		
		$('.display').html('<b><?php echo $text_display; ?></b> <span class="fa fa-list"></span><?php echo $text_list; ?> <b>/</b> <a onclick="display(\'grid\');"> <span class="fa fa-th-list"></span><?php echo $text_grid; ?></a>');
		
		$.totalStorage('display', 'list'); 
	} else {
		$('.product-list').attr('class', 'product-grid');
		
		 
					
		$('.display').html('<b> <?php echo $text_display; ?></b> <a onclick="display(\'list\');"> <span class="fa fa-list"></span><?php echo $text_list; ?></a> <b>/</b> <span class="fa fa-th-list"></span> <?php echo $text_grid; ?>');
		
		$.totalStorage('display', 'grid');
	}
}

view = $.totalStorage('display');

if (view) {
	display(view);
} else {
	display('<?php echo $DISPLAY_MODE;?>');
}
//--></script>  
<?php if( $categoryPzoom ) {  ?>
<script type="text/javascript"><!--
$(document).ready(function() {
	$('.colorbox').colorbox({
		overlayClose: true,
		opacity: 0.5,
		rel: false,
		onLoad:function(){
			$("#cboxNext").remove(0);
			$("#cboxPrevious").remove(0);
			$("#cboxCurrent").remove(0);
		}
	});
	 
});
//--></script>
<?php } ?>
