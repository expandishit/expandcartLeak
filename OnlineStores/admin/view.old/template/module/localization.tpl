<?php include_ckeditor($header, $footer); ?>
<?php echo $header; ?>
<div id="content">
    <ol class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb): ?>
            <?php if ($breadcrumb === end($breadcrumbs)): ?>
                <li class="active">
            <?php else: ?>
                <li>
            <?php endif; ?>
            
            <a href="<?= $breadcrumb['href']; ?>">
            <?php if ($breadcrumb === reset($breadcrumbs)): ?>
                <?= $breadcrumb['text']; ?>
            <?php else: ?>
                <span><?= $breadcrumb['text']; ?></span>
            <?php endif; ?>
            </a>
            </li>
        <?php endforeach; ?>
    </ol>
    <?php if ($error_warning): ?>
    <script>
        var notificationString = '<?= $error_warning; ?>';
        var notificationType = 'warning';
    </script>
    <?php endif; ?>

    <?php if ($text_success): ?>
        <script>
            var notificationString = '<?= $text_success; ?>';
            var notificationType = 'success';
        </script>
    <?php endif; ?>

    <div class="box">
        <div class="heading">
            <h1><?= $heading_title; ?></h1>
            <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><?= $button_save; ?></a><a href="<?= $cancel; ?>" class="button btn btn-primary"><?= $button_cancel; ?></a></div>
        </div>
        <div class="content">
            <div class="htabs" id="tabs_htabs">
                <?php foreach ($langs as $lang): ?>
                    <a href="#tab_localiz_<?= $lang['code'] ?>"><img src="view/image/flags/<?= $lang['image'] ?>" alt=""> <?= $lang['name']; ?></a>
                <?php endforeach ?>
            </div>

        <!-- button_cart -->
        <!-- button_req_quote_ar -->

            <form action="<?= $action; ?>" method="post" enctype="multipart/form-data" id="form">
                <br>
                <?php foreach ($langs as $lang): ?>
                    <div id="tab_localiz_<?= $lang['code']; ?>" class="htab-content">
                        <div class="tab-body table-form">
                            <?php foreach ($lang['fields'] as $field): ?>
                                <div class="row">

                                    <div class="col-md-3 th">
                                        <div class="wrap-5"><?= $field['text']; ?></div>
                                    </div>

                                    <div class="col-md-9">
                                        <div class="wrap-5">
                                            <div class="input-group">
                                                <?php if ( isset($field['html']) ): ?>
                                                    <?= $field['html']; ?>
                                                <?php else: ?>
                                                    <input type="text" name="<?= $field['name']; ?>" class="form-control" value="<?= $field['value']; ?>" placeholder="<?= $field['text'] . ' (' . $lang['name'] . ')' ?>">
                                                <?php endif; ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </form>
        </div>
    </div>
</div>

<script>
    $('#tabs_htabs a').tabs();
</script>

<script>
    <?php foreach ($langs as $lang): ?>
        CKEDITOR.replace('CKEDITOR_<?= $lang['code']; ?>', {
        filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
        filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
        filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
        filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
        filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
        filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
    });
    <?php endforeach ?>
</script>

<?= $footer; ?>