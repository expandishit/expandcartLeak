<div id="content">
    <link href="view/stylesheet/csspin.css" rel="stylesheet" type="text/css"/>
    <style type="text/css">
      .alert-success{
        color: green;
      }
      .cp-round::before, .cp-round::after{
        width: 35px;
        left:8px;
        height: 35px;
        margin-top: 25px;
      }
      .btn-success{
        background-color:#6ABD6A;
        color:#FFF;
        border-style: solid;
        border-width: 1px;
        border-color: #6ABD6A;
        border-bottom-width: 3px;
      }
      .btn-success:hover{
        background-color:#e6e6e6;
        color:#333;
        border-style: solid;
        border-width: 1px;
        border-color: #adadad;
        border-bottom-width: 3px;
      }
      .order_import_section, #profiler_order, #amazonOrderList, #start_import_all_order, .result_report, #createOrderProcessBar{
        display: none;
      }
    </style>
      <div class="page-header">
        <div class="container-fluid">
          <?php if(isset($panel) && $panel == 'import_order'){ ?>
            <h3><?php echo $heading_title_import; ?></h3>
          <?php }else{ ?>
              <h3><?php echo $heading_title; ?></h3>
          <?php } ?>
          <hr style="margin-bottom:0px;">
        </div>
      </div>


      <div class="page-header container-fluid">
        <div class="pull-right" style="margin-bottom: 10px;">
          <?php if(!$panel){ ?>
            <a href="<?php echo $import_order_tab; ?>" id="import-order-tab" type="button" data-toggle="tooltip" title="<?php echo $button_import_order; ?>" class="btn btn-info" ><i class="fa fa-download col-sm-12" aria-hidden="true"></i> <span class="col-sm-12"><?php echo $button_import; ?></span></a>
            <button class="btn btn-danger" type="button" data-toggle="tooltip" title="<?php echo $button_delete_info; ?>" onclick="confirm('<?php echo $text_confirm; ?>') ? $('#form-order-delete').submit() : false;"><i class="fa fa-trash-o col-sm-12" aria-hidden="true"></i> <span class="col-sm-12"><?php echo $button_delete; ?></span></button>
            <?php if(!empty($order_delete_result)){ ?>
                <button type="button" class="btn btn-success" data-toggle="modal" data-target=".order_delete_result" id="order_delete"><i class="fa fa-info col-sm-12" aria-hidden="true"></i>  <span class="col-sm-12"><?php echo "Result"; ?></span></button>
            <?php } ?>
          <?php }else{ ?>
            <a href="<?php echo $button_back_link; ?>" type="button" data-toggle="tooltip" title="<?php echo $button_back; ?>" class="btn btn-default" ><i class="fa fa-reply" aria-hidden="true"></i></a>
          <?php } ?>
        </div>
      </div>

      <div class="modal fade order_delete_result" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="gridSystemModalLabel"><strong><?php echo "Order Result"; ?></strong></h4>
            </div>
            <div class="modal-body">
              <?php if(isset($order_delete_result) && $order_delete_result) { ?>
              <?php foreach($order_delete_result as $p_key => $result){ ?>
                <?php if(isset($result['status']) && $result['status']){ ?>
                  <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $result['message']; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                  </div>
                <?php }else{ ?>
                  <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $result['message']; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                  </div>
                <?php } ?>
              <?php } ?>
              <?php } ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $button_close; ?></button>
            </div>
          </div>
        </div>
      </div>

      <div class="container-fluid">

        <?php if(isset($panel) && $panel == 'import_order'){ ?>
          <div id="order_import_list_section">
            <?php if ($error_warning) { ?>
            <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
              <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php } ?>

            <div class="panel panel-primary">
              <div class="panel-heading"  style="display:inline-block;width:100%;">
                <h3 class="panel-title"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php echo $info_about_import_tab; ?></h3>
              </div>
              <div class="panel-body">
                <ul>
                  <li> <?php echo $text_tab_info1; ?></li>
                  <li> <?php echo $text_tab_info2; ?></li>
                  <li> <?php echo $text_tab_info3; ?></li>
                </ul>
              </div>
            </div>

            <div class="panel panel-default">
              <div class="panel-heading" style="display:inline-block;width:100%;">
                <h3 class="panel-title"><i class="fa fa-refresh" aria-hidden="true"></i> <?php echo $sync_order_tab; ?></h3>
              </div>
              <div class="panel-body" id="generate-report-order">
                <div class="alert alert-warning"> <i class="fa fa-info-circle" aria-hidden="true"></i> <?php echo $info_report_id; ?></div>

                <div class="panel-body">
                  <button type="button" class="btn btn-info" id="generate_order_report_id"><i class="fa fa-plus-circle" aria-hidden="true"></i> <?php echo $button_report_id; ?></button>

                  <div class="row form-group order_import_section">

                    <div class="col-sm-12 form-horizontal text-right" id="createOrderProcessBar">
                     <div class="col-sm-12 form-group">
                       <label class="col-sm-2 control-label"><?php echo "Processing..."; ?></label>
                       <div class="col-sm-10" style="margin-top:10px">
                         <div class="progress">
                           <div id="progress-bar-createorder" class="progress-bar" style="width: 0%;"></div>
                         </div>
                         <div id="progress-text-createorder"></div>
                       </div>
                     </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6" style="margin-top:5px;">
                      <button type="button" class="btn btn-warning" id="import_amazon_order" data-toggle="modal" data-target=".order_details_model"><i class="fa fa-refresh" aria-hidden="true"></i>  <?php echo $button_import_order; ?></button>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6" style="margin-top:5px;">
                      <button type="button" class="btn btn-warning" id="create_amazon_order" data-total="<?php echo count($total_imported_order); ?>"><i class="fa fa-refresh" aria-hidden="true"></i>  <?php echo $button_create_order; ?></button>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6" style="margin-top:5px;">
                      <button type="button" class="btn btn-warning" data-toggle="modal" data-target=".order_id_model" id="import_order_one_by_one"><i class="fa fa-check-square-o" aria-hidden="true"></i>  <?php echo $button_import_order_by_id; ?></button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade order_details_model" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="gridSystemModalLabel"><strong><?php echo $entry_order_details; ?></strong></h4>
                  </div>
                  <div class="modal-body">
                    <div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <?php echo $error_not_referesh; ?></div>
                    <form id="importOrderList" class="form-horizontal" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="next_token"  value="" />
                        <div class="form-group required" style="margin-left: 0px;margin-right: 0px;">
                          <label class="control-label" style="margin-bottom: 10px;"><?php echo $entry_order_from; ?></label>
                          <div class="col-sm-12">
                            <div class="input-group date">
                              <input type="text" name="amazon_order_from" value="" placeholder="<?php echo $placeholder_order_from; ?>" class="form-control" data-date-format="YYYY-MM-DD" />
                              <span class="input-group-btn">
                              <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>
                              </span>
                            </div>
                          </div>
                        </div>

                        <div class="form-group required" style="margin-left: 0px;margin-right: 0px;">
                          <label class="control-label" style="margin-bottom: 10px;"><?php echo $entry_order_to; ?></label>
                          <div class="col-sm-12">
                            <div class="input-group date">
                              <input type="text" name="amazon_order_to" value="" placeholder="<?php echo $placeholder_order_to; ?>" class="form-control" data-date-format="YYYY-MM-DD" />
                              <span class="input-group-btn">
                              <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>
                              </span>
                            </div>
                          </div>
                        </div>
                    </form>

                    <div class="result_report" style="height:350px;overflow-x:auto;"></div>
                  </div>
                  <div class="modal-footer">
                    <span class="demo-spin" style="color: #1e91cf;position: relative;top: 6px;"><i class="fa  fa-spin fa-2x fa-fw"></i></span>
                    <button type="button" id="import_all_order" class="btn btn-primary"><?php echo $button_import; ?></button>
                    <button type="button" id="start_import_all_order" class="btn btn-primary"><?php echo $button_import; ?></button>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $button_close; ?></button>
                  </div>

                </div>
              </div>
            </div>

            <div class="modal fade order_id_model" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="gridSystemModalLabel"><strong><?php echo $heading_import_order; ?></strong></h4>
                  </div>
                  <div class="modal-body">

                  <form id="amazonOrderOneByOne" class="form-horizontal" method="post" enctype="multipart/form-data">

                    <div class="form-horizontal">
                       <div class="row form-group">
                         <label class="col-sm-2 control-label"><?php echo "Processing..."; ?></label>
                         <div class="col-sm-10" style="margin-top:10px">
                           <div class="progress">
                             <div id="progress-bar-importorder" class="progress-bar" style="width: 0%;"></div>
                           </div>
                           <div id="progress-text-importorder"></div>
                         </div>
                       </div>

                      <div class="form-group required">
                        <label class="control-label" style="margin-bottom: 10px;"><?php echo $text_order_id; ?></label>
                        <div class="col-sm-12">
                            <input type="text" name="selected[]" value="" placeholder="<?php echo $entry_order_id; ?>" class="form-control" />
                        </div>
                      </div>
                    </div>
                  </form>
                  </div>
                  <div class="modal-footer">
                    <span class="demo-spin" style="color: #1e91cf;position: relative;top: 6px;"><i class="fa  fa-spin fa-2x fa-fw"></i></span>
                    <button type="button" id="import_by_orderId" class="btn btn-primary"><?php echo $button_import; ?></button>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $button_close; ?></button>
                  </div>

                </div>
              </div>
            </div>

            <div class="panel panel-default" id="profiler_order">
              <div class="panel-heading"  style="display:inline-block;width:100%;">
                <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $entry_order_response; ?></h3>
              </div>
              <div class="panel-body profiler_order_body">
                <div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <?php echo $error_not_referesh; ?></div>

              </div>
            </div>

          </div><!-- product-import -->
        <?php } ?>

        <?php if(!$panel){ ?>
          <div class="panel panel-default" id="imported_order_list">
            <div class="panel-heading"  style="display:inline-block;width:100%;">
              <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_order_list; ?></h3>
            </div>
            <div class="panel-body">

              <div class="well">
                <div class="row">
                  <div class="col-sm-4">
                    <div class="form-group">
                      <label class="control-label" for="input-oc-order-id"><?php echo $column_oc_order_id; ?></label>
                        <input type="text" name="filter_oc_order_id" value="<?php echo $filter_oc_order_id; ?>" placeholder="<?php echo $column_oc_order_id; ?>" id="input-oc-order-id" class="form-control"/>
                    </div>
                    <div class="form-group">
                      <label class="control-label" for="input-oc-buyer-email"><?php echo $column_buyer_email; ?></label>
                      <div class='input-group'>
                        <input type="text" name="filter_buyer_email" value="<?php echo $filter_buyer_email; ?>" placeholder="<?php echo $column_buyer_email; ?>" id="input-oc-buyer-email" class="form-control"/>
                        <span class="input-group-addon">
                          <span class="fa fa-angle-double-down"></span>
                        </span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label" for="input-date-added"><?php echo $column_created_date; ?></label>
                      <div class="input-group datetime">
                        <input type="text" name="filter_date_added" value="<?php echo $filter_date_added; ?>" data-date-format="YYYY-MM-DD HH:mm:ss" placeholder="<?php echo $column_created_date; ?>" id="input-date-added" class="form-control"/>
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>
                        </span>
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-4">
                      <div class="form-group">
                        <label class="control-label" for="input-ebay-order-id"><?php echo $column_amazon_id; ?></label>
                          <input type="text" name="filter_amazon_order_id" value="<?php echo $filter_amazon_order_id; ?>" placeholder="<?php echo $column_amazon_id; ?>" id="input-ebay-order-id" class="form-control"/>
                      </div>

                      <div class="form-group">
                        <label class="control-label" for="input-oc-order-total"><?php echo $column_order_total; ?></label>
                        <input type="text" name="filter_order_total" value="<?php echo $filter_order_total; ?>" placeholder="<?php echo $column_order_total; ?>" id="input-oc-order-total" class="form-control"/>
                      </div>

                  </div>

                  <div class="col-sm-4">
                    <div class="form-group">
                      <label class="control-label" for="input-oc-buyer-name"><?php echo $column_buyer_name; ?></label>
                      <div class='input-group'>
                        <input type="text" name="filter_buyer_name" value="<?php echo $filter_buyer_name; ?>" placeholder="<?php echo $column_buyer_name; ?>" id="input-oc-buyer-name" class="form-control"/>
                        <span class="input-group-addon">
                          <span class="fa fa-angle-double-down"></span>
                        </span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label" for="input-order-status"><?php echo $column_amazon_order_status; ?></label>
                        <input type="text" name="filter_order_status" value="<?php echo $filter_order_status; ?>" placeholder="<?php echo $column_amazon_order_status; ?>" id="input-order-status" class="form-control"/>
                    </div>
                  </div>

                  <div class="col-sm-6" style="margin-top:38px;">
                    <button type="button" onclick="filter_map_order();" class="btn btn-primary" style="border-radius: 4px;  ">
                      <i class="fa fa-search"></i><?php echo $button_filter_order; ?></button>
                    <a href="<?php echo $clear_order_filter; ?>" class="btn btn-default" style="border-radius: 4px; "><i class="fa fa-eraser" aria-hidden="true"></i><?php echo $button_clear_order; ?></a>
                  </div>
                </div>
              </div>
              <form action="<?php echo $action_delete; ?>" method="post" enctype="multipart/form-data" id="form-order-delete">
                <div class="table-responsive">
                  <table class="table table-bordered table-hover">
                    <thead>
                      <tr>
                        <td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></td>
                        <td class="text-left"><?php if ($sort == 'apm.id') { ?>
                          <a href="<?php echo $sort_map_id; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_map_id; ?></a>
                          <?php } else { ?>
                          <a href="<?php echo $sort_map_id; ?>"><?php echo $column_map_id; ?></a>
                          <?php } ?></td>
                        <td class="text-left"><?php if ($sort == 'apm.oc_product_id') { ?>
                            <a href="<?php echo $sort_oc_product_id; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_oc_order_id; ?></a>
                            <?php } else { ?>
                            <a href="<?php echo $sort_oc_product_id; ?>"><?php echo $column_oc_order_id; ?></a>
                            <?php } ?></td>
                        <td class="text-left"><?php if ($sort == 'product_name') { ?>
                            <a href="<?php echo $sort_oc_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_amazon_id; ?></a>
                            <?php } else { ?>
                            <a href="<?php echo $sort_oc_name; ?>"><?php echo $column_amazon_id; ?></a>
                            <?php } ?></td>
                        <td class="text-left"><?php echo $column_buyer_name; ?></td>
                        <td class="text-left"><?php echo $column_buyer_email; ?></td>
                        <td class="text-left"><?php if ($sort == 'p.quantity') { ?>
                            <a href="<?php echo $sort_oc_quantity; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_amazon_order_status; ?></a>
                            <?php } else { ?>
                            <a href="<?php echo $sort_oc_quantity; ?>"><?php echo $column_amazon_order_status; ?></a>
                            <?php } ?></td>
                        <td class="text-left"><?php if ($sort == 'p.price') { ?>
                            <a href="<?php echo $sort_oc_price; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_order_total; ?></a>
                            <?php } else { ?>
                            <a href="<?php echo $sort_oc_price; ?>"><?php echo $column_order_total; ?></a>
                            <?php } ?></td>
                        <td class="text-left"><?php echo $column_action; ?></td>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (isset($import_orders)) { ?>
                      <?php foreach ($import_orders as $amazon_order) { ?>
                      <tr>
                        <td class="text-center"><?php if (in_array($amazon_order['map_id'], $selected)) { ?>
                          <input type="checkbox" name="selected[]" value="<?php echo $amazon_order['map_id']; ?>" checked="checked" />
                          <?php } else { ?>
                          <input type="checkbox" name="selected[]" value="<?php echo $amazon_order['map_id']; ?>" />
                          <?php } ?></td>

                        <td class="text-left"><?php echo $amazon_order['map_id']; ?></td>
                        <td class="text-left"><?php echo $amazon_order['oc_order_id']; ?></td>
                        <td class="text-left"><?php echo $amazon_order['amazon_order_id']; ?></td>
                        <td class="text-left"><?php echo $amazon_order['customer_name']; ?></td>
                        <td class="text-left"><?php echo $amazon_order['customer_email']; ?></td>
                        <td class="text-left">
                          <button class="btn btn-success" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            <?php echo $amazon_order['amazon_order_status']; ?>
                          </button>
                          </td>
                        <td class="text-left"><?php echo $amazon_order['total']; ?></td>
                        <td class="text-left">
                          <a href="<?php echo $amazon_order['view']; ?>" target="_blank" class="btn btn-warning" data-toggle="tooltip" title="<?php echo $button_view_info; ?>" >
                            <i class="fa fa-eye" aria-hidden="true"></i>  <?php echo $button_view; ?>
                          </a>
                        </td>
                      </tr>
                      <?php } ?>
                      <?php } else { ?>
                      <tr>
                        <td class="text-center" colspan="9"><?php echo $text_no_results; ?></td>
                      </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
              </form>
              <div class="row" style="margin-top:10px;">
                <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
                <div class="col-sm-6 text-right"><?php echo $results; ?></div>
              </div>
            </div>
          </div><!--imported_list-->
        <?php } ?>

      </div><!--container-fluid-->
  </div><!--content-->

<script type="text/javascript"><!--
  // Order filter section
  function filter_map_order() {
  	url = 'index.php?route=amazon_map/account/edit&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>&status=account_order_map';

    var filter_oc_order_id = $('input[name=\'filter_oc_order_id\']').val();

    if (filter_oc_order_id) {
      url += '&filter_oc_order_id=' + encodeURIComponent(filter_oc_order_id);
    }

    var filter_amazon_order_id = $('input[name=\'filter_amazon_order_id\']').val();

    if (filter_amazon_order_id) {
      url += '&filter_amazon_order_id=' + encodeURIComponent(filter_amazon_order_id);
    }

    var filter_buyer_name = $('input[name=\'filter_buyer_name\']').val();

    if (filter_buyer_name) {
      url += '&filter_buyer_name=' + encodeURIComponent(filter_buyer_name);
    }

    var filter_buyer_email = $('input[name=\'filter_buyer_email\']').val();

    if (filter_buyer_email) {
      url += '&filter_buyer_email=' + encodeURIComponent(filter_buyer_email);
    }
    var filter_order_total = $('input[name=\'filter_order_total\']').val();

    if (filter_order_total) {
      url += '&filter_order_total=' + encodeURIComponent(filter_order_total);
    }

  	var filter_date_added = $('input[name=\'filter_date_added\']').val();

  	if (filter_date_added) {
  		url += '&filter_date_added=' + encodeURIComponent(filter_date_added);
  	}

    var filter_order_status = $('input[name=\'filter_order_status\']').val();

    if (filter_order_status) {
      url += '&filter_order_status=' + encodeURIComponent(filter_order_status);
    }
  	location = url;
  }

  $('input[name=\'filter_buyer_name\']').autocomplete({
    delay: 0,
    source: function(request, response) {
      $.ajax({
        url: 'index.php?route=amazon_map/order/autocomplete&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>&filter_buyer_name=' +  encodeURIComponent(request),
        dataType: 'json',
        success: function(json) {
          response($.map(json, function(item) {
            return {
              label: item.name,
              value: item.item_id
            }
          }));
        }
      });
    },
    select: function(item) {
      $('input[name=\'filter_buyer_name\']').val(item.label);
      return false;
    },
    focus: function(item) {
        return false;
    }
  });

  $('input[name=\'filter_buyer_email\']').autocomplete({
    delay: 0,
    source: function(request, response) {
      $.ajax({
        url: 'index.php?route=amazon_map/order/autocomplete&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>&filter_buyer_email=' +  encodeURIComponent(request),
        dataType: 'json',
        success: function(json) {
          response($.map(json, function(item) {
            return {
              label: item.name,
              value: item.item_id
            }
          }));
        }
      });
    },
    select: function(item) {
      $('input[name=\'filter_buyer_email\']').val(item.label);
      return false;
    },
    focus: function(item) {
        return false;
    }
  });

  $('.datetime').datetimepicker({
    pickDate: true,
    pickTime: true
  });
//--></script>

<script type="text/javascript"><!--
    // Order Generate Report Id section
    $('#generate_order_report_id').on('click',function(){
        $.ajax({
          url     : 'index.php?route=amazon_map/product/generate_report_id&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>&status=order',
          dataType:'json',
          type    : 'POST',
          cache   : false,
          beforeSend: function() {
            $('.block_div').css('display','block');
          },
          complete: function() {
            $('.block_div').css('display','none');
          },
          success: function(json) {
            if (json['error']) {
              html = '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>  '+ json['error']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
              $('#generate-report-order').prepend(html);
            }
            if (json['success']){
              $('#generate-report-order .alert').remove();
              html = '<div class="alert alert-success"><i class="fa fa-exclamation-circle"></i>  '+ json['success']['message']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
              $('#generate-report-order').prepend(html);
              $('.order_import_section').find('input').attr('value', json['success']['report_id']);
              $('#generate_order_report_id').css('display','none');
              $('.order_import_section').css('display','block');
            }
          },
          error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
          }
        });
    });

    $('.date').datetimepicker({
  		pickTime: false
  	});
//--></script>

<script type="text/javascript"><!--
  // Import all amazon orders and save to opencart table
  var countSavedOrder = 0;
  $("#import_all_order").on("click", function(){
    countSavedOrder = 0;
    $("#importOrderList").find('.text-danger').remove();
    $("#importOrderList").find('.alert-success, .alert-danger').remove();
    var args        = [];
    var getDateFrom = $('input[name="amazon_order_from"]').val();
    var getDateTo   = $('input[name="amazon_order_to"]').val();
        args['order_from']  = getDateFrom;
        args['order_to']    = getDateTo;
      if(getDateFrom.length == 0){
        $('input[name="amazon_order_from"]').parent().parent().append('<div class="text-danger"><?php echo $error_fill_from_date; ?></div>');
      }
      if(getDateTo.length == 0){
        $('input[name="amazon_order_to"]').parent().parent().append('<div class="text-danger"><?php echo $error_fill_from_to; ?></div>');
      }
      if(getDateFrom.length != 0 && getDateTo.length != 0){
        jQuery.ajax({
            url: 'index.php?route=amazon_map/order/getOrderList&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>',
            data: {
                'amazon_order_from' : getDateFrom,
                'amazon_order_to'   : getDateTo,
                'next_token'        : ''
            },
            dataType:'json',
            type:'POST',
            beforeSend: function() {
              $('#profiler_order .profiler_order_body, .result_report').find('.alert-success, .alert-danger').remove();
              $(".demo-spin > .fa").addClass('fa-cog');
            },
            success: function(json) {
              if (json.warning) {
                  if(json.warning['error_date_from']){
                      $('#importOrderList').find('input[name="amazon_order_from"]').parent().parent().append('<div class="text-danger"> '+json.warning['error_date_from']+'</div>');
                  }
                  if(json.warning['error_date_to']){
                      $('#importOrderList').find('input[name="amazon_order_to"]').parent().parent().append('<div class="text-danger"> '+json.warning['error_date_to']+'</div>');
                  }
                  if(json.warning['error']){
                      $('#importOrderList').append('<div class="alert alert-danger"><i class="fa fa-times" aria-hidden="true"></i>   '+json.warning['error']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                  }
                  $(".demo-spin > .fa").removeClass('fa-cog');
              }else{
                  $('#importOrderList').css('display', 'none');
                  $('.result_report').css('display', 'block');
                  if (json.success) {
                      countSavedOrder = countSavedOrder + json.success;
                      $('.result_report').append('<div class="alert alert-success"><i class="fa fa-exclamation-circle" aria-hidden="true"></i>  Success: '+countSavedOrder+' amazon order(s) imported!<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                  }
                  if (json.error) {
                      for (i in json.error) {
                        var html = '';
                          html = '<div class="alert alert-danger"><i class="fa fa-times" aria-hidden="true"></i>   '+json.error[i]['message']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                          $('.result_report').append(html);
                      }
                  }
                  if(json.next_token){
                      nextOrderArray(args, json.next_token, countSavedOrder);
                  }else{
                      $('.result_report').append('<div class="alert alert-success"><i class="fa fa-exclamation-circle" aria-hidden="true"></i>  Success: Total '+countSavedOrder+' amazon order(s) imported in opencart store from amazon, Click on <b>Create Imported Order</b> button to create imported product to opencart store! <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                      $('body .order_import_section').parent().before('<div class="alert alert-success"><i class="fa fa-exclamation-circle" aria-hidden="true"></i>  Success: Total '+countSavedOrder+' amazon order(s) imported in opencart store from amazon, Click on <b>Create Imported Order</b> button to create imported product to opencart store! <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                      $(".demo-spin > .fa").removeClass('fa-cog');
                      $('#create_amazon_order').attr('data-total', json.total_order);
                  }
              }
            },
            error: function(xhr, ajaxOptions, thrownError) {
              alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
      }
  })

  function nextOrderArray(args, nxt_token, countSavedOrder) {
      $.ajax({
          url: 'index.php?route=amazon_map/order/getOrderList&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>',
          data: {
            'amazon_order_from' : args['order_from'],
            'amazon_order_to'   : args['order_to'],
            'next_token'        : nxt_token
          },
          type: 'POST',
          dataType:'JSON',
          success: function (response) {
              if(response.warning){
                  if(response.warning['error']){
                      $('#importOrderList').append('<div class="alert alert-danger"><i class="fa fa-times" aria-hidden="true"></i>   '+response.warning['error']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                  }
                  $('.result_report').append('<div class="alert alert-success"><i class="fa fa-exclamation-circle" aria-hidden="true"></i>  Success: Total '+countSavedOrder+' amazon order(s) imported in opencart store from amazon, Click on <b>Create Imported Order</b> button to create imported product to opencart store! <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

                  $('body .order_import_section').parent().before('<div class="alert alert-success"><i class="fa fa-exclamation-circle" aria-hidden="true"></i>  Success: Total '+countSavedOrder+' amazon order(s) imported in opencart store from amazon, Click on <b>Create Imported Order</b> button to create imported product to opencart store! <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

                  $(".demo-spin > .fa").removeClass('fa-cog');
                  $('#create_amazon_order').attr('data-total', response.total_order);
              }else{
                  if (response.success) {
                      countSavedOrder = countSavedOrder + response.success;
                      $('.result_report').append('<div class="alert alert-success"><i class="fa fa-exclamation-circle" aria-hidden="true"></i>  Success: '+countSavedOrder+' amazon order(s) imported!<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                  }
                  if(response.next_token){
                      if (response.error) {
                          for (i in response.error) {
                            var html = '';
                              html = '<div class="alert alert-danger"><i class="fa fa-times" aria-hidden="true"></i>   '+response.error[i]['message']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                              $('.result_report').append(html);
                          }
                      }
                      nextOrderArray(args, response.next_token, countSavedOrder);
                  }else{
                      $('.result_report').append('<div class="alert alert-success"><i class="fa fa-exclamation-circle" aria-hidden="true"></i>  Success: Total '+countSavedOrder+' amazon order(s) imported in opencart store from amazon, Click on <b>Create Imported Order</b> button to create imported product to opencart store! <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                      $('body .order_import_section').parent().before('<div class="alert alert-success"><i class="fa fa-exclamation-circle" aria-hidden="true"></i>  Success: Total '+countSavedOrder+' amazon order(s) imported in opencart store from amazon, Click on <b>Create Imported Order</b> button to create imported product to opencart store! <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                      $(".demo-spin > .fa").removeClass('fa-cog');
                      $('#create_amazon_order').attr('data-total', response.total_order);
                  }
              }
          },
          error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
          }
      });
  }
//--></script>

<script type="text/javascript"><!--
  // create imported amazon orders to opencart store
  var createRequests    = []; var totalCreatedOrder = 0; var totalImportedOrders = 0;
  var countOrder  = 1;
  $('#create_amazon_order').on('click', function(e){
      e.preventDefault();
      totalCreatedOrder = totalImportedOrders = 0;
      totalImportedOrders = $('#create_amazon_order').attr('data-total');

      $('#createOrderProcessBar').css('display', 'block');
      if (typeof timer != 'undefined') {
          clearInterval(timer);
      }
      timer = setInterval(function() {
              clearInterval(timer);
      // Reset everything
      $('.alert').remove();
      $('#progress-bar-createorder').css('width', '0%');
      $('#progress-bar-createorder').removeClass('progress-bar-danger progress-bar-success');
      $('#progress-text-createorder').html('<div class="text-info text-left"><?php echo "Amazon order(s) create processing..."; ?></div>');
      $.ajax({
          url     : 'index.php?route=amazon_map/order/createOrder&token=<?php echo $token; ?>',
          data    : {
                      'account_id' : '<?php echo $account_id; ?>',
                      'count'      : countOrder,
                    },
          dataType: 'json',
          type    : 'POST',
          beforeSend: function() {
            $('#profiler_order').css('display', 'block');
            $('#profiler_order .profiler_order_body').find('.alert-danger, .alert-success').remove();
            $('.block_div').css('display','block');
            $('.container-fluid > .alert').remove();
          },
          complete:function() {
              NextCreateStep();
          },
          success: function(jsonAmazonOrd) {
                if (jsonAmazonOrd.error_failed) {
                    $('#progress-bar-createorder').addClass('progress-bar-danger');
                    $('#progress-text-createorder').html('<div class="text-danger">' + jsonAmazonOrd.error_failed + '</div>');
                    $('#profiler_order .profiler_order_body').append('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+jsonAmazonOrd.error_failed+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                }else{
                    if(jsonAmazonOrd.error){
                        $('#profiler_order .profiler_order_body').append('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+jsonAmazonOrd.error+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                    }
                    if(jsonAmazonOrd.success){
                        $('#profiler_order .profiler_order_body').append('<div class="alert alert-success"><i class="fa fa-check-circle"></i> '+jsonAmazonOrd.success+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                        totalCreatedOrder = totalCreatedOrder + 1;
                    }
                    for(countOrder = 2; countOrder <= totalImportedOrders; countOrder++) {
                        createRequests.push({
                            url     : 'index.php?route=amazon_map/order/createOrder&token=<?php echo $token; ?>',
                            data    : {
                                      'account_id' : '<?php echo $account_id; ?>',
                                      'count'      : countOrder,
                                    },
                            dataType: 'json',
                            type    : 'POST',
                            success :   function(json_response){
                                if(json_response.error){
                                    $('#profiler_order .profiler_order_body').append('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+json_response.error+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                                }
                                if(json_response.success){
                                    $('#profiler_order .profiler_order_body').append('<div class="alert alert-success"><i class="fa fa-check-circle"></i> '+json_response.success+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                                    totalCreatedOrder = totalCreatedOrder + 1;
                                }
                            },
                        });
                    }
                }
              },
              error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
              }
          });
      }, 500);
  })

  var NextCreateStep = function(){
      if (createRequests.length) {
          $('#progress-bar-createorder').css('width', (100 - (createRequests.length / totalImportedOrders) * 100) + '%');
          $.ajax(createRequests.shift()).then(NextCreateStep);
      } else {
          $('#progress-bar-createorder').css('width', '100%');
          if(totalCreatedOrder != 0){
              $('#progress-text-createorder').html('<div class="text-success"><?php echo "Total '+totalCreatedOrder+' amazon order(s) created to opencart store from amazon store!" ?></div>');
              $('#progress-bar-createorder').addClass('progress-bar-success');
          }else{
              $('#progress-text-createorder').html('<div class="text-danger"><?php echo "Total '+totalCreatedOrder+' amazon order(s) created to opencart store from amazon store!" ?></div>');
              $('#progress-bar-createorder').addClass('progress-bar-danger');
          }
          $('#profiler_order .profiler_order_body').append('<div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo "Total '+totalCreatedOrder+' amazon order(s) created to opencart store from amazon store!" ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

          $('#profiler_order .profiler_order_body').append('<div class="alert alert-info"><i class="fa fa-check-circle"></i> Finished Execution <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
          $('.block_div').css('display','none');
      }
  };
//--></script>

<script type="text/javascript"><!--
  // import and create imported amazon single order to opencart store
  $("#import_by_orderId").on("click", function(e){
    e.preventDefault();
    var getOrderId = $('#amazonOrderOneByOne').find('input').val();
    if(getOrderId.length > 10 && getOrderId.length < 25){
        $('#progress-bar-importorder').css('width', '0%');
        $('#progress-bar-importorder').removeClass('progress-bar-danger progress-bar-success');
        $('#progress-text-importorder').html('<div class="text-info text-left"><?php echo $text_currently_importorder; ?></div>');

        jQuery.ajax({
            url   : 'index.php?route=amazon_map/order/importSingleOrder&token=<?php echo $token; ?>',
            data  : {
                      'account_id'      : '<?php echo $account_id; ?>',
                      'amazon_order_id' : getOrderId,
                    },
            dataType: 'json',
            type    : 'POST',
            beforeSend: function() {
                $('#amazonOrderOneByOne').find('.text-danger').remove();
                $('#amazonOrderOneByOne').find('.alert').remove();
            },
            success: function(json) {
                if (json.error_failed) {
                    $('#progress-bar-importorder').addClass('progress-bar-danger');
                    $('#progress-text-importorder').html('<div class="text-danger">' + json.error_failed + '</div>');
                    $("#amazonOrderOneByOne .form-horizontal").prepend('<div class="alert alert-danger"><i class="fa fa-check-circle"></i> '+json.error_failed+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                }
                if(json.success){
                    $('#progress-bar-importorder').addClass('progress-bar-success');
                    $('#progress-text-importorder').html('<div class="text-success">' + json.success + '</div>');
                    $("#amazonOrderOneByOne .form-horizontal").prepend('<div class="alert alert-success"><i class="fa fa-check-circle"></i> '+json.success+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                }
                $('#progress-bar-importorder').css('width', '100%');
            },
            error: function(xhr, ajaxOptions, thrownError) {
              alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
  }else{
      $('#amazonOrderOneByOne').find('.text-danger').remove();
      $('#amazonOrderOneByOne').find('input').parent().append('<div class="text-danger">Warning: Provide valid Amazon order Id!</div>');
  }
})
$(document).on("keypress", ":input:not(textarea)", function(event) {
    if (event.keyCode == 13) {
        event.preventDefault();
    }
});
//--></script>
