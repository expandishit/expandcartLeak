<?php $id = rand(1,10); $span =  floor(12/$limit); // echo $columns;die;?>
<div id="carousel<?php echo $module; ?>" class="carousel slide pavcarousel">
	<div class="carousel-inner">
    <?php foreach ($banners as $i=> $banner) {  ?>
	<?php if( $i++%$limit==0 ) { ?>
	<div class="row-fluid item <?php if(($i-1)==0) {?>active<?php } ?>">
	<?php } ?>
   <div class="span<?php echo $span;?>"><a href="<?php echo $banner['link']; ?>"><img src="<?php echo $banner['image']; ?>" alt="<?php echo $banner['title']; ?>" title="<?php echo $banner['title']; ?>" /></a></div>
   
		<?php if( $i%$limit==0 || $i==count($banners)) { ?>
		</div>
		<?php } ?>
   <?php } ?>
	</div>
</div>
<script type="text/javascript"><!--
$('#carousel<?php echo $module; ?> ul').carousel({
	vertical: false,
	visible: <?php echo $limit; ?>,
	scroll: <?php echo $scroll; ?>
});
//--></script>