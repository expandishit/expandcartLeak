<div id="content">
<style>
  #form-export-product-amazon .dropdown-item{
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
  #form-export-product-amazon .table-responsive{
    overflow-x: visible;
  }
</style>
  <div class="page-header">
    <div class="container-fluid">
      <h3><?php echo $heading_title; ?></h3>
      <div class="pull-right" style="margin-bottom: 10px;">
        <button type="button" id="export-ebay-product" data-toggle="tooltip" data-token="<?php echo $token; ?>"  title="<?php echo $button_export_to_amazon; ?>" class="btn btn-warning"><i class="fa fa-upload" aria-hidden="true"></i> <?php echo $button_export_to_amazon; ?></button>
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#export_amazon_result">
          <i class="fa fa-info-circle" aria-hidden="true"></i> <?php echo "Show Result"; ?>
        </button>
      </div>
    </div>
  </div>
  <!-- Button trigger modal -->


      <!-- Modal -->
      <div class="modal fade" id="export_amazon_result" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="myModalLabel"><?php echo $text_export_result; ?></h4>
            </div>
            <div class="modal-body" id="sync_result" style="overflow-y: scroll;max-height: 350px">
              <?php if(isset($ocproduct_export_result['success']) && $ocproduct_export_result['success']){
                foreach($ocproduct_export_result['success'] as $product_export_success){
                  if(isset($product_export_success['name'])){ ?>
                  <div class="alert alert-success"> Success: Opencart store product <b><?php echo $product_export_success['name']; ?></b> [Id: <?php echo $product_export_success['product_id']; ?>] exported to amazon store successfully!</div>
                <?php } } ?>
              <?php } ?>
              <?php if(isset($ocproduct_export_result['error']) && $ocproduct_export_result['error']){
                foreach($ocproduct_export_result['error'] as $product_export_error){
                  if(isset($product_export_error['message'])){?>
                  <div class="alert alert-danger"> <?php echo $product_export_error['message']; ?></div>
                <?php } } ?>
              <?php } ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>

  <div class="container-fluid" id="product_export_list_section">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-primary">
      <div class="panel-heading"  style="display:inline-block;width:100%;">
        <h3 class="panel-title"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php echo $info_about_export_tab; ?></h3>
      </div>
      <div class="panel-body">
        <ul>
          <li> <?php echo $text_export_tab_info1; ?></li>
          <li> <?php echo $text_export_tab_info2; ?></li>
          <li> <?php echo $text_export_tab_info4; ?></li>
          <li> <?php echo $text_export_tab_info5; ?></li>
        </ul>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading"  style="display:inline-block;width:100%;">
        <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_oc_product_list; ?></h3>
      </div>
      <div class="panel-body">
        <div class="form-horizontal text-right">
          <div class="col-sm-12 form-group">
            <label class="col-sm-2 control-label"><?php echo $text_processing; ?></label>
            <div class="col-sm-10" style="margin-top:10px">
              <div class="progress">
                <div id="progress-bar-exportamazon" class="progress-bar" style="width: 0%;"></div>
              </div>
              <div id="progress-text-exportamazon"></div>
            </div>
          </div>
        </div>

        <div class="well">
          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label class="control-label" for="input-oc-product-id"><?php echo $column_oc_product_id; ?></label>
                  <input type="text" name="filter_oc_prod_id" value="<?php echo $filter_oc_prod_id; ?>" placeholder="<?php echo $column_oc_product_id; ?>" id="input-oc-product-id" class="form-control"/>
              </div>

              <div class="form-group">
                <label class="control-label" for="input-oc-price"><?php echo $column_price; ?></label>
                  <input type="text" name="filter_oc_price" value="<?php echo $filter_oc_price; ?>" placeholder="<?php echo $column_price; ?>" id="input-oc-price" class="form-control"/>
              </div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
                  <label class="control-label" for="input-oc-product-name"><?php echo $column_name; ?></label>
                  <div class='input-group'>
                    <input type="text" name="filter_oc_prod_name" value="<?php echo $filter_oc_prod_name; ?>" placeholder="<?php echo $column_name; ?>" id="input-oc-product-name" class="form-control"/>
                    <span class="input-group-addon">
                      <span class="fa fa-angle-double-down"></span>
                    </span>
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label" for="input-oc-quantity"><?php echo $column_quantity; ?></label>
                    <input type="text" name="filter_oc_quantity" value="<?php echo $filter_oc_quantity; ?>" placeholder="<?php echo $column_quantity; ?>" id="input-oc-quantity" class="form-control"/>
                </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <label class="control-label" for="input-oc-category-name"><?php echo $column_category_name; ?></label>
                <div class='input-group'>
                  <input type="text" name="filter_oc_cat_name" value="<?php echo $filter_oc_cat_name; ?>" placeholder="<?php echo $column_category_name; ?>" id="input-oc-category-name" class="form-control"/>
                  <span class="input-group-addon">
                    <span class="fa fa-angle-double-down"></span>
                  </span>
                </div>
              </div>
            </div>

            <div class="col-sm-6" style="margin-top:38px;">
              <button type="button" onclick="filter_export_product();" class="btn btn-primary" style="border-radius:0px;">
                <i class="fa fa-search"></i><?php echo $button_filter_product; ?></button>
              <a href="<?php echo $clear_amazon_export_filter; ?>" class="btn btn-default pull-right" style="border-radius:0px;"><i class="fa fa-eraser" aria-hidden="true"></i><?php echo $button_clear_product; ?></a>
            </div>
          </div>
        </div>
        <form method="post" enctype="multipart/form-data" id="form-export-product-amazon">
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('#product_export_list_section input[name*=\'selected\']').prop('checked', this.checked);" /></td>

                  <td class="text-left"><?php echo $column_product_id; ?></td>
                  <td class="text-left"><?php echo $column_name; ?></td>
                  <td class="text-left"><?php echo $column_category_name; ?></td>
                  <td class="text-left"><?php echo $column_price; ?></td>
                  <td class="text-left"><?php echo $column_quantity; ?></td>
                </tr>
              </thead>
              <tbody>

                <input type="hidden" name="account_id" value="<?php echo $account_id; ?>" />
                <?php if ($oc_products) { ?>
                <?php foreach ($oc_products as $product) { ?>
                <tr>
                  <td class="text-center"><?php if (in_array($product['product_id'], $selected)) { ?>
                    <input type="checkbox" name="selected[]" value="<?php echo $product['product_id']; ?>" checked="checked" />
                    <?php } else { ?>
                    <input type="checkbox" name="selected[]" value="<?php echo $product['product_id']; ?>" />
                    <?php } ?></td>

                  <td class="text-left"><?php echo $product['product_id']; ?></td>
                  <td class="text-left"><?php echo $product['name']; ?></td>
                  <td class="text-left">
                    <?php if(isset($product['category']) && $product['category']){ ?>
                      <div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Categories</button>
                        <div class="dropdown-menu">
                          <?php foreach ($product['category'] as $category) { ?>
                            <p class="dropdown-item"><?php echo $category['name']; ?></p>
                          <?php } ?>
                        </div>
                      </div>
                    <?php }else{ ?>
                      N/A
                    <?php } ?>
                  </td>
                  <td class="text-left"><?php echo $product['price']; ?></td>
                  <td class="text-left"><?php echo $product['quantity']; ?></td>
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
        <div class="row">
          <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
          <div class="col-sm-6 text-right"><?php echo $results; ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
function filter_export_product() {
	url = 'index.php?route=amazon_map/account/edit&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>&status=account_export_product_map';

  var filter_oc_prod_id = $('input[name=\'filter_oc_prod_id\']').val();

  if (filter_oc_prod_id) {
    url += '&filter_oc_prod_id=' + encodeURIComponent(filter_oc_prod_id);
  }

	var filter_oc_prod_name = $('input[name=\'filter_oc_prod_name\']').val();

	if (filter_oc_prod_name) {
		url += '&filter_oc_prod_name=' + encodeURIComponent(filter_oc_prod_name);
	}

	var filter_oc_cat_name = $('input[name=\'filter_oc_cat_name\']').val();

	if (filter_oc_cat_name) {
		url += '&filter_oc_cat_name=' + encodeURIComponent(filter_oc_cat_name);
	}

  var filter_oc_price = $('input[name=\'filter_oc_price\']').val();

  if (filter_oc_price) {
    url += '&filter_oc_price=' + encodeURIComponent(filter_oc_price);
  }

  var filter_oc_quantity = $('input[name=\'filter_oc_quantity\']').val();

  if (filter_oc_quantity) {
    url += '&filter_oc_quantity=' + encodeURIComponent(filter_oc_quantity);
  }

	location = url;
}

$('input[name=\'filter_oc_cat_name\']').autocomplete({
  delay: 0,
  source: function(request, response) {
    $.ajax({
      url: 'index.php?route=amazon_map/export_product/autocomplete&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>&filter_oc_cat_name=' +  encodeURIComponent(request),
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
    $('input[name=\'filter_oc_cat_name\']').val(item.label);
    return false;
  },
  focus: function(item) {
      return false;
  }
});

$('input[name=\'filter_oc_prod_name\']').autocomplete({
  delay: 0,
  source: function(request, response) {
    $.ajax({
      url: 'index.php?route=amazon_map/export_product/autocomplete&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>&filter_oc_prod_name=' +  encodeURIComponent(request),
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
    $('input[name=\'filter_oc_prod_name\']').val(item.label);
    return false;
  },
  focus: function(item) {
      return false;
  }
});

//--></script>

<script type="text/javascript">
var requests    = []; var totalExportedProduct = 0; var total = 0;

var error_failed = false;
$('#export-ebay-product').on('click', function(e){

    e.preventDefault();
      var start_page  = 1;
    var formData = new FormData($('#form-export-product-amazon')[0]);

    if (typeof timer != 'undefined') {
        clearInterval(timer);
    }
    timer = setInterval(function() {
            clearInterval(timer);
    // Reset everything
    $('.alert').remove();
    $('#progress-bar-exportamazon').css('width', '0%');
    $('#progress-bar-exportamazon').removeClass('progress-bar-danger progress-bar-success');
    $('#progress-text-exportamazon').html('');

      $.ajax({
          url     : 'index.php?route=amazon_map/export_product/ocExportProduct&token=<?php echo $token; ?>&page='+start_page,
          data: formData,
          dataType:'json',
          type:'POST',
          cache: false,
          contentType: false,
          processData: false,
          beforeSend: function() {
            $('.block_div').css('display','block');
            $('.container-fluid > .alert').remove();
          },
          complete:function() {
            NextStep();
          },
          success: function(jsonAmazonPro) {
                    if (jsonAmazonPro.error_failed) {
                        error_failed = true;
                        $('#progress-bar-exportamazon').addClass('progress-bar-danger');
                        $('#progress-text-exportamazon').html('<div class="text-danger">' + jsonAmazonPro.error_failed + '</div>');
                        $('#export_amazon_result #sync_result').append('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+jsonAmazonPro.error_failed+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                    }else{
                        error_failed = false;
                        if(jsonAmazonPro.data.error){
                            var html = '';
                            for (i in jsonAmazonPro.data.error) {
                                html += '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+jsonAmazonPro.data.error[i]['message']+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                            }
                            $('#export_amazon_result #sync_result').append(html);
                            if(jsonAmazonPro.data.error_count){
                                $('#progress-text-exportamazon').html('<div class="text-danger"> Warning: '+jsonAmazonPro.data.error_count+' products failed to export at amazon store!</div>');
                            }
                        }
                        if(jsonAmazonPro.data.success){
                            html1 = '';
                            for (i in jsonAmazonPro.data.success) {
                              html1 += '<div class="alert alert-success"><i class="fa fa-check-circle"></i> Success: Opencart store product <b>'+jsonAmazonPro.data.success[i]['name']+'</b> exported to amazon store successfully! <button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                            }
                            $('#export_amazon_result #sync_result').append(html1);
                            if(jsonAmazonPro.totalPage == 1) {
                                totalExportedProduct = totalExportedProduct + jsonAmazonPro.data.success.length;
                            } else {
                                totalExportedProduct = totalExportedProduct + jsonAmazonPro.data.success.length;
                            }

                            $('#progress-text-exportamazon').html('<div class="text-success"> '+jsonAmazonPro.data.success_count+' products exported to amazon store successfully!</div>');
                        }
                        total = jsonAmazonPro.totalPage;

                        for(start_page = 2; start_page <= jsonAmazonPro.totalPage; start_page++) {
                            requests.push({
                                url     : 'index.php?route=amazon_map/export_product/ocExportProduct&token=<?php echo $token; ?>&page='+start_page,
                                data: formData,
                                dataType:'json',
                                type:'POST',
                                cache: false,
                                contentType: false,
                                processData: false,
                                success :   function(json_response){
                                    if(json_response.data.error){
                                        var html = '';
                                        for (i in json_response.data.error) {
                                            html += '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+json_response.data.error[i]['message']+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                                        }
                                        $('#export_amazon_result #sync_result').append(html);
                                        if(json_response.data.error_count){
                                          $('#progress-text-exportamazon').html('<div class="text-danger"> Warning: '+json_response.data.error_count+' products failed to export at amazon store!</div>');
                                        }
                                    }
                                    if(json_response.data.success){
                                        html1 = '';
                                        for (i in json_response.data.success) {
                                          html1 += '<div class="alert alert-success"><i class="fa fa-check-circle"></i> Success: Opencart store product <b>'+json_response.data.success[i]['name']+'</b> exported to amazon store successfully! <button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                                        }
                                        $('#export_amazon_result #sync_result').append(html1);
                                        totalExportedProduct = totalExportedProduct + json_response.data.success.length;
                                        $('#progress-text-exportamazon').html('<div class="text-success"> '+json_response.data.success.length+' products exported to amazon store successfully!</div>');
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
    }, 500);
});

var NextStep = function(){
    if (requests.length) {
        $('#progress-bar-exportamazon').css('width', (100 - (requests.length / total) * 100) + '%');
        $.ajax(requests.shift()).then(NextStep);
    } else {
        $('#progress-bar-exportamazon').css('width', '100%');
        if(totalExportedProduct != 0){
            $('#progress-text-exportamazon').html('<div class="text-success"><?php echo "Total '+totalExportedProduct+' products exported to amazon store from opencart store!" ?></div>');
            $('#progress-bar-exportamazon').addClass('progress-bar-success');
            var redirect = '<?php echo $redirect; ?>';
            if(redirect){
              window.location.href = redirect;
            }
        }else{
            if(!error_failed){
                $('#progress-text-exportamazon').html('<div class="text-danger"><?php echo "Total '+totalExportedProduct+' products exported to amazon store from opencart store!" ?></div>');
            }
            $('#progress-bar-exportamazon').addClass('progress-bar-danger');
        }

        $('.block_div').css('display','none');
    }
};
</script>
