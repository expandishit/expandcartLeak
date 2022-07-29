<div class="box style-one featured">
  <span class="style-one-shadow"></span>
  <div class="box-heading"><?php echo $heading_title; ?><span class="heading-shadow"></span>
  <a id="next2" class="next" href="#">&raquo;</a>
  <a id="prev2" class="prev" href="#">&laquo;</a>
  </div>
  <div class="box-content">  
    <div class="box-product">
			
            
    <div class="list_carousel responsive" >
    <ul class="foo3">
          <?php foreach ($products as $product) { ?>
          	<li>
                <div class="main">
                    <!-- image //
                    =============-->
                    <!--for swap image-->
                    <?php if ($product['thumb_swap']) { ?>
                      <div class="image">
                          <a href="<?php echo $product['href']; ?>">
                             <img oversrc="<?php echo $product['thumb_swap']; ?>" src="<?php echo $product['thumb']; ?>"
                                  data-original="<?php echo $product['thumb']; ?>"
                             title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" style="border:none"/>
                          </a>
                      </div>
        
                      <?php } else {?>
        
                      <div class="image">
                          <a href="<?php echo $product['href']; ?>">
                              <img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>"
                      data-original="<?php echo $product['thumb']; ?>"
                              alt="<?php echo $product['name']; ?>" style="border:none"/>
                          </a>
                      </div>
        
                      <?php } ?>
                    <!--/ swap img-->
                    
                    <!-- name //
                    =============-->
                    <div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
                    
                    <!-- price //
                    =============-->
                    <?php if ($product['price']) { ?>
                    <div class="price">
                      <?php if (!$product['special']) { ?>
                      <?php echo $product['price']; ?>
                      <?php } else { ?>
                      <span class="price-old"><?php echo $product['price']; ?></span> 
                      <span class="price-new"><?php echo $product['special']; ?></span>
                      <span class="sale"><?php echo $product['saving']; ?>%</span>
                      <?php } ?>
                    </div>
                    <?php } ?>
                    
                    <!-- rate //
                    =============-->
                    <div class="rating">
                    <img src="catalog/view/theme/gazal/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" />
                    </div>
                    
                    <!-- wish  //  compare  //  cart
                    =============-->
                    <div class="hover-options">
                    <div class="cart">
                      <input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
                    </div>
                    <a class="wish tooltip" onclick="addToWishList('<?php echo $product['product_id']; ?>');"><span><?php echo $button_wishlist; ?></span></a>
                    <a class="compare tooltip" onclick="addToCompare('<?php echo $product['product_id']; ?>');"><span><?php echo $button_compare; ?></span></a>
                    </div>
                
                </div><!--/main-->
            </li>
          <?php } ?>
    </ul>
    <div class="pager pager2"></div>
    
    </div> 
    </div><!--/box-product-->    
  </div><!--/box-content-->
</div><!--/box-->
<!-- liquid carousel -->
    
