<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<div class="buttons">
  <div class="right">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="button" />
  </div>
</div>

<form style="display:none" name="payfort_payment_form" id="payfort_payment_form" method="post">
    <?php foreach ($payment_request_params['params'] as $k => $v): ?>
        <input type="hidden" name="<?php echo $k?>" value="<?php echo $v?>">
    <?php endforeach; ?>
</form>

<div class="pf-iframe-background" id="div-pf-iframe" style="display:none">
    <div class="pf-iframe-container">
        <span class="pf-close-container">
            <i class="fa fa-times-circle pf-iframe-close" onclick="payfortFortMerchantPage.closePopup()"></i>
        </span>
        <i class="fa fa-spinner fa-spin pf-iframe-spin"></i>
        <div class="pf-iframe" id="pf_iframe_content"></div>
    </div>
</div>
<script type="text/javascript"><!--
$('#button-confirm').bind('click', function () {
    payfortFortMerchantPage.showMerchantPage("<?php echo $payment_request_params['url']?>");
});
//--></script>
<style>
.pf-iframe-background{
    position: fixed;
    z-index: 999;
    width: 100%;
    height: 100%;
    text-align: center;
    top: 0;
    left: 0;
    background: rgba(0,0,0,0.8);
    z-index: 99999
}
.pf-iframe-container{
    position: relative;
    z-index: 99999999
}
iframe#payfort_merchant_page {
    width:80% !important;
    min-height:100% !important;
}
.pf-close-container{
    display:block;
    text-align:center;
    margin:1em auto;
}
.pf-iframe-close{
    font-size: 1.7em;
    color: #fff;
    cursor: pointer
}
.pf-iframe-spin{
    font-size: 3em;
    color: #fff;
    display: block;
    margin: 1em auto;
    cursor: default
}
</style>

<script>
    var payfortFort = (function () {
        return {
            validateCreditCard: function(element) {
                var isValid = false;
                var eleVal = $(element).val();
                eleVal = this.trimString(element.val());
                eleVal = eleVal.replace(/\s+/g, '');
                $(element).val(eleVal);
                $(element).validateCreditCard(function(result) {
                    /*$('.log').html('Card type: ' + (result.card_type == null ? '-' : result.card_type.name)
                     + '<br>Valid: ' + result.valid
                     + '<br>Length valid: ' + result.length_valid
                     + '<br>Luhn valid: ' + result.luhn_valid);*/
                    isValid = result.valid;
                });
                return isValid;
            },
            validateCardHolderName: function(element) {
                $(element).val(this.trimString(element.val()));
                var cardHolderName = $(element).val();
                if(cardHolderName.length > 255) {
                    return false;
                }
                return true;
            },
            validateCvc: function(element) {
                $(element).val(this.trimString(element.val()));
                var cvc = $(element).val();
                if(cvc.length > 4 || cvc.length == 0) {
                    return false;
                }
                if(!this.isPosInteger(cvc)) {
                    return false;
                }
                return true;
            },
            translate: function(key, category, replacments) {
                if(!this.isDefined(category)) {
                    category = 'payfort_fort';
                }
                var message = (arr_messages[category + '.' + key]) ? arr_messages[category + '.' + key] : key;
                if (this.isDefined(replacments)) {
                    $.each(replacments, function (obj, callback) {
                        message = message.replace(obj, callback);
                    });
                }
                return message;
            },
            isDefined: function(variable) {
                if (typeof (variable) === 'undefined' || typeof (variable) === null) {
                    return false;
                }
                return true;
            },
            isTouchDevice: function() {
                return 'ontouchstart' in window        // works on most browsers
                    || navigator.maxTouchPoints;       // works on IE10/11 and Surface
            },
            trimString: function(str){
                return str.trim();
            },
            isPosInteger: function(data) {
                var objRegExp  = /(^\d*$)/;
                return objRegExp.test( data );
            }
        };
    })();

    var payfortFortMerchantPage2 = (function () {
        var merchantPage2FormId = 'frm_payfort_fort_payment';
        return {
            validateCcForm: function () {
                this.hideError();
                var isValid = payfortFort.validateCardHolderName($('#payfort_fort_card_holder_name'));
                if(!isValid) {
                    this.showError(payfortFort.translate('error_invalid_card_holder_name'));
                    return false;
                }
                isValid = payfortFort.validateCreditCard($('#payfort_fort_card_number'));
                if(!isValid) {
                    this.showError(payfortFort.translate('error_invalid_card_number'));
                    return false;
                }
                isValid = payfortFort.validateCvc($('#payfort_fort_card_security_code'));
                if(!isValid) {
                    this.showError(payfortFort.translate('error_invalid_cvc_code'));
                    return false;
                }
                var expDate = $('#payfort_fort_expiry_year').val()+''+$('#payfort_fort_expiry_month').val();
                $('#payfort_fort_expiry').val(expDate);
                return true;
            },
            showError: function(msg) {
                $('#payfort_fort_msg').html(msg);
                $('#payfort_fort_msg').show();
            },
            hideError: function() {
                $('#payfort_fort_msg').hide();
            }
        };
    })();

    var payfortFortMerchantPage = (function () {
        return {
            showMerchantPage: function(gatewayUrl) {
                if($("#payfort_merchant_page").size()) {
                    $( "#payfort_merchant_page" ).remove();
                }
                $('<iframe  name="payfort_merchant_page" id="payfort_merchant_page"height="550px" frameborder="0" scrolling="no" onload="payfortFortMerchantPage.iframeLoaded(this)" style="display:none"></iframe>').appendTo('#pf_iframe_content');
                $('.pf-iframe-spin').show();
                $('.pf-iframe-close').hide();
                $( "#payfort_merchant_page" ).attr("src", gatewayUrl);
                $( "#payfort_payment_form" ).attr("action",gatewayUrl);
                $( "#payfort_payment_form" ).attr("target","payfort_merchant_page");
                $( "#payfort_payment_form" ).submit();
                //fix for touch devices
                if (payfortFort.isTouchDevice()) {
                    setTimeout(function() {
                        $("html, body").animate({ scrollTop: 0 }, "slow");
                    }, 1);
                }
                $( "#div-pf-iframe" ).show();
            },
            closePopup: function() {
                $( "#div-pf-iframe" ).hide();
                $( "#payfort_merchant_page" ).remove();
                window.location = 'index.php?route=payment/payfort_fort/merchantPageCancel';
            },
            iframeLoaded: function(){
                $('.pf-iframe-spin').hide();
                $('.pf-iframe-close').show();
                $('#payfort_merchant_page').show();
            },
        };
    })();
</script>