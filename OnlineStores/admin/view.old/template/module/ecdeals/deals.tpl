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

  <?php if ($success) { ?>
  <script>
      var notificationString = '<?php echo $success; ?>';
      var notificationType = 'success';
  </script>
  <?php } ?>

  <div class="box">
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a><a onclick="$('#action').val('save_stay');$('#form').submit();" class="button btn btn-primary"><?php echo $button_save_stay; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <input type="hidden" name="action" id="action" value=""/>
        <table class="form">
          <tr>
            <td>
              <select name="filter_category_id" onchange="">
                <option value=""><?php echo $this->language->get("text_filter_category");?></option>
                <?php foreach ($categories as $category) { ?>
                <?php if($filter_category && $category['category_id'] == $filter_category){ ?>
                  <option value="<?php echo $category['category_id']; ?>" selected="selected"><?php echo $category['name']; ?></option>
                <?php }else{ ?>
                  <option value="<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></option>
                  <?php } ?>
                  <?php if ($category['children']) { ?>
                    <?php foreach ($category['children'] as $child) { ?>
                    <?php if($filter_category && $category['category_id'] == $filter_category){ ?>
                      <option value="<?php echo $child['category_id']; ?>" selected="selected"> - <?php echo $child['name']; ?></option>
                    <?php }else{ ?>
                      <option value="<?php echo $child['category_id']; ?>"> - <?php echo $child['name']; ?></option>
                    <?php } ?>
                    <?php } ?>
                  <?php } ?>
               
                <?php } ?>
              </select>
          </td>

          <td style="display: none;">
              <select name="filter_store_id" onchange="">
                <option value=""><?php echo $this->language->get("text_filter_store");?></option>
                <?php if (isset($filter_store) && $filter_store == 0) { ?>
                  <option value="0" selected="selected"><?php echo $text_default; ?></option>
                  <?php } else { ?>
                  <option value="0"><?php echo $text_default; ?></option>
                  <?php } ?>

                <?php foreach ($stores as $store) { ?>
                <?php if(isset($filter_store) && $store['store_id'] == $filter_store){ ?>
                  <option value="<?php echo $store['store_id']; ?>" selected="selected"><?php echo $store['name']; ?></option>
                <?php }else{ ?>
                  <option value="<?php echo $store['store_id']; ?>"><?php echo $store['name']; ?></option>
                  <?php } ?>
                <?php } ?>
              </select>
          </td>

          </tr>
        </table>
         <table class="table table-hover dataTable no-footer">
          <thead>
            <tr>
              <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>
              <td class="center"><?php echo $column_image; ?></td>
              <td class="left"><?php if ($sort == 'pd.name') { ?>
                <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_name; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_name; ?>"><?php echo $column_name; ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'p.model') { ?>
                <a href="<?php echo $sort_model; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_model; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_model; ?>"><?php echo $column_model; ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'p.price') { ?>
                <a href="<?php echo $sort_price; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_price; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_price; ?>"><?php echo $column_price; ?></a>
                <?php } ?></td>
              <td class="right"><?php if ($sort == 'p.quantity') { ?>
                <a href="<?php echo $sort_quantity; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_quantity; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_quantity; ?>"><?php echo $column_quantity; ?></a>
                <?php } ?></td>
              <td class="right"><?php echo $column_deal_status; ?></td>
              <td class="right"><?php if ($sort == 'p.featured') { ?>
                <a href="<?php echo $sort_featured; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_featured; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_featured; ?>"><?php echo $column_featured ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'p.status') { ?>
                <a href="<?php echo $sort_status; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_status; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_status; ?>"><?php echo $column_status; ?></a>
                <?php } ?></td>
              <td class="right"><?php echo $column_action; ?></td>
            </tr>
          </thead>
          <tbody>
            <tr class="filter">
              <td></td>
              <td></td>
              <td><input type="text" name="filter_name" value="<?php echo $filter_name; ?>" /></td>
              <td><input type="text" name="filter_model" value="<?php echo $filter_model; ?>" /></td>
              <td align="left"><input type="text" name="filter_price" value="<?php echo $filter_price; ?>" size="8"/></td>
              <td align="right"><input type="text" name="filter_quantity" value="<?php echo $filter_quantity; ?>" style="text-align: right;" /></td>
              <td align="right"><select name="filter_deal_status">
                  <option value="*"></option>
                  <?php foreach($deal_status as $key=>$val) { ?>
                  <?php if($key == $filter_deal_status ) { ?>
                  <option value="<?php echo $key; ?>" class="status_<?php echo $key; ?>" selected="selected"><?php echo $val; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $key; ?>" class="status_<?php echo $key; ?>"><?php echo $val; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select></td>
              <td><select name="filter_featured">
                  <option value="*"></option>
                  <?php if ($filter_featured) { ?>
                  <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_yes; ?></option>
                  <?php } ?>
                  <?php if (!is_null($filter_featured) && !$filter_featured) { ?>
                  <option value="0" selected="selected"><?php echo $text_no; ?></option>
                  <?php } else { ?>
                  <option value="0"><?php echo $text_no; ?></option>
                  <?php } ?>
                </select></td>
              <td><select name="filter_status">
                  <option value="*"></option>
                  <?php if ($filter_status) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <?php } ?>
                  <?php if (!is_null($filter_status) && !$filter_status) { ?>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select></td>
              <td align="right"><a onclick="filter();" class="button btn btn-primary"><?php echo $button_filter; ?></a></td>
            </tr>
            <?php if ($products) { ?>
            <?php foreach ($products as $product) { ?>
            <tr data-id="<?php echo $product['product_id']; ?>">
              <td style="text-align: center;"><?php if ($product['selected']) { ?>
                <input type="checkbox" class="checkbox" name="selected[]" value="<?php echo $product['product_id']; ?>" checked="checked" />
                <?php } else { ?>
                <input type="checkbox" class="checkbox" name="selected[]" value="<?php echo $product['product_id']; ?>" />
                <?php } ?></td>
              <td class="center"><img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" style="padding: 1px; border: 1px solid #DDDDDD;" /></td>
              <td class="left">
                <span class="editable" title="<?php echo $this->language->get("text_double_click_to_edit");?>" rel="product_name_<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></span>
                <input type="text" id="product_name_<?php echo $product['product_id']; ?>" name="product[<?php echo $product['product_id']; ?>][name]" value="<?php echo $product['name']; ?>" class="product_name input_hidden" style="display:none"/>
              </td>
              <td class="left">
                <span class="editable" title="<?php echo $this->language->get("text_double_click_to_edit");?>" rel="product_model_<?php echo $product['product_id']; ?>"><?php echo $product['model']; ?></span>
                <input type="text" id="product_model_<?php echo $product['product_id']; ?>" name="product[<?php echo $product['product_id']; ?>][model]" value="<?php echo $product['model']; ?>" class="product_model input_hidden" style="display:none"/>
              </td>
              <td class="left">
                <span class="editable nohide" id="product_price_<?php echo $product['product_id']; ?>" title="<?php echo $this->language->get("text_double_click_to_edit");?>" rel="form_product_price_<?php echo $product['product_id']; ?>">
                <?php if ($product['special']) { ?>
                <span style="text-decoration: line-through;"><?php echo $product['price']; ?></span><br/>
                <span style="color: #b00;" id="special_price_<?php echo $product['product_id']; ?>"><?php echo $product['special']; ?></span>
                <?php } else { ?>
                <?php echo $product['price']; ?>
                <?php } ?>
                </span>
              </td>
              <td class="right"><span class="editable" title="<?php echo $this->language->get("text_double_click_to_edit");?>" rel="product_quantity_<?php echo $product['product_id']; ?>"><?php if ($product['quantity'] <= 0) { ?>
                <span style="color: #FF0000;"><?php echo $product['quantity']; ?></span>
                <?php } elseif ($product['quantity'] <= 5) { ?>
                <span style="color: #FFA500;"><?php echo $product['quantity']; ?></span>
                <?php } else { ?>
                <span style="color: #008000;"><?php echo $product['quantity']; ?></span>
                <?php } ?>
              </span>
                 <input type="text" id="product_quantity_<?php echo $product['product_id']; ?>" name="product[<?php echo $product['product_id']; ?>][quantity]" value="<?php echo $product['quantity']; ?>" class="product_quantity input_hidden" style="display:none"/>
              </td>
              <td class="right">
                <?php 
                  $product_status = isset($product['deal_status'])?$product['deal_status']:"";
                  $product_status_label = isset($product['deal_status_label'])?$product['deal_status_label']:"";
                  ?>
                <span class="status_<?php echo $product_status;?>"><?php echo $product_status_label;?></span>
              </td>
              <td class="left">
                <span class="editable" title="<?php echo $this->language->get("text_double_click_to_edit");?>" rel="product_featured_<?php echo $product['product_id']; ?>"><?php echo $product['featured']; ?></span>
                <select  id="product_featured_<?php echo $product['product_id']; ?>" name="product[<?php echo $product['product_id']; ?>][featured]" class="input_hidden" style="display:none">
                  <?php if ($product['featured_index']) { ?>
                  <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_yes; ?></option>
                  <?php } ?>
                  <?php if (!is_null($product['featured_index']) && !$product['featured_index']) { ?>
                  <option value="0" selected="selected"><?php echo $text_no; ?></option>
                  <?php } else { ?>
                  <option value="0"><?php echo $text_no; ?></option>
                  <?php } ?>
                </select>
              </td>
              <td class="left">
                <span class="editable" title="<?php echo $this->language->get("text_double_click_to_edit");?>" rel="product_status_<?php echo $product['product_id']; ?>"><?php echo $product['status']; ?></span>
                <select  id="product_status_<?php echo $product['product_id']; ?>" name="product[<?php echo $product['product_id']; ?>][status]" class="input_hidden" style="display:none">
                  <?php if ($product['status_index']) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <?php } ?>
                  <?php if (!is_null($product['status_index']) && !$product['status_index']) { ?>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select>
              </td>
              <td class="right"><?php foreach ($product['action'] as $action) { ?>
                [ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]
                <?php } ?></td>
            </tr>
            <tr class="product_special_block" id="form_product_price_<?php echo $product['product_id']; ?>" rel="product_price_<?php echo $product['product_id']; ?>" style="display:none;">
              <td colspan="10">
                <h3 style="text-align:center"><?php echo $this->language->get("text_product_specials");?></h3>
                <table id="special<?php echo $product['product_id']; ?>" class="table table-hover dataTable no-footer">
                <thead>
                  <tr>
                    <td class="left"><?php echo $this->language->get("entry_customer_group"); ?></td>
                    <td class="right"><?php echo $this->language->get("entry_priority"); ?></td>
                    <td class="right"><?php echo $this->language->get("entry_price"); ?></td>
                    <td class="left"><?php echo $this->language->get("entry_date_start"); ?></td>
                    <td class="left"><?php echo $this->language->get("entry_date_end"); ?></td>
                    <td></td>
                  </tr>
                </thead>
                 <?php $special_row = 0; ?>
                 <?php if($product['product_specials']){ ?>
                  <?php foreach ($product['product_specials'] as $product_special) { ?>
                      <tbody id="special-row-<?php echo $product['product_id']; ?>-<?php echo $special_row; ?>">
                        <tr>
                          <td class="left"><select name="product[<?php echo $product['product_id']; ?>][special][<?php echo $special_row; ?>][customer_group_id]">
                              <?php foreach ($customer_groups as $customer_group) { ?>
                              <?php if ($customer_group['customer_group_id'] == $product_special['customer_group_id']) { ?>
                              <option value="<?php echo $customer_group['customer_group_id']; ?>" selected="selected"><?php echo $customer_group['name']; ?></option>
                              <?php } else { ?>
                              <option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>
                              <?php } ?>
                              <?php } ?>
                            </select></td>
                          <td class="right"><input type="text" name="product[<?php echo $product['product_id']; ?>][special][<?php echo $special_row; ?>][priority]" value="<?php echo $product_special['priority']; ?>" size="2" /></td>
                          <td class="right"><input type="text" class="special_value" name="product[<?php echo $product['product_id']; ?>][special][<?php echo $special_row; ?>][price]" value="<?php echo $product_special['price']; ?>" /></td>
                          <td class="left"><input type="text" name="product[<?php echo $product['product_id']; ?>][special][<?php echo $special_row; ?>][date_start]" value="<?php echo $product_special['date_start']; ?>" class="datetime" /></td>
                          <td class="left"><input type="text" name="product[<?php echo $product['product_id']; ?>][special][<?php echo $special_row; ?>][date_end]" value="<?php echo $product_special['date_end']; ?>" class="datetime" /></td>
                          <td class="left"><a onclick="$('#special-row-<?php echo $product['product_id']; ?>-<?php echo $special_row; ?>').remove();" class="button btn btn-primary"><?php echo $this->language->get("button_remove"); ?></a></td>
                        </tr>
                      </tbody>
                      <?php $special_row++; ?>
                      <?php } ?>
                  <?php } ?>
                  <tfoot>
                    <tr>
                      <td colspan="5"></td>
                      <td class="left"><a onclick="addSpecial(<?php echo $product['product_id']; ?>);" class="button btn btn-primary"><?php echo $this->language->get("button_add_special"); ?></a></td>
                    </tr>
                  </tfoot>
                </table>
              </td>
            </tr>
            <?php } ?>
            <?php } else { ?>
            <tr>
              <td class="center" colspan="9"><?php echo $text_no_results; ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
var special_row = <?php echo $special_row; ?>;

function addSpecial(product_id, special_row) {
  html  = '<tbody id="special-row-'+product_id+ '-' + special_row + '">';
  html += '  <tr>'; 
    html += '    <td class="left"><select name="product['+product_id+'][special][' + special_row + '][customer_group_id]">';
    <?php foreach ($customer_groups as $customer_group) { ?>
    html += '      <option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>';
    <?php } ?>
    html += '    </select></td>';   
    html += '    <td class="right"><input type="text" name="product['+product_id+'][special][' + special_row + '][priority]" value="" size="2" /></td>';
  html += '    <td class="right"><input type="text" class="special_value" name="product['+product_id+'][special][' + special_row + '][price]" value="" /></td>';
    html += '    <td class="left"><input type="text" name="product['+product_id+'][special][' + special_row + '][date_start]" value="" class="datetime" /></td>';
  html += '    <td class="left"><input type="text" name="product['+product_id+'][special][' + special_row + '][date_end]" value="" class="datetime" /></td>';
  html += '    <td class="left"><a onclick="$(\'#special-row-'+product_id+'-' + special_row + '\').remove();" class="button btn btn-primary"><?php echo $this->language->get("button_remove"); ?></a></td>';
  html += '  </tr>';
    html += '</tbody>';
  
  $('#special'+product_id+' tfoot').before(html);
 
  $('#special-row-'+product_id+'-' + special_row + ' .datetime').datetimepicker({dateFormat: 'yy-mm-dd', timeFormat: 'h:m'});
  $('.time').timepicker({timeFormat: 'h:m'});
  special_row++;
}
//--></script> 
<script type="text/javascript"><!--
 
  $(function() {
    $(".table.table-hover.dataTable.no-footer .checkbox").click(function(){
         if($(this).is(':checked')){
            $(this).parent().parent().addClass('row-selected');
         }else{
            $(this).parent().parent().removeClass('row-selected');
         }
      })

    $(".table.table-hover.dataTable.no-footer .editable").each(function(){
      var element_id = $(this).attr("rel");
      var obj = this;
      var qty = 0;
      if(element_id != ""){
        $(this).dblclick(function(){
          $("#"+element_id).show();
          if(!$(this).hasClass("nohide")){
            $(this).hide();
          }
          var ele = $(this).parent().parent().find(":checkbox");
          ele.prop('checked', true);
          $(this).addClass('admin_checked');
          $(this).parent().parent().addClass('row-selected');

        });

        $("#"+element_id).dblclick(function(){
          var back_element = $(this).attr("rel");
          if($(this).hasClass("product_quantity")){
            qty = $(this).val();
            qty = parseInt(qty);

            if(qty <= 0){
              $(obj).html('<span style="color: #FF0000;">'+qty+'</span>');
            }else if(qty <= 5 ){
              $(obj).html('<span style="color: #FFA500;">'+qty+'</span>');
            }else{
              $(obj).html('<span style="color: #008000;">'+qty+'</span>');
            }
          }else if($("#"+back_element)){

          }else{
            $(obj).html($(this).val());
          }
          
          $(this).hide();
          $(obj).show();

        });
        $("#"+element_id).keypress(function(event){
          if ( event.which == 13 ) {
             $("#"+element_id).trigger("dblclick");
          }
        })
      }
      
    })
  });
//--></script>

<script type="text/javascript"><!--

$(function() {

    // grab the initial top offset of the navigation 
    var sticky_navigation_offset_top = $('#sticky_navigation').offset().top;
    
    // our function that decides weather the navigation bar should have "fixed" css position or not.
    var sticky_navigation = function(){
        var scroll_top = $(window).scrollTop(); // our current vertical position from the top
        
        // if we've scrolled more than the navigation, change its position to fixed to stick to top,
        // otherwise change it back to relative
        if (scroll_top > sticky_navigation_offset_top) {
            $('#sticky_navigation').css({ 'position': 'fixed', 'top':0, 'left':0, 'width': '100%' });
        } else {
            $('#sticky_navigation').css({ 'position': 'relative', 'width': '98.8%' }); 
        }   
    };
    
    // run our function on load
    sticky_navigation();
    
    // and run it again every time you scroll
    $(window).scroll(function() {
         sticky_navigation();
    });

});


function filter_category(obj){
  window.location='index.php?route=module/ecdeals/deals&token=<?php echo $token; ?>&filter_category_id='+obj.value;
}
function filter_store(obj){
  window.location='index.php?route=module/ecdeals/deals&token=<?php echo $token; ?>&filter_store_id='+obj.value;
}
function filter_special(obj){
  window.location='index.php?route=module/ecdeals/deals&token=<?php echo $token; ?>&filter_special='+obj.value;
}
$('.datetime').datetimepicker({dateFormat: 'yy-mm-dd', timeFormat: 'h:m'});
//--></script>
<script type="text/javascript"><!--
    function image_upload(field, thumb) {
        $.startImageManager(field, thumb);
    };
    //--></script>
<script type="text/javascript"><!--
function filter() {
  url = 'index.php?route=module/ecdeals/deals&token=<?php echo $token; ?>';
  
  var filter_category_id = $('select[name=\'filter_category_id\']').val();

  if (filter_category_id){
    url += '&filter_category_id=' + encodeURIComponent(filter_category_id);
  }

  var filter_store_id = $('select[name=\'filter_store_id\']').val();

  if (filter_store_id){
    url += '&filter_store_id=' + encodeURIComponent(filter_store_id);
  }

  var filter_name = $('input[name=\'filter_name\']').val();
  
  if (filter_name) {
    url += '&filter_name=' + encodeURIComponent(filter_name);
  }
  
  var filter_model = $('input[name=\'filter_model\']').val();
  
  if (filter_model) {
    url += '&filter_model=' + encodeURIComponent(filter_model);
  }
  
  var filter_price = $('input[name=\'filter_price\']').val();
  
  if (filter_price) {
    url += '&filter_price=' + encodeURIComponent(filter_price);
  }
  
  var filter_quantity = $('input[name=\'filter_quantity\']').val();
  
  if (filter_quantity) {
    url += '&filter_quantity=' + encodeURIComponent(filter_quantity);
  }

  var filter_deal_status = $('select[name=\'filter_deal_status\']').val();
  
  if (filter_deal_status && filter_deal_status != '*') {
    url += '&filter_deal_status=' + encodeURIComponent(filter_deal_status);
  }
  
  var filter_featured = $('select[name=\'filter_featured\']').val();
  
  if (filter_featured != '*' && filter_featured !=0) {
    url += '&filter_featured=' + encodeURIComponent(filter_featured);
  } 

  var filter_status = $('select[name=\'filter_status\']').val();
  
  if (filter_status != '*') {
    url += '&filter_status=' + encodeURIComponent(filter_status);
  } 

  location = url;
}
//--></script> 
<script type="text/javascript"><!--
$('#form .filter input').keydown(function(e) {
  if (e.keyCode == 13) {
    filter();
  }
});
//--></script> 
<script type="text/javascript"><!--
$('input[name=\'filter_name\']').autocomplete({
  delay: 500,
  source: function(request, response) {
    $.ajax({
      url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
      dataType: 'json',
      success: function(json) {   
        response($.map(json, function(item) {
          return {
            label: item.name,
            value: item.product_id
          }
        }));
      }
    });
  }, 
  select: function(event, ui) {
    $('input[name=\'filter_name\']').val(ui.item.label);
            
    return false;
  },
  focus: function(event, ui) {
        return false;
    }
});

$('input[name=\'filter_model\']').autocomplete({
  delay: 500,
  source: function(request, response) {
    $.ajax({
      url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_model=' +  encodeURIComponent(request.term),
      dataType: 'json',
      success: function(json) {   
        response($.map(json, function(item) {
          return {
            label: item.model,
            value: item.product_id
          }
        }));
      }
    });
  }, 
  select: function(event, ui) {
    $('input[name=\'filter_model\']').val(ui.item.label);
            
    return false;
  },
  focus: function(event, ui) {
        return false;
    }
});
//--></script> 
<?php echo $footer; ?>