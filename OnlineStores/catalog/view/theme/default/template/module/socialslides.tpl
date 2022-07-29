<?php
// ------------------------------
// Social Slides for OpenCart
// By Best-Byte
// www.best-byte.com
// ------------------------------
?>

<?php $useragent=$_SERVER['HTTP_USER_AGENT'];
if(!preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
{ ?>
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

<?php } ?>