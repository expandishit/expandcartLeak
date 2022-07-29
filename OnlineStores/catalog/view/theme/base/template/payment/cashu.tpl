<?php if (isset($error)) { ?>
<div class="warning"><?php echo $error; ?></div>
<?php } ?>
<?php if ($testmode) { ?>
  <div class="warning"><?php echo $this->language->get('text_testmode'); ?></div>
<?php } ?>
<form action="<?php echo $action; ?>" method="post" id="checkout-form">
  <?php foreach ($fields as $key => $value) { ?>
    <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" />
  <?php } ?>
</form>
<div class="buttons">
  <table>
    <tr>
      <td align="right"><a onclick="confirmSubmit();" class="button"><span><?php echo $button_continue; ?></span></a></td>
    </tr>
  </table>
</div>
<script type="text/javascript"><!--
function confirmSubmit() {
	$.ajax({
		type: 'GET',
		url: 'index.php?route=payment/cashu/confirm',
		success: function() {
			$('#checkout-form').submit();
		}
	});
}
//--></script>
