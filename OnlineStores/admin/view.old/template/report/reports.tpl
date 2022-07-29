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
                        <h4 class="box-bold-heading"><?php echo $text_sales; ?></h4>
                        <div>
                            <a href="<?php echo $report_sale_order; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-dollar"></i>
                                        <br>
                                        <?php echo $text_report_sale_order; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $report_sale_tax; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-dollar"></i>
                                        <br>
                                        <?php echo $text_report_sale_tax; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $report_sale_shipping; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-dollar"></i>
                                        <br>
                                        <?php echo $text_report_sale_shipping; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $report_sale_return; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-dollar"></i>
                                        <br>
                                        <?php echo $text_report_sale_return; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $report_sale_coupon; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-dollar"></i>
                                        <br>
                                        <?php echo $text_report_sale_coupon; ?>
                                    </center>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="main-box show-grid clearfix">
                    <div class="clearfix">
                        <h4 class="box-bold-heading"><?php echo $text_customers; ?></h4>
                        <div>
                            <a href="<?php echo $report_customer_online; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-users"></i>
                                        <br>
                                        <?php echo $text_report_customer_online; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $report_customer_order; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-users"></i>
                                        <br>
                                        <?php echo $text_report_customer_order; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $report_customer_reward; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-users"></i>
                                        <br>
                                        <?php echo $text_report_customer_reward; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $report_customer_credit; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-users"></i>
                                        <br>
                                        <?php echo $text_report_customer_credit; ?>
                                    </center>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="main-box show-grid clearfix">
                    <div class="clearfix">
                        <h4 class="box-bold-heading"><?php echo $text_products; ?></h4>
                        <div>
                            <a href="<?php echo $report_product_viewed; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-cubes"></i>
                                        <br>
                                        <?php echo $text_report_product_viewed; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $report_product_purchased; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-cubes"></i>
                                        <br>
                                        <?php echo $text_report_product_purchased; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $report_product_top_ten_purchased; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-cubes"></i>
                                        <br>
                                        <?php echo $text_report_product_top_ten_purchased; ?>
                                    </center>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="main-box show-grid clearfix">
                    <div class="clearfix">
                        <h4 class="box-bold-heading"><?php echo $text_affiliates; ?></h4>
                        <div>
                            <a href="<?php echo $report_affiliate_commission; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-share-alt"></i>
                                        <br>
                                        <?php echo $text_report_affiliate_commission; ?>
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