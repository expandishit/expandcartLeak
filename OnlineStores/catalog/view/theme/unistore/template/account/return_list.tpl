<?php echo $header; ?>
<?php $grid = 12; if($column_left != '') { $grid = $grid-3; } if($column_right != '') { $grid = $grid-3; } ?>
<?php echo $content_top; ?>
		<!-- Content Center -->
		
		<div id="content-center">
		
			<!-- Breadcrumb -->
			
			<div class="breadcrumb">
			
			    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
			    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
			    <?php } ?>
				<h2><?php echo $heading_title; ?></h2>
			
			</div>
			
			<!-- End Breadcrumb -->
			
			<?php echo $column_left; ?>
			
			<div class="grid-<?php echo $grid; ?> float-left">
			

  <?php if ($returns) { ?>
  <?php foreach ($returns as $return) { ?>
  <div class="return-list">
    <div class="return-id"><b><?php echo $text_return_id; ?></b> #<?php echo $return['return_id']; ?></div>
    <div class="return-status"><b><?php echo $text_status; ?></b> <?php echo $return['status']; ?></div>
    <div class="return-content">
      <div><b><?php echo $text_date_added; ?></b> <?php echo $return['date_added']; ?><br />
        <b><?php echo $text_order_id; ?></b> <?php echo $return['order_id']; ?></div>
      <div><b><?php echo $text_customer; ?></b> <?php echo $return['name']; ?><br />
        <b><?php echo $text_products; ?></b> <?php echo $return['products']; ?></div>
      <div class="return-info"><a href="<?php echo $return['href']; ?>" class="button"><span><?php echo $button_view; ?></span></a></div>
    </div>
  </div>
  <?php } ?>
  <div class="pagination"><?php echo $pagination; ?></div>
  <?php } else { ?>
  <div class="content"><?php echo $text_empty; ?></div>
  <?php } ?>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><span><?php echo $button_continue; ?></span></a></div>
  </div>
	
			</div>
			
			<?php echo $column_right; ?>
			
			<p class="clear"></p>
		
		</div>
		
		<!-- End Content Center -->
	
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?>