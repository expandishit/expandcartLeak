<div class="tabbable tabs-left" id="popup_tabs">
	
    <ul class="nav nav-tabs popup-list">
        <li class="static"><a class="addNewPopUp"><i class="fa fa-plus"></i> <?php echo $this->language->get('Add New Popup'); ?></a></li>
        <?php if (isset($moduleData['PopupWindow'])) { ?>
            <?php foreach ($moduleData['PopupWindow'] as $popup) { ?>
            <li><a href="#popup_<?php echo $popup['id']; ?>" data-toggle="tab" data-popup-id="<?php echo $popup['id']; ?>"><i class="fa fa-pencil-square-o"></i> <?php echo $this->language->get('Popup'); ?> <?php echo $popup['id']; ?> <i class="fa fa-minus-circle removePopUp"></i>
                <input type="hidden" name="<?php echo $moduleName; ?>[PopupWindow][<?php echo $popup['id']; ?>][id]" value="<?php echo $popup['id']; ?>" />
                </a> </li>
            <?php } ?>
        <?php } ?>
    </ul>
    <div class="tab-content popup-settings">
            <?php if (isset($moduleData['PopupWindow'])) { ?>
            <?php foreach ($moduleData['PopupWindow'] as $popup) { 
                require(DIR_APPLICATION.'view/template/module/'.$moduleNameSmall.'/tab_popuptab.tpl');
				
            } ?>
        <?php } ?>
    </div>
</div>
        
<script type="text/javascript"><!--
// Add PopUp
function addNewPopUp() {
	count = $('.popup-list li:last-child > a').data('popup-id') + 1 || 1;
	var ajax_data = {};
	ajax_data.token = '<?php echo $this->session->data['token']; ?>';
	ajax_data.store_id = '<?php echo $this->request->get['store_id']; ?>';
	ajax_data.popup_id = count;

	$.ajax({
		url: 'index.php?route=module/<?php echo $moduleNameSmall; ?>/get_popupwindow_settings',
		data: ajax_data,
		dataType: 'html',
		beforeSend: function() {
		},
		success: function(settings_html) {
		$('.popup-settings').append(settings_html);
	
			if (count == 1) { $('a[href="#popup_'+ count +'"]').tab('show'); }
			tpl 	= '<li>';
			tpl 	+= '<a href="#popup_'+ count +'" data-toggle="tab" data-popup-id="'+ count +'">';
			tpl 	+= '<i class="fa fa-pencil-square-o"></i> <?php echo $this->language->get('Popup'); ?> '+ count + ' ';
			tpl 	+= '<i class="fa fa-minus-circle removePopUp"></i>';
			tpl 	+= '<input type="hidden" name="<?php echo $moduleName; ?>[PopupWindow]['+ count +'][id]" value="'+ count +'"/>';
			tpl 	+= '</a>';
			tpl	+= '</li>';
			
			$('.popup-list').append(tpl);
			selectorsForPopups();
			$('.popup-list').children().last().children('a').trigger('click');
			window.localStorage['currentSubTab'] = $('.popup-list').children().last().children('a').attr('href');
		}
	});
}

// Remove PopUp
function removePopUp(e) {
	tab_link = $(e).parent();
	tab_pane_id = tab_link.attr('href');
	
	var confirmRemove = confirm('<?php echo $this->language->get('Are you sure you want to remove'); ?> ' + tab_link.text().trim() + '?');
	
	if (confirmRemove == true) {
		tab_link.parent().remove();
		$(tab_pane_id).remove();
		
		if ($('.popup-list').children().length > 1) {
			$('.popup-list > li:nth-child(2) a').tab('show');
			window.localStorage['currentSubTab'] = $('.popup-list > li:nth-child(2) a').attr('href');
		}
	}
}

// Events for the Add and Remove buttons
$(document).ready(function() {
	// Add New Label
	$(document).on('click', '.addNewPopUp', function(e) { addNewPopUp(); });
	
	// Remove Label
	$(document).on('click', '.removePopUp', function(e) { removePopUp(this); });
});



// Display & Hide the log tab
$(function() {
    var $typeSelector = $('#LogChecker');
    var $toggleArea = $('#log_tab');
	 if ($typeSelector.val() === 'yes') {
            $toggleArea.show(); 
        }
        else {
            $toggleArea.hide(); 
        }
    $typeSelector.change(function(){
        if ($typeSelector.val() === 'yes') {
            $toggleArea.show(300); 
        }
        else {
            $toggleArea.hide(300); 
        }
    });
});

// Show the CKEditor
<?php 
if (isset($moduleData['PopupWindow'])) { 
	foreach ($moduleData['PopupWindow'] as $popup) {
		foreach ($languages as $language) { ?>
			CKEDITOR.replace('message_<?php echo $popup['id']; ?>_<?php echo $language['language_id']; ?>', { 
				filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $this->session->data['token']; ?>',
				filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $this->session->data['token']; ?>',
				filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $this->session->data['token']; ?>',
				filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $this->session->data['token']; ?>',
				filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $this->session->data['token']; ?>',
				filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $this->session->data['token']; ?>',
				height: '300px'
			});
<?php	}
	}
} ?>

// Selectors for discount
function selectorsForPopups() {
	$(document).find('.popups').find('.startTime').timepicker();
    $(document).find('.popups').find('.endTime').timepicker();
    $(document).find('.popups').find('.startDate').datepicker({dateFormat: 'yy-mm-dd'});
    $(document).find('.popups').find('.endDate').datepicker({dateFormat: 'yy-mm-dd'});

	$('.methodTypeSelect').each(function() {
		if($(this).val() == 0) {
			$(this).parents('.popups').find('.specURL').hide();
			$(this).parents('.popups').find('.excludeURL').hide();
			$(this).parents('.popups').find('.cssSelector').hide();
			$(this).parents('.popups').find('.eventSelect').prop('disabled', false);
		}
		else if($(this).val() == 1) {
			$(this).parents('.popups').find('.specURL').hide();
			$(this).parents('.popups').find('.excludeURL').show();
			$(this).parents('.popups').find('.cssSelector').hide();
			$(this).parents('.popups').find('.eventSelect').prop('disabled', false);
		}
		else if($(this).val() == 2) {
			$(this).parents('.popups').find('.specURL').show();
			$(this).parents('.popups').find('.excludeURL').hide();
			$(this).parents('.popups').find('.cssSelector').hide();
			$(this).parents('.popups').find('.eventSelect').prop('disabled', false);
		}
		else if($(this).val() == 3) {
			$(this).parents('.popups').find('.specURL').hide();
			$(this).parents('.popups').find('.excludeURL').hide();
			$(this).parents('.popups').find('.cssSelector').show();
			$(this).parents('.popups').find('.eventSelect').prop('disabled', true);
		}
	});

	$('.methodTypeSelect').on('change', function(e){ 
		if($(this).val() == 0) {
			$(this).parents('.popups').find('.specURL').hide(200);
			$(this).parents('.popups').find('.excludeURL').hide(200);
			$(this).parents('.popups').find('.cssSelector').hide(200);
			$(this).parents('.popups').find('.eventSelect').prop('disabled', false);
		}
		else if($(this).val() == 1) {
			$(this).parents('.popups').find('.specURL').hide(200);
			$(this).parents('.popups').find('.excludeURL').show(200);
			$(this).parents('.popups').find('.cssSelector').hide(200);
			$(this).parents('.popups').find('.eventSelect').prop('disabled', false);
		}
		else if($(this).val() == 2) {
			$(this).parents('.popups').find('.specURL').show(200);
			$(this).parents('.popups').find('.excludeURL').hide(200);
			$(this).parents('.popups').find('.cssSelector').hide(200);
			$(this).parents('.popups').find('.eventSelect').prop('disabled', false);
		}
		else if($(this).val() == 3) {
			$(this).parents('.popups').find('.specURL').hide(200);
			$(this).parents('.popups').find('.excludeURL').hide(200);
			$(this).parents('.popups').find('.cssSelector').show(200);
			$(this).parents('.popups').find('.eventSelect').prop('disabled', true);
		}
	});

	$('.timeIntervalSelect').each(function(e){ 
		if($(this).val() == 0) {
			$(this).parents('.popups').find('.timeInterval').hide();
		}
		else {
			$(this).parents('.popups').find('.timeInterval').show();
		}
	});

	$('.timeIntervalSelect').on('change', function(e){ 
		if($(this).val() == 0) {
			$(this).parents('.popups').find('.timeInterval').hide(200);
		}
		else {
			$(this).parents('.popups').find('.timeInterval').show(200);
		}
	});

	$('.dateIntervalSelect').each(function(e){ 
		if($(this).val() == 0) {
			$(this).parents('.popups').find('.dateInterval').hide();
		}
		else {
			$(this).parents('.popups').find('.dateInterval').show();
		}
	});

	$('.dateIntervalSelect').on('change', function(e){ 
		if($(this).val() == 0) {
			$(this).parents('.popups').find('.dateInterval').hide(200);
		}
		else {
			$(this).parents('.popups').find('.dateInterval').show(200);
		}
	});

	$('.repeatSelect').each(function(e){ 
		if($(this).val() == 2) {
			$(this).parents('.popups').find('.daysPicker').show();
		}
		else {
			$(this).parents('.popups').find('.daysPicker').hide();
		}
	});

	$('.repeatSelect').on('change', function(e){ 
		if($(this).val() == 2) {
			$(this).parents('.popups').find('.daysPicker').show(200);
		}
		else {
			$(this).parents('.popups').find('.daysPicker').hide(200);
		}
	});

	$('.eventSelect').each(function(e){ 
		if($(this).val() == 4) {
			$(this).parents('.popups').find('.percentageInput').show();
		}
		else {
			$(this).parents('.popups').find('.percentageInput').hide();
		}
	});

	$('.eventSelect').on('change', function(e){ 
		if($(this).val() == 4) {
			$(this).parents('.popups').find('.percentageInput').show(200);
		}
		else {
			$(this).parents('.popups').find('.percentageInput').hide(200);
		}
	});


}

// Initialize selector for discount
$(function() {
	selectorsForPopups();
});

</script>

