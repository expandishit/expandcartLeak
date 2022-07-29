<?php
/******************************************************
 * @package:   Pav Banners Manager module for Opencart 1.5.x
 * @version:   1.0
 * @author:    http://www.pavothemes.com
 * @copyright: Copyright (C) Feb 2012 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @License :  GNU General Public License version 2
*******************************************************/

echo $header; ?>
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
				<a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a>
				<a onclick="$('#action').val('save_stay');$('#form').submit();" class="button btn btn-primary"><?php echo $button_save_stay; ?></a>
				<a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a>
			</div>
		</div>
		<div class="content">
			<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">

			<input name="action" type="hidden" id="action"/>  
			<div id="tabs-modules" class="htabs">
				<a href="#tab-modules"><?php echo $this->language->get('tab_module_assign'); ?></a>
				<a href="#tab-data"><?php echo $this->language->get('tab_banner_image'); ?></a>
			</div>
			<div id="tab-modules">
				<table class="form">
					<tr>
						<td><?php echo $this->language->get('prefix_class'); ?></td>
						<td>
							<?php $module_row = 0; ?>
							<input name="pavbannermanager_module[<?php echo $module_row; ?>][prefix]" value="<?php echo ( isset($module['prefix'])?$module['prefix']:'' ) ?>"/>
						</td>
					</tr>
			
					<tr>
						<td><?php echo $entry_layout; ?></td>
						<td>
							<select name="pavbannermanager_module[<?php echo $module_row; ?>][layout_id]">
								<?php foreach ($layouts as $layout) { ?>
								<?php if ($layout['layout_id'] == $module['layout_id']) { ?>
								<option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
								<?php } ?>
								<?php } ?>
							</select>
						</td>
					</tr>

					<tr>
						<td><?php echo $entry_position; ?></td>
						<td>
							<select name="pavbannermanager_module[<?php echo $module_row; ?>][position]">
								<?php foreach( $positions as $pos ) { ?>
								<?php if ($pos == $module['position']) { ?>
								<option value="<?php echo $pos;?>" selected="selected"><?php echo $this->language->get('text_'.$pos); ?></option>
								<?php } else { ?>
								<option value="<?php echo $pos;?>"><?php echo $this->language->get('text_'.$pos); ?></option>
								<?php } ?>
								<?php } ?> 
							</select>
						</td>
					</tr>

					<tr>
						<td><?php echo $entry_status; ?></td>
						<td>
							<select name="pavbannermanager_module[<?php echo $module_row; ?>][status]">
								<?php if ($module['status']) { ?>
								<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
								<option value="0"><?php echo $text_disabled; ?></option>
								<?php } else { ?>
								<option value="1"><?php echo $text_enabled; ?></option>
								<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $entry_sort_order; ?></td>
						<td><input type="text" name="pavbannermanager_module[<?php echo $module_row; ?>][sort_order]" value="<?php echo isset($module['sort_order'])?$module['sort_order']:''; ?>" size="3" /></td>
					</tr>
					<tr>
						<td><?php echo $entry_banner_layouts; ?></td>
						<td>
							<select name="pavbannermanager_module[<?php echo $module_row; ?>][banner_layout]">
								<?php foreach( $layout_groups as $layout ) { ?>
								<?php if ($layout['layout_id'] == $module['banner_layout']) { ?>
								<option value="<?php echo $layout['layout_id'];?>" selected="selected"><?php echo $layout['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $layout['layout_id'];?>"><?php echo $layout['name']; ?></option>
								<?php } ?>
								<?php } ?> 
							</select>
						</td>
					</tr>
				</table>

			</div>

			<div id="tab-data">
				<?php $banner_row = 1; ?>
				<div class="vtabs">
					<?php if(isset($module['banners']) && !empty($module['banners'])) { ?>
					<?php $banner_row = 1; ?>						
					<?php foreach ($module['banners'] as $banner) { ?>
					<a href="#tab-module-<?php echo $banner_row; ?>" id="module-<?php echo $banner_row; ?>"><?php echo $tab_module . ' ' . $banner_row; ?>&nbsp;<img src="view/image/delete.png" alt="" onclick="$('.vtabs a:first').trigger('click'); $('#module-<?php echo $banner_row; ?>').remove(); $('#tab-module-<?php echo $banner_row; ?>').remove(); return false;" /></a>
					<?php $banner_row++; ?>
					<?php } ?>
					<?php } //end check isset banners data?>
					<span id="module-add"><?php echo $button_add_module; ?>&nbsp;<img src="view/image/add.png" alt="" onclick="addModule();" /></span> 
				</div>
				<?php if(isset($module['banners']) && !empty($module['banners'])) { ?>
				<?php $banner_row = 1; ?>
				<?php foreach ($module['banners'] as $banner) { ?>
				
				<?php //echo "<pre>"; print_r($module['banners']); die;?>
				<div id="tab-module-<?php echo $banner_row; ?>" class="vtabs-content">

					<table class="form">
						<tr>
							<td><?php echo $this->language->get('entry_image') ?></td>
							<td class="left"><div class="image"><img src="<?php echo $banner['thumb']; ?>" alt="" id="thumb<?php echo $banner_row; ?>" />
								<input type="hidden" name="pavbannermanager_module[<?php echo $module_row; ?>][banners][<?php echo $banner_row; ?>][image]" value="<?php echo $banner['image']; ?>" id="image<?php echo $banner_row; ?>"  />
								<br />
								<a onclick="image_upload('image<?php echo $banner_row; ?>', 'thumb<?php echo $banner_row; ?>');"><?php echo $text_browse; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('#thumb<?php echo $banner_row; ?>').attr('src', '<?php echo $no_image; ?>'); $('#image<?php echo $banner_row; ?>').attr('value', '');"><?php echo $text_clear; ?></a></div>
							</td>
						</tr>
						<tr>
							<td><?php echo $this->language->get('entry_link') ?></td>
							<td><input name="pavbannermanager_module[<?php echo $module_row; ?>][banners][<?php echo $banner_row;?>][link]" value="<?php echo isset($banner['link'])?$banner['link']:'';?>" type="text"/></td>
						</tr>

						<tr>
							<td><?php echo $this->language->get('entry_banner_width_height') ?></td>
							<td>
								<input type="text" size="3" value="<?php echo isset($banner['width'])?$banner['width']:275; ?>" name="pavbannermanager_module[<?php echo $module_row; ?>][banners][<?php echo $banner_row;?>][width]">
								<input type="text" size="3" value="<?php echo isset($banner['height'])?$banner['height']:254; ?>" name="pavbannermanager_module[<?php echo $module_row; ?>][banners][<?php echo $banner_row;?>][height]">
							</td>
						</tr>
					</table>

					<?php /*
					// Language Tab */ ?>
					<div id="language-<?php echo $banner_row; ?>" class="htabs">
						<?php foreach ($languages as $language) { ?>
						<a href="#tab-language-<?php echo $banner_row; ?>-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
						<?php } ?>
					</div>
					

					<?php /*
					// Text Editor */ ?>
					<?php foreach ($languages as $language) { ?>
					<div id="tab-language-<?php echo $banner_row; ?>-<?php echo $language['language_id']; ?>">
						<table class="form">
							<tr>
								<td><?php echo $entry_caption; ?></td>
								<td>
									<textarea cols="60" rows="5" name="pavbannermanager_module[<?php echo $module_row; ?>][banners][<?php echo $banner_row; ?>][caption][<?php echo $language['language_id']; ?>]" id="caption-<?php echo $banner_row; ?>-<?php echo $language['language_id']; ?>"><?php echo isset($banner['caption'][$language['language_id']]) ? $banner['caption'][$language['language_id']] : ''; ?></textarea>
								</td>
							</tr>
						</table>
					</div>
					<?php } ?>

				</div>
				<?php $banner_row++; ?>
				<?php } ?>
				<?php } //end check isset banners data?>
				
			</div>
			</form>
		</div>
	</div>
</div>



<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script> 
<script type="text/javascript"><!--

<?php /* // CKEDITOR */ ?>
<?php $banner_row = 1; ?>
<?php foreach ($module['banners'] as $banner) { ?>
<?php foreach ($languages as $language) { ?>
CKEDITOR.replace('caption-<?php echo $banner_row; ?>-<?php echo $language['language_id']; ?>', {
	filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
});
<?php } ?>
<?php $banner_row++; ?>
<?php } ?>


//--></script> 
<script type="text/javascript"><!--
var module_row = <?php echo $module_row; ?>;

var banner_row = <?php echo $banner_row; ?>;

function addModule() {	
	html  = '<div id="tab-module-' + banner_row + '" class="vtabs-content">';

	html += '	<table class="form">';
	html += '		<tr>';
	html += '			<td><?php echo $this->language->get('entry_image') ?></td>';
	html += '			<td class="left"><div class="image"><img src="<?php echo $no_image; ?>" alt="" id="thumb'+banner_row+'" />';
	html += '				<input type="hidden" name="pavbannermanager_module['+module_row+'][banners]['+banner_row+'][image]" value="<?php echo $no_image; ?>" id="image'+banner_row+'"  />';
	html += '				<br />';
	html += '				<a onclick="image_upload(\'image'+banner_row+'\', \'thumb'+banner_row+'\');"><?php echo $text_browse; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$(\'#thumb'+banner_row+'\').attr(\'src\', \''+banner_row+'\'); $(\'#image'+banner_row+'\').attr(\'value\', \'\');"><?php echo $text_clear; ?></a></div>';
	html += '			</td>';
	html += '		</tr>';
	html += '			<tr>';
	html += '				<td><?php echo $this->language->get('entry_link') ?></td>';
	html += '				<td><input name="pavbannermanager_module['+module_row+'][banners]['+banner_row+'][link]" value="#" type="text"/></td>';
	html += '			</tr>';
	html += '		<tr>';
	html += '			<td><?php echo $this->language->get('entry_banner_width_height') ?></td>';
	html += '			<td>';
	html += '				<input type="text" size="3" value="275" name="pavbannermanager_module['+module_row+'][banners]['+banner_row+'][width]">';
	html += '				<input type="text" size="3" value="254" name="pavbannermanager_module['+module_row+'][banners]['+banner_row+'][height]">';
	html += '			</td>';
	html += '		</tr>';
	html += '	</table>';

	<?php /*
	// Language Tab */ ?>
	html += '	<div id="language-' + banner_row + '" class="htabs">';
					<?php foreach ($languages as $language) { ?>
	html += '		<a href="#tab-language-'+ banner_row + '-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>';
					<?php } ?>
	html += '	</div>';
	


	// Text Editor
	<?php /* 
	*/ 
	?>
				<?php foreach ($languages as $language) { ?>
	html += '    <div id="tab-language-'+ banner_row + '-<?php echo $language['language_id']; ?>">';
	html += '      <table class="form">';
	html += '        <tr>';
	html += '          <td><?php echo $entry_caption; ?></td>';
	html += '          <td><textarea cols="60" rows="5" name="pavbannermanager_module['+module_row+'][banners]['+banner_row+'][caption][<?php echo $language['language_id']; ?>]" id="caption-' + banner_row + '-<?php echo $language['language_id']; ?>"></textarea></td>';
	html += '        </tr>';
	html += '      </table>';
	html += '    </div>';
				<?php } ?>
	html += '</div>';
	
	$('#tab-data').append(html);
	
	<?php /* //CKEDITOR */ ?>
	<?php foreach ($languages as $language) { ?>
	CKEDITOR.replace('caption-' + banner_row + '-<?php echo $language['language_id']; ?>', {
		filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
	});  
	<?php } ?>
	
	
	$('#language-' + banner_row + ' a').tabs();
	
	$('#module-add').before('<a href="#tab-module-' + banner_row + '" id="module-' + banner_row + '"><?php echo $tab_module; ?> ' + banner_row + '&nbsp;<img src="view/image/delete.png" alt="" onclick="$(\'.vtabs a:first\').trigger(\'click\'); $(\'#module-' + banner_row + '\').remove(); $(\'#tab-module-' + banner_row + '\').remove(); return false;" /></a>');
	
	$('.vtabs a').tabs();
	
	$('#module-' + banner_row).trigger('click');
	
	banner_row++;
}
//--></script> 
<script type="text/javascript"><!--
$("#tabs-modules a").tabs();
$('#tabs-modules a').click( function(){
	$.cookie("sactived_tab", $(this).attr("href") );
} );

if( $.cookie("sactived_tab") !="undefined" ){
	$('#tabs-modules a').each( function(){ 
		if( $(this).attr("href") ==  $.cookie("sactived_tab") ){
			$(this).click();
			return ;
		}
	} );
}


$('.vtabs a').tabs();
//--></script> 
<script type="text/javascript"><!--
<?php if(isset($module['banners']) && !empty($module['banners'])){ ?>
<?php $banner_row = 1; ?>
<?php foreach ($module['banners'] as $banner) { ?>
$('#language-<?php echo $banner_row; ?> a').tabs();
<?php $banner_row++; ?>
<?php } ?>
<?php } ?> 
//--></script>

<script type="text/javascript"><!--
function image_upload(field, thumb) {
    $.startImageManager(field, thumb);
};
//--></script> 
<?php echo $footer; ?>
