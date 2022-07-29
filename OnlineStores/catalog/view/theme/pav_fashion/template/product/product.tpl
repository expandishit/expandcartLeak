<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" );

  $themeConfig = $this->config->get('themecontrol');
  $productConfig = array(
      'product_enablezoom'         => 1,
      'product_zoommode'           => 'basic',
      'product_zoomeasing'         => 1,
      'product_zoomlensshape'      => "round",
      'product_zoomlenssize'       => "150",
      'product_zoomgallery'        => 0,
      'enable_product_customtab'   => 0,
      'product_customtab_name'     => '',
      'product_customtab_content'  => '',
      'product_related_column'     => 0,
  );
  $languageID = $this->config->get('config_language_id');
  $productConfig = array_merge( $productConfig, $themeConfig ); 
?>

<?php

$productOGimg = "";

$productOGimg .= '<meta property="og:image" content="' . str_replace(" ", "%20", $popup) . '">';

if ($images) {
foreach ($images as  $image) {
$productOGimg .= '<meta property="og:image" content="' . str_replace(" ", "%20", $image['popup']) . '">';
}
}

$header = str_replace("<!-- og:image -->", $productOGimg, $header);

?>

<?php echo $header; ?>
<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/breadcrumb.tpl" );  ?>  
<?php if( $SPAN[0] ): ?>
	<aside class="col-lg-<?php echo $SPAN[0];?> col-md-<?php echo $SPAN[0];?> col-sm-12 col-xs-12">
		<?php echo $column_left; ?>
	</aside>	
<?php endif; ?>  
<section class="col-lg-<?php echo $SPAN[1];?> col-md-<?php echo $SPAN[1];?> col-sm-12 col-xs-12">
<div id="content"><?php echo $content_top; ?>
  
 
  <div class="product-info">
	<div class="row">

    
        <?php if ($thumb || $images) { ?>
    <div class="col-lg-7 image-container">
        <?php if ($thumb) { ?>
        <div class="image">

        <?php if( $special )  { ?>
           <span class="product-label-special label"><span class="special"><?php echo $this->language->get( 'text_sale' ); ?></span></span>
        <?php } ?>
          <a href="<?php echo $popup; ?>" title="<?php echo $heading_title; ?>" class="colorbox">
          <img src="<?php echo $thumb; ?>" title="<?php echo $heading_title; ?>" alt="<?php echo $heading_title; ?>" id="image"  data-zoom-image="<?php echo $popup; ?>" class="product-image-zoom"/></a></div>
        <?php } ?>
        <?php if ($images) { ?>
        <div class="image-additional slide carousel" id="image-additional"><div class="carousel-inner">
        <?php 
        if( $productConfig['product_zoomgallery'] == 'slider' && $thumb ) {  
          $eimages = array( 0=> array( 'popup'=>$popup,'thumb'=> $thumb )  ); 
          $images = array_merge( $eimages, $images );
        }
        $icols = 4; $i= 0;
        foreach ($images as  $image) { ?>
          <?php if( (++$i)%$icols == 1 ) { ?>
          <div class="item">
          <?php } ?>

              <a href="<?php echo $image['popup']; ?>" title="<?php echo $heading_title; ?>" class="colorbox" data-zoom-image="<?php echo $image['popup']; ?>" data-image="<?php echo $image['popup']; ?>">
                <img src="<?php echo $image['thumb']; ?>" style="max-width:<?php echo $this->config->get('config_image_additional_width');?>px"  title="<?php echo $heading_title; ?>" alt="<?php echo $heading_title; ?>" data-zoom-image="<?php echo $image['popup']; ?>" class="product-image-zoom" />
              </a>
            <?php if( $i%$icols == 0 || $i==count($images) ) { ?>
              </div>
          <?php } ?>
        <?php } ?>
      </div>
          <div class="carousel-control left fa fa-angle-left" href="#image-additional" data-slide="prev"></div>
          <div class="carousel-control right fa fa-angle-right" href="#image-additional" data-slide="next"></div>
        </div>
          <script type="text/javascript">
            $('#image-additional .item:first').addClass('active');
            $('#image-additional').carousel({interval:false})
          </script>

        <?php } ?>
     
         </div>
    <?php } ?>


    
    <div class="col-lg-5">
		 <h1><?php echo $heading_title; ?></h1>
		  <?php if ($review_status) { ?>
      <div class="review">
        <div><img src="catalog/view/theme/<?php echo $this->config->get('config_template');?>/image/stars-<?php echo $rating; ?>.png" alt="<?php echo $reviews; ?>" />&nbsp;&nbsp;<a onclick="$('a[href=\'#tab-review\']').trigger('click');"><?php echo $reviews; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('a[href=\'#tab-review\']').trigger('click');"><?php echo $text_write; ?></a></div>
      </div>
      <?php } ?>
	    
      <div class="description">
        <?php if ($manufacturer) { ?>
        <span><?php echo $text_manufacturer; ?></span> <a href="<?php echo $manufacturers; ?>"><?php echo $manufacturer; ?></a><br />
        <?php } ?>
        <span><?php echo $text_model; ?></span> <?php echo $model; ?><br />
        <?php if ($reward) { ?>
        <span><?php echo $text_reward; ?></span> <?php echo $reward; ?><br />
        <?php } ?>
        <span class="availability"><?php echo $text_stock; ?></span> <?php echo $stock; ?></div>
	
	    <?php if ($price) { ?>
        <div class="price"><?php //echo $text_price; ?>
          <?php if (!$special) { ?>
          <?php echo $price; ?>
          <?php } else { ?>
          <span class="price-old"><?php echo $price; ?></span> <span class="price-new"><?php echo $special; ?></span>
          <?php } ?>
          <br />
          <?php if ($tax) { ?>
          <span class="price-tax"><?php echo $text_tax; ?> <?php echo $tax; ?></span><br />
          <?php } ?>
          <?php if ($points) { ?>
          <span class="reward"><small><?php echo $text_points; ?> <?php echo $points; ?></small></span><br />
          <?php } ?>
          <?php if ($discounts) { ?>
          <div class="discount">
            <?php foreach ($discounts as $discount) { ?>
            <?php echo sprintf($text_discount, $discount['quantity'], $discount['price']); ?><br />
            <?php } ?>
          </div>
          <?php } ?>
        </div>
      <?php } ?>

        <div class="product-extra">
          <div class="quantity-adder">
            <div class="quantity-number pull-left">
              <span><?php echo $text_qty; ?></span>            
          <input class="form-control" type="text" name="quantity" size="2" value="<?php echo $minimum; ?>" />
            </div>
            <div class="quantity-wrapper pull-left">
            <span class="add-up add-action">+</span> 
            <span class="add-down add-action">-</span>
            </div>
          </div>
          

            <input type="hidden" name="product_id" size="2" value="<?php echo $product_id; ?>" />
            &nbsp;
          
            <div class="cart"><i class=" fa fa-shopping-cart"></i><input type="button" value="<?php echo $button_cart; ?>" id="button-cart" class="" /></div>
           <!-- <span>&nbsp;&nbsp;<?php //echo $text_or; ?>&nbsp;&nbsp;</span> -->
            <div class="wishlist"><i class=" fa fa-heart"></i><a onclick="addToWishList('<?php echo $product_id; ?>');" data-placement="top" data-toggle="" data-original-title="<?php echo $button_wishlist; ?>"><span><?php echo $button_wishlist; ?></a></span></div>
            <div class="compare"><i class=" fa fa-retweet"></i><a onclick="addToCompare('<?php echo $product_id; ?>');" data-placement="top" data-toggle="" data-original-title="<?php echo $button_compare; ?>"><span><?php echo $button_compare; ?></a></span></div>
         
        </div>
        <div class="share clearfix">
            <!-- Go to www.addthis.com/dashboard to customize your tools -->
            <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-56c3976c6618d23f"></script>

            <!-- Go to www.addthis.com/dashboard to customize your tools -->
            <div class="addthis_sharing_toolbox"></div>
        </div>


        <?php if ($minimum > 1) { ?>
        <div class="minimum"><?php echo $text_minimum; ?></div>
        <?php } ?> 
      <?php if ($options) { ?>
      <div class="options">
        <h2><?php echo $text_option; ?></h2>
        <?php foreach ($options as $option) { ?>
        <?php if ($option['type'] == 'select') { ?>
        <div id="option-<?php echo $option['product_option_id']; ?>" class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <label><?php echo $option['name']; ?>:</label>
          <select class="form-control" name="option[<?php echo $option['product_option_id']; ?>]">
            <option value=""><?php echo $text_select; ?></option>
            <?php foreach ($option['option_value'] as $option_value) { ?>
            <option value="<?php echo $option_value['product_option_value_id']; ?>"><?php echo $option_value['name']; ?>
            <?php if ($option_value['price']) { ?>
            (<?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>)
            <?php } ?>
            </option>
            <?php } ?>
          </select>
        </div>
        <?php } ?>
        <?php if ($option['type'] == 'radio') { ?>
        <div id="option-<?php echo $option['product_option_id']; ?>" class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <label><?php echo $option['name']; ?>:</label>
          <?php foreach ($option['option_value'] as $option_value) { ?>
          <div class="radio"><input type="radio" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option_value['product_option_value_id']; ?>" id="option-value-<?php echo $option_value['product_option_value_id']; ?>" />
          <label for="option-value-<?php echo $option_value['product_option_value_id']; ?>"><?php echo $option_value['name']; ?>
            <?php if ($option_value['price']) { ?>
            (<?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>)
            <?php } ?>
          </label>
          </div>
          <?php } ?>
        </div>
        <?php } ?>
        <?php if ($option['type'] == 'checkbox') { ?>
        <div id="option-<?php echo $option['product_option_id']; ?>" class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <label><?php echo $option['name']; ?>:</label>
          <?php foreach ($option['option_value'] as $option_value) { ?>
           <div class="checkbox"><input type="checkbox" name="option[<?php echo $option['product_option_id']; ?>][]" value="<?php echo $option_value['product_option_value_id']; ?>" id="option-value-<?php echo $option_value['product_option_value_id']; ?>" />
          <label for="option-value-<?php echo $option_value['product_option_value_id']; ?>"><?php echo $option_value['name']; ?>
            <?php if ($option_value['price']) { ?>
            (<?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>)
            <?php } ?>
          </label>
          </div>
          <?php } ?>
        </div>
        <?php } ?>
        <?php if ($option['type'] == 'image') { ?>
        <div id="option-<?php echo $option['product_option_id']; ?>" class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <label><?php echo $option['name']; ?>:</label>
          <table class="option-image">
            <?php foreach ($option['option_value'] as $option_value) { ?>
            <tr>
              <td style="width: 1px;"><input type="radio" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option_value['product_option_value_id']; ?>" id="option-value-<?php echo $option_value['product_option_value_id']; ?>" /></td>
              <td><label for="option-value-<?php echo $option_value['product_option_value_id']; ?>"><img src="<?php echo $option_value['image']; ?>" alt="<?php echo $option_value['name'] . ($option_value['price'] ? ' ' . $option_value['price_prefix'] . $option_value['price'] : ''); ?>" /></label></td>
              <td><label for="option-value-<?php echo $option_value['product_option_value_id']; ?>"><?php echo $option_value['name']; ?>
                  <?php if ($option_value['price']) { ?>
                  (<?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>)
                  <?php } ?>
                </label></td>
            </tr>
            <?php } ?>
          </table>
        </div>
        <?php } ?>
        <?php if ($option['type'] == 'text') { ?>
        <div id="option-<?php echo $option['product_option_id']; ?>" class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <label><?php echo $option['name']; ?>:</label>
          <input class="form-control" type="text" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['option_value']; ?>" />
        </div>
        <?php } ?>
        <?php if ($option['type'] == 'textarea') { ?>
        <div id="option-<?php echo $option['product_option_id']; ?>" class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <label><?php echo $option['name']; ?>:</label>
          <textarea class="form-control" name="option[<?php echo $option['product_option_id']; ?>]" cols="40" rows="5"><?php echo $option['option_value']; ?></textarea>
        </div>
        <?php } ?>
        <?php if ($option['type'] == 'file') { ?>
        <div id="option-<?php echo $option['product_option_id']; ?>" class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <label><?php echo $option['name']; ?>:</label>
          <input class="btn btn-theme-primary" type="button" value="<?php echo $button_upload; ?>" id="button-option-<?php echo $option['product_option_id']; ?>" class="button">
          <input type="hidden" name="option[<?php echo $option['product_option_id']; ?>]" value="" />
        </div>
        <?php } ?>
        <?php if ($option['type'] == 'date') { ?>
        <div id="option-<?php echo $option['product_option_id']; ?>" class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <label><?php echo $option['name']; ?>:</label>
          <input class="form-control date" type="text" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['option_value']; ?>" />
        </div>
        <?php } ?>
        <?php if ($option['type'] == 'datetime') { ?>
        <div id="option-<?php echo $option['product_option_id']; ?>" class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <label><?php echo $option['name']; ?>:</label>
          <input class="form-control datetime" type="text" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['option_value']; ?>" />
        </div>
        <?php } ?>
        <?php if ($option['type'] == 'time') { ?>
        <div id="option-<?php echo $option['product_option_id']; ?>" class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <label><?php echo $option['name']; ?>:</label>
          <input class="form-control time" type="text" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['option_value']; ?>" />
        </div>
        <?php } ?>
        <?php } ?>
      </div>
      <?php } ?>
  
  <?php if ($tags) { ?>
  <div class="tags"><b><?php echo $text_tags; ?></b>
    <?php for ($i = 0; $i < count($tags); $i++) { ?>
    <?php if ($i < (count($tags) - 1)) { ?>
    <a href="<?php echo $tags[$i]['href']; ?>"><?php echo $tags[$i]['tag']; ?></a>,
    <?php } else { ?>
    <a href="<?php echo $tags[$i]['href']; ?>"><?php echo $tags[$i]['tag']; ?></a>
    <?php } ?>
    <?php } ?>
  </div>
  <?php } ?>
  

    </div>
	</div>
  </div>
  <div class="tabs-group">
  <div id="tabs" class="htabs clearfix"><a href="#tab-description"><?php echo $tab_description; ?></a>
    <?php if ($attribute_groups) { ?>
    <a href="#tab-attribute"><?php echo $tab_attribute; ?></a>
    <?php } ?>
    <?php if ($review_status) { ?>
    <a href="#tab-review"><?php echo $tab_review; ?></a>
    <?php } ?>
    <?php if( $productConfig['enable_product_customtab'] && isset($productConfig['product_customtab_name'][$languageID]) ) { ?>
     <a href="#tab-customtab"><?php echo $productConfig['product_customtab_name'][$languageID]; ?></a>
   <?php } ?> 

  </div>
  <div id="tab-description" class="tab-content"><?php echo $description; ?></div>
  <?php if ($attribute_groups) { ?>
  <div id="tab-attribute" class="tab-content">
    <table class="attribute">
      <?php foreach ($attribute_groups as $attribute_group) { ?>
      <thead>
        <tr>
          <td colspan="2"><?php echo $attribute_group['name']; ?></td>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($attribute_group['attribute'] as $attribute) { ?>
        <tr>
          <td><?php echo $attribute['name']; ?></td>
          <td><?php echo $attribute['text']; ?></td>
        </tr>
        <?php } ?>
      </tbody>
      <?php } ?>
    </table>
  </div>
  <?php } ?>
  <?php if ($review_status) { ?>
  <div id="tab-review" class="tab-content">
    <div id="review"></div>
    <h2 id="review-title"><?php echo $text_write; ?></h2>
    <div class="form-review">
    <div class="option"><label><?php echo $entry_name; ?></label>
    <input class="form-control" type="text" name="name" value="" />
    </div>
    <div class="option">
    <label><?php echo $entry_review; ?></label>
    <textarea class="form-control" name="text" cols="40" rows="8"></textarea>
    <span style="font-size: 11px;"><?php echo $text_note; ?></span><br />
    </div>

    <div class="option">
    <label><?php echo $entry_rating; ?></label> <span><?php echo $entry_bad; ?></span>&nbsp;
    <input type="radio" name="rating" value="1" />
    &nbsp;
    <input type="radio" name="rating" value="2" />
    &nbsp;
    <input type="radio" name="rating" value="3" />
    &nbsp;
    <input type="radio" name="rating" value="4" />
    &nbsp;
    <input type="radio" name="rating" value="5" />
    &nbsp;<span><?php echo $entry_good; ?></span><br />
    </div>
    <div class=" option form-inline">
    <label><?php echo $entry_captcha; ?></label> 
	  <span class="form-group"><img src="index.php?route=common/captcha" alt="" id="captcha" /></span>
    <span class="form-group"><input class="form-control" type="text" name="captcha" value="" /></span>
    </div>
      <div><a id="button-review" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php } ?>
  </div>
    <?php if( $productConfig['enable_product_customtab'] && isset($productConfig['product_customtab_content'][$languageID]) ) { ?>
     <div id="tab-customtab" class="tab-content custom-tab">
      <div class="inner">
        <?php echo html_entity_decode( $productConfig['product_customtab_content'][$languageID], ENT_QUOTES, 'UTF-8'); ?>
      </div></div>
   <?php } ?> 
  </div>
   
      <?php if ($products) { ?>
  <?php 
 	$cols = ($productConfig['product_related_column'] == 0)?3:$productConfig['product_related_column'];
  $span = 12/$cols; 
    ?>
  <div class="product-related box bloglatest">
   <div class="box-heading"><span><?php echo $tab_related; ?> (<?php echo count($products); ?>)</span></div>
   <div id="related" class="slide product-grid" data-interval="0">
    <div class="carousel-controls">
      <a class="carousel-control left icon-angle-left" href="#related" data-slide="prev"></a>
      <a class="carousel-control right icon-angle-right" href="#related" data-slide="next"></a>
    </div>
    <div class="box-content products-block carousel-inner">
        <?php foreach ($products as $i => $product) { $i=$i+1; ?>
        <?php if( $i%$cols == 1 && $cols > 1 ) { ?>
        <div class= "item <?php if($i==1) {?>active<?php } ?>">
        <div class="row">
        <?php } ?>
        <div class="col-lg-<?php echo $span;?> col-md-<?php echo $span;?> col-sm-6 col-xs-12 product-block">
          <?php if ($product['thumb']) { ?>
      <div class="image">
        <?php if( $product['special'] ) {   ?>
        <span class="product-label-special label"><span class="special"><?php echo $this->language->get( 'text_sale' ); ?></span></span>
        <?php } ?>
        <a class="img" href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" /></a>
        
      </div>
      <?php } ?>
          <div class="product-meta">
            <div class="warp-info">
                <h3 class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></h3>               
                <?php if ($product['price']) { ?>
                <div class="price">
                <?php if (!$product['special']) { ?>
                <?php echo $product['price']; ?>
                <?php } else { ?>
                <span class="price-old"><?php echo $product['price']; ?></span> <span class="price-new"><?php echo $product['special']; ?></span>
                <?php } ?>
              </div>
                <?php } ?>
            </div>
        <div class="product-action">

          <div class="cart">
            <i class=" fa fa-shopping-cart"></i>
            <input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
          </div>
          <div class="wishlist-compare">

          <a class="wishlist fa fa-heart" onclick="addToWishList('<?php echo $product['product_id']; ?>');"  data-placement="top" data-toggle="tooltip" data-original-title="<?php echo $button_wishlist; ?>"><span><?php echo $button_wishlist; ?></span></a>
          <a class="compare fa fa-retweet" onclick="addToCompare('<?php echo $product['product_id']; ?>');"  data-placement="top" data-toggle="tooltip" data-original-title="<?php echo $button_compare; ?>"><span><?php echo $button_compare; ?></span></a>
          </div>

        </div>
    </div>

 </div>
        
        <?php if( $cols > 1  && ($i%$cols == 0 || $i==count($products)) ) { ?>
       </div>
        </div>
        <?php } ?>
        
        <?php } ?>
  </div>
  </div>
  </div>
  <?php } ?>
  
  
  <?php echo $content_bottom; ?></div>
  <?php if( $productConfig['product_enablezoom'] ) { ?>
<script type="text/javascript" src=" catalog/view/javascript/jquery/elevatezoom/elevatezoom-min.js"></script>
<script type="text/javascript">
 <?php if( $productConfig['product_zoomgallery'] == 'slider' ) {  ?>
  $("#image").elevateZoom({gallery:'image-additional', cursor: 'pointer', galleryActiveClass: 'active'});
  <?php } else { ?>
  var zoomCollection = '<?php echo $productConfig["product_zoomgallery"]=="basic"?".product-image-zoom":"#image";?>';
   $( zoomCollection ).elevateZoom({
      <?php if( $productConfig['product_zoommode'] != 'basic' ) { ?>
      zoomType        : "<?php echo $productConfig['product_zoommode'];?>",
      <?php } ?>
      lensShape : "<?php echo $productConfig['product_zoomlensshape'];?>",
      lensSize    : <?php echo (int)$productConfig['product_zoomlenssize'];?>,
  
   });
  <?php } ?> 
</script>
<?php } ?>
<script type="text/javascript"><!--
$(document).ready(function() {
  setTimeout(function() {
    $('a.colorbox').colorbox({
      overlayClose: true,
      opacity: 0.5,
      rel: "colorbox"
    })
  },100);
});
//--></script> 
 <script type="text/javascript"><!--    
$('#button-cart').bind('click', function() {
  $.ajax({
    url: 'index.php?route=checkout/cart/add',
    type: 'post',
    data: $('.product-info input[type=\'text\'], .product-info input[type=\'hidden\'], .product-info input[type=\'radio\']:checked, .product-info input[type=\'checkbox\']:checked, .product-info select, .product-info textarea'),
    dataType: 'json',
    success: function(json) {
      $('.success, .warning, .attention, information, .error').remove();
      
      if (json['error']) {
        if (json['error']['option']) {
          for (i in json['error']['option']) {
            $('#option-' + i).after('<span class="error">' + json['error']['option'][i] + '</span>');
          }
                }
      } 
      
      if (json['success']) {
        $('#notification').html('<div class="success" style="display: none;">' + json['success'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>');
          
        $('.success').fadeIn('slow');
          
        $('#cart-total').html(json['total']);

          if (json['enable_order_popup'] != '1')
              $('html, body').animate({ scrollTop: 0 }, 'slow');

          if (json['enable_order_popup'] == '1') {
              $('head').append("<style type='text/css'>.customAddtoCart .ui-dialog-titlebar.ui-widget-header.ui-corner-all.ui-helper-clearfix {display: none;} .customAddtoCart div#add-to-cart-dialog {min-height: 1px !important;} [dir=rtl] .ui-widget {font-family: 'Droid Arabic Kufi', 'droid_serifregular' !important;}</style>");

              $('body').append('<div id="add-to-cart-dialog" style="display:none;"><div style="margin: 13px 0;">' + json['text_cart_dialog'] + '</div></div>');

              $("#add-to-cart-dialog").dialog({
                  modal: true,
                  draggable: false,
                  resizable: false,
                  position: ['center', 'center'],
                  show: 'blind',
                  hide: 'blind',
                  width: 500,
                  dialogClass: 'ui-dialog-osx customAddtoCart',
                  buttons: [{
                      text: json['text_cart_dialog_continue'],
                      click: function() {
                          $(this).dialog("close");
                      }
                  },
                      {
                          text: json['text_cart_dialog_cart'],
                          click: function() {
                              window.location.href = json['cart_link'];
                          }
                      }
                  ]
              });
          }
      } 
    }
  });

  event.stopPropagation();
});
//--></script>
<?php if ($options) { ?>
<script type="text/javascript" src="catalog/view/javascript/jquery/ajaxupload.js"></script>
<?php foreach ($options as $option) { ?>
<?php if ($option['type'] == 'file') { ?>
<script type="text/javascript"><!--
new AjaxUpload('#button-option-<?php echo $option['product_option_id']; ?>', {
  action: 'index.php?route=product/product/upload',
  name: 'file',
  autoSubmit: true,
  responseType: 'json',
  onSubmit: function(file, extension) {
    $('#button-option-<?php echo $option['product_option_id']; ?>').after('<img src="catalog/view/theme/default/image/loading.gif" class="loading" style="padding-left: 5px;" />');
    $('#button-option-<?php echo $option['product_option_id']; ?>').attr('disabled', true);
  },
  onComplete: function(file, json) {
    $('#button-option-<?php echo $option['product_option_id']; ?>').attr('disabled', false);
    
    $('.error').remove();
    
    if (json['success']) {
      alert(json['success']);
      
      $('input[name=\'option[<?php echo $option['product_option_id']; ?>]\']').attr('value', json['file']);
    }
    
    if (json['error']) {
      $('#option-<?php echo $option['product_option_id']; ?>').after('<span class="error">' + json['error'] + '</span>');
    }
    
    $('.loading').remove(); 
  }
});
//--></script>
<?php } ?>
<?php } ?>
<?php } ?>
<script type="text/javascript"><!--
$('#review .pagination a').live('click', function() {
  $('#review').fadeOut('slow');
    
  $('#review').load(this.href);
  
  $('#review').fadeIn('slow');
  
  return false;
});     

$('#review').load('index.php?route=product/product/review&product_id=<?php echo $product_id; ?>');

$('#button-review').bind('click', function() {
  $.ajax({
    url: 'index.php?route=product/product/write&product_id=<?php echo $product_id; ?>',
    type: 'post',
    dataType: 'json',
    data: 'name=' + encodeURIComponent($('input[name=\'name\']').val()) + '&text=' + encodeURIComponent($('textarea[name=\'text\']').val()) + '&rating=' + encodeURIComponent($('input[name=\'rating\']:checked').val() ? $('input[name=\'rating\']:checked').val() : '') + '&captcha=' + encodeURIComponent($('input[name=\'captcha\']').val()),
    beforeSend: function() {
      $('.success, .warning').remove();
      $('#button-review').attr('disabled', true);
      $('#review-title').after('<div class="attention"><img src="catalog/view/theme/default/image/loading.gif" alt="" /> <?php echo $text_wait; ?></div>');
    },
    complete: function() {
      $('#button-review').attr('disabled', false);
      $('.attention').remove();
    },
    success: function(data) {
      if (data['error']) {
        $('#review-title').after('<div class="warning">' + data['error'] + '</div>');
      }
      
      if (data['success']) {
        $('#review-title').after('<div class="success">' + data['success'] + '</div>');
                
        $('input[name=\'name\']').val('');
        $('textarea[name=\'text\']').val('');
        $('input[name=\'rating\']:checked').attr('checked', '');
        $('input[name=\'captcha\']').val('');
      }
    }
  });
});
//--></script> 
<script type="text/javascript"><!--
$('#tabs a').tabs();
//--></script> 
<script type="text/javascript" src="catalog/view/javascript/jquery/ui/jquery-ui-timepicker-addon.js"></script> 
<script type="text/javascript"><!--
$(document).ready(function() {
  if ($.browser.msie && $.browser.version == 6) {
    $('.date, .datetime, .time').bgIframe();
  }

  $('.date').datepicker({dateFormat: 'yy-mm-dd'});
  $('.datetime').datetimepicker({
    dateFormat: 'yy-mm-dd',
    timeFormat: 'h:m'
  });
  $('.time').timepicker({timeFormat: 'h:m'});
});
//--></script> 

</section> 

<?php if( $SPAN[2] ): ?>
	<aside class="col-lg-<?php echo $SPAN[2];?> col-md-<?php echo $SPAN[2];?> col-sm-12 col-xs-12">	
		<?php echo $column_right; ?>
	</aside>
<?php endif; ?>
 
<?php echo $footer; ?>