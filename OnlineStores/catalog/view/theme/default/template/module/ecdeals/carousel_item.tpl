<?php
   if(isset($is_product_item) && $is_product_item && !defined("ECDEALS_ASSETS_LOADED") && $stylesheet && $script){
      echo '<link rel="stylesheet" type="text/css" href="'.$base.$stylesheet.'" />';
      echo '<script type="text/javascript" src="'.$base.$script.'"></script>';
      define("ECDEALS_ASSETS_LOADED", 1);
   }

?>
<?php if(!$is_expired){ ?>
<?php
  $product_id = isset($deal['product_id'])?$deal['product_id']:0;
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
$date_end_format = (isset($date_format) && $date_format)?date($date_format, $date_end_time): $date_end;
  $label = isset($deal['label'])?$deal['label']:'';
  $reward_points = isset($deal['reward'])?$deal['reward']:0;
  $reward_points_text = $this->language->get("text_reward_points");
  $reward_points_text = sprintf($reward_points_text, $reward_points);

  $is_upcomming = isset($deal['is_upcomming'])?$deal['is_upcomming']:false;

$bought = isset($bought)?(int)$bought: 0;
$total_quantity = (int)$quantity + $bought;
$total_quantity = !empty($total_quantity)?$total_quantity:1;
$percent = ($bought / $total_quantity) * 100;
$percent = round(floor($percent * 100) / 100, 2);
$percent = empty($quantity)?100: $percent;

$bar_class = $this->getBarClass( $percent );

  $notify_message = isset($deal['notify_message'])?$deal['notify_message']:array();
  $module_id = "carousel".$module_id;
?>
<a href="<?php echo $product_link;?>">
<div class="product-inner" id="ecdeal<?php echo $module_id; ?>" style="    margin-top: 0px;">
    <div class="image">
        <?php if(isset($enable_discount) && $enable_discount){ ?>
        <div class="product_discount fixPNG"><?php echo $discount; ?><span>%</span></div>
        <?php } ?>
        <a href="<?php echo $product_link;?>">
            <img src="<?php echo $product_image; ?>" alt="<?php echo $product_name; ?>">
        </a>
    </div>
    <a href="<?php echo $product_link;?>">
    <div class="product-meta detail-deal">
        <?php if( !$is_upcomming) { ?>
        <div class="stock_container" style="margin-top: 10px;">
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

        <div class="name"><a href="<?php echo $deal['link']; ?>"><?php echo $deal['name']; ?></a></div>

        <div class="rating">
            <?php if ($deal['rating']) { ?>
            <img src="catalog/view/theme/<?php echo $this->config->get('config_template');?>/image/stars-<?php echo $deal['rating']; ?>.png" alt="<?php echo $deal['reviews']; ?>" />
            <?php } ?>
        </div>

        <?php if(isset($enable_discount) && $enable_discount){ ?>
        <div class="you-save">
            <label><?php echo $this->language->get("text_you_save");?>&nbsp;</label><span class="save-price"><?php echo $save_price;?></span>
        </div>
        <?php } ?>

        <?php if ($deal['price']) { ?>
        <div class="price">
            <?php if (!$deal['special']) { ?>
            <?php echo $deal['price']; ?>
            <?php } else { ?>
            <span class="price-old"><?php echo $deal['price']; ?></span> <span class="price-new"><?php echo $deal['special']; ?></span>
            <?php } ?>
            <?php if ($deal['tax']) { ?>
            <br />
            <span class="price-tax"><?php echo $text_tax; ?> <?php echo $deal['tax']; ?></span>
            <?php } ?>
        </div>
        <?php } ?>

        <div class="meta" style="margin-top: 10px;">
            <div style="text-align: center;"><label><?php echo $this->language->get("text_time_left");?></label></div>
            <?php if($date_end_time !== false && !$is_expired && !$is_upcomming) { ?>
            <div class="block-deal" style="box-shadow: none !important;">
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
    </div>
    </a>
</div>
</a>
<?php if($date_end && $module_id && $date_end_time !== false){ ?>
 <script type="text/javascript">
        var ec_server_time = { 
            target_date : "<?php echo date('m/d/Y G:i:s', strtotime($date_end));?>",
            callback : '',

            id_day  : '#cd_day-' + "<?php echo $module_id; ?>",
            id_hour  : '#cd_hour-' + "<?php echo $module_id; ?>",
            id_minute  : '#cd_min-' + "<?php echo $module_id; ?>",
            id_second  : '#cd_sec-' + "<?php echo $module_id; ?>",
            
            label_day : '#lb_day'+"<?php echo $module_id; ?>",
            label_hour : '#lb_hour'+"<?php echo $module_id; ?>",
            label_minute : '#lb_minute'+"<?php echo $module_id; ?>",
            label_second : '#lb_second'+"<?php echo $module_id; ?>",
      
      label_day_value : '<?php echo $this->language->get("text_day");?>',
            label_hour_value : '<?php echo $this->language->get("text_hours");?>',
            label_minute_value : '<?php echo $this->language->get("text_mins");?>',
            label_second_value : '<?php echo $this->language->get("text_secs");?>',
            show_empty_day : true
        };
        $("#eccounter<?php echo $module_id; ?>").ecCountDown(ec_server_time);

</script>
<?php } ?>

<?php } ?>
