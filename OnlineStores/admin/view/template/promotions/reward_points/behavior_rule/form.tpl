<?php echo $header; ?>
<?php $rule_counter = 1; ?>
<?php $rule_sub_counter = 1; ?>
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
    <div class="box catalog-rules">
        <div class="heading">
            <h1><?php echo $heading_title; ?></h1>
            <div class="buttons">
                <a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $this->language->get('button_save'); ?></a>
                <a onclick="if(confirm('<?php echo $this->language->get("Are you sure want to delete this rule?"); ?>')) location.href = '<?php echo $this->url->link('promotions/reward_points/behaviorRuleDelete', 'token=' . $this->session->data['token']."&rule_id=".$this->request->get['rule_id'], 'SSL');?>';" href="javascript:;" class="button btn btn-danger"><?php echo $this->language->get('button_delete'); ?></a>
                <a href="<?php echo $cancel?>" class="button btn btn-primary"><?php echo $this->language->get('button_cancel'); ?></a>
            </div>
        </div>
        <div class="content behavior-customer-rule">
            <div id="tabs" class="htabs">
                <a href="#tab-rule-infomation"><?php echo $this->language->get('tab_rule_infomation'); ?></a>
            </div>
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                <input type="hidden" id="rule_id" name="rule_id" value="<?php echo (isset($this->request->get['rule_id'])) ? $this->request->get['rule_id'] : ''?>"/>
                <input type="hidden" id="apply_rule" name="apply_rule" value=""/>
                <div id="tab-rule-infomation">
                    <div>
                        <div class="entry-head">
                            <h4><?php echo $this->language->get('tab_rule_info_h4')?></h4>
                        </div>
                        <div id="rule_actions_fieldset">
                            <table class="form">
                                <tr>
                                    <td><?php echo $this->language->get('Rule Name')?><span class="required"> *</span></td>
                                    <td>
                                        <input type="text" name="name" value="<?php echo $name?>"/>
	                                    <?php if ($error_name) { ?>
		                                    <span class="error"><?php echo $error_name; ?></span>
	                                    <?php } ?>
                                    </td>
	                                </td>
                                </tr>
	                            <tr>
                                    <td><?php echo $this->language->get('reward for')?></td>
                                    <td>
                                        <select name="actions" id="actions">
                                            <option value="1" <?php echo ($actions == "1" ? 'selected="selected"' : '')?>><?php echo $this->language->get('Signing Up')?></option>
											<option value="2" <?php echo ($actions == "2" ? 'selected="selected"' : '')?>><?php echo $this->language->get('Posting Product Review')?></option>
											<!--<option value="3" <?php echo ($actions == "3" ? 'selected="selected"' : '')?>><?php echo $this->language->get('Referral Visitor (Friend click on referral link)')?></option>
											<option value="4" <?php echo ($actions == "4" ? 'selected="selected"' : '')?>><?php echo $this->language->get('Referral Sign-Up')?></option>-->
											<option value="5" <?php echo ($actions == "5" ? 'selected="selected"' : '')?>><?php echo $this->language->get('Signing Up Newsletter')?></option>
											<!--<option value="6" <?php echo ($actions == "6" ? 'selected="selected"' : '')?>><?php echo $this->language->get('Facebook Like')?></option>
											<option value="7" <?php echo ($actions == "7" ? 'selected="selected"' : '')?>><?php echo $this->language->get('Facebook Share')?></option>
											<option value="8" <?php echo ($actions == "8" ? 'selected="selected"' : '')?>><?php echo $this->language->get('Customer Birthday')?></option>-->
                                            <!-- DISPATCH_EVENT:BEHAVIOR_AFTER_RENDER_FIELDS_ACTION -->
                                        </select>
	                                    <p class="note" style="margin: 6px 0 0;">
		                                    <span id="post_review" style="display: <?php echo ($actions == '2' ? 'block' : 'none')?>"><?php echo $this->language->get('text_note_behavior_review')?></span>
	                                    </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo $this->language->get('column_status')?></td>
                                    <td>
                                        <select name="status" id="status">
                                            <option value="0" <?php echo ($status == "0" ? 'selected="selected"' : '')?>><?php echo $this->language->get('text_disabled')?></option>
                                            <option value="1" <?php echo ($status == "1" ? 'selected="selected"' : '')?>><?php echo $this->language->get('text_enabled')?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo $this->language->get('Customer Groups')?><span class="required"> *</span></td>
                                    <td>
                                        <select name="customer_group_ids[]" id="customer_group_ids" multiple="multiple" style="width: 242px; height: 90px;">
                                            <option value="99" <?php echo (count($customer_group_ids) > 0 && in_array(99, $customer_group_ids) ? 'selected="selected"' : '')?>><?php echo $this->language->get('NOT LOGGED IN')?></option>
	                                        <?php foreach($customer_groups as $group) { ?>
		                                        <option value="<?php echo $group["customer_group_id"] ?>" <?php echo (count($customer_group_ids) > 0 && in_array($group["customer_group_id"], $customer_group_ids) ? 'selected="selected"' : '')?>><?php echo $group["name"] ?></option>
	                                        <?php } ?>
                                        </select>
	                                    <?php if ($error_customer_group) { ?>
		                                    <span class="error"><?php echo $error_customer_group; ?></span>
	                                    <?php } ?>
                                    </td>
                                </tr>
                                <!-- DISPATCH_EVENT:BEHAVIOR_BEFORE_RENDER_FIELD_REWARD_POINT -->
	                            <tr>
                                    <td><?php echo $this->language->get('Reward Points (X)')?><span class="required"> *</span></td>
                                    <td>
                                        <input type="text" name="reward_point" style="width: 242px" value="<?php echo $reward_point?>"/>

	                                    <?php if ($error_reward_point) { ?>
		                                    <span class="error"><?php echo $error_reward_point; ?></span>
	                                    <?php } ?>
                                    </td>
                                </tr>

                                <!-- DISPATCH_EVENT:BEHAVIOR_AFTER_RENDER_FIELD_REWARD_POINT -->
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
	<script type="text/javascript">
    <!--
    $('#tabs a').tabs();
    //-->
</script>
<?php echo $footer; ?>
