<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" ); 
	$themeConfig = $this->config->get('themecontrol');
	 
	$DISPLAY_MODE = 'grid';
	if( isset($themeConfig['cateogry_display_mode']) ){
		$DISPLAY_MODE = $themeConfig['cateogry_display_mode'];
	}
	$MAX_ITEM_ROW = 3; 
	if( isset($themeConfig['cateogry_product_row']) && $themeConfig['cateogry_product_row'] ){
		$MAX_ITEM_ROW = $themeConfig['cateogry_product_row'];
	}
	$categoryPzoom = isset($themeConfig['category_pzoom']) ? $themeConfig['category_pzoom']:0; 

?>
<?php echo $header; ?>
<div id="breadcrumb">
	<div class="breadcrumb">
	<?php foreach ($breadcrumbs as $breadcrumb) { ?>
	<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
	<?php } ?>
	</div>	
</div>
<div id="group-content">
<?php if( $SPAN[0] ): ?>
	<div class="span<?php echo $SPAN[0];?>">
		<?php echo $column_left; ?>
	</div>
	
<?php endif; ?> 
<div class="span<?php echo $SPAN[1];?>">
<div id="content" class="special"><?php echo $content_top; ?>
  <h1><?php echo $heading_title; ?></h1>
  <?php if ($products) { ?>
  <div class="product-filter">
    <div class="display">
		<span><?php echo $text_display; ?></span>
		<span><?php echo $text_list; ?></span>
		<a onclick="display('grid');"><?php echo $text_grid; ?></a>
	
	</div>
     <div class="sort"><span><?php echo $text_sort; ?></span>
					  <select onchange="location = this.value;">
						<?php foreach ($sorts as $sorts) { ?>
						<?php if ($sorts['value'] == $sort . '-' . $order) { ?>
						<option value="<?php echo $sorts['href']; ?>" selected="selected"><?php echo $sorts['text']; ?></option>
						<?php } else { ?>
						<option value="<?php echo $sorts['href']; ?>"><?php echo $sorts['text']; ?></option>
						<?php } ?>
						<?php } ?>
					  </select>
					</div>
		
	<div class="limit"><span><?php echo $text_limit; ?></span>
						<select onchange="location = this.value;">
							<?php foreach ($limits as $limits) { ?>
							<?php if ($limits['value'] == $limit) { ?>
							<option value="<?php echo $limits['href']; ?>" selected="selected"><?php echo $limits['text']; ?></option>
							<?php } else { ?>
							<option value="<?php echo $limits['href']; ?>"><?php echo $limits['text']; ?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>
   <div class="product-compare"><a href="<?php echo $compare; ?>" id="compare-total"><?php echo $text_compare; ?></a></div>
  </div>
   
  
<div class="product-list">
	<?php
	$cols = $MAX_ITEM_ROW ;
		$span = floor(12/$cols);
		foreach ($products as $i => $product) { ;?>
		<?php if( $i++%$cols == 0 ) { ?>
		<div class="row-fluid">
			<?php } ?>
    <div class="span<?php echo $span;?> product-block">
    	<div class="product-inner">
      <?php if ($product['thumb']) { ?>
      <div class="image">
      	<?php if( $product['special'] ) {   ?>
    	<div class="product-label-special label">
			<span><?php echo $this->language->get( 'text_sale' ); ?></span>
		</div>
    	<?php } ?>
		<a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" /></a>
		<?php if( $categoryPzoom ) { $zimage = str_replace( "cache/","", preg_replace("#-\d+x\d+#", "",  $product['thumb'] ));  ?>
		<a href="<?php echo $zimage;?>" class="hidden-tablet hidden-phone colorbox product-zoom" rel="colorbox" title="<?php echo $product['name']; ?>"><span class="fa fa-search-plus"></span></a>
		<?php } ?>
		
		<?php if( $categoryPzoom ) { $zimage = str_replace( "cache/","", preg_replace("#-\d+x\d+#", "",  $product['thumb'] ));  ?>
      	<?php } ?>
      	<div class="group-action">
			<div class="btn-overlay cart">
				<input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />									
			</div>
			<div class="btn-overlay wishlist">				
				<a class="pavicon-wishlist" onclick="addToWishList('<?php echo $product['product_id']; ?>');"><?php echo $this->language->get("button_wishlist"); ?></a>
			</div>
			<div class="btn-overlay compare">												
				<a class="pavicon-compare" onclick="addToCompare('<?php echo $product['product_id']; ?>');"><?php echo $this->language->get("button_compare"); ?></a>
			</div>
		</div>

		</div>
      <?php } ?>

      <h4 class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></h4> 
      <div class="description"><?php echo utf8_substr( strip_tags($product['description']),0,85);?>...</div>

		<?php if ($product['rating']) { ?>
		<div class="rating is-hover">
			<img src="catalog/view/theme/<?php echo $this->config->get('config_template');?>/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" />
		</div>
		<?php } ?>
		<?php if ($product['price']) { ?>
		<div class="price">
			<?php if (!$product['special']) { ?>
			<?php echo $product['price']; ?>
			<?php } else { ?>
			<span class="price-old"><?php echo $product['price']; ?></span> <span class="price-new"><?php echo $product['special']; ?></span>
			<?php } ?>
		</div>
		<?php } ?>

      </div>
    </div>
	 <?php if( $i%$cols == 0 || $i==count($products) ) { ?>
	 </div>
	 <?php } ?>
				
    <?php } ?>
  </div>
 <div class="product-filter">
    <div class="pagination"><?php echo $pagination; ?></div>
  </div>
 
  <?php } ?>
  
  <?php echo $content_bottom; ?></div>
<script type="text/javascript"><!--
function display(view) {
	if (view == 'list') {
		$('.product-grid').attr('class', 'product-list');
		
		$('.product-list div.product_block').each(function(index, element) {
			html  = '<div class="right">';
			html += '  <div class="cart">' + $(element).find('.cart').html() + '</div>';
			html += '  <div class="wishlist">' + $(element).find('.wishlist').html() + '</div>';
			html += '  <div class="compare">' + $(element).find('.compare').html() + '</div>';
			html += '</div>';			
			
			html += '<div class="left">';
			
			var image = $(element).find('.image').html();
			
			if (image != null) { 
				html += '<div class="image">' + image + '</div>';
			}
			
			var price = $(element).find('.price').html();
			
			if (price != null) {
				html += '<div class="price">' + price  + '</div>';
			}
					
					var rating = $(element).find('.rating').html();			
					if (rating != null) {
						html += '<div class="rating">' + rating + '</div>';
					}	
					
					html += '  <div class="description"><p>' + $(element).find('.description').html() + '</p></div>';
					
					html += '  <div class="pav-action clearfix"><div class="cart">' + $(element).find('.cart').html() + '</div>';
					html += '  <div class="wishlist">' + $(element).find('.wishlist').html() + '</div>';
					html += '  <div class="compare">' + $(element).find('.compare').html() + '</div></div>';				
					html += '</div></div></div>';
					
					$(element).html(html);
				});		

		$('.display').html('<span style="float: left;"><?php echo $text_display; ?></span><a class="list active"><?php echo $text_list; ?></a><a class="grid"  onclick="display(\'grid\');"><?php echo $text_grid; ?></a>');
	
$.totalStorage('display', 'list'); 
} else {
	$('.product-list').attr('class', 'product-grid');
	
	$('.product-grid div.product_block').each(function(index, element) {
		html = '';
		
		var image = $(element).find('.image').html();
		
		if (image != null) {
			html += '<div class="pav-product-grid"><div class="image">' + image + '</div>';
		}
		
		html += '<h3 class="name">' + $(element).find('.name').html() + '</h3>';
		html += '<div class="description">' + $(element).find('.description').html() + '</div>';
		
		var price = $(element).find('.price').html();
		
		if (price != null) {
			html += '<div class="price">' + price  + '</div>';
		}
		
		var rating = $(element).find('.rating').html();
		
		if (rating != null) {
			html += '<div class="rating">' + rating + '</div>';
		}
		
		html += '<div class="pav-action clearfix"><div class="cart">' + $(element).find('.cart').html() + '</div>';
		html += '<div class="wishlist">' + $(element).find('.wishlist').html() + '</div>';
		html += '<div class="compare">' + $(element).find('.compare').html() + '</div></div></div>';
		
		$(element).html(html);
	});	
	
		$('.display').html('<span style="float: left;"><?php echo $text_display; ?></span><a class="list" onclick="display(\'list\');"><?php echo $text_list; ?></a><a class="grid active"><?php echo $text_grid; ?></a>');
	
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
			$("#cboxPrenvious").remove(0);
			$("#cboxCurret").remove(0);
		}
	});
	 
});
//--></script>
<?php } ?> 
  
  
  </div>
</div> 
<?php if( $SPAN[2] ): ?>
<div class="span<?php echo $SPAN[2];?>">	
	<?php echo $column_right; ?>
</div>
<?php endif; ?>
</div>
<?php echo $footer; ?>