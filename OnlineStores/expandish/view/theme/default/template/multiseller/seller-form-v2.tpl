<div class="form-group">
    <label for="seller-nickname"><?php echo $ms_account_sellerinfo_nickname; ?> <span class="required">*</span></label>
    <?php if (!$this->config->get('msconf_change_seller_nickname') && !empty($seller['ms.nickname'])): ?>
    <input type="text" class="form-control" id="seller-nickname"
        value="<?php echo $seller['ms.nickname']; ?>" placeholder="<?php echo $ms_account_sellerinfo_nickname_placeholder;?>" readonly />
    <?php else: ?>
    <input type="text" name="seller[nickname]" class="form-control" id="seller-nickname"
        value="<?php echo $seller['ms.nickname']; ?>" placeholder="<?php echo $ms_account_sellerinfo_nickname_placeholder;?>" />
    <?php endif ?>
    <?php if ($error_seller_nickname) { ?>
    <span class="error"><?php echo $error_seller_nickname; ?></span>
    <?php } ?>
</div>

<?php foreach ($seller_custom_fields as $key => $field): ?>
<?php if($field['active'] == '0') continue; ?>
<div class="form-group">
    <label for="seller-custom-<?php echo $key;?>">
        <?php echo $field['title'][$config_language_id]; ?>
        <?php if($field['required'] == '1'): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <?php if($field['field_type']['name'] == 'text' || !$field['field_type']['name']){ ?>
    <input type="text" name="seller[custom_fields][<?php echo $key ?>]" class="form-control" id="seller-custom-<?php echo $key;?>"
        value="<?php echo $seller['ms.custom_fields'][$key]; ?>" placeholder="<?php echo $field['title'][$config_language_id];?>" />
    <?php } else if ($field['field_type']['name'] == 'file_uploader') { ?>         
        <a name="ms-file-addfiles" id="ms-file-images-addfiles_<?php echo $key ?>" class="button" onclick="setUploderIndex(<?php echo $key ?>)">
            <span><?php echo $ms_button_select_files; ?></span></a>
        <p class="ms-note" ><?php echo $ms_images_files_upload_note; ?></p>
        <div class="error" id="error_profile_attaches_<?php echo $key ?>"></div>
        <div class="profile_image_files_<?php echo $key ?> seller_files">
            <?php if (isset ($seller['ms.custom_fields'][$key]['name'] )){?>
                <input type="hidden" value="<?php echo $seller['ms.custom_fields'][$key]['name'] ?>" name="seller[custom_fields][<?php echo $key ?>]" />
                <?php if($seller['ms.custom_fields'][$key]['image'] ) {?>
                    <div class="ms-image">
                        <img src="<?php echo $seller['ms.custom_fields'][$key]['image'] ?>">
                        <span class="ms-remove"></span>
                    </div>
                <?php } else if ($seller['ms.custom_fields'][$key]['href']){ ?>
                    <div class="ms-download">
                        <span class="ms-download-name"><?php echo  $seller['ms.custom_fields'][$key]['mask']; ?></span>
                        <div class="ms-buttons">
                            <a href="<?php echo $seller['ms.custom_fields'][$key]['href']; ?>" class="ms-button-download" title="<?php echo $ms_download; ?>"></a>
                            <a class="ms-button-delete" title="<?php echo $ms_delete; ?>"></a>
                        </div>
                    </div>
                <?php } ?>	
            <?php } ?>
        </div>
    <?php } else if($field['field_type']['name'] == 'select'){  ?> 
        <select name="seller[custom_fields][<?php echo $key ?>]" class="form-control" >
            <option value=""><?php echo $select_option; ?></option>
            <?php foreach ($field['field_type']['option_value'] as $option){?> 
                <option <?php if($option[$config_language_id] == $seller['ms.custom_fields'][$key] || in_array($seller['ms.custom_fields'][$key] ,$option ) ) { ?> selected <?php } ?>
                value="<?php echo $option[$config_language_id]?>"><?php echo $option[$config_language_id]?></option>
            <?php } ?>
        </select>
    <?php } else if($field['field_type']['name'] == 'radio'){ ?> 
        <?php foreach ($field['field_type']['option_value'] as $option){?> 
            <input type="radio"  name="seller[custom_fields][<?php echo $key ?>]" 
            <?php  if($option[$config_language_id] == $seller['ms.custom_fields'][$key] || in_array($seller['ms.custom_fields'][$key] ,$option ) ) { ?> checked <?php } ?>
                value="<?php echo $option[$config_language_id]?>">
            <label> <?php echo $option[$config_language_id]?> </label><br>
        <?php } ?>
    <?php } else if($field['field_type']['name'] == 'checkbox'){ ?> 
        <?php foreach ($field['field_type']['option_value'] as $option){?> 
            <input type="checkbox"  name="seller[custom_fields][<?php echo $key ?>]" 
            <?php  if($option[$config_language_id] == $seller['ms.custom_fields'][$key] || in_array($seller['ms.custom_fields'][$key] ,$option ) ) { ?> checked <?php } ?>
                value="<?php echo $option[$config_language_id]?>">
            <label> <?php echo $option[$config_language_id]?>  </label><br>
        <?php } ?>
    <?php } ?>
                        
        
    <?php
    ///// This Type of error check, for register-seller
    ///// But account-profile errors appears using account-seller-profile.js append to td.
    $errVar = 'error_seller_data_custom_'.$key; ?>
    <?php if ($errVar): ?>
        <span class="error"><?php echo $errVar; ?></span>
    <?php endif ?>
</div>
<?php endforeach ?>

<?php if(in_array(ucfirst('tax card'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="name">
        <?php echo $ms_account_sellerinfo_tax_card; ?>
        <?php if(in_array(ucfirst('tax card'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <input type="text" name="seller[tax_card]" class="form-control" id="name"
        value="<?php echo $seller['ms.tax_card']; ?>" placeholder="<?php echo $ms_account_sellerinfo_tax_card_placeholder;?>" />
    <?php if ($error_seller_tax_card): ?>
    <span class="error"><?php echo $error_seller_tax_card; ?></span>
    <?php endif ?>
</div>
<?php endif ?>

<script> var langCode =  $('html').attr('lang'); </script>

<?php if(in_array(ucfirst('description'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="msseller-description">
        <?php echo $ms_account_sellerinfo_description; ?>
        <?php if(in_array(ucfirst('description'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <textarea name="seller[description]" id="seller_textarea" placeholder="<?php echo $ms_account_sellerinfo_description_note; ?>" class="<?php echo $this->config->get('msconf_enable_rte') ? "ckeditor" : ''; ?> form-control"><?php echo $this->config->get('msconf_enable_rte') ? htmlspecialchars_decode($seller['ms.description']) : strip_tags(htmlspecialchars_decode($seller['ms.description'])); ?></textarea>
			
    <?php if ($error_seller_description) { ?>
        <span class="error"><?php echo $error_seller_description; ?></span>
    <?php } ?>
</div>
<?php endif ?>

<?php if(in_array(ucfirst('mobile'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="ms-mobile">
        <?php echo $ms_account_sellerinfo_mobile; ?>
        <?php if(in_array(ucfirst('mobile'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <input type="tel" name="seller[mobile]" class="form-control" id="ms-mobile"
        value="<?php echo $seller['ms.mobile']; ?>" placeholder="<?php echo $ms_account_sellerinfo_mobile_placeholder;?>" />
    <?php if ($error_seller_mobile): ?>
    <span class="error"><?php echo $error_seller_mobile; ?></span>
    <?php endif ?>
</div>
<?php endif ?>

<?php if(in_array(ucfirst('company'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="ms-company">
        <?php echo $ms_account_sellerinfo_company; ?>
        <?php if(in_array(ucfirst('company'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <input name="seller[company]" class="form-control" id="ms-company"
        value="<?php echo $seller['ms.company']; ?>" placeholder="<?php echo $ms_account_sellerinfo_company_placeholder;?>" />
    <?php if ($error_seller_company): ?>
    <span class="error"><?php echo $error_seller_company; ?></span>
    <?php endif ?>
</div>
<?php endif ?>


<?php if(in_array(ucfirst('website'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="ms-website">
        <?php echo $ms_account_sellerinfo_website; ?>
        <?php if(in_array(ucfirst('website'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <input name="seller[website]" class="form-control" id="ms-website"
        value="<?php echo $seller['ms.website']; ?>" placeholder="<?php echo $ms_account_sellerinfo_website_placeholder;?>" />
    <?php if ($error_seller_website): ?>
    <span class="error"><?php echo $error_seller_website; ?></span>
    <?php endif ?>
</div>
<?php endif ?>

<?php if(in_array(ucfirst('commercial register'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="ms-commercial_reg">
        <?php echo $ms_account_sellerinfo_commercial_reg; ?>
        <?php if(in_array(ucfirst('commercial register'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <input name="seller[commercial_reg]" class="form-control" id="ms-commercial_reg"
        value="<?php echo $seller['ms.commercial_reg']; ?>" placeholder="<?php echo $ms_account_sellerinfo_commercial_reg_placeholder;?>" />
    <?php if ($error_seller_commercial_reg): ?>
    <span class="error"><?php echo $error_seller_commercial_reg; ?></span>
    <?php endif ?>
</div>
<?php endif ?>


<?php if(in_array(ucfirst('record expiration date'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="ms-rec_exp_date">
        <?php echo $ms_account_sellerinfo_rec_exp_date; ?>
        <?php if(in_array(ucfirst('record expiration date'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <input name="seller[rec_exp_date]" class="form-control datepicker" id="ms-rec_exp_date"
        value="<?php echo $seller['ms.rec_exp_date']; ?>" placeholder="<?php echo $ms_account_sellerinfo_rec_exp_date_placeholder;?>" autocomplete="off" />
    <?php if ($error_seller_rec_exp_date): ?>
    <span class="error"><?php echo $error_seller_rec_exp_date; ?></span>
    <?php endif ?>
</div>
<?php endif ?>

<?php if(in_array(ucfirst('industrial license number'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="ms-license_num">
        <?php echo $ms_account_sellerinfo_license_num; ?>
        <?php if(in_array(ucfirst('industrial license number'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <input name="seller[license_num]" class="form-control" id="ms-license_num"
        value="<?php echo $seller['ms.license_num']; ?>" placeholder="<?php echo $ms_account_sellerinfo_license_num_placeholder;?>" autocomplete="off" />
    <?php if ($error_seller_license_num): ?>
    <span class="error"><?php echo $error_seller_license_num; ?></span>
    <?php endif ?>
</div>
<?php endif ?>

<?php if(in_array(ucfirst('license expiration date'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="ms-lcn_exp_date">
        <?php echo $ms_account_sellerinfo_lcn_exp_date; ?>
        <?php if(in_array(ucfirst('license expiration date'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <input name="seller[lcn_exp_date]" class="form-control datepicker" id="ms-lcn_exp_date"
        value="<?php echo $seller['ms.lcn_exp_date']; ?>" placeholder="<?php echo $ms_account_sellerinfo_lcn_exp_date_placeholder;?>" autocomplete="off" />
    <?php if ($error_seller_lcn_exp_date): ?>
    <span class="error"><?php echo $error_seller_lcn_exp_date; ?></span>
    <?php endif ?>
</div>
<?php endif ?>

<?php if(in_array(ucfirst('personal id'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="ms-personal_id">
        <?php echo $ms_account_sellerinfo_personal_id; ?>
        <?php if(in_array(ucfirst('personal id'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <input name="seller[personal_id]" class="form-control" id="ms-personal_id"
        value="<?php echo $seller['ms.personal_id']; ?>" placeholder="<?php echo $ms_account_sellerinfo_personal_id_placeholder;?>" autocomplete="off" />
    <?php if ($error_seller_personal_id): ?>
    <span class="error"><?php echo $error_seller_personal_id; ?></span>
    <?php endif ?>
</div>
<?php endif ?>

<?php if (in_array(ucfirst('country'),$seller_show_fields) && !$hide_country_region): ?>
<div class="form-group">
    <label for="ms-country">
        <?php echo $ms_account_sellerinfo_country; ?>
        <?php if(in_array(ucfirst('country'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <select name="seller[country]" class="form-control">
        <option value="" selected="selected"><?php echo $ms_account_sellerinfo_country_dont_display; ?></option>
        <?php foreach ($countries as $country) { ?>
        <option value="<?php echo $country['country_id']; ?>" <?php if ($seller['ms.country_id'] == $country['country_id'] || $country_id == $country['country_id']) { ?>selected="selected"<?php } ?>><?php echo $country['name']; ?></option>
        <?php } ?>
    </select>
    <?php if ($error_seller_country): ?>
    <span class="error"><?php echo $error_seller_country; ?></span>
    <?php endif ?>
</div>
<?php endif ?>

<?php if (in_array(ucfirst('region'),$seller_show_fields) && !$hide_country_region): ?>
<div class="form-group">
    <label for="ms-zone">
        <?php echo $ms_account_sellerinfo_zone; ?>
        <?php if(in_array(ucfirst('region'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <select name="seller[zone]" class="form-control"></select>
    <?php if ($error_seller_zone): ?>
    <span class="error"><?php echo $error_seller_zone; ?></span>
    <?php endif ?>
</div>
<?php endif ?>

<?php if(in_array(ucfirst('paypal'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="ms-paypal">
        <?php echo $ms_account_sellerinfo_paypal; ?>
        <?php if(in_array(ucfirst('paypal'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <input name="seller[paypal]" class="form-control" id="ms-paypal"
        value="<?php echo $seller['ms.paypal']; ?>" placeholder="<?php echo $ms_account_sellerinfo_paypal_placeholder;?>" />
    <?php if ($error_seller_paypal): ?>
    <span class="error"><?php echo $error_seller_paypal; ?></span>
    <?php endif ?>
</div>
<?php endif ?>

<?php if(in_array(ucfirst('bank name'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="ms-bank_name">
        <?php echo $ms_account_sellerinfo_bank_name; ?>
        <?php if(in_array(ucfirst('bank name'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <input name="seller[bank_name]" class="form-control" id="ms-bank_name"
        value="<?php echo $seller['ms.bank_name']; ?>" placeholder="<?php echo $ms_account_sellerinfo_bank_name_placeholder;?>" />
    <?php if ($error_seller_bank_name): ?>
    <span class="error"><?php echo $error_seller_bank_name; ?></span>
    <?php endif ?>
</div>
<?php endif ?>

<?php if(in_array(ucfirst('bank iban'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="ms-bank_iban">
        <?php echo $ms_account_sellerinfo_bank_iban; ?>
        <?php if(in_array(ucfirst('bank iban'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <input name="seller[bank_iban]" class="form-control" id="ms-bank_iban"
        value="<?php echo $seller['ms.bank_iban']; ?>" placeholder="<?php echo $ms_account_sellerinfo_bank_iban_placeholder;?>" />
    <?php if ($error_seller_bank_iban): ?>
    <span class="error"><?php echo $error_seller_bank_iban; ?></span>
    <?php endif ?>
</div>
<?php endif ?>

<?php if(in_array(ucfirst('bank transfer'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="ms-bank_transfer">
        <?php echo $ms_account_sellerinfo_creditcart; ?>
        <?php if(in_array(ucfirst('bank transfer'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    <textarea name="seller[bank_transfer]" id="ms-bank_transfer" placeholder="<?php echo $ms_account_sellerinfo_credit_note; ?>" class="form-control"><?php echo $seller['ms.bank_transfer']; ?></textarea>
    <?php if ($error_seller_bank_transfer): ?>
    <span class="error"><?php echo $error_seller_bank_transfer; ?></span>
    <?php endif ?>
</div>
<?php endif ?>

<?php if(in_array(ucfirst('payment methods'),$seller_show_fields) && $show_dedicated_payment_methods != -1): ?>
<div class="form-group">
    <label for="ms-payment_methods">
        <?php echo $ms_account_sellerinfo_creditcart; ?>
        {{ lang('ms_account_payment_gateways') }}
        <?php if(in_array(ucfirst('payment methods'),$seller_required_fields)): ?>
        <span class="required">*</span>
        <?php endif ?>
    </label>
    
    <?php foreach ($payment_gateways as $key => $payment_gateway) : ?>
        <input type="checkbox" id="payment_methods_<?=$key;?>"
            value="<?= $payment_gateway; ?>"
            <?= in_array($payment_gateway, $active_payment_gateways) ? 'checked' : '' ?>
            name="seller[payment_methods][]" />
        <label for="payment_methods_<?=$key;?>">{{ lang('seller_payment_<?= $payment_gateway; ?>') }}</label>
        <br />
    <?php endforeach; ?>
                
    <?php if ($error_seller_payment_methods): ?>
    <span class="error"><?php echo $error_seller_payment_methods; ?></span>
    <?php endif ?>
</div>
<?php endif ?>

<?php if ($ms_account_sellerinfo_terms_note): ?>
<div class="form-group">
    <label for="ms-terms">
        <?=$ms_account_sellerinfo_terms;?>
        <input id="ms-terms" type="checkbox" name="seller[terms]" value="1" />
    </label>
</div>
<?php endif ?>

<?php if (!isset($seller['seller_id']) &&$seller_validation != MsSeller::MS_SELLER_VALIDATION_NONE): ?>
<div class="form-group">
    <label for="msseller-reviewer_message">
        <?php echo $ms_account_sellerinfo_reviewer_message; ?>
    </label>
    <textarea name="seller[reviewer_message]" id="seller_textarea" placeholder="<?php echo $ms_account_sellerinfo_reviewer_message_note; ?>" class="<?php echo $this->config->get('msconf_enable_rte') ? "ckeditor" : ''; ?> form-control"><?php echo $this->config->get('msconf_enable_rte') ? htmlspecialchars_decode($seller['ms.reviewer_message']) : strip_tags(htmlspecialchars_decode($seller['ms.reviewer_message'])); ?></textarea>
    <?php if ($error_seller_reviewer_message) { ?>
        <span class="error"><?php echo $error_seller_reviewer_message; ?></span>
    <?php } ?>
</div>
<?php endif ?>

<?php if(in_array(ucfirst('avatar'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="msseller-avatar">
        <?php echo $ms_account_sellerinfo_avatar; ?>
        <?php if(in_array(ucfirst('avatar'),$seller_required_fields)): ?> <span class="required">*</span> <?php endif ?>
    </label>
    <div>
        <!--<input type="file" name="ms-file-selleravatar" id="ms-file-selleravatar" />-->
        <div class="buttons" style="width: 100%;">
        <?php if ($this->config->get('msconf_avatars_for_sellers') != 2) { ?>
            <a name="ms-file-selleravatar" id="ms-file-selleravatar" class="button"><span><?php echo $ms_button_select_image; ?></span></a>
        <?php } ?>
        <?php if ($this->config->get('msconf_avatars_for_sellers') == 1 || $this->config->get('msconf_avatars_for_sellers') == 2) { ?>
            <a name="ms-predefined-avatars" id="ms-predefined-avatars" class="button"><span><?php echo $ms_button_select_predefined_avatar; ?></span></a>
            <div><div id="ms-predefined-avatars-container">
                <?php if ($predefined_avatars) { ?>
                    <?php foreach ($predefined_avatars as $avatar_category_name => $avatars) { ?>
                    <div class="avatars-group">
                        <h4><?php echo $avatar_category_name; ?></h4>
                        <div class="avatars-list">
                        <?php foreach ($avatars as $key => $avatar) { ?>
                            <img src="<?php echo $avatar['image']; ?>" data-value="<?php echo $avatar['dir'] . $avatar['filename']; ?>">
                        <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                <?php } ?>
            </div></div>
        <?php } ?>
        </div>
        
        <p class="error" id="error_sellerinfo_avatar"></p>

        <div id="sellerinfo_avatar_files" style="text-align: center;">
        <?php if (!empty($seller['avatar'])) { ?>
            <div class="ms-image">
                <input type="hidden" name="seller[avatar_name]" value="<?php echo $seller['ms.avatar_name']; ?>" />
                <img src="<?php echo $seller['avatar']['thumb']; ?>" />
                <span class="ms-remove"></span>
            </div>
        <?php } ?>
        </div>

    </div>
</div>
<?php endif ?>

<?php if(in_array(ucfirst('commercial record image'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="ms-file-commercial_image">
        <?php echo $ms_account_sellerinfo_commercial_image; ?>
        <?php if(in_array(ucfirst('commercial record image'),$seller_required_fields)): ?> <span class="required">*</span> <?php endif ?>
    </label>
    <div>
        <div class="buttons" style="width: 100%;">
            <a name="ms-file-commercial_image" id="ms-file-commercial_image" class="button"><span><?php echo $ms_button_select_image; ?></span></a>
        </div>
        <p class="error" id="error_sellerinfo_commercial_image"></p>
        <div id="sellerinfo_commercial_image_files" style="text-align: center;">
        <?php if (!empty($seller['commercial_image'])) { ?>
            <div class="ms-image">
                <input type="hidden" name="seller[commercial_image_name]" value="<?php echo $seller['commercial_image']['name']; ?>" />
                <img src="<?php echo $seller['commercial_image']['thumb']; ?>" />
                <span class="ms-remove"></span>
            </div>
        <?php } ?>
        </div>
    </div>
</div>
<?php endif ?>

<?php if(in_array(ucfirst('industrial license image'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="msseller-industrial_license_image">
        <?php echo $ms_account_sellerinfo_license_image; ?>
        <?php if(in_array(ucfirst('industrial license image'),$seller_required_fields)): ?> <span class="required">*</span> <?php endif ?>
    </label>
    <div>
        <div class="buttons" style="width:100%;">
        <a name="ms-file-license_image" id="ms-file-license_image" class="button"><span><?php echo $ms_button_select_image; ?></span></a>
        </div>
        <p class="error" id="error_sellerinfo_license_image"></p>
        <div id="sellerinfo_license_image_files" style="text-align: center;">
        <?php if (!empty($seller['license_image'])) { ?>
            <div class="ms-image">
                <input type="hidden" name="seller[license_image_name]" value="<?php echo $seller['license_image']['name']; ?>" />
                <img src="<?php echo $seller['license_image']['thumb']; ?>" />
                <span class="ms-remove"></span>
            </div>
        <?php } ?>
        </div>
    </div>
</div>
<?php endif ?>

<?php if(in_array(ucfirst('tax card image'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="msseller-tax_card_image">
        <?php echo $ms_account_sellerinfo_tax_image; ?>
        <?php if(in_array(ucfirst('tax card image'),$seller_required_fields)): ?> <span class="required">*</span> <?php endif ?>
    </label>
    <div>
        <div class="buttons" style="width:100%;">
            <a name="ms-file-tax_image" id="ms-file-tax_image" class="button"><span><?php echo $ms_button_select_image; ?></span></a>
            </div>
            <p class="ms-note"><?php echo $ms_account_sellerinfo_tax_image_note; ?></p>
            <p class="error" id="error_sellerinfo_tax_image"></p>

            <div id="sellerinfo_tax_image_files" style="text-align: center;">
            <?php if (!empty($seller['tax_image'])) { ?>
                <div class="ms-image">
                    <input type="hidden" name="seller[tax_image_name]" value="<?php echo $seller['tax_image']['name']; ?>" />
                    <img src="<?php echo $seller['tax_image']['thumb']; ?>" />
                    <span class="ms-remove"></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php endif ?>


<?php if(in_array(ucfirst('image id'),$seller_show_fields)): ?>
<div class="form-group">
    <label for="msseller-image_id">
        <?php echo $ms_account_sellerinfo_image_id; ?>
        <?php if(in_array(ucfirst('image id'),$seller_required_fields)): ?> <span class="required">*</span> <?php endif ?>
    </label>
    <div>
        <div class="buttons" style="width:100%;">
            <a name="ms-file-image_id" id="ms-file-image_id" class="button"><span><?php echo $ms_button_select_image; ?></span></a>
            </div>
            <p class="ms-note"><?php echo $ms_account_sellerinfo_image_id_note; ?></p>
            <p class="error" id="error_sellerinfo_image_id"></p>

            <div id="sellerinfo_image_id_files" style="text-align: center;">
            <?php if (!empty($seller['image_id'])) { ?>
                <div class="ms-image">
                    <input type="hidden" name="seller[image_id_name]" value="<?php echo $seller['image_id']['name']; ?>" />
                    <img src="<?php echo $seller['image_id']['thumb']; ?>" />
                    <span class="ms-remove"></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php endif ?>

<?php
// Seller Google Map Location
require('seller-location-map-v2.tpl'); 
?>
<script>

	function setUploderIndex(uploder_index){
		pressed_button_id = 'ms-file-images-addfiles_'+uploder_index;
		new plupload.Uploader({
			runtimes : 'gears,html5,flash,silverlight',
			//runtimes : 'flash',
			multi_selection:false,
			browse_button:  pressed_button_id,
			url: $('base').attr('href') + 'index.php?route=seller/account-profile/jxUploadFiels',
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
