<?php require(  DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" ); ?>
<?php echo $header; ?>
<?php if( $SPAN[0] ): ?>
	<div class="span<?php echo $SPAN[0];?>">
		<?php echo $column_left; ?>
	</div>
<?php endif; ?> 
<div class="span<?php echo $SPAN[1];?>">
	<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/breadcrumb.tpl" ); ?>
<div id="content"><?php echo $content_top; ?>

  <h1><?php echo $heading_title; ?></h1>
  <?php if ($products) { ?>
		<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/product/product-filter.tpl" ); ?>
		<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/product/product-list.tpl" ); ?>
  <div class="pagination"><?php echo $pagination; ?></div>
  <?php } else { ?>
  <div class="content"><?php echo $text_empty; ?></div>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php }?>
  <?php echo $content_bottom; ?>
</div>
<script type="text/javascript" src="catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js"></script>
</div> 
<?php if( $SPAN[2] ): ?>
<div class="span<?php echo $SPAN[2];?>">	
	<?php echo $column_right; ?>
</div>
<?php endif; ?>
<?php echo $footer; ?>