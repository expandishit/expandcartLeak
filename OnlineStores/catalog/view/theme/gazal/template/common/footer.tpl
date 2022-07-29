<?php
$LANGUAGE_ID = $this->config->get( 'config_language_id' );
$gazal_custom_widget_title = $this->config->get('gazal_custom_widget_title');
$gazal_footer_info_text = $this->config->get('gazal_footer_info_text');
$gazal_shipping_text = $this->config->get('gazal_shipping_text');
$gazal_cus_img = $this->config->get('gazal_cus_img');
$gazal_shipping_last_text = $this->config->get('gazal_shipping_last_text');
$gazal_phone = $this->config->get('gazal_phone');
$gazal_contact_img = $this->config->get('gazal_contact_img');
$gazal_address = $this->config->get('gazal_address');
$gazal_email = $this->config->get('gazal_email');
$gazal_email_second = $this->config->get('gazal_email_second');
$gazal_twitter_username = $this->config->get('gazal_twitter_username');
$gazal_facebook_id = $this->config->get('gazal_facebook_id');

$gazal_pay1_img = $this->config->get('gazal_pay1_img');
$gazal_pay1_link = $this->config->get('gazal_pay1_link');
$gazal_pay2_img = $this->config->get('gazal_pay2_img');
$gazal_pay2_link = $this->config->get('gazal_pay2_link');
$gazal_pay3_img = $this->config->get('gazal_pay3_img');
$gazal_pay3_link = $this->config->get('gazal_pay3_link');
$gazal_pay4_img = $this->config->get('gazal_pay4_img');
$gazal_pay4_link = $this->config->get('gazal_pay4_link');
$gazal_pay5_img = $this->config->get('gazal_pay5_img');
$gazal_pay5_link = $this->config->get('gazal_pay5_link');
$gazal_pay6_img = $this->config->get('gazal_pay6_img');
$gazal_pay6_link = $this->config->get('gazal_pay6_link');
$gazal_pay7_img = $this->config->get('gazal_pay7_img');
$gazal_pay7_link = $this->config->get('gazal_pay7_link');
$gazal_pay8_img = $this->config->get('gazal_pay8_img');
$gazal_pay8_link = $this->config->get('gazal_pay8_link');
$gazal_pay9_img = $this->config->get('gazal_pay9_img');
$gazal_pay9_link = $this->config->get('gazal_pay9_link');
$gazal_pay10_img = $this->config->get('gazal_pay10_img');
$gazal_pay10_link = $this->config->get('gazal_pay10_link');

$gazal_facebook_link = $this->config->get('gazal_facebook_link');
$gazal_twitter_link = $this->config->get('gazal_twitter_link');
$gazal_google_link = $this->config->get('gazal_google_link');
$gazal_youtube_link = $this->config->get('gazal_youtube_link');
$gazal_linkedin_link = $this->config->get('gazal_linkedin_link');
$gazal_digg_link = $this->config->get('gazal_digg_link');
$gazal_dribbble_link = $this->config->get('gazal_dribbble_link');
$gazal_flickr_link = $this->config->get('gazal_flickr_link');
$gazal_pinterest_link = $this->config->get('gazal_pinterest_link');
$gazal_skype_link = $this->config->get('gazal_skype_link');
$gazal_vimeo_link = $this->config->get('gazal_vimeo_link');
$gazal_rss_link = $this->config->get('gazal_rss_link');
?>
</div><!--/container_12-->
<div class="container_12 footer-bg">

    <!--Aboutus-->
    <?php if($gazal_custom_widget_title[$LANGUAGE_ID] != '') { ?>
	<div class="grid_3">
    	<h3 class="widgetsTitle"><?php echo $gazal_custom_widget_title[$LANGUAGE_ID]?></h3>
    	<div class="marB30">
    	<div class="about">
            <p>
            <?php echo $gazal_footer_info_text[$LANGUAGE_ID]?>
            </p>
        </div>   
        
        <div class="clearfix"></div>
        
        <?php if($gazal_shipping_text[$LANGUAGE_ID] != '') { ?>
        <div class="shipping">
        	<?php if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
            else $path_image = HTTP_IMAGE; 
            if($gazal_cus_img[$LANGUAGE_ID]!='') { ?>
            <img src="<?php echo $path_image . $gazal_cus_img[$LANGUAGE_ID] ?>" alt="" width="75" >
            <?php } ?> 
            <span class="second-line"><?php echo $gazal_shipping_text[$LANGUAGE_ID]?></span> <br />
            <span class="third-line"><?php echo $gazal_shipping_last_text[$LANGUAGE_ID]?></span>
        </div>
        <?php } ?>
        </div>
    </div>
    <?php } ?>
    
    <!--contact us-->
    
    <?php if($gazal_phone[$LANGUAGE_ID] != '') { ?>
    <div class="grid_3">
    <h3 class="widgetsTitle"><?php echo $text_contact; ?></h3>
    	<div class="contact marB30">
            <?php if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
            else $path_image = HTTP_IMAGE; 
            if($gazal_contact_img[$LANGUAGE_ID]!='') { ?>
            <img src="<?php echo $path_image . $gazal_contact_img[$LANGUAGE_ID] ?>" alt="" width="100" class="contactImg" >
            <?php } ?> 
                <div class="rightCont">
                <?php if($gazal_address[$LANGUAGE_ID] != '') { ?>
                    <img class="icon" src="catalog/view/theme/gazal/image/home-2.png" width="25" alt="" > <?php echo $gazal_address[$LANGUAGE_ID]?>
                <?php } ?>
                <br/><br/><div class="clearfix"></div>
                <?php if($gazal_phone[$LANGUAGE_ID] != '') { ?>
                    <img class="icon" src="catalog/view/theme/gazal/image/phone.png" width="25" alt="" > <?php echo $gazal_phone[$LANGUAGE_ID]?> <br /><?php echo $gazal_phone_second[$LANGUAGE_ID]?>
                <?php } ?>
                <br/><br/><div class="clearfix"></div>
                <?php if($gazal_email[$LANGUAGE_ID] != '') { ?>
                    <img class="icon" src="catalog/view/theme/gazal/image/mail.png" width="25" alt="" > <a href="mailto:<?php echo $gazal_email[$LANGUAGE_ID]?>"><?php echo $gazal_email[$LANGUAGE_ID]?></a> <br />
                    <a href="mailto:<?php echo $gazal_email_second[$LANGUAGE_ID]?>"><?php echo $gazal_email_second[$LANGUAGE_ID]?></a>
                <?php } ?>
                </div>
            </div>
    </div>
    <?php } ?>
	
    
    <!--twitter-->
    
    <?php if($gazal_twitter_username[$LANGUAGE_ID] != '') { ?>
    <div class="grid_3">
    	<h3 class="widgetsTitle"><?php echo $text_twitter; ?></h3>
    	<div class="tweet marB30"></div>
		<script>
            $(document).ready(function() {
                $(".tweet").tweet({
					modpath: 'catalog/view/javascript/jquery/twitter/',
                    username: "<?php echo $gazal_twitter_username[$LANGUAGE_ID]; ?>",
                    avatar_size: 32,
                    count: 4,
                    loading_text: "loading tweets..."
                });
            });
        </script>
    </div>
    <?php } ?>
    
    
    
    <!--facebook-->
    
    <?php if($gazal_facebook_id[$LANGUAGE_ID] != '') { ?>
    <div class="grid_3">
    	<h3 class="widgetsTitle"><?php echo $text_facebook; ?></h3>
          <iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2F<?php echo $gazal_facebook_id[$LANGUAGE_ID]; ?>&amp;width=292&amp;height=258&amp;show_faces=true&amp;colorscheme=light&amp;stream=false&amp;show_border=false&amp;header=false" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:94%; height:260px; margin-bottom: 30px; background:<?php echo $this->config->get('facebook_bg_color'); ?>; padding: 3%" allowTransparency="true"></iframe>
    </div>
	<?php } ?>
    
    
	<div class="clearfix"></div>
	<hr/>
    <div class="clearfix"></div>

    <footer>
      <?php if ($informations) { ?>
      <div class="column grid_3">
        <h3><?php echo $text_information; ?></h3>
        <ul>
          <?php foreach ($informations as $information) { ?>
          <li><a href="<?php echo $information['href']; ?>"><?php echo $information['title']; ?></a></li>
          <?php } ?>
        </ul>
      </div>
      <?php } ?>
      <div class="column grid_3">
        <h3><?php echo $text_service; ?></h3>
        <ul>
          <li><a href="<?php echo $contact; ?>"><?php echo $text_contact; ?></a></li>
          <li><a href="<?php echo $return; ?>"><?php echo $text_return; ?></a></li>
          <li><a href="<?php echo $sitemap; ?>"><?php echo $text_sitemap; ?></a></li>
        </ul>
      </div>
      <div class="column grid_3">
        <h3><?php echo $text_extra; ?></h3>
        <ul>
          <li><a href="<?php echo $manufacturer; ?>"><?php echo $text_manufacturer; ?></a></li>
          <li><a href="<?php echo $voucher; ?>"><?php echo $text_voucher; ?></a></li>
          <?php $queryAffiliate = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'affiliate'"); if($queryAffiliate->num_rows) { ?>
          <li><a href="<?php echo $affiliate; ?>"><?php echo $text_affiliate; ?></a></li>
          <?php }?>
          <li><a href="<?php echo $special; ?>"><?php echo $text_special; ?></a></li>
        </ul>
      </div>
      <div class="column grid_3">
        <h3><?php echo $text_account; ?></h3>
        <ul>
          <li><a href="<?php echo $account; ?>"><?php echo $text_account; ?></a></li>
          <li><a href="<?php echo $order; ?>"><?php echo $text_order; ?></a></li>
          <li><a href="<?php echo $wishlist; ?>"><?php echo $text_wishlist; ?></a></li>
          <li><a href="<?php echo $newsletter; ?>"><?php echo $text_newsletter; ?></a></li>
        </ul>
      </div>
      
      <hr /><div class="clearfix"></div>
      
      <div class="grid_6">
      	  <ul class="payment">
          
          	<?php if($gazal_pay1_img[$LANGUAGE_ID] != '') { ?>
          	<li>
            	<a href="<?php echo $gazal_pay1_link[$LANGUAGE_ID]?>" target="_blank">
            	<?php if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
				else $path_image = HTTP_IMAGE; 
                if($gazal_pay1_img[$LANGUAGE_ID]!='') { ?>
            	<img src="<?php echo $path_image . $gazal_pay1_img[$LANGUAGE_ID] ?>" alt="" width="45" ><!--custom image-->
                <?php } ?>    
                </a>
            </li>
            <?php } ?>
            
            <?php if($gazal_pay2_img[$LANGUAGE_ID] != '') { ?>
          	<li>
            	<a href="<?php echo $gazal_pay2_link[$LANGUAGE_ID]?>" target="_blank">
            	<?php if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
				else $path_image = HTTP_IMAGE; 
                if($gazal_pay2_img[$LANGUAGE_ID]!='') { ?>
            	<img src="<?php echo $path_image . $gazal_pay2_img[$LANGUAGE_ID] ?>" alt="" width="45" ><!--custom image-->
                <?php } ?>    
                </a>
            </li>
            <?php } ?>
            
            <?php if($gazal_pay3_img[$LANGUAGE_ID] != '') { ?>
          	<li>
            	<a href="<?php echo $gazal_pay3_link[$LANGUAGE_ID]?>" target="_blank">
            	<?php if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
				else $path_image = HTTP_IMAGE; 
                if($gazal_pay3_img[$LANGUAGE_ID]!='') { ?>
            	<img src="<?php echo $path_image . $gazal_pay3_img[$LANGUAGE_ID] ?>" alt="" width="45" ><!--custom image-->
                <?php } ?>    
                </a>
            </li>
            <?php } ?>
            
            <?php if($gazal_pay4_img[$LANGUAGE_ID] != '') { ?>
          	<li>
            	<a href="<?php echo $gazal_pay4_link[$LANGUAGE_ID]?>" target="_blank">
            	<?php if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
				else $path_image = HTTP_IMAGE; 
                if($gazal_pay4_img[$LANGUAGE_ID]!='') { ?>
            	<img src="<?php echo $path_image . $gazal_pay4_img[$LANGUAGE_ID] ?>" alt="" width="45" ><!--custom image-->
                <?php } ?>    
                </a>
            </li>
            <?php } ?>
            
            <?php if($gazal_pay5_img[$LANGUAGE_ID] != '') { ?>
          	<li>
            	<a href="<?php echo $gazal_pay5_link[$LANGUAGE_ID]?>" target="_blank">
            	<?php if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
				else $path_image = HTTP_IMAGE; 
                if($gazal_pay5_img[$LANGUAGE_ID]!='') { ?>
            	<img src="<?php echo $path_image . $gazal_pay5_img[$LANGUAGE_ID] ?>" alt="" width="45" ><!--custom image-->
                <?php } ?>    
                </a>
            </li>
            <?php } ?>
            
            <?php if($gazal_pay6_img[$LANGUAGE_ID] != '') { ?>
          	<li>
            	<a href="<?php echo $gazal_pay6_link[$LANGUAGE_ID]?>" target="_blank">
            	<?php if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
				else $path_image = HTTP_IMAGE; 
                if($gazal_pay6_img[$LANGUAGE_ID]!='') { ?>
            	<img src="<?php echo $path_image . $gazal_pay6_img[$LANGUAGE_ID] ?>" alt="" width="45" ><!--custom image-->
                <?php } ?>    
                </a>
            </li>
            <?php } ?>
            
            <?php if($gazal_pay7_img[$LANGUAGE_ID] != '') { ?>
          	<li>
            	<a href="<?php echo $gazal_pay7_link[$LANGUAGE_ID]?>" target="_blank">
            	<?php if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
				else $path_image = HTTP_IMAGE; 
                if($gazal_pay7_img[$LANGUAGE_ID]!='') { ?>
            	<img src="<?php echo $path_image . $gazal_pay7_img[$LANGUAGE_ID] ?>" alt="" width="45" ><!--custom image-->
                <?php } ?>    
                </a>
            </li>
            <?php } ?>
            
            <?php if($gazal_pay8_img[$LANGUAGE_ID] != '') { ?>
          	<li>
            	<a href="<?php echo $gazal_pay8_link[$LANGUAGE_ID]?>" target="_blank">
            	<?php if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
				else $path_image = HTTP_IMAGE; 
                if($gazal_pay8_img[$LANGUAGE_ID]!='') { ?>
            	<img src="<?php echo $path_image . $gazal_pay8_img[$LANGUAGE_ID] ?>" alt="" width="45" ><!--custom image-->
                <?php } ?>    
                </a>
            </li>
            <?php } ?>
            
            <?php if($gazal_pay9_img[$LANGUAGE_ID] != '') { ?>
          	<li>
            	<a href="<?php echo $gazal_pay9_link[$LANGUAGE_ID]?>" target="_blank">
            	<?php if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
				else $path_image = HTTP_IMAGE; 
                if($gazal_pay9_img[$LANGUAGE_ID]!='') { ?>
            	<img src="<?php echo $path_image . $gazal_pay9_img[$LANGUAGE_ID] ?>" alt="" width="45" ><!--custom image-->
                <?php } ?>    
                </a>
            </li>
            <?php } ?>
            
            <?php if($gazal_pay10_img[$LANGUAGE_ID] != '') { ?>
          	<li>
            	<a href="<?php echo $gazal_pay10_link[$LANGUAGE_ID]?>" target="_blank">
            	<?php if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $path_image = HTTPS_IMAGE;
				else $path_image = HTTP_IMAGE; 
                if($gazal_pay10_img[$LANGUAGE_ID]!='') { ?>
            	<img src="<?php echo $path_image . $gazal_pay10_img[$LANGUAGE_ID] ?>" alt="" width="45" ><!--custom image-->
                <?php } ?>    
                </a>
            </li>
            <?php } ?>
          </ul>
      </div>
      
      <div class="grid_6">
      	<ul class="socials">
        
        	<?php if($gazal_facebook_link[$LANGUAGE_ID] != '') { ?>
        	<li>
                <a href="<?php echo $gazal_facebook_link[$LANGUAGE_ID]; ?>" title="facebook" target="_blank">
                	<img src="catalog/view/theme/gazal/image/social/facebook.png" width="30" alt="" />
                </a>
            </li>
            <?php } ?>
            <?php if($gazal_twitter_link[$LANGUAGE_ID] != '') { ?>
            <li>
                <a href="<?php echo $gazal_twitter_link[$LANGUAGE_ID]; ?>" title="twitter" target="_blank">
                	<img src="catalog/view/theme/gazal/image/social/twitter.png" width="30" alt="" />
                </a>
            </li>
            <?php } ?>
            <?php if($gazal_google_link[$LANGUAGE_ID] != '') { ?>
            <li>
                <a href="<?php echo $gazal_google_link[$LANGUAGE_ID]; ?>" title="google" target="_blank">
                	<img src="catalog/view/theme/gazal/image/social/google.png" width="30" alt="" />
                </a>
            </li>
            <?php } ?>
            <?php if($gazal_youtube_link[$LANGUAGE_ID] != '') { ?>
            <li>
                <a href="<?php echo $gazal_youtube_link[$LANGUAGE_ID]; ?>" title="youtube" target="_blank">
                	<img src="catalog/view/theme/gazal/image/social/youtube.png" width="30" alt="" />
                </a>
            </li>
            <?php } ?>
            <?php if($gazal_linkedin_link[$LANGUAGE_ID] != '') { ?>
            <li>
                <a href="<?php echo $gazal_linkedin_link[$LANGUAGE_ID]; ?>" title="linkedin" target="_blank">
                    <img src="catalog/view/theme/gazal/image/social/linkedin.png" width="30" alt="" />
                </a>
            </li>
            <?php } ?>
            <?php if($gazal_digg_link[$LANGUAGE_ID] != '') { ?>
            <li>
                <a href="<?php echo $gazal_digg_link[$LANGUAGE_ID]; ?>" title="digg" target="_blank">
                    <img src="catalog/view/theme/gazal/image/social/digg.png" width="30" alt="" />
                </a>
            </li>
            <?php } ?>
            <?php if($gazal_dribbble_link[$LANGUAGE_ID] != '') { ?>
            <li>
                <a href="<?php echo $gazal_dribbble_link[$LANGUAGE_ID]; ?>" title="dribbble" target="_blank">
                    <img src="catalog/view/theme/gazal/image/social/dribbble.png" width="30" alt="" />
                </a>
            </li>
            <?php } ?>
            <?php if($gazal_flickr_link[$LANGUAGE_ID] != '') { ?>
            <li>
                <a href="<?php echo $gazal_flickr_link[$LANGUAGE_ID]; ?>" title="flickr" target="_blank">
                    <img src="catalog/view/theme/gazal/image/social/flickr.png" width="30" alt="" />
                </a>
            </li>
            <?php } ?>
            <?php if($gazal_pinterest_link[$LANGUAGE_ID] != '') { ?>
            <li>
                <a href="<?php echo $gazal_pinterest_link[$LANGUAGE_ID]; ?>" title="pinterest" target="_blank">
                    <img src="catalog/view/theme/gazal/image/social/pinterest.png" width="30" alt="" />
                </a>
            </li>
            <?php } ?>
            <?php if($gazal_skype_link[$LANGUAGE_ID] != '') { ?>
            <li>
                <a href="<?php echo $gazal_skype_link[$LANGUAGE_ID]; ?>" title="skype" target="_blank">
                    <img src="catalog/view/theme/gazal/image/social/skype.png" width="30" alt="" />
                </a>
            </li>
            <?php } ?>
            <?php if($gazal_vimeo_link[$LANGUAGE_ID] != '') { ?>
            <li>
                <a href="<?php echo $gazal_vimeo_link[$LANGUAGE_ID]; ?>" title="vimeo" target="_blank">
                    <img src="catalog/view/theme/gazal/image/social/vimeo.png" width="30" alt="" />
                </a>
            </li>
            <?php } ?>
            <?php if($gazal_rss_link[$LANGUAGE_ID] != '') { ?>
            <li>
                <a href="<?php echo $gazal_rss_link[$LANGUAGE_ID]; ?>" title="rss" target="_blank">
                	<img src="catalog/view/theme/gazal/image/social/rss.png" width="30" alt="" />
                </a>
            </li>
            <?php } ?>
        </ul>
      	<div id="powered"><?php echo $powered; ?></div>
      </div>
      
    </footer>
    
    
</div><!--/container_12-->



<script src="catalog/view/theme/gazal/javascript/lazyload/jquery.lazyload.min.js?v=1.9.7"></script>
<script type="text/javascript" charset="utf-8">
    $(function() {
        $("img.lazy").lazyload({
            threshold : 200,
            effect : "fadeIn"
            //skip_invisible : true
        });
    });
</script>

</body></html>