<?php 
	$cols = 3;
	$span = 12/$cols; 
?>

<div class="box pav-special">
	<h3 class="box-heading"><?php echo $heading_title; ?></h3>
	<section class="box-content">		
		<?php foreach ($products as $i => $product) { $i=$i+1; ?>	
		<?php if( $i%$cols == 1 ) { ?>
		<div class="row-fluid box-product">
			<?php } ?>
			<div class="span<?php echo $span;?> product-block">
				<div class="product-inner">
					<?php if ($product['thumb']) { ?>
						<div class="image">
							<a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a>
						</div>
					<?php } ?>
					
					<h3 class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></h3>				
					
					<?php if ($product['rating']) { ?>
						<div class="rating">
							<img src="catalog/view/theme/pav_asenti/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" />
						</div>
					<?php } ?>
					
					<?php if ($product['price']) { ?>
						<div class="price">
							<?php if (!$product['special']) { ?>
							<?php echo $product['price']; ?>
							<?php } else { ?>
							<span class="price-old"><?php echo $product['price']; ?></span> 
							<span class="price-new"><?php echo $product['special']; ?></span>
							<?php } ?>
						</div>
					<?php } ?>
					
					<div class="pav-group-button clearfix">
						<div class="cart pull-left">							
							<input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
						</div>
						<div class="wishlist pull-left">
							<a onclick="addToWishList('<?php echo $product['product_id']; ?>');"><?php echo $this->language->get("button_wishlist"); ?></a>
						</div>
						<div class="compare pull-left">													
							<a onclick="addToCompare('<?php echo $product['product_id']; ?>');"><?php echo $this->language->get("button_compare"); ?></a>
						</div>
					</div>
				</div>
			</div>

			<?php if( $i%$cols == 0 || $i==count($products) ) { ?>
		</div> 
		<?php } ?>  
		<?php } ?> 
	</section>
</div>
