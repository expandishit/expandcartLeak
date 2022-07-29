<div class="ms-attributes">
	<p class="error" id="error_attributes"></p>

	<div class="attributes"></div>
	
	<div>
		<select name="attributes[0]" class="select_attribute">
			<option value="0" disabled="disabled" selected="selected"><?php echo $ms_attributes_add; ?></option>
			<?php foreach($attributes as $attribute) { ?>
			<option data-attr_name="<?php echo $attribute['name']; ?>" value="<?php echo $attribute['attribute_id']?>"><?php echo $attribute['name']; ?> - <?php echo $attribute['attribute_group']; ?></option>
			<?php } ?>
		</select>
	</div>
</div>