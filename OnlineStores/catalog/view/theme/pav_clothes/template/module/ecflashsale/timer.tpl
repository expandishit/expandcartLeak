<?php
   if(!defined("ECFLASHSALE_ASSETS_LOADED") && $stylesheet && $script){
      echo '<link rel="stylesheet" type="text/css" href="'.$base.$stylesheet.'" />';
      echo '<script type="text/javascript" src="'.$base.$script.'"></script>';
      define("ECFLASHSALE_ASSETS_LOADED", 1);
   }

?>

<?php
  $date_end = isset($deal['date_end'])?$deal['date_end']:'';
  $date_start = isset($deal['date_start'])?$deal['date_start']:'';
  $date_end_time = strtotime($date_end);
  $quantity = isset($deal['quantity'])?$deal['quantity']:0;

  $module_id = $deal['product_id'].rand().time();
?>
<?php if($quantity > 0 && $date_end_time !== false && !$is_expired ) { ?>
<div class="ecdeal-timer">
<div class="deal-timer" id="ecdeal<?php echo $module_id; ?>">
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
    <?php if($date_end && $module_id && $quantity > 0 && $date_end_time !== false){ ?>
     <script type="text/javascript">

            var ec_server_time = {
                module_id : "#eccounter<?php echo $module_id; ?>",
                target_date : "<?php echo date('m/d/Y G:i:s', strtotime($date_end));?>",
                callback : '',

                id_day  : '#cd_day-<?php echo $module_id; ?>',
                id_hour  : '#cd_hour-<?php echo $module_id; ?>',
                id_minute  : '#cd_min-<?php echo $module_id; ?>',
                id_second  : '#cd_sec-<?php echo $module_id; ?>',
                
                label_day : '#lb_day<?php echo $module_id; ?>',
                label_hour : '#lb_hour<?php echo $module_id; ?>',
                label_minute : '#lb_minute<?php echo $module_id; ?>',
                label_second : '#lb_second<?php echo $module_id; ?>',
          
                label_day_value : '<?php echo $this->language->get("text_day");?>',
                label_hour_value : '<?php echo $this->language->get("text_hours");?>',
                label_minute_value : '<?php echo $this->language->get("text_mins");?>',
                label_second_value : '<?php echo $this->language->get("text_secs");?>',
                show_empty_day: true
            };
            $("#eccounter<?php echo $module_id; ?>").ecCountDown(ec_server_time);

            if(typeof(list_deals) != "undefined") {
              var list_deals = [];
            }
            list_deals.push(JSON.stringify(ec_server_time));
    </script>
    <?php } ?>
</div>
</div>
<?php } ?>
