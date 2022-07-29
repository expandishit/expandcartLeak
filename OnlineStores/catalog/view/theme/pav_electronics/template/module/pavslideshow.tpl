	<?php $id = rand(1,10);?>
   <div id="pavslideshow<?php echo $id;?>" class="carousel slide pavslideshow">
		<div class="carousel-inner">
			 <?php foreach ($banners as $i => $banner) { ?>
				<div class="item <?php if($i==0) {?>active<?php } ?>">
					<?php if ($banner['link']) { ?>
					<a href="<?php echo $banner['link']; ?>"><img src="<?php echo $banner['image']; ?>" alt="<?php echo $banner['title']; ?>" /></a>
					<?php } else { ?>
					<img src="<?php echo $banner['image']; ?>" alt="<?php echo $banner['title']; ?>" />
					<?php } ?>
				</div>
			<?php } ?>
		</div>
		<?php if( count($banners) > 1 ){ ?>	
		<a class="carousel-control left" href="#pavslideshow<?php echo $id;?>" data-slide="prev">&lsaquo;</a>
		<a class="carousel-control right" href="#pavslideshow<?php echo $id;?>" data-slide="next">&rsaquo;</a>
		<?php } ?>
    </div>
<?php if( count($banners) > 1 ){ ?>
<script type="text/javascript"><!--
 $('#pavslideshow<?php echo $id;?>').carousel({interval:8000});
--></script>
<?php } ?>