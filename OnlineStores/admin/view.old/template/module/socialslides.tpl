<?php
// ----------------------------------------------
// Social Slides for OpenCart v1.5.1.x, 1.5.2.x
// By Best-Byte
// www.best-byte.com
// ----------------------------------------------
?>
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
		<div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><span><?php echo $button_save; ?></span></a>
			<a onclick="location = '<?php echo $cancel; ?>';" class="button btn btn-primary"><span><?php echo $button_cancel; ?></span></a>
		</div>
	</div>
	<div class="content">
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
		<table class="form">
				<tr>
					<td colspan="3"><?php echo $entry_facebook; ?></td> 
        </tr>
				<tr>
					<td><?php echo $entry_facebook_show; ?></td> 
					<td colspan="3">  
						<?php if($socialslides_facebook_show) { 
						$checked1 = ' checked="checked"'; 
						$checked0 = ''; 
						} else { 
						$checked1 = ''; 
						$checked0 = ' checked="checked"'; 
						} ?> 
					<label for="socialslides_facebook_show_0"><?php echo $entry_yes; ?></label> 
					<input type="radio"<?php echo $checked0; ?> id="facebook_show_0" name="socialslides_facebook_show" value="0" /> 						
					<label for="socialslides_facebook_show_1"><?php echo $entry_no; ?></label> 
					<input type="radio"<?php echo $checked1; ?> id="facebook_show_1" name="socialslides_facebook_show" value="1" /> 
					</td>
        </tr>
        <tr>
          <td><?php echo $entry_facebook_code; ?></td>
					<td><input name="socialslides_facebook_code" type="text" size="40" maxlength="60" value="<?php echo $socialslides_facebook_code; ?>"></td>
				</tr>
				<tr>
					<td colspan="3"><?php echo $entry_twitter; ?></td> 
        </tr>
				<tr>
					<td><?php echo $entry_twitter_show; ?></td> 
					<td colspan="3">  
						<?php if($socialslides_twitter_show) { 
						$checked1 = ' checked="checked"'; 
						$checked0 = ''; 
						} else { 
						$checked1 = ''; 
						$checked0 = ' checked="checked"'; 
						} ?> 
					<label for="socialslides_twitter_show_0"><?php echo $entry_yes; ?></label> 
					<input type="radio"<?php echo $checked0; ?> id="twitter_show_0" name="socialslides_twitter_show" value="0" /> 						
					<label for="socialslides_twitter_show_1"><?php echo $entry_no; ?></label> 
					<input type="radio"<?php echo $checked1; ?> id="twitter_show_1" name="socialslides_twitter_show" value="1" /> 
					</td>
        </tr>
        <tr>
          <td><?php echo $entry_twitter_code; ?></td>
					<td><textarea name="socialslides_twitter_code" cols="60" rows="7"><?php echo $socialslides_twitter_code; ?></textarea></td>
				</tr>				
				<tr>
					<td colspan="3"><?php echo $entry_google; ?></td> 
        </tr>
				<tr>
					<td><?php echo $entry_google_show; ?></td> 
					<td colspan="3">  
						<?php if($socialslides_google_show) { 
						$checked1 = ' checked="checked"'; 
						$checked0 = ''; 
						} else { 
						$checked1 = ''; 
						$checked0 = ' checked="checked"'; 
						} ?> 
					<label for="socialslides_google_show_0"><?php echo $entry_yes; ?></label> 
					<input type="radio"<?php echo $checked0; ?> id="google_show_0" name="socialslides_google_show" value="0" /> 						
					<label for="socialslides_google_show_1"><?php echo $entry_no; ?></label> 
					<input type="radio"<?php echo $checked1; ?> id="google_show_1" name="socialslides_google_show" value="1" /> 
					</td>
        </tr>
        <tr>
          <td><?php echo $entry_google_code; ?></td>
					<td><input name="socialslides_google_code" type="text" size="40" maxlength="60" value="<?php echo $socialslides_google_code; ?>"></td>
				</tr>
				<tr>
					<td colspan="3"><?php echo $entry_pinterest; ?></td> 
        </tr>
				<tr>
					<td><?php echo $entry_pinterest_show; ?></td> 
					<td colspan="3">  
						<?php if($socialslides_pinterest_show) { 
						$checked1 = ' checked="checked"'; 
						$checked0 = ''; 
						} else { 
						$checked1 = ''; 
						$checked0 = ' checked="checked"'; 
						} ?> 
					<label for="socialslides_pinterest_show_0"><?php echo $entry_yes; ?></label> 
					<input type="radio"<?php echo $checked0; ?> id="pinterest_show_0" name="socialslides_pinterest_show" value="0" /> 						
					<label for="socialslides_pinterest_show_1"><?php echo $entry_no; ?></label> 
					<input type="radio"<?php echo $checked1; ?> id="pinterest_show_1" name="socialslides_pinterest_show" value="1" /> 
					</td>
        </tr>
        <tr>
          <td><?php echo $entry_pinterest_code; ?></td>
					<td><input name="socialslides_pinterest_code" type="text" size="40" maxlength="60" value="<?php echo $socialslides_pinterest_code; ?>"></td>
				</tr>
				<tr>
					<td colspan="3"><?php echo $entry_youtube; ?></td> 
        </tr>
				<tr>
					<td><?php echo $entry_youtube_show; ?></td> 
					<td colspan="3">  
						<?php if($socialslides_youtube_show) { 
						$checked1 = ' checked="checked"'; 
						$checked0 = ''; 
						} else { 
						$checked1 = ''; 
						$checked0 = ' checked="checked"'; 
						} ?> 
					<label for="socialslides_youtube_show_0"><?php echo $entry_yes; ?></label> 
					<input type="radio"<?php echo $checked0; ?> id="youtube_show_0" name="socialslides_youtube_show" value="0" /> 						
					<label for="socialslides_youtube_show_1"><?php echo $entry_no; ?></label> 
					<input type="radio"<?php echo $checked1; ?> id="youtube_show_1" name="socialslides_youtube_show" value="1" /> 
					</td>
        </tr>
        <tr>
          <td><?php echo $entry_youtube_code; ?></td>
					<td><input name="socialslides_youtube_code" type="text" size="40" maxlength="60" value="<?php echo $socialslides_youtube_code; ?>"></td>
				</tr>	
				<tr>
					<td colspan="3"><?php echo $entry_linkedin; ?></td> 
        </tr>
				<tr>
					<td><?php echo $entry_linkedin_show; ?></td> 
					<td colspan="3">  
						<?php if($socialslides_linkedin_show) { 
						$checked1 = ' checked="checked"'; 
						$checked0 = ''; 
						} else { 
						$checked1 = ''; 
						$checked0 = ' checked="checked"'; 
						} ?> 
					<label for="socialslides_linkedin_show_0"><?php echo $entry_yes; ?></label> 
					<input type="radio"<?php echo $checked0; ?> id="linkedin_show_0" name="socialslides_linkedin_show" value="0" /> 						
					<label for="socialslides_linkedin_show_1"><?php echo $entry_no; ?></label> 
					<input type="radio"<?php echo $checked1; ?> id="linkedin_show_1" name="socialslides_linkedin_show" value="1" /> 
					</td>
        </tr>
       <tr>
          <td><?php echo $entry_linkedin_code; ?></td>
					<td><input name="socialslides_linkedin_code" type="text" size="40" value="<?php echo $socialslides_linkedin_code; ?>"></td>
				</tr>



                <tr>
                    <td colspan="3"><?php echo $entry_instagram; ?></td>
                </tr>
                <tr>
                    <td><?php echo $entry_instagram_show; ?></td>
                    <td colspan="3">
                        <?php if($socialslides_instagram_show) {
						$checked1 = ' checked="checked"';
						$checked0 = '';
						} else {
						$checked1 = '';
						$checked0 = ' checked="checked"';
						} ?>
                        <label for="socialslides_instagram_show_0"><?php echo $entry_yes; ?></label>
                        <input type="radio"<?php echo $checked0; ?> id="instagram_show_0" name="socialslides_instagram_show" value="0" />
                        <label for="socialslides_instagram_show_1"><?php echo $entry_no; ?></label>
                        <input type="radio"<?php echo $checked1; ?> id="instagram_show_1" name="socialslides_instagram_show" value="1" />
                    </td>
                </tr>
                <tr>
                    <td><?php echo $entry_instagram_code; ?></td>
                    <td><input name="socialslides_instagram_code" type="text" size="40" value="<?php echo $socialslides_instagram_code; ?>"></td>
                </tr>



                <tr><td colspan="3">&nbsp;</td></tr>
                <tr>
                    <td><?php echo $top_position; ?></td>
                    <td><input name="socialslides_top_position" type="text" size="3" maxlength="3" value="<?php echo $socialslides_top_position; ?>">px</td>
                </tr>
                <tr>
					<td><?php echo $entry_display; ?></td> 
					<td colspan="3">  
						<?php if($socialslides_display) { 
						$checked1 = ' checked="checked"'; 
						$checked0 = ''; 
						} else { 
						$checked1 = ''; 
						$checked0 = ' checked="checked"'; 
						} ?> 
					<label for="socialslides_display_0"><?php echo $entry_left; ?></label> 
					<input type="radio"<?php echo $checked0; ?> id="display_0" name="socialslides_display" value="0" /> 						
					<label for="socialslides_display_1"><?php echo $entry_right; ?></label> 
					<input type="radio"<?php echo $checked1; ?> id="display_1" name="socialslides_display" value="1" /> 
					</td> 
				</tr>		
				<tr>
					<td><?php echo $entry_template; ?></td>
					<td colspan="3"> 
						<?php foreach ($templates as $template) { ?>
							<?php if ($template == $config_template) { ?>
								<span style='color: #990000; padding: 0 5px;'><b><?php echo $template; ?></b></span> 
							<?php } ?>
						<?php } ?>	
					</td>
				</tr>
				<tr>
					<td colspan="4">
						<span style='text-align: center;'><b><?php echo $text_module_settings; ?></b></span>
					</td>
				</tr>
		</table>
	<table id="module" class="table table-hover dataTable no-footer">
			<thead>
				<tr>
					<td class="left"><?php echo $entry_layout; ?></td>
					<td class="left"><?php echo $entry_status; ?></td>
					<td></td>
				</tr>
			</thead>
        <?php $module_row = 0; ?>
        <?php foreach ($modules as $module) { ?>
			<tbody id="module-row<?php echo $module_row; ?>">
				<tr>
					<td class="left"><select name="socialslides_module[<?php echo $module_row; ?>][layout_id]">
					<?php foreach ($layouts as $layout) { ?>
						<?php if ($layout['layout_id'] == $module['layout_id']) { ?>
							<option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
						<?php } else { ?>
							<option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
						<?php } ?>
					<?php } ?>
					</select></td>
					<td class="left"><select name="socialslides_module[<?php echo $module_row; ?>][status]">
					<?php if ($module['status']) { ?>
						<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
						<option value="0"><?php echo $text_disabled; ?></option>
					<?php } else { ?>
						<option value="1"><?php echo $text_enabled; ?></option>
						<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
					<?php } ?>
					</select></td>
					<input name="socialslides_module[<?php echo $module_row; ?>][position]" value="content_bottom" type="hidden">
					<input name="socialslides_module[<?php echo $module_row; ?>][sort_order]" value="0" type="hidden" />
					<td class="center">
						<a onclick="$('#module-row<?php echo $module_row; ?>').remove();" class="button btn btn-primary"><span><?php echo $button_remove; ?></span></a>
					</td>
				</tr>
			</tbody>
        <?php $module_row++; ?>
        <?php } ?>
			<tfoot>
				<tr>
					<td colspan="2"></td>
					<td class="center"><a onclick="addModule();" class="button btn btn-primary"><span><?php echo $button_add_module; ?></span></a></td>
				</tr>
			</tfoot>
		</table>
    </form>
	</div>
</div>
<?php echo $footer; ?>
<script type="text/javascript"><!--
var module_row = <?php echo $module_row; ?>;

function addModule() {	
	html  = '<tbody id="module-row' + module_row + '">';
	html += '  <tr>';
	html += '    <td class="left"><select name="socialslides_module[' + module_row + '][layout_id]">';
	<?php foreach ($layouts as $layout) { ?>
	html += '      <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>';
	<?php } ?>
	html += '    </select></td>';
	html += '    <td class="left"><select name="socialslides_module[' + module_row + '][status]">';
  html += '      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
  html += '      <option value="0"><?php echo $text_disabled; ?></option>';
  html += '    </select></td>';
	html += '    <input name="socialslides_module[' + module_row + '][position]" value="content_bottom" type="hidden">';
	html += '    <input name="socialslides_module[' + module_row + '][sort_order]" value="0" type="hidden" />';
	html += '    <td class="center"><a onclick="$(\'#module-row' + module_row + '\').remove();" class="button btn btn-primary"><span><?php echo $button_remove; ?></span></a></td>';
	html += '  </tr>';
	html += '</tbody>';
	
	$('#module tfoot').before(html);
	
	module_row++;
}
//--></script>