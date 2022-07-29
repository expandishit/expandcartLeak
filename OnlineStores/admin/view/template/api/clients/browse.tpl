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
            <h1>{{ lang('api_clients_heading_title') }}</h1>
            <div class="buttons">
                <a href="<?= $links['newClient']; ?>"
                   class="button btn btn-primary">
                    <span>{{ lang('button_insert') }}</span>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="content col-md-12">
                <table class="table table-striped table-bordered domains">
                    <thead>
                    <tr>
                        <td>#</td>
                        <td>{{ lang('client_id') }}</td>
                        <td>{{ lang('created_at') }}</td>
                        <td>{{ lang('client_status') }}</td>
                        <td>{{ lang('text_options') }}</td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($clients) { ?>
                    <?php foreach ($clients as $client) { ?>
                    <tr>
                        <td><?= $client['id']; ?></td>
                        <td><?= $client['client_id']; ?></td>
                        <td><?= $client['created_at']; ?></td>
                        <td>
                            <?php if ($client['client_status'] == 1) { ?>
                            {{ lang('text_enabled') }}
                            <?php } else { ?>
                            {{ lang('text_disabled') }}
                            <?php } ?>
                        </td>
                        <td>
                            <a href="<?= $links['editClient'] . '&client_id=' . $client['id']; ?>"
                               class="button btn btn-primary">
                                <span>{{ lang('button_edit') }}</span>
                            </a>
                            <?php if ($client['client_status'] == 0) { ?>
                            <a href="<?= $links['activateClient'] . '&client_id=' . $client['id']; ?>"
                               class="button btn btn-primary">
                                <span>{{ lang('button_activate') }}</span>
                            </a>
                            <?php } else { ?>
                            <a href="<?= $links['deactivateClient'] . '&client_id=' . $client['id']; ?>"
                               class="button btn btn-danger">
                                <span>{{ lang('button_deactivate') }}</span>
                            </a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php } else { ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">{{ lang('text_no_results') }}</td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>
