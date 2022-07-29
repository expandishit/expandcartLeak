<div class="modal-dialog modal-lg">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="md-close close" data-dismiss="modal" aria-hidden="true">&times;</button>
      <h4 class="modal-title"><?php echo $heading_title; ?></h4>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-sm-5"><a href="<?php echo $parent; ?>" data-toggle="tooltip" title="<?php echo $button_parent; ?>" id="button-parent" class="btn btn-default"><i class="fa fa-level-up"></i></a> <a href="<?php echo $refresh; ?>" data-toggle="tooltip" title="<?php echo $button_refresh; ?>" id="button-refresh" class="btn btn-default"><i class="fa fa-refresh"></i></a>
          <button type="button" data-toggle="tooltip" title="<?php echo $button_upload; ?>" id="button-upload" class="btn btn-primary"><i class="fa fa-upload"></i></button>
          <button type="button" data-toggle="tooltip" title="<?php echo $button_folder; ?>" id="button-folder" class="btn btn-default"><i class="fa fa-folder"></i></button>
          <button type="button" data-toggle="tooltip" title="<?php echo $button_delete; ?>" id="button-delete" class="btn btn-danger"><i class="fa fa-trash-o"></i></button>
        </div>
        <div class="col-sm-7">
          <div class="input-group">
            <input type="text" name="search" value="<?php echo $filter_name; ?>" placeholder="<?php echo $entry_search; ?>" class="form-control">
            <span class="input-group-btn">
            <button type="button" data-toggle="tooltip" title="<?php echo $button_search; ?>" id="button-search" class="btn btn-primary"><i class="fa fa-search"></i></button>
            </span></div>
        </div>
      </div>
        <hr />
        <div class="alert alert-success uploadHelp">
            <i class="fa fa-check-circle fa-fw fa-lg"></i>
            <?php echo $text_upload_help; ?>
        </div>
        <form action="/upload-target" class="dropzone"></form>
      <?php foreach (array_chunk($images, 4) as $image) { ?>
      <div class="row">
        <?php foreach ($image as $image) { ?>
        <div class="col-sm-3 col-xs-6 text-center">
          <?php if ($image['type'] == 'directory') { ?>
          <div class="text-center"><a href="<?php echo $image['href']; ?>" class="directory" style="vertical-align: middle;"><i class="fa fa-folder fa-5x" style="width: 100px; height: 100px; line-height: 100px;"></i></a></div>
          <label>
            <input type="checkbox" name="path[]" value="<?php echo $image['path']; ?>" />
            <?php echo $image['name']; ?></label>
          <?php } ?>
          <?php if ($image['type'] == 'image') { ?>
          <a href="<?php echo $image['href']; ?>" class="thumbnail"><img src="<?php echo $image['thumb']; ?>" alt="<?php echo $image['name']; ?>" title="<?php echo $image['name']; ?>" /></a>
          <label>
            <input type="checkbox" name="path[]" value="<?php echo $image['path']; ?>" />
            <?php echo $image['name']; ?></label>
          <?php } ?>
        </div>
        <?php } ?>
      </div>
      <br />
      <?php } ?>
    </div>
    <div class="modal-footer"><?php echo $pagination; ?></div>
  </div>
</div>
<script type="text/javascript"><!--
    var url = 'index.php?route=common/filemanager/upload&token=<?php echo $token; ?>&directory=<?php echo $directory; ?>';
    var myDropzone = new Dropzone(".dropzone", { url: url, clickable: '#button-upload, .dropzone' });
    myDropzone.on("success", function(file, resp) {
        $(file.previewElement).data("fileData", resp.fileData);
        $(file.previewElement).click(function() {thumbClick(this);});
        $('.uploadHelp').slideDown("slow");
    });

function thumbClick(clickEventElem) {
     var fileData = $(clickEventElem).data('fileData');

    <?php if ($editorFunc != 'null') { ?>
        <?php if ($callerName == 'ckeditor') { ?>
            CKEDITOR.tools.callFunction('<?php echo $editorFunc;?>', fileData.href);
        <?php } else { ?>
            if (typeof window["<?php echo $editorFunc;?>"] == 'function') {
                window["<?php echo $editorFunc;?>"]("<?php echo $target; ?>", "<?php echo $thumb; ?>", fileData.href, fileData.path, fileData.thumb);
            }
        <?php } ?>
    <?php } else { ?>
        $('#<?php echo $target; ?>').val(fileData.path).trigger('change');

        <?php if ($thumb) { ?>
            <?php if (strpos($thumb, "img-slayerID", 0) === 0) { ?>
                var $parentDiv = $('#<?php echo $thumb; ?>').parent();
                $('#<?php echo $thumb; ?>').remove();
                $parentDiv.append( "<img src='" + fileData.href + "' alt='' id='" + "<?php echo $thumb; ?>" + "' />" );
                $pavoEditor.storeCurrentLayerData();
            <?php } else if ($thumb == "imgMgrThumb") { ?>
                $('#<?php echo $thumb; ?>').attr('src', fileData.href);
            <?php } else { ?>
                $('#<?php echo $thumb; ?>').attr('src', fileData.thumb);
            <?php } ?>
        <?php } ?>
    <?php } ?>

    $('#modal-image').modal('hide');
}

<?php if ($target) { ?>
$('a.thumbnail').on('click', function(e) {
	e.preventDefault();

    <?php if ($editorFunc != 'null') { ?>
        <?php if ($callerName == 'ckeditor') { ?>
            CKEDITOR.tools.callFunction('<?php echo $editorFunc;?>', $(this).attr('href'));
        <?php } else { ?>
            if (typeof window["<?php echo $editorFunc;?>"] == 'function') {
                window["<?php echo $editorFunc;?>"]("<?php echo $target; ?>", "<?php echo $thumb; ?>", $(this).attr('href'), $(this).parent().find('input').attr('value'), $(this).find('img').attr('src'));
            }
        <?php } ?>
    <?php } else { ?>
        $('#<?php echo $target; ?>').val($(this).parent().find('input').attr('value')).trigger('change');

        <?php if ($thumb) { ?>
            <?php if (strpos($thumb, "img-slayerID", 0) === 0) { ?>
                var $parentDiv = $('#<?php echo $thumb; ?>').parent();
                $('#<?php echo $thumb; ?>').remove();
                $parentDiv.append( "<img src='" + $(this).attr("href") + "' alt='' id='" + "<?php echo $thumb; ?>" + "' />" );
                $pavoEditor.storeCurrentLayerData();
            <?php } else if ($thumb == "imgMgrThumb") { ?>
                $('#<?php echo $thumb; ?>').attr('src', $(this).attr('href'));
            <?php } else { ?>
                $('#<?php echo $thumb; ?>').attr('src', $(this).find('img').attr('src'));
            <?php } ?>
        <?php } ?>
    <?php } ?>

	$('#modal-image').modal('hide');
});
<?php } ?>

$('a.directory').on('click', function(e) {
	e.preventDefault();

	$('#modal-image').load($(this).attr('href'));
});

$('.pagination a').on('click', function(e) {
	e.preventDefault();

	$('#modal-image').load($(this).attr('href'));
});

$('#button-parent').on('click', function(e) {
	e.preventDefault();

	$('#modal-image').load($(this).attr('href'));
});

$('#button-refresh').on('click', function(e) {
	e.preventDefault();

	$('#modal-image').load($(this).attr('href'));
});

$('input[name=\'search\']').on('keydown', function(e) {
	if (e.which == 13) {
		$('#button-search').trigger('click');
	}
});

$('#button-search').on('click', function(e) {
	var url = 'index.php?route=common/filemanager&token=<?php echo $token; ?>&directory=<?php echo $directory; ?>';

	var filter_name = $('input[name=\'search\']').val();

	if (filter_name) {
		url += '&filter_name=' + encodeURIComponent(filter_name);
	}

	<?php if ($thumb) { ?>
	    url += '&thumb=' + '<?php echo $thumb; ?>';
	<?php } ?>

	<?php if ($target) { ?>
	    url += '&target=' + '<?php echo $target; ?>';
	<?php } ?>

    <?php if ($editorFunc != '') { ?>
        url += '&editorFunc=' + '<?php echo $editorFunc; ?>';
    <?php } ?>

    <?php if ($callerName != '') { ?>
        url += '&callerName=' + '<?php echo $callerName; ?>';
    <?php } ?>

	$('#modal-image').load(url);
});
//--></script>
<script type="text/javascript"><!--
$('#NOTWORKINGNOW').on('click', function() {
	$('#form-upload').remove();

	$('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" value="" /></form>');

	$('#form-upload input[name=\'file\']').trigger('click');

	if (typeof timer != 'undefined') {
    	clearInterval(timer);
	}

	timer = setInterval(function() {
		if ($('#form-upload input[name=\'file\']').val() != '') {
			clearInterval(timer);

			$.ajax({
				url: 'index.php?route=common/filemanager/upload&token=<?php echo $token; ?>&directory=<?php echo $directory; ?>',
				type: 'post',
				dataType: 'json',
				data: new FormData($('#form-upload')[0]),
				cache: false,
				contentType: false,
				processData: false,
				beforeSend: function() {
					$('#button-upload i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
					$('#button-upload').prop('disabled', true);
				},
				complete: function() {
					$('#button-upload i').replaceWith('<i class="fa fa-upload"></i>');
					$('#button-upload').prop('disabled', false);
				},
				success: function(json) {
					if (json['error']) {
						alert(json['error']);
					}

					if (json['success']) {
						alert(json['success']);

						$('#button-refresh').trigger('click');
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
	}, 500);
});

$('#button-folder').popover({
	html: true,
	placement: 'bottom',
	trigger: 'click',
	title: '<?php echo $entry_folder; ?>',
	content: function() {
		html  = '<div class="input-group">';
		html += '  <input type="text" name="folder" value="" placeholder="<?php echo $entry_folder; ?>" class="form-control">';
		html += '  <span class="input-group-btn"><button type="button" title="<?php echo $button_folder; ?>" id="button-create" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></span>';
		html += '</div>';

		return html;
	}
});

$('#button-folder').on('shown.bs.popover', function() {
	$('#button-create').on('click', function() {
		$.ajax({
			url: 'index.php?route=common/filemanager/folder&token=<?php echo $token; ?>&directory=<?php echo $directory; ?>',
			type: 'post',
			dataType: 'json',
			data: 'folder=' + encodeURIComponent($('input[name=\'folder\']').val()),
			beforeSend: function() {
				$('#button-create').prop('disabled', true);
			},
			complete: function() {
				$('#button-create').prop('disabled', false);
			},
			success: function(json) {
				if (json['error']) {
					alert(json['error']);
				}

				if (json['success']) {
					alert(json['success']);

					$('#button-refresh').trigger('click');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});
});

$('#modal-image #button-delete').on('click', function(e) {
	if (confirm('<?php echo $text_confirm; ?>')) {
		$.ajax({
			url: 'index.php?route=common/filemanager/delete&token=<?php echo $token; ?>',
			type: 'post',
			dataType: 'json',
			data: $('input[name^=\'path\']:checked'),
			beforeSend: function() {
				$('#button-delete').prop('disabled', true);
			},
			complete: function() {
				$('#button-delete').prop('disabled', false);
			},
			success: function(json) {
				if (json['error']) {
					alert(json['error']);
				}

				if (json['success']) {
					alert(json['success']);

					$('#button-refresh').trigger('click');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
});
//--></script>
