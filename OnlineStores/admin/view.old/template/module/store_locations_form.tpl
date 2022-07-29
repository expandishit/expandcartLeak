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
    <div>
      <table class="form">
        <tr>
          <td><?php echo $store_loc_name; ?></td>
          <td><input type="text" id="Name" name="Name" value="<?php echo $Name ?>" style="width:180px" /></td>
        </tr>
        <tr>
          <td><?php echo $store_loc_detail; ?></td>
          <td><textarea id="Details" name="Details" cols="20" rows="3"><?php echo $Details ?></textarea></td>
        </tr>
        <tr>
          <td><?php echo $store_loc_address; ?></td>
          <td><textarea id="Address" name="Address" cols="20" rows="3"><?php echo $Address; ?></textarea>&nbsp;&nbsp;<span><input type="checkbox" name="gCodeReq" id="gCodeReq" <?php echo $edit; ?>  />&nbsp;<?php echo $store_loc_gcode_req ?></span></td>
        </tr>
        <tr>
          <td><?php echo $store_loc_latlon; ?></td>
          <td><?php echo $lat; ?>, <?php echo $lon; ?></td>
        </tr>
        <tr>
          <td><?php echo $store_loc_email; ?></td>
          <td><input type="text" id="Email" value="<?php echo $Email; ?>" name="Email" style="width:90px" /></td>
        </tr>
        <tr>
          <td><?php echo $store_loc_phone; ?></td>
          <td><input type="text" id="Phone" name="Phone" value="<?php echo $Phone; ?>"  /></td>
        </tr>
        <tr>
          <td><?php echo $store_loc_offers; ?></td>
          <td><input type="text" name="SpecialOffers" value="<?php echo $SpecialOffers; ?>" /></td>
        </tr>
        <tr>
          <td><?php echo $store_loc_timing; ?></td>
          <td><input type="text" name="Timing" value="<?php echo $Timing; ?>" /></td>
        </tr>
        <tr>
          <td><?php echo $entry_sort_order; ?></td>
          <td><input type="text" name="sort_order" value="<?php echo $sort_order; ?>" /></td>
        </tr>
      </table>
      </div>
      <div id="tab-image">
          <table id="images" class="table table-hover dataTable no-footer">
            <thead>
              <tr>
                <td class="left"><?php echo $store_loc_image; ?></td>
                <td class="right"><?php echo $entry_sort_order; ?></td>
                <td></td>
              </tr>
            </thead>
            <?php $image_row = 0; ?>
            <?php foreach ($location_images as $location_image) { ?>
            <tbody id="image-row<?php echo $image_row; ?>">
              <tr>
                <td class="left"><div class="image"><img src="<?php echo $location_image['thumb']; ?>" alt="" id="thumb<?php echo $image_row; ?>" />
                    <input type="hidden" name="location_image[<?php echo $image_row; ?>][image]" value="<?php echo $location_image['image']; ?>" id="image<?php echo $image_row; ?>" />
                    <br />
                    <a onclick="image_upload('image<?php echo $image_row; ?>', 'thumb<?php echo $image_row; ?>');"><?php echo $text_browse; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('#thumb<?php echo $image_row; ?>').attr('src', '<?php echo $no_image; ?>'); $('#image<?php echo $image_row; ?>').attr('value', '');"><?php echo $text_clear; ?></a></div></td>
                <td class="right"><input type="text" name="location_image[<?php echo $image_row; ?>][sort_order]" value="<?php echo $location_image['sort_order']; ?>" size="2" /></td>
                <td class="left"><a onclick="$('#image-row<?php echo $image_row; ?>').remove();" class="button btn btn-primary"><?php echo $button_remove; ?></a></td>
              </tr>
            </tbody>
            <?php $image_row++; ?>
            <?php } ?>
            <tfoot>
              <tr>
                <td colspan="2"></td>
                <td class="left"><a onclick="addImage();" class="button btn btn-primary"><?php echo $button_add_image; ?></a></td>
              </tr>
            </tfoot>
          </table>
        </div>
    </form>
  </div>
</div>
<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script> 
<script type="text/javascript"><!--
CKEDITOR.replace('Details', {
	filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
});
//--></script> 
<script type="text/javascript">

	
	var image_row = <?php echo $image_row; ?>;
	function addImage() {
		html  = '<tbody id="image-row' + image_row + '">';
		html += '  <tr>';
		html += '    <td class="left"><div class="image"><img src="<?php echo $no_image; ?>" alt="" id="thumb' + image_row + '" /><input type="hidden" name="location_image[' + image_row + '][image]" value="" id="image' + image_row + '" /><br /><a onclick="image_upload(\'image' + image_row + '\', \'thumb' + image_row + '\');"><?php echo $text_browse; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$(\'#thumb' + image_row + '\').attr(\'src\', \'<?php echo $no_image; ?>\'); $(\'#image' + image_row + '\').attr(\'value\', \'\');"><?php echo $text_clear; ?></a></div></td>';
		html += '    <td class="right"><input type="text" name="location_image[' + image_row + '][sort_order]" value="0" /></td>';
		html += '    <td class="left"><a onclick="$(\'#image-row' + image_row  + '\').remove();" class="button btn btn-primary"><?php echo $button_remove; ?></a></td>';
		html += '  </tr>';
		html += '</tbody>';
		
		$('#images tfoot').before(html);
		
		image_row++;
	}
</script> 
<script type="text/javascript"><!--
function image_upload(field, thumb) {
    $.startImageManager(field, thumb);
};
//--></script>
<?php echo $footer; ?>