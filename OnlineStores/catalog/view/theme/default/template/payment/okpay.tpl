<form action="<?php echo str_replace('&', '&amp;', $action); ?>" method="post" id="okpay_checkout">
  <input type="hidden" name="ok_receiver" value="<?php echo $ok_receiver; ?>" />
  <input type="hidden" name="ok_return_success" value="<?php echo $ok_return_success; ?>" />
  <input type="hidden" name="ok_return_fail" value="<?php echo $ok_return_fail; ?>" />
  <input type="hidden" name="ok_ipn" value="<?php echo $ok_ipn; ?>" />
  <input type="hidden" name="ok_invoice" value="<?php echo $ok_invoice; ?>" />
  <input type="hidden" name="ok_currency" value="<?php echo $ok_currency; ?>" />
  <input type="hidden" name="ok_item_1_price" value="<?php echo $ok_item_1_price; ?>" />
  <input type="hidden" name="ok_item_1_name" value="<?php echo $ok_item_1_name; ?>" />
</form>
<div class="buttons">
  <table>
    <tr>
      <td align="left"><a onclick="location = '<?php echo str_replace('&', '&amp;', $back); ?>'" class="button"><span><?php echo $button_back; ?></span></a></td>
      <td align="right"><a id="button_confirm" onclick="" class="button"><span><?php echo $button_confirm; ?></span></a></td>
    </tr>
  </table>
  <script>
	$(function(){
		$('#button_confirm').bind('click',function(){
			$('#okpay_checkout').submit();
		});
		
	});
  </script>
</div>
