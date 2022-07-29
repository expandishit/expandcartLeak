<?php echo $header; ?><?php echo $column_left; ?>
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
          <button type="submit" form="form-shortcode" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
          <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
        </div>
      </div>

      <div class="content">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_form; ?></h4>
          </div>
          <div class="panel-body">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-shortcode" class="form-horizontal">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                <div class="col-sm-4">
                  <select name="status" id="input-status" class="form-control">
                    <?php if ($status == 1) { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <?php } ?>
                  </select>
                  <input type="hidden" name="store_id" value="<?php echo $store_id; ?>" class="form-control" />
                   <input type="hidden" name="code" value="<?php echo $code; ?>" class="form-control" />
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label s_help" for="input-bcc"><?php echo $entry_bcc; ?><b><?php echo $help_bcc; ?></b></label>
                <div class="col-sm-4">
                  <input type="text" name="bcc" value="<?php echo $bcc; ?>" placeholder="<?php echo $entry_bcc; ?>" id="input-bcc" class="form-control" />
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label s_help" for="input-product"><?php echo $entry_product; ?><b><?php echo $help_product; ?></b></label>
                <div class="col-sm-2">
                  <select name="product" id="input-product" class="form-control">
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <?php foreach ($products as $key => $value) { ?>
                    <?php if ($key == $product) { ?>
                    <option value="<?php echo $key; ?>" selected="selected"><?php echo $value; ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                    <?php } ?>
                    <?php } ?>
                  </select>
                </div>
                <div class="col-sm-2">
                  <div class="input-group">
                    <span class="input-group-addon"><?php echo $entry_product_limit; ?></span>
                    <input type="text" name="product_limit" value="<?php echo $product_limit; ?>" placeholder="<?php echo $entry_product_limit; ?>" id="input-product-limit" class="form-control" />
                  </div>
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-12">
                  <ul class="nav nav-tabs" id="language">
                    <?php foreach ($languages as $language) { ?>
                    <li><a href="#language<?php echo $language['language_id']; ?>" data-toggle="tab"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></a></li>
                    <?php } ?>
                  </ul>
                  <div class="tab-content">
                    <?php foreach ($languages as $language) { ?>
                    <div class="tab-pane" id="language<?php echo $language['language_id']; ?>">
                      <div class="form-group">
                        <label class="col-sm-3 control-label" for="input-subject<?php echo $language['language_id']; ?>"><?php echo $entry_subject; ?></label>
                        <div class="col-sm-9">
                          <input type="text" name="template_description[<?php echo $language['language_id']; ?>][subject]" value="<?php echo (isset($template_description[$language['language_id']]['subject'])) ? $template_description[$language['language_id']]['subject'] : ''; ?>" placeholder="<?php echo $entry_subject; ?>" id="input-subject<?php echo $language['language_id']; ?>" class="form-control" />
                          <?php if (isset($error_subject[$language['language_id']])) { ?>
                          <div class="text-danger"><?php echo $error_subject[$language['language_id']]; ?></div>
                          <?php } ?>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="col-sm-3 col-sm-offset-9">
                          <select name="template" id="template" data-language-id="<?php echo $language['language_id']; ?>" class="form-control">
                            <option value="0"><?php echo $text_template; ?></option>
                            <?php foreach ($templates as $template) { ?>
                            <option value="<?php echo $template; ?>"><?php echo $template; ?></option>
                            <?php } ?>
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label s_help" for="input-description<?php echo $language['language_id']; ?>"><?php echo $entry_description; ?><b><?php echo $text_shortcode; ?></b></label>
                        <div class="col-sm-9">
                          <textarea name="template_description[<?php echo $language['language_id']; ?>][description]" rows="12" placeholder="<?php echo $entry_description; ?>" id="input-description<?php echo $language['language_id']; ?>" class="form-control"><?php echo isset($template_description[$language['language_id']]['description']) ? $template_description[$language['language_id']]['description'] : ''; ?></textarea>
                          <?php if (isset($error_description[$language['language_id']])) { ?>
                          <div class="text-danger"><?php echo $error_description[$language['language_id']]; ?></div>
                          <?php } ?>
                        </div>
                      </div>
                    </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
</div>
<?php if (version_compare(VERSION, '2.0') < 0) { ?>
<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="view/javascript/ckeditor/adapters/jquery.js"></script>
<?php } ?>
<script type="text/javascript"><!--
<?php foreach ($languages as $language) { ?>
<?php if (version_compare(VERSION, '2.0') < 0) { ?>
CKEDITOR.replace('input-description<?php echo $language['language_id']; ?>', {
	filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	height: 500
});
<?php } else { ?>
$('#input-description<?php echo $language['language_id']; ?>').summernote({height: 550});
<?php } ?>
<?php } ?>

$('#language a:first').tab('show');
//--></script>
<script type="text/javascript"><!--
$('select[name="template"]').bind('change', function() {
  var file = $(this).val();
  var language_id = $(this).data('language-id');

  if (file != '0') {
    $.ajax({
      url: 'index.php?route=module/custom_email_templates/loadtemplate&file=' + file + '&token=<?php echo $token; ?>&format=raw',
      dataType: 'json',
      success: function(json) {
        if (json['warning']) {
          alert(json['warning']);
        } else {
          $('#language' + language_id + ' input[name$="[subject]"]').val(json['subject']);
          $('#language' + language_id + ' textarea[name$="[description]"]').html(json['description']);

		  <?php if (version_compare(VERSION, '2.0') < 0) { ?>
		  CKEDITOR.instances['input-description' + language_id].insertHtml($('#language' + language_id + ' textarea[name$="[description]"]').text());
		  <?php } else { ?>
          $('#input-description' + language_id).code($('#language' + language_id + ' textarea[name$="[description]"]').text());
		  <?php } ?>
        }
      }
    });
  }
});
//--></script>
<?php echo $footer; ?>