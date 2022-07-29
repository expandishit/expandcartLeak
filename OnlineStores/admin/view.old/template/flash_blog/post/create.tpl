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
            <h1>{{ lang('heading_create_new_post') }}</h1>
            <div class="buttons">
                <a onclick="$('#newPostForm').submit();" class="button btn btn-primary">
                    {{ lang('button_save') }}
                </a>
                <a href="<?= $links['parentCategory']; ?>" class="button btn btn-primary">
                    <span>{{ lang('button_cancel') }}</span>
                </a>
            </div>
        </div>
        <form id="newPostForm" method="post" action="<?= $links['insertPost']; ?>">
            <div>
                <table class="form table">
                    <tr>
                        <td>{{ lang('post_sort') }}</td>
                        <td><input type="text" name="post[sort_order]" value="<?= $post['sort_order']; ?>" /></td>
                    </tr>

                    <tr>
                        <td>{{ lang('post_image') }}</td>
                        <td>
                            <div class="image">
                                <img src="<?php echo $post_thump; ?>" alt="" id="post_thump" /><br />
                                <input type="hidden" name="post[post_image]"
                                       value="<?php echo $post['post_image']; ?>" id="post_image" />
                                <a onclick="image_upload('post_image', 'post_thump');">
                                    {{ lang('text_browse') }}
                                </a>&nbsp;&nbsp;|&nbsp;&nbsp;
                                <a onclick="$('#post_thump').attr('src', '<?php echo $no_image; ?>');
                                $('#post_image').attr('value', '');">
                                    {{ lang('text_clear') }}
                                </a>
                            </div>
                        </td>
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
                        <td>{{ lang('stop_publishing') }} :</td>
                        <td>
                            <input type="checkbox" value="0"
                                   name="post_description[<?= $language['language_id']; ?>][post_status]"
                            <?= ($description[$language['language_id']]['post_status'] == 0 ? 'checked' : '') ?> />
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('post_name') }} :</td>
                        <td>
                            <input type="text" name="post_description[<?= $language['language_id']; ?>][name]"
                                   value="<?= $description[$language['language_id']]['name']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('post_description') }}</td>
                        <td>
                            <textarea name="post_description[<?= $language['language_id']; ?>][description]"
                            ><?= $description[$language['language_id']]['description']; ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('post_content') }}</td>
                        <td>
                            <textarea name="post_description[<?= $language['language_id']; ?>][content]"
                                      id="post_content_<?php echo $language['language_id']; ?>">
                                <?= $description[$language['language_id']]['content']; ?>
                            </textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('post_meta_description') }}</td>
                        <td>
                            <input type="text"
                                   name="post_description[<?= $language['language_id']; ?>][meta_description]"
                                   value="<?= $description[$language['language_id']]['meta_description']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('post_meta_keywords') }}</td>
                        <td>
                            <input type="text"
                                   name="post_description[<?= $language['language_id']; ?>][meta_keywords]"
                                   value="<?= $description[$language['language_id']]['meta_keywords']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ lang('post_tags') }}
                            <span class="help">{{ lang('post_tags_note') }}</span>
                        </td>
                        <td>
                            <input type="text"
                                   name="post_description[<?= $language['language_id']; ?>][tags]"
                                   value="<?= $description[$language['language_id']]['tags']; ?>" />
                        </td>
                    </tr>
                </table>
            </div>
            <?php } ?>
            <input type="hidden" name="actionType" value="create" />
            <input type="hidden" name="post[parent_id]" value="<?= $categoryId; ?>" />
        </form>
    </div>
</div>
<script>

    function image_upload(field, thumb) {
        $.startImageManager(field, thumb);
    };

    <?php foreach ($languages as $language) { ?>
        CKEDITOR.replace('post_content_<?php echo $language['language_id']; ?>', {
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