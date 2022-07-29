<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>" xml:lang="<?php echo $lang; ?>">
<head>
<title><?php echo $title; ?></title>
<base href="<?php echo $base; ?>" />
<style>
body {
	background: #FFFFFF;
	margin: 0px;
	padding: 0px;
}
body, td, th, input, select, textarea, option, optgroup {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #5c5c5c;
}
.container {
	width: 94%;
	margin-left: auto;
	margin-right: auto;
	clear: both;
	position: relative;
}
.logo {
	text-align: right;
}
h1 {
	text-align: left;
	font-size: 16px;
	font-weight: normal;
	padding: 0px;
	margin-top: 0px;
	margin-bottom: 3px;
}
h2 {
	color: #FFFFFF;
	text-align: center;
	font-size: 21px;
	font-weight: normal;
	padding: 10px 18px;
	margin-top: 30px;
	margin-bottom: 45px;
	background: #ff2a00;
	display: block;
}
.address, .products, .totals {
	border-collapse: collapse;
	margin-top: 20px;
}
.address .heading td {
	background: #ffffff;
	font-size: 14px;
	padding-bottom: 10px;
}
.address {
	margin-bottom: 20px;
	border: none;
	width: 100%;
}
.address th, .address td {
	border: none;
	padding: 0px;
	font-size: 13px;
	vertical-align: text-bottom;
}
.address td {
	width: 50%;
}
.products {
	width: 100%;
	margin-bottom: 5px;
	border-top: 1px solid #ebebeb;
	border-right: 1px solid #ebebeb;
}
.products .heading td {
	text-transform: uppercase;
	background: #424242;
	font-size: 12px;
	color: #ffffff;
	font-weight: normal;
	padding: 14px 12px;
}
.products td {
	border-left: 1px solid #ebebeb;
	border-bottom: 1px solid #ebebeb;
	padding: 14px 12px;
}
.totals {
	margin-bottom: 10px;
	border: none;
	display: inline-table;
}
.totals td {
	border-bottom: 1px solid #ebebeb;
	padding: 10px;
	font-size: 13px;
}
.totals td.total {
	font-weight: bold;
	font-size: 16px;
}
.comment {
	margin-top: 30px;
	width: 40%;
}
</style>
</head>
<body>
  <img src="<?php echo $logo; ?>" class="logo" />
  <h2><?php echo $text_paid; ?></h2>
  <div class="container">
    <h1><?php echo $text_invoice; ?><?php if ($order_info['invoice_no']) { ?> <?php echo $order_info['invoice_no']; ?><?php } ?></h1>
	<?php echo $text_invoice_date; ?> <?php echo $order_info['invoice_date']; ?>
	<table class="address">
      <tr class="heading">
        <td width="50%"><b><?php echo $text_from; ?>:</b></td>
        <td width="50%"><b><?php echo $text_to; ?>:</b></td>
      </tr>
      <tr>
	    <td><?php echo $order_info['store_name']; ?><br />
          <?php echo $order_info['store_address']; ?><br />
          <?php echo $text_telephone; ?> <?php echo $order_info['store_telephone']; ?><br />
          <?php if ($order_info['store_fax']) { ?>
          <?php echo $text_fax; ?> <?php echo $order_info['store_fax']; ?><br />
          <?php } ?>
          <?php echo $order_info['store_email']; ?><br />
          <?php echo $order_info['store_url']; ?></td>
        <td><?php echo $order_info['payment_address']; ?><br/>
          <?php echo $order_info['email']; ?><br/>
          <?php echo $order_info['telephone']; ?></td>
      </tr>
	</table>
	<table class="products">
      <tr class="heading">
        <td><?php echo $column_product; ?></td>
        <td><?php echo $column_model; ?></td>
        <td align="center"><?php echo $column_quantity; ?></td>
        <td align="center"><?php echo $column_price; ?></td>
        <td align="center"><?php echo $column_tax; ?></td>
        <td align="right"><?php echo $column_total; ?></td>
      </tr>
      <?php foreach ($order_info['products'] as $product) { ?>
      <tr>
        <td><?php echo $product['name'] . $product['option']; ?></td>
        <td><?php echo $product['model']; ?></td>
        <td align="center"><?php echo $product['quantity']; ?></td>
        <td align="center"><?php echo $product['price']; ?></td>
        <td align="center"><?php echo $product['tax']; ?></td>
        <td align="right"><?php echo $product['total']; ?></td>
      </tr>
      <?php } ?>
      <?php foreach ($order_info['vouchers'] as $voucher) { ?>
      <tr>
        <td align="left"><?php echo $voucher['description']; ?></td>
        <td align="left"></td>
        <td align="center">1</td>
        <td align="center"><?php echo $voucher['amount']; ?></td>
        <td align="center"></td>
        <td align="right"><?php echo $voucher['amount']; ?></td>
      </tr>
      <?php } ?>
    </table>
    <div style="text-align: right;">
      <table width="100%">
	    <tr>
		  <td> </td>
		  <td align="right" style="width: 25%;">
		    <table class="totals" style="text-align: right;">
		      <?php foreach ($order_info['totals'] as $key => $totals2) { ?>
		      <?php foreach ($totals2 as $total) { ?>
		      <tr>
		        <td align="right" style="white-space: nowrap;" class="<?php echo $key; ?>"><?php echo $total['title']; ?>:</td>
		        <td align="right" style="white-space: nowrap;"class="<?php echo $key; ?>"><?php echo $total['value']; ?></td>
		      </tr>
		      <?php } ?>
		      <?php } ?>
		    </table>
		  </td>
		</tr>
      </table>
    </div>
    <table class="comment">
      <?php if ($order_info['comment']) { ?>
	  <tr>
        <td colspan="2"><b><?php echo $column_comment; ?></b></td>
      </tr>
      <tr>
        <td colspan="2"><?php echo $order_info['comment']; ?></td>
      </tr>
	  <?php } ?>
	  <tr>
        <td><b><?php echo $text_tax_amount; ?></b>:</td>
        <td><?php echo $order_info['tax_amount']; ?></td>
	  </tr>
	  <tr>
        <td><b><?php echo $text_payment_method; ?></b>:</td>
        <td><?php echo $order_info['payment_method']; ?></td>
	  </tr>
    </table>
  </div>
</body>
</html>