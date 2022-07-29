<?php if($status) { ?>
<?php if (isset($banners) && !empty($banners)) { ?>
<div class="box-module-pavbanners <?php echo $prefix_class ?>">

	<?php if($banner_layout == 2 && count($banners) >= 6) { //show 2-rows, 3-cols?>
	<section class="col-lg-12 col-md-12 col-sm-12 col-xs-12">  
		<div class="container">
			<div class="row">	
				<?php foreach ($banners as $banner) { ?>
				<div class="col-lg-4">
					<div class="box banner-center">
						<a class="pull-right" href="<?php echo $banner['link']; ?>"><img  src="<?php echo $banner['thumb']; ?>"></a>
						<div class="description" style=" left:  50%;position: absolute;top: 50%;">
							<p><?php echo $banner['caption'][$language]; ?></p>
						</div>
					</div>
				</div>
				<?php } //end foreach ?>
			</div>
		</div> 
	</section>
	<?php } //end if banner_layout?>

	<?php if($banner_layout == 1) { //show 1-rows, 3-cols, 5-cells?>

	<section id="banner-main"> 
		<div class="row">
			<div class="col-md-4">
						<div class="box banner-center">
							<a href="<?php echo $banners[1]['link']; ?>"><img  src="<?php echo $banners[1]['thumb']; ?>"></a>
							<div class="description" >
								<p><?php echo $banners[1]['caption'][$language]; ?></p>
							</div>
						</div>
						<div class="box banner-center">
							<a href="<?php echo $banners[2]['link']; ?>"><img  src="<?php echo $banners[2]['thumb']; ?>"></a>
							<div class="description" >
								<p><?php echo $banners[2]['caption'][$language]; ?></p>
							</div>
						</div>
			</div>
			<div class="col-md-4">
				<div class="box banner-center">
					<a href="<?php echo $banners[3]['link']; ?>"><img  src="<?php echo $banners[3]['thumb']; ?>"></a>
					<div class="description" >
						<p><?php echo $banners[3]['caption'][$language]; ?></p>
					</div>
				</div>
			</div>

			<div class="col-md-4">
						<div class="box banner-center">
							<a href="<?php echo $banners[4]['link']; ?>"><img  src="<?php echo $banners[4]['thumb']; ?>"></a>
							<div class="description" >
								<p><?php echo $banners[4]['caption'][$language]; ?></p>
							</div>
						</div>
						<div class="box banner-center">
							<a href="<?php echo $banners[5]['link']; ?>"><img  src="<?php echo $banners[5]['thumb']; ?>"></a>
							<div class="description" >
								<p><?php echo $banners[5]['caption'][$language]; ?></p>
							</div>
						</div>
			</div>
		</div> 
	</section>
	<?php } //end if show 1-rows, 3-cols, 5-cells?>


</div>
<?php } ?>
<?php } ?>