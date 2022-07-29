<?php 
	$span = 12/$cols; 
?>

<div class="box pav-block bloglatest nopadding">
	<div class="box-heading"><?php echo $heading_title; ?></div>
	<div class="box-content block-content padding" >
		<?php if( !empty($blogs) ) { ?>
		<div class="pavblog-latest clearfix">
			<?php foreach( $blogs as $key => $blog ) { $key = $key + 1;?>
				<div class="span<?php echo $span;?> pavblock">
					<div class="blog-item">					
						<div class="blog-body clearfix">
							<div class="image clearfix">
								<?php if( $blog['thumb']  )  { ?>
									<img src="<?php echo $blog['thumb'];?>" title="<?php echo $blog['title'];?>" align="left"/>
								<?php } ?>
							</div>

							<h4 class="blog-title">
								<a href="<?php echo $blog['link'];?>" title="<?php echo $blog['title'];?>"><?php echo $blog['title'];?></a>
							</h4>

							<div class="description">
								<?php echo utf8_substr( $blog['description'],0, 180 );?>...
							</div>

							<!--<p>
								<a href="<?php echo $blog['link'];?>" class="readmore button"><?php echo $this->language->get('text_readmore');?></a>
							</p> -->
							
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


