<h2>{{ lang('text_credit_card') }}</h2>
<div class="content" id="payment">
    <?php if ($test_mode) { ?>
    <div class="warning">{{ lang('test_mode_warning') }}</div>
    <?php } ?>
    <table class="form">
        <tr>
            <td><span class="required">*</span>{{ lang('entry_cc_owner') }}</td>
            <td><input type="text" name="cc_owner" value="" id="cc_owner" size="28" maxlength="50" /></td>
        </tr>
        <tr>
            <td><span class="required">*</span>{{ lang('entry_cc_number') }}</td>
            <td><input type="text" name="cc_number" value="" id="cc_number" size="28" maxlength="16" /></td>
        </tr>
        <tr>
            <td><span class="required">*</span>{{ lang('entry_cc_expire_date') }}</td>
            <td><select name="cc_expire_date_month" id="cc_expire_date_month" >
                    <option value="">{{ lang('text_month') }}</option>
                    {% for month in months %}
                    <option value="{{ month['value'] }}">{{ month['value'] ~ ' - ' ~ month['text'] }}</option>
                    {% endfor %}
                </select>
                <select name="cc_expire_date_year" id="cc_expire_date_year">
                    <option value="">{{ lang('text_year') }}</option>
                    {% for year in year_expire %}
                    <option value="{{ year['value'] }}">{{ year['text'] }}</option>
                    {% endfor %}
                </select>
            </td>
        </tr>
        <tr>
            <td><span class="required">*</span>{{ lang('entry_cc_cvv2') }}</td>
            <td><input type="text" name="cc_cvv2" value="" size="28" id="cc_cvv2"
                       placeholder="{{ lang('entry_cc_cvv2_placeholder') }}" maxlength="4" /></td>
        </tr>
    </table>
    <div>{{ lang('credit_card_charged_info') }}</div>
</div>
<div class="buttons">
    <div class="right"><input type="button" value="<?php echo $button_confirm;  ?>" id="button-confirm" class="button"  /></div>
</div>
<script src="https://eshopping.nbe.com.eg/webapi/SMEOnline/api.js?v=2" type="text/javascript"></script>
<script type="text/javascript"><!--

    $('#button-confirm').bind('click', function () {
        $('.warning, .error').remove();

        var isAllValid = true;

        // validate card holder name
        if($('#cc_owner').val() == ''){ //validate empty field
            $('input[name=\'cc_owner\']').after('<span class="error">{{ lang('error_is_empty') }}</span>');
            isAllValid = false;
        }
        console.log($('#cc_owner').val());

        //validate card number
        if($('#cc_number').val() == ''){ //validate empty field
            $('input[name=\'cc_number\']').after('<span class="error">{{ lang('error_is_empty') }}</span>');
            isAllValid = false;

        } else if(!validateCreditCardNumber($('#cc_number').val())){ //validate valid card
            $('input[name=\'cc_number\']').after('<span class="error">{{ lang('error_invalid_creditcard') }}</span>');
            isAllValid = false;
        }

        console.log($('#cc_number').val());

        // validate card expiry day
        var expiryMonth = $('#cc_expire_date_month').val();
        var expiryYear = $('#cc_expire_date_year').val();
        if(expiryMonth == '' || expiryYear == ''){
            if(expiryMonth == ''){
                $('select[name=\'cc_expire_date_month\']').after('<span class="error">{{ lang('error_is_empty') }}</span>');
            }
            if(expiryYear == ''){
                $('select[name=\'cc_expire_date_year\']').after('<span class="error">{{ lang('error_is_empty') }}</span>');
            }
            isAllValid = false;
        }else if(!validateExpiryDate(expiryMonth,expiryYear)){
            $('select[name=\'cc_expire_date_month\']').after('<span class="error">{{ lang('error_expiration_date') }}</span>');
            isAllValid = false;
        }

        // validate CVN
        if($('#cc_cvv2').val() == ''){ //validate empty field
            $('input[name=\'cc_cvv2\']').after('<span class="error">{{ lang('error_is_empty') }}</span>');
            isAllValid = false;
        } else if(!validateCVN($('#cc_cvv2').val())){
            $('input[name=\'cc_cvv2\']').after('<span class="error">{{ lang('error_invalid_cvn') }}</span>');
            isAllValid = false;
        } else if(getCreditCardType($('#cc_number').val()) == 'AE' && $("#cc_cvv2").val().length < 4){
            $('input[name=\'cc_cvv2\']').after('<span class="error">{{ lang('error_invalid_cvn') }}</span>');
            isAllValid = false;
        } else if(getCreditCardType($('#cc_number').val()) != 'AE' && getCreditCardType($('#cc_number').val()) != '' && $("#cc_cvv2").val().length > 3){
            $('input[name=\'cc_cvv2\']').after('<span class="error">{{ lang('error_invalid_cvn') }}</span>');
            isAllValid = false;
        }

        if(isAllValid) {
            $.ajax({
                url: 'index.php?route=payment/nbe_bank/send',
                type: 'post',
                data: '',
                dataType: 'json',
                beforeSend: function () {
                    $('#button-confirm').prop('disabled', true);
                    $('#payment').before('<div class="attention"><img src="catalog/view/theme/default/image/loading.gif" alt="" /> {{ lang('text_wait') }}</div>');
                },
                success: function (result, message) {
                    if (result['redirect']) {
                        window.location = result['redirect'];
                    } else if (result['success']) {
                        processPayment(result['AuthKey']);
                    } else {
                        showConfirmError(result['error']);
                        $('.attention').remove();
                        $('#button-confirm').prop('disabled', false);
                    }
                }
            });
        } else {
            showConfirmError("{{ lang('error_common') }}");
        }
    });
    function processPayment(authkey) {
        if (typeof CBA == 'undefined') {
            showConfirmError("{{ lang('error_request') }}");
            $('.attention').remove();
            $('#button-confirm').prop('disabled', false);
            return;
        }
        //get month & year
        var year = $("#cc_expire_date_year").val();
        if (year.length > 2) {
            year = year.substring(2);
        }
        var month = parseInt($("#cc_expire_date_month").val(), 10);
        if (month < 10) {
            month = '0' + month;
        }

        CBA.ProcessPayment({
            AuthKey: authkey,
            BillerCode: "",
            MerchantReference: "",
            CardNumber: standardizeCreditCardNumber($("#cc_number").val()),
            Cvn: standardizeCVN($("#cc_cvv2").val()),
            ExpiryMonth: month,
            ExpiryYear: year,
            CardHolderName: $("#cc_owner").val(),
            StoreCard: 0,
            CallbackFunction: ProcessPaymentCallBack
        });
    }

    function ProcessPaymentCallBack(result) {
        var errors = new Array();
        if (result.AjaxResponseType == 0) { //AJAX call was successful
            if (result.ApiResponseCode == 0 || result.ApiResponseCode == 300) {
                window.location = result.RedirectionUrl;
            } else {
                errors = result.Errors;
            }
        }
        else if (result.AjaxResponseType == 1) { //Error with AJAX call
            errors = result.Errors;
        }
        else if (result.AjaxResponseType == 2) { //AJAX call timed out
            errors = result.Errors;
        }
        //Show errors on the page
        if (errors.length > 0) {
            showConfirmError("{{ lang('error_request') }}");
            $('.attention').remove();
            $('#button-confirm').prop('disabled', false);
        }
    }

    function showConfirmError(message) {
        $('#confirm .checkout-content').prepend('<div class="warning" style="display: none;">' + message + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>');
        $('.warning').fadeIn('slow');
    }

    //validate credit card
    function validateCreditCardNumber(CardNumber) {
        var a = standardizeCreditCardNumber(CardNumber);
        var b;
        if (b = a.length >= 10) {
            if (b = a.length <= 16) {
                a = a;
                if (/[^0-9-\s]+/.test(a))
                    b = false;
                else {
                    b = 0;
                    var d, e = false;
                    a = a.replace(/\D/g, "");
                    for (var h = a.length - 1; h >= 0; h--) {
                        d = a.charAt(h);
                        d = parseInt(d, 10);
                        if (e)
                            if ((d *= 2) > 9)
                                d -= 9;
                        b += d;
                        e = !e
                    }
                    b = b % 10 == 0
                }
            }
            b = b
        }
        //return result (true/false)
        return b;
    }

    //validate CVN
    function validateCVN(CVN) {
        var a = standardizeCVN(CVN);
        //return result (true/false)
        return /^\d+$/.test(a) && a.length >= 3 && a.length <= 4;
    }

    // validate ExpiryDate
    function validateExpiryDate(ExpiryMonth, ExpiryYear) {
        var d, e;
        a = ExpiryMonth;
        b = ExpiryYear;
        if (/^\d+$/.test(a) == false)
            return false;
        if (/^\d+$/.test(b) == false)
            return false;
        if (parseInt(a, 10) <= 12 == false)
            return false;
        e = new Date(b, a);
        d = new Date;
        e.setMonth(e.getMonth() - 1);
        e.setMonth(e.getMonth() + 1, 1);
        return e > d
    }

    function standardizeCreditCardNumber(CardNumber){
        //remove space and "-"
        return (CardNumber + "").replace(/\s+|-/g, "");
    }

    function standardizeCVN(CVN){
        //remove space of start and end
        return (CVN + "").replace(/^\s+|\s+$/g, "");
    }

    //get Card Type
    function getCreditCardType(CardNumber) {
        /*
         * Return Credit Card Type
         * MC: Master Card
         * VC: Visa
         * AE: American Express
         * DC Dinners Club
         * JC: JCB
         * empty string for others
         */
        var a = standardizeCreditCardNumber(CardNumber);
        if (new RegExp('^5[1-5][0-9]{14}$').test(a)) {
            return 'MC';
        }
        if (new RegExp('^4[0-9]{12}([0-9]{3})?$').test(a)) {
            return 'VC';
        }
        if (new RegExp('^3[47][0-9]{13}$').test(a)) {
            return 'AE';
        }
        if (new RegExp('^(30[0-5,9][0-9]{11}|3[689][0-9]{12}|5[45][0-9]{14})$').test(a)) {
            return 'DC';
        }
        if (new RegExp('^35(2[89][0-9]{12}$|[3-8][0-9]{13}$)').test(a)) {
            return 'JC';
        }
        return '';
    }
    //--></script>
