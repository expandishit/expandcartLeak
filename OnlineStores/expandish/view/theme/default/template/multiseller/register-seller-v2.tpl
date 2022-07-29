<?php echo $header; ?>
<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <script>
    $(document).ready(function (){
        $('#colorbox, #cboxOverlay').remove();
    });
  </script>
<style>
  .iti { width: 100%;direction: ltr !important;}
  #colorbox, #cboxOverlay{
      display: none !important;
  }
</style>
  <!-- Buyer account part -->
  
  <!--<p><?php echo $text_account_already; ?></p> -->
  <div class="multi-seller-container">
      <div class="multi-seller__header">
          <h1 class="text-center"><?php echo $ms_account_register_seller; ?></h1>
      </div>
      <div class="container">
          <form id="ms-accountinfo" >
              <div class="multi-seller__form">
                  <div class="row">
                  
                      <!-- customer profile -->
                      <div class="col-md-12">
                          <h2 class="form__header">
                              <?php echo $text_your_details; ?>
                              <input type="hidden" name="customer_id" value="<?php echo $customer_id;?>"/>
                          </h2>
              
                          
                          <div class="form-group">
                              <label for="name"><?php echo $entry_name; ?> <span class="required">*</span></label>
                              <input type="text" name="firstname" class="form-control" id="name"
                                  value="<?php echo $firstname; ?>" placeholder="<?php echo $entry_name_placeholder;?>" />
                              <?php if ($error_firstname) { ?>
                              <span class="error"><?php echo $error_firstname; ?></span>
                              <?php } ?>
                          </div>
              
                          <div class="form-group">
                              <label for="telephone"><?php echo $entry_telephone; ?> 
                                  <?php if($customer_fields['telephone'] == '1'): ?>
                                      <span class="required">*</span>
                                  <?php endif ?>
                              </label>
                              <input type="tel" class="form-control" id="telephone" name="telephone"
                                  value="<?php echo $telephone ? $telephone : ''; ?>" />
                              <?php if ($error_telephone) { ?>
                              <span class="error"><?php echo $error_telephone; ?></span>
                              <?php } ?>
                          </div>            
                          
                          <div class="form-group">
                              <label for="gender"><?php echo $entry_gender ?>
                                  <?php if ($customer_fields['gender'] == '1'): ?>
                                  <span class="required">*</span>
                                  <?php endif ?>
                              </label>
                              <div>
                                <select name="gender" class="form-control">
                                    <option value="" selected="selected" disabled><?php echo $text_none;?></option>
                                    <option value="m" <?php echo $gender =="m" ? 'selected="selected"' : ""; ?> id="gender_m"><?php echo $entry_gender_m; ?></option>
                                    <option value="f" <?php echo $gender =="f" ? 'selected="selected"' : ""; ?> id="gender_f"><?php echo $entry_gender_f; ?></option>
                                </select>
                              </div>
                              <?php if ($error_gender) { ?>
                              <span class="error"><?php echo $error_gender; ?></span>
                              <?php } ?>
                          </div>
                          
                          <div class="form-group">
                              <label for="email"><?php echo $entry_email; ?> 
                                  <?php if($customer_fields['email'] == '1'):?>
                                      <span class="required">*</span>
                                  <?php endif ?>
                              </label>
                              <input type="text" name="email" class="form-control" id="email"
                                  value="<?php echo $email; ?>" placeholder="<?php echo $entry_email_placeholder;?>"/>
                              <?php if ($error_email) { ?>
                              <span class="error"><?php echo $error_email; ?></span>
                              <?php } ?>
                          </div>   
                      
                          <div class="form-group">
                              <label for="dob"><?php echo $entry_dob; ?>
                                  <?php if ($customer_fields['dob'] == '1'): ?>
                                  <span class="required">*</span>
                                  <?php endif ?>
                              </label>
                              <input type="date" name="dob" class="form-control three-dob-datepicker" id="dob"
                                  value="<?php echo $dob; ?>" />
                              <?php if ($error_dob) { ?>
                              <span class="error"><?php echo $error_dob; ?></span>
                              <?php } ?>
                          </div>
                          
                          <?php if (isset($customer_groups) and $customer_fields['groups'] == '0'): ?>
                          <div class="form-group">
                              <label for=""><?php echo $entry_customer_group; ?></label>
                              <div>
                                    <select name="customer_group_id" class="form-control">
                                        <option value="" selected="selected" disabled><?php echo $text_none;?></option>
                                        <?php foreach ($customer_groups as $customer_group): ?>
                                            <?php $checked = $customer_group_id == $customer_group['customer_group_id']; ?>
                                            <option value="<?php echo $customer_group['customer_group_id'];?>" <?php echo $checked ? 'selected="selected"' : ''; ?>><?php echo $customer_group['name']; ?></option>
                                        <?php endforeach ?>
                                    </select>
                              </div>
                              <?php if ($error_customer_group_id) { ?>
                              <span class="error"><?php echo $error_customer_group_id; ?></span>
                              <?php } ?>
                          </div>
                          <?php endif ?>                            
                            
                            <?php if($customer_fields['company'] > -1 ):?>
                            <div class="form-group">
                                <label for="company"><?php echo $entry_company; ?> 
                                    <?php if($customer_fields['company'] == '1'):?>
                                        <span class="required">*</span>
                                    <?php endif ?>
                                </label>
                                <input type="text" name="company" class="form-control" id="company"
                                    value="<?php echo $company; ?>" placeholder="<?php echo $entry_company_placeholder;?>"/>
                                <?php if ($error_company) { ?>
                                <span class="error"><?php echo $error_company; ?></span>
                                <?php } ?>
                            </div>
                            <?php endif ?>
                        </div>
                      <!-- /Customer profile -->
                      
                      <div class="col-md-12">
                      </div>
                      
                      <!-- Seller fields -->
                      <div class="col-md-12">
                          <!-- Seller account part -->
                          <h2 class="form__header"><?php echo $ms_account_sellerinfo_heading; ?></h2>
                          
                          <input type="hidden" name="action" id="ms_action" />
                          
                          <?php require('seller-form-v2.tpl'); ?>
                              
                          <?php if (isset($group_commissions) && $group_commissions[MsCommission::RATE_SIGNUP]['flat'] > 0) { ?>
                              <p class="attention ms-commission">
                                  <?php echo sprintf($this->language->get('ms_account_sellerinfo_fee_flat'),$this->currency->format($group_commissions[MsCommission::RATE_SIGNUP]['flat'], $this->config->get('config_currency')), $this->config->get('config_name')); ?>
                                  <?php echo $ms_commission_payment_type; ?>
                              </p>
                          <?php } ?>
              
                          <!-- Your Service Module -->
                          <?php if ($ys_enabled == 1):?>
                              <h2><?=$ys_service_settings?></h2>
                              <div class="content">
                              <?php foreach ($ys_services as $service):?>
                                  <?php if (!empty($service['sub_services'])):?>
                                  <h4 class="parent-service"><?=$service['name']?></h4>
                                  <ul class="list-unstyled">
                                      <?php foreach ($service['sub_services'] as $subService):?>
                                      <li>
                                          <input 
                                              name="service_ids[]" 
                                              id="<?=$subService['ys_service_id']?>" 
                                              type="checkbox" 
                                              value="<?=$subService['ys_service_id']?>" />
                                          <label for="<?=$subService['ys_service_id']?>"><?=$subService['name']?></label>
                                      </li>
                                      <?php endforeach?>
                                  </ul>
                                  <?php endif?>
                              <?php endforeach?>
                              <span class="text-danger"><?=$ys_services_error?></span>
                              </div>
                          <?php endif?>        
                      </div>        
                      <!-- /Seller fields -->
                      
                      <div class="col-md-12">
                      </div>
                  
                      <?php require('seller-form-address-v2.tpl'); ?>
                    
                    </div>
                    <?php //if ($text_agree) { ?>
                        <!--<div class="buttons">
                          <div class="right">--><?php //echo $text_agree; ?>
                            <?php //if ($agree) { ?>
                            <!--<input type="checkbox" name="agree" value="1" checked="checked" />-->
                            <?php //} else { ?>
                            <!--<input type="checkbox" name="agree" value="1" />-->
                            <?php //} ?>
                            <!--<a class="button" id="ms-submit-button" value="<?php //echo $button_continue; ?>"><span><?php //echo $ms_button_save; ?></span></a>
                          </div>
                        </div>-->
                        <?php //} else { ?>
                          <div class="register">
                            <a  id="ms-submit-button" value="<?php echo $button_continue; ?>"><span><?php echo $ms_button_save; ?></span></a>
                          </div>
                        <?php //} ?>
              </div>
            
            <!-- Common part -->
            
            <!--<h2><?php //echo $text_newsletter; ?></h2>
            <div class="content">
              <table class="form">
                <tr>
                  <td><?php //echo $entry_newsletter; ?></td>
                  <td><?php //if ($newsletter) { ?>
                    <input type="radio" name="newsletter" value="1" checked="checked" />
                    <?php //echo $text_yes; ?>
                    <input type="radio" name="newsletter" value="0" />
                    <?php //echo $text_no; ?>
                    <?php //} else { ?>
                    <input type="radio" name="newsletter" value="1" />
                    <?php //echo $text_yes; ?>
                    <input type="radio" name="newsletter" value="0" checked="checked" />
                    <?php //echo $text_no; ?>
                    <?php //} ?></td>
                </tr>
              </table>
            </div>-->
 
          </form>
      </div>
  </div>
  
  <!-- Payment Form -->
  	<?php if (isset($group_commissions) && $group_commissions[MsCommission::RATE_SIGNUP]['flat'] > 0) { ?>
		<?php if(isset($payment_form)) { ?><div class="ms-payment-form"><?php echo $payment_form; ?></div><?php } ?>
	<?php } ?>
  
  <?php echo $content_bottom; ?></div>

<!-- Seller account part -->
<?php $timestamp = time(); ?>


<script>
$(document).ready(function (){
    $('.multi-seller-container').closest('.container').removeClass('container').addClass('fluid').find('.header').removeClass('row').addClass('container').closest('.fluid').find('.breadcrumb').addClass('container')
});
</script>
<script type="text/javascript">
	var msGlobals = {
		timestamp: '<?php echo $timestamp; ?>',
		token : '<?php echo md5($timestamp); ?>',
		session_id: '<?php echo session_id(); ?>',
		uploadError: '<?php echo htmlspecialchars($ms_error_file_upload_error, ENT_QUOTES, "UTF-8"); ?>',
		config_enable_rte: '<?php echo $this->config->get('msconf_enable_rte'); ?>',
    zoneSelectError: '<?php echo htmlspecialchars($ms_account_sellerinfo_zone_select, ENT_QUOTES, "UTF-8"); ?>',
    zoneNotSelectedError: '<?php echo htmlspecialchars($ms_account_sellerinfo_zone_not_selected, ENT_QUOTES, "UTF-8"); ?>'
	};
</script>

<!-- Buyer account part -->
<script type="text/javascript"><!--
$('input[name=\'customer_group_id\']:checked').on('change', function() {
	var customer_group = [];
	
<?php foreach ($customer_groups as $customer_group) { ?>
	customer_group[<?php echo $customer_group['customer_group_id']; ?>] = [];
	customer_group[<?php echo $customer_group['customer_group_id']; ?>]['company_id_display'] = '<?php echo $customer_group['company_id_display']; ?>';
	customer_group[<?php echo $customer_group['customer_group_id']; ?>]['company_id_required'] = '<?php echo $customer_group['company_id_required']; ?>';
	customer_group[<?php echo $customer_group['customer_group_id']; ?>]['tax_id_display'] = '<?php echo $customer_group['tax_id_display']; ?>';
	customer_group[<?php echo $customer_group['customer_group_id']; ?>]['tax_id_required'] = '<?php echo $customer_group['tax_id_required']; ?>';
<?php } ?>	

	if (customer_group[this.value]) {
		if (customer_group[this.value]['company_id_display'] == '1') {
			$('#company-id-display').show();
		} else {
			$('#company-id-display').hide();
		}
		
		if (customer_group[this.value]['company_id_required'] == '1') {
			$('#company-id-required').show();
		} else {
			$('#company-id-required').hide();
		}
		
		if (customer_group[this.value]['tax_id_display'] == '1') {
			$('#tax-id-display').show();
		} else {
			$('#tax-id-display').hide();
		}
		
		if (customer_group[this.value]['tax_id_required'] == '1') {
			$('#tax-id-required').show();
		} else {
			$('#tax-id-required').hide();
		}	
	}
});

$('input[name=\'customer_group_id\']:checked').trigger('change');
//--></script> 
<script type="text/javascript"><!--
$('select[name=\'country_id\']').bind('change', function() {
    var _this = this;
	$.ajax({
		url: $('base').attr('href') + 'index.php?route=seller/register-seller/country&country_id=' + _this.value,
		dataType: 'json',
		beforeSend: function() {
			$('select[name=\'country_id\']').after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
		},
		complete: function() {
			$('.wait').remove();
		},			
		success: function(json) {
			//if (json['postcode_required'] == '1') {
			//	$('#postcode-required').show();
			//} else {
			//	$('#postcode-required').hide();
			//}
			
			html = '<option value=""><?php echo $text_select; ?></option>';
			
			if (json['zone'] && json['zone'] != '') {
				for (i = 0; i < json['zone'].length; i++) {
        			html += '<option value="' + json['zone'][i]['zone_id'] + '"';
	    			
					if (json['zone'][i]['zone_id'] == '<?php echo $zone_id; ?>') {
	      				html += ' selected="selected"';
	    			}
	
	    			html += '>' + json['zone'][i]['name'] + '</option>';
				}
			} else {
				html += '<option value="0" selected="selected"><?php echo $text_none; ?></option>';
			}
			
			$('select[name=\'zone_id\']').html(html);
            $('select[name=\'zone_id\']').trigger('change');
            
            // set telphone code
            if (_this.options[_this.selectedIndex] && _this.options[_this.selectedIndex].dataset.code) {
                var selectedCode = _this.options[_this.selectedIndex].dataset.code;
                var inputs = document.querySelectorAll("input[type=tel]");
                if(inputs.length && typeof intlTelInput !== "undefined" ) {
                    inputs.forEach(function(input) {
                        input.iti && input.iti.getNumber().length == 0 && (input.iti.setCountry(selectedCode.trim().toLowerCase()));
                    });
                }
            }
            
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});

$("select[name='zone_id']").bind('change', function() {
	$.ajax({
		url: $('base').attr('href') + 'index.php?route=seller/register-seller/zone&zone_id=' + this.value,
		dataType: 'json',
		beforeSend: function() {
			$("select[name='zone_id']").after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
		},
		complete: function() {
			$('.wait').remove();
		},			
		success: function(json) {
			html = '<option value=""><?php echo $text_select; ?></option>';
			
			if (json['area'] && json['area'] != '') {
				for (i = 0; i < json['area'].length; i++) {
        			html += '<option value="' + json['area'][i]['area_id'] + '"';
	    			
					if (json['area'][i]['area_id'] == '<?php echo $area_id; ?>') {
	      				html += ' selected="selected"';
	    			}
	
	    			html += '>' + json['area'][i]['name'] + '</option>';
				}
			} else {
				html += '<option value="0" selected="selected"><?php echo $text_none; ?></option>';
			}
			
			$('select[name=\'area_id\']').html(html);
            $('select[name=\'area_id\']').trigger('change');
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});

$('select[name=\'seller_country_id\']').bind('change', function() {
	$.ajax({
		url: $('base').attr('href') + 'index.php?route=seller/register-seller/country&country_id=' + this.value,
		dataType: 'json',
		beforeSend: function() {
			$('select[name=\'seller_country_id\']').after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
		},
		complete: function() {
			$('.wait').remove();
		},
		success: function(json) {
			html = '<option value=""><?php echo $text_select; ?></option>';
			
			if (json['zone'] != '') {
				for (i = 0; i < json['zone'].length; i++) {
        			html += '<option value="' + json['zone'][i]['zone_id'] + '"';
	    			
					if (json['zone'][i]['zone_id'] == '<?php echo $zone_id; ?>') {
	      				html += ' selected="selected"';
	    			}
	
	    			html += '>' + json['zone'][i]['name'] + '</option>';
				}
			} else {
				html += '<option value="0" selected="selected"><?php echo $text_none; ?></option>';
			}
			
			$('select[name=\'seller_zone\']').html(html);
			$('select[name=\'seller_zone\']').trigger('change');
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});

$('select[name=\'country_id\']').trigger('change');
$('select[name=\'seller_country_id\']').trigger('change');
//--></script>
<script type="text/javascript"><!--
$(document).ready(function() {
    // $('.datepicker').datepicker({dateFormat: 'yy-mm-dd'});
});
//--></script>
<?php if ($this->config->get('msconf_avatars_for_sellers') == 1 || $this->config->get('msconf_avatars_for_sellers') == 2) { ?>
	<script type="text/javascript">
		/*$('#ms-predefined-avatars').colorbox({
			width:'600px', height:'70%', inline:true, href:'#ms-predefined-avatars-container'
		}); */

		/**$('.avatars-list img').click(function() {
			if ($('.ms-image img').length == 0) {
				$('#sellerinfo_avatar_files').html('<div class="ms-image">' +
					'<input type="hidden" value="'+$(this).data('value')+'" name="seller_avatar_name" />' +
					'<img src="'+$(this).attr('src')+'" />' +
					'<span class="ms-remove"></span>' +
					'</div>');
			} else {
				$('.ms-image input[name="seller_avatar_name"]').val($(this).data('value'));
				$('.ms-image img').attr('src', $(this).attr('src'));
			}
			// $(window).colorbox.close();
		});*/
	</script>
<?php } ?>

<script>
    // telephone js
    $(function () {
        var inputs = document.querySelectorAll("input[type=tel]");

        if (inputs.length && typeof intlTelInput !== "undefined") {
            inputs.forEach(function (input) {
                var name = input.name;
                input.setAttribute('name', '');
                var iti = intlTelInput(input, Object.assign({
                    initialCountry: "auto",
                    nationalMode: true,
                    separateDialCode: !true,
                    autoPlaceholder: "aggressive",
                    formatOnDisplay: true,
                    preferredCountries: [],
                    responsiveDropdown: true,
                    placeholderNumberType: "MOBILE",
                    hiddenInput: name,
                    utilsScript: "expandish/view/javascript/iti/js/utils.js",
                }, function () {
                    if (!input.value.length) {
                        return {
                            geoIpLookup: function (callback) {
                                $.get('https://ipinfo.io', function () { }, "jsonp").always(function (resp) {
                                    var countryCode = (resp && resp.country) ? resp.country : "us";
                                    callback(countryCode);
                                });
                            },
                        }
                    }

                    return {};
                }()));

                input.onkeypress = function (e) {
                    e.stopPropagation ? e.stopPropagation() : (e.cancelBubble = !0);
                    "number" != typeof e.which && (e.which = e.keyCode);
                    if (
                        [43, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57].indexOf(
                            e.which
                        ) === -1
                    ) {
                        e.preventDefault();
                        return false;
                    }
                };

                input.onkeyup = function(event) {
                    iti.hiddenInput.value = iti.getNumber();
                }

                if (input.value) {
                    setTimeout(() => {
                        iti.hiddenInput.value = iti.getNumber();
                    }, 500);
                }

                input.iti = iti;
            });
        }
    });
 
</script>

<script>

	function setUploderIndex(uploder_index){
		pressed_button_id = 'ms-file-images-addfiles_'+uploder_index;
		new plupload.Uploader({
			runtimes : 'gears,html5,flash,silverlight',
			//runtimes : 'flash',
			multi_selection:false,
			browse_button:  pressed_button_id,
			url: $('base').attr('href') + 'index.php?route=seller/register-seller/jxUploadFiels',
			flash_swf_url: 'catalog/view/javascript/plupload/plupload.flash.swf',
			silverlight_xap_url : 'catalog/view/javascript/plupload/plupload.silverlight.xap',
			
			init : {
				FilesAdded: function(up, files) {
					$('#error_profile_attaches').html('');
					up.start();
				},
				
				FileUploaded: function(up, file, info) {
					try {
						data = $.parseJSON(info.response);
					} catch(e) {
						data = []; data.errors = []; data.errors.push(msGlobals.uploadError);
					}

					if (!$.isEmptyObject(data.errors)) {
						var errorText = '';
						for (var i = 0; i < data.errors.length; i++) {
							errorText += data.errors[i] + '<br />';
						}
						$('#error_profile_attaches').append(errorText).hide().fadeIn(2000);
					}

					if (!$.isEmptyObject(data.files)) {
						key = uploder_index;
						for (var i = 0; i < data.files.length; i++) {
							if(data.files[i].thumb){
								$(".profile_image_files_"+uploder_index).html(
									'<div class="ms-image">' +
									'<input type="hidden" value="'+data.files[i].name+'" name="seller[custom_fields]['+ key +']" />' +
									'<img src="'+data.files[i].thumb+'" />' +
									'<span class="ms-remove"></span>' +
								'</div>').children(':last').hide().fadeIn(2000);
							}else{
								$(".profile_image_files_"+uploder_index).html(
								'<div class="ms-download">' +
									'<input type="hidden" value="'+data.files[i].name+'" name="seller[custom_fields]['+ key +']" />' +
									'<span class="ms-download-name">'+data.files[i].fileMask+'</span>' +
								'<div class="ms-buttons">' +
									'<span class="ms-button-download disabled"></span>' +
									'<span class="ms-button-update disabled"></span>' +
								'</div></div>').children(':last').hide().fadeIn(2000);
							}
							
						}
					}
					
					up.stop();
				},
				
				Error: function(up, args) {
					$('#error_profile_attaches').append(msGlobals.uploadError).hide().fadeIn(2000);
					console.log('[error] ', args);
				}
			}
		}).init();
	}
    $(function() {	
        seller_indcies = <?php echo json_encode($seller['files_indecies']) ?>;
        $.each(seller_indcies, function( index, value ) {//to intialize pluploader -- it needs id and be intialized before clicking upload
            setUploderIndex(value);
        });
    });
	
</script>
<?php echo $footer; ?>
