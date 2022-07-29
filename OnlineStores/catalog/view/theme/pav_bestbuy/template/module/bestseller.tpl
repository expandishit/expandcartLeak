<?php 
	$cols = 4;
	$span = 12/$cols; 
?>
<div class="box box-produce special bg-title">
  <h3 class="box-heading"><span><?php echo $heading_title; ?></span></h3>
  <div class="box-content">
    <div class="box-product">
			  <?php foreach ($products as $i => $product) { ?>
				<?php if( $i++%$cols == 0 ) { ?>
				  <div class="row-fluid">
				<?php } ?> 
		<div class="span<?php echo $span;?> product-block">
    	<div class="product-inner">
      <?php if ($product['thumb']) { ?>
      <div class="image">
	    <?php if( $product['special'] ) {   ?>
			<span class="product-label-special label"><?php echo $this->language->get( 'text_sale' ); ?></span>
		<?php } ?>

		<a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a>

      </div>
      <?php } ?>     
      <div class="product-meta">
      <div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
 

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
			<div class="wishlist"> <a onclick="addToWishList('<?php echo $product['product_id']; ?>');" title="<?php echo $this->language->get("button_wishlist"); ?>" ><?php echo $this->language->get("button_wishlist"); ?></a></div>
			<div class="compare"><a onclick="addToCompare('<?php echo $product['product_id']; ?>');" title="<?php echo $this->language->get("button_compare"); ?>" ><?php echo $this->language->get("button_compare"); ?></a></div>
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
