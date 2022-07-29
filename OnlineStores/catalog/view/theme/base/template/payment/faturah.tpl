<div class="buttons">
  <div class="pull-right">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="btn btn-primary" data-loading-text="<?php echo $text_loading; ?>" />
  </div>
</div>
<script type="text/javascript">
$('#button-confirm').on('click', function() {
    document.location.href="<?php echo $sendurl; ?>";	
});
</script>