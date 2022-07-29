<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>
#body {
  background: #FFFFFF;
}
#body,#body td,#body th,#body input,#body select,#body textarea,#body option,#body optgroup {
  font-family: DejaVu Sans, sans;
  font-size: 12px;
  color: #2b2b2b;
}
</style>
</head>
<body id="body">
<div class="cet_container">
  <table style="border-collapse: collapse; width: 100%; border: none; background: #000000;">
    <tr>
      <!-- <img src="{logo}" alt="{store_name}" style="margin-bottom: 0px; border: none;" /> -->
	  <td style="padding: 20px 15px 15px 15px; background: #000000;" width="50%"><a href="{store_url}" title="{store_name}">{logo}</a></td>
	  <td style="padding: 20px 15px 15px 15px; background: #000000;" width="50%" align="right">Follow us: <a href="http://facebook.com">Facebook</a> <a href="http://google.com">Google</a> <a href="http://twitter.com">Twitter</a></td>
	</tr>
	<tr>
	  <td colspan="2" valign="top">
	    <table style="border-collapse: collapse; width: 100%; border: none; background: #363636;">
	      <tr>
	        <td height="33" style="text-align: center; background: #363636;" width="110"><a href="https://www.expandcart.com" title="Category 1" style="line-height: 33px; text-decoration: none;">Category 1</a></td>
			<td width="1" height="33" style="font-weight: 300; vertical-align: top; padding: 0; margin: 0; background: #5e5e5e;"></td>
			<td height="33" style="text-align: center; background: #363636;" width="110"><a href="https://www.expandcart.com" title="Category 2" style="line-height: 33px; text-decoration: none;">Category 2</a></td>
			<td width="1" height="33" style="font-weight: 300; vertical-align: top; padding: 0; margin: 0; background: #5e5e5e;"></td>
			<td height="33" style="text-align: center; background: #363636;" width="110"><a href="https://www.expandcart.com" title="Category 3" style="line-height: 33px; text-decoration: none;">Category 3</a></td>
			<td width="1" height="33" style="font-weight: 300; vertical-align: top; padding: 0; margin: 0; background: #5e5e5e;"></td>
			<td height="33" style="text-align: center; background: #363636;" width="110"><a href="https://www.expandcart.com" title="Category 4" style="line-height: 33px; text-decoration: none;">Category 4</a></td>
			<td width="1" height="33" style="font-weight: 300; vertical-align: top; padding: 0; margin: 0; background: #5e5e5e;"></td>
			<td height="33" style="text-align: center; background: #363636;" width="110"><a href="https://www.expandcart.com" title="Category 5" style="line-height: 33px; text-decoration: none;">Category 5</a></td>
	      </tr>
		</table>
	  </td>
	</tr>
  </table>
  <div style="padding: 20px 20px 15px 20px;">
    <p style="margin: 0px; padding: 0px; text-transform: uppercase; font-weight: 500; font-size: 28px; line-height: 33px; text-align: center;">HI {firstname} {lastname},</p>
    <p style="margin: 0px; padding: 0px; text-transform: uppercase; font-weight: 500; font-size: 16px; line-height: 25px; text-align: center;">THANK YOU FOR SHOPPING WITH YOUR BRAND!</p>
    <table style="border-collapse: collapse; width: 100%; border-top: 1px solid #eeeeee; border-left: 1px solid #eeeeee; margin-top: 30px; margin-bottom: 10px;">
      <thead>
        <tr>
          <td style="font-size: 14px; border-right: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee; background-color: #fbfbfb; font-weight: bold; text-align: left; padding: 11px; color: #555454;">ORDER DETAILS</td>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="background-color: #fbfbfb; font-size: 12px; border-right: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee; text-align: left; padding: 7px;"><b>Order ID:</b> {order_id} Placed on {order_date}<br />
            <b>Payment method:</b> {payment}<br />
			<b>Shipping method:</b> {shipment}</td>
        </tr>
      </tbody>
    </table>
    {products}
    <table style="border-collapse: collapse; width: 100%; border: none; margin-top: 10px; margin-bottom: 10px;">
      <tbody>
        <tr>
          <td valign="top" width="49%">
		    <table style="border-collapse: collapse; width: 100%; border-top: 1px solid #eeeeee; border-left: 1px solid #eeeeee; margin-bottom: 10px;">
		      <thead>
		        <tr>
		          <td style="font-size: 14px; border-right: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee; background-color: #fbfbfb; font-weight: bold; text-align: left; padding: 11px; color: #555454;">DELIVERY ADDRESS</td>
		        </tr>
		      </thead>
		      <tbody>
		        <tr>
		          <td style="background-color: #fbfbfb; font-size: 12px; border-right: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee; text-align: left; padding: 7px;">{delivery_address}</td>
		        </tr>
		      </tbody>
		    </table>
		  </td>
          <td valign="top" width="49%">
		    <table style="border-collapse: collapse; width: 100%; border-top: 1px solid #eeeeee; border-left: 1px solid #eeeeee; margin-bottom: 10px;">
		      <thead>
		        <tr>
		          <td style="font-size: 14px; border-right: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee; background-color: #fbfbfb; font-weight: bold; text-align: left; padding: 11px; color: #555454;">BILLING ADDRESS</td>
		        </tr>
		      </thead>
		      <tbody>
		        <tr>
		          <td style="background-color: #fbfbfb; font-size: 12px; border-right: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee; text-align: left; padding: 7px;">{payment_address}</td>
		        </tr>
		      </tbody>
		    </table>
		  </td>
        </tr>
      </tbody>
    </table>
    <p style="margin-top: 0px; margin-bottom: 5px;">You can review your order and download your invoice from the "<a href="{store_url}index.php?route=account/order">Order history</a>" section of your customer account by clicking "<a href="{store_url}index.php?route=account/account">My account</a>" on our shop.</p>
  </div>
  <table style="border-collapse: collapse; width: 100%; border: none; background: #000000;">
    <tr>
	  <td style="padding: 20px 15px 15px 15px; background: #000000;" width="50%"><a href="{store_url}" title="{store_name}">{logo}</a></td>
	  <td style="padding: 20px 15px 15px 15px; background: #000000;" width="50%" align="right">2015 MyStore, All right reserved.</td>
	</tr>
  </table>
</div>
&lt;style&gt;
.cet_container { border: 1px solid #ccc; border-radius: 5px; }
&lt;/style&gt;
</body>
</html>