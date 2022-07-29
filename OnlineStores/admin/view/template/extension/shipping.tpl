<?php include_jsfile($header, $footer, 'view/template/extension/shipping.tpl.js'); ?>

<?php echo $header; ?>

<script>
    var mDisabledSuccess = '<?php echo $entry_disable_success; ?>';
    var mEnabledSuccess = '<?php echo $entry_enable_success; ?>';
    var mDeactivatedSuccess = '<?php echo $entry_deactivate_success; ?>';
    var mEnabled = '<?php echo $entry_enabledmethod; ?>';
    var mDisabled = '<?php echo $entry_disabledmethod; ?>';

    var deactivateURL = 'index.php?route=extension/shipping/deactivate&token=<?php echo $token; ?>&psid=';
    var disableURL = 'index.php?route=extension/shipping/disable&token=<?php echo $token; ?>';
    var enableURL = 'index.php?route=extension/shipping/enable&token=<?php echo $token; ?>';
</script>

<?php if ($success) { ?>
<script>
    var notificationString = '<?php echo $success; ?>';
    var notificationType = 'success';
</script>
<?php } ?>
<?php if ($error) { ?>
<script>
    var notificationString = '<?php echo $error; ?>';
    var notificationType = 'warning';
</script>
<?php } ?>

<div class="md-modal md-effect-11" id="modal-disable-ps">
    <div class="md-content">
        <div class="modal-header emerald-bg">
            <button class="md-close close">×</button>
            <h4 class="modal-title"><?php echo $entry_disable_payment_method; ?> <span id="ps-title"></span></h4>
        </div>
        <div class="modal-body">
            <p><?php echo $entry_disable_message; ?></p>

            <p><?php echo $entry_disable_message_conf; ?></p>
        </div>
        <div class="modal-footer">
            <span class="server-loading"><i class="fa fa-refresh fa-spin"></i></span>
            <button type="button" class="btn btn-primary" psid="" id="disable-ps"><?php echo $entry_disable; ?></button>
            <button type="button" class="btn btn-default" onclick="$(this).parents('.md-show').removeClass('md-show');"><?php echo $entry_cancel; ?></button>
        </div>
    </div>
</div>

<div class="md-modal md-effect-11" id="modal-deactivate-ps">
    <div class="md-content">
        <div class="modal-header red-bg">
            <button class="md-close close">×</button>
            <h4 class="modal-title"><?php echo $entry_deactivate_payment_method; ?> <span id="ps-title"></span></h4>
        </div>
        <div class="modal-body">
            <p><?php echo $entry_deactivate_message; ?></p>

            <p><?php echo $entry_deactivate_message_conf; ?></p>
        </div>
        <div class="modal-footer">
            <span class="server-loading"><i class="fa fa-refresh fa-spin"></i></span>
            <button type="button" class="btn btn-danger" psid="" id="deactivate-ps"><?php echo $entry_deactivate; ?></button>
            <button type="button" class="btn btn-default" onclick="$(this).parents('.md-show').removeClass('md-show');"><?php echo $entry_cancel; ?></button>
        </div>
    </div>
</div>

<div class="md-modal md-effect-11" id="modal-enable-ps">
    <div class="md-content">
        <div class="modal-header emerald-bg">
            <button class="md-close close">×</button>
            <h4 class="modal-title"><?php echo $entry_enable_payment_method; ?> <span id="ps-title"></span></h4>
        </div>
        <div class="modal-body">
            <p><?php echo $entry_enable_message; ?></p>

            <p><?php echo $entry_enable_message_conf; ?></p>
        </div>
        <div class="modal-footer">
            <span class="server-loading"><i class="fa fa-refresh fa-spin"></i></span>
            <button type="button" class="btn btn-primary" psid="" id="enable-ps"><?php echo $entry_enable; ?></button>
            <button type="button" class="btn btn-default" onclick="$(this).parents('.md-show').removeClass('md-show');"><?php echo $entry_cancel; ?></button>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-12">
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

        <?php if ($activated_methods) { ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="main-box clearfix activated-methods">
                    <header class="main-box-header psmethods clearfix green-bg">
                        <h1 class="pull-left"><?php echo $entry_activatedmethods; ?></h1>
                        <div class="pull-right">
                            <i class="fa fa-chevron-up"></i>
                        </div>
                    </header>

                    <div class="main-box-body clearfix psmethod-container">
                        <div class="widget-psmethod">
                            <?php foreach ($activated_methods as $gateway) { ?>
                            <div class="row method" psid="<?php echo $gateway['id']; ?>">
                                <div class="img col-lg-2 col-sm-2 col-xs-4">
                                    <img src="<?php echo $gateway['image']; ?>" alt="<?php echo $gateway['image_alt']; ?>">
                                </div>

                                <div class="product col-lg-8 col-sm-8 col-xs-8">
                                    <div class="name">
                                        <h2>
                                            <?php echo $gateway['title']; ?>
                                        </h2>

                                        <span class="green enabled" style="<?php echo $gateway['status'] === '0' ? 'display:none;' : ''; ?>"><?php echo $entry_enabledmethod; ?></span>
                                        <span class="red disabled" style="<?php echo $gateway['status'] === '1' ? 'display:none;' : ''; ?>"><?php echo $entry_disabledmethod; ?></span>
                                    </div>
                                    <div class="desc"><?php echo $gateway['desc']; ?></div>
                                </div>

                                <div class="actions col-lg-2 col-sm-2 hidden-xs">
                                    <a href="<?php echo $gateway['action']; ?>" class="btn btn-success" data-container="body" data-toggle="popover" data-placement="<?php echo $direction == 'rtl' ? 'right' : 'left'; ?>" data-content="<?php echo $entry_configure_tt; ?>" data-original-title="" data-trigger="hover"><?php echo $entry_configure; ?></a>
                                    <button type="submit" class="md-trigger btn btn-success"
                                            data-container="body"
                                            data-toggle="popover"
                                            data-placement="<?php echo $direction == 'rtl' ? 'right' : 'left'; ?>"
                                            data-content="<?php echo $entry_disable_tt; ?>"
                                            data-original-title=""
                                            data-trigger="hover"
                                            data-modal="modal-disable-ps"
                                            ps-title="<?php echo $gateway['title']; ?>"
                                            style="<?php echo $gateway['status'] === '0' ? 'display:none;' : ''; ?>"
                                            psid="<?php echo $gateway['id']; ?>"><?php echo $entry_disable; ?></button>
                                    <button type="submit" class="md-trigger btn btn-success"
                                            data-container="body"
                                            data-toggle="popover"
                                            data-placement="<?php echo $direction == 'rtl' ? 'right' : 'left'; ?>"
                                            data-content="<?php echo $entry_enable_tt; ?>"
                                            data-original-title=""
                                            data-trigger="hover"
                                            data-modal="modal-enable-ps"
                                            ps-title="<?php echo $gateway['title']; ?>"
                                            style="<?php echo $gateway['status'] === '1' ? 'display:none;' : ''; ?>"
                                            psid="<?php echo $gateway['id']; ?>"><?php echo $entry_enable; ?></button>
                                    <button type="submit" class="md-trigger btn btn-danger"
                                            data-container="body"
                                            data-toggle="popover"
                                            data-placement="<?php echo $direction == 'rtl' ? 'right' : 'left'; ?>"
                                            data-content="<?php echo $entry_deactivate_tt; ?>"
                                            data-original-title=""
                                            data-trigger="hover"
                                            data-modal="modal-deactivate-ps"
                                            ps-title="<?php echo $gateway['title']; ?>"
                                            psid="<?php echo $gateway['id']; ?>"><?php echo $entry_deactivate; ?></button>
                                </div>

                                <div class="actions-small col-lg-2 col-sm-2 visible-xs">
                                    <a href="<?php echo $gateway['action']; ?>" class="btn btn-success" data-container="body" data-toggle="popover" data-placement="<?php echo $direction == 'rtl' ? 'right' : 'left'; ?>" data-content="<?php echo $entry_configure_tt; ?>" data-original-title="" data-trigger="hover"><?php echo $entry_configure; ?></a>
                                    <button style="<?php echo $gateway['status'] === '1' ? 'display:none;' : ''; ?>" data-modal="modal-enable-ps" type="submit" class="md-trigger btn btn-success" data-container="body" data-toggle="popover" data-placement="<?php echo $direction == 'rtl' ? 'right' : 'left'; ?>" data-content="<?php echo $entry_enable_tt; ?>" data-original-title="" data-trigger="hover"><?php echo $entry_enable; ?></button>
                                    <button style="<?php echo $gateway['status'] === '0' ? 'display:none;' : ''; ?>" data-modal="modal-disable-ps" type="submit" class="md-trigger btn btn-success" data-container="body" data-toggle="popover" data-placement="<?php echo $direction == 'rtl' ? 'right' : 'left'; ?>" data-content="<?php echo $entry_disable_tt; ?>" data-original-title="" data-trigger="hover"><?php echo $entry_disable; ?></button>
                                    <button data-modal="modal-deactivate-ps" type="submit" class="md-trigger btn btn-danger" data-container="body" data-toggle="popover" data-placement="<?php echo $direction == 'rtl' ? 'right' : 'left'; ?>" data-content="<?php echo $entry_deactivate_tt; ?>" data-original-title="" data-trigger="hover"><?php echo $entry_deactivate; ?></button>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if ($delivery_companies) { ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="main-box clearfix available-methods">
                    <header class="main-box-header psmethods clearfix emerald-bg">
                        <h1 class="pull-left"><?php echo $entry_delivery_companies; ?></h1>
                        <div class="pull-right">
                            <i class="fa fa-chevron-up"></i>
                        </div>
                    </header>

                    <div class="main-box-body clearfix psmethod-container">
                        <div class="widget-psmethod">
                            <?php foreach ($delivery_companies as $gateway) { ?>
                            <div class="row method">
                                <div class="img col-lg-2 col-sm-2 col-xs-4">
                                    <img src="<?php echo $gateway['image']; ?>" alt="<?php echo $gateway['image_alt']; ?>">
                                </div>

                                <div class="product col-lg-8 col-sm-8 col-xs-8">
                                    <div class="name">
                                        <h2><?php echo $gateway['title']; ?></h2>
                                    </div>
                                    <div class="desc"><?php echo $gateway['desc']; ?></div>
                                </div>

                                <div class="actions col-lg-2 col-sm-2 hidden-xs">
                                    <a href="<?php echo $gateway['action']; ?>" class="btn btn-success" data-container="body" data-toggle="popover" data-placement="<?php echo $direction == 'rtl' ? 'right' : 'left'; ?>" data-content="<?php echo $entry_activate_tt; ?>" data-original-title="" data-trigger="hover"><?php echo $entry_activate; ?></a>
                                </div>

                                <div class="actions-small col-lg-2 col-sm-2 visible-xs">
                                    <a href="<?php echo $gateway['action']; ?>" class="btn btn-success" data-container="body" data-toggle="popover" data-placement="<?php echo $direction == 'rtl' ? 'right' : 'left'; ?>" data-content="<?php echo $entry_activate_tt; ?>" data-original-title="" data-trigger="hover"><?php echo $entry_activate; ?></a>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if ($custom_cost) { ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="main-box clearfix available-methods">
                    <header class="main-box-header psmethods clearfix emerald-bg">
                        <h1 class="pull-left"><?php echo $entry_custom_cost; ?></h1>
                        <div class="pull-right">
                            <i class="fa fa-chevron-up"></i>
                        </div>
                    </header>

                    <div class="main-box-body clearfix psmethod-container">
                        <div class="widget-psmethod">
                            <?php foreach ($custom_cost as $gateway) { ?>
                            <div class="row method">
                                <div class="img col-lg-2 col-sm-2 col-xs-4">
                                    <img src="<?php echo $gateway['image']; ?>" alt="<?php echo $gateway['image_alt']; ?>">
                                </div>

                                <div class="product col-lg-8 col-sm-8 col-xs-8">
                                    <div class="name">
                                        <h2><?php echo $gateway['title']; ?></h2>
                                    </div>
                                    <div class="desc"><?php echo $gateway['desc']; ?></div>
                                </div>

                                <div class="actions col-lg-2 col-sm-2 hidden-xs">
                                    <a href="<?php echo $gateway['action']; ?>" class="btn btn-success" data-container="body" data-toggle="popover" data-placement="<?php echo $direction == 'rtl' ? 'right' : 'left'; ?>" data-content="<?php echo $entry_activate_tt; ?>" data-original-title="" data-trigger="hover"><?php echo $entry_activate; ?></a>
                                </div>

                                <div class="actions-small col-lg-2 col-sm-2 visible-xs">
                                    <a href="<?php echo $gateway['action']; ?>" class="btn btn-success" data-container="body" data-toggle="popover" data-placement="<?php echo $direction == 'rtl' ? 'right' : 'left'; ?>" data-content="<?php echo $entry_activate_tt; ?>" data-original-title="" data-trigger="hover"><?php echo $entry_activate; ?></a>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if ($offline_methods) { ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="main-box clearfix available-methods">
                    <header class="main-box-header psmethods clearfix emerald-bg">
                        <h1 class="pull-left"><?php echo $entry_offlinemethods; ?></h1>
                        <div class="pull-right">
                            <i class="fa fa-chevron-up"></i>
                        </div>
                    </header>

                    <div class="main-box-body clearfix psmethod-container">
                        <div class="widget-psmethod">
                            <?php foreach ($offline_methods as $gateway) { ?>
                            <div class="row method">
                                <div class="img col-lg-2 col-sm-2 col-xs-4">
                                    <img src="<?php echo $gateway['image']; ?>" alt="<?php echo $gateway['image_alt']; ?>">
                                </div>

                                <div class="product col-lg-8 col-sm-8 col-xs-8">
                                    <div class="name">
                                        <h2><?php echo $gateway['title']; ?></h2>
                                    </div>
                                    <div class="desc"><?php echo $gateway['desc']; ?></div>
                                </div>

                                <div class="actions col-lg-2 col-sm-2 hidden-xs">
                                    <a href="<?php echo $gateway['action']; ?>" class="btn btn-success" data-container="body" data-toggle="popover" data-placement="<?php echo $direction == 'rtl' ? 'right' : 'left'; ?>" data-content="<?php echo $entry_activate_tt; ?>" data-original-title="" data-trigger="hover"><?php echo $entry_activate; ?></a>
                                </div>

                                <div class="actions-small col-lg-2 col-sm-2 visible-xs">
                                    <a href="<?php echo $gateway['action']; ?>" class="btn btn-success" data-container="body" data-toggle="popover" data-placement="<?php echo $direction == 'rtl' ? 'right' : 'left'; ?>" data-content="<?php echo $entry_activate_tt; ?>" data-original-title="" data-trigger="hover"><?php echo $entry_activate; ?></a>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<div class="md-overlay"></div><!-- the overlay element -->
<?php echo $footer; ?>