<?php
   if(isset($is_product_item) && $is_product_item && !defined("ECDEALS_ASSETS_LOADED") && $stylesheet && $script){
      echo '<link rel="stylesheet" type="text/css" href="'.$base.$stylesheet.'" />';
echo '<script type="text/javascript" src="'.$base.$script.'"></script>';
define("ECDEALS_ASSETS_LOADED", 1);
}

?>
<?php if(!$is_expired || $show_expired_deal){ ?>
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
$date_start = isset($deal['date_start'])?$deal['date_start']:'';
$date_start_format = (isset($date_format) && $date_format)?date($date_format, strtotime($date_start)): $date_start;
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

?>
<div class="product-inner product-block" id="ecdeal<?php echo $module_id; ?>">
    <?php if ($deal['thumb']) { ?>
    <div class="image">
        <a href="<?php echo $deal['link']; ?>"><img src="<?php echo $deal['thumb']; ?>" title="<?php echo $deal['name']; ?>" alt="<?php echo $deal['name']; ?>" /></a>
        <?php if( $categoryPzoom ) { $zimage = str_replace( "cache/","", preg_replace("#-\d+x\d+#", "",  $deal['thumb'] ));  ?>
        <a href="<?php echo $zimage;?>" class="colorbox product-zoom" rel="colorbox" title="<?php echo $deal['name']; ?>"><span class="fa fa-search-plus"></span></a>
        <?php } ?>

        <?php if(isset($enable_discount) && $enable_discount){ ?>
            <div class="product_discount fixPNG"><?php echo $discount; ?><span>%</span></div>
        <?php } ?>
    </div>
    <?php } ?>

    <div class="product-meta detail-deal">
        <?php if(isset($show_stock_bar) && $show_stock_bar && !$is_upcomming) { ?>
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

        <?php if(isset($enable_buy_now) && $enable_buy_now) { ?>
        <?php if(!$is_expired && !$is_upcomming){ ?>
        <input type="button" class="button btn btn-default btn-dailydeal" style="float: none;margin-top: 5px;margin-bottom: 9px;" onclick="addToCart('<?php echo $product_id; ?>');" title="<?php echo $this->language->get("text_add_to_cart");?>" value="<?php echo $this->language->get("text_buy_now");?>">
        <?php } elseif($is_upcomming) { ?>
        <input type="button" class="button btn btn-default btn-dailydeal btn-upcomming" style="float: none;margin-top: 5px;margin-bottom: 9px;" title="<?php echo $this->language->get("text_soon");?>" value="<?php echo $this->language->get("text_soon");?>">
        <?php } else { ?>
        <input type="button" class="button btn btn-default btn-dailydeal btn-expired" style="float: none;margin-top: 5px;margin-bottom: 9px;" title="<?php echo $this->language->get("text_expired");?>" value="<?php echo $this->language->get("text_expired");?>">
        <?php } ?>
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

        <ul class="time-left">
            <?php if( $quantity > 0 && !$is_expired) { ?>

            <?php if($show_reward_point && $reward_points) { ?>

            <div class="extra_point"><?php echo $reward_points_text; ?></div>

            <?php } ?>

            <?php if($show_notify_message && $notify_message) { ?>

            <div class="notify_message">
                <ul class="notify">
                    <?php foreach($notify_message as $message) { ?>
                    <li><?php echo $message; ?></li>
                    <?php } ?>
                </ul>
            </div>

            <?php } ?>
            <?php } ?>

            <?php if($show_social){ ?>
            <li class="socials-share">
                <ul>
                    <li><?php echo $this->language->get("text_share");?></li>
                    <li>
                        <?php
                            $title=urlencode($product_name);
                            $url=urlencode($product_link);
                            $summary=urlencode($product_description);
                            $image=urlencode($product_image);
                        ?>
                        <a class="share facebook" title="Facebook" onClick="window.open('http://www.facebook.com/sharer.php?s=100&amp;p[title]=<?php echo $title;?>&amp;p[summary]=<?php echo $summary;?>&amp;p[url]=<?php echo $url; ?>&amp;&p[images][0]=<?php echo $image;?>', 'sharer', 'toolbar=0,status=0,width=548,height=325');" target="_parent" href="javascript: void(0)" id="$Button" rel="nofollow">Facebook</a>
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

            <?php if( !$is_upcomming ) { ?>

            <li><label class="expire_date"><?php echo $this->language->get("text_expired_date").$date_end_format; ?></label></li>

            <?php } else { ?>

            <li><label class="expire_date"><?php echo $this->language->get("text_start_date").$date_start_format; ?></label></li>

            <?php } ?>
        </ul>
    </div>
</div>

<?php if($date_end && $module_id && $date_end_time !== false){ ?>
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
        show_empty_day : true
    };
    $("#eccounter<?php echo $module_id; ?>").ecCountDown(ec_server_time);

</script>
<?php } ?>

<?php } ?>
