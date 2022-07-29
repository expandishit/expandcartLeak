<?php echo $header; ?>
<?php include_jsfile($header, $footer, 'view/template/marketplace/home.tpl.js'); ?>

<div class="md-modal md-effect-11" id="modal-pick-plan">
    <div class="md-content">
        <div class="modal-header emerald-bg">
            <button class="md-close close">×</button>
            <h4 class="modal-title"><?php echo $text_choose_plans; ?></h4>
        </div>
        <div class="modal-body" data-srcId="">
            <span><?php echo $text_choose_plans_text; ?></span>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="sign-up" onclick="goToPackages();"><?php echo $text_explore_plans; ?></button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="row" id="breadcrumb">
            <div class="col-lg-12">
                <div id="content-header" class="clearfix">
                    <div class="pull-left">
                        <?php if (defined('CLIENT_SUSPEND')) { ?>
                        <h1><?php echo $trial_expired; ?></h1>
                        <?php } else { ?>
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
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

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

        <div class="row marketplace" id="marketplace">
            <?php if ($AvaliableApps) { ?>
            <div class="col-lg-12">
                <div class="main-box appservice-container clearfix">
                    <br/>
                    <?php foreach ($AvaliableApps as $app) { ?>
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <div class="main-box clearfix project-box">
                            <div class="main-box-body clearfix">
                                <div class="project-box-header <?php if ($app['purchased'] || $app['isbundle']) { echo 'gray-bg'; } else { echo 'green-bg'; } ?>">
                                    <div class="name" title="<?php echo $app['name'];?>">
                                        <a href="<?php echo $app['applink']; ?>">
                                            <?php echo $app['name']; ?>
                                        </a>

                                    </div>
                                </div>

                                <div class="project-box-content">
                                    <div class="profile-box-header clearfix">
                                        <a href="<?php echo $app['applink']; ?>"><img src="<?php echo $app['image']; ?>" alt="" class="profile-img img-responsive center-block" /></a>
                                        <h2><?php echo $app['desc']; ?></h2>
                                    </div>
                                </div>

                                <div class="buy-now-area clearfix">
                                    <?php if (!$app['purchased'] && !$app['isbundle']) { ?>
                                    <h2>
                                        <?php if ($app['price'] != '$0') { ?>
                                        <span class="sky-highlight"><?php echo $app['price']; ?></span>
                                        <?php
                                            if ($app['recurring'] == '0') {
                                                echo $text_rec_once;
                                            }
                                        ?>
                                        <?php } ?>
                                    </h2>

                                    <?php if ($isTrial == "1") { ?>
                                        <a class="btn btn-primary md-trigger center-block buy-button" data-modal="modal-pick-plan"><?php echo $text_buy; ?></a>
                                    <?php } else { ?>
                                        <a href="<?php echo $app['buylink']; ?>" class="btn btn-primary center-block buy-button qty-buy"><?php echo $text_buy;?></a>
                                    <?php } ?>

                                    <?php } ?>
                                    <h2>
                                        <?php
                                            if ($app['freeplan'] == '2') {
                                                echo $text_free_all;
                                            } else if ($app['freeplan'] == '4') {
                                                echo $text_free_business;
                                            } else if ($app['freeplan'] == '6') {
                                                echo $text_free_ultimate;
                                            } else if ($app['freeplan'] == '8') {
                                                echo $text_free_enterprise;
                                            }
                                        ?>
                                    </h2>
                                </div>

                                <?php if ($app['purchased'] || $app['isbundle']) { ?>
                                <div class="project-box-footer clearfix">
                                    <?php if($app['installed']) {?>
                                    <a href="<?php echo $app['extensionlink']; ?>">
                                    <span class="value"><i class="fa fa-cogs"></i></span>
                                    <span class="label"><?php echo $text_edit; ?></span>
                                    </a>
                                    <?php }?>
                                    <?php foreach ($app['action'] as $action) { ?>
                                    <a href="<?php echo $action['href']; ?>">
                                        <span class="value"><i class="<?php echo $action['icon']; ?>"></i></span>
                                        <span class="label"><?php echo $action['text']; ?></span>
                                    </a>
                                    <?php } ?>
                                </div>
                                <?php } ?>

                                <div class="project-box-ultrafooter clearfix">
                                    <?php if($app['purchased']) {?>
                                    <span class="label label-success pull-left"><?php echo $text_purchased;?></span>
                                    <?php }?>

                                    <?php if($app['installed']) {?>
                                    <span class="label label-info pull-left"><?php echo $text_installed;?></span>
                                    <?php }?>

                                    <?php if($app['isnew']) {?>
                                    <span class="label label-danger pull-left"><?php echo $text_new;?></span>
                                    <?php }?>
                                    <a href="<?php echo $app['applink']; ?>" class="link pull-right">
                                        <?php echo $text_moredetails;?>
                                        <?php if ($direction == 'rtl') { ?>
                                        <i class="fa fa-arrow-circle-left fa-lg"></i>
                                        <?php } else { ?>
                                        <i class="fa fa-arrow-circle-right fa-lg"></i>
                                        <?php } ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <?php if ($AvaliableServices) { ?>
            <div class="col-lg-12">
                <div class="main-box appservice-container clearfix">
                    <br/>
                    <?php foreach ($AvaliableServices as $service) { ?>
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <div class="main-box clearfix project-box">
                            <div class="main-box-body clearfix">
                                <div class="project-box-header green-bg">
                                    <div class="name" title="<?php echo $service['name'];?>">
                                        <a href="<?php echo $service['servicelink']; ?>">
                                            <?php echo $service['name']; ?>
                                        </a>

                                    </div>
                                </div>

                                <div class="project-box-content">
                                    <div class="profile-box-header clearfix">
                                        <a href="<?php echo $service['servicelink']; ?>"><img src="<?php echo $service['image']; ?>" alt="" class="profile-img img-responsive center-block" /></a>
                                        <h2><?php echo $service['desc']; ?></h2>
                                    </div>
                                </div>

                                <div class="buy-now-area clearfix">
                                    <?php if (!$service['isbundle']) { ?>
                                        <h2>
                                            <?php if ($service['price'] != '$0') { ?>
                                            <span class="sky-highlight"><?php echo $service['price']; ?></span>
                                            <?php
                                                if ($service['recurring'] == '0') {
                                                    echo $text_rec_once;
                                                }

                                                if ($service['isquantity']) {
                                                    echo $text_item;
                                                }
                                            ?>
                                            <?php } ?>
                                        </h2>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <?php if($service['isquantity']) {?>
                                            <div class="count" style="margin: 0px 15px;text-align: center;padding-bottom: 5px;padding-top: 5px;">

                                            <i class="fa fa-plus btn-plus"></i>
                                            <input type="text" class="quantity-input" style="width: 2em;text-align: center;"/>
                                            <i class="fa fa-minus btn-minus"></i>
                                            </div>
                                            <?php }?>

                                            <?php if ($isTrial == "1") { ?>
                                                <a class="btn btn-primary md-trigger center-block buy-button" data-modal="modal-pick-plan"><?php echo $text_buy; ?></a>
                                            <?php } else { ?>
                                                <?php if (($service['price'] == '$0' && PRODUCTID >= $service['freeplan']) || $service['price'] != '$0') { ?>
                                                    <a href="<?php echo $service['buylink']; ?>" class="btn btn-primary center-block buy-button qty-buy"><?php echo $text_buy;?></a>
                                                <?php } ?>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <?php } ?>
                                    <h2>
                                        <?php
                                            if ($service['freeplan'] == '2') {
                                                echo $text_free_all;
                                            } else if ($service['freeplan'] == '4') {
                                                echo $text_free_business;
                                            } else if ($service['freeplan'] == '6') {
                                                echo $text_free_ultimate;
                                            } else if ($service['freeplan'] == '8') {
                                                echo $text_free_enterprise;
                                            }
                                        ?>
                                        <?php
                                            if ($service['freepaymentterm'] == 'y') {
                                                echo "<br>".$text_yearly_only;
                                            }
                                        ?>
                                    </h2>
                                </div>

                                <div class="project-box-ultrafooter clearfix">
                                    <?php if($service['isnew']) {?>
                                    <span class="label label-danger pull-left"><?php echo $text_new;?></span>
                                    <?php }?>

                                    <a href="<?php echo $service['servicelink']; ?>" class="link pull-right">
                                        <?php echo $text_moredetails;?>
                                        <?php if ($direction == 'rtl') { ?>
                                        <i class="fa fa-arrow-circle-left fa-lg"></i>
                                        <?php } else { ?>
                                        <i class="fa fa-arrow-circle-right fa-lg"></i>
                                        <?php } ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

<div class="md-overlay"></div><!-- the overlay element -->

<script>
    function goToPackages() {
        window.location = "<?php echo html_entity_decode($packageslink); ?>";
        return false;
    }
</script>

<?php echo $footer; ?>