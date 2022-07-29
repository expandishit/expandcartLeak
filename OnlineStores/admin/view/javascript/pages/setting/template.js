$(function($) {
    $(document).on("click", '[data-target="#modal-reset-def"]', function () {
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
        data: {basename: templateName, resetToDefault : resetToDef},
        async: true,
        success: function(json) {
            if ( json.refresh == '1' ) {
                window.location.reload(window.location);
                return;
            }

            if (json.success == "true") {
                $(".thumbnail.bg-green-300 #apply").fadeIn("slow", function() {
                    //$(this).replaceWith('<button id="apply" onclick="Pace.restart();changeTemplate(this);" class="btn btn-success btn-block"><i class="icon-brush position-left"></i> ' + txtApplyTemplate + '</button>');

                    $(this).replaceWith(
                        `<div id="apply" class="btn-group" style="width: 100%;">
                            <button onclick="Pace.restart();changeTemplate(this);"
                                class="btn btn-success btn-block" style="width: calc(100% - 37px);">
                                <i class="icon-brush position-left"></i> ${txtApplyTemplate}
                            </button>
                            <button type="button" class="btn btn-success dropdown-toggle"
                                data-toggle="dropdown" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <a target="_blank"
                                        href="${json.previous.previewlink}">
                                        <i class="icon-eye"></i> ${txtPreviewTemplate}
                                    </a>
                                </li>
                            </ul>
                        </div>`
                    );
                });

                $(".thumbnail.bg-green-300").fadeIn("slow", function() {
                    $(this).find('.icon-checkmark4').remove();
                    $(this).find('.heading-elements').remove();
                    $(this).removeClass("bg-green-300");
                });

                $("#" + templateName + " .thumbnail").fadeIn("slow", function() {
                    $(this).addClass("bg-green-300");
                    $('.breadcrumb-elements.not-collapsible .text-bold').text($('#' + templateName + ' .panel-title').text());
                    $('[data-target="#modal-reset-def"]').attr('tempName', templateName);
                    $(this).find('.panel-heading').append('<div class="heading-elements"><ul class="icons-list"><li><a data-action="reload" data-popup="tooltip" data-toggle="modal" data-target="#modal-reset-def" tempname="' + templateName + '" data-original-title="' + text_reset_templ + '"></a></li></ul></div>');
                    $(this).find('.panel-title').append('<i class="icon-checkmark4 text-white pull-left"></i>');
                });

                $("#" + templateName + " #apply").fadeIn("slow", function() {
                    $(this).replaceWith('<button id="apply" class="btn btn-default btn-block">' + txtAlreadyApplied + '</button>');
                });
            }

            if (resetToDef) {
                $('#modal-reset-def').modal('toggle');
            }

            notify('Sucess', 'success', resetToDef ? text_template_reset : txtTemplateChanged);
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
