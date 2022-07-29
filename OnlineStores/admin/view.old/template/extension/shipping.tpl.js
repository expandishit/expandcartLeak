$(function($) {
    $('.main-box-header.psmethods').click(function () {
        $(this).parent().find('.main-box-body.psmethod-container').slideToggle();

        $(this).find('i').toggleClass("closed-icon");
    });

    $("[data-toggle='popover']").each(function (index, el) {
        $(el).popover({
            placement: $(this).data("placement") || 'top'
        });
    });

    $("[data-modal='modal-disable-ps']").click(function () {
        $('#modal-disable-ps').find('#disable-ps').attr('psid', $(this).attr('psid'));
        $('#modal-disable-ps').find('#ps-title').html($(this).attr('ps-title'));
    });

    $("[data-modal='modal-deactivate-ps']").click(function () {
        $('#modal-deactivate-ps').find('#deactivate-ps').attr('psid', $(this).attr('psid'));
        $('#modal-deactivate-ps').find('#ps-title').html($(this).attr('ps-title'));
    });

    $("[data-modal='modal-enable-ps']").click(function () {
        $('#modal-enable-ps').find('#enable-ps').attr('psid', $(this).attr('psid'));
        $('#modal-enable-ps').find('#ps-title').html($(this).attr('ps-title'));
    });

    $("#disable-ps").click(function () {
        $('#disable-ps').parent().find('.server-loading').show();

        $.ajax({
            type: 'get',
            url: disableURL,
            dataType: 'json',
            data: {psid: $('#disable-ps').attr('psid')},
            async: true,
            success: function(json) {
                if (json.success == "true") {
                    // create the notification
                    var notificationDS = new NotificationFx({
                        message : mDisabledSuccess,
                        layout : 'growl',
                        effect : 'jelly',
                        ttl : 2500,
                        type : 'success'
                    });

                    $('#disable-ps').parent().find('.server-loading').hide();

                    var $methodDiv = $(".method[psid='" + $("#disable-ps").attr('psid') + "']");

                    $methodDiv.find("[data-modal='modal-disable-ps']").hide();
                    $methodDiv.find("[data-modal='modal-enable-ps']").show();
                    $methodDiv.find(".product span.enabled").hide();
                    $methodDiv.find(".product span.disabled").show();

                    $("#disable-ps").parents('.md-show').removeClass('md-show');

                    // show the notification
                    notificationDS.show();
                }
                else {
                    // create the notification
                    var notificationDS = new NotificationFx({
                        message : json.error,
                        layout : 'growl',
                        effect : 'jelly',
                        ttl : 10000,
                        type : 'error'
                    });

                    $('#disable-ps').parent().find('.server-loading').hide();

                    $("#disable-ps").parents('.md-show').removeClass('md-show');

                    // show the notification
                    notificationDS.show();
                }
            }
        });
    });

    $("#enable-ps").click(function () {
        $('#enable-ps').parent().find('.server-loading').show();

        $.ajax({
            type: 'get',
            url: enableURL,
            dataType: 'json',
            data: {psid: $('#enable-ps').attr('psid')},
            async: true,
            success: function(json) {
                if (json.success == "true") {
                    // create the notification
                    var notificationDS = new NotificationFx({
                        message : mEnabledSuccess,
                        layout : 'growl',
                        effect : 'jelly',
                        ttl : 2500,
                        type : 'success'
                    });

                    $('#enable-ps').parent().find('.server-loading').hide();

                    var $methodDiv = $(".method[psid='" + $("#enable-ps").attr('psid') + "']");

                    $methodDiv.find("[data-modal='modal-enable-ps']").hide();
                    $methodDiv.find("[data-modal='modal-disable-ps']").show();
                    $methodDiv.find(".product span.disabled").hide();
                    $methodDiv.find(".product span.enabled").show();

                    $("#enable-ps").parents('.md-show').removeClass('md-show');

                    // show the notification
                    notificationDS.show();
                }
                else {
                    // create the notification
                    var notificationDS = new NotificationFx({
                        message : json.error,
                        layout : 'growl',
                        effect : 'jelly',
                        ttl : 10000,
                        type : 'error'
                    });

                    $('#enable-ps').parent().find('.server-loading').hide();

                    $("#enable-ps").parents('.md-show').removeClass('md-show');

                    // show the notification
                    notificationDS.show();
                }
            }
        });
    });

    $("#deactivate-ps").click(function () {
        $('#deactivate-ps').parent().find('.server-loading').show();
        window.location.replace(deactivateURL + $("#deactivate-ps").attr('psid'));
    });
});