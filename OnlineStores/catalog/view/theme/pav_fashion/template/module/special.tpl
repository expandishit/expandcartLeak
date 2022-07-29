<?php 
	$categoryConfig = array( 
		'show_swap_image'					 => 0,
	); 
	$cols = 3;
	$span = 12/$cols;
?>
<div class="box box-product special">
	<div class="box-heading"><span><?php echo $heading_title; ?></span></div>  	
	<div class="box-content">
		<div class="product-grid">
			<?php foreach ($products as $i => $product) { $i=$i+1; ?>
			<?php if( $i%$cols == 1 && $cols > 1 ) { ?>
			<div class="row">
			<?php } ?> 
				<div class="col-lg-<?php echo $span;?> col-sm-<?php echo $span;?> col-xs-12">
					<div class="product-block">
						<?php if ($product['thumb']) { ?>
	      <div class="image"><?php if( $product['special'] ) {   ?>
	    	<span class="product-label-special label"><span class="special"><?php echo $this->language->get( 'text_sale' ); ?></span></span>
	    	<?php } ?>
								<a class="img" href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" class="img-responsive" /></a>
								<?php 
								if( $categoryConfig['show_swap_image'] ){
								$product_images = $this->model_catalog_product->getProductImages( $product['product_id'] );
								if(isset($product_images) && !empty($product_images)) {
								$thumb2 = $this->model_tool_image->resize($product_images[0]['image'],  $this->config->get('config_image_product_width'),  $this->config->get('config_image_product_height') );
								?>	
								<span class="hover-image">
								<img src="<?php echo $thumb2; ?>" alt="<?php echo $product['name']; ?>">
								</span>
								<?php } } ?>
							</div>
						<?php } ?>
						
						<div class="product-meta">
<div class="warp-info">
							<h3 class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></h3>
							<?php if ($product['rating']) { ?>
							<div class="rating">
								<img src="catalog/view/theme/<?php echo $this->config->get('config_template');?>/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" />
							</div>
							<?php } ?>
							<?php if( isset($product['description']) ) { ?>
							<p class="description"><?php echo utf8_substr( strip_tags($product['description']),0,160);?>...</p>
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
							<div class="product-action">
								<div class="cart">
									<i class=" fa fa-shopping-cart"></i>
		        <input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
								</div>
		      <div class="wishlist-compare">

			    <a class="wishlist fa fa-heart" onclick="addToWishList('<?php echo $product['product_id']; ?>');"  data-placement="top" data-toggle="tooltip" data-original-title="<?php echo  $this->language->get("button_wishlist"); ?>"><span><?php echo $this->language->get("button_wishlist"); ?></span></a>
			    <a class="compare fa fa-retweet" onclick="addToCompare('<?php echo $product['product_id']; ?>');"  data-placement="top" data-toggle="tooltip" data-original-title="<?php echo$this->language->get("button_compare"); ?>"><span><?php echo $this->language->get("button_compare"); ?></span></a>
			    
								</div>
							</div>
						</div>		
					</div>
				</div>
			  
			<?php if( ($i%$cols == 0 || $i==count($products) ) && $cols > 1 ) { ?>
			</div>
			<?php } ?>			
			<?php } ?>
		</div>
	</div>	
	
</div>
