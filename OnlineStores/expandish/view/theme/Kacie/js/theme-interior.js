jQuery(function (jQuery) {

    //'use strict';

    // Sticky Header

    //var jQuery = jQuery.noConflict();


    /**
    * ==============================
    * Page Loader
    * ==============================
    */

    function addCSS(css){

        var head    = document.getElementsByTagName('head')[0],
            stylez       = document.createElement('style');

        stylez.setAttribute('type', 'text/css');

        if (stylez.styleSheet) {
            stylez.styleSheet.cssText = css;
        } else {                        
            stylez.appendChild(document.createTextNode(css));
        }

        head.appendChild(stylez);
    }

     addCSS('<style>#full-site-wrapper { display: none; opacity: 0; }</style>')

    jQuery(window).load(function () {
        
        setTimeout(function() {
            jQuery("#loading").fadeOut(300);
            jQuery("#full-site-wrapper").show().animate({
                opacity: 1
            }, 150)
        }, 3100);


        /**
        * ==============================
        * Custom Scroll Style
        * ==============================
        */

        if (jQuery(".header-wrap").length) {
            jQuery(".header-wrap").mCustomScrollbar({
                theme: "dark-2",
                scrollButtons: {
                    enable: false
                }
            });
        }


        /**
        * ==============================
        * ISOTOPE
        * ==============================
        */


        if (jQuery().isotope) {
            var jQuerycontainer = jQuery('.isotope'); // cache container
            jQuerycontainer.isotope({
                itemSelector: '.isotope-item'
            });
            jQuery('.filtrable a').click(function () {
                var selector = jQuery(this).attr('data-filter');
                jQuery('.filtrable li').removeClass('current');
                jQuery(this).parent().addClass('current');
                jQuerycontainer.isotope({filter: selector});
                return false;
            });
            jQuerycontainer.isotope('layout');  // layout/layout
        }


        jQuery(window).resize(function () {
            if (jQuery().isotope) {
                jQuery('.row.isotope').isotope('layout'); // layout/relayout on window resize
            }
            return false;
        });

        jQuery('#product-filter').isotope({filter: '.tab-1'});

    });


    jQuery(window).scroll(function () {

        /**
        * ==============================
        * Scroll To Top 
        * ==============================
        */

        if (jQuery(this).scrollTop() > 100) {
            jQuery('.to-top').css({bottom: '55px'});
        } else {
            jQuery('.to-top').css({bottom: '-150px'});
        }

    });

    jQuery(function (jQuery) {

        /**
        * ==============================
        * Remove Active Class
        * ==============================
        */

        jQuery(document).click(function (e) {
            var active = e.target ? jQuery(e.target).closest('.active').get(0) : null;
            jQuery(".header-wrap").removeClass('off-canvas');
            jQuery("body").removeClass('off-canvas-body');
            jQuery(".top-elements li" + ",.nav-trigger").filter(function () {
                return !active || active !== this;
            }).removeClass('active');
        });


        /**
        * ==============================
        * Scroll To Top Animate
        * ==============================
        */

        jQuery('.to-top').click(function () {
            jQuery('html, body').animate({scrollTop: 0}, 800);
            return false;
        });


        /**
        * ==============================
        * Header Off Canvas Add
        * ==============================
        */

        jQuery(".nav-trigger").on("click", function (e) {
            e.stopPropagation();
            jQuery(".header-wrap").toggleClass("off-canvas");
            jQuery("body").toggleClass("off-canvas-body");
        });


        /**
        * ==============================
        * Product Thumbnails Hover
        * ==============================
        */

        jQuery('.product-thumbnails').on('click', 'li', function () {
            jQuery('.product-thumbnails li.active').removeClass('active');
            jQuery(this).addClass('active');
        });


        /**
        * ==============================
        * Header PopUps
        * ==============================
        */

        jQuery(".search-hover, .toggle-hover, .cart-hover").each(function () {
            var $toggle = jQuery(this);
            $toggle.children('a').click(function (e) {
                e.preventDefault();
                $toggle.toggleClass("active");
            });
        });

        /**
        * ==============================
        * Subscribe Popup
        * ==============================
        */

        jQuery(".subscribe-me").subscribeBetter({
            trigger: "onidle", // You can choose which kind of trigger you want for the subscription modal to appear. Available triggers are "atendpage" which will display when the user scrolls to the bottom of the page, "onload" which will display once the page is loaded, and "onidle" which will display after you've scrolled.
            animation: "flyInDown", // You can set the entrance animation here. Available options are "fade", "flyInRight", "flyInLeft", "flyInUp", and "flyInDown". The default value is "fade".
            delay: 0, // You can set the delay between the trigger and the appearance of the modal window. This works on all triggers. The value should be in milliseconds. The default value is 0.
            showOnce: true, // Toggle this to false if you hate your users. :)
            autoClose: false, // Toggle this to true to automatically close the modal window when the user continue to scroll to make it less intrusive. The default value is false.
            scrollableModal: false      //  If the modal window is long and you need the ability for the form to be scrollable, toggle this to true. The default value is false.
        });

        /**
        * ==============================
        * Page Loader Text
        * ==============================
        */

        var words = ["SUNGLASSES", "FASHION", "INTERIOR", "COSMETIC", "JEWELRY", "BAKERY", "BIKE", "ACCESSORIES", "COLLECTIONS", "SUGGETIONS"];

        window.addEventListener("load", function () {
            var randoms = window.document.getElementsByClassName("randoms");
            for (var i = 0; i < randoms.length; i++)
                changeWord(randoms[i]);
        }, false);

        function changeWord(a) {
            a.style.opacity = '0.1';
            a.innerHTML = words[getRandomInt(0, words.length - 1)];
            setTimeout(function () {
                a.style.opacity = '1';
            }, 825);
            setTimeout(function () {
                changeWord(a);
            }, getRandomInt(500, 1600));
        }

        function getRandomInt(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }


        /**
        * ==============================
        * Revolution Slider 
        * ==============================
        */

        if (jQuery('.slider-section').length > 0) {
            jQuery('.tp-banner').revolution({
                delay: 15000,
                startwidth: 1170,
                startheight: 600,
                hideThumbs: 10,
                fullWidth: "on",
                forceFullWidth: "on",
                onHoverStop: "off",
                navigationStyle: "square",
                spinner: "spinner2",
                hideTimerBar: "on"
            });
        }

        /**
        * ==============================
        * Sticky Header
        * ==============================
        */

        if (jQuery(window).width() > 767) {
            jQuery(".main-header").sticky({topSpacing: 0});
        }


    });

});