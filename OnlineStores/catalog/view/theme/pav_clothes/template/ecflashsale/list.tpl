<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <?php if(isset($description)) { ?>
  <div class="category-info">
    <?php echo $description; ?>
  </div>
 <?php } ?>

  <div class="form">
    <?php echo $this->language->get("text_deal_search"); ?><input type="text" value="<?php echo isset($search)?$search:''; ?>" name="search"> <input type="button" class="button" id="button-search" value="<?php echo $this->language->get("text_search"); ?>">
  </div>
  <div class="flashsale-filter product-filter">
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
  <?php if ($flashsales) { ?>
  <div id="list_flashsale_item" class="row product-list flashsale-list">
    <?php
    $cols = 3;
    $span = floor(12/$cols);
    $i = 0;
    ?>
    <?php foreach ($flashsales as $flashsale) { ?>
    <?php if( $i++%$cols == 0 ) { ?>
      <div class="row product-row">
    <?php } ?>
      <div class="col-md-<?php echo $span;?> product-item<?php echo $flashsale['featured']?' featured':''; ?> col-fullwidth  block-flashsale">
        <div class="image">   
            <a class="product-image" href="<?php echo $flashsale['href'] ?>" title="<?php echo $flashsale['name']; ?>"><img src="<?php echo $flashsale['thumb'];?>" alt="<?php echo $flashsale['name'];?>"/></a>
        </div>
        <div class="details">
                <div class="title">
                    <h2 class="product-name">
                      <a class="product-image" href="<?php echo $flashsale['href'] ?>" title="<?php echo $flashsale['name']; ?>"><?php echo $flashsale['name'];?></a>
                    </h2>
                </div>

                <div class="description"><?php echo $flashsale['description']; ?></div>
                <div class="flashsale-info">
                  <?php if(isset($show_sale_off) && $show_sale_off) { ?>
                  <span class="flashsale_discount">
                    <?php echo $flashsale['text_discount'];?>
                  </span>
                  <br/>
                  <?php } ?>
                  <?php if(isset($show_expire_date) && $show_expire_date) { ?>
                  <span class="flashsale-date-box">
                    <?php echo $this->language->get("text_expired_date")." <span>".$flashsale['date_end']."</span>"; ?>                        
                  </span>
                  <?php } ?>
                </div>
                <?php if(!$flashsale['is_expired']){ ?>
                <?php if($show_countdown){ ?>
                <!-- Countdown Javascript -->
                  <div id="eccounter-flashsale-<?php echo $flashsale['ecflashsale_id']; ?>" class="counter">
                      <ul class="countdown">  
                          <li class="first">
                              <div class="countdown_num" id="cd_day-flashsale-<?php echo $flashsale['ecflashsale_id']; ?>"></div><div id="lb_day-flashsale-<?php echo $flashsale['ecflashsale_id']; ?>"></div></li>
                          <li>
                              <div class="countdown_num" id="cd_hour-flashsale-<?php echo $flashsale['ecflashsale_id']; ?>"></div><div id="lb_hour-flashsale-<?php echo $flashsale['ecflashsale_id']; ?>"></div></li>
                          <li>
                              <div class="countdown_num" id="cd_min-flashsale-<?php echo $flashsale['ecflashsale_id']; ?>"></div><div id="lb_minute-flashsale-<?php echo $flashsale['ecflashsale_id']; ?>"></div></li>
                          <li class="last">
                              <div class="countdown_num" id="cd_sec-flashsale-<?php echo $flashsale['ecflashsale_id']; ?>"></div><div id="lb_second-flashsale-<?php echo $flashsale['ecflashsale_id']; ?>"></div></li>
                      </ul>
                      <div class="clear"><span>&nbsp;</span></div>
                  </div>
                  <script type="text/javascript">
                      var ec_server_time = { 
                        target_date : "<?php echo date('m/d/Y G:i:s', strtotime($flashsale['date_end']));?>",
                        callback : '',

                        id_day  : '#cd_day-flashsale-' + <?php echo $flashsale['ecflashsale_id']; ?>,
                        id_hour  : '#cd_hour-flashsale-' + <?php echo $flashsale['ecflashsale_id']; ?>,
                        id_minute  : '#cd_min-flashsale-' + <?php echo $flashsale['ecflashsale_id']; ?>,
                        id_second  : '#cd_sec-flashsale-' + <?php echo $flashsale['ecflashsale_id']; ?>,
                        
                        label_day : '#lb_day-flashsale-'+<?php echo $flashsale['ecflashsale_id']; ?>,
                        label_hour : '#lb_hour-flashsale-'+<?php echo $flashsale['ecflashsale_id']; ?>,
                        label_minute : '#lb_minute-flashsale-'+<?php echo $flashsale['ecflashsale_id']; ?>,
                        label_second : '#lb_second-flashsale-'+<?php echo $flashsale['ecflashsale_id']; ?>,
                  
                        label_day_value : '<?php echo $this->language->get("text_day");?>',
                        label_hour_value : '<?php echo $this->language->get("text_hours");?>',
                        label_minute_value : '<?php echo $this->language->get("text_mins");?>',
                        label_second_value : '<?php echo $this->language->get("text_secs");?>',
                        show_empty_day: true
                        };
                        $("#eccounter-flashsale-<?php echo $flashsale['ecflashsale_id']; ?>").ecCountDown(ec_server_time);
                    
                 </script>
                 <?php } ?>
                <?php }else{ ?>
                  <div class="expired"><?php echo $this->language->get("text_expired_sale");?></div>
                <?php } ?>
                <div class="footer">
                    
                  <div class="see-more-link">
                      <a href="<?php echo $flashsale['href']; ?>"><?php echo $this->language->get("text_view_more_flashsale");?></a>
                  </div>
                    
                  <div class="socials-share">
                    <?php if($show_social){ ?>
                    <ul>
                      <li><?php echo $this->language->get("text_share");?></li>
                      <li>
                         <a class="share facebook" title="Facebook" href="http://www.facebook.com/sharer/sharer.php?s=100&p[url]=<?php echo $flashsale['href']; ?>&p[images][0]=<?php echo $flashsale['thumb']; ?>&p[title]=<?php echo str_replace(" ","%20", $flashsale['name']);?>&p[summary]=<?php echo str_replace(" ","%20", $flashsale['description']); ?>" id="$Button" rel="nofollow" target="_BLANK">Facebook</a>
                      </li>
                      <li>
                        <a class="share twitter" title="Twitter" href="http://twitter.com/home?status=<?php echo str_replace(" ","%20", $flashsale['name'])." ". $flashsale['href']; ?>" id="$Button" rel="nofollow" target="_BLANK">Twitter</a>
                      </li>
                      <li>
                        <a class="share plus" title="Google Plus" href="https://plus.google.com/share?url=<?php echo $flashsale['href']; ?>" target="_BLANK" rel="nofollow">Google</a>
                      </li>
                      <li>
                        <a class="share pinterest" title="Pinterest" href="http://pinterest.com/pin/create/button/?url=<?php echo $flashsale['href']; ?>&media=<?php echo $flashsale['thumb'];?>&description=<?php echo str_replace(" ","%20", $flashsale['name']." - ".$flashsale['description']);?>"  target="_BLANK" rel="nofollow">Pinterest</a>
                      </li>
                    </ul>
                      <?php } ?>
                     
                  </div>
                </div>

                <div class="clearfix"></div>
        </div>
      </div>
       <?php if( $i%$cols == 0 || $i==count($flashsales) ) { ?>
       </div>
       <?php } ?>
    <?php 
      $i++;
      } ?>
  </div>
  <div class="pagination"><?php echo $pagination; ?></div>
  <?php } ?>
  <?php if (!$flashsales) { ?>
  <div class="content"><?php echo $text_empty; ?></div>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php } ?>
<script type="text/javascript"><!--
    $('#content input[name=\'search\']').keydown(function(e) {
      if (e.keyCode == 13) {
        $('#button-search').trigger('click');
      }
    });

    $('#button-search').bind('click', function() {
      url = 'index.php?route=ecflashsale/list';
      
      var search = $('#content input[name=\'search\']').attr('value');
      
      if (search) {
        url += '&search=' + encodeURIComponent(search);
      }
      
      location = url;
    });
//--></script> 
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?>