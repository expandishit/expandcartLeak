<?php 
	$cols = 4;
	$span = 12/$cols;
?>
<div class="box box-product latest">
  <div class="box-heading"><span><?php echo $heading_title; ?></span></div>
  <div class="box-content">
    <div class="box-product" >
			  <?php foreach ($products as $i => $product) { ?>
				 <?php if( $i++%$cols == 0 ) { ?>
				  <div class="row box-product">
							<?php } ?>
								  <div class=" col-lg-<?php echo $span;?>">
								  	<div class="product-block">	
								      <?php if ($product['thumb']) { ?>
								      <div class="image">
								    	<a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" /></a>
								      </div>
								      <?php } ?>
								      <div class="product-meta">
								      	<div class="warp-info">
										      <h3 class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></h3>
													<?php if (isset($product['description']))  { ?><div class="description">
													<?php echo utf8_substr( strip_tags($product['description']),0,60);?>...
												</div>
												<?php } ?>
											  <?php if ($product['rating']) { ?>
										      <div class="rating"><img src="catalog/view/theme/<?php echo $this->config->get('config_template');?>/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
										      <?php } ?>
										      <div class="price-cart">
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
										<div class="group-item"> 				     
										   	<div class="cart">
									      	<i class="fa fa-shopping-cart"></i>
									        <input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
										    </div>
										    <div class="wishlist-compare">
											  <a class="wishlist fa fa-heart" onclick="addToWishList('<?php echo $product['product_id']; ?>');" title="<?php echo $this->language->get("button_wishlist"); ?>" ><span><?php echo $this->language->get("button_wishlist"); ?></span></a>
											  <a class="compare fa fa-retweet"  onclick="addToCompare('<?php echo $product['product_id']; ?>');" title="<?php echo $this->language->get("button_compare"); ?>" ><span><?php echo $this->language->get("button_compare"); ?></span></a>
											</div>
										</div>

									 </div>
	    
									</div>
								</div>
						  
						   <?php if( $i%$cols == 0 || $i==count($products) ) { ?>
							 </div>
				<?php } ?>
			
			  <?php } ?>

    </div>
  </div>
   </div>
