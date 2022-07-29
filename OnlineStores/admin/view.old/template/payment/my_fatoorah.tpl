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
              <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
          </div>
         <div class="content">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form" class="form-horizontal">
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-merchant-id"><?php echo $username; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="my_fatoorah_username" value="<?php echo $my_fatoorah_username; ?>" class="form-control" id="my_fatoorah_username" placeholder="<?php echo $username; ?>" />
                </div>
              </div>

              <div class="form-group required">
                <label class="col-sm-2 control-label" for="my_fatoorah_password"><?php echo $password; ?></label>
                <div class="col-sm-10">
                  <input type="password" name="my_fatoorah_password" value="<?php echo $my_fatoorah_password; ?>" class="form-control" id="my_fatoorah_password" placeholder="<?php echo $password; ?>" />
                </div>
              </div>

              <div class="form-group required">
                <label class="col-sm-2 control-label" for="my_fatoorah_password"><?php echo $merchant_code; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="my_fatoorah_merchant_code" value="<?php echo $my_fatoorah_merchant_code; ?>"  class="form-control" id="my_fatoorah_merchant_code" placeholder="<?php echo $merchant_code; ?>" />
                </div>
              </div>

              <div class="form-group required">
                <label class="col-sm-2 control-label" for="my_fatoorah_password"><?php echo $merchant_username; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="my_fatoorah_merchant_username" value="<?php echo $my_fatoorah_merchant_username; ?>" class="form-control" id="my_fatoorah_merchant_username" placeholder="<?php echo $merchant_username; ?>" />
                </div>
              </div>

              <div class="form-group required">
                <label class="col-sm-2 control-label" for="my_fatoorah_password"><?php echo $merchant_password; ?></label>
                <div class="col-sm-10">
                  <input type="password" name="my_fatoorah_merchant_password" value="<?php echo $my_fatoorah_merchant_password; ?>" class="form-control" id="my_fatoorah_merchant_password" placeholder="<?php echo $merchant_password; ?>" />
                </div>
              </div>

              <div class="form-group required">
                <label class="col-sm-2 control-label" for="my_fatoorah_password"><?php echo $return_url; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="my_fatoorah_return_url" value="<?php echo $my_fatoorah_return_url; ?>" class="form-control" id="my_fatoorah_return_url" placeholder="<?php echo $return_url; ?>" />
                </div>
              </div>

              <div class="form-group required">
                <label class="col-sm-2 control-label" for="my_fatoorah_password"><?php echo $merchant_error_url; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="my_fatoorah_merchant_error_url" value="<?php echo $my_fatoorah_merchant_error_url; ?>" class="form-control" id="my_fatoorah_return_url" placeholder="<?php echo $return_url; ?>" />
                </div>
              </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label" for="my_fatoorah_gateway_mode"><?php echo $gateway_mode; ?></label>
                    <div class="col-sm-10">
                        <select name="my_fatoorah_gateway_mode" class="form-control">
                            <?php if ($my_fatoorah_gateway_mode === "1") { ?>
                            <option value="1" selected="selected"><?php echo $text_live_mode; ?></option>
                            <option value="0"><?php echo $text_test_mode; ?></option>
                            <?php } elseif ($my_fatoorah_gateway_mode === "0") { ?>
                            <option value="1"><?php echo $text_live_mode; ?></option>
                            <option value="0" selected="selected"><?php echo $text_test_mode; ?></option>
                            <?php } else { ?>
                            <option value="1" selected="selected"><?php echo $text_live_mode; ?></option>
                            <option value="0"><?php echo $text_test_mode; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

              <div class="form-group">
                <label class="col-sm-2 control-label" for="my_fatoorah_password"><?php echo $entry_total; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="my_fatoorah_total" value="<?php echo $my_fatoorah_total; ?>" class="form-control" id="my_fatoorah_total" placeholder="<?php echo $my_fatoorah_total; ?>" />
                  <p><?php echo $entry_total_help_text; ?></p>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label" for="entry_order_status"><?php echo $entry_order_status; ?></label>
                <div class="col-sm-10">
                  <select name="my_fatoorah_order_status_id" class="form-control">
                    <?php foreach ($order_statuses as $order_status) { ?>
                      <?php if ($order_status['order_status_id'] == $my_fatoorah_order_status_id) { ?>
                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                      <?php } else { ?>
                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                      <?php } ?>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label" for="entry_status"><?php echo $entry_status; ?></label>
                <div class="col-sm-10">
                  <select name="my_fatoorah_status" class="form-control">
                    <?php if ($my_fatoorah_status === "1") { ?>
                      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                      <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } elseif ($my_fatoorah_status === "0") { ?>
                      <option value="1"><?php echo $text_enabled; ?></option>
                      <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <?php } else { ?>
                      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                      <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label" for="my_fatoorah_password"><?php echo $entry_sort_order; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="my_fatoorah_sort_order" value="<?php echo $my_fatoorah_sort_order; ?>" class="form-control" id="my_fatoorah_total" placeholder="<?php echo $entry_sort_order; ?>" />
                </div>
              </div>


            </form>
         </div>
      </div>

</div>
<?php echo $footer; ?>
