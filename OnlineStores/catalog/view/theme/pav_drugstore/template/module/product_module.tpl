<?php 
$cols = 1;
$span = $cols <= 1 ? 'mode-list' : 'span'.floor(12/$cols); 
$themeName = $this->config->get('config_template') ;
?>

<div class="product-grid">		
	<?php foreach ($products as $i => $product) {   $i=$i+1; ?>
	<?php if( $i%$cols == 1 && $cols > 1 ) { ?>
	<div class="row-fluid box-product">		
	<?php } ?>
		<div class="product-block <?php echo $span;?>">
			<div class="product-inner">			
				<?php if ($product['thumb']) { ?>
				<div class="image">
					<a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a>
				</div>
				<?php } ?>	
				
				<div class="product-bottom is-over">									
					<h3 class="name">
						<a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a>
					</h3>
					
					<?php if( isset($product['description']) ){	?>
					<p class="description hiden">
						<?php echo utf8_substr(strip_tags( $product['description']),0,120); ?> ...
					</p>
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

					<?php if ($product['rating']) { ?>
					<div class="rating">
						<img src="catalog/view/theme/<?php echo $themeName;?>/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" />
					</div>
					<?php } ?>

					<div class="pav-group-button hiden">
						<div class="cart">							
							<input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
						</div>
					</div>		
					
					<div class="actions hiden is-over">
						<div class="wishlist item-hover">				
							<a class="i-product" onclick="addToWishList('<?php echo $product['product_id']; ?>');"><?php echo $this->language->get("button_wishlist"); ?></a>
						</div>
						<div class="compare item-hover">												
							<a class="i-product" onclick="addToCompare('<?php echo $product['product_id']; ?>');"><?php echo $this->language->get("button_compare"); ?></a>
						</div>
					</div>	
					<div class="img-overlay hiden">&nbsp;</div>			
								
				</div>	
			</div>
		</div>
	<?php if( ($i%$cols == 0 || $i==count($products)) && $cols > 1 ) { ?>
	</div>
	<?php } ?>

	<?php } ?>
</div>