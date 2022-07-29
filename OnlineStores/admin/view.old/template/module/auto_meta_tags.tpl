<?php echo $header; ?>
<div id="content">
    <ol class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb): ?>
            <?php if ($breadcrumb === end($breadcrumbs)): ?>
                <li class="active">
            <?php else: ?>
                <li>
            <?php endif; ?>

                <a href="<?php echo $breadcrumb['href']; ?>">
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

    <?php if ($success_message): ?>
        <script>
            var notificationString = '<?= $success_message; ?>';
            var notificationType = 'success';
        </script>
    <?php endif; ?>

    <div class="box">
        <div class="heading">
            <h1><?= $heading_title; ?></h1>
            <div class="buttons">
                <a onclick="$('#form').submit();" class="button btn btn-primary"><?= $button_save; ?></a>
                <a href="<?= $cancel; ?>" class="button btn btn-danger"><?= $button_cancel; ?></a>
            </div>
        </div>

    <div class="content">
        <form action="<?= $action ?>" method="post" enctype="multipart/form-data" id="form">

            <div id="setting_general">
                <div class="tab-body table-form">

                    <!-- Module Status -->
                    <div class="row">
                        <div class="col-md-3 th">
                            <div class="wrap-5"><?= $text_module_status; ?></div>
                            <span class="help"><?= $text_module_status_descr; ?></span>
                        </div>

                        <div class="col-md-9">
                            <div class="wrap-5">
                                <select name="module_status" id="module_status">
                                    <option value="1" <?php if($module_enabled){echo 'selected="selected"';}?>><?= $text_module_enabled ?></option>
                                    <option value="0" <?php if(!$module_enabled) {echo 'selected="selected"';}?>><?= $text_module_disabled ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- End of Module Status -->
                    <hr>
                    <!-- Words number for meta description -->
                    <div class="row">
                        <div class="col-md-3 th">
                            <div class="wrap-5"><?= $text_meta_description_words_number ?></div>
                            <span class="help"><?= $text_meta_description_words_number_descr ?></span>
                        </div>

                        <div class="col-md-9">
                            <div class="wrap-5">
                                <input type="number" pattern="[0-9]" name="description_words_number" id="description_words_number" placeholder="Number" value="<?= $description_words_number? $description_words_number: 0; ?>" min="0" max="99">
                            </div>
                        </div>
                    </div>
                    <!-- End of Words number for meta description -->
                </div>
            </div>
        </form>
    </div>
</div>

<?php echo $footer; ?>