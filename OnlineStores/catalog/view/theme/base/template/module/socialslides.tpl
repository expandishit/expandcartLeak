<?php
// ------------------------------
// Social Slides for OpenCart
// By Best-Byte
// www.best-byte.com
// ------------------------------
?>
<?php
        $newTPLs = array("pav_asenti", "pav_clothes", "pav_cosmetics", "pav_digitalstore",
            "pav_fashion", "pav_floral", "pav_foodstore", "pav_furniture");

if (in_array($this->config->get('config_template'), $newTPLs)) { ?>
    <link rel="stylesheet" type="text/css" href="catalog/view/theme/default/social-slides/social-slides_newtpl.css">
<?php } else { ?>
    <link rel="stylesheet" type="text/css" href="catalog/view/theme/default/social-slides/social-slides.css">
<?php } ?>
<?php if ($display) { ?>
<script type="text/javascript">
jQuery(document).ready(function(){
 jQuery("#facebook_right").hover(function(){ jQuery(this).stop(true,false).animate({right:  0}, 500); },function(){ jQuery("#facebook_right").stop(true,false).animate({right: -300}, 500); }); 
 jQuery("#twitter_right").hover(function(){ jQuery(this).stop(true,false).animate({right:  0}, 500); },function(){ jQuery("#twitter_right").stop(true,false).animate({right: -250}, 500); }); 
 jQuery("#google_plus_right").hover(function(){ jQuery(this).stop(true,false).animate({right:  0}, 500); },function(){ jQuery("#google_plus_right").stop(true,false).animate({right: -316}, 500); }); 
 jQuery("#pinterest_right").hover(function(){ jQuery(this).stop(true,false).animate({right:  0}, 500); },function(){ jQuery("#pinterest_right").stop(true,false).animate({right: -205}, 500); });
 jQuery("#youtube_right").hover(function(){ jQuery(this).stop(true,false).animate({right:  0}, 500); },function(){ jQuery("#youtube_right").stop(true,false).animate({right: -279}, 500); }); 
 jQuery("#linkedin_right").hover(function(){ jQuery(this).stop(true,false).animate({right:  0}, 500); },function(){ jQuery("#linkedin_right").stop(true,false).animate({right: -130}, 500); });
 jQuery("#instagram_right").hover(function(){ jQuery(this).stop(true,false).animate({right:  0}, 500); },function(){ jQuery("#instagram_right").stop(true,false).animate({right: -170}, 500); });
});
</script>
<?php $currentpos = 0; $top_pos = $top_position; ?>
<?php if ($facebook_show) { ?>
<div id="off">
<?php } else { ?>
<?php
    if ($currentpos == 0) {
        $currentpos = $top_pos;
    }
    else {
        $currentpos = $currentpos + 50;
    }
?>
<div id="on">
<?php } ?>
    <div id="facebook_right" style="top: <?php echo $currentpos; ?>px;">
        <div id="facebook_div">
            <img src="catalog/view/theme/default/social-slides/facebook.png" alt="Facebook">            
           <iframe src="https://www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Ffacebook.com%2Fpages%2F<?php echo $facebook_code; ?>&amp;locale=en_GB&amp;width=300&amp;connections=20&amp;stream=&amp;header=false&amp;show_faces=true&amp;height=340"></iframe>
        </div>
    </div>
</div>
<?php if ($twitter_show) { ?>
<div id="off1">
<?php } else { ?>
<?php
    if ($currentpos == 0) {
        $currentpos = $top_pos;
    }
    else {
        $currentpos = $currentpos + 50;
    }
?>
<div id="on1">
<?php } ?>
    <div id="twitter_right" style="top: <?php echo $currentpos; ?>px;">
        <div id="twitter_div">
            <img id="twitter_right_img" src="catalog/view/theme/default/social-slides/twitter.png" alt="Twitter">
<div style="width:246px;height:350px;"><?php echo html_entity_decode($twitter_code); ?></div>
        </div>
    </div>
</div>
<?php if ($google_show) { ?>
<div id="off2">
<?php } else { ?>
    <?php
    if ($currentpos == 0) {
        $currentpos = $top_pos;
    }
    else {
        $currentpos = $currentpos + 50;
    }
?>
<div id="on2">
<?php } ?>
    <div id="google_plus_right" style="top: <?php echo $currentpos; ?>px;">
        <div id="google_plus_div">
          <img id="google_plus_right_img" src="catalog/view/theme/default/social-slides/google.png" alt="Google">
          <script type="text/javascript" src="https://apis.google.com/js/platform.js"></script>
          <div style="float:left;margin-left:3px;"><div class="g-page" data-width="309" data-href="https://plus.google.com/<?php echo $google_code; ?>" data-layout="landscape" data-rel="publisher"></div></div>
    </div>
  </div>
</div>
<?php if ($pinterest_show) { ?> 
<div id="off3">
<?php } else { ?>
<div id="on3">
    <?php
    if ($currentpos == 0) {
        $currentpos = $top_pos;
    }
    else {
        $currentpos = $currentpos + 50;
    }
?>
<?php } ?>
    <div id="pinterest_right" style="top: <?php echo $currentpos; ?>px;">
        <div id="pinterest_div">          
          <div style="margin-top: 6px;text-align: center;">
          <a href="https://pinterest.com/<?php echo $pinterest_code; ?>/" target="_blank"><img src="https://passets-cdn.pinterest.com/images/about/buttons/follow-me-on-pinterest-button.png" width="169" height="28" alt="Follow Me on Pinterest" /></a>
          </div>
          <div style="margin-top: 40px;text-align: center;"><a href="https://pinterest.com/pin/create/button/?url=<?php echo HTTP_SERVER; ?>&amp;media=<?php echo HTTP_SERVER; ?>image/data/logo.png" data-pin-do="buttonPin" data-pin-config="above"><img src="https://assets.pinterest.com/images/PinExt.png" title="Pin It" alt="Pinterest" /></a>
          <script type="text/javascript" src="https://assets.pinterest.com/js/pinit.js"></script>
          </div>
          <img id="pinterest_right_img" src="catalog/view/theme/default/social-slides/pinterest.png" alt="Pinterest">
    </div>
  </div>
</div>
<?php if ($youtube_show) { ?>
<div id="off4">
<?php } else { ?>
<div id="on4">
<?php
    if ($currentpos == 0) {
        $currentpos = $top_pos;
    }
    else {
        $currentpos = $currentpos + 50;
    }
?>
<?php } ?>
    <div id="youtube_right" style="top: <?php echo $currentpos; ?>px;">
        <div id="youtube_div">
<iframe id="fr" src="https://www.youtube.com/subscribe_widget?p=<?php echo $youtube_code; ?>" style="overflow: hidden; height: 105px; width: 275px; border: 0;"></iframe>
            <img id="youtube_right_img" src="catalog/view/theme/default/social-slides/youtube.png" alt="YouTube">
        </div>
    </div>
</div>
<?php if ($linkedin_show) { ?>
<div id="off5">
<?php } else { ?>
<div id="on5">
    <?php
    if ($currentpos == 0) {
        $currentpos = $top_pos;
    }
    else {
        $currentpos = $currentpos + 50;
    }
?>
<?php } ?>
    <div id="linkedin_right" style="top: <?php echo $currentpos; ?>px;">
        <div id="linkedin_div">
        <div style="text-align: center;margin-top: 4px;">
<a href="<?php echo $linkedin_code; ?>" target="_blank"><?php echo $text_linkedin; ?></a>      
        </div>
        <div style="text-align: center;margin-top: 4px;">
<script src="https://platform.linkedin.com/in.js" type="text/javascript"></script>
<script type="IN/Share" data-counter="top"></script>
        </div>   
<img id="linkedin_right_img" src="catalog/view/theme/default/social-slides/linkedin.png" alt="Linkedin">
        </div>
    </div>
</div>
    <?php if ($instagram_show) { ?>
    <div id="off6">
        <?php } else { ?>
        <div id="on6">
            <?php
    if ($currentpos == 0) {
        $currentpos = $top_pos;
    }
    else {
        $currentpos = $currentpos + 50;
    }
?>
            <?php } ?>
            <div id="instagram_right" style="top: <?php echo $currentpos; ?>px;">
                <div id="instagram_div">
                    <div style="text-align: center;margin-top: 11px;">
                        <style>.ig-b- { display: inline-block; }
                            .ig-b- img { visibility: hidden; }
                            .ig-b-:hover { background-position: 0 -60px; } .ig-b-:active { background-position: 0 -120px; }
                            .ig-b-v-24 { width: 137px; height: 24px; background: url(//badges.instagram.com/static/images/ig-badge-view-sprite-24.png) no-repeat 0 0; }
                            @media only screen and (-webkit-min-device-pixel-ratio: 2), only screen and (min--moz-device-pixel-ratio: 2), only screen and (-o-min-device-pixel-ratio: 2 / 1), only screen and (min-device-pixel-ratio: 2), only screen and (min-resolution: 192dpi), only screen and (min-resolution: 2dppx) {
                                .ig-b-v-24 { background-image: url(//badges.instagram.com/static/images/ig-badge-view-sprite-24@2x.png); background-size: 160px 178px; } }</style>
                        <a href="<?php echo $instagram_code; ?>" target="_blank" class="ig-b- ig-b-v-24"><img src="//badges.instagram.com/static/images/ig-badge-view-24.png" alt="Instagram" /></a>
                    </div>
                    <img id="instagram_right_img" src="catalog/view/theme/default/social-slides/instagram.png" alt="Instagram">
                </div>
            </div>
        </div>
<?php } else { ?>
<script type="text/javascript">
jQuery(document).ready(function(){
 jQuery("#facebook_left").hover(function(){ jQuery(this).stop(true,false).animate({left:  0}, 500); },function(){ jQuery("#facebook_left").stop(true,false).animate({left: -300}, 500); }); 
 jQuery("#twitter_left").hover(function(){ jQuery(this).stop(true,false).animate({left:  0}, 500); },function(){ jQuery("#twitter_left").stop(true,false).animate({left: -250}, 500); }); 
 jQuery("#google_plus_left").hover(function(){ jQuery(this).stop(true,false).animate({left:  0}, 500); },function(){ jQuery("#google_plus_left").stop(true,false).animate({left: -316}, 500); });
 jQuery("#pinterest_left").hover(function(){ jQuery(this).stop(true,false).animate({left:  0}, 500); },function(){ jQuery("#pinterest_left").stop(true,false).animate({left: -205}, 500); });
 jQuery("#youtube_left").hover(function(){ jQuery(this).stop(true,false).animate({left:  0}, 500); },function(){ jQuery("#youtube_left").stop(true,false).animate({left: -279}, 500); }); 
 jQuery("#linkedin_left").hover(function(){ jQuery(this).stop(true,false).animate({left:  0}, 500); },function(){ jQuery("#linkedin_left").stop(true,false).animate({left: -130}, 500); });
 jQuery("#instagram_left").hover(function(){ jQuery(this).stop(true,false).animate({left:  0}, 500); },function(){ jQuery("#instagram_left").stop(true,false).animate({left: -170}, 500); });
});
</script>
<?php $currentpos = 0; $top_pos = $top_position; ?>
<?php if ($facebook_show) { ?>
<div id="off">
<?php } else { ?>
<div id="on">
    <?php
    if ($currentpos == 0) {
        $currentpos = $top_pos;
    }
    else {
        $currentpos = $currentpos + 50;
    }
?>
<?php } ?>
    <div id="facebook_left" style="top: <?php echo $currentpos; ?>px;">
        <div id="facebook_div">
            <img src="catalog/view/theme/default/social-slides/facebook.png" alt="Facebook">
            <iframe src="https://www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Ffacebook.com%2Fpages%2F<?php echo $facebook_code; ?>&amp;locale=en_GB&amp;width=300&amp;connections=20&amp;stream=&amp;header=false&amp;show_faces=true&amp;height=340"></iframe>
        </div>
    </div>
</div>
<?php if ($twitter_show) { ?>
<div id="off1">
<?php } else { ?>
<div id="on1">
    <?php
    if ($currentpos == 0) {
        $currentpos = $top_pos;
    }
    else {
        $currentpos = $currentpos + 50;
    }
?>
<?php } ?>
    <div id="twitter_left" style="top: <?php echo $currentpos; ?>px;">
        <div id="twitter_div">
            <img id="twitter_left_img" src="catalog/view/theme/default/social-slides/twitter.png" alt="Twitter">
<div style="width:246px;height:350px;"><?php echo html_entity_decode($twitter_code); ?></div>
        </div>
    </div>
</div>
<?php if ($google_show) { ?>
<div id="off2">
<?php } else { ?>
<div id="on2">
    <?php
    if ($currentpos == 0) {
        $currentpos = $top_pos;
    }
    else {
        $currentpos = $currentpos + 50;
    }
?>
<?php } ?>
    <div id="google_plus_left" style="top: <?php echo $currentpos; ?>px;">
        <div id="google_plus_div">
          <img id="google_plus_left_img" src="catalog/view/theme/default/social-slides/google.png" alt="Google">
          <script type="text/javascript" src="https://apis.google.com/js/platform.js"></script>
          <div style="float:right;margin-right:3px;"><div class="g-page" data-width="309" data-href="https://plus.google.com/<?php echo $google_code; ?>" data-layout="landscape" data-rel="publisher"></div></div>
    </div>
  </div>
</div>
<?php if ($pinterest_show) { ?>
<div id="off3">
<?php } else { ?>
<div id="on3">
    <?php
    if ($currentpos == 0) {
        $currentpos = $top_pos;
    }
    else {
        $currentpos = $currentpos + 50;
    }
?>
<?php } ?>
    <div id="pinterest_left" style="top: <?php echo $currentpos; ?>px;">
        <div id="pinterest_div">
          <div style="margin-top: 6px;text-align: center;">
          <a href="https://pinterest.com/<?php echo $pinterest_code; ?>/" target="_blank"><img src="https://passets-cdn.pinterest.com/images/about/buttons/follow-me-on-pinterest-button.png" width="169" height="28" alt="Follow Me on Pinterest" /></a>
          </div>
          <div style="margin-top: 40px;text-align: center;"><a href="https://pinterest.com/pin/create/button/?url=<?php echo HTTP_SERVER; ?>&amp;media=<?php echo HTTP_SERVER; ?>image/data/logo.png" data-pin-do="buttonPin" data-pin-config="above"><img src="https://assets.pinterest.com/images/PinExt.png" title="Pin It" alt="Pinterest" /></a>
          <script type="text/javascript" src="https://assets.pinterest.com/js/pinit.js"></script>
          </div>
          <img id="pinterest_left_img" src="catalog/view/theme/default/social-slides/pinterest.png" alt="Pinterest">
    </div>
  </div>
</div>
<?php if ($youtube_show) { ?>
<div id="off4">
<?php } else { ?>
<div id="on4">
    <?php
    if ($currentpos == 0) {
        $currentpos = $top_pos;
    }
    else {
        $currentpos = $currentpos + 50;
    }
?>
<?php } ?>
    <div id="youtube_left" style="top: <?php echo $currentpos; ?>px;">
        <div id="youtube_div">
<iframe id="fr" src="https://www.youtube.com/subscribe_widget?p=<?php echo $youtube_code; ?>" style="overflow: hidden; height: 105px; width: 275px; border: 0;"></iframe>
            <img id="youtube_left_img" src="catalog/view/theme/default/social-slides/youtube.png" alt="YouTube">
        </div>
    </div>
</div>
<?php if ($linkedin_show) { ?>
<div id="off5">
<?php } else { ?>
<div id="on5">
    <?php
    if ($currentpos == 0) {
        $currentpos = $top_pos;
    }
    else {
        $currentpos = $currentpos + 50;
    }
?>
<?php } ?>
    <div id="linkedin_left" style="top: <?php echo $currentpos; ?>px;">
        <div id="linkedin_div">
        <div style="text-align: center;margin-top: 4px;">
<a href="<?php echo $linkedin_code; ?>" target="_blank"><?php echo $text_linkedin; ?></a>       
        </div>
        <div style="text-align: center;margin-top: 4px;">
<script src="https://platform.linkedin.com/in.js" type="text/javascript"></script>
<script type="IN/Share" data-counter="top"></script>
        </div> 
            <img id="linkedin_left_img" src="catalog/view/theme/default/social-slides/linkedin.png" alt="Linkedin">        
        </div>
    </div>
</div>
    <?php if ($instagram_show) { ?>
    <div id="off6">
        <?php } else { ?>
        <div id="on6">
            <?php
    if ($currentpos == 0) {
        $currentpos = $top_pos;
    }
    else {
        $currentpos = $currentpos + 50;
    }
?>
            <?php } ?>
            <div id="instagram_left" style="top: <?php echo $currentpos; ?>px;">
                <div id="instagram_div">
                    <div style="text-align: center;margin-top: 11px;">
                        <style>.ig-b- { display: inline-block; }
                            .ig-b- img { visibility: hidden; }
                            .ig-b-:hover { background-position: 0 -60px; } .ig-b-:active { background-position: 0 -120px; }
                            .ig-b-v-24 { width: 137px; height: 24px; background: url(//badges.instagram.com/static/images/ig-badge-view-sprite-24.png) no-repeat 0 0; }
                            @media only screen and (-webkit-min-device-pixel-ratio: 2), only screen and (min--moz-device-pixel-ratio: 2), only screen and (-o-min-device-pixel-ratio: 2 / 1), only screen and (min-device-pixel-ratio: 2), only screen and (min-resolution: 192dpi), only screen and (min-resolution: 2dppx) {
                                .ig-b-v-24 { background-image: url(//badges.instagram.com/static/images/ig-badge-view-sprite-24@2x.png); background-size: 160px 178px; } }</style>
                        <a href="<?php echo $instagram_code; ?>" target="_blank" class="ig-b- ig-b-v-24"><img src="//badges.instagram.com/static/images/ig-badge-view-24.png" alt="Instagram" /></a>
                    </div>
                    <img id="instagram_left_img" src="catalog/view/theme/default/social-slides/instagram.png" alt="Instagram">
                </div>
            </div>
        </div>
<?php } ?>