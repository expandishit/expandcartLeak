<?php 
$span = 12/$cols; 
?>

<div class="box pav-blog-latest">
	<h3 class="box-heading">
		<span><?php echo $heading_title; ?></span>
	</h3>
	<div class="box-content" >
		<?php if( !empty($blogs) ) { ?>
		<div class="pavblog-latest clearfix">
			<?php foreach( $blogs as $key => $blog ) { $key = $key + 1;?>
			<?php if( $key%$cols == 1 ) { ?>
			<div class="row-fluid">
				<?php } ?>
				<div class="span<?php echo $span;?>">
					<div class="blog-item">					
						<div class="blog-body clearfix">
							<div class="image clearfix">
								<?php if( $blog['thumb']  )  { ?>
								<a href="<?php echo $blog['link'];?>" title="<?php echo $blog['title'];?>">
									<img src="<?php echo $blog['thumb'];?>" title="<?php echo $blog['title'];?>" align="left"/>
								</a>
								<?php } ?>
							</div>

							<h4 class="blog-title">
								<a href="<?php echo $blog['link'];?>" title="<?php echo $blog['title'];?>"><?php echo $blog['title'];?></a>
							</h4>

							<div class="description">
								<?php echo utf8_substr( $blog['description'],0, 100 );?>...
							</div>

							<div class="more-link">
								<a href="<?php echo $blog['link'];?>" class="readmore button"><?php echo $this->language->get('text_readmore');?></a>
							</div>
							
						</div>	
					</div>
				</div>
				<?php if( ( $key%$cols==0 || $key == count($blogs)) ){  ?>			
			</div>
			<?php } ?>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
</div>

