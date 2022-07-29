
<div id="content">
<link href="view/stylesheet/csspin.css" rel="stylesheet" type="text/css"/>
<style type="text/css">
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
  .product_import_section, #profiler_product, #profiler_product_export, #combination_section, #selected_export{
    display: none;
  }
  #update_export_opt .dropdown-item{
    display: block;
    width: 100%;
    padding: .25rem 1.5rem;
    clear: both;
    font-weight: 400;
    color: #212529;
    text-align: inherit;
    white-space: nowrap;
    background: 0 0;
    border: 0;
  }
  #update_export_opt .table-responsive{
    overflow-x: visible;
  }
</style>
  <div class="page-header">
    <div class="container-fluid">
      <?php if(isset($tab) && $tab == 'import_product'){ ?>
        <h3><?php echo $heading_title_import; ?></h3>
      <?php }else if(isset($tab) && $tab == 'export_product'){ ?>
        <h3><?php echo $heading_title_export; ?></h3>
      <?php }else{ ?>
          <h3><?php echo $heading_title; ?></h3>
      <?php } ?>
      <hr>
    </div>
  </div>

  <div class="page-header container-fluid">
    <div class="pull-right" style="margin-bottom: 10px;">
      <?php if(!$tab){ ?>
        <a href="<?php echo $import_product_tab; ?>" id="import-product-tab" type="button" data-toggle="tooltip" title="<?php echo $button_import_amazon_product; ?>" class="btn btn-info" ><i class="fa fa-download col-sm-12" aria-hidden="true"></i> <span class="col-sm-12"><?php echo $button_import; ?></span></a>
        <a href="<?php echo $export_product_tab; ?>" id="export-product-tab" type="button" data-toggle="tooltip" title="<?php echo $button_export; ?>" class="btn btn-info" ><i class="fa fa-upload col-sm-12" aria-hidden="true"></i> <span class="col-sm-12"><?php echo $button_export; ?></span></a>
        <button class="btn btn-danger" type="button" data-toggle="tooltip" title="<?php echo $button_delete_product_info; ?>" onclick="confirm('<?php echo $text_confirm; ?>') ? $('#form-product-delete').submit() : false;"><i class="fa fa-trash-o col-sm-12" aria-hidden="true"></i> <span class="col-sm-12"><?php echo $button_delete_product; ?></span></button>
        <?php if(!empty($product_delete_result)){ ?>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target=".product_delete_result" id="product_delete"><i class="fa fa-info col-sm-12" aria-hidden="true"></i>  <span class="col-sm-12"><?php echo "Result"; ?></span></button>
        <?php } ?>
      <?php }else{ ?>
        <a href="<?php echo $button_back_link; ?>" type="button" data-toggle="tooltip" title="<?php echo $button_back; ?>" class="btn btn-default" ><i class="fa fa-reply" aria-hidden="true"></i></a>
      <?php } ?>
    </div>
  </div>
  <div class="modal fade product_delete_result" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="gridSystemModalLabel"><strong><?php echo "Product Result"; ?></strong></h4>
        </div>
        <div class="modal-body">
          <?php if(isset($product_delete_result) && $product_delete_result) { ?>
          <?php foreach($product_delete_result as $p_key => $result){ ?>
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

    <?php if(isset($tab) && $tab == 'import_product'){ ?>
      <div id="product_import_list_section">
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
            <h3 class="panel-title"><i class="fa fa-refresh" aria-hidden="true"></i> <?php echo $sync_product_tab; ?></h3>
          </div>
          <div class="panel-body" id="generate-report-section">
            <div class="alert alert-warning"> <i class="fa fa-info-circle" aria-hidden="true"></i> <?php echo $info_report_id; ?></div>

            <div class="panel-body">
              <button type="button" class="btn btn-info" id="generate_report_id"><i class="fa fa-plus-circle" aria-hidden="true"></i> <?php echo $button_report_id; ?></button>

              <div class="row form-group product_import_section">

                <div class="col-sm-12 form-horizontal text-right">
                 <div class="col-sm-12 form-group">
                   <label class="col-sm-2 control-label"><?php echo "Processing..."; ?></label>
                   <div class="col-sm-10" style="margin-top:10px">
                     <div class="progress">
                       <div id="progress-bar-importproduct" class="progress-bar" style="width: 0%;"></div>
                     </div>
                     <div id="progress-text-importproduct"></div>
                   </div>
                 </div>
               </div>
               <div class="col-sm-12 col-md-6 col-lg-6" style="margin-top:5px;">
                <button type="button" class="btn btn-warning" id="import_update_product"><i class="fa fa-refresh" aria-hidden="true"></i>  <?php echo $button_import_product; ?></button>
               </div>
               <div class="col-sm-12 col-md-6 col-lg-6" style="margin-top:5px;">
                <button type="button" class="btn btn-warning" id="create_import_product" disabled="disabled" data-total="<?php echo count($total_import_product); ?>"><i class="fa fa-refresh" aria-hidden="true"></i>  <?php echo $button_create_import; ?></button>
               </div>
               <div class="col-sm-12 col-md-6 col-lg-6" style="margin-top:5px;">
                <button type="button" class="btn btn-warning" data-toggle="modal" disabled="disabled" data-target=".asin_model" id="import_update_one_by_one"><i class="fa fa-check-square-o" aria-hidden="true"></i>  <?php echo $button_import_product_by_asin; ?></button>
               </div>
              </div>
            </div>
          </div>
        </div>





        <div class="modal fade asin_model" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel"><strong><?php echo $button_import_product_by_asin; ?></strong></h4>
              </div>
              <div class="modal-body">
                <div class="form-horizontal">
                  <div class="form-group required">
                    <label class="control-label" style="margin-bottom: 10px;"><?php echo $text_product_asin; ?></label>
                    <div class="col-sm-12">
                        <input type="text" name="amazon_product_asin" value="" placeholder="<?php echo $entry_product_asin; ?>" class="form-control" />
                    </div>
                  </div>
                  <div class="alert alert-info" id="info_asin"><?php echo $info_product_asin_sync; ?></div>
                </div>
              </div>
              <div class="modal-footer">
                <span class="demo-spin" style="color: #1e91cf;position: relative;top: 6px;"><i class="fa  fa-spin fa-2x fa-fw"></i></span>
                <button type="button" id="import_by_asin" class="btn btn-primary"><?php echo $button_import; ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $button_close; ?></button>
              </div>

            </div>
          </div>
        </div>

        <div class="panel panel-default" id="profiler_product">
          <div class="panel-heading"  style="display:inline-block;width:100%;">
            <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $entry_product_response; ?></h3>
          </div>
          <div class="panel-body profiler_product_body">
            <div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <?php echo $error_not_referesh; ?></div>

          </div>
        </div>

      </div><!-- product-import -->
    <?php } ?>

    <?php if(isset($tab) && $tab == 'export_product'){ ?>
      <div id="product_export_list_section">
        <div class="panel panel-primary">
          <div class="panel-heading"  style="display:inline-block;width:100%;">
            <h3 class="panel-title"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php echo $info_about_export_tab; ?></h3>
          </div>
          <div class="panel-body">
            <ul>
              <li> <?php echo $text_export_tab_info3; ?></li>
            </ul>
          </div>
        </div>

        <div class="panel panel-danger">
          <div class="panel-heading" style="display:inline-block;width:100%;">
            <h3 class="panel-title"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> <?php echo $update_delete_export; ?></h3>
          </div>
          <div class="panel-body">
            <div class="form-horizontal text-right">
              <div class="col-sm-12 form-group">
                <label class="col-sm-2 control-label"><?php echo $text_processing; ?></label>
                <div class="col-sm-10" style="margin-top:10px">
                  <div class="progress">
                    <div id="progress-bar-updateDeleteProduct" class="progress-bar" style="width: 0%;"></div>
                  </div>
                  <div id="progress-text-updateDeleteProduct"></div>
                </div>
              </div>
            </div>
            <div class="pull-right" style="margin-bottom:10px;">
                <button type="button" class="btn btn-primary update_export_start" id="update" data-toggle="tooltip" title="<?php echo $info_button_update_export; ?>" ><i class="fa fa-pencil-square" aria-hidden="true"></i> <?php echo $button_update_export; ?></button>
                <button type="button" class="btn btn-danger update_export_start" id="delete" data-toggle="tooltip" title="<?php echo $info_button_delete_export; ?>" ><i class="fa fa-trash-o" aria-hidden="true"></i> <?php echo $button_delete_export; ?></button>
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#UpdateDeleteResult">
                  <i class="fa fa-info-circle" aria-hidden="true"></i> <?php echo "Show Result"; ?>
                </button>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="UpdateDeleteResult" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel"><?php echo "Update/Delete Result List"; ?></h4>
                  </div>
                  <div class="modal-body" id="sync_result" style="overflow-y: scroll;max-height: 350px">
                      <div class="alert alert-info text-center"> <?php echo "No result Found"; ?></div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-12">
            <form id="update_export_opt" class="form-horizontal" method="post" enctype="multipart/form-data">
              <div class="table-responsive">
                <table class="table table-bordered table-hover">
                  <thead>
                    <tr>
                      <td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('#update_export_opt input[name*=\'selected\']').prop('checked', this.checked);" /></td>
                      <td class="text-center"><?php echo $column_oc_product_id; ?></td>
                      <td class="text-center"><?php echo $column_amazon_product_id; ?></td>
                      <td class="text-left"><?php echo $column_product_name; ?></td>
                      <td class="text-center"><?php echo $column_product_type; ?></td>
                    </tr>
                  </thead>
                  <tbody>

                    <input type="hidden" name="account_id" value="<?php echo $account_id; ?>" />
                    <?php if ($updateproductData) { ?>
                    <?php foreach ($updateproductData as $product) { ?>
                    <tr>
                      <td class="text-center"><?php if (in_array($product['oc_product_id'], $selected)) { ?>
                        <input type="checkbox" name="selected[]" value="<?php echo $product['oc_product_id']; ?>" checked="checked" />
                        <?php } else { ?>
                        <input type="checkbox" name="selected[]" value="<?php echo $product['oc_product_id']; ?>" />
                        <?php } ?></td>

                      <td class="text-center"><?php echo $product['oc_product_id']; ?></td>
                      <td class="text-center"><?php echo $product['main_product_type_value']; ?></td>
                      <td class="text-left"><?php echo $product['product_name']; ?></td>
                      <td class="text-center">
                        <?php if(isset($product['combinations']) && $product['combinations']){ ?>
                          <div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Variations List</button>
                            <div class="dropdown-menu">
                              <?php foreach ($product['combinations'] as $combination) { ?>
                                <p class="dropdown-item"><?php echo $combination['name'].' ('.$combination['id_value'].')'; ?></p>
                              <?php } ?>
                            </div>
                          </div>
                        <?php }else{ ?>
                          Simple
                        <?php } ?>
                      </td>
                    </tr>
                    <?php } ?>
                    <?php } else { ?>
                    <tr>
                      <td class="text-center" colspan="7"><?php echo $text_no_results; ?></td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>

            </form>
          </div><!--col-sm-12-->
          </div>
        </div>

        <div class="panel panel-default" id="profiler_product_export">
          <div class="panel-heading"  style="display:inline-block;width:100%;">
            <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $entry_product_response; ?></h3>
          </div>
          <div class="panel-body profiler_product_export_body">
            <div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <?php echo $error_not_referesh; ?></div>

          </div>
        </div>
      </div>
    <?php } ?> <!--export Product-->

    <?php if(!$tab){ ?>
      <div class="panel panel-default" id="imported_list">
        <div class="panel-heading"  style="display:inline-block;width:100%;">
          <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_product_list; ?></h3>
        </div>
        <div class="panel-body">

           <div class="well">
             <div class="row">
               <div class="col-sm-4">
                 <div class="form-group">
                   <label class="control-label" for="input-oc-product-id"><?php echo $column_oc_product_id; ?></label>
                     <input type="text" name="filter_oc_product_id" value="<?php echo $filter_oc_product_id; ?>" placeholder="<?php echo $column_oc_product_id; ?>" id="input-oc-product-id" class="form-control"/>
                 </div>

                 <div class="form-group">
                   <label class="control-label" for="input-amazon-product-id"><?php echo $column_amazon_product_id; ?></label>
                     <input type="text" name="filter_amazon_product_id" value="<?php echo $filter_amazon_product_id; ?>" placeholder="<?php echo $column_amazon_product_id; ?>" id="input-amazon-product-id" class="form-control"/>
                 </div>
               </div>

               <div class="col-sm-4">
                   <div class="form-group">
                     <label class="control-label" for="input-oc-product-name"><?php echo $column_product_name; ?></label>
                     <div class='input-group'>
                       <input type="text" name="filter_oc_product_name" value="<?php echo $filter_oc_product_name; ?>" placeholder="<?php echo $column_product_name; ?>" id="input-oc-product-name" class="form-control"/>
                       <span class="input-group-addon">
                         <span class="fa fa-angle-double-down"></span>
                       </span>
                     </div>
                   </div>

                   <div class="form-group">
                     <label class="control-label" for="input-oc-price"><?php echo $column_price; ?></label>
                       <input type="text" name="filter_price" value="<?php echo $filter_price; ?>" placeholder="<?php echo $column_price; ?>" id="input-oc-price" class="form-control"/>
                   </div>
               </div>

               <div class="col-sm-4">
                 <div class="form-group">
                   <label class="control-label" for="input-sync-source"><?php echo $column_sync_source; ?></label>

                   <select name="filter_source_sync" class="form-control">
                     <option value="*"><?php echo $entry_sync_source; ?></option>
                       <option value="Amazon Item" <?php if(isset($filter_source_sync) && $filter_source_sync == 'Amazon Item'){ echo 'selected'; }?>>Amazon Item</option>
                       <option value="Opencart Item" <?php if(isset($filter_source_sync) && $filter_source_sync == 'Opencart Item'){ echo 'selected'; }?>>Opencart Item</option>
                   </select>
                 </div>

                 <div class="form-group">
                   <label class="control-label" for="input-oc-quantity"><?php echo $column_quantity; ?></label>
                     <input type="text" name="filter_quantity" value="<?php echo $filter_quantity; ?>" placeholder="<?php echo $column_quantity; ?>" id="input-oc-quantity" class="form-control"/>
                 </div>
               </div>
               <div class="col-sm-6" style="margin-top:38px;">
                 <button type="button" onclick="filter_map_product();" class="btn btn-primary" style="border-radius:0px;">
                   <i class="fa fa-search"></i><?php echo $button_filter_product; ?></button>
                 <a href="<?php echo $clear_product_filter; ?>" class="btn btn-default pull-right" style="border-radius:0px;"><i class="fa fa-eraser" aria-hidden="true"></i><?php echo $button_clear_product; ?></a>
               </div>
             </div>
           </div>
          <form action = "<?php echo $action_product; ?>" method="post" enctype="multipart/form-data" id="form-product-delete">
            <div class="table-responsive" >
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
                        <a href="<?php echo $sort_oc_product_id; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_oc_product_id; ?></a>
                        <?php } else { ?>
                        <a href="<?php echo $sort_oc_product_id; ?>"><?php echo $column_oc_product_id; ?></a>
                        <?php } ?></td>
                    <td class="text-left"><?php if ($sort == 'product_name') { ?>
                        <a href="<?php echo $sort_oc_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_product_name; ?></a>
                        <?php } else { ?>
                        <a href="<?php echo $sort_oc_name; ?>"><?php echo $column_product_name; ?></a>
                        <?php } ?></td>
                    <td class="text-left"><?php echo $column_amazon_product_asin; ?></td>
                    <td class="text-left"><?php echo $column_sync_source; ?></td>
                    <td class="text-left"><?php if ($sort == 'p.price') { ?>
                        <a href="<?php echo $sort_oc_price; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_price; ?></a>
                        <?php } else { ?>
                        <a href="<?php echo $sort_oc_price; ?>"><?php echo $column_price; ?></a>
                        <?php } ?></td>
                    <td class="text-left"><?php if ($sort == 'p.quantity') { ?>
                        <a href="<?php echo $sort_oc_quantity; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_quantity; ?></a>
                        <?php } else { ?>
                        <a href="<?php echo $sort_oc_quantity; ?>"><?php echo $column_quantity; ?></a>
                        <?php } ?></td>
                        <td class="text-left"> <?php echo $column_map; ?></td>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($import_products) { ?>
                  <?php foreach ($import_products as $amazon_product) { ?>
                  <tr>
                    <td class="text-center"><?php if (in_array($amazon_product['map_id'], $selected)) { ?>
                      <input type="checkbox" name="selected[]" value="<?php echo $amazon_product['map_id']; ?>" checked="checked" />
                      <?php } else { ?>
                      <input type="checkbox" name="selected[]" value="<?php echo $amazon_product['map_id']; ?>" />
                      <?php } ?></td>

                    <td class="text-left"><?php echo $amazon_product['map_id']; ?></td>
                    <td class="text-left"><?php echo $amazon_product['oc_product_id']; ?></td>
                    <td class="text-left">
                      <div class="col-sm-12"><?php echo $amazon_product['product_name']; ?></div>
                      <?php if(!empty($amazon_product['option_values'])){?>
                        <div class="dropdown pull-right">
                          <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuVariation" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            <?php echo $text_variation_list; ?>
                            <span class="caret"></span>
                          </button>
                          <ul class="dropdown-menu" aria-labelledby="dropdownMenuVariation">
                            <?php foreach ($amazon_product['option_values'] as $opt_variation) {  ?>
                              <li style="padding:5px 15px; cursor:pointer;"><?php echo $opt_variation['name'].' - ('.$opt_variation['asin'].')'; ?></li>
                            <?php } ?>
                          </ul>
                        </div>
                      <?php } ?>
                    </td>
                    <td class="text-left text-info"><?php echo $amazon_product['amazon_product_asin']; ?></td>
                    <td class="text-left"><button class="btn btn-<?php if(isset($amazon_product['source']) && $amazon_product['source'] == 'Amazon Item'){ echo 'warning'; }else{ echo 'info'; } ?>" type="button"> <?php echo $amazon_product['source']; ?></button></td>
                    <td class="text-left"><?php echo $amazon_product['price']; ?></td>
                    <td class="text-left"><?php echo $amazon_product['quantity']; ?>  </td>
                    <td class="text-left"><?php if(isset($amazon_product['source']) && $amazon_product['source'] == 'Amazon Item'){ ?>
                 <span class="btn btn-warning" onclick="openModel_map_product('<?php echo $amazon_product['map_id']; ?>','<?php echo $amazon_product['oc_product_id']; ?>')" ><?php echo $text_map_product; ?> </span>
                  <?php } ?>
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

  </div><!-- container-fluid -->
</div><!-- content -->



        <div class="modal fade map_duplicate_product" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="map_duplicate_id"><strong><?php echo $text_map_product; ?></strong></h4>
              </div>
              <div class="modal-body">
                <div class="map-product-error"> </div>
                <form id="form-map_product"  class="form-horizontal" method="post" enctype="multipart/form-data" >
                <div class="form-group">
                  <label class="control-label" for="input-opencart-product-id"><?php echo $column_product_name; ?></label>
                    <input type="text" name="opencart_product_name"  id="input-opencart-product-id" class="form-control"/>
                    <input type="hidden" name="opencart_product_id">
                    <input type="hidden" name="product_map_id">
                    <input type="hidden" name="opencart_map_product_id">
                </div>

              </div>
              <div class="modal-footer">
                <span class="demo-spin" style="color: #1e91cf;position: relative;top: 6px;"><i class="fa  fa-spin fa-2x fa-fw"></i></span>
       <button type="button"  class="btn btn-primary"  onclick="map_product();"><?php echo $button_map_product; ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $button_close; ?></button>
                    </form>
              </div>

            </div>
          </div>
        </div>
<script type="text/javascript"><!--
function filter_map_product() {
	url = 'index.php?route=amazon_map/account/edit&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>&status=account_product_map';

  var filter_oc_product_id = $('input[name=\'filter_oc_product_id\']').val();

  if (filter_oc_product_id) {
    url += '&filter_oc_product_id=' + encodeURIComponent(filter_oc_product_id);
  }

	var filter_oc_product_name = $('input[name=\'filter_oc_product_name\']').val();

	if (filter_oc_product_name) {
		url += '&filter_oc_product_name=' + encodeURIComponent(filter_oc_product_name);
	}

  var filter_amazon_product_id = $('input[name=\'filter_amazon_product_id\']').val();

  if (filter_amazon_product_id) {
    url += '&filter_amazon_product_id=' + encodeURIComponent(filter_amazon_product_id);
  }

  var filter_source_sync = $('select[name=\'filter_source_sync\']').val();

	if (filter_source_sync != '*') {
		url += '&filter_source_sync=' + encodeURIComponent(filter_source_sync);
	}

	var filter_price = $('input[name=\'filter_price\']').val();

	if (filter_price) {
		url += '&filter_price=' + encodeURIComponent(filter_price);
	}

  var filter_quantity = $('input[name=\'filter_quantity\']').val();

  if (filter_quantity) {
    url += '&filter_quantity=' + encodeURIComponent(filter_quantity);
  }

	location = url;
}


$('input[name=\'opencart_product_name\']').autocomplete({
  delay: 0,
  source: function(request, response) {
    $.ajax({
      url: 'index.php?route=amazon_map/product/get_map_product_list&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>&opencart_product_name=' +  encodeURIComponent(request)+'&opencart_map_product_id='+$('input[name=\'opencart_map_product_id\']').val(),
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
    $('input[name=\'opencart_product_name\']').val(item.label);
    $('input[name=\'opencart_product_id\']').val(item.value);
    return false;
  },
  focus: function(item) {
      return false;
  }
});
$('input[name=\'filter_oc_product_name\']').autocomplete({
  delay: 0,
  source: function(request, response) {
    $.ajax({
      url: 'index.php?route=amazon_map/product/autocomplete&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>&filter_oc_product_name=' +  encodeURIComponent(request),
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
    $('input[name=\'filter_oc_product_name\']').val(item.label);
    return false;
  },
  focus: function(item) {
      return false;
  }
});

//--></script>

<script type="text/javascript">
    $('#generate_report_id').on('click',function(){
        $.ajax({
          url: 'index.php?route=amazon_map/product/generate_report_id&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>',
          dataType:'json',
          type:'POST',
          cache: false,
          beforeSend: function() {
            $('.block_div').css('display','block');
          },
          complete: function() {
            $('.block_div').css('display','none');
          },
          success: function(json) {
            if (json['error']) {
              html = '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>  '+ json['error']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
              $('#generate-report-section').prepend(html);
            }
            if (json['success']){
              $('#generate-report-section .alert').remove();
              html = '<div class="alert alert-success"><i class="fa fa-exclamation-circle"></i>  '+ json['success']['message']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
              $('#generate-report-section').prepend(html);
              $('#generate_report_id').css('display','none');
              $('.product_import_section').css('display','block');
            }
            if(json['redirect']){
              location  = json['redirect'];
            }
          },
          error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
          }
        });
    });
</script>

<script type="text/javascript">
    // update/delete the exported product(s) at the amazon store
    var updateRequests    = []; var totalpages = 0; var totalUpdated = 0;
    var start_page    = 1;
    var error_report  = false;
    $(".update_export_start").on("click", function(e){
        e.preventDefault();
        var getExportOption = $(this).attr('id');
        var formData1       = new FormData($('#update_export_opt')[0]);
                              formData1.append('export_option', getExportOption);
        if (typeof timerSet != 'undefined') {
            clearInterval(timerSet);
        }
        timerSet = setInterval(function() {
                clearInterval(timerSet);
        // Reset everything
        $('.alert').remove();
        $('#progress-bar-updateDeleteProduct').css('width', '0%');
        $('#progress-bar-updateDeleteProduct').removeClass('progress-bar-danger progress-bar-success');
        $('#progress-text-updateDeleteProduct').html('');

          jQuery.ajax({
              url: 'index.php?route=amazon_map/product/opration_export_product&token=<?php echo $token; ?>&page='+start_page,
              data: formData1,
              dataType:'json',
              type:'POST',
              cache: false,
              contentType: false,
              processData: false,
              beforeSend: function() {
                $('.block_div').css('display','block');
                $('#product_export_list_section, #UpdateDeleteResult').find('.text-danger, .alert').remove();
              },
              complete: function() {
                NextUpdateDeleteStep();
              },
              success: function(jsonResponse) {
                  if (jsonResponse.error_failed) {
                      error_report = true;
                      $('#progress-bar-updateDeleteProduct').addClass('progress-bar-danger');
                      $('#progress-text-updateDeleteProduct').html('<div class="text-danger">' + jsonResponse.error_failed + '</div>');
                      $('#UpdateDeleteResult #sync_result').append('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+jsonResponse.error_failed+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                  }else{
                      error_report = false;
                      if (jsonResponse.error) {
                        var html = '';
                        for (i in jsonResponse.error) {
                            html += '<div class="alert alert-danger"><i class="fa fa-times" aria-hidden="true"></i>   '+jsonResponse.error[i]+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                        }
                        $('#UpdateDeleteResult #sync_result').append(html);
                        $('#progress-text-updateDeleteProduct').html('<div class="text-danger"> Warning: '+jsonResponse.error.length+' products failed to '+getExportOption+'d at amazon store!</div>');
                      }
                      if (jsonResponse.success) {
                        var html1 = '';
                        for (i in jsonResponse.success) {
                          html1 += '<div class="alert alert-success"><i class="fa fa-check-circle-o" aria-hidden="true"></i>  '+jsonResponse.success[i]+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                        }
                        totalUpdated = totalUpdated + jsonResponse.success.length;
                        $('#UpdateDeleteResult #sync_result').append(html1);
                        $('#progress-text-updateDeleteProduct').html('<div class="text-success"> '+jsonResponse.success.length+' products '+getExportOption+'d to amazon store successfully!</div>');
                      }
                      if(jsonResponse.totalpages){
                          totalpages = jsonResponse.totalpages;
                          for(start_page = 2; start_page < totalpages; start_page++) {
                              updateRequests.push({
                                  url: 'index.php?route=amazon_map/product/opration_export_product&token=<?php echo $token; ?>&page='+start_page,
                                  data: formData1,
                                  dataType:'json',
                                  type:'POST',
                                  cache: false,
                                  contentType: false,
                                  processData: false,
                                  success :   function(json_response){
                                      if (json_response.error) {
                                        var html = '';
                                        for (i in json_response.error) {
                                            html += '<div class="alert alert-danger"><i class="fa fa-times" aria-hidden="true"></i>   '+json_response.error[i]+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                                        }
                                        $('#UpdateDeleteResult #sync_result').append(html);
                                        $('#progress-text-updateDeleteProduct').html('<div class="text-danger"> Warning: '+json_response.error.length+' products failed to '+getExportOption+'d at amazon store!</div>');
                                      }
                                      if (json_response.success) {
                                        var html1 = '';
                                        for (i in json_response.success) {
                                          html1 += '<div class="alert alert-success"><i class="fa fa-check-circle-o" aria-hidden="true"></i>  '+json_response.success[i]+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                                        }
                                        totalUpdated = totalUpdated + json_response.success.length;
                                        $('#UpdateDeleteResult #sync_result').append(html1);
                                        $('#progress-text-updateDeleteProduct').html('<div class="text-success"> '+json_response.success.length+' products '+getExportOption+'d to amazon store successfully!</div>');
                                      }
                                  },
                              });
                          }

                      }
                  }
              },
              error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
              }
          });
        }, 500);
    })

    var NextUpdateDeleteStep = function(){
        if (updateRequests.length) {
            $('#progress-bar-updateDeleteProduct').css('width', (100 - (updateRequests.length / totalpages) * 100) + '%');
            $.ajax(updateRequests.shift()).then(NextUpdateDeleteStep);
        } else {
            $('#progress-bar-updateDeleteProduct').css('width', '100%');
            if(totalUpdated != 0){
                $('#progress-text-updateDeleteProduct').html('<div class="text-success"><?php echo "Total '+totalUpdated+' products updated/deleted to amazon store from opencart store, check show result!" ?></div>');
                $('#progress-bar-updateDeleteProduct').addClass('progress-bar-success');
            }else{
                if(!error_report){
                    $('#progress-text-updateDeleteProduct').html('<div class="text-danger"><?php echo "Total '+totalUpdated+' products updated/deleted to amazon store from opencart store, check show result!" ?></div>');
                }
                $('#progress-bar-updateDeleteProduct').addClass('progress-bar-danger');
            }
            $('.block_div').css('display','none');
        }
    };
</script>

<script type="text/javascript">
// ********** Amazon product import section **********
  $('#import_update_product').on('click', function(e){
      $.ajax({
          url     : 'index.php?route=amazon_map/product/getProductReport&token=<?php echo $token; ?>',
          data: {
            'account_id' : '<?php echo $account_id; ?>',
          },
          dataType:'json',
          type:'POST',
          beforeSend: function() {
            $('#profiler_product .profiler_product_body, #generate-report-section').find('.alert').remove();
            $('#progress-bar-importproduct').removeClass('progress-bar-danger progress-bar-success');
            $('.block_div, #profiler_product').css('display','block');
            $('.block_div').css('display','block');
            $('.container-fluid > .alert').remove();
          },
          complete:function() {
              $('.block_div').css('display','none');
              $('#progress-bar-importproduct').css('width', '100%');
              $('#create_import_product').prop('disabled',false);
              $('#import_update_one_by_one').prop('disabled',false);
          },
          success: function(jsonAmazonPro) {
              if (jsonAmazonPro.error) {
                  $('#progress-bar-importproduct').addClass('progress-bar-danger');
                  $('#progress-text-importproduct').html('<div class="text-danger">' + jsonAmazonPro.error + '</div>');
                  $('#profiler_product').append('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+jsonAmazonPro.error+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
              }else{
                  if(jsonAmazonPro.data && jsonAmazonPro.message){
                    $('#progress-bar-importproduct').addClass('progress-bar-success');
                    $('#profiler_product .profiler_product_body').append('<div class="alert alert-success"><i class="fa fa-exclamation-circle"></i> '+jsonAmazonPro.message+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                    $('#progress-text-importproduct').html('<div class="text-success"> '+jsonAmazonPro.message+' </div>');
                    $('#create_import_product').attr('data-total', jsonAmazonPro.total_product);
                  }
              }
          },
        })
  })


function checkFeedStatus(feed_id) {
       var html = '';
      $.ajax({
          url     : 'index.php?route=amazon_map/product/checkFeedStatus&token=<?php echo $token; ?>',
          data: {
            'account_id' : '<?php echo $account_id; ?>',
            'feed_id'    : feed_id
          },
          dataType:'json',
          type:'POST',
          beforeSend: function() {

            $('.block_div, #profiler_product').css('display','block');
            $('.block_div').css('display','block');

          },
          complete:function() {
              $('.block_div').css('display','none');

          },
          success: function(response) {
            if(response['error']){

            html += ' <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+response['comment']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
            $('.feed_html').html(html);
            $('.feed_status_id').modal('show');
            } else {

             html += ' <div class="alert alert-success"><b> <?php echo $FeedProcessingStatus; ?> :</b> '+response[0]['getFeedProcessingStatus'];
              html += '<b><br><?php echo $SubmittedDate; ?> : </b> '+response[0]['getSubmittedDate']['date'];
             html += '<br><b><?php echo $StartedProcessingDate; ?> :</b> '+response[0]['getStartedProcessingDate']['date'];
            html +='</div>';
             $('.feed_html').html(html);
             $('.feed_status_id').modal('show');
            }
        }
      });
  }
  // Suppose any product already Opencart cart store and amazon also end then if you import product then same product list two times so avoid this issue we map the product
  function openModel_map_product(map_id, map_product_id) {
    $('input[name=\'opencart_product_name\']').val('');
    $('input[name=\'opencart_product_id\']').val('');
    $('.map-product-error').html('');
    $('input[name=\'opencart_map_product_id\']').val(map_product_id);
    $('input[name=\'product_map_id\']').val(map_id);
    $('.map_duplicate_product').modal('show');
  }
   function map_product() {
       $('.map-product-error').html('');
     if($('input[name=\'opencart_product_id\']').val()!='')
     {

        var formData      = new FormData($('#form-map_product')[0]);
        formData.append('account_id', '<?php echo $account_id; ?>');

      $.ajax({
            url     : 'index.php?route=amazon_map/product/map_product_with_existing_product&token=<?php echo $token; ?>',
            data: formData,
            dataType:'json',
            type:'POST',
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {

              $('.block_div, #profiler_product').css('display','block');
              $('.block_div').css('display','block');

            },
            complete:function() {
                $('.block_div').css('display','none');

            },
            success: function(response) {
             if(response) {
                $('.map_duplicate_product').modal('hide');
               filter_map_product();
             } else {
                  $('.map-product-error').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i><?php echo "Something wrong!!"; ?><button type="button" class="close" data-dismiss="alert">&times;</button></div>');
             }
           }
      })
    } else {
          $('.map-product-error').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i><?php echo $map_product_error; ?><button type="button" class="close" data-dismiss="alert">&times;</button></div>');
    }
   }
</script>

<script type="text/javascript">
// ********** Amazon product create/update section **********
  var importRequests    = []; var totalImportProduct = 0; var import_total = 0;
  var count  = 1;
  $('#create_import_product').on('click', function(e){
      e.preventDefault();
      totalImportProduct = import_total = 0;
      import_total = $('#create_import_product').attr('data-total');
      if (typeof timer != 'undefined') {
          clearInterval(timer);
      }
      timer = setInterval(function() {
              clearInterval(timer);
      // Reset everything
      $('.alert').remove();
      $('#progress-bar-importproduct').css('width', '0%');
      $('#progress-bar-importproduct').removeClass('progress-bar-danger progress-bar-success');
      $('#progress-text-importproduct').html('<div class="text-info text-left"><?php echo $text_currently_import; ?></div>');

      $.ajax({
          url     : 'index.php?route=amazon_map/product/createProduct&token=<?php echo $token; ?>',
          data: {
            'account_id' : '<?php echo $account_id; ?>',
            'count'      : count,
          },
          dataType:'json',
          type:'POST',
          beforeSend: function() {
            $('#profiler_product .profiler_product_body, #generate-report-section').find('.alert').remove();
            $('.block_div').css('display','block');
            $('.container-fluid > .alert').remove();
          },
          complete:function() {
              NextImportStep();
          },
          success: function(jsonAmazonPro) {
                if (jsonAmazonPro.error_failed) {
                    if(jsonAmazonPro.error_failed.status && jsonAmazonPro.error_failed.status == 'complete'){
                        $('#progress-bar-importproduct').addClass('progress-bar-success');
                        $('#progress-text-importproduct').html('<div class="text-danger">' + jsonAmazonPro.error_failed.message + '</div>');
                        $('#profiler_product .profiler_product_body').append('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+jsonAmazonPro.error_failed.message+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                    }else{
                        $('#progress-bar-importproduct').addClass('progress-bar-danger');
                        $('#progress-text-importproduct').html('<div class="text-danger">' + jsonAmazonPro.error_failed + '</div>');
                        $('#profiler_product .profiler_product_body').append('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+jsonAmazonPro.error_failed+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                    }
                }else{
                    if(jsonAmazonPro.error){
                        html = '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+jsonAmazonPro.error+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                        $('#profiler_product .profiler_product_body').append(html);
                    }
                    if(jsonAmazonPro.success){
                        html = '<div class="alert alert-success"><i class="fa fa-check-circle"></i> '+jsonAmazonPro.success+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                        $('#profiler_product .profiler_product_body').append(html);
                        totalImportProduct = totalImportProduct + 1;
                    }

                    for(count = 2; count <= import_total; count++) {
                        importRequests.push({
                            url     : 'index.php?route=amazon_map/product/createProduct&token=<?php echo $token; ?>',
                            data: {
                              'account_id' : '<?php echo $account_id; ?>',
                              'count'      : count,
                            },
                            dataType:'json',
                            type:'POST',
                            success :   function(json_response){
                                if(json_response.error){
                                    html = '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+json_response.error+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                                    $('#profiler_product .profiler_product_body').append(html);
                                }
                                if(json_response.success){
                                    html = '<div class="alert alert-success"><i class="fa fa-check-circle"></i> '+json_response.success+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                                    $('#profiler_product .profiler_product_body').append(html);
                                    totalImportProduct = totalImportProduct + 1;
                                }
                            }
                        });
                    }
                }
              },
              error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
              }
          });
      }, 600);
  });

  var NextImportStep = function(){
      if (importRequests.length) {
          $('#progress-bar-importproduct').css('width', (100 - (importRequests.length / import_total) * 100) + '%');
          $.ajax(importRequests.shift()).then(NextImportStep);
      } else {
          $('#progress-bar-importproduct').css('width', '100%');
          if(totalImportProduct != 0){
              $('#progress-text-importproduct').html('<div class="text-success"><?php echo "Total '+totalImportProduct+' amazon products imported to opencart store from amazon store!" ?></div>');
              $('#progress-bar-importproduct').addClass('progress-bar-success');
          }else{
              $('#progress-text-importproduct').html('<div class="text-danger"><?php echo "Total '+totalImportProduct+' amazon products imported to opencart store from amazon store!" ?></div>');
              $('#progress-bar-importproduct').addClass('progress-bar-danger');
          }
          $('#generate-report-section').append('<div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo "Total '+totalImportProduct+' amazon products imported to opencart store from amazon store!" ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
          $('#generate-report-section').append('<div class="alert alert-info"><i class="fa fa-check-circle"></i> Finished Execution <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
          $('.block_div').css('display','none');
      }
  };
</script>

<script type="text/javascript">
   $( ".asin_model input[name='amazon_product_asin']" ).keyup(function() {
      $(".asin_model input[name='amazon_product_asin']").parent().removeClass('has-error');
      $(".asin_model").find('.text-danger').remove();
      var getASIN = $(this).val();
      var regex = new RegExp("^[A-Z0-9]+$");

      if(getASIN.length == 0 || (getASIN.length > 0 && getASIN.length < 10 && regex.test(getASIN))){
        $(".asin_model input[name='amazon_product_asin']").parent().removeClass('has-error');
        $(".asin_model input[name='amazon_product_asin']").parent().removeClass('has-success');
        $(".asin_model").find('.text-danger').remove();
      }else if(getASIN.length == 10 && regex.test(getASIN)){
        $(".asin_model input[name='amazon_product_asin']").parent().addClass('has-success');
      }else if (!regex.test(getASIN)) {
        $(".asin_model input[name='amazon_product_asin']").parent().addClass('has-error');
        $(".asin_model input[name='amazon_product_asin']").parent().parent().append('<div class="text-danger"><?php echo $error_wrong_asinformat; ?></div>');
      }
  });
  // ********** Amazon product create/update section by ASIN **********
  $('#import_by_asin').on('click',  function(){
    $(".asin_model input[name='amazon_product_asin']").parent().removeClass('.has-error');
    $(".asin_model").find('.text-danger').remove();
    var getASIN = $(".asin_model input[name='amazon_product_asin']").val();
    if((getASIN.length == 10) && getASIN.match(/((^[A-Z]+)|(^[0-9]+)|(^[0-9]+[A-Z]+)|(^[A-Z]+[0-9]+))+[0-9A-Z]+$/)){
      $.ajax({
        url     : 'index.php?route=amazon_map/product/createProduct&token=<?php echo $token; ?>',
        data    : {
          'account_id'  : '<?php echo $account_id; ?>',
          'product_asin': getASIN,
          'count'        : 1,
        },
        dataType: 'json',
        type    : 'POST',
        beforeSend: function() {
          $("input[name='amazon_product_asin'] ").parent().parent().find('.alert').remove();
          $('.block_div').css('display','block');
          $(".asin_model").find('.alert-danger').remove();
          $(".demo-spin > .fa").addClass('fa-cog');
        },
        complete: function() {
          $('.block_div').css('display','none');
          $(".asin_model input[name='amazon_product_asin']").val('');
          $(".demo-spin > .fa").removeClass('fa-cog');
        },
        success: function(jsonAmazonPro) {
                if (jsonAmazonPro.error_failed) {
                    if(jsonAmazonPro.error_failed.status && jsonAmazonPro.error_failed.status == 'complete'){
                        $('#info_asin').parent().append('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+jsonAmazonPro.error_failed.message+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                    }else{
                        $('#info_asin').parent().append('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+jsonAmazonPro.error_failed+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                    }
                }else{
                    if(jsonAmazonPro.error){
                        html = '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+jsonAmazonPro.error+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                        $('#info_asin').parent().append(html);
                    }
                    if(jsonAmazonPro.success){
                        html = '<div class="alert alert-success"><i class="fa fa-check-circle"></i> '+jsonAmazonPro.success+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                        $('#info_asin').parent().append(html);
                    }
                }
        },
        error: function(xhr, ajaxOptions, thrownError) {
          alert(thrownError + " " + xhr.statusText + " " + xhr.responseText);
        }
      });
    }else {
      $(".asin_model input[name='amazon_product_asin']").parent().addClass('.has-error');
      $(".asin_model input[name='amazon_product_asin']").parent().parent().append('<div class="text-danger"><?php echo $error_wrong_asinformat; ?></div>');
      $(".asin_model").find('.alert-danger').remove();
    }
  })
</script>
