<?php
   if(isset($is_product_item) && $is_product_item && !defined("ECFLASHSALE_ASSETS_LOADED") && $stylesheet && $script){
      echo '<link rel="stylesheet" type="text/css" href="'.$base.$stylesheet.'" />';
      echo '<script type="text/javascript" src="'.$base.$script.'"></script>';
      define("ECFLASHSALE_ASSETS_LOADED", 1);
   }

?>
<?php
  $product_name = isset($deal['name'])?$deal['name']:'';
  $product_price = isset($deal['special'])?$deal['special']:'';
  $product_old_price = isset($deal['price'])?$deal['price']:'';
  $discount = isset($deal['discount'])?$deal['discount']:'';
  $bought = isset($deal['bought'])?$deal['bought']:0;
  $quantity = isset($deal['quantity'])?$deal['quantity']:0;
  $save_price = isset($deal['save_price'])?$deal['save_price']:'';
  $date_end = isset($deal['date_end'])?$deal['date_end']:'';
  $save_price = isset($deal['save_price'])?$deal['save_price']:'0';
  $date_end_time = strtotime($date_end);

  $text_left = "";
  if($show_quantity) {
    $text_left = sprintf($this->language->get("text_left"), $quantity);
  }

  $text_save_discount = "";
  if($show_discount_price && $show_discount_percent) {
    $text_save_discount = sprintf($this->language->get("text_save_percent"), $save_price." (".$discount."%)");
  } elseif ($show_discount_price && !$show_discount_percent) {
    $text_save_discount = sprintf($this->language->get("text_save_percent"), $save_price);
  } elseif (!$show_discount_price && $show_discount_percent)  {
    $text_save_discount = sprintf($this->language->get("text_save_percent"), $discount."%");  
  }
  
?>
<?php
if($popup_mode): ?>
 <a id="ecflashsale_popup<?php echo $module_id;?>" href="#ecflashsale_mini_deal_<?php echo $module_id;?>" style="display:none;">flashsale</a>
 <div style="display:none;">
<?php endif; ?>
<div id="ecflashsale_mini_deal_<?php echo $module_id; ?>" class="mini-deal theme-green dailydeal-grid block-flashsale"> <span class="offer_title"><?php echo $this->language->get("text_limit_offer_end"); ?></span>
            <div id="UniversumCount">
              <div id="mini_deal_eccounter<?php echo $module_id; ?>" class="counter">
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
              $("#mini_deal_eccounter<?php echo $module_id; ?>").ecCountDown(ec_server_time);

      </script>
  <div class="count_info info_prod_left"><?php echo $text_left; ?></div>
  <div class="count_info_left info_prod_save"><?php echo $text_save_discount; ?></div>
</div>
<?php
if($popup_mode): ?>
 </div>
 <script type="text/javascript">
 $(document).ready(function(){
    $("#ecflashsale_popup<?php echo $module_id;?>").colorbox({title:"<?php echo $this->language->get("heading_title");?>", inline:true, width:"<?php echo (isset($popup_width) && $popup_width !='auto')?$popup_width:'50%';?>", overlayClose: true, opacity: 0.5});
    $("#ecflashsale_popup<?php echo $module_id;?>").click();
  });
</script>
<?php endif; ?>