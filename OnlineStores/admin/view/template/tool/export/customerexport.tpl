<table class="form">
  <tr>
	<td>Customer Name</td>
	<td><input type="text" name="customer_name" value="" placeholder="Customer Name" id="input-name" class="form-control" /></td>
  </tr>
  <tr>
	<td><?php echo $entry_email; ?></td>
	<td><input type="text" name="filter_email" value="" placeholder="<?php echo $entry_email; ?>" id="input-email" class="form-control" /></td>
  </tr>
  <tr>
	<td><?php echo $entry_customer_group; ?></td>
	<td>
	    <select name="filter_customer_group_id" id="input-customer-group" class="form-control">
		  <option value="*"></option>
		  <?php foreach ($customer_groups as $customer_group) { ?>
		  <option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>
		  <?php } ?>
		</select>
	</td>
   </tr>
   <tr>
	<td><?php echo $entry_status; ?></td>
	<td>
		<select name="filter_status" id="input-status" class="form-control">
		  <option value="*"></option>
		  <option value="1"><?php echo $text_enabled; ?></option>
		  <option value="0"><?php echo $text_disabled; ?></option>
		</select>
	</td>
   </tr>
   <tr>
	  <td><?php echo $entry_approved; ?></td>
	  <td>
		<select name="filter_approved" id="input-approved" class="form-control">
		  <option value="*"></option>
		  <option value="1"><?php echo $text_yes; ?></option>
		  <option value="0"><?php echo $text_no; ?></option>
		</select>
	  </td>
   </tr>
   <tr>
	 <td><?php echo $entry_ip; ?></td>
	 <td><input type="text" name="filter_ip" value="" placeholder="<?php echo $entry_ip; ?>" id="input-ip" class="form-control" /></td>
   </tr>
   <tr>
	 <td><?php echo $entry_date_added; ?></td>
	 <td><input size="12" id="date" type="text" name="filter_date_added" value=""/></td>
   </tr>
   <tr>
	 <td>Limit (Note: Export Data limit)</td>
	 <td>
		<input style="display:inline-block; width:47%;"; type="text" name="filter_start" value="0" placeholder="Start" id="input-start" class="form-control"/> -
		<input style="display:inline-block; width:47%;"; type="text" name="filter_limit" value="<?php echo $filter_limit; ?>" placeholder="Limit" id="input-limit" class="form-control" />
	 </td>
   </tr>
   <tr>
	<td colspan="2">
	  <button style="width:100%; margin-top:23px;" type="button" id="button-customer_export" class="button_export pull-right"><?php echo $button_export; ?></button>
	</td>
   </tr>
</table>