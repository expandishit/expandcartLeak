<?php 
	$col = 12/$cols; 
	$active = 'latest';
	$id = rand(1,rand(0,9))+rand(2,time());
	$themeConfig = $this->config->get('themecontrol');
	$categoryConfig = array(
		'category_pzoom'                     => 1,
		'quickview'                          => 0,
		'show_swap_image'                    => 0,
	);
	$categoryConfig     = array_merge($categoryConfig, $themeConfig );
	$categoryPzoom 	    = $categoryConfig['category_pzoom'];
	$quickview          = $categoryConfig['quickview'];
	$swapimg            = $categoryConfig['show_swap_image'];
	$categoryPzoom = isset($themeConfig['category_pzoom']) ? $themeConfig['category_pzoom']:0; 
?>
<div class="<?php echo $prefix;?>  box productcarousel highlighted">
	<div class="box-heading"><span><?php echo $heading_title; ?></span></div>
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
							  <div class="row box-product">
							<?php } ?>
								  <div class="pavcol-sm-<?php echo $cols;?> col-xs-12 col-sm-6">
								  	<div class="product-block">	
								      <?php if ($product['thumb']) { ?>
								      <div class="image"><?php if( $product['special'] ) {   ?>
								    	<span class="product-label-special label"><?php 	$price = preg_replace('#[^\d]+#', '', $product['price']); 	$special = preg_replace('#[^\d]+#', '', $product['special']); 	$discount = round(100*($price - $special)/$price); 	echo sprintf($this->language->get('text_sale_detail'), (int)$discount); ?></span>
								    	<?php } ?>
								    	<a class="img" href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" /></a>
										<?php if ($quickview) { ?>
										<a class="pav-colorbox hidden-sm hidden-xs" href="index.php?route=themecontrol/product&product_id=<?php echo $product['product_id']; ?>"><span class='fa fa-eye'></span></a>
										<?php } ?>
										<?php 
							  			if( $swapimg ){
							      		$product_images = $this->model_catalog_product->getProductImages( $product['product_id'] );
										if(isset($product_images) && !empty($product_images)) {
											$thumb2 = $this->model_tool_image->resize($product_images[0]['image'],  $this->config->get('config_image_product_width'),  $this->config->get('config_image_product_height') );
										?>	
										<span class="hover-image">
											<a class="img" href="<?php echo $product['href']; ?>"><img src="<?php echo $thumb2; ?>" alt="<?php echo $product['name']; ?>"></a>
										</span>
										
										<?php } } ?>
								      </div>
								      <?php } ?>
								      <div class="product-meta">
								      	<div class="warp-info">
										      <h3 class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></h3>
										      <div class="description">
										      <?php echo utf8_substr( strip_tags($product['description']),0,50);?>...</div>
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
<script type="text/javascript"><!--
$(document).ready(function() {
	$('.pav-colorbox').colorbox({
        width: '890px', 
        height: '750px',
        overlayClose: true,
        opacity: 0.5,
        iframe: true, 
    });
});
--></script>
