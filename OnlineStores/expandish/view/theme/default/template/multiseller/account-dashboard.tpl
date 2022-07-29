<?php echo $header; ?>

<style>
	/* The switch - the box around the slider */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

/* Hide default HTML checkbox */
.switch input {display:none;}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>

<div id="content" class="ms-account-dashboard">
	<?php echo $content_top; ?>

	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>

	<h1><?php echo $ms_account_dashboard_heading; ?></h1>

	<!-- Display Alerts -->
	<?php if (isset($warning) && ($warning)) { ?>
		<div class="attention"><?php echo $warning; ?></div>
	<?php } ?>
	
	<?php if (isset($error) && ($error)) { ?>
		<div class="warning"><?php echo $error; ?></div>
	<?php } ?>

	<?php if (isset($success) && ($success)) { ?>
		<div class="success"><?php echo $success; ?></div>
	<?php } ?>
	<!-- /Display Alerts -->

	<div class="overview col-4">
		<h3><?php echo $ms_account_dashboard_overview; ?></h3>
		<img src="<?php echo $seller['avatar']; ?>" /><br />
		<span class="nickname"><?php echo $seller['ms.nickname']; ?></span>
		<p><span>ID <?php echo $ms_id; ?>:</span> <span><?php echo $seller_id; ?></span></p>
		<p><span><?php echo $ms_date_created; ?>:</span> <span><?php echo $seller['date_created']; ?></span></p>
		<p><span><?php echo $ms_account_dashboard_seller_group; ?>:</span> <span><?php echo $seller['seller_group']; ?></span></p>
		<p>
			<span><?php echo $ms_account_dashboard_listing; ?>:</span>

			<span>
			<?php echo $this->currency->getSymbolLeft(); ?><?php echo isset($seller['commission_rates'][MsCommission::RATE_LISTING]['flat']) ? $this->currency->format($seller['commission_rates'][MsCommission::RATE_LISTING]['flat'], $this->config->get('config_currency'), '', FALSE) : '0' ?><?php echo $this->currency->getSymbolRight(); ?>
			+ <?php echo isset($seller['commission_rates'][MsCommission::RATE_LISTING]['percent']) ? $seller['commission_rates'][MsCommission::RATE_LISTING]['percent'] : '0'; ?>%
			</span>
		</p>

		<!-- Sale commission rate -->
		<p>
			<span><?php echo $ms_account_dashboard_sale; ?>:</span>

			<?php if($seller['commission_type'] == 'price_list'){?>
				<a href="<?php echo $this->url->link('seller/commission-price-list')?>">{{ lang('ms_view_price_list') }}</a>
			<?php } else { ?>
				<span>
				<?php echo $this->currency->getSymbolLeft(); ?><?php echo isset($seller['commission_rates'][MsCommission::RATE_SALE]['flat']) ? $this->currency->format($seller['commission_rates'][MsCommission::RATE_SALE]['flat'], $this->config->get('config_currency'), '', FALSE) : '0' ?><?php echo $this->currency->getSymbolRight(); ?>
				+ <?php echo isset($seller['commission_rates'][MsCommission::RATE_SALE]['percent']) ? $seller['commission_rates'][MsCommission::RATE_SALE]['percent'] : '0'; ?>%
				</span>
			<?php } ?>
		</p>

		<!-- <p>
			<span><?php echo $ms_account_dashboard_royalty; ?>:</span>

			<span>
			<?php echo isset($seller['commission_rates'][MsCommission::RATE_SALE]['percent']) ? 100 - $seller['commission_rates'][MsCommission::RATE_SALE]['percent'] : '100'; ?>% -
			<?php echo $this->currency->getSymbolLeft(); ?><?php echo isset($seller['commission_rates'][MsCommission::RATE_SALE]['flat']) ? $this->currency->format($seller['commission_rates'][MsCommission::RATE_SALE]['flat'], $this->config->get('config_currency'), '', FALSE) : '0' ?><?php echo $this->currency->getSymbolRight(); ?>
			</span>
		</p> -->
	</div>

	<div class="stats col-4">
		<h3><?php echo $ms_account_dashboard_stats; ?></h3>
		<p><span><?php echo $ms_account_dashboard_balance; ?>:</span> <span><?php echo $seller['balance']; ?></span></p>
		<p><span><?php echo $ms_account_dashboard_total_sales; ?>:</span> <span><?php echo $seller['total_sales']; ?></span></p>
		<p><span><?php echo $ms_account_dashboard_total_earnings; ?>:</span> <span><?php echo $seller['total_earnings']; ?></span></p>
		<p><span><?php echo $ms_account_dashboard_sales_month; ?>:</span> <span><?php echo $seller['sales_month']; ?></span></p>
		<p><span><?php echo $ms_account_dashboard_earnings_month; ?>:</span> <span><?php echo $seller['earnings_month']; ?></span></p>
		<br>
		<p>
			<b><?= $text_activate_products; ?></b>
			<br><br>
			<label class="switch" style="vertical-align: middle;">
				<input type="checkbox" name="seller_products_state" id="seller_products_state" <?php if ( (string) $products_state != "0" ) {echo "checked";} ?>>
				<span class="slider round"></span>
			</label>
		</p>
	</div>

	<div class="nav col-4 <?php echo $lang == 'ar'?'arabic_padding':'' ?>">
		<h3><?php echo $ms_account_dashboard_nav; ?></h3>
		<a href="<?php echo $this->url->link('seller/account-profile', '', 'SSL'); ?>">
			<img src="expandish/view/theme/default/image/ms-profile.png" />
			<span><?php echo $ms_account_dashboard_nav_profile; ?></span>
		</a>

		<?php if ($gallery_status) { ?>
		<a href="<?php echo $this->url->link('seller/account-gallery', '', 'SSL'); ?>">
			<img src="expandish/view/theme/default/image/ms-gallery.png" />
			<span><?php echo $ms_account_dashboard_nav_gallery; ?></span>
		</a>
		<?php } ?>

		<?php if ($warehouses) { ?>
			<a href="<?php echo $this->url->link('seller/account-warehouse', '', 'SSL'); ?>">
				<img src="expandish/view/theme/default/image/ms-warehouse.png" />
				<span><?php echo $ms_account_dashboard_nav_warehouse; ?></span>
			</a>
			<?php } ?>
		<?php  if (\Extension::isInstalled('seller_based') && $this->config->get('seller_based_status') == 1) {?>
			<a href="<?php echo $this->url->link('shipping/seller_based/activate', 'code=seller_based&activated=1&delivery_company=0', 'SSL'); ?>">
			<img src="expandish/view/theme/default/image/ms-cart-96.png" />
			<span><?php echo $ms_account_dashboard_shipping_methods; ?></span>
		</a>
		<?php } ?>
	

		<a href="<?php echo $this->url->link('seller/account-product/create', '', 'SSL'); ?>">
			<img src="expandish/view/theme/default/image/ms-bag-plus.png" />
			<span><?php echo $ms_account_dashboard_nav_product; ?></span>
		</a>

		<a href="<?php echo $this->url->link('seller/account-product', '', 'SSL'); ?>">
			<img src="expandish/view/theme/default/image/ms-bag.png" />
			<span><?php echo $ms_account_dashboard_nav_products; ?></span>
		</a>

		<a href="<?php echo $this->url->link('seller/account-order', '', 'SSL'); ?>">
			<img src="expandish/view/theme/default/image/ms-cart-96.png" />
			<span><?php echo $ms_account_dashboard_nav_orders; ?></span>
		</a>

		<a href="<?php echo $this->url->link('seller/account-transaction', '', 'SSL'); ?>">
			<img src="expandish/view/theme/default/image/ms-book-96.png" />
			<span><?php echo $ms_account_dashboard_nav_balance; ?></span>
		</a>

		<?php if ($this->config->get('msconf_allow_withdrawal_requests')) { ?>
		<a href="<?php echo $this->url->link('seller/account-withdrawal', '', 'SSL'); ?>">
			<img src="expandish/view/theme/default/image/ms-dollar.png" />
			<span><?php echo $ms_account_dashboard_nav_payout; ?></span>
		</a>
		<?php } ?>

		<?php if ($this->config->get('msconf_enable_seller_independent_payments')) { ?>
		<a href="<?php echo $this->url->link('seller/account-allowed-payment-methods', '', 'SSL'); ?>">
			<img src="expandish/view/theme/default/image/ms-dollar.png" />
			<span><?php echo $ms_account_dashboard_nav_allowed_payment_methods; ?></span>
		</a>
		<?php } ?>

		<?php if (\Extension::isInstalled('your_service')):?>
			<?php if ($this->config->get('ys')['ms_view_requests'] == 1):?>
				<a href="<?= $this->url->link('module/your_service/serviceRequests', '', 'SSL')?>">
					<img src="expandish/view/theme/default/image/ms-book-96.png" />
					<span><?= $ys_ms_show_service_requests?></span>
				</a>
				<a href="<?= $this->url->link('module/your_service/serviceSettings', '', 'SSL')?>">
					<img src="expandish/view/theme/default/image/ms-book-96.png" />
					<span><?= $ys_ms_service_settings?></span>
				</a>
			<?php endif?>
		<?php endif?>

		<a href="<?php echo $this->url->link('seller/account-stats', '', 'SSL'); ?>">
			<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2RpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDpEQTk1NUM0MDI4RjZFMzExQjBDOTk5OEJCOEVDQzgwQSIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo1RTBCMUJFOUY2MjkxMUUzQjQ4NUQyRkQxRjNGNjdDMyIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo1RTBCMUJFOEY2MjkxMUUzQjQ4NUQyRkQxRjNGNjdDMyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IFdpbmRvd3MiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpEQTk1NUM0MDI4RjZFMzExQjBDOTk5OEJCOEVDQzgwQSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpEQTk1NUM0MDI4RjZFMzExQjBDOTk5OEJCOEVDQzgwQSIvPiA8L3JkZjpEZXNjcmlwdGlv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" />
			<span><?php echo $ms_account_stats; ?></span>
		</a>

		<a href="<?php echo $this->url->link('seller/account-subscriptions/updatePlan', '', 'SSL'); ?>">
			<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2RpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDpEQTk1NUM0MDI4RjZFMzExQjBDOTk5OEJCOEVDQzgwQSIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo1RTBCMUJFOUY2MjkxMUUzQjQ4NUQyRkQxRjNGNjdDMyIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo1RTBCMUJFOEY2MjkxMUUzQjQ4NUQyRkQxRjNGNjdDMyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IFdpbmRvd3MiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpEQTk1NUM0MDI4RjZFMzExQjBDOTk5OEJCOEVDQzgwQSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpEQTk1NUM0MDI4RjZFMzExQjBDOTk5OEJCOEVDQzgwQSIvPiA8L3JkZjpEZXNjcmlwdGlv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" />
			<span>{{ lang('ms_update_plan') }}</span>
		</a>

		<?php if (\Extension::isInstalled('printful')):?>
			<?php if ($this->config->get('printful')['status'] == 1):?>
				<a href="<?= $this->url->link('module/printful/settings', '', 'SSL')?>">
					<img src="expandish/view/theme/default/image/ms-bag.png" />
					<span><?= $printful_settings?></span>
				</a>

				<a href="<?= $this->url->link('seller/account-dashboard/printful', '', 'SSL')?>">
					<img src="/admin/view/image/logo-printful.svg" />
					<span><?= $visit_printful?></span>
				</a>
			<?php endif?>
		<?php endif?>

		<?php if (\Extension::isInstalled('seller_ads')):?>
			<a href="<?= $this->url->link('seller/account-ads', '', 'SSL')?>">
				<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2RpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDpEQTk1NUM0MDI4RjZFMzExQjBDOTk5OEJCOEVDQzgwQSIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo1RTBCMUJFOUY2MjkxMUUzQjQ4NUQyRkQxRjNGNjdDMyIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo1RTBCMUJFOEY2MjkxMUUzQjQ4NUQyRkQxRjNGNjdDMyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IFdpbmRvd3MiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpEQTk1NUM0MDI4RjZFMzExQjBDOTk5OEJCOEVDQzgwQSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpEQTk1NUM0MDI4RjZFMzExQjBDOTk5OEJCOEVDQzgwQSIvPiA8L3JkZjpEZXNjcmlwdGlv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" />
				<span><?= $ms_seller_ads?></span>
			</a>
		<?php endif?>

		<?php if ($auctions):?>
			<a href="<?= $this->url->link('seller/account-auctions', '', 'SSL')?>">
			<img src="expandish/view/theme/default/image/aucation.png" />
				<span><?php echo $ms_account_dashboard_aucation; ?></span>
			</a>
		<?php endif?>

		<?php if ($delivery_slot):?>
			<a href="<?= $this->url->link('seller/account-delivery-slot', '', 'SSL')?>">
			<img src="expandish/view/theme/default/image/delivery_slot.png" />
				<span><?php echo $ms_account_delivery_slot; ?></span>
			</a>
		<?php endif ?>

	</div>

	<h2><?php echo $ms_account_dashboard_orders; ?></h2>
	<table class="list">
		<thead>
			<tr>
				<td><?php echo $ms_account_orders_id; ?></td>
				<?php if (!$this->config->get('msconf_hide_customer_email')) { ?>
					<td><?php echo $ms_account_orders_customer; ?></td>
				<?php } ?>
				<td style="width: 40%"><?php echo $ms_account_orders_products; ?></td>
				<td><?php echo $ms_date_created; ?></td>
				<!--<td><?php echo $ms_account_orders_total; ?></td>-->
				<td><?php echo $ms_account_orders_view; ?></td>
			</tr>
		</thead>

		<tbody>
		<?php if (isset($orders) && $orders) { ?>
			<?php foreach ($orders as $order) { ?>
			<tr>
				<td><?php echo $order['order_id']; ?></td>
				<?php if (!$this->config->get('msconf_hide_customer_email')) { ?>
					<td><?php echo $order['customer']; ?></td>
				<?php } ?>
				<td class="left products">
				<?php foreach ($order['products'] as $p) { ?>
				<p>
					<span class="name"><?php if ($p['quantity'] > 1) { echo "{$p['quantity']} x "; } ?> <a href="<?php echo $this->url->link('product/product', 'product_id=' . $p['product_id'], 'SSL'); ?>"><?php echo $p['name']; ?></a></span>
                    <?php foreach ($p['options'] as $option) { ?>
                    <br />
                    &nbsp;<small> - <?php echo $option['name']; ?>:<?php echo $option['value']; ?></small>
                    <?php } ?>
                    <span class="total"><?php echo $this->currency->format($p['seller_net_amt'], $this->config->get('config_currency')); ?></span>
				</p>
				<?php } ?>
				</td>
				<td><?php echo $order['date_created']; ?></td>
				<!--<td><?php echo $order['total']; ?></td>-->
				<td><a href="<?php echo $this->url->link('seller/account-order/viewOrder', 'order_id=' . $order['order_id']); ?>" class="ms-button ms-button-view"></a></td>
			</tr>
			<?php } ?>
		<?php } else { ?>
			<tr>
				<td class="center" colspan="6"><?php echo $ms_account_orders_noorders; ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>

	<div class="buttons">
		<div class="left">
			<a href="<?php echo $link_back; ?>" class="button">
				<span><?php echo $button_back; ?></span>
			</a>
		</div>
	</div>

	<?php echo $content_bottom; ?>
</div>

<div id="dialog-message">

</div>

<style>
	/* For mobile phones: */
	.col-4 {
		width: 100% !important;
		padding-bottom: 30px;
	}
	.col-4 h3{
		padding-left: 40px !important;
	}
	.col-4 p{
		padding-left: 40px !important;
	}
	.col-4 a{
		padding-left: 40px !important;
	}
	.col-4 .nickname{
		padding-left: 40px !important;
	}

	.arabic_padding a{
		padding-left: 0 !important;
	}
	@media only screen and (min-width: 700px) {
		/* For desktop: */
		.col-4 {width: 33.33% !important;}
	}

</style>

<script>
	$('#seller_products_state').on('change', function() {
		var product_state = $(this).is(':checked');
		if (product_state)
		{
			product_state = 'yes';
		}
		else
		{
			product_state = 'no';
		}
		$.ajax({
			url: "index.php?route=seller/account-dashboard",
			type: "POST",
			data: {action: 'change_products_state', 'product_state': product_state},
			success: function(resp){
				$('#dialog-message').html(resp);
			    $( "#dialog-message" ).dialog({
			      modal: true,
			      show: {
			      	effect: "blind",
			      	duration: 200
			      },
			      hide: {
			      	effect: "explode",
			      	duration: 200
			      },
			      buttons: {
			        Ok: function() {
			          $( this ).dialog( "close" );
			        }
			      }
			    });
			},
			error: function(xhr, status, error) {
				var err = eval("(" + xhr.responseText + ")");
				alert(err.Message);
			}
		});
	});
</script>

<?php echo $footer; ?>
