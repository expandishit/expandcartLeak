$(document).ready(function() {

	$('.qty-down').click(function(){
		if($("#qty1").val() > 1){
		$('#qty1').val(parseInt($("#qty1").val()) - 1);
	}
	});
	$('.qty-up').click(function(){
		$('#qty1').val(parseInt($("#qty1").val()) + 1);
	});

	
	
	/* Search */
	$('.button-search').bind('click', function() {
		url = $('base').attr('href') + 'index.php?route=product/search' ;
				 
		var search = $('input[name="search"]:visible').val();
		
		if (search) {
			url += '&search=' + encodeURIComponent(search);
		}
		
		location = url;
	});
	
	$('#search input[name=\'search\']').bind('keydown', function(e) {
		if (e.keyCode == 13) {
			e.preventDefault();
			e.stopPropagation();
			url = $('base').attr('href') + 'index.php?route=product/search';
			 
			var search = $(this).val();
			
			if (search) {
				url += '&search=' + encodeURIComponent(search);
			}
			
			location = url;
		}
	});

	$(document).on('click', '.list-mini-cart-item .delete', function() {
		var del_id = $(this).attr('id');

		if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
            location = 'index.php?route=checkout/cart&remove=' + del_id;
		} else {
            $.get('index.php?route=common/cart&remove=' + del_id, function(html) {
                $.ajax({
                    url: 'index.php?route=checkout/cart/count',
                    dataType: 'json',
                    success: function(json) {
                        $('.counterLabel').html(json['product_count']);
                        $('.cartDropList').html(html);
                    }
                });
            });
		}

        return false;
	});

    var sectionId = getURLVar('draftsectionid');
    if(sectionId != '') {
        setTimeout(function() {
            //debugger;
            if($('div#section-' + sectionId).length > 0)
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
    $('#contact-form input[name="enquiry"]').val($('#enquiry-'+id).html()+'\n');
     $('#contact-form input[name="product_id"]').val(id);
    $('#contact-form').submit();
}

function addToCart(product_id, quantity, is_negativeprice) {
	quantity = typeof(quantity) != 'undefined' ? quantity : 1;
    is_negativeprice = typeof(is_negativeprice) != 'undefined' ? is_negativeprice : false;

    if(is_negativeprice) {
        $('#contact-form input[name="enquiry"]').val($('#enquiry-'+product_id).html()+'\n');
        $('#contact-form').submit();
        return;
	}

	$.ajax({
		url: 'index.php?route=checkout/cart/add',
		type: 'post',
		data: 'product_id=' + product_id + '&quantity=' + quantity,
		dataType: 'json',
		success: function(json) {
						
            if(json['success'] == 'affiliate_link') {
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
				// $('#notification').html('<br><div class="alert alert-success alert-dismissible" style="display: none;" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + json['success'] + '</div>');
				
				// $('.alert-success').fadeIn('slow');
				
				$('.counterLabel').html(json['product_count']);

                $.get('index.php?route=common/cart', function(html) {
                    $('.cartDropList').html(html);
                });

                if (json['enable_order_popup'] != '1')
					// $('html, body').animate({ scrollTop: 0 }, 'slow');
					
					$('#cartPopUp').fadeIn();
					$('#cartPopUp .wishlist-alert').html(json['success'] );
					var counter = 10;
					var popup;
					popup = setInterval(function() {
						counter--;
						if(counter < 0) {
							clearInterval(popup);
							$('#cartPopUp').fadeOut();
						} else {
							$("#cartPopUp .wishlist-countdown").text(counter.toString());
						}
					}, 1000);

                if (json['enable_order_popup'] == '1') {
                    $('head').append("<style type='text/css'>.customAddtoCart .ui-dialog-titlebar.ui-widget-header.ui-corner-all.ui-helper-clearfix {display: none;} .customAddtoCart div#add-to-cart-dialog {min-height: 1px !important;} [dir=rtl] .ui-widget {font-family: 'Droid Arabic Kufi', 'droid_serifregular' !important;}</style>");

                    $('body').append('<div id="add-to-cart-dialog" style="display:none;"><div style="margin: 13px 0;">' + json['text_cart_dialog'] + '</div></div>');

                    // $("#add-to-cart-dialog").dialog({
                    //     modal: true,
                    //     draggable: false,
                    //     resizable: false,
                    //     position: ['center', 'center'],
                    //     show: 'blind',
                    //     hide: 'blind',
                    //     width: 500,
                    //     dialogClass: 'ui-dialog-osx customAddtoCart',
                    //     buttons: [{
                    //         text: json['text_cart_dialog_continue'],
                    //         click: function() {
                    //             $(this).dialog("close");
                    //         }
                    //     },
                    //         {
                    //             text: json['text_cart_dialog_cart'],
                    //             click: function() {
                    //                 window.location.href = json['cart_link'];
                    //             }
                    //         }
                    //     ]
                    // });
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
		success: function(json) {
			// $('.alert-success, .alert-warning, .alert-attention, .alert-information').remove();
						
			if (json['success']) {
				// $('#notification').html('<br><div class="alert alert-success alert-dismissible" style="display: none;" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + json['success'] + '</div>');
				
				$('#wishPopUp').fadeIn();
				$('#wishPopUp .wishlist-alert').html(''+json['success']+'');
				var counter = 10;
				var popup;
				popup = setInterval(function() {
					counter--;
					if(counter < 0) {
						clearInterval(popup);
						$('#wishPopUp').fadeOut();
					} else {
						$("#wishPopUp .wishlist-countdown").text(counter.toString());
					}
				}, 1000);

				// $('.alert-success').fadeIn('slow');
				
				$('#wishlist-total').html(json['total']);
				
				// $('html, body').animate({ scrollTop: 0 }, 'slow');
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
		success: function(json) {
			$('.alert-success, .alert-warning, .alert-attention, .alert-information').remove();
						
			if (json['success']) {
				//  $('#notification').html('<br><div class="alert alert-success alert-dismissible" style="display: none;" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + json['success'] + '</div>');
				
				// $('.alert-success').fadeIn('slow');

				$('#comparePopUp').fadeIn();
				$('#comparePopUp .wishlist-alert').html(json['success'] );
				var counter = 10;
				var popup;
				popup = setInterval(function() {
					counter--;
					if(counter < 0) {
						clearInterval(popup);
						$('#comparePopUp').fadeOut();
					} else {
						$("#comparePopUp .wishlist-countdown").text(counter.toString());
					}
				}, 1000);

				$('#compare-total').html(json['total']);
				
				//  $('html, body').animate({ scrollTop: 0 }, 'slow'); 
			}	
		}
	});
}