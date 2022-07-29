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
            <h1>{{ lang('heading_show_post') }}</h1>
            <div class="buttons">
                <a href="<?= $links['editPost']; ?>" class="button btn btn-primary">
                    {{ lang('button_edit') }}
                </a>
                <a href="<?= $links['parentCategory']; ?>" class="button btn btn-primary">
                    <span>{{ lang('button_cancel') }}</span>
                </a>
            </div>
        </div>

        <div class="content">
            <div>
                <table class="form table">
                    <tr>
                        <td>{{ lang('post_sort') }}</td>
                        <td><?= $post['sort_order']; ?></td>
                    </tr>
                </table>
            </div>

            <div id="languages" class="htabs">
                <?php foreach ($languages as $language) { ?>
                <a href="#language<?php echo $language['language_id']; ?>">
                    <img src="view/image/flags/<?php echo $language['image']; ?>"
                         title="<?php echo $language['name']; ?>"/>
                    <?php echo $language['name']; ?>
                </a>
                <?php } ?>
            </div>
            <?php foreach ($languages as $language) { ?>
            <div id="language<?php echo $language['language_id']; ?>">
                <table class="form table">
                    <tr>
                        <td>{{ lang('post_status') }} :</td>
                        <td>{{ lang('post_name') }} :</td>
                    </tr>
                    <tr>
                        <td>
                            <?php if ($description[$language['language_id']]['post_status'] == 1) { ?>
                            {{ lang('text_enabled') }}
                            <?php } else { ?>
                            {{ lang('text_disabled') }}
                            <?php } ?>
                        </td>
                        <td><?= $description[$language['language_id']]['name']; ?></td>
                    </tr>
                </table>
            </div>
            <?php } ?>

        </div>

        <div class="content">
            <h1>{{ lang('post_comments') }}</h1>
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>{{ lang('comment_email') }}</th>
                    <th>{{ lang('comment_date') }}</th>
                    <th>{{ lang('text_options') }}</th>
                </tr>
                </thead>
                <?php if ($comments) { ?>
                <?php foreach ($comments as $comment) { ?>
                <tbody>
                <tr>
                    <td><?= $comment['email']; ?></td>
                    <td><?= $comment['created_at']; ?></td>
                    <td>
                        <a class="showComment btn btn-primary">
                            {{ lang('text_view') }}
                        </a>
                        <?php if ($comment['comment_status'] == 1) { ?>
                        <a href="<?= $links['deactivateComment'] . '&comment_id=' . $comment['comment_id']; ?>"
                           class="btn btn-danger">
                            {{ lang('text_deactivate') }}
                        </a>
                        <?php } else { ?>
                        <a href="<?= $links['activateComment'] . '&comment_id=' . $comment['comment_id']; ?>"
                           class="btn btn-primary">
                            {{ lang('text_activate') }}
                        </a>
                        <?php } ?>
                        <a href="<?= $links['deleteComment'] . '&comment_id=' . $comment['comment_id']; ?>"
                           class="btn btn-danger" onclick="return confirm('{{ lang('delete_alert') }}')">
                            {{ lang('button_delete') }}
                        </a>
                    </td>
                </tr>
                <tr class="hide commentDetails">
                    <td colspan="5" class="commenter">
                        <table class="table table-striped table-bordered">
                            <tr>
                                <td>{{ lang('commenter_name') }}</td>
                                <td><?= $comment['name']; ?></td>
                            </tr>
                            <tr>
                                <td>{{ lang('commenter_id') }}</td>
                                <td>
                                    <?php if ($comment['customer_id'] != 0) { ?>
                                    <a href="<?= $links['customerLink'] . '&customer_id=' . $comment['customer_id']; ?>">
                                        #<?= $comment['customer_id']; ?>
                                    </a>
                                    <?php } else { ?>
                                    {{ lang('text_unregistered') }}
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ lang('comment_status') }}</td>
                                <td>
                                    <?php if ($comment['comment_status'] == 1) { ?>
                                    {{ lang('text_enabled') }}
                                    <?php } else { ?>
                                    {{ lang('text_disabled') }}
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ lang('comment_contents') }}</td>
                                <td><?= $comment['comment']; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                </tbody>
                <?php } ?>
                <?php } else { ?>
                <tr>
                    <td style="text-align: center;" colspan="4">{{ lang('text_no_results') }}</td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>
<script>
    $('#languages a').tabs();

    $('.showComment').click(function(event) {
        var $this = $(this);
        var $parent = $this.parent().parent().parent();

        $('.commentDetails', $parent).toggleClass('hide');

    });
</script>
<?php echo $footer; ?>