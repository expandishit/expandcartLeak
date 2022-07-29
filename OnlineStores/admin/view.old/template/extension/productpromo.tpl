<?php echo $header; ?>

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
                            <?php foreach ($apps as $app) { ?>
                                <a href="<?php echo $app['URL']; ?>" class="report-link">
                                    <div class="col-md-4 col-sm-4 reportbox report has-desc">
                                        <center>
                                            <i class="fa <?php echo $app['Icon'] ?>"></i>
                                            <br>
                                            <?php echo $app['Name'] ?>
                                            <br>
                                            <h5>
                                                <?php echo $app['Description'] ?>
                                            </h5>
                                        </center>
                                    </div>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>