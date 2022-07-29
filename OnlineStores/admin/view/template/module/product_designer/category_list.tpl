<?php echo $header; ?>
<div id="content">
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
  <div class="box">
    <div class="heading">
      <h1>{{ lang('pd_category_and_cliparts') }}</h1>
      <div class="buttons">
        <a href="<?php echo $insert; ?>" class="button btn btn-primary">{{ lang('button_insert') }}</a>
        <a href="<?php echo $pd_settings; ?>" class="button btn btn-primary">{{ lang('text_pd_settings') }}</a>
        <!-- <a onclick="$('#form').attr('action', '<?php echo $delete; ?>'); $('#form').attr('target', '_self'); $('#form').submit();" class="button btn btn-danger">{{ lang('button_delete') }}</a> -->
      </div>
    </div>
    <div class="content">
      <form action="" method="post" enctype="multipart/form-data" id="form">
        <table class="table table-hover dataTable no-footer">
          <thead>
              <tr>
                <td>#</a></td>
                <td>{{ lang('pd_category_name') }}</a></td>
                <td>{{ lang('pd_category_image') }}</a></td>
                <td>{{ lang('status') }}</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
          </thead>
          <tbody>
            <?php if ($categorylist) { ?>
            <?php foreach ($categorylist as $clist) { ?>
            <tr>
              <td class="right"><?php echo $clist['caid']; ?></td>
              <td class="left"><?php echo $clist['category_name']; ?></td>
              <td class="left"><img src="<?php echo $clist['category_image']; ?>" /></td>
              <td><?= ($clist['status'] == '0' ? $text_inactive : $text_active); ?></td>
              <td><a href="<?= $clist['href3']; ?>" class="btn btn-primary" title="" data-toggle="tooltip" data-original-title="{{ lang('pd_view_image') }}"><i class="fa fa-eye"></i></a></td>
              <td><a href="<?= $clist['href1']; ?>" class="btn btn-primary" title="" data-toggle="tooltip" data-original-title="{{ lang('pd_edit') }}"><i class="fa fa-pencil"></i></a></td>
              <td><button onclick="deleteCategory('<?= $clist['href2']; ?>')" class="btn btn-danger" title="" data-toggle="tooltip" type="button" data-original-title="{{ lang('pd_delete') }}"><i class="fa fa-trash-o"></i></button></td>
            </tr>
            <?php } ?>
            <?php } else { ?>
            <tr>
              <td class="center" colspan="8"><?php echo $text_no_results; ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </form>
      <div class="pagination"><?php echo $pagination; ?></div>
    </div>
  </div>
</div>
<div class="md-overlay"></div><!-- the overlay element -->

<script type="text/javascript">
    function deleteCategory(param)
    {
        var iAnswer = confirm("{{ lang('pd_delete_confirmation_message') }}");
        if(iAnswer){
            //true here
            window.location.href = param;
        }
        else{
            return false;
        }
    }
</script>

<?php echo $footer; ?>
