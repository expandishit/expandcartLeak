<?php include 'menu.tpl';?>
<div class="contentleft">
 
                 <div class="row">
                   <form id="slot_form">
                   <input type="hidden" name="slot_id" value="<?php echo $slot[ds_delivery_slot_id]; ?>">
                    <div class="modal-header">
                        <h5><?= $text_new_delivery_slot?></h5>
                       
                    </div>
                    <div class="modal-body">
                     <div class="alert alert-success" id="successfull">
                    <strong><?php echo $ms_success; ?></strong> 
                     </div>
                      <div class="col-md-12" id="error-area">
                      <div class="alert alert-danger alert-styled-left alert-bordered">
                        <p class="error_text"></p>
                        </div>
                        </div>
                     <div class="form-group">
                            <label for="ds_name_date" class="control-label">{{lang('entry_delivery_slot')}} <span class="required ">*</span> </label>
                            <input id="ds_name_date" name="delivery_slot" value="<?=$slot[delivery_slot]; ?>" class="form-control"  />
                            <span class="help-block"></span>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ds_time_start" class="control-label">{{lang('entry_time_start')}} <span class="required ">*</span>  </label>
                                    <input id="slot_time_start" name="time_start" value="<?=$slot[time_start_formated]; ?>" type="text" class="form-control time "  />
                                    <span class="help-block"></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ds_time_end" class="control-label">{{lang('entry_time_end')}} <span class="required ">*</span>  </label>
                                    <input id="slot_time_end"  name="time_end" value="<?=$slot[time_end_formated]; ?>" type="text" class="form-control time"   />
                                    <span class="help-block"></span>
                                </div>
                            </div>
                        </div>
                         <div class="row">
                        <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ lang('entry_day') }}</label>
                        <select class="form-control" name="day_id" >
                        <option value="">{{lang('select_option')}} </option>
                        <?php if($days){
                         foreach ($days as $key => $day){  ?>
                                <option value="<?php echo $key; ?>"  <?php echo ($key == $slot[ds_day_id]) ? 'selected':''; ?>> <?php echo $day; ?></option>
                        <?php } } ?>
                        </select>
                        <span class="help-block"></span>
                    </div>
                </div>
                      <div class="col-md-6">
                        <div class="form-group">
                            <label for="ds_orders_slot" class="control-label">{{lang('entry_orders_count')}} <span class="required ">*</span>  </label>
                            <input id="ds_orders_slot" name="total_orders" value="<?=$slot[total_orders]; ?>" class="form-control touchy" type="number" />
                            <span class="help-block"></span>
                        </div>
                        </div>
                        </div>

                        <div class="form-group">
                        <label><?=$ms_status?></label><br/>
                        <label class="switch cut_off">
                        <input type="checkbox" name="status" value="1"
                        <?= $slot['status'] == '1' ? 'checked' : '' ?>>
                        <span class="slider round"></span>
                        </label>
                        </div>
                       

                    </div>
                    <div class="modal-footer">
                        <a id="slot-submit-button" class="btn btn-primary"><i class="fa fa-save"></i> {{ lang('button_save') }}</a>
                    </div>
                </form>
                </div>
</div>


<script type="text/javascript">

        $('#error-area').hide();
        $('#successfull').hide();
		$("#slot-submit-button").click(function() {
		$('.success').remove();
        $('#error-area').hide();
        $( ".error_text" ).html('<p class=""></p>');
		var button = $(this);
		var id = $(this).attr('id');
		
		$.ajax({
			type: "POST",
			dataType: "json",
			url: $('base').attr('href') + 'index.php?route=seller/account-delivery-slot/storeSlot',
			data: $("form#slot_form").serialize(),
			beforeSend: function() {
				button.hide().before('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
				$('p.error').remove();
			},
			complete: function(jqXHR, textStatus) {
				if (textStatus != 'success') {
					button.show().prev('span.wait').remove();
					$(".warning.main").text(msGlobals.formError).show();
					window.scrollTo(0,0);
				}
			},  
			success: function(jsonData) {	
                if(jsonData.success){
                     $('#error-area').hide();
                    $('#slot-submit-button').show().prev('span.wait').remove();
                     $("#successfull").show().delay(5000).fadeOut();
                   
                    }
                else{
				if (!jQuery.isEmptyObject(jsonData.errors)) {
					$('#slot-submit-button').show().prev('span.wait').remove();
                     $('#successfull').hide();
                    $('#error-area').show();
                    $(".error_text").show();    
					for (error in jsonData.errors) {
                     $( ".error_text" ).append('<p class="">-' + jsonData.errors[error] + '</p>');
                    }
					window.scrollTo(0,0);
				} else {
					window.location = jsonData.redirect;
				}
                }
	       	}
		});
	});
    </script>

<?php echo $footer; ?>

