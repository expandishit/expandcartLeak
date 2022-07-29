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
          <button type="submit" form="form-custom-email-templates" data-toggle="tooltip" title="<?php echo $button_save; ?>" id="button-save" class="btn btn-primary"><i class="fa fa-save"></i></button>
          <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
      </div>
  </div>

  <div class="content">
      <ul class="nav nav-tabs" id="general-tabs">
          <li class="active"><a href="#tab-template" data-toggle="tab"><?php echo $tab_template; ?></a></li>
          <li><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
          <li><a href="#tab-history" data-toggle="tab"><?php echo $tab_history; ?></a></li>
      </ul>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-custom-email-templates" class="form-horizontal">
          <div class="tab-content">
              <div class="tab-pane" id="tab-general">
              <div class="row">
              <div class="col-sm-2">
                  <ul class="nav nav-pills nav-stacked tab-switcher" id="setting">
                      <li><a href="#tab-section-general" data-toggle="tab"><?php echo $text_tab_general; ?></a></li>
                      <li><a href="#tab-section-showcase" data-toggle="tab"><?php echo $text_tab_showcase; ?></a></li>
                      <li><a href="#tab-section-predefined" data-toggle="tab"><?php echo $text_tab_predefined; ?></a></li>
                      <li><a href="#tab-section-order-status" data-toggle="tab"><?php echo $text_tab_order_status; ?></a></li>
                      <li><a href="#tab-section-return-status" data-toggle="tab"><?php echo $text_tab_return_status; ?></a></li>
                      <li><a href="#tab-section-taxes" data-toggle="tab"><?php echo $text_tab_tax; ?></a></li>
                      <li><a href="#tab-section-totals" data-toggle="tab"><?php echo $text_tab_total; ?></a></li>
                      <li><a href="#tab-section-comment" data-toggle="tab"><?php echo $text_tab_comment; ?></a></li>
                  </ul>
              </div>
              <div class="col-sm-10">
              <div class="tab-content">
              <div class="tab-pane" id="tab-section-general">
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-module-status"><?php echo $entry_module_status; ?></label>
                  <div class="col-sm-7">
                      <select name="custom_email_templates[status]" id="input-module-status" class="form-control">
                          <?php if ($status == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label s_help" for="input-generate-invoice-number"><?php echo $entry_generate_invoice_number; ?><b><?php echo $help_generate_invoice_number; ?></b></label>
                  <div class="col-sm-7">
                      <div class="well well-sm" style="height: 150px; overflow: auto;">
                          <?php foreach ($order_statuses as $order_status) { ?>
                          <div class="checkbox">
                              <label>
                                  <?php if (in_array($order_status['order_status_id'], $generate_invoice_number)) { ?>
                                  <input type="checkbox" name="custom_email_templates[generate_invoice_number][]" value="<?php echo $order_status['order_status_id']; ?>" checked="checked" />
                                  <?php echo $order_status['name']; ?>
                                  <?php } else { ?>
                                  <input type="checkbox" name="custom_email_templates[generate_invoice_number][]" value="<?php echo $order_status['order_status_id']; ?>" />
                                  <?php echo $order_status['name']; ?>
                                  <?php } ?>
                              </label>
                          </div>
                          <?php } ?>
                      </div>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label s_help" for="input-attach-invoice"><?php echo $entry_attach_invoice; ?><b><?php echo $help_attach_invoice; ?></b></label>
                  <div class="col-sm-7">
                      <div class="well well-sm" style="height: 150px; overflow: auto;">
                          <?php foreach ($order_statuses as $order_status) { ?>
                          <div class="checkbox">
                              <label>
                                  <?php if (in_array($order_status['order_status_id'], (array)$attach_invoice)) { ?>
                                  <input type="checkbox" name="custom_email_templates[attach_invoice][]" value="<?php echo $order_status['order_status_id']; ?>" checked="checked" />
                                  <?php echo $order_status['name']; ?>
                                  <?php } else { ?>
                                  <input type="checkbox" name="custom_email_templates[attach_invoice][]" value="<?php echo $order_status['order_status_id']; ?>" />
                                  <?php echo $order_status['name']; ?>
                                  <?php } ?>
                              </label>
                          </div>
                          <?php } ?>
                      </div>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-invoice-template"><?php echo $entry_invoice_template; ?></label>
                  <div class="col-sm-5">
                      <select name="custom_email_templates[invoice_template]" id="input-invoice-template" class="form-control">
                          <?php foreach ($invoice_templates as $template) { ?>
                          <?php if ($invoice_template == $template) { ?>
                          <option value="<?php echo $template; ?>" selected="selected"><?php echo $template; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $template; ?>"><?php echo $template; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-2">
                      <img src="<?php echo HTTP_CATALOG . 'catalog/view/theme/default/template/custom_email_templates/invoice/' . str_replace('.tpl', '.jpg', $invoice_template); ?>" id="img-invoice-preview" class="img-thumbnail" />
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-notify-admin-status"><?php echo $entry_notify_admin_status; ?></label>
                  <div class="col-sm-7">
                      <select name="custom_email_templates[notify_admin_status]" id="input-notify-admin-status" class="form-control">
                          <?php if ($notify_admin_status == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label s_help" for="input-bcc"><?php echo $entry_bcc; ?><b><?php echo $help_bcc; ?></b></label>
                  <div class="col-sm-7">
                      <input type="text" name="custom_email_templates[bcc]" value="<?php echo $bcc; ?>" placeholder="<?php echo $entry_bcc; ?>" id="input-bcc" class="form-control" />
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-track-status"><?php echo $entry_track_status; ?></label>
                  <div class="col-sm-7">
                      <select name="custom_email_templates[track_status]" id="input-track-status" class="form-control">
                          <?php if ($track_status == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-date-format"><?php echo $entry_date_format; ?></label>
                  <div class="col-sm-7">
                      <select name="custom_email_templates[date_format]" id="input-date-format" class="form-control">
                          <?php foreach ($date_formats as $format) { ?>
                          <?php if ($date_format == $format) { ?>
                          <option value="<?php echo $format; ?>" selected="selected"><?php echo $format; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $format; ?>"><?php echo $format; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label s_help" for="input-plain-text-status"><?php echo $entry_plain_text_status; ?><b><?php echo $help_plain_text_status; ?></b></label>
                  <div class="col-sm-7">
                      <select name="custom_email_templates[plain_text_status]" id="input-plain-text-status" class="form-control">
                          <?php if ($plain_text_status == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                          <option value="0"><?php echo $text_no; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_yes; ?></option>
                          <option value="0" selected="selected"><?php echo $text_no; ?></option>
                          <?php } ?>
                      </select>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label s_help" for="input-layout-width"><?php echo $entry_layout_width; ?><b><?php echo $help_layout_width; ?></b></label>
                  <div class="col-sm-7">
                      <input type="text" name="custom_email_templates[layout_width]" value="<?php echo $layout_width; ?>" placeholder="<?php echo $entry_layout_width; ?>" id="input-layout-width" class="form-control" />
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-text-color"><?php echo $entry_text_color; ?></label>
                  <div class="col-sm-7">
                      <input type="text" name="custom_email_templates[text_color]" value="<?php echo $text_color; ?>" placeholder="<?php echo $entry_text_color; ?>" id="input-text-color" class="form-control" />
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-link-color"><?php echo $entry_link_color; ?></label>
                  <div class="col-sm-7">
                      <input type="text" name="custom_email_templates[link_color]" value="<?php echo $link_color; ?>" placeholder="<?php echo $entry_link_color; ?>" id="input-link-color" class="form-control" />
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-background-image"><?php echo $entry_background_image; ?></label>
                  <div class="col-sm-3">
                      <a id="thumb-background-image" data-toggle="image" class="img-thumbnail" <?php if (version_compare(VERSION, '2.0') < 0) { ?>onclick="image_upload('input-background-image', 'thumb-background-image');<?php } ?>"><img src="<?php echo $background_image_thumb ? $background_image_thumb : $placeholder ; ?>" alt="" title="" data-placeholder="<?php echo $placeholder; ?>" /></a>
                      <input type="hidden" name="custom_email_templates[background_image]" value="<?php echo $background_image; ?>" id="input-background-image" />
                      <?php if (version_compare(VERSION, '2.0') < 0) { ?><a onclick="$('#thumb-background-image img').attr('src', '<?php echo $placeholder; ?>'); $('#input-background-image').attr('value', '');"><?php echo $text_clear; ?></a><?php } ?>
                  </div>
                  <label class="col-sm-2 control-label" for="input-background-repeat"><?php echo $entry_background_repeat; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[background_repeat]" id="input-background-repeat" class="form-control">
                          <?php foreach ($repeats as $repeat) { ?>
                          <?php if ($repeat== $background_repeat) { ?>
                          <option value="<?php echo $repeat; ?>" selected="selected"><?php echo $repeat; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $repeat; ?>"><?php echo $repeat; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-background-color"><?php echo $entry_background; ?></label>
                  <div class="col-sm-7">
                      <input type="text" name="custom_email_templates[background_color]" value="<?php echo $background_color; ?>" placeholder="<?php echo $entry_background; ?>" id="input-background-color" class="form-control" />
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-language-tax-id"><?php echo $entry_language_tax_id; ?></label>
                  <div class="col-sm-7">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[mlanguage][<?php echo $language['language_id']; ?>][tax_id]" value="<?php echo isset($mlanguage[$language['language_id']]['tax_id']) ? $mlanguage[$language['language_id']]['tax_id'] : $mlanguage['tax_id']; ?>" placeholder="<?php echo $entry_language_tax_id; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-language-company-id"><?php echo $entry_language_company_id; ?></label>
                  <div class="col-sm-7">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[mlanguage][<?php echo $language['language_id']; ?>][company_id]" value="<?php echo isset($mlanguage[$language['language_id']]['company_id']) ? $mlanguage[$language['language_id']]['company_id'] : $mlanguage['company_id']; ?>" placeholder="<?php echo $entry_language_company_id; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              </div>
              <div class="tab-pane" id="tab-section-showcase">
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-product-image-dimension"><?php echo $entry_showcase_image_dimension; ?></label>
                      <div class="col-sm-3">
                          <input type="text" name="custom_email_templates[showcase][product_image_width]" value="<?php echo $showcase['product_image_width']; ?>" placeholder="<?php echo $entry_width; ?>" id="input-showcase-image-dimension" class="form-control" />
                      </div>
                      <div class="col-sm-3">
                          <input type="text" name="custom_email_templates[showcase][product_image_height]" value="<?php echo $showcase['product_image_height']; ?>" placeholder="<?php echo $entry_height; ?>" id="input-showcase-image-dimension" class="form-control" />
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-showcase-product-name"><?php echo $entry_showcase_product_name; ?></label>
                      <div class="col-sm-7">
                          <div class="input-group">
                              <span class="input-group-addon"><?php echo $entry_size; ?></span>
                              <input type="text" name="custom_email_templates[showcase][product_name_size]" value="<?php echo $showcase['product_name_size']; ?>" placeholder="<?php echo $entry_size; ?>" id="input-showcase-product-name" class="form-control" />
                              <span class="input-group-addon" style="border-left: 0; border-right: 0;"><?php echo $entry_color; ?></span>
                              <input type="text" name="custom_email_templates[showcase][product_name_color]" value="<?php echo $showcase['product_name_color']; ?>" placeholder="<?php echo $entry_color; ?>" id="input-showcase-product-name" class="form-control" />
                              <span class="input-group-addon" style="border-left: 0; border-right: 0;"><?php echo $entry_weight; ?></span>
                              <input type="text" name="custom_email_templates[showcase][product_name_weight]" value="<?php echo $showcase['product_name_weight']; ?>" placeholder="<?php echo $entry_weight; ?>" id="input-showcase-product-name" class="form-control" />
                          </div>
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-showcase-product-description"><?php echo $entry_showcase_product_description; ?></label>
                      <div class="col-sm-7">
                          <div class="input-group">
                              <span class="input-group-addon"><?php echo $entry_size; ?></span>
                              <input type="text" name="custom_email_templates[showcase][product_description_size]" value="<?php echo $showcase['product_description_size']; ?>" placeholder="<?php echo $entry_size; ?>" id="input-showcase-product-description" class="form-control" />
                              <span class="input-group-addon" style="border-left: 0; border-right: 0;"><?php echo $entry_color; ?></span>
                              <input type="text" name="custom_email_templates[showcase][product_description_color]" value="<?php echo $showcase['product_description_color']; ?>" placeholder="<?php echo $entry_color; ?>" id="input-showcase-product-description" class="form-control" />
                              <span class="input-group-addon" style="border-left: 0; border-right: 0;"><?php echo $entry_weight; ?></span>
                              <input type="text" name="custom_email_templates[showcase][product_description_weight]" value="<?php echo $showcase['product_description_weight']; ?>" placeholder="<?php echo $entry_weight; ?>" id="input-showcase-product-description" class="form-control" />
                          </div>
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-showcase-button"><?php echo $entry_showcase_button; ?></label>
                      <div class="col-sm-7">
                          <div class="input-group">
                              <span class="input-group-addon"><?php echo $entry_size; ?></span>
                              <input type="text" name="custom_email_templates[showcase][button_size]" value="<?php echo $showcase['button_size']; ?>" placeholder="<?php echo $entry_size; ?>" id="input-showcase-button" class="form-control" />
                              <span class="input-group-addon" style="border-left: 0; border-right: 0;"><?php echo $entry_color; ?></span>
                              <input type="text" name="custom_email_templates[showcase][button_color]" value="<?php echo $showcase['button_color']; ?>" placeholder="<?php echo $entry_color; ?>" id="input-showcase-button" class="form-control" />
                              <span class="input-group-addon" style="border-left: 0; border-right: 0;"><?php echo $entry_background; ?></span>
                              <input type="text" name="custom_email_templates[showcase][button_background]" value="<?php echo $showcase['button_background']; ?>" placeholder="<?php echo $entry_background; ?>" id="input-showcase-button" class="form-control" />
                          </div>
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-showcase-button-text"><?php echo $entry_showcase_button_text; ?></label>
                      <div class="col-sm-7">
                          <?php foreach ($languages as $language) { ?>
                          <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                              <input type="text" name="custom_email_templates[showcase][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($showcase['text'])) ? ((isset($showcase['text'][$language['language_id']])) ? $showcase['text'][$language['language_id']] : '') : $showcase['text']; ?>" placeholder="<?php echo $entry_button_text; ?>" class="form-control" />
                          </div>
                          <?php } ?>
                      </div>
                  </div>
              </div>
              <div class="tab-pane" id="tab-section-predefined">
                  <div class="form-group">
                      <div class="col-sm-9"><div class="alert alert-info"><i class="fa fa-exclamation-circle"></i> <?php echo $text_setting_table; ?></div></div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-table-column-name-font"><?php echo $entry_column_name_font; ?></label>
                      <div class="col-sm-7">
                          <div class="input-group">
                              <span class="input-group-addon"><?php echo $entry_size; ?></span>
                              <input type="text" name="custom_email_templates[table][font_size]" value="<?php echo $table['font_size']; ?>" placeholder="<?php echo $entry_size; ?>" id="input-table-column-name-font" class="form-control" />
                              <span class="input-group-addon" style="border-left: 0; border-right: 0;"><?php echo $entry_color; ?></span>
                              <input type="text" name="custom_email_templates[table][font_color]" value="<?php echo $table['font_color']; ?>" placeholder="<?php echo $entry_color; ?>" id="input-table-column-name-font" class="form-control" />
                              <span class="input-group-addon" style="border-left: 0; border-right: 0;"><?php echo $entry_weight; ?></span>
                              <input type="text" name="custom_email_templates[table][font_weight]" value="<?php echo $table['font_weight']; ?>" placeholder="<?php echo $entry_weight; ?>" id="input-table-column-name-font" class="form-control" />
                              <span class="input-group-addon" style="border-left: 0; border-right: 0;"><?php echo $entry_background; ?></span>
                              <input type="text" name="custom_email_templates[table][background]" value="<?php echo $table['background']; ?>" placeholder="<?php echo $entry_weight; ?>" id="input-table-column-name-font" class="form-control" />
                          </div>
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-table-padding"><?php echo $entry_padding; ?></label>
                      <div class="col-sm-7">
                          <div class="input-group">
                              <span class="input-group-addon"><?php echo $entry_top; ?></span>
                              <input type="text" name="custom_email_templates[table][padding_top]" value="<?php echo $table['padding_top']; ?>" placeholder="<?php echo $entry_top; ?>" id="input-table-padding" class="form-control" />
                              <span class="input-group-addon" style="border-left: 0; border-right: 0;"><?php echo $entry_right; ?></span>
                              <input type="text" name="custom_email_templates[table][padding_right]" value="<?php echo $table['padding_right']; ?>" placeholder="<?php echo $entry_right; ?>" id="input-table-padding" class="form-control" />
                              <span class="input-group-addon" style="border-left: 0; border-right: 0;"><?php echo $entry_bottom; ?></span>
                              <input type="text" name="custom_email_templates[table][padding_bottom]" value="<?php echo $table['padding_bottom']; ?>" placeholder="<?php echo $entry_bottom; ?>" id="input-table-padding" class="form-control" />
                              <span class="input-group-addon" style="border-left: 0; border-right: 0;"><?php echo $entry_left; ?></span>
                              <input type="text" name="custom_email_templates[table][padding_left]" value="<?php echo $table['padding_left']; ?>" placeholder="<?php echo $entry_left; ?>" id="input-table-padding" class="form-control" />
                          </div>
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-table-border"><?php echo $entry_border; ?></label>
                      <div class="col-sm-3">
                          <select name="custom_email_templates[table][border_style]" id="input-table-border" class="form-control">
                              <option value="0"><?php echo $text_no; ?></option>
                              <?php foreach ($lines as $line) { ?>
                              <?php if ($line== $table['border_style']) { ?>
                              <option value="<?php echo $line; ?>" selected="selected"><?php echo $line; ?></option>
                              <?php } else { ?>
                              <option value="<?php echo $line; ?>"><?php echo $line; ?></option>
                              <?php } ?>
                              <?php } ?>
                          </select>
                      </div>
                      <div class="col-sm-4">
                          <div class="input-group">
                              <span class="input-group-addon"><?php echo $entry_color; ?></span>
                              <input type="text" name="custom_email_templates[table][border_color]" value="<?php echo $table['border_color']; ?>" placeholder="<?php echo $entry_color; ?>" id="input-table-border" class="form-control" />
                              <span class="input-group-addon" style="border-left: 0; border-right: 0;"><?php echo $entry_size; ?></span>
                              <input type="text" name="custom_email_templates[table][border_size]" value="<?php echo $table['border_size']; ?>" placeholder="<?php echo $entry_size; ?>" id="input-table-border" class="form-control" />
                          </div>
                      </div>
                  </div>
              </div>
              <div class="tab-pane" id="tab-section-order-status">
              <div class="form-group">
                  <div class="col-sm-9">
                      <div class="alert alert-info"><i class="fa fa-exclamation-circle"></i> <?php echo $text_setting_order_status; ?></div>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-products-section-column-image-status"><?php echo $entry_column_image_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[products_section][column][image][status]" id="input-products-section-column-image-status" class="form-control">
                          <?php if ($products_section['column']['image']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-products-section-column-image-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[products_section][column][image][align]" id="input-products-section-column-image-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $products_section['column']['image']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[products_section][column][image][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($products_section['column']['image']['text'])) ? ((isset($products_section['column']['image']['text'][$language['language_id']])) ? $products_section['column']['image']['text'][$language['language_id']] : '') : $products_section['column']['image']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-products-section-column-image-dimension"><?php echo $entry_column_image_dimension; ?></label>
                  <div class="col-sm-3">
                      <input type="text" name="custom_email_templates[products_section][column][image][image_width]" value="<?php echo $products_section['column']['image']['image_width']; ?>" placeholder="<?php echo $entry_width; ?>" id="input-products-section-column-image-dimension" class="form-control" />
                  </div>
                  <div class="col-sm-3">
                      <input type="text" name="custom_email_templates[products_section][column][image][image_height]" value="<?php echo $products_section['column']['image']['image_height']; ?>" placeholder="<?php echo $entry_height; ?>" id="input-products-section-column-image-dimension" class="form-control" />
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-products-section-column-product-status"><?php echo $entry_column_product_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[products_section][column][product][status]" id="input-products-section-column-product-status" class="form-control">
                          <?php if ($products_section['column']['product']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-products-section-column-product-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[products_section][column][product][align]" id="input-products-section-column-product-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $products_section['column']['product']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[products_section][column][product][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($products_section['column']['product']['text'])) ? ((isset($products_section['column']['product']['text'][$language['language_id']])) ? $products_section['column']['product']['text'][$language['language_id']] : '') : $products_section['column']['product']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-products-section-column-product-link-status"><?php echo $entry_column_product_link_status; ?></label>
                  <div class="col-sm-7">
                      <select name="custom_email_templates[products_section][column][product][link_status]" id="input-products-section-column-product-link-status" class="form-control">
                          <?php if ($products_section['column']['product']['link_status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                          <option value="0"><?php echo $text_no; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_yes; ?></option>
                          <option value="0" selected="selected"><?php echo $text_no; ?></option>
                          <?php } ?>
                      </select>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-products-section-column-quantity-status"><?php echo $entry_column_quantity_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[products_section][column][quantity][status]" id="input-products-section-column-quantity-status" class="form-control">
                          <?php if ($products_section['column']['quantity']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-products-section-column-quantity-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[products_section][column][quantity][align]" id="input-products-section-column-quantity-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $products_section['column']['quantity']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[products_section][column][quantity][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($products_section['column']['quantity']['text'])) ? ((isset($products_section['column']['quantity']['text'][$language['language_id']])) ? $products_section['column']['quantity']['text'][$language['language_id']] : '') : $products_section['column']['quantity']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-products-section-column-model-status"><?php echo $entry_column_model_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[products_section][column][model][status]" id="input-products-section-column-model-status" class="form-control">
                          <?php if ($products_section['column']['model']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-products-section-column-model-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[products_section][column][model][align]" id="input-products-section-column-model-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $products_section['column']['model']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[products_section][column][model][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($products_section['column']['model']['text'])) ? ((isset($products_section['column']['model']['text'][$language['language_id']])) ? $products_section['column']['model']['text'][$language['language_id']] : '') : $products_section['column']['model']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-products-section-column-sku-status"><?php echo $entry_column_sku_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[products_section][column][sku][status]" id="input-products-section-column-sku-status" class="form-control">
                          <?php if ($products_section['column']['sku']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-products-section-column-sku-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[products_section][column][sku][align]" id="input-products-section-column-sku-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $products_section['column']['sku']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[products_section][column][sku][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($products_section['column']['sku']['text'])) ? ((isset($products_section['column']['sku']['text'][$language['language_id']])) ? $products_section['column']['sku']['text'][$language['language_id']] : '') : $products_section['column']['sku']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-products-section-column-upc-status"><?php echo $entry_column_upc_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[products_section][column][upc][status]" id="input-products-section-column-upc-status" class="form-control">
                          <?php if ($products_section['column']['upc']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-products-section-column-upc-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[products_section][column][upc][align]" id="input-products-section-column-upc-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $products_section['column']['upc']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[products_section][column][upc][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($products_section['column']['upc']['text'])) ? ((isset($products_section['column']['upc']['text'][$language['language_id']])) ? $products_section['column']['upc']['text'][$language['language_id']] : '') : $products_section['column']['upc']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-products-section-column-attribute-status"><?php echo $entry_column_attribute_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[products_section][column][attribute][status]" id="input-products-section-column-attribute-status" class="form-control">
                          <?php if ($products_section['column']['attribute']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-products-section-column-attribute-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[products_section][column][attribute][align]" id="input-products-section-column-attribute-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $products_section['column']['attribute']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[products_section][column][attribute][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($products_section['column']['attribute']['text'])) ? ((isset($products_section['column']['attribute']['text'][$language['language_id']])) ? $products_section['column']['attribute']['text'][$language['language_id']] : '') : $products_section['column']['attribute']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-products-section-column-price-status"><?php echo $entry_column_price_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[products_section][column][price][status]" id="input-products-section-column-price-status" class="form-control">
                          <?php if ($products_section['column']['price']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-products-section-column-price-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[products_section][column][price][align]" id="input-products-section-column-price-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $products_section['column']['price']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[products_section][column][price][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($products_section['column']['price']['text'])) ? ((isset($products_section['column']['price']['text'][$language['language_id']])) ? $products_section['column']['price']['text'][$language['language_id']] : '') : $products_section['column']['price']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-products-section-column-price-gross-status"><?php echo $entry_column_price_gross_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[products_section][column][price_gross][status]" id="input-products-section-column-price-gross-status" class="form-control">
                          <?php if ($products_section['column']['price_gross']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-products-section-column-price-gross-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[products_section][column][price_gross][align]" id="input-products-section-column-price-gross-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $products_section['column']['price_gross']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[products_section][column][price_gross][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($products_section['column']['price_gross']['text'])) ? ((isset($products_section['column']['price_gross']['text'][$language['language_id']])) ? $products_section['column']['price_gross']['text'][$language['language_id']] : '') : $products_section['column']['price_gross']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-products-section-column-tax-status"><?php echo $entry_column_tax_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[products_section][column][tax][status]" id="input-products-section-column-tax-status" class="form-control">
                          <?php if ($products_section['column']['tax']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-products-section-column-tax-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[products_section][column][tax][align]" id="input-products-section-column-tax-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $products_section['column']['tax']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[products_section][column][tax][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($products_section['column']['tax']['text'])) ? ((isset($products_section['column']['tax']['text'][$language['language_id']])) ? $products_section['column']['tax']['text'][$language['language_id']] : '') : $products_section['column']['tax']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-products-section-column-total-status"><?php echo $entry_column_total_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[products_section][column][total][status]" id="input-products-section-column-total-status" class="form-control">
                          <?php if ($products_section['column']['total']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-products-section-column-total-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[products_section][column][total][align]" id="input-products-section-column-total-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $products_section['column']['total']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[products_section][column][total][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($products_section['column']['total']['text'])) ? ((isset($products_section['column']['total']['text'][$language['language_id']])) ? $products_section['column']['total']['text'][$language['language_id']] : '') : $products_section['column']['total']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-products-section-column-total-gross-status"><?php echo $entry_column_total_gross_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[products_section][column][total_gross][status]" id="input-products-section-column-total-gross-status" class="form-control">
                          <?php if ($products_section['column']['total_gross']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-products-section-column-total-gross-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[products_section][column][total_gross][align]" id="input-products-section-column-total-gross-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $products_section['column']['total_gross']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[products_section][column][total_gross][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($products_section['column']['total_gross']['text'])) ? ((isset($products_section['column']['total_gross']['text'][$language['language_id']])) ? $products_section['column']['total_gross']['text'][$language['language_id']] : '') : $products_section['column']['total_gross']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input--products-section-option-status"><?php echo $entry_product_option_status; ?></label>
                  <div class="col-sm-7">
                      <select name="custom_email_templates[products_section][option_status]" id="input-products-section-option-status" class="form-control">
                          <?php if ($products_section['option_status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input--products-section-totals-status"><?php echo $entry_product_totals_status; ?></label>
                  <div class="col-sm-7">
                      <select name="custom_email_templates[products_section][totals_status]" id="input-products-section-totals-status" class="form-control">
                          <?php if ($products_section['totals_status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
              </div>
              </div>
              <div class="tab-pane" id="tab-section-return-status">
              <div class="form-group">
                  <div class="col-sm-9">
                      <div class="alert alert-info"><i class="fa fa-exclamation-circle"></i> <?php echo $text_setting_return_status; ?></div>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-returns-section-column-product-status"><?php echo $entry_column_product_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[returns_section][column][product][status]" id="input-returns-section-column-product-status" class="form-control">
                          <?php if ($returns_section['column']['product']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-returns-section-column-product-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[returns_section][column][product][align]" id="input-returns-section-column-product-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $returns_section['column']['product']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[returns_section][column][product][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($returns_section['column']['product']['text'])) ? ((isset($returns_section['column']['product']['text'][$language['language_id']])) ? $returns_section['column']['product']['text'][$language['language_id']] : '') : $returns_section['column']['product']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-returns-section-column-model-status"><?php echo $entry_column_model_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[returns_section][column][model][status]" id="input-returns-section-column-model-status" class="form-control">
                          <?php if ($returns_section['column']['model']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-returns-section-column-model-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[returns_section][column][model][align]" id="input-returns-section-column-model-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $returns_section['column']['model']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[returns_section][column][model][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($returns_section['column']['model']['text'])) ? ((isset($returns_section['column']['model']['text'][$language['language_id']])) ? $returns_section['column']['model']['text'][$language['language_id']] : '') : $returns_section['column']['model']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-returns-section-column-quantity-status"><?php echo $entry_column_quantity_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[returns_section][column][quantity][status]" id="input-returns-section-column-quantity-status" class="form-control">
                          <?php if ($returns_section['column']['quantity']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-returns-section-column-quantity-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[returns_section][column][quantity][align]" id="input-returns-section-column-quantity-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $returns_section['column']['quantity']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[returns_section][column][quantity][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($returns_section['column']['quantity']['text'])) ? ((isset($returns_section['column']['quantity']['text'][$language['language_id']])) ? $returns_section['column']['quantity']['text'][$language['language_id']] : '') : $returns_section['column']['quantity']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-returns-section-column-reason-status"><?php echo $entry_column_reason_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[returns_section][column][reason][status]" id="input-returns-section-column-reason-status" class="form-control">
                          <?php if ($returns_section['column']['reason']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-returns-section-column-reason-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[returns_section][column][reason][align]" id="input-returns-section-column-reason-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $returns_section['column']['reason']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[returns_section][column][reason][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($returns_section['column']['reason']['text'])) ? ((isset($returns_section['column']['reason']['text'][$language['language_id']])) ? $returns_section['column']['reason']['text'][$language['language_id']] : '') : $returns_section['column']['reason']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-returns-section-column-opened-status"><?php echo $entry_column_opened_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[returns_section][column][opened][status]" id="input-returns-section-column-opened-status" class="form-control">
                          <?php if ($returns_section['column']['opened']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-returns-section-column-opened-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[returns_section][column][opened][align]" id="input-returns-section-column-opened-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $returns_section['column']['opened']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[returns_section][column][opened][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($returns_section['column']['opened']['text'])) ? ((isset($returns_section['column']['opened']['text'][$language['language_id']])) ? $returns_section['column']['opened']['text'][$language['language_id']] : '') : $returns_section['column']['opened']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-returns-section-column-action-status"><?php echo $entry_column_action_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[returns_section][column][action][status]" id="input-returns-section-column-action-status" class="form-control">
                          <?php if ($returns_section['column']['action']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-returns-section-column-action-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[returns_section][column][action][align]" id="input-returns-section-column-action-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $returns_section['column']['action']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[returns_section][column][action][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($returns_section['column']['action']['text'])) ? ((isset($returns_section['column']['action']['text'][$language['language_id']])) ? $returns_section['column']['action']['text'][$language['language_id']] : '') : $returns_section['column']['action']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-returns-section-column-return-status-status"><?php echo $entry_column_return_status_status; ?></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[returns_section][column][return_status][status]" id="input-returns-section-column-return-status-status" class="form-control">
                          <?php if ($returns_section['column']['return_status']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-returns-section-column-return-status-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[returns_section][column][return_status][align]" id="input-returns-section-column-return-status-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $returns_section['column']['return_status']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[returns_section][column][return_status][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($returns_section['column']['return_status']['text'])) ? ((isset($returns_section['column']['return_status']['text'][$language['language_id']])) ? $returns_section['column']['return_status']['text'][$language['language_id']] : '') : $returns_section['column']['return_status']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-2 control-label s_help" for="input-returns-section-column-comment-status"><?php echo $entry_column_comment_status; ?><b>Only for Opencart 1.5.1 - 1.5.1.3</b></label>
                  <div class="col-sm-2">
                      <select name="custom_email_templates[returns_section][column][comment][status]" id="input-returns-section-column-comment-status" class="form-control">
                          <?php if ($returns_section['column']['comment']['status'] == 1) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                      </select>
                  </div>
                  <label class="col-sm-1 control-label" for="input-returns-section-column-comment-align"><?php echo $entry_text_align; ?></label>
                  <div class="col-sm-1">
                      <select name="custom_email_templates[returns_section][column][comment][align]" id="input-returns-section-column-comment-align" class="form-control">
                          <?php foreach ($aligns as $key => $align) { ?>
                          <?php if ($key == $returns_section['column']['comment']['align']) { ?>
                          <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                          <?php } ?>
                          <?php } ?>
                      </select>
                  </div>
                  <div class="col-sm-3">
                      <?php foreach ($languages as $language) { ?>
                      <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                          <input type="text" name="custom_email_templates[returns_section][column][comment][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($returns_section['column']['comment']['text'])) ? ((isset($returns_section['column']['comment']['text'][$language['language_id']])) ? $returns_section['column']['comment']['text'][$language['language_id']] : '') : $returns_section['column']['comment']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                      </div>
                      <?php } ?>
                  </div>
              </div>
              </div>
              <div class="tab-pane" id="tab-section-taxes">
                  <div class="form-group">
                      <div class="col-sm-9">
                          <div class="alert alert-info"><i class="fa fa-exclamation-circle"></i> <?php echo $text_setting_tax; ?></div>
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-taxes-section-title-align"><?php echo $entry_taxes_section_title_align; ?></label>
                      <div class="col-sm-7">
                          <select name="custom_email_templates[taxes_section][title][align]" id="input-taxes-section-title-align" class="form-control">
                              <?php foreach ($aligns as $key => $align) { ?>
                              <?php if ($key == $taxes_section['title']['align']) { ?>
                              <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                              <?php } else { ?>
                              <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                              <?php } ?>
                              <?php } ?>
                          </select>
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-taxes-section-rate"><?php echo $entry_taxes_section_rate; ?></label>
                      <div class="col-sm-7">
                          <?php foreach ($languages as $language) { ?>
                          <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                              <input type="text" name="custom_email_templates[taxes_section][rate][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($taxes_section['rate']['text'])) ? ((isset($taxes_section['rate']['text'][$language['language_id']])) ? $taxes_section['rate']['text'][$language['language_id']] : '') : $taxes_section['rate']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                          </div>
                          <?php } ?>
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-taxes-section-rate-align"><?php echo $entry_taxes_section_rate_align; ?></label>
                      <div class="col-sm-7">
                          <select name="custom_email_templates[taxes_section][rate][align]" id="input-taxes-section-rate-align" class="form-control">
                              <?php foreach ($aligns as $key => $align) { ?>
                              <?php if ($key == $taxes_section['rate']['align']) { ?>
                              <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                              <?php } else { ?>
                              <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                              <?php } ?>
                              <?php } ?>
                          </select>
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-taxes-section-tax"><?php echo $entry_taxes_section_tax; ?></label>
                      <div class="col-sm-7">
                          <?php foreach ($languages as $language) { ?>
                          <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                              <input type="text" name="custom_email_templates[taxes_section][amount][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($taxes_section['amount']['text'])) ? ((isset($taxes_section['amount']['text'][$language['language_id']])) ? $taxes_section['amount']['text'][$language['language_id']] : '') : $taxes_section['amount']['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                          </div>
                          <?php } ?>
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-taxes-section-tax-align"><?php echo $entry_taxes_section_tax_align; ?></label>
                      <div class="col-sm-7">
                          <select name="custom_email_templates[taxes_section][amount][align]" id="input-taxes-section-tax-align" class="form-control">
                              <?php foreach ($aligns as $key => $align) { ?>
                              <?php if ($key == $taxes_section['amount']['align']) { ?>
                              <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                              <?php } else { ?>
                              <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                              <?php } ?>
                              <?php } ?>
                          </select>
                      </div>
                  </div>
              </div>
              <div class="tab-pane" id="tab-section-totals">
                  <div class="form-group">
                      <div class="col-sm-9">
                          <div class="alert alert-info"><i class="fa fa-exclamation-circle"></i> <?php echo $text_setting_total; ?></div>
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-totals-section-title-align"><?php echo $entry_totals_section_title_align; ?></label>
                      <div class="col-sm-7">
                          <select name="custom_email_templates[totals_section][title][align]" id="input-totals-section-title-align" class="form-control">
                              <?php foreach ($aligns as $key => $align) { ?>
                              <?php if ($key == $totals_section['title']['align']) { ?>
                              <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                              <?php } else { ?>
                              <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                              <?php } ?>
                              <?php } ?>
                          </select>
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-totals-section-amount-align"><?php echo $entry_totals_section_amount_align; ?></label>
                      <div class="col-sm-7">
                          <select name="custom_email_templates[totals_section][amount][align]" id="input-totals-section-amount-align" class="form-control">
                              <?php foreach ($aligns as $key => $align) { ?>
                              <?php if ($key == $totals_section['amount']['align']) { ?>
                              <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                              <?php } else { ?>
                              <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                              <?php } ?>
                              <?php } ?>
                          </select>
                      </div>
                  </div>
              </div>
              <div class="tab-pane" id="tab-section-comment">
                  <div class="form-group">
                      <div class="col-sm-9">
                          <div class="alert alert-info"><i class="fa fa-exclamation-circle"></i> <?php echo $text_setting_comment; ?></div>
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-comment-section"><?php echo $entry_comment; ?></label>
                      <div class="col-sm-7">
                          <?php foreach ($languages as $language) { ?>
                          <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                              <input type="text" name="custom_email_templates[comment_section][text][<?php echo $language['language_id']; ?>]" value="<?php echo (is_array($comment_section['text'])) ? ((isset($comment_section['text'][$language['language_id']])) ? $comment_section['text'][$language['language_id']] : '') : $comment_section['text']; ?>" placeholder="<?php echo $entry_column_name; ?>" class="form-control" />
                          </div>
                          <?php } ?>
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label" for="input-comment-section-align"><?php echo $entry_text_align; ?></label>
                      <div class="col-sm-7">
                          <select name="custom_email_templates[comment_section][align]" id="input-comment-section-align" class="form-control">
                              <?php foreach ($aligns as $key => $align) { ?>
                              <?php if ($key == $comment_section['align']) { ?>
                              <option value="<?php echo $key; ?>" selected="selected"><?php echo $align; ?></option>
                              <?php } else { ?>
                              <option value="<?php echo $key; ?>"><?php echo $align; ?></option>
                              <?php } ?>
                              <?php } ?>
                          </select>
                      </div>
                  </div>
                  <div class="form-group">
                      <label class="col-sm-2 control-label s_help" for="input-comment-section-table"><?php echo $entry_table; ?><b><?php echo $help_table; ?></b></label>
                      <div class="col-sm-7">
                          <select name="custom_email_templates[comment_section][table]" id="input-comment-section-table" class="form-control">
                              <?php if (!isset($comment_section['table']) || $comment_section['table']) { ?>
                              <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                              <option value="0"><?php echo $text_no; ?></option>
                              <?php } else { ?>
                              <option value="1"><?php echo $text_yes; ?></option>
                              <option value="0" selected="selected"><?php echo $text_no; ?></option>
                              <?php } ?>
                          </select>
                      </div>
                  </div>
              </div>
              </div>
              </div>
              </div>
              </div>
              <div class="tab-pane active in" id="tab-template">
                  <div class="row">
                      <div class="col-sm-2">
                          <ul class="nav nav-pills nav-stacked tab-switcher" id="template">
                              <?php foreach ($templates as $key => $template) { ?>
                              <li><a href="#tab-section-<?php echo $key; ?>" data-toggle="tab"><?php echo $template['name']; ?></a></li>
                              <?php } ?>
                          </ul>
                      </div>
                      <div class="col-sm-10">
                          <div class="tab-content">
                              <?php foreach ($templates as $key => $template) { ?>
                              <div class="tab-pane" id="tab-section-<?php echo $key; ?>">
                                  <?php if ($key == 'mail') { ?>
                                  <div style="margin-bottom: 10px;">
                                      <a href="<?php echo $add; ?>" data-toggle="tooltip" title="<?php echo $button_add; ?>" class="btn btn-warning"><i class="fa fa-plus"></i> <?php echo $button_add; ?></a>
                                  </div>
                                  <?php } ?>

                                          <table class="table table-bordered">
                                              <thead>
                                              <tr>
                                                  <td class=""><?php echo $column_name; ?></td>
                                                  <td class=""><?php echo $column_code; ?></td>
                                                  <td class=""><?php echo $column_status; ?></td>
                                                  <td class=""></td>
                                              </tr>
                                              </thead>
                                              <tbody>
                                              <?php if ($template['templates']) { ?>
                                              <?php foreach ($template['templates'] as $value) { ?>
                                              <tr>
                                                  <td class=""><?php echo $value['name']; ?></td>
                                                  <td class=""><?php echo $value['code']; ?></td>
                                                  <td class=""><?php echo $value['status']; ?></td>
                                                  <td class=""><?php if ($value['template_id']) { ?>
                                                      <a id="button-template-preview" data-toggle="tooltip" data-template-id="<?php echo $value['template_id']; ?>" title="<?php echo $button_preview; ?>" class="btn btn-info"><i class="fa fa-eye"></i></a>
                                                      <?php } ?><a href="<?php echo $value['edit']; ?>" data-toggle="tooltip" title="<?php echo $button_edit; ?>" class="btn btn-primary"><i class="fa fa-pencil"></i></a></td>
                                              </tr>
                                              <?php } ?>
                                              <?php } else { ?>
                                              <tr>
                                                  <td class="text-center" colspan="4"><?php echo $text_no_results; ?></td>
                                              </tr>
                                              <?php } ?>
                                              </tbody>
                                          </table>

                              </div>
                              <?php } ?>
                          </div>
                      </div>
                  </div>
              </div>
              <div class="tab-pane" id="tab-history">
                  <div class="row" style="margin-bottom: 15px;">
                      <div class="col-sm-12">
                          <a id="button-history-remove" data-toggle="tooltip" title="<?php echo $button_remove_all; ?>" class="btn btn-danger pull-right"><?php echo $button_remove_all; ?></a>
                      </div>
                  </div>
                  <div class="well">
                      <div class="row">
                          <div class="col-sm-3">
                              <div class="form-group">
                                  <label class="control-label" for="input-subject"><?php echo $entry_subject; ?></label>
                                  <input type="text" name="filter_subject" value="" placeholder="<?php echo $entry_subject; ?>" id="input-subject" class="form-control" />
                              </div>
                          </div>
                          <div class="col-sm-3">
                              <div class="form-group">
                                  <label class="control-label" for="input-email"><?php echo $entry_email; ?></label>
                                  <input type="text" name="filter_email" value="" placeholder="<?php echo $entry_email; ?>" id="input-email" class="form-control" />
                              </div>
                          </div>
                          <div class="col-sm-2">
                              <div class="form-group">
                                  <label class="control-label" for="input-template-code"><?php echo $entry_template_code; ?></label>
                                  <select name="filter_template_code" id="input-template-code" class="form-control">
                                      <option value="*"></option>
                                      <?php foreach ($templates as $key => $template) { ?>
                                      <optgroup label="<?php echo $key; ?>">
                                          <?php foreach ($template['templates'] as $tpl) { ?>
                                          <option value="<?php echo $tpl['code']; ?>"><?php echo $tpl['code']; ?> - <?php echo $tpl['name']; ?></option>
                                          <?php } ?>
                                      </optgroup>
                                      <?php } ?>
                                  </select>
                              </div>
                          </div>
                          <div class="col-sm-2">
                              <div class="form-group">
                                  <label class="control-label" for="input-attachment"><?php echo $entry_attachment; ?></label>
                                  <select name="filter_attachment" id="input-attachment" class="form-control">
                                      <option value="*"></option>
                                      <option value="2"><?php echo $text_yes; ?></option>
                                      <option value="1"><?php echo $text_no; ?></option>
                                  </select>
                              </div>
                          </div>
                          <div class="col-sm-2">
                              <div class="form-group">
                                  <label class="control-label" for="input-date-added"><?php echo $entry_date_added; ?></label>
                                  <div class="input-group date">
                                      <input type="text" name="filter_date_added" value="" placeholder="<?php echo $entry_date_added; ?>" data-date-format="YYYY-MM-DD" id="input-date-added" class="form-control" />
                                    <span class="input-group-btn">
                                      <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                                    </span>
                                  </div>
                              </div>
                              <button type="button" id="button-filter" class="btn btn-primary pull-right"><i class="fa fa-search"></i> <?php echo $button_filter; ?></button>
                          </div>
                      </div>
                  </div>
                  <div id="history"></div>
              </div>
          </div>
      </form>

        <div class="modal fade" id="dialog-template-preview" tabindex="-1" role="dialog">
          <div class="modal-dialog" style="display: table;">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $text_template_preview; ?></h4>
              </div>
              <div class="modal-body"><iframe frameborder="0"></iframe></div>
            </div>
          </div>
        </div>
  </div>

</div>
</div>
<script type="text/javascript"><!--
function getFilter() {
	var url = '';

	var filter_subject = $('input[name=\'filter_subject\']').val();

	if (filter_subject) {
		url += '&filter_subject=' + encodeURIComponent(filter_subject);
	}

	var filter_template_code = $('select[name=\'filter_template_code\']').val();

	if (filter_template_code != '*') {
		url += '&filter_template_code=' + encodeURIComponent(filter_template_code);
	}

	var filter_email = $('input[name=\'filter_email\']').val();

	if (filter_email) {
		url += '&filter_email=' + encodeURIComponent(filter_email);
	}

	var filter_attachment = $('select[name=\'filter_attachment\']').val();

	if (filter_attachment != '*') {
		url += '&filter_attachment=' + encodeURIComponent(filter_attachment);
	}

	var filter_date_added = $('input[name=\'filter_date_added\']').val();
	
	if (filter_date_added) {
		url += '&filter_date_added=' + encodeURIComponent(filter_date_added);
	}

	return url;
}

$('#button-filter').on('click', function() {
	filter = getFilter();

	$('#history').load('index.php?route=module/custom_email_templates/history&token=<?php echo $token; ?>&store_id=<?php echo $filter_store_id; ?>&format=raw' + filter);
});

$('#input-invoice-template').bind('change', function() {
	$('#img-invoice-preview').attr('src', '<?php echo HTTP_CATALOG; ?>catalog/view/theme/default/template/custom_email_templates/invoice/' + $(this).val().replace('.tpl', '.jpg'));
});

$(document).delegate('#button-history-remove', 'click', function() {
	obj = $(this);

	if (typeof obj.data('history-id') !== 'undefined') {
		id = '&history_id=' + obj.data('history-id');
	} else {
		id = '';
	}

	$.ajax({
		url: 'index.php?route=module/custom_email_templates/remove&token=<?php echo $token; ?>&store_id=<?php echo $filter_store_id; ?>&format=raw' + id,
		type: 'post',
		dataType: 'json',
		beforeSend: function() {
			obj.html('<i class="fa fa-spinner fa-spin"></i>');
		},
		complete: function() {
			if (typeof obj.data('history-id') !== 'undefined') {
				obj.html('<i class="fa fa-trash-o"></i>');
			} else {
				obj.html('<?php echo $button_remove_all; ?>');
			}
		},
		success: function(json) {
			$('.alert').remove();

			if (json['error']) {
				$('#content > .container-fluid').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + '</div>');
			}

			if (json['success']) {
                $('#content > .container-fluid').prepend('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['success'] + '</div>');

				filter = getFilter();

				$('#history').load('index.php?route=module/custom_email_templates/history&token=<?php echo $token; ?>&store_id=<?php echo $filter_store_id; ?>&format=raw' + filter);
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});

$(document).delegate('#button-template-preview', 'click', function() {
	obj = $(this);

	if (typeof obj.data('history-id') !== 'undefined') {
		id = obj.data('history-id');
		type = 'history';
	} else if (typeof obj.data('template-id') !== 'undefined') {
		id = obj.data('template-id');
		type = 'template';
	} else {
		return false;
	}

	$('#dialog-template-preview').find('iframe').attr({
		'src': '<?php echo defined('HTTPS_SERVER') ? HTTPS_SERVER : HTTP_SERVER ; ?>index.php?route=module/custom_email_templates/preview&token=<?php echo $token; ?>&format=raw&type=' + type + '&id=' + id,
		'width': '<?php $iframe_width = (int)$this->registry->get('config')->get('custom_email_templates_layout_width'); echo (($iframe_width ? $iframe_width : 600) + 50); ?>',
		'height': '400'
	});

	$('#dialog-template-preview').modal('show');
});

$('#history').delegate('.pagination a, .links a', 'click', function(e) {
	e.preventDefault();

	$('#history').load(this.href);
});

$('#history').load('index.php?route=module/custom_email_templates/history&token=<?php echo $token; ?>&store_id=<?php echo $filter_store_id; ?>&format=raw');

$(document).delegate('#general-tabs a[href="#tab-history"]', 'click', function() {
	filter = getFilter();

	$('#history').load('index.php?route=module/custom_email_templates/history&token=<?php echo $token; ?>&store_id=<?php echo $filter_store_id; ?>&format=raw' + filter);
});

$('#template a:first').tab('show');
$('#setting a:first').tab('show');

<?php if ($filter_tab_show) { ?>
$('#general-tabs a[href="#tab-<?php echo $filter_tab_show; ?>"]').tab('show');
<?php } ?>

$('.date').datetimepicker({
	pickTime: false
});
//--></script>
<?php if (version_compare(VERSION, '2.0') < 0) { ?>
<script type="text/javascript"><!--
function image_upload(field, thumb) {
    $.startImageManager(field, thumb + ' img');
};
//--></script>
<?php } ?>
<?php echo $footer; ?>