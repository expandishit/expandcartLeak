<?php
if($popup_mode): ?>
 <a id="ecflashsale_popup<?php echo $module_id;?>" href="#ecflashsale<?php echo $module_id;?>" style="display:none;">flashsale</a>
 <div style="display:none;">
<?php endif; ?>

<?php
   if(isset($is_product_item) && $is_product_item){
   		echo '<link rel="stylesheet" type="text/css" href="'.$base.$stylesheet.'" />';
   		echo '<script type="text/javascript" src="'.$base.$script.'"></script>';
   }
   $date_end_time = strtotime($date_end);
?>
<div class="<?php echo $prefix;?> block block-flashsale<?php echo (isset($no_border) && $no_border)?' noborder':'';?>" id="ecflashsale<?php echo $module_id; ?>">
<?php if($show_name):?>
	<div class="title">
        <h3><a href="<?php echo $flashsale_link; ?>" title=""><span><?php echo $name; ?></span></a></h3>
    </div>
<?php endif; ?>
     <div class="block-content">
     	<div  class="mini-products-list">
     		 <?php if(!$is_expired): ?>
            	<?php if($show_image):?>
				<a href="<?php echo $flashsale_link; ?>" title="" class="flashsale-image">
					<img src="<?php echo $thumb; ?>" width="<?php echo $image_width; ?>" height="<?php echo $image_height; ?>" alt="" />
				</a>		
				<?php endif; ?>
				<?php if($show_description):?>
				<div class="flashsale_detail"><?php echo $description; ?></div>
				<?php endif; ?>
				<?php if($show_sale_off): ?>                   
            	<div class="flashsale_info">
					<?php echo $text_discount;?>
				</div>
				<?php endif; ?>
				<?php if($show_expire_date): ?>
				<div class="flashsale-date-box">
					<?php if($date_end_time !== false) { ?>
					<?php echo $this->language->get("text_expired_date")." <span>".$date_end."</span>"; ?>
					<?php } else { ?>
					<?php echo $this->language->get("text_never_expired_date"); ?>
					<?php } ?>
				</div>
				<?php endif; ?>
				<?php if($show_viewmore): ?>
				<div class="flashsale-button">
					<a href="<?php echo $flashsale_link; ?>" class="button button-showmore"><?php echo $text_show_detail; ?><span class="arrow-icon"></span></a>
				</div>
				<?php endif; ?>
				<!-- Countdown Javascript -->
					<?php if( $date_end_time !== false ): ?>
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
			        <?php endif; ?>
		    	<?php else: ?>
		        <div class="expired"><?php echo $this->language->get("text_expired_sale");?></div>
		    	<?php endif; ?>
				
				<br class="clear clr"/>
        </div>
	
    </div>
</div>
<?php
if($popup_mode): ?>
 </div>
<?php endif; ?>
<?php if( $date_end_time !== false ) { ?>
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

<?php if($popup_mode): ?>
<script type="text/javascript">
 $(document).ready(function(){
    $("#ecflashsale_popup<?php echo $module_id;?>").colorbox({title:"<?php echo $this->language->get("heading_title");?>", inline:true, width:"<?php echo (isset($popup_width) && $popup_width !='auto')?$popup_width:'50%';?>", overlayClose: true, opacity: 0.5});
    $("#ecflashsale_popup<?php echo $module_id;?>").click();
  });
</script>
<?php endif; ?>