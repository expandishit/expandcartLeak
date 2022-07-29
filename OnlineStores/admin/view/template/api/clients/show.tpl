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
            <h1>{{ lang('api_clients_show_client') }}</h1>
            <div class="buttons">
                <a href="<?= $links['browseClients']; ?>"
                   class="button btn btn-primary">
                    <span>{{ lang('button_back') }}</span>
                </a>
            </div>
        </div>
        <div class="content">
            <div class="tab-content">
                <form action="<?= $links['submit']; ?>" method="post" id="form">
                    <table class="table table-striped table-bordered">
                        <tr>
                            <td>{{ lang('entry_status') }} :</td>
                            <td>
                                <?php if ($client['client_status'] == 1) { ?>
                                {{ lang('text_enabled') }}
                                <?php } else { ?>
                                {{ lang('text_disabled') }}
                                <?php } ?>
                            </td>
                        </tr>

                        <tr style="direction: ltr;">
                            <td colspan="2">{{ lang('api_client_id') }} :</td>
                        </tr>
                        <tr style="direction: ltr;">
                            <td colspan="2" class="success"><?= $client['client_id']; ?></td>
                        </tr>

                        <tr style="direction: ltr;">
                            <td colspan="2">{{ lang('api_secret_key') }} :</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="success" style="direction: ltr;">
                                <?= $client['client_secret']; ?>
                            </td>
                        </tr>

                    </table>
                </form>
            </div>
        </div>
    </div>
</div>
<script>

</script>
<?php echo $footer; ?>
