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
        </div>
        <div class="content">
	        <form action="<?php echo $action?>" method="get">
		        <input type="hidden" name="token" value="<?php echo $token?>"/>
		        <input type="hidden" name="route" value="promotions/reward_points/allTransactionHistory"/>
		        <input type="hidden" id="url_post_status" value="<?php echo $url_post_status?>"/>
	        <div class="statistic">
		        <table class="form" style="width: 800px">
			        <tr>
				        <td>
					        <?php echo $this->language->get('Date Range:'); ?>
				        </td>
				        <td>
                            <?php echo $this->language->get('From:'); ?> <input type="text" class="date" name="start_date" value="<?php echo $start_date?>"/>
                            <?php echo $this->language->get('To:'); ?> <input type="text" class="date" name="end_date" value="<?php echo $end_date?>"/>
					        <input type="submit" class="button btn btn-primary" value="<?php echo $this->language->get('button_filter'); ?>" />
				        </td>
			        </tr>
			        <tr>
				        <td><?php echo $this->language->get('Total <b>Rewarded Points</b>'); ?></td>
				        <td><b><?php echo number_format($stats['total_rewarded'])?></b> <?php echo $this->config->get('text_points_'.$this->language->get('code'))?></td>
			        </tr>
			        <tr>
				        <td><?php echo $this->language->get('Total <b>Redeemed Points</b>'); ?></td>
				        <td><b><?php echo number_format($stats['total_redeemed'])?></b> <?php echo $this->config->get('text_points_'.$this->language->get('code'))?></td>
			        </tr>
			        <tr>
				        <td><?php echo $this->language->get('Total <b>Orders</b>'); ?></td>
				        <td><b><?php echo $stats['total_order']?></b></td>
			        </tr>
		        </table>
	        </div>
                <div style="clear: both"></div>
                <div style="float: right;color: gray;  margin: 0 0 10px;display: block;"><i><?php echo $this->language->get('Hover on status column to change and update'); ?></i></div>
                <table class="table table-hover dataTable no-footer transactions">
                    <thead>
                    <tr>
                        <!--<td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>-->
                        <td class="center" style="width: 1px;"><?php echo $this->language->get('column_id')?></td>
                        <td class="center" style="width: 130px;"><?php echo $this->language->get('column_date_added')?></td>
                        <!-- DISPATCH_EVENT:TRANSACTION_AFTER_RENDER_COLUMN_DATE_ADDED -->
                        <td class="center" style="width: 130px;"><?php echo $this->language->get('column_customer_name')?></td>
                        <td class="center" style="width: 120px;"><?php echo $this->language->get('column_customer_email')?></td>
                        <td class="center" style="width: 60px;"><?php echo $this->language->get('column_amount')?></td>
                        <!-- DISPATCH_EVENT:TRANSACTION_AFTER_RENDER_COLUMN_AMOUNT-->
                        <td class="center" style=""><?php echo $this->language->get('column_transaction_detail')?></td>
                        <!-- DISPATCH_EVENT:TRANSACTION_AFTER_RENDER_COLUMN_DETAIL -->
                        <td class="center" style="width: 50px;"><?php echo $this->language->get('column_status')?></td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="row-filter-field" style="height: 40px;">
                        <td><!-- ID --></td>
                        <td><!-- START_DATE --></td>
                        <!-- DISPATCH_EVENT:TRANSACTION_AFTER_RENDER_COLUMN_FILTER_DATE_ADDED -->
                        <td></td>
                        <td><input type="text" name="filter_email" value="<?php echo $filter_email?>"/></td>
                        <td></td>
                        <!-- DISPATCH_EVENT:TRANSACTION_AFTER_RENDER_COLUMN_FILTER_AMOUNT -->
                        <td></td>
                        <!-- DISPATCH_EVENT:TRANSACTION_AFTER_RENDER_COLUMN_FILTER_DETAIL -->
                        <td>
                            <select name="filter_status" id="">
                                <option value=""></option>
                                <option value="0" <?php echo ($filter_status == '0' ? 'selected="selected"' : '')?>><?php echo $this->language->get('Pending')?></option>
                                <option value="1" <?php echo ($filter_status == '1' ? 'selected="selected"' : '')?>><?php echo $this->language->get('Complete')?></option>
                                <option value="2" <?php echo ($filter_status == '2' ? 'selected="selected"' : '')?>><?php echo $this->language->get('Expired')?></option>
                            </select>
                        </td>
                    </tr>
                    <?php if ($transactions) { ?>
	                    <?php $this->load->model('sale/customer');?>
	                    <?php $this->load->model('sale/order');?>
	                    <?php foreach($transactions as $transaction) { ?>
                            <!-- DISPATCH_EVENT:TRANSACTION_AFTER_FOREACH_TRANSACTIONS -->
		                    <?php if($transaction['customer_id'] != '0'){ ?>
			                    <?php $customer = $this->model_sale_customer->getCustomer($transaction['customer_id'])?>
			                    <?php $customer_name = $customer['firstname']." ".$customer['lastname']?>
		                    <?php }else{ ?>
			                    <?php if($transaction['order_id'] != 0){ ?>
				                    <?php $order = $this->model_sale_order->getOrder($transaction['order_id']); ?>
				                    <?php $customer_name = $order['firstname']." ". $order['lastname'] ?>
				                    <?php $customer['email'] = $order['email']; ?>
			                    <?php } ?>
		                    <?php }?>
		                    <tr>
			                    <td class="left"><?php echo $transaction['customer_reward_id']?></td>
			                    <td class="left"><?php echo $transaction['date_added']?></td>
                                <!-- DISPATCH_EVENT:TRANSACTION_AFTER_FOREACH_COLUMN_DATE_ADDED -->
			                    <td class="left"><?php echo $customer_name?></td>
			                    <td class="center"><?php echo $customer['email']?></td>
			                    <td class="center"><?php echo ($transaction['points'] > 0 ? '+' : '').$transaction['points']?></td>
                                <!-- DISPATCH_EVENT:TRANSACTION_AFTER_FOREACH_COLUMN_AMOUNT -->
			                    <td class="left"><?php echo $transaction['description']?></td>
                                <!-- DISPATCH_EVENT:TRANSACTION_AFTER_FOREACH_COLUMN_DETAIL -->
			                    <td class="center transaction_status">
                                    <span class="container_status">
                                        <span class="text_status"><?php echo ($transaction['status'] == '1' ? $this->language->get('Complete') : ($transaction['status'] == '2' ? $this->language->get('Expired') : $this->language->get('Pending')))?></span>
                                        <span class="selection_status">
                                            <select class="reward_status">
                                                <option value="0" <?php echo ($transaction['status'] == "0" ? 'selected="selected"' : '')?>><?php echo $this->language->get('Pending')?></option>
                                                <option value="1" <?php echo ($transaction['status'] == "1" ? 'selected="selected"' : '')?>><?php echo $this->language->get('Complete')?></option>
                                            </select>
                                        </span>
                                        <span class="action_status"><a href="javascript:;" data="<?php echo $transaction['customer_reward_id']?>" class="update_status"><?php echo $this->language->get('Update')?></a></span>
                                    </span>
                                </td>
                            </tr>
	                    <?php } ?>
                    <?php } else { ?>
	                    <tr>
                        <td class="center" colspan="8"><?php echo $this->language->get('text_no_result'); ?></td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>

            <div class="pagination"><?php echo $pagination; ?></div>
		    </form>
        </div>
    </div>
</div>
	<script type="text/javascript" src="view/javascript/jquery/ui/jquery-ui-timepicker-addon.js"></script>
	<script type="text/javascript"><!--
		$('.date').datepicker({dateFormat: 'yy-mm-dd'});
		//--></script>
<?php echo $footer; ?>
