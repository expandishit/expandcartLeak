<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-wkpos" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
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
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
        <a href="<?php echo $front_end; ?>" class="pull-right btn btn-warning" style="margin-top: -8px; font-weight: bold;" target="_blank"><?php echo $text_pos_front; ?></a>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-wkpos" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="wkpos_status" id="input-status" class="form-control">
                <?php if ($wkpos_status) { ?>
                <option value="0"><?php echo $text_disabled; ?></option>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <?php } else { ?>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <option value="1"><?php echo $text_enabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
            <li><a href="#tab-customer" data-toggle="tab"><?php echo $tab_customer; ?></a></li>
            <li><a href="#tab-payment" data-toggle="tab"><?php echo $tab_payment; ?></a></li>
            <li><a href="#tab-shipping" data-toggle="tab"><?php echo $tab_shipping; ?></a></li>
            <li><a href="#tab-receipt" data-toggle="tab"><?php echo $tab_receipt; ?></a></li>
            <li><a href="#tab-barcode" data-toggle="tab"><?php echo $tab_barcode; ?></a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab-general">
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-store-country"><?php echo $entry_store_country; ?></label>
                <div class="col-sm-10">
                  <select name="wkpos_store_country_id" id="input-store-country" class="form-control">
                    <option value=""><?php echo $text_select; ?></option>
                    <?php foreach ($countries as $country) { ?>
                    <?php if ($country['country_id'] == $wkpos_store_country_id) { ?>
                    <option value="<?php echo $country['country_id']; ?>" selected="selected"><?php echo $country['name']; ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $country['country_id']; ?>"><?php echo $country['name']; ?></option>
                    <?php } ?>
                    <?php } ?>
                  </select>
                  <?php if ($error_store_country) { ?>
                  <div class="text-danger"><?php echo $error_store_country; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-store-zone"><?php echo $entry_store_zone; ?></label>
                <div class="col-sm-10">
                  <select name="wkpos_store_zone_id" id="input-store-zone" class="form-control">
                  </select>
                  <?php if ($error_store_zone) { ?>
                  <div class="text-danger"><?php echo $error_store_zone; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-heading1"><span data-toggle="tooltip" title="<?php echo $help_heading1; ?>"><?php echo $entry_heading1; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="wkpos_heading1" value="<?php echo $wkpos_heading1; ?>" placeholder="<?php echo $entry_heading1; ?>" id="input-heading1" class="form-control" />
                  <?php if ($error_wkpos_heading1) { ?>
                  <div class="text-danger"><?php echo $error_wkpos_heading1; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-heading2"><span data-toggle="tooltip" title="<?php echo $help_heading2; ?>"><?php echo $entry_heading2; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="wkpos_heading2" value="<?php echo $wkpos_heading2; ?>" placeholder="<?php echo $entry_heading2; ?>" id="input-heading2" class="form-control" />
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-logcontent"><span data-toggle="tooltip" title="<?php echo $help_logcontent; ?>"><?php echo $entry_logcontent; ?></span></label>
                <div class="col-sm-10">
                  <textarea name="wkpos_logcontent" placeholder="<?php echo $entry_logcontent; ?>" id="input-logcontent" class="form-control" rows="6"><?php echo $wkpos_logcontent; ?></textarea>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-populars"><span data-toggle="tooltip" title="<?php echo $help_populars; ?>"><?php echo $entry_populars; ?></span></label>
                <div class="col-sm-10">
                  <input type="number" name="wkpos_populars" value="<?php echo $wkpos_populars; ?>" min="0" placeholder="<?php echo $entry_populars; ?>" id="input-populars" class="form-control" onkeypress="return validate(event, this);"/>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-low-stock"><span data-toggle="tooltip" title="<?php echo $help_low_stock; ?>"><?php echo $entry_low_stock; ?></span></label>
                <div class="col-sm-10">
                  <input type="number" name="wkpos_low_stock" min="0" value="<?php echo $wkpos_low_stock; ?>" placeholder="<?php echo $entry_low_stock; ?>" id="input-low-stock" class="form-control" onkeypress="return validate(event, this);"/>
                  <?php if ($error_low_stock) { ?>
                  <div class="text-danger"><?php echo $error_low_stock; ?></div>
                  <?php } ?>
                </div>
              </div>
              <?php if ($price_list_status) { ?>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-price-list"><span data-toggle="tooltip" title="<?php echo $help_price_list; ?>"><?php echo $entry_price_list_status; ?></span></label>
                <div class="col-sm-10">
                  <select class="form-control" name="wkpos_price_list_status">
                    <option <?php if ($wkpos_price_list_status) { ?>selected<?php } ?> value="1"><?php echo $text_enabled; ?></option>
                    <option <?php if ($wkpos_price_list_status == 0) { ?>selected<?php } ?> value="0"><?php echo $text_disabled; ?></option>
                  </select>
                </div>
              </div>
              <?php } ?>
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-show-whole"><span data-toggle="tooltip" title="<?php echo $help_show_whole; ?>"><?php echo $entry_show_whole; ?></span></label>
                <div class="col-sm-10">
                  <label class="radio-inline">
                    <?php if ($wkpos_show_whole) { ?>
                    <input type="radio" name="wkpos_show_whole" value="1" checked="checked" />
                    <?php echo $text_yes; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_show_whole" value="1" />
                    <?php echo $text_yes; ?>
                    <?php } ?>
                  </label>
                  <label class="radio-inline">
                    <?php if (!$wkpos_show_whole) { ?>
                    <input type="radio" name="wkpos_show_whole" value="0" checked="checked" />
                    <?php echo $text_no; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_show_whole" value="0" />
                    <?php echo $text_no; ?>
                    <?php } ?>
                  </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-show-lowstockproduct"><?php echo $entry_show_lowstock_prod; ?></label>
                <div class="col-sm-10">
                  <label class="radio-inline">
                    <?php if ($wkpos_show_lowstock_prod) { ?>
                    <input type="radio" name="wkpos_show_lowstock_prod" value="1" checked="checked" />
                    <?php echo $text_yes; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_show_lowstock_prod" value="1" />
                    <?php echo $text_yes; ?>
                    <?php } ?>
                  </label>
                  <label class="radio-inline">
                    <?php if (!$wkpos_show_lowstock_prod) { ?>
                    <input type="radio" name="wkpos_show_lowstock_prod" value="0" checked="checked" />
                    <?php echo $text_no; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_show_lowstock_prod" value="0" />
                    <?php echo $text_no; ?>
                    <?php } ?>
                  </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-email-agent"><?php echo $entry_email_agent; ?></label>
                <div class="col-sm-10">
                  <label class="radio-inline">
                    <?php if ($wkpos_email_agent) { ?>
                    <input type="radio" name="wkpos_email_agent" value="1" checked="checked" />
                    <?php echo $text_yes; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_email_agent" value="1" />
                    <?php echo $text_yes; ?>
                    <?php } ?>
                  </label>
                  <label class="radio-inline">
                    <?php if (!$wkpos_email_agent) { ?>
                    <input type="radio" name="wkpos_email_agent" value="0" checked="checked" />
                    <?php echo $text_no; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_email_agent" value="0" />
                    <?php echo $text_no; ?>
                    <?php } ?>
                  </label>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-customer">
              <legend><?php echo $text_new_customer; ?></legend>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-new-customer-group"><span data-toggle="tooltip" title="<?php echo $help_new_group; ?>"><?php echo $entry_customer_group; ?></label>
                <div class="col-sm-10">
                  <select name="wkpos_new_customer_group_id" id="input-new-customer-group" class="form-control">
                    <?php foreach ($customer_groups as $customer_group) { ?>
                    <?php if ($customer_group['customer_group_id'] == $wkpos_new_customer_group_id) { ?>
                    <option value="<?php echo $customer_group['customer_group_id']; ?>" selected="selected"><?php echo $customer_group['name']; ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>
                    <?php } ?>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-newsletter"><span data-toggle="tooltip" title="<?php echo $help_newsletter; ?>"><?php echo $entry_newsletter; ?></span></label>
                <div class="col-sm-10">
                  <select name="wkpos_newsletter" id="input-newsletter" class="form-control">
                    <?php if ($wkpos_newsletter) { ?>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <?php } else { ?>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-password"><span data-toggle="tooltip" title="<?php echo $help_password; ?>"><?php echo $entry_password; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="wkpos_customer_password" value="<?php echo $wkpos_customer_password; ?>" placeholder="<?php echo $entry_password; ?>" id="input-password" class="form-control" />
                </div>
              </div>
              <legend><?php echo $text_default_customer; ?></legend>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-customer-group"><?php echo $entry_customer_group; ?></label>
                <div class="col-sm-10">
                  <select name="wkpos_customer_group_id" id="input-customer-group" class="form-control">
                    <?php foreach ($customer_groups as $customer_group) { ?>
                    <?php if ($customer_group['customer_group_id'] == $wkpos_customer_group_id) { ?>
                    <option value="<?php echo $customer_group['customer_group_id']; ?>" selected="selected"><?php echo $customer_group['name']; ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>
                    <?php } ?>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-firstname"><?php echo $entry_firstname; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="wkpos_firstname" value="<?php echo $wkpos_firstname; ?>" placeholder="<?php echo $entry_firstname; ?>" id="input-firstname" class="form-control" />
                  <?php if ($error_firstname) { ?>
                  <div class="text-danger"><?php echo $error_firstname; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-lastname"><?php echo $entry_lastname; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="wkpos_lastname" value="<?php echo $wkpos_lastname; ?>" placeholder="<?php echo $entry_lastname; ?>" id="input-lastname" class="form-control" />
                  <?php if ($error_lastname) { ?>
                  <div class="text-danger"><?php echo $error_lastname; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-email"><?php echo $entry_email; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="wkpos_email" value="<?php echo $wkpos_email; ?>" placeholder="<?php echo $entry_email; ?>" id="input-email" class="form-control" />
                  <?php if ($error_email) { ?>
                  <div class="text-danger"><?php echo $error_email; ?></div>
                  <?php  } ?>
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-telephone"><?php echo $entry_telephone; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="wkpos_telephone" value="<?php echo $wkpos_telephone; ?>" placeholder="<?php echo $entry_telephone; ?>" id="input-telephone" class="form-control" />
                  <?php if ($error_telephone) { ?>
                  <div class="text-danger"><?php echo $error_telephone; ?></div>
                  <?php  } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-fax"><?php echo $entry_fax; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="wkpos_fax" value="<?php echo $wkpos_fax; ?>" placeholder="<?php echo $entry_fax; ?>" id="input-fax" class="form-control" />
                </div>
              </div>
              <legend><?php echo $text_default_address; ?></legend>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-company"><?php echo $entry_company; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="wkpos_company" value="<?php echo $wkpos_company; ?>" placeholder="<?php echo $entry_company; ?>" id="input-company" class="form-control" />
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-address-1"><?php echo $entry_address_1; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="wkpos_address_1" value="<?php echo $wkpos_address_1; ?>" placeholder="<?php echo $entry_address_1; ?>" id="input-address-1" class="form-control" />
                  <?php if ($error_address_1) { ?>
                  <div class="text-danger"><?php echo $error_address_1; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-address-2"><?php echo $entry_address_2; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="wkpos_address_2" value="<?php echo $wkpos_address_2; ?>" placeholder="<?php echo $entry_address_2; ?>" id="input-address-2" class="form-control" />
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-city"><?php echo $entry_city; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="wkpos_city" value="<?php echo $wkpos_city; ?>" placeholder="<?php echo $entry_city; ?>" id="input-city" class="form-control" />
                  <?php if ($error_city) { ?>
                  <div class="text-danger"><?php echo $error_city; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-postcode"><?php echo $entry_postcode; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="wkpos_postcode" value="<?php echo $wkpos_postcode; ?>" placeholder="<?php echo $entry_postcode; ?>" id="input-postcode" class="form-control" />
                  <?php if ($error_postcode) { ?>
                  <div class="text-danger"><?php echo $error_postcode; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-country"><?php echo $entry_country; ?></label>
                <div class="col-sm-10">
                  <select name="wkpos_country_id" id="input-country" class="form-control">
                    <option value=""><?php echo $text_select; ?></option>
                    <?php foreach ($countries as $country) { ?>
                    <?php if ($country['country_id'] == $wkpos_country_id) { ?>
                    <option value="<?php echo $country['country_id']; ?>" selected="selected"><?php echo $country['name']; ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $country['country_id']; ?>"><?php echo $country['name']; ?></option>
                    <?php } ?>
                    <?php } ?>
                  </select>
                  <?php if ($error_country) { ?>
                  <div class="text-danger"><?php echo $error_country; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-zone"><?php echo $entry_zone; ?></label>
                <div class="col-sm-10">
                  <select name="wkpos_zone_id" id="input-zone" class="form-control">
                  </select>
                  <?php if ($error_zone) { ?>
                  <div class="text-danger"><?php echo $error_zone; ?></div>
                  <?php } ?>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-payment">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-cash-status"><?php echo $entry_cash_status; ?></label>
                <div class="col-sm-10">
                  <select name="wkpos_cash_status" id="input-cash-status" class="form-control">
                    <?php if ($wkpos_cash_status) { ?>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <?php } else { ?>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-cash-title"><span data-toggle="tooltip" title="<?php echo $help_cash_title; ?>"><?php echo $entry_cash_title; ?></span></label>
                <div class="col-sm-10">
                  <?php foreach ($languages as $language) { ?>
                    <div class="input-group">
                      <span class="input-group-addon" data-toggle="tooltip" title="<?php echo $language['name']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" alt="<?php echo $language['name']; ?>"></span>
                      <input type="text" class="form-control" name="wkpos_cash_title<?php echo $language['language_id']; ?>" value="<?php echo $wkpos_cash_title[$language['language_id']]; ?>" placeholder="<?php echo $entry_cash_title; ?>">
                    </div>
                    <?php if ($err_cash_title[$language['language_id']] != '') { ?>
                    <div class="text-danger"><?php echo $err_cash_title[$language['language_id']]; ?></div>
                    <?php } ?>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-cash-order-status"><span data-toggle="tooltip" title="<?php echo $help_cash_status; ?>"><?php echo $entry_cash_order_status; ?></span></label>
                <div class="col-sm-10">
                  <select name="wkpos_cash_order_status_id" id="input-cash-order-status" class="form-control">
                    <?php foreach ($order_statuses as $order_status) { ?>
                    <?php if ($order_status['order_status_id'] == $wkpos_cash_order_status_id) { ?>
                    <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                    <?php } ?>
                    <?php } ?>
                  </select>
                </div>
              </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-card-status"><?php echo $entry_card_status; ?></label>
                  <div class="col-sm-10">
                    <select name="wkpos_card_status" id="input-card-status" class="form-control">
                      <?php if ($wkpos_card_status) { ?>
                      <option value="0"><?php echo $text_disabled; ?></option>
                      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                      <?php } else { ?>
                      <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                      <option value="1"><?php echo $text_enabled; ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
                <div class="form-group required">
                  <label class="col-sm-2 control-label" for="input-card-title"><span data-toggle="tooltip" title="<?php echo $help_card_title; ?>"><?php echo $entry_card_title; ?></span></label>
                  <div class="col-sm-10">
                    <?php foreach ($languages as $language) { ?>
                      <div class="input-group">
                        <span class="input-group-addon" data-toggle="tooltip" title="<?php echo $language['name']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" alt="<?php echo $language['name']; ?>"></span>
                        <input type="text" class="form-control" name="wkpos_card_title<?php echo $language['language_id']; ?>" value="<?php echo $wkpos_card_title[$language['language_id']]; ?>" placeholder="<?php echo $entry_card_title; ?>">
                      </div>
                      <?php if ($err_card_title[$language['language_id']] != '') { ?>
                      <div class="text-danger"><?php echo $err_card_title[$language['language_id']]; ?></div>
                      <?php } ?>
                    <?php } ?>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-card-order-status"><span data-toggle="tooltip" title="<?php echo $help_card_status; ?>"><?php echo $entry_card_order_status; ?></span></label>
                  <div class="col-sm-10">
                    <select name="wkpos_card_order_status_id" id="input-card-order-status" class="form-control">
                      <?php foreach ($order_statuses as $order_status) { ?>
                      <?php if ($order_status['order_status_id'] == $wkpos_card_order_status_id) { ?>
                      <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                      <?php } else { ?>
                      <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                      <?php } ?>
                      <?php } ?>
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-2 control-label" for="select-credit-status"><?php echo $entry_credit_status; ?></label>
                  <div class="col-sm-10">
                    <select class="form-control" name="wkpos_credit_status" id="select-credit-status">
                      <option <?php if ($wkpos_credit_status == 1) { ?>selected<?php } ?> value="1"><?php echo $text_enabled; ?></option>
                      <option <?php if ($wkpos_credit_status == 0) { ?>selected<?php } ?> value="0"><?php echo $text_disabled; ?></option>
                    </select>
                  </div>
                </div>
                <div class="form-group required">
                  <label class="col-sm-2 control-label"><?php echo $entry_credit_title; ?><span data-toggle="tooltip" title="<?php echo $help_credit_title; ?>"></span></label>
                  <div class="col-sm-10">
                    <?php foreach ($languages as $language) { ?>
                      <div class="input-group">
                        <span class="input-group-addon" data-toggle="tooltip" title="<?php echo $language['name']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" alt="<?php echo $language['name']; ?>"></span>
                        <input type="text" class="form-control" name="wkpos_credit_title<?php echo $language['language_id']; ?>" value="<?php echo $wkpos_credit_title[$language['language_id']]; ?>" placeholder="<?php echo $entry_credit_title; ?>">
                      </div>
                      <?php if ($err_credit_title[$language['language_id']]) { ?>
                      <div class="text-danger"><?php echo $err_credit_title[$language['language_id']]; ?></div>
                      <?php } ?>
                    <?php } ?>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label"> <span data-toggle="tooltip" title="<?php echo $help_credit_order; ?>"><?php echo $entry_credit_order_status; ?></span></label>
                  <div class="col-sm-10">
                    <select class="form-control" name="wkpos_credit_order_status">
                      <?php foreach($order_statuses as $order_status) { ?>
                        <option <?php if($wkpos_credit_order_status == $order_status['order_status_id']) { echo 'selected'; } ?>  value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-discount-status"><?php echo $entry_discount_status; ?></label>
                <div class="col-sm-10">
                  <select name="wkpos_discount_status" id="input-discount-status" class="form-control">
                    <?php if ($wkpos_discount_status) { ?>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <?php } else { ?>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-coupon-status"><?php echo $entry_coupon_status; ?></label>
                <div class="col-sm-10">
                  <select name="wkpos_coupon_status" id="input-coupon-status" class="form-control">
                    <?php if ($wkpos_coupon_status) { ?>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <?php } else { ?>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-tax-status"><?php echo $entry_tax_status; ?></label>
                <div class="col-sm-10">
                  <select name="wkpos_tax_status" id="input-tax-status" class="form-control">
                    <?php if ($wkpos_tax_status) { ?>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <?php } else { ?>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-shipping">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="select-home-delivery"><?php echo $entry_home_delivery_staus; ?></label>
                <div class="col-sm-10">
                  <select class="form-control" name="wkpos_home_delivery_status" id="select-home-delivery">
                    <option <?php if ($wkpos_home_delivery_status) { echo 'selected'; } ?> value="1"><?php echo $text_enabled; ?></option>
                    <option <?php if ($wkpos_home_delivery_status == 0) { echo "selected"; } ?> value="0"><?php echo $text_disabled; ?></option>
                  </select>
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-home-delivery-title"><?php echo $entry_home_delivery_title; ?></label>
                <div class="col-sm-10">
                  <?php foreach ($languages as $language) { ?>
                    <div class="input-group">
                      <span class="input-group-addon" data-toggle="tooltip" title="<?php echo $language['name']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" alt="<?php echo $language['name']; ?>"></span>
                      <input type="text" class="form-control" name="wkpos_home_delivery_title<?php echo $language['language_id']; ?>" value="<?php echo $wkpos_home_delivery_title[$language['language_id']]; ?>">
                    </div>
                    <?php if ($err_delivery_title[$language['language_id']]) { ?>
                      <div class="text-danger">
                        <?php echo $err_delivery_title[$language['language_id']]; ?>
                      </div>
                    <?php } ?>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-max-charge"><span data-toggle="tooltip" title="<?php echo $help_max_charge; ?>"><?php echo $entry_home_delivery_max; ?></span></label>
                <div class="col-sm-10">
                  <div class="input-group">
                    <span class="input-group-addon"><?php echo $currency; ?></span>
                    <input type="number" name="wkpos_home_delivery_max" class="form-control" id="input-max-charge" placeholder="<?php echo $entry_home_delivery_max; ?>" value="<?php echo $wkpos_home_delivery_max; ?>" min="0" onkeypress="return validate(event, this)">
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-receipt">
              <div class="form-group">
                <label class="control-label col-sm-2" for="input-print-size"><span data-toggle="tooltip" title="<?php echo $help_print_size; ?>"><?php echo $entry_paper_size; ?></span></label>
                <div class="col-sm-10">
                  <select class="form-control" name="wkpos_print_size" id="input-print-size">
                    <option <?php if ($wkpos_print_size == '595px') { echo 'selected'; } ?> value="595px"><?php echo $text_a4; ?></option>
                    <option <?php if ($wkpos_print_size == '80mm') { echo 'selected'; } ?> value="80mm"><?php echo $text_thermal; ?></option>
                    <option <?php if ($wkpos_print_size == '58mm') { echo 'selected'; } ?> value="58mm"><?php echo $text_mini_thermal; ?></option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label for="input-font-weight" class="control-label col-sm-2"><?php echo $entry_font_weight; ?></label>
                <div class="col-sm-10">
                  <select class="form-control" name="wkpos_print_font_weight">
                    <option <?php if ($wkpos_print_font_weight == 'normal') { echo 'selected'; } ?> value="normal"><?php echo $text_normal; ?></option>
                    <option <?php if ($wkpos_print_font_weight == 'bold') { echo 'selected'; } ?> value="bold"><?php echo $text_bold; ?></option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-store-logo"><?php echo $entry_store_logo; ?></label>
                <div class="col-sm-10">
                  <label class="radio-inline">
                    <?php if ($wkpos_store_logo) { ?>
                    <input type="radio" name="wkpos_store_logo" value="1" checked="checked" />
                    <?php echo $text_yes; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_store_logo" value="1" />
                    <?php echo $text_yes; ?>
                    <?php } ?>
                  </label>
                  <label class="radio-inline">
                    <?php if (!$wkpos_store_logo) { ?>
                    <input type="radio" name="wkpos_store_logo" value="0" checked="checked" />
                    <?php echo $text_no; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_store_logo" value="0" />
                    <?php echo $text_no; ?>
                    <?php } ?>
                  </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-store-name"><?php echo $entry_store_name; ?></label>
                <div class="col-sm-10">
                  <label class="radio-inline">
                    <?php if ($wkpos_store_name) { ?>
                    <input type="radio" name="wkpos_store_name" value="1" checked="checked" />
                    <?php echo $text_yes; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_store_name" value="1" />
                    <?php echo $text_yes; ?>
                    <?php } ?>
                  </label>
                  <label class="radio-inline">
                    <?php if (!$wkpos_store_name) { ?>
                    <input type="radio" name="wkpos_store_name" value="0" checked="checked" />
                    <?php echo $text_no; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_store_name" value="0" />
                    <?php echo $text_no; ?>
                    <?php } ?>
                  </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-store-address"><?php echo $entry_store_address; ?></label>
                <div class="col-sm-10">
                  <label class="radio-inline">
                    <?php if ($wkpos_store_address) { ?>
                    <input type="radio" name="wkpos_store_address" value="1" checked="checked" />
                    <?php echo $text_yes; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_store_address" value="1" />
                    <?php echo $text_yes; ?>
                    <?php } ?>
                  </label>
                  <label class="radio-inline">
                    <?php if (!$wkpos_store_address) { ?>
                    <input type="radio" name="wkpos_store_address" value="0" checked="checked" />
                    <?php echo $text_no; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_store_address" value="0" />
                    <?php echo $text_no; ?>
                    <?php } ?>
                  </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-order-date"><?php echo $entry_order_date; ?></label>
                <div class="col-sm-10">
                  <label class="radio-inline">
                    <?php if ($wkpos_order_date) { ?>
                    <input type="radio" name="wkpos_order_date" value="1" checked="checked" />
                    <?php echo $text_yes; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_order_date" value="1" />
                    <?php echo $text_yes; ?>
                    <?php } ?>
                  </label>
                  <label class="radio-inline">
                    <?php if (!$wkpos_order_date) { ?>
                    <input type="radio" name="wkpos_order_date" value="0" checked="checked" />
                    <?php echo $text_no; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_order_date" value="0" />
                    <?php echo $text_no; ?>
                    <?php } ?>
                  </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-order-time"><?php echo $entry_order_time; ?></label>
                <div class="col-sm-10">
                  <label class="radio-inline">
                    <?php if ($wkpos_order_time) { ?>
                    <input type="radio" name="wkpos_order_time" value="1" checked="checked" />
                    <?php echo $text_yes; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_order_time" value="1" />
                    <?php echo $text_yes; ?>
                    <?php } ?>
                  </label>
                  <label class="radio-inline">
                    <?php if (!$wkpos_order_time) { ?>
                    <input type="radio" name="wkpos_order_time" value="0" checked="checked" />
                    <?php echo $text_no; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_order_time" value="0" />
                    <?php echo $text_no; ?>
                    <?php } ?>
                  </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-order-id"><?php echo $entry_order_id; ?></label>
                <div class="col-sm-10">
                  <label class="radio-inline">
                    <?php if ($wkpos_order_id) { ?>
                    <input type="radio" name="wkpos_order_id" value="1" checked="checked" />
                    <?php echo $text_yes; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_order_id" value="1" />
                    <?php echo $text_yes; ?>
                    <?php } ?>
                  </label>
                  <label class="radio-inline">
                    <?php if (!$wkpos_order_id) { ?>
                    <input type="radio" name="wkpos_order_id" value="0" checked="checked" />
                    <?php echo $text_no; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_order_id" value="0" />
                    <?php echo $text_no; ?>
                    <?php } ?>
                  </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-cashier-name"><?php echo $entry_cashier_name; ?></label>
                <div class="col-sm-10">
                  <label class="radio-inline">
                    <?php if ($wkpos_cashier_name) { ?>
                    <input type="radio" name="wkpos_cashier_name" value="1" checked="checked" />
                    <?php echo $text_yes; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_cashier_name" value="1" />
                    <?php echo $text_yes; ?>
                    <?php } ?>
                  </label>
                  <label class="radio-inline">
                    <?php if (!$wkpos_cashier_name) { ?>
                    <input type="radio" name="wkpos_cashier_name" value="0" checked="checked" />
                    <?php echo $text_no; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_cashier_name" value="0" />
                    <?php echo $text_no; ?>
                    <?php } ?>
                  </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_customer_name; ?></label>
                <div class="col-sm-10">
                  <?php if ($wkpos_customer_name) { ?>
                  <label class="radio-inline">
                    <input type="radio" name="wkpos_customer_name" value="1" checked="checked"> <?php echo $text_yes; ?>
                  </label>
                  <label class="radio-inline">
                    <input type="radio" name="wkpos_customer_name" value="0"> <?php echo $text_no; ?>
                  </label>
                  <?php } else { ?>
                  <label class="radio-inline">
                    <input type="radio" name="wkpos_customer_name" value="1"> <?php echo $text_yes; ?>
                  </label>
                  <label class="radio-inline">
                    <input type="radio" name="wkpos_customer_name" value="0" checked="checked"><?php echo $text_no; ?>
                  </label>
                <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-shipping-mode"><?php echo $entry_shipping_mode; ?></label>
                <div class="col-sm-10">
                  <label class="radio-inline">
                    <?php if ($wkpos_shipping_mode) { ?>
                    <input type="radio" name="wkpos_shipping_mode" value="1" checked="checked" />
                    <?php echo $text_yes; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_shipping_mode" value="1" />
                    <?php echo $text_yes; ?>
                    <?php } ?>
                  </label>
                  <label class="radio-inline">
                    <?php if (!$wkpos_shipping_mode) { ?>
                    <input type="radio" name="wkpos_shipping_mode" value="0" checked="checked" />
                    <?php echo $text_no; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_shipping_mode" value="0" />
                    <?php echo $text_no; ?>
                    <?php } ?>
                  </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-payment-mode"><?php echo $entry_payment_mode; ?></label>
                <div class="col-sm-10">
                  <label class="radio-inline">
                    <?php if ($wkpos_payment_mode) { ?>
                    <input type="radio" name="wkpos_payment_mode" value="1" checked="checked" />
                    <?php echo $text_yes; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_payment_mode" value="1" />
                    <?php echo $text_yes; ?>
                    <?php } ?>
                  </label>
                  <label class="radio-inline">
                    <?php if (!$wkpos_payment_mode) { ?>
                    <input type="radio" name="wkpos_payment_mode" value="0" checked="checked" />
                    <?php echo $text_no; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_payment_mode" value="0" />
                    <?php echo $text_no; ?>
                    <?php } ?>
                  </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-ordernote"><?php echo $entry_show_note; ?></label>
                <div class="col-sm-10">
                  <label class="radio-inline">
                    <?php if ($wkpos_show_note) { ?>
                    <input type="radio" name="wkpos_show_note" value="1" checked="checked" />
                    <?php echo $text_yes; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_show_note" value="1" />
                    <?php echo $text_yes; ?>
                    <?php } ?>
                  </label>
                  <label class="radio-inline">
                    <?php if (!$wkpos_show_note) { ?>
                    <input type="radio" name="wkpos_show_note" value="0" checked="checked" />
                    <?php echo $text_no; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_show_note" value="0" />
                    <?php echo $text_no; ?>
                    <?php } ?>
                  </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-store-detail"><span data-toggle="tooltip" title="<?php echo $help_store_detail; ?>"><?php echo $entry_store_detail; ?></span></label>
                <div class="col-sm-10">
                  <textarea name="wkpos_store_detail" placeholder="<?php echo $entry_store_detail; ?>" id="input-store-detail" class="form-control" rows="6"><?php echo $wkpos_store_detail; ?></textarea>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-barcode">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-barcode-width"><span data-toggle="tooltip" title="<?php echo $help_barcode_width; ?>"><?php echo $entry_barcode_width; ?></span></label>
                <div class="col-sm-10">
                  <input type="number" name="wkpos_barcode_width" value="<?php echo $wkpos_barcode_width; ?>" placeholder="<?php echo $entry_barcode_width; ?>" min="0" id="input-barcode-width" class="form-control" onkeypress="return validate(event, this);"/>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-barcode-name"><span data-toggle="tooltip" title="<?php echo $help_barcode_name; ?>"><?php echo $entry_barcode_name; ?></span></label>
                <div class="col-sm-10">
                  <label class="radio-inline">
                    <?php if ($wkpos_barcode_name) { ?>
                    <input type="radio" name="wkpos_barcode_name" value="1" checked="checked" id="input-barcodename" />
                    <?php echo $text_yes; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_barcode_name" value="1" />
                    <?php echo $text_yes; ?>
                    <?php } ?>
                  </label>
                  <label class="radio-inline">
                    <?php if (!$wkpos_barcode_name) { ?>
                    <input type="radio" name="wkpos_barcode_name" value="0" checked="checked" id="input-barcodename" />
                    <?php echo $text_no; ?>
                    <?php } else { ?>
                    <input type="radio" name="wkpos_barcode_name" value="0" />
                    <?php echo $text_no; ?>
                    <?php } ?>
                  </label>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-sm-2" for="input-barcode-image"><?php echo $entry_barcode_type; ?></label>
                <div class="col-sm-10">
                  <?php if ($wkpos_barcode_type == 'vertical') { ?>
                    <label class="radio-inline">
                      <input type="radio" name="wkpos_barcode_type" value="horizontal"><?php echo $text_horizontal; ?><img src="../image/wkpos/horizontal.png"/>
                    </label>
                    <label class="radio-inline">
                      <input type="radio" name="wkpos_barcode_type" value="vertical" checked="checked"><?php echo $text_vertical; ?><img style="margin-left: 10px;" src="../image/wkpos/vertical.png"/>
                    </label>
                  <?php } else { ?>
                    <label class="radio-inline">
                      <input type="radio" name="wkpos_barcode_type" value="horizontal" checked="checked"><?php echo $text_horizontal; ?><img src="../image/wkpos/horizontal.png"/>
                    </label>
                    <label class="radio-inline">
                      <input type="radio" name="wkpos_barcode_type" value="vertical"><?php echo $text_vertical; ?><img style="margin-left: 10px;" src="../image/wkpos/vertical.png"/>
                    </label>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="select-barcode-generate"><span data-toggle="tooltip" title="<?php echo $help_generate_with; ?>"><?php echo $entry_generate_with; ?></span></label>
                <div class="col-sm-10">
                  <select class="form-control" name="wkpos_generate_barcode" id="select-barcode-generate">
                    <option <?php if ($wkpos_generate_barcode == 'id') { echo 'selected'; } ?> value="id"><?php echo $text_id; ?></option>
                    <option <?php if ($wkpos_generate_barcode == 'upc' ) { echo 'selected'; } ?> value="upc"><?php echo $text_upc; ?></option>
                    <option <?php if ($wkpos_generate_barcode == 'sku') { echo 'selected'; } ?> value="sku"><?php echo $text_sku; ?></option>
                    <option <?php if ($wkpos_generate_barcode == 'ean') { echo 'selected'; } ?> value="ean"><?php echo $text_ean; ?></option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-slot-number"><span data-toggle="tooltip" title="<?php echo $help_number_slot; ?>"><?php echo $entry_number_slot; ?></span></label>
                <div class="col-sm-10">
                  <input type="number" id="input-slot-number" name="wkpos_barcode_slot" value="<?php echo $wkpos_barcode_slot; ?>" class="form-control"  min="5" placeholder="<?php echo $entry_number_slot; ?>" onkeypress="return validate(event, this, true)"/>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
$('select[name=\'wkpos_country_id\']').on('change', function() {
  $.ajax({
    url: 'sale/customer/country?country_id=' + this.value,
    dataType: 'json',
    beforeSend: function() {
      $('select[name=\'wkpos_country_id\']').after(' <i class="fa fa-circle-o-notch fa-spin"></i>');
    },
    complete: function() {
      $('.fa-spin').remove();
    },
    success: function(json) {
      if (json['postcode_required'] == '1') {
        $('input[name=\'wkpos_postcode\']').parent().parent().addClass('required');
      } else {
        $('input[name=\'wkpos_postcode\']').parent().parent().removeClass('required');
      }

      html = '<option value=""><?php echo $text_select; ?></option>';

      if (json['zone'] && json['zone'] != '') {
        for (i = 0; i < json['zone'].length; i++) {
          html += '<option value="' + json['zone'][i]['zone_id'] + '"';

          if (json['zone'][i]['zone_id'] == '<?php echo $wkpos_zone_id; ?>') {
            html += ' selected="selected"';
          }

          html += '>' + json['zone'][i]['name'] + '</option>';
        }
      } else {
        html += '<option value="0" selected="selected"><?php echo $text_none; ?></option>';
      }

      $('select[name=\'wkpos_zone_id\']').html(html);
    },
    error: function(xhr, ajaxOptions, thrownError) {
      alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
    }
  });
});

$('select[name=\'wkpos_country_id\']').trigger('change');
$('select[name=\'wkpos_store_country_id\']').on('change', function() {
  $.ajax({
    url: 'sale/customer/country?country_id=' + this.value,
    dataType: 'json',
    beforeSend: function() {
      $('select[name=\'wkpos_store_country_id\']').after(' <i class="fa fa-circle-o-notch fa-spin"></i>');
    },
    complete: function() {
      $('.fa-spin').remove();
    },
    success: function(json) {
      html = '<option value=""><?php echo $text_select; ?></option>';

      if (json['zone'] && json['zone'] != '') {
        for (i = 0; i < json['zone'].length; i++) {
          html += '<option value="' + json['zone'][i]['zone_id'] + '"';

          if (json['zone'][i]['zone_id'] == '<?php echo $wkpos_store_zone_id; ?>') {
            html += ' selected="selected"';
          }

          html += '>' + json['zone'][i]['name'] + '</option>';
        }
      } else {
        html += '<option value="0" selected="selected"><?php echo $text_none; ?></option>';
      }

      $('select[name=\'wkpos_store_zone_id\']').html(html);
    },
    error: function(xhr, ajaxOptions, thrownError) {
      alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
    }
  });
});
function validate(key, thisthis, nodot) {
  //getting key code of pressed key
  var keycode = (key.which) ? key.which : key.keyCode;

  if (keycode == 46) {
    if (nodot) {
      return false;
    }

    var val = $(thisthis).val();
    if (val == val.replace('.', '')) {
      return true;
    } else {
      return false;
    }
  }

  //comparing pressed keycodes
  if (!(keycode == 8 || keycode == 9 || keycode == 46 || keycode == 116) && (keycode < 48 || keycode > 57)) {
    return false;
  } else {
    return true;
  }
}
$('select[name=\'wkpos_store_country_id\']').trigger('change');
//--></script>
