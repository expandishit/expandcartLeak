<form action="<?php echo $orderaction; ?>" method="post" enctype="multipart/form-data" id="form_orderaction" class="form-horizontal">

	<table>
		<tr>
			<td>
				<input type="file" name="import" value="" />
			</td>
			<td><?php echo $entry_orderimport; ?></td>
		</tr>
	</table>
	
	<br /><br />
	
	<table width="100%">
		
		<tr>
			<td width="40%">
				<button onclick="$('#form_orderaction').submit();" class="excel_main_button" type="button">Import</button>
			</td>
			<td width="40%"></td>
		</tr>
	
	</table>

</form>