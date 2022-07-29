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
    <?php if ($success) { ?>
    <script>
        var notificationString = '<?php echo $success; ?>';
        var notificationType = 'success';
    </script>
    <?php } ?>
    <?php if ($error_warning) { ?>
    <script>
        var notificationString = '<?php echo $error_warning; ?>';
        var notificationType = 'warning';
    </script>
    <?php } ?>
    <div class="box">
        <div class="heading">
            <h1>{{ lang('sofort_heading_title') }}</h1>
            <div class="buttons">
                <a onclick="$('#form').submit();" class="button btn btn-primary">
                    {{ lang('button_save') }}
                </a>
                <a href="<?php echo $links['cancel']; ?>" class="button btn btn-primary">
                    {{ lang('button_cancel') }}
                </a>
            </div>
        </div>
        <div class="content">
            <form action="<?php echo $links['submit']; ?>" method="post" id="form">
                <table class="form">
                    <tr>
                        <td>{{ lang('entry_status') }}</td>
                        <td><select name="sofort[status]" style="width: 100%;">
                                <option value="1" <?= $settings['status'] == 1 ? 'selected="selected"' : ''; ?>>
                                    {{ lang('text_enabled') }}
                                </option>
                                <option value="0" <?= $settings['status'] != 1 ? 'selected="selected"' : ''; ?>>
                                    {{ lang('text_disabled') }}
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('config_key') }}</td>
                        <td>
                            <input type="text" name="sofort[config_key]"
                                   value="<?= $settings['config_key']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ lang('default_currency') }}
                            <span class="help">{{ lang('default_currency_note') }}</span>
                        </td>
                        <td><select name="sofort[default_currency]" style="width: 100%;">
                                <?php foreach ($currencies as $key => $currency) { ?>
                                <?php if ($key == $settings['default_currency']) { ?>
                                <option value="<?php echo $key; ?>" selected="selected"><?php echo $key; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $key; ?>"><?php echo $key; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td>{{ lang('entry_untraceable_status') }}</td>
                        <td><select name="sofort[untraceable_status_id]">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $settings['untraceable_status_id']) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td>{{ lang('entry_completed_status') }}</td>
                        <td><select name="sofort[completed_status_id]">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $settings['completed_status_id']) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td>{{ lang('entry_pending_status') }}</td>
                        <td><select name="sofort[pending_status_id]">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $settings['pending_status_id']) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td>{{ lang('entry_partially_refunded_status') }}</td>
                        <td><select name="sofort[partially_refunded_status_id]">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $settings['partially_refunded_status_id']) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td>{{ lang('entry_refunded_status') }}</td>
                        <td><select name="sofort[refunded_status_id]">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $settings['refunded_status_id']) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td>{{ lang('entry_sort_order') }}</td>
                        <td>
                            <input type="text" name="sofort[sort_order]"
                                   value="<?= $settings['sort_order']; ?>" size="1" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>
<?php echo $footer; ?>