(function($){
"use strict"; // Start of use strict
$(function() {
	$('.show-product ul.list-none li a:first').tab('show');

	$('#notification .close').on('click',function(event){
		$('notification').hide();
	});	
	//Tag Toggle
	if($('.toggle-tab').length>0){
		$('.toggle-tab').each(function(){
			$(this).find('.item-toggle-tab').first().find('.toggle-tab-content').show();
			$('.toggle-tab-title').on('click',function(event){
				if($(this).next().length>0){
					event.preventDefault();
					$(this).parent().siblings().removeClass('active');
					$(this).parent().addClass('active');
					$(this).parents('.toggle-tab').find('.toggle-tab-content').slideUp();
					$(this).next().stop(true,false).slideDown();
				}
			});
		});
	}
	//Popup Wishlist
	$('.wishlist-link').on('click',function(event){
		event.preventDefault();
		$('#wishPopUp').fadeIn();
		var counter = 10;
		var popup;
		popup = setInterval(function() {
			counter--;
			if(counter < 0) {
				clearInterval(popup);
				$('#wishPopUp').hide();
			} else {
				$("#wishPopUp .wishlist-countdown").text(counter.toString());
			}
		}, 1000);
	});	

	//Title Tab Lamp
	if($('.title-lamp').length>0){
		$('.title-lamp').each(function(){
			$(this).append('<div class="ef-lamp"></div>');
			$(this).find('.ef-lamp').width($(this).find('li.active').width());
			var osl=$(this).find('li.active').offset().left-$(this).offset().left;
			$(this).find('.ef-lamp').css('left',osl+'px');
			$(this).find('.list-inline-block li a').on('click',function(){
				$(this).parents('.title-lamp').find('.ef-lamp').width($(this).parent().width());
				var osl=$(this).offset().left-$(this).parents('.title-lamp').offset().left;
				$(this).parents('.title-lamp').find('.ef-lamp').css('left',osl+'px');
			});
		});
	}
	//Menu Responsive
	// $('.toggle-mobile-menu').on('click',function(event){
	// 	event.preventDefault();
	// 	$(this).parents('.main-nav').toggleClass('active');
	// });
	$('.main-nav li.menu-item-has-children>a').on('click',function(event){
		if($(window).width()<768){
			event.preventDefault();
			$(this).next().stop(true,false).slideToggle();
		}
	});
	//Custom ScrollBar
	if($('.custom-scroll').length>0){
		$('.custom-scroll').each(function(){
			$(this).mCustomScrollbar({
				scrollButtons:{
					enable:true
				}
			});
		});
	}
	//Detail Gallery
	if($('.wrap-detail-gallery').length>0){
		$('.wrap-detail-gallery').each(function(){
			$(this).find(".carousel").jCarouselLite({
				btnNext: $(this).find(".gallery-control .next"),
				btnPrev: $(this).find(".gallery-control .prev"),
				speed: 800,
				visible:4,
				vertical: true
			});
			//Elevate Zoom
			if ($(window).width() > 767) {
				if ( $('html').attr('lang') == 'ar' ) {
					$(this).find('.mid img').elevateZoom({
						cursor: "pointer",
						zoomWindowFadeIn: 500,
						zoomWindowPosition: 9,
						zoomType : "window",
						gallery:'zoomGallery',
						scrollZoom : true ,
						zoomWindowFadeOut: 750
					});
					$("#zoom_01").bind("click", function(e) {  
					var ez =   $('#zoom_01').data('elevateZoom');	
					$.fancybox(ez.getGalleryList());
					return false;
				});
					} else {
					$(this).find('.mid img').elevateZoom({
						cursor: "pointer",
						zoomWindowFadeIn: 500,
						zoomType : "window",
						gallery:'zoomGallery',
						scrollZoom : true ,
						zoomWindowFadeOut: 750
					});	
					$("#zoom_01").bind("click", function(e) {  
					var ez =   $('#zoom_01').data('elevateZoom');	
					$.fancybox(ez.getGalleryList());
					return false;
				});
				}
				   
			}
			else {
				if ( $('html').attr('lang') == 'ar' ) {
					$(this).find('.mid img').elevateZoom({
						cursor: "pointer",
						zoomWindowFadeIn: 500,
						zoomWindowPosition: 10,
						zoomType : "inner",
						gallery:'zoomGallery',
						galleryActiveClass: 'active',
						zoomWindowFadeOut: 750
					});
					 
					} else {
					$(this).find('.mid img').elevateZoom({
						cursor: "pointer",
						zoomWindowFadeIn: 500,
						zoomType : "inner",
						zoomWindowFadeOut: 750
					});	
				}

			} 
			
			$(this).find(".carousel a").on('click',function(event) {
				event.preventDefault();
				$(this).parents('.detail-gallery').find(".carousel a").removeClass('active');
				$(this).addClass('active');
				$(this).parents('.detail-gallery').find(".mid img").attr("src", $(this).find('img').attr("src"));
				var z_url = $(this).parents('.detail-gallery').find('.mid img').attr('src');
				$('.zoomWindow').css('background-image','url("'+z_url+'")');
			});
		});
	}
});
//Offset Menu
function offset_menu(){
	if($(window).width()>767){
		$('.main-nav .sub-menu').each(function(){
			var wdm = $(window).width();
			var wde = $(this).width();
			var offset = $(this).offset().left;
			var tw = offset+wde;
			if(tw>wdm){
				$(this).addClass('offset-right');
			}
		});
	}else{
		return false;
	}
}
//Fixed Header
function fixed_header(){
	if($('.header-ontop').length>0){
		if($(window).width()>1023){
			var ht = $('#header').height();
			var st = $(window).scrollTop();
			if(st>ht){
				$('.header-ontop').addClass('fixed-ontop');
			}else{
				$('.header-ontop').removeClass('fixed-ontop');
			}
		}else{
			$('.header-ontop').removeClass('fixed-ontop');
		}
	}
} 
//Slider Background
function background(){
	$('.bg-slider .item-slider').each(function(){
		var src=$(this).find('.banner-thumb a img').attr('src');
		$(this).css('background-image','url("'+src+'")');
	});	
}
function animated(){
	$('.banner-slider .owl-item').each(function(){
		var check = $(this).hasClass('active');
		if(check==true){
			$(this).find('.animated').each(function(){
				var anime = $(this).attr('data-animated');
				$(this).addClass(anime);
			});
		}else{
			$(this).find('.animated').each(function(){
				var anime = $(this).attr('data-animated');
				$(this).removeClass(anime);
			});
		}
	});
}
function slick_animated(){
	$('.banner-slider .item-slider').each(function(){
		var check = $(this).hasClass('slick-active');
		if(check==true){
			$(this).find('.animated').each(function(){
				var anime = $(this).attr('data-animated');
				$(this).addClass(anime);
			});
		}else{
			$(this).find('.animated').each(function(){
				var anime = $(this).attr('data-animated');
				$(this).removeClass(anime);
			});
		}
	});
}
//Document Ready
jQuery(document).ready(function(){
	//Fixed Main Nav
	$('.menu-fixed .btn-menu-fixed').on('click',function(event){
		event.preventDefault();
		$('.wrap').toggleClass('boxed-overlay');
	});
	//Banner Background
	if($('.adv-background').length>0){
		$('.adv-background').each(function(){
			var adv_src = $(this).find('.adv-thumb img').attr('src');
			$(this).css('background-image','url("'+adv_src+'")')
		});
	}
	//Week Count Down
	if($('.week-countdown').length>0){
		var austDay = new Date();
		austDay = new Date(austDay.getFullYear() + 3, 1 - 1, 26);
		$('.week-countdown').countdown({
			until: austDay,
			format: 'WDHMS',
			labels: ['Years', 'Months', 'Weeks', 'Days', 'Hours', 'Min', 'Sec']
		});
	}
	$('.title-tab-gal-detail .list-inline-block a').on('click',function(event){
		event.preventDefault();
		var id = $(this).attr('href').substring(1);
		$('.tab-pane').each(function(){
			if($(this).attr('id')==id){
				var z_url = $(this).find(".mid img").attr('src');
				$('.zoomWindow').css('background-image','url("'+z_url+'")');
			}
		});
	});
	//Product Gallery
	$('.thumb-gallery a').on('click',function(event){
		event.preventDefault();
		$(this).siblings().removeClass('active');
		$(this).addClass('active');
		var src=$(this).find('img').attr('src');
		$(this).parents('.product-thumb').find('.product-thumb-link img').attr('src',src);
	});
	//Widget Toggle
	$('.widget-title').on('click',function(){
		$(this).toggleClass('active');
		$(this).next().slideToggle();
	});
	//Filter Default
	$('.filter-default a').on('click',function(event){
		event.preventDefault();
		$(this).toggleClass('active');
	});
	
	//Widget Product Category
	$('.widget-product-cat ul li.has-sub-cat>a').on('click',function(event){
		event.preventDefault();
		$(this).next().slideToggle();
	});
	//Shop The Look
	$('.box-hover-dir').each( function() {
		$(this).hoverdir(); 
	});
	//Filter Price
	if($('.range-filter').length>0){
		$( ".range-filter #slider-range" ).slider({
			range: true,
			min: 0,
			max: 700,
			values: [ 50, 500 ],
			slide: function( event, ui ) {
			$( "#amount" ).html( "<span class='number'>" + ui.values[ 0 ] + "</span>" + "<span class='separate'> - </span>" + "<span class='number'>" + ui.values[ 1 ] + "</span>" );
			}
		});
		$( ".range-filter #amount" ).html( "<span class='number'>" + $( "#slider-range" ).slider( "values", 0 )+ "</span>" + " <span class='separate'> - </span> " + "<span class='number'>" + $( "#slider-range" ).slider( "values", 1 ) + "</span>" );
	}
	//Qty Up-Down
	$('.info-qty').each(function(){
		var qtyval = parseInt($(this).find('.qty-val').text());
		$(this).find('.qty-up').on('click',function(event){
			event.preventDefault();
			qtyval=qtyval+1;
			$('.qty-val').text(qtyval);
		});
		$(this).find('.qty-down').on('click',function(event){
			event.preventDefault();
			qtyval=qtyval-1;
			if(qtyval>1){
				$('.qty-val').text(qtyval);
			}else{
				qtyval=1;
				$('.qty-val').text(qtyval);
			}
		});
	});
	//Offset Menu
	offset_menu();
	//Animate
	if($('.wow').length>0){
		new WOW().init();
	}
	//Product Quick View
	$('.quickview-link').fancybox();	
	//Back To Top
	$('.scroll-top').on('click',function(event){
		event.preventDefault();
		$('html, body').animate({scrollTop:0}, 'slow');
	});
});
//Window Load
jQuery(window).load(function(){ 
	//Post Effect
	$('.item-post-format.item-blog-full .post-info-hidden').hide();
	$('.item-post-format.item-blog-full').on('mouseover',function(event){
		$(this).find('.post-info-hidden').stop(true,false).slideDown();
		$(this).find('.post-comment-date').stop(true,false).slideUp();
	});
	$('.item-post-format.item-blog-full').on('mouseout',function(event){
		$(this).find('.post-info-hidden').stop(true,false).slideUp();
		$(this).find('.post-comment-date').stop(true,false).slideDown();
	});	
	//Owl Carousel
	if($('.wrap-item').length>0){
		$('.wrap-item').each(function(){
			var data = $(this).data();
			$(this).owlCarousel({
				addClassActive:true,
				stopOnHover:true,
				autoHeight:true,
				itemsCustom:data.itemscustom,
				autoPlay:data.autoplay,
				transitionStyle:data.transition, 
				paginationNumbers:data.paginumber,
				beforeInit:background,
				afterAction:animated,
				navigationText:['<i class="fa fa-arrow-circle-left" aria-hidden="true"></i>','<i class="fa fa-arrow-circle-right" aria-hidden="true"></i>'],
			});
			$(this).find('.owl-controls').css('left',data.control+'px');
		});
	}
	//Parallax Slider
	if($('.parallax-slider').length>0){
		$(window).scroll(function() {
			var ot = $('.parallax-slider').offset().top;
			var sh = $('.parallax-slider').height();
			var st = $(window).scrollTop();
			var top = (($(window).scrollTop() - ot) * 0.4) + 'px';
			if(st>ot&&st<ot+sh){
				$('.parallax-slider .item-slider').css({
					'background-position': 'center ' + top
				});
			}
			if(st<ot){
				$('.parallax-slider .item-slider').css({
					'background-position': 'center 0'
				});
			}else{
				return false;
			}
		});
	}
	//Slick Slider
	if($('.slick-slider .slick').length>0){
		$('.slick-slider .slick').each(function(){
			$(this).slick({
				centerMode: true,
				infinite: true,
				centerPadding: '340px',
				slidesToShow: 1,
				prevArrow:'<div class="slick-prev"><i class="fa fa-arrow-circle-left" aria-hidden="true"></i></div>',
				nextArrow:'<div class="slick-next"><i class="fa fa-arrow-circle-right" aria-hidden="true"></i></div>',
				responsive: [
				{
				  breakpoint: 1200,
				  settings: {
					centerPadding: '0px',
				  }
				}]
			});
		});
	}		
});
//Window Resize
jQuery(window).resize(function(){
	offset_menu();
	fixed_header();
});
//Window Scroll
jQuery(window).scroll(function(){
	//Scroll Top
	if($(this).scrollTop()>$(this).height()){
		$('.scroll-top').addClass('active');
	}else{
		$('.scroll-top').removeClass('active');
	}
	//Fixed Header
	fixed_header();
});
})(jQuery); // End of use strict