<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
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
    z-index: 99999;
    display: none;
  }
  .block_spinner {
    left: 50%;
    position: relative;
    top: 35%;
  }
  .tabs-left > .li-format{
    margin:12px 0;
    margin-right: -18px;
    border-left: 3px solid #1978ab;
    float: none;
  }
  .tabs-left > .li-format > a{
    border-radius: 0;
    border-top: 1px solid #e8e8e8;
    border-bottom: 1px solid #e8e8e8;
  }
  .tabs-left > li.active{
    border-left: 3px solid #E22C5C;
  }
    .nav-tabs > li.active > a, .nav-tabs > li.active > a:hover, .nav-tabs > li.active > a:focus{
    border-bottom: 1px solid #e8e8e8;
    border-right: none;
  }
</style>
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-amazon-account" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo $button_save; ?></button>

        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
      </div>
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
    <?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $heading_title; ?></h3>
      </div>
      <div class="panel-body">
          <div class="col-sm-3" id="amazon_left_link">
              <div class="panel-group panel-primary" id="accordion_amazon" role="tablist" aria-multiselectable="true">
              <div class="panel">
                <div class="panel-heading" role="tab" id="headingOne" >
                  <h4 class="panel-title">
                    <center><b><a role="button" data-toggle="collapse" data-parent="#accordion_amazon" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne" onclick="$('.map-product-class').toggleClass('col-md-12');">
                      <?php echo strtoupper($entry_amazon_account_info); ?>
                    </a></b></center>
                  </h4>
                </div>
                <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                  <div class="panel-body">
                  <!-- Nav tabs -->
                  <ul class="nav nav-tabs tabs-left"><!-- 'tabs-right' for right tabs -->
                    <li class="active li-format"><a href="#add-amazon-account" data-toggle="tab"><?php echo $heading_title; ?></a></li>
                    <?php if(isset($account_id) && $account_id){ ?>
                      <li class="li-format" ><a href="#account_product_map" data-toggle="tab"><?php echo $text_product_tab; ?></a></li>
                      <li class="li-format"><a href="#account_order_map" data-toggle="tab"><?php echo $text_order_tab; ?></a></li>
                      <li class="li-format"><a href="#account_customer_map" data-toggle="tab"><?php echo $text_customer_tab; ?></a></li>
                      <li class="li-format"><a href="#account_export_product_map" data-toggle="tab"><?php echo $text_export_product_tab; ?></a></li>

                      <li class="li-format"><a href="#account_product_review" data-toggle="tab"><?php echo $text_product_review; ?></a></li>
                    <?php } ?>
                  </ul>
                  </div>
                </div>
              </div>
            </div>
          </div><!--Col-sm-3-->

          <div  class="col-sm-9 map-product-class">
            <!-- Tab panes -->
              <div class="tab-content" id="amazon_right_link">
                <div class="tab-pane active" id="add-amazon-account">
                  <h3><?php echo $text_amazon_account; ?> </h3>
                  <hr>

                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-amazon-account" class="form-horizontal">

                    <input type="hidden" name="account_id" value="<?php echo $account_id; ?>" />

                    <div class="form-group required">
                      <label class="col-sm-3 control-label" for="input-store-name"><span data-toggle="tooltip" title="<?php echo $help_amazon_store_name; ?>"><?php echo $entry_amazon_store_name; ?></span></label>
                      <div class="col-sm-9">
                        <input type="text" name="wk_amazon_connector_store_name" class="form-control" id="input-store-name" value="<?php if(isset($wk_amazon_connector_store_name) && $wk_amazon_connector_store_name) { echo $wk_amazon_connector_store_name; } ?>" <?php if(isset($account_id) && $account_id && !$error_wk_amazon_connector_store_name){ echo 'readonly = 1'; } ?> />

                        <?php if($error_wk_amazon_connector_store_name){ ?>
                          <div class="text-danger"><?php echo $error_wk_amazon_connector_store_name; ?></div>
                        <?php } ?>
                      </div>
                    </div>

                    <div class="form-group required">
                      <label class="col-sm-3 control-label" for="input-attribute-group"><span data-toggle="tooltip" title="<?php echo $help_amazon_attribute_group; ?>"><?php echo $entry_amazon_attribute_group; ?></span></label>
                      <div class="col-sm-9">
                        <select name="wk_amazon_connector_attribute_group" id="input-attribute-group" class="form-control">
                          <option value=""><?php echo $text_select; ?></option>
                          <?php foreach ($attribute_groups as $key => $attribute_group) { ?>
                            <option value="<?php echo $attribute_group['attribute_group_id']; ?>" <?php if(isset($wk_amazon_connector_attribute_group) && $wk_amazon_connector_attribute_group == $attribute_group['attribute_group_id']){ echo 'selected'; } ?>><?php echo $attribute_group['name']; ?></option>
                          <?php } ?>
                        </select>
                        <?php if($error_wk_amazon_connector_attribute_group){ ?>
                          <div class="text-danger"><?php echo $error_wk_amazon_connector_attribute_group; ?></div>
                        <?php } ?>
                      </div>
                    </div>

                    <div class="form-group required">
                      <label class="col-sm-3 control-label" for="input-marketplace-id"><span data-toggle="tooltip" title="<?php echo $help_amazon_marketplace_id; ?>"><?php echo $entry_amazon_marketplace_id; ?></span></label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" name="wk_amazon_connector_marketplace_id" id="input-marketplace-id" value="<?php if(isset($wk_amazon_connector_marketplace_id)){ echo $wk_amazon_connector_marketplace_id; } ?>" placeholder="<?php echo $placeholder_marketplace_id; ?>" />
                        <?php if($error_wk_amazon_connector_marketplace_id){ ?>
                          <div class="text-danger"><?php echo $error_wk_amazon_connector_marketplace_id; ?></div>
                        <?php } ?>
                      </div>
                    </div>

                    <div class="form-group required">
                      <label class="col-sm-3 control-label" for="input-seller-id"><span data-toggle="tooltip" title="<?php echo $help_amazon_seller_id; ?>"><?php echo $entry_amazon_seller_id; ?></span></label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" name="wk_amazon_connector_seller_id" id="input-seller-id" value="<?php if(isset($wk_amazon_connector_seller_id)){ echo $wk_amazon_connector_seller_id; } ?>" placeholder="<?php echo $placeholder_seller_id; ?>" />
                        <?php if($error_wk_amazon_connector_seller_id){ ?>
                          <div class="text-danger"><?php echo $error_wk_amazon_connector_seller_id; ?></div>
                        <?php } ?>
                      </div>
                    </div>

                    <div class="form-group required">
                      <label class="col-sm-3 control-label" for="input-access-key"><span data-toggle="tooltip" title="<?php echo $help_amazon_access_id; ?>"><?php echo $entry_amazon_access_id; ?></span></label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" name="wk_amazon_connector_access_key_id" id="input-access-key" value="<?php if(isset($wk_amazon_connector_access_key_id)){ echo $wk_amazon_connector_access_key_id; } ?>" placeholder="<?php echo $placeholder_access_key_id; ?>" />
                        <?php if($error_wk_amazon_connector_access_key_id){ ?>
                          <div class="text-danger"><?php echo $error_wk_amazon_connector_access_key_id; ?></div>
                        <?php } ?>
                      </div>
                    </div>


                    <div class="form-group required">
                      <label class="col-sm-3 control-label" for="input-secret-key"><span data-toggle="tooltip" title="<?php echo $help_amazon_secret_key; ?>"><?php echo $entry_amazon_secret_key; ?></span></label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" name="wk_amazon_connector_secret_key" id="input-secret-key" value="<?php if(isset($wk_amazon_connector_secret_key)){ echo $wk_amazon_connector_secret_key; } ?>" placeholder="<?php echo $placeholder_secret_key; ?>" />
                        <?php if($error_wk_amazon_connector_secret_key){ ?>
                          <div class="text-danger"><?php echo $error_wk_amazon_connector_secret_key; ?></div>
                        <?php } ?>
                      </div>
                    </div>
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

                    <div class="form-group required">
                      <label class="col-sm-3 control-label" for="input-country"><span data-toggle="tooltip" title="<?php echo $help_amazon_country; ?>"><?php echo $entry_amazon_country; ?></span></label>
                      <div class="col-sm-9">
                        <select name="wk_amazon_connector_country" id="input-country" class="form-control">
                          <?php foreach ($countries as $key => $country) { ?>
                            <option value="<?php echo $country['iso_code_2']; ?>" <?php if(isset($wk_amazon_connector_country) && $wk_amazon_connector_country == $country['iso_code_2']){ echo 'selected'; } ?>><?php echo $country['name']; ?></option>
                          <?php } ?>
                        </select>
                        <?php if($error_wk_amazon_connector_country){ ?>
                          <div class="text-danger"><?php echo $error_wk_amazon_connector_country; ?></div>
                        <?php } ?>
                      </div>
                    </div>

                    <div class="form-group required">
                      <label class="col-sm-3 control-label" for="input-currency-rate"><span data-toggle="tooltip" title="<?php echo $help_amazon_currency_rate; ?>"><?php echo $entry_amazon_currency_rate; ?></span></label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" name="wk_amazon_connector_currency_rate" id="input-currency-rate" value="<?php if(isset($wk_amazon_connector_currency_rate)){ echo $wk_amazon_connector_currency_rate; } ?>" placeholder="<?php echo $placeholder_currency_rate; ?>" />
                        <div class="alert alert-info"><?php echo $help_amazon_currency_rate; ?></div>
                        <?php if($error_wk_amazon_connector_currency_rate){ ?>
                          <div class="text-danger"><?php echo $error_wk_amazon_connector_currency_rate; ?></div>
                        <?php } ?>
                      </div>
                    </div>

                  </form>
                </div><!--add-amazon-account-->

                <div class="tab-pane" id="account_product_map">
                  <?php echo $product_map; ?>
                </div><!--account_product_map-->

                <div class="tab-pane" id="account_order_map">
                  <?php echo $order_map; ?>
                </div><!--account_order_map-->

                <div class="tab-pane" id="account_customer_map">
                  <?php echo $customer_map; ?>
                </div><!--account_customer_map-->

                <div class="tab-pane" id="account_export_product_map">
                  <?php echo $export_product; ?>
                </div><!--account_export_product_map-->
                <div class="tab-pane" id="account_product_review">
                  <?php echo $product_review; ?>
                </div>

          <!--tab-content-col-sm-9-->
        <!--Col-sm-9-->


      </div>
    </div>
  </div>
  <div class="block_div">
    <div class="block_spinner">
      <div class="cp-spinner cp-flip"></div>
    </div>
  </div>
</div>

<?php echo $footer; ?>
<script>$(document).on('keypress', 'input[name=wk_amazon_connector_currency_rate]', function(event){
  var keycode = event.which;
  if (!(keycode == 8 || keycode == 9 || keycode == 46 || keycode == 116) && (keycode < 48 || keycode > 57)) {
    return false;
  } else {
    return true;
  }

});
</script>
