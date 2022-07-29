<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $title; ?></title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
<div style="width: 680px;"><a href="<?php echo $store_url; ?>" title="<?php echo $store_name; ?>"><img src="<?php echo $logo; ?>" alt="<?php echo $store_name; ?>" style="margin-bottom: 20px; border: none;height:<?php echo $logo_height;?>px" /></a>
  <p style="margin-top: 0px; margin-bottom: 20px;"><?php echo $text_greeting; ?></p>
  <?php if ($customer_id) { ?>
  <p style="margin-top: 0px; margin-bottom: 20px;"><?php echo $text_link; ?></p>
  <p style="margin-top: 0px; margin-bottom: 20px;"><a href="<?php echo $link; ?>"><?php echo $link; ?></a></p>
  <?php } ?>
  
  <h2><?php echo $text_payThem_purchases; ?></h2>
  <?php foreach ($payThem_purchases as $purchase) { ?>
  <ul>
    <li><?php echo $text_payThem_transactionid . $purchase['TRANSACTION_ID']; ?></li>
    <li><?php echo $text_voucher_qty . $purchase['TRANSACTION_VOUCHER_QUANTITY']; ?></li>
    <li><?php echo $text_transaction_date . $purchase['TRANSACTION_DATE']; ?></li>
    <li><?php echo $text_tranaction_value . $purchase['TRANSACTION_VALUE'] . ': ' . $purchase['TRANSACTION_CURRENCY']; ?></li>
    <li><?php echo $text_purchase_name . $purchase['OEM_PRODUCT_Name']; ?></li>
    <li><?php echo $text_brand_name . $purchase['OEM_BRAND_Name']; ?></li>
    <li><?php echo $text_vvssku . $purchase['OEM_PRODUCT_VVSSKU']; ?></li>
    <li><?php echo $purchase['OEM_PRODUCT_RedemptionInstructions']; ?></li>
  </ul>
    
    <table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
      <thead>
        <tr>
          <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;"><?php echo $text_voucher_id; ?></td>
          <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;"><?php echo $text_voucher_pin; ?></td>
          <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;"><?php echo $text_voucher_serial; ?></td>
          <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;"><?php echo $text_sales_ref; ?></td>
          <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;"><?php echo $text_expire_date; ?></td>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($purchase['VOUCHERS'] as $voucher) { ?>
        <tr>
          <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;"><?php echo $voucher['OEM_VOUCHER_ID']; ?></td>
          <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;"><?php echo $voucher['OEM_VOUCHER_PIN']; ?></td>
          <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;"><?php echo $voucher['OEM_VOUCHER_SERIAL']; ?></td>
          <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;"><?php echo $voucher['OEM_VOUCHER_SALES_REFERENCE']; ?></td>
          <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;"><?php echo $voucher['OEM_VOUCHER_EXPIRATION_DATE']; ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  <?php } ?>
  <br>
  <?php if ($comment) { ?>
  <table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
    <thead>
      <tr>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;"><?php echo $text_instruction; ?></td>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php echo $comment; ?></td>
      </tr>
    </tbody>
  </table>
  <?php } ?>
 
  <p style="margin-top: 0px; margin-bottom: 20px;"><?php echo $text_footer; ?></p>
</div>
</body>
</html>
