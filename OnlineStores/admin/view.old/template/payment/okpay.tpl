<?php echo $header; ?>


<div class="content">
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

    <div class="box">
        <div class="heading">
            <h1><?php echo $heading_title; ?></h1>
            <div class="buttons">
                <a onclick="$('#form').submit();" class="button btn btn-primary"><span><?php echo $button_save; ?></span></a>
                <a href="<?php echo $cancel; ?>" class="button btn btn-primary"><span><?php echo $button_cancel; ?></span></a>
            </div>
        </div>

        <div class="content">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
              <table class="form">
                <tr>
                  <td><span class="required">*</span> <?php echo $entry_receiver; ?></td>
                  <td><input type="text" name="okpay_receiver" value="<?php echo $okpay_receiver; ?>" />
                    <?php if ($error_receiver) { ?>
                    <span class="error"><?php echo $error_receiver; ?></span>
                    <?php } ?></td>
                </tr>
                <tr>
                  <td><?php echo $entry_order_status; ?></td>
                  <td><select name="okpay_order_status_id">
                      <?php foreach ($order_statuses as $order_status) { ?>
                      <?php if ($order_status['order_status_id'] == $okpay_order_status_id) { ?>
                      <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                      <?php } else { ?>
                      <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                      <?php } ?>
                      <?php } ?>
                    </select></td>
                </tr>
                <tr>
                  <td><?php echo $entry_geo_zone; ?></td>
                  <td><select name="okpay_geo_zone_id">
                      <option value="0"><?php echo $text_all_zones; ?></option>
                      <?php foreach ($geo_zones as $geo_zone) { ?>
                      <?php if ($geo_zone['geo_zone_id'] == $okpay_geo_zone_id) { ?>
                      <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                      <?php } else { ?>
                      <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                      <?php } ?>
                      <?php } ?>
                    </select></td>
                </tr>
                <tr>
                  <td><?php echo $entry_status; ?></td>
                  <td><select name="okpay_status">
                      <?php if ($okpay_status === "1") { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                      <?php } elseif ($pp_standard_status === "0") { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                      <?php } else { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                      <?php } ?>
                    </select></td>
                </tr>
                <tr>
                  <td><?php echo $entry_sort_order; ?></td>
                  <td><input type="text" name="okpay_sort_order" value="<?php echo $okpay_sort_order; ?>" size="1" /></td>
                </tr>
              </table>
            </form>
        </div>
    </div>
</div>

<?php echo $footer; ?>