<?php echo $header; ?>
<?php include_jsfile($header, $footer, 'view/template/marketplace/app.tpl.js'); ?>

<style>
    .desc {
        font-size: 1.2em;
        line-height: 1.7em;
    }
</style>

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

        <div class="row" id="appservice">
            <div class="col-lg-12">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="main-box clearfix profile-box-contact">
                        <div class="main-box-body clearfix psmethod-container app-service">
                            <div class="widget-psmethod">
                                <div class="row method">
                                    <div class="product col-lg-9 col-sm-9 col-xs-9">
                                        <div class="img">
                                            <img src="<?php echo $image; ?>" alt="<?php echo $name;?>">
                                        </div>

                                        <div class="badges">
                                            <?php if($ispurchased) {?>
                                            <span class="label label-success badge" style="margin: 2px;"><?php echo $text_purchased;?></span>
                                            <?php }?>

                                            <?php if($isinstalled) {?>
                                            <span class="label label-info badge" style="margin: 2px;"><?php echo $text_installed;?></span>
                                            <?php }?>

                                            <?php if($isnew) {?>
                                            <span class="label label-danger badge" style="margin: 2px;"><?php echo $text_new;?></span>
                                            <?php }?>
                                        </div>

                                        <div class="desc">
                                            <?php echo $desc;?>
                                        </div>
                                    </div>

                                    <div class="actions col-lg-3 col-sm-3 col-xs-3 profile-box-footer">
                                        <?php if(!$isfree && !$ispurchased) {?>
                                        <?php if ($price != '$0') { ?>
                                        <h2 class="price-box">
                                        <span class="sky-highlight"><?php echo $price; ?></span>
                                        <?php
                                        if ($recurring == '0') {
                                            echo $text_rec_once;
                                        }
                                        if ($isquantity) {
                                            echo "<br>" . $text_item;
                                        }
                                        ?>
                                        </h2>

                                        <?php if($isservice) {?>
                                        <?php if($isquantity) {?>
                                        <div class="count pull-right" style="padding-top: 25px;padding-bottom: 15px;text-align: center;width: 100%;">
                                        <i class="fa fa-plus btn-plus"></i>
                                        <input type="text" style="text-align: center;font-size: 1em;width: 2em; height:30px; margin: 6px;"/>

                                        <i class="fa fa-minus btn-minus"></i>
                                        </div>
                                        <?php } else { echo "<br><br>"; }?>
                                        <?php } else { echo "<br><br>"; }?>
                                        <?php } ?>

                                        <?php if ($isTrial == "1") { ?>
                                            <a class="btn btn-primary md-trigger center-block buy-button" data-modal="modal-pick-plan"><?php echo $text_buy; ?></a>
                                        <?php } else { ?>
                                            <?php if (((($price == '$0' && PRODUCTID >= $freeplan) || $price != '$0') && $isservice) || !$isservice) { ?>
                                            <a href="<?php echo $buylink;?>" class="qty-buy btn btn-primary">
                                                <?php echo $text_buy;?>
                                            </a>
                                            <?php } ?>
                                        <?php } ?>

                                        <?php } elseif($isinstalled) {?>
                                        <a class="pull-right" href="<?php echo $extensionlink; ?>">
                                        <span class="value"><i class="fa fa-cogs"></i></span>
                                        <span class="label"><?php echo $text_edit;?></span>
                                        </a>
                                        <?php } ?>

                                        <?php foreach ($actions as $action) {?>
                                        <a class="pull-right" href="<?php echo $action['href']; ?>">
                                        <span class="value"><i class="<?php echo $action['icon']; ?>"></i></span>
                                        <span class="label"><?php echo $action['text']; ?></span>
                                        </a>
                                        <?php }?>

                                        <h2 class="price-box" style="margin-top: 10px;">
                                            <?php
                                                if ($freeplan == '2') {
                                                    echo $text_free_all;
                                                } else if ($freeplan == '4') {
                                                    echo $text_free_business;
                                                } else if ($freeplan == '6') {
                                                    echo $text_free_ultimate;
                                                } else if ($freeplan == '8') {
                                                    echo $text_free_enterprise;
                                                }
                                            ?>
                                            <?php
                                                if ($freepaymentterm == 'y') {
                                                    echo "<br>".$text_yearly_only;
                                                }
                                            ?>
                                        </h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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