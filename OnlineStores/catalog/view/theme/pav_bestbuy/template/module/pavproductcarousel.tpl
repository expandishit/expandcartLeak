<?php 
	$span = 12/$cols;
	if ($cols == 5)
		$span="2-5";
	$active = 'latest';
	$id = rand(1,9);	
?>
<div class="<?php echo $prefix;?> box productcarousel">
	<h3 class="box-heading"><span><?php echo $heading_title; ?></span></h3>
	<div class="box-content" >
 		<div class="box-products slide" id="productcarousel<?php echo $id;?>">
			<?php if( trim($message) ) { ?>
			<div class="box-description"><?php echo $message;?></div>
			<?php } ?>
			<?php if( count($products) > $itemsperpage ) { ?>
			<div class="carousel-controls">
			<a class="carousel-control left" href="#productcarousel<?php echo $id;?>"   data-slide="prev">&lsaquo;</a>
			<a class="carousel-control right" href="#productcarousel<?php echo $id;?>"  data-slide="next">&rsaquo;</a>
			</div>
			<?php } ?>
			<div class="carousel-inner ">		
			 <?php 
				$pages = array_chunk( $products, $itemsperpage);
			//	echo '<pre>'.print_r( $pages, 1 ); die;
			 ?>	
			  <?php foreach ($pages as  $k => $tproducts ) {   ?>
					<div class="item <?php if($k==0) {?>active<?php } ?>">
						<?php foreach( $tproducts as $i => $product ) { ?>
							<?php if( $i++%$cols == 0 ) { ?>
							  <div class="row-fluid box-product">
							<?php } ?>
								<div class="span<?php echo $span;?>">
								  	<div class="product-inner">
									<?php if ($product['thumb']) { ?>
									<div class="image">
									<?php if( $product['special'] ) {   ?>
									<?php
									 $price = preg_replace('#[^\d]+#', '', $product['price']);
									 $special = preg_replace('#[^\d]+#', '', $product['special']);
									 $discount = -round(100*($price - $special)/$price);
									// echo $price. " -- ".$special;die();
									?>
								    		<span class="product-label-special label"><?php echo $discount."%"; ?><?php //echo $this->language->get( 'text_sale' ); ?></span>
								    	<?php } ?>
									<a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a>
									</div>
									<?php } ?>
										<div class="product-meta">
												  
											<div class="name">
												<a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a>
											</div>

											<div class="description">
												<?php echo utf8_substr( strip_tags($product['description']),0,60);?>...
											</div>

											<div class="rating">
											<?php if ($product['rating']) { ?>
													<img src="catalog/view/theme/<?php echo $this->config->get('config_template');?>/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" />
											<?php } ?>
											</div>

											<?php if ($product['price']) { ?>
											<div class="price">
											  <?php if (!$product['special']) { ?>
											  <?php echo $product['price']; ?>
											  <?php } else { ?>
											  <span class="price-old"><?php echo $product['price']; ?></span> <span class="price-new"><?php echo $product['special']; ?></span>
											  <?php } ?>
											</div>
											<?php } ?>
											<div class="group-action">

												<div class="cart">
													<input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
												</div>
											    <div class="wishlist"><a onclick="addToWishList('<?php echo $product['product_id']; ?>');" title="<?php echo $this->language->get("button_wishlist"); ?>" ><?php echo $this->language->get("button_wishlist"); ?></a></div>
											  <div class="compare"><a onclick="addToCompare('<?php echo $product['product_id']; ?>');" title="<?php echo $this->language->get("button_compare"); ?>" ><?php echo $this->language->get("button_compare"); ?></a></div>
											</div>
										</div>

									</div>
								</div>	  
							  <?php if( $i%$cols == 0 || $i==count($tproducts) ) { ?>
								 </div>
								<?php } ?>
									<?php } //endforeach; ?>
								</div>
						  <?php } ?>
					</div>
				</div> 

		</div>
</div>


<script type="text/javascript">
$('#productcarousel<?php echo $id;?>').carousel({interval:<?php echo ( $auto_play_mode?$interval:'false') ;?>,auto:<?php echo $auto_play;?>,pause:'hover'});
</script>
