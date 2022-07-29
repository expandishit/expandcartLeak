/* ------------------------------------------------------------------------------
*
*  # Template JS core
*
*  Core JS file with default functionality configuration
*
*  Version: 1.2
*  Latest update: Dec 11, 2015
*
* ---------------------------------------------------------------------------- */


// Allow CSS transitions when page is loaded
$(window).on('load', function() {
    $('body').removeClass('no-transitions');
});

// page loader function
$(window).on("load", function() {
	$('.page-loader').fadeOut()
	$('body').removeClass('overflow-hidden')
})

// This method takes in a params array that consists of objects that has key and value keys and updates the URL query params with them.
// Example Input [ { key: 'foo', value: 'bar' }, { key: 'baz', value: 'whatever' } ]
// Example Output http://qaz123.expandcart.com/admin/payment?foo=bar&baz=whatever
function updateURLQueryParams( params )
{
    if ( history.pushState )
    {

        var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?';

        if ( params != undefined )
        {

            for ( i in params )
            {
                if ( params[i].key == undefined || params[i].value == undefined )
                {
                    params.splice(i, 1);
                }
            }

            for (var i = params.length - 1; i >= 0; i--)
            {
                newurl += params[i].key + '=' + params[i].value;
                
                if ( i > 0 )
                {
                    newurl += '&';
                }
            }
        }

        window.history.pushState({path:newurl},'',newurl);
    }
}


function applyInfiniteScroll( Inf_Scroll_Config )
{
    var inf_scroll_container = $('.infinite-scroll-container');

    if ( Inf_Scroll_Config == undefined )
    {
        var Inf_Scroll_Config = {
            path: '.pagination__next',
            append: '.infinite-scroll-content',
            history: false,
            status: '.page-load-status',
            hideNav: '.pagination-wrapper',
            scrollThreshold: 750
        };
    }

    var the_container = $('.infinite-scroll-container');

    if ( the_container.length > 1 )
    {
        the_container = $('.infinite-scroll-container').last();
    }

    the_container.infiniteScroll(Inf_Scroll_Config);
}

function applyInfiniteScrollWithCallback(config, callback) {

    if (config == undefined || jQuery.isEmptyObject(config) || config == null) {
        var config = {
            path: '.pagination__next',
            append: '.infinite-scroll-content',
            history: false,
            status: '.page-load-status',
            hideNav: '.pagination-wrapper',
            scrollThreshold: 750
        };
    }

    var $container = $('.infinite-scroll-container');

    if ($container.length > 1) {
        $container = $('.infinite-scroll-container').last();
    }

    $container.infiniteScroll(config);

    if (typeof callback === 'function') {
        $container.on('append.infiniteScroll', function(event, response, path, items) {
            callback();
        });
    }
}


function startLoadingScreen( msg )
{
    if ( msg == undefined ) { msg = 'Loading...'; }
    var wrapper_div = $('body');

    wrapper_div.append('<div id="loading-screen-wrapper">');
    wrapper_div.append('<style class="loading-screen-style"> #loading-screen-wrapper { width: 100%; height: 100vh; position:fixed; z-index: 9999; background: rgba(0, 0, 0, 0.7); top:0; left:0; } </style>');
    wrapper_div.append('<style class="loading-screen-style"> #loading-screen { position:fixed; z-index:99999; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 2.5em; text-align: center; font-weight: bold; color: #e5e5e5; font-family:Tahoma, arial;  } </style>');
    wrapper_div.append('<div id="loading-screen"><i class="fa fa-spinner fa-spin fa-2x"></i><br> ' + msg + '</div>');
    wrapper_div.append('</div>');
}

function removeLoadingScreen()
{
    $('#loading-screen-wrapper').remove();
    $('#loading-screen').remove();
    $('.loading-screen-style').remove();
}

// Change Switchery's State
function setSwitchery(switchElement, checkedBool) {
    if (checkedBool && !switchElement.checked) { // switch on if not on
        $(switchElement).trigger('click').attr("checked", "checked");
    } else if (!checkedBool && switchElement.checked) { // switch off if not off
        $(switchElement).trigger('click').removeAttr("checked");
    }
}


function displayHideRelatedDiv(switchControl, divId) {
    if (switchControl.checked) {
        $("#" + divId).slideDown();
    } else {
        $("#" + divId).slideUp();
    }
}

function ajax(url, postData, doneCallback, failCallback, headers) {
    clearFormErrors();
    
    if($("#sending_mail_alert").length > 0){
        $("#sending_mail_alert").show();
    }
    if($("#adding_products").length > 0){
        $("#adding_products").show();
    }

    let ajaxObject = {
        type: 'POST',
        url: url,
        data: postData
    };

    if (typeof headers != 'undefined') {
        ajaxObject['headers'] = headers;
    }

    $.ajax(ajaxObject)
    .done(function(response) {
        // removeLoadingScreen();
        if($("#sending_mail_alert").length > 0){
            $("#sending_mail_alert").hide();
        }
        if($("#adding_products").length > 0){
            $("#adding_products").hide();
        }

        try {
            var returnResult = JSON.parse(response);

            if ( returnResult.redirect == '1' )
            {
                if ( returnResult.success == '1' )
                {
                    notify('', 'success', returnResult.success_msg);
                }
    
                window.location.href = returnResult.to;
                return;
            }
    
            if (returnResult.success == '1' || returnResult.success == 'success') {
                notify('', 'success', returnResult.success_msg);
                // window,location.reload()
                if($('#app_status').val() == 0) {
                    window.location.reload()
                }
            } else {
    
                if(returnResult.duplicate == 1) {
                    // show alert
                    $('#duplicate_confirm').modal({
                        show: true,
                        backdrop: 'static',
                        keyboard: false
                    })
                }
    
                let errorsObj = [];
    
                if(returnResult.errors){
                    errorsObj = returnResult.errors;
                }else if(returnResult.error){
                    errorsObj = returnResult.error;
                }
    
                if(errorsObj.error) {
                    displayErrors(errorsObj.error);
                } else {
                    var errorMsg = errorsObj.warning;
                    if (typeof errorsObj == "string") {
                        errorMsg = errorsObj;
                    } else {
                        if (errorMsg === undefined) {
                            errorMsg = returnResult.title;
                        }
                        for(var el in errorsObj) {
                            if($('#' + el + '-group').length <= 0 && el != "warning" && el != "error") {
                                errorMsg += "<br/> - " + errorsObj[el];
                            }
                        }
                    }
                    if (errorMsg && errorMsg != "") {
                        console.log(errorMsg);
                        displayErrors(errorMsg);
                    }
                    applyFormErrors(errorsObj);
                }
                $("html").animate({ scrollTop: 0 }, "slow");
            }
        } catch (error) {
            // catch invalid json response error    
        }
        
        if (typeof doneCallback === 'function') { doneCallback(response); }
    })
    .fail(function(data) {
        if (typeof failCallback === 'function') { failCallback(data); }
    });
}

function Get(url, postData, doneCallback, failCallback) {
    $.ajax({
        type: 'GET',
        url: url,
        data: postData,
        async: false
    })
        .done(function(response) {
            if (typeof doneCallback === 'function') { doneCallback(response); }
        })
        .fail(function(data) {
            if (typeof failCallback === 'function') { failCallback(data); }
        });
}

function notify(title, type, message) {
    if(!title || 0 === title.length) {
        title = locales['notif_title'];
    }
    PNotify.prototype.options.delay = 1000;
    new PNotify({
        title: title,
        text: message,
        addclass: 'alert alert-styled-left alert-success',
        type: type,
        buttons: {
            closer_hover: false,
            sticker_hover: false
        }
    });
}
//TODO: Still work in progress
$(function() {
    var elems;
    // Convert all switcheries to the new switch style.
    if (Array.prototype.forEach)
    {
        elems = Array.prototype.slice.call(document.querySelectorAll('.switchery'));
        elems.forEach(function(html) {
            var switchery = new Switchery(html);
        });
    }
    else {
        elems = document.querySelectorAll('.switchery');
        for (var i = 0; i < elems.length; i++) {
            var switchery = new Switchery(elems[i]);
        }
    }

    $('.styled').uniform();

    // Get the form.
    var form = $('.form');

    // Get the form.
    var combined_inputs_form = $('.combined_inputs_form');
    // Get the messages div.
    //var formMessages = $('#form-messages');

    // Set up an event listener for the contact form.
    form.submit(function(event) {
        // Stop the browser from submitting the form.
        event.preventDefault();
        event.stopPropagation();
        //debugger;
        Ladda.startAll();

        // Serialize the form data.   
        var formData = $('[type!=checkbox]', this).serialize();

        $('[type=checkbox]').each(function () {

            var name = $(this).attr('name');
            var value = $(this).is(':checked') ? 1 : 0;

            formData += '&' + name + '=' + value;
        });
        
        var doneCallback = function(response) {
            Ladda.stopAll();
            // $('form:dirty').dirtyForms('setClean');
        };

        var failCallback = function(data) {
            Ladda.stopAll();
            //$('form:dirty').dirtyForms('setClean');
        };

        // Submit the form using AJAX.
        ajax($(this).attr('action'), formData, doneCallback, failCallback, typeof customHeaders !== 'undefined' ? customHeaders : {});
    });

    // Set up an event listener for the contact combined_inputs_form.
    combined_inputs_form.submit(function(event) {
        // Stop the browser from submitting the combined_inputs_form.
        event.preventDefault();
        event.stopPropagation();
        //debugger;
        Ladda.startAll();

        // Serialize the combined_inputs_form data.
        var formData=$("[type!=checkbox]", this).serializeArray();
        
        for(i=0;i<formData.length;i++){
            if(!formData[i].name == 'product_description' ){
            if (formData[i].value.indexOf('&nbsp;') > -1){
                var searchStr = "&nbsp;";
                var replaceStr = " ";
                var re = new RegExp(searchStr, "g");
                formData[i].value = formData[i].value.replace(re, replaceStr);
            }
            if (formData[i].value.indexOf('&') > -1)
            {
                var searchStr = "&";
                var replaceStr = "%26";
                var re = new RegExp(searchStr, "g");
                formData[i].value = formData[i].value.replace(re, replaceStr);
            }
            if (formData[i].value.indexOf('%') > -1)
            {
                var searchStr = "%";
                var replaceStr = " %25 ";
                var re = new RegExp(searchStr, "g");
                formData[i].value = formData[i].value.replace(re, replaceStr);
            }
        }
    }
        $('[type=checkbox]').each(function () {
            var name = $(this).attr('name');
            var value = $(this).is(':checked') ? 1 : 0;
            formData.push({ 'name':name,'value':value});
        });

        var formData = JSON.stringify(formData);

        formData='inputs='+encodeURIComponent(formData);
        
        var doneCallback = function(response) {
            Ladda.stopAll();
            // $('form:dirty').dirtyForms('setClean');
        };

        var failCallback = function(data) {
            Ladda.stopAll();
            //$('form:dirty').dirtyForms('setClean');
        };

        // Submit the form using AJAX.
        ajax($(this).attr('action'), formData, doneCallback, failCallback, {
            "X-EC-FORM-INPUTS": "COMBINED"
        });
    });
    
    $('.switchery').on("change" , function(e) {
        if(this.checked) {
            if($(this).data('on-text')) $(this).parent().children(".switchery-status").text($(this).data('on-text'));
        } else {
            if($(this).data('off-text')) $(this).parent().children(".switchery-status").text($(this).data('off-text'));
        }
    });

    $('.switchery').each(function () {
        if(this.checked) {
            if($(this).data('on-text')) $(this).parent().children(".switchery-status").text($(this).data('on-text'));
        } else {
            if($(this).data('off-text')) $(this).parent().children(".switchery-status").text($(this).data('off-text'));
        }
    });

    Ladda.bind('.btn-ladda-spinner');
    // $('form.form').dirtyForms({
    //     dialog: {
    //         title: locales['dirty_title'],
    //         stayButtonText: locales['dirty_stay'],
    //         proceedButtonText: locales['dirty_leave'],
    //         styling: 'fontawesome'
    //     },
    //     message: locales['dirty_message']
    // });
    // var $actionButtons = $('.top-save-button, .top-cancel-button, .bottom-save-button, .bottom-cancel-button');
    // $('form.form').on('dirty.dirtyforms clean.dirtyforms', function (ev) {
    //     if (ev.type === 'dirty') {
    //         $actionButtons.removeAttr('disabled');
    //     } else {
    //         $actionButtons.attr('disabled', 'disabled');
    //     }
    // });
    // $('.top-cancel-button, .bottom-cancel-button').on('click', function() {
    //     $('form:dirty').dirtyForms('setClean');
    // });

});

//TODO: localize the strings and implement "customConfirmMessage" func
function confirmMessage(confirmCallbackFunc, cancelCallBackFunc, data) {
    //set default values
    if(typeof data == "undefined") {
        data = {
            title: locales['cm_title'],
            text: locales['cm_text'],
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#EF5350",
            confirmButtonText: locales['cm_confirm_button'],
            cancelButtonText: locales['cm_cancel_button'],
            closeOnConfirm: true,
        };
    }
    swal({
            title: data.title ? data.title : locales['cm_title'],
            text: data.text ? data.text : locales['cm_text'],
            type: data.type ? data.type : "warning",
            icon: data.icon ? data.icon : '<i class="fas fa-lock"></i>',
            showCancelButton: data.showCancelButton ? data.showCancelButton : true,
            confirmButtonColor: data.confirmButtonColor ? data.confirmButtonColor : "#EF5350",
            confirmButtonText: data.confirmButtonText ? data.confirmButtonText : locales['cm_confirm_button'],
            cancelButtonText: data.cancelButtonText ? data.cancelButtonText : locales['cm_cancel_button'],
            closeOnConfirm: typeof data.closeOnConfirm !== 'undefined' ? data.closeOnConfirm : true,
        },
        function(isConfirm){
            if (isConfirm) {
                if (typeof confirmCallbackFunc === 'function') { confirmCallbackFunc(); }
            }
            else {
                if (typeof cancelCallBackFunc === 'function') { cancelCallBackFunc(); }
            }
        }
    );
}

$(document).ready(function () {
    $('.selectBasic').select2();
    $('select[multiple]').attr('data-placeholder', locales['tags_placeholder']);
});
function displayErrors(errors) {
    var errArea = $('#error-area');
    errArea.empty();
    //debugger;
    errArea.append('<div class="alert alert-danger alert-styled-left alert-bordered"><button type="button" class="close" data-dismiss="alert"><span>&times;</span><span class="sr-only">Close</span></button>' + errors + '</div>');
}

function applyFormErrors(errors) {
    //clearFormErrors();
    for(var el in errors) {
        var $el = $('#' + el + '-group');
        $el.addClass("has-error");
        $el.children(".help-block").append(errors[el]);
    }
}

function clearFormErrors() {
    $('#error-area').empty();
    var elErrors = $(".has-error");
    elErrors.removeClass("has-error");
    elErrors.children(".help-block").empty();
}

function navigateStatus(obj) {
    var self = $(obj['object']);

    var switch_status = self.siblings('.switchery-status');

    if (self.is(':checked')) {
        switch_status.html(
            (typeof obj['enabled'] === 'undefined') ? locales['switch_text_enabled'] : obj['enabled']
        );
    }
    else {
        switch_status.html(
            (typeof obj['disabled'] === 'undefined') ? locales['switch_text_disabled'] : obj['disabled']
        );
    }
}

$(function() {

    // Disable CSS transitions on page load
    $('body').addClass('no-transitions');



    // ========================================
    //
    // Content area height
    //
    // ========================================


    // Calculate min height
    function containerHeight() {
        if($('.page-container').length > 0) {
            var availableHeight = $(window).height() - $('.page-container').offset().top - $('.navbar-fixed-bottom').outerHeight();
    
            $('.page-container').attr('style', 'min-height:' + availableHeight + 'px');
        }
    }

    // Initialize
    containerHeight();




    // ========================================
    //
    // Heading elements
    //
    // ========================================


    // Heading elements toggler
    // -------------------------

    // Add control button toggler to page and panel headers if have heading elements
    $('.panel-footer').has('> .heading-elements:not(.not-collapsible)').prepend('<a class="heading-elements-toggle"><i class="icon-more"></i></a>');
    $('.page-title, .panel-title').parent().has('> .heading-elements:not(.not-collapsible)').children('.page-title, .panel-title').append('<a class="heading-elements-toggle"><i class="icon-more"></i></a>');


    // Toggle visible state of heading elements
    $('.page-title .heading-elements-toggle, .panel-title .heading-elements-toggle').on('click', function() {
        $(this).parent().parent().toggleClass('has-visible-elements').children('.heading-elements').toggleClass('visible-elements');
    });
    $('.panel-footer .heading-elements-toggle').on('click', function() {
        $(this).parent().toggleClass('has-visible-elements').children('.heading-elements').toggleClass('visible-elements');
    });



    // Breadcrumb elements toggler
    // -------------------------

    // Add control button toggler to breadcrumbs if has elements
    //$('.breadcrumb-line').has('.breadcrumb-elements').prepend('<a class="breadcrumb-elements-toggle"><i class="icon-menu-open"></i></a>');


    // Toggle visible state of breadcrumb elements
    //$('.breadcrumb-elements-toggle').on('click', function() {
    //    $(this).parent().children('.breadcrumb-elements').toggleClass('visible-elements');
    //});




    // ========================================
    //
    // Navbar
    //
    // ========================================


    // Navbar navigation
    // -------------------------

    // Prevent dropdown from closing on click
    $(document).on('click', '.dropdown-content', function (e) {
        e.stopPropagation();
    });

    // Disabled links
    $('.navbar-nav .disabled a').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    // Show tabs inside dropdowns
    $('.dropdown-content a[data-toggle="tab"]').on('click', function (e) {
        $(this).tab('show');
    });




    // ========================================
    //
    // Element controls
    //
    // ========================================


    // Reload elements
    // -------------------------

    // Panels
    $('.panel [data-action=reload]').click(function (e) {
        e.preventDefault();
        var block = $(this).parent().parent().parent().parent().parent();
        $(block).block({ 
            message: '<i class="icon-spinner2 spinner"></i>',
            overlayCSS: {
                backgroundColor: '#fff',
                opacity: 0.8,
                cursor: 'wait',
                'box-shadow': '0 0 0 1px #ddd'
            },
            css: {
                border: 0,
                padding: 0,
                backgroundColor: 'none'
            }
        });

        // For demo purposes
        window.setTimeout(function () {
           $(block).unblock();
        }, 2000); 
    });


    // Sidebar categories
    $('.category-title [data-action=reload]').click(function (e) {
        e.preventDefault();
        var block = $(this).parent().parent().parent().parent();
        $(block).block({ 
            message: '<i class="icon-spinner2 spinner"></i>',
            overlayCSS: {
                backgroundColor: '#000',
                opacity: 0.5,
                cursor: 'wait',
                'box-shadow': '0 0 0 1px #000'
            },
            css: {
                border: 0,
                padding: 0,
                backgroundColor: 'none',
                color: '#fff'
            }
        });

        // For demo purposes
        window.setTimeout(function () {
           $(block).unblock();
        }, 2000); 
    }); 


    // Light sidebar categories
    $('.sidebar-default .category-title [data-action=reload]').click(function (e) {
        e.preventDefault();
        var block = $(this).parent().parent().parent().parent();
        $(block).block({ 
            message: '<i class="icon-spinner2 spinner"></i>',
            overlayCSS: {
                backgroundColor: '#fff',
                opacity: 0.8,
                cursor: 'wait',
                'box-shadow': '0 0 0 1px #ddd'
            },
            css: {
                border: 0,
                padding: 0,
                backgroundColor: 'none'
            }
        });

        // For demo purposes
        window.setTimeout(function () {
           $(block).unblock();
        }, 2000); 
    }); 



    // Collapse elements
    // -------------------------

    //
    // Sidebar categories
    //

    // Hide if collapsed by default
    $('.category-collapsed').children('.category-content').hide();


    // Rotate icon if collapsed by default
    $('.category-collapsed').find('[data-action=collapse]').addClass('rotate-180');


    // Collapse on click
    $('.category-title').click(function (e) {
        e.preventDefault();
        var $this = $(this).find('[data-action=collapse]');
        if(!$($this).parents('.sidebar').hasClass('sidebar-normal')) return;
        var $categoryCollapse = $($this).parent().parent().parent().nextAll();
        $($this).parents('.category-title').toggleClass('category-collapsed');
        $($this).toggleClass('rotate-180');

        containerHeight(); // adjust page height

        $categoryCollapse.slideToggle(150);
    });

    $('.category-title').click(function (e) {
        e.preventDefault();
        if($(this).parents('.sidebar').hasClass('sidebar-normal')) return;
        var $categoryCollapse;

        var collapseMenu = !$(this).hasClass('category-collapsed');
        if(!collapseMenu) {
            // close all menus
            $(this).parents('.sidebar-content').find('.category-title').addClass('category-collapsed');
            var $allCategories = $('.category-collapsed').nextAll();
            $allCategories.slideUp(150);
            $(this).parents('.sidebar-content').find('[data-action=collapse]').addClass('rotate-180');

            //open only one menu
            $categoryCollapse = $(this).nextAll();
            $(this).toggleClass('category-collapsed');
            $(this).find("[data-action=collapse]").toggleClass('rotate-180');


            containerHeight();

            $categoryCollapse.slideToggle(150);
        } else {
            $categoryCollapse = $(this).nextAll();
            $(this).toggleClass('category-collapsed');
            $(this).find("[data-action=collapse]").toggleClass('rotate-180');


            containerHeight();

            $categoryCollapse.slideToggle(150);
        }
        //$(this).find("[data-action=collapse]").click();
    });

    //
    // Panels
    //

    // Hide if collapsed by default
    $('.panel-collapsed').children('.panel-heading').nextAll().hide();


    // Rotate icon if collapsed by default
    $('.panel-collapsed').find('[data-action=collapse]').addClass('rotate-180');


    // Collapse on click
    $('.panel [data-action=collapse]').click(function (e) {
        e.preventDefault();
        var $panelCollapse = $(this).parent().parent().parent().parent().nextAll();
        $(this).parents('.panel').toggleClass('panel-collapsed');
        $(this).toggleClass('rotate-180');

        containerHeight(); // recalculate page height

        $panelCollapse.slideToggle(150);
    });



    // Remove elements
    // -------------------------

    // Panels
    $('.panel [data-action=close]').click(function (e) {
        e.preventDefault();
        var $panelClose = $(this).parent().parent().parent().parent().parent();

        containerHeight(); // recalculate page height

        $panelClose.slideUp(150, function() {
            $(this).remove();
        });
    });


    // Sidebar categories
    $('.category-title [data-action=close]').click(function (e) {
        e.preventDefault();
        var $categoryClose = $(this).parent().parent().parent().parent();

        containerHeight(); // recalculate page height

        $categoryClose.slideUp(150, function() {
            $(this).remove();
        });
    });




    // ========================================
    //
    // Main navigation
    //
    // ========================================


    // Main navigation
    // -------------------------

    // Add 'active' class to parent list item in all levels
    $('.navigation').find('li.active').parents('li').addClass('active');

    // Hide all nested lists
    $('.navigation').find('li').not('.active, .category-title').has('ul').children('ul').addClass('hidden-ul');

    // Highlight children links
    $('.navigation').find('li').has('ul').children('a').addClass('has-ul');

    // Add active state to all dropdown parent levels
    $('.dropdown-menu:not(.dropdown-content), .dropdown-menu:not(.dropdown-content) .dropdown-submenu').has('li.active').addClass('active').parents('.navbar-nav .dropdown:not(.language-switch), .navbar-nav .dropup:not(.language-switch)').addClass('active');

    

    // Main navigation tooltips positioning
    // -------------------------

    // Left sidebar
    $('.navigation-main > .navigation-header > i').tooltip({
        placement: 'right',
        container: 'body'
    });



    // Collapsible functionality
    // -------------------------

    // Main navigation
    $('.navigation-main').find('li').has('ul').children('a').on('click', function (e) {
        e.preventDefault();

        // Collapsible
        $(this).parent('li').not('.disabled').not($('.sidebar-xs').not('.sidebar-xs-indicator').find('.navigation-main').children('li')).toggleClass('active').children('ul').slideToggle(250);

        // Accordion
        if ($('.navigation-main').hasClass('navigation-accordion')) {
            $(this).parent('li').not('.disabled').not($('.sidebar-xs').not('.sidebar-xs-indicator').find('.navigation-main').children('li')).siblings(':has(.has-ul)').removeClass('active').children('ul').slideUp(250);
        }
    });

    // show acive list on load
    $('.navigation').find('li.active').has('ul').children('ul').slideDown(0);

        
    // Alternate navigation
    $('.navigation-alt').find('li').has('ul').children('a').on('click', function (e) {
        e.preventDefault();

        // Collapsible
        $(this).parent('li').not('.disabled').toggleClass('active').children('ul').slideToggle(200);

        // Accordion
        if ($('.navigation-alt').hasClass('navigation-accordion')) {
            $(this).parent('li').not('.disabled').siblings(':has(.has-ul)').removeClass('active').children('ul').slideUp(200);
        }
    }); 




    // ========================================
    //
    // Sidebars
    //
    // ========================================


    // Mini sidebar
    // -------------------------

    // Toggle mini sidebar
    $('.sidebar-main-toggle').on('click', function (e) {
        e.preventDefault();

        // Toggle min sidebar class
        $('body').toggleClass('sidebar-xs');
    });



    // Sidebar controls
    // -------------------------

    // Disable click in disabled navigation items
    $(document).on('click', '.navigation .disabled a', function (e) {
        e.preventDefault();
    });


    // Adjust page height on sidebar control button click
    $(document).on('click', '.sidebar-control', function (e) {
        containerHeight();
    });


    // Hide main sidebar in Dual Sidebar
    $(document).on('click', '.sidebar-main-hide', function (e) {
        e.preventDefault();
        $('body').toggleClass('sidebar-main-hidden');
    });


    // Toggle second sidebar in Dual Sidebar
    $(document).on('click', '.sidebar-secondary-hide', function (e) {
        e.preventDefault();
        $('body').toggleClass('sidebar-secondary-hidden');
    });


    // Hide detached sidebar
    $(document).on('click', '.sidebar-detached-hide', function (e) {
        e.preventDefault();
        $('body').toggleClass('sidebar-detached-hidden');
    });


    // Hide all sidebars
    $(document).on('click', '.sidebar-all-hide', function (e) {
        e.preventDefault();

        $('body').toggleClass('sidebar-all-hidden');
    });



    //
    // Opposite sidebar
    //

    // Collapse main sidebar if opposite sidebar is visible
    $(document).on('click', '.sidebar-opposite-toggle', function (e) {
        e.preventDefault();

        // Opposite sidebar visibility
        $('body').toggleClass('sidebar-opposite-visible');

        // If visible
        if ($('body').hasClass('sidebar-opposite-visible')) {

            // Make main sidebar mini
            $('body').addClass('sidebar-xs');

            // Hide children lists
            $('.navigation-main').children('li').children('ul').css('display', '');
        }
        else {

            // Make main sidebar default
            $('body').removeClass('sidebar-xs');
        }
    });


    // Hide main sidebar if opposite sidebar is shown
    $(document).on('click', '.sidebar-opposite-main-hide', function (e) {
        e.preventDefault();

        // Opposite sidebar visibility
        $('body').toggleClass('sidebar-opposite-visible');
        
        // If visible
        if ($('body').hasClass('sidebar-opposite-visible')) {

            // Hide main sidebar
            $('body').addClass('sidebar-main-hidden');
        }
        else {

            // Show main sidebar
            $('body').removeClass('sidebar-main-hidden');
        }
    });


    // Hide secondary sidebar if opposite sidebar is shown
    $(document).on('click', '.sidebar-opposite-secondary-hide', function (e) {
        e.preventDefault();

        // Opposite sidebar visibility
        $('body').toggleClass('sidebar-opposite-visible');

        // If visible
        if ($('body').hasClass('sidebar-opposite-visible')) {

            // Hide secondary
            $('body').addClass('sidebar-secondary-hidden');

        }
        else {

            // Show secondary
            $('body').removeClass('sidebar-secondary-hidden');
        }
    });


    // Hide all sidebars if opposite sidebar is shown
    $(document).on('click', '.sidebar-opposite-hide', function (e) {
        e.preventDefault();

        // Toggle sidebars visibility
        $('body').toggleClass('sidebar-all-hidden');

        // If hidden
        if ($('body').hasClass('sidebar-all-hidden')) {

            // Show opposite
            $('body').addClass('sidebar-opposite-visible');

            // Hide children lists
            $('.navigation-main').children('li').children('ul').css('display', '');
        }
        else {

            // Hide opposite
            $('body').removeClass('sidebar-opposite-visible');
        }
    });


    // Keep the width of the main sidebar if opposite sidebar is visible
    $(document).on('click', '.sidebar-opposite-fix', function (e) {
        e.preventDefault();

        // Toggle opposite sidebar visibility
        $('body').toggleClass('sidebar-opposite-visible');
    });



    // Mobile sidebar controls
    // -------------------------

    // Toggle main sidebar
    $('.sidebar-mobile-main-toggle').on('click', function (e) {
        e.preventDefault();
        $('body').toggleClass('sidebar-mobile-main').removeClass('sidebar-mobile-secondary sidebar-mobile-opposite sidebar-mobile-detached');
    });


    // Toggle secondary sidebar
    $('.sidebar-mobile-secondary-toggle').on('click', function (e) {
        e.preventDefault();
        $('body').toggleClass('sidebar-mobile-secondary').removeClass('sidebar-mobile-main sidebar-mobile-opposite sidebar-mobile-detached');
    });


    // Toggle opposite sidebar
    $('.sidebar-mobile-opposite-toggle').on('click', function (e) {
        e.preventDefault();
        $('body').toggleClass('sidebar-mobile-opposite').removeClass('sidebar-mobile-main sidebar-mobile-secondary sidebar-mobile-detached');
    });


    // Toggle detached sidebar
    $('.sidebar-mobile-detached-toggle').on('click', function (e) {
        e.preventDefault();
        $('body').toggleClass('sidebar-mobile-detached').removeClass('sidebar-mobile-main sidebar-mobile-secondary sidebar-mobile-opposite');
    });



    // Mobile sidebar setup
    // -------------------------

    $(window).on('resize', function() {
        setTimeout(function() {
            containerHeight();
            
            if($(window).width() <= 768) {

                // Add mini sidebar indicator
                $('body').addClass('sidebar-xs-indicator');

                // Place right sidebar before content
                $('.sidebar-opposite').insertBefore('.content-wrapper');

                // Place detached sidebar before content
                $('.sidebar-detached').insertBefore('.content-wrapper');

                if($('#order-filter-sidebar').length > 0) {
                    $('#order-filter-sidebar').insertBefore('#product-grid');
                }

                // Add mouse events for dropdown submenus
                $('.dropdown-submenu').on('mouseenter', function() {
                    $(this).children('.dropdown-menu').addClass('show');
                }).on('mouseleave', function() {
                    $(this).children('.dropdown-menu').removeClass('show');
                });
            }
            else {

                // Remove mini sidebar indicator
                $('body').removeClass('sidebar-xs-indicator');

                // Revert back right sidebar
                $('.sidebar-opposite').insertAfter('.content-wrapper');

                // Remove all mobile sidebar classes
                $('body').removeClass('sidebar-mobile-main sidebar-mobile-secondary sidebar-mobile-detached sidebar-mobile-opposite');

                // Revert left detached position
                if($('body').hasClass('has-detached-left')) {
                    $('.sidebar-detached').insertBefore('.container-detached');
                }

                // Revert right detached position
                else if($('body').hasClass('has-detached-right')) {
                    $('.sidebar-detached').insertAfter('.container-detached');
                }

                // Remove visibility of heading elements on desktop
                $('.page-header-content, .panel-heading, .panel-footer').removeClass('has-visible-elements');
                $('.heading-elements').removeClass('visible-elements');

                // Disable appearance of dropdown submenus
                $('.dropdown-submenu').children('.dropdown-menu').removeClass('show');
            }
        }, 100);
    }).resize();




    // ========================================
    //
    // Other code
    //
    // ========================================


    // Plugins
    // -------------------------

    // Popover
    $('[data-popup="popover"]').popover();


    // Tooltip
    $('[data-popup="tooltip"]').tooltip();

    // Clipboard
    var clipboard = new ClipboardJS('.btn-clipboard');
    clipboard.on('success', function(e) {
        //e.preventDefault();
        e.clearSelection();
        changeButtonText(e.trigger, "Copied!");
    });
    $('.btn-clipboard').on('click', function(e) {
        e.preventDefault();
    });

});
function changeButtonText(objButton, text) {
    //debugger;
    textToChangeBackTo = objButton.innerHTML;
    objButton.innerHTML = text;
    setTimeout(function() { back(objButton, textToChangeBackTo); }, 1500);
    function back(button, textToChangeBackTo){ button.innerHTML = textToChangeBackTo; }
}

$(document).ready(function() {

});
