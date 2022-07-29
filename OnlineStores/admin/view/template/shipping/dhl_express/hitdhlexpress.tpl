<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-ups" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-shipping" class="form-horizontal">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_test; ?></label>
            <div class="col-sm-10">
              <label class="radio-inline">
                <?php if ($hitdhlexpress_test) { ?>
                <input type="radio" name="hitdhlexpress_test" value="1" checked="checked" />
                <?php echo $text_yes; ?>
                <?php } else { ?>
                <input type="radio" name="hitdhlexpress_test" value="1" />
                <?php echo $text_yes; ?>
                <?php } ?>
              </label>
              <label class="radio-inline">
                <?php if (!$hitdhlexpress_test) { ?>
                <input type="radio" name="hitdhlexpress_test" value="0" checked="checked" />
                <?php echo $text_no; ?>
                <?php }else{ ?>
                <input type="radio" name="hitdhlexpress_test" value="0" />
                <?php echo $text_no; ?>
                <?php } ?>
              </label>
            </div>
          </div>
		  <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-key"><?php echo $entry_key; ?></label>
            <div class="col-sm-10">
              <input type="text" name="hitdhlexpress_key" value="<?php echo $hitdhlexpress_key; ?>" placeholder="<?php echo $entry_key; ?>" id="input-key" class="form-control" />
              <?php if ($error_key) {?>
              <div class="text-danger"><?php echo $error_key; ?></div>
              <?php } ?>
            </div>
          </div>
            <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-account"><?php echo $entry_password; ?></label>
            <div class="col-sm-10">
              <input type="text" name="hitdhlexpress_password" value="<?php echo $hitdhlexpress_password; ?>" placeholder="<?php echo $entry_password; ?>" id="input-account" class="form-control" />
             </div>
          </div>
          
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-account"><?php echo $entry_account; ?></label>
            <div class="col-sm-10">
              <input type="text" name="hitdhlexpress_account" value="<?php echo $hitdhlexpress_account; ?>" placeholder="<?php echo $entry_account; ?>" id="input-account" class="form-control" />
              <?php if ($error_account) { ?>
              <div class="text-danger"><?php echo $error_account; ?></div>
              <?php } ?>
            </div>
          </div>
		  <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="hitdhlexpress_status" id="input-status" class="form-control">
                <?php if ($hitdhlexpress_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else {  ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
            <div class="col-sm-10">
              <input type="text" name="hitdhlexpress_sort_order" value="<?php echo $hitdhlexpress_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
            </div>
          </div>
		</div>
	</div>
	<div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_shiiping_address; ?></h3>
      </div>
      <div class="panel-body">
		  <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-shipper_name"><?php echo $entry_shipper_name; ?></label>
            <div class="col-sm-10">
              <input type="text" name="hitdhlexpress_shipper_name" value="<?php echo $hitdhlexpress_shipper_name; ?>" placeholder="<?php echo $entry_shipper_name; ?>" id="input-shipper_name" class="form-control" />
            </div>
          </div>
		    <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-company_name"><?php echo $entry_company_name; ?></label>
            <div class="col-sm-10">
              <input type="text" name="hitdhlexpress_company_name" value="<?php echo $hitdhlexpress_company_name; ?>" placeholder="<?php echo $entry_company_name; ?>" id="input-company_name" class="form-control" />
            </div>
          </div>
		  <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-phone_num"><?php echo $entry_phone_num; ?></label>
            <div class="col-sm-10">
              <input type="text" name="hitdhlexpress_phone_num" value="<?php echo $hitdhlexpress_phone_num; ?>" placeholder="<?php echo $entry_phone_num; ?>" id="input-phone_num" class="form-control" />
            </div>
          </div>
		  <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-email_addr"><?php echo $entry_email_addr; ?></label>
            <div class="col-sm-10">
              <input type="text" name="hitdhlexpress_email_addr" value="<?php echo $hitdhlexpress_email_addr; ?>" placeholder="<?php echo $entry_email_addr; ?>" id="input-email_addr" class="form-control" />
            </div>
          </div>
		  <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-address1"><?php echo $entry_address1; ?></label>
            <div class="col-sm-10">
              <input type="text" name="hitdhlexpress_address1" value="<?php echo $hitdhlexpress_address1; ?>" placeholder="<?php echo $entry_address1; ?>" id="input-address1" class="form-control" />
            </div>
          </div>
		  <div class="form-group">
            <label class="col-sm-2 control-label" for="input-address2"><?php echo $entry_address2; ?></label>
            <div class="col-sm-10">
              <input type="text" name="hitdhlexpress_address2" value="<?php echo $hitdhlexpress_address2; ?>" placeholder="<?php echo $entry_address2; ?>" id="input-address2" class="form-control" />
            </div>
          </div>
		  <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-city"><?php echo $entry_city; ?></label>
            <div class="col-sm-10">
              <input type="text" name="hitdhlexpress_city" value="<?php echo $hitdhlexpress_city; ?>" placeholder="<?php echo $entry_city; ?>" id="input-city" class="form-control" />
            </div>
          </div>
		  <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-state"><?php echo $entry_state; ?></label>
            <div class="col-sm-10">
              <input type="text" name="hitdhlexpress_state" value="<?php echo $hitdhlexpress_state; ?>" placeholder="<?php echo $entry_state; ?>" id="input-state" class="form-control" />
            </div>
          </div>
		    <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-country_code"><?php echo $entry_country_code; ?></label>
            <div class="col-sm-10">
				<select name="hitdhlexpress_country_code" class="form-control control-label">
				<?php foreach($countrylist as $key=>$value) { ?>
				<option value="<?php echo $key ?>" <?php if ($key == $hitdhlexpress_country_code) { ?>selected="true"<?php } ?>><?php echo $value ?></option>
				<?php } ?>
				</select>
	          </div>
          </div>
		    
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-postcode"><?php echo $entry_postcode; ?></label>
            <div class="col-sm-10">
              <input type="text" name="hitdhlexpress_postcode" value="<?php echo $hitdhlexpress_postcode; ?>" placeholder="<?php echo $entry_postcode; ?>" id="input-postcode" class="form-control" />
              <?php if ($error_postcode) { ?>
              <div class="text-danger"><?php echo $error_postcode; ?></div>
              <?php } ?>
            </div>
          </div>
          
	  </div>
	</div>
	<div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_rates; ?></h3>
      </div>
      <div class="panel-body">
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_realtime_rates; ?></label>
            <div class="col-sm-10">
              <label class="radio-inline">
                <?php if ($hitdhlexpress_realtime_rates) { ?>
                <input type="radio" name="hitdhlexpress_realtime_rates" value="1" checked="checked" />
                <?php echo $text_yes; ?>
                <?php }else{ ?>
                <input type="radio" name="hitdhlexpress_realtime_rates" value="1" />
                <?php echo $text_yes; ?>
                <?php } ?>
              </label>
              <label class="radio-inline">
                <?php if (!$hitdhlexpress_realtime_rates) { ?>
                <input type="radio" name="hitdhlexpress_realtime_rates" value="0" checked="checked" />
                <?php echo $text_no; ?>
                <?php }else{ ?>
                <input type="radio" name="hitdhlexpress_realtime_rates" value="0" />
                <?php echo $text_no; ?>
                <?php } ?>
              </label>
            </div>
          </div>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_insurance; ?></label>
            <div class="col-sm-10">
              <label class="radio-inline">
                <?php if ($hitdhlexpress_insurance) { ?>
                <input type="radio" name="hitdhlexpress_insurance" value="1" checked="checked" />
                <?php echo $text_yes; ?>
                <?php }else{ ?>
                <input type="radio" name="hitdhlexpress_insurance" value="1" />
                <?php echo $text_yes; ?>
                <?php } ?>
              </label>
              <label class="radio-inline">
                <?php if(!$hitdhlexpress_insurance) { ?>
                <input type="radio" name="hitdhlexpress_insurance" value="0" checked="checked" />
                <?php echo $text_no; ?>
                <?php }else{ ?>
                <input type="radio" name="hitdhlexpress_insurance" value="0" />
                <?php echo $text_no; ?>
                <?php } ?>
              </label>
            </div>
          </div>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $help_display_time; ?>"><?php echo $entry_display_time; ?></span></label>
            <div class="col-sm-10">
              <label class="radio-inline">
                <?php if ($hitdhlexpress_display_time) { ?>
                <input type="radio" name="hitdhlexpress_display_time" value="1" checked="checked" />
                <?php echo $text_yes; ?>
                <?php }else{ ?>
                <input type="radio" name="hitdhlexpress_display_time" value="1" />
                <?php echo $text_yes; ?>
                <?php } ?>
              </label>
              <label class="radio-inline">
                <?php if (!$hitdhlexpress_display_time) { ?>
                <input type="radio" name="hitdhlexpress_display_time" value="0" checked="checked" />
                <?php echo $text_no; ?>
                <?php }else{ ?>
                <input type="radio" name="hitdhlexpress_display_time" value="0" />
                <?php echo $text_no; ?>
                <?php } ?>
              </label>
            </div>
          </div>
		   <div class="form-group">
            <label class="col-sm-2 control-label" for="input-rate-type"><?php echo $entry_rate_type; ?></label>
            <div class="col-sm-10">
              <select name="hitdhlexpress_rate_type" id="input-rate-type" class="form-control">
                <?php if ($hitdhlexpress_rate_type == 'LIST') { ?>
                <option value="LIST" selected="selected"><?php echo $text_list_rate; ?></option>
                <?php }else{ ?>
                <option value="LIST"><?php echo $text_list_rate; ?></option>
                <?php } ?>
                <?php if ($hitdhlexpress_rate_type == 'ACCOUNT'){ ?>
                <option value="ACCOUNT" selected="selected"><?php echo $text_account_rate; ?></option>
                <?php }else{ ?>
                <option value="ACCOUNT"><?php echo $text_account_rate; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
		  
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_service; ?></label>
            <div class="col-sm-10">
              <div class="well well-sm" style="height: 150px; overflow: auto;">
                <?php foreach($services as $service) { ?>
                <div class="checkbox">
                  <label>
                    <?php if (in_array($service['value'],$hitdhlexpress_service)) { ?>
                    <input type="checkbox" name="hitdhlexpress_service[]" value="<?php echo $service['value']; ?>" checked="checked" />
                    <?php echo $service['text']; ?>
                    <?php } else {  ?>
                    <input type="checkbox" name="hitdhlexpress_service[]" value="<?php echo $service['value']; ?>" />
                    <?php echo $service['text']; ?>
                    <?php } ?>
                  </label>
                </div>
                <?php } ?>
              </div>
              <button type="button" onclick="$(this).parent().find(':checkbox').prop('checked', true);" class="btn btn-link"><?php echo $text_select_all; ?></button> / <button type="button" onclick="$(this).parent().find(':checkbox').prop('checked', false);" class="btn btn-link"><?php echo $text_unselect_all; ?></button></div>
          </div>
		  </div>
		</div>
	<div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_packing; ?></h3>
      </div>
      <div class="panel-body">
	   <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $_entry_weight; ?></label>
            <div class="col-sm-10">
              <label class="radio-inline">
                <?php if ($hitdhlexpress_weight){ ?>
                <input type="radio" name="hitdhlexpress_weight" value="1" checked="checked" />
                <?php echo $_entry_lbin; ?>
                <?php } else {  ?>
                <input type="radio" name="hitdhlexpress_weight" value="1" />
                <?php echo $_entry_lbin; ?>
                <?php } ?>
              </label>
              <label class="radio-inline">
                <?php if (!$hitdhlexpress_weight){ ?>
                <input type="radio" name="hitdhlexpress_weight" value="0" checked="checked" />
                <?php echo $_entry_kgcm; ?>
                <?php } else {  ?>
                <input type="radio" name="hitdhlexpress_weight" value="0" />
                <?php echo $_entry_kgcm; ?>
                <?php } ?>
              </label>
            </div>
          </div>
		   <div class="form-group">
            <label class="col-sm-2 control-label" for="input-packaging-type"><?php echo $_entry_packing_type; ?></label>
            <div class="col-sm-10">
              <select name="hitdhlexpress_packing_type" id="input-packing-type" class="form-control">
                <?php if ($hitdhlexpress_packing_type == 'per_item'){ ?>
                <option value="per_item" selected="selected"><?php echo $text_per_item; ?></option>
                <?php } else {  ?>
                <option value="per_item"><?php echo $text_per_item; ?></option>
                <?php } ?>
                <?php if ($hitdhlexpress_packing_type == 'weight_based'){ ?>
                <option value="weight_based" selected="selected"><?php echo $text_dhl_weight_based; ?></option>
                <?php } else {  ?>
                <option value="weight_based"><?php echo $text_dhl_weight_based; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
         </div>
		</div>
	<div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_peritem_head; ?></h3>
      </div>
      <div class="panel-body">
		<div class="form-group">
            <label class="col-sm-2 control-label" for="input-packaging-type"><?php echo $_entry_packing_type; ?></label>
            <div class="col-sm-10">
              <select name="hitdhlexpress_per_item" id="input-packing-type" class="form-control">
                <?php if ($hitdhlexpress_per_item == 'BOX'){ ?>
                <option value="BOX" selected="selected"><?php echo $text_box; ?></option>
                <?php } else {  ?>
                <option value="BOX"><?php echo $text_box; ?></option>
                <?php } ?>
                <?php if ($hitdhlexpress_per_item == 'FLY'){ ?>
                <option value="FLY" selected="selected"><?php echo $text_fly; ?></option>
                <?php } else {  ?>
                <option value="FLY"><?php echo $text_fly; ?></option>
                <?php } ?>
                <?php if ($hitdhlexpress_per_item == 'YP'){ ?>
                <option value="YP" selected="selected"><?php echo $text_dhl_yp; ?></option>
                <?php } else {  ?>
                <option value="YP"><?php echo $text_dhl_yp; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
		  </div>
		</div>
	
	<div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_weight_head; ?></h3>
      </div>
      <div class="panel-body">
		  
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-postcode"><?php echo $text_head12; ?></label>
            <div class="col-sm-10">
              <input type="text" name="hitdhlexpress_wight_b" value="<?php echo $hitdhlexpress_wight_b; ?>" placeholder="<?php echo $text_head12; ?>" id="input-wight_b" class="form-control" />
            </div>
          </div>
		  <div class="form-group">
            <label class="col-sm-2 control-label" for="input-packaging-type"><?php echo $_entry_packing_type; ?></label>
            <div class="col-sm-10">
              <select name="hitdhlexpress_weight_c" id="input-packing-type" class="form-control">
                <?php if ($hitdhlexpress_weight_c == 'pack_descending'){ ?>
                <option value="pack_descending" selected="selected"><?php echo $text_head13; ?></option>
                <?php } else {  ?>
                <option value="pack_descending"><?php echo $text_head13; ?></option>
                <?php } ?>
                <?php if ($hitdhlexpress_weight_c == 'pack_ascending'){ ?>
                <option value="pack_ascending" selected="selected"><?php echo $text_head14; ?></option>
                <?php } else {  ?>
                <option value="pack_ascending"><?php echo $text_head14; ?></option>
                <?php } ?>
                <?php if ($hitdhlexpress_weight_c == 'pack_simple'){ ?>
                <option value="pack_simple" selected="selected"><?php echo $text_head15; ?></option>
                <?php } else {  ?>
                <option value="pack_simple"><?php echo $text_head15; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
		 </div>
		</div>
		<div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_label; ?> <span style="color:red">( Only Available in Premium Version) <a href="https://hittechmarket.com/downloads/dhl-express-shipping-for-opencart/" target="_blank"> Go Premium | Demo</a></span></h3>
      </div>
      <div class="panel-body">
	  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $text_head16; ?></label>
            <div class="col-sm-10">
              <label class="radio-inline">
                <?php if ($hitdhlexpress_plt){ ?>
                <input type="radio" name="hitdhlexpress_plt" value="1" checked="checked" />
                <?php echo $text_enable; ?>
                <?php } else {  ?>
                <input type="radio" name="hitdhlexpress_plt" value="1" />
                <?php echo $text_enable; ?>
                <?php } ?>
              </label>
              <label class="radio-inline">
                <?php if (!$hitdhlexpress_plt){ ?>
                <input type="radio" name="hitdhlexpress_plt" value="0" checked="checked" />
                <?php echo $text_disable; ?>
                <?php } else {  ?>
                <input type="radio" name="hitdhlexpress_plt" value="0" />
                <?php echo $text_disable; ?>
                <?php } ?>
              </label>
            </div>
          </div>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $text_head17; ?></label>
            <div class="col-sm-10">
              <label class="radio-inline">
                <?php if ($hitdhlexpress_sat){ ?>
                <input type="radio" name="hitdhlexpress_sat" value="1" checked="checked" />
                <?php echo $text_enable; ?>
                <?php } else {  ?>
                <input type="radio" name="hitdhlexpress_sat" value="1" />
                <?php echo $text_enable; ?>
                <?php } ?>
              </label>
              <label class="radio-inline">
                <?php if (!$hitdhlexpress_sat){ ?>
                <input type="radio" name="hitdhlexpress_sat" value="0" checked="checked" />
                <?php echo $text_disable; ?>
                <?php } else {  ?>
                <input type="radio" name="hitdhlexpress_sat" value="0" />
                <?php echo $text_disable; ?>
                <?php } ?>
              </label>
            </div>
          </div>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $text_head18; ?></label>
            <div class="col-sm-10">
              <label class="radio-inline">
                <?php if ($hitdhlexpress_email_trach){ ?>
                <input type="radio" name="hitdhlexpress_email_trach" value="1" checked="checked" />
                <?php echo $text_enable; ?>
                <?php } else {  ?>
                <input type="radio" name="hitdhlexpress_email_trach" value="1" />
                <?php echo $text_enable; ?>
                <?php } ?>
              </label>
              <label class="radio-inline">
                <?php if (!$hitdhlexpress_email_trach){ ?>
                <input type="radio" name="hitdhlexpress_email_trach" value="0" checked="checked" />
                <?php echo $text_disable; ?>
                <?php } else {  ?>
                <input type="radio" name="hitdhlexpress_email_trach" value="0" />
                <?php echo $text_disable; ?>
                <?php } ?>
              </label>
            </div>
          </div>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $text_head19; ?></label>
            <div class="col-sm-10">
              <label class="radio-inline">
                <?php if ($hitdhlexpress_airway){ ?>
                <input type="radio" name="hitdhlexpress_airway" value="1" checked="checked" />
                <?php echo $text_enable; ?>
                <?php } else {  ?>
                <input type="radio" name="hitdhlexpress_airway" value="1" />
                <?php echo $text_enable; ?>
                <?php } ?>
              </label>
              <label class="radio-inline">
                <?php if (!$hitdhlexpress_airway){ ?>
                <input type="radio" name="hitdhlexpress_airway" value="0" checked="checked" />
                <?php echo $text_disable; ?>
                <?php } else {  ?>
                <input type="radio" name="hitdhlexpress_airway" value="0" />
                <?php echo $text_disable; ?>
                <?php } ?>
              </label>
            </div>
          </div>
		  <div class="form-group">
            <label class="col-sm-2 control-label" for="input-dropoff-type"><?php echo $text_head20; ?></label>
            <div class="col-sm-10">
              <select name="hitdhlexpress_dropoff_type" id="input-dropoff-type" class="form-control">
                <?php if ($hitdhlexpress_dropoff_type == '8X4_A4_PDF'){ ?>
                <option value="8X4_A4_PDF" selected="selected"><?php echo $text_head21; ?></option>
                <?php } else {  ?>
                <option value="8X4_A4_PDF"><?php echo $text_head21; ?></option>
                <?php } ?>
                <?php if ($hitdhlexpress_dropoff_type == '8X4_thermal'){ ?>
                <option value="8X4_thermal" selected="selected"><?php echo $text_head22; ?></option>
                <?php } else {  ?>
                <option value="8X4_thermal"><?php echo $text_head22; ?></option>
                <?php } ?>
                <?php if ($hitdhlexpress_dropoff_type == '8X4_A4_TC_PDF'){ ?>
                <option value="8X4_A4_TC_PDF" selected="selected"><?php echo $text_head23; ?></option>
                <?php } else {  ?>
                <option value="8X4_A4_TC_PDF"><?php echo $text_head23; ?></option>
                <?php } ?>
                <?php if ($hitdhlexpress_dropoff_type == '8X4_CI_PDF'){ ?>
                <option value="8X4_CI_PDF" selected="selected"><?php echo $text_head24; ?></option>
                <?php } else {  ?>
                <option value="8X4_CI_PDF"><?php echo $text_head24; ?></option>
                <?php } ?>
                <?php if ($hitdhlexpress_dropoff_type == '8X4_CI_thermal'){ ?>
                <option value="8X4_CI_thermal" selected="selected"><?php echo $text_head25; ?></option>
                <?php } else {  ?>
                <option value="8X4_CI_thermal"><?php echo $text_head25; ?></option>
                <?php } ?>
				<?php if ($hitdhlexpress_dropoff_type == '8X4_RU_A4_PDF'){ ?>
                <option value="8X4_RU_A4_PDF" selected="selected"><?php echo $text_head26; ?></option>
                <?php } else {  ?>
                <option value="8X4_RU_A4_PDF"><?php echo $text_head26; ?></option>
                <?php } ?>
				<?php if ($hitdhlexpress_dropoff_type == '8X4_PDF'){ ?>
                <option value="8X4_PDF" selected="selected"><?php echo $text_head27; ?></option>
                <?php } else {  ?>
                <option value="8X4_PDF"><?php echo $text_head27; ?></option>
                <?php } ?>
				<?php if ($hitdhlexpress_dropoff_type == '8X4_CustBarCode_PDF'){ ?>
                <option value="8X4_CustBarCode_PDF" selected="selected"><?php echo $text_head28; ?></option>
                <?php } else {  ?>
                <option value="8X4_CustBarCode_PDF"><?php echo $text_head28; ?></option>
                <?php } ?>
				<?php if ($hitdhlexpress_dropoff_type == '8X4_CustBarCode_thermal'){ ?>
                <option value="8X4_CustBarCode_thermal" selected="selected"><?php echo $text_head29; ?></option>
                <?php } else {  ?>
                <option value="8X4_CustBarCode_thermal"><?php echo $text_head29; ?></option>
                <?php } ?>
				<?php if ($hitdhlexpress_dropoff_type == '6X4_A4_PDF'){ ?>
                <option value="6X4_A4_PDF" selected="selected"><?php echo $text_head30; ?></option>
                <?php } else {  ?>
                <option value="6X4_A4_PDF"><?php echo $text_head30; ?></option>
                <?php } ?>
				<?php if ($hitdhlexpress_dropoff_type == '6X4_thermal'){ ?>
                <option value="6X4_thermal" selected="selected"><?php echo $text_head31; ?></option>
                <?php } else {  ?>
                <option value="6X4_thermal"><?php echo $text_head31; ?></option>
                <?php } ?>
				<?php if ($hitdhlexpress_dropoff_type == '6X4_PDF'){ ?>
                <option value="6X4_PDF" selected="selected"><?php echo $text_head32; ?></option>
                <?php } else {  ?>
                <option value="6X4_PDF"><?php echo $text_head32; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
		  
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-dropoff-type"><?php echo $text_head33; ?></label>
            <div class="col-sm-10">
              <select name="hitdhlexpress_output_type" id="input-dropoff-type" class="form-control">
                <?php if ($hitdhlexpress_output_type == 'PDF'){ ?>
                <option value="PDF" selected="selected"><?php echo $text_head34; ?></option>
                <?php } else {  ?>
                <option value="PDF"><?php echo $text_head34; ?></option>
                <?php } ?>
                <?php if ($hitdhlexpress_output_type == 'ZPL2'){ ?>
                <option value="ZPL2" selected="selected"><?php echo $text_head35; ?></option>
                <?php } else {  ?>
                <option value="ZPL2"><?php echo $text_head35; ?></option>
                <?php } ?>
                <?php if ($hitdhlexpress_output_type == 'EPL2'){ ?>
                <option value="EPL2" selected="selected"><?php echo $text_head36; ?></option>
                <?php } else {  ?>
                <option value="EPL2"><?php echo $text_head36; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
		    <div class="form-group">
            <label class="col-sm-2 control-label" for="input-dropoff-type"><?php echo $text_head41; ?></label>
            <div class="col-sm-10">
              <select name="hitdhlexpress_duty_type" id="input-dropoff-type" class="form-control">
                <?php if ($hitdhlexpress_duty_type == ''){ ?>
                <option value="" selected="selected"><?php echo $text_head37; ?></option>
                <?php } else {  ?>
                <option value=""><?php echo $text_head37; ?></option>
                <?php } ?>
                <?php if ($hitdhlexpress_duty_type == 'S'){ ?>
                <option value="S" selected="selected"><?php echo $text_head38; ?></option>
                <?php } else {  ?>
                <option value="S"><?php echo $text_head38; ?></option>
                <?php } ?>
                <?php if ($hitdhlexpress_duty_type == 'R'){ ?>
                <option value="R" selected="selected"><?php echo $text_head39; ?></option>
                <?php } else {  ?>
                <option value="R"><?php echo $text_head39; ?></option>
                <?php } ?>
				<?php if ($hitdhlexpress_duty_type == 'T'){ ?>
                <option value="T" selected="selected"><?php echo $text_head40; ?></option>
                <?php } else {  ?>
                <option value="T"><?php echo $text_head40; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
		  
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-shipment_content"><?php echo $text_head42; ?></label>
            <div class="col-sm-10">
              <input type="text" name="hitdhlexpress_shipment_content" value="<?php echo $hitdhlexpress_shipment_content; ?>" placeholder="<?php echo $text_head42; ?>" id="input-shipment_content" class="form-control" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-shipment_content"><?php echo $text_head43; ?></label>
            <div class="col-sm-10">
              <input type="text" name="hitdhlexpress_logo" value="<?php echo $hitdhlexpress_logo; ?>" placeholder="<?php echo $text_head43; ?>" id="input-logo" class="form-control" />
            </div>
          </div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="input-packaging-type">Contact person Name (Pickup)</label>
				<div class="col-sm-10">
				 <input type="text" name="hitdhlexpress_picper" value="<?php echo $hitdhlexpress_picper; ?>" placeholder="Person Name" id="input-account" class="form-control" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="input-packaging-type">Contact Phone Number (Pickup)</label>
				<div class="col-sm-10">
				 <input type="text" name="hitdhlexpress_piccon" value="<?php echo $hitdhlexpress_piccon; ?>" placeholder="Person Mobile" id="input-account" class="form-control" />
				</div>
			</div>
			<div class="form-group">
            <label class="col-sm-2 control-label" for="input-packaging-type">Pickup Open Time</label>
            <div class="col-sm-10">
              <select name="hitdhlexpress_pickup_time" id="input-packing-type" class="form-control">
						<option value="01:00" <?php if ($hitdhlexpress_pickup_time == '01:00') { ?>selected="selected"<?php } ?> >01:00</option>
						<option value="02:00" <?php if ($hitdhlexpress_pickup_time == '02:00') { ?>selected="selected"<?php } ?>>02:00</option>
						<option value="03:00" <?php if ($hitdhlexpress_pickup_time == '03:00') { ?>selected="selected"<?php } ?>>03:00</option>
						<option value="04:00" <?php if ($hitdhlexpress_pickup_time == '04:00') { ?>selected="selected"<?php } ?>>04:00</option>
						<option value="05:00" <?php if ($hitdhlexpress_pickup_time == '05:00') { ?>selected="selected"<?php } ?>>05:00</option>
						<option value="06:00" <?php if ($hitdhlexpress_pickup_time == '06:00') { ?>selected="selected"<?php } ?>>06:00</option>
						<option value="07:00" <?php if ($hitdhlexpress_pickup_time == '07:00') { ?>selected="selected"<?php } ?>>07:00</option>
						<option value="08:00" <?php if ($hitdhlexpress_pickup_time == '08:00') { ?>selected="selected"<?php } ?>>08:00</option>
						<option value="09:00" <?php if ($hitdhlexpress_pickup_time == '09:00') { ?>selected="selected"<?php } ?>>09:00</option>
						<option value="10:00" <?php if ($hitdhlexpress_pickup_time == '10:00') { ?>selected="selected"<?php } ?>>10:00</option>
						<option value="11:00" <?php if ($hitdhlexpress_pickup_time == '11:00') { ?>selected="selected"<?php } ?>>11:00</option>
						<option value="12:00" <?php if ($hitdhlexpress_pickup_time == '12:00') { ?>selected="selected"<?php } ?>>12:00</option>
						<option value="13:00" <?php if ($hitdhlexpress_pickup_time == '13:00') { ?>selected="selected"<?php } ?>>13:00</option>
						<option value="14:00" <?php if ($hitdhlexpress_pickup_time == '14:00') { ?>selected="selected"<?php } ?>>14:00</option>
						<option value="15:00" <?php if ($hitdhlexpress_pickup_time == '15:00') { ?>selected="selected"<?php } ?>>15:00</option>
						<option value="16:00" <?php if ($hitdhlexpress_pickup_time == '16:00') { ?>selected="selected"<?php } ?>>16:00</option>
						<option value="17:00" <?php if ($hitdhlexpress_pickup_time == '17:00') { ?>selected="selected"<?php } ?>>17:00</option>
						<option value="18:00" <?php if ($hitdhlexpress_pickup_time == '18:00') { ?>selected="selected"<?php } ?>>18:00</option>
						<option value="19:00" <?php if ($hitdhlexpress_pickup_time == '19:00') { ?>selected="selected"<?php } ?>>19:00</option>
						<option value="20:00" <?php if ($hitdhlexpress_pickup_time == '20:00') { ?>selected="selected"<?php } ?>>20:00</option>
						<option value="21:00" <?php if ($hitdhlexpress_pickup_time == '21:00') { ?>selected="selected"<?php } ?>>21:00</option>
						<option value="22:00" <?php if ($hitdhlexpress_pickup_time == '22:00') { ?>selected="selected"<?php } ?>>22:00</option>
						<option value="23:00" <?php if ($hitdhlexpress_pickup_time == '23:00') { ?>selected="selected"<?php } ?>>23:00</option>
						<option value="00:00" <?php if ($hitdhlexpress_pickup_time == '00:00') { ?>selected="selected"<?php } ?>>00:00</option>
		      </select>
            </div>
          </div>
		  <div class="form-group">
            <label class="col-sm-2 control-label" for="input-packaging-type">Pickup Close Time</label>
            <div class="col-sm-10">
              <select name="hitdhlexpress_close_time" id="input-packing-type" class="form-control">
						<option value="01:00" <?php if ($hitdhlexpress_close_time == '01:00') { ?>selected="selected"<?php } ?> >01:00</option>
						<option value="02:00" <?php if ($hitdhlexpress_close_time == '02:00') { ?>selected="selected"<?php } ?>>02:00</option>
						<option value="03:00" <?php if ($hitdhlexpress_close_time == '03:00') { ?>selected="selected"<?php } ?>>03:00</option>
						<option value="04:00" <?php if ($hitdhlexpress_close_time == '04:00') { ?>selected="selected"<?php } ?>>04:00</option>
						<option value="05:00" <?php if ($hitdhlexpress_close_time == '05:00') { ?>selected="selected"<?php } ?>>05:00</option>
						<option value="06:00" <?php if ($hitdhlexpress_close_time == '06:00') { ?>selected="selected"<?php } ?>>06:00</option>
						<option value="07:00" <?php if ($hitdhlexpress_close_time == '07:00') { ?>selected="selected"<?php } ?>>07:00</option>
						<option value="08:00" <?php if ($hitdhlexpress_close_time == '08:00') { ?>selected="selected"<?php } ?>>08:00</option>
						<option value="09:00" <?php if ($hitdhlexpress_close_time == '09:00') { ?>selected="selected"<?php } ?>>09:00</option>
						<option value="10:00" <?php if ($hitdhlexpress_close_time == '10:00') { ?>selected="selected"<?php } ?>>10:00</option>
						<option value="11:00" <?php if ($hitdhlexpress_close_time == '11:00') { ?>selected="selected"<?php } ?>>11:00</option>
						<option value="12:00" <?php if ($hitdhlexpress_close_time == '12:00') { ?>selected="selected"<?php } ?>>12:00</option>
						<option value="13:00" <?php if ($hitdhlexpress_close_time == '13:00') { ?>selected="selected"<?php } ?>>13:00</option>
						<option value="14:00" <?php if ($hitdhlexpress_close_time == '14:00') { ?>selected="selected"<?php } ?>>14:00</option>
						<option value="15:00" <?php if ($hitdhlexpress_close_time == '15:00') { ?>selected="selected"<?php } ?>>15:00</option>
						<option value="16:00" <?php if ($hitdhlexpress_close_time == '16:00') { ?>selected="selected"<?php } ?>>16:00</option>
						<option value="17:00" <?php if ($hitdhlexpress_close_time == '17:00') { ?>selected="selected"<?php } ?>>17:00</option>
						<option value="18:00" <?php if ($hitdhlexpress_close_time == '18:00') { ?>selected="selected"<?php } ?>>18:00</option>
						<option value="19:00" <?php if ($hitdhlexpress_close_time == '19:00') { ?>selected="selected"<?php } ?>>19:00</option>
						<option value="20:00" <?php if ($hitdhlexpress_close_time == '20:00') { ?>selected="selected"<?php } ?>>20:00</option>
						<option value="21:00" <?php if ($hitdhlexpress_close_time == '21:00') { ?>selected="selected"<?php } ?>>21:00</option>
						<option value="22:00" <?php if ($hitdhlexpress_close_time == '22:00') { ?>selected="selected"<?php } ?>>22:00</option>
						<option value="23:00" <?php if ($hitdhlexpress_close_time == '23:00') { ?>selected="selected"<?php } ?>>23:00</option>
						<option value="00:00" <?php if ($hitdhlexpress_close_time == '00:00') { ?>selected="selected"<?php } ?>>00:00</option>
		      </select>
            </div>
          </div>
		
        </form>
		<div>
			
		</div>
		
		<div class="pull-right">
		<a href="https://hittechmarket.com/downloads/dhl-express-shipping-for-opencart/" target="_blank" class="btn btn-primary"> Go Premium | Demo</a>
        <button type="submit" form="form-shipping" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
		</div>
      </div>
		</div>
    </div>
	
  </div>
</div>
<script type="text/javascript">
	jQuery(window).load(function(){
		jQuery('#hit_dhl_shipping_add_trackingpin_shipmentid').change(function(){
			if(jQuery(hit_dhl_shipping_add_trackingpin_shipmentid).is(':checked')) {
				jQuery('#dhl_email_service').show();
			}else
			{
				jQuery('#dhl_email_service').hide();
			}
		}).change();

		jQuery('#hit_dhl_shipping_return_label_key').change(function(){
			if(jQuery('#hit_dhl_shipping_return_label_key').is(':checked')) {
				jQuery('#hit_return_label_acc_number').show();
			}else
			{
				jQuery('#hit_return_label_acc_number').hide();
			}
		}).change();

		jQuery('#hit_dhl_shipping_request_archive_airway_label').change(function(){
			if(jQuery('#hit_dhl_shipping_request_archive_airway_label').is(':checked')) {
				jQuery('#hit_no_of_archive_bills').show();
			}else
			{
				jQuery('#hit_no_of_archive_bills').hide();
			}
		}).change();
		jQuery('#hit_dhl_hitdhlexpress_email_notification_service').change(function(){
			if(jQuery('#hit_dhl_hitdhlexpress_email_notification_service').is(':checked')) {
				jQuery('#hit_dhl_email_notification_message').show();
			}else
			{
				jQuery('#hit_dhl_email_notification_message').hide();
			}
		}).change();
		jQuery('#hit_dhl_shipping_dutypayment_type').change(function(){
			if(jQuery(this).val() == 'T') {
				jQuery('#hit_t_acc_number').show();
			}else
			{
				jQuery('#hit_t_acc_number').hide();
			}
		}).change();



		var pack_type_options = '<?php echo $option_string; ?>';
		jQuery('.dhl_boxes .insert').click( function() {
			var $tbody = jQuery('.dhl_boxes').find('#rates');
			var size = $tbody.find('tr').size();
			var code = '<tr class="new">\
	<td  style="padding:3px;" class="check-column"><input type="checkbox" /></td>\
	<input type="hidden" size="1" name="boxes_id[' + size + ']" />\
	<td style="padding:3px;"><input type="text" size="25" name="boxes_name[' + size + ']" /></td>\
	<td style="padding:3px;"><input type="text" style="width:100%;" name="boxes_length[' + size + ']" /></td>\
	<td style="padding:3px;"><input type="text" style="width:100%;" name="boxes_width[' + size + ']" /></td>\
	<td style="padding:3px;"><input type="text" style="width:100%;" name="boxes_height[' + size + ']" /></td>\
	<td style="padding:3px;"><input type="text" style="width:100%;" name="boxes_box_weight[' + size + ']" /></td>\
	<td style="padding:3px;"><input type="text" style="width:100%;" name="boxes_max_weight[' + size + ']" /></td>\
	<td style="padding:3px;"><center><input type="checkbox" name="boxes_enabled[' + size + ']" /></center></td>\
	<td style="padding:3px;"><select name="boxes_pack_type[' + size + ']" >' + pack_type_options + '</select></td>\
	</tr>';
			$tbody.append( code );
			return false;
		});

		jQuery('.dhl_boxes .remove').click(function() {
			var $tbody = jQuery('.dhl_boxes').find('#rates');
			$tbody.find('.check-column input:checked').each(function() {
				jQuery(this).closest('tr').hide().find('input').val('');
			});

			return false;
		});

	});

</script>
      
<?php echo $footer; ?>