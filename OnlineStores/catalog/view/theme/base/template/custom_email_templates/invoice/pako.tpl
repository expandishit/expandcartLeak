<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>" xml:lang="<?php echo $lang; ?>">
<head>
<title><?php echo $title; ?></title>
<base href="<?php echo $base; ?>" />
<style>
body {
	background: #FFFFFF;
}
body, td, th, input, select, textarea, option, optgroup {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #2b2b2b;
}
.container {
	width: 100%;
	position: relative;
}

.invoice, .address, .detail, .products {
	border-collapse: collapse;
	margin-top: 5px;
	width: 100%;
	border: 1px solid #2b2b2b;
}
.invoice td, .address td, .detail td, .products td, td.bordered {
	border: 1px solid #2b2b2b;
}
.invoice td {
	font-size: 10px;
	padding: 7px;
}
.invoice td b {
	font-size: 13px;
	font-weight: bold;
	display: block;
	margin-bottom: 4px;
}
.invoice td.inv {
	font-size: 18px;
	font-weight: bold;
	background: #d7d7d7;
	color: #000000;
	padding: 16px;
}
.address td, .detail td {
	padding: 7px;
	vertical-align: top;
}
.products {
	margin-bottom: 30px;
}
.products .heading td {
	background: #d7d7d7;
	font-size: 12px;
	color: #000000;
	font-weight: bold;
	padding: 7px;
}
.products td {
	padding: 7px;
}
td.total {
	background: #d7d7d7;
}
.authorized {
	border-collapse: collapse;
	margin-top: 25px;
	display: inline-table;
	width: 300px;
	margin-right: 50px;
	border: 1px solid #2b2b2b;
}
.authorized td {
	padding: 20px;
	font-size: 10px;
}
.authorized td b {
	font-size: 13px;
	font-weight: bold;
	display: block;
	margin-bottom: 4px;
}
.noborder, .noborder td {
	border: none;
}
.noborder td {
	padding: 17px 25px;
}
</style>
</head>
<body>
  <div class="container">
    <table class="invoice">
      <tr>
        <td width="33%" rowspan="2" align="center" valign="bottom"><?php echo $text_company_stamp; ?></td>
        <td width="33%" align="center" class="inv"><?php echo $text_invoice; ?></td>
		<td width="33%" align="center"><b><?php if ($order_info['invoice_no']) { ?> <?php echo $order_info['invoice_no']; ?><?php } ?></b></td>
      </tr>
      <tr>
	    <td align="center"><b><?php echo $order_info['invoice_date']; ?></b><?php echo $text_invoice_date; ?></td>
		<td align="center"><b><?php echo $order_info['date_added']; ?></b><?php echo $text_date_added; ?></td>
      </tr>
	</table>
	<table class="address">
      <tr>
	    <td width="50%"><b><?php echo $text_from; ?>:</b><br /><?php echo $order_info['store_name']; ?><br />
          <?php echo $order_info['store_address']; ?><br />
          <?php echo $text_telephone; ?> <?php echo $order_info['store_telephone']; ?><br />
          <?php if ($order_info['store_fax']) { ?>
          <?php echo $text_fax; ?> <?php echo $order_info['store_fax']; ?><br />
          <?php } ?>
          <?php echo $order_info['store_email']; ?><br />
          <?php echo $order_info['store_url']; ?></td>
        <td width="50%"><b><?php echo $text_to; ?>:</b><br /><?php echo $order_info['payment_address']; ?><br/>
          <?php echo $order_info['email']; ?><br/>
          <?php echo $order_info['telephone']; ?></td>
      </tr>
	</table>
	<table class="detail">
      <tr>
		<td><?php echo $text_payment_method; ?>: <b><?php echo $order_info['payment_method']; ?></b><?php if ($order_info['shipping_method']) { ?> <?php echo $text_shipping_method; ?>: <b><?php echo $order_info['shipping_method']; ?></b><?php } ?> <?php echo $text_order_id; ?>: <b><?php echo $order_info['order_id']; ?></b></td>
      </tr>
	</table>
	<?php $i = 1; ?>
	<table class="products">
      <tr class="heading">
        <td align="center"><?php echo $column_no; ?></td>
		<td><?php echo $column_product; ?></td>
        <td><?php echo $column_model; ?></td>
        <td align="center"><?php echo $column_quantity; ?></td>
        <td align="center"><?php echo $column_price; ?></td>
		<td align="center"><?php echo $column_total; ?></td>
        <td align="center"><?php echo $column_tax; ?></td>
		<td align="right"><?php echo $column_total_gross; ?></td>
      </tr>
      <?php foreach ($order_info['products'] as $product) { ?>
      <tr>
        <td align="center"><?php echo $i; ?></td>
		<td><?php echo $product['name'] . $product['option']; ?></td>
        <td><?php echo $product['model']; ?></td>
        <td align="center"><?php echo $product['quantity']; ?></td>
        <td align="center"><?php echo $product['price']; ?></td>
		<td align="center"><?php echo $product['total']; ?></td>
        <td align="center"><?php echo $product['tax']; ?></td>
		<td align="right"><?php echo $product['total_gross']; ?></td>
      </tr>
	  <?php $i++; ?>
      <?php } ?>
      <?php foreach ($order_info['vouchers'] as $voucher) { ?>
      <tr>
        <td align="center"><?php echo $i; ?></td>
		<td align="left"><?php echo $voucher['description']; ?></td>
        <td align="left"></td>
        <td align="center">1</td>
        <td align="center"><?php echo $voucher['amount']; ?></td>
        <td align="center"><?php echo $voucher['amount']; ?></td>
        <td align="center"></td>
		<td align="right"><?php echo $voucher['amount']; ?></td>
      </tr>
      <?php } ?>
	  <?php foreach ($order_info['totals'] as $key => $totals2) { ?>
	  <?php foreach ($totals2 as $total) { ?>
	  <tr>
	    <td align="right" class="total" colspan="7"><b><?php echo $total['title']; ?>:</b></td>
	    <td align="right"><?php echo $total['value']; ?></td>
	  </tr>
	  <?php } ?>
	  <?php } ?>
    </table>
	<table class="noborder">
	  <tr>
	    <td style="border: 1px solid #2b2b2b;" width="20%"><b><?php echo $text_total; ?>: <?php echo $order_info['total']; ?></b></td>
		<td><?php echo $text_paid; ?>: <?php echo $order_info['total']; ?></td>
	  </tr>
	</table>
    <table style="width: 80%; text-align: right;">
      <tr>
	    <td></td>
		<td width="25%" align="right">
	  <table class="authorized">
        <tr>
          <td align="center"><br /><br /><br /><b><?php echo $order_info['store_owner']; ?></b><?php echo $text_authorized; ?></td>
        </tr>
      </table>
	    </td>
	  </tr>
    </table>
  </div>
</body>
</html>