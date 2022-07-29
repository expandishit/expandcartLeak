function addCartStyle(json)
{
    if(json['success'] == 'affiliate_link') {
        document.location = json['affiliate_link'];
        return;
    }

    $('.alert-success, .alert-warning, .alert-attention, .alert-information, .alert-error').remove();

    if (json['error']) {
        /*
        if (json['error']['option']) {
            for (i in json['error']['option']) {
                $('#option-' + i).after('<span class="error">' + json['error']['option'][i] + '</span>');
            }
        }
        */
        if (json['error']['profile']) {
            $('select[name="profile_id"]').after('<span class="error">' + json['error']['profile'] + '</span>');
        }
    }

    if (json['success']) {
        $('#notification').html('<br><div class="alert alert-success alert-dismissible" style="display: none;" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + json['success'] + '</div>');

        $('.alert-success').fadeIn('slow');

        $('#counterLabel').html(json['product_count']);
        $.get('index.php?route=common/cart', function(html) {
            $('#cartDropList').html(html);
            $('.cartDropList .dropdown-menu').html(html);
        });

        if (json['enable_order_popup'] != '1')
            $('html, body').animate({ scrollTop: 0 }, 'slow');

        if (json['enable_order_popup'] == '1') {
            $('head').append("<style type='text/css'>.customAddtoCart .ui-dialog-titlebar.ui-widget-header.ui-corner-all.ui-helper-clearfix {display: none;} .customAddtoCart div#add-to-cart-dialog {min-height: 1px !important;} [dir=rtl] .ui-widget {font-family: 'Droid Arabic Kufi', 'droid_serifregular' !important;}</style>");

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
                    click: function() {
                        $(this).dialog("close");
                    }
                },
                    {
                        text: json['text_cart_dialog_cart'],
                        click: function() {
                            window.location.href = json['cart_link'];
                        }
                    }
                ]
            });
        }
    }

    if (typeof(json['error']) != "undefined") {
        if(typeof(json['error']['option']) != "undefined") {
            $.each(json['error']['option'], function(index, error) {
                $('#notification').html('<br><div class="alert alert-warning alert-dismissible" style="display: none;" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + error + '</div>');

                $('.alert-warning').fadeIn('slow');

                $('html, body').animate({ scrollTop: 0 }, 'slow');
            });
        }
        else{
            $.each(json['error'], function(index, error) {
                $('#notification').html('<br><div class="alert alert-warning alert-dismissible" style="display: none;" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + error + '</div>');

                $('.alert-warning').fadeIn('slow');

                $('html, body').animate({ scrollTop: 0 }, 'slow');
            });
        }
    }
}
function submitCoupon(){
        
	$.ajax({
		url: 'index.php?route=checkout/cart/coupon',
		type: 'POST',
		dataType: 'json',
		data: {
			'coupon':$("#coupon_input").val()
		},
		success: function(json) {
			$('.alert-success, .alert-warning, .alert-attention, .alert-information').remove();
		
			if (json['success']) {

                if(json['error']){
                    $('#notification').html('<br><div class="alert alert-warning alert-dismissible" style="display: none;" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + json['success'] + '</div>');
                    $('.alert-warning').fadeIn('slow');
                }
				else{
                    $('#notification').html('<br><div class="alert alert-success alert-dismissible" style="display: none;" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + json['success'] + '</div>');
                    $('.alert-success').fadeIn('slow');
                    setInterval('location.reload()', 5000);
                }
				$('html, body').animate({ scrollTop: 0 }, 'slow'); 
			}
		}	
	});
}
// wishlist

var sumWidthLi = 0;
  $( ".list-none .active" ).prevAll().each(function (){
  var widthOfLi =  $(this).outerWidth();
  sumWidthLi = widthOfLi + sumWidthLi;
});
if($('html').attr('lang') == 'ar'){
    $( ".list-none" ).scrollLeft( -sumWidthLi );
} else{
    $( ".list-none" ).scrollLeft( sumWidthLi );
}

$('.my-account').closest('.container').addClass('no-padding-xs');


function subtractFromCart(key) {
    $.ajax({
        url: 'index.php?route=checkout/cart/updateCartQuantity',
        type: 'post',
        data: 'key=' + key + '&decrease=' + 1,
        dataType: 'json',
        success: function(json) {
            $('.alert-success, .alert-warning, .alert-attention, .alert-information, .alert-error').remove();
                        
            if (json['success']) {
                $('#notification').html('<br><div class="alert alert-success alert-dismissible" style="display: none;" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + json['message'] + '</div>');    
                $('.alert-success').fadeIn('slow');
                //Remove 0 label when cart is empty
                $('#counterLabel.cat-counter.counter-number, #counterScroll, #countermobile').show();
                $('#counterLabel, #counterScroll, #countermobile').html(json['product_count']);
                $.get('index.php?route=common/cart', function(html) {
                    $('#cartDropList').html(html);
                });         
            }   
        }
    });
}

function increaseCart(key) {
    $.ajax({
        url: 'index.php?route=checkout/cart/updateCartQuantity',
        type: 'post',
        data: 'key=' + key + '&increase=' + 1,
        dataType: 'json',
        success: function(json) {
            $('.alert-success, .alert-warning, .alert-attention, .alert-information, .alert-error').remove();
                        
            if (json['success']) {
                $('#notification').html('<br><div class="alert alert-success alert-dismissible" style="display: none;" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + json['message'] + '</div>');    
                $('.alert-success').fadeIn('slow');
                //Remove 0 label when cart is empty
                $('#counterLabel.cat-counter.counter-number, #counterScroll, #countermobile').show();
                $('#counterLabel, #counterScroll, #countermobile').html(json['product_count']);
                $.get('index.php?route=common/cart', function(html) {
                    $('#cartDropList').html(html);
                });         
            }   
        }
    });
}

