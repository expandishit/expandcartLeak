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
            <h1>{{ lang('pd_category_and_cliparts') }}</h1>
            <div class="buttons"><a onclick="$('#form').submit();"
                                    class="button btn btn-primary">{{ lang('button_save') }}</a><a
                        href="<?php echo $cancel; ?>" class="button btn btn-primary">{{ lang('button_cancel') }}</a>
            </div>
        </div>
        <div class="content">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form" class="form-horizontal">
                <table class="form">
                  <tr>
                    <td><span class="required">*</span> {{ lang('pd_category_name') }}</td>
                    <td><input type="text" id="txtCategoryName" value="<?= $category_name; ?>" name="txtCategoryName" class="form-control" /></td>
                  </tr>
                  <tr>
                    <td>{{ lang('pd_category_image') }} :</td>
                    <td>
                        <?php if ($category_image != '') { ?>
                        <img data-placeholder="<?= $category_image; ?>" title="" alt="" src="<?= $category_image; ?>">
                        <?php } ?>
                        <input type="file" id="input-image0" name="uploadimage">
                    </td>
                  </tr>
                  <tr>
                    <td>{{ lang('status') }}</td>
                    <td><select id="optActive" name="optActive" class="form-control">
                        <?php if (isset($status) && $status == '0') { ?>
                        <option value="1">{{ lang('text_enabled') }}</option>
                        <option value="0" selected="selected">{{ lang('text_disabled') }}</option>
                        <?php } elseif (isset($status) && $status == '1') { ?>
                        <option value="1" selected="selected">{{ lang('text_enabled') }}</option>
                        <option value="0">{{ lang('text_disabled') }}</option>
                        <?php } else { ?>
                        <option value="1">{{ lang('text_enabled') }}</option>
                        <option value="0">{{ lang('text_disabled') }}</option>
                        <?php } ?>
                      </select>
                    </td>
                  </tr>
                </table>
            </form>
        </div>
    </div>
</div>
<?php echo $footer; ?>
