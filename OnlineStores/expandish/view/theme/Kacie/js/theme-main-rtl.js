jQuery(function (jQuery) {

    //'use strict';

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
            jQuerycontainer.isotope('layout'); // layout/layout
        }

    });


    jQuery(window).resize(function () {
        if (jQuery().isotope) {
            jQuery('.row.isotope').isotope('layout'); // layout/relayout on window resize
        }
    });

    jQuery('#product-filter').isotope({filter: '.tab-1'});
        // ---------------------------------------------------------------------------------------


    jQuery(function ($) {

        

        /**
         * ==============================
         *  Revolution Slider
         * ==============================
        */

        if (jQuery('.slider-section').length > 0) {
            jQuery('.tp-banner').revolution({
                delay: 15000,
                startwidth: 1170,
                startheight: 800,
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
         *  Sticky Header
         * ==============================
        */

        if (jQuery(window).width() > 760 && (jQuery('.main-header').length > 0)) {
            jQuery(".main-header").sticky({topSpacing: 0});
        }


        /**
         * ==============================
         *  Related Product Slider 
         * ==============================
        */

        if (jQuery('.related-prod-slider').length > 0) {
            jQuery(".related-prod-slider").owlCarousel({
                rtl: true,
                dots: false,
                loop: true,
                autoplay: true,
                autoplayHoverPause: true,
                smartSpeed: 100,
                responsive: {
                    0: {items: 1},
                    1200: {items: 4},
                    990: {items: 3},
                    600: {items: 2},
                    480: {items: 1}
                }
            });
        }

        /**
         * ==============================
         *  Related Product Slider 2
         * ==============================
        */

        if (jQuery('.related-prod-slider-2').length > 0) {

            jQuery(".related-prod-slider-2").owlCarousel({
                rtl: true,
                dots: false,
                loop: true,
                autoplay: true,
                autoplayHoverPause: true,
                smartSpeed: 100,
                responsive: {
                    0: {items: 1},
                    1200: {items: 3},
                    990: {items: 2},
                    600: {items: 2},
                    480: {items: 1}
                }
            });
        }

        /**
         * ==============================
         *  Recent Product Slider
         * ==============================
        */

        if (jQuery('.recent-prod-slider').length > 0) {
            jQuery(".recent-prod-slider").owlCarousel({
                rtl: true,
                dots: false,
                loop: true,
                autoplay: true,
                autoplayHoverPause: true,
                smartSpeed: 100,
                responsive: {
                    0: {items: 1},
                    1200: {items: 1},
                    767: {items: 1},
                    600: {items: 2},
                    480: {items: 1}
                }
            });
        }

        jQuery(".recent-nav .next").on("click", function () {
            jQuery(".recent-prod-slider").trigger('next.owl.carousel');
        });
        jQuery(".recent-nav .prev").on("click", function () {
            jQuery(".recent-prod-slider").trigger('prev.owl.carousel');
        });

        /**
         * ==============================
         *  Recent Post Slider
         * ==============================
        */

        if (jQuery('.related-post-slider').length > 0) {
            jQuery(".related-post-slider").owlCarousel({
                rtl: true,
                dots: false,
                loop: false,
                autoplay: true,
                autoplayHoverPause: true,
                smartSpeed: 100,
                responsive: {
                    0: {items: 1},
                    1200: {items: 2},
                    990: {items: 1},
                    600: {items: 1}
                }
            });
        }

 

        /**
         * ==============================
         *  Pretty Photo Popup 
         * ==============================
        */


        if (jQuery('.caption-link').length > 0) {
            jQuery("a[data-gal^='prettyPhoto']").prettyPhoto({
                theme: 'facebook',
                slideshow: 5000,
                autoplay_slideshow: true
            });
        }

     

    
        /**
        * ==============================
        *  Slider Product
        *  Resize carousels in modal
        * ==============================
        */
       
        jQuery(document).on('shown.bs.modal', function () {
            jQuery(this).find('.sync1, .sync2').each(function () {
                jQuery(this).data('owlCarousel') ? jQuery(this).data('owlCarousel').onResize() : null;
            });
        });

        var sync1 = jQuery(".sync1");
        var sync2 = jQuery(".sync2");
        var sync3 = jQuery(".style-5.sync1");
        var sync4 = jQuery(".style-5.sync2");
        var navSpeedThumbs = 500;

        sync4.owlCarousel({
            rtl: true,
            items: 4,
            nav: false,
            navSpeed: navSpeedThumbs,
            responsive: {
                992: {items: 4},
                767: {items: 5},
                600: {items: 4}
            },
            responsiveRefreshRate: 200
        });

        sync3.owlCarousel({
            rtl: true,
            items: 1,
            navSpeed: 1000,
            nav: true,
            onChanged: syncPosition,
            responsiveRefreshRate: 200,
            navText: [
                "<span class='nav-prev'>Prev</span>",
                "<span class='nav-next'>Next</span>"
            ]
        });


        sync2.owlCarousel({
            rtl: true,
            items: 4,
            nav: true,
            navSpeed: navSpeedThumbs,
            responsive: {
                992: {items: 4},
                767: {items: 5},
                600: {items: 4}
            },
            responsiveRefreshRate: 200,
            navText: [
                "<i class='arrow_carrot-left'></i>",
                "<i class='arrow_carrot-right'></i>"
            ]
        });

        sync1.owlCarousel({
            rtl: true,
            items: 1,
            navSpeed: 1000,
            nav: false,
            onChanged: syncPosition,
            responsiveRefreshRate: 200

        });


        function syncPosition(el) {
            var current = this._current;
            jQuery(".sync2")
                    .find(".owl-item")
                    .removeClass("synced")
                    .eq(current)
                    .addClass("synced");
            center(current);
        }

        jQuery(".sync2").on("click", ".owl-item", function (e) {
            e.preventDefault();
            var number = jQuery(this).index();
            sync1.trigger("to.owl.carousel", [number, 1000]);
        });

        function center(num) {

            var sync2visible = sync2.find('.owl-item.active').map(function () {
                return jQuery(this).index();
            });

            if (jQuery.inArray(num, sync2visible) === -1) {
                if (num > sync2visible[sync2visible.length - 1]) {
                    sync2.trigger("to.owl.carousel", [num - sync2visible.length + 2, navSpeedThumbs, true]);
                } else {
                    sync2.trigger("to.owl.carousel", Math.max(0, num - 1));
                }
            } else if (num === sync2visible[sync2visible.length - 1]) {
                sync2.trigger("to.owl.carousel", [sync2visible[1], navSpeedThumbs, true]);
            } else if (num === sync2visible[0]) {
                sync2.trigger("to.owl.carousel", [Math.max(0, num - 1), navSpeedThumbs, true]);
            }
        } 

    });


});