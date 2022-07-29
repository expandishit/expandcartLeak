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
                <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><span><?php echo $button_save; ?></span></a><a onclick="location = '<?php echo $cancel; ?>';" class="button btn btn-primary"><span><?php echo $button_cancel; ?></span></a></div>
            </div>
            <div class="content">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form" class="form-horizontal">
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-heading-title"><?php echo $entry_heading_title; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="gate2play_heading_title" value="<?php echo $gate2play_heading_title; ?>" placeholder="<?php echo $entry_heading_title; ?>" id="input-heading-title" class="form-control" />
                            <?php if ($error_heading_title) { ?>
                            <div class="text-danger"><?php echo $error_heading_title; ?></div>
                            <?php } ?>                
                        </div>
                    </div>  
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                        <div class="col-sm-10">
                            <select name="gate2play_status" class="form-control">
                                <option value="1" <?php echo ($gate2play_status == '1')?'selected="selected"':''; ?>><?php echo $text_enabled; ?></option>
                                <option value="0" <?php echo ($gate2play_status == '0')?'selected="selected"':''; ?>><?php echo $text_disabled; ?></option>
                            </select>
                        </div>
                    </div>              
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-testmode"><?php echo $entry_testmode; ?></label>
                        <div class="col-sm-10">
                            <select name="gate2play_testmode" class="form-control">
                                <option value="0" <?php echo ($gate2play_testmode == '0')?'selected="selected"':''; ?>><?php echo $entry_testmode_off; ?></option>
                                <option value="1" <?php echo ($gate2play_testmode == '1')?'selected="selected"':''; ?>><?php echo $entry_testmode_on; ?></option>		                    
                            </select>
                        </div>
                    </div>              
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-type"><?php echo $entry_base_currency; ?></label>
                        <div class="col-sm-10">
                            <select name="gate2play_base_currency" class="form-control">
                                <?php foreach ($entry_all_currencies as $code => $value) { ?>
                                <option value="<?php echo $value; ?>" <?php echo ($gate2play_base_currency == $value)?'selected="selected"':''; ?>><?php echo $value; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>   
		     <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-type"><?php echo $entry_trans_type; ?></label>
                        <div class="col-sm-10">
                            <select name="gate2play_trans_type" class="form-control">
                                <?php foreach ($entry_all_trans_type as $trans_type_val => $trans_type) { ?>
                                <option value="<?php echo $trans_type_val; ?>" <?php echo ($gate2play_trans_type == $trans_type_val)?'selected="selected"':''; ?>><?php echo $trans_type; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>             
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-mode"><?php echo $entry_trans_mode; ?></label>
                        <div class="col-sm-10">
                            <select name="gate2play_trans_mode" class="form-control">
                                <?php foreach ($entry_all_trans_mode as $trans_mode_val => $trans_mode) { ?>
                                <option value="<?php echo $trans_mode_val; ?>" <?php echo ($gate2play_trans_mode == $trans_mode_val)?'selected="selected"':''; ?>><?php echo $trans_mode; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>                
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-channel"><?php echo $entry_channel; ?></label>
                        <div class="col-sm-10">
                            <input type="text"name="gate2play_channel" value="<?php echo $gate2play_channel; ?>" placeholder="<?php echo $entry_channel; ?>" id="input-channel" class="form-control" />
                            <?php if ($error_channel) { ?>
                            <div class="text-danger"><?php echo $error_channel; ?></div>
                            <?php } ?>                
                        </div>
                    </div>              
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-loginid"><?php echo $entry_loginid; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="gate2play_loginid" value="<?php echo $gate2play_loginid; ?>" placeholder="<?php echo $entry_loginid; ?>" id="input-loginid" class="form-control" />
                            <?php if ($error_loginid) { ?>
                            <div class="text-danger"><?php echo $error_loginid; ?></div>
                            <?php } ?>                  
                        </div>
                    </div>             
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-password"><?php echo $entry_password; ?></label>
                        <div class="col-sm-10">
                            <input type="password" name="gate2play_password" value="<?php echo $gate2play_password; ?>" placeholder="<?php echo $entry_password; ?>" id="input-password" class="form-control" />
                            <?php if ($error_password) { ?>
                            <div class="text-danger"><?php echo $error_password; ?></div>
                            <?php } ?>                  
                        </div>
                    </div>             
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-brands"><?php echo $entry_brands; ?></label>
                        <div class="col-sm-10">
                            <?php 
                            $i = 1;
                            foreach ($entry_all_brands as $payment => $name) {                     
                            ?>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="gate2play_brands[]" value="<?php echo $payment; ?>" <?php echo (is_array($gate2play_brands) && in_array($payment,$gate2play_brands))?'checked="checked"':''; ?>/> <?php echo $name; ?>
                            </label>
                            <?php 
                            if($i%2 == 0) {
                            echo '</tr><tr>';
                            }
                            $i++;
                            } 
                            ?>
                        </div>
                    </div>                
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-style"><?php echo $entry_payment_style; ?></label>
                        <div class="col-sm-10">                
                            <select name="gate2play_payment_style" class="form-control">
                                <?php foreach ($entry_all_payment_style as $payment_style_val => $payment_style) { ?>
                                <option value="<?php echo $payment_style_val; ?>" <?php echo ($gate2play_payment_style == $payment_style_val)?'selected="selected"':''; ?>><?php echo $payment_style; ?></option>
                                <?php } ?>
                            </select>                
                        </div>
                    </div>             
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_order_status; ?></label>
                        <div class="col-sm-10">                
                            <select name="gate2play_order_status_id" class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" <?php echo ($order_status['order_status_id'] == $gate2play_order_status_id)?'selected="selected"':''; ?>><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                            </select>               
                        </div>
                    </div>            
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-failed"><?php echo $entry_order_status_failed; ?></label>
                        <div class="col-sm-10">                
                            <select name="gate2play_order_status_failed_id" class="form-control">
                                <option value="0" <?php echo ('0' == $gate2play_order_status_failed_id)?'selected="selected"':''; ?>><?php echo $text_missing; ?></option>
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" <?php echo ($order_status['order_status_id'] == $gate2play_order_status_failed_id)?'selected="selected"':''; ?>><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                            </select>              
                        </div>
                    </div>                
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-mailerrors"><?php echo str_replace('admin_email',$gate2play_admin_email,$entry_mailerrors); ?></label>
                        <div class="col-sm-10">          
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="gate2play_mailerrors" value="1" <?php echo ($gate2play_mailerrors == '1')?'checked="checked"':''; ?>/> <?php echo $entry_mailerrors_enable; ?>             
                                </label>
                            </div>
                        </div>
                    </div>   
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="gate2play_sort_order" value="<?php echo $gate2play_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
                        </div>
                    </div>            
                </form>
            </div>
        </div>
</div>
<?php echo $footer; ?>
