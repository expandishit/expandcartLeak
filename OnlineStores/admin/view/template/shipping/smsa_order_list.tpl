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
            <h1>{{ lang('heading_title_list_orders') }}</h1>
            <div class="buttons">
                <a href="<?= $links['settings']; ?>" class="button btn btn-primary">{{ lang('button_extension_settings') }}</a>
            </div>
        </div>
        <div class="content">
            <form action="" method="post" enctype="multipart/form-data" id="form">
                <table class="table table-hover dataTable no-footer">
                    <thead>
                    <tr>
                        <td>#</a></td>
                        <td>{{ lang('column_customer') }}</a></td>
                        <td>{{ lang('order_status') }}</a></td>
                        <td>{{ lang('column_status') }}</a></td>
                        <td>&nbsp;</td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($orders) { ?>
                    <?php foreach ($orders as $order) { ?>
                    <tr>
                        <td class="right"><?= $order['orderId'] ?></td>
                        <td class="left"><?= $order['firstname'] . $order['lastname']; ?></td>
                        <td class="left"><?= $order['name']; ?></td>
                        <td class="left">
                            <div class="smsaStatus">
                                {{ lang('smsa_shipment_status_<?= $order["shipment_status"]; ?>') }}
                            </div>
                        </td>
                        <td>
                            <a href="<?= $links['viewOrder'] ?>&order_id=<?= $order['orderId'] ?>"
                               class="btn btn-primary">{{ lang('view_order') }}</a>

                            <?php if (isset($order['shipment_id'])) { ?>
                            <a data-href="<?= $links['updateStatus'] ?>&order_id=<?= $order['order_id'] ?>"
                               class="inlineRows updateSmsaStatus btn btn-primary">{{ lang('update_smsa_status') }}</a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php } else { ?>
                    <tr>
                        <td class="center" colspan="8"><?php echo $text_no_results; ?></td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </form>
            <div class="pagination"><?php echo $pagination; ?></div>
        </div>
    </div>
</div>

<?php echo $footer; ?>
