<div class="box">
  <div class="box-heading"><?php echo $heading_title; ?></div>
  <div class="box-content">
				
				<?php $i = 0; foreach ($products as $product) { $i++; } ?>
				<ul class="btn">
					
					<li><a href="javascript:;" class="prev"></a></li>
					<?php $test = ceil($i/2); ?>
					<?php for( $s = 1; $s <= $test; $s++ ) { ?>
					<li class="number"><a href="javascript:;" rel="<?php echo $s; ?>"></a></li>
					<?php } ?>
					<li><a href="javascript:;" class="next"></a></li>
				
				</ul>
				
				
				<!-- List items -->
				
				<ul class="list-items">
					 
					 <?php foreach ($products as $product) { ?>
					<!-- Item -->
					<li>
						
						<?php if ($product['thumb']) { ?>
						<div class="image float-left">
							
							<?php if ($product['special']) { ?><div class="product-sale"></div><?php } ?>
							<a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a>
							
						</div>
						<?php } ?>
						
						<div class="float-left">
						
							<div class="name"><p><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></p><?php if($product['model'] != '') { ?>Brand: <a href="<?php echo $product['model_url']; ?>"><?php echo $product['model']; ?></a><?php } ?></div>
							
							<div class="description">
							
								<?php echo $product['description']; ?>
							
							</div>
							
							<?php if ($product['price']) { ?>
								<?php if (!$product['special']) { ?>
								<div class="price"><?php echo $product['price']; ?></div>
								<?php } else { ?>
								<div class="price"><span class="price-old"><?php echo $product['price']; ?> </span><br /><?php echo $product['special']; ?></div>
								<?php } ?>
							<?php } ?>

							<div class="cart"><a onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button"><span><?php echo $button_cart; ?></span></a></div>
							
							<div class="wish-list"><a onclick="addToWishList('<?php echo $product['product_id']; ?>');">Add to Wish List</a>&nbsp;&nbsp; | &nbsp;&nbsp;<a onclick="addToCompare('<?php echo $product['product_id']; ?>');">Add to Compare</a></div>
						
						</div>
					
					</li>
					<!-- End Item -->
					<?php } ?>
				
				</ul>
				
				<!-- End List items -->
	 
  </div>
</div>
