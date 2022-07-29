

     <table class="list" id="list-products">
        <thead>
            <tr>
                <td><?php echo $column_bidder_name; ?></td>
                <td><?php echo $column_amount; ?></td>
                <td><?php echo $column_date_added; ?></td>
                <td class="large"><?php echo $ms_account_products_action; ?></td>
          
            </tr>
        </thead>
         <tbody>
                <?php if($bids) {?>
                     <?php foreach($bids as $bid) { ?>
                        <tr id="<?php echo $bid[bid_id];?>">  
                            <td>
                              <?php echo $bid[bidder_name]; ?>
                            </td>
                            <td><?php echo $bid[amount]; ?></td>
                            <td><?php echo $bid[created_at]; ?></td>
                            <td>
                                <a class='removeItem ms-button ms-button-delete' data-id="<?php echo $bid[bid_id];?>"></a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else {?>
                    <tr>
                        <td colspan="8">{{ lang('text_no_results') }}</td>
                    </tr>
               <?php }?>
            </tbody>
    </table>


<script type="text/javascript">

$(document).ready(function(){

 // Delete 
 $('.removeItem').click(function(){
   var el = this;
  
   // Delete id
   var deleteid = $(this).data('id');
 
   var confirmalert = confirm("Are you sure?");
   if (confirmalert == true) {
      // AJAX Request
      $.ajax({
        url: 'index.php?route=seller/account-auctions/deleteBid',
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
   }

 });

});
</script>
