<?php 
	$themeConfig = $this->config->get('themecontrol');
	$listConfig = array( 
		
		'category_pzoom' => 0
	); 

	$listConfig = array_merge( $listConfig, $themeConfig );	
	$categoryPzoom = $listConfig['category_pzoom'];  
	
?>

<?php if ($products) { ?>	
	<div class="box product-related">	
		<h3 class="box-heading pull-left">
			<span><?php echo $tab_related; ?> (<?php echo count($products); ?>)</span>
		</h3>
		<section class="box-content nopadding">		
			<div class="product-grid">	
				<?php $cols = $productConfig['product_related_column']?$productConfig['product_related_column']:4;
				$ispan = floor(12/$cols); ?>
				<?php foreach ($products as $i => $product) { ?>
				<?php if( ($i+1)%$cols == 1 ) {  ?>
				<div class="row-fluid box-product">
				<?php } ?>		
					<div class="span<?php echo $ispan;?> product-block <?php if($i % $cols == 0) { echo "first";} ?>">
						<div class="product-inner">						
							<?php if ($product['thumb']) { ?>
							<div class="image">
								<?php if( $special )  { ?>
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

							<h3 class="name is-hover">
								<a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a>
							</h3>					

							<?php if ($product['price']) { ?>
							<div class="price is-hover">
								<?php if (!$product['special']) { ?>
								<?php echo $product['price']; ?>
								<?php } else { ?>
								<span class="price-old highlight"><?php echo $product['price']; ?></span> 
								<span class="price-new highlight"><?php echo $product['special']; ?></span>
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
					<?php $i=$i+1; if( $i % $cols == 0 || $i == count($products) ) { ?>
				</div>
				<?php } ?>

				<?php } ?>			
			</div>
		</section>
	</div>	
<?php } ?>

