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

function startLoadingScreen( msg )
{
    if ( !msg ) { msg = 'Loading...'; }
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

function ajax(url, postData, doneCallback, failCallback) {
    clearFormErrors();
    $.ajax({
        type: 'POST',
        url: url,
        data: postData
    })
    .done(function(response) {

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

        if (returnResult.success == '1') {
            notify('', 'success', returnResult.success_msg);
        } else {

            if(returnResult.errors){
                errorsObj = returnResult.errors;
            }else if(returnResult.error){
                errorsObj = returnResult.error;
            }

            if(errorsObj.error) {
                displayErrors(errorsObj.error);
            } else {
                var errorMsg = errorsObj.warning;
                for(var el in errorsObj) {
                    if($('#' + el + '-group').length <= 0 && el != "warning" && el != "error") {
                        errorMsg += "<br/> - " + errorsObj[el];
                    }
                }
                if (errorMsg && errorMsg != "") {
                    displayErrors(errorMsg);
                }
                applyFormErrors(errorsObj);
            }
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
    // Get the messages div.
    //var formMessages = $('#form-messages');

    // Set up an event listener for the contact form.
    $(form).submit(function(event) {
        // Stop the browser from submitting the form.
        event.preventDefault();

        // Serialize the form data.
        var formData = $('[type!=checkbox]', this).serialize();

        $('[type=checkbox]').each(function () {

            var name = $(this).attr('name');
            var value = $(this).is(':checked') ? 1 : 0;

            formData += '&' + name + '=' + value;
        });

        var doneCallback = function(response) {

        };

        var failCallback = function(data) {

        };

        // Submit the form using AJAX.
        ajax($(this).attr('action'), formData, doneCallback, failCallback);
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
