<div class="box">
  <div class="box-heading"><span><?php echo $heading_title; ?></span></div>
  <div class="box-content">
    <div class="box-product">
      <?php foreach ($products as $product) { ?>
      <div>
     
        <?php if ($product['thumb']) { ?>
        <div class="image"><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" title="<?php echo $product['name']; ?>" /></a></div>
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
        <div class="abs">
        <!--<?php if ($product['rating']) { ?>
        <div class="rating"><img src="catalog/view/theme/ustore-color1/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
        <?php } ?>-->
        
        <div class="cart">
        	<a onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button1" title="Add to Cart"><span><?php echo $button_cart; ?></span></a>
        	<a class="btn-detail ml10" title="Detail" href="<?php echo $product['href']; ?>"><span>Detail</span></a></div>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
</div>
