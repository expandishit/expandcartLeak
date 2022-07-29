<table class="form">
	<tr>
		<td>
			<?php echo $entry_order_id; ?>
		</td>
		<td>
			<input style="display:inline-block; width:47%;"; type="text" name="filter_to_order_id" value="" placeholder="To Order" id="input-to_order-id" class="form-control" /> -
		<input style="display:inline-block; width:47%;"; type="text" name="filter_from_order_id" value="" placeholder="From Order" id="input-from_order-id" class="form-control" />
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $entry_order_status; ?>
		</td>
		<td>
			<select name="filter_order_status" id="input-order-status" class="form-control">
				<option value="*"></option>
				<option value="0"><?php echo $text_missing; ?></option>
				<?php foreach ($order_statuses as $order_status){ ?>
				<option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $entry_total; ?>
		</td>
		<td>
			<input type="text" name="filter_total" value="" placeholder="" id="input-total" class="form-control" />
		</td>
	</tr>
	<tr>
		<td>
			Limit (Note: Export Data limit)
		</td>
		<td>
			<input style="display:inline-block; width:47%;"; type="text" name="filter_start" value="0" placeholder="Start" id="input-start" class="form-control"/> -
		<input style="display:inline-block; width:47%;"; type="text" name="filter_limit" value="<?php echo $filter_limit; ?>" placeholder="Limit" id="input-limit" class="form-control" />
		</td>
	</tr>
	<tr>
		<td>
			Date Added
		</td>
		<td>
			<input style="display:inline-block; width:47%;"; id="to_date_addedd" type="text" name="filter_to_date_added" placeholder="To Date Added" value=""/> - <input style="display:inline-block; width:47%;"; id="from_date_addedd" type="text" name="filter_from_date_added" placeholder="From Date Added" value=""/>
		</td>
	</tr>
	<tr>
		<td>
			Date Modified
		</td>
		<td>
			<input style="display:inline-block; width:47%;"; id="filter_to_date_modified" type="text" name="filter_to_date_modified" placeholder="To Date Modified" value=""/> - <input style="display:inline-block; width:47%;"; id="filter_form_date_modified" type="text" name="filter_form_date_modified" placeholder="From Date Modified" value=""/>
		</td>
	</tr>
	
	<tr>
		<td colspan="2">
			 <button style="width:100%; margin-top:23px;" type="button" id="button-order_export" class="button_export"><?php echo $button_export; ?></button>
		</td>
	</tr>
	
</table>