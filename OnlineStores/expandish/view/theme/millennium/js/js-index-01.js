$(document).ready(function() {

// Init All Carousel			
	
/*
productCarousel($('#carouselNew'));
productCarousel($('#carouselSale'),2,2,3,2,1);
productCarousel($('#postsCarousel'),2,3,3,2,1); // 3 - xl, 3 - lg, 3 - md, 2 - sm, 1 - xs
*/

$('.carouselSlick').slick({
	responsive: [
		{
		  breakpoint: 1024,
		  settings: {
			slidesToShow: 3,
			slidesToScroll: 3,
			infinite: true,
			dots: true
		  }
		},
		{
		  breakpoint: 600,
		  settings: {
			slidesToShow: 2,
			slidesToScroll: 2
		  }
		},
		{
		  breakpoint: 480,
		  settings: {
			slidesToShow: 1,
			slidesToScroll: 1
		  }
		}]
})
$('.carouselSlickHolder span.btn-next').click(function(){
	$(this).parentsUntil('.carouselSlickHolder').find('.carouselSlick').slick('slickNext');
});
$('.carouselSlickHolder span.btn-prev').click(function(){
	$(this).parentsUntil('.carouselSlickHolder').find('.carouselSlick').slick('slickPrev');
});

mobileOnlyCarousel();
bannerCarousel($('.banner-carousel'));
bannerCarousel($('.category-carousel'));				
brandsCarousel($('.brands-carousel'));

/*
var feed = new Instafeed({
	get: 'user',
	userId: '2324131028',
	clientId: '422b4d6cf31747f7990a723ca097f64e',
	limit: 20,
	sortBy: 'most-liked',
	resolution: "standard_resolution",
	accessToken: '2324131028.422b4d6.d6d71d31431a4e8fbf6cb1efa1a2dfdc',
	template: '<a href="{{link}}" target="_blank"><img src="{{image}}" /></a>'
});
feed.run();
*/

// Revolution Slider
var windowW = window.innerWidth || $(window).width();
var fullwidth;
var fullscreen;

jQuery(window).resize(sliderOptions);
sliderOptions();
function sliderOptions(){
	if (windowW > 767) {
		fullwidth = "off";
		fullscreen = "on";	
	} else {
		fullwidth = "on";
		fullscreen = "off";	
	}	
}




	jQuery('.tp-banner').show().revolution(
	{
	dottedOverlay:"none",
	delay:16000,
	startwidth:2048,
	startheight:900,
	hideThumbs:200,
	hideTimerBar:"on",
	
	thumbWidth:100,
	thumbHeight:50,
	thumbAmount:5,
	
	navigationType:"none",
	navigationArrows:"",
	navigationStyle:"",
	
	touchenabled:"on",
	onHoverStop:"on",
	
	swipe_velocity: 0.7,
	swipe_min_touches: 1,
	swipe_max_touches: 1,
	drag_block_vertical: false,
				
	parallax:"mouse",
	parallaxBgFreeze:"on",
	parallaxLevels:[7,4,3,2,5,4,3,2,1,0],
				
	keyboardNavigation:"off",
	
	navigationHAlign:"center",
	navigationVAlign:"bottom",
	navigationHOffset:0,
	navigationVOffset:20,

	soloArrowLeftHalign:"left",
	soloArrowLeftValign:"center",
	soloArrowLeftHOffset:20,
	soloArrowLeftVOffset:0,

	soloArrowRightHalign:"right",
	soloArrowRightValign:"center",
	soloArrowRightHOffset:20,
	soloArrowRightVOffset:0,
		
	shadow:0,
	fullWidth: fullwidth,
	fullScreen: fullscreen,

	spinner:"",
	h_align:"left",
	
	stopLoop:"off",
	stopAfterLoops:-1,
	stopAtSlide:-1,

	shuffle:"off",
	
	autoHeight:"off",           
	forceFullWidth:"off",           
										
				
	hideThumbsOnMobile:"off",
	hideNavDelayOnMobile:1500,            
	hideBulletsOnMobile:"off",
	hideArrowsOnMobile:"off",
	hideThumbsUnderResolution:0,
	
	hideSliderAtLimit:0,
	hideCaptionAtLimit:0,
	hideAllCaptionAtLilmit:0,
	startWithSlide:0,
	fullScreenOffsetContainer: "#header"  
	});
})