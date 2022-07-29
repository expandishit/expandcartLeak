PayPalSdk.Marks().render('#payment_methods_options');
PayPalSdk.Buttons({
    createOrder: function () {
        //$("body").addClass("spinner-border text-success");

        return fetch('index.php?route=payment/paypal/createOrder', {
            method: 'post',
            headers: {
                'content-type': 'application/json'
            }
        }).then(function (res) {
            return res.json();
        }).then(function (data) {

            if(!data.id) {


                var errorMessage = (data.details[0].value ) ? data.details[0].value + ": " : "";

                errorMessage += data.details[0].description;


                if(data.details[0].field.indexOf("national_number") != -1) {
                    errorMessage = "phone number is not in correct format";
                }
                $("label[for='paypal']").after("<span class='btn-danger paypal-error' style='display: block; margin-bottom: 5px;'>" + errorMessage + "</span>")
                return;
            } else {
                $(".paypal-error").remove();
            }
            return data.id;
        });
    },
    onApprove: function (data, actions) {
        if($(".loader").length == 0) {

            $("#payment_method_wrap .box-content").prepend("<div  class='loader'></div>");

        }
        return fetch('index.php?route=payment/paypal/approveOrder', {
            method: 'POST',
            headers: {
                'content-type': 'application/json'
            },
            body: JSON.stringify({
                orderID: data.orderID
            })
        }).then(function (res) {
            return res.json();
        }).then(function (res) {
            if(res.details.details != null) {
                var errorDetail = Array.isArray(res.details.details) && res.details.details[0];

                if (errorDetail && errorDetail.issue === 'INSTRUMENT_DECLINED') {
                    return actions.restart(); // Recoverable state, per:
                }
            } else {

                location.href = res.redirectTo;

            }
        });
    },
    style: {
        label: 'paypal',
        size: 'responsive',
        shape: 'rect',
        layout: 'vertical',
        color: `${paypal_button_color}`,
        tagline: false
    }
}).render('#payment_methods_options');
