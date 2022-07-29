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
            <h1>{{ lang('network_marketing_heading_title') }}</h1>
            <div class="buttons">
                <a onclick="$('#form').submit();"
                   class="button btn btn-primary tab_settings">
                    <span>{{ lang('button_save') }}</span>
                </a>
                <a href="<?= $links['cancel']; ?>"
                   class="button btn btn-primary">
                    <span>{{ lang('button_back') }}</span>
                </a>
            </div>
        </div>
        <div class="content">
            <form action="<?= $links['submit']; ?>" method="post" id="form">
                <!--<div id="tabs" class="htabs">
                    <a href="#tab-general">{{ lang('tab_general') }}</a>
                    <a href="#tab-points">{{ lang('tab_points') }}</a>
                    <a href="#tab-warehouse">{{ lang('tab_warehouse') }}</a>
                </div>-->
                <div class="tab-content" id="tab-general">
                    <table class="table table-striped table-bordered">
                        <tr>
                            <td>{{ lang('entry_status') }} :</td>
                            <td>
                                <select class="form-control" name='network_marketing[nm_status]'>
                                    <option value='1'
                                    <?= ($settings['nm_status'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_enabled') }}</option>
                                    <option value='2'
                                    <?= ($settings['nm_status'] != '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_disabled') }}</option>
                                </select>
                            </td>
                        </tr>
                        <!-- <?php /*<tr>
                            <td>{{ lang('enable_notifications') }} :</td>
                            <td>
                                <select class="form-control" name='network_marketing[enable_notifications]'>
                                    <option value='1'
                                    <?= ($settings['enable_notifications'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_enabled') }}</option>
                                    <option value='2'
                                    <?= ($settings['enable_notifications'] != '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_disabled') }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ lang('enable_mail_notifications') }} :</td>
                            <td>
                                <select class="form-control" name='network_marketing[enable_mail_notifications]'>
                                    <option value='1'
                                    <?= ($settings['enable_mail_notifications'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_enabled') }}</option>
                                    <option value='2'
                                    <?= ($settings['enable_mail_notifications'] != '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_disabled') }}</option>
                                </select>
                            </td>
                        </tr> */ ?>-->
                        <tr>
                            <td>{{ lang('register_format') }} :</td>
                            <td>
                                <select class="form-control" name='network_marketing[register_format]'>
                                    <option value='1'
                                    <?= ($settings['register_format'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('referrals_register') }}</option>
                                    <option value='2'
                                    <?= ($settings['register_format'] == '2' ? 'selected' : ''); ?>
                                    >{{ lang('invitation_link') }}</option>
                                    <option value='3'
                                    <?= ($settings['register_format'] == '3' ? 'selected' : ''); ?>
                                    >{{ lang('text_both') }}</option>
                                </select>
                            </td>
                        </tr>
                        <?php /* <tr>
                            <td>{{ lang('invitation_link_expiration') }} :</td>
                            <td>
                                <input type="text" name="network_marketing[invitaion_link_expiration]"
                                       value="<?= $settings['invitaion_link_expiration']; ?>" />
                            </td>
                        </tr> */ ?>
                        <tr>
                            <td>{{ lang('maximum_number_of_agencies') }} :</td>
                            <td>
                                <input type="text" name="network_marketing[max_agencies]"
                                       value="<?= $settings['max_agencies']; ?>" />
                            </td>
                        </tr>
                        <!-- <?php /* <tr>
                            <td>{{ lang('enable_register_based_on') }} :</td>
                            <td>
                                <select class="form-control" name='network_marketing[register_definer]'>
                                    <option value='1'
                                    <?= ($settings['register_definer'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('total_points') }}</option>
                                    <option value='2'
                                    <?= ($settings['register_definer'] == '2' ? 'selected' : ''); ?>
                                    >{{ lang('spent_money') }}</option>
                                    <option value='3'
                                    <?= ($settings['register_definer'] == '3' ? 'selected' : ''); ?>
                                    >{{ lang('total_buyed_products') }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span>{{ lang('minimum_number_of_definer') }} :</span>
                                <span class="help">{{ lang('minimum_number_of_definer_note') }} :</span>
                            </td>
                            <td>
                                <input type="text" name="network_marketing[minimum_definer]"
                                       value="<?= $settings['minimum_definer']; ?>" />
                            </td>
                        </tr> */ ?> -->
                        <tr>
                            <td>
                                <span>{{ lang('minimum_number_of_points') }} :</span>
                                <span class="help">{{ lang('minimum_number_of_points_note') }} :</span>
                            </td>
                            <td>
                                <input type="text" name="network_marketing[minimum_points]"
                                       value="<?= $settings['minimum_points']; ?>" />
                            </td>
                        </tr>
                        <!-- <?php /* <tr>
                            <td>{{ lang('enable_transfer_credit') }} :</td>
                            <td>
                                <select class="form-control" name='network_marketing[transfer_credit]'>
                                    <option value='1'
                                    <?= ($settings['transfer_credit'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_enabled') }}</option>
                                    <option value='2'
                                    <?= ($settings['transfer_credit'] != '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_disabled') }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ lang('enable_buy_credit') }} :</td>
                            <td>
                                <select class="form-control" name='network_marketing[buy_credit]'>
                                    <option value='1'
                                    <?= ($settings['buy_credit'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_enabled') }}</option>
                                    <option value='2'
                                    <?= ($settings['buy_credit'] != '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_disabled') }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ lang('reward_type') }} :</td>
                            <td>
                                <select class="form-control" name='network_marketing[reward_type]'>
                                    <option value='1'
                                    <?= ($settings['reward_type'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_cash') }}</option>
                                    <option value='2'
                                    <?= ($settings['reward_type'] == '2' ? 'selected' : ''); ?>
                                    >{{ lang('text_points') }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ lang('min_order_price') }} :
                                <span class="help">{{ lang('min_order_price_note') }}</span>
                            </td>
                            <td>
                                <input class="form-control" name='network_marketing[min_order_price]'
                                       value="<?= $settings['min_order_price']; ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ lang('commision_definer') }} :
                            </td>
                            <td>
                                <select class="form-control" name='network_marketing[commision_definer]'>
                                    <option value='1'
                                    <?= ($settings['commision_definer'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_make_order') }}</option>
                                    <option value='2'
                                    <?= ($settings['commision_definer'] == '2' ? 'selected' : ''); ?>
                                    >{{ lang('text_register') }}</option>
                                    <option value='3'
                                    <?= ($settings['commision_definer'] == '3' ? 'selected' : ''); ?>
                                    >{{ lang('text_both') }}</option>
                                </select>
                            </td>
                        </tr> */ ?> -->
                    </table>
                </div>
                <?php /*
                <div class="tab-points" id="tab-points">
                    <table class="table table-striped table-bordered">
                        <tr>
                            <td>
                                <span>{{ lang('specify_points_to_usd') }} :</span>
                                <span class="help">{{ lang('specify_points_to_usd_note', '<?= $currency; ?>') }}</span>
                            </td>
                            <td>
                                <input class="form-control" name='network_marketing[points_to_usd]'
                                       value="<?= $settings['points_to_usd']; ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>{{ lang('specify_periods_to_balance') }} :</td>
                            <td>
                                {{ lang('text_each') }}
                                <input type="text" style="width: 10%;"
                                       name="network_marketing[periods_to_balance]"
                                       value="<?= $settings['periods_to_balance']; ?>" />
                                <select style="width: 20%;display: inline-block;"
                                        class="form-control" name='network_marketing[periods_type]'>
                                    <option value='1'
                                    <?= ($settings['periods_type'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_year') }}</option>
                                    <option value='2'
                                    <?= ($settings['periods_type'] == '2' ? 'selected' : ''); ?>
                                    >{{ lang('text_month') }}</option>
                                    <option value='3'
                                    <?= ($settings['periods_type'] == '3' ? 'selected' : ''); ?>
                                    >{{ lang('text_week') }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ lang('sepcify_maximum_balance_points_per_period') }} :
                                <span class="help">{{ lang('sepcify_maximum_balance_points_per_period_note') }}</span>
                            </td>
                            <td>
                                <input class="form-control" name='network_marketing[max_balance_points]'
                                       value="<?= $settings['max_balance_points']; ?>" />
                            </td>
                        </tr>
                    </table>
                </div>
                <?php */ ?>
            </form>
        </div>
    </div>
</div>
<script>
    $('#tabs a').tabs();
</script>
<?php echo $footer; ?>
