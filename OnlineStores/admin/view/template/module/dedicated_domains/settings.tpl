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
            <h1>{{ lang('dedicated_domains_heading_title') }}</h1>
            <div class="buttons">
                <a href="<?= $links['domains']; ?>"
                   class="button btn btn-primary">
                    <span>{{ lang('text_domains') }}</span>
                </a>
                <a onclick="$('#form').submit();"
                   class="button btn btn-primary tab_settings">
                    <span>{{ lang('button_save') }}</span>
                </a>
                <a href="<?= $links['cancel']; ?>"
                   class="button btn btn-primary">
                    <span>{{ lang('button_cancel') }}</span>
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
                                <select class="form-control" name='dedicated_domains[status]'>
                                    <option value='1'
                                    <?= ($settings['status'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_enabled') }}</option>
                                    <option value='2'
                                    <?= ($settings['status'] != '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_disabled') }}</option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td>{{ lang('enable_change_currency') }} :</td>
                            <td>
                                <select class="form-control" name='dedicated_domains[changeCurrency]'>
                                    <option value='1'
                                    <?= ($settings['changeCurrency'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_yes') }}</option>
                                    <option value='0'
                                    <?= ($settings['changeCurrency'] != '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_no') }}</option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td>{{ lang('force_redirect') }} :</td>
                            <td>
                                <select class="form-control" name='dedicated_domains[force_redirect]'>
                                    <option value='1'
                                    <?= ($settings['force_redirect'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_enabled') }}</option>
                                    <option value='2'
                                    <?= ($settings['force_redirect'] != '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_disabled') }}</option>
                                </select>
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
