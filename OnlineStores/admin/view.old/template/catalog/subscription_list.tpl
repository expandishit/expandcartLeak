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
      <h1><?php echo $heading_title; ?></h1>
	   <div class="buttons"><a onclick="$('form').submit();" class="button btn btn-primary"><?php echo $button_delete; ?></a></div>
     
    </div>
    <div class="content">
      <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="table table-hover dataTable no-footer">
          <thead>
            <tr>
              <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>
              <td class="left"><?php echo $column_name; ?></td>
              <td class="right">Email</td>
              <td class="right">Date Added</td>
			  <td class="right">Status</td>
            </tr>
          </thead>
          <tbody>
            <?php if ($subscriptions) { ?>
            <?php foreach ($subscriptions as $subscription) { ?>
            <tr>
              <td style="text-align: center;"><?php if ($subscription['selected']) { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $subscription['Id']; ?>" checked="checked" />
                <?php } else { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $subscription['Id']; ?>" />
                <?php } ?></td>
              <td class="left"><?php echo $subscription['name']; ?></td>
              <td class="right"><?php echo $subscription['Email']; ?></td>
	       <td class="right"><?php echo $subscription['date_added']; ?></td>
		   <td class="right"><a href="<?php echo $subscription['href'];?>"><?php echo $subscription['status']; ?></a></td>
	
             
            </tr>
            <?php } ?>
            <?php } else { ?>
            <tr>
              <td class="center" colspan="5"><?php echo $text_no_results; ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?>