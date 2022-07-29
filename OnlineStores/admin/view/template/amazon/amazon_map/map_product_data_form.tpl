<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <a href="<?php echo $back; ?>"  data-toggle="tooltip" title="" class="btn btn-primary" data-original-title="">Back</a>
<a href="javascript:;" onclick="$('#form-map').submit();" data-toggle="tooltip" title="" class="btn btn-primary" data-original-title="">Submit</a>
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

      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_list; ?></h3>
        </div>
        <div class="panel-body">


          <div>
            <div class="info">
            </div>
            <form action="<?php echo $action ?>" method="post" enctype="multipart/form-data" id="form-map" class="form-horizontal">



              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-product_name"><span data-toggle="tooltip" title="<?php echo $entry_product_name; ?>"><?php echo $entry_product_name; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="product_name" value="<?php if(isset($product_name)) { echo $product_name; } ?>"  id="input-related" class="form-control" />
                  <input type="hidden" name="product_id" value="<?php if(isset($product_id)) { echo $product_id; } ?>">
                </div>
              </div>
              <ul class="nav nav-tabs">
                <?php if(isset($amazon_connector_status) && $amazon_connector_status){ ?>
  								<li class="active"><a href="#tab-amazon-authorization" data-toggle="tab"><?php echo $tab_amazon_authorization; ?></a></li>
                  <?php if(isset($getAmazonSpecification) && $getAmazonSpecification){ ?>
                    <li><a href="#tab-amazon-specification" data-toggle="tab"><?php echo $tab_amazon_specification; ?></a></li>
                  <?php } ?>
                  <?php if(isset($getAmazonVariation) && $getAmazonVariation && !empty($getAmazonVariation['option_values']) ){ ?>
                    <li><a href="#tab-amazon-variation" data-toggle="tab"><?php echo $tab_amazon_variation; ?></a></li>
                  <?php } ?>
                <?php } ?>
              </ul>
  <div class="tab-content">

    <?php if(isset($amazon_connector_status) && $amazon_connector_status){ ?>
      <div class="tab-pane active" id="tab-amazon-authorization">
        <div class="form-group">
          <label class="col-sm-4 control-label"><?php echo $entry_uin; ?></label>
          <div class="col-sm-8">
            <select class="form-control col-sm-6" name="amazonProductType" style="width:40%;">
              <option value=""><?php echo $text_select; ?></option>
              <option value="ASIN" <?php if(isset($getProductFields['main_product_type']) && $getProductFields['main_product_type'] == 'ASIN') echo "selected"; ?> ><?php echo $text_asin; ?></option>
              <option value="EAN" <?php if(isset($getProductFields['main_product_type']) && $getProductFields['main_product_type'] == 'EAN') echo "selected"; ?>><?php echo $text_ean; ?></option>
              <option value="GTIN" <?php if(isset($getProductFields['main_product_type']) && $getProductFields['main_product_type'] == 'GTIN') echo "selected"; ?>><?php echo $text_gtin; ?></option>
              <option value="UPC" <?php if(isset($getProductFields['main_product_type']) && $getProductFields['main_product_type'] == 'UPC') echo "selected"; ?>><?php echo $text_upc; ?></option>
            </select>
            <div class="col-sm-12 alert alert-warning">
              <ul>
                  <li><?php echo $info_asin; ?></li>
                  <li><?php echo $info_ean; ?></li>
                  <li><?php echo $info_gtin; ?></li>
                  <li><?php echo $info_upc; ?></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-4 control-label" for="input-amazon-product-type-value"><?php echo $entry_in; ?></label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="amazonProductTypeValue" value="<?php if(isset($getProductFields['main_product_type_value'])) echo $getProductFields['main_product_type_value']; ?>" id="input-amazon-product-type-value" />
            <div class="col-sm-12 alert alert-warning"><?php echo $info_in; ?></div>
          </div>
        </div>
      </div>
      <?php if(isset($getAmazonSpecification) && $getAmazonSpecification){ ?>
        <div class="tab-pane" id="tab-amazon-specification">
          <div class="table-responsive">
            <table id="amazon-attribute" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <td class="text-left"><?php echo $entry_amazon_specification; ?></td>
                  <td class="text-left"><?php echo $entry_amazon_specification_value; ?></td>
                  <td></td>
                </tr>
              </thead>
              <tbody>
                <?php $amazon_attribute_row = 0; ?>
                <?php if(isset($amazon_product_specifications)) { ?>
                <?php foreach ($amazon_product_specifications as $amazon_product_specification) { ?>
                  <tr id="amazon-attribute-row<?php echo $amazon_attribute_row; ?>">
                    <td class="text-left" style="width: 40%;"><input type="text" name="amazon_product_specification[<?php echo $amazon_attribute_row; ?>][name]" value="<?php echo $amazon_product_specification['name']; ?>" placeholder="<?php echo $entry_attribute; ?>" class="form-control" />
                      <input type="hidden" name="amazon_product_specification[<?php echo $amazon_attribute_row; ?>][attribute_id]" value="<?php echo $amazon_product_specification['attribute_id']; ?>" /></td>
                    <td class="text-left"><?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                        <textarea name="amazon_product_specification[<?php echo $amazon_attribute_row; ?>][product_attribute_description][<?php echo $language['language_id']; ?>][text]" rows="5" placeholder="<?php echo $entry_text; ?>" class="form-control"><?php echo isset($amazon_product_specification['product_attribute_description'][$language['language_id']]) ? $amazon_product_specification['product_attribute_description'][$language['language_id']]['text'] : ''; ?></textarea>
                      </div>
                      <?php } ?></td>
                    <td class="text-left"><button type="button" onclick="$('#amazon-attribute-row<?php echo $amazon_attribute_row; ?>').remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                  </tr>
                  <?php $amazon_attribute_row++; ?>
                  <?php } ?>
                  <?php } ?>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="2"></td>
                    <td class="text-left"><button type="button" onclick="addAmazonAttribute();" data-toggle="tooltip" title="<?php echo $button_attribute_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                  </tr>
                </tfoot>
              </table>
            </div>
        </div>
      <script type="text/javascript">
          var amazon_attribute_row = <?php echo $amazon_attribute_row; ?>;
          function addAmazonAttribute() {
              html  = '<tr id="amazon-attribute-row' + amazon_attribute_row + '">';
              html += '   <td class="text-left" style="width: 20%;"><input type="text" name="amazon_product_specification[' + amazon_attribute_row + '][name]" value="" placeholder="<?php echo $entry_attribute; ?>" class="form-control" /><input type="hidden" name="amazon_product_specification[' + amazon_attribute_row + '][attribute_id]" value="" /></td>';
              html += '  <td class="text-left">';
              <?php foreach ($languages as $language) { ?>
                html += '<div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span><textarea name="amazon_product_specification[' + amazon_attribute_row + '][product_attribute_description][<?php echo $language['language_id']; ?>][text]" rows="5" placeholder="<?php echo $entry_text; ?>" class="form-control"></textarea></div>';
              <?php } ?>
              html += '  </td>';
              html += '  <td class="text-left"><button type="button" onclick="$(\'#amazon-attribute-row' + amazon_attribute_row + '\').remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
              html += '</tr>';

            $('#tab-amazon-specification tbody').append(html);

               amazonAttributeAutocomplete(amazon_attribute_row);

            amazon_attribute_row++;
          }
          function amazonAttributeAutocomplete(amazon_attribute_row) {
            $('input[name=\'amazon_product_specification[' + amazon_attribute_row + '][name]\']').autocomplete({
              'source': function(request, response) {
                $.ajax({
                  url: 'index.php?route=amazon_map/product/attributeAutocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
                  dataType: 'json',
                  success: function(json) {
                    response($.map(json, function(item) {
                      return {
                        category: item.attribute_group,
                        label: item.name,
                        value: item.attribute_id
                      }
                    }));
                  }
                });
              },
              'select': function(item) {
                $('input[name=\'amazon_product_specification[' + amazon_attribute_row + '][name]\']').val(item['label']);
                $('input[name=\'amazon_product_specification[' + amazon_attribute_row + '][attribute_id]\']').val(item['value']);
              }
            });
          }
          $('#amazon-attribute tbody tr').each(function(index, element) {
            amazonAttributeAutocomplete(index);
          });
      </script>
      <?php } ?>

      <?php if(isset($getAmazonVariation) && $getAmazonVariation && !empty($getAmazonVariation['option_values']) ){ ?>
        <div class="tab-pane" id="tab-amazon-variation">
          <div class="form-group">
            <div class="col-sm-12">
              <div class="col-sm-3">
                <h3><?php echo $getAmazonVariation['option_name']; ?></h3>
                <div class="well well-sm" style="height:450px;overflow:auto" >
                  <?php foreach ($getAmazonVariation['option_values'] as $key => $amazon_option_value) { ?>
                    <div class="checkbox amazon_variation_value">
                      <label for="amazon_option_value_<?php echo $amazon_option_value['option_value_id']; ?>">
                        <input type="checkbox" name="amazon_product_variation[]" value="<?php echo $amazon_option_value['option_value_id']; ?>" id="amazon_option_value_<?php echo $amazon_option_value['option_value_id']; ?>" data-variation-id = "<?php echo $amazon_option_value['option_id']; ?>"
                        <?php if(isset($amazon_product_variation) && $amazon_product_variation && in_array($amazon_option_value['option_value_id'], $amazon_product_variation)) { echo "checked"; } ?> />
                        <?php echo $amazon_option_value['name']; ?>
                      </label>
                    </div>
                  <?php } ?>
                </div>
              </div>
              <div class="col-sm-9">
                <h3><?php echo $entry_combination_list; ?></h3>
                <div class="well well-sm" style="height:450px;overflow:auto">
                  <ul class="nav nav-pills nav-stacked" id="amazon_product_variation_list">
                    <?php if(isset($amazon_product_variation_value)) { ?>
                      <?php foreach ($amazon_product_variation_value as $key => $amazon_option_value) {
                            foreach ($amazon_option_value['option_value'] as $key_option => $amazon_product_option_value) { ?>
                        <li id="<?php echo 'amazon_product_variation_'.$key_option; ?>">
                          <div class="form-group">
                            <div class="col-sm-3"><input class="form-control" type="hidden"  name="amazon_product_variation_value[<?php echo $key; ?>][option_id]" value="<?php echo $key; ?>" />
                              Variation Name<input class="form-control" type="text" data-toggle="tooltip" title="" data-original-title="" readonly name="amazon_product_variation_value[<?php echo $key; ?>][option_value][<?php echo $key_option; ?>][name]" value="<?php echo $amazon_product_option_value['name']; ?>"  /> </div>

                              <div class="col-sm-2" style="padding-right: 0px;">Seller SKU
                                <input type="text" class="form-control" name="amazon_product_variation_value[<?php echo $key; ?>][option_value][<?php echo $key_option; ?>][sku]" value="<?php echo $amazon_product_option_value['sku']; ?>" placeholder="SKU"  />
                                <?php if(isset($variation_error[$key]['option_value'][$key_option]['sku']) && $variation_error[$key]['option_value'][$key_option]['sku']){ ?>
                                    <div class="text-danger"><?php echo $variation_error[$key]['option_value'][$key_option]['sku']; ?></div>
                                <?php } ?>
                              </div>

                            <div class="col-sm-2">ID Type
                                <select class="form-control" name="amazon_product_variation_value[<?php echo $key; ?>][option_value][<?php echo $key_option; ?>][id_type]">
                                  <option value=""><?php echo $text_select; ?></option>
                                  <option value="ASIN" <?php if(isset($amazon_product_option_value['id_type']) && $amazon_product_option_value['id_type'] == 'ASIN') echo "selected"; ?> ><?php echo $text_asin; ?></option>
                                  <option value="EAN" <?php if(isset($amazon_product_option_value['id_type']) && $amazon_product_option_value['id_type'] == 'EAN') echo "selected"; ?>><?php echo $text_ean; ?></option>
                                  <option value="GTIN" <?php if(isset($amazon_product_option_value['id_type']) && $amazon_product_option_value['id_type'] == 'GTIN') echo "selected"; ?>><?php echo $text_gtin; ?></option>
                                  <option value="UPC" <?php if(isset($amazon_product_option_value['id_type']) && $amazon_product_option_value['id_type'] == 'UPC') echo "selected"; ?>><?php echo $text_upc; ?></option>
                                </select>
                                <?php if(isset($variation_error[$key]['option_value'][$key_option]['id_type']) && $variation_error[$key]['option_value'][$key_option]['id_type']){ ?>
                                    <div class="text-danger"><?php echo $variation_error[$key]['option_value'][$key_option]['id_type']; ?></div>
                                <?php } ?>
                                Product ID
                                <input type="text" class="form-control" name="amazon_product_variation_value[<?php echo $key; ?>][option_value][<?php echo $key_option; ?>][id_value]" value="<?php echo $amazon_product_option_value['id_value']; ?>" placeholder="ID" />
                                <?php if(isset($variation_error[$key]['option_value'][$key_option]['id_value']) && $variation_error[$key]['option_value'][$key_option]['id_value']){ ?>
                                    <div class="text-danger"><?php echo $variation_error[$key]['option_value'][$key_option]['id_value']; ?></div>
                                <?php } ?>
                            </div>

                            <div class="col-sm-2">
                              Quantity<input type="text" class="form-control" name="amazon_product_variation_value[<?php echo $key; ?>][option_value][<?php echo $key_option; ?>][quantity]" value="<?php echo $amazon_product_option_value['quantity']; ?>" placeholder="Quantity" />
                                <?php if(isset($variation_error[$key]['option_value'][$key_option]['quantity']) && $variation_error[$key]['option_value'][$key_option]['quantity']){ ?>
                                    <div class="text-danger"><?php echo $variation_error[$key]['option_value'][$key_option]['quantity']; ?></div>
                                <?php } ?>
                              </div>
                              <div class="col-sm-2">
                                Price Prefix<select class="form-control" name="amazon_product_variation_value[<?php echo $key; ?>][option_value][<?php echo $key_option; ?>][price_prefix]">
                                  <?php if ($amazon_product_option_value['price_prefix'] == '+') { ?>
                                    <option value="+" selected="selected">+</option>
                                  <?php } else { ?>
                                    <option value="+">+</option>
                                  <?php } ?>
                                  <?php if ($amazon_product_option_value['price_prefix'] == '-') { ?>
                                    <option value="-" selected="selected">-</option>
                                  <?php } else { ?>
                                    <option value="-">-</option>
                                  <?php } ?>
                                </select>

                                Price<input type="text" class="form-control" name="amazon_product_variation_value[<?php echo $key; ?>][option_value][<?php echo $key_option; ?>][price]" value="<?php echo $amazon_product_option_value['price']; ?>" placeholder="Price" />
                                <?php if(isset($variation_error[$key]['option_value'][$key_option]['price']) && $variation_error[$key]['option_value'][$key_option]['price']){ ?>
                                    <div class="text-danger"><?php echo $variation_error[$key]['option_value'][$key_option]['price']; ?></div>
                                <?php } ?>
                              </div>
                          </div>
                        </li>
                        <?php } ?>
                      <?php } ?>
                    <?php } ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <script type="text/javascript">
          $('.amazon_variation_value > label > input[type="checkbox"]').on('click', function(){
            option_value_id = $(this).val();
            option_value_name = $.trim($(this).parent('label').text());
            option_id = $(this).data('variation-id');
            if($(this).is(':checked')) {
              html = '';
              html += '<li id="amazon_product_variation_'+option_value_id+'"><div class="form-group">';

              html += '<div class="col-sm-3"><input class="form-control" type="hidden"  name="amazon_product_variation_value['+option_id+'][option_id]" value="'+option_id+'" /> Variation Name<input class="form-control" type="text" readonly name="amazon_product_variation_value['+option_id+'][option_value]['+option_value_id+'][name]" value="'+option_value_name+'" /> </div>';

              html += '<div class="col-sm-2" style="padding-right: 0px;">Seller SKU<input type="text" class="form-control" name="amazon_product_variation_value['+option_id+'][option_value]['+option_value_id+'][sku]" value="" placeholder="SKU"  /></div>';

              html += '<div class="col-sm-2">ID Type<select class="form-control" name="amazon_product_variation_value['+option_id+'][option_value]['+option_value_id+'][id_type]"><option value=""><?php echo $text_select; ?></option><option value="ASIN"><?php echo $text_asin; ?></option><option value="EAN"><?php echo $text_ean; ?></option><option value="GTIN"><?php echo $text_gtin; ?></option><option value="UPC"><?php echo $text_upc; ?></option></select>';

              html += 'Product ID<input type="text" class="form-control" name="amazon_product_variation_value['+option_id+'][option_value]['+option_value_id+'][id_value]" value="" placeholder="ID" /></div>';

              html += '<div class="col-sm-2" style="padding-right: 0px;">Quantity<input type="text" class="form-control" name="amazon_product_variation_value['+option_id+'][option_value]['+option_value_id+'][quantity]" value="" placeholder="Quantity"  /></div>';

              html += '<div class="col-sm-2">Price Prefix<select class="form-control" name="amazon_product_variation_value['+option_id+'][option_value]['+option_value_id+'][price_prefix]"><option value="+" >+</option><option value="-" >-</option></select>';

              html += 'Price<input type="text" class="form-control" name="amazon_product_variation_value['+option_id+'][option_value]['+option_value_id+'][price]" value="" placeholder="Price" /></div>';

              html += '</div></li>';

              $('#amazon_product_variation_list').append(html);
            } else {
              $('#amazon_product_variation_'+option_value_id).remove();
            }
          });
        </script>
      <?php } ?>
    <?php } ?>
  </div>


            </div>
            </form>
          </div>

      </div>
     </div>
    </div>
  </div>
<?php echo $footer; ?>
<?php if(isset($add_action)){ ?>
<script type="text/javascript">
$('input[name=\'product_name\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
			url: 'index.php?route=amazon_map/map_product_data/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
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
		$('input[name=\'product_name\']').val(item['label']);
    $('input[name=\'product_id\']').val(item['value']);

	}
});
</script>
<?php } ?>
