<?php include 'menu.tpl';?>
<div class="contentleft">

   <div class="row paddingTop">
    <div class="alert alert-success" id="successfull">
         <strong><?php echo $ms_success; ?></strong> 
            </div>
        <form class="form" id="ms-slots">
      <div class="col-md-4">
         <div class="col-md-12">
            <div class="form-group">
               <label><?=$entry_checkout_required?></label><br/>
               <label class="switch">
               <input type="checkbox" name="required" 
               <?= $deliverySlotSettings['required'] == 'on' ? 'checked' : '' ?>>
               <span class="slider round"></span>
               </label>
            </div>
         </div>
         <div class="col-md-12">
            <div class="form-group">
               <label><?=$entry_delivery_cutOff?></label><br/>
               <label class="switch cut_off">
               <input type="checkbox" name="cutoff"
               <?= $deliverySlotSettings['cutoff'] == 'on' ? 'checked' : '' ?>>
               <span class="slider round"></span>
               </label>
            </div>
         </div>
      </div>
      
      <div class="col-md-8">
         <div class="form-group col-md-6">
            <label class="control-label">
           <?=$entry_max_day?>
            </label>
            <input type="number" name="slot_max_day" class="form-control touchy" min="0"
               value="<?=$deliverySlotSettings['slot_max_day']?>"/>
         </div>
         <div class="form-group col-md-6" id="delivery_slot_calendar_type-group">
            <label for="delivery_slot_calendar_type" class="control-label">  <?=$entry_calendar_style?> </label>
            <select name="delivery_slot_calendar_type" id="delivery_slot_calendar_type" class="form-control select">
            <option value="default"
            <?= $deliverySlotSettings['delivery_slot_calendar_type'] == 'default' ? 'selected' : '' ?>>
            <?= $text_default?>
            </option>
            <option value="advanced"
            <?= $deliverySlotSettings['delivery_slot_calendar_type'] == 'advanced' ? 'selected' : ''?>>
              <?= $text_advanced?>
            </option>
            </select>
            <span class="help-block"><?=$entry_calendar_style_help?></span>
         </div>
      </div>
     <div class="cutoff_slot col-md-12" <?= $deliverySlotSettings['cutoff'] == 'on' ? 'style="display:block"' : 'style="display:none"' ?>>
                                <div class="form-group col-md-3">
                                    <label class="control-label">
                                        <?=$entry_time_start?> <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="slot_time_start" name="slot_time_start" class="form-control time"
                                           value="<?=$deliverySlotSettings['slot_time_start']?>"/>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="control-label">
                                        <?=$entry_time_end?><span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="slot_time_end" name="slot_time_end" class="form-control time"
                                           value="<?=$deliverySlotSettings['slot_time_end']?>"/>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="control-label">
                                    <?=$entry_day_index?> <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" min="0" name="slot_day_index" class="form-control touchy"
                                           value="<?=$deliverySlotSettings['slot_day_index']?>"/>

                                    <span class="help-block"><?=$entry_day_index_note?></span>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="control-label">
                                        <?=$entry_other_time?> <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" min="0" name="slot_other_time" class="form-control touchy"
                                           value="<?=$deliverySlotSettings['slot_other_time']?>"/>
                                </div>
                            </div>
               </div>
            <div class="row">
               <div class="col-md-12  <?= $direction == 'rtl' ? 'text-left' : 'text-right' ?> ">
                  <hr/>
                  <a class="btn btn-primary" id="slots-submit-button">
                  <i class="fa fa-save"></i> {{ lang('button_save') }}
                  </a>
               </div>
         </div>
         </div>


<?php echo $footer; ?>
<script type="text/javascript">

 $(document).ready(function(){
       $(".cut_off").change(function(){
       $(".cutoff_slot").toggle();
       });
   });
        $('#successfull').hide();
		$("#slots-submit-button").click(function() {
		$('.success').remove();
		var button = $(this);
		$.ajax({
			type: "POST",
			dataType: "json",
			url: $('base').attr('href') + 'index.php?route=seller/account-delivery-slot/storeSettings',
			data: $("form#ms-slots").serialize(),
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
                    $('#slots-submit-button').show().prev('span.wait').remove();
                     $("#successfull").show().delay(3000).fadeOut();
                     $('#slots-submit-button').show();
                    }
	       	}
		});
	});

</script>
