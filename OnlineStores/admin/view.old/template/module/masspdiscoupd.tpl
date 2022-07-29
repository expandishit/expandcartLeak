<?php echo $header; ?>
<div id="content">
<ol class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php if ($breadcrumb === end($breadcrumbs)) { ?>
    <li class="active">
        <?php } else { ?>
    <li>
        <?php } ?>
        <a href="<?php echo $breadcrumb['href']; ?>">
            <?php if ($breadcrumb === reset($breadcrumbs)) { ?>
            <?php echo $breadcrumb['text']; ?>
            <?php } else { ?>
            <span><?php echo $breadcrumb['text']; ?></span>
            <?php } ?>
        </a>
    </li>
    <?php } ?>
</ol>
<?php if ($error_warning) { ?>
<script>
    var notificationString = '<?php echo $error_warning; ?>';
    var notificationType = 'warning';
</script>
<?php } ?>

<?php if ($success) { ?>
<script>
    var notificationString = '<?php echo $success; ?>';
    var notificationType = 'success';
</script>
<?php } ?>
  
  <div class="box">
    
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
    </div>
    
    <div class="content">
    	
    	<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
    	
    	<table class="table table-hover dataTable no-footer">
    	
    	<thead>
    	<tr>
    	  <td colspan="5">
    	  <h3><?php echo $masstxt_p_filters_h; ?></h3>
    	  </td>
    	</tr>
    	</thead>
    	
    	<tbody>

    	<tr>
    	  <td class="left" style="width:256px;">
    	  <strong><?php echo $masstxt_name; ?></strong>
    	  </td>
    	  <td colspan="4" class="left">
    	  <input size="22" type="text" value="<?php echo $filter_name; ?>" name="filter_name">
    	  <span style="color:#666666;font-size:11px;">&nbsp;<?php echo $masstxt_name_help; ?></span>
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_tag; ?></strong>
    	  </td>
    	  <td colspan="4" class="left">
    	  <input size="22" type="text" value="<?php echo $filter_tag; ?>" name="filter_tag">
    	  <span style="color:#666666;font-size:11px;">&nbsp;<?php echo $masstxt_tag_help; ?></span>
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_model; ?></strong>
    	  </td>
    	  <td colspan="4" class="left">
    	  <input size="22" type="text" value="<?php echo $filter_model; ?>" name="filter_model">
    	  <span style="color:#666666;font-size:11px;">&nbsp;<?php echo $masstxt_model_help; ?></span>
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_categories; ?></strong>
    	  <br />
    	  <span class="help"><?php echo $masstxt_unselect_all_to_ignore; ?></span>
    	  </td>
    	  <td colspan="4" class="left">
    	  <div class="scrollbox" style="width:510px !important;">
                  <?php $class = 'odd'; ?>
                  
                  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                  <div class="<?php echo $class; ?>">
                    <?php if (in_array(0, $product_category)) { ?>
                    <input type="checkbox" name="product_category[]" value="0" checked="checked" /><?php echo $masstxt_none_cat; ?>
                    <?php } else { ?>
                    <input type="checkbox" name="product_category[]" value="0" /><?php echo $masstxt_none_cat; ?>
                    <?php } ?>
                  </div>
                  
                  <?php foreach ($categories as $category) { ?>
                  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                  <div class="<?php echo $class; ?>">
                    <?php if (in_array($category['category_id'], $product_category)) { ?>
                    <input type="checkbox" name="product_category[]" value="<?php echo $category['category_id']; ?>" checked="checked" />
                    <?php echo $category['name']; ?>
                    <?php } else { ?>
                    <input type="checkbox" name="product_category[]" value="<?php echo $category['category_id']; ?>" />
                    <?php echo $category['name']; ?>
                    <?php } ?>
                  </div>
                  <?php } ?>
                </div>
                <a onclick="$(this).parent().find(':checkbox').attr('checked', true);"><?php echo $masstxt_select_all; ?></a> / <a onclick="$(this).parent().find(':checkbox').attr('checked', false);"><?php echo $masstxt_unselect_all; ?></a>
    	  </td>
    	</tr>
    	
    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_manufacturers; ?></strong>
    	  <br />
    	  <span class="help"><?php echo $masstxt_unselect_all_to_ignore; ?></span>
    	  </td>
    	  <td colspan="4" class="left">
    	  <div class="scrollbox" style="width:510px !important;">
                  <?php $class = 'odd'; ?>
                  
                  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                  <div class="<?php echo $class; ?>">
                    <?php if (in_array(0, $manufacturer_ids)) { ?>
                    <input type="checkbox" name="manufacturer_ids[]" value="0" checked="checked" /><?php echo $masstxt_none; ?>
                    <?php } else { ?>
                    <input type="checkbox" name="manufacturer_ids[]" value="0" /><?php echo $masstxt_none; ?>
                    <?php } ?>
                  </div>
                  
                  <?php foreach ($manufacturers as $manufacturer) { ?>
                  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                  <div class="<?php echo $class; ?>">
                    <?php if (in_array($manufacturer['manufacturer_id'], $manufacturer_ids)) { ?>
                    <input type="checkbox" name="manufacturer_ids[]" value="<?php echo $manufacturer['manufacturer_id']; ?>" checked="checked" />
                    <?php echo $manufacturer['name']; ?>
                    <?php } else { ?>
                    <input type="checkbox" name="manufacturer_ids[]" value="<?php echo $manufacturer['manufacturer_id']; ?>" />
                    <?php echo $manufacturer['name']; ?>
                    <?php } ?>
                  </div>
                  <?php } ?>
                </div>
                <a onclick="$(this).parent().find(':checkbox').attr('checked', true);"><?php echo $masstxt_select_all; ?></a> / <a onclick="$(this).parent().find(':checkbox').attr('checked', false);"><?php echo $masstxt_unselect_all; ?></a>
    	  </td>
    	</tr>
    	
    	<?php if(version_compare(VERSION, '1.5.4.1', '>')) { ?>
    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_p_filters; ?></strong>
    	  <br />
    	  <span class="help"><?php echo $masstxt_unselect_all_to_ignore; ?></span>
    	  </td>
    	  <td colspan="4" class="left">
    	  <?php if($p_filters) { ?>
    	  <div class="scrollbox" style="width:510px !important;">
                  <?php $class = 'odd'; ?>
                  
                  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                  <div class="<?php echo $class; ?>">
                    <?php if (in_array(0, $filters_ids)) { ?>
                    <input type="checkbox" name="filters_ids[]" value="0" checked="checked" /><?php echo $masstxt_none_fil; ?>
                    <?php } else { ?>
                    <input type="checkbox" name="filters_ids[]" value="0" /><?php echo $masstxt_none_fil; ?>
                    <?php } ?>
                  </div>

                  <?php foreach ($p_filters as $p_filter) { ?>
                  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                  <div class="<?php echo $class; ?>">
                    <?php if (in_array($p_filter['filter_id'], $filters_ids)) { ?>
                    <input type="checkbox" name="filters_ids[]" value="<?php echo $p_filter['filter_id']; ?>" checked="checked" />
                    <?php echo $p_filter['group'].' &gt; '.$p_filter['name']; ?>
                    <?php } else { ?>
                    <input type="checkbox" name="filters_ids[]" value="<?php echo $p_filter['filter_id']; ?>" />
                    <?php echo $p_filter['group'].' &gt; '.$p_filter['name']; ?>
                    <?php } ?>
                  </div>
                  <?php } ?>
                </div>
                <a onclick="$(this).parent().find(':checkbox').attr('checked', true);"><?php echo $masstxt_select_all; ?></a> / <a onclick="$(this).parent().find(':checkbox').attr('checked', false);"><?php echo $masstxt_unselect_all; ?></a>
    	  <?php } else { echo $masstxt_p_filters_none; } ?>
    	  </td>
    	</tr>
	<?php } ?>
    	
    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_price; ?></strong>
    	  <br />
    	  <span class="help"><?php echo $masstxt_price_help; ?></span>
    	  </td>
    	  <td class="right">
    	  <?php echo $masstxt_greater_than_or_equal; ?>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td class="left">
    	  <input size="10" type="text" value="<?php echo $price_mmarese; ?>" name="price_mmarese">
    	  </td>
    	  <td class="right">
    	  <?php echo $masstxt_less_than_or_equal; ?>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td class="left">
    	  <input size="10" type="text" value="<?php echo $price_mmicse; ?>" name="price_mmicse">
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_discount; ?></strong>
    	  </td>
    	  <td class="right">
    	  
    	  <div class="customer-group-box">
    	  <?php echo $masstxt_customer_group; ?><br />
    	  <select name="d_cust_group_filter">
    	  <option value="any"<?php if ($d_cust_group_filter=='any') { echo ' selected="selected"'; } ?>><?php echo $masstxt_all; ?></option>
    	  <?php foreach ($customer_groups as $customer_group) { ?>
    	  <option value="<?php echo $customer_group['customer_group_id']; ?>"<?php if ($customer_group['customer_group_id']==$d_cust_group_filter) { echo ' selected="selected"'; } ?>><?php echo $customer_group['name']; ?></option>
    	  <?php } ?>
    	  </select>
    	  </div>
    	  
    	  <?php echo $masstxt_greater_than_or_equal; ?>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td class="left">
    	  <input size="10" type="text" value="<?php echo $d_price_mmarese; ?>" name="d_price_mmarese">
    	  </td>
    	  <td class="right">
    	  <?php echo $masstxt_less_than_or_equal; ?>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td class="left">
    	  <input size="10" type="text" value="<?php echo $d_price_mmicse; ?>" name="d_price_mmicse">
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_special; ?></strong>
    	  </td>
    	  <td class="right">
    	  
    	  <div class="customer-group-box">
    	  <?php echo $masstxt_customer_group; ?><br />
    	  <select name="s_cust_group_filter">
    	  <option value="any"<?php if ($s_cust_group_filter=='any') { echo ' selected="selected"'; } ?>><?php echo $masstxt_all; ?></option>
    	  <?php foreach ($customer_groups as $customer_group) { ?>
    	  <option value="<?php echo $customer_group['customer_group_id']; ?>"<?php if ($customer_group['customer_group_id']==$s_cust_group_filter) { echo ' selected="selected"'; } ?>><?php echo $customer_group['name']; ?></option>
    	  <?php } ?>
    	  </select>
    	  </div>
    	  
    	  <?php echo $masstxt_greater_than_or_equal; ?>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td class="left">
    	  <input size="10" type="text" value="<?php echo $s_price_mmarese; ?>" name="s_price_mmarese">
    	  </td>
    	  <td class="right">
    	  <?php echo $masstxt_less_than_or_equal; ?>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td class="left">
    	  <input size="10" type="text" value="<?php echo $s_price_mmicse; ?>" name="s_price_mmicse">
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_tax_class; ?></strong>
    	  </td>
    	  <td colspan="4" class="left">
    	  <select name="tax_class_filter">
    	  <option value="any"<?php if ($tax_class_filter=='any') { echo ' selected="selected"'; } ?>><?php echo $masstxt_ignore_this; ?></option>
    	  <option value="0"<?php if ($tax_class_filter=='0') { echo ' selected="selected"'; } ?>> <?php echo $masstxt_none; ?> </option>
    	  <?php foreach ($tax_classes as $tax_class) { ?>
    	  <option value="<?php echo $tax_class['tax_class_id']; ?>"<?php if ($tax_class['tax_class_id']==$tax_class_filter) { echo ' selected="selected"'; } ?>><?php echo $tax_class['title']; ?></option>
    	  <?php } ?>
    	  </select>
    	  </td>
    	</tr>
    	
    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_quantity; ?></strong>
    	  </td>
    	  <td class="right">
    	  <?php echo $masstxt_greater_than_or_equal; ?>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td class="left">
    	  <input size="10" type="text" value="<?php echo $stock_mmarese; ?>" name="stock_mmarese">
    	  </td>
    	  <td class="right">
    	  <?php echo $masstxt_less_than_or_equal; ?>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td class="left">
    	  <input size="10" type="text" value="<?php echo $stock_mmicse; ?>" name="stock_mmicse">
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_minimum_quantity; ?></strong>
    	  </td>
    	  <td class="right">
    	  <?php echo $masstxt_greater_than_or_equal; ?>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td class="left">
    	  <input size="10" type="text" value="<?php echo $min_q_mmarese; ?>" name="min_q_mmarese">
    	  </td>
    	  <td class="right">
    	  <?php echo $masstxt_less_than_or_equal; ?>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td class="left">
    	  <input size="10" type="text" value="<?php echo $min_q_mmicse; ?>" name="min_q_mmicse">
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_subtract_stock; ?></strong>
    	  </td>
    	  <td colspan="4" class="left">
    	  <select name="subtract_filter">
    	  <option value="any"<?php if ($subtract_filter=='any') { echo ' selected="selected"'; } ?>><?php echo $masstxt_ignore_this; ?></option>
    	  <option value="1"<?php if ($subtract_filter=='1') { echo ' selected="selected"'; } ?>><?php echo $masstxt_yes; ?></option>
    	  <option value="0"<?php if ($subtract_filter=='0') { echo ' selected="selected"'; } ?>><?php echo $masstxt_no; ?></option>
    	  </select>
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_out_of_stock_status; ?></strong>
    	  </td>
    	  <td colspan="4" class="left">
    	  <select name="stock_status_filter">
    	  <option value="any"<?php if ($stock_status_filter=='any') { echo ' selected="selected"'; } ?>><?php echo $masstxt_ignore_this; ?></option>
    	  <?php foreach ($stock_statuses as $stock_status) { ?>
    	  <option value="<?php echo $stock_status['stock_status_id']; ?>"<?php if ($stock_status['stock_status_id']==$stock_status_filter) { echo ' selected="selected"'; } ?>><?php echo $stock_status['name']; ?></option>
    	  <?php } ?>
    	  </select>
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_requires_shipping; ?></strong>
    	  </td>
    	  <td colspan="4" class="left">
    	  <select name="shipping_filter">
    	  <option value="any"<?php if ($shipping_filter=='any') { echo ' selected="selected"'; } ?>><?php echo $masstxt_ignore_this; ?></option>
    	  <option value="1"<?php if ($shipping_filter=='1') { echo ' selected="selected"'; } ?>><?php echo $masstxt_yes; ?></option>
    	  <option value="0"<?php if ($shipping_filter=='0') { echo ' selected="selected"'; } ?>><?php echo $masstxt_no; ?></option>
    	  </select>
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_date_available; ?></strong>
    	  </td>
    	  <td class="right">
    	  <?php echo $masstxt_greater_than_or_equal; ?>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td class="left">
    	  <input class="date" size="14" type="text" value="<?php echo $date_mmarese; ?>" name="date_mmarese">
    	  </td>
    	  <td class="right">
    	  <?php echo $masstxt_less_than_or_equal; ?>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td class="left">
    	  <input class="date" size="14" type="text" value="<?php echo $date_mmicse; ?>" name="date_mmicse">
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_date_added; ?></strong>
    	  </td>
    	  <td class="right">
    	  <?php echo $masstxt_greater_than_or_equal; ?>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td class="left">
    	  <input class="datetime" size="14" type="text" value="<?php echo $date_added_mmarese; ?>" name="date_added_mmarese">
    	  </td>
    	  <td class="right">
    	  <?php echo $masstxt_less_than_or_equal; ?>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td class="left">
    	  <input class="datetime" size="14" type="text" value="<?php echo $date_added_mmicse; ?>" name="date_added_mmicse">
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_date_modified; ?></strong>
    	  </td>
    	  <td class="right">
    	  <?php echo $masstxt_greater_than_or_equal; ?>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td class="left">
    	  <input class="datetime" size="14" type="text" value="<?php echo $date_modified_mmarese; ?>" name="date_modified_mmarese">
    	  </td>
    	  <td class="right">
    	  <?php echo $masstxt_less_than_or_equal; ?>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td class="left">
    	  <input class="datetime" size="14" type="text" value="<?php echo $date_modified_mmicse; ?>" name="date_modified_mmicse">
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_status; ?></strong>
    	  </td>
    	  <td colspan="4" class="left">
    	  <select name="prod_status">
    	  <option value="any"<?php if ($prod_status=='any') { echo ' selected="selected"'; } ?>><?php echo $masstxt_ignore_this; ?></option>
    	  <option value="1"<?php if ($prod_status=='1') { echo ' selected="selected"'; } ?>><?php echo $masstxt_enabled; ?></option>
    	  <option value="0"<?php if ($prod_status=='0') { echo ' selected="selected"'; } ?>><?php echo $masstxt_disabled; ?></option>
    	  </select>
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_store; ?></strong>
    	  </td>
    	  <td colspan="4" class="left">
    	  <select name="store_filter">
    	  <option value="any"<?php if ($store_filter=='any') { echo ' selected="selected"'; } ?>><?php echo $masstxt_ignore_this; ?></option>
    	  <option value="0"<?php if ($store_filter=='0') { echo ' selected="selected"'; } ?>><?php echo $masstxt_default; ?></option>
    	  <?php foreach ($stores as $store) { ?>
    	  <option value="<?php echo $store['store_id']; ?>"<?php if ($store['store_id']==$store_filter) { echo ' selected="selected"'; } ?>><?php echo $store['name']; ?></option>
    	  <?php } ?>
    	  </select>
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_with_attribute; ?></strong>
    	  </td>
    	  <td colspan="4" class="left">
    	  <select name="filter_attr">
    	  <option value="any"<?php if ($filter_attr=='any') { echo ' selected="selected"'; } ?>><?php echo $masstxt_ignore_this; ?></option>
    	  <?php foreach ($all_attributes as $attrib) { ?>
    	  <option value="<?php echo $attrib['attribute_id']; ?>"<?php if ($attrib['attribute_id']==$filter_attr) { echo ' selected="selected"'; } ?>><?php echo $attrib['attribute_group']." > ".$attrib['name']; ?></option>
    	  <?php } ?>
    	  </select>
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_with_attribute_value; ?></strong>
    	  <br />
    	  <span class="help"><?php echo $masstxt_leave_empty_to_ignore; ?></span>
    	  </td>
    	  <td colspan="4" class="left">
    	  <textarea name="filter_attr_val" cols="40" rows="2"><?php echo $filter_attr_val; ?></textarea>
    	  <span style="color:#666666;font-size:11px;">&nbsp;<?php echo $masstxt_with_attribute_value_help; ?></span>
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_with_this_option; ?></strong>
    	  </td>
    	  <td colspan="4" class="left">
    	  <select name="filter_opti">
    	  <option value="any"<?php if ($filter_opti=='any') { echo ' selected="selected"'; } ?>><?php echo $masstxt_ignore_this; ?></option>
    	  <?php foreach ($all_options as $option) { ?>
    	  <option value="<?php echo $option['option_id']; ?>"<?php if ($option['option_id']==$filter_opti) { echo ' selected="selected"'; } ?>><?php echo $option['name']; ?></option>
    	  <?php } ?>
    	  </select>
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_with_this_option_value; ?></strong>
    	  </td>
    	  <td colspan="4" class="left">
    	  <select name="filter_opti_val">
    	  <option value="any"<?php if ($filter_opti_val=='any') { echo ' selected="selected"'; } ?>><?php echo $masstxt_ignore_this; ?></option>
    	  <?php foreach ($all_optval as $optval) { ?>
    	  <option value="<?php echo $optval['option_value_id']; ?>"<?php if ($optval['option_value_id']==$filter_opti_val) { echo ' selected="selected"'; } ?>><?php echo $optval['o_name']." > ".$optval['ov_name']; ?></option>
    	  <?php } ?>
    	  </select>
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  
    	  
    	  <br />
    	  <?php echo $masstxt_max_prod_pag1; ?> 
    	  <input size="2" type="text" value="<?php echo $max_prod_pag; ?>" name="max_prod_pag"> 
    	  <?php echo $masstxt_max_prod_pag2; ?><br />
    	  
    	  <?php echo $masstxt_show_page_of1; ?>
    	  <select name="curent_pag">
    	  <?php for ($pg=1;$pg<=$total_pag;$pg++) { ?>
    	  <option value="<?php echo $pg; ?>"<?php if ($pg==$curent_pag) { echo ' selected="selected"'; } ?>><?php echo $pg; ?></option>
    	  <?php } ?>
    	  </select>
    	  <?php echo $masstxt_show_page_of2; ?><?php echo $total_pag; ?><br /><br />

    	  
    	  <input type="submit" value="<?php echo $masstxt_filter_products_button; ?>" name="lista_prod" style="color:#00C;font-size:13px;font-weight:bold;padding-top:8px;padding-bottom:8px;">

    	  <br /><br />
    	  <?php echo $total_prod_filtered; ?><?php echo $masstxt_total_prod_res; ?><br /><br />
    	  <span class="counter" style="font-weight:bold;">0</span><?php echo $masstxt_prod_sel_for_upd; ?><br />
    	  <br />


    	  </td>
    	  <td colspan="4" class="left">
    	  <div style="max-height:350px; overflow:auto; border-top:1px solid #DDDDDD;">
    	  <table class="table table-hover dataTable no-footer" style="margin-bottom:0 !important;">
          <thead>
            <tr>
              <td style="padding:4px;background-color:#EFEFEF;" width="1"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" checked="checked" name="sel_desel_all" /></td>
              <td style="padding:4px;background-color:#EFEFEF;"><?php echo $masstxt_table_name; ?></td>
              <td style="padding:4px;background-color:#EFEFEF;"><?php echo $masstxt_table_model; ?></td>
              <td style="padding:4px;text-align:right;background-color:#EFEFEF;"><?php echo $masstxt_table_price; ?></td>
              <td style="padding:4px;text-align:right;background-color:#EFEFEF;"><?php echo $masstxt_table_quantity; ?></td>
              <td style="padding:4px;background-color:#EFEFEF;"><?php echo $masstxt_table_status; ?></td>
            </tr>
          </thead>
          <tbody class="products_to_upd">
          <?php if ($arr_lista_prod) { ?>
            <?php foreach ($arr_lista_prod as $product) { ?>
            <tr>
              <td style="padding:4px;"><input type="checkbox" name="selected[]" value="<?php echo $product['product_id']; ?>" checked="checked" /></td>
              <td style="padding:4px;"><?php echo $product['name']; ?></td>
              <td style="padding:4px;"><?php echo $product['model']; ?></td>
              <td style="padding:4px;text-align:right;"><?php echo $product['price']; ?></td>
              <td style="padding:4px;text-align:right;"><?php if ($product['quantity'] <= 0) { ?>
                <span style="color: #FF0000;"><?php echo $product['quantity']; ?></span>
                <?php } elseif ($product['quantity'] <= 5) { ?>
                <span style="color: #FFA500;"><?php echo $product['quantity']; ?></span>
                <?php } else { ?>
                <span style="color: #008000;"><?php echo $product['quantity']; ?></span>
                <?php } ?></td>
              <td style="padding:4px;"><?php if ($product['status']==1) { ?>
                <span style="color: #008000;"><?php echo $masstxt_enabled; ?></span>
                <?php } else { ?>
                <span style="color: #FF0000;"><?php echo $masstxt_disabled; ?></span>
                <?php } ?></td>
            </tr>
            <?php } ?>
            <?php } else { ?>
            <tr>
              <td class="center" colspan="6"> </td>
            </tr>
            <?php } ?>
        </tbody>
        </table>
    	</div>
    	
    	  </td>
    	</tr>

        </tbody>
        </table>    	
    	



    	<table class="table table-hover dataTable no-footer">
    	
    	<thead>
    	<tr>
    	  <td colspan="2">
    	  <h3><?php echo $masstxt_p_updates; ?></h3>
    	  </td>
    	</tr>
    	</thead>
    	
    	<tbody>
     	<tr>
    	  <td colspan="2" class="left">
    	  <span style="color:#666666;font-size:11px;">
    	  <?php echo $masstxt_upd_help1; ?>
    	  </span>
    	  </td>
    	</tr>
 
    	<tr>
    	  <td colspan="2" class="left" style="background-color:#EFEFEF;font-size:14px;">
    	  <strong><?php echo $masstxt_upd_discount; ?></strong>
    	  </td>
    	</tr>
    	
    	<tr>
    	  <td class="left" style="width:114px;">
    	  <strong><?php echo $masstxt_upd_set_new_discounts; ?></strong>
    	  </td>
    	  
    	  <td class="left">
          <div id="tab-discount">
          <table id="discount" class="table table-hover dataTable no-footer">
            <thead>
              <tr>
                <td class="left" style="background-color:#EFEFEF;"><?php echo $masstxt_upd_customer_group; ?></td>
                <td class="right" style="background-color:#EFEFEF;"><?php echo $masstxt_upd_quantity; ?></td>
                <td class="right" style="background-color:#EFEFEF;"><?php echo $masstxt_upd_priority; ?></td>
                <td class="right" style="background-color:#EFEFEF;"><?php echo $masstxt_upd_price_type; ?></td>
                <td class="left" style="background-color:#EFEFEF;"><?php echo $masstxt_upd_date_start; ?></td>
                <td class="left" style="background-color:#EFEFEF;"><?php echo $masstxt_upd_date_end; ?></td>
                <td style="background-color:#EFEFEF;"></td>
              </tr>
            </thead>
            <?php $discount_row = 0; ?>
            <?php foreach ($product_discounts as $product_discount) { ?>
            <tbody id="discount-row<?php echo $discount_row; ?>">
              <tr>
                <td class="left"><select name="product_discount[<?php echo $discount_row; ?>][customer_group_id]">
                    <?php foreach ($customer_groups as $customer_group) { ?>
                    <?php if ($customer_group['customer_group_id'] == $product_discount['customer_group_id']) { ?>
                    <option value="<?php echo $customer_group['customer_group_id']; ?>" selected="selected"><?php echo $customer_group['name']; ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>
                    <?php } ?>
                    <?php } ?>
                  </select></td>
                <td class="right"><input type="text" name="product_discount[<?php echo $discount_row; ?>][quantity]" value="<?php echo $product_discount['quantity']; ?>" size="2" /></td>
                <td class="right"><input type="text" name="product_discount[<?php echo $discount_row; ?>][priority]" value="<?php echo $product_discount['priority']; ?>" size="2" /></td>
                <td class="right">
                <?php if (!isset($product_discount['price_mode'])) { $product_discount['price_mode']='flat'; } ?>
                <select name="product_discount[<?php echo $discount_row; ?>][price_mode]">
    	  	<option value="flat"<?php if ($product_discount['price_mode']=='flat') { echo ' selected="selected"'; } ?>><?php echo $masstxt_upd_flat; ?></option>
    	  	<option value="sub"<?php if ($product_discount['price_mode']=='sub') { echo ' selected="selected"'; } ?>><?php echo $masstxt_upd_subtraction_price; ?></option>
    	  	<option value="per"<?php if ($product_discount['price_mode']=='per') { echo ' selected="selected"'; } ?>><?php echo $masstxt_upd_percentage_price; ?></option>
    	  	</select>
                <input type="text" name="product_discount[<?php echo $discount_row; ?>][price]" value="<?php echo $product_discount['price']; ?>" size="6" /></td>
                <td class="left"><input type="text" name="product_discount[<?php echo $discount_row; ?>][date_start]" value="<?php echo $product_discount['date_start']; ?>" class="date" size="8" /></td>
                <td class="left"><input type="text" name="product_discount[<?php echo $discount_row; ?>][date_end]" value="<?php echo $product_discount['date_end']; ?>" class="date"  size="8"/></td>
                <td class="left"><a onclick="$('#discount-row<?php echo $discount_row; ?>').remove();" class="button btn btn-primary"><span><?php echo $masstxt_remove; ?></span></a></td>
              </tr>
            </tbody>
            <?php $discount_row++; ?>
            <?php } ?>
            <tfoot>
              <tr>
                <td colspan="6"></td>
                <td class="left"><a onclick="addDiscount();" class="button btn btn-primary"><span><?php echo $masstxt_upd_add_discount; ?></span></a></td>
              </tr>
            </tfoot>
          </table>
          </div>
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_update_mode; ?></strong>
    	  </td>

    	  <td class="left">
    	  <input type="radio"<?php if ($upd_mode=='ad') { echo ' checked="checked"'; } ?> value="ad" name="upd_mode" id="rg1">
    	  <label for="rg1"> <?php echo $masstxt_upd_mode_discount_ad; ?></label>
    	  <span style="color:#666666;font-size:11px;">&nbsp;<?php echo $masstxt_upd_mode_discount_ad_help; ?></span>
    	  <br />
    	  <input type="radio"<?php if ($upd_mode=='re') { echo ' checked="checked"'; } ?> value="re" name="upd_mode" id="rg4">
    	  <label for="rg4"> <?php echo $masstxt_upd_mode_discount_re; ?></label>
    	  <span style="color:#666666;font-size:11px;">&nbsp;<?php echo $masstxt_upd_mode_discount_re_help; ?></span>
    	  </td>
    	</tr>

     	<tr>
    	  <td colspan="2" style="padding:4px;">
    	  </td>
    	</tr>
    	
    	<tr>
    	  <td colspan="2" class="left" style="background-color:#EFEFEF;font-size:14px;">
    	  <strong><?php echo $masstxt_upd_special; ?></strong>
    	  </td>
    	</tr>
    	
    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_upd_set_new_special; ?></strong>
    	  </td>
    	  
    	  <td class="left">
        <div id="tab-special">
          <table id="special" class="table table-hover dataTable no-footer">
            <thead>
              <tr>
                <td class="left" style="background-color:#EFEFEF;"><?php echo $masstxt_upd_customer_group; ?></td>
                <td class="right" style="background-color:#EFEFEF;"><?php echo $masstxt_upd_priority; ?></td>
                <td class="right" style="background-color:#EFEFEF;"><?php echo $masstxt_upd_price_type; ?></td>
                <td class="left" style="background-color:#EFEFEF;"><?php echo $masstxt_upd_date_start; ?></td>
                <td class="left" style="background-color:#EFEFEF;"><?php echo $masstxt_upd_date_end; ?></td>
                <td style="background-color:#EFEFEF;"></td>
              </tr>
            </thead>
            <?php $special_row = 0; ?>
            <?php foreach ($product_specials as $product_special) { ?>
            <tbody id="special-row<?php echo $special_row; ?>">
              <tr>
                <td class="left"><select name="product_special[<?php echo $special_row; ?>][customer_group_id]">
                    <?php foreach ($customer_groups as $customer_group) { ?>
                    <?php if ($customer_group['customer_group_id'] == $product_special['customer_group_id']) { ?>
                    <option value="<?php echo $customer_group['customer_group_id']; ?>" selected="selected"><?php echo $customer_group['name']; ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>
                    <?php } ?>
                    <?php } ?>
                  </select></td>
                <td class="right"><input type="text" name="product_special[<?php echo $special_row; ?>][priority]" value="<?php echo $product_special['priority']; ?>" size="2" /></td>
                <td class="right">
                <?php if (!isset($product_special['price_mode'])) { $product_special['price_mode']='flat'; } ?>
                <select name="product_special[<?php echo $special_row; ?>][price_mode]">
    	  	<option value="flat"<?php if ($product_special['price_mode']=='flat') { echo ' selected="selected"'; } ?>><?php echo $masstxt_upd_flat; ?></option>
    	  	<option value="sub"<?php if ($product_special['price_mode']=='sub') { echo ' selected="selected"'; } ?>><?php echo $masstxt_upd_subtraction_price; ?></option>
    	  	<option value="per"<?php if ($product_special['price_mode']=='per') { echo ' selected="selected"'; } ?>><?php echo $masstxt_upd_percentage_price; ?></option>
    	  	</select>
                <input type="text" name="product_special[<?php echo $special_row; ?>][price]" value="<?php echo $product_special['price']; ?>" size="6" /></td>
                <td class="left"><input type="text" name="product_special[<?php echo $special_row; ?>][date_start]" value="<?php echo $product_special['date_start']; ?>" class="date" size="8" /></td>
                <td class="left"><input type="text" name="product_special[<?php echo $special_row; ?>][date_end]" value="<?php echo $product_special['date_end']; ?>" class="date" size="8" /></td>
                <td class="left"><a onclick="$('#special-row<?php echo $special_row; ?>').remove();" class="button btn btn-primary"><span><?php echo $masstxt_remove; ?></span></a></td>
              </tr>
            </tbody>
            <?php $special_row++; ?>
            <?php } ?>
            <tfoot>
              <tr>
                <td colspan="5"></td>
                <td class="left"><a onclick="addSpecial();" class="button btn btn-primary"><span><?php echo $masstxt_upd_add_special; ?></span></a></td>
              </tr>
            </tfoot>
          </table>
        </div>
    	  </td>
    	</tr>

    	<tr>
    	  <td class="left">
    	  <strong><?php echo $masstxt_update_mode; ?></strong>
    	  </td>

    	  <td class="left">
    	  <input type="radio"<?php if ($upd_mode2=='ad') { echo ' checked="checked"'; } ?> value="ad" name="upd_mode2" id="rg12">
    	  <label for="rg12"> <?php echo $masstxt_upd_mode_special_ad; ?></label>
    	  <span style="color:#666666;font-size:11px;">&nbsp;<?php echo $masstxt_upd_mode_special_ad_help; ?></span>
    	  <br />
    	  <input type="radio"<?php if ($upd_mode2=='re') { echo ' checked="checked"'; } ?> value="re" name="upd_mode2" id="rg42">
    	  <label for="rg42"> <?php echo $masstxt_upd_mode_special_re; ?></label>
    	  <span style="color:#666666;font-size:11px;">&nbsp;<?php echo $masstxt_upd_mode_special_re_help; ?></span>
    	  </td>
    	</tr>

     	<tr>
    	  <td colspan="2" style="padding:4px;">
    	  </td>
    	</tr>

    	<tr>
    	  <td colspan="2" class="center" style="color:#FF0000;">
    	  
    	  <span class="counter" style="font-weight:bold;">0</span>
    	  <?php echo $masstxt_mass_update_button_top1; ?>
    	  <?php echo $curent_pag; ?>
    	  <?php echo $masstxt_mass_update_button_top2; ?>
    	  <?php echo $total_pag; ?>
    	  <?php echo $masstxt_mass_update_button_top3; ?>
    	  <br /><br />
    	  
    	  <input type="submit" value="<?php echo $masstxt_mass_update_button; ?>" name="mass_update" style="font-weight:bold;font-size:15px;padding:7px 40px;color:#FF0000;">
    	  <br /><br />
    	  <span class="help"><?php echo $masstxt_mass_update_button_help; ?></span>
    	  </td>
    	</tr>

    	</tbody>
    	</table>

    	</form>


    </div>
    
  </div>
  
</div>


<script type="text/javascript" src="view/javascript/jquery/ui/jquery-ui-timepicker-addon.js"></script> 
<script type="text/javascript"><!--
$('.date').datepicker({dateFormat: 'yy-mm-dd'});
$('.datetime').datetimepicker({
	dateFormat: 'yy-mm-dd',
	timeFormat: 'h:m'
});
//--></script> 


<script type="text/javascript"><!--
var discount_row = <?php echo $discount_row; ?>;

function addDiscount() {
	html  = '<tbody id="discount-row' + discount_row + '">';
	html += '  <tr>'; 
    html += '    <td class="left"><select name="product_discount[' + discount_row + '][customer_group_id]">';
    <?php foreach ($customer_groups as $customer_group) { ?>
    html += '      <option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>';
    <?php } ?>
    html += '    </select></td>';		
    html += '    <td class="right"><input type="text" name="product_discount[' + discount_row + '][quantity]" value="" size="2" /></td>';
    html += '    <td class="right"><input type="text" name="product_discount[' + discount_row + '][priority]" value="" size="2" /></td>';
    html += '    <td class="right"><select name="product_discount[' + discount_row + '][price_mode]">';
    	html += '<option value="flat"><?php echo $masstxt_upd_flat; ?></option>';
    	html += '<option value="sub"><?php echo $masstxt_upd_subtraction_price; ?></option>';
    	html += '<option value="per"><?php echo $masstxt_upd_percentage_price; ?></option></select>';
    	html += '<input type="text" name="product_discount[' + discount_row + '][price]" value="" size="6" /></td>';
    html += '    <td class="left"><input type="text" name="product_discount[' + discount_row + '][date_start]" value="" class="date" size="8"/></td>';
	html += '    <td class="left"><input type="text" name="product_discount[' + discount_row + '][date_end]" value="" class="date" size="8" /></td>';
	html += '    <td class="left"><a onclick="$(\'#discount-row' + discount_row + '\').remove();" class="button btn btn-primary"><span><?php echo $masstxt_remove; ?></span></a></td>';
	html += '  </tr>';	
    html += '</tbody>';
	
	$('#discount tfoot').before(html);
		
	$('#discount-row' + discount_row + ' .date').datepicker({dateFormat: 'yy-mm-dd'});
	
	discount_row++;
}
//--></script>

<script type="text/javascript"><!--
var special_row = <?php echo $special_row; ?>;

function addSpecial() {
	html  = '<tbody id="special-row' + special_row + '">';
	html += '  <tr>'; 
    html += '    <td class="left"><select name="product_special[' + special_row + '][customer_group_id]">';
    <?php foreach ($customer_groups as $customer_group) { ?>
    html += '      <option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>';
    <?php } ?>
    html += '    </select></td>';		
    html += '    <td class="right"><input type="text" name="product_special[' + special_row + '][priority]" value="" size="2" /></td>';
    
    html += '    <td class="right"><select name="product_special[' + special_row + '][price_mode]">';
    	html += '<option value="flat"><?php echo $masstxt_upd_flat; ?></option>';
    	html += '<option value="sub"><?php echo $masstxt_upd_subtraction_price; ?></option>';
    	html += '<option value="per"><?php echo $masstxt_upd_percentage_price; ?></option></select>';
	html += '<input type="text" name="product_special[' + special_row + '][price]" value="" size="6" /></td>';
    html += '    <td class="left"><input type="text" name="product_special[' + special_row + '][date_start]" value="" class="date" size="8" /></td>';
	html += '    <td class="left"><input type="text" name="product_special[' + special_row + '][date_end]" value="" class="date" size="8" /></td>';
	html += '    <td class="left"><a onclick="$(\'#special-row' + special_row + '\').remove();" class="button btn btn-primary"><span><?php echo $masstxt_remove; ?></span></a></td>';
	html += '  </tr>';
    html += '</tbody>';
	
	$('#special tfoot').before(html);
 
	$('#special-row' + special_row + ' .date').datepicker({dateFormat: 'yy-mm-dd'});
	
	special_row++;
}
//--></script>

<script type="text/javascript"><!--
$('input[name=\'selected[]\']').click(function(){
var len = $('.products_to_upd input:checked').length;
$('.counter').text(len);
});

$('input[name=\'sel_desel_all\']').click(function(){
var len = $('.products_to_upd input:checked').length;
$('.counter').text(len);
});

var len = $('.products_to_upd input:checked').length;
$('.counter').text(len);
//--></script>

<?php echo $footer; ?>
