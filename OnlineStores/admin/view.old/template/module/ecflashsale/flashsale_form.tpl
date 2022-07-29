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

  <div class="box">
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a><a onclick="$('#action').val('save_stay');$('#form').submit();" class="button btn btn-primary"><?php echo $this->language->get("button_save_stay"); ?></a><a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <div id="tabs" class="htabs">
        <a href="#tab-general"><?php echo $tab_general; ?></a>
         <a href="#tab-discount-setting"><?php echo $this->language->get("tab_discount_setting"); ?></a>
        <a href="#tab-data"><?php echo $tab_data; ?></a>
        <a href="#tab-design"><?php echo $tab_design; ?></a></div>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <input type="hidden" name="action" id="action" value=""/>
        <input type="hidden" name="ecflashsale_id" value="<?php echo isset($ecflashsale_id)?$ecflashsale_id:0;?>"/>
        <div id="tab-general">
          <div id="languages" class="htabs">
            <?php foreach ($languages as $language) { ?>
            <a href="#language<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
            <?php } ?>
          </div>
          <?php foreach ($languages as $language) { ?>
          <div id="language<?php echo $language['language_id']; ?>">
            <table class="form">
              <tr>
                <td><span class="required">*</span> <?php echo $entry_name; ?></td>
                <td><input type="text" name="flashsale_description[<?php echo $language['language_id']; ?>][name]" size="100" value="<?php echo isset($flashsale_description[$language['language_id']]) ? $flashsale_description[$language['language_id']]['name'] : ''; ?>" />
                  <?php if (isset($error_name[$language['language_id']])) { ?>
                  <span class="error"><?php echo $error_name[$language['language_id']]; ?></span>
                  <?php } ?></td>
              </tr>
              <tr>
                <td><?php echo $entry_meta_description; ?></td>
                <td><textarea name="flashsale_description[<?php echo $language['language_id']; ?>][meta_description]" cols="40" rows="5"><?php echo isset($flashsale_description[$language['language_id']]) ? $flashsale_description[$language['language_id']]['meta_description'] : ''; ?></textarea></td>
              </tr>
              <tr>
                <td><?php echo $entry_meta_keyword; ?></td>
                <td><textarea name="flashsale_description[<?php echo $language['language_id']; ?>][meta_keyword]" cols="40" rows="5"><?php echo isset($flashsale_description[$language['language_id']]) ? $flashsale_description[$language['language_id']]['meta_keyword'] : ''; ?></textarea></td>
              </tr>
              <tr>
                <td><?php echo $entry_description; ?></td>
                <td><textarea name="flashsale_description[<?php echo $language['language_id']; ?>][description]" id="description<?php echo $language['language_id']; ?>"><?php echo isset($flashsale_description[$language['language_id']]) ? $flashsale_description[$language['language_id']]['description'] : ''; ?></textarea></td>
              </tr>
              <tr style="display:none">
                  <td><?php echo $this->language->get("entry_flashsale_tags"); ?></td>
                  <td><input type="text" name="flashsale_description[<?php echo $language['language_id']; ?>][tag]" value="<?php echo isset($flashsale_description[$language['language_id']]) ? $flashsale_description[$language['language_id']]['tag'] : ''; ?>" size="80"/></td>
                </tr>
            </table>
          </div>
          <?php } ?>
          
        </div>
        <div id="tab-discount-setting">
                <div class="tab-inner">
                  <table class="form">

                        <tr>
                          <td><?php echo $this->language->get("entry_date_start"); ?></td>
                          <td><input type="text" name="date_start" value="<?php echo isset($date_start)?$date_start:""; ?>" size="20" class="date" /></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_date_end"); ?></td>
                          <td><input type="text" name="date_end" value="<?php echo isset($date_end)?$date_end:""; ?>" size="20" class="date" /></td>
                        </tr>
                          <tr>
                          <td><?php echo $this->language->get("entry_discount"); ?></td>
                          <td><input type="text" name="discount_percent" value="<?php echo isset($discount_percent)?$discount_percent:"0"; ?>" size="12"/>% or <input type="text" name="discount_amount" value="<?php echo isset($discount_amount)?$discount_amount:"0"; ?>" size="12"/><?php echo isset($currency)?$currency:"$";?></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_customer_group"); ?></td>
                          <td><select name="customer_group_id[]" size="10" style="width:200px;" multiple="multiple">
                            <?php
                              if(!empty($customer_groups)){
                                foreach($customer_groups as $group){
                                  if(isset($customer_group_id) && in_array($group["customer_group_id"], $customer_group_id)){
                                  ?>
                                  <option value="<?php echo $group["customer_group_id"]; ?>" selected="selected"><?php echo $group["name"];?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $group["customer_group_id"]; ?>"><?php echo $group["name"];?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_apply_for"); ?></td>
                          <td><select class="data_source" name="apply_for" onchange="showDataSource(this.value,0)">
                            <?php
                              if(!empty($sources)){
                                foreach($sources as $key=>$val){
                                  if(isset($apply_for) && $key == $apply_for){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select>
                           <script type="text/javascript">
                            jQuery(function(){
                              showDataSource('<?php echo isset($apply_for)?$apply_for:'category'; ?>',0);
                            })
                          </script>
                        </td>
                        </tr>
                        <tr class="source_category_0">
                          <td><?php echo $this->language->get("entry_category"); ?></td>
                          <td><select name="category[]" multiple="multiple" size="10">
                            <?php
                              if(!empty($categories)){
                                foreach($categories as $category){
                                  if(isset($category_ids) && in_array($category["category_id"], $category_ids)){
                                  ?>
                                  <option value="<?php echo $category["category_id"]; ?>" selected="selected"><?php echo $category["name"];?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $category["category_id"]; ?>"><?php echo $category["name"];?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr class="source_product_0">
                          <td><?php echo $this->language->get("entry_products"); ?></td>
                          <td>
                            <input type="text" id="product_autocomplete_0" name="product_name" value="" size="40" placeholder="<?php echo $this->language->get("text_input_product_name");?>"/><br/><br/>
                            <div class="scrollbox" id="product_0">
                            <?php $class = 'odd'; ?>
                            <?php if(isset($products) && !empty($products)){ ?>
                              <?php foreach ($products as $product) { ?>
                              <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                              <div id="product_0_<?php echo $product['product_id']; ?>" class="<?php echo $class; ?>"><?php echo $product['name']; ?><img src="view/image/delete.png" />
                                <input type="hidden" name="products[]" value="<?php echo $product['product_id']; ?>" />
                              </div>
                              <?php } ?>
                            <?php } ?>
                          </div>
                          <script type="text/javascript">
                            jQuery(function(){
                              initAutocomplete(0);
                            })
                          </script>
                          </td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_clear_deals"); ?></td>
                          <td><select name="clear_deal">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($clear_deal) && $key == $clear_deal){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <!--
                         <tr>
                          <td></td>
                          <td><a onclick="clearDeals()" class="button btn btn-primary"><?php echo $this->language->get("text_clear_all_deals");?></a></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_coupon_code"); ?></td>
                          <td><input type="text" name="coupon_code" value="<?php echo isset($coupon_code)?$coupon_code:""; ?>" size="20" /></td>
                        </tr>-->
                  </table>
                </div>
            </div>
        <div id="tab-data">
          <table class="form">
            <tr>
              <td><?php echo $entry_store; ?></td>
              <td><div class="scrollbox">
                  <?php $class = 'even'; ?>
                  <div class="<?php echo $class; ?>">
                    <?php if (in_array(0, $flashsale_store)) { ?>
                    <input type="checkbox" name="flashsale_store[]" value="0" checked="checked" />
                    <?php echo $text_default; ?>
                    <?php } else { ?>
                    <input type="checkbox" name="flashsale_store[]" value="0" />
                    <?php echo $text_default; ?>
                    <?php } ?>
                  </div>
                  <?php foreach ($stores as $store) { ?>
                  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                  <div class="<?php echo $class; ?>">
                    <?php if (in_array($store['store_id'], $flashsale_store)) { ?>
                    <input type="checkbox" name="flashsale_store[]" value="<?php echo $store['store_id']; ?>" checked="checked" />
                    <?php echo $store['name']; ?>
                    <?php } else { ?>
                    <input type="checkbox" name="flashsale_store[]" value="<?php echo $store['store_id']; ?>" />
                    <?php echo $store['name']; ?>
                    <?php } ?>
                  </div>
                  <?php } ?>
                </div></td>
            </tr>
            <tr>
              <td><?php echo $entry_keyword; ?></td>
              <td><input type="text" name="keyword" value="<?php echo $keyword; ?>" /></td>
            </tr>
            <tr>
              <td><?php echo $entry_image; ?></td>
              <td valign="top"><div class="image"><img src="<?php echo $thumb; ?>" alt="" id="thumb" />
                  <input type="hidden" name="image" value="<?php echo $image; ?>" id="image" />
                  <br />
                  <a onclick="image_upload('image', 'thumb');"><?php echo $text_browse; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('#thumb').attr('src', '<?php echo $no_image; ?>'); $('#image').attr('value', '');"><?php echo $text_clear; ?></a></div></td>
            </tr>
            
            <tr>
              <td><?php echo $entry_sort_order; ?></td>
              <td><input type="text" name="sort_order" value="<?php echo $sort_order; ?>" size="1" /></td>
            </tr>

            <tr>
              <td><?php echo $entry_status; ?></td>
              <td><select name="status">
                  <?php if ($status) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select></td>
            </tr>
            <tr>
              <td><?php echo $this->language->get("entry_featured"); ?></td>
              <td><select name="featured">
                  <?php if ($featured) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select></td>
            </tr>
            <tr>
                  <td><?php echo $this->language->get("entry_hits"); ?></td>
                  <td><input type="text" name="hits" value="<?php echo isset($hits)?$hits:"0"; ?>" size="10"/></td>
                </tr>
          </table>
        </div>
        <div id="tab-design">
          <table class="table table-hover dataTable no-footer">
            <thead>
              <tr>
                <td class="left"><?php echo $entry_store; ?></td>
                <td class="left"><?php echo $entry_layout; ?></td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="left"><?php echo $text_default; ?></td>
                <td class="left"><select name="flashsale_layout[0][layout_id]">
                    <option value=""></option>
                    <?php foreach ($layouts as $layout) { ?>
                    <?php if (isset($flashsale_layout[0]) && $flashsale_layout[0] == $layout['layout_id']) { ?>
                    <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                    <?php } ?>
                    <?php } ?>
                  </select></td>
              </tr>
            </tbody>
            <?php foreach ($stores as $store) { ?>
            <tbody>
              <tr>
                <td class="left"><?php echo $store['name']; ?></td>
                <td class="left"><select name="flashsale_layout[<?php echo $store['store_id']; ?>][layout_id]">
                    <option value=""></option>
                    <?php foreach ($layouts as $layout) { ?>
                    <?php if (isset($flashsale_layout[$store['store_id']]) && $flashsale_layout[$store['store_id']] == $layout['layout_id']) { ?>
                    <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                    <?php } ?>
                    <?php } ?>
                  </select></td>
              </tr>
            </tbody>
            <?php } ?>
          </table>
        </div>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script> 
<script type="text/javascript"><!--
<?php foreach ($languages as $language) { ?>
CKEDITOR.replace('description<?php echo $language['language_id']; ?>', {
  filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
});
<?php } ?>
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
//--></script> 
<script type="text/javascript"><!--

function importDefaultDescription(module_index, language_id){
  $("#description-"+module_index+"-"+language_id).html("default description here!");
  var html = '<?php echo isset($default_description)?$default_description:''; ?>';
  if (CKEDITOR.instances["description-"+module_index+"-"+language_id]) {
    CKEDITOR.instances["description-"+module_index+"-"+language_id].setData(html);
  }
}
function showDataSource(group_name,module_index){
  $(".source_category_"+module_index).first().hide();
  $(".source_product_"+module_index).first().hide();

  if($(".source_"+group_name+"_"+module_index)){
    $(".source_"+group_name+"_"+module_index).first().show();
  }
}
function clearDeals(){
    var r=confirm("<?php echo $this->language->get("text_warning_clear_deals");?>")
    if (r==true)
      {
        $("#action").val("clear_deal");
        $("#form").submit();
      }
  }
function initAutocomplete(module_index){
   
    $('#product_autocomplete_'+module_index).autocomplete({
            delay: 0,
            source: function(request, response) {
              $.ajax({
                url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
                dataType: 'json',
                data: {token: '<?php echo $token; ?>', filter_name: encodeURIComponent(request.term)},
                type: 'POST',
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
              $('#product_'+module_index+'_' + ui.item.value).remove();
              
              $('#product_'+module_index).append('<div id="product_'+module_index+'_' + ui.item.value + '">' + ui.item.label + '<img src="view/image/delete.png" /><input type="hidden" name="products[]" value="' + ui.item.value + '" /></div>');

              $('#product_'+module_index+' div:odd').attr('class', 'odd');
              $('#product_'+module_index+' div:even').attr('class', 'even');
                  
              return false;
            }
          });

          $('#product_'+module_index+' div img').live('click', function() {
            $(this).parent().remove();
            
            $('#product_'+module_index+' div:odd').attr('class', 'odd');
            $('#product_'+module_index+' div:even').attr('class', 'even'); 
          });
  }
  $('.date').datetimepicker({dateFormat: 'yy-mm-dd'});
//--></script>
<script type="text/javascript"><!--
    function image_upload(field, thumb) {
        $.startImageManager(field, thumb);
    };
    //--></script>
<script type="text/javascript"><!--
$('#tabs a').tabs(); 
$('#languages a').tabs();
//--></script> 
<?php echo $footer; ?>