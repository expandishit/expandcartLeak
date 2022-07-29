/*********************************************************************

    Template Name: Tanzim - eCommerce HTML Template  
    Template URI: https://themeforest.net/user/
    Description: This is html5 multipurpose e-connerce template
    Author: nilArtStudio
    Author URI: https://themeforest.net/user/nilArtStudio
    Version: 1.4

    Note: This is mian js. Declare your javascript code and pkugin activation code here.

*********************************************************************/

/*================================================
[  Table of contents  ]
================================================

    01. Selectpicker
    02. Count Down
    03. Owl active
        03.1 Product slider owl active
        03.2 Main slider 1 owl active
        03.3 Main slider 2 owl active
        03.4 Main slider 3 owl active
        03.5 Brand logo owl active
    04. Tab active
    05. Meanmenu active
    06. Wow js active
    07. Scroll up active
    08. Fancybox active
    09. Cart-plus-minus-button
    10. Price slider active
    11. Custom query
        11.1 Menu slice active
        11.2 Shopping cart
        11.3 Account box
        11.4 Account box top
        11.5 Search box
        11.6 Category menu
        11.7 Account action 2
        11.8 Shopping cart 2
        11.9 Search box 2
        11.10 Category menu 2
 
======================================
[ End table content ]
======================================*/
(function($) {
    'use strict';

    /* 01. Selectpicker -------------------------*/
    $('.selectpicker').selectpicker({
      multiple: true
    });

    /* 02. Count Down ---------------------------
    $('#offer-time').countdown('2018/02/10', function(event) {
        var totalHours = event.offset.totalDays * 24 + event.offset.hours;
        $(this).html(event.strftime(totalHours + ' : %M : %S'));
    });*/

    /* 03. Owl active ---------------------------*/

        // 03.1 Product slider owl active
    $('.owl-carousel.featured-products').owlCarousel({
        autoplay: false,
        loop:true,
        items :  4,
        nav: true,
        dots: false,
        lazyLoad: true,
        navText: ['<span class="lnr lnr-chevron-left"></span>','<span class="lnr lnr-chevron-right"></span>'],
        responsive : {
            0 : {
                items:1,
            },
            600 : {
                items:2,
            },
            768 : {
                items:3,
            },
            992 : {
                items:4,
            }
        }
    });

    // 03.2 Main slider 1 owl active
    $('.slider-style-1.owl-carousel').owlCarousel({
        autoplay: false,
        loop:true,
        items :  1,
        nav: false,
        navText: ['<span class="lnr lnr-chevron-left"></span>','<span class="lnr lnr-chevron-right"></span>'],
        dots: true,
        lazyLoad: true,
        center: true   
    });

    // 03.3 Main slider 2 owl active
    $('.slider-style-2-active.owl-carousel').owlCarousel({
        autoplay: false,
        loop:true,
        items : 1,
        dots: true,
        nav:false,
        lazyLoad: true,
        center: true 
    });

    //03.4 Main slider 3 owl active
    $('.slider-area.slider-style-3').owlCarousel({
        autoplay: false,
        loop:true,
        items : 1,
        dots: false,
        navText: ['<span class="lnr lnr-chevron-left"></span>','<span class="lnr lnr-chevron-right"></span>'],
        nav:true,
        lazyLoad: true,
        center: true 
    });

    // 03.5  Brand logo owl active    
    $('.brand-logo-activator').owlCarousel({
        autoplay: false,
        loop:true,
        items : 4,
        dots: false,
        nav:false,
        lazyLoad: true,
        responsive : {
            0 : {
                items:1,
            },
            480 : {
                items:2,
            },
            768 : {
                items:3,
            },
            992 : {
                items:4,
            }
        }
    });


    /* 04. Tab active ---------------------------*/
    $('#tablist li a').on('click', function (e) {
      e.preventDefault()
      $(this).tab('show')
    });

    /* 05. Meanmenu active ---------------------------*/
    $('.mobile-menu-area nav').meanmenu({
        meanScreenWidth: '991',
        meanMenuClose: 'X',
        meanMenuContainer: '.mobile-menu-area',
    });

   /* 06. Wow js active-------------------------- */
    new WOW().init();

    /* 07. Scroll up active ---------------------*/
    $.scrollUp({
        scrollText: '<i class="fa fa-angle-up"></i>',
        easingType: 'linear',
        scrollSpeed: 900,
        animation: 'fade'
    });

    /* 08. Fancybox active ---------------------*/
        $('.grouped_elements').fancybox({
            'transitionIn'  :   'elastic',
            'transitionOut' :   'elastic',
            'speedIn'       :   600, 
            'speedOut'      :   200
        });

    /*09. Cart-plus-minus-button -------------
    $('.cart-plus-minus').append('<div class="dec qtybutton">-</i></div><div class="inc qtybutton">+</div>');

    $('.qtybutton').on('click', function () {
        var $button = $(this);
        var oldValue = $button.parent().find('input').val();
        if ($button.text() == "+") {
            var newVal = parseFloat(oldValue) + 1;
        } else {
            // Don't allow decrementing below zero
            if (oldValue > 1) {
                var newVal = parseFloat(oldValue) - 1;
            } else {
                newVal = 1;
            }
        }
        $button.parent().find('input').val(newVal);
    });

    /* 10. Price slider active ----------------------*/
    $( '#slider-range' ).slider({
      range: true,
      min: 0,
      max: 1000,
      values: [ 275, 645 ],
      slide: function( event, ui ) {
        $( '#amount' ).val( '$' + ui.values[ 0 ] + ' - $' + ui.values[ 1 ] );
      }
    });

    $( '#amount' ).val( '$' + $( '#slider-range' ).slider( 'values', 0 ) +
      ' - $' + $( '#slider-range' ).slider( 'values', 1 ) );

    /* 11. Custom query ----------------------*/

    // 11.1 Menu slice active
    $('.main-menu > li').slice(-2).addClass('last-elements');

    // 11.2 Shopping cart
    $('.wish-cart .shopping').on('mouseover', function(e){
        e.preventDefault();
        $('.shopping-cart-list').addClass('shopping-cart-show');
    });
    $('.wish-cart .shopping').on('mouseout', function(e){
        e.preventDefault();
        $('.shopping-cart-list').removeClass('shopping-cart-show');
    });
    $('.shopping-cart-list').on('mouseover', function(e){
        e.preventDefault();
        $('.shopping-cart-list').addClass('shopping-cart-show');
    });
    $('.shopping-cart-list').on('mouseout', function(e){
        e.preventDefault();
        $('.shopping-cart-list').removeClass('shopping-cart-show');
    });

    // 11.3 Account box
    $('.account-action a').on('click', function(e){
        e.preventDefault();
        $(this).parent().parent().find('.account-form').slideToggle(400).css('display','block');
    });

    // 11.4 Account box top
    $('.account-action.account-action-top a').on('click', function(e){
        e.preventDefault();
        $(this).parent().find('.account-form').slideToggle(400).css('display','block');
    });

    // 11.5 Search box
    $('.wish-cart .search').on('mouseover', function(e){
        e.preventDefault();
        $('.search-form-2-inner').addClass('search-box-show');
    });
    $('.wish-cart .search').on('mouseout', function(e){
        e.preventDefault();
        $('.search-form-2-inner').removeClass('search-box-show');
    });
    $('.search-form-2-inner').on('mouseover', function(e){
        e.preventDefault();
        $('.search-form-2-inner').addClass('search-box-show');
    });
    $('.search-form-2-inner').on('mouseout', function(e){
        e.preventDefault();
        $('.search-form-2-inner').removeClass('search-box-show');
    });

    // 11.6 Category menu
    $('.cat-menu-more a').on('click', function(e){
        e.preventDefault();
        $(this).find('span.lnr').toggleClass('lnr-circle-minus');
        $(this).parent().find('.cat-menu-more-items').toggleClass('cat-menu-more-show');
    });

    // 11.7 Account action 2
    $('.user-action-button').on('click', function(e){
        e.preventDefault();
        $(this).parent().find('.account-form').slideToggle(400).css('display', 'block');
    });

    // 11.8 Shopping cart action 2
    $('.shopping-action-button').on('mouseover', function(e){
        e.preventDefault();
        $(this).parent().find('.shopping-cart-list').addClass('shopping-cart-show');
    });
    $('.shopping-action-button').on('mouseout', function(e){
        e.preventDefault();
        $(this).parent().find('.shopping-cart-list').removeClass('shopping-cart-show');
    });

    // 11.9 Search form 2
    $('.search-action-button').on('mouseover', function(e){
        e.preventDefault();
        $(this).parent().find('.search-form-2-inner').addClass('search-box-show');
    });
    $('.search-action-button').on('mouseout', function(e){
        e.preventDefault();
        $(this).parent().find('.search-form-2-inner').removeClass('search-box-show');
    });

    // 11.10 Category menu 2
    $('#category-menu-active > ul > li > a').on('click', function(e){
        e.preventDefault();
        $(this).parent().find('.fa').toggleClass('fa-minus');
        $(this).parent().find('.cp-category-sub').slideToggle('slow');
    });

})(jQuery);