<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $lang; ?>" xml:lang="<?php echo $lang; ?>">
<head>
  <title><?php echo $title; ?></title>
  <base href="<?php echo $base; ?>" />
  <style>
    body {
      background: #FFFFFF;
    }
    body, td, th, input, select, textarea, option, optgroup {
      font-family: DejaVu Sans, sans;
      font-size: 12px;
      color: #000000;
      text-align: right;
    }
    h1 {
      text-transform: uppercase;
      color: #CCCCCC;
      text-align: right;
      font-size: 24px;
      font-weight: normal;
      padding-bottom: 5px;
      margin-top: 0px;
      margin-bottom: 15px;
      border-bottom: 1px solid #CDDDDD;
    }
    .store {
      width: 100%;
      margin-bottom: 20px;
    }
    .div2 {
      float: left;
      display: inline-block;
    }
    .div3 {
      float: right;
      display: inline-block;
      padding: 5px;
    }
    .heading td {
      background: #E7EFEF;
    }
    .address, .product {
      border-collapse: collapse;
    }
    .address {
      width: 100%;
      margin-bottom: 20px;
      border-top: 1px solid #CDDDDD;
      border-right: 1px solid #CDDDDD;
    }
    .address th, .address td {
      border-left: 1px solid #CDDDDD;
      border-bottom: 1px solid #CDDDDD;
      padding: 5px;
      vertical-align: text-bottom;
    }
    .address td {
      width: 50%;
    }
    .product {
      width: 100%;
      margin-bottom: 20px;
      border-top: 1px solid #CDDDDD;
      border-right: 1px solid #CDDDDD;
    }
    .product td {
      border-left: 1px solid #CDDDDD;
      border-bottom: 1px solid #CDDDDD;
      padding: 5px;
      direction: rtl;
    }

    .txtleft{
      text-align: left;
    }
  </style>
</head>
<body>
<table class="store">
  <tr>
      <td class="txtleft" style="vertical-align: middle;">
          <img src="<?php echo $logo;?>" alt="logo" style="height: 50px;">
      </td>
      <td align="right" valign="top" style="vertical-align: middle;width: 50%;">  
        <h1><?php echo $text_invoice; ?></h1>
      </td>
  </tr>
  <tr>
    <td align="right" valign="top" width="50%"><table width="100%">
        <tr>
          <td><?php echo $order_info['date_added']; ?></td>
          <td>:<b><?php echo $text_date_added; ?></b></td>
        </tr>
        <?php if ($order_info['invoice_no']) { ?>
        <tr>
          <td>#<?php echo $order_info['invoice_no']; ?></td>
          <td>:<b><?php echo $text_invoice_no; ?></b></td>
        </tr>
        <?php } ?>
        <tr>
          <td><?php echo $order_info['order_id']; ?></td>
          <td>:<b><?php echo $text_order_id; ?></b></td>
        </tr>
        <tr>
          <td><?php echo $order_info['payment_method']; ?></td>
          <td>:<b><?php echo $text_payment_method; ?></b></td>
        </tr>
        <?php if ($order_info['shipping_method']) { ?>
        <tr>
          <td><?php echo $order_info['shipping_method']; ?></td>
          <td>:<b><?php echo $text_shipping_method; ?></b></td>
        </tr>
        <?php } ?>
      </table></td>
      <td><?php echo $order_info['store_name']; ?><br />
      <?php echo $order_info['store_address']; ?><br />
      <?php echo $text_telephone; ?> <?php echo $order_info['store_telephone']; ?><br />
      <?php if ($order_info['store_fax']) { ?>
      <?php echo $text_fax; ?> <?php echo $order_info['store_fax']; ?><br />
      <?php } ?>
      <?php echo $order_info['store_email']; ?><br />
      <?php echo $order_info['store_url']; ?></td>
  </tr>
</table>
<table class="address">
  <tr class="heading">
    <td width="50%"><b><?php echo $text_ship_to; ?></b></td>
    <td width="50%"><b><?php echo $text_to; ?></b></td>
  </tr>
  <tr>
    <td><?php echo $order_info['shipping_address']; ?></td>
    <td><?php echo $order_info['payment_address']; ?><br/>
      <?php echo $order_info['email']; ?><br/>
      <?php echo $order_info['telephone']; ?></td>
  </tr>
</table>
<table class="product">
  <tr class="heading">
    <td align="right"><b><?php echo $column_total; ?></b></td>
    <td align="right"><b><?php echo $column_price; ?></b></td>
    <td align="right"><b><?php echo $column_quantity; ?></b></td>
    <td><b><?php echo $column_model; ?></b></td>
    <td><b><?php echo $column_product; ?></b></td>
  </tr>
  <?php foreach ($order_info['products'] as $product) { ?>
  <tr>
    <td align="right"><?php echo $product['total']; ?></td>
    <td align="right"><?php echo $product['price']; ?></td>
    <td align="right"><?php echo $product['quantity']; ?></td>
    <td><?php echo $product['model']; ?></td>
    <td><?php echo $product['name'] . $product['option']; ?></td>
  </tr>
  <?php } ?>
  <?php foreach ($order_info['vouchers'] as $voucher) { ?>
  <tr>
    <td align="right"><?php echo $voucher['amount']; ?></td>
    <td align="right"><?php echo $voucher['amount']; ?></td>
    <td align="right">1</td>
    <td align="left"></td>
    <td align="left"><?php echo $voucher['description']; ?></td>
  </tr>
  <?php } ?>
  <?php foreach ($order_info['totals'] as $totals2) { ?>
  <?php foreach ($totals2 as $total) { ?>
  <tr>
    <td align="right"><?php echo $total['value']; ?></td>
    <td align="right" colspan="4"><b><?php echo $total['title']; ?>:</b></td>
  </tr>
  <?php } ?>
  <?php } ?>
</table>
<?php if ($order_info['comment']) { ?>
<table>
<tr class="heading">
  <td><b><?php echo $column_comment; ?></b></td>
</tr>
<tr>
  <td><?php echo str_replace('\n', '<br>', $order_info['comment']); ?></td>
</tr>
</table>
<?php } ?>
</body>
</html>
