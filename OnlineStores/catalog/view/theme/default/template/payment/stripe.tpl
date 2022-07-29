<?php
//==============================================================================
// Stripe Payment Gateway v230.4
// 
// Author: Clear Thinking, LLC
// E-mail: johnathan@getclearthinking.com
// Website: http://www.getclearthinking.com
// 
// All code within this file is copyright Clear Thinking, LLC.
// You may not copy or reuse code within this file without written permission.
//==============================================================================
?>

<?php if (isset($link_data)) { ?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<style type="text/css">
		body {
			background-image: url(data:image/png;base64,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);
		}
		#payment-link-button {
			background: linear-gradient(#7CE, #08D 85%, #39E);
			border-radius: 5px;
			box-shadow: 0 1px 1px #000;
			color: #FFF;
			cursor: pointer;
			font: bold 24px Helvetica;
			padding: 10px 20px;
			margin-left: 45%;
			position: relative;
			top: 200px;
		}
		.alert {
			background-color: #FFE;
			border: 1px solid #EE8;
			color: #333;
			padding: 10px;
		}
	</style>
<?php } elseif (!empty($settings['applepay'])) { ?>
	<?php include(DIR_TEMPLATE . 'default/template/payment/stripe_applepay.tpl'); ?>
<?php } ?>

<?php if ($settings['use_checkout'] == 'all' || ($settings['use_checkout'] == 'desktop' && !$is_mobile) || $embed) { ?>
	
	<div id="payment"></div>
	<script>
		var handler;
		
		$.getScript('https://checkout.stripe.com/checkout.js', function(data) {
			handler = StripeCheckout.configure({
				// Required
				key:				'<?php echo $settings[$settings['transaction_mode'].'_publishable_key']; ?>',
				token:				function(token, args) { displayWait(); chargeToken(token, args, ''); },
				
				// Highly Recommended
				name:				'<?php echo str_replace("'", "\'", $checkout_title); ?>',
				amount:				<?php echo $checkout_amount; ?>,
			<?php if ($checkout_image) { ?>
				image:				'<?php echo $checkout_image; ?>',
			<?php } ?>
			<?php if ($checkout_description) { ?>
				description:		'<?php echo str_replace("'", "\'", $checkout_description); ?>',
			<?php } ?>
				
				// Optional
			<?php if ($checkout_button) { ?>
				panelLabel:			'<?php echo str_replace(array("'", '[amount]'), array("\'", '{{amount}}'), $checkout_button); ?>',
			<?php } ?>
				currency:			'<?php echo strtolower($currency); ?>',
				billingAddress:		<?php echo (!empty($settings['checkout_billing'])) ? 'true' : 'false'; ?>,
				shippingAddress:	<?php echo (!empty($settings['checkout_shipping'])) ? 'true' : 'false'; ?>,
				email:				'<?php echo (!empty($settings['checkout_bitcoin'])) ? '' : $order_info['email']; ?>',
				allowRememberMe:	<?php echo (!empty($settings['checkout_remember_me'])) ? 'true' : 'false'; ?>,
				alipay:				<?php echo (!empty($settings['checkout_alipay'])) ? 'true' : 'false'; ?>,
				bitcoin:			<?php echo (!empty($settings['checkout_bitcoin'])) ? 'true' : 'false'; ?>,
			});
		});
		
		function confirmOrder() {
			<?php if (!empty($settings['checkout_shipping']) && $no_shipping_method) { ?>
				alert('<?php echo str_replace("'", "\'", $settings['error_shipping_required_' . $language]); ?>');
			<?php } else { ?>
				handler.open();
			<?php } ?>
			return false;
		}
	</script>
	
<?php } else { ?>
	
	<style type="text/css">
		body .payment-stripe {
			display: block !important; /* Journal fix */
		}
		#payment {
			text-align: center;
		}
		#card-select, #stored-card, #new-card, #card-element, #payment label {
			display: inline-block;
		}
		#card-select {
			width: 200px;
			margin: 0 10px 10px 0;
		}
		#stored-card {
			margin-top: 10px;
			padding: 5px;
		}
		#card-element {
			background: #FFF;
			border: 1px solid #DDD;
			border-radius: 10px;
			margin-right: 10px;
			padding: 0 15px;
			vertical-align: middle;
			width: 300px;
		}
		.StripeElement--complete {
			border: 1px solid #0C0 !important;
		}
		#payment label {
			cursor: pointer;
		}
		#store-card {
			cursor: pointer;
			margin-top: 10px;
		}
		.alert {
			margin-top: 10px;
		}
		.buttons {
			margin-bottom: 10px;
		}
	</style>
	<?php if (version_compare(VERSION, '2.0', '<')) { ?>
		<style type="text/css">
			#payment fieldset {
				margin-bottom: 25px !important;
			}
			#payment legend {
				font-size: 18px !important;
			}
		</style>
	<?php } ?>
	
	<form id="payment" class="form-horizontal">
		<fieldset id="card-fieldset">
			<legend><?php echo html_entity_decode($settings['text_card_details_' . $language], ENT_QUOTES, 'UTF-8'); ?></legend>
		<?php if ($customer) { ?>
			<select id="card-select" class="form-control" onchange="if ($('#new-card').css('display') == 'none') { $('#stored-card').fadeOut(400, function(){$('#new-card').fadeIn()}); } else { $('#new-card').fadeOut(400, function(){$('#stored-card').fadeIn()}); }">
				<option value="stored"><?php echo html_entity_decode($settings['text_use_your_stored_card_' . $language], ENT_QUOTES, 'UTF-8'); ?></option>
				<option value="new"><?php echo html_entity_decode($settings['text_use_a_new_card_' . $language], ENT_QUOTES, 'UTF-8'); ?></option>
			</select>
			<div id="stored-card">
				<?php echo $customer['default_source']['brand'] . ' ' . html_entity_decode($settings['text_ending_in_' . $language], ENT_QUOTES, 'UTF-8') . ' ' . $customer['default_source']['last4']; ?>
				(<?php echo str_pad($customer['default_source']['exp_month'], 2, '0', STR_PAD_LEFT) . '/' . substr($customer['default_source']['exp_year'], 2); ?>)
			</div>
			<div id="new-card" style="display: none">
		<?php } else { ?>
			<div id="new-card">
		<?php } ?>
				<div id="card-element"></div>
				<?php if ($logged_in && $settings['allow_stored_cards'] && $settings['send_customer_data'] == 'choice') { ?>					
					<label><?php echo html_entity_decode($settings['text_store_card_' . $language], ENT_QUOTES, 'UTF-8'); ?> <input type="checkbox" id="store-card" /></label>
				<?php } ?>
			</div>
		</fieldset>
	</form>
	<script>
		function confirmOrder() {
			displayWait();
			
			if ($('#new-card').css('display') == 'none') {
				chargeToken('', '', '');
			} else {
				var extraDetails = {
					name: '<?php echo trim(str_replace("'", "\'", html_entity_decode($order_info['firstname'] . ' ' . $order_info['lastname'], ENT_QUOTES, 'UTF-8'))); ?>',
					address_line1: '<?php echo trim(str_replace("'", "\'", html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8'))); ?>',
					address_line2: '<?php echo trim(str_replace("'", "\'", html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8'))); ?>',
					address_city: '<?php echo trim(str_replace("'", "\'", html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8'))); ?>',
					address_state: '<?php echo trim(str_replace("'", "\'", html_entity_decode($order_info['payment_zone'], ENT_QUOTES, 'UTF-8'))); ?>',
					address_zip: '<?php echo trim(str_replace("'", "\'", html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8'))); ?>',
					address_country: '<?php echo trim(str_replace("'", "\'", html_entity_decode($order_info['payment_country'], ENT_QUOTES, 'UTF-8'))); ?>'
				};
				stripe.createToken(card, extraDetails).then(function(result){
					if (result.error) {
						displayError(stripe_errors[result.error.code] ? stripe_errors[result.error.code] : result.error.message);
					} else {
						chargeToken(result.token, '', '');
					}
				});
			}
		}
	</script>
	
<?php } ?>

<div id="payment-buttons" class="buttons">
	<div class="right pull-right">
		<a id="button-confirm" onclick="confirmOrder()" class="<?php echo $settings['button_class']; ?>" style="<?php echo $settings['button_styling']; ?>">
			<?php echo $settings['button_text_' . $language]; ?>
		</a>
	</div>
</div>

<script>
	<?php if ($settings['transaction_mode'] == 'production') { ?>
		if (window.location.protocol != 'https:') {
			displayError('You are in LIVE mode but are not on a secure (https) connection! Payment info is not secure!');
		}
	<?php } ?>
	
	var stripe;
	var card;
	
	$.getScript('https://js.stripe.com/v3/', function(data) {
		stripe = Stripe('<?php echo $settings[$settings['transaction_mode'].'_publishable_key']; ?>');
		stripeElements = stripe.elements({locale: '<?php echo substr($language, 0, 2); ?>'});
		card = stripeElements.create('card', {
			hidePostalCode: true,
			//iconStyle: 'solid', // use solid if you are on a dark background
			style: {
				base: {
					// full styling options are available at https://stripe.com/docs/stripe.js#element-options
					color: '#444',
					fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
					fontSize: '15px',
					iconColor: '#66F',
					lineHeight: '40px',
					'::placeholder': {
						color: '#CCC',
					},
				},
			}
		});
		card.on('change', function(event){
			/*
			$('.alert').remove();
			if (event.error) {
				displayError(event.error.message);
			}
			*/
		}).mount('#card-element');
		
		<?php if (!empty($settings['applepay'])) { ?>
			Stripe.applePay.checkAvailability(function(available) {
				if (available) {
					$('#apple-pay-fieldset').show();
					<?php if (version_compare(VERSION, '2.0', '<')) { ?>
						$('#payment-buttons').appendTo('#card-fieldset');
					<?php } ?>
				}
			});
		<?php } ?>
	});
	
	var stripe_errors = {
		<?php foreach ($stripe_errors as $stripe_error) { ?>
			<?php echo $stripe_error; ?>: '<?php echo str_replace("'", "\'", html_entity_decode($settings['error_' . $stripe_error . '_' . $language], ENT_QUOTES, 'UTF-8')); ?>',
		<?php } ?>
	}
	
	function displayWait() {
		$('#button-confirm').removeAttr('onclick').attr('disabled', 'disabled');
		$('#card-select').attr('disabled', 'disabled');
		$('.alert').remove();
		$('#payment').after('<div class="attention alert alert-warning" style="display: none"><?php if (version_compare(VERSION, '2.0', '>=')) echo '<i class="fa fa-spinner fa-spin"></i> &nbsp; '; ?><?php echo str_replace("'", "\'", html_entity_decode($settings['text_please_wait_' . $language], ENT_QUOTES, 'UTF-8')); ?></div>');
		$('.attention').fadeIn();
	}
	
	function displayError(message) {
		$('.alert').remove();
		$('#payment').after('<div class="warning alert alert-danger" style="display: none"><?php if (version_compare(VERSION, '2.0', '>=')) echo '<i class="fa fa-exclamation-triangle"></i> &nbsp; '; ?>' + message.trim() + '</div>');
		$('.warning').fadeIn();
		$('#button-confirm').attr('onclick', 'confirmOrder()').removeAttr('disabled');
		$('#card-select').removeAttr('disabled');
	}
	
	function chargeToken(token, addresses, applepay) {
		$.ajax({
			type: 'POST',
			url: 'index.php?route=<?php echo $type . '/' . $name . '/' . (isset($link_data) ? 'chargeLink&link_data=' . $link_data : 'chargeToken'); ?>',
			data: {token: token.id, email: token.email, addresses: addresses, store_card: $('#store-card').is(':checked'), embed: <?php echo (int)$embed; ?>},
			success: function(error) {
				if (error.trim()) {
					if (applepay) {
						applepay(ApplePaySession.STATUS_FAILURE);
					}
					displayError(error);
				} else {
					if (applepay) {
						applepay(ApplePaySession.STATUS_SUCCESS);
					}
					<?php if (isset($link_data)) { ?>
						alert('Success!');
						location = '<?php echo $checkout_success; ?>';
					<?php } else { ?>
						completeOrder();
					<?php } ?>
				}
			},
			error: function(xhr, status, error) {
				displayError(xhr.responseText ? xhr.responseText : error);
			}
		});
	}
	
	function completeOrder() {
		$.ajax({
			url: 'index.php?route=<?php echo $type; ?>/<?php echo $name; ?>/completeOrder',
			success: function(error) {
				if (error.trim()) {
					completeWithError(error.trim());
				} else {
					location = '<?php echo $checkout_success; ?>';
				}
			},
			error: function(xhr, status, error) {
				completeWithError(xhr.responseText ? xhr.responseText : error);
			}
		});
	}
	
	function completeWithError(errorMessage) {
		$.ajax({
			type: 'POST',
			url: 'index.php?route=<?php echo $type; ?>/<?php echo $name; ?>/completeWithError',
			data: {error_message: errorMessage},
			success: function(error) {
				if (error.trim()) {
					triggerFatalError(error);
				} else {
					location = '<?php echo $checkout_success; ?>';
				}
			},
			error: function(xhr, status, error) {
				triggerFatalError(xhr.responseText ? xhr.responseText : error);
			}
		});
	}
	
	function triggerFatalError(errorMessage) {
		$('.alert').remove();
		$('#payment').after('<div class="warning alert alert-danger"><?php if (version_compare(VERSION, '2.0', '>=')) echo '<i class="fa fa-exclamation-triangle"></i> '; ?><strong>Fatal Error:</strong> Your payment was completed successfully, but the system encountered a fatal error when trying to complete your order. Please do not resubmit your order! Instead, please <a target="_blank" href="index.php?route=information/contact">contact the store administrator</a> with your order number (#<?php echo $order_info['order_id']; ?>) and the following error message:<br /><br />' + errorMessage.trim() + '</div>');
	}
</script>
