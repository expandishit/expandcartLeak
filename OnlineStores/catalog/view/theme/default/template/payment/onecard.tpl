<!--
//-----------------------------------------
// Author: Qphoria@gmail.com
// Web: http://www.OpenCartGuru.com/
//-----------------------------------------
-->
<?php if (isset($error)) { ?>
<div class="warning"><?php echo $error; ?></div>
<?php } ?>
<?php if ($testmode) { ?>
  <div class="warning"><?php echo $this->language->get('text_testmode'); ?></div>
<?php } ?>
<form action="<?php echo $action; ?>" method="post" id="checkout-form">
  <?php foreach ($hidden_fields as $key => $value) { ?>
    <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" />
  <?php } ?>
  <?php if (isset($text_fields)) { ?>
  <?php foreach ($text_fields as $key => $value) { ?>
    <input type="text" name="<?php echo $key; ?>" value="<?php echo $value; ?>" />
  <?php } ?>
  <?php } ?>
</form>
<div class="buttons" style="text-align: right;min-height:20px;">
  <div class="right">
    <?php /* <a id="button-confirm" class="button"><span><?php echo $button_continue; ?></span></a> */ ?>
	<input type="button" value="<?php echo $button_continue; ?>" id="button-confirm" class="button" />
  </div>
</div>
<script type="text/javascript"><!--
$('#button-confirm').bind('click', function() {
	$.ajax({
		type: 'GET',
		url: 'index.php?route=payment/onecard/confirm',
		success: function() {
			$('#checkout-form').submit();
		}
	});
});
//--></script>