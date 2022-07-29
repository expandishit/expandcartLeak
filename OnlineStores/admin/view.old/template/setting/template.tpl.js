$(function($) {
    $(document).on("click", "[data-modal='modal-reset-def']", function () {
        $('#reset-templ').parent().find('.server-loading').hide();
        $('#modal-reset-def').find('#reset-templ').attr('tempName', $(this).attr('tempName'));
    });
});


function changeTemplate(templateBox, tempName) {
    var resetToDef = false;

    if (templateBox === "") {
        templateName = tempName;
        resetToDef = true;
    } else {
        templateName = $(templateBox).parents('.template-box').attr("id");
    }

    $.ajax({
        type: 'get',
        url: changeTemplateURL,
        dataType: 'json',
        data: {basename: templateName},
        async: true,
        success: function(json) {
            if (json.success == "true") {
                $(".project-box.emerald-bg #apply").fadeIn("slow", function() {
                    $(this).replaceWith('<div id="apply" class="btn btn-success" onclick="changeTemplate(this);">' + txtApplyTemplate + '</div>');
                });

                $(".project-box.emerald-bg").fadeIn("slow", function() {
                    $(this).find('.resetDefault').remove();
                    $(this).removeClass("emerald-bg");
                    $(this).addClass("green-box");
                });

                $(".project-box-header.emerald-bg").fadeIn("slow", function() {
                    $(this).removeClass("emerald-bg");
                    $(this).addClass("green-bg");
                });

                $("#" + templateName + " .green-box").fadeIn("slow", function() {
                    $(this).removeClass("green-box");
                    $(this).addClass("emerald-bg");
                });

                $("#" + templateName + " .green-bg").fadeIn("slow", function() {
                    $(this).find('.name a').append('<button id="reset" type="submit" class="md-trigger btn btn-danger resetDefault pull-right" data-modal="modal-reset-def" data-container="body" tempName="' + templateName + '">' + text_reset_templ + '</button>');
                    $(this).removeClass("green-bg");
                    $(this).addClass("emerald-bg");
                });

                $("#" + templateName + " #apply").fadeIn("slow", function() {
                    $(this).replaceWith('<div id="apply" class="label label-success label-large">' + txtAlreadyApplied + '</div>');
                });

                if (json.guidestate == "true") {
                    showGuideCallout();
                }

                ModalEffects.reInit();
            }

            if (resetToDef) {
                $("#reset-templ").parents('.md-show').removeClass('md-show');
            }

            var notification = new NotificationFx({
                message : resetToDef ? text_template_reset : txtTemplateChanged,
                layout : 'growl',
                effect : 'jelly',
                ttl : 2500,
                type : 'success', // notice, warning, error or success
            });

            updateMenus();

            // show the notification
            notification.show();
        }
    });
}

function updateMenus()
{
    $.get( window.location.href, function( data ) {
        $('#sidebar-nav').replaceWith($(data).find('#sidebar-nav'));
    });
}

function resetDefault(templateName) {
    Pace.restart();
    changeTemplate("", templateName);
}

function showGuideCallout() {
    // Start the tour!
    if ($(document).width() >= 992) {
        var placementRight = 'right';
        var placementLeft = 'left';

        if ($('body').hasClass('rtl')) {
            placementRight = 'left';
            placementLeft = 'right';
        }

        mgr = hopscotch.getCalloutManager();

        setTimeout(function() {
            $('#dashboard').css('background-color', 'rgba(3, 169, 244, 0.37)');
            mgr.createCallout({
                id: "design-intro",
                target: 'dashboard',
                placement: placementRight,
                title: callout_title,
                content: callout_content,
                yOffset: -10,
                onClose: function() {
                    $('#dashboard').css('background-color', 'initial');
                }
            });
        }, 100);
    }
}

$(document).on("click", "#reset-templ", function () {
    $('#reset-templ').parent().find('.server-loading').show();

    var tempName = $(this).attr('tempName');

    resetDefault(tempName);
});