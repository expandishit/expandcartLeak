<?php 
	require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" ); 
	$themeName = $this->config->get('config_template');
?>
<?php echo $header; ?>


<?php if( $SPAN[0] ): ?>
	<aside class="span<?php echo $SPAN[0];?>">
		<?php echo $column_left; ?>
	</aside>
<?php endif; ?> 

<section class="span<?php echo $SPAN[1];?>">
	<?php if ($error_warning) { ?>
	<div class="warning"><?php echo $error_warning; ?></div>
	<?php } ?>
	
	<div id="content" class="register one-page">
	
		<?php echo $content_top; ?>
		
		<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/breadcrumb.tpl" ); ?>

		<h1><?php echo $heading_title; ?></h1>		
		
		<div class="inner">
			<p><?php echo $text_account_already; ?></p>
			<p><?php echo $text_signup; ?></p>
			<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" class="pav-form">
				<h3><?php echo $text_your_details; ?></h3>
				<div class="content wrapper">
					<table class="form table no-border">
						<tr>
							<td>
								<span class="required">*</span> 
								<?php echo $entry_firstname; ?>
							</td>
							<td>
								<input type="text" name="firstname" value="<?php echo $firstname; ?>" class="span8" />
								<?php if ($error_firstname) { ?>
								<span class="error"><?php echo $error_firstname; ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td>
								<span class="required">*</span> 
								<?php echo $entry_lastname; ?>
							</td>
							<td>
								<input type="text" name="lastname" value="<?php echo $lastname; ?>" class="span8" />
								<?php if ($error_lastname) { ?>
								<span class="error"><?php echo $error_lastname; ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td>
								<span class="required">*</span> 
								<?php echo $entry_email; ?>
							</td>
							<td>
								<input type="text" name="email" value="<?php echo $email; ?>" class="span8" />
								<?php if ($error_email) { ?>
								<span class="error"><?php echo $error_email; ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td>
								<span class="required">*</span> 
								<?php echo $entry_telephone; ?>
							</td>
							<td>
								<input type="text" name="telephone" value="<?php echo $telephone; ?>" class="span8" />
								<?php if ($error_telephone) { ?>
								<span class="error"><?php echo $error_telephone; ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td><?php echo $entry_fax; ?></td>
							<td><input type="text" name="fax" value="<?php echo $fax; ?>" class="span8" /></td>
						</tr>
					</table>
				</div>	
				
				<h3><?php echo $text_your_address; ?></h3>		
				<div class="content wrapper">
					<table class="form table no-border">
						<tr>
							<td><?php echo $entry_company; ?></td>
							<td><input type="text" name="company" value="<?php echo $company; ?>" class="span8" /></td>
						</tr>
						<tr>
							<td><?php echo $entry_website; ?></td>
							<td><input type="text" name="website" value="<?php echo $website; ?>" class="span8" /></td>
						</tr>
						<tr>
							<td>
								<span class="required">*</span> 
								<?php echo $entry_address_1; ?>
							</td>
							<td>
								<input type="text" name="address_1" value="<?php echo $address_1; ?>" class="span8" />
								<?php if ($error_address_1) { ?>
								<span class="error"><?php echo $error_address_1; ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td><?php echo $entry_address_2; ?></td>
							<td><input type="text" name="address_2" value="<?php echo $address_2; ?>" class="span8" /></td>
						</tr>
						<tr>
							<td>
								<span class="required">*</span> 
								<?php echo $entry_city; ?>
							</td>
							<td>
								<input type="text" name="city" value="<?php echo $city; ?>" class="span8" />
								<?php if ($error_city) { ?>
								<span class="error"><?php echo $error_city; ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td>
								<span id="postcode-required" class="required">*</span> 
								<?php echo $entry_postcode; ?>
							</td>
							<td>
								<input type="text" name="postcode" value="<?php echo $postcode; ?>" class="span8" />
								<?php if ($error_postcode) { ?>
								<span class="error"><?php echo $error_postcode; ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td>
								<span class="required">*</span> 
								<?php echo $entry_country; ?>
							</td>
							<td>
								<select name="country_id" class="span8">
									<option value="false"><?php echo $text_select; ?></option>
									<?php foreach ($countries as $country) { ?>
									<?php if ($country['country_id'] == $country_id) { ?>
									<option value="<?php echo $country['country_id']; ?>" selected="selected"><?php echo $country['name']; ?></option>
									<?php } else { ?>
									<option value="<?php echo $country['country_id']; ?>"><?php echo $country['name']; ?></option>
									<?php } ?>
									<?php } ?>
								</select>
								<?php if ($error_country) { ?>
								<span class="error"><?php echo $error_country; ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td>
								<span class="required">*</span> 
								<?php echo $entry_zone; ?>
							</td>
							<td>
								<select name="zone_id" class="span8"></select>
								<?php if ($error_zone) { ?>
								<span class="error"><?php echo $error_zone; ?></span>
								<?php } ?>
							</td>
						</tr>
					</table>
				</div>

				<h3><?php echo $text_payment; ?></h3>
				<div class="content wrapper">
					<table class="form table no-border">
						<tbody>
							<tr>
								<td><?php echo $entry_tax; ?></td>
								<td><input type="text" name="tax" value="<?php echo $tax; ?>" class="span8" /></td>
							</tr>
							<tr>
								<td>
									<?php echo $entry_payment; ?>
								</td>
								<td>
									<?php if ($payment == 'cheque') { ?>
									<input type="radio" name="payment" value="cheque" id="cheque" checked="checked" />
									<?php } else { ?>
									<input type="radio" name="payment" value="cheque" id="cheque" />
									<?php } ?>
									<label for="cheque"><?php echo $text_cheque; ?></label>
									<?php if ($payment == 'paypal') { ?>
									<input type="radio" name="payment" value="paypal" id="paypal" checked="checked" />
									<?php } else { ?>
									<input type="radio" name="payment" value="paypal" id="paypal" />
									<?php } ?>
									<label for="paypal"><?php echo $text_paypal; ?></label>
									<?php if ($payment == 'bank') { ?>
									<input type="radio" name="payment" value="bank" id="bank" checked="checked" />
									<?php } else { ?>
									<input type="radio" name="payment" value="bank" id="bank" />
									<?php } ?>
									<label for="bank"><?php echo $text_bank; ?></label>
								</td>
							</tr>
						</tbody>
						<tbody id="payment-cheque" class="payment">
							<tr>
								<td><?php echo $entry_cheque; ?></td>
								<td><input type="text" name="cheque" value="<?php echo $cheque; ?>" class="span8" /></td>
							</tr>
						</tbody>
						<tbody class="payment" id="payment-paypal">
							<tr>
								<td><?php echo $entry_paypal; ?></td>
								<td><input type="text" name="paypal" value="<?php echo $paypal; ?>" class="span8" /></td>
							</tr>
						</tbody>
						<tbody id="payment-bank" class="payment">
							<tr>
								<td><?php echo $entry_bank_name; ?></td>
								<td><input type="text" name="bank_name" value="<?php echo $bank_name; ?>" class="span8" /></td>
							</tr>
							<tr>
								<td><?php echo $entry_bank_branch_number; ?></td>
								<td><input type="text" name="bank_branch_number" value="<?php echo $bank_branch_number; ?>" class="span8" /></td>
							</tr>
							<tr>
								<td><?php echo $entry_bank_swift_code; ?></td>
								<td><input type="text" name="bank_swift_code" value="<?php echo $bank_swift_code; ?>" class="span8" /></td>
							</tr>
							<tr>
								<td><?php echo $entry_bank_account_name; ?></td>
								<td><input type="text" name="bank_account_name" value="<?php echo $bank_account_name; ?>" class="span8" /></td>
							</tr>
							<tr>
								<td><?php echo $entry_bank_account_number; ?></td>
								<td><input type="text" name="bank_account_number" value="<?php echo $bank_account_number; ?>" class="span8" /></td>
							</tr>
						</tbody>
					</table>
				</div>

				<h3><?php echo $text_your_password; ?></h3>
				<div class="content wrapper">
					<table class="form table no-border">
						<tr>
							<td>
								<span class="required">*</span> 
								<?php echo $entry_password; ?>
							</td>
							<td>
								<input type="password" name="password" value="<?php echo $password; ?>" class="span8" />
								<?php if ($error_password) { ?>
								<span class="error"><?php echo $error_password; ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td>
								<span class="required">*</span> 
								<?php echo $entry_confirm; ?>
							</td>
							<td>
								<input type="password" name="confirm" value="<?php echo $confirm; ?>" class="span8" />
								<?php if ($error_confirm) { ?>
								<span class="error"><?php echo $error_confirm; ?></span>
								<?php } ?>
							</td>
						</tr>
					</table>
				</div>
				<?php if ($text_agree) { ?>
					<div class="buttons clearfix wrapper">
						<div class="row-fluid">
							<div class="span6">
								<div class="left">
									<?php echo $text_agree; ?>
									<?php if ($agree) { ?>
									<input type="checkbox" name="agree" value="1" checked="checked" />
									<?php } else { ?>
									<input type="checkbox" name="agree" value="1" />
									<?php } ?>
								</div>
							</div>							
							<div class="span6">
								<div class="right">
									<input type="submit" value="<?php echo $button_continue; ?>" class="button" />
								</div>
							</div>
						</div>
					</div>
					<?php } else { ?>
					<div class="buttons clearfix wrapper">
						<div class="right">
							<input type="submit" value="<?php echo $button_continue; ?>" class="button" />
						</div>
					</div>
					<?php } ?>
			</form>  		
		</div>		
		<?php echo $content_bottom; ?>
	</div>

	<script type="text/javascript">
	<!--
	$('select[name=\'country_id\']').bind('change', function() {
		$.ajax({
			url: 'index.php?route=affiliate/register/country&country_id=' + this.value,
			dataType: 'json',
			beforeSend: function() {
				$('select[name=\'country_id\']').after('<span class="wait">&nbsp;<img src="catalog/view/theme/<?php echo $themeName;?>/image/loading.gif" alt="" /></span>');
			},
			complete: function() {
				$('.wait').remove();
			},			
			success: function(json) {
				if (json['postcode_required'] == '1') {
					$('#postcode-required').show();
				} else {
					$('#postcode-required').hide();
				}
				
				html = '<option value=""><?php echo $text_select; ?></option>';
				
				if (json['zone'] != '') {
					for (i = 0; i < json['zone'].length; i++) {
						html += '<option value="' + json['zone'][i]['zone_id'] + '"';
						
						if (json['zone'][i]['zone_id'] == '<?php echo $zone_id; ?>') {
							html += ' selected="selected"';
						}

						html += '>' + json['zone'][i]['name'] + '</option>';
					}
				} else {
					html += '<option value="0" selected="selected"><?php echo $text_none; ?></option>';
				}
				
				$('select[name=\'zone_id\']').html(html);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});

		$('select[name=\'country_id\']').trigger('change');
	//-->
	
	</script>

	<script type="text/javascript">
	<!--
		$('input[name=\'payment\']').bind('change', function() {
			$('.payment').hide();
			
			$('#payment-' + this.value).show();
		});

		$('input[name=\'payment\']:checked').trigger('change');
	//-->
	</script> 
		
	<script type="text/javascript">
	<!--
		$(document).ready(function() {
			$('.colorbox').colorbox({
				width: 640,
				height: 480
			});
		});
	//-->
	</script> 

</section> 

<?php if( $SPAN[2] ): ?>
<aside class="span<?php echo $SPAN[2];?>">	
	<?php echo $column_right; ?>
</aside>
<?php endif; ?>

<?php echo $footer; ?>