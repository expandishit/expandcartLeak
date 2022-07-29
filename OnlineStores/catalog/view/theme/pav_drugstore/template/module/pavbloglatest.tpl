<?php 
$cols = 12/$cols; 
?>

<div class="box pav-blog-latest">
	<h3 class="box-heading">
		<span><?php echo $heading_title; ?></span>
	</h3>
	<section class="box-content nopadding">
		<?php if( !empty($blogs) ) { ?>
		<div class="pavblog-latest clearfix">				
			<?php foreach( $blogs as $key => $blog ) { $key = $key + 1;?>
			<?php if( $key%$cols == 1 ) { ?>
			<div class="row-fluid">	
				<?php } ?>
				<div class="span<?php echo $cols;?> m-blog">
					<div class="blog-item clearfix">
						<div class="blog-body pull-left">
							<?php if( $blog['thumb']  )  { ?>
							<div class="image blog-image">
								<img src="<?php echo $blog['thumb'];?>" title="<?php echo $blog['title'];?>" align="left"/>
							</div>
							<?php } ?>
							<div class="description">
								<?php echo utf8_substr( $blog['description'],0, 100 );?>...
							</div>
							<div class="view-more">
								<a class="button" href="<?php echo $blog['link'];?>" class="button"><?php echo $this->language->get('text_readmore');?></a>
							</div>
						</div>
						<div class="blog-header is-over">
							<h4 class="blog-title normal">
								<a href="<?php echo $blog['link'];?>" title="<?php echo $blog['title'];?>"><?php echo $blog['title'];?></a>
							</h4>
						</div>							
					</div>
				</div>
				<?php if( ( $key%$cols==0 || $key == count($blogs)) ){  ?>
			</div>	
			<?php } ?>
			<?php } ?>									
		</div>
		<?php } ?>
	</section>
</div>
