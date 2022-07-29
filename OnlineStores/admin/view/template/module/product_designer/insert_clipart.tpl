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
            <h1>{{ lang('pd_category_and_cliparts') }}</h1>
            <div class="buttons"><a onclick="$('#form').submit();"
                                    class="button btn btn-primary">{{ lang('button_save') }}</a><a
                        href="<?php echo $cancel; ?>" class="button btn btn-primary">{{ lang('button_cancel') }}</a>
            </div>
        </div>
        <div class="content">
            <form action="<?= $action_image; ?>" method="post" enctype="multipart/form-data" id="form">
              <table class="form">
                <tr>
                  <td>{{ lang('pd_upload_image') }} :</td>
                  <td><input class="form-control" type="file" name="uploadimage" id="uploadimage" />
                  </td>
                </tr>
              </table>
            </form>
        </div>
    </div>
</div>
<?php echo $footer; ?>
