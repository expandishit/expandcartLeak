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
            <a onclick="if(confirm('<?php echo $this->language->get("Are you sure want to delete this rule?"); ?>')) location.href = '<?php echo $this->url->link('promotions/reward_points/shoppingCartRuleDelete', 'token=' . $this->session->data['token']."&rule_id=".(isset($this->request->get['rule_id']) ? $this->request->get['rule_id'] : 0), 'SSL');?>';" href="javascript:;" class="button btn btn-danger"><?php echo $this->language->get('button_delete'); ?></a>
	        <a href="<?php echo $cancel?>" class="button btn btn-primary"><?php echo $this->language->get('button_cancel'); ?></a>
        </div>
    </div>
    <div class="content">
        <div id="tabs" class="htabs">
            <a href="#tab-rule-infomation"><?php echo $this->language->get('tab_rule_infomation'); ?></a>
            <a href="#tab-rule-conditions"><?php echo $this->language->get('tab_rule_conditions'); ?></a>
            <a href="#tab-rule-actions"><?php echo $this->language->get('tab_rule_actions'); ?></a>
        </div>
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
            <input type="hidden" id="rule_id" name="rule_id" value="<?php echo (isset($this->request->get['rule_id'])) ? $this->request->get['rule_id'] : ''?>"/>
            <input type="hidden" id="apply_rule" name="apply_rule" value=""/>
            <input type="hidden" id="rule_counter" value="<?php echo $rule_counter?>"/>
            <input type="hidden" id="rule_sub_counter" value="<?php echo $rule_sub_counter?>"/>
            <input type="hidden" id="rule_url_post" value="<?php echo $this->url->link('promotions/reward_points/getDataOption', 'token=' . $this->session->data['token'], 'SSL')?>"/>
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
                                <td><?php echo $this->language->get('Rule Description')?><span class="required"> *</span></td>
                                <td><textarea name="description" id="" cols="60" rows="10"><?php echo $description?></textarea></td>
                            </tr>
                            <tr>
                                <td><?php echo $this->language->get('entry_status')?></td>
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
                            <tr>
                                <td><?php echo $this->language->get('column_start_date')?></td>
                                <td><input type="text" name="start_date" class="date" style="width: 100px" value="<?php echo $start_date?>"/></td>
                            </tr>
                            <tr>
                                <td><?php echo $this->language->get('column_end_date')?></td>
                                <td><input type="text" name="end_date" class="date" style="width: 100px" value="<?php echo $end_date?>"/></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div id="tab-rule-conditions">
                <div class="rule-tree">
                    <div class="entry-head">
                        <h4><?php echo $this->language->get('tab_rule_conditions_h4')?></h4>
                    </div>
	                <?php
		                if(!isset($conditions_combine) || count($conditions_combine) == 0)
		                {
			                $conditions_combine[$rule_counter] = array(
				                'aggregator'  =>  'all',
				                'value'  =>  '1',
				                'new_child'  =>  '',
			                );
		                }
	                ?>

	                <?php foreach($conditions_combine as $counter => $condition){ ?>
		                <?php $rule_counter++?>
		                <fieldset id="rule_conditions_fieldset">
                            <?php echo $this->language->get('If')?> <span class="rule-param"><a href="javascript:void(0)" class="label"><?php echo strtoupper($this->language->get($condition['aggregator']))?></a><span class="element"> <select id="conditions__<?php echo $counter?>__aggregator" name="rule[conditions][<?php echo $counter?>][aggregator]" class=" element-value-changer select">
                                        <option value="all" <?php echo ($condition['aggregator'] == 'all' ? 'selected="selected"' : '')?>><?php echo strtoupper($this->language->get('all'))?></option>
                                        <option value="any" <?php echo ($condition['aggregator'] == 'any' ? 'selected="selected"' : '')?>><?php echo strtoupper($this->language->get('any'))?></option>
                                    </select>
                            </span></span><?php echo $this->language->get('&nbsp; of these conditions are')?>
                            <span class="rule-param"><a href="javascript:void(0)" class="label"><?php echo strtoupper(($condition['value'] ? $this->language->get('true') : $this->language->get('false')))?></a>
                                <span class="element">
                                    <select id="conditions__<?php echo $counter?>__value" name="rule[conditions][<?php echo $counter?>][value]" class=" element-value-changer select">
                                        <option value="1" <?php echo ($condition['value'] == '1' ? 'selected="selected"' : '')?>><?php echo strtoupper($this->language->get('true'))?></option>
                                        <option value="0" <?php echo ($condition['value'] == '0' ? 'selected="selected"' : '')?>><?php echo strtoupper($this->language->get('false'))?></option>
                                    </select>
                                </span>
                            </span>&nbsp;:
                            <ul class="rule-param-children">
                                <?php $sub_counter = 0;?>
	                            <?php foreach ($rule as $key => $data) { ?>
		                            <?php $sub_counter++;?>
		                            <?php $key_counter = explode("--", $key);?>
		                            <?php $key_counter = $key_counter[0];?>
		                            <?php if($counter == $key_counter) { ?>
			                            <li>
                                            <input id="conditions__<?php echo $counter?>--<?php echo $sub_counter?>__type" name="rule[conditions][<?php echo $counter?>--<?php echo $sub_counter?>][type]" value="<?php echo $data['type']?>" class="hidden" type="hidden">
                                            <input type="hidden" class="hidden" id="conditions__<?php echo $counter?>--<?php echo $sub_counter?>__attribute" name="rule[conditions][<?php echo $counter?>--<?php echo $sub_counter?>][attribute]" value="attribute_set_id"> <?php echo $data['data']['label']?>&nbsp;
                                            <span class="rule-param"><a href="javascript:void(0)" class="label"><?php echo $this->getOperatorToText(htmlspecialchars_decode($data['operator']))?></a>
                                            <span class="element">
                                            <select id="conditions__<?php echo $counter?>--<?php echo $sub_counter?>__operator" name="rule[conditions][<?php echo $counter?>--<?php echo $sub_counter?>][operator]" class=" element-value-changer select">
                                                <?php foreach($data['data']['operator'] as $op => $label) { ?>
	                                                <option value="<?php echo $op?>" <?php echo (htmlspecialchars_decode($data['operator']) === $op ? 'selected="selected"' : '')?>><?php echo $label?></option>
                                                <?php } ?>
                                            </select>
                                            </span></span>&nbsp;
                                            <span class="rule-param"><a href="javascript:void(0)" class="label"><?php echo $data['data']['selected']?></a>
                                            <span class="element">
                                                <?php if($data['data']['type'] == 'select' || $data['data']['type'] == 'radio' || $data['data']['type'] == 'select' || $data['data']['type'] == 'checkbox') { ?>
	                                                <select id="conditions__<?php echo $counter?>--<?php echo $sub_counter?>__value" name="rule[conditions][<?php echo $counter?>--<?php echo $sub_counter?>][value]" class=" element-value-changer select">
                                                        <option value=""></option>
		                                                <?php foreach($data['data']['values'] as $v) { ?>
			                                                <option value="<?php echo $v['value_id']?>" <?php echo ($v['selected'] == 1 ? 'selected="selected"' : '')?>><?php echo $v['name']?></option>
		                                                <?php } ?>
                                                    </select>
                                                <?php }else{ ?>
	                                                <input id="conditions__<?php echo $counter?>--<?php echo $sub_counter?>__value" name="rule[conditions][<?php echo $counter?>--<?php echo $sub_counter?>][value]" value="<?php echo $data['data']['selected']?>" type="text" class=" input-text element-value-changer">
                                                <?php } ?>
                                            </span></span>&nbsp; <span class="rule-param"><a href="javascript:void(0)" class="rule-param-remove" title="<?php echo $this->language->get('Remove'); ?>"><img src="view/image/rewardpoints/rule_component_remove.gif" alt="" class="v-middle"></a></span>
                                        </li>
		                            <?php } ?>
	                            <?php } ?>
	                            <li>

                                    <span class="rule-param rule-param-new-child">
                                        <a href="javascript:void(0)" class="label"><img src="view/image/rewardpoints/rule_component_add.gif" class="rule-param-add v-middle" alt="" title="<?php echo $this->language->get('Add'); ?>"></a>
                                        <span class="element">
                                            <select id="conditions__<?php echo $counter?>__new_child" name="rule[conditions][<?php echo $counter?>][new_child]" class="element-value-changer select">
                                                <option value="" selected="selected"><?php echo $this->language->get('Please choose a condition to add...')?></option>
                                                <optgroup label="<?php echo $this->language->get('text_product_attribute')?>">
                                                    <?php foreach($product_attributes as $product_attribute){ ?>
                                                    <?php
		                                            $addition_model = $product_attribute['model'].
			                                            (isset($product_attribute['type']) && !empty($product_attribute['type']) ? '-'.$product_attribute['type'] : '').
			                                            (isset($product_attribute['method']) && !empty($product_attribute['method']) ? '-'.$product_attribute['method'] : '');
		                                            ?>
                                                    <option value="<?php echo $addition_model.($product_attribute['id'] ? '|'.$product_attribute['id']: '')?>"><?php echo $product_attribute['text']?></option>
                                                    <?php } ?>
                                                </optgroup>
                                                <optgroup label="<?php echo $this->language->get('text_cart_attribute')?>">
                                                    <?php foreach($cart_attributes as $cart_attribute){ ?>
	                                                    <?php
	                                                    $addition_model = $cart_attribute['model'].
		                                                    (isset($cart_attribute['type']) && !empty($cart_attribute['type']) ? '-'.$cart_attribute['type'] : '').
		                                                    (isset($cart_attribute['method']) && !empty($cart_attribute['method']) ? '-'.$cart_attribute['method'] : '');
	                                                    ?>
	                                                    <option value="<?php echo $addition_model.($cart_attribute['id'] ? '|'.$cart_attribute['id']: '')?>"><?php echo $cart_attribute['text']?></option>
                                                    <?php } ?>
                                                </optgroup>
                                            </select>
                                    </span></span>&nbsp;
                                </li>
                            </ul>
                        </fieldset>
	                <?php } ?>
                </div>
            </div>
            <div id="tab-rule-actions">
                <div>
                    <div class="entry-head">
                        <h4><?php echo $this->language->get('tab_sepnding_rule_actions_h4')?></h4>
                    </div>
                    <div id="rule_actions_fieldset">
                        <table class="form">
                            <tr>
                                <td><?php echo $this->language->get('text_apply')?></td>
                                <td>
                                    <select id="rule_simple_action" name="actions" class="">
                                        <!--<option value="1" <?php /*echo ($actions == "1" ? 'selected=="selected"' : '')*/?>><?php /*echo $this->language->get('Fixed Reward Points per item(X)')*/?></option>-->
                                        <option value="2" <?php echo ($actions == "2" ? 'selected=="selected"' : '')?>><?php echo $this->language->get('Fixed Reward Points (X) for Whole Cart')?></option>
                                        <!--<option value="3" <?php /*echo ($actions == "3" ? 'selected=="selected"' : '')*/?>><?php /*echo $this->language->get('Spend (Y) Get (X) Reward Points per item')*/?></option>-->
                                        <option value="4" <?php echo ($actions == "4" ? 'selected=="selected"' : '')?>><?php echo $this->language->get('Spend (Y) Get (X) Reward Points for Whole Cart')?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo $this->language->get('Reward Points (X)')?><span class="required"> *</span></td>
                                <td>
                                    <input type="text" name="reward_point" style="width: 242px" value="<?php echo $reward_point?>"/>

	                                <?php if ($error_reward_point) { ?>
		                                <span class="error"><?php echo $error_reward_point; ?></span>
	                                <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo $this->language->get('Per (Y) money Spent')?></td>
                                <td>
                                    <input type="text" name="reward_per_spent" style="width: 242px" value="<?php echo $reward_per_spent?>"/>
                                    <p class="note" id="note_reward_per_spent"><span><?php echo $this->language->get('Skip if Fixed Reward Points chosen')?></span></p>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo $this->language->get('Stop Further Rule Processing')?></td>
                                <td>
                                    <select name="stop_rules_processing" id="stop_rules_processing">
                                        <option value="1" <?php echo ($stop_rules_processing == "1" ? 'selected=="selected"' : '')?>><?php echo $this->language->get('No')?></option>
                                        <option value="2" <?php echo ($stop_rules_processing == "2" ? 'selected=="selected"' : '')?>><?php echo $this->language->get('Yes')?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo $this->language->get('Priority')?></td>
                                <td><input type="text" name="rule_position" value="<?php echo $rule_position?>"/></td>
                            </tr>
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
	<script type="text/template" id="rule-condition-li">
        <li>
            <input id="conditions__1--<%= counter %>__type" name="rule[conditions][1--<%= counter %>][type]" value="<%= condition_model %>" class="hidden" type="hidden">
            <input id="conditions__1--<%= counter %>__text" name="rule[conditions][1--<%= counter %>][text]" value="<%= condition_label %>" class="hidden" type="hidden">
            <input type="hidden" class="hidden" id="conditions__1--<%= counter %>__attribute" name="rule[conditions][1--<%= counter %>][attribute]" value="attribute_set_id"> <%= condition_label %>&nbsp;
        <span class="rule-param"><a href="javascript:void(0)" class="label"><?php echo $this->language->get('is'); ?></a>
        <span class="element">
        <%= condition_operator %>
        </span></span>&nbsp;
        <span class="rule-param"><a href="javascript:void(0)" class="label"><%= condition_value_selected %></a>
            <span class="element">
        <%= condition_options_value %>
        </span></span>&nbsp; <span class="rule-param"><a href="javascript:void(0)" class="rule-param-remove" title="<?php echo $this->language->get('Remove'); ?>"><img src="view/image/rewardpoints/rule_component_remove.gif" alt="" class="v-middle"></a></span>
        </li>
    </script>

	<script type="text/template" id="rule-condition-li-wait">
        <li class="rule-param-wait"><?php echo $this->language->get('text_rule_condition_wait')?></li>
    </script>

	<script type="text/template" id="rule-condition-operator">
        <select id="conditions__1--<%= counter %>__operator" name="rule[conditions][1--<%= counter %>][operator]" class=" element-value-changer select">
        <% _.each(operator, function(text, op){ %>
	        <option value="<%= op%>"><%= text%></option>
        <% });%>
    </select>
    </script>

	<script type="text/template" id="rule-condition-value">
    <% if(type == 'select' || type == 'radio' || type == 'checkbox') { %>
	    <select id="conditions__1--<%= counter %>__value" name="rule[conditions][1--<%= counter %>][value]" class=" element-value-changer select">
            <option value="" selected="selected"></option>
		    <% _.each(values, function(value){ %>
			    <option value="<%= value.value_id%>"><%= value.name%></option>
		    <% });%>
    </select>
    <% }else{ %>
	    <input id="conditions__1--<%= counter %>__value" name="rule[conditions][1--<%= counter %>][value]" value="" type="text" style='width:100px !important;' class=" input-text element-value-changer">
    <% } %>
    </script>

	<script type="text/javascript" src="view/javascript/jquery/ui/jquery-ui-timepicker-addon.js"></script>
	<script type="text/javascript"><!--
		$('.date').datepicker({dateFormat: 'yy-mm-dd'});
		//--></script>
<?php echo $footer; ?>
