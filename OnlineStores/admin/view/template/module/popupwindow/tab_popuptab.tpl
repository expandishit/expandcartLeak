<?php 
    $popup_name = $moduleName.'[PopupWindow]['.$popup['id'].']';
    $popup_data = isset($moduleData['PopupWindow'][$popup['id']]) ? $moduleData['PopupWindow'][$popup['id']] : array();
?>

<div id="popup_<?php echo $popup['id']; ?>" class="tab-pane popups" style="width:99%">
	 <ul class="nav nav-tabs">
	  <li class="active"><a data-toggle="tab" href="#popup_settings_<?php echo $popup['id'] ?>"><?php echo $this->language->get('Popup settings'); ?></a></li>
	  <li><a data-toggle="tab" href="#popup_appearance_<?php echo $popup['id'] ?>"><?php echo $this->language->get('Appearance'); ?></a></li>
	 </ul>

	<div class="tab-content" style="margin-top:10px;">
	  <div id="popup_settings_<?php echo $popup['id'] ?>" class="tab-pane fade in active">
	    <?php require(DIR_APPLICATION.'view/template/module/popupwindow/tab_popup_settings.php'); ?>
	  </div>
	  <div id="popup_appearance_<?php echo $popup['id'] ?>" class="tab-pane fade in">
	   	<?php require(DIR_APPLICATION.'view/template/module/popupwindow/tab_popup_appearance.php'); ?>
	  </div>
	</div>


   <?php if (isset($newAddition) && $newAddition==true) { ?>
		<script type="text/javascript">
            <?php foreach ($languages as $language) { ?>
                CKEDITOR.replace('message_<?php echo $popup['id']; ?>_<?php echo $language['language_id']; ?>', { 
				filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $this->session->data['token']; ?>',
				filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $this->session->data['token']; ?>',
				filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $this->session->data['token']; ?>',
				filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $this->session->data['token']; ?>',
				filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $this->session->data['token']; ?>',
				filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $this->session->data['token']; ?>',
				height: '300px'
				});
            <?php } ?>
        </script>
	<?php } ?>

	<script>

$(function() {
	selectorsForPopups(this);
});
	</script>
</div>