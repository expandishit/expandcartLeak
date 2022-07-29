$(document).ready(function () {
	
	var position_left = $("#categories ul").eq(0).position().left;

	$("#categories div > ul > li ").hover(function () {
		var position_li = $(this).position().left;
		var roznica = position_li-position_left;
		var troznica = roznica-280;
		if(roznica>290) {
			$(this).find(".column-4").css("margin-left", "-"+troznica+"px");
		}
		var troznica1 = roznica-450;
		if(roznica>460) {
			$(this).find(".column-3").css("margin-left", "-"+troznica1+"px");
		}
		var troznica2 = roznica-620;
		if(roznica>630) {
			$(this).find(".column-2").css("margin-left", "-"+troznica2+"px");
		}
	});
	
	// Animation for the languages and currency dropdown
	$('.switcher').hover(function() {
	$(this).find('.option').stop(true, true).slideDown(300);
	},function() {
	$(this).find('.option').stop(true, true).slideUp(150);
	}); 
	

	
	/* Items */
	var item = 2; // list of items on one page
	var active = 0;
	var all_element = $(".btn li.number").length;
	$(".list-items li").css("display", "none");
	$(".list-items li").slice(0, item).css("display", "block");
	$('.btn li.number').eq(0).addClass("active");

	$('.btn li.number a').click(function() {
		var element_index = $('.btn li.number a').index(this);
		active = element_index;
		$('.btn li.number').removeClass("active");
		$('.btn li.number').eq(element_index).addClass("active");
		$(".list-items li").hide();
		$(".list-items li").slice(element_index*item, element_index*item+item).fadeIn(400);
		return false;
	});
	
	$('.btn li a.prev').click(function() {
		active = active-1;
		if(active<0) { active = 0; }
		$('.btn li.number').removeClass("active");
		$('.btn li.number').eq(active).addClass("active");
		$(".list-items li").hide();
		$(".list-items li").slice(active*item, active*item+item).fadeIn(400);
		return false;
	});
	
	$('.btn li a.next').click(function() {
		active = active+1;
		if(active>=all_element) { active = all_element-1; }
		$('.btn li.number').removeClass("active");
		$('.btn li.number').eq(active).addClass("active");
		$(".list-items li").hide();
		$(".list-items li").slice(active*item, active*item+item).fadeIn(400);
		return false;
	});
	
	/* Categories */

	$("#categories div > ul > li ").hover(function () {
						
		if ($.browser.msie && ($.browser.version == 8 || $.browser.version == 7 || $.browser.version == 6)) {
		
			$(this).find("ul.sub-menu").eq(0).show();
		
	 	} else {
		
			$(this).find("ul.sub-menu").eq(0).slideToggle(300);
		
		}
								
	},function () {

		if ($.browser.msie && ($.browser.version == 8 || $.browser.version == 7 || $.browser.version == 6)) {
		
			$(this).find("ul.sub-menu").eq(0).hide();		
		
		} else {
	
			$(this).find("ul.sub-menu").eq(0).slideToggle(300);
		
		}

	}); 
	
		$("#categories div > ul > li > ul.sub-menu > li ").hover(function () {

			if ($.browser.msie && ($.browser.version == 8 || $.browser.version == 7 || $.browser.version == 6)) {
			
		  		$(this).find("ul.sub-menu").eq(0).show();
			
			} else {

		  		$(this).find("ul.sub-menu").eq(0).slideToggle(300);
			
			}

		},function () {
		
			if ($.browser.msie && ($.browser.version == 8 || $.browser.version == 7 || $.browser.version == 6)) {
			
				$(this).find("ul.sub-menu").eq(0).hide();
			
			} else {

				$(this).find("ul.sub-menu").eq(0).slideToggle(300);
			
			}

		}); 
		 		 
				 
	/* autoclear function for inputs */
	$('.autoclear').click(function() {
	if (this.value == this.defaultValue) {
	this.value = '';
	}
	});

	$('.autoclear').blur(function() {
	if (this.value == '') {
	this.value = this.defaultValue;
	}
	});
	
	/* OPENCART */
	
	/* Search */
	$('.button-search').bind('click', function() {
		url = $('base').attr('href') + 'index.php?route=product/search';
				 
		var filter_name = $('input[name=\'filter_name\']').attr('value')
		
		if (filter_name) {
			url += '&filter_name=' + encodeURIComponent(filter_name);
		}
		
		location = url;
	});
	
	$('.search input[name=\'filter_name\']').keydown(function(e) {
		if (e.keyCode == 13) {
			url = $('base').attr('href') + 'index.php?route=product/search';
			 
			var filter_name = $('input[name=\'filter_name\']').attr('value')
			
			if (filter_name) {
				url += '&filter_name=' + encodeURIComponent(filter_name);
			}
			
			location = url;
		}
	});
	
	$('.success img, .warning img, .attention img, .information img').live('click', function() {
		$(this).parent().fadeOut('slow', function() {
			$(this).remove();
		});
	});	
	
	/* Ajax Cart */
	$('.shopping-cart div').hover(function() {
		

		
	});

});

function addToCart(product_id) {
	$.ajax({
		url: 'index.php?route=checkout/cart/add',
		type: 'post',
		data: 'product_id=' + product_id,
		dataType: 'json',
		success: function(json) {
			$('.success, .warning, .attention, .information, .error').remove();
			
			if (json['redirect']) {
				location = json['redirect'];
			}
			
			if (json['error']) {
				if (json['error']['warning']) {
					$('#notification').html('<div class="warning" style="display: none;">' + json['error']['warning'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /><p class="clear"></p></div>');
					
					$('.warning').fadeIn('slow');
					
					$('html, body').animate({ scrollTop: 0 }, 'slow');
				}
			}	 
						
			if (json['success']) {
				$('#notification').html('<div class="success" style="display: none;">' + json['success'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /><p class="clear"></p></div>');
				
				$('.success').fadeIn('slow');
				
				$('#cart_total').html(json['total']);
				
				$('html, body').animate({ scrollTop: 0 }, 'slow'); 
			}	
		}
	});
}

function removeCart(key) {
	$.ajax({
		url: 'index.php?route=checkout/cart/add',
		type: 'post',
		data: 'remove=' + key,
		dataType: 'json',
		success: function(json) {
			$('.success, .warning, .attention, .information').remove();
			
			if (json['output']) {
				$('#cart_total').html(json['total']);
				
				$('#cart .content').html(json['output']);
			}			
		}
	});
}

function removeVoucher(key) {
	$.ajax({
		url: 'index.php?route=checkout/cart/add',
		type: 'post',
		data: 'voucher=' + key,
		dataType: 'json',
		success: function(json) {
			$('.success, .warning, .attention, .information').remove();
			
			if (json['output']) {
				$('#cart_total').html(json['total']);
				
				$('#cart .content').html(json['output']);
			}			
		}
	});
}

function addToWishList(product_id) {
	$.ajax({
		url: 'index.php?route=account/wishlist/update',
		type: 'post',
		data: 'product_id=' + product_id,
		dataType: 'json',
		success: function(json) {
			$('.success, .warning, .attention, .information').remove();
						
			if (json['success']) {
				$('#notification').html('<div class="success" style="display: none;">' + json['success'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /><p class="clear"></p></div>');
				
				$('.success').fadeIn('slow');
				
				$('#wishlist_total').html(json['total']);
				
				$('html, body').animate({ scrollTop: 0 }, 'slow'); 				
			}	
		}
	});
}

function addToCompare(product_id) { 
	$.ajax({
		url: 'index.php?route=product/compare/update',
		type: 'post',
		data: 'product_id=' + product_id,
		dataType: 'json',
		success: function(json) {
			$('.success, .warning, .attention, .information').remove();
						
			if (json['success']) {
				$('#notification').html('<div class="success" style="display: none;">' + json['success'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /><p class="clear"></p></div>');
				
				$('.success').fadeIn('slow');
				
				$('#compare_total').html(json['total']);
				
				$('html, body').animate({ scrollTop: 0 }, 'slow'); 
			}	
		}
	});
}