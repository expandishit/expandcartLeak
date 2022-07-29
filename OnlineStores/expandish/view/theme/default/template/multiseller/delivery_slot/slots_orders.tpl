
<?php include 'menu.tpl';?>

<div class="contentleft">
                 <table class="list" id="list-products">
        <thead>
            <tr>
                <td>{{ lang('column_order_id') }}</td>
                <td>{{ lang('entry_day') }}</td>
                <td> {{ lang('column_slot') }}</td>
                <td>{{ lang('column_delivery_date') }}</td>
                <td>{{ lang('ms_status') }}</td>
                <td>{{ lang('ms_action') }}</td>
            </tr>
        </thead>
         <tbody>
        <?php if($order_slots) {?>
            <?php foreach($order_slots as $orderSlot) { ?> 
                <tr>  
                    <td><a href="<?php echo $this->url->link('seller/account-order/viewOrder','order_id=' . $orderSlot['order_id'] , 'SSL'); ?>" ># <?php echo $orderSlot[order_id]; ?></a> </td>
                    <td><?php echo $orderSlot[day_name]; ?></td>
                    <td><?php echo $orderSlot[delivery_date]; ?></td>
                    <td><?php echo $orderSlot[slot_description]; ?></td>
                    <td><?php echo $orderSlot[status]; ?></td>
                    <td>
                    <a  class='removeItem ms-button ms-button-delete' data-id="<?php echo $orderSlot[ds_delivery_slot_order_id];?>"></a>
                    </td>
                    
                </tr>
            <?php } ?>
        <?php } else {?>
            <tr>
                <td colspan="6">{{ lang('text_no_results') }}</td>
            </tr>
    <?php }?>
    </tbody>
    </table>
 
</div>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
 // Delete 
 $('.removeItem').click(function(){
   var el = this;
   // Delete id
   var deleteid = $(this).data('id');
 swal({
        title: '{{ lang('ms_account_delivery_slot_confirmdelete') }}',
        icon: 'warning',
        buttons: ["{{ lang('ms_account_delivery_slot_confirmdeleteCancelBTN') }}", "{{ lang('ms_account_delivery_slot_confirmdeleteBTN') }}"],
    }).then((result) => {
   if (result == true) {
      // AJAX Request
      $.ajax({
        url: 'index.php?route=seller/account-delivery-slot/deleteSlotOrder',
        type: 'POST',
        data: { id:deleteid },
        success: function(response){
          if(response){
	    // Remove row from HTML Table
	    $(el).closest('tr').css('background','tomato');
	    $(el).closest('tr').fadeOut(800,function(){
	       $(this).remove();
	    });
          }else{
	    alert('Invalid ID.');
          }

        }
      });
   } });

 });

});

</script>
<?php echo $footer; ?>

