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
            <h1>{{ lang('ms_config_subscriptions_plans') }}</h1>
            <div class="buttons">
                <a href="<?= $createPlan; ?>" id="new_plane" class="button btn btn-primary">
                    <span>{{ lang('button_insert') }}</span>
                </a>
            </div>
        </div>
        <table class="plansList table table-hover dataTable" style="text-align: center" id="plans_list">
            <thead>
            <tr>
                <td>{{ lang('ms_plan_title') }}</td>
                <td>{{ lang('ms_seller_groups_column_action') }}</td>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($plans['data'] as $plan) { ?>
            <tr>
                <td><?= $plan['title'] ?></td>
                <td>
                    <div class="actions">
                        <a href="<?= $editLink; ?>&plan_id=<?= $plan['plan_id']; ?>">{{ lang('text_edit') }}</a>
                        -
                        <a href="<?= $deleteLink; ?>&plan_id=<?= $plan['plan_id']; ?>">{{ lang('button_delete') }}</a>
                    </div>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php echo $footer; ?>