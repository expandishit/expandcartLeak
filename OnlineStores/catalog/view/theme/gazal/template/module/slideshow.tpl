<?php
$LANGUAGE_ID = $this->config->get( 'config_language_id' );
$option_slideshow = $this->config->get('option_slideshow');
?>
<div class="<?php echo $option_slideshow[$LANGUAGE_ID]; ?>">
<div class="flexslider" id="<?php echo $module; ?>" style="direction:ltr">
  <ul class="slides">
  	<?php foreach ($banners as $banner) { ?>
    <li>
    <?php if ($banner['link']) { ?>
        <a href="<?php echo $banner['link']; ?>"><img src="<?php echo $banner['image']; ?>" /></a>
    <?php } else { ?>
        <img src="<?php echo $banner['image']; ?>" />
    <?php } ?>
    </li> 
    <?php } ?>
  </ul>
</div>
</div>
<script>
$(document).ready(function(){
  $('.flexslider').flexslider({
	animation: "slide",
	start: function(slider){
	  $('body').removeClass('loading');
	}
  });
});
</script>