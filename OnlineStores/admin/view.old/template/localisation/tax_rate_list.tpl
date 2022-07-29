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
                            <li class="active"><a href="#tab-taxrate" data-toggle="tab"><?php echo $text_taxrate; ?></a></li>
                            <li><a href="<?php echo $taxclass; ?>"><?php echo $text_taxclass; ?></a></li>
                        </ul>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="tab-taxrate">
                            <div class="heading">
                              <div class="buttons"><a href="<?php echo $insert; ?>" class="button btn btn-primary"><?php echo $button_insert; ?></a><a onclick="$('form').submit();" class="button btn btn-primary"><?php echo $button_delete; ?></a></div>
                            </div>
                            <div class="content">
                              <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
                                <table class="table table-hover dataTable no-footer">
                                  <thead>
                                    <tr>
                                      <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>
                                      <td class="left"><?php if ($sort == 'tr.name') { ?>
                                        <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_name; ?></a>
                                        <?php } else { ?>
                                        <a href="<?php echo $sort_name; ?>"><?php echo $column_name; ?></a>
                                        <?php } ?></td>
                                      <td class="right"><?php if ($sort == 'tr.rate') { ?>
                                        <a href="<?php echo $sort_rate; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_rate; ?></a>
                                        <?php } else { ?>
                                        <a href="<?php echo $sort_rate; ?>"><?php echo $column_rate; ?></a>
                                        <?php } ?></td>
                                      <td class="left"><?php if ($sort == 'tr.type') { ?>
                                        <a href="<?php echo $sort_type; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_type; ?></a>
                                        <?php } else { ?>
                                        <a href="<?php echo $sort_type; ?>"><?php echo $column_type; ?></a>
                                        <?php } ?></td>
                                      <td class="left"><?php if ($sort == 'gz.name') { ?>
                                        <a href="<?php echo $sort_geo_zone; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_geo_zone; ?></a>
                                        <?php } else { ?>
                                        <a href="<?php echo $sort_geo_zone; ?>"><?php echo $column_geo_zone; ?></a>
                                        <?php } ?></td>
                                      <td class="left"><?php if ($sort == 'tr.date_added') { ?>
                                        <a href="<?php echo $sort_date_added; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_date_added; ?></a>
                                        <?php } else { ?>
                                        <a href="<?php echo $sort_date_added; ?>"><?php echo $column_date_added; ?></a>
                                        <?php } ?></td>
                                      <td class="left"><?php if ($sort == 'tr.date_modified') { ?>
                                        <a href="<?php echo $sort_date_modified; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_date_modified; ?></a>
                                        <?php } else { ?>
                                        <a href="<?php echo $sort_date_modified; ?>"><?php echo $column_date_modified; ?></a>
                                        <?php } ?></td>
                                      <td class="right"><?php echo $column_action; ?></td>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php if ($tax_rates) { ?>
                                    <?php foreach ($tax_rates as $tax_rate) { ?>
                                    <tr>
                                      <td style="text-align: center;"><?php if ($tax_rate['selected']) { ?>
                                        <input type="checkbox" name="selected[]" value="<?php echo $tax_rate['tax_rate_id']; ?>" checked="checked" />
                                        <?php } else { ?>
                                        <input type="checkbox" name="selected[]" value="<?php echo $tax_rate['tax_rate_id']; ?>" />
                                        <?php } ?></td>
                                      <td class="left"><?php echo $tax_rate['name']; ?></td>
                                      <td class="right"><?php echo $tax_rate['rate']; ?></td>
                                      <td class="left"><?php echo $tax_rate['type']; ?></td>
                                      <td class="left"><?php echo $tax_rate['geo_zone']; ?></td>
                                      <td class="left"><?php echo $tax_rate['date_added']; ?></td>
                                      <td class="left"><?php echo $tax_rate['date_modified']; ?></td>
                                      <td class="right"><?php foreach ($tax_rate['action'] as $action) { ?>
                                        [ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]
                                        <?php } ?></td>
                                    </tr>
                                    <?php } ?>
                                    <?php } else { ?>
                                    <tr>
                                      <td class="center" colspan="9"><?php echo $text_no_results; ?></td>
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