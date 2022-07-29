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
                        <h4 class="box-bold-heading"><?php echo $text_workflow_fields; ?></h4>
                        <div>
                            <a href="<?php echo $stock_status; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-sitemap"></i>
                                        <br>
                                        <?php echo $text_stock_status; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $order_status; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-sitemap"></i>
                                        <br>
                                        <?php echo $text_order_status; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $return_status; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-sitemap"></i>
                                        <br>
                                        <?php echo $text_return_status; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $return_action; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-sitemap"></i>
                                        <br>
                                        <?php echo $text_return_action; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $return_reason; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-sitemap"></i>
                                        <br>
                                        <?php echo $text_return_reason; ?>
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
                        <h4 class="box-bold-heading"><?php echo $text_geodata; ?></h4>
                        <div>
                            <a href="<?php echo $countries; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-map-marker"></i>
                                        <br>
                                        <?php echo $text_countries; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $cities; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-map-marker"></i>
                                        <br>
                                        <?php echo $text_cities; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $geo_zones; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-map-marker"></i>
                                        <br>
                                        <?php echo $text_geo_zones; ?>
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
                        <h4 class="box-bold-heading"><?php echo $text_productmetrics; ?></h4>
                        <div>
                            <a href="<?php echo $lengthclass; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-sliders"></i>
                                        <br>
                                        <?php echo $text_lengthclass; ?>
                                    </center>
                                </div>
                            </a>
                            <a href="<?php echo $weightclass; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-sliders"></i>
                                        <br>
                                        <?php echo $text_weightclass; ?>
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
                        <h4 class="box-bold-heading"><?php echo $text_productfeedstitle; ?></h4>
                        <div>
                            <a href="<?php echo $productfeeds; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-rss"></i>
                                        <br>
                                        <?php echo $text_productfeeds; ?>
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