<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
<h1><?php echo 'Your payment '.$x_response_reason_text; ?></h1>
<?php if($x_response_code == '0') { ?>
<p>Your payment was processed successfully. Here is your receipt:</p>

<?php if(!empty($vpc_TransactionNo)) { ?>
<p>
Payment Status: <?php echo $x_response_reason_text; ?><br/>
ReceiptNo: <?php echo $vpc_ReceiptNo; ?><br/>
TransactionNo: <?php echo $vpc_TransactionNo; ?> 
</p>
<?php } ?>
<div class="buttons">
  <table>
    <tr>
      <td align="left"></td>
      <td align="right"><a href="<?php echo $confirm; ?>" class="button"><?php echo $button_confirm; ?></a></td>
    </tr>
  </table>
</div>
<?php } else { ?>
<p>Your payment failed.  Please try again later.</p>
<pre>
<?php echo $x_response_reason_text; ?>
</pre>
<div class="buttons">
  <table>
    <tr>
      <td align="left"><a href="<?php echo $back; ?>" class="button"><?php echo $button_back; ?></a></td>
      <td align="right"></td>
    </tr>
  </table>
</div>
<?php } ?>
<?php echo $content_bottom; ?>
</div>