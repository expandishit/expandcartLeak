$(document).ready(function () {

    $('.updateSmsaStatus').click(function (event) {
        event.preventDefault();

        var that = $(this);

        var href = that.data('href');

        if (that.hasClass('inlineRows')) {
            var parent = that.parent().parent();
        } else {
            var parent = that.parent().parent().parent();
        }

        var smsaStatus = $('.smsaStatus', parent);

        smsaStatus.hide();
        smsaStatus.html('');

        $.ajax({
            url: href,
            dataType: 'json',
            success: function (response) {
                if (response['status'] == 'error') {
                    smsaStatus.append('<div class="warning">' +
                        response['errors'].join("<br />") +
                        '</div>').fadeIn();
                } else {
                    smsaStatus.html(response['statusString']).fadeIn();
                }
            }
        });
    });

});