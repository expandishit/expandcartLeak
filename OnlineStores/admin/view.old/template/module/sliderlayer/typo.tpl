<script type="text/javascript" src="view/javascript/jquery/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="view/javascript/jquery/ui/jquery-ui-1.8.16.custom.min.js"></script>
 
<link rel="stylesheet" href="<?php echo $typoFile;?>" type="text/css">
<style>
	.typo{ position: relative; width: 90%; min-height: 50px; padding:12px 0;}
	.typo .tp-caption {
	    display: block;
	    left: 0;
	    margin: 0;
	    padding: 0;
	    top: 0;
	}
	.note{ font-size: 12px;}
	.note a { color: #003A88 }
</style>
<div class="typos">
	<div class="note">
		<p><?php echo $this->language->get("To Select One, You Click The Text Of Each Typo");?></p>
	</div>
	 
	<div class="typos-wrap">	
  		
		<?php foreach( $captions as $caption ) {  ?>
  		<div class="typo"><div class="tp-caption <?php echo $caption;?>" data-class="<?php echo $caption;?>"><?php echo $this->language->get("Use This Caption Typo");?></div></div>
  		<?php } ?>	
	 </div>
</div>  

<script type="text/javascript">
$(document).on('click', 'div.typo', function() {
	parent.$('#<?php echo $field; ?>').val($("div", this).attr("data-class"));
	parent.$('#dialog').dialog('close');
			
	parent.$('#dialog').remove();	
});
</script>