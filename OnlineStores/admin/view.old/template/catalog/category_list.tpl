<?php include_lib($header, $footer,'datatables'); ?>
<?php include_jsfile($header, $footer, 'view/template/catalog/category_list.tpl.js'); ?>

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



  <div class="row">
    <div class="col-lg-12">

      <!--breadcrumb Start-->
      <div class="row">
        <div class="col-lg-12">
          <ol class="breadcrumb">
            <?php for($i=0; $i<count($breadcrumbs)-1; $i++) { ?>
            <li><a href="<?php echo $breadcrumbs[$i]['href']; ?>"><?php echo $breadcrumbs[$i]['text']; ?></a></li>
            <?php } ?>
            <li class="active"><span><?php echo $breadcrumbs[count($breadcrumbs)-1]['text']; ?></span></li>
          </ol>

          <h1><?php echo $heading_title; ?></h1>
        </div>
      </div>
      <!--breadcrumb End-->

      <div class="row">
        <div class="col-lg-12">
          <div class="main-box clearfix">
            <header class="main-box-header clearfix">
              <h2>
                  <button type="button" class="btn btn-primary btn-lg" onclick="location.href='<?php echo $insert; ?>'" >
                      <span class="fa fa-folder-open-o"></span> <?php echo $button_insert; ?>
                  </button>

                  <button type="button" class="btn btn-danger btn-lg" onclick="$('#form').submit();" >
                      <span class="fa fa-times"></span> <?php echo $button_delete; ?>
                  </button>
              </h2>
            </header>
            <div class="main-box-body clearfix">
              <div class="table-responsive col-lg-12">
                <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
                    <table id="table-categories" class="table table-hover">
                      <thead>
                        <tr>
                          <th>
                            <div class="checkbox-nice">
                              <input type="checkbox" id="chkAllItems" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" />
                              <label for="chkAllItems"></label>
                            </div>
                          </th>
                          <th><?php echo $column_name; ?></th>
                          <th><?php echo $column_sort_order; ?></th>
                          <th><?php echo $column_action; ?></th>
                          <!--<th>Start date</th>
                          <th>Salary</th>-->
                        </tr>
                      </thead>
                    </table>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
<script>
  var data= <?php echo json_encode($categories); ?>;
</script>
<?php echo $footer; ?>
