<table class="form">
  <tr>
	<td><?php echo $column_order_id; ?></td>
	<td>
		<?php echo $transaction['order_id']; ?>
		<?php if ($cancel_action) { ?>
		<a class="button button-command" data-type="cancel"><?php echo $button_cancel; ?></a>
		<?php } ?>
	</td>
  </tr>
  <tr>
	<td><?php echo $column_merchant_id; ?></td>
	<td><?php echo $transaction['merchant_id']; ?></td>
  </tr>
  <tr>
	<td><?php echo $column_reference; ?></td>
	<td><?php echo $transaction['reference']; ?></td>
  </tr>
  <tr>
	<td><?php echo $column_status; ?></td>
	<td><?php echo $transaction['status']; ?></td>
  </tr>
  <tr>
	<td><?php echo $column_fraud_status; ?></td>
	<td><?php echo $transaction['fraud_status']; ?></td>
  </tr>
  <tr>
	<td><?php echo $column_merchant_reference_1; ?></td>
	<td>
		<?php if ($merchant_reference_action) { ?>
        <input type="text" name="merchant_reference_1" value="<?php echo $transaction['merchant_reference_1']; ?>" />
        <a class="button button-command" data-type="merchant_reference"><?php echo $button_update; ?></a>
		<?php } else { ?>
		<?php echo $transaction['merchant_reference_1']; ?>
		<?php } ?>
	</td>
  </tr>
  <tr>
	<td><?php echo $column_customer_details; ?></td>
	<td>
	  <table class="list">
		<thead>
		  <tr>
			<td class="left"><?php echo $column_billing_address; ?></td>
			<td class="left"><?php echo $column_shipping_address; ?></td>
		  </tr>
		</thead>
		<tr>
		  <td class="left"><?php echo $transaction['billing_address_formatted']; ?></td>
		  <td class="left"><?php echo $transaction['shipping_address_formatted']; ?></td>
		</tr>
		<?php if ($address_action) { ?>
		<tr>
		  <td class="left">
			<a class="button kc-modal" data-target="#billing_address"><?php echo $button_edit; ?></a>
		  </td>
		  <td class="left">
			<a class="button kc-modal" data-target="#shipping_address"><?php echo $button_edit; ?></a>
		  </td>
		</tr>
		<?php } ?>
	  </table>
	</td>
  </tr>
  <tr>
	<td><?php echo $column_order_lines; ?></td>
	<td>
	  <table class="list">
		<thead>
		  <tr>
			<td class="left"><?php echo $column_item_reference; ?></td>
			<td class="left"><?php echo $column_type; ?></td>
			<td class="left"><?php echo $column_quantity; ?></td>
			<td class="left"><?php echo $column_quantity_unit; ?></td>
			<td class="left"><?php echo $column_name; ?></td>
			<td class="left"><?php echo $column_total_amount; ?></td>
			<td class="left"><?php echo $column_unit_price; ?></td>
			<td class="left"><?php echo $column_total_discount_amount; ?></td>
			<td class="left"><?php echo $column_tax_rate; ?></td>
			<td class="left"><?php echo $column_total_tax_amount; ?></td>
		  </tr>
		</thead>
		<tbody>
			<?php foreach ($transaction['order_lines'] as $order_line) { ?>
			  <tr>
				<td class="left"><?php echo $order_line['reference']; ?></td>
				<td class="left"><?php echo $order_line['type']; ?></td>
				<td class="left"><?php echo $order_line['quantity']; ?></td>
				<td class="left"><?php echo $order_line['quantity_unit']; ?></td>
				<td class="left"><?php echo $order_line['name']; ?></td>
				<td class="left"><?php echo $order_line['total_amount']; ?></td>
				<td class="left"><?php echo $order_line['unit_price']; ?></td>
				<td class="left"><?php echo $order_line['total_discount_amount']; ?></td>
				<td class="left"><?php echo $order_line['tax_rate']; ?></td>
				<td class="left"><?php echo $order_line['total_tax_amount']; ?></td>
			  </tr>
		  <?php } ?>
		</tbody>
	  </table>
	</td>
  </tr>
  <tr>
	<td><?php echo $column_amount; ?></td>
	<td><?php echo $transaction['amount']; ?></td>
  </tr>
  <tr>
	<td><?php echo $column_authorization_remaining; ?></td>
	<td>
	  <?php echo $transaction['authorization_remaining']; ?>
	  <?php if ($release_authorization_action) { ?>
	  <a class="button button-command" data-type="release_authorization"><?php echo $button_release_authorization; ?></a>
	  <?php } ?>
	</td>
  </tr>
  <?php if ($transaction['authorization_expiry']) { ?>
  <tr>
	<td><?php echo $column_authorization_expiry; ?></td>
	<td>
		<?php echo $transaction['authorization_expiry']; ?>
		<?php if ($extend_authorization_action) { ?>
		<a class="button button-command" data-type="extend_authorization"><?php echo $button_extend_authorization; ?></a>
		<?php } ?>
	</td>
  </tr>
  <?php } ?>
  <tr>
	<td><?php echo $column_capture; ?></td>
	<td>
		<table class="list">
		  <thead>
			<tr>
			  <td class="left"><?php echo $column_capture_id; ?></td>
			  <td class="left"><?php echo $column_date; ?></td>
			  <td class="left"><?php echo $column_amount; ?></td>
			  <td class="left"><?php echo $column_reference; ?></td>
			  <td class="left"><?php echo $column_action; ?></td>
			</tr>
		  </thead>
		  <tbody>
			<?php if ($captures) { ?>
			<?php foreach ($captures as $capture) { ?>
			<tr>
			  <td class="left"><?php echo $capture['capture_id']; ?></td>
			  <td class="left"><?php echo $capture['date_added']; ?></td>
			  <td class="left"><?php echo $capture['amount']; ?></td>
			  <td class="left"><?php echo $capture['reference']; ?></td>
			  <td class="left">
				<a class="button button-command" data-type="trigger_send_out" data-id="<?php echo $capture['capture_id']; ?>"><?php echo $button_trigger_send_out; ?></a>
				<a class="button kc-modal" data-target="#capture-shipping-info-<?php echo $capture['capture_id']; ?>"><?php echo $button_edit_shipping_info; ?></a>
				<a class="button kc-modal" data-target="#capture-billing-address-<?php echo $capture['capture_id']; ?>"><?php echo $button_edit_billing_address; ?></a>
			  </td>
			</tr>
			<?php } ?>
			<?php } else { ?>
			<tr>
			  <td class="center" colspan="4"><?php echo $text_no_capture; ?></td>
			</tr>
			<?php } ?>
		  </tbody>
		  <?php if ($capture_action) { ?>
		  <tfoot>
			<tr>
			  <td colspan="4"></td>
			  <td class="left">
                  <a class="kc-modal button" data-toggle="modal" data-target="#capture"><?php echo $button_new_capture; ?></a>
              </td>
			</tr>
		  </tfoot>
		  <?php } ?>
		</table>
	</td>
  </tr>
  <tr>
	<td><?php echo $column_refund; ?></td>
	<td>
		<table class="list">
		  <thead>
			<tr>
			  <td class="left"><?php echo $column_date; ?></td>
			  <td class="left"><?php echo $column_amount; ?></td>
			  <td class="left"><?php echo $column_action; ?></td>
			</tr>
		  </thead>
		  <tbody>
			<?php if ($refunds) { ?>
			<?php foreach ($refunds as $refund) { ?>
			<tr>
			  <td class="left"><?php echo $refund['date_added']; ?></td>
			  <td class="left"><?php echo $refund['amount']; ?></td>
			</tr>
			<?php } ?>
			<?php } ?>
		  </tbody>
		  <?php if ($refund_action) { ?>
		  <tfoot>
			<tr>
			  <td colspan="2"></td>
			  <td class="left">
				<a class="kc-modal button" data-toggle="modal" data-target="#refund"><?php echo $button_new_refund; ?></a>
			  </td>
			</tr>
		  </tfoot>
		  <?php } ?>
		</table>
	</td>
  </tr>
</table>

<!-- Modals -->
<div class="modal fade" id="billing_address">
    <h4 class="modal-title" id="billing_address_title"><?php echo $column_billing_address; ?></h4>
    <table class="form">
		<tr>
		  <td><?php echo $column_title; ?></td>
          <td>
			<input type="text" name="title" value="<?php echo $transaction['billing_address']['title']; ?>" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_given_name; ?></td>
		  <td>
			<input type="text" name="given_name" value="<?php echo $transaction['billing_address']['given_name']; ?>" id="input-billing-address-given-name" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_family_name; ?></td>
		  <td>
			<input type="text" name="family_name" value="<?php echo $transaction['billing_address']['family_name']; ?>" id="input-billing-address-family-name" />
		  </td>
		</div>
		<tr>
		  <td><?php echo $column_street_address; ?></td>
		  <td>
			<input type="text" name="street_address" value="<?php echo $transaction['billing_address']['street_address']; ?>" id="input-billing-address-street-address" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_street_address2; ?></td>
		  <td>
			<input type="text" name="street_address2" value="<?php echo $transaction['billing_address']['street_address2']; ?>" id="input-billing-address-street-address2" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_city; ?></td>
		  <td>
			<input type="text" name="city" value="<?php echo $transaction['billing_address']['city']; ?>" id="input-billing-address-city" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_postal_code; ?></td>
		  <td>
			<input type="text" name="postal_code" value="<?php echo $transaction['billing_address']['postal_code']; ?>" id="input-billing-address-postal-code" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_region; ?></td>
		  <td>
			<input type="text" name="region" value="<?php echo $transaction['billing_address']['region']; ?>" id="input-billing-address-region" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_country; ?></td>
		  <td>
			<input type="text" name="country" value="<?php echo $transaction['billing_address']['country']; ?>" id="input-billing-address-country" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_email; ?></td>
		  <td>
			<input type="text" name="email" value="<?php echo $transaction['billing_address']['email']; ?>" id="input-billing-address-email" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_phone; ?></td>
          <td>
			<input type="text" name="phone" value="<?php echo $transaction['billing_address']['phone']; ?>" id="input-billing-address-phone" />
		  </td>
		</tr>
        <tr>
            <td></td>
            <td><a class="button button-command" data-type="billing_address"><?php echo $button_update; ?></a></td>
        </tr>
    </table>
</div>

<div class="modal fade" id="shipping_address">
    <h4 class="modal-title" id="shipping_address_title"><?php echo $column_shipping_address; ?></h4>
      <table class="form">
        <tr>
		  <td><?php echo $column_title; ?></td>
          <td>
			<input type="text" name="title" value="<?php echo $transaction['shipping_address']['title']; ?>" id="input-capture-shipping-address-title" />
		  </td>
		</tr>
        <tr>
		  <td><?php echo $column_given_name; ?></td>
          <td>
			<input type="text" name="given_name" value="<?php echo $transaction['shipping_address']['given_name']; ?>" id="input-shipping-address-given-name" />
		  </td>
		</tr>
        <tr>
		  <td><?php echo $column_family_name; ?></td>
          <td>
			<input type="text" name="family_name" value="<?php echo $transaction['shipping_address']['family_name']; ?>" id="input-shipping-address-family-name" />
		  </td>
		</tr>
        <tr>
		  <td><?php echo $column_street_address; ?></td>
          <td>
			<input type="text" name="street_address" value="<?php echo $transaction['shipping_address']['street_address']; ?>" id="input-shipping-address-street-address" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_street_address2; ?></td>
          <td>
			<input type="text" name="street_address2" value="<?php echo $transaction['shipping_address']['street_address2']; ?>" id="input-shipping-address-street-address2" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_city; ?></td>
          <td>
			<input type="text" name="city" value="<?php echo $transaction['shipping_address']['city']; ?>" id="input-shipping-address-city" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_postal_code; ?></td>
          <td>
			<input type="text" name="postal_code" value="<?php echo $transaction['shipping_address']['postal_code']; ?>" id="input-shipping-address-postal-code" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_region; ?></td>
          <td>
			<input type="text" name="region" value="<?php echo $transaction['shipping_address']['region']; ?>" id="input-shipping-address-region" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_country; ?></td>
          <td>
			<input type="text" name="country" value="<?php echo $transaction['shipping_address']['country']; ?>" id="input-shipping-address-country" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_email; ?></td>
          <td>
			<input type="text" name="email" value="<?php echo $transaction['shipping_address']['email']; ?>" id="input-shipping-address-email" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_phone; ?></td>
          <td>
			<input type="text" name="phone" value="<?php echo $transaction['shipping_address']['phone']; ?>" id="input-shipping-address-phone" />
		  </td>
		</tr>
        <tr>
            <td></td>
            <td><a class="button button-command" data-type="shipping_address"><?php echo $button_update; ?></a></td>
        </tr>
      </div>
  </table>
</div>

<div class="modal fade" id="capture">
    <h4 class="modal-title" id="capture_title"><?php echo $text_new_capture_title; ?></h4>
    <table class="form">
      <tr>
          <td><?php echo $column_amount; ?></td>
          <td>
            <input text="text" name="capture_amount" value="<?php echo $max_capture_amount; ?>" id="input-capture-amount" />
          </td>
      </tr>
      <tr>
          <td><a class="button button-command" data-type="capture"><?php echo $button_update; ?></a></td>
      </tr>
    </table>
</div>

<div class="modal fade" id="refund">
        <h4 class="modal-title" id="refund_title"><?php echo $text_new_refund_title; ?></h4>
      <table class="form">
		<tr>
		  <td><?php echo $column_amount; ?></td>
          <td>
			<input text="text" name="refund_amount" value="<?php echo $max_refund_amount; ?>" id="input-refund-amount" />
		  </td>
		</tr>
	  </table>
      <div class="modal-footer">
        <a class="button button-command" data-type="refund" data-modal="#refund"><?php echo $button_update; ?></a>
      </div>
    </div>
  </div>
</div>

<?php foreach ($captures as $capture) { ?>
<div class="modal fade" id="capture-shipping-info-<?php echo $capture['capture_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="capture-shipping-info-<?php echo $capture['capture_id']; ?>-title" style="">
        <h4 class="modal-title" id="capture-shipping-info-<?php echo $capture['capture_id']; ?>-title"><?php echo $capture['shipping_info_title']; ?></h4>
		<table class="form shipping-info-data">
		  <tbody>
			<tr>
			  <?php foreach ($capture['shipping_info'] as $key => $shipping_info) { ?>
			  <td><?php echo $shipping_info['shipping_company']; ?></td>
			  <?php } ?>
			</tr>
			<tr>
			  <?php foreach ($capture['shipping_info'] as $key => $shipping_info) { ?>
			  <td><?php echo $shipping_info['shipping_method']; ?></td>
			  <?php } ?>
			</tr>
			<tr>
			  <?php foreach ($capture['shipping_info'] as $key => $shipping_info) { ?>
			  <td><?php echo $shipping_info['tracking_number']; ?></td>
			  <?php } ?>
			</tr>
			<tr>
			  <?php foreach ($capture['shipping_info'] as $key => $shipping_info) { ?>
			  <td><?php echo $shipping_info['tracking_uri']; ?></td>
			  <?php } ?>
			</tr>
			<tr>
			  <?php foreach ($capture['shipping_info'] as $key => $shipping_info) { ?>
			  <td><?php echo $shipping_info['return_shipping_company']; ?></td>
			  <?php } ?>
			</tr>
			<tr>
			  <?php foreach ($capture['shipping_info'] as $key => $shipping_info) { ?>
			  <td><?php echo $shipping_info['return_tracking_number']; ?></td>
			  <?php } ?>
			</tr>
			<tr>
			  <?php foreach ($capture['shipping_info'] as $key => $shipping_info) { ?>
			  <td><?php echo $shipping_info['return_tracking_uri']; ?></td>
			  <?php } ?>
			</tr>
		  </tbody>
		  <tfoot>
			<tr>
			  <td colspan="<?php echo count($capture['shipping_info']) + 1; ?>"></td>
			  <td class="left">
                  <a id="add-shipping-info" onclick="addShippingInfo('#capture-shipping-info-<?php echo $capture['capture_id']; ?>');" title="<?php echo $button_add_shipping_info; ?>" class="button"><?php echo $button_add_shipping_info; ?></a>
              </td>
			</tr>
		  </tfoot>
		</table>
      <div class="modal-footer">
        <a type="button" class="button button-command" data-type="capture_shipping_info" data-id="<?php echo $capture['capture_id']; ?>"><?php echo $button_update; ?></a>
      </div>
</div>

<div class="modal fade" id="capture-billing-address-<?php echo $capture['capture_id']; ?>">
        <h4 class="modal-title" id="capture-billing-address-<?php echo $capture['capture_id']; ?>-title"><?php echo $capture['billing_address_title']; ?></h4>
      <table>
		<tr>
		  <td><?php echo $column_title; ?></td>
          <td>
			<input type="text" name="title" value="<?php echo $capture['billing_address']['title']; ?>" id="input-capture-billing-address-<?php echo $capture['capture_id']; ?>-title" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_given_name; ?></td>
          <td>
			<input type="text" name="given_name" value="<?php echo $capture['billing_address']['given_name']; ?>" id="input-capture-billing-address-<?php echo $capture['capture_id']; ?>-given-name" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_family_name; ?></td>
          <td>
			<input type="text" name="family_name" value="<?php echo $capture['billing_address']['family_name']; ?>" id="input-capture-billing-address-<?php echo $capture['capture_id']; ?>-family-name" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_street_address; ?></td>
          <td>
			<input type="text" name="street_address" value="<?php echo $capture['billing_address']['street_address']; ?>" id="input-capture-billing-address-<?php echo $capture['capture_id']; ?>-street-address" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_street_address2; ?></td>
          <td>
			<input type="text" name="street_address2" value="<?php echo $capture['billing_address']['street_address2']; ?>" id="input-capture-billing-address-<?php echo $capture['capture_id']; ?>-street-address2" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_city; ?></td>
          <td>
			<input type="text" name="city" value="<?php echo $capture['billing_address']['city']; ?>" id="input-capture-billing-address-<?php echo $capture['capture_id']; ?>-city" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_postal_code; ?></td>
          <td>
			<input type="text" name="postal_code" value="<?php echo $capture['billing_address']['postal_code']; ?>" id="input-capture-billing-address-<?php echo $capture['capture_id']; ?>-postal-code" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_region; ?></td>
          <td>
			<input type="text" name="region" value="<?php echo $capture['billing_address']['region']; ?>" id="input-capture-billing-address-<?php echo $capture['capture_id']; ?>-region" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_country; ?></td>
          <td>
			<input type="text" name="country" value="<?php echo $capture['billing_address']['country']; ?>" id="input-capture-billing-address-<?php echo $capture['capture_id']; ?>-country" />
		  </td>
		</tr>
		<tr>
		  <td><?php echo $column_email; ?></td>
          <td>
			<input type="text" name="email" value="<?php echo $capture['billing_address']['email']; ?>" id="input-capture-billing-address-<?php echo $capture['capture_id']; ?>-email" />
		  </td>
		</div>
		<tr>
		  <td><?php echo $column_phone; ?></td>
          <td>
			<input type="text" name="phone" value="<?php echo $capture['billing_address']['phone']; ?>" id="input-capture-billing-address-<?php echo $capture['capture_id']; ?>-phone" />
		  </td>
		</div>
      </table>
      <div class="modal-footer">
        <a class="button button-command" data-type="capture_billing_address" data-id="<?php echo $capture['capture_id']; ?>" data-modal="#capture-billing-address-<?php echo $capture['capture_id']; ?>"><?php echo $button_update; ?></a>
      </div>
</div>
<?php } ?>
<style>
.button {
    color: #fff !important;
}
</style>
<script type="text/javascript"><!--

$('#billing_address').dialog({
    autoOpen: false,
    height: 700,
    width: 450,
    modal: true
});

$('#shipping_address').dialog({
    autoOpen: false,
    height: 700,
    width: 450,
    modal: true
});

$('#capture').dialog({
    autoOpen: false,
    height: 400,
    width: 300,
    modal: true
});

$('#refund').dialog({
    autoOpen: false,
    height: 400,
    width: 300,
    modal: true
});

<?php foreach ($captures as $capture) { ?>
$('#capture-shipping-info-<?php echo $capture['capture_id']; ?>').dialog({
    autoOpen: false,
    height: 550,
    width: 500,
    modal: true
});

$('#capture-billing-address-<?php echo $capture['capture_id']; ?>').dialog({
    autoOpen: false,
    height: 550,
    width: 500,
    modal: true
});
<?php } ?>

$('.kc-modal').on('click', function() {
    $($(this).attr('data-target')).dialog('open');
});

$(document).off('click', '.button-command').on('click', '.button-command', function(e) {
    e.preventDefault();

	var type = $(this).attr('data-type');
	var id = $(this).attr('data-id');
	var confirm_text = '';
	var data = {};

	if (type === 'cancel') {
		confirm_text = '<?php echo $text_confirm_cancel; ?>';
	} else if (type === 'capture') {
		data = $('#input-capture-amount').val();

		<?php if ($symbol_left) { ?>
		confirm_text = '<?php echo $text_confirm_capture; ?> ' + '<?php echo $symbol_left; ?>' + $('#input-capture-amount').val();
		<?php } elseif ($symbol_right) { ?>
		confirm_text = '<?php echo $text_confirm_capture; ?> ' + $('#input-capture-amount').val() + '<?php echo $symbol_right; ?>';
		<?php } ?>
	} else if (type === 'refund') {
		data = $('#input-refund-amount').val();

		<?php if ($symbol_left) { ?>
		confirm_text = '<?php echo $text_confirm_refund; ?> ' + '<?php echo $symbol_left; ?>' + $('#input-refund-amount').val();
		<?php } elseif ($symbol_right) { ?>
		confirm_text = '<?php echo $text_confirm_refund; ?> ' + $('#input-refund-amount').val() + '<?php echo $symbol_right; ?>';
		<?php } ?>
	} else if (type === 'extend_authorization') {
		confirm_text = '<?php echo $text_confirm_extend_authorization; ?>';
	} else if (type === 'merchant_reference') {
		data = $('input[name=\'merchant_reference_1\']').serialize();

		confirm_text = '<?php echo $text_confirm_merchant_reference; ?>';
	} else if (type === 'billing_address') {
		data = $('#billing_address :input').serialize();

		confirm_text = '<?php echo $text_confirm_billing_address; ?>';
	} else if (type === 'shipping_address') {
		data = $('#shipping_address :input').serialize();

		confirm_text = '<?php echo $text_confirm_shipping_address; ?>';
	} else if (type === 'release_authorization') {
		confirm_text = '<?php echo $text_confirm_release_authorization; ?>';
	} else if (type === 'capture_shipping_info') {
		data = $('#capture-shipping-info-' + id + ' :input').serialize();

		confirm_text = '<?php echo $text_confirm_shipping_info; ?>';
	} else if (type === 'capture_billing_address') {
		data = $('#capture-billing-address-' + id + ' :input').serialize();

		confirm_text = '<?php echo $text_confirm_billing_address; ?>';
	} else if (type === 'trigger_send_out') {
		confirm_text = '<?php echo $text_confirm_trigger_send_out; ?>';
	} else {
		return;
	}

	if (confirm(confirm_text)) {
		$.ajax({
			url: 'index.php?route=payment/klarna_checkout/transactionCommand&token=<?php echo $token; ?>&order_id=<?php echo $order_id; ?>',
			type: 'post',
			data: {
				type: type,
				id: id,
				order_ref: '<?php echo $order_ref; ?>',
				data: data
			},
			dataType: 'json',
			beforeSend: function() {
				$(".ui-dialog-content").dialog('close');

				$('.kc-alert').hide();

				$('.kc-alert').removeClass('success warning');
			},
			complete: function() {

			},
			success: function(json) {
				if (json.error) {
					$('.kc-alert').show().addClass('warning').html(json.error);
				}

				if (json.success) {
					$('.kc-alert').show().addClass('success').html(json.success);
				}

				setTimeout(function() {
					getTransaction('<?php echo $order_id; ?>');
				}, 300);
			}
		});
	}
});

var shipping_info_row = 0;

function addShippingInfo(id) {
	$(id + ' .shipping-info-data tbody tr:nth-child(1)').append('<td><div class="col-sm-12"><input type="text" name="shipping_info[' + shipping_info_row + '][shipping_company]" value="" data-id="' + shipping_info_row + '" placeholder="<?php echo $entry_shipping_company; ?>" /></div></td>');

    html = '  <td><div class="col-sm-12"><select name="shipping_info[' + shipping_info_row + '][shipping_method]" class="form-control">';
    <?php foreach ($allowed_shipping_methods as $shipping_method) { ?>
    html += '    <option value="<?php echo $shipping_method; ?>"><?php echo addslashes($shipping_method); ?></option>';
    <?php } ?>
    html += '  </select></div></td>';

	$(id + ' .shipping-info-data tbody tr:nth-child(2)').append(html);
	$(id + ' .shipping-info-data tbody tr:nth-child(3)').append('<td><div class="col-sm-12"><input type="text" name="shipping_info[' + shipping_info_row + '][tracking_number]" value="" data-id="' + shipping_info_row + '" placeholder="<?php echo $entry_tracking_number; ?>" /></div></td>');
	$(id + ' .shipping-info-data tbody tr:nth-child(4)').append('<td><div class="col-sm-12"><input type="text" name="shipping_info[' + shipping_info_row + '][tracking_uri]" value="" data-id="' + shipping_info_row + '" placeholder="<?php echo $entry_tracking_uri; ?>" /></div></td>');
	$(id + ' .shipping-info-data tbody tr:nth-child(5)').append('<td><div class="col-sm-12"><input type="text" name="shipping_info[' + shipping_info_row + '][return_shipping_company]" value="" data-id="' + shipping_info_row + '" placeholder="<?php echo $entry_return_shipping_company; ?>" /></div></td>');
	$(id + ' .shipping-info-data tbody tr:nth-child(6)').append('<td><div class="col-sm-12"><input type="text" name="shipping_info[' + shipping_info_row + '][return_tracking_number]" value="" data-id="' + shipping_info_row + '" placeholder="<?php echo $entry_return_tracking_number; ?>" /></div></td>');
	$(id + ' .shipping-info-data tbody tr:nth-child(7)').append('<td><div class="col-sm-12"><input type="text" name="shipping_info[' + shipping_info_row + '][return_tracking_uri]" value="" data-id="' + shipping_info_row + '" placeholder="<?php echo $entry_return_tracking_uri; ?>" /></div></td>');

	var colspan = $(id + ' .shipping-info-data tfoot tr td:nth-child(1)').attr('colspan');

	$(id + ' .shipping-info-data tfoot tr td:nth-child(1)').attr('colspan', parseInt(colspan) + 1);

	shipping_info_row++;
}
//--></script>