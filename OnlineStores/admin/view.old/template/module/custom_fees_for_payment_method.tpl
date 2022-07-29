<?php echo $header; ?>

<!-- New font awesome -->
<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

<style>
    label
    {
        padding: 5px;
    }

    label:hover
    {
        cursor: pointer;
    }
</style>

<div id="content">
    <ol class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb): ?>
            <?php if ($breadcrumb === end($breadcrumbs)): ?>
                <li class="active">
            <?php else: ?>
                <li>
            <?php endif; ?>

                <a href="<?php echo $breadcrumb['href']; ?>">
                    <?php if ($breadcrumb === reset($breadcrumbs)): ?>
                        <?= $breadcrumb['text']; ?>
                    <?php else: ?>
                        <span><?= $breadcrumb['text']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ol>
    
    <?php if ($error_warning): ?>
        <script>
            var notificationString = '<?= $error_warning; ?>';
            var notificationType = 'warning';
        </script>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <script>
            var notificationString = '<?= $success_message; ?>';
            var notificationType = 'success';
        </script>
    <?php endif; ?>

    <div class="box">
        <div class="heading">
            <h1><?= $heading_title; ?></h1>
            <div class="buttons">
                <a onclick="$('#form').submit();" class="button btn btn-primary"><?= $button_save; ?></a>
                <a href="<?= $cancel; ?>" class="button btn btn-danger"><?= $button_cancel; ?></a>
            </div>
        </div>

    <div class="content">

        <div class="htabs" id="tabs_htabs">
          <a href="#fees"><span class="fa fa-money"></span> <?php echo $tab_all_payment_methods; ?></a>
          <a href="#show_range"><span class="fa fa-eye"></span> <span class="fa fa-exchange"></span> <?php echo $tab_show_range; ?></a>
          <a href="#fees_range"><span class="fa fa-money"></span> <span class="fa fa-exchange"></span> <?php echo $tab_fees_range; ?></a>
          <a href="#settings_general"><span class="fa fa-cog"></span> <?php echo $tab_settings; ?></a>
        </div>

        <form action="<?= $action ?>" method="post" enctype="multipart/form-data" id="form">
    
            <!-- Fees -->
            <div id="fees" class="htab-content">
                <div class="tab-body table-form">

                    <br>
                    
                    <?php foreach ($payment_methods as $code => $value): ?>

                        <div class="row">

                            <div class="col-md-3 th">
                                <div class="wrap-5"><label for="<?= $code; ?>"><?= $value['title'] ?></label></div>
                            </div>

                            <div class="col-md-9">
                                <div class="wrap-5">
                                    
                                    <div class="input-group">
                                        <?php if ( isset($value['fees_pcg']) && $value['fees_pcg'] === 'yes' ): ?>
                                            <span class="input-group-addon" id="<?= $code; ?>"><i class="fa fa-percent"></i></span>
                                        <?php else: ?>
                                            <span class="input-group-addon" id="<?= $code; ?>"><i class="fa fa-money"></i></span>
                                        <?php endif ?>

                                        <input class="form-control" type="text" title="<?= $value['title']; ?>" placeholder="0" value="<?= $value['fees']; ?>" id="<?= $code; ?>" name="<?= $code; ?>_fees">
                                    </div>

                                    <!-- % of subtotal -->
                                    <input type="checkbox" class="pcg_of_subtotal_checkbox" href="<?= $code; ?>" name="<?= $code ?>_pcg_of_subtotal" id="pcg_of_subtotal_<?= $code ?>" style="vertical-align: middle;" <?php if ( isset($value['fees_pcg']) && $value['fees_pcg'] === 'yes' ) { echo "checked"; } ?>>
                                    <label for="pcg_of_subtotal_<?= $code ?>"> <?= $pcg_of_subtotal ?> </label>
                                    <!-- /% of subtotal -->

                                    <?php if ( $errors[$code.'_fees'] ): ?>
                                        <p class="text-danger"><?= $errors[$code.'_fees']; ?></p>
                                    <?php endif ?>

                                </div>
                            </div>
                        </div>
                        <br>

                    <?php endforeach; ?>

                </div>
            </div>
            <!-- /Fees -->

            <!-- show range -->
            <div id="show_range" class="htab-content">
                <div class="tab-body table-form">
                    <br>

                    <?php foreach ($payment_methods as $code => $value): ?>

                        <div class="row">

                            <div class="col-md-3 th">
                                <div class="wrap-5"><label for="<?= $code; ?>_show_range_min"><?= $value['title'] ?></label></div>
                            </div>

                            <div class="col-md-9">
                                <div class="wrap-5">

                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-money"></i></span>

                                        <input type="text" class="form-control" title="<?= $title_minimum ?>" placeholder="0" value="<?= $value['show_range_min']; ?>" id="<?= $code; ?>_show_range_min" name="<?= $code; ?>_show_range_min"> 
                                    </div>
                                    
                                    <?php if ( $errors[$code.'_show_range_min'] ): ?>
                                        <span class="text-danger"><?= $errors[$code.'_show_range_min']; ?></span>
                                    <?php endif ?>

                                    <br>

                                    <?php if ($value['show_range_max'] !== 'no_max'): ?>

                                        <div class="input-group" id="<?= $code; ?>_show_range_max">
                                            <span class="input-group-addon"><i class="fa fa-money"></i></span>

                                            <input type="text" class="form-control" title="<?= $title_maximum ?>" placeholder="0" value="<?= $value['show_range_max']; ?>" name="<?= $code; ?>_show_range_max">
                                        </div>

                                        <input type="checkbox" class="no-max-checkbox" id="<?= $code; ?>_no_max" name="<?= $code; ?>_show_range_no_max" href="<?= $code; ?>_show"> 

                                        <label for="<?= $code; ?>_no_max"><?= $no_max; ?></label>
                                    
                                    <?php else: ?>

                                        <div class="input-group" id="<?= $code; ?>_show_range_max" style="display: none;">
                                            <span class="input-group-addon"><i class="fa fa-money"></i></span>

                                            <input type="text" class="form-control" title="<?= $title_maximum ?>" placeholder="0" value="0" name="<?= $code; ?>_show_range_max">
                                        </div>

                                        <input type="checkbox" class="no-max-checkbox" id="<?= $code; ?>_no_max" name="<?= $code; ?>_show_range_no_max" href="<?= $code; ?>_show" checked="checked"> 

                                        <label for="<?= $code; ?>_no_max"><?= $no_max; ?></label>

                                    <?php endif; ?>
                                    
                                    <?php if ( $errors[$code.'_show_range_max'] ): ?>
                                        <p class="text-danger"><?= $errors[$code.'_show_range_max']; ?></p>
                                    <?php endif ?>

                                </div>
                            </div>
                        </div>
                        <br>
                        <hr>
                    <?php endforeach; ?>

                </div>
            </div>
            <!-- /show range -->

            <!-- fees range -->
            <div id="fees_range" class="htab-content">
                <div class="tab-body table-form">
                    <br>

                    <?php foreach ($payment_methods as $code => $value): ?>

                        <div class="row">

                            <div class="col-md-3 th">
                                <div class="wrap-5"><label for="<?= $code; ?>_fees_range_min"><?= $value['title'] ?></label></div>
                            </div>

                            <div class="col-md-9">
                                <div class="wrap-5">
                                    
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-money"></i></span>

                                        <input type="text" class="form-control" title="<?= $title_minimum ?>" placeholder="0" value="<?= $value['fees_range_min']; ?>" id="<?= $code; ?>_fees_range_min" name="<?= $code; ?>_feez_range_min">
                                    </div>
                                    
                                    <?php if ( $errors[$code.'_fees_range_min'] ): ?>
                                        <span class="text-danger"><?= $errors[$code.'_fees_range_min']; ?></span>
                                    <?php endif ?>
                                    
                                    <br>

                                    <?php if ($value['fees_range_max'] !== 'no_max'): ?>
                                        
                                        <div class="input-group" id="<?= $code; ?>_fees_range_max">
                                            <span class="input-group-addon"><i class="fa fa-money"></i></span>

                                            <input type="text" class="form-control" title="<?= $title_maximum ?>" placeholder="0" value="<?= $value['fees_range_max']; ?>" name="<?= $code; ?>_feez_range_max">
                                        </div>

                                        <input type="checkbox" class="no-max-checkbox" id="<?= $code; ?>_fees_range_no_max" name="<?= $code; ?>_feez_range_no_max" href="<?= $code; ?>_fees"> 

                                        <label for="<?= $code; ?>_fees_range_no_max"><?= $no_max; ?></label>
                                    
                                    <?php else: ?>

                                        <div class="input-group" id="<?= $code; ?>_fees_range_max" style="display:none;">
                                            <span class="input-group-addon"><i class="fa fa-money"></i></span>

                                            <input type="text" class="form-control" title="<?= $title_maximum ?>" placeholder="0" value="0" name="<?= $code; ?>_feez_range_max">
                                        </div>

                                        <input type="checkbox" class="no-max-checkbox" id="<?= $code; ?>_fees_range_no_max" name="<?= $code; ?>_feez_range_no_max" href="<?= $code; ?>_fees" checked="checked"> 

                                        <label for="<?= $code; ?>_fees_range_no_max"><?= $no_max; ?></label>

                                    <?php endif; ?>
                                    
                                    <?php if ( $errors[$code.'_fees_range_max'] ): ?>
                                        <p class="text-danger"><?= $errors[$code.'_fees_range_max']; ?></p>
                                    <?php endif ?>

                                </div>
                            </div>
                        </div>
                        <br>
                        <hr>
                    <?php endforeach; ?>

                </div>
            </div>
            <!-- /fees range -->

            <!-- Settings -->
            <div id="settings_general" class="htab-content">
                <div class="tab-body table-form">

                    <br>

                    <div class="row">
                        <div class="col-md-3 th">
                            <div class="wrap-5"><label for="setting_total_row_name"><?= $entry_total_row_name; ?></label></div>
                        </div>

                        <div class="col-md-9">
                            <div class="wrap-5">
                                <?php foreach ($all_languages as $lang): ?>
                                    
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <img src="view/image/flags/<?= $lang['image'] ?>" alt="">
                                        </span>

                                        <input type="text" class="form-control" placeholder="<?= $entry_total_row_name; ?>" id="setting_total_row_name" name="setting_total_row_name_<?= $lang['code'] ?>" title="<?= $lang['name'] ?>" value="<?= $total_row_names[$lang['code']] ?>">
                                    </div>
                                    <br>

                                <?php endforeach ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /Settings -->

        </form>
    </div>
</div>

<!-- htabs -->
<script>
  $('#tabs_htabs a').tabs();

  $('input.no-max-checkbox').on('click', function() {
    var code = $(this).attr('href');
    $('[id=' + code + '_range_max]').toggle();
  });

    $('.pcg_of_subtotal_checkbox').on('click', function() {
        var code = $(this).attr('href');
        
        if ( $(this).is(':checked') ) {
            $('span#'+code).html("<i class='fa fa-percent'></i>");
        } else {
            $('span#'+code).html("<i class='fa fa-money'></i>");
        }

    });

</script>
<!-- /htabs -->
<?php echo $footer; ?>