<style>
form {
    display:block;
}
</style>

<script>
    var wpwlOptions = {
        style: "<?php echo $formStyle ?>"
    }
</script>

<script>
$.ajaxSetup({
    // Disable caching of AJAX responses
    cache: true
});
</script>

<script src="<?php echo $scriptURL;?>"></script>

<div>
    <form action="<?php echo $postbackURL;?>" class="paymentWidgets">
      <?php echo $payment_brands;?>
    </form>    
</div>
<div class="buttons" style="display:none;">
  <div class="right"><a id="button-confirm" class="button" onclick="$('#payment').submit();"><span><?php echo $button_confirm; ?></span></a></div>
</div>
