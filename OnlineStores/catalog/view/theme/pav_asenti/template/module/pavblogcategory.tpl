<div class="box pav-blog-category no-border">
	<h3 class="box-heading highlight no-border">
		<span><?php echo $heading_title; ?></span>
	</h3>
	<nav class="box-content highlight no-border" id="pav-categorymenu">
		<?php echo $tree;?>
	</nav>
</div>
<script>
$(document).ready(function(){
		// applying the settings
		$("#pav-categorymenu li.active span.head").addClass("selected");
		$('#pav-categorymenu ul').Accordion({
			active: 'span.selected',
			header: 'span.head',
			alwaysOpen: false,
			animated: true,
			showSpeed: 400,
			hideSpeed: 800,
			event: 'click'
		});
	});

</script>