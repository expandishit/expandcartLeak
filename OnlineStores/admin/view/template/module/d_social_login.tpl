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
            <div class="buttons"><a onclick="saveAndStay();" class="button btn btn-primary"><?php echo $button_save_and_stay; ?></a><a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
        </div>
    <div class="content">

        <div class="htabs" id="tabs_htabs">
          <?php if(!IS_NEXTGEN_FRONT) { ?>
          <a href="#modules"><span class="icon-puzzle"></span><?php echo $tab_modules; ?></a>
          <?php } ?>
          <a href="#setting_basic"><span class="icon-settings"></span><?php echo $tab_basic; ?></a>
          <a href="#setting_button"><span class="icon-star-empty"></span><?php echo $tab_buttons; ?></a>
          <a href="#setting_field"><span class="icon-drag"></span><?php echo $tab_fields; ?></a>
          <a href="#setting_provider"><span class="icon-code-closed"></span><?php echo $tab_providers; ?></a>
        </div>

        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">

          <?php if(!IS_NEXTGEN_FRONT) { ?>
          <div id="modules" class="htab-content">
            <div class="tab-body">


            <table id="module" class="table table-hover dataTable no-footer">
              <thead>
                <tr>
                  <td class="left"><?php echo $entry_size; ?></td>
                  <td class="left"><?php echo $entry_layout; ?></td>
                  <td class="left"><?php echo $entry_position; ?></td>
                  <td class="left"><?php echo $entry_status; ?></td>
                  <td class="right"><?php echo $entry_sort_order; ?></td>
                  <td></td>
                </tr>
              </thead>
              <?php $module_row = 0; ?>
              <?php foreach ($modules as $module) { ?>
              <tbody id="module-row<?php echo $module_row; ?>">
                <tr>
                  <td class="left"><select name="d_social_login_module[<?php echo $module_row; ?>][size]">
                      <?php if ($module['size'] == 'icons') { ?>
                      <option value="icons" selected="selected"><?php echo $text_icons; ?></option>
                      <?php } else { ?>
                      <option value="icons"><?php echo $text_icons; ?></option>
                      <?php } ?>
                      <?php if ($module['size'] == 'small') { ?>
                      <option value="small" selected="selected"><?php echo $text_small; ?></option>
                      <?php } else { ?>
                      <option value="small"><?php echo $text_small; ?></option>
                      <?php } ?>
                      <?php if ($module['size'] == 'medium') { ?>
                      <option value="medium" selected="selected"><?php echo $text_medium; ?></option>
                      <?php } else { ?>
                      <option value="medium"><?php echo $text_medium; ?></option>
                      <?php } ?>
                      <?php if ($module['size'] == 'large') { ?>
                      <option value="large" selected="selected"><?php echo $text_large; ?></option>
                      <?php } else { ?>
                      <option value="large"><?php echo $text_large; ?></option>
                      <?php } ?>
                      <?php if ($module['size'] == 'huge') { ?>
                      <option value="huge" selected="selected"><?php echo $text_huge; ?></option>
                      <?php } else { ?>
                      <option value="huge"><?php echo $text_huge; ?></option>
                      <?php } ?>
                    </select></td>
                  <td class="left"><select name="d_social_login_module[<?php echo $module_row; ?>][layout_id]">
                      <?php foreach ($layouts as $layout) { ?>
                      <?php if ($layout['layout_id'] == $module['layout_id']) { ?>
                      <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                      <?php } else { ?>
                      <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                      <?php } ?>
                      <?php } ?>
                    </select></td>
                  <td class="left"><select name="d_social_login_module[<?php echo $module_row; ?>][position]">
                      <?php if ($module['position'] == 'content_top') { ?>
                      <option value="content_top" selected="selected"><?php echo $text_content_top; ?></option>
                      <?php } else { ?>
                      <option value="content_top"><?php echo $text_content_top; ?></option>
                      <?php } ?>
                      <?php if ($module['position'] == 'content_bottom') { ?>
                      <option value="content_bottom" selected="selected"><?php echo $text_content_bottom; ?></option>
                      <?php } else { ?>
                      <option value="content_bottom"><?php echo $text_content_bottom; ?></option>
                      <?php } ?>
                      <?php if ($module['position'] == 'column_left') { ?>
                      <option value="column_left" selected="selected"><?php echo $text_column_left; ?></option>
                      <?php } else { ?>
                      <option value="column_left"><?php echo $text_column_left; ?></option>
                      <?php } ?>
                      <?php if ($module['position'] == 'column_right') { ?>
                      <option value="column_right" selected="selected"><?php echo $text_column_right; ?></option>
                      <?php } else { ?>
                      <option value="column_right"><?php echo $text_column_right; ?></option>
                      <?php } ?>
                    </select></td>
                  <td class="left"><select name="d_social_login_module[<?php echo $module_row; ?>][status]">
                      <?php if ($module['status']) { ?>
                      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                      <option value="0"><?php echo $text_disabled; ?></option>
                      <?php } else { ?>
                      <option value="1"><?php echo $text_enabled; ?></option>
                      <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                      <?php } ?>
                    </select></td>
                  <td class="right"><input type="text" name="d_social_login_module[<?php echo $module_row; ?>][sort_order]" value="<?php echo $module['sort_order']; ?>" size="3" /></td>
                  <td class="left"><a onclick="$('#module-row<?php echo $module_row; ?>').remove();" class="button btn btn-primary"><?php echo $button_remove; ?></a></td>
                </tr>
              </tbody>
              <?php $module_row++; ?>
              <?php } ?>
              <tfoot>
                <tr>
                  <td colspan="5"></td>
                  <td class="left"><a onclick="addModule();" class="button btn btn-primary"><?php echo $button_add_module; ?></a></td>
                </tr>
              </tfoot>
            </table>


            </div>
          </div>
          <?php } ?>

      <div id="setting_basic" class="htab-content">
        <div class="tab-body table-form">


          <div class="row">
            <div class="col-md-3 th">
              <div class="wrap-5"><?php echo $text_customer_group; ?></div>
            </div>
            <div class="col-md-9">
              <div class="wrap-5">
                <select name="d_social_login_settings[customer_group]">
                  <?php foreach($customer_groups as $customer_group) {?>
                    <option value="<?php echo $customer_group['customer_group_id']; ?>" <?php echo ($setting['customer_group'] == $customer_group['customer_group_id']) ? 'selected="selected"' : ''; ?> ><?php echo $customer_group['name']; ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 th">
              <div class="wrap-5"><?php echo $text_newsletter; ?></div>
            </div>
            <div class="col-md-9">
              <div class="wrap-5">
                <input type="hidden" name="d_social_login_settings[newsletter]" value="0" />
                <input type="checkbox" name="d_social_login_settings[newsletter]" <?php echo ($setting['newsletter'])? 'checked="checked"':'';?> value="1" id="d_social_login_settings_newsletter"/>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 th">
              <div class="wrap-5"><?php echo $text_return_page_url; ?></div>
            </div>
            <div class="col-md-9">
              <div class="wrap-5">
                <input type="text" id="input_return_page_url" name="<?php echo $id;?>_settings[return_page_url]" value="<?php echo $setting['return_page_url']; ?>" style="width:100%;" />
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 th">
              <div class="wrap-5"><?php echo $text_background_img; ?></div>
            </div>
            <div class="col-md-9">
              <div class="wrap-5">

                <div class="image"><img src="<?php echo $background_img_thumb; ?>" alt="" id="background_img_thumb" />
                  <input type="hidden" id="background_img" name="d_social_login_settings[background_img]" value="<?php echo $setting['background_img']; ?>" id="logo" />
                  <br />
                  <a onclick="image_upload('background_img', 'background_img_thumb');"><?php echo $text_browse; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('#background_img_thumb').attr('src', '<?php echo $no_image; ?>'); $('#background_img').attr('value', '');"><?php echo $text_clear; ?></a></div>
              </div>
            </div>
          </div>

            <?php if(IS_NEXTGEN_FRONT) { ?>
            <div class="row">
                <div class="col-md-3 th">
                    <div class="wrap-5"><?php echo $entry_status; ?></div>
                </div>
                <div class="col-md-9">
                    <div class="wrap-5">
                        <select name="d_social_login_settings[status]" id="status">
                            <?php if ($setting['status']) { ?>
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
            <?php } ?>

          <script type="text/javascript"><!--
            function image_upload(field, thumb) {
                $.startImageManager(field, thumb);
            };
            //--></script>


        </div>
      </div>

      <div id="setting_button" class="htab-content">
        <div class="tab-body table-form">

          <div class="row">
            <div class="col-md-3 th"><div class="wrap-5"><?php echo $text_sort_order; ?></div></div>
            <div class="col-md-9">
              <div id="providers_sort_order_form" class="sortable atab wrap-5">
              <?php foreach($providers as $key => $provider) { ?>
                <div class="row atab-item sort-item">
                  <div class="col-sm-3">
                  <input type="hidden" name="d_social_login_settings[providers][<?php echo $key; ?>][enabled]" value="0" />
                  <input type="checkbox" name="d_social_login_settings[providers][<?php echo $key; ?>][enabled]" <?php echo ($provider['enabled'])? 'checked="checked"':'';?> value="1" id="d_social_login_settings_providers_<?php echo $key; ?>_enabled"/>
                  <label for="d_social_login_settings_providers_<?php echo $key; ?>_enabled"><i class="<?php echo $provider['icon']; ?>"></i> <?php echo ${'text_'.$provider['id']}; ?></label>
                  <input type="hidden" name="d_social_login_settings[providers][<?php echo $key; ?>][sort_order]" class="sort-value" value="<?php echo $provider['sort_order']; ?>" /><span class="dsl-icon-<?php echo $provider['id']; ?>"></span>
                  </div>
                  <div class="col-sm-4">
                    <label><?php echo $text_background_color; ?></label>
                    <input type="hidden" name="d_social_login_settings[providers][<?php echo $key; ?>][background_color]" class="color-picker" value="<?php echo $provider['background_color']; ?>" />
                    <label><?php echo $text_background_color_active; ?></label>
                    <input type="hidden" name="d_social_login_settings[providers][<?php echo $key; ?>][background_color_active]" class="color-picker" value="<?php echo $provider['background_color_active']; ?>" />
                  </div>
                  <div class="col-sm-4">
                    <label><?php echo $text_icons; ?></label>
                    <input type="text" name="d_social_login_settings[providers][<?php echo $key; ?>][icon]"  value="<?php echo $provider['icon']; ?>" />
                  </div>
                  <span class="icon-drag"></span>
                </div>
              <?php } ?>
              </div>
              <script>
                $(function() {
                    $( "#providers_sort_order_form" ).sortable({
                      axis: "y",
                      placeholder: "ui-state-highlight",
                      distance: 5,
                      stop: function( event, ui ) {
                        ui.item.children( ".sort-item" ).triggerHandler( "focusout" );
                    $(this).find(".sort-item").each(function(i, el){
                      $(this).find(".sort-value").val($(el).index())
                    });
                      }
                    });
                });
              $(function() {
                  $('.color-picker').colorpicker({
                      parts: 'simple',
                      showOn: 'both',
                      buttonColorize: true,
                      showNoneButton: true,
                      alpha: true,
                      colorFormat: '#HEX'
                  });
              });

              </script>
            </div>
          </div>

        </div>
      </div>

      <div id="setting_field" class="htab-content">
        <div class="tab-body table-form">

          <div class="row">
            <div class="col-md-3 th">
              <div class="wrap-5"><?php echo $text_fields_sort_order; ?></div>
            </div>
            <div class="col-md-9">
                <div id="fields_sort_order_form" class="sortable atab wrap-5">
                
                <?php foreach($fields as $key => $field) { ?>
                <div class="row atab-item sort-item">
                  <div class="col-sm-5">
                  <input type="hidden" name="d_social_login_settings[fields][<?php echo $key; ?>][enabled]" value="0" />
                  <input type="checkbox" name="d_social_login_settings[fields][<?php echo $key; ?>][enabled]" <?php echo ($field['enabled'])? 'checked="checked"':'';?> value="1" id="d_social_login_settings_fields_<?php echo $key; ?>_enabled" />
                  <label for="d_social_login_settings_fields_<?php echo $key; ?>_enabled"><?php echo ${'text_'.$key}; ?></label>
                  <input type="hidden" name="d_social_login_settings[fields][<?php echo $key; ?>][sort_order]" class="sort-value" value="<?php echo $field['sort_order']; ?>" />
                  <input type="hidden" name="d_social_login_settings[fields][<?php echo $key; ?>][type]"  value="<?php echo $field['type']; ?>" />
                  <input type="hidden" name="d_social_login_settings[fields][<?php echo $key; ?>][id]"   value="<?php echo $key; ?>" />
                  </div>
                  <?php if(isset($field['mask'])) {?>
                    <label class="col-sm-2">
                      <?php echo $text_mask; ?>
                    </label>
                    <div class="col-sm-3"><input type="text" name="d_social_login_settings[fields][<?php echo $key; ?>][mask]" value="<?php echo $field['mask']; ?>" />
                    </div>
                  <?php } ?>
                  <span class="icon-drag"></span>
                </div>
               <?php } ?>
             </div>
             <script>
                $(function() {
                    $( "#fields_sort_order_form" ).sortable({
                      axis: "y",
                      placeholder: "ui-state-highlight",
                      distance: 5,
                      stop: function( event, ui ) {
                        ui.item.children( ".sort-item" ).triggerHandler( "focusout" );
                    $(this).find(".sort-item").each(function(i, el){
                      $(this).find(".sort-value").val($(el).index())
                    });
                      }
                    });
                });
              </script>
            </div>
          </div>

        </div>
      </div>

      <div id="setting_provider" class="htab-content">
        <div class="tab-body table-form">

          <?php foreach($providers as $key => $provider) { ?>
            <div class="row">
              <h3><i class="<?php echo $provider['icon']; ?>"></i> <?php echo ${'text_'.$provider['id']}. ' '.$text_app_settings ?><input type="hidden" name="d_social_login_settings[providers][<?php echo $key; ?>][id]" value="<?php echo $provider['id']; ?>" /></h3>
            </div>
            <?php foreach($provider['keys'] as $k => $v){ ?>
            <div class="row">
              <div class="col-sm-2 th">
                <div class="wrap-5"><?php echo ${'text_app_'.$k}; ?></div>
              </div>
              <div class="col-sm-5">
                <div class="wrap-5">
                  <input type="text" name="d_social_login_settings[providers][<?php echo $key; ?>][keys][<?php echo $k; ?>]" value="<?php echo $v; ?>" style="width:100%;"/>          
                </div>
              </div>
            </div>
            <?php } ?>
            <?php if(isset($provider['scope'])){ ?>
              <div class="row">
              
                  <div class="col-sm-2 th">
                    <div class="wrap-5"><?php echo $text_app_scope; ?></div>
                  </div>
         
                <div class="col-sm-5">
                  <div class="wrap-5">
                    <input type="text" name="<?php echo $id;?>_settings[providers][<?php echo $key; ?>][scope]" value="<?php echo $provider['scope']; ?>" class="form-control" style="width:100%;" />          
                  </div>
                </div>
              </div>
              <?php } ?>
          <?php } ?>
          
        </div>
      </div>



      <script>
          $('#tabs_htabs a').tabs();
      </script>
      <!-- htabs-->
      </form>
    </div>
  </div><!-- .box -->
</div><!-- #content -->

<script type="text/javascript"><!--
var module_row = <?php echo $module_row; ?>;

function addModule() {  
  html  = '<tbody id="module-row' + module_row + '">';
  html += '  <tr>';
  html += '    <td class="left"><select name="d_social_login_module[' + module_row + '][size]">';
  html += '      <option value="icons"><?php echo $text_icons; ?></option>';
  html += '      <option value="small"><?php echo $text_small; ?></option>';
  html += '      <option value="medium"><?php echo $text_medium; ?></option>';
  html += '      <option value="larg"><?php echo $text_large; ?></option>';
  html += '      <option value="huge"><?php echo $text_huge; ?></option>';
  html += '    </select></td>';
  html += '    <td class="left"><select name="d_social_login_module[' + module_row + '][layout_id]">';
  <?php foreach ($layouts as $layout) { ?>
  html += '      <option value="<?php echo $layout['layout_id']; ?>"><?php echo addslashes($layout['name']); ?></option>';
  <?php } ?>
  html += '    </select></td>';
  html += '    <td class="left"><select name="d_social_login_module[' + module_row + '][position]">';
  html += '      <option value="content_top"><?php echo $text_content_top; ?></option>';
  html += '      <option value="content_bottom"><?php echo $text_content_bottom; ?></option>';
  html += '      <option value="column_left"><?php echo $text_column_left; ?></option>';
  html += '      <option value="column_right"><?php echo $text_column_right; ?></option>';
  html += '    </select></td>';
  html += '    <td class="left"><select name="d_social_login_module[' + module_row + '][status]">';
    html += '      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
    html += '      <option value="0"><?php echo $text_disabled; ?></option>';
    html += '    </select></td>';
  html += '    <td class="right"><input type="text" name="d_social_login_module[' + module_row + '][sort_order]" value="" size="3" /></td>';
  html += '    <td class="left"><a onclick="$(\'#module-row' + module_row + '\').remove();" class="button btn btn-primary"><?php echo $button_remove; ?></a></td>';
  html += '  </tr>';
  html += '</tbody>';
  
  $('#module tfoot').before(html);
  
  module_row++;
}
//--></script> 
<?php echo $footer; ?>