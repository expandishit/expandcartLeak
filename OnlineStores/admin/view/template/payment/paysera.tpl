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
            <div class="buttons"><a onclick="$('#form-paysera').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
        </div>
        <div class="content">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-paysera" class="form-horizontal">
                <table class="form">
                    <tr>
                        <td><span class="required">*</span> <?php echo $entry_project; ?></td>
                        <td>
                            <input type="text" name="paysera_project" value="<?php echo $paysera_project; ?>" class="form-control" />
                            <?php if ($error_project) { ?>
                            <span class="error"><?php echo $error_project; ?></span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="required">*</span> <?php echo $entry_sign; ?></td>
                        <td>
                            <input type="text" name="paysera_sign" value="<?php echo $paysera_sign; ?>" class="form-control" />
                            <?php if ($error_project) { ?>
                            <span class="error"><?php echo $error_sign; ?></span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="required">*</span> <?php echo $entry_lang; ?>
                            <br /><span class="help"><?= $help_lang ?></span>
                        </td>
                        <td>
                            <input type="text" name="paysera_lang" value="<?php echo $paysera_lang; ?>" class="form-control" />
                            <?php if ($error_project) { ?>
                            <span class="error"><?php echo $error_lang; ?></span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_test; ?></td>
                        <td>
                             <select name="paysera_test" class="form-control">
                                <?php if($paysera_test == 0): ?>
                                     <option value="0" selected="selected"><?php echo $text_off; ?></option>
                                <option value="100"><?php echo $text_on; ?></option>
                                     <?php else: ?>
                                     <option value="0"><?php echo $text_off; ?></option>
                                <option value="100" selected="selected"><?php echo $text_on; ?></option>
                                     <?php endif;?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_new_order_status; ?></td>
                        <td>
                            <select name="paysera_new_order_status_id" id="input-new-order-status" class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $paysera_new_order_status_id) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_order_status; ?></td>
                        <td>
                            <select name="paysera_order_status_id" id="input-order-status" class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $paysera_order_status_id) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_geo_zone; ?></td>
                        <td>
                            <select name="paysera_geo_zone_id" id="input-geo-zone" class="form-control">
                                <option value="0"><?php echo $text_all_zones; ?></option>
                                <?php foreach ($geo_zones as $geo_zone) { ?>
                                <?php if ($geo_zone['geo_zone_id'] == $paysera_geo_zone_id) { ?>
                                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_status; ?></td>
                        <td>
                            <select name="paysera_status" id="input-status" class="form-control">
                                <?php if ($paysera_status === "1") { ?>
                                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } elseif ($paysera_status === "0") { ?>
                                <option value="1"><?php echo $text_enabled; ?></option>
                                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php } else { ?>
                                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_display_payments; ?></td>
                        <td>
                            <select name="paysera_display_payments_list" class="form-control">
                                <?php if($paysera_display_payments_list == 1): ?>
                                <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                                <option value="0"><?php echo $text_no; ?></option>
                                <?php else: ?>
                                <option value="1"><?php echo $text_yes; ?></option>
                                <option value="0" selected="selected"><?php echo $text_no; ?></option>
                                <?php endif; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_sort_order; ?></td>
                        <td><input type="text" name="paysera_sort_order" value="<?php echo $paysera_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" /></td>
                    </tr>
                </table>
            </form>
        </div>

    </div>
</div>

<?php echo $footer; ?>