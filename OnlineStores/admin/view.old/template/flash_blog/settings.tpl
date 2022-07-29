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
            <h1>{{ lang('flash_blog_heading_title') }}</h1>
            <div class="buttons">
                <a onclick="$('#form').submit();"
                   class="button btn btn-primary tab_settings">
                    <span>{{ lang('button_save') }}</span>
                </a>
                <a href="<?= $links['cancel']; ?>"
                   class="button btn btn-primary">
                    <span>{{ lang('button_back') }}</span>
                </a>
            </div>
        </div>
        <div class="content">
            <form action="<?= $links['submit']; ?>" method="post" id="form">
                <div class="tab-content" id="tab-general">
                    <table class="table table-striped table-bordered">
                        <tr>
                            <td>{{ lang('entry_status') }} :</td>
                            <td>
                                <select class="form-control" name='flash_blog[status]'>
                                    <option value='1'
                                        <?= ($settings['status'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_enabled') }}</option>
                                    <option value='0'
                                        <?= ($settings['status'] != '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_disabled') }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ lang('show_comments') }}</td>
                            <td>
                                <select class="form-control" name='flash_blog[show_comments]'>
                                    <option value='0'
                                    <?= ($settings['show_comments'] == '0' ? 'selected' : ''); ?>
                                    >{{ lang('text_disabled') }}</option>
                                    <option value='1'
                                    <?= ($settings['show_comments'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('for_both') }}</option>
                                    <option value='2'
                                    <?= ($settings['show_comments'] == '2' ? 'selected' : ''); ?>
                                    >{{ lang('for_users') }}</option>
                                    <option value='3'
                                    <?= ($settings['show_comments'] == '3' ? 'selected' : ''); ?>
                                    >{{ lang('for_guests') }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ lang('enable_commenting') }}</td>
                            <td>
                                <select class="form-control" name='flash_blog[enable_commenting]'>
                                    <option value='0'
                                    <?= ($settings['enable_commenting'] == '0' ? 'selected' : ''); ?>
                                    >{{ lang('text_disabled') }}</option>
                                    <option value='1'
                                    <?= ($settings['enable_commenting'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('for_both') }}</option>
                                    <option value='2'
                                    <?= ($settings['enable_commenting'] == '2' ? 'selected' : ''); ?>
                                    >{{ lang('for_users') }}</option>
                                    <option value='3'
                                    <?= ($settings['enable_commenting'] == '3' ? 'selected' : ''); ?>
                                    >{{ lang('for_guests') }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ lang('comments_auto_approval') }}
                                <span class="help">{{ lang('comments_auto_approval_note') }}</span>
                            </td>
                            <td>
                                <select class="form-control" name='flash_blog[auto_approval]'>
                                    <option value='0'
                                    <?= ($settings['auto_approval'] == '0' ? 'selected' : ''); ?>
                                    >{{ lang('text_disabled') }}</option>
                                    <option value='1'
                                    <?= ($settings['auto_approval'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('for_both') }}</option>
                                    <option value='2'
                                    <?= ($settings['auto_approval'] == '2' ? 'selected' : ''); ?>
                                    >{{ lang('for_users') }}</option>
                                    <option value='3'
                                    <?= ($settings['auto_approval'] == '3' ? 'selected' : ''); ?>
                                    >{{ lang('for_guests') }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ lang('maximum_index_blogs') }}
                                <span class="help">{{ lang('maximum_index_blogs_note') }}</span>
                            </td>
                            <td>
                                <input class="form-control" name='flash_blog[maximum_index_blogs]'
                                       value="<?= $settings['maximum_index_blogs']; ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ lang('maximum_index_categories') }}
                                <span class="help">{{ lang('maximum_index_categories_note') }}</span>
                            </td>
                            <td>
                                <input class="form-control" name='flash_blog[maximum_index_categories]'
                                       value="<?= $settings['maximum_index_categories']; ?>" />
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $('#tabs a').tabs();
</script>
<?php echo $footer; ?>
