<?php 
	$cols = 3;
	$span = floor(12/$cols); 
	$span = $cols > 1 ? "span".$span: 'mode-list';
?>
<div class="box box-produce featured nopadding">
  <div class="box-heading"><h4><?php echo $heading_title; ?></h4></div>
  <div class="box-content block-content">
    <div class="box-product" style="width:100%">
			  <?php foreach ($products as $i => $product) {   ?>
				<?php if( $i++%$cols == 0 &  $cols > 1 ) { ?>
				  <div class="row-fluid">
				<?php } ?>
			  <div class="product-block <?php echo $span; ?>"><div class="product-inner">
			  	<div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
				<?php if ($product['thumb']) { ?>
				<div class="image"><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a></div>
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
				<?php if ($product['rating']) { ?>
				<div class="rating"><img src="catalog/view/theme/default/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
				<?php } ?>
				<div class="cart"><input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" /></div>

				<div class="wishlist">
		          <a onclick="addToWishList('<?php echo $product['product_id']; ?>');" title="" ></a>
		        </div>
		        <div class="compare">
		          <a onclick="addToCompare('<?php echo $product['product_id']; ?>');" title="" ></a>
		        </div>
        
			  </div>
			</div>
			  
				<?php if( $cols > 1 && ( $i%$cols == 0 || $i==count($products)) ) { ?>
				 </div>
				<?php } ?>
				
			  <?php } ?>

    </div>
  </div>
</div>
