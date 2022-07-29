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
  <div class="box">
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><span class="required">*</span> <?php echo $entry_store; ?></td>
            <td><input type="text" name="innovatepayments_store" value="<?php echo $innovatepayments_store; ?>" />
              <?php if ($error_store) { ?>
              <span class="error"><?php echo $error_store; ?></span>
              <?php } ?></td>
              <td><?php if ($help_store) { ?><span class="help"><?php echo $help_secret; ?></span><?php } ?></td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_secret; ?></td>
            <td><input type="text" name="innovatepayments_secret" value="<?php echo $innovatepayments_secret; ?>" />
              <?php if ($error_secret) { ?>
              <span class="error"><?php echo $error_secret; ?></span>
              <?php } ?></td>
              <td><?php if ($help_secret) { ?><span class="help"><?php echo $help_secret; ?></span><?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_cartdesc; ?></td>
            <td><input type="text" name="innovatepayments_cartdesc" value="<?php echo $innovatepayments_cartdesc; ?>" />
            <td><?php if ($help_cartdesc) { ?><span class="help"><?php echo $help_cartdesc; ?></span><?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_test; ?></td>
            <td><select name="innovatepayments_test">
                <option value="test"><?php echo $text_test; ?></option>
                <?php if ($innovatepayments_test == 'live') { ?>
                <option value="live" selected="selected"><?php echo $text_live; ?></option>
                <?php } else { ?>
                <option value="live"><?php echo $text_live; ?></option>
                <?php } ?>
              </select></td>
              <td><?php if ($help_test) { ?><span class="help"><?php echo $help_test; ?></span><?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_total; ?></td>
            <td><input type="text" name="innovatepayments_total" value="<?php echo $innovatepayments_total; ?>" /></td>
            <td><?php if ($help_total) { ?><span class="help"><?php echo $help_total; ?></span><?php } ?></td>
          </tr>          
          <tr>
            <td><?php echo $entry_order_status; ?></td>
            <td><select name="innovatepayments_order_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $innovatepayments_order_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
              <td><?php if ($help_order_status) { ?><span class="help"><?php echo $help_order_status; ?></span><?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_geo_zone; ?></td>
            <td><select name="innovatepayments_geo_zone_id">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                <?php if ($geo_zone['geo_zone_id'] == $innovatepayments_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
              <td><?php if ($help_geo_zone) { ?><span class="help"><?php echo $help_geo_zone; ?></span><?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_status; ?></td>
            <td><select name="innovatepayments_status">
                <?php if ($innovatepayments_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select></td>
              <td><?php if ($help_status) { ?><span class="help"><?php echo $help_status; ?></span><?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_sort_order; ?></td>
            <td><input type="text" name="innovatepayments_sort_order" value="<?php echo $innovatepayments_sort_order; ?>" size="1" /></td>
              <td><?php if ($help_sort_order) { ?><span class="help"><?php echo $help_sort_order; ?></span><?php } ?></td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?> 
