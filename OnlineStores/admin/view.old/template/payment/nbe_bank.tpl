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

    <div class="box">
        <div class="heading">
            <h1><?php echo $heading_title; ?></h1>
            <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
        </div>
        <div class="content">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                <table class="form">
                    <tr>
                        <td>{{ lang('entry_status') }}</td>
                        <td><select name="nbe_bank_status">
                                <?php if ($nbe_bank['nbe_bank_status']) { ?>
                                <option value="1" selected="selected">{{ lang('text_enabled') }}</option>
                                <option value="0">{{ lang('text_disabled') }}</option>
                                <?php } else { ?>
                                <option value="1">{{ lang('text_enabled') }}</option>
                                <option value="0" selected="selected">{{ lang('text_disabled') }}</option>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td><span class="required">*</span> {{ lang('entry_api_url') }}</td>
                        <td><input type="text" name="nbe_bank_api_url" value="<?= $nbe_bank['api_url']; ?>" />
                            <?php if ($error_api_url) { ?>
                            <span class="error"><?php echo $error_api_url; ?></span>
                            <?php } ?></td>
                    </tr>
                    <tr>
                        <td>
                            <span class="required">*</span>
                            {{ lang('entry_merchant_number') }}
                            <span class="help">{{ lang('entry_merchant_number_note') }}</span>
                        </td>
                        <td><input type="text" name="nbe_bank_merchant_number" value="<?= $nbe_bank['merchant_number'] ?>" />
                            <?php if ($error_merchant_number) { ?>
                            <span class="error"><?php echo $error_merchant_number; ?></span>
                            <?php } ?></td>
                    </tr>
                    <tr>
                        <td><span class="required">*</span> {{ lang('entry_username') }}</td>
                        <td><input type="text" name="nbe_bank_username" value="<?= $nbe_bank['username'] ?>" />
                            <?php if ($error_username) { ?>
                            <span class="error"><?php echo $error_username; ?></span>
                            <?php } ?></td>
                    </tr>
                    <tr>
                        <td><span class="required">*</span> {{ lang('entry_password') }}</td>
                        <td><input type="password" name="nbe_bank_password" value="<?= $nbe_bank['password'] ?>" />
                            <?php if ($error_password) { ?>
                            <span class="error"><?php echo $error_password; ?></span>
                            <?php } ?></td>
                    </tr>
                    <?php /*
                    <tr>
                        <td>
                            {{ lang('entry_payment_action') }}
                            <span class="help">{{ lang('entry_payment_action_note') }}</span>
                        </td>
                        <td><select name="nbe_bank_payment_action" id="input-payment-action">
                                <?php if ($nbe_bank['payment_action'] == 'payment_only') { ?>
                                <option value="payment_only" selected="selected">{{ lang('text_purchase') }}</option>
                                <?php } else { ?>
                                <option value="payment_only">{{ lang('text_purchase') }}</option>
                                <?php } ?>
                                <?php if ($nbe_bank['payment_action'] == 'preauth_capture') { ?>
                                <option value="preauth_capture" selected="selected">{{ lang('text_preauth_capture') }}</option>
                                <?php } else { ?>
                                <option value="preauth_capture">{{ lang('text_preauth_capture') }}</option>
                                <?php } ?>
                            </select></td>
                    </tr>
                    */ ?>
                    <tr>
                        <td>
                            {{ lang('entry_test_mode') }}
                            <span class="help">{{ lang('entry_test_mode_note') }}</span>
                        </td>
                        <td><select name="nbe_bank_test_mode">
                                <?php if ($nbe_bank['test_mode']) { ?>
                                <option value="1" selected="selected">{{ lang('text_yes') }}</option>
                                <option value="0">{{ lang('text_no') }}</option>
                                <?php } else { ?>
                                <option value="1">{{ lang('text_yes') }}</option>
                                <option value="0" selected="selected">{{ lang('text_no') }}</option>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td>{{ lang('entry_pending_status') }}</td>
                        <td>

                            <!-- this is a temporary fixed value untill we enable preauth actions -->
                            <input type="hidden" value="payment_only" name="nbe_bank_payment_action" />

                            <select name="nbe_bank_pending_status_id">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $nbe_bank['pending_status_id']) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr class="method-payment-only">
                        <td>{{ lang('entry_payment_status') }}</td>
                        <td><select name="nbe_bank_payment_status_id">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $nbe_bank['payment_status_id']) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td>{{ lang('entry_failed_status') }}</td>
                        <td><select name="nbe_bank_failed_status_id">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $nbe_bank['failed_status_id']) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <?php /*
                    <tr class="method-preauth-capture">
                        <td>{{ lang('entry_preauth_status') }}</td>
                        <td><select name="nbe_bank_preauth_status_id">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $nbe_bank['preauth_status_id']) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr class="method-preauth-capture">
                        <td>{{ lang('entry_captured_status') }}</td>
                        <td><select name="nbe_bank_captured_status_id">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $nbe_bank['captured_status_id']) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select></td>
                    </tr>
                    */ ?>
                    <tr>
                        <td>{{ lang('entry_refunded_status') }}</td>
                        <td><select name="nbe_bank_refunded_status_id">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $nbe_bank['refunded_status_id']) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td>
                            {{ lang('entry_total') }}
                            <span class="help">{{ lang('entry_total_note') }}</span>
                        </td>
                        <td>
                            <input type="text" name="nbe_bank_total" value="<?= $nbe_bank['total']  ?>" id="smeonline_total" />
                        </td>
                    </tr>
                    <tr>
                        <td>{{ lang('entry_geo_zone') }}</td>
                        <td><select name="nbe_bank_geo_zone_id">
                                <option value="0">{{ lang('text_all_zones') }}</option>
                                <?php foreach ($geo_zones as $geo_zone) { ?>
                                <?php if ($geo_zone['geo_zone_id'] == $nbe_bank['geo_zone_id']) { ?>
                                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td>{{ lang('entry_sort_order') }}</td>
                        <td>
                            <input type="text" name="nbe_bank_sort_order" value="<?= $nbe_bank['sort_order']?>" size="1" id="smeonline_sort_order" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    // Validate total input
    $('input[id=smeonline_total]').keyup(function () {
        $(this).val($(this).val().replace(/[^0-9.]/g, ''));
    });

    $('input[id=smeonline_sort_order]').keyup(function () {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
    });

    // Checking transaction method selected
    if ($('#input-payment-action').val() == 'payment_only') {
        $('.method-preauth-capture').hide();
    } else {
        $('.method-payment-only').hide();
    }

    $('select[name="nbe_bank_payment_action').change(function () {
        var val = $(this).val();
        switch (val) {
            case 'payment_only':
                $('.method-preauth-capture').hide();
                $('.method-payment-only').show();
                break;

            case 'preauth_capture':
                $('.method-preauth-capture').show();
                $('.method-payment-only').hide();
                break;

        }
    });
</script>
<?php echo $footer; ?>