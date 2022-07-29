<?php
   if(isset($is_product_item) && $is_product_item && !defined("ECDEAL_ASSETS_LOADED") && $stylesheet && $script){
      echo '<link rel="stylesheet" type="text/css" href="'.$base.$stylesheet.'" />';
      echo '<script type="text/javascript" src="'.$base.$script.'"></script>';
      define("ECDEAL_ASSETS_LOADED", 1);
   }

?>
<?php if((!$is_expired || $show_expired_deal) && $deal){ ?>
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
  $date_start = isset($deal['date_start'])?$deal['date_start']:'';
  $date_end_time = strtotime($date_end);

  $date_start_format = (isset($date_format) && $date_format)?date($date_format, strtotime($date_start)): $date_start;
  $date_end_format = (isset($date_format) && $date_format)?date($date_format, $date_end_time): $date_end;

  $label = isset($deal['label'])?$deal['label']:'';
  $reward_points = isset($deal['reward'])?$deal['reward']:0;
  $reward_points_text = $this->language->get("text_reward_points");
  $reward_points_text = sprintf($reward_points_text, $reward_points);

  $notify_message = isset($deal['notify_message'])?$deal['notify_message']:array();

 $bought = isset($bought)?(int)$bought: 0;
 $total_quantity = (int)$quantity + $bought;
 $total_quantity = !empty($total_quantity)?$total_quantity:1;
 $percent = ($bought / $total_quantity) * 100;
 $percent = round(floor($percent * 100) / 100, 2);
 $percent = empty($quantity)?100: $percent;

 $bar_class = $this->getBarClass( $percent );
 $is_upcomming = isset($deal['is_upcomming'])?$deal['is_upcomming']:false;

?>
<div class="<?php echo $prefix;?> detail-deal dailydeal-grid block-deal noborder" id="ecdeal<?php echo $module_id; ?>">
<div class="item" style="<?php echo isset($deal_block_width)?'width:'.$deal_block_width:'';?>">
  <?php if(isset($enable_deal_image) && $enable_deal_image) { ?>
   <a class="product-image" title="<?php echo $product_name; ?>" href="<?php echo $product_link;?>">
    <img src="<?php echo $product_image; ?>" width="<?php echo isset($deal_image_width)?$deal_image_width:$image_width; ?>" height="<?php echo isset($deal_image_height)?$deal_image_height:$image_height; ?>" alt="<?php echo $product_name; ?>" />
  </a>
  <?php } ?>
<div class="bottom-grid-dailydeal">

<?php if(isset($enable_deal_name) && $enable_deal_name) { ?>
<h2 class="product-name dailydeal-sidebar-product-name"><a title="<?php echo $product_name; ?>" href="<?php echo $product_link;?>"><?php echo $product_name; ?></a>
</h2>
<?php } ?>

<?php if(isset($enable_deal_price) && $enable_deal_price && 1 == 0) { ?>
  <ul class="dailydeal-price">                                
      <li class="special-price">
        <span class="price"><?php echo $product_price;?></span>             </li>
      <li class="old-price"><span class="price"><?php echo $product_old_price;?></span></li>
  </ul>
<?php } ?>
    <div class="wrap-grid-action" style="padding-bottom: 15px;">

       <?php if(isset($enable_buy_now) && $enable_buy_now){ ?>
              <?php if( (int)$quantity > 0 && !$is_expired && !$is_upcomming) { ?>
              <input type="button" class="button btn-dailydeal" onclick="addToCart('<?php echo $product_id; ?>');" title="<?php echo $this->language->get("text_add_to_cart");?>" value="<?php echo $this->language->get("text_buy_now");?>">

              <?php } elseif($is_upcomming) { ?>

              <input type="button" class="button btn-dailydeal btn-upcomming" title="<?php echo $this->language->get("text_soon");?>" value="<?php echo $this->language->get("text_soon");?>">

              <?php } else { ?>

              <input type="button" class="button btn-dailydeal btn-expired" title="<?php echo $this->language->get("text_expired");?>" value="<?php echo $this->language->get("text_expired");?>">

              <?php } ?>
        <?php } ?>
        <?php if(isset($enable_discount) && $enable_discount){ ?>
        <ul class="save-sold">
                <li class="special-price"><label><?php echo $this->language->get("text_you_save");?></label><span class="price"><?php echo $save_price;?></span></li>
                <li class="special-price lastspecial"><label><?php echo $this->language->get("text_bought");?></label><span class="price"><?php echo $bought; ?> /<?php echo $quantity; ?></span></li>
        </ul>
        <?php } ?>

    </div>
    <?php if( $quantity > 0 && !$is_expired) { ?>
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
    <?php } ?>
    <?php if(isset($show_stock_bar) && $show_stock_bar && !$is_upcomming) { ?>
    <div class="stock_container">
      <div class="sold_percent">
        <span><?php echo sprintf($this->language->get("text_sold_percent"), $percent."%");?> </span>
      </div>
      <div id="bar" style="height: 10px;" title="<?php echo $percent;?>%">
        <span style="width: <?php echo $percent; ?>%;" class="<?php echo $bar_class; ?>"></span>
      </div>
      <div class="bar_info"><div class="start_number">0%</div><div class="end_number">100%</div></div>
      <br/>
    </div>
    <?php } ?>
    <?php if($quantity > 0) { ?>
      <div class="time-left" style="margin-top: 15px;">
          <?php if( $is_upcomming ) { ?>

          <div><label class="expire_date"><?php echo $this->language->get("text_start_date").$date_start_format; ?></label></div>

          <?php } else { ?>

          <div><label class="expire_date"><?php echo $this->language->get("text_expired_date").$date_end_format; ?></label></div>

          <?php } ?>

                <div><?php if($rating && $show_rating){ ?>
                <div class="rating"><img src="catalog/view/theme/default/image/stars-<?php echo $rating; ?>.png" alt="<?php echo $reviews; ?>" /> <a href="<?php echo $product_link; ?>"><?php echo $reviews; ?></a></div>
               <?php } ?></div>
               <?php if($show_social){ ?>
                <div class="socials-share" style="margin-top:10px;">
                  <ul>
                      <li><?php echo $this->language->get("text_share");?></li>
                      <li>
                        <?php
                            $title=urlencode($product_name);
                            $url=urlencode($product_link);
                            $product_link2=str_replace("&amp;", "&", $product_link);
                            $url2=urlencode($product_link2);
                            $summary=urlencode($product_description);
                            $image=urlencode($product_image);
                        ?>
                        <a class="share facebook" title="Facebook" onClick="window.open('http://www.facebook.com/sharer.php?s=100&amp;p[title]=<?php echo $title;?>&amp;p[summary]=<?php echo $summary;?>&amp;p[url]=<?php echo $url; ?>&amp;&p[images][0]=<?php echo $image;?>', 'sharer', 'toolbar=0,status=0,width=548,height=325');" target="_parent" href="javascript: void(0)" id="$Button" rel="nofollow">Facebook</a>
                      </li>
                      <li>
                        <a class="share twitter" title="Twitter" href="http://twitter.com/home?status=<?php echo str_replace(" ","%20", $product_name)." ". $url2; ?>" id="$Button" rel="nofollow" target="_BLANK">Twitter</a>
                      </li>
                      <li>
                        <a class="share plus" title="Google Plus" href="https://plus.google.com/share?url=<?php echo $url2; ?>" onclick="javascript:window.open(this.href,
  '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;" target="_BLANK" rel="nofollow">Google</a>
                      </li>
                      <li>
                        <a class="share pinterest" title="Pinterest" href="http://pinterest.com/pin/create/button/?url=<?php echo $product_link; ?>&media=<?php echo $product_image;?>&description=<?php echo str_replace(" ","%20", $product_name." - ".$product_description);?>"  target="_BLANK" rel="nofollow">Pinterest</a>
                      </li>
                    </ul>
                </div>
                <?php } ?>
                <br><br><br>
                <div><label><?php echo $this->language->get("text_time_left");?></label></div>
                <!-- Countdown Javascript -->
                <?php if($date_end_time !== false && !$is_expired && !$is_upcomming) { ?>

                    <div>
                    <div id="eccounter<?php echo $module_id; ?>" class="counter">
                        <ul class="countdown">  
                            <li class="first">
                                <div class="countdown_num" id="cd_day-<?php echo $module_id; ?>"></div><div id="lb_day<?php echo $module_id; ?>"></div></li>
                            <li>
                                <div class="countdown_num" id="cd_hour-<?php echo $module_id; ?>"></div><div id="lb_hour<?php echo $module_id; ?>"></div></li>
                            <li>
                                <div class="countdown_num" id="cd_min-<?php echo $module_id; ?>"></div><div id="lb_minute<?php echo $module_id; ?>"></div></li>
                            <li class="last">
                                <div class="countdown_num" id="cd_sec-<?php echo $module_id; ?>"></div><div id="lb_second<?php echo $module_id; ?>"></div></li>
                        </ul>
                        <div class="clear"><span>&nbsp;</span></div>
                    </div>
                    </div>

                <?php } elseif( $is_upcomming ) { ?>

                <div><div class="counter upcomming"><span><?php echo $this->language->get("text_deal_upcomming"); ?></span></div></div>
               
               <?php } elseif( !$date_end_time && !$is_expired && !$is_upcomming ) { ?>
               
                <?php } else { ?>
                
                <div><div class="counter expired"><span><?php echo $this->language->get("text_deal_expired"); ?></span></div></div>
                
                <?php } ?>
      </div>

      <?php } else {
        echo '<span>'.$this->language->get("text_sold_out").'</span>';
      } ?>
  </div>
  <div class="bg-bottom-dailydeal">&nbsp;</div>
</div>
</div>
<?php if($date_end && $module_id && $quantity > 0 && $date_end_time !== false ){ ?>
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

<?php } ?>
