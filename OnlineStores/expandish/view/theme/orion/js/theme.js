(function($){
"use strict"; // Start of use strict
$(function() {
	//Pre Load
    $(document).ready(function(){
		$('body').removeClass('preload'); 
	});
	//Check RTL
	if($('body').attr('dir')=="rtl"){
		$('body').addClass("right-to-left");
	}else{
		$('body').removeClass("right-to-left");
	}
	//Full Mega Menu
	if($('.main-nav').length>0){
		$('.main-nav').each(function(){
			var nav = $(this);
			if($('body').attr('dir')=="rtl"){
				var nav_os = ($(window).width() - (nav.offset().left + nav.outerWidth()));
				var par_os = ($(window).width() - (nav.parents('.container').offset().left + nav.parents('.container').outerWidth()));
				var nav_right = nav_os - par_os - 15;
				nav.find('.has-mega-menu > .sub-menu').css('margin-right','-' + nav_right + 'px');
			}else{
				var nav_os = nav.offset().left;
				var par_os = nav.parents('.container').offset().left;
				var nav_left = nav_os - par_os - 15;
				nav.find('.has-mega-menu > .sub-menu').css('margin-left','-' + nav_left + 'px');
			}
		});
	}
	//Tag Toggle
	if($('.toggle-tab').length>0){
		$('.toggle-tab').each(function(){
			var tab = $(this);
			tab.find('.item-toggle-tab').first().find('.toggle-tab-content').show();
			$('.toggle-tab-title').on('click',function(){
				var title = $(this);
				title.parent().siblings().removeClass('active');
				title.parent().toggleClass('active');
				title.parents('.toggle-tab').find('.toggle-tab-content').slideUp();
				title.next().stop(true,false).slideToggle();
			});
		});
	}
	//Popup Wishlist
	$('.wishlist-link').on('click',function(event){
		event.preventDefault();
		$('.wishlist-mask').fadeIn();
		var counter = 5;
		var popup;
		popup = setInterval(function() {
			counter--;
			if(counter < 0) {
				clearInterval(popup);
				$('.wishlist-mask').hide();
			} else {
				$(".wishlist-countdown").text(counter.toString());
			}
		}, 1000);
	});
	//Menu Responsive
	$('.toggle-mobile-menu').on('click',function(event){
		event.preventDefault();
		$(this).parents('.main-nav').toggleClass('active');
	});
	//Custom ScrollBar
	if($('.custom-scroll').length>0){
		$('.custom-scroll').each(function(){
			$(this).mCustomScrollbar({
				scrollButtons:{
					enable:true,
				},
				advanced:{
					autoScrollOnFocus: false,
				}  
			});
		});
	}
	//Animate
	if($('.wow').length>0){
		new WOW().init();
	}
	//Light Box
	if($('.fancybox').length>0){
		$('.fancybox').fancybox();	
	}
	//Back To Top
	$('.scroll-top').on('click',function(event){
		event.preventDefault();
		$('html, body').animate({scrollTop:0}, 'slow');
	});
	//Box Hover Dir
	$('.box-hover-dir').each( function() {
		$(this).hoverdir(); 
	});
	//Background Image
	if($('.banner-background').length>0){
		$('.banner-background').each(function(){
			var i_url = $(this).find('.image-background').attr("src");
			$(this).css('background-image','url("'+i_url+'")');	
		});
	}
	//Box Parallax	
	if($('.parallax').length>0){
		$('.parallax').each(function(){
			var p_url = $(this).attr("data-image");
			$(this).css('background-image','url("'+p_url+'")');	
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
//Detail Gallery
function detail_gallery(){
	if($('.detail-gallery').length>0){
		$('.detail-gallery').each(function(){
			var data=$(this).find(".carousel").data();
			$(this).find(".carousel").jCarouselLite({
				btnNext: $(this).find(".gallery-control .next"),
				btnPrev: $(this).find(".gallery-control .prev"),
				speed: 800,
				visible:data.visible,
				vertical:data.vertical,
			});
			//Elevate Zoom
			$('.detail-gallery').find('.mid img').elevateZoom({
				zoomType: "inner",
				cursor: "crosshair",
				zoomWindowFadeIn: 500,
				zoomWindowFadeOut: 750
			});
			$(this).find(".carousel a").on('click',function(event) {
				event.preventDefault();
				$(this).parents('.detail-gallery').find(".carousel a").removeClass('active');
				$(this).addClass('active');
				var z_url =  $(this).find('img').attr("src");
				$(this).parents('.detail-gallery').find(".mid img").attr("src", z_url);
				$('.zoomWindow').css('background-image','url("'+z_url+'")');
			});
		});
	}
}
//Menu Responsive
function menu_responsive(){
	if($(window).width()<768){
		if($('.btn-toggle-mobile-menu').length>0){
			return false;
		}else{
			$('.main-nav li.menu-item-has-children,.main-nav li.has-mega-menu').append('<span class="btn-toggle-mobile-menu"></span>');
			$('.main-nav .btn-toggle-mobile-menu').on('click',function(event){
				$(this).toggleClass('active');
				$(this).prev().stop(true,false).slideToggle();
			});
		}
	}else{
		$('.btn-toggle-mobile-menu').remove();
		$('.main-nav .sub-menu,.main-nav .mega-menu').slideDown();
	}
}
//Document Ready
jQuery(document).ready(function(){
	//Remove All
	$('.remove-all').on('click',function(event){
		event.preventDefault();
		$(this).parents('.current-category').remove();	
	});
	//Toggle Filter
	$('.filter-attr>.title18').on('click',function(event){
		$(this).toggleClass('active');
		$(this).next().slideToggle();
	});
	$('.list-attr>li>a,.list-color-filter>a').on('click',function(event){
		event.preventDefault();
		$(this).toggleClass('active');
	});
	//Hover Acive
	if($('.box-hover-active').length>0){
		$('.box-hover-active').each(function(){
			$(this).find('.item-hover-active').on('mouseover',function(){
				$(this).parents('.box-hover-active').find('.item-hover-active').removeClass('active');
				$(this).addClass('active');
			});
			$(this).on('mouseout',function(){
				$(this).find('.item-hover-active').removeClass('active');
				$(this).find('.item-active').addClass('active');
			});
		});
	}
	//Product Color
	if($('.list-color').length>0){
		$('.list-color a').on('mouseover',function(){
			$(this).parents('.product-thumb').find('.product-thumb-link img').attr('src',$(this).attr("data-src"));
		});
	}
	//Filter Price
	if($('.range-filter').length>0){
		$('.range-filter').each(function(){
			$(this).find( ".slider-range" ).slider({
				range: true,
				min: 0,
				max: 800,
				values: [ 50, 545 ],
				slide: function( event, ui ) {
					$(this).parents('.range-filter').find( ".amount" ).html( '<label>$</label>' + '<span>' +ui.values[ 0 ]+'</span>' + ' - ' + '<span>' + ui.values[ 1 ]+'</span>');
				}
			});
			$(this).find( ".amount" ).html('<label>$</label>' + '<span>' +$(this).find( ".slider-range" ).slider( "values", 0 )+'</span>' + ' - ' + '<span>' +$(this).find( ".slider-range" ).slider( "values", 1 )+'</span>');
		});
	}
	//Qty Up-Down
	$('.detail-qty').each(function(){
		$(this).find('.qty-up').on('click',function(event){
			event.preventDefault();
			var up = $(this);
			var qtyval = parseInt(up.parent().find('.qty-val').text(),10);
			qtyval=qtyval+1;
			up.parent().find('.qty-val').text(qtyval);
		});
		$(this).find('.qty-down').on('click',function(event){
			event.preventDefault();
			var down = $(this);
			var qtyval = parseInt(down.parent().find('.qty-val').text(),10);
			qtyval=qtyval-1;
			if(qtyval>1){
				down.parent().find('.qty-val').text(qtyval);
			}else{
				qtyval=1;
				down.parent().find('.qty-val').text(qtyval);
			}
		});
	});
	//Detail Gallery
	detail_gallery();
	//Offset Menu
	offset_menu();
	menu_responsive();
	
});
//Window Load
jQuery(window).on('load',function(){ 
	//Owl Carousel
	if($('.wrap-item').length>0){
		$('.wrap-item').each(function(){
			var owl = $(this);
			var data = owl.data();
			owl.owlCarousel({
				addClassActive:true,
				stopOnHover:true,
				lazyLoad:true,
				itemsCustom:data.itemscustom,
				autoPlay:data.autoplay,
				transitionStyle:data.transition, 
				paginationNumbers:data.paginumber,
				beforeInit:background,
				afterAction:animated,
				navigationText:['<i class="icon ion-android-arrow-back"></i>','<i class="icon ion-android-arrow-forward"></i>'],
			});
		});
	}
	//Trigger Slider
	var wrap = $('.control-slider .wrap-item');
	$('.control-slider .prev').on('click', function(e){
		e.preventDefault();
		wrap.trigger('owl.prev');
	});
	$('.control-slider .next').on('click', function(e){
		e.preventDefault();
		wrap.trigger('owl.next');
	});
	//Parallax Slider
	if($('.parallax-slider').length>0){
		$(window).scroll(function() {
			var ot = $('.parallax-slider').offset().top;
			var sh = $('.parallax-slider').height();
			var st = $(window).scrollTop();
			var top = (($(window).scrollTop() - ot) * 0.5) + 'px';
			var item = $('.parallax-slider .item-slider');
			if(st>ot&&st<ot+sh){
				item.css({
					'background-position': 'center ' + top
				});
			}else if(st<ot){
				item.css({
					'background-position': 'center 0'
				});
			}else{
				return false;
			}
		});
	}
	//Banner Bx Slider
	if($('.banner-bx-sider').length>0){
		$('.banner-bx-sider').each(function(){
			var bx = $(this);
			bx.bxSlider({
				prevText:'<i class="fa fa-angle-down" aria-hidden="true"></i> Scroll',
				nextText:'',
				pager:false,
				mode: 'vertical',
			});
		});
	}
	//Bx Slider
	if($('.bx-slider').length>0){
		$('.bx-slider').each(function(){
			var bx = $(this);
			bx.find('.bxslider').bxSlider({
				prevText:'<i class="icon ion-android-arrow-back"></i>',
				nextText:'<i class="icon ion-android-arrow-forward"></i>',
				pagerCustom: bx.find('.bx-pager'),
			});
		});
	}
	//Time Countdown
	if($('.time-countdown').length>0){
		$(".time-countdown").each(function(){
			var time = $(this);
			var data = time.data(); 
			time.TimeCircles({
				fg_width: data.width,
				bg_width: 0,
				text_size: 0,
				circle_bg_color: data.bg,
				time: {
					Days: {
						show: data.day,
						text: data.text[0],
						color: data.color,
					},
					Hours: {
						show: data.hou,
						text: data.text[1],
						color: data.color,
					},
					Minutes: {
						show: data.min,
						text: data.text[2],
						color: data.color,
					},
					Seconds: {
						show: data.sec,
						text: data.text[3],
						color: data.color,
					}
				}
			}); 
		});
	}
	//Count Down Master
	if($('.countdown-master').length>0){
		$('.countdown-master').each(function(){
			var clock = $(this);
			clock.FlipClock(65100,{
		        clockFace: 'HourlyCounter',
		        countdown: true,
		        autoStart: true,
		    });
		});
	}
	//Blog Masonry 
	if($('.masonry-list-post').length>0){
		$('.masonry-list-post').masonry({
			itemSelector: '.item-post-masonry',
		});
	}
	//Percentage
	$('.percentage').each(function(){
		var circle = $(this);
		var data = circle.data();
		// console.log(data);
		circle.circularloader({
			backgroundColor: "#ffffff",//background colour of inner circle
			fontColor: "#000000",//font color of progress text
			fontSize: "40px",//font size of progress text
			radius: 90,//radius of circle
			progressBarBackground: "#e9e9e9",//background colour of circular progress Bar
			progressBarColor: data.color,//colour of circular progress bar
			progressBarWidth: 10,//progress bar width
			progressPercent: data.value,//progress percentage out of 100
			progressValue:0,//diplay this value instead of percentage
			showText: false,//show progress text or not
			title: "",//show header title for the progress bar
		});
	});
});
//Window Resize
jQuery(window).on('resize',function(){
	offset_menu();
	fixed_header();
	detail_gallery();
	menu_responsive();
});
//Window Scroll
jQuery(window).on('scroll',function(){
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