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
            <h1>{{ lang('pp_plus_heading_title') }}</h1>
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
                        <td><select name="pp_plus[status]" style="width: 100%;">
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
                        <td>{{ lang('test_mode') }}</td>
                        <td><select name="pp_plus[test_mode]" style="width: 100%;">
                                <option value="1" <?= $settings['test_mode'] == 1 ? 'selected="selected"' : ''; ?>>
                                {{ lang('text_enabled') }}
                                </option>
                                <option value="0" <?= $settings['test_mode'] != 1 ? 'selected="selected"' : ''; ?>>
                                {{ lang('text_disabled') }}
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('client_id') }}</td>
                        <td>
                            <input type="text" name="pp_plus[client_id]"
                                   value="<?= $settings['client_id']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('client_secret') }}</td>
                        <td>
                            <input type="text" name="pp_plus[client_secret]"
                                   value="<?= $settings['client_secret']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('entry_completed_status') }}</td>
                        <td><select name="pp_plus[completed_status_id]">
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
                        <td><select name="pp_plus[pending_status_id]">
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
                        <td><select name="pp_plus[partially_refunded_status_id]">
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
                        <td><select name="pp_plus[refunded_status_id]">
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
                        <td>{{ lang('entry_denied_status') }}</td>
                        <td><select name="pp_plus[denied_status_id]">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $settings['denied_status_id']) { ?>
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
                            <input type="text" name="pp_plus[sort_order]"
                                   value="<?= $settings['sort_order']; ?>" size="1" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ lang('webhook_string') }}
                            <span class="help">{{ lang('webhook_string_note') }}</span>
                        </td>
                        <td>
                            <input type="text" name="pp_plus[webhook]"
                                   value="<?= $settings['webhook']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ lang('callback_url') }}
                            <span class="help">{{ lang('callback_url_note') }}</span>
                        </td>
                        <td><?= $links['callback']; ?></td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>
<?php echo $footer; ?>