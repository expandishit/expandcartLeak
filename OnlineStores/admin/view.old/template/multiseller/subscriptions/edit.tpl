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
            <h1>{{ lang('ms_config_subscriptions_new_plan') }}</h1>
            <div class="buttons">
                <a onclick="$('#newPlanForm').submit();" class="button btn btn-primary">
                    {{ lang('button_save') }}
                </a>
                <a onclick="history.back()" class="button btn btn-primary">
                    <span>{{ lang('button_cancel') }}</span>
                </a>
            </div>
        </div>
        <form id="newPlanForm" method="post" action="<?= $submitForm; ?>">
            <div>
                <table class="form table">
                    <tr>
                        <td>{{ lang('entry_status') }} :</td>
                        <td>
                            <select class="form-control" name='ms_plan[plans][status]'>
                                <option <?= $plan['plan_status'] == 1 ? 'selected' : ''; ?>
                                        value='1'>{{ lang('text_enabled') }}</option>
                                <option <?= $plan['plan_status'] != 1 ? 'selected' : ''; ?>
                                        value='2'>{{ lang('text_disabled') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('ms_plan_price') }}</td>
                        <td><input type="text" name="ms_plan[plans][price]" value="<?= $plan['price'] ?>" /></td>
                    </tr>
                    <tr>
                        <td>{{ lang('ms_conf_subscriptions_payment_format') }}</td>
                        <td>
                            <input type="text" name="ms_plan[plans][period]" size="4"
                                   value="<?= $plan['period']; ?>" />
                            <select name="ms_plan[plans][format]">
                                <option value="1" <?= ($plan['format'] == 1) ? "selected" : '' ?> >{{ lang('day') }}</option>
                                <option value="2" <?= ($plan['format'] == 2) ? "selected" : '' ?> >{{ lang('month') }}</option>
                                <option value="3" <?= ($plan['format'] == 3) ? "selected" : '' ?> >{{ lang('year') }}</option>
                            </select>
                            <!--<select name="msconf_subscriptions_payment_format">
									<option value="1" <?= ($msconf_subscriptions_payment_format == 1) ? "selected" : '' ?> >{{ lang('ms_conf_yearly_payment_format') }}</option>
									<option value="2" <?= ($msconf_subscriptions_payment_format == 2) ? "selected" : '' ?> >{{ lang('ms_conf_monthly_payment_format') }}</option>
								</select>-->
                        </td>
                    </tr>
                </table>
            </div>

            <div id="languages" class="htabs">
                <?php foreach ($languages as $language) { ?>
                <a href="#language<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
                <?php } ?>
            </div>
            <?php foreach ($plan['details'] as $details) { ?>
            <div id="language<?php echo $details['language_id']; ?>">
                <table class="form table">
                    <tr>
                        <td>{{ lang('ms_plan_title') }} :</td>
                        <td>
                            <input type="text" name="ms_plan[details][<?= $details['language_id']; ?>][title]"
                            value="<?= $details['title']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('ms_plan_description') }}</td>
                        <td>
                            <textarea
                                    name="ms_plan[details][<?= $details['language_id']; ?>][description]"
                            ><?= $details['description']; ?></textarea>
                        </td>
                    </tr>
                </table>
            </div>
            <?php } ?>
            <input type="hidden" name="ms_plan[actionType]" value="update" />
        </form>
    </div>
</div>
<script>

    $('#languages a').tabs();
</script>
<?php echo $footer; ?>