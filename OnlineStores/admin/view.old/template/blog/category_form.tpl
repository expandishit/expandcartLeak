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
            <td><span class="required">*</span> إسم التصنيف</td>

            <td><input type="text" name="name" value="<?php echo $name; ?>" />

          </tr>

<tr>
  <td><?php echo $entry_description; ?></td>

  <td><textarea name="category_description[<?php echo $language['language_id']; ?>][description]" id="description<?php echo $language['language_id']; ?>"><?php echo isset($category_description[$language['language_id']]) ? $category_description[$language['language_id']]['description'] : ''; ?></textarea></td>

</tr>
        </table>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
        CKEDITOR.replace('description<?php echo $language['language_id']; ?>', {
            filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
        });
    </script>
<script type="text/javascript"><!--
    $('input[name=\'path\']').autocomplete({
        delay: 500,
        source: function(request, response) {
            $.ajax({
                url: 'index.php?route=catalog/category/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
                dataType: 'json',
                success: function(json) {
                    json.unshift({
                        'category_id':  0,
                        'name':  '<?php echo $text_none; ?>'
                    });

                    response($.map(json, function(item) {
                        return {
                            label: item.name,
                            value: item.category_id
                        }
                    }));
                }
            });
        },
        select: function(event, ui) {
            $('input[name=\'path\']').val(ui.item.label);
            $('input[name=\'parent_id\']').val(ui.item.value);

            return false;
        },
        focus: function(event, ui) {
            return false;
        }
    });
    //--></script>
<script type="text/javascript"><!--
    // Filter
    $('input[name=\'filter\']').autocomplete({
        delay: 500,
        source: function(request, response) {
            $.ajax({
                url: 'index.php?route=catalog/filter/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
                dataType: 'json',
                success: function(json) {
                    response($.map(json, function(item) {
                        return {
                            label: item.name,
                            value: item.filter_id
                        }
                    }));
                }
            });
        },
        select: function(event, ui) {
            $('#category-filter' + ui.item.value).remove();

            $('#category-filter').append('<div id="category-filter' + ui.item.value + '">' + ui.item.label + '<i class="fa fa-times"></i><input type="hidden" name="category_filter[]" value="' + ui.item.value + '" /></div>');

            $('#category-filter div:odd').attr('class', 'odd');
            $('#category-filter div:even').attr('class', 'even');

            return false;
        },
        focus: function(event, ui) {
            return false;
        }
    });

    $(document).on('click', '#category-filter div i', function() {
        $(this).parent().remove();

        $('#category-filter div:odd').attr('class', 'odd');
        $('#category-filter div:even').attr('class', 'even');
    });

    $(document).on('change', 'input[name="path"]', function() {
        if ($(this).val() == '')
            $('input[name="parent_id"]').val('');
    })
    //--></script>
<script type="text/javascript"><!--
    function image_upload(field, thumb) {
        $.startImageManager(field, thumb);
    };
    //--></script>
<script type="text/javascript"><!--
    $('#tabs a').tabs();
    $('#languages a').tabs();
    //--></script>
<?php echo $footer; ?>