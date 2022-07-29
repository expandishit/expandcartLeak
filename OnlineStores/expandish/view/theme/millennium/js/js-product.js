$(document).ready(function() {
 	
	// Init All Carousel		

	thumbnailsCarousel($('.product-images-carousel ul'));
	productCarousel($('#carouselRelated'),6,4,4,2,1);
	verticalCarousel($('.vertical-carousel-2'),3);
	productCarousel($('#mobileGallery'),1,1,1,1,1);

	
	elevateZoom();

})