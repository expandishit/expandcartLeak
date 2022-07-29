$( document ).ready(function() {
    
        var telephoneInput = $('#telephone'),
        countriesPhonesCodesList = $('#countriesPhonesCodesList'),
        telephoneCodeInputHidden = $('#telephoneCodeInputHidden'),
        storeDefaultCountry = $('#def_phone_country_code').val(),
        phoneCodeClass = ($('html').hasClass('cms-rtl')) ? 'pull-left' : 'pull-right';

        if($('html').hasClass('cms-rtl')){ 
            phoneCodeWithPlusRight = '';
            phoneCodeWithPlusLeft = '+';
            phoneCodeWithPlusValueRight = '';
            phoneCodeWithPlusValueLeft = '+';
        }else{
            phoneCodeWithPlusRight = '+';
            phoneCodeWithPlusLeft = '';
            phoneCodeWithPlusValueRight = '';
            phoneCodeWithPlusValueLeft = '+';
            telephoneInput.removeClass('pull-left');
            countriesPhonesCodesList.addClass('pull-left');
        }

        function codeFlagComponent(data)
        {
            if(data.country_id == storeDefaultCountry)
            {
                var phoneCode = phoneCodeWithPlusRight + data.phonecode+phoneCodeWithPlusLeft
                    phoneCodeValue = phoneCodeWithPlusValueLeft+data.phonecode+phoneCodeWithPlusValueRight;
                telephoneCodeInputHidden.val(phoneCodeValue); //here we set  choosen phone code in the input hidden value
                $('#phoneCodeSpan').addClass(phoneCodeClass)
                                    .text(phoneCode);

                $('#flagImage').attr('src',`admin/view/image/flags/${data.iso_code_2.toLowerCase()}.png`);
            }
            return `
                    <li>
                        <a style='display:inline-block;direction:ltr !important;white-space: nowrap;' class="phoneCodeFlag" href='#' title="${data.main_name}" data-country-iso = '${data.iso_code_2.toLowerCase()}' data-phone-code = '${data.phonecode}'>
                        <img src="admin/view/image/flags/${data.iso_code_2.toLowerCase()}.png">${phoneCodeWithPlusValueLeft}${data.phonecode}${phoneCodeWithPlusValueRight}
                        </a>
                    </li>
                    `;  
        }
        function fillCountriesDropDownList()
        {
            $.ajax({
            url: 'index.php?route=account/register/countries',
            dataType: 'json',
            success: function(data) {           
                var options = data.map(item => codeFlagComponent(item));
                    countriesPhonesCodesList.html(options);
            }     

            });
        }

        fillCountriesDropDownList();
        $(document).on('click','.phoneCodeFlag',function(e){
            e.preventDefault();
            var _this = $(this),
                phoneCodeValue = phoneCodeWithPlusValueLeft+ _this.data('phone-code')+phoneCodeWithPlusValueRight,
                phoneCode = phoneCodeWithPlusRight + _this.data('phone-code')+phoneCodeWithPlusLeft,
                countryIso = _this.data('country-iso');

                telephoneCodeInputHidden.val(phoneCodeValue); //here we set  choosen phone code in the input hidden value

            $('#phoneCodeSpan').addClass(phoneCodeClass)
                                .text(phoneCode);

            $('#flagImage').attr('src',`admin/view/image/flags/${countryIso}.png`);

        });
    
});