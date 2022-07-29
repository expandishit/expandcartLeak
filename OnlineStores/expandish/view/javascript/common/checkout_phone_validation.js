$(document).ready(function () {
    function isNumberKey(evt){
        var charCode = (evt.which) ? evt.which : evt.keyCode
        if (
            charCode > 31 && ((charCode < 48 || charCode > 57) && (charCode < 1632 || charCode > 1641))
        ) {
            return false;
        }

        return true;
    }

    function parseArabic(str) {
        return (str.replace(/[٠-٩]/g, function(d) {
            return '٠١٢٣٤٥٦٧٨٩'.indexOf(d);
        }).replace(/[۰-۹]/g, function(d) {
            return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
        }));

    }

    $('#payment_address_telephone').keypress(function(event){
        return isNumberKey(event);
    });
    
    $('#payment_address_telephone').on('keyup ', function (event) {
        var element = $(this);
        var inputValue = parseArabic(element.val());
       
        setTimeout(function () {
            if (!isNaN(inputValue)) {
              
                element.val(inputValue);
            }
            if (inputValue.toString().match(/^\d+$/g)) {
             
                element.val(inputValue);
            }
        });
    });

    $('#payment_address_telephone').on('paste ', function (event) {
        var element = $(this);
        var inputValue = parseArabic(event.originalEvent.clipboardData.getData('text'));
        console.log(inputValue)
        setTimeout(function () {
            if (!isNaN(inputValue)) {
                element.val(inputValue);
            }else{
                element.val('');
            }
            if (inputValue.toString().match(/^\d+$/g)) {
                element.val(inputValue);
            }
        });
    });

   
});


