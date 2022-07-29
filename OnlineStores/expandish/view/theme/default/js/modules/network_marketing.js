function fillData(ss)
{
    var _id = $('#customer_' + ss);

    $('#referral').val(_id.data('email'));
    $('#network_marketing_referral').val(_id.data('userid'));

    $('#referralContainer ul').html('');
    $('#referralContainer').hide();
}

$(document).ready(function () {
    var delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();

    var refIdChecker = $('#referralsLink').data('value');
    var errorMessageInvalidRefId = $('#referralsLink').data('error-invalid-refid');

    var refIdTmpValue = null;

    $('#network_marketing_referral').keyup(function () {

        var that = $(this);

        var value = that.val();

        var submitButton = $('.btn.btn-inline');

        if (refIdTmpValue == value) {
            return false;
        }

        if (value == 0) {
            submitButton.attr('disabled', false);
        } else {
            submitButton.attr('disabled', true);
        }

        var refValidator = function (value) {
            if (value.length < 8) {
                return false;
            }

            if (value.match(/REF\-(\d){4,}/g)) {
                return true;
            }

            return false;
        };

        that.css('border-color', '#a82020');

        if (refValidator(value)) {
            that.css('border-color', '#ccc');
            delay(function () {
                $.ajax({
                    url: refIdChecker,
                    method: 'POST',
                    data: {refid: value},
                    dataType: 'json',
                    success: function(response) {
                        if (response['status'] == 'success') {
                            submitButton.attr('disabled', false);
                            $('.refIdError').slideUp();
                        } else {
                            $('.refIdError').html(response.message);
                            $('.refIdError').slideDown();
                        }
                    }
                })
            }, 500);
        } else {
            delay(function () {
                $('.refIdError').html(errorMessageInvalidRefId);
                $('.refIdError').slideDown();
            }, 700);
        }

        refIdTmpValue = value;
    });



    /**
     * To prevent firing the `keyup` event when the user click ctrl + [a,c,v...]
     * */
    /*var tmpVal = 0;

    $('#referral').keyup(function (event) {
        var _val = $(this).val();

        if (_val.length >= 2 && _val.length != tmpVal) {

            tmpVal = _val.length;

            delay(function () {
                $.ajax({
                    url: referralsLink,
                    method: 'POST',
                    data: {referral: _val},
                    dataType: 'JSON',
                    success: function (response) {

                        if (response.status == 'success') {
                            $('#referralContainer').show();
                            var referralContainer = $('#referralContainer ul');

                            referralContainer.html('');

                            for (referralKey in response.referrals) {
                                var referral = response.referrals[referralKey];

                                var $append = '';

                                $append += '<li id="customer_' + referral['customer_id'] + '"';
                                $append += 'data-email="' + referral['email'] + '"';
                                $append += 'data-userid="' + referral['customer_id'] + '"';
                                $append += 'onclick="fillData(\'' + referral['customer_id'] + '\');"';
                                $append += '>' + referral['email'] + '</li>';


                                referralContainer.append($append);
                            }

                        }

                    }
                });
            }, 500);

        }
    });*/
});