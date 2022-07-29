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
			

  <?php if ($orders) { ?>
  <?php foreach ($orders as $order) { ?>
  <div class="order-list">
    <div class="order-id"><b><?php echo $text_order_id; ?></b> #<?php echo $order['order_id']; ?></div>
    <div class="order-status"><b><?php echo $text_status; ?></b> <?php echo $order['status']; ?></div>
    <div class="order-content">
      <div><b><?php echo $text_date_added; ?></b> <?php echo $order['date_added']; ?><br />
        <b><?php echo $text_products; ?></b> <?php echo $order['products']; ?></div>
      <div><b><?php echo $text_customer; ?></b> <?php echo $order['name']; ?><br />
        <b><?php echo $text_total; ?></b> <?php echo $order['total']; ?></div>
      <div class="order-info"><a href="<?php echo $order['href']; ?>" class="button"><span><?php echo $button_view; ?></span></a></div>
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