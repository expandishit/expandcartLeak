<?php
   if(isset($is_product_item) && $is_product_item && !defined("ECFLASHSALE_ASSETS_LOADED") && $stylesheet && $script){
      echo '<link rel="stylesheet" type="text/css" href="'.$base.$stylesheet.'" />';
      echo '<script type="text/javascript" src="'.$base.$script.'"></script>';
      define("ECFLASHSALE_ASSETS_LOADED", 1);
   }

?>
<?php if(!$is_expired){ ?>
<?php
  $product_name = isset($deal['name'])?$deal['name']:'';
  $product_description = isset($deal['description'])?$deal['description']:'';
  $product_description = strip_tags($product_description);
  $product_image = isset($deal['thumb'])?$deal['thumb']:'';
  $product_link = isset($deal['link'])?$deal['link']:'';
  $product_price = isset($deal['special'])?$deal['special']:'';
  $product_old_price = isset($deal['price'])?$deal['price']:'';
  $discount = isset($deal['discount'])?$deal['discount']:'';
  $bought = isset($deal['bought'])?$deal['bought']:0;
  $quantity = isset($deal['quantity'])?$deal['quantity']:0;
  $save_price = isset($deal['save_price'])?$deal['save_price']:'';
  $reviews = isset($deal['reviews'])?$deal['reviews']:'';
  $rating = isset($deal['rating'])?$deal['rating']:'';
  //$discount_text = $this->language->get("text_discount_deal");
  //$discount = sprintf($discount_text, $discount);
  $date_end = isset($deal['date_end'])?$deal['date_end']:'';
  $date_end_time = strtotime($date_end);
  $label = isset($deal['label'])?$deal['label']:'';
  $reward_points = isset($deal['reward'])?$deal['reward']:0;
  $reward_points_text = $this->language->get("text_reward_points");
  $reward_points_text = sprintf($reward_points_text, $reward_points);

  $notify_message = isset($deal['notify_message'])?$deal['notify_message']:array();

?>
<div class="<?php echo $prefix;?> listing-deal dailydeal-grid block-flashsale<?php echo (isset($no_border) && $no_border)?' noborder':'';?>" id="ecflashsale<?php echo $module_id; ?>">
      <div class="meta" style="display:none">
        <?php if(isset($enable_discount) && $enable_discount){ ?>
         <div class="buy_number"><span><?php echo $bought; ?></span> <?php echo $this->language->get("text_bought");?></div>
         <?php } ?>
         <?php if($date_end_time !== false) { ?>
         <div class="time">                    
            <span id="eccounter<?php echo $module_id; ?>" class="counter key hasCountdown">
                 <span class="countdown_num" id="cd_day-<?php echo $module_id; ?>"></span> : <span class="countdown_num" id="cd_hour-<?php echo $module_id; ?>"></span> : <span class="countdown_num" id="cd_min-<?php echo $module_id; ?>"></span> : <span class="countdown_num" id="cd_sec-<?php echo $module_id; ?>"></span>
            </span>
          </div>
          <?php } ?>
      </div>
       
      <div class="thumb">
       <?php if(isset($enable_deal_image) && $enable_deal_image) { ?>
         <a class="product-image" title="<?php echo $product_name; ?>" href="<?php echo $product_link;?>">
          <img src="<?php echo $product_image; ?>" width="<?php echo isset($deal_image_width)?$deal_image_width:$image_width; ?>" height="<?php echo isset($deal_image_height)?$deal_image_height:$image_height; ?>" alt="<?php echo $product_name; ?>" />
        </a>
        <?php } ?>
        <?php if(isset($enable_discount) && $enable_discount){ ?>
        <div class="type-voucher-star discount" style="display:none">
          <label><?php echo $this->language->get("text_you_save");?></label><span class="price"><?php echo $save_price;?></span>   
        </div>
        <?php } ?>
         <?php if(isset($enable_discount) && $enable_discount){ ?>
        <div class="product_discount fixPNG"><?php echo $discount; ?><span>%</span></div>
        <?php } ?>
      </div>
      <div class="title">
        <?php if(isset($enable_deal_name) && $enable_deal_name) { ?>
        <h2 class="product-name dailydeal-sidebar-product-name"><a title="<?php echo $product_name; ?>" href="<?php echo $product_link;?>"><?php echo $product_name; ?></a>
        </h2>
        <?php } ?>
        <?php if(isset($enable_deal_price) && $enable_deal_price) { ?>
        <span class="sell-price price"><?php echo $product_price;?></span>
        <span class="original-price price"><?php echo $product_old_price;?></span>
        <?php } ?>
        <?php if(isset($enable_buy_now) && $enable_buy_now){ ?>
        <input type="button" class="button btn-dailydeal" onclick="addToCart('<?php echo $product_id; ?>');" title="<?php echo $this->language->get("text_add_to_cart");?>" value="<?php echo $this->language->get("text_buy_now");?>">

        <?php } ?>
        <br class="clr clear"/>
        <?php if($show_reward_point && $reward_points){ ?>
        <div class="extra_point"><?php echo $reward_points_text; ?></div>
        <?php } ?>
        <?php if($show_notify_message && $notify_message){ ?>
        <div class="notify_message">
          <ul class="notify">
          <?php foreach($notify_message as $message){ ?>
            <li><?php echo $message; ?></li>
          <?php } ?>
         </ul>
        </div>
        <?php } ?>
        
        <ul class="time-left">
                <li><?php if($rating && $show_rating){ ?>
                <div class="rating"><img src="catalog/view/theme/default/image/stars-<?php echo $rating; ?>.png" alt="<?php echo $reviews; ?>" /> <a href="<?php echo $product_link; ?>"><?php echo $reviews; ?></a></div>
               <?php } ?></li>
               <?php if($show_social){ ?>
                <li class="socials-share">
                  <ul>
                      <li><?php echo $this->language->get("text_share");?></li>
                      <li>
                         <a class="share facebook" title="Facebook" href="http://www.facebook.com/sharer/sharer.php?s=100&p[url]=<?php echo $product_link; ?>&p[images][0]=<?php echo $product_image; ?>&p[title]=<?php echo str_replace(" ","%20", $product_name);?>&p[summary]=<?php echo str_replace(" ","%20", $product_description);?>" id="$Button" rel="nofollow" target="_BLANK">Facebook</a>
                      </li>
                      <li>
                        <a class="share twitter" title="Twitter" href="http://twitter.com/home?status=<?php echo str_replace(" ","%20", $product_name)." ". $product_link; ?>" id="$Button" rel="nofollow" target="_BLANK">Twitter</a>
                      </li>
                      <li>
                        <a class="share plus" title="Google Plus" href="https://plus.google.com/share?url=<?php echo $product_link; ?>" target="_BLANK" rel="nofollow">Google</a>
                      </li>
                      <li>
                        <a class="share pinterest" title="Pinterest" href="http://pinterest.com/pin/create/button/?url=<?php echo $product_link; ?>&media=<?php echo $product_image;?>&description=<?php echo str_replace(" ","%20", $product_name." - ".$product_description);?>"  target="_BLANK" rel="nofollow">Pinterest</a>
                      </li>
                    </ul>
                </li>
                <?php } ?>
                <?php if($date_end_time !== false) { ?>
                 <li><label class="expire_date"><?php echo $this->language->get("text_expired_date").$date_end; ?></label></li>
                <?php } else { ?>
                 <li><label class="expire_date"><?php echo $this->language->get("text_never_expired_date"); ?></label></li>
                <?php } ?>

      </ul>
      </div>
</div>
<script type="text/javascript">
  $(document).ready(function() {
    $("#ecflashsale<?php echo $module_id; ?>").hover(function(){
      $(this).find(".meta").fadeIn("slow");
      $(this).find(".discount").fadeIn("slow");
    },
    function(){
      $(this).find(".meta").hide();
      $(this).find(".discount").hide();
    });
});
</script>
<?php if($date_end && $module_id ){ ?>
 <script type="text/javascript">
        var ec_server_time = { 
            target_date : "<?php echo date('m/d/Y G:i:s', strtotime($date_end));?>",
            callback : '',

            id_day  : '#cd_day-' + <?php echo $module_id; ?>,
            id_hour  : '#cd_hour-' + <?php echo $module_id; ?>,
            id_minute  : '#cd_min-' + <?php echo $module_id; ?>,
            id_second  : '#cd_sec-' + <?php echo $module_id; ?>,
            
            label_day : '#lb_day'+<?php echo $module_id; ?>,
            label_hour : '#lb_hour'+<?php echo $module_id; ?>,
            label_minute : '#lb_minute'+<?php echo $module_id; ?>,
            label_second : '#lb_second'+<?php echo $module_id; ?>,
      
      label_day_value : '<?php echo $this->language->get("text_day");?>',
            label_hour_value : '<?php echo $this->language->get("text_hours");?>',
            label_minute_value : '<?php echo $this->language->get("text_mins");?>',
            label_second_value : '<?php echo $this->language->get("text_secs");?>',
            show_empty_day: true
        };
        $("#eccounter<?php echo $module_id; ?>").ecCountDown(ec_server_time);

</script>
<?php } ?>

<?php }else{ ?>

    <div class="expired"><?php echo $this->language->get("text_expired_deal");?></div>

<?php }?>
