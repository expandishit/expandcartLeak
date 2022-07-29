<?php require(  DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" ); ?>
<?php echo $header; ?><?php //echo $column_left; ?><?php //echo $column_right; ?>

<?php if( $SPAN[0] ): ?>
	<div class="span<?php echo $SPAN[0];?>">
		<?php echo $column_left; ?>
	</div>
<?php endif; ?> 

<div class="span<?php echo $SPAN[1];?> blog">
  
<div id="content" class="blogs page"><?php echo $content_top; ?>

<h2><?php echo $heading_title; ?></h2>
  
  <div class="pav-filter-blogs page-content">

		<div class="pav-blogs">
			<?php
			$cols = $config->get('cat_columns_leading_blog');
			if( count($leading_blogs) ) { ?>
				<div class="leading-blogs clearfix">
					<?php foreach( $leading_blogs as $key => $blog ) { $key = $key + 1;?>
					<div class="pavcol<?php echo $cols;?>">
					<?php require( '_item.tpl' ); ?>
					</div>
					<?php if( ( $key%$cols==0 || $cols == count($leading_blogs)) ){ ?>
						<div class="clearfix"></div>
					<?php } ?>
					<?php } ?>
				</div>
			<?php } ?>

			<?php
				$cols = $config->get('cat_columns_secondary_blogs');
				if ( count($secondary_blogs) ) { ?>
				<div class="secondary clearfix">
					
					<?php foreach( $secondary_blogs as $key => $blog ) {  $key = $key+1; ?>
					<div class="pavcol<?php echo $cols;?>">
					<?php require( '_item.tpl' ); ?>
					</div>
					<?php if( ( $key%$cols==0 || $cols == count($leading_blogs)) ){ ?>
						<div class="clearfix"></div>
					<?php } ?>
					<?php } ?>
				</div>
			<?php } ?>
				
			<div class="pav-pagination pagination"><?php echo $pagination;?></div>
		</div>
  </div>
 

  <?php echo $content_bottom; ?></div>
  </div> 
<?php if( $SPAN[2] ): ?>
<div class="span<?php echo $SPAN[2];?>">	
	<?php echo $column_right; ?>
</div>
<?php endif; ?>
<?php echo $footer; ?>