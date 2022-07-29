<?php echo $header; ?>
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

        <div class="row">
            <div class="col-lg-12">
                <div class="main-box show-grid clearfix">
                    <div class="clearfix">
                        <div>
                            <a href="<?php echo $url_catalog_earning_rules; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-cubes"></i>
                                        <br>
                                        <?php echo $text_catalog_earning_rules; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $url_shopping_cart_earning_rules; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-shopping-cart"></i>
                                        <br>
                                        <?php echo $text_shopping_cart_earning_rules; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $url_behavior_rules; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-users"></i>
                                        <br>
                                        <?php echo $text_behavior_rules; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $url_spending_rules; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-money"></i>
                                        <br>
                                        <?php echo $text_spending_rules; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $url_transaction_history; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-history"></i>
                                        <br>
                                        <?php echo $text_transaction_history; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $url_configuration; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-gears"></i>
                                        <br>
                                        <?php echo $text_configuration; ?>
                                    </center>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>