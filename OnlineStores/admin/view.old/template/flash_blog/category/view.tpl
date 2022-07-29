<?php echo $header; ?>

<div id=content">
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
            <h1>{{ lang('heading_view_category') }}</h1>
            <div class="buttons">
                <a href="<?= $links['editCategory']; ?>"
                   class="button btn btn-primary">
                    <span>{{ lang('button_edit') }}</span>
                </a>
                <a href="<?= $links['categories']; ?>"
                   class="button btn btn-primary">
                    <span>{{ lang('button_back') }}</span>
                </a>
            </div>
        </div>
        <div class="content col-md-12">
            <h1>{{ lang('heading_view_sub_categories') }}</h1>
            <div class="buttons">
                <a href="<?= $links['newCategory']; ?>"
                   class="button btn btn-primary tab_settings">
                    <span>{{ lang('button_insert') }}</span>
                </a>
            </div>
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ lang('category_title') }}</th>
                    <th>{{ lang('text_options') }}</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($subCategories) { ?>
                <?php foreach ($subCategories as $category) { ?>
                <tr>
                    <td><?= $category['category_id']; ?></td>
                    <td><?= $category['name']; ?></td>
                    <td>
                        <a href="<?= $links['viewCategory'] . '&category_id=' . $category['category_id']; ?>"
                           class="btn btn-primary">
                            {{ lang('text_view') }}
                        </a>

                        <a href="<?= $links['editSubCategory'] . '&category_id=' . $category['category_id']; ?>"
                           class="btn btn-primary">
                            {{ lang('text_edit') }}
                        </a>

                        <a href="<?= $links['deleteCategory'] . '&category_id=' . $category['category_id']; ?>"
                           onclick='return confirm("{{ lang('delete_alert') }}");' class="btn btn-primary">
                            {{ lang('button_delete') }}
                        </a>
                    </td>
                </tr>
                <?php } ?>
                <?php } else { ?>
                <tr>
                    <td style="text-align: center;" colspan="4">{{ lang('text_no_results') }}</td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="content col-md-12">
            <h1>{{ lang('heading_view_posts') }}</h1>
            <div class="buttons">
                <a href="<?= $links['newPost']; ?>"
                   class="button btn btn-primary tab_settings">
                    <span>{{ lang('button_insert') }}</span>
                </a>
            </div>
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ lang('post_title') }}</th>
                    <th>{{ lang('post_status') }}</th>
                    <th>{{ lang('text_options') }}</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($categoryPosts) { ?>
                <?php foreach ($categoryPosts as $post) { ?>
                <tr>
                    <td><?= $post['post_id']; ?></td>
                    <td><?= $post['name']; ?></td>
                    <td>
                        <?php if ($post['post_status'] == 1) { ?>
                        {{ lang('text_enabled') }}
                        <?php } else { ?>
                        {{ lang('text_disabled') }}
                        <?php } ?>
                    </td>
                    <td>
                        <a href="<?= $links['viewPost'] . '&post_id=' . $post['post_id']; ?>"
                           class="btn btn-primary">
                            {{ lang('text_view') }}
                        </a>

                        <a href="<?= $links['editPost'] . '&post_id=' . $post['post_id']; ?>"
                           class="btn btn-primary">
                            {{ lang('text_edit') }}
                        </a>

                        <a href="<?= $links['deletePost'] . '&post_id=' . $post['post_id']; ?>"
                           onclick='return confirm("{{ lang('delete_alert') }}");' class="btn btn-primary">
                            {{ lang('button_delete') }}
                        </a>
                    </td>
                </tr>
                <?php } ?>
                <?php } else { ?>
                <tr>
                    <td style="text-align: center;" colspan="4">{{ lang('text_no_results') }}</td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php echo $footer; ?>
