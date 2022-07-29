<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <a href="<?php echo $export_product; ?>" data-toggle="tooltip" title="" class="btn btn-primary" ><?php echo $button_export; ?></a>
        <a href="<?php echo $import_product; ?>" data-toggle="tooltip" title="" class="btn btn-warning" ><?php echo $button_import; ?></a>
    <a href="<?php echo $add; ?>" data-toggle="tooltip" title="" class="btn btn-primary" ><?php echo $text_add; ?></a>
      </div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
    <div class="container-fluid">
      <?php if (isset($error_warning) && $error_warning) { ?>
      <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
      <?php } ?>
      <?php if (isset($success) &&  $success) { ?>
      <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
      <?php } ?>
      <div class="well">
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label class="control-label" for="input-name"><?php echo $entry_product_name; ?></label>
              <input type="text" name="filter_name" value="<?php echo $filter_name; ?>" placeholder="<?php echo $entry_product_name; ?>" id="input-name" class="form-control" />
            </div>

          </div>

          <div class="col-sm-6">
            <div class="form-group">
              <label class="control-label" for="input-model"><?php echo $entry_category_name; ?></label>
              <input type="text" name="category_name" value="<?php echo $category_name; ?>" placeholder="<?php echo $entry_category_name; ?>" id="input-model" class="form-control" />
            </div>

            <button type="button" id="button-reset" style="margin-left: 20px;     background-color: #cf701e;" class="btn btn-primary pull-right"> <?php echo $button_reset; ?></button>
             <button type="button" id="button-filter"  class="btn btn-primary pull-right"><i class="fa fa-filter"></i> <?php echo $button_filter; ?></button>
          </div>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_list; ?></h3>
        </div>
        <form method="post" enctype="multipart/form-data" id="form-export-product-amazon">
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></td>

                  <td class="text-left"><?php echo $column_product_id; ?></td>
                  <td class="text-left"><?php echo $column_name; ?></td>
                  <td class="text-left"><?php echo $column_category_name; ?></td>
                  <td class="text-left"><?php echo $column_price; ?></td>
                  <td class="text-left"><?php echo $column_quantity; ?></td>
                  <td class="text-left"><?php echo $column_action; ?></td>
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
                  <td class="text-left"><a href="<?php echo $add.'&product_id='.$product['product_id']; ?>" data-toggle="tooltip" title="" class="btn btn-primary" data-original-title="Edit"><i class="fa fa-pencil"></i></a></td>
                </tr>
                <?php } ?>
                <?php } else { ?>
                <tr>
                  <td class="text-center" colspan="8"><?php echo $text_no_results; ?></td>
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
     </div>
    </div>
  </div>
<?php echo $footer; ?>
<script type="text/javascript">
$('#button-filter').on('click', function() {
var url = 'index.php?route=amazon_map/map_product_data&token=<?php echo $token; ?>';

var filter_name = $('input[name=\'filter_name\']').val();

if (filter_name) {
  url += '&filter_name=' + encodeURIComponent(filter_name);
}

var category_name = $('input[name=\'category_name\']').val();

if (category_name) {
  url += '&category_name=' + encodeURIComponent(category_name);
}



location = url;
});
$('#button-reset').on('click', function() {
  var url = 'index.php?route=amazon_map/map_product_data&token=<?php echo $token; ?>';
  location = url;
});

$('input[name=\'filter_name\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
			url: 'index.php?route=amazon_map/map_product_data/autocompleteMapdata&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item['name'],
						value: item['product_id']
					}
				}));
			}
		});
	},
	'select': function(item) {
		$('input[name=\'filter_name\']').val(item['label']);


	}
});
$('input[name=\'category_name\']').autocomplete({
  delay: 0,
  source: function(request, response) {
    $.ajax({
      url: 'index.php?route=amazon_map/map_product_data/autocompleteCategory&token=<?php echo $token; ?>&account_id=<?php echo $account_id; ?>&category_name=' +  encodeURIComponent(request),
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
    $('input[name=\'category_name\']').val(item.label);
    return false;
  },
  focus: function(item) {
      return false;
  }
});

</script>
