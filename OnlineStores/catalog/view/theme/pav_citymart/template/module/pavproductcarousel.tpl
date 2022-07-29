<?php 
	$span = 12/$cols; 
	$active = 'latest';
	$id = rand(1,9)+substr(md5($heading_title),0,3);
?>
<div class="<?php echo $prefix;?> box productcarousel" id="module<?php echo $id; ?>">
	<div class="box-heading"><h2><?php echo $heading_title; ?></h2></div>
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
						<?php foreach( $tproducts as $i => $product ) {  $i=$i+1;?>
							<?php if( $i%$cols == 1 ) { ?>
							  <div class="row-fluid box-product <?php ;if($i == count($tproducts) - $cols +1) { echo "last";} ?>">
							<?php } ?>
								  <div class="myspan<?php echo $span;?> product_block <?php if($i%$cols == 0) { echo "last";} ?>">
								  
								  <div class="product-inner">
									<?php if ($product['thumb']) { ?>
									<div class="image">
										<?php if( $product['special'] ) {   ?>
										<div class="product-label-special label">
											<?php echo $this->language->get( 'text_sale' ); ?>
										</div>
										<?php } ?>
										<a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a></div>
									<?php } ?>
									<div class="wrap-infor">
										<div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
										<div class="description">
											<?php echo utf8_substr( strip_tags($product['description']),0,58);?>...
										</div>
                                        <?php if(($this->config->get('config_buy_now')=="1" && $product['bidprice'] > 0) || $product['bidprice'] < 0 || $product['bidprice'] === null){ ?>
										<?php if (!strpos($product['price'], '-1')) { ?>
										<div class="price">
										  <?php if (!$product['special']) { ?>
										  <?php echo $product['price']; ?>
										  <?php } else { ?>
										  <span class="price-old"><?php echo $product['price']; ?></span> <span class="price-new"><?php echo $product['special']; ?></span>
										  <?php } ?>
										</div>
										<?php } ?>
                                        <?php } ?>
										<?php if ($product['rating']) { ?>
										<div class="rating"><img src="catalog/view/theme/default/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
										<?php } ?>
										
										<div class="action">
											<div class="wishlist"><a onclick="addToWishList('<?php echo $product['product_id']; ?>');"><?php echo $this->language->get("button_wishlist"); ?></a></div>
											<div class="compare"><a onclick="addToCompare('<?php echo $product['product_id']; ?>');"><?php echo $this->language->get("button_compare"); ?></a></div>
							
										</div>
										
										<div class="cart"><input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" /></div>
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
 </div> </div>



<script type="text/javascript">
$('#productcarousel<?php echo $id;?>').carousel({interval:<?php echo ( $auto_play_mode?$interval:'false') ;?>,auto:<?php echo $auto_play;?>,pause:'hover'});
</script>
