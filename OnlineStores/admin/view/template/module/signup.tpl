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
    <?php if (isset($this->session->data['success'])) { ?>
    <script>
        var notificationString = '<?php echo $this->session->data["success"]; ?>';
        var notificationType = 'success';
    </script>
    <?php unset($this->session->data["success"]); } ?>
    <div class="box">
        <div class="heading">
          <h1><?php echo $heading_title; ?></h1>
          <div class="buttons">
              <a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a>
              <a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a>
          </div>
        </div>
        <div class="content">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                <?php if ($isModActive['enablemod'] == 1) { ?>
                    <h4 style="margin-top: 0px;"><?php echo $text_enable_module; ?> <input type="checkbox" value = "1"  name="mod_enable" checked></h4>
                <?php } else { ?>
                    <h4 style="margin-top: 0px;"><?php echo $text_enable_module; ?> <input type="checkbox" value = "1"  name="mod_enable"></h4>
                <?php } ?>

                <?php if ($isModActive['single_box'] == 1) { ?>
                    <h4><?php echo $text_show_all_in_box; ?> <input type="checkbox" value = "1"  name="single_box" checked></h4>
                <?php } else { ?>
                    <h4><?php echo $text_show_all_in_box; ?> <input type="checkbox" value = "1"  name="single_box"></h4>
                <?php } ?>

                <?php if ($isModActive['newsletter_sub_enabled'] == 1) { ?>
                <h4><?php echo $text_newsletter_sub_enabled; ?> <input type="checkbox" value = "1"  name="newsletter_sub_enabled" checked></h4>
                <?php } else { ?>
                <h4><?php echo $text_newsletter_sub_enabled; ?> <input type="checkbox" value = "1"  name="newsletter_sub_enabled"></h4>
                <?php } ?>

                <br/>

                <table  class="table table-hover dataTable no-footer">
                    <thead>
                        <tr>
                            <td class="left"><?php echo $text_attr; ?></td>
                            <td class="left"><?php echo $text_show; ?></td>
                            <td class="left"><?php echo $text_required; ?></td>
                            <td class="left"><?php echo $text_custom_name; ?></td>
                        </tr>
                    </thead>
                    <tr>
                        <td class="left"><?php echo $text_first_name; ?></td>
                        <td class="left"><input type="checkbox" value ="1" name="f_name_show" <?php if ($modData['f_name_show'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="checkbox" value = "1"  name="f_name_req" <?php if ($modData['f_name_req'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="text" name="f_name_cstm" value="<?php echo $modData['f_name_cstm']; ?>"  maxlength="20" /></td>
                    </tr>
                    <tr>
                        <td class="left"><?php echo $text_second_name; ?></td>
                        <td class="left"><input type="checkbox" value = "1"  name="l_name_show" <?php if ($modData['l_name_show'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="checkbox" value = "1"  name="l_name_req" <?php if ($modData['l_name_req'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="text" name="l_name_cstm" value="<?php echo $modData['l_name_cstm']; ?>"  maxlength="20" /></td>
                    </tr>
                    <tr>
                        <td class="left"><?php echo $text_tel; ?></td>
                        <td class="left"><input type="checkbox" value = "1"  name="mob_show" <?php if ($modData['mob_show'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="checkbox" value = "1"  name="mob_req" <?php if ($modData['mob_req'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="text" name="mob_cstm" value="<?php echo $modData['mob_cstm']; ?>"  maxlength="20" /></td>

                    </tr>
                    <tr>
                        <td class="left"><?php echo $text_fax; ?></td>
                        <td class="left"><input type="checkbox" value = "1"  name="fax_show" <?php if ($modData['fax_show'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="checkbox" value = "1" disabled name="fax_req" <?php if ($modData['fax_req'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="text" name="fax_cstm" value="<?php echo $modData['fax_cstm']; ?>"  maxlength="20" /></td>

                    </tr>
                    <tr>
                        <td class="left"><?php echo $text_company; ?></td>
                        <td class="left"><input type="checkbox" value = "1"  name="company_show" <?php if ($modData['company_show'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="checkbox" value = "1"  disabled name="company_req" <?php if ($modData['company_req'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="text" name="company_cstm" value="<?php echo $modData['company_cstm']; ?>"  maxlength="20" /></td>

                    </tr>
                    <tr>
                        <td class="left"><?php echo $text_company_id; ?></td>
                        <td class="left"><input type="checkbox" value = "1"  name="companyId_show" <?php if ($modData['companyId_show'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="checkbox" value = "1"  disabled name="companyId_req" <?php if ($modData['companyId_req'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="text" name="companyId_cstm" value="<?php echo $modData['companyId_cstm']; ?>"  maxlength="20" /></td>
                    </tr>
                    <tr>
                        <td class="left"><?php echo $text_add1; ?></td>
                        <td class="left"><input type="checkbox" value = "1"  name="address1_show" <?php if ($modData['address1_show'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="checkbox" value = "1"  name="address1_req" <?php if ($modData['address1_req'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="text" name="address1_cstm" value="<?php echo $modData['address1_cstm']; ?>"  maxlength="20" /></td>
                    </tr>
                    <tr>
                        <td class="left"><?php echo $text_add2; ?></td>
                        <td class="left"><input type="checkbox" value = "1"  name="address2_show" <?php if ($modData['address2_show'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="checkbox" value = "1"  disabled name="address2_req" <?php if ($modData['address2_req'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="text" name="address2_cstm" value="<?php echo $modData['address2_cstm']; ?>"  maxlength="20" /></td>
                    </tr>
                    <tr>
                        <td class="left"><?php echo $text_city; ?></td>
                        <td class="left"><input type="checkbox" value = "1"  name="city_show" <?php if ($modData['city_show'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="checkbox" value = "1"  name="city_req" <?php if ($modData['city_req'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="text" name="city_cstm" value="<?php echo $modData['city_cstm']; ?>"  maxlength="20" /></td>
                    </tr>
                    <tr>
                        <td class="left"><?php echo $text_post_code; ?></td>
                        <td class="left"><input type="checkbox" value = "1"  name="pin_show" <?php if ($modData['pin_show'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="checkbox" value = "1"  name="pin_req" <?php if ($modData['pin_req'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="text" name="pin_cstm" value="<?php echo $modData['pin_cstm']; ?>"  maxlength="20" /></td>
                    </tr>
                    <tr>
                        <td class="left"><?php echo $text_state; ?></td>
                        <td class="left"><input type="checkbox" value = "1"  name="state_show" <?php if ($modData['state_show'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="checkbox" value = "1"  name="state_req" <?php if ($modData['state_req'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="text" name="state_cstm" value="<?php echo $modData['state_cstm']; ?>"  maxlength="20" /></td>
                    </tr>
                    <tr>
                        <td class="left"><?php echo $text_country; ?></td>
                        <td class="left"><input type="checkbox" value = "1"  name="country_show" <?php if ($modData['country_show'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="checkbox" value = "1"  name="country_req" <?php if ($modData['country_req'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="text" name="country_cstm" value="<?php echo $modData['country_cstm']; ?>"  maxlength="20" /></td>
                    </tr>
                    <tr>
                        <td class="left"><?php echo $text_newsletter; ?></td>
                        <td class="left"><input type="checkbox" value = "1"  name="subsribe_show" <?php if ($modData['subsribe_show'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="checkbox" value = "1" disabled name="subsribe_req" <?php if ($modData['subsribe_req'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="text" name="subsribe_cstm" value="<?php echo $modData['subsribe_cstm']; ?>"  maxlength="20" /></td>
                    </tr>
                    <tr>
                        <td class="left"><?php echo $text_pass_confirm; ?></td>
                        <td class="left"><input type="checkbox" value = "1"  name="passconf_show" <?php if ($modData['passconf_show'] >0)  echo "checked"; ?>></td>
                        <td class="left"><input type="checkbox" value = "1" disabled name="passconf_req" <?php  echo "checked"; ?>></td>
                        <td class="left"><input type="text" name="passconf_cstm" value="<?php echo $modData['passconf_cstm']; ?>"  maxlength="20" /></td>
                    </tr>

                    <thead>
                        <tr>
                            <td class="left"><?php echo $text_attr_length; ?></td>
                            <td class="left"><?php echo $text_min; ?></td>
                            <td class="left"><?php echo $text_max; ?></td>
                            <td class="left"><?php echo $text_fixed_length; ?></td>
                        </tr>
                    </thead>
                    <tr>
                        <td class="left"><?php echo $text_mobile; ?></td>
                        <td class="left"><input type="text" name="mob_min" value="<?php echo $modData['mob_min']; ?>"  maxlength="20" /></td>
                        <td class="left"><input type="text" name="mob_max" value="<?php echo $modData['mob_max']; ?>"  maxlength="20" /></td>
                        <td class="left"><input type="text" name="mob_fix" value="<?php echo $modData['mob_fix']; ?>"  maxlength="20" /></td>
                    </tr>
                    <tr>
                        <td class="left"><?php echo $text_pass; ?></td>
                        <td class="left"><input type="text" name="pass_min" value="<?php echo $modData['pass_min']; ?>"  maxlength="20" /></td>
                        <td class="left"><input type="text" name="pass_max" value="<?php echo $modData['pass_max']; ?>"  maxlength="20" /></td>
                        <td class="left"><input type="text" name="pass_fix" value="<?php echo $modData['pass_fix']; ?>"  maxlength="20" /></td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>

<?php echo $footer; ?>