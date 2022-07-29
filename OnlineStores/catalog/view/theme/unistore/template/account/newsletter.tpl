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
			

  <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="newsletter">
    <div class="content">
      <table class="form">
        <tr>
          <td><?php echo $entry_newsletter; ?></td>
          <td><?php if ($newsletter) { ?>
            <input type="radio" name="newsletter" value="1" checked="checked" />
            <?php echo $text_yes; ?>&nbsp;
            <input type="radio" name="newsletter" value="0" />
            <?php echo $text_no; ?>
            <?php } else { ?>
            <input type="radio" name="newsletter" value="1" />
            <?php echo $text_yes; ?>&nbsp;
            <input type="radio" name="newsletter" value="0" checked="checked" />
            <?php echo $text_no; ?>
            <?php } ?></td>
        </tr>
      </table>
    </div>
    <div class="buttons">
      <div class="left"><a href="<?php echo $back; ?>" class="button"><span><?php echo $button_back; ?></span></a></div>
      <div class="right"><a onclick="$('#newsletter').submit();" class="button"><span><?php echo $button_continue; ?></span></a></div>
    </div>
  </form>
	
			</div>
			
			<?php echo $column_right; ?>
			
			<p class="clear"></p>
		
		</div>
		
		<!-- End Content Center -->
	
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?>