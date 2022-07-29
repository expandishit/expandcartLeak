<?php echo $header; ?>

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

<div class = "row">
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
                <div class="main-box clearfix">
                    <div class="tabs-wrapper profile-tabs">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab-banners" data-toggle="tab"><?php echo $text_banners; ?></a></li>
                            <li><a href="<?php echo $bannerpos; ?>"><?php echo $text_bannerpos; ?></a></li>
                        </ul>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="tab-banners">
                            <div class="heading">
                              <div class="buttons"><a href="<?php echo $insert; ?>" class="button btn btn-primary"><?php echo $button_insert; ?></a><a onclick="$('form').submit();" class="button btn btn-primary"><?php echo $button_delete; ?></a></div>
                            </div>
                            <div class="content">
                              <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
                                <table class="table table-hover dataTable no-footer">
                                  <thead>
                                    <tr>
                                      <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>
                                      <td class="left"><?php if ($sort == 'name') { ?>
                                        <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_name; ?></a>
                                        <?php } else { ?>
                                        <a href="<?php echo $sort_name; ?>"><?php echo $column_name; ?></a>
                                        <?php } ?></td>
                                      <td class="left"><?php if ($sort == 'status') { ?>
                                        <a href="<?php echo $sort_status; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_status; ?></a>
                                        <?php } else { ?>
                                        <a href="<?php echo $sort_status; ?>"><?php echo $column_status; ?></a>
                                        <?php } ?></td>
                                      <td class="right"><?php echo $column_action; ?></td>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php if ($banners) { ?>
                                    <?php foreach ($banners as $banner) { ?>
                                    <tr>
                                      <td style="text-align: center;"><?php if ($banner['selected']) { ?>
                                        <input type="checkbox" name="selected[]" value="<?php echo $banner['banner_id']; ?>" checked="checked" />
                                        <?php } else { ?>
                                        <input type="checkbox" name="selected[]" value="<?php echo $banner['banner_id']; ?>" />
                                        <?php } ?></td>
                                      <td class="left"><?php echo $banner['name']; ?></td>
                                      <td class="left"><?php echo $banner['status']; ?></td>
                                      <td class="right"><?php foreach ($banner['action'] as $action) { ?>
                                        [ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]
                                        <?php } ?></td>
                                    </tr>
                                    <?php } ?>
                                    <?php } else { ?>
                                    <tr>
                                      <td class="center" colspan="4"><?php echo $text_no_results; ?></td>
                                    </tr>
                                    <?php } ?>
                                  </tbody>
                                </table>
                              </form>
                              <div class="pagination"><?php echo $pagination; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>