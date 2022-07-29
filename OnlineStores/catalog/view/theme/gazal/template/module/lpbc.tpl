<?php if ($dm == 'carousel') { ?>

<?php if ($products && !$subcats) { ?>

<div class="box tf subs">

<h2 class="cat-title"><?php echo $category_name; ?><?php if (!isset($this->request->get['path'])) { ?> - <a href="<?php echo $category_url; ?>"><?php echo $viewall; ?></a><?php } ?>
</h2>

    <div class="box-content">
    
        <div class="box-product">
        
            <div id="lpbccarousel<?php echo $module; ?>" style="display: block; width: 100%; margin-right: 0px; margin-bottom: 0px;">
              <ul class="jcarousel-skin-opencart">
            <?php foreach ($products as $product) { ?>
                <li>
                    <div class="main2">
                      <!-- image //
                        =============-->
                        <!--for swap image-->
                        <?php if ($product['thumb_swap']) { ?>
                          <div class="image">
                              <a href="<?php echo $product['href']; ?>">
                                 <img class="lazy" oversrc="<?php echo $product['thumb_swap']; ?>" data-original="<?php echo $product['thumb']; ?>"
                                 title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" style="border:none"/>
                              </a>
                          </div>
            
                          <?php } else {?>
            
                          <div class="image">
                              <a href="<?php echo $product['href']; ?>">
                                  <img class="lazy" data-original="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>"
                                  alt="<?php echo $product['name']; ?>" style="border:none"/>
                              </a>
                          </div>
            
                          <?php } ?>
                        <!--/ swap img-->
                        
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
                      
                        <div class="rating">
                        <img class="lazy" data-original="catalog/view/theme/gazal/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" />
                        </div>

                      <div class="hover-options">
                      <div class="cart">
                        <input type="button" value="<?php echo $button_cart; ?>"onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
                      </div>
                      <a class="wish tooltip" onclick="addToWishList('<?php echo $product['product_id']; ?>');"><span><?php echo $button_wishlist; ?></span></a>
                      <a class="compare tooltip" onclick="addToCompare('<?php echo $product['product_id']; ?>');"><span><?php echo $button_compare; ?></span></a>
                      </div>
                      
                    </div><!--/main2-->
                </li>
                <?php } ?>
              </ul>
            </div><!--/lpbccarousel-->
        
        </div><!--/box-product-->
    
    </div><!--/box-content-->
    
</div><!--/box-->

<?php } elseif ($subcats) { ?>

<div class="box tt subs">

<h2 class="cat-title"> <?php echo $category_name; ?><?php if (!isset($this->request->get['path'])) { ?> - <a href="<?php echo $category_url; ?>"><?php echo $viewall; ?></a><?php } ?>
</h2>

<div class="box-content">

<div id="catabs<?php echo $module; ?>" class="htabs mod">
 <?php foreach ($subcats as $subcat) { ?>
  <?php if (count($subcat['subproducts']) > $pmin) { ?>
   <a href="#tab-cat<?php echo $subcat['id']; ?>"><?php echo $subcat['name']; ?></a>
  <?php } ?>
 <?php } ?>
</div>

<?php foreach ($subcats as $subcat) { ?>
<?php if (count($subcat['subproducts']) > $pmin) { ?>
 
 <div id="tab-cat<?php echo $subcat['id']; ?>" class="tab-content" style="padding: 0px; padding-top: 10px; border: none;">
  
  <div class="box-product">
  
      <div id="lpbccarousel<?php echo $module; ?>cat<?php echo $subcat['id']; ?>" style="display: block; width: 100%; margin-right: 0px; margin-bottom: 0px;">
      
      <ul class="jcarousel-skin-opencart">
       <?php foreach ($subcat['subproducts'] as $product) { ?>
        <li>
          
          <div class="main2">
        
          <!-- image //
            =============-->
            <!--for swap image-->
            <?php if ($product['thumb_swap']) { ?>
              <div class="image">
                  <a href="<?php echo $product['href']; ?>">
                     <img class="lazy" oversrc="<?php echo $product['thumb_swap']; ?>" data-original="<?php echo $product['thumb']; ?>"
                     title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" style="border:none"/>
                  </a>
              </div>

              <?php } else {?>

              <div class="image">
                  <a href="<?php echo $product['href']; ?>">
                      <img class="lazy" data-original="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>"
                      alt="<?php echo $product['name']; ?>" style="border:none"/>
                  </a>
              </div>

              <?php } ?>
            <!--/ swap img-->
                            
                            
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
                            
          <div class="rating"><img class="lazy" data-original="catalog/view/theme/gazal/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>

          <div class="hover-options">
          <div class="cart">
          <input type="button" value="<?php echo $button_cart; ?>"onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
          </div>
          <a class="wish tooltip" onclick="addToWishList('<?php echo $product['product_id']; ?>');"><span><?php echo $button_wishlist; ?></span></a>
          <a class="compare tooltip" onclick="addToCompare('<?php echo $product['product_id']; ?>');"><span><?php echo $button_compare; ?></span></a>
          </div>
        
         </div>
        
        </li>
        <?php } ?>
        </ul>
        
        </div><!--/lpbccarousel-->
    
    </div><!--/box-product-->
  
  </div><!--/tab-content-->
  <script type="text/javascript"><!--
$('#lpbccarousel<?php echo $module; ?>cat<?php echo $subcat['id']; ?> ul').jcarousel({
	vertical: false,
	visible: 4,
	scroll: 3
});
//--></script>
<?php } ?>
<?php } ?>
</div>
</div>
<script type="text/javascript"><!--
$('#catabs<?php echo $module; ?> a').tabs();
//--></script> 
<?php } ?>
<?php } else { ?>
<?php if ($products && !$subcats) { ?>
<div class="box subs">

<h2 class="cat-title"><?php echo $category_name; ?><?php if (!isset($this->request->get['path'])) { ?> - <a href="<?php echo $category_url; ?>"><?php echo $viewall; ?></a><?php } ?>
</h2>

<div class="box-content">
<div class="box-product">
<?php foreach ($products as $product) { ?>
    <div class="main2">
      <!-- image //
            =============-->
            <!--for swap image-->
            <?php if ($product['thumb_swap']) { ?>
              <div class="image">
                  <a href="<?php echo $product['href']; ?>">
                     <img class="lazy" oversrc="<?php echo $product['thumb_swap']; ?>" data-original="<?php echo $product['thumb']; ?>"
                     title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" style="border:none"/>
                  </a>
              </div>

              <?php } else {?>

              <div class="image">
                  <a href="<?php echo $product['href']; ?>">
                      <img class="lazy" data-original="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>"
                      alt="<?php echo $product['name']; ?>" style="border:none"/>
                  </a>
              </div>

              <?php } ?>
            <!--/ swap img-->
            
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
  
      <div class="rating"><img class="lazy" data-original="catalog/view/theme/gazal/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
    
	  <!-- cart -->
      <div class="hover-options">
      <div class="cart">
      <input type="button" value="<?php echo $button_cart; ?>"onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
      </div>
      <a class="wish tooltip" onclick="addToWishList('<?php echo $product['product_id']; ?>');"><span><?php echo $button_wishlist; ?></span></a>
      <a class="compare tooltip" onclick="addToCompare('<?php echo $product['product_id']; ?>');"><span><?php echo $button_compare; ?></span></a>
      </div>
      
    </div>
    <?php } ?>
</div>
</div>
</div>
<?php } elseif ($subcats) { ?>
<div class="box subs">

<h2 class="cat-title"><?php echo $category_name; ?><?php if (!isset($this->request->get['path'])) { ?> - <a href="<?php echo $category_url; ?>"><?php echo $viewall; ?></a><?php } ?></h2>

<div class="box-content">
<div id="catabs<?php echo $module; ?>" class="htabs mod">
 <?php foreach ($subcats as $subcat) { ?>
  <?php if (count($subcat['subproducts']) > $pmin) { ?>
   <a href="#tab-cat<?php echo $subcat['id']; ?>"><?php echo $subcat['name']; ?></a>
  <?php } ?>
 <?php } ?>
</div>

<?php foreach ($subcats as $subcat) { ?>
<?php if (count($subcat['subproducts']) > $pmin) { ?>
 <div id="tab-cat<?php echo $subcat['id']; ?>" class="tab-content" style="padding: 0px; padding-top: 10px; border: none;">
  <div class="box-product">
   <?php foreach ($subcat['subproducts'] as $product) { ?>
    <div class="main2">
      <!-- image //
        =============-->
        <!--for swap image-->
        <?php if ($product['thumb_swap']) { ?>
          <div class="image">
              <a href="<?php echo $product['href']; ?>">
                 <img class="lazy" oversrc="<?php echo $product['thumb_swap']; ?>" data-original="<?php echo $product['thumb']; ?>"
                 title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" style="border:none"/>
              </a>
          </div>

          <?php } else {?>

          <div class="image">
              <a href="<?php echo $product['href']; ?>">
                  <img class="lazy" data-original="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>"
                  alt="<?php echo $product['name']; ?>" style="border:none"/>
              </a>
          </div>

          <?php } ?>
        <!--/ swap img-->
        
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
      
      <div class="rating"><img class="lazy" data-original="catalog/view/theme/gazal/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
   
      <!-- cart -->
      <div class="hover-options">
      <div class="cart">
      <input type="button" value="<?php echo $button_cart; ?>"onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
      </div>
      <a class="wish tooltip" onclick="addToWishList('<?php echo $product['product_id']; ?>');"><span><?php echo $button_wishlist; ?></span></a>
      <a class="compare tooltip" onclick="addToCompare('<?php echo $product['product_id']; ?>');"><span><?php echo $button_compare; ?></span></a>
      </div>
      
    </div>
    <?php } ?>
    </div>
  </div>
<?php } ?>
<?php } ?>
</div>
</div>
<script type="text/javascript"><!--
$('#catabs<?php echo $module; ?> a').tabs();
//--></script> 
<?php } ?>
<?php } ?>
<?php if ($dm == 'carousel') { ?>
<?php if ($products && !$subcats) { ?>
<script type="text/javascript"><!--
$('#lpbccarousel<?php echo $module; ?> ul').jcarousel({
	vertical: false,
	visible: 4,
	scroll: 3
});
//--></script>
<?php } ?>
<?php } ?>