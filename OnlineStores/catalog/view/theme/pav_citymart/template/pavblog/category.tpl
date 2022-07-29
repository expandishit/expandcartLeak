<?php require(  DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl"); ?>
<?php echo $header; ?><?php //echo $column_left; ?><?php //echo $column_right; ?>
  
 <?php if( $SPAN[0] ): ?>
	<div class="span<?php echo $SPAN[0];?>">
		<?php echo $column_left; ?>
	</div>
<?php endif; ?> 

<div class="span<?php echo $SPAN[1];?> blog"> 

  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>

<div id="content" class="blog-category page"><?php echo $content_top; ?>

  <h2><?php echo $heading_title; ?></h2>
  
  <div class="pav-category page-content">
		<?php if( !empty($children) ) { ?>
		<div class="pav-children clearfix">
			<h3><?php echo $this->language->get('text_children');?></h3>
			<div class="children-wrap">
				
				<?php 
				$cols = (int)$config->get('children_columns');
				$span = 12/$cols;
				foreach( $children as $key => $sub )  {  $key = $key + 1; ?>
					<?php if( $key%$cols == 1 ) {  ?>
						<div class="row-fluid ">
					<?php } ?>
				
					<div class="span<?php echo $span;?>">
						<div class="children-inner">
							<h4><?php echo $sub['title']; ?></h4>
							<?php if( $sub['thumb'] ) { ?>
								<img src="<?php echo $sub['thumb'];?>" />
							<?php } ?>
							<div class="sub-description">
								<?php echo $sub['description']; ?>
							</div>
						</div>
					</div>
					<?php if( $cols > 1 && ( $key%$cols==0 || $cols == count($leading_blogs)) ){ ?>
						</div>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
		<?php } ?>
		<div class="pav-blogs">
			<?php
			$cols = $config->get('cat_columns_leading_blog');
			$span = 12/$cols;
			if( count($leading_blogs) ) { ?>
				<div class="leading-blogs clearfix">
					<?php foreach( $leading_blogs as $key => $blog ) {  $key = $key + 1;?>
					<?php if( $key%$cols == 1 ) {  ?>
						<div class="row-fluid ">
					<?php } ?>
					
					<div class="span<?php echo $span ;?>">
						<?php require( '_item.tpl' ); ?>
					</div>
					
					<?php if( $cols > 1 &&  ( $key%$cols==0 || $cols == count($leading_blogs)) ){ ?>
						</div>
					<?php } ?>
					<?php } ?>
				</div>
			<?php } ?>

			<?php
				$cols = $config->get('cat_columns_secondary_blogs');
				$span = 12/$cols;
				if ( count($secondary_blogs) ) { ?>
				<div class="secondary clearfix">
					
					<?php foreach( $secondary_blogs as $key => $blog ) {  $key = $key+1; ?>
					<?php if( $key%$cols == 1 ) {  ?>
						<div class="row-fluid ">
					<?php } ?>
					
					<div class="span<?php echo $span;?>">
					<?php require( '_item.tpl' ); ?>
					</div>
					
					<?php if( $cols > 1 && ( $key%$cols==0 || $cols == count($leading_blogs)) ){ ?>
						</div>
					<?php } ?>
					<?php } ?>
				</div>
			<?php } ?>
				
			<div class="pav-pagination pagination"><?php echo $pagination;?></div>
		</div>
  </div>
  
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  </div>
  <?php echo $content_bottom; ?>
  
  </div>
 </div> 
<?php if( $SPAN[2] ): ?>
<div class="span<?php echo $SPAN[2];?>">	
	<?php echo $column_right; ?>
</div>
<?php endif; ?>
   
<?php echo $footer; ?>