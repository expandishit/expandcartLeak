<?php include_ckeditor($header, $footer); ?>
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
            <h1>{{ lang('heading_edit_category') }}</h1>
            <div class="buttons">
                <a onclick="$('#newPlanForm').submit();" class="button btn btn-primary">
                    {{ lang('button_save') }}
                </a>
                <a href="<?= $links['categories']; ?>" class="button btn btn-primary">
                    <span>{{ lang('button_cancel') }}</span>
                </a>
            </div>
        </div>
        <form id="newPlanForm" method="post" action="<?= $links['submitCategory']; ?>">
            <div>
                <table class="form table">
                    <tr>
                        <td>{{ lang('category_status') }} :</td>
                        <td>
                            <select class="form-control" name='category[category_status]'>
                                <option value='1'
                                <?= $category['category_status'] == 1 ? 'selected' : ''; ?>
                                >{{ lang('text_enabled') }}</option>
                                <option value='0'
                                <?= $category['category_status'] == 0 ? 'selected' : ''; ?>
                                >{{ lang('text_disabled') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('category_image') }}</td>
                        <td>
                            <div class="image">
                                <img src="<?php echo $category_thump; ?>" alt="" id="category_thump" /><br />
                                <input type="hidden" name="category[category_image]"
                                       value="<?php echo $category['category_image']; ?>" id="category_image" />
                                <a onclick="image_upload('category_image', 'category_thump');">
                                    {{ lang('text_browse') }}
                                </a>&nbsp;&nbsp;|&nbsp;&nbsp;
                                <a onclick="$('#category_thump').attr('src', '<?php echo $no_image; ?>');
                                $('#category_image').attr('value', '');">
                                    {{ lang('text_clear') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('category_sort') }}</td>
                        <td><input type="text" name="category[sort_order]"
                                   value="<?= $category['sort_order']; ?>" /></td>
                    </tr>
                </table>
            </div>

            <div id="languages" class="htabs">
                <?php foreach ($languages as $language) { ?>
                <a href="#language<?php echo $language['language_id']; ?>">
                    <img src="view/image/flags/<?php echo $language['image']; ?>"
                         title="<?php echo $language['name']; ?>" />
                    <?php echo $language['name']; ?>
                </a>
                <?php } ?>
            </div>
            <?php foreach ($languages as $language) { ?>
            <div id="language<?php echo $language['language_id']; ?>">
                <table class="form table">
                    <tr>
                        <td>{{ lang('blog_category_name') }} :</td>
                        <td>
                            <input type="text" name="category_description[<?= $language['language_id']; ?>][name]"
                                   value="<?= $description[$language['language_id']]['name']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('blog_category_description') }}</td>
                        <td>
                            <textarea name="category_description[<?= $language['language_id']; ?>][description]"
                                      id="category_description_<?php echo $language['language_id']; ?>">
                                <?= $description[$language['language_id']]['description']; ?>
                            </textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('blog_category_meta_description') }}</td>
                        <td>
                            <input type="text"
                                   name="category_description[<?= $language['language_id']; ?>][meta_description]"
                                   value="<?= $description[$language['language_id']]['meta_description']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('blog_category_meta_keywords') }}</td>
                        <td>
                            <input type="text"
                                   name="category_description[<?= $language['language_id']; ?>][meta_keywords]"
                                   value="<?= $description[$language['language_id']]['meta_keywords']; ?>" />
                        </td>
                    </tr>
                </table>
            </div>
            <?php } ?>
            <input type="hidden" name="actionType" value="update" />
            <input type="hidden" name="categoryId" value="<?= $categoryId; ?>" />
        </form>
    </div>
</div>
<script>

    function image_upload(field, thumb) {
        $.startImageManager(field, thumb);
    };

    <?php foreach ($languages as $language) { ?>
        CKEDITOR.replace('category_description_<?php echo $language['language_id']; ?>', {
            filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
        });
    <?php } ?>
    $('#languages a').tabs();
</script>
<?php echo $footer; ?>