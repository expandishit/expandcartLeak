<?php if(in_array(ucfirst('google map location'),$seller_show_fields)): ?>
<input type="hidden" id="coordinates" name="seller[seller_location]" value="<?php echo $seller['ms.google_map_location']; ?>"  class="form-control"/>
<?php endif; ?>
