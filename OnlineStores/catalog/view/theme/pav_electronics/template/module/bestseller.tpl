<?php 
	$cols = 3;
	$span = 12/$cols;
?>
<div class="box box-produce dark nopadding ">
  <div class="box-heading"><?php echo $heading_title; ?></div>
  <div class="box-content block-content">
    <div class="box-product sidelist" >
			  <?php foreach ($products as $i => $product) { ?>
				 <?php if( $i++%$cols == 0 ) { ?>
				  <div class="row-fluid">
				<?php } ?> 
			  <div class="span<?php echo $span;?> product-block"><div class="product-inner">
			  <div class="name">
						
		    	<a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
				<?php if ($product['thumb']) { ?>
				<div class="image">
		    	<a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" /></a>
		      </div>
				<?php } ?>
				
				<?php if (isset($product['description']))  { ?><div class="description">
					<?php echo utf8_substr( strip_tags($product['description']),0,60);?>...
				</div>
				<?php } ?>
				<?php if ($product['rating']) { ?>
				<div class="rating"><img src="catalog/view/theme/default/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
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
				<div class="cart"><input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" /></div>
			</div>
			  <div class="wishlist"><a onclick="addToWishList('<?php echo $product['product_id']; ?>');" title="" ></a></div>
		  <div class="compare"><a onclick="addToCompare('<?php echo $product['product_id']; ?>');" title="" ></a></div>
			  </div></div>
			  
			 <?php if( $i%$cols == 0 || $i==count($products) ) { ?>
				 </div>
				<?php } ?>
			
			  <?php } ?>

    </div>
  </div>
   </div>
