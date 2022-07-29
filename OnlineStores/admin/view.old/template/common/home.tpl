<?php include_jsfile($header, $footer, 'view/javascript/cube/wizard.js'); ?>
<?php include_jsfile($header, $footer, 'view/template/common/home.tpl.js'); ?>
<?php include_cssfile($header, $footer, 'view/stylesheet/cube/css/compiled/wizard.css'); ?>
<?php echo $header; ?>

<script>
    var chartURL = 'index.php?route=common/home/chart&token=<?php echo $token; ?>&range=year';
    var hideGettingStartedURL = 'index.php?route=common/home/skiptut&token=<?php echo $token; ?>';
    var text_orders = '<?php echo $text_orders; ?>';
    var text_customers = '<?php echo $text_customers; ?>';
    var selLang = '<?php echo $direction; ?>';
    var gettingStarted = '<?php echo $gettingStarted["GENERAL"]; ?>';
</script>

<?php if ($error_install) { ?>
<script>
    var notificationString = '<?php echo $error_install; ?>';
    var notificationType = 'warning';
</script>
<?php } ?>

<?php if ($error_image) { ?>
<script>
    var notificationString = '<?php echo $error_image; ?>';
    var notificationType = 'warning';
</script>
<?php } ?>

<?php if ($error_image_cache) { ?>
<script>
    var notificationString = '<?php echo $error_image_cache; ?>';
    var notificationType = 'warning';
</script>
<?php } ?>

<?php if ($error_cache) { ?>
<script>
    var notificationString = '<?php echo $error_cache; ?>';
    var notificationType = 'warning';
</script>
<?php } ?>

<?php if ($error_download) { ?>
<script>
    var notificationString = '<?php echo $error_download; ?>';
    var notificationType = 'warning';
</script>
<?php } ?>

<?php if ($error_logs) { ?>
<script>
    var notificationString = '<?php echo $error_logs; ?>';
    var notificationType = 'warning';
</script>
<?php } ?>

<div class="row">
<div class="col-lg-12">
<div class="row" id="breadcrumb" <?php echo $gettingStarted['GENERAL'] == '0' ? 'style="display:none;"' : ''; ?>>
    <div class="col-lg-12">
        <div id="content-header" class="clearfix">
            <div class="pull-left">
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

                <h1><?php echo $heading_title; ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="row" id="guideDiv" <?php echo $gettingStarted['GENERAL'] == '1' ? 'style="display:none;"' : ''; ?>>
    <div class="col-lg-12">
        <div class="main-box clearfix">
            <div class="main-box-body">

                <header class="main-box-header clearfix getting-started-header">
                    <h1><?php echo $text_welcome; ?></h1>
                    <h2><?php echo $text_welcome_sub; ?></h2>
                </header>

                <?php if ($gettingStarted['EDIT_SETTINGS'] == '0') { ?>
                <div class="row">
                    <div class="col-lg-12 col-sm-12 col-xs-12">
                        <div class="main-box infographic-box">
                            <i class="fa fa-gears yellow-bg"></i>

                            <span class="headline"><?php echo $text_edit_settings; ?></span>
                        <span class="value">
                            <span class="timer" data-from="30" data-to="658" data-speed="800" data-refresh-interval="30">
                                <?php echo $text_edit_settings_sub; ?>
                            </span>
                        </span>
                            <a class="btn btn-success" href="<?php echo $directStoreSettings; ?>"><?php echo $text_edit_settings_btn; ?></a>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php if ($gettingStarted['CUST_DESIGN'] == '0') { ?>
                <div class="row">
                    <div class="col-lg-12 col-sm-12 col-xs-12">
                        <div class="main-box infographic-box">
                            <i class="fa fa-magic green-bg"></i>
                            <span class="headline"><?php echo $text_customize_design; ?></span>

                        <span class="value">
                            <span class="timer" data-from="30" data-to="658" data-speed="800" data-refresh-interval="30">
                                <?php echo $text_customize_design_sub; ?>
                            </span>
                        </span>
                            <a class="btn btn-success" href="<?php echo $changeTemplateURL; ?>"><?php echo $text_customize_design_btn; ?></a>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php if ($gettingStarted['ADD_PRODUCTS'] == '0') { ?>
                <div class="row">
                    <div class="col-lg-12 col-sm-12 col-xs-12">
                        <div class="main-box infographic-box">
                            <i class="fa fa-tags purple-bg"></i>

                            <span class="headline"><?php echo $text_add_product; ?></span>
                        <span class="value">
                            <span class="timer" data-from="30" data-to="658" data-speed="800" data-refresh-interval="30">
                                <?php echo $text_add_product_sub; ?>
                            </span>
                        </span>
                            <a class="btn btn-success" href="<?php echo $insertProductURL; ?>"><?php echo $text_add_product_btn; ?></a>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php if ($gettingStarted['UPGRADE_NOW'] == '0') { ?>
                <div class="row">
                    <div class="col-lg-12 col-sm-12 col-xs-12">
                        <div class="main-box infographic-box">
                            <i class="fa fa-dollar emerald-bg"></i>

                            <span class="headline"><?php echo $text_upgrade_domain; ?></span>
                        <span class="value">
                            <span class="timer" data-from="30" data-to="658" data-speed="800" data-refresh-interval="30">
                                <?php echo $text_upgrade_domain_sub; ?>
                            </span>
                        </span>
                            <a class="btn btn-danger" href="<?php echo $upgradeNowURL; ?>"><?php echo $text_upgrade_domain_btn; ?></a>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php if ($gettingStarted['EDIT_SETTINGS'] == '1') { ?>
                <div class="row">
                    <div class="col-lg-12 col-sm-12 col-xs-12">
                        <div class="main-box infographic-box done-step">
                            <i class="fa fa-check gray-bg"></i>

                            <span class="headline"><?php echo $text_edit_settings; ?></span>
                        <span class="value">
                            <span class="timer" data-from="30" data-to="658" data-speed="800" data-refresh-interval="30">
                                <?php echo $text_edit_settings_sub; ?>
                            </span>
                        </span>
                            <a class="btn btn-default" href="<?php echo $directStoreSettings; ?>"><?php echo $text_edit_settings_btn; ?></a>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php if ($gettingStarted['CUST_DESIGN'] == '1') { ?>
                <div class="row">
                    <div class="col-lg-12 col-sm-12 col-xs-12">
                        <div class="main-box infographic-box done-step">
                            <i class="fa fa-check gray-bg"></i>
                            <span class="headline"><?php echo $text_customize_design; ?></span>

                        <span class="value">
                            <span class="timer" data-from="30" data-to="658" data-speed="800" data-refresh-interval="30">
                                <?php echo $text_customize_design_sub; ?>
                            </span>
                        </span>
                            <a class="btn btn-default" href="<?php echo $changeTemplateURL; ?>"><?php echo $text_customize_design_btn; ?></a>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php if ($gettingStarted['ADD_PRODUCTS'] == '1') { ?>
                <div class="row">
                    <div class="col-lg-12 col-sm-12 col-xs-12">
                        <div class="main-box infographic-box done-step">
                            <i class="fa fa-check gray-bg"></i>

                            <span class="headline"><?php echo $text_add_product; ?></span>
                        <span class="value">
                            <span class="timer" data-from="30" data-to="658" data-speed="800" data-refresh-interval="30">
                                <?php echo $text_add_product_sub; ?>
                            </span>
                        </span>
                            <a class="btn btn-default" href="<?php echo $insertProductURL; ?>"><?php echo $text_add_product_btn; ?></a>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php if ($gettingStarted['UPGRADE_NOW'] == '1') { ?>
                <div class="row">
                    <div class="col-lg-12 col-sm-12 col-xs-12">
                        <div class="main-box infographic-box done-step">
                            <i class="fa fa-check gray-bg"></i>

                            <span class="headline"><?php echo $text_upgrade_domain; ?></span>
                        <span class="value">
                            <span class="timer" data-from="30" data-to="658" data-speed="800" data-refresh-interval="30">
                                <?php echo $text_upgrade_domain_sub; ?>
                            </span>
                        </span>
                            <a class="btn btn-default" href="<?php echo $upgradeNowURL; ?>"><?php echo $text_upgrade_domain_btn; ?></a>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <p>
                    <?php echo $text_skip_tutorial; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row" id="shortStats" <?php echo $gettingStarted['GENERAL'] == '0' ? 'style="display:none;"' : ''; ?>>
    <div class="col-lg-3 col-sm-6 col-xs-12">
        <div class="main-box infographic-box colored emerald-bg">
            <span class="headline"><?php echo $text_total_sale; ?></span>
            <span class="value"><?php echo $total_sale; ?></span>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 col-xs-12">
        <div class="main-box infographic-box colored green-bg">
            <i class="fa fa-shopping-cart"></i>
            <span class="headline"><?php echo $text_order; ?></span>
            <span class="value"><?php echo $total_order; ?></span>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 col-xs-12">
        <div class="main-box infographic-box colored red-bg">
            <i class="fa fa-user"></i>
            <span class="headline"><?php echo $text_total_customer; ?></span>
            <span class="value"><?php echo $total_customer; ?></span>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 col-xs-12">
        <div class="main-box infographic-box colored purple-bg">
            <i class="fa fa-globe"></i>
            <span class="headline"><?php echo $text_total_affiliate; ?></span>
            <span class="value"><?php echo $total_affiliate; ?></span>
        </div>
    </div>
</div>

<div class="row" id="yearlyData" <?php echo $gettingStarted['GENERAL'] == '0' ? 'style="display:none;"' : ''; ?>>
    <div class="col-md-12">
        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2 class="pull-left"><?php echo $text_sales_earnings; ?></h2>
            </header>

            <div class="main-box-body clearfix">
                <div class="row">
                    <div class="col-md-9">
                        <div id="graph-bar" style="height: 240px; padding: 0px; position: relative;"></div>
                    </div>
                    <div class="col-md-3">
                        <ul class="graph-stats">
                            <li>
                                <div class="clearfix">
                                    <div class="title pull-left">
                                        <?php echo $text_orders; ?>
                                    </div>
                                    <div class="value pull-right" data-toggle="tooltip">
                                        <?php echo $total_order_year; ?>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="clearfix">
                                    <div class="title pull-left">
                                        <?php echo $text_customers; ?>
                                    </div>
                                    <div class="value pull-right" data-toggle="tooltip">
                                        <?php echo $total_customer_year; ?>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="clearfix">
                                    <div class="title pull-left">
                                        <?php echo $text_earnings; ?>
                                    </div>
                                    <div class="value pull-right" data-toggle="tooltip">
                                        <?php echo $total_sale_year; ?>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="lastOrders" <?php echo $gettingStarted['GENERAL'] == '0' ? 'style="display:none;"' : ''; ?>>
    <div class="col-lg-12">
        <div class="main-box clearfix">
            <header class="main-box-header clearfix">
                <h2 class="pull-left"><?php echo $text_latest_10_orders; ?></h2>

                <div class="filter-block pull-right">
                    <a href="<?php echo $orders_link; ?>" class="btn btn-primary pull-right">
                        <i class="fa fa-eye fa-lg"></i> <?php echo $text_view_all_orders; ?>
                    </a>
                </div>
            </header>

            <div class="main-box-body clearfix">
                <div class="table-responsive clearfix">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th><span><?php echo $text_orderID_grid; ?></span></th>
                            <th><span><?php echo $text_Date_grid; ?></span></th>
                            <th><span><?php echo $text_customer_grid; ?></span></th>
                            <th class="text-center"><span><?php echo $text_status_grid; ?></span></th>
                            <th class="text-right"><span><?php echo $text_price_grid; ?></span></th>
                            <th>&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php if ($orders) { ?>
                            <?php foreach ($orders as $order) { ?>
                            <tr>
                                <td>
                                    <a href="<?php echo $order['action']['href']; ?>">#<?php echo $order['order_id']; ?></a>
                                </td>
                                <td>
                                    <?php echo $order['date_added']; ?>
                                </td>
                                <td>
                                    <?php if (!empty($order['action']['customer_href'])): ?>
                                        <a href="<?php echo $order['action']['customer_href']; ?>"><?php echo $order['customer']; ?></a>
                                    <?php else: ?>
                                        <p title="<?= $title_no_customer_exists; ?>">
                                            <?= $order['customer']; ?>        
                                        </p>
                                    <?php endif ?>
                                </td>
                                <td class="text-center">
                                    <span class="label label-success"><?php echo $order['status']; ?></span>
                                </td>
                                <td class="text-right">
                                    <?php echo $order['total']; ?>
                                </td>
                                <td class="text-center" style="width: 15%;">
                                    <a href="<?php echo $order['action']['href']; ?>" class="table-link">
                                                                        <span class="fa-stack">
                                                                            <i class="fa fa-square fa-stack-2x"></i>
                                                                            <i class="fa fa-search-plus fa-stack-1x fa-inverse"></i>
                                                                        </span>
                                    </a>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php } else { ?>
                            <tr>
                                <td valign="top" colspan="6" class="dataTables_empty"><?php echo $text_no_results; ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
</div>
<?php echo $footer; ?>