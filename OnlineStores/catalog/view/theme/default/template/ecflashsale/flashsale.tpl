<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <?php if ($thumb || $description) { ?>
  <div class="flashsale-info category-info">
    <?php if ($thumb) { ?>
    <div class="image"><img src="<?php echo $thumb; ?>" alt="<?php echo $heading_title; ?>" /></div>
    <?php } ?>
    <?php if ($description) { ?>
    <?php echo $description; ?>
    <?php } ?>
  </div>
  <?php } ?>
 <div class="<?php echo $prefix;?> block block-flashsale<?php echo (isset($no_border) && $no_border)?' noborder':'';?>" id="ecflashsale<?php echo $module_id; ?>">
     <div class="block-content">
      <div  class="mini-products-list">
        <?php if($show_sale_off): ?>                   
                    <div class="flashsale_info">
                <?php echo $text_discount;?>
              </div>
        <?php endif; ?>
        <?php if($show_expire_date): ?>
        <div class="flashsale-date-box">
          <?php echo $this->language->get("text_expired_date")." <span>".$date_end."</span>"; ?>                        
        </div>
        <?php endif; ?>
        <?php if($show_viewmore): ?>
        <div class="flashsale-button">
          <a href="<?php echo $flashsale_link; ?>" class="button button-showmore"><?php echo $text_show_detail; ?><span class="arrow-icon"></span></a>
        </div>
        <?php endif; ?>
        <?php if(!$is_expired): ?>
            <?php if( $show_countdown) { ?>
            <!-- Countdown Javascript -->
                <div id="eccounter_flashsale_<?php echo $module_id; ?>" class="counter">
                    <ul class="countdown">  
                        <li class="first">
                            <div class="countdown_num" id="cd_day-flashsale-<?php echo $module_id; ?>"></div><div id="lb_dayflashsale-<?php echo $module_id; ?>"></div></li>
                        <li>
                            <div class="countdown_num" id="cd_hour-flashsale-<?php echo $module_id; ?>"></div><div id="lb_hourflashsale-<?php echo $module_id; ?>"></div></li>
                        <li>
                            <div class="countdown_num" id="cd_min-flashsale-<?php echo $module_id; ?>"></div><div id="lb_minuteflashsale-<?php echo $module_id; ?>"></div></li>
                        <li class="last">
                            <div class="countdown_num" id="cd_sec-flashsale-<?php echo $module_id; ?>"></div><div id="lb_secondflashsale-<?php echo $module_id; ?>"></div></li>
                    </ul>
                    <div class="clear"><span>&nbsp;</span></div>
                </div>
              <?php } ?>
          <?php else: ?>
            <div class="expired"><?php echo $this->language->get("text_expired_sale");?></div>
          <?php endif; ?>
        
        <br class="clear clr"/>
        </div>
  
    </div>
</div>
 <?php if( $show_countdown) { ?>
 <script type="text/javascript">
        var ec_server_time = { 
            target_date : "<?php echo date('m/d/Y G:i:s', strtotime($date_end));?>",
            callback : '',

            id_day  : '#cd_day-flashsale-' + <?php echo $module_id; ?>,
            id_hour  : '#cd_hour-flashsale-' + <?php echo $module_id; ?>,
            id_minute  : '#cd_min-flashsale-' + <?php echo $module_id; ?>,
            id_second  : '#cd_sec-flashsale-' + <?php echo $module_id; ?>,
            
            label_day : '#lb_dayflashsale-'+<?php echo $module_id; ?>,
            label_hour : '#lb_hourflashsale-'+<?php echo $module_id; ?>,
            label_minute : '#lb_minuteflashsale-'+<?php echo $module_id; ?>,
            label_second : '#lb_secondflashsale-'+<?php echo $module_id; ?>,
      
            label_day_value : '<?php echo $this->language->get("text_day");?>',
            label_hour_value : '<?php echo $this->language->get("text_hours");?>',
            label_minute_value : '<?php echo $this->language->get("text_mins");?>',
            label_second_value : '<?php echo $this->language->get("text_secs");?>',
            show_empty_day: true
        };
        $("#eccounter_flashsale_<?php echo $module_id; ?>").ecCountDown(ec_server_time);
</script>
<?php } ?>
  <div class="form">
    <?php echo $this->language->get("text_deal_search"); ?><input type="text" value="<?php echo isset($search)?$search:''; ?>" name="search"> <input type="button" class="button" id="button-search" value="<?php echo $this->language->get("text_search"); ?>">
  </div>
  <br/>
  <?php if ($products) { ?>
  
  <div class="product-filter">
    <div class="display"><b><?php echo $text_display; ?></b> <?php echo $text_list; ?> <b>/</b> <a onclick="display('grid');"><?php echo $text_grid; ?></a></div>
    <div class="limit"><b><?php echo $text_limit; ?></b>
      <select onchange="location = this.value;">
        <?php foreach ($limits as $limit_item) { ?>
        <?php if ($limit_item['value'] == $limit) { ?>
        <option value="<?php echo $limit_item['href']; ?>" selected="selected"><?php echo $limit_item['text']; ?></option>
        <?php } else { ?>
        <option value="<?php echo $limit_item['href']; ?>"><?php echo $limit_item['text']; ?></option>
        <?php } ?>
        <?php } ?>
      </select>
    </div>
    <div class="sort"><b><?php echo $text_sort; ?></b>
      <select onchange="location = this.value;">
        <?php foreach ($sorts as $sort_item) { ?>
        <?php if ($sort_item['value'] == $sort . '-' . $order) { ?>
        <option value="<?php echo $sort_item['href']; ?>" selected="selected"><?php echo $sort_item['text']; ?></option>
        <?php } else { ?>
        <option value="<?php echo $sort_item['href']; ?>"><?php echo $sort_item['text']; ?></option>
        <?php } ?>
        <?php } ?>
      </select>
    </div>
  </div>
  <div class="product-compare"><a href="<?php echo $compare; ?>" id="compare-total"><?php echo $text_compare; ?></a></div>
  <div class="product-list">
    <?php foreach ($products as $product) { ?>
    
    <div>
      <?php if ($product['thumb']) { ?>
      <div class="image"><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" /></a><a class="ecquickviewproduct-colorbox" href="<?php echo $quickview_link; ?>" style="display:none;"><?php echo $this->language->get('text_quick_view'); ?></a></div>
      <?php } ?>
      <div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
      <div class="description"><?php echo $product['description']; ?></div>
      <?php if ($product['price']) { ?>
      <div class="price">
        <?php if (!$product['special']) { ?>
        <?php echo $product['price']; ?>
        <?php } else { ?>
        <span class="price-old"><?php echo $product['price']; ?></span> <span class="price-new"><?php echo $product['special']; ?></span>
        <?php } ?>
        <?php if ($product['tax']) { ?>
        <br />
        <span class="price-tax"><?php echo $text_tax; ?> <?php echo $product['tax']; ?></span>
        <?php } ?>
      </div>
      <?php } ?>
      <?php if ($product['rating']) { ?>
      <div class="rating"><img src="catalog/view/theme/default/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
      <?php } ?>
      <div class="cart">
        <input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
      </div>
      <div class="wishlist"><a onclick="addToWishList('<?php echo $product['product_id']; ?>');"><?php echo $button_wishlist; ?></a></div>
      <div class="compare"><a onclick="addToCompare('<?php echo $product['product_id']; ?>');"><?php echo $button_compare; ?></a></div>
    </div>
    <?php } ?>
  </div>
  <div class="pagination"><?php echo $pagination; ?></div>
  <?php } ?>
  <?php if (!$products) { ?>
  <div class="content"><?php echo $text_empty; ?></div>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php } ?>
  <?php echo $content_bottom; ?></div>
<script type="text/javascript"><!--
$('#content input[name=\'search\']').keydown(function(e) {
  if (e.keyCode == 13) {
    $('#button-search').trigger('click');
  }
});

$('#button-search').bind('click', function() {
  url = 'index.php?route=ecflashsale/flashsale&ecflashsale_id=<?php echo $ecflashsale_id; ?>';
  
  var search = $('#content input[name=\'search\']').attr('value');
  
  if (search) {
    url += '&search=' + encodeURIComponent(search);
  }
  
  location = url;
});
//--></script> 
<script type="text/javascript"><!--
function display(view) {
  if (view == 'list') {
    $('.product-grid').attr('class', 'product-list');
    
    $('.product-list > div').each(function(index, element) {
      html  = '<div class="right">';
      html += '  <div class="cart">' + $(element).find('.cart').html() + '</div>';
      html += '  <div class="wishlist">' + $(element).find('.wishlist').html() + '</div>';
      html += '  <div class="compare">' + $(element).find('.compare').html() + '</div>';
      html += '</div>';     
      
      html += '<div class="left">';
      
      var image = $(element).find('.image').html();
      
      if (image != null) { 
        html += '<div class="image">' + image + '</div>';
      }
      
      var price = $(element).find('.price').html();
      
      if (price != null) {
        html += '<div class="price">' + price  + '</div>';
      }
          
      html += '  <div class="name">' + $(element).find('.name').html() + '</div>';
      html += '  <div class="description">' + $(element).find('.description').html() + '</div>';
      
      var rating = $(element).find('.rating').html();
      
      if (rating != null) {
        html += '<div class="rating">' + rating + '</div>';
      }
        
      html += '</div>';
            
      $(element).html(html);
    });   
    
    $('.display').html('<b><?php echo $text_display; ?></b> <?php echo $text_list; ?> <b>/</b> <a onclick="display(\'grid\');"><?php echo $text_grid; ?></a>');
    
    $.totalStorage('display', 'list'); 
  } else {
    $('.product-list').attr('class', 'product-grid');
    
    $('.product-grid > div').each(function(index, element) {
      html = '';
      
      var image = $(element).find('.image').html();
      
      if (image != null) {
        html += '<div class="image">' + image + '</div>';
      }
      
      html += '<div class="name">' + $(element).find('.name').html() + '</div>';
      html += '<div class="description">' + $(element).find('.description').html() + '</div>';
      
      var price = $(element).find('.price').html();
      
      if (price != null) {
        html += '<div class="price">' + price  + '</div>';
      }
      
      var rating = $(element).find('.rating').html();
      
      if (rating != null) {
        html += '<div class="rating">' + rating + '</div>';
      }
            
      html += '<div class="cart">' + $(element).find('.cart').html() + '</div>';
      html += '<div class="wishlist">' + $(element).find('.wishlist').html() + '</div>';
      html += '<div class="compare">' + $(element).find('.compare').html() + '</div>';
      
      $(element).html(html);
    }); 
          
    $('.display').html('<b><?php echo $text_display; ?></b> <a onclick="display(\'list\');"><?php echo $text_list; ?></a> <b>/</b> <?php echo $text_grid; ?>');
    
    $.totalStorage('display', 'grid');
  }
}

view = $.totalStorage('display');

if (view) {
  display(view);
} else {
  display('list');
}
//--></script> 
<?php echo $footer; ?>