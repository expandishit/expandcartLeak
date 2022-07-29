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
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
      <div class="heading">
          <h1><?php echo $heading_title; ?></h1>
          <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
      </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
		  <tr>


            <td><?php echo $entry_type; ?></td>
            <td><?php if ($migscheckout_type == "1") { ?>
                  <input type="radio" name="migscheckout_type" value="1" checked="checked" />
                  <?php echo $text_server; ?>
                  <input type="radio" name="migscheckout_type" value="0" />
                  <?php echo $text_merchange; ?>
              <?php } elseif ($migscheckout_type == "0") { ?>
                  <input type="radio" name="migscheckout_type" value="1" />
                  <?php echo $text_server; ?>
                  <input type="radio" name="migscheckout_type" value="0" checked="checked" />
                  <?php echo $text_merchange; ?>
              <?php } else { ?>
                  <input type="radio" name="migscheckout_type" value="1" checked="checked" />
                  <?php echo $text_server; ?>
                  <input type="radio" name="migscheckout_type" value="0"  />
                  <?php echo $text_merchange; ?>
              <?php } ?>
            </td>
          </tr>
		
          <tr>
            <td><?php echo $entry_merchant; ?></td>
            <td><input type="text" name="migscheckout_merchant" value="<?php echo $migscheckout_merchant; ?>" />
              <?php if ($error_account) { ?>
              <span class="error"><?php echo $error_account; ?></span>
              <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_secret; ?></td>
            <td><input type="text" name="migscheckout_secret" value="<?php echo $migscheckout_secret; ?>" />
              <?php if ($error_secret) { ?>
              <span class="error"><?php echo $error_secret; ?></span>
              <?php } ?></td>
          </tr>
		  
		  
		   <tr>
            <td><?php echo $entry_secret_mode; ?></td>
            <td><select name="migscheckout_secret_mode">
                <?php if ($migscheckout_secret_mode == 'MD5') { ?>
                <option value="MD5" selected="selected">MD5</option>
                <option value="SHA256">SHA256</option>
                <?php } else { ?>
                <option value="MD5">MD5</option>
                <option value="SHA256" selected="selected">SHA256</option>
                <?php } ?>
              </select></td>
          </tr>
		  
          <tr>
            <td><?php echo $entry_accesscode; ?></td>
            <td>
              <input type="text" name="migscheckout_accesscode" value="<?php echo $migscheckout_accesscode; ?>" />
			  <?php if ($error_accesscode) { ?>
              <span class="error"><?php echo $error_accesscode; ?></span>
              <?php } ?></td>
			  
            </td>
          </tr>
		   <tr>
            <td><?php echo $entry_url; ?></td>
            <td>
				<input type="text" name="migscheckout_url" value="<?php echo $migscheckout_url; ?>" />
             </td>
          </tr>
		    <tr>
            <td><?php echo $entry_locale; ?></td>
            <td>
				<input type="text" name="migscheckout_locale" value="<?php echo $migscheckout_locale; ?>" />
             </td>
          </tr>
		  
          <tr>
            <td><?php echo $entry_test; ?></td>
            <td><?php if ($migscheckout_test) { ?>
              <input type="radio" name="migscheckout_test" value="1" checked="checked" />
              <?php echo $text_yes; ?>
              <input type="radio" name="migscheckout_test" value="0" />
              <?php echo $text_no; ?>
              <?php } else { ?>
              <input type="radio" name="migscheckout_test" value="1" />
              <?php echo $text_yes; ?>
              <input type="radio" name="migscheckout_test" value="0" checked="checked" />
              <?php echo $text_no; ?>
              <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_complete_order_status; ?></td>
            <td><select name="migscheckout_complete_order_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $migscheckout_complete_order_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
		    <tr>
            <td><?php echo $entry_denied_order_status; ?></td>
            <td><select name="migscheckout_denied_order_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $migscheckout_denied_order_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_geo_zone; ?></td>
            <td><select name="twocheckout_geo_zone_id">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                <?php if ($geo_zone['geo_zone_id'] == $twocheckout_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_status; ?></td>

              <td><select name="migscheckout_status">
                      <?php if ($migscheckout_status === "1") { ?>
                      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                      <option value="0"><?php echo $text_disabled; ?></option>
                      <?php } elseif ($migscheckout_status === "0") { ?>
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
            <td><input type="text" name="migscheckout_sort_order" value="<?php echo $migscheckout_sort_order; ?>" size="1" /></td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?>