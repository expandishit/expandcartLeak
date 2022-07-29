<?php 
	$themeConfig = $this->config->get('themecontrol');
	$listConfig = array( 
		'cateogry_display_mode' => 'grid', 
		'cateogry_product_row'  => 4,
		'category_pzoom'        => 0
	); 

	$listConfig = array_merge( $listConfig, $themeConfig );

	$DISPLAY_MODE = $listConfig['cateogry_display_mode'];
	$MAX_ITEM_ROW = $listConfig['cateogry_product_row']? $listConfig['cateogry_product_row']:4; 
	$categoryPzoom = $listConfig['category_pzoom'];  
	$text_grid = '';$text_list = '';
	 
?>

<?php if ($products) { ?>
	<div class="wrapper nomargin nopadding">
		<!--Filter-->
		<div class="product-filter clearfix">
			<div class="filter pull-left">
				<div class="sort">
					<b><?php echo $text_sort; ?></b>
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
				<div class="display">					
					<ul class="clearfix">
						<li class="first"><b><?php echo $text_display; ?></b></li>
						<li><a onclick="display('list');" class="icon-list"><?php echo $text_list; ?></a></li>						
						<li class="last"><a onclick="display('grid');" class="icon-th-large"><?php echo $text_grid; ?></a></li>
					</ul>
				</div>	
				<div class="limit">
					<b><?php echo $text_limit; ?></b>
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
			</div>
			<div class="filter pull-right">
				<div class="product-compare">
					<a href="<?php echo $compare; ?>" id="compare-total" class="button bg-color"><?php echo $text_compare; ?></a>
				</div>
			</div>
		</div>

		<!--Product-->
		<div class="product-list"> 				
			<?php
			$cols = $MAX_ITEM_ROW ;
			$span = floor(12/$cols);
			foreach ($products as $i => $product) { $i=$i+1;?>
			<?php if( $i%$cols == 1 ) { ?>
			<div class="row-fluid box-product">
			<?php } ?>
				<div class="span<?php echo $span;?> product-block">					
					<div class="product-inner">
						<?php if ($product['thumb']) { ?>
						<div class="image bd-right">
							<?php if( $product['special'] ) {   ?>
							<div class="product-label-special label">
								<span><?php echo $this->language->get( 'text_sale' ); ?></span>
							</div>
							<?php } ?>
							
							<a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" /></a>
							<?php if( $categoryPzoom ) { $zimage = str_replace( "cache/","", preg_replace("#-\d+x\d+#", "",  $product['thumb'] ));  ?>
							<a href="<?php echo $zimage;?>" class="colorbox product-zoom" rel="colorbox" title="<?php echo $product['name']; ?>"><span class="icon-zoom-in"></span></a>
							<?php } ?>
						</div>
						<?php } ?>

						<h3 class="name is-over is-hover">
							<a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a>
						</h3>

						<p class="description is-over hiden"><?php echo $product['description']; ?></p>						

						<?php if ($product['price']) { ?>
						<div class="price is-hover is-over">
							<?php if (!$product['special']) { ?>
							<?php echo $product['price']; ?>
							<?php } else { ?>
							<span class="price-old"><?php echo $product['price']; ?></span> 
							<span class="price-new"><?php echo $product['special']; ?></span>
							<?php } ?>
							<?php if ($product['tax']) { ?>																
							<span class="price-tax"><?php echo $text_tax; ?> <?php echo $product['tax']; ?></span>
							<?php } ?>
						</div>
						<?php } ?>

						<?php if ($product['rating']) { ?>
						<div class="rating is-hover is-over">
							<img src="catalog/view/theme/<?php echo $themeName;?>/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" />
						</div>
						<?php } ?>

						<div class="pav-group-button is-over">
							<div class="cart is-hover">
								<input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
							</div>						
						</div>
						<div class="actions is-over">
							<div class="wishlist item-hover">
								<a class="pavicon-wishlist" onclick="addToWishList('<?php echo $product['product_id']; ?>');">
									<?php echo $button_wishlist; ?>
								</a>
							</div>
							<div class="compare item-hover">
								<a class="pavicon-compare" onclick="addToCompare('<?php echo $product['product_id']; ?>');">
									<?php echo $button_compare; ?>
								</a>
							</div>
						</div>	
						<div class="img-overlay hiden">&nbsp;</div>		
					</div>					
				</div>

			<?php if( $i%$cols == 0 || $i==count($products) ) { ?>
			</div>
			<?php } ?>	

			<?php } ?>
			
		</div>
		
		<!--Pagination-->
		<div class="pagination clearfix">			
			<aside class="noborder-top">
				<div class="product-compare pull-right">
					<a href="<?php echo $compare; ?>" id="compare-total" class="button bg-color"><?php echo $text_compare; ?></a>
				</div>
				<?php echo $pagination; ?>
			</aside>
		</div>			
	</div>	
		
	<?php } else { ?>			
	<div class="content wrapper">
		<?php echo $text_empty; ?>
	</div>	
	<?php } ?>

	
<script type="text/javascript">
<!--
function display(view) {
	if (view == 'list') {
		$('.product-grid').attr('class', 'product-list');
		
		$('.product-list .item').each(function(index, element) {
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
					
			html += '  <div class="name">' + $(element).find('.name').html() + '</div>';
			html += '  <div class="description">' + $(element).find('.description').html() + '</div>';
			
			var rating = $(element).find('.rating').html();
			
			if (rating != null) {
				html += '<div class="rating">' + rating + '</div>';
			}
				
			html += '</div>';
						
			$(element).html(html);
		});		
		
		$('.display .clear').html('<b><?php echo $text_display; ?></b> <?php echo $text_list; ?> <a class="icon-list" onclick="display(\'list\');"><?php echo $text_list; ?></a> <a class="icon-th-large" onclick="display(\'grid\');"><?php echo $text_grid; ?></a>');
		
		$.totalStorage('display', 'list'); 
		
	} else {
	
		$('.product-list').attr('class', 'product-grid');
		
		$('.product-grid .item').each(function(index, element) {
		
			html = '';
			
			var image = $(element).find('.image').html();
			
			if (image != null) {
				html += '<div class="image">' + image + '</div>';
			}
			
			html += '<div class="name">' + $(element).find('.name').html() + '</div>';
			html += '<div class="description">' + $(element).find('.description').html() + '</div>';
			
			var price = $(element).find('.price').html();
			
			if (price != null) {
				html += '<div class="price">' + price  + '</div>';
			}
			
			var rating = $(element).find('.rating').html();
			
			if (rating != null) {
				html += '<div class="rating">' + rating + '</div>';
			}
						
			html += '<div class="cart">' + $(element).find('.cart').html() + '</div>';
			html += '<div class="wishlist">' + $(element).find('.wishlist').html() + '</div>';
			html += '<div class="compare">' + $(element).find('.compare').html() + '</div>';
			
			$(element).html(html);
			
		});	
					
		$('.display .clear').html('<b><?php echo $text_display; ?></b> <a class="icon-list" onclick="display(\'list\');"><?php echo $text_list; ?></a> <a class="icon-th-large" onclick="display(\'grid\');"><?php echo $text_grid; ?></a> <?php echo $text_grid; ?>');
		
		$.totalStorage('display', 'grid');
	}
}

view = $.totalStorage('display');

if (view) {
	display(view);
} else {
	display('<?php echo $DISPLAY_MODE;?>');
}

//-->
</script> 


<?php if( $categoryPzoom ) {  ?>
<script type="text/javascript">
<!--
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
//-->
</script>
<?php } ?>