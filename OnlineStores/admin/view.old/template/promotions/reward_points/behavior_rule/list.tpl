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
    <?php if ($success) { ?>
    <script>
        var notificationString = '<?php echo $success; ?>';
        var notificationType = 'success';
    </script>
    <?php } ?>
	<div class="box">
        <div class="heading">
            <h1><?php echo $heading_title; ?></h1>
            <div class="buttons" style="display: block"><a href="<?php echo $add_rule?>" class="button btn btn-primary"><?php echo $this->language->get('button_add_rule'); ?></a></div>
        </div>
        <div class="content">
            <form action="" method="post" enctype="multipart/form-data" id="form">
                <table class="table table-hover dataTable no-footer">
	                <thead>
                    <tr>
                        <!--<td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>-->
                        <td class="center" style="width: 1px;"><?php echo $this->language->get('column_id')?></td>
                        <td class="center"><?php echo $this->language->get('column_rule_name')?></td>
                        <td class="center" style="width: 430px;"><?php echo $this->language->get('column_behavior_type')?></td>
                        <td class="center" style="width: 130px;"><?php echo $this->language->get('column_reward_point')?></td>
                        <td class="center" style="width: 80px;"><?php echo $this->language->get('column_status')?></td>
                        <td class="center" style="width: 60px;"><?php echo $this->language->get('column_action')?></td>
                    </tr>
	                </thead>
	                <tbody>
	                <?php $this->load->model('promotions/reward_points_transactions');?>

                    <?php if ($rules) { ?>
	                    <?php foreach($rules as $rule) {?>
		                    <?php $rule_url = $this->url->link('promotions/reward_points/behaviorRuleEdit', 'rule_id='.$rule['rule_id'].'&token=' . $this->session->data['token'], 'SSL')?>
		                    <tr>
                    <td class="left"><?php echo $rule['rule_id']?></td>
                    <td class="left"><?php echo $rule['name']?></td>
                    <td class="center">
	                    <span class="behavior-customer-label"><?php echo $this->language->get($this->model_promotions_reward_points_transactions->behaviorToText($rule['actions']));?></span>
                    </td>
                    <td class="center"><?php echo $rule['reward_point']?></td>
                    <td class="center"><?php echo ($rule['status'] == '1' ? $this->language->get('text_enabled') : $this->language->get('text_disabled')) ?></td>
                    <td class="center">[<a href="<?php echo $rule_url?>"><?php echo $this->language->get('text_edit') ?></a>]</td>
                    </tr>
	                    <?php } ?>
                    <?php } else { ?>
	                    <tr>
                        <td class="center" colspan="8"><?php echo $this->language->get('text_no_result'); ?></td>
                    </tr>
                    <?php } ?>
                </table>
            </form>
            <div class="pagination"><?php echo $pagination; ?></div>
        </div>
    </div>
</div>
<?php echo $footer; ?>
