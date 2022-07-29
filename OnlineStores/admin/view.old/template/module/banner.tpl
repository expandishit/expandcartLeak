<?php echo $header; ?>

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
<?php if ($error) { ?>
<script>
    var notificationString = '<?php echo $error; ?>';
    var notificationType = 'warning';
</script>
<?php } ?>

<div class = "row">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-12">
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

                <h1><?php echo $heading_title; ?></h1>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="main-box clearfix">
                    <div class="tabs-wrapper profile-tabs">
                        <ul class="nav nav-tabs">
                            <li><a href="<?php echo $banners_URL; ?>"><?php echo $text_banners; ?></a></li>
                            <li class="active"><a href="#tab-bannerpos" data-toggle="tab"><?php echo $text_bannerpos; ?></a></li>
                        </ul>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="tab-bannerpos">
                            <div class="heading">
                              <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a></div>
                            </div>
                            <div class="content">
                              <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                                <table id="module" class="table table-hover dataTable no-footer">
                                  <thead>
                                    <tr>
                                      <td class="left"><?php echo $entry_banner; ?></td>
                                      <td class="left"><span class="required">*</span> <?php echo $entry_dimension; ?></td>
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
                                      <td class="left"><select name="banner_module[<?php echo $module_row; ?>][banner_id]">
                                          <?php foreach ($banners as $banner) { ?>
                                          <?php if ($banner['banner_id'] == $module['banner_id']) { ?>
                                          <option value="<?php echo $banner['banner_id']; ?>" selected="selected"><?php echo $banner['name']; ?></option>
                                          <?php } else { ?>
                                          <option value="<?php echo $banner['banner_id']; ?>"><?php echo $banner['name']; ?></option>
                                          <?php } ?>
                                          <?php } ?>
                                        </select></td>
                                      <td class="left"><input type="text" name="banner_module[<?php echo $module_row; ?>][width]" value="<?php echo $module['width']; ?>" size="3" />
                                        <input type="text" name="banner_module[<?php echo $module_row; ?>][height]" value="<?php echo $module['height']; ?>" size="3" />
                                        <?php if (isset($error_dimension[$module_row])) { ?>
                                        <span class="error"><?php echo $error_dimension[$module_row]; ?></span>
                                        <?php } ?></td>
                                      <td class="left"><select name="banner_module[<?php echo $module_row; ?>][layout_id]">
                                          <?php foreach ($layouts as $layout) { ?>
                                          <?php if ($layout['layout_id'] == $module['layout_id']) { ?>
                                          <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                                          <?php } else { ?>
                                          <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                                          <?php } ?>
                                          <?php } ?>
                                        </select></td>
                                      <td class="left"><select name="banner_module[<?php echo $module_row; ?>][position]">
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
                                      <td class="left"><select name="banner_module[<?php echo $module_row; ?>][status]">
                                          <?php if ($module['status']) { ?>
                                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                          <option value="0"><?php echo $text_disabled; ?></option>
                                          <?php } else { ?>
                                          <option value="1"><?php echo $text_enabled; ?></option>
                                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                          <?php } ?>
                                        </select></td>
                                      <td class="right"><input type="text" name="banner_module[<?php echo $module_row; ?>][sort_order]" value="<?php echo $module['sort_order']; ?>" size="3" /></td>
                                      <td class="left"><a onclick="$('#module-row<?php echo $module_row; ?>').remove();" class="button btn btn-primary"><?php echo $button_remove; ?></a></td>
                                    </tr>
                                  </tbody>
                                  <?php $module_row++; ?>
                                  <?php } ?>
                                  <tfoot>
                                    <tr>
                                      <td colspan="6"></td>
                                      <td class="left"><a onclick="addModule();" class="button btn btn-primary"><?php echo $button_add_module; ?></a></td>
                                    </tr>
                                  </tfoot>
                                </table>
                              </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript"><!--
var module_row = <?php echo $module_row; ?>;

function addModule() {	
	html  = '<tbody id="module-row' + module_row + '">';
	html += '  <tr>';
	html += '    <td class="left"><select name="banner_module[' + module_row + '][banner_id]">';
	<?php foreach ($banners as $banner) { ?>
	html += '      <option value="<?php echo $banner['banner_id']; ?>"><?php echo addslashes($banner['name']); ?></option>';
	<?php } ?>
	html += '    </select></td>';
	html += '    <td class="left"><input type="text" name="banner_module[' + module_row + '][width]" value="" size="3" /> <input type="text" name="banner_module[' + module_row + '][height]" value="" size="3" /></td>'; 
	html += '    <td class="left"><select name="banner_module[' + module_row + '][layout_id]">';
	<?php foreach ($layouts as $layout) { ?>
	html += '      <option value="<?php echo $layout['layout_id']; ?>"><?php echo addslashes($layout['name']); ?></option>';
	<?php } ?>
	html += '    </select></td>';
	html += '    <td class="left"><select name="banner_module[' + module_row + '][position]">';
	html += '      <option value="content_top"><?php echo $text_content_top; ?></option>';
	html += '      <option value="content_bottom"><?php echo $text_content_bottom; ?></option>';
	html += '      <option value="column_left"><?php echo $text_column_left; ?></option>';
	html += '      <option value="column_right"><?php echo $text_column_right; ?></option>';
	html += '    </select></td>';
	html += '    <td class="left"><select name="banner_module[' + module_row + '][status]">';
    html += '      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
    html += '      <option value="0"><?php echo $text_disabled; ?></option>';
    html += '    </select></td>';
	html += '    <td class="right"><input type="text" name="banner_module[' + module_row + '][sort_order]" value="" size="3" /></td>';
	html += '    <td class="left"><a onclick="$(\'#module-row' + module_row + '\').remove();" class="button btn btn-primary"><?php echo $button_remove; ?></a></td>';
	html += '  </tr>';
	html += '</tbody>';
	
	$('#module tfoot').before(html);
	
	module_row++;
}
//--></script> 
<?php echo $footer; ?>