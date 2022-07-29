<?php 
	$cols = 4;
	$span = 12/$cols; 
?>
<div class="box special">
  <div class="box-heading"><h2><?php echo $heading_title; ?></h2></div>
  <div class="box-content">
    <div class="box-product">
			  <?php foreach ($products as $i => $product) { ?>
				 <?php if( $i++%$cols == 0 ) { ?>
			<div class="row-fluid">
				<?php } ?>
			  <div class="product_block span<?php echo $span;?>"><div class="product-inner">
        <?php if ($product['thumb']) { ?>
        <div class="image">       
          <a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a></div>
        <?php } ?>
        <div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
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
        </div>
      </div>
        <!--div class="cart"><input type="button" value="<?php //echo $button_cart; ?>" onclick="addToCart('<?php //echo $product['product_id']; ?>');" class="button" /></div-->
			 <?php if( $i%$cols == 0 || $i==count($products) ) { ?>
				 </div>
				<?php } ?>
				
			  <?php } ?>

    
  </div>
</div>
</div>
