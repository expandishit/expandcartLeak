<form action="<?php echo $customeraction; ?>" method="post" enctype="multipart/form-data" id="customerimportform" class="form-horizontal">
	<table>
		<tr>
			<td><input type="file" name="import" value="" /></td>
			<td><?php echo $text_import_customer; ?></td>
		</tr>
	</table>
	<br /><br />
	<table width="100%">
		<tr>
			<td width="40%">
				<?php echo $text_password; ?><br />
				<label class="radio-inline">
					<input type="radio" checked="checked" name="password_format" value="P"/> Plain Password
				</label>
				<label class="radio-inline">
					<input type="radio" name="password_format" value="E"/> Encrypted Password
				</label>
				<div style="font-size:14px; padding:0px; margin-top:8px;" class="col-sm-10">
					<b>Note:</b> <?php echo $help_password; ?>
				</div>
			</td>
		</tr>
		<tr><td height="10px"></td></tr>
		<tr>
			<td width="40%">
				<button onclick="$('#customerimportform').submit();" class="excel_main_button" type="button">Import</button>
			</td>
			<td width="40%"></td>
		</tr>
	</table>
</form>