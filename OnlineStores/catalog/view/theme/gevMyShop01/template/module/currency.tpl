<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" name="formcur" id="formcur">
<div id="currency"><?php echo $text_currency; ?>:&nbsp; 

<?php 
$a = 0; 
foreach ($currencies as $currency) { 
    $thisCurTitle[$a] = $currency['title']; 
    $thisCurCode[$a] = $currency['code']; 
    if ($currency['symbol_left']) { 
        $thisCurSymb[$a] = $currency['symbol_left']; 
    } else { 
        $thisCurSymb[$a] = $currency['symbol_right']; 
    } 
    $a++; 
} 
?>
<select name="curselect" id="curselect" onchange="$('input[name=\'currency_code\']').attr('value', document.getElementById('curselect').value); document.forms['formcur'].submit();">
    
<?php 
for ($z = 0; $z <= $a - 1; $z++) { 
    if ($thisCurCode[$z] == $currency_code) { ?>
<option value="<?php echo $thisCurCode[$z]; ?>" selected><?php echo $thisCurCode[$z]; ?></option>
<?php 
} else { 
?>
<option value="<?php echo $thisCurCode[$z]; ?>"><?php echo $thisCurCode[$z]; ?></option>
<?php } ?>
<?php } ?>
</select>
<input type="hidden" name="currency_code" value="" />
<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
</div>
</form>