<div class="bestsellers_grid">
<div class="box">
  <div class="box-heading"><?php echo $heading_title; ?></div>
  <div class="box-content">
	
				    <ul class="bestsellers">
					 	
						<?php foreach ($products as $product) { ?>
						<!-- Item -->
						<li>
						
							<?php if ($product['thumb']) { ?>
							<div class="image"><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a></div>
							<?php } ?>
							<a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a>
			          <?php if (!$product['special']) { ?>
			          <div class="price"><?php echo $product['price']; ?></div>
			          <?php } else { ?>
			          <div class="price"><span class="old-price"><?php echo $product['price']; ?></span> <?php echo $product['special']; ?></div>
			          <?php } ?>
							<p class="clear"></p>
						
						</li>
						<!-- End Item -->
						 <?php } ?>
						
					 </ul>

  </div>
</div>
</div>

<div class="bestsellers_nogrid">
	<div class="box no-bg">
	  <div class="box-heading"><?php echo $heading_title; ?></div>
	  <div class="box-content">
	    <div class="box-product">
	      <?php foreach ($products as $product) { ?>
	      <div>
	        <?php if ($product['thumb']) { ?>
			  <?php if ($product['special']) { ?><div class="product-sale"></div><?php } ?>
	        <div class="image"><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a></div>
	        <?php } ?>
	        <div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
	        <?php if ($product['price']) { ?>
	        <div class="price">
	          <?php if (!$product['special']) { ?>
	          <?php echo $product['price']; ?>
	          <?php } else { ?>
	          <span class="price-new"><?php echo $product['special']; ?></span><br /><span class="price-old"><?php echo $product['price']; ?></span>
	          <?php } ?>
	        </div>
	        <?php } ?>
	        <div class="cart"><a onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button"><span><?php echo $button_cart; ?></span></a></div>
	      </div>
	      <?php } ?>
	    </div>
	  </div>
	</div>
</div>

