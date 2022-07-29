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
		
    <div class="content"><?php echo $text_error; ?></div>
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