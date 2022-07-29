<?php 
	$span = 12/$cols; 
	$active = 'latest';
	$id = rand(1,9);	
	$themeName = $this->config->get('config_template') ;
?>
<div class="box productcarousel">
	<h3 class="box-heading">
		<span><?php echo $heading_title; ?></span>
	</h3>
	<div class="box-content nopadding">
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
					<div class="product-grid">			  
						<div class="item <?php if($k==0) {?>active<?php } ?>">
							<?php foreach( $tproducts as $i => $product ) {  $i=$i+1;?>
								<?php if( $i%$cols == 1 ) { ?>
								<div class="row-fluid box-product">
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
												<a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a>
											</div>
											<?php } ?>
										
											<h3 class="name is-hover"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></h3>
										
											<p class="description hiden">
												<?php echo utf8_substr( strip_tags($product['description']),0,58);?>...
											</p>
										
											<?php if ($product['price']) { ?>
											<div class="price is-hover">
												<?php if (!$product['special']) { ?>
												<?php echo $product['price']; ?>
												<?php } else { ?>
												<span class="price-old"><?php echo $product['price']; ?></span> 
												<span class="price-new"><?php echo $product['special']; ?></span>
												<?php } ?>
											</div>
											<?php } ?>
										
											<?php if ($product['rating']) { ?>
												<div class="rating is-hover">
													<img src="catalog/view/theme/<?php echo $themeName;?>/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" />
												</div>
											<?php } ?>																	
											
											
											<div class="pav-group-button clearfix">
												<div class="cart is-hover">
													<input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
												</div>										
											</div>	
											
											<div class="actions is-over">
												<div class="wishlist item-hover">				
													<a class="pavicon-wishlist" onclick="addToWishList('<?php echo $product['product_id']; ?>');"><?php echo $this->language->get("button_wishlist"); ?></a>
												</div>
												<div class="compare item-hover">												
													<a class="pavicon-compare" onclick="addToCompare('<?php echo $product['product_id']; ?>');"><?php echo $this->language->get("button_compare"); ?></a>
												</div>
											</div>	
											<div class="img-overlay hiden">&nbsp;</div>	
										</div>
									</div>
							  
								<?php if( $i%$cols == 0 || $i==count($tproducts) ) { ?>
								</div>
								<?php } ?>
							<?php } //endforeach; ?>
						</div>
					</div>					
				<?php } ?>
			</div>  
		</div>
	</div> 
 </div>


<script type="text/javascript">
$('#productcarousel<?php echo $id;?>').carousel({interval:<?php echo ( $auto_play_mode?$interval:'false') ;?>,auto:<?php echo $auto_play;?>,pause:'hover'});
</script>
