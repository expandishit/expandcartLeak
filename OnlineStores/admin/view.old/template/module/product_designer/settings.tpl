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
            <h1>{{ lang('pd_heading_title') }}</h1>
            <div class="buttons">

                <a onclick="$('#form').submit();" class="button btn btn-primary">{{ lang('button_save') }}</a>
                <a href="<?= $settingsLink; ?>" class="button btn btn-primary">{{ lang('pd_category_and_cliparts') }}</a>
                <a href="<?php echo $cancel; ?>" class="button btn btn-primary">{{ lang('button_cancel') }}</a>
            </div>
        </div>
        <div class="content">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form"
                  class="form-horizontal">
                <table class="form">
                <tr>
                    <td>{{ lang('entry_status') }} :</td>
                    <td>
                        <select class="form-control" name='tshirt_module[pd_status]'>
                            <option value='1' <?= ($modules['pd_status'] == '1' ? 'selected' : ''); ?>>{{ lang('text_enabled') }}</option>
                            <option value='2' <?= ($modules['pd_status'] != '1' ? 'selected' : ''); ?>>{{ lang('text_disabled') }}</option>
                        </select>
                    </td>
                </tr>
                </table>
            </form>
        </div>
    </div>
</div>
<script>
function showpreview(obj){
    if(obj.value != '--select--') {
        var imgname = '<?= $themesDir; ?>' + obj.value + '/background.png';
        console.log(imgname);
        jQuery("#prevImg").attr("src",imgname);
    }
    else{
        jQuery("#prevImg").attr("src","<?= $http_catalog . 'image/no_image.jpg'; ?>");
    }
}
</script>
<?php echo $footer; ?>
