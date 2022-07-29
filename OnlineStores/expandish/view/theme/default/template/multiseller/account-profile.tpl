<?php echo $header; ?>

<div id="content" class="ms-account-profile row">
	<?php echo $content_top; ?>
	
	<div class="breadcrumb col-md-12">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?>
		<a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	
	<h2 class="heading-profile"> <?php echo $ms_account_sellerinfo_heading; ?> :</h2>
	
	<?php if (isset($success) && ($success)) { ?>
		<div class="success"><?php echo $success; ?></div>
	<?php } ?>
	
	<?php if (isset($statustext) && ($statustext)) { ?>
		<div class="<?php echo $statusclass; ?>"><?php echo $statustext; ?></div>
	<?php } ?>

	<p class="warning main"></p>
	
	<form id="ms-sellerinfo" class="ms-form">
		<input type="hidden" name="action" id="ms_action" />
		
		<div class="content">
			<!-- todo status check update -->
			<?php if ($seller['ms.seller_status'] == MsSeller::STATUS_DISABLED || $seller['ms.seller_status'] == MsSeller::STATUS_DELETED) { ?>
			<div class="ms-overlay"></div>
			<?php } ?>

			<?php 
			/////// Seller Fields
			require('seller-form.tpl'); 
			////////////////////
			?>

		</div>
		</form>
		
		<?php if (isset($group_commissions) && $group_commissions[MsCommission::RATE_SIGNUP]['flat'] > 0) { ?>
			<p class="attention ms-commission">
				<?php echo sprintf($this->language->get('ms_account_sellerinfo_fee_flat'),$this->currency->format($group_commissions[MsCommission::RATE_SIGNUP]['flat'], $this->config->get('config_currency')), $this->config->get('config_name')); ?>
				<?php echo $ms_commission_payment_type; ?>
			</p>
			
			<?php if(isset($payment_form)) { ?><div class="ms-payment-form"><?php echo $payment_form; ?></div><?php } ?>
		<?php } ?>
		
		<div class="buttons">
			<div class="left">
				<a href="<?php echo $link_back; ?>" class="button">
					<span><?php echo $button_back; ?></span>
				</a>
			</div>
			
			<?php if ($seller['ms.seller_status'] != MsSeller::STATUS_DISABLED && $seller['ms.seller_status'] != MsSeller::STATUS_DELETED) { ?>
			<div class="right">
				<a class="button" id="ms-submit-button">
					<span><?php echo $ms_button_save; ?></span>
				</a>
			</div>
			<?php } ?>
		</div>
	<?php echo $content_bottom; ?>
</div>

<?php $timestamp = time(); ?>
<script>
	
	$(document).ready(function() {
	  $('.datepicker').datepicker({dateFormat: 'yy-mm-dd'});
	});

	var msGlobals = {
		timestamp: '<?php echo $timestamp; ?>',
		token : '<?php echo md5($salt . $timestamp); ?>',
		session_id: '<?php echo session_id(); ?>',
		zone_id: '<?php echo $seller['ms.zone_id'] ?>',
		uploadError: '<?php echo htmlspecialchars($ms_error_file_upload_error, ENT_QUOTES, "UTF-8"); ?>',
		config_enable_rte: '<?php echo $this->config->get('msconf_enable_rte'); ?>',
		zoneSelectError: '<?php echo htmlspecialchars($ms_account_sellerinfo_zone_select, ENT_QUOTES, "UTF-8"); ?>',
		zoneNotSelectedError: '<?php echo htmlspecialchars($ms_account_sellerinfo_zone_not_selected, ENT_QUOTES, "UTF-8"); ?>'
	};
</script>

<?php if ($this->config->get('msconf_avatars_for_sellers') == 1 || $this->config->get('msconf_avatars_for_sellers') == 2) { ?>
<script type="text/javascript">
	$('#ms-predefined-avatars').colorbox({
		width:'600px', height:'70%', inline:true, href:'#ms-predefined-avatars-container'
	});

	$('.avatars-list img').click(function() {
		if ($('.ms-image img').length == 0) {
			$('#sellerinfo_avatar_files').html('<div class="ms-image">' +
				'<input type="hidden" value="'+$(this).data('value')+'" name="seller[avatar_name]" />' +
				'<img src="'+$(this).attr('src')+'" />' +
				'<span class="ms-remove"></span>' +
				'</div>');
		} else {
			$('.ms-image input[name="seller[avatar_name]"]').val($(this).data('value'));
			$('.ms-image img').attr('src', $(this).attr('src'));
		}
		$(window).colorbox.close();
	});
</script>
<?php } ?>
<?php echo $footer; ?>
