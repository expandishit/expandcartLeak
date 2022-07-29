// group tables actions
$(document).ready(function() {
    // append dropdown
    let btnsdrop = '';
    btnsdrop += `<div class="dropdown table-btns-drop">`;
    btnsdrop += `<button type="button" >`;
    btnsdrop += `<i class="fas fa-ellipsis-h"></i>`;
    btnsdrop += `</button>`;
    btnsdrop += `<div class="dropdown-menu dropdown-menu-right">`;
    btnsdrop += `</div>`;
    btnsdrop += `</div>`;
    if($('.dt-buttons').length > 0) {
        $('.dt-buttons').each(function() {
            if($(window).width() > 1024) {
                if($(this).find('.dt-menu').length > 0) {
                    $(this).append(btnsdrop);
                }
                $(this).find('.dt-menu').appendTo($(this).find('.table-btns-drop .dropdown-menu'));
            } else {
                $(this).append(btnsdrop);
                $(this).children('*:not(.table-btns-drop)').appendTo($(this).find('.table-btns-drop .dropdown-menu'));
            }
        })
    }

    $('.table-btns-drop button').click(function(e) {
        $(this).parent().toggleClass('open');
    });

    $('.table-btns-drop a').click(function() {
        let elOffset = $(this).offset();
        let dropCollection = $('.dt-button-collection');
        
        if($(window).width() > 1024) {
            dropCollection.css({
                top: elOffset.top,
                left: ltr ? (elOffset.left - dropCollection.width()) : (elOffset.left + $(this).width())
            });
        } else {
            if(ltr) {
                dropCollection.css({
                    top: elOffset.top + $(this).outerHeight(),
                    left: elOffset.left + $(this).outerWidth() - dropCollection.outerWidth()
                });
            } else {
                dropCollection.css({
                    top: elOffset.top + $(this).outerHeight(),
                    left: elOffset.left
                });
            }
        }
        
        $('.dt-button-background').css({
            opacity: 0,
            visibility: 'hidden'
        })
    })

    $('.dt-buttons .disabled').on('click', function(e) {
        e.stopPropagation();
        console.log('ss')
    })
})


$(document).on("click", "[class*='intercom'] a", function(e) {
    e.preventDefault();
    let h = $(this).attr('href');
    $.ajax({
        url: 'common/home/allow_login',
        dataType: 'json',
        type: 'get',
        success: function (data) {
            if (data.status==1){
                window.location.href = h;
            }
        }
    });

});
$('body').on('click', function (e) {
    if (!$('.table-btns-drop .dropdown-menu > *').is(e.target) 
        && $('.table-btns-drop .dropdown-menu > *').has(e.target).length === 0 
        && $('.dt-button-collection').has(e.target).length === 0 
        && $('.open').has(e.target).length === 0
    ) {
        $('.table-btns-drop').removeClass('open');
    }
});
    $(document).ajaxStop(function() {
        if($('.has-error').length > 0) {
            let targetOffset = $('.has-error').eq(0).offset().top - 80;
            if(!$('.has-error').hasClass('stopscroll')) {
                $('html').animate({scrollTop: targetOffset})
            }
            
        }
    });
    
// Initialize jquery telephone js for all inputs that have class "jquery-intl-tel"
$(function () {
    var inputs = document.querySelectorAll('input.jquery-intl-tel');
    if (inputs.length && typeof $.fn.intlTelInput !== "undefined") {
        inputs.forEach(function (input) {
            var name = input.name.slice(0);
            input.setAttribute('name', '');
            input.setAttribute('data-name', name);
            $(input).intlTelInput({
                initialCountry: "auto",
                nationalMode: true,
                separateDialCode: !true,
                autoPlaceholder: "aggressive",
                formatOnDisplay: true,
                preferredCountries: [],
                responsiveDropdown: true,
                placeholderNumberType: "MOBILE",
                hiddenInput: name,
                utilsScript: "/admin/view/build/js/utils.js",
                geoIpLookup: function (callback) {
                    $.get('https://ipinfo.io', function () { }, "jsonp").always(function (resp) {
                        var countryCode = (resp && resp.country) ? resp.country : "us";
                        callback(countryCode);
                    });
                },
            });

            input.onkeypress = function (e) {
                e.stopPropagation ? e.stopPropagation() : (e.cancelBubble = !0);
                "number" != typeof e.which && (e.which = e.keyCode);
                if (
                    [43, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57].indexOf(
                        e.which
                    ) === -1
                ) {
                    e.preventDefault();
                    return false;
                }
            };

            input.onkeyup = function(event) {
                $(`input[name="${input.dataset.name}"]`).val($(input).intlTelInput('getNumber'));
            }
            
            // empty phone val when change country
            $(input).on('countrychange',function () {
                $(input).val('');
                $(`input[name="${input.dataset.name}"]`).val('');
            });
    
            if (input.value) {
                $(input).intlTelInput('setNumber', input.value);
                $(`input[name="${input.dataset.name}"]`).val($(input).intlTelInput('getNumber'));
            }  
        });
    }
});

// Reset for class
class ResetForm {
    initialForm = null;
    initialInputs = {};
    
    constructor(form = null) {
        this.initialForm = form;
        form && this._setInitialInputs();
    }
    
    getInitialForm() {
        return this.initialForm;
    }

    setInitialForm(form) {
        this.initialForm = form;
        this._setInitialInputs();
    }

    _setInitialInputs() {
        const form = this.getInitialForm();
        if (!form) return;

        setTimeout(() => {
            [...form].forEach((input) => {
                if ("BUTTON" === input.nodeName) return;

                var name = (input.nodeName == "INPUT" && input.type == "radio") ? (input.name + '_' + input.value) : input.name;
                
                this.initialInputs[name] = {};

                switch (input.nodeName) {
                    case "INPUT":
                        if (input.type == "text" || input.type == "hidden") {
                            this.initialInputs[name].value = input.value;
                        }

                        if ("tel" === input.type && "iti" in input) {
                            this.initialInputs[name].value = input.iti.getNumber();
                        }

                        if ("checkbox" === input.type) {
                            this.initialInputs[name].checked = input.checked;
                        }
                        
                        if ("radio" === input.type) {
                            this.initialInputs[name].checked = input.checked;
                        }

                        break;
                    case "SELECT":
                        this.initialInputs[name].value = input.value;
                        this.initialInputs[name].selectedIndex =
                            input.selectedIndex;
                        break;
                }
            });
        }, 1000);
    }

    reset(event = null, afterResetInput = function () { }) {
        event && event.preventDefault();
        event && event.stopPropagation();

        var form = this.getInitialForm();

        if (!form) return;

        [...form].forEach((input) => {
            if ("BUTTON" === input.nodeName) return;
            
            var name = (input.nodeName == "INPUT" && input.type == "radio") ? (input.name + '_' + input.value) : input.name;
            
            if (!name in this.initialInputs) return;
            
            switch (input.nodeName) {
                case "INPUT":
                    if (input.type == "text" || input.type == "hidden") {
                        input.value = this.initialInputs[name].value;
                    }

                    if ("tel" === input.type && "iti" in input) {
                        input.iti.setNumber(
                            this.initialInputs[name].value
                        );
                    }

                    if ("checkbox" === input.type) {
                        input.checked = this.initialInputs[name].checked;
                    }
                    
                    if ("radio" === input.type) {
                        input.checked = this.initialInputs[name].checked;
                    }

                    break;
                case "SELECT":
                    input.selectedIndex = this.initialInputs[name].selectedIndex;
                    break;
            }
            
            afterResetInput(input);
        });
    }
}
