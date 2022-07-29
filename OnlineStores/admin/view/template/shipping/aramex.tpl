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
  <div class="box">
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
		<tr>
            <td colspan="2"><h2><?php echo $entry_client_information; ?></h2></td>
            
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_email; ?></td>
            <td><input type="text" name="aramex_email" value="<?php echo $aramex_email; ?>" />
              <?php if ($error_email) { ?>
              <span class="error"><?php echo $error_email; ?></span>
              <?php } ?></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_password; ?></td>
            <td><input type="text" name="aramex_password" value="<?php echo $aramex_password; ?>" />
              <?php if ($error_password) { ?>
              <span class="error"><?php echo $error_password; ?></span>
              <?php } ?></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_account_pin; ?></td>
            <td><input type="text" name="aramex_account_pin" value="<?php echo $aramex_account_pin; ?>" />
              <?php if ($error_account_pin) { ?>
              <span class="error"><?php echo $error_account_pin; ?></span>
              <?php } ?></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_account_number; ?></td>
            <td><input type="text" name="aramex_account_number" value="<?php echo $aramex_account_number; ?>" />
              <?php if ($error_account_number) { ?>
              <span class="error"><?php echo $error_account_number; ?></span>
              <?php } ?></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_account_entity; ?></td>
            <td><input type="text" name="aramex_account_entity" value="<?php echo $aramex_account_entity; ?>" />
              <?php if ($error_account_entity) { ?>
              <span class="error"><?php echo $error_account_entity; ?></span>
              <?php } ?></td>
          </tr> 
		  <tr>
            <td><span class="required">*</span> <?php echo $entry_account_country_code; ?></td>
            <td><input type="text" name="aramex_account_country_code" value="<?php echo $aramex_account_country_code; ?>" />
              <?php if ($error_account_country_code) { ?>
              <span class="error"><?php echo $error_account_country_code; ?></span>
              <?php } ?></td>
          </tr>
			
		  <tr>
            <td colspan="2"><h2><?php echo $entry_service_configuration; ?></h2></td>
            
          </tr>
		  
          <tr>
            <td><?php echo $entry_test_mode; ?></td>
            <td><?php if ($aramex_test) { ?>
              <input type="radio" name="aramex_test" value="1" checked="checked" />
              <?php echo $text_yes; ?>
              <input type="radio" name="aramex_test" value="0" />
              <?php echo $text_no; ?>
              <?php } else { ?>
              <input type="radio" name="aramex_test" value="1" />
              <?php echo $text_yes; ?>
              <input type="radio" name="aramex_test" value="0" checked="checked" />
              <?php echo $text_no; ?>
              <?php } ?></td>
          </tr>
		  <tr>
            <td><span class="required">*</span> <?php echo $entry_report_id; ?></td>
            <td>
			<input type="text" name="aramex_report_id" value="<?php echo $aramex_report_id; ?>" />
             </td>
          </tr> 
          <tr>
            <td><?php echo $entry_allowed_domestic_methods; ?></td>
            <td><div class="scrollbox">
                <?php $class = 'odd'; ?>
                <?php foreach ($allowed_domestic_methods as $key=>$dservice) { ?>
                <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                <div class="<?php echo $class; ?>">
                  <?php if (in_array($dservice['value'], $aramex_allowed_domestic_methods)) { ?>
                  <input type="checkbox" name="aramex_allowed_domestic_methods[]" value="<?php echo $dservice['value']; ?>" checked="checked" />
                  <?php echo $dservice['label']; ?>
                  <?php } else { ?>
                  <input type="checkbox" name="aramex_allowed_domestic_methods[]" value="<?php echo $dservice['value']; ?>" />
                  <?php echo $dservice['label']; ?>
                  <?php } ?>
                </div>
                <?php } ?>
              </div>
              <a onclick="$(this).parent().find(':checkbox').attr('checked', true);"><?php echo $text_select_all; ?></a> / <a onclick="$(this).parent().find(':checkbox').attr('checked', false);"><?php echo $text_unselect_all; ?></a></td>
          </tr>
		  <tr>
            <td><?php echo $entry_allowed_domestic_additional_services; ?></td>
            <td><div class="scrollbox">
                <?php $class = 'odd'; ?>
                <?php foreach ($allowed_domestic_additional_services as $key=>$daservice) { ?>
                <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                <div class="<?php echo $class; ?>">
                  <?php if (in_array($daservice['value'], $aramex_allowed_domestic_additional_services)) { ?>
                  <input type="checkbox" name="aramex_allowed_domestic_additional_services[]" value="<?php echo $daservice['value']; ?>" checked="checked" />
                  <?php echo $daservice['label']; ?>
                  <?php } else { ?>
                  <input type="checkbox" name="aramex_allowed_domestic_additional_services[]" value="<?php echo $daservice['value']; ?>" />
                  <?php echo $daservice['label']; ?>
                  <?php } ?>
                </div>
                <?php } ?>
              </div>
              <a onclick="$(this).parent().find(':checkbox').attr('checked', true);"><?php echo $text_select_all; ?></a> / <a onclick="$(this).parent().find(':checkbox').attr('checked', false);"><?php echo $text_unselect_all; ?></a></td>
          </tr>
		  <tr>
            <td><?php echo $entry_allowed_international_methods; ?></td>
            <td><div class="scrollbox">
                <?php $class = 'odd'; ?>
                <?php foreach ($allowed_international_methods as $key=>$iservice) { ?>
                <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                <div class="<?php echo $class; ?>">
                  <?php if (in_array($iservice['value'], $aramex_allowed_international_methods)) { ?>
                  <input type="checkbox" name="aramex_allowed_international_methods[]" value="<?php echo $iservice['value']; ?>" checked="checked" />
                  <?php echo $iservice['label']; ?>
                  <?php } else { ?>
                  <input type="checkbox" name="aramex_allowed_international_methods[]" value="<?php echo $iservice['value']; ?>" />
                  <?php echo $iservice['label']; ?>
                  <?php } ?>
                </div>
                <?php } ?>
              </div>
              <a onclick="$(this).parent().find(':checkbox').attr('checked', true);"><?php echo $text_select_all; ?></a> / <a onclick="$(this).parent().find(':checkbox').attr('checked', false);"><?php echo $text_unselect_all; ?></a></td>
          </tr>
		  <tr>
            <td><?php echo $entry_allowed_international_additional_services; ?></td>
            <td><div class="scrollbox">
                <?php $class = 'odd'; ?>
                <?php foreach ($allowed_international_additional_services as $key=>$iaservice) { ?>
                <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                <div class="<?php echo $class; ?>">
                  <?php if (in_array($iaservice['value'], $aramex_allowed_international_additional_services)) { ?>
                  <input type="checkbox" name="aramex_allowed_international_additional_services[]" value="<?php echo $iaservice['value']; ?>" checked="checked" />
                  <?php echo $iaservice['label']; ?>
                  <?php } else { ?>
                  <input type="checkbox" name="aramex_allowed_international_additional_services[]" value="<?php echo $iaservice['value']; ?>" />
                  <?php echo $iaservice['label']; ?>
                  <?php } ?>
                </div>
                <?php } ?>
              </div>
              <a onclick="$(this).parent().find(':checkbox').attr('checked', true);"><?php echo $text_select_all; ?></a> / <a onclick="$(this).parent().find(':checkbox').attr('checked', false);"><?php echo $text_unselect_all; ?></a></td>
          </tr>
                     
          <tr>
            <td><?php echo $entry_weight_class; ?></td>
            <td><select name="aramex_weight_class_id">
                <?php foreach ($weight_classes as $weight_class) { ?>
                <?php if ($weight_class['weight_class_id'] == $aramex_weight_class_id) { ?>
                <option value="<?php echo $weight_class['weight_class_id']; ?>" selected="selected"><?php echo $weight_class['title']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $weight_class['weight_class_id']; ?>"><?php echo $weight_class['title']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>                                
          <tr>
            <td><?php echo $entry_tax_class; ?></td>
            <td><select name="aramex_tax_class_id">
                <option value="0"><?php echo $text_none; ?></option>
                <?php foreach ($tax_classes as $tax_class) { ?>
                <?php if ($tax_class['tax_class_id'] == $aramex_tax_class_id) { ?>
                <option value="<?php echo $tax_class['tax_class_id']; ?>" selected="selected"><?php echo $tax_class['title']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $tax_class['tax_class_id']; ?>"><?php echo $tax_class['title']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>          
          <tr>
            <td><?php echo $entry_geo_zone; ?></td>
            <td><select name="aramex_geo_zone_id">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                <?php if ($geo_zone['geo_zone_id'] == $aramex_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_status ?></td>
            <td><select name="aramex_status">
                <?php if ($aramex_status === "0") { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <option value="0"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_sort_order; ?></td>
            <td><input type="text" name="aramex_sort_order" value="<?php echo $aramex_sort_order; ?>" size="1" /></td>
          </tr>
		  
		  <tr>
            <td colspan="2"><h2><?php echo $entry_shipper_details; ?></h2></td>
          </tr>
		   <tr>
				<td><span class="required">*</span><?php echo $entry_name; ?></td>
				<td><input type="text" name="aramex_shipper_name" value="<?php echo $aramex_shipper_name; ?>"  />
				<?php if ($error_shipper_name) { ?>
					<span class="error"><?php echo $error_shipper_name; ?></span>
				<?php } ?>
				</td>
          </tr>
		   <tr>
				<td><span class="required">*</span><?php echo $entry_email; ?></td>
				<td><input type="text" name="aramex_shipper_email" value="<?php echo $aramex_shipper_email; ?>"  />
				<?php if ($error_shipper_email) { ?>
					<span class="error"><?php echo $error_shipper_email; ?></span>
				<?php } ?>
				</td>
          </tr>
		  <tr>
				<td><span class="required">*</span><?php echo $entry_company; ?></td>
				<td><input type="text" name="aramex_shipper_company" value="<?php echo $aramex_shipper_company; ?>"  />
				<?php if ($error_shipper_company) { ?>
					<span class="error"><?php echo $error_shipper_company; ?></span>
				<?php } ?>
				</td>
          </tr>
		  <tr>
				<td><span class="required">*</span><?php echo $entry_address; ?></td>
				<td><input type="text" name="aramex_shipper_address" value="<?php echo $aramex_shipper_address; ?>"  />
				<?php if ($error_shipper_address) { ?>
					<span class="error"><?php echo $error_shipper_address; ?></span>
				<?php } ?>
				</td>
          </tr>
		  <tr>
				<td><span class="required">*</span><?php echo $entry_country_code; ?></td>
				<td><input type="text" name="aramex_shipper_country_code" value="<?php echo $aramex_shipper_country_code; ?>"  />
				<?php if ($error_shipper_country_code) { ?>
					<span class="error"><?php echo $error_shipper_country_code; ?></span>
				<?php } ?>
				</td>
          </tr>
		  <tr>
				<td><span class="required">*</span><?php echo $entry_city; ?></td>
				<td><input type="text" name="aramex_shipper_city" value="<?php echo $aramex_shipper_city; ?>"  />
				<?php if ($error_shipper_city) { ?>
					<span class="error"><?php echo $error_shipper_city; ?></span>
				<?php } ?>
				</td>
          </tr>
		  <tr>
				<td><span class="required">*</span><?php echo $entry_postal_code; ?></td>
				<td><input type="text" name="aramex_shipper_postal_code" value="<?php echo $aramex_shipper_postal_code; ?>"  />
				<?php if ($error_shipper_postal_code) { ?>
					<span class="error"><?php echo $error_shipper_postal_code; ?></span>
				<?php } ?>
				</td>
          </tr>
		  <tr>
				<td><span class="required">*</span><?php echo $entry_state; ?></td>
				<td><input type="text" name="aramex_shipper_state" value="<?php echo $aramex_shipper_state; ?>"  />
				<?php if ($error_shipper_state) { ?>
					<span class="error"><?php echo $error_shipper_state; ?></span>
				<?php } ?>
				</td>
          </tr>
		  <tr>
				<td><span class="required">*</span><?php echo $entry_phone; ?></td>
				<td><input type="text" name="aramex_shipper_phone" value="<?php echo $aramex_shipper_phone; ?>"  />
				<?php if ($error_shipper_phone) { ?>
					<span class="error"><?php echo $error_shipper_phone; ?></span>
				<?php } ?>
				</td>
          </tr>
		  
		  <tr>
            <td colspan="2"><h2><?php echo $entry_default_service_configuration; ?></h2></td>
          </tr>
		  <tr>
            <td><?php echo $entry_auto_create_shipment; ?></td>
            <td><?php if ($aramex_auto_create_shipment) { ?>
              <input type="radio" name="aramex_auto_create_shipment" value="1" checked="checked" />
              <?php echo $text_yes; ?>
              <input type="radio" name="aramex_auto_create_shipment" value="0" />
              <?php echo $text_no; ?>
              <?php } else { ?>
              <input type="radio" name="aramex_auto_create_shipment" value="1" />
              <?php echo $text_yes; ?>
              <input type="radio" name="aramex_auto_create_shipment" value="0" checked="checked" />
              <?php echo $text_no; ?>
              <?php } ?></td>
          </tr>
		  <tr>
            <td><?php echo $entry_live_rate_calculation; ?></td>
            <td><?php if ($aramex_live_rate_calculation) { ?>
              <input type="radio" name="aramex_live_rate_calculation" value="1" checked="checked" />
              <?php echo $text_yes; ?>
              <input type="radio" name="aramex_live_rate_calculation" value="0" />
              <?php echo $text_no; ?>
              <?php } else { ?>
              <input type="radio" name="aramex_live_rate_calculation" value="1" />
              <?php echo $text_yes; ?>
              <input type="radio" name="aramex_live_rate_calculation" value="0" checked="checked" />
              <?php echo $text_no; ?>
              <?php } ?></td>
          </tr>
		   <tr>
				<td><?php echo $entry_default_rate; ?></td>
				<td><input type="text" name="aramex_default_rate" value="<?php echo $aramex_default_rate; ?>"  /></td>
          </tr>
		  <tr>
            <td><?php echo $entry_allowed_domestic_methods; ?></td>
            <td>
			
				<select name="aramex_default_allowed_domestic_methods">
					<option value=""/><?php echo $text_please_select_domestic; ?></option>
					<?php foreach ($allowed_domestic_methods as $key=>$dservice) { ?>
					<?php if ($dservice['value']==$aramex_default_allowed_domestic_methods) { ?>
					<option value="<?php echo $dservice['value']; ?>" selected/><?php echo $dservice['label']; ?></option>
					 <?php } else { ?>
					 <option value="<?php echo $dservice['value']; ?>"/><?php echo $dservice['label']; ?></option>
					<?php }
					} ?>
				</select>
				<?php if ($error_default_allowed_domestic_methods) { ?>
					<span class="error"><?php echo $error_default_allowed_domestic_methods; ?></span>
				<?php } ?>
				</td>
          </tr>
		  <tr>
            <td><?php echo $entry_allowed_domestic_additional_services; ?></td>
            <td>
			
				<select name="aramex_default_allowed_domestic_additional_services">
					<option value=""/><?php echo $text_please_select_add_domestic; ?></option>
					<?php foreach ($allowed_domestic_additional_services as $key=>$daservice) { ?>
					<?php if ($daservice['value']==$aramex_default_allowed_domestic_additional_services) { ?>
					<option value="<?php echo $daservice['value']; ?>" selected/><?php echo $daservice['label']; ?></option>
					 <?php } else { ?>
					 <option value="<?php echo $daservice['value']; ?>"/><?php echo $daservice['label']; ?></option>
					 
					<?php }
					} ?>
				</select>
				<?php if ($error_default_allowed_domestic_additional_services) { ?>
					<span class="error"><?php echo $error_default_allowed_domestic_additional_services; ?></span>
				<?php } ?>
				</td>
          </tr>
		  <tr>
            <td><?php echo $entry_allowed_international_methods; ?></td>
            <td>
			
				<select name="aramex_default_allowed_international_methods">
					<option value=""/><?php echo $text_please_select_interna; ?></option>
					<?php foreach ($allowed_international_methods as $key=>$iservice) { ?>
					<?php if ($iservice['value']==$aramex_default_allowed_international_methods) { ?>
					<option value="<?php echo $iservice['value']; ?>" selected/><?php echo $iservice['label']; ?></option>
					 <?php } else { ?>
					 <option value="<?php echo $iservice['value']; ?>"/><?php echo $iservice['label']; ?></option>
					 
					<?php }
					} ?>
				</select>
				<?php if ($error_default_allowed_international_methods) { ?>
					<span class="error"><?php echo $error_default_allowed_international_methods; ?></span>
				<?php } ?>
				</td>
          </tr>
		  <tr>
            <td><?php echo $entry_allowed_international_additional_services; ?></td>
            <td>
			
				<select name="aramex_default_allowed_international_additional_services">
					<option value=""/><?php echo $text_please_select_add_interna; ?></option>
					<?php foreach ($allowed_international_additional_services as $key=>$iaservice) { ?>
					<?php if ($iaservice['value']==$aramex_default_allowed_international_additional_services) { ?>
					<option value="<?php echo $iaservice['value']; ?>" selected/><?php echo $iaservice['label']; ?></option>
					 <?php } else { ?>
					 <option value="<?php echo $iaservice['value']; ?>"/><?php echo $iaservice['label']; ?></option>
					 
					<?php }
					} ?>
				</select>
				<?php if ($error_default_allowed_international_additional_services) { ?>
					<span class="error"><?php echo $error_default_allowed_international_additional_services; ?></span>
				<?php } ?>
				</td>
          </tr>
		  
		  
		  
		  
		  
        </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?>