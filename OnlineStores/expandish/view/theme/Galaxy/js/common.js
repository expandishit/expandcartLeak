jQuery(document).ready(function () {


    jQuery('.reviewTrigger').click(function(){
        jQuery('.nav-tabs--wd  li').removeClass('active');
        jQuery('#reviewTab').addClass('active');
        jQuery('html,body').animate({scrollTop: jQuery("#reviewTab").offset().top},'slow');
    });


    var windowW = window.innerWidth || $j(window).width();

    var fullwidth;

    var fullscreen;



    if (windowW > 767) {

        fullwidth = "off";

        fullscreen = "on";

    } else {

        fullwidth = "on";

        fullscreen = "off";

    }





    jQuery('.tp-banner').show().revolution(

        {

            dottedOverlay: "none",

            delay: 16000,

            startwidth: 1170,

            startheight: 700,

            hideThumbs: 200,

            hideTimerBar: "on",



            thumbWidth: 100,

            thumbHeight: 50,

            thumbAmount: 5,



            navigationType: "none",

            navigationArrows: "",

            navigationStyle: "",



            touchenabled: "on",

            onHoverStop: "on",



            swipe_velocity: 0.7,

            swipe_min_touches: 1,

            swipe_max_touches: 1,

            drag_block_vertical: false,



            parallax: "mouse",

            parallaxBgFreeze: "on",

            parallaxLevels: [7, 4, 3, 2, 5, 4, 3, 2, 1, 0],



            keyboardNavigation: "off",



            navigationHAlign: "center",

            navigationVAlign: "bottom",

            navigationHOffset: 0,

            navigationVOffset: 20,



            soloArrowLeftHalign: "left",

            soloArrowLeftValign: "center",

            soloArrowLeftHOffset: 20,

            soloArrowLeftVOffset: 0,



            soloArrowRightHalign: "right",

            soloArrowRightValign: "center",

            soloArrowRightHOffset: 20,

            soloArrowRightVOffset: 0,



            shadow: 0,

            fullWidth: fullwidth,

            fullScreen: fullscreen,



            spinner: "",



            stopLoop: "off",

            stopAfterLoops: -1,

            stopAtSlide: -1,



            shuffle: "off",



            autoHeight: "off",

            forceFullWidth: "off",



            hideThumbsOnMobile: "off",

            hideNavDelayOnMobile: 1500,

            hideBulletsOnMobile: "off",

            hideArrowsOnMobile: "off",

            hideThumbsUnderResolution: 0,



            hideSliderAtLimit: 0,

            hideCaptionAtLimit: 0,

            hideAllCaptionAtLilmit: 0,

            startWithSlide: 0,

            fullScreenOffsetContainer: ".header"

        });









});	//ready





$(document).ready(function () {






    $(document).on('click', ".btn-number", function () {

        if ($(this).hasClass('qtyplus')) {

            $("[name=quantity]", '.form-qty').val(parseInt($("[name=quantity]", '.form-qty').val()) + 1);



        } else {

            if (parseInt($("[name=quantity]", '.form-qty').val()) > 1) {

                $("[name=quantity]", '.form-qty').val(parseInt($("[name=quantity]", '.form-qty').val()) - 1);



            }

        }

    });



    /* Search */

    $('.button-search').bind('click', function () {

        url = $('base').attr('href') + 'index.php?route=product/search' ;



        var search = $('input[name="search"]:visible').val();



        if (search) {

            url += '&search=' + encodeURIComponent(search);

        }



        location = url;

    });



    $('#search input[name=\'search\']').bind('keydown', function (e) {

        if (e.keyCode == 13) {

            url = $('base').attr('href') + 'index.php?route=product/search';

            e.preventDefault();

            e.stopPropagation();

            var search = $(this).val();



            if (search) {

                url += '&search=' + encodeURIComponent(search);

            }



            location = url;

        }

    });



    $(document).on('click', '.shopping-cart .deleteProduct', function () {

        var del_id = $(this).attr('id');



        if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {

            location = 'index.php?route=checkout/cart&remove=' + del_id;

        } else {

            $.get('index.php?route=common/cart&remove=' + del_id, function (html) {

                $.ajax({

                    url: 'index.php?route=checkout/cart/count',

                    dataType: 'json',

                    success: function (json) {

                        $('#counterLabel').html(json['product_count']);

                        $('.cartDropList .dropdown-menu').html(html);

                    }

                });

            });

        }



        return false;

    });


    var sectionId = getURLVar('draftsectionid');
    if (sectionId != '') {
        setTimeout(function () {
            //debugger;
            if ($('div#section-' + sectionId).length > 0)
                $(document).scrollTop($('div#section-' + sectionId).offset().top);
        }, 100);
    }
});



function getURLVar(key) {

    var value = [];



    var query = String(document.location).split('?');



    if (query[1]) {

        var part = query[1].split('&');



        for (i = 0; i < part.length; i++) {

            var data = part[i].split('=');



            if (data[0] && data[1]) {

                value[data[0]] = data[1];

            }

        }



        if (value[key]) {

            return value[key];

        } else {

            return '';

        }

    }

}



function contact_us(id) {
    $('#contact-form input[name="enquiry"]').val($('#enquiry-' + id).html() + '\n');
    $('#contact-form input[name="product_id"]').val(id);
    $('#contact-form').submit();
}
function addToCartQty(product_id, holder){
    var qty = $('#qty_'+holder+product_id).val();
    if(parseInt(qty) < 0)
        qty = 1;

    addToCart(product_id, qty);
}
function addToCart(product_id, quantity, is_negativeprice) {
    quantity = typeof (quantity) != 'undefined' ? quantity : 1;
    is_negativeprice = typeof (is_negativeprice) != 'undefined' ? is_negativeprice : false;

    if (is_negativeprice) {
        $('#contact-form input[name="enquiry"]').val($('#enquiry-' + product_id).html() + '\n');
        $('#contact-form').submit();
        return;
    }

    $.ajax({

        url: 'index.php?route=checkout/cart/add',

        type: 'post',

        data: 'product_id=' + product_id + '&quantity=' + quantity,

        dataType: 'json',

        success: function (json) {
            			
            if (json['success'] == 'affiliate_link') {
                document.location = json['affiliate_link'];
                return;
            }


            $('.alert-success, .alert-warning, .alert-attention, .alert-information, .alert-error').remove();



            if (json['redirect']) {

                location = json['redirect'];

            }


            if (json['success']) {
                if (json['fb_data_track']) {
                    fbq('track', json['fb_data_track']['event'], {
                        "Product Name": json['fb_data_track']['product_name'],
                        "content_type":json['fb_data_track']['content_type'],
                        "content_ids":json['fb_data_track']['content_ids'],
                        "value":json['fb_data_track']['value'],
                        "currency":json['fb_data_track']['currency'],
                        "product_catalog_id":json['fb_data_track']['product_catalog_id']
                    });
                }
                $('#notification').html('<br><div class="alert alert-success alert-dismissible" style="display: none;" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + json['success'] + '</div>');



                $('.alert-success').fadeIn('slow');



                $('#counterLabel').html(json['product_count']);



                $.get('index.php?route=common/cart', function (html) {
                    $('.cartDropList .dropdown-menu').html(html);
                });


                if (json['enable_order_popup'] == '1') {
                    $('head').append("<style type='text/css'>.customAddtoCart .ui-dialog-titlebar.ui-widget-header.ui-helper-clearfix {display: none;} .customAddtoCart div#add-to-cart-dialog {min-height: 1px !important;} [dir=rtl] .ui-widget {font-family: 'Droid Arabic Kufi', 'droid_serifregular' !important;}</style>");

                    $('body').append('<div id="add-to-cart-dialog" style="display:none;"><div style="margin: 13px 0;">' + json['text_cart_dialog'] + '</div></div>');

                    $("#add-to-cart-dialog").dialog({

                        modal: true,

                        draggable: false,

                        resizable: false,

                        position: ['center', 'center'],

                        show: 'blind',

                        hide: 'blind',

                        width: 500,

                        dialogClass: 'ui-dialog-osx customAddtoCart',

                        buttons: [{

                            text: json['text_cart_dialog_continue'],

                            click: function () {

                                $(this).dialog("close");

                            }

                        },

                        {

                            text: json['text_cart_dialog_cart'],

                            click: function () {

                                window.location.href = json['cart_link'];

                            }

                        }

                        ]

                    });

                }
            }
        }
    });

}

function addToWishList(product_id) {
	$.ajax({
		url: 'index.php?route=account/wishlist/add',
		type: 'post',
		data: 'product_id=' + product_id,
		dataType: 'json',
		success: function (json) {
			$('.alert-success, .alert-warning, .alert-attention, .alert-information').remove();

			if (json['success']) {
                let alertClass  = ''
                if(json['status'] == '1')
                    alertClass  = 'alert-success'
                else
                    alertClass  = 'alert-warning'
                
				$('#notification').html('<br><div class="alert '+alertClass+' alert-dismissible" style="display: none;" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + json['success'] + '</div>');

				if(json['status'] == '1')
                    $('.alert-success').fadeIn('slow');
                else
                    $('.alert-warning').fadeIn('slow');

				$('#wishlist-total').html(json['total']);

				$('html, body').animate({
					scrollTop: 0
				}, 'slow');
			}
		}
	});
}

function addToCompare(product_id) {
	$.ajax({
		url: 'index.php?route=product/compare/add',
		type: 'post',
		data: 'product_id=' + product_id,
		dataType: 'json',
		success: function (json) {
			$('.alert-success, .alert-warning, .alert-attention, .alert-information').remove();

			if (json['success']) {
				$('#notification').html('<br><div class="alert alert-success alert-dismissible" style="display: none;" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + json['success'] + '</div>');

				$('.alert-success').fadeIn('slow');

				$('#compare-total').html(json['total']);

				$('html, body').animate({
					scrollTop: 0
				}, 'slow');
			}
		}
	});
}