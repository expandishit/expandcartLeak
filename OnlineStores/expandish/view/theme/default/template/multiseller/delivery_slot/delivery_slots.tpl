
<?php include 'menu.tpl';?>

<div class="contentleft">
                 <div class="row">

                    <div class="col-md-12">
                        <div class="panel panel-white">
                            <div class="panel-heading">
                            </div>
                             <div class="col-md-12  <?= $direction == 'rtl' ? 'text-left' : 'text-right' ?> ">    
                                    <a href="<?= $this->url->link('seller/account-delivery-slot/addNewSlot', '', 'SSL')?>" style="color:#fff;" class="btn btn-primary" id="slots-submit-button"><i class="fa fa-plus"></i> <?= $text_new_delivery_slot?>
                                    </a>
                                </div>
                            <div class="panel-body">
                            
                                <div class="collapse in" id="panel1">
                                    <div class="form-group" id="">
                                        <div class="tabbable nav-tabs-vertical nav-tabs-right">
                                         <ul class="nav nav-tabs nav-tabs-highlight nav-tabs-lang">
                                        
                                               <?php foreach ($days as $key => $day){  ?>
                                                    <li class="<?php if($day == $currentDay){?> active <?php } ?>">
                                                        <a href="<?php echo $this->url->link('seller/account-delivery-slot/delivery_slots','day=' . $day.'&'.'day_id=' . $key, 'SSL'); ?>" aria-expanded="false">
                                                            <div><span> <?=$day?></span></div>
                                                        </a>
                                                    </li>
                                                    
                                              <?php } ?>
                                                
                                            </ul>
                                            <div class="tab-content">

                                                    <div class="tab-pane has-padding active" id="langTab">
                                                        <table id="datatableGrid" class="table table-hover datatable-highlight">
                                                            <thead>
                                                            <tr>
                                                              
                                                                <th>{{ lang('column_date') }}</th>
                                                                <th>{{ lang('column_slot') }}</th>
                                                                <th>{{ lang('column_orders_count') }}</th>
                                                                <th>{{ lang('column_orders_reserved') }}  </th>
                                                                <th>{{ lang('column_orders_available') }} </th>
                                                                <th>{{ lang('ms_action') }}</th>
                                                              
                                                            </tr>
                                                            </thead>
                                                             <tbody>
                                                            <?php if($slots) {?>
                                                                <?php foreach($slots as $slot) { ?> 
                                                                    <tr>  
                                                                        <td><?php echo $slot[day]; ?></td>
                                                                        <td><?php echo $slot[slot_desc]; ?></td>
                                                                        <td><?php echo $slot[total_orders]; ?></td>
                                                                        <td><?php echo $slot[reserved]; ?></td>
                                                                        <td><?php echo $slot[balance]; ?></td>
                                                                        <td>
                                                                        <a href="<?php echo $this->url->link('seller/account-delivery-slot/editSlot','slot_id=' . $slot[slot_id ], 'SSL'); ?>" class='ms-button ms-button-edit' title="edit"></a>
                                                                        <a  class='removeItem ms-button ms-button-delete' data-id="<?php echo $slot[slot_id];?>"></a>
                                                                        </td>
                                                                        
                                                                    </tr>
                                                                <?php } ?>
                                                            <?php } else {?>
                                                                <tr>
                                                                    <td colspan="4">{{ lang('text_no_results') }}</td>
                                                                </tr>
                                                        <?php }?>
                                                        </tbody>
                                                        </table>
                                                    </div>
                                            </div>

                                           
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
        url: 'index.php?route=seller/account-delivery-slot/deleteSlot',
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

