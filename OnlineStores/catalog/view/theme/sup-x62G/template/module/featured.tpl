<div class="box">
  <div class="box-heading"><?php echo $heading_title; ?></div>
  <div class="box-content">
    <div class="box-product">
      <?php foreach ($products as $product) { ?>
      <div>
        <?php if ($product['thumb']) { ?>
        <div class="image"><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a></div>
        <?php } ?>
        <div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
        <?php if ($product['rating']) { ?>
        <div class="rating"><img src="catalog/view/theme/default/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
        <?php } ?>
		<?php if ($product['price']) { ?>
		<div class="cartprice" style="float:left;">
			<?php if (!$product['special']) { ?><?php echo $product['price']; ?>
			<?php } else { ?>
			<?php echo $product['special']; ?>
			<?php } ?>
		</div>
		<?php } ?>
        <div class="cart2" style="float:right;"><input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" /></div>
		<div style="clear:both;"></div>
      </div>
      <?php } ?>
    </div>
  </div>
</div>
