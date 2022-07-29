<form action="<?php echo $action; ?>" method="post" id="ccavenuepay_standard_checkout" name="redirect">
	<input type="hidden" name="encRequest" id="encRequest" value="<?php echo $encrypted_data; ?>" />
	<input type="hidden" name="access_code" id="access_code" value="<?php echo $access_code; ?>" />
</form>
<div class="buttons">
	<div class="right">
		<a id="button-confirm" class="button" onclick="$('#ccavenuepay_standard_checkout').submit();">
			<span><?php echo $button_confirm; ?></span>
		</a>
	</div>
</div>