<div class="product-filter">
  	<div class="row-fluid">
		<div class="span5">
			<div class="display">
				<b><?php echo $text_display; ?></b> 
			 <span class="fa fa-list"></span>	<?php echo $text_list; ?> 
				<b>/</b> 
				<a onclick="display('grid');"> <span class="fa fa-th-list"></span><?php echo $text_grid; ?></a>
			</div>
			<div class="product-compare"><a href="<?php echo $compare; ?>" id="compare-total"><span class="fa fa-file"></span><?php echo $text_compare; ?></a></div>
		  </div>
		  <div class="span7">
				<div class="limit"><b><?php echo $text_limit; ?></b>
				  <select onchange="location = this.value;">
					<?php foreach ($limits as $limits) { ?>
					<?php if ($limits['value'] == $limit) { ?>
					<option value="<?php echo $limits['href']; ?>" selected="selected"><?php echo $limits['text']; ?></option>
					<?php } else { ?>
					<option value="<?php echo $limits['href']; ?>"><?php echo $limits['text']; ?></option>
					<?php } ?>
					<?php } ?>
				  </select>
				</div>
				<div class="sort"><b><?php echo $text_sort; ?></b>
				  <select onchange="location = this.value;">
					<?php foreach ($sorts as $sorts) { ?>
					<?php if ($sorts['value'] == $sort . '-' . $order) { ?>
					<option value="<?php echo $sorts['href']; ?>" selected="selected"><?php echo $sorts['text']; ?></option>
					<?php } else { ?>
					<option value="<?php echo $sorts['href']; ?>"><?php echo $sorts['text']; ?></option>
					<?php } ?>
					<?php } ?>
				  </select>
				</div>
			</div>
  	</div>
</div>
