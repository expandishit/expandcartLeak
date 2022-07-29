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
            <h1>{{ lang('fastpay_heading_title') }}</h1>
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
                        <td><select name="fastpaycash[status]" style="width: 100%;">
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
                        <td><select name="fastpaycash[test_mode]" style="width: 100%;">
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
                        <td>{{ lang('mobile_no') }}</td>
                        <td>
                            <input type="text" name="fastpaycash[merchant_no]"
                                   value="<?= $settings['merchant_no']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('store_password') }}</td>
                        <td>
                            <input type="text" name="fastpaycash[store_password]"
                                   value="<?= $settings['store_password']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('entry_completed_status') }}</td>
                        <td><select name="fastpaycash[completed_status_id]">
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
                        <td><select name="fastpaycash[pending_status_id]">
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
                        <td>{{ lang('entry_denied_status') }}</td>
                        <td><select name="fastpaycash[denied_status_id]">
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
                        <td>{{ lang('entry_cancelled_status') }}</td>
                        <td><select name="fastpaycash[cancelled_status_id]">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $settings['cancelled_status_id']) { ?>
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
                            <input type="text" name="fastpaycash[sort_order]"
                                   value="<?= $settings['sort_order']; ?>" size="1" />
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