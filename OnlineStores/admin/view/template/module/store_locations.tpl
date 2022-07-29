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
    <div class="buttons">
        <a onclick="$('#form').submit();" class="button btn btn-primary"><span><?php echo $button_save; ?></span></a>
        <a onclick="location = '<?php echo $cancel; ?>';" class="button btn btn-primary"><span><?php echo $button_cancel; ?></span></a>
    </div>
  </div>
  <div class="content">
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
      <table class="form">
        <tr>
          <td><?php echo $store_loc_iWidth; ?></td>
          <td><input type="text" name="store_locations_iWidth" value="<?php echo $store_locations_iWidth; ?>" size="1" /></td>
        </tr>
        <tr>
          <td><?php echo $store_loc_iHeight; ?></td>
          <td><input type="text" name="store_locations_iHeight" value="<?php echo $store_locations_iHeight; ?>" size="1" /></td>
        </tr>
        <tr>
          <td><?php echo $store_loc_mdWidth; ?></td>
          <td><input type="text" name="store_locations_mdWidth" value="<?php echo $store_locations_mdWidth; ?>" size="1" /></td>
        </tr>
        <tr>
          <td><?php echo $store_loc_mdHeight; ?></td>
          <td><input type="text" name="store_locations_mdHeight" value="<?php echo $store_locations_mdHeight; ?>" size="1" /></td>
        </tr>
        <tr>
          <td><?php echo $store_loc_iaWidth; ?></td>
          <td><input type="text" name="store_locations_iaWidth" value="<?php echo $store_locations_iaWidth; ?>" size="1" /></td>
        </tr>
        <tr>
          <td><?php echo $store_loc_iaHeight; ?></td>
          <td><input type="text" name="store_locations_iaHeight" value="<?php echo $store_locations_iaHeight; ?>" size="1" /></td>
        </tr>
        <tr>
          <td><?php echo $store_loc_mWidth; ?></td>
          <td><input type="text" name="store_locations_mWidth" value="<?php echo $store_locations_mWidth; ?>" size="1" /></td>
        </tr>
        <tr>
          <td><?php echo $store_loc_mHeight; ?></td>
          <td><input type="text" name="store_locations_mHeight" value="<?php echo $store_locations_mHeight; ?>" size="1" /></td>
        </tr>
        <tr>
          <td><?php echo $store_loc_txt_per_page; ?></td>
          <td><input type="text" name="store_locations_per_page" value="<?php echo $store_locations_per_page; ?>" size="1" /></td>
        </tr>
        <tr>
          <td><?php echo $store_loc_auto_detect; ?></td>
          <td><input type="checkbox" name="store_locations_auto_detect" <?php echo ($store_locations_auto_detect == true?'checked': '' ); ?> size="1" /></td>
        </tr>
        <tr>
              <td><?php echo $entry_icon; ?></td>
              <td valign="top"><div class="image"><img src="<?php echo $thumb_icon; ?>" alt="" id="thumb_icon" />
                <input type="hidden" name="store_locations_image_icon" value="<?php echo $store_locations_image_icon; ?>" id="store_locations_image_icon" />
                <br /><a onclick="image_upload('store_locations_image_icon', 'thumb_icon');"><?php echo $text_browse; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('#thumb_icon').attr('src', '<?php echo $no_image; ?>'); $('#store_locations_image_icon').attr('value', '');"><?php echo $text_clear; ?></a></div></td>
         </tr>
		 <tr>
              <td><?php echo $entry_health; ?></td>
              <td valign="top"><span id = "diagnoseGoogleSpan"><a id="diagnose_google" class="button btn btn-primary"><span><?php echo $button_diagnose; ?></span></a></span></td>
         </tr>
      </table>
      <table class="table table-hover dataTable no-footer">
          <thead>
          	<tr>
            	<td colspan="4"><div class="buttons"><a onclick="location = '<?php echo $insert; ?>'" class="button btn btn-primary"><?php echo $button_insert; ?></a>&nbsp;<a onclick="$('#form').attr('action','<?php echo $delete; ?>');$('#form').submit();" class="button btn btn-primary"><?php echo $button_delete; ?></a></div>
                </td>
            </tr>
            <tr>
              <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>
              <td class="left"><?php echo $column_name; ?></td>
              <td class="right"><?php echo $column_sort_order; ?></td>
              <td class="right"><?php echo $column_action; ?></td>
            </tr>
          </thead>
          <tbody>
            <?php if ($sLocations) { ?>
            <?php foreach ($sLocations as $location) { ?>
            <tr>
              <td style="text-align: center;"><?php if ($location['selected']) { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $location['location_id']; ?>" checked="checked" />
                <?php } else { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $location['location_id']; ?>" />
                <?php } ?></td>
              <td class="left"><?php echo $location['name']; ?></td>
              <td class="right"><?php echo $location['sort_order']; ?></td>
              <td class="right"><?php foreach ($location['action'] as $action) { ?>
                [ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]
                <?php } ?></td>
            </tr>
            <?php } ?>
            <?php } else { ?>
            <tr>
              <td class="center" colspan="4"><?php echo $text_no_results; ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      <table id="module" class="table table-hover dataTable no-footer">
        <thead>
          <tr>
            <td class="left"><?php echo $entry_limit; ?></td>
            <td class="left"><?php echo $entry_image; ?></td>
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
            <td class="left"><input type="text" name="store_locations_module[<?php echo $module_row; ?>][limit]" value="<?php echo $module['limit']; ?>" size="1" /></td>
            <td class="left"><input type="text" name="store_locations_module[<?php echo $module_row; ?>][image_width]" value="<?php echo $module['image_width']; ?>" size="3" />
              <input type="text" name="store_locations_module[<?php echo $module_row; ?>][image_height]" value="<?php echo $module['image_height']; ?>" size="3" />
              <?php if (isset($error_image[$module_row])) { ?>
              <span class="error"><?php echo $error_image[$module_row]; ?></span>
              <?php } ?></td>
            <td class="left"><select name="store_locations_module[<?php echo $module_row; ?>][layout_id]">
                <?php foreach ($layouts as $layout) { ?>
                <?php if ($layout['layout_id'] == $module['layout_id']) { ?>
                <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
            <td class="left"><select name="store_locations_module[<?php echo $module_row; ?>][position]">
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
            <td class="left"><select name="store_locations_module[<?php echo $module_row; ?>][status]">
                <?php if ($module['status']) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select></td>
            <td class="right"><input type="text" name="store_locations_module[<?php echo $module_row; ?>][sort_order]" value="<?php echo $module['sort_order']; ?>" size="3" /></td>
            <td class="left"><a onclick="$('#module-row<?php echo $module_row; ?>').remove();" class="button btn btn-primary"><span><?php echo $button_remove; ?></span></a></td>
          </tr>
        </tbody>
        <?php $module_row++; ?>
        <?php } ?>
        <tfoot>
          <tr>
            <td colspan="6"></td>
            <td class="left"><a onclick="addModule();" class="button btn btn-primary"><span><?php echo $button_add_module; ?></span></a></td>
          </tr>
        </tfoot>
      </table>
    </form>
  </div>
</div>
<script type="text/javascript"><!--
var module_row = <?php echo $module_row; ?>;

function addModule() {	
	html  = '<tbody id="module-row' + module_row + '">';
	html += '  <tr>';
	html += '    <td class="left"><input type="text" name="store_locations_module[' + module_row + '][limit]" value="5" size="1" /></td>';
	html += '    <td class="left"><input type="text" name="store_locations_module[' + module_row + '][image_width]" value="80" size="3" /> <input type="text" name="store_locations_module[' + module_row + '][image_height]" value="80" size="3" /></td>';		
	html += '    <td class="left"><select name="store_locations_module[' + module_row + '][layout_id]">';
	<?php foreach ($layouts as $layout) { ?>
	html += '      <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>';
	<?php } ?>
	html += '    </select></td>';
	html += '    <td class="left"><select name="store_locations_module[' + module_row + '][position]">';
	html += '      <option value="content_top"><?php echo $text_content_top; ?></option>';
	html += '      <option value="content_bottom"><?php echo $text_content_bottom; ?></option>';
	html += '      <option value="column_left"><?php echo $text_column_left; ?></option>';
	html += '      <option value="column_right"><?php echo $text_column_right; ?></option>';
	html += '    </select></td>';
	html += '    <td class="left"><select name="store_locations_module[' + module_row + '][status]">';
    html += '      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
    html += '      <option value="0"><?php echo $text_disabled; ?></option>';
    html += '    </select></td>';
	html += '    <td class="right"><input type="text" name="store_locations_module[' + module_row + '][sort_order]" value="" size="3" /></td>';
	html += '    <td class="left"><a onclick="$(\'#module-row' + module_row + '\').remove();" class="button btn btn-primary"><span><?php echo $button_remove; ?></span></a></td>';
	html += '  </tr>';
	html += '</tbody>';
	
	$('#module tfoot').before(html);
	
	module_row++;
}

$('#diagnose_google').click(function() {
		$.ajax({
			url: 'index.php?route=module/store_locations/diagnoseGoogleAPI&token=<?php echo $token; ?>',
			dataType: 'json',
			beforeSend: function() {
				$('#diagnoseGoogleSpan').html("<span>Working...</span>");
			},
			complete: function() {
	
			},
			success: function(data) {
				retRes = '<span class="help"><b>' + data.reason + '</b></span>';
				$('#diagnoseGoogleSpan').html(retRes);
			}
		});
	});
//--></script>
<script type="text/javascript"><!--
function image_upload(field, thumb) {
    $.startImageManager(field, thumb);
};
//--></script> 
<?php echo $footer; ?>