function ms_addToCart(product_id, quantity) {
	quantity = typeof(quantity) != 'undefined' ? quantity : 1;

	$.ajax({
		url: 'index.php?route=checkout/cart/add',
		type: 'post',
		data: 'product_id=' + product_id + '&quantity=' + quantity,
		dataType: 'json',
        error: function(s,d,r) {
          debugger;
        },
		success: function(json) {
            if(json['success'] == 'affiliate_link') {
                document.location = json['affiliate_link'];
                return;
            }

			$('.success, .warning, .attention, .information, .error').remove();

			if (json['redirect']) {
				location = json['redirect'];
			}

			if (typeof(json['error']) != "undefined") {
			if (json['error']['seller']) {
				$('#notification').html('<div class="warning" style="display: none;">' + json['error']['seller'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>');

				$('.warning').fadeIn('slow');

				$('html, body').animate({ scrollTop: 0 }, 'slow');
			}
			}

			if (json['success']) {
				$('#notification').html('<div class="success" style="display: none;">' + json['success'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>');

				$('.success').fadeIn('slow');

				$('#cart-total').html(json['total']);

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
                        //width: 500,
                        width: $(window).width() > 500 ? 500 : 'auto',
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
		}
	});
}
