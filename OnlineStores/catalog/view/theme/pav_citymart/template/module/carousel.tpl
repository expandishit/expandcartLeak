<div id="carousel<?php echo $module; ?>" class="jcarousel" >
<div class="row-fluid">
	<div class="span3"><h2>Featured Brands</h2></div>
	<div class="span9 jcarousel-wrap">		
		<ul class="jcarousel-skin-opencart">
			<?php foreach ($banners as $banner) { ?>
			<li><a href="<?php echo $banner['link']; ?>"><img src="<?php echo $banner['image']; ?>" alt="<?php echo $banner['title']; ?>" title="<?php echo $banner['title']; ?>" /></a></li>
			<?php } ?>
		</ul>
	</div>
</div>  
</div>
<script type="text/javascript"><!--
$('#carousel<?php echo $module; ?> ul').jcarousel({
	vertical: false,
	visible: <?php echo $limit; ?>,
	scroll: <?php echo $scroll; ?>
});
//--></script>