<?php 
	$span = 12/$cols; 
?>
<div class="box bloglatest">
	<div class="box-heading"><h2><?php echo $heading_title; ?></h2></div>
	<div class="box-content" >
		<?php if( !empty($blogs) ) { ?>
		<div class="pavblog-latest clearfix">
			<?php foreach( $blogs as $key => $blog ) { $key = $key + 1;?>
				<?php if( $key%$cols == 1 ) { ?>
				<div class="row-fluid ">
				<?php } ?>
					<div class="blog-item span<?php echo $span;?>">
						<div class="blog-header clearfix">
							<?php if( $blog['thumb']  )  { ?>
							<img src="<?php echo $blog['thumb'];?>" title="<?php echo $blog['title'];?>" align="left"/>
							<?php } ?>
						</div>
						<div class="blog-body">
							<h4 class="blog-title">
								<a href="<?php echo $blog['link'];?>" title="<?php echo $blog['title'];?>"><?php echo $blog['title'];?></a>
							</h4>
							<div class="description">
								<?php echo utf8_substr( $blog['description'],0, 200 );?>...
							</div>
							<div class="a-button" >
								<a href="<?php echo $blog['link'];?>" class="readmore"><?php echo $this->language->get('text_readmore');?></a>
							</div>
						</div>	
					</div>
					
				
			<?php if( $key%$cols==0 || $key == count($blogs) ){  ?>
				</div>
			<?php } ?>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
 </div>
