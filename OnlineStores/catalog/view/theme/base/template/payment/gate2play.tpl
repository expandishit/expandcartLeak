<div>
<?php
$i = 0;
$output = '';
foreach ($paymentMethods as $payment) {
    if($payment == 999 && $testmode != 1) {
        continue;
    }
?>                
<iframe frameborder=0 scrolling='no' src='<?php echo $iframe_url.$payment; ?>' width='<?php echo $width; ?>' height='<?php echo $height; ?>' scrolling='auto' frameborder='0'></iframe>
<?php
    $i++;
    if ($i % 3 == 0) {
        $output .= '<div style="clear:both;"></div>';
    }            
} 
?>      
</div>
<div class="buttons" style="display:none;">
  <div class="right"><a id="button-confirm" class="button" onclick="$('#payment').submit();"><span><?php echo $button_confirm; ?></span></a></div>
</div>