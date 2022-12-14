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
    <div class="left-panel">
      <div class="logo"><h1><?php echo $heading_title; ?> </h1></div>
      <div class="slidebar"><?php require( DIR_TEMPLATE.'module/pavnewsletter/toolbar.tpl' ); ?></div>
      <div class="clear clr"></div>
    </div>
    <div class="right-panel">
      <div class="heading">
        <h1><?php echo $this->language->get("text_templates"); ?></h1>
      </div>
      <div class="toolbar"><?php $menu_active="template"; require( DIR_TEMPLATE.'module/pavnewsletter/action_bar.tpl' ); ?></div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                  <table class="form"> 
                    <tr>
                      <td colspan="2"><?php echo $this->language->get('entry_mail_settings'); ?></td>
                    </tr>
                      <tr>
                        <td><?php echo $this->language->get('entry_use_custom_email_config'); ?></td>
                        <td>
                              <?php if (isset($general['custom_email_config'])) { ?>
                                    <input type="radio" name="pavnewsletter_config[custom_email_config]" id="custom_email_config1" value="1" <?php echo $general['custom_email_config'] == '1' ? 'checked="checked"' : ''; ?>><label for="custom_email_config1"><?php echo $text_yes; ?></label>
                                     <input type="radio" name="pavnewsletter_config[custom_email_config]" id="custom_email_config0" value="0" <?php echo $general['custom_email_config'] == '0' ? 'checked="checked"' : ''; ?>><label for="custom_email_config0"><?php echo $text_no; ?></label>
                                <?php } else { ?>
                                    <input type="radio" name="pavnewsletter_config[custom_email_config]" id="custom_email_config1" value="1"><label for="custom_email_config1"><?php echo $text_yes; ?></label>
                                     <input type="radio" name="pavnewsletter_config[custom_email_config]" id="custom_email_config0" value="0"  checked="checked"><label for="custom_email_config0"><?php echo $text_no; ?></label>
                                    <?php } ?>
                                  </td>
                      </tr>
                      <tr id="group_1" class="group">
                        <td><?php echo $this->language->get('entry_stores'); ?></td>
                        <td>
                          <table>
                            <tr>
                              <td><?php echo $this->language->get('entry_mail_protocal'); ?></td>
                              <td><select name="pavnewsletter_config[protocal]">
                                <?php
                                if(isset($mail_protocals)){
                                  foreach($mail_protocals as $key=>$val){
                                    if(isset($general["protocal"]) && $key == $general["protocal"]):
                                    ?>
                                   <option value="<?php echo $key;?>" selected="selected"><?php echo $val; ?></option>
                                    <?php
                                    else:
                                      ?>
                                    <option value="<?php echo $key;?>"><?php echo $val; ?></option>
                                    <?php
                                      endif;
                                  }
                                }
                                ?>
                              </select></td>
                            </tr>
                             <tr>
                              <td><?php echo $this->language->get('entry_mail_address'); ?></td>
                              <td><input size="45" type="text" name="pavnewsletter_config[email]" value="<?php echo isset($general['email'])?$general['email']:"";?>"/></td>
                            </tr>
                             <tr>
                              <td><?php echo $this->language->get('entry_smtp_host'); ?></td>
                              <td><input size="45" type="text" name="pavnewsletter_config[smtp_host]" value="<?php echo isset($general['smtp_host'])?$general['smtp_host']:"";?>"/></td>
                            </tr>
                             <tr>
                              <td><?php echo $this->language->get('entry_smtp_username'); ?></td>
                              <td><input size="45" type="text" name="pavnewsletter_config[smtp_username]" value="<?php echo isset($general['smtp_username'])?$general['smtp_username']:"";?>"/></td>
                            </tr>
                            <tr>
                              <td><?php echo $this->language->get('entry_smtp_password'); ?></td>
                              <td><input size="45" type="text" name="pavnewsletter_config[smtp_password]" value="<?php echo isset($general['smtp_password'])?$general['smtp_password']:"";?>"/></td>
                            </tr>
                            <tr>
                              <td><?php echo $this->language->get('entry_smtp_port'); ?></td>
                              <td><input size="45" type="text" name="pavnewsletter_config[smtp_port]" value="<?php echo isset($general['smtp_port'])?$general['smtp_port']:"";?>"/></td>
                            </tr>
                            <tr>
                              <td><?php echo $this->language->get('entry_smtp_timeout'); ?></td>
                              <td><input size="45" type="text" name="pavnewsletter_config[smtp_timeout]" value="<?php echo isset($general['smtp_timeout'])?$general['smtp_timeout']:"5";?>"/></td>
                            </tr>
                            
                          </table>
                        </td>
                      </tr>
                      <tr>
                              <td><?php echo $this->language->get('entry_retries_count'); ?></td>
                              <td><input type="text" name="pavnewsletter_config[retries_count]" value="<?php echo isset($general['retries_count'])?$general['retries_count']:3;?>"/></td>
                            </tr>
                      <!--
                      <tr>
                              <td><?php echo $this->language->get('entry_cron_command'); ?></td>
                              <td></td>
                            </tr>
                      <tr>
                              <td><?php echo $this->language->get('entry_cron_help'); ?></td>
                              <td></td>
                            </tr>
                      -->
                  </table>
      </form>
    </div>
    </div>
    <div class="clear clr"></div>
  </div>
</div>
<script type="text/javascript"><!--
var module_row = <?php echo $module_row; ?>;
$('#tabs a').tabs();
$(".vtabs a").tabs();
</script>
<?php echo $footer; ?>