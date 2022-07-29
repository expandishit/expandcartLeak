$(document).ready(function () {
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

	$(document).on('click', '.block-minicart .delete', function () {
		var del_id = $(this).attr('id');

		if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
			location = 'index.php?route=checkout/cart&remove=' + del_id;
		} else {
			$.get('index.php?route=common/cart&remove=' + del_id, function (html) {
				$.ajax({
					url: 'index.php?route=checkout/cart/count',
					dataType: 'json',
					success: function (json) {
						$('.block-minicart .counter-number').html(json['product_count']);
						$('.block-minicart .cart_items').html(html);
					}
				});
			});
		}

		return false;
	});

	$(document).on('click', ".form-qty .btn-number", function () {
		if ($(this).hasClass('qtyplus')) {
			$("[name=quantity]", '.form-qty').val(parseInt($("[name=quantity]", '.form-qty').val()) + 1);
		} else {
			if (parseInt($("[name=quantity]", '.form-qty').val()) > 1) {
				$("[name=quantity]", '.form-qty').val(parseInt($("[name=quantity]", '.form-qty').val()) - 1);
			}
		}
	});

	var sectionId = getURLVar('draftsectionid');
	if (sectionId != '') {
		setTimeout(function () {
			//debugger;
			if ($('div#section-' + sectionId).length > 0)
				$(document).scrollTop($('div#section-' + sectionId).offset().top);
		}, 100);
	}
	$(".cloudzoom-gallery").click(function(){
		var src = $(this).parent().attr("href");
		$('.large-image .thumb-link').attr('href', src)
	})
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

function addToCart(product_id, quantity) {
	quantity = typeof (quantity) != 'undefined' ? quantity : 1;

	$.ajax({
		url: 'index.php?route=checkout/cart/add',
		type: 'post',
		data: 'product_id=' + product_id + '&quantity=' + quantity,
		dataType: 'json',
		success: function (json) {
						
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
				$('#notification').html('<br><div class="alert alert-success alert-dismissible" style="display: none;" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + json['success'] + '</div>');

				$('.alert-success').fadeIn('slow');

				$('.block-minicart .counter-number').html(json['product_count']);

				$.get('index.php?route=common/cart', function (html) {
					$('.block-minicart .cart_items').html(html);
				});

				if (json['enable_order_popup'] != '1')
					$('html, body').animate({
						scrollTop: 0
					}, 'slow');

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
						width: '50%',
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
 $(document).ready(function () {
        CloudZoom.quickStart();
		jQuery("#daily-deal-slider .slider-items").owlCarousel({
            items: 1,
            itemsDesktop: [1024, 1],
            itemsDesktopSmall: [900, 1],
            itemsTablet: [640, 1],
            itemsMobile: [390, 1],
            slideSpeed: 100,
            rewindSpeed: 100,
			loop:true,
            slideSpeed: 500,
            pagination: !1,
            autoPlay: true,
			nav:true,
		});
        jQuery(".slider-items").owlCarousel({
            items: 3,
            itemsDesktop: [1024, 3],
            itemsDesktopSmall: [900, 3],
            itemsTablet: [640, 3],
            itemsMobile: [390, 3],
            slideSpeed: 100,
            rewindSpeed: 100,
            pagination: !1,
            autoPlay: true
		});
	
		jQuery(".slider-items-3").owlCarousel({
            items: 3,
            itemsDesktop: [1024, 3],
            itemsDesktopSmall: [900, 3],
            itemsTablet: [640, 3],
            itemsMobile: [390, 3],
            slideSpeed: 100,
            rewindSpeed: 100,
            pagination: !1,
            autoPlay: true
        })
		jQuery(".slider-items-4").owlCarousel({
            items: 4,
            itemsDesktop: [1024, 4],
            itemsDesktopSmall: [900, 4],
            itemsTablet: [640, 4],
            itemsMobile: [390, 3],
            slideSpeed: 100,
            rewindSpeed: 100,
            pagination: !1,
            autoPlay: true
		})
		jQuery(".slider-items-5").owlCarousel({
            items: 5,
            itemsDesktop: [1024, 5],
            itemsDesktopSmall: [900, 5],
            itemsTablet: [640, 4],
            itemsMobile: [390, 3],
            slideSpeed: 100,
            rewindSpeed: 100,
            pagination: !1,
            autoPlay: true
        })
    })
$(document).ready(function () {
	$(window).scroll(function() {
		var sidebar = $('.side-info');
		if ($(window).scrollTop() > 400) {
			sidebar.css("position", "fixed");
			sidebar.css("top", "15px");
			sidebar.css("width", "260px");
		} else if ($(window).scrollTop() <= 390) {
			sidebar.css("position", "");
			sidebar.css("top", "");
			sidebar.css("width", "");
		}
		if (sidebar.offset.top + sidebar.height() > $("footer").offset.top ) {
			sidebar.css( "top", -( sidebar.offset.top + $("footer").offset.top));
		}
		});

        $(function ($) {
            $.fn.uncheckableRadio = function () {
                var $root = this;
                $root.each(function () {
                    var $radio = $(this);
                    if ($radio.prop('checked')) {
                        $radio.data('checked', true);
                    } else {
                        $radio.data('checked', false);
                        calculateFinalPrice('def_price');
                        $('.heading_pro .name span').html('')
                        $('.heading_pro .name').html('');
                    }

                    $radio.click(function () {
                        var $this = $(this);
                        if ($this.data('checked')) {
                            $this.prop('checked', false);
                            $this.data('checked', false);
                            $this.trigger('change');
                            calculateFinalPrice('def_price');
                            $('.heading_pro .name span').html('')
                            $('.heading_pro .name').html('');
                        } else {
                            $this.data('checked', true);
                            $this.closest('form').find('[name="' + $this.prop('name') + '"]').not($this).data('checked', false);
                        }
                    });
                });
                return $root;
            };
        }(jQuery));
        $('input[type="radio"].form-check-input').uncheckableRadio();

        $('.form-check-input').on('click',function(){
            if($(this).prop('checked') == false){
              $(this).parents('.c-options-group').find('.sub-name').html('');
            }
        })
	});
	//multiselect Option
	function MultiSelection(elementId){
			const lengthcheckboxchecked =  $("#option" + elementId + ' .prod-options-box input[type=checkbox]:checked').length;
		let checkboxchecked =  $("#option" + elementId + ' .prod-options-box input[type=checkbox]:checked');
			let checkboxcheckedClear =  $("#heading" + elementId + ' .sub-name');
			checkboxcheckedClear.html('');
			for(let i=0; i < lengthcheckboxchecked; i++){
				let btnName = checkboxchecked.eq(i).parents('.c-options-group').find('.c-option-heading .sub-name');
				let textValue = checkboxchecked.eq(i).parents('.prod-options-box').find('.option-name').text();
					btnName.append("<span class='d-block'>"+textValue+'</span>');
			}

		}
	$(function($) {

	// custom product options collapse
	$('.prod-options-box input[type="radio"]').change(function() {
		let selectedName = $(this).parents('.prod-options-box').find('.option-name').text();
		let selectedImg = $(this).parents('.prod-options-box').find('.img img').attr('src');
		let btnName = $(this).parents('.c-options-group').find('.c-option-heading .sub-name');
		let mainOptionName = $(this).parents('.c-options-group').find('.c-option-heading .name').text();
		let btnImg = $(this).parents('.c-options-group').find('.c-option-heading img');
		

		//console.log(mainOptionName + selectedName//)
		

		$(this).parents('.prod-options-box').addClass('active')
		$(this).parents('.c-options-group').find('.prod-options-box').removeClass('active')
		btnName.html(selectedName);	
		btnImg.attr('src', selectedImg);
		let selectedValue = $(this).parents('.prod-options-box').find('.option-price span').data('value');
		$(this).parents('.c-options-group').find('.option-price span').each(function() {
			let elValue = $(this).data('value')
			let calcValue = elValue - selectedValue > 0 ? '+' + (elValue - selectedValue) : elValue - selectedValue < 0 ? elValue - selectedValue : '';
			//console.log(elValue , selectedValue, elValue - selectedValue)
			$(this).html(calcValue)
		});

	});
		//Product Option Select,configuration summary click
		var ProductValue = [];
        $('.optionProduct .form-check-input,.btn.configuration-modal-btn').click(function () {
            ProductValue = [];
            $.each($(".optionProduct .form-check-input:checked"), function () {
                ProductValue.push($(this).val());
            });
            
			$('.heading_pro .name').text('');
            for(var i=0; i< ProductValue.length; i++){
				const text_value = $('#option-value-' + ProductValue[i]).parent().find('.option-name').text();
				let mainOptionName =  $('#option-value-' + ProductValue[i]).attr('main-option-value');
				// condation for multi selection
				let MultiOptionName =  $('#option-value-' + ProductValue[i]).attr('main-multi-option-value');

				if(MultiOptionName){
					$('.heading_pro .name').append("<p> <span class='main-name-multi'>"+MultiOptionName+ " : </span> <span class='sub-name-multi'> "+ text_value +"</span></p>");
				}else{
					$('.heading_pro .name').append("<p> <span class='main-name'>"+mainOptionName+ " : </span> <span class='sub-name'> "+ text_value +"</span></p>");
				}
			}
			// to View First Header about multi selection. 
			$('p.name p span.main-name-multi').eq(0).css('display','block')
		});
	$('.c-options-group').each(function() {
		if($(this).find('.prod-options-box input[checked]').length > 0) {
			//console.log($(this).find('.prod-options-box input[checked]').length)
			let selectedValue = $(this).find('.prod-options-container:first-child .option-price span').data('value');
			$(this).find('.prod-options-container .option-price span').each(function() {
				let elValue = $(this).data('value')
				let calcValue = elValue - selectedValue > 0 ? '+' + (elValue - selectedValue) : elValue - selectedValue < 0 ? elValue - selectedValue : '';
				// console.log(elValue , selectedValue, elValue - selectedValue)
				$(this).html(calcValue)
			});
			// console.log(selectedValue)
		}
	})

});