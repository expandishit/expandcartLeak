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
      <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><span class="required">*</span> <?php echo $entry_name; ?></td>
            <td><?php foreach ($languages as $language) { ?>
              <input type="text" name="option_description[<?php echo $language['language_id']; ?>][name]" value="<?php echo isset($option_description[$language['language_id']]) ? $option_description[$language['language_id']]['name'] : ''; ?>" />
              <img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /><br />
              <?php if (isset($error_name[$language['language_id']])) { ?>
              <span class="error"><?php echo $error_name[$language['language_id']]; ?></span><br />
              <?php } ?>
              <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_type; ?></td>
            <td><select name="type">
                <optgroup label="<?php echo $text_choose; ?>">
                <?php if ($type == 'select') { ?>
                <option value="select" selected><?php echo $text_select; ?></option>
                <?php } else { ?>
                <option value="select"><?php echo $text_select; ?></option>
                <?php } ?>
                <?php if ($type == 'radio') { ?>
                <option value="radio" selected><?php echo $text_radio; ?></option>
                <?php } else { ?>
                <option value="radio"><?php echo $text_radio; ?></option>
                <?php } ?>
                <?php if ($type == 'checkbox') { ?>
                <option value="checkbox" selected><?php echo $text_checkbox; ?></option>
                <?php } else { ?>
                <option value="checkbox"><?php echo $text_checkbox; ?></option>
                <?php } ?>
                <?php if ($type == 'image') { ?>
                <option value="image" selected><?php echo $text_image; ?></option>
                <?php } else { ?>
                <option value="image"><?php echo $text_image; ?></option>
                <?php } ?>
                </optgroup>
                <optgroup label="<?php echo $text_input; ?>">
                <?php if ($type == 'text') { ?>
                <option value="text" selected><?php echo $text_text; ?></option>
                <?php } else { ?>
                <option value="text"><?php echo $text_text; ?></option>
                <?php } ?>
                <?php if ($type == 'textarea') { ?>
                <option value="textarea" selected><?php echo $text_textarea; ?></option>
                <?php } else { ?>
                <option value="textarea"><?php echo $text_textarea; ?></option>
                <?php } ?>
                </optgroup>
                <optgroup label="<?php echo $text_file; ?>">
                <?php if ($type == 'file') { ?>
                <option value="file" selected><?php echo $text_file; ?></option>
                <?php } else { ?>
                <option value="file"><?php echo $text_file; ?></option>
                <?php } ?>
                </optgroup>
                <optgroup label="<?php echo $text_date; ?>">
                <?php if ($type == 'date') { ?>
                <option value="date" selected><?php echo $text_date; ?></option>
                <?php } else { ?>
                <option value="date"><?php echo $text_date; ?></option>
                <?php } ?>
                <?php if ($type == 'time') { ?>
                <option value="time" selected><?php echo $text_time; ?></option>
                <?php } else { ?>
                <option value="time"><?php echo $text_time; ?></option>
                <?php } ?>
                <?php if ($type == 'datetime') { ?>
                <option value="datetime" selected><?php echo $text_datetime; ?></option>
                <?php } else { ?>
                <option value="datetime"><?php echo $text_datetime; ?></option>
                <?php } ?>
                </optgroup>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_sort_order; ?></td>
            <td><input type="text" name="sort_order" value="<?php echo $sort_order; ?>" size="1" /></td>

              <!-- Product Option Image PRO module << -->
              <?php  if ($poip_installed) {  ?>
          </tr>
            <tr><td><b><?php echo $poip_module_name; ?>:</b>
                </td><td>

                    <table>
                        <?php foreach ($poip_settings_names as $poip_setting_name) { ?>
                        <tr>
                            <td><?php echo ${"entry_".$poip_setting_name}; ?>:</td>
                            <td>
                                <select name="poip_settings[<?php echo $poip_setting_name; ?>]">
                                    <?php
                  $poip_html = "";

                  if (in_array($poip_setting_name."_v0", $poip_settings_values)) {
                    $poip_html .= "<option value=\"0\" ".(isset($poip_saved_settings[$poip_setting_name]) && $poip_saved_settings[$poip_setting_name]==0 ? "selected":"").">".$poip_settings_select_options[0]."</option>";
                                    for ($i=1; $i<9; $i++) {
                                    if (in_array($poip_setting_name."_v".$i, $poip_settings_values)) {
                                    $poip_html .= "<option value=\"".(1+$i)."\" ".(isset($poip_saved_settings[$poip_setting_name]) && $poip_saved_settings[$poip_setting_name]==(1+$i) ? "selected":"").">".${("entry_".$poip_setting_name."_v".$i)}."</option>";
                                    }
                                    }
                                    $poip_html .= "<option value=\"".(1)."\" ".(isset($poip_saved_settings[$poip_setting_name]) && $poip_saved_settings[$poip_setting_name]==1 ? "selected":"").">".${("entry_".$poip_setting_name."_v0")}."</option>";
                                    } else {
                                    foreach ($poip_settings_select_options as $select_value => $select_name) {
                                    $poip_html .= "<option value=\"".$select_value."\" ".(isset($poip_saved_settings[$poip_setting_name]) && $poip_saved_settings[$poip_setting_name]==$select_value ? "selected":"").">".$select_name."</option>";
                                    }
                                    }
                                    echo $poip_html;
                                    ?>

                                </select>
                            </td>
                        </tr>
                        <?php } ?>
                    </table>
                </td>
                <?php  }  ?>
                <!-- >> Product Option Image PRO module -->

          </tr>
        </table>
        <table id="option-value" class="table table-hover dataTable no-footer">
          <thead>
            <tr>
              <td class="left"><span class="required">*</span> <?php echo $entry_option_value; ?></td>
              <td class="left"><?php echo $entry_image; ?></td>
              <td class="right"><?php echo $entry_sort_order; ?></td>
              <td></td>
            </tr>
          </thead>
          <?php $option_value_row = 0; ?>
          <?php foreach ($option_values as $option_value) { ?>
          <tbody id="option-value-row<?php echo $option_value_row; ?>">
            <tr>
              <td class="left"><input type="hidden" name="option_value[<?php echo $option_value_row; ?>][option_value_id]" value="<?php echo $option_value['option_value_id']; ?>" />
                <?php foreach ($languages as $language) { ?>
                <input type="text" name="option_value[<?php echo $option_value_row; ?>][option_value_description][<?php echo $language['language_id']; ?>][name]" value="<?php echo isset($option_value['option_value_description'][$language['language_id']]) ? $option_value['option_value_description'][$language['language_id']]['name'] : ''; ?>" />
                <img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /><br />
                <?php if (isset($error_option_value[$option_value_row][$language['language_id']])) { ?>
                <span class="error"><?php echo $error_option_value[$option_value_row][$language['language_id']]; ?></span>
                <?php } ?>
                <?php } ?></td>
              <td class="left"><div class="image"><img src="<?php echo $option_value['thumb']; ?>" alt="" id="thumb<?php echo $option_value_row; ?>" />
                  <input type="hidden" name="option_value[<?php echo $option_value_row; ?>][image]" value="<?php echo $option_value['image']; ?>" id="image<?php echo $option_value_row; ?>"  />
                  <br />
                  <a onclick="image_upload('image<?php echo $option_value_row; ?>', 'thumb<?php echo $option_value_row; ?>');"><?php echo $text_browse; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('#thumb<?php echo $option_value_row; ?>').attr('src', '<?php echo $no_image; ?>'); $('#image<?php echo $option_value_row; ?>').attr('value', '');"><?php echo $text_clear; ?></a></div></td>
              <td class="right"><input type="text" name="option_value[<?php echo $option_value_row; ?>][sort_order]" value="<?php echo $option_value['sort_order']; ?>" size="1" /></td>
              <td class="left"><a onclick="$('#option-value-row<?php echo $option_value_row; ?>').remove();" class="button btn btn-primary"><?php echo $button_remove; ?></a></td>
            </tr>
          </tbody>
          <?php $option_value_row++; ?>
          <?php } ?>
          <tfoot>
            <tr>
              <td colspan="3"></td>
              <td class="left"><a onclick="addOptionValue();" class="button btn btn-primary"><?php echo $button_add_option_value; ?></a></td>
            </tr>
          </tfoot>
        </table>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
$('select[name=\'type\']').bind('change', function() {
	if (this.value == 'select' || this.value == 'radio' || this.value == 'checkbox' || this.value == 'image') {
		$('#option-value').show();
	} else {
		$('#option-value').hide();
	}
});

var option_value_row = <?php echo $option_value_row; ?>;

function addOptionValue() {
	html  = '<tbody id="option-value-row' + option_value_row + '">';
	html += '  <tr>';	
    html += '    <td class="left"><input type="hidden" name="option_value[' + option_value_row + '][option_value_id]" value="" />';
	<?php foreach ($languages as $language) { ?>
	html += '<input type="text" name="option_value[' + option_value_row + '][option_value_description][<?php echo $language['language_id']; ?>][name]" value="" /> <img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /><br />';
    <?php } ?>
	html += '    </td>';
    html += '    <td class="left"><div class="image"><img src="<?php echo $no_image; ?>" alt="" id="thumb' + option_value_row + '" /><input type="hidden" name="option_value[' + option_value_row + '][image]" value="" id="image' + option_value_row + '" /><br /><a onclick="image_upload(\'image' + option_value_row + '\', \'thumb' + option_value_row + '\');"><?php echo $text_browse; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$(\'#thumb' + option_value_row + '\').attr(\'src\', \'<?php echo $no_image; ?>\'); $(\'#image' + option_value_row + '\').attr(\'value\', \'\');"><?php echo $text_clear; ?></a></div></td>';
	html += '    <td class="right"><input type="text" name="option_value[' + option_value_row + '][sort_order]" value="" size="1" /></td>';
	html += '    <td class="left"><a onclick="$(\'#option-value-row' + option_value_row + '\').remove();" class="button btn btn-primary"><?php echo $button_remove; ?></a></td>';
	html += '  </tr>';	
    html += '</tbody>';
	
	$('#option-value tfoot').before(html);
	
	option_value_row++;
}
//--></script> 
<script type="text/javascript"><!--
function image_upload(field, thumb) {
    $.startImageManager(field, thumb);
};
//--></script> 
<?php echo $footer; ?>