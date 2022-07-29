<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
<link href="view/stylesheet/csspin.css" rel="stylesheet" type="text/css"/>
<link href="view/stylesheet/style.css" rel="stylesheet" type="text/css"/>
<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed" rel="stylesheet" type="text/css">
<style type="text/css">
  .block_div{
    background-color: #000;
    height: 100%;
    left: 0;
    opacity: 0.5;
    position: absolute;
    top: 0;
    width: 100%;
    z-index: 99;
    display: none;
  }
  .block_spinner {
    left: 50%;
    position: relative;
    top: 35%;
  }
</style>
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-account" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
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
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-account" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-3 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-9">
              <select name="wk_amazon_connector_status" id="input-status" class="form-control">
                <?php if ($wk_amazon_connector_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <div id="accordion" role="tablist" aria-multiselectable="true">
            <div class="panel panel-primary">
              <div class="panel-heading collapsed" id="heading_general" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_general" aria-expanded="false" aria-controls="collapse_general">
                <h4 class="panel-title">
                  <i class="text-danger fa fa-cogs" aria-hidden="true"></i> <?php echo $panel_general_options; ?>
                </h4>
              </div>
              <div id="collapse_general" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading_general">
                <div class="panel-body">
                    <div class="form-group">
                      <label class="col-sm-3 control-label" for="input-default-category"><span data-toggle="tooltip" title="<?php echo $help_default_category; ?>"><?php echo $entry_default_category; ?></span></label>
                      <div class="col-sm-9">
                        <select id="input-default-category" name="wk_amazon_connector_default_category" class="form-control">
                         <?php foreach($getOcParentCategory as $key => $value){ ?>
                            <?php if(isset($wk_amazon_connector_default_category) && $wk_amazon_connector_default_category == $value['category_id']){ ?>
                              <option value="<?php echo $value['category_id']; ?>" selected="selected"><?php echo $value['name']; ?></option>
                            <?php }else{ ?>
                              <option value="<?php echo $value['category_id']; ?>" ><?php echo $value['name']; ?></option>
                            <?php } ?>
                          <?php } ?>
                        </select>
                      </div>
                    </div>

                    <div class="form-group required">
                      <label class="col-sm-3 control-label" for="input-default-quantity"><span data-toggle="tooltip" title="<?php echo $help_default_quantity; ?>"><?php echo $entry_default_quantity; ?></span></label>
                      <div class="col-sm-9">
                        <input type="text" name="wk_amazon_connector_default_quantity" class="form-control" id="input-default-quantity" placeholder="<?php echo $placeholder_quantity; ?>" value="<?php if(isset($wk_amazon_connector_default_quantity) && $wk_amazon_connector_default_quantity) { echo $wk_amazon_connector_default_quantity; } ?>" />
                      </div>
                    </div>

                    <div class="form-group required">
                      <label class="col-sm-3 control-label" for="input-default-weight"><span data-toggle="tooltip" title="<?php echo $help_default_weight; ?>"><?php echo $entry_default_weight; ?></span></label>
                      <div class="col-sm-9">
                        <input type="text" name="wk_amazon_connector_default_weight" class="form-control" id="input-default-weight" placeholder="<?php echo $placeholder_weight; ?>" value="<?php if(isset($wk_amazon_connector_default_weight) && $wk_amazon_connector_default_weight) { echo $wk_amazon_connector_default_weight; } ?>" />
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-sm-3 control-label" for="input-cron-create-product"><span data-toggle="tooltip" title="<?php echo $help_cron_create_product; ?>"><?php echo $entry_cron_create_product; ?></span></label>
                      <div class="col-sm-9">
                        <select name="wk_amazon_connector_cron_create_product" class="form-control">
                          <?php if ($wk_amazon_connector_cron_create_product) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-sm-3 control-label" for="input-cron-update-product"><span data-toggle="tooltip" title="<?php echo $help_cron_update_product; ?>"><?php echo $entry_cron_update_product; ?></span></label>
                      <div class="col-sm-9">
                        <select name="wk_amazon_connector_cron_update_product" class="form-control">
                          <?php if ($wk_amazon_connector_cron_update_product) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>
                </div>
              </div>
            </div>


            <div class="panel panel-primary">
              <div class="panel-heading collapsed" id="heading_order" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_order" aria-expanded="false" aria-controls="collapse_order">
                <h4 class="panel-title">
                  <i class="text-success fa fa-gift" aria-hidden="true"></i> <?php echo $panel_order_options; ?>
                </h4>
              </div>
              <div id="collapse_order" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_order">
                <div class="panel-body">

                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="input-default-store"><span data-toggle="tooltip" title="<?php echo $help_default_store; ?>"><?php echo $entry_default_store; ?></span></label>
                    <div class="col-sm-9">
                      <select id="input-default-store" name="wk_amazon_connector_default_store" class="form-control">
                       <option value="0"><?php echo $text_default; ?></option>
                        <?php if(isset($stores) && $stores){ ?>
                          <?php foreach($stores as $key => $store){ ?>
                            <?php if(isset($wk_amazon_connector_default_store) && $wk_amazon_connector_default_store == $store['store_id']){ ?>
                              <option value="<?php echo $store['store_id']; ?>" selected="selected"><?php echo $store['name']; ?></option>
                            <?php }else{ ?>
                              <option value="<?php echo $store['store_id']; ?>" ><?php echo $store['name']; ?></option>
                            <?php } ?>
                          <?php } ?>
                        <?php } ?>
                      </select>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="input-order-status"><span data-toggle="tooltip" title="<?php echo $help_order_status; ?>"><?php echo $entry_order_status; ?></span></label>
                    <div class="col-sm-9">
                      <select id="input-order-status" name="wk_amazon_connector_order_status" class="form-control">
                       <?php foreach($order_status as $key => $value){ ?>
                          <?php if(isset($wk_amazon_connector_order_status) && $wk_amazon_connector_order_status == $value['order_status_id']){ ?>
                            <option value="<?php echo $value['order_status_id']; ?>" selected="selected"><?php echo $value['name']; ?></option>
                          <?php }else{ ?>
                            <option value="<?php echo $value['order_status_id']; ?>" ><?php echo $value['name']; ?></option>
                          <?php } ?>
                        <?php } ?>
                      </select>
                    </div>
                  </div>

                </div>
              </div>
            </div>

            <div class="panel panel-primary">
              <div class="panel-heading collapsed" id="heading_product"  role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_product" aria-expanded="false" aria-controls="collapse_product">
                <h4 class="panel-title">
                  <i class="text-warning fa fa-picture-o" aria-hidden="true"></i> <?php echo $panel_product_options; ?>
                </h4>
              </div>
              <div id="collapse_product" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_product">
                <div class="panel-body">

                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="input-default-store"><span data-toggle="tooltip" title="<?php echo $help_default_product_store; ?>"><?php echo $entry_default_product_store; ?></span></label>
                    <div class="col-sm-9">
                      <select id="input-default-store" name="wk_amazon_connector_default_product_store" class="form-control">
                       <option value="0"><?php echo $text_default; ?></option>
                        <?php if(isset($stores) && $stores){ ?>
                          <?php foreach($stores as $key => $store){ ?>
                            <?php if(isset($wk_amazon_connector_default_product_store) && $wk_amazon_connector_default_product_store == $store['store_id']){ ?>
                              <option value="<?php echo $store['store_id']; ?>" selected="selected"><?php echo $store['name']; ?></option>
                            <?php }else{ ?>
                              <option value="<?php echo $store['store_id']; ?>" ><?php echo $store['name']; ?></option>
                            <?php } ?>
                          <?php } ?>
                        <?php } ?>
                      </select>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="input-variation-option"><span data-toggle="tooltip" title="<?php echo $help_variation_options; ?>"><?php echo $entry_variation_options; ?></span></label>
                    <div class="col-sm-9">
                      <label class="col-sm-12 radio-inline">
                        <?php if(isset($wk_amazon_connector_variation) && $wk_amazon_connector_variation == 1){ ?>
                          <input type="radio" name="wk_amazon_connector_variation" value="1" checked = "checked" />
                        <?php }else{ ?>
                          <input type="radio" name="wk_amazon_connector_variation" value="1" />
                        <?php } ?>
                        <?php echo $text_option1; ?>
                      </label>
                      <label class="col-sm-12 radio-inline" style="margin-left: 0;">
                        <?php if(isset($wk_amazon_connector_variation) && $wk_amazon_connector_variation == 2){ ?>
                          <input type="radio" name="wk_amazon_connector_variation" value="2" checked = "checked" />
                        <?php }else{ ?>
                          <input type="radio" name="wk_amazon_connector_variation" value="2" />
                        <?php } ?>
                        <?php echo $text_option2; ?>
                      </label>
                      <br><br>
                      <div class="col-sm-12 alert alert-info" style="margin-top: 12px;"><?php echo $info_option; ?></div>
                    </div>
                  </div>

                </div>
              </div>
            </div>

            <div class="panel panel-primary">
              <div class="panel-heading collapsed" id="heading_real_time" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_update" aria-expanded="false" aria-controls="collapse_update">
                <h4 class="panel-title">
                  <i class="text-info fa fa-cog fa-spin fa-3x fa-fw"></i> <?php echo $panel_real_time_setting; ?>
                </h4>
              </div>
              <div id="collapse_update" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_real_time">
                <div class="panel-body">

                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="input-update-imported"><span data-toggle="tooltip" title="<?php echo $help_update_imported; ?>"><?php echo $entry_update_imported; ?></span></label>
                    <div class="col-sm-9">
                      <input type="checkbox"  data-toggle="toggle" data-width="75"  <?php if (isset($wk_amazon_connector_import_update) && $wk_amazon_connector_import_update === 'on') { echo "checked"; } ?>  data-onstyle="success" data-offstyle="danger" name="wk_amazon_connector_import_update">
                      <div class="text-warning"><i><?php echo $info_update_imported; ?></i></div>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="input-update-exported"><span data-toggle="tooltip" title="<?php echo $help_update_exported; ?>"><?php echo $entry_update_exported; ?></span></label>
                    <div class="col-sm-9 text-left">
                        <input type="checkbox"  data-toggle="toggle" data-width="75"  <?php if (isset($wk_amazon_connector_export_update) && $wk_amazon_connector_export_update === 'on') { echo "checked"; } ?>  data-onstyle="success" data-offstyle="danger" name="wk_amazon_connector_export_update">
                        <div class="text-warning"><i><?php echo $info_update_exported; ?></i></div>

                    </div>
                  </div>
                </div>
              </div>
            </div>

           <!--  Price rule tab starts-->
            <div class="panel panel-primary">
              <div class="panel-heading collapsed" id="heading_price_rules" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_price_rules" aria-expanded="false" aria-controls="collapse_update">
                <h4 class="panel-title">
                  <i class="text-info fa fa-money fa-fw"></i> <?php echo $panel_price_rules; ?>
                </h4>
              </div>
            <div id="collapse_price_rules" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_price_rules">
              <div class="panel-body">
                <div class="form-group">
                  <label class="col-sm-3 control-label" for="input-update-imported"><span data-toggle="tooltip" title="<?php echo $help_price_rules; ?>"><?php echo $entry_price_rules; ?></span></label>
                  <div class="col-sm-9">
                    <input type="checkbox"  data-toggle="toggle" data-width="75"  <?php if (isset($wk_amazon_connector_price_rules) && $wk_amazon_connector_price_rules === 'on') { echo "checked"; } ?>  data-on="Export" data-off="Import" data-onstyle="success" data-offstyle="danger" name="wk_amazon_connector_price_rules">
                    <div class="text-warning"><i><?php echo $info_price_rules; ?></i></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label" for="input-update-imported"><span data-toggle="tooltip" title="<?php echo $info_import_quantity_rule; ?>"><?php echo $entry_import_quantity_rule; ?></span></label>
                  <div class="col-sm-9">
                    <input type="checkbox"  data-toggle="toggle" data-width="75"  <?php if (isset($wk_amazon_connector_import_quantity_rule) && $wk_amazon_connector_import_quantity_rule === 'on') { echo "checked"; } ?>  data-on="Enable" data-off="Disable" data-onstyle="success" data-offstyle="danger" name="wk_amazon_connector_import_quantity_rule">
                    <div class="text-warning"><i><?php echo $info_import_quantity_rule; ?></i></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label" for="input-update-imported"><span data-toggle="tooltip" title="<?php echo $info_export_quantity_rule; ?>"><?php echo $entry_export_quantity_rule; ?></span></label>
                  <div class="col-sm-9">
                    <input type="checkbox"  data-toggle="toggle" data-width="75"  <?php if (isset($wk_amazon_connector_export_quantity_rule) && $wk_amazon_connector_export_quantity_rule === 'on') { echo "checked"; } ?>  data-on="Enable" data-off="Disable" data-onstyle="success" data-offstyle="danger" name="wk_amazon_connector_export_quantity_rule">
                    <div class="text-warning"><i><?php echo $info_export_quantity_rule; ?></i></div>
                  </div>
                </div>


              </div>
            </div>
          </div>
         <!--  Price rule tab ends-->

          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="block_div">
    <div class="block_spinner">
      <div class="cp-spinner cp-balls"></div>
    </div>
  </div>
</div>
<?php echo $footer; ?>
<script>
$(document).on('keypress', 'input[name=wk_amazon_connector_default_quantity], input[name=wk_amazon_connector_default_weight]', function(event){
  var keycode = event.which;
  if (!(keycode == 8 || keycode == 9 || keycode == 46 || keycode == 116) && (keycode < 48 || keycode > 57)) {
    return false;
  } else {
    return true;
  }
});

</script>
