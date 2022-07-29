<?= $header; ?>
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
  <?php if ($api_error) { ?>
  <script>
      var notificationString = '<?php echo $api_error; ?>';
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
      <div class="buttons">
        <a href="<?= $push; ?>" class="button btn btn-warning"><?= $button_push; ?></a>
        <a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a>
        <a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
        <form action="<?= $action; ?>" method="post" enctype="multipart/form-data" id="form" class="form-horizontal">
            
            <div class="form-group">
            <label class="col-sm-2 control-label" for="input-fetchr_servicetype"><?= $entry_fetchr_servicetype; ?></label>
            <div class="col-sm-10">
             <?php // echo $fetchr_servicetype; ?>
              <select name="fetchr_servicetype" id="input-fetchr_servicetype" class="form-control">
                <?php if ($fetchr_servicetype) { ?>
                <option value="0"><?= $text_delivery; ?></option>
                <option value="1" selected="selected"><?= $text_fulfildelivery; ?></option>
                <?php } else { ?>
                <option value="0" selected="selected"><?= $text_delivery; ?></option>
                <option value="1"><?= $text_fulfildelivery; ?></option>
                <?php } ?>
              </select>
              
            </div>
          </div>
            <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-fetchr_username"><?= $entry_fetchr_username; ?></label>
            <div class="col-sm-10">
              <input type="text" name="fetchr_username" value="<?= empty($fetchr_username) ? '' : $fetchr_username ; ?>" placeholder="<?= $entry_fetchr_username; ?>" id="input-fetchr_username" class="form-control" />
              <?php if ($error_username) { ?>
              <div class="text-danger"><?= $error_fetchr_username; ?></div>
              <?php } ?>
            </div>
          </div>
          
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-fetchr_password"><?= $entry_fetchr_password; ?></label>
            <div class="col-sm-10">
              <input type="fetchr_password" name="fetchr_password" value="<?= $fetchr_password; ?>" placeholder="<?= $entry_fetchr_password; ?>" id="input-fetchr_password" class="form-control" autocomplete="off" />
              <?php if ($error_password) { ?>
              <div class="text-danger"><?= $error_fetchr_password; ?></div>
              <?php  } ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-fetchr_ready_shipping_status"><?= $entry_fetchr_ready_shipping_status; ?></label>
            <div class="col-sm-10">
              <select name="fetchr_ready_shipping_status" id="input-fetchr_ready_shipping_status" class="form-control">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $fetchr_ready_shipping_status) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-fetchr_being_shipped_status"><?= $entry_fetchr_being_shipped_status; ?></label>
            <div class="col-sm-10">
              <select name="fetchr_being_shipped_status" id="input-fetchr_being_shipped_status" class="form-control">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $fetchr_being_shipped_status) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-fetchr_already_shipped_status">{{ lang('entry_fetchr_already_shipped_status') }}</label>
            <div class="col-sm-10">
              <select name="fetchr_already_shipped_status" id="input-fetchr_already_shipped_status" class="form-control">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $fetchr_already_shipped_status) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <div class="col-sm-2 control-label">
                {{ lang('entry_fetchr_api_callback_text') }}
            </div>
            <div class="col-sm-10">
              <?= $fetchr_callback_url; ?>
            </div>
          </div>

        </form>
    </div>
  </div>
</div>
<?= $footer; ?> 