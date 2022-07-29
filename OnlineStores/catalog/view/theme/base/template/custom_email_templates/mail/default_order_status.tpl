<div class="cet_container"><a href="{store_url}" title="{store_name}"><img src="{logo}" alt="{store_name}" style="margin-bottom: 20px; border: none;" /></a>
  <p style="margin-top: 0px; margin-bottom: 20px;">Thank you for your interest in {store_name} products. Your order has been received and will be processed once payment has been confirmed.</p>
  <p style="margin-top: 0px; margin-bottom: 20px;">To view your order click on the link below:</p>
  <p style="margin-top: 0px; margin-bottom: 20px;">{order_link}</p>
  <table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
    <thead>
      <tr>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2">Order details</td>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><b>Order ID:</b> {order_id}<br />
          <b>Date added:</b> {order_date}<br />
          <b>Payment method:</b> {payment}<br />
          <b>Shipping method:</b> {shipment}</td>
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><b>Email:</b> {email}<br />
          <b>Telephone:</b> {telephone}<br />
          <b>IP:</b> {ip}<br />
          <b>Order status:</b> {status_name}</td>
      </tr>
    </tbody>
  </table>
  {comment}
  <table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
    <thead>
      <tr>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Payment Address</td>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Shipping Address</td>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">{payment_address}</td>
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">{shipping_address}</td>
      </tr>
    </tbody>
  </table>
  {products}<br />
  <br />
  <p style="margin-top: 0px; margin-bottom: 20px;">Please reply to this email if you have any questions.<br /><br />2015 MyStore, All right reserved.</p>
</div>