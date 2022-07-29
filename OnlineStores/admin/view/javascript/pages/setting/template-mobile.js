$(function($) {
    $(document).on("click", '[data-target="#modal-reset-def"]', function () {
        $('#reset-templ').parent().find('.server-loading').hide();
        $('#modal-reset-def').find('#reset-templ').attr('tempName', $(this).attr('tempName'));
    });

    $(document).on("click", '[data-target="#modal-update-def"]', function () {
        $('#update-templ').parent().find('.server-loading').hide();
        $('#modal-update-def').find('#update-templ').attr('tempName', $(this).attr('tempName'));
    });

    $(document).on("click", '[data-target="#modal-apply-def"]', function () {
        $('#apply-templ').parent().find('.server-loading').hide();
        $('#modal-apply-def').find('#apply-templ').attr('tempName', $(this).attr('tempName'));
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
        data: {basename: templateName, src : 'ajax'},
        async: true,
        success: function(json) {
            if ( json.refresh == '1' ) {
                window.location.reload(window.location);
                return;
            }

            if(json.error == 'true'){
                $('#apply-templ').parent().find('.server-loading').hide();
                notify('Error', 'error', 'Directory missed!');
            }
        }
    });
}

function resetDefault(templateName) {
    Pace.restart();
    changeTemplate("", templateName);
}

$(document).on("click", "#reset-templ", function () {
    $('#reset-templ').parent().find('.server-loading').show();

    var tempName = $(this).attr('tempName');

    resetDefault(tempName);
});

$(document).on("click", "#apply-templ", function () {
    $('#apply-templ').parent().find('.server-loading').show();

    var tempName = $(this).attr('tempName');

    Pace.restart();
    changeTemplate("", tempName);
});

$(document).on("click", "#update-templ", function () {
    $('#update-templ').parent().find('.server-loading').show();

    var templateName = $(this).attr('tempName');

    Pace.restart();

    $.ajax({
        type: 'get',
        url: updateTemplateURL,
        dataType: 'json',
        data: {basename: templateName, src : 'ajax'},
        async: true,
        success: function(json) {
            if ( json.refresh == '1' ) {
                window.location.reload(window.location);
                return;
            }

            if(json.error == 'true'){
                $('#apply-templ').parent().find('.server-loading').hide();
                notify('Error', 'error', 'Directory missed!');
            }
        }
    });
});