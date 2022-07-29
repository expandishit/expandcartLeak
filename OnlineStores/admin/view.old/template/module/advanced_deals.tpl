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
                            <a href="<?php echo $url_ecdeals; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-tags"></i>
                                        <br>
                                        <?php echo $heading_title_ecdeals; ?>
                                    </center>
                                </div>
                            </a>

                            <a href="<?php echo $url_ecflashsale; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-bolt"></i>
                                        <br>
                                        <?php echo $heading_title_ecflashsale; ?>
                                    </center>
                                </div>
                            </a>

                            <a href="<?php echo $url_ecdeals_settings; ?>" class="report-link">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-gears"></i>
                                        <br>
                                        <?php echo $heading_title_ecdeals_settings; ?>
                                    </center>
                                </div>
                            </a>


                            <a href="<?php echo $url_ecflashsale_settings; ?>" class="report-link" style="display:none;">
                                <div class="col-md-3 col-sm-4 reportbox report">
                                    <center>
                                        <i class="fa fa-bolt"></i>
                                        <br>
                                        <?php echo $heading_title_ecflashsale_settings; ?>
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