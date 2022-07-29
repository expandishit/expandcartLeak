	"use strict";
	/*
	var $ = jQuery.noConflict();
	*/
	$('.cart__bottom .btn-checkout').prev('.cart__total').addClass('total_view');
	$(".rtl #cboxClose").hide();

	function debouncer(func, timeout) {
	    var timeoutID, timeout = timeout || 500;
	    return function() {
	        var scope = this,
	            args = arguments;
	        clearTimeout(timeoutID);
	        timeoutID = setTimeout(function() {
	            func.apply(scope, Array.prototype.slice.call(args));
	        }, timeout);
	    }
	}
	
	
	// Compare
	function compareSlideIni(){
	    if ($("#compareSlide").length > 0) {
	        $('.compare-link').on('click', function(e){
				$("#compareSlide").toggleClass('open');
				e.preventDefault();
			});
			 $('.compareSlide__close').on('click', function(e){
				$("#compareSlide").toggleClass('open');
				e.preventDefault();
			});
	    }	
	}

	// placeholder
	$(function() {
	    $('[placeholder]').focus(function() {
	        var input = $(this);
	        if (input.val() == input.attr('placeholder')) {
	            input.val('');
	            input.removeClass('placeholder');
	        }
	    }).blur(function() {
	        var input = $(this);
	        if (input.val() == '' || input.val() == input.attr('placeholder')) {
	            input.addClass('placeholder');
	            input.val(input.attr('placeholder'));
	        }
	    }).blur();
	    $('[placeholder]').parents('form').submit(function() {
	        $(this).find('[placeholder]').each(function() {
	            var input = $(this);
	            if (input.val() == input.attr('placeholder')) {
	                input.val('');
	            }
	        })
	    });
	});

	// Cart	
	function cartSlideIni(){
	    if ($("header .cart").length > 0) {
	        $('header .cart .dropdown-toggle').on('click', function(e){
				$("header .cart .dropdown").toggleClass('open');
				headerCartSize();
				e.preventDefault();
			});
			$('header .cart .cart__close').on('click', function(e){
				$("header .cart .dropdown").toggleClass('open');
				e.preventDefault();
			});						
	    }
	}
	var $cart = $(".cart");
	$(window).resize(headerCartSize);
	function headerCartSize() {
	  if ($cart.length) {
		$cart.find(".dropdown-menu").scrollTop(0)
		cartHeight();
	  }
	}
	function cartHeight(){
	  var $obj = $cart.find(".dropdown-menu");
	  var w_height = window.innerHeight;
	  var o_height = $obj.outerHeight();
	  var delta = parseInt(w_height - o_height);
	  if(delta < 0) {
		$obj.css({"max-height": o_height + delta, "overflow": "auto", "overflow-x": "hidden" });
	  }
	}

	
	function changeInputNameCartPage() {
      var name= "updates[]";
      if ($(window).width() > 767) {
        $(".input-mobile").attr("name", "");
        $(".input-desktop").attr("name", name);
      }
      else {
        $(".input-mobile").attr("name", name);
        $(".input-desktop").attr("name", "");
      }
    }
    if ($(".input-mobile").length && $(".input-desktop").length ) {
      changeInputNameCartPage();
      $(window).resize(changeInputNameCartPage);
    }

	// Slide Column  $('.slide-column-close').trigger('click');	
     function slideColumn(){
      if ($('#leftColumn').length > 0) {
        $(window).resize(function(){
          if(window.innerWidth < 992 ) {
           filtersHeight();
          } else {
           $('#leftColumn').removeAttr('style');
          }
        });

        
       $('.slide-column-close').addClass('position-fix');
        $('.slide-column-open').on('click', function(e){
          e.preventDefault();
          $('#leftColumn').addClass('column-open');
          $('body').css("top", -$('body').scrollTop());         
          $('body').addClass("no-scroll").append( '<div class="modal-filter"></div>');	
          if ($(".modal-filter").length > 0) {	        	
			  $(".modal-filter").click(function(){
			   $('.slide-column-close').trigger('click');
			  })
			} 
        });
        $('.slide-column-close').on('click', function(e){
          e.preventDefault();
          $("#leftColumn").removeClass('column-open');
          var top = parseInt($('body').css("top").replace("px", ""))*-1;
          $('body').removeAttr("style");
          $('body').removeClass("no-scroll");
          $('body').scrollTop(top);
          $(".modal-filter").unbind();
          $(".modal-filter").remove();
        });
      }

    }
	
    function filtersHeight(){
      var $obj = $('#leftColumn');
      var w_height = window.innerHeight;
      var o_height = $obj.outerHeight();
      var delta = parseInt(w_height - o_height);
      if(delta < 0) {
        $obj.css({"max-height": o_height + delta, "overflow": "auto", "overflow-x": "hidden" });
      }
    }

	
	
	// Countdown
	function countDown(){
	    if ($("#countdown1").length > 0) {
	        $('#countdown1').countdown({
	            until: new Date(2016, 12, 1)
	        });
	    }	
	}

	// input-counter
	function inputCounter(){
	    if ($(".input-counter").length > 0) {
	        $('.minus-btn').click(function () {
                var $input = $(this).parent().find('input');
                var count = parseInt($input.val()) - 1;
                count = count < 1 ? 1 : count;
                $input.val(count);
                $input.change();
                return false;
            });
            $('.plus-btn').click(function () {
                var $input = $(this).parent().find('input');
                $input.val(parseInt($input.val()) + 1);
                $input.change();
                return false;
            });
	    }	
	}

	//slider on product-fields
	function sliderNoZoom(){
	   if ($(".slider-no-zoom").length > 0) {
	  		$('.slider-product-large').slick({
				  slidesToShow: 1,
				  slidesToScroll: 1,
				  arrows: false,
				  fade: true,
				   centerPadding: '40px',
				   infinite: true,
				  asNavFor: '.slider-product-small'
				});
				$('.slider-product-small').slick({
				  slidesToShow: 4,
				  slidesToScroll: 1,
				  infinite: true,
				  asNavFor: '.slider-product-large',
				  dots: true,
				 centerPadding: '40px',
				  focusOnSelect: true
				});
		}		    
	}
	
	
	//mobile menu 
	jQuery(function($) {
		  if ($("#off-canvas-menu").length > 0) {
			"use strict";
		    $(document).bind('cbox_open', function() {
		        $('html').css({
		            overflow: 'hidden'
		        });
		    }).bind('cbox_closed', function() {
		        $('html').css({
		            overflow: ''
		        });
		    });


		    // check if RTL mode
		    var colorBoxMenuPosL = ($("body").hasClass('rtl')) ? 'none' : '0';
		    var colorBoxMenuPosR = ($("body").hasClass('rtl')) ? '0' : 'none';

		    $(".off-canvas-menu-toggle").colorbox({
		        inline: true,
		        opacity: 0.55,
		        transition: "none",
		        arrowKey: false,
		        width: "300",
		        height: "100%",
		        fixed: true,
		        className: 'canvas-menu',
		        top: 0,
		        right: colorBoxMenuPosR,
		        left: colorBoxMenuPosL,
		        colorBoxCartPos: 0
		    })
		} 

	});

	$(window).resize(function(){
	  var $cboxClose = $("#cboxClose");
	  if($cboxClose.length && window.innerWidth > 1024 ) {
	    $("#cboxClose").trigger("click");
	  }
	})


	jQuery(function($) {
	    "use strict";
	    $("#off-canvas-menu .expander-list").find("ul").hide().end().find(" .expander").text("+").end().find(".active").each(function() {
	        $(this).parents("li ").each(function() {
	            var $this = $(this),
	                $ul = $this.find("> ul"),
	                
	                $expander = $this.find("> .name .expander");
	            $ul.show();
	         
	            $expander.html("&minus;")
	        })
	    }).end().find(" .expander").each(function() {
	        var $this = $(this),
	            hide = $this.text() === "+",
	            $ul = $this.parent(".name").next("ul"),
	            $name = $this.next("a");
	        $this.click(function() {
	            if ($ul.css("display") ==
	                "block") $ul.slideUp("slow");
	            else $ul.slideDown("slow");
	            $(this).html(hide ? "&minus;" : "+");	            
	            hide = !hide
	        })
	    })
	});
	//end mobile
				

	// Product Carousel	
	function productCarousel(carousel,numberXl,numberLg,numberMd,numberSm,numberXs) {

		var windowW = window.innerWidth || $(window).width();

		var slidesToShowXl = (numberXl > 0) ? numberXl : 6;
		var slidesToShowLg = (numberLg > 0) ? numberLg : 4;
		var slidesToShowMd = (numberMd > 0) ? numberMd : numberLg;
		var slidesToShowSm = (numberSm > 0) ? numberSm : numberMd;
		var slidesToShowXs = (numberXs > 0) ? numberXs : 1;
		
		var carousel = carousel;
		var speed = 500;
			
		if (carousel.parent().find('.carousel-products__button').length > 0) {
	
				carousel.slick({
						prevArrow: carousel.parent().find('.carousel-products__button .btn-prev'),
						nextArrow: carousel.parent().find('.carousel-products__button .btn-next'),
						dots: true,
						slidesToShow: slidesToShowXl,
						slidesToScroll: slidesToShowXl,
						responsive: [{
							breakpoint: 1770,
							settings: {
								slidesToShow: slidesToShowLg,
								slidesToScroll: slidesToShowLg
							}
						},{
							breakpoint: 992,
							settings: {
								slidesToShow: slidesToShowMd,
								slidesToScroll: slidesToShowMd
							}
						}, {
							breakpoint: 768,
							settings: {
								slidesToShow: slidesToShowSm,
								slidesToScroll: slidesToShowSm
							}
						}, {
							breakpoint: 480,
							settings: {
								slidesToShow: slidesToShowXs,
								slidesToScroll: slidesToShowXs
							}
						}]
					});
			}
			else {
				carousel.slick({
					slidesToShow: slidesToShowXl,
					slidesToScroll: 1,
					speed: speed,
						responsive: [{
							breakpoint: 1770,
							settings: {
								slidesToShow: slidesToShowLg,
								slidesToScroll: slidesToShowLg
							}
						},{
							breakpoint: 992,
							settings: {
								slidesToShow: slidesToShowMd,
								slidesToScroll: slidesToShowMd
							}
						}, {
							breakpoint: 768,
							settings: {
								slidesToShow: slidesToShowSm,
								slidesToScroll: slidesToShowSm
							}
						}, {
							breakpoint: 480,
							settings: {
								slidesToShow: slidesToShowXs,
								slidesToScroll: slidesToShowXs
							}
						}]
				});
			}
		

		fixCarouselHover(carousel);

	};

	// Product productBigCarousel	
	function productBigCarousel(carousel,numberXl,numberLg,numberMd,numberSm,numberXs) {

		var windowW = window.innerWidth || $(window).width();

		var slidesToShowXl = (numberXl > 0) ? numberXl : 6;
		var slidesToShowLg = (numberLg > 0) ? numberLg : 4;
		var slidesToShowMd = (numberMd > 0) ? numberMd : numberLg;
		var slidesToShowSm = (numberSm > 0) ? numberSm : numberMd;
		var slidesToShowXs = (numberXs > 0) ? numberXs : 1;
		
		var carousel = carousel;
		var speed = 500;
			
		if (carousel.parent().find('.carousel-products__button').length > 0) {
	
				carousel.slick({
						prevArrow: carousel.parent().find('.carousel-products__button .btn-prev'),
						nextArrow: carousel.parent().find('.carousel-products__button .btn-next'),
						dots: true,
						slidesToShow: slidesToShowXl,
						slidesToScroll: slidesToShowXl,
						responsive: [{
							breakpoint: 1770,
							settings: {
								slidesToShow: slidesToShowLg,
								slidesToScroll: slidesToShowXs
							}
						},{
							breakpoint: 992,
							settings: {
								slidesToShow: slidesToShowMd,
								slidesToScroll: slidesToShowXs
							}
						}, {
							breakpoint: 768,
							settings: {
								slidesToShow: slidesToShowSm,
								slidesToScroll: slidesToShowXs
							}
						}, {
							breakpoint: 480,
							settings: {
								slidesToShow: slidesToShowXs,
								slidesToScroll: slidesToShowXs
							}
						}]
					});
			}
			else {
				carousel.slick({
					slidesToShow: slidesToShowXl,
					slidesToScroll: 1,
					speed: speed,
						responsive: [{
							breakpoint: 1770,
							settings: {
								slidesToShow: slidesToShowLg,
								slidesToScroll: slidesToShowXs
							}
						},{
							breakpoint: 992,
							settings: {
								slidesToShow: slidesToShowMd,
								slidesToScroll: slidesToShowXs
							}
						}, {
							breakpoint: 768,
							settings: {
								slidesToShow: slidesToShowSm,
								slidesToScroll: slidesToShowXs
							}
						}, {
							breakpoint: 480,
							settings: {
								slidesToShow: slidesToShowXs,
								slidesToScroll: slidesToShowXs
							}
						}]
				});
			}		

		fixCarouselHover(carousel);
	};


	// Carousel Product Mobile only		
	function mobileOnlyCarousel() {

		var windowW = window.innerWidth || $(window).width();
	
		var carouselMobileOnly = $('.carousel-products-mobile');
		
		if (windowW < 480) {
			$('.carousel-products-mobile').slick({
				slidesToShow: 1,
				slidesToScroll: 1
			});
		}
		$(window).resize(debouncer(function(e) {
		
			var currentW = window.innerWidth || $(window).width();
			
			if (currentW < 480) {
			  carouselMobileOnly.slick({
				slidesToShow: 1,
				slidesToScroll: 1
			});
			} else {
				if (carouselMobileOnly.hasClass('slick-initialized')){
					carouselMobileOnly.slick('unslick');
				}
			}
		}))
	};

	
	// Category carousel
	
	function bannerCarousel(carousel) {
	    carousel.slick({
	        infinite: true,
	        dots: false,
	        slidesToShow: 3,
	        slidesToScroll: 3,
	        responsive: [{
				breakpoint: 768,
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
	    });
	}	


	// Category carousel		
	function bannerCarouselShort(carousel) {
	    carousel.slick({
	        infinite: true,
	        dots: false,
	        slidesToShow: 3,
	        slidesToScroll: 3,
	        responsive: [{
				breakpoint: 1200,
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
	    });
	}	
	

	// Blog carousel		
	function blogCarousel(carousel,numberXl,numberLg,numberMd,numberSm,numberXs) {

		var windowW = window.innerWidth || $(window).width();

		var slidesToShowXl = (numberXl > 0) ? numberXl : 6;
		var slidesToShowLg = (numberLg > 0) ? numberLg : 4;
		var slidesToShowMd = (numberMd > 0) ? numberMd : numberLg;
		var slidesToShowSm = (numberSm > 0) ? numberSm : numberMd;
		var slidesToShowXs = (numberXs > 0) ? numberXs : 1;
		
		var carousel = carousel;
		var speed = 500;
			
		if (carousel.parent().find('.carousel-products__button').length > 0) {
	
				carousel.slick({
						prevArrow: carousel.parent().find('.carousel-products__button .btn-prev'),
						nextArrow: carousel.parent().find('.carousel-products__button .btn-next'),
						dots: false,
						infinite: true,
						slidesToShow: slidesToShowXl,
						slidesToScroll: slidesToShowXl,
						responsive: [{
							breakpoint: 1770,
							settings: {
								slidesToShow: slidesToShowLg,
								slidesToScroll: slidesToShowLg
							}
						},{
							breakpoint: 992,
							settings: {
								slidesToShow: slidesToShowMd,
								slidesToScroll: slidesToShowMd
							}
						}, {
							breakpoint: 768,
							settings: {
								slidesToShow: slidesToShowSm,
								slidesToScroll: slidesToShowSm
							}
						}, {
							breakpoint: 480,
							settings: {
								slidesToShow: slidesToShowXs,
								slidesToScroll: slidesToShowXs
							}
						}]
					});
			}
			else {
				carousel.slick({
					slidesToShow: slidesToShowXl,
					slidesToScroll: 1,
					speed: speed,
						responsive: [{
							breakpoint: 1770,
							settings: {
								slidesToShow: slidesToShowLg,
								slidesToScroll: slidesToShowLg
							}
						},{
							breakpoint: 992,
							settings: {
								slidesToShow: slidesToShowMd,
								slidesToScroll: slidesToShowMd
							}
						}, {
							breakpoint: 768,
							settings: {
								slidesToShow: slidesToShowSm,
								slidesToScroll: slidesToShowSm
							}
						}, {
							breakpoint: 480,
							settings: {
								slidesToShow: slidesToShowXs,
								slidesToScroll: slidesToShowXs
							}
						}]
				});
			}
		


	};


	// bannerAsid carousel		
	function bannerAsid(carousel) {
	    carousel.slick({
	        infinite: true,
	        dots: true,
	        arrows: false,
	        slidesToShow: 1,
	        slidesToScroll: 1
	    });
	}


	// bannerAsid carousel	
	function testimonialsAsid(carousel) {
	    carousel.slick({
	        infinite: true,
	        dots: true,
	        arrows: false,
	        slidesToShow: 1,
	        slidesToScroll: 1
	    });
	}

	

	// Brands carousel	
	function brandsCarousel(carousel) {
	    carousel.slick({
	        infinite: true,
	        dots: false,
	        slidesToShow: 10,
	        slidesToScroll: 10,
	        responsive: [{
				breakpoint: 1770,
				settings: {
	                slidesToShow: 6,
	                slidesToScroll: 6
					}
				},{
				breakpoint: 1199,
				settings: {
	                slidesToShow: 3,
	                slidesToScroll: 3
					}
				},{
				breakpoint: 768,
				settings: {
	                slidesToShow: 3,
	                slidesToScroll: 3
					}
				}, 
				{
	            breakpoint: 480,
	            settings: {
	                slidesToShow: 2,
	                slidesToScroll: 2
	            }
	        }]
	    });
	}

	
	// Vertical carousel	
	function verticalCarousel(carousel, slidesToShow) {
		var slidesToShow = (slidesToShow > 0) ? slidesToShow : 2;
	    carousel.slick({
	        infinite: false,
	        dots: false,
	        slidesToShow: slidesToShow,
	        slidesToScroll: slidesToShow,
			vertical: true
	    });
	}
	

	// Product thumbnails carousel	
	function thumbnailsCarousel(carousel) {
		carousel.slick({
			infinite: true,
			dots: false,
			slidesToShow: 4,
			slidesToScroll: 1,
			responsive: [{
				breakpoint: 1200,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 1
				}
			},{
				breakpoint: 992,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 1
				}
			}]
		});
	}
	
	// Fix z-index problem on carousel hover	
	function fixCarouselHover(carousel) {
		carousel.find('.slick-slide').bind( "mouseenter mouseleave",
                function( event ){
                    $(this).closest('.slick-slider').toggleClass('hover');
                }
        );			
	};

	
	// Elevate Zoom
	function elevateZoom() {

		var windowW = window.innerWidth || document.documentElement.clientWidth;
		$('.product-zoom').imagesLoaded(function() {
		if ($('.product-zoom').length) {

			   var zoomPosition
				if ( $('html').css('direction').toLowerCase() == 'rtl' ) {
					zoomPosition = 11;					
				}
				else {
					zoomPosition = 1
				}


			if (windowW > 767) {
				$('.product-zoom').elevateZoom({
					//zoomWindowHeight: $('.product-zoom').height(), // if zoom container must be as image height
					zoomWindowWidth: $('.product-zoom').width()- 60,
					zoomWindowHeight: $('.product-zoom').width() - 60,
					gallery: "smallGallery",
					galleryActiveClass: 'active',
					zoomWindowPosition	: zoomPosition
				})

			} else {
				$(".product-zoom").elevateZoom({
					gallery: "smallGallery",
					zoomType: "inner",
					galleryActiveClass: 'active',
					zoomWindowPosition	: zoomPosition
				});
			}
		}
		})
		
		
		$('.product-main-image > .product-main-image__zoom ').bind('click', function(){
		
			
				galleryObj = [];
				current = 0;
				itemN = 0;
				
			if ($('#smallGallery').length){
				console.log('1');
				$('#smallGallery li a').not('.video-link').each(function() {
					if ($(this).hasClass('active')) {
						current = itemN;
					}
					itemN++;
					var src = $(this).data('zoom-image'),
						type = 'image';
						image = {};
						image ["src"] = src;
						image ["type"] = type;

					galleryObj.push(image);
				});
			}
			
			else {
				console.log('2');
					itemN++;
					var src = $(this).parent().find('.product-zoom').data('zoom-image'),
						type = 'image';
						image = {};
						image ["src"] = src;
						image ["type"] = type;

					galleryObj.push(image);
			}

			$.magnificPopup.open({
				items: galleryObj,
				gallery: {
					enabled: true,
				}
			}, current);
			
		});
		
		var  prevW = windowW;


		$(window).resize(debouncer(function(e) {
			var currentW = window.innerWidth || $(window).width();

			if (currentW != prevW) {
				// start resize events

				$('.zoomContainer').remove();
				$('.elevatezoom').removeData('elevateZoom');

				if ($('.product-zoom').length) {
					if (currentW > 767) {
						$('.product-zoom').elevateZoom({
							zoomWindowHeight: $('.product-zoom').height(),
							gallery: "smallGallery"
						})
					} else {
						$(".product-zoom").elevateZoom({
							gallery: "smallGallery",
							zoomType: "inner"
						});
					}
				}					
						
			}

			prevW = window.innerWidth || $(window).width();


		}));
	}

	// Elevate Zoom
	function elevateZoom1() {
		var currentW = window.innerWidth || $(window).width();
		if (currentW > 767) {
			if ($('.bigGallery .product-zoom1').length) {
				 $('.bigGallery .product-zoom1').elevateZoom({
				    zoomType: "inner",
					cursor: "crosshair",
					zoomWindowFadeIn: 300,
					zoomWindowFadeOut: 300
				}); 
			}
		}

	}
	
	// Set Product Size 
	function setProductSize() {
		
	    var windowW = window.innerWidth || $(window).width();
		
	    if (windowW > 767) {
	        $('.product').each(function() {
	            var productH = $(this).outerHeight();
	            $(this).css({
	                'min-height': productH + 'px'
	            });
	            $(this).find('.product__inside').addClass('pos-abs');
	        });
	    }
	
	    $(window).resize(function(e) {
	        $('.product').each(function() {
	            $(this).css({
	                'min-height': ''
	            })
	            $(this).find('.product__inside__info').css({
	                'height': '0'
	            })
	            $(this).find('.product__inside').removeClass('pos-abs');
	        })
			
			var timeout;
			clearTimeout(timeout);
			timeout = setTimeout(function() {
				var currentW = window.innerWidth || $(window).width();
				$('.product').each(function() {
					$(this).find('.product__inside__info').css({
						'height': ''
					});
					if (currentW > 767) {
						var productH = $(this).outerHeight();
						$(this).css({
							'min-height': productH + 'px'
						});
						$(this).find('.product__inside').addClass('pos-abs');
					}
				});
			}, 1000);				
	    })
	};

	
	
	// menu click go URL	
	function navbarClick() {
		
		var windowW = window.innerWidth || $(window).width();
		// mobile menu off width
		if (windowW > 1025) {
			$('.navbar .dropdown > a').on('click', function(){
				location.href = this.href;
				return false
			});
		}
		
	};
	

	// Set Product Page Prev-Next Arrows Position at center of the product image 
	function setProductArrows() {
		var windowW = window.innerWidth || $(window).width();
		var setArrowPos = function(windowW) {
			if (windowW > 1199) {
				var imgH = $('.product-main-image img').height();
				$('#productPrevNext > a').css({'top': imgH/2 + 'px'});
			}
		}
		
		setArrowPos(windowW);
		
		$(window).resize(debouncer(function(e) {
			var currentW = window.innerWidth || $(window).width();
			setArrowPos(currentW);
		}))
	};

	
	// Set Mobile Carousel Arrows Position at center of the product image 
	function setCarouselArrows() {

	    var windowW = window.innerWidth || $(window).width();
		
		var setArrowPos = function(windowW) {
			if (windowW < 480) {
				if ($('.carousel-products-mobile.slick-initialized').length || $('.carousel-products.slick-initialized').length){
					$('.carousel-products-mobile').each(function() {
						var imgH = $(this).find('.slick-list .slick-track .slick-slide:first-child img').height();
						$(this).find('.slick-arrow').css({'top': imgH/2 + 'px'});
					})					
					$('.carousel-products').each(function() {
						if ($(this).parent().parent().find('.carousel-products__button').length > 0) {
							var imgH = $(this).find('.slick-list .slick-track .slick-slide:first-child img').height();
							var titleH = $(this).parent().parent().find('.title-with-button').height();
							$(this).parent().parent().find('.carousel-products__button').css({'top': imgH/2 + titleH + 'px'});
						}
					})
				}
			} else {
				$('.carousel-products').each(function() {
					if ($(this).parent().parent().find('.carousel-products__button').length > 0) {
						$(this).parent().parent().find('.carousel-products__button').css({'top': ''});
					}
					else {
						var imgH = $(this).find('.slick-list .slick-track .slick-slide:first-child img').height();
						$(this).find('.slick-arrow').css({'top': imgH/2 + 'px'});
					}
				})
			}		
		}
		
		setArrowPos(windowW);
		
		$(window).resize(debouncer(function(e) {
			var currentW = window.innerWidth || $(window).width();
			setArrowPos(currentW);
		}))
	};


	// Set mobile dropdowns width	
	function setMobileDrop() {
	
	    var windowW = $('body').innerWidth();
		
		var setDropsW;
		
		setDropsW = function(windowW) {
		// mobile menu off width
			if (windowW < 1025) {
				$('.dropdown-menu--xs-full').each(function() {
					$(this).css({'width': windowW + 'px'});
				})			
			}
			else {
				$('.dropdown-menu--xs-full').each(function() {
					$(this).css({'width': ''});
				})	
			}
		}
				
		setDropsW(windowW);
		
		$(window).resize(debouncer(function(e) {
			var currentW = $('body').innerWidth();
			setDropsW(currentW);
		}))
			
	};
	

	// DropDown Close	
	function handlerDropDownClose() {
	
		$('.dropdown-menu__close').on('click', function(e) {
			e.preventDefault();
			$(this).closest('.dropdown.open .dropdown-toggle').dropdown('toggle');
		});
			
	};
	

	// Search DropDown	
	function searchDropDown() {
	
		$('.search__open').on('click', function(e) {
			e.preventDefault();
			$(this).parent('.search').addClass('open');
			$(this).next('#search-dropdown, .search-dropdown').addClass('open');
			$('header .badge').addClass('badge--hidden');
		});		
		$('.search__close').on('click', function(e) {
			e.preventDefault();
			$(this).closest('.search').removeClass('open');
			$(this).closest('#search-dropdown, .search-dropdown').removeClass('open');
			$('header .badge').removeClass('badge--hidden');
		});
			
	};	
	

	// Mobile footer collapse
	function footerCollapse() {

		$('.mobile-collapse__title').on('click', function(e) {
			e.preventDefault;
			$(this).parent('.mobile-collapse').toggleClass('open');
		})

	};
	
	
	// Product inside carousel
	 function productInsideCarousel() {
	  $(".product__inside__carousel").each(function () {
	            var $this = $(this);
	   $this.carousel({
	    interval: false
	   })
	   $this.find('.carousel-control.next').on('click', function() {
	    $this.carousel('next');
	   });  
	   $this.find('.carousel-control.prev').on('click', function() {
	    $this.carousel('prev');
	   });
	  });
	 };

	
	// Category list collapse	
	function expanderList() {
		$('.expander-list .expander').each(function() {
			if ($(this).parent('li').hasClass('active')){
				$(this).next('ul').slideDown(0);
				$(this).parent().addClass('open');
			}
		})
		$(".expander-list .expander").on('click', function(e) {
			e.preventDefault;
			var speed = 300;
			var thisItem = $(this).parent(),
				nextLevel = $(this).next('ul');
			if (thisItem.hasClass('open')){
				thisItem.removeClass('open');
				nextLevel.slideUp(speed);
			}
			else {
				thisItem.addClass('open');
				nextLevel.slideDown(speed);
			}
		})
	};
	

	// Collapse Block (left column in listing)
	function collapseBlock() {
		$('.collapse-block__content').each(function() {
			if ($(this).parent('.collapse-block').hasClass('open')){
				$(this).slideDown(0);
			}
		})
		$('.collapse-block__title').on('click', function(e) {
			e.preventDefault;
			var speed = 300;
			var thisItem = $(this).parent(),
				nextLevel = $(this).next('.collapse-block__content');
			if (thisItem.hasClass('open')){
				thisItem.removeClass('open');
				nextLevel.slideUp(speed);
			}
			else {
				thisItem.addClass('open');
				nextLevel.slideDown(speed);
			}
		})
	};


	

	// Price Slider initialize
	function priceSlider() {

		if ($('.price-slider').length) {
		
			var priceSlider = document.getElementById('priceSlider');

			noUiSlider.create(priceSlider, {
				start: [100, 900],
				connect: true,
				step: 1,
				range: {
					'min': 0,
					'max': 1000
				}
			});
			
			var inputPriceMax = document.getElementById('priceMax');
			var inputPriceMin = document.getElementById('priceMin');

			priceSlider.noUiSlider.on('update', function( values, handle ) {

				var value = values[handle];

				if ( handle ) {
					inputPriceMax.value = value;
				} else {
					inputPriceMin.value = value;
				}
			});

			inputPriceMax.addEventListener('change', function(){
				priceSlider.noUiSlider.set([null, this.value]);
			});			
			inputPriceMin.addEventListener('change', function(){
				priceSlider.noUiSlider.set([this.value, null]);
			});
		};
	}


	
	// Listing view mode
	function listingModeToggle() {
		if ($('a.link-row-view').length) {
			$('a.link-row-view').on('click', function(e) {
				e.preventDefault();
				$(this).addClass('active');
				$('a.link-grid-view').removeClass('active');
				$('.product-listing').addClass('row-view');
			})
		}
		if ($('a.link-row-view').length) {
		
			$('a.link-grid-view').on('click', function(e) {
				e.preventDefault();
				$(this).addClass('active');
				$('a.link-row-view').removeClass('active');
				$('.product-listing').removeClass('row-view');
			})
		}
	}
	


// back-to-top
function backToTop(){
    if ($(".back-to-top").length > 0) {
        $('.back-to-top').click(function() {
	        $('html, body').animate({scrollTop: 0},500);
	        return false;
	      }) 

	     $(window).scroll(function () {
	        if ( $(window).scrollTop() > 500) {$(".back-to-top").stop(true.false).fadeIn(110)}
	        else {$(".back-to-top").stop(true.false).fadeOut(110)}
	    })
    }	
}


// stuck-nav
var HeaderTop = '';
function stuckNav(){
  if ($(".stuck-nav").length > 0) {
    HeaderTop = $('.header-layout-02').length && window.innerWidth > 1024 ? $('#pageContent').offset().top : $('.stuck-nav').offset().top;
    $(window).scroll(function(){
      checkStickyPosition();
      $('.header-layout-02').length ? stickNav() : false;
    });
    $(window).resize(function(){
      HeaderTop = $('#pageContent').offset().top;
      checkStickyPosition();
      $('.header-layout-02').length ? $( '.stuck-nav' ).length && window.innerWidth <= 1024 ? $( '.stuck-nav' ).show() : stickNav() : false;
    });
    checkStickyPosition();
  }
}
function checkStickyPosition(){
  $(window).scrollTop() > HeaderTop ? $('.stuck-nav').addClass('fixedbar') : $('.stuck-nav').removeClass('fixedbar');
}
function stickNav() {
  if($( '.stuck-nav' ).length && window.innerWidth > 1024) {
    $( window ).scrollTop() > $('#header').innerHeight() ? $( '.stuck-nav' ).show() : $( '.stuck-nav' ).hide();
  }
}


// click on cart(header-layout-06)
jQuery(function($) {

    "use strict";

     if ($(".header-layout-06 ").length > 0) {
     	 $(".header-layout-06 .icon-search").click(function() {
	        $(".header-layout-06 .alignment-extra").toggleClass('width-extra');
	    });
	     $(".header-layout-06 .icon-close").click(function() {
	        $(".header-layout-06 .alignment-extra").toggleClass('width-extra');
	    });
     }

});



// click on cart
jQuery(function($) {

    "use strict";

    $("header .cart").click(function() {
        $("#slider").toggleClass('slider-button');
    });
	
	if ($("html.touch").length > 0) {
		 $(".product .product__inside__image a").click(function(event) {	       
	        event.preventDefault();
	    });	
	}

});



// click on toggle-menu(icon toggle menu)
jQuery(function($) {

    "use strict";

    if ($('.toggle-menu').length) {
	    $(".toggle-menu .icon, .toggle-menu .close").click(function() {
	        $(".toggle-menu .dropdown-menu").fadeToggle(20);        
	    });
	}

});


// Image background
jQuery(function($) {

    "use strict";
	if ($('.image-bg').length) {
		$('.image-bg').each(function() {
			var attr = $(this).attr('data-image');		
			$(this).css({'background-image': 'url('+attr+')'});
		})
	}

});


// show newsletter Modal
jQuery(function($) {

    "use strict";
	if ($('#newsletterModal').length) {
		var pause = $('#newsletterModal').attr('data-pause');
		setTimeout(function() {
			$('#newsletterModal').modal('show');
		}, pause);
	}

})



// Parallax
jQuery(function($) {

    "use strict";
    if ($('.content--parallax, .carusel--parallax').length) {
		$('.content--parallax, .carusel--parallax').each(function() {
			var attr = $(this).attr('data-image');		
			$(this).css({'background-image': 'url('+attr+')'});
		})
	}	
	
});

// Blog carousel
function blogPostSlider(){
	"use strict";
    if ($(".blogPostSlider").length > 0) {
        $('.blogPostSlider').slick({
		  infinite: true,
		  slidesToShow: 1
		});
    }	
}

// Add active class to opened accordion panel
jQuery(function($) {
	
    "use strict";
	
	$('.panel-group')
	  .on('show.bs.collapse', function(e) {
		$(e.target).prev('.panel-heading').addClass('active');
	  })
	  .on('hide.bs.collapse', function(e) {
		$(e.target).prev('.panel-heading').removeClass('active');
	});

});

// Custom select initialized
function selectpicker(){
	"use strict";
    if ($(".selectpicker").length > 0) {
        $('.selectpicker').selectpicker({
            showSubtext: true
        });
        
    }	
}



// desctop menu(popup)
$("nav").each(function(){
  if(!$( this ).hasClass("navbar-vertical")) {
    $( this ).find(".dropdown").each(function(){
      $( this ).hover(
        function() {
   var $this = $( this );
   var $obj = $this.find(".dropdown-menu");
   submenuXposition($obj);
   submenuYposition($obj);
   $( window ).bind( "scroll", { obj: $obj }, menuScroll);

        }, function() {
   var $this = $( this );
   $this.find(".dropdown-menu").removeAttr("style");
   $( window ).unbind( "scroll", menuScroll);
        }
      );
    });
  }
});
function submenuXposition($obj){
 var w_width = window.innerWidth;
 var o_position = $obj.offset().left;
 var o_width = $obj.outerWidth();
 var delta = parseInt(w_width - o_position - o_width - 25);
 
 if(delta < 0) {
  $obj.css("left", delta);
 }
}
function submenuYposition($obj){
 var w_height = window.innerHeight;
 var o_position = $(".stuck-nav").hasClass("fixedbar") ? $obj.position().top : $obj.offset().top;
 var o_height = $obj.outerHeight();
 var delta = parseInt(w_height - o_position - o_height);
 if(delta < 0) {
  $obj.css({"max-height": o_height + delta - 25, "overflow": "auto"});
 }
}
function menuScroll(event) {
 event.data.obj.removeAttr("style");
 submenuXposition(event.data.obj);
 submenuYposition(event.data.obj)
}

if($(".l9-one-product-js").length) {
 l9rectangle();
 $(window).resize(l9rectangle);
}
function l9rectangle() {
 var $obj = $(".l9-one-product-js");
 $obj.find(".row").removeAttr("style");
 setTimeout(function(){
  var w_height = window.innerHeight;
  var y_pos = $obj.offset().top;
  var h_obj = $obj.outerHeight();
  var delta = parseInt(w_height - y_pos - h_obj);
  if(delta > 0) {
   $obj.find(".row").css("padding-bottom", delta);
  }
 }, 100);
}




$(document).ready(function() {
	
	"use strict";
	
	navbarClick();
	countDown();
	setMobileDrop();
	handlerDropDownClose();
	searchDropDown();
	footerCollapse();
	productInsideCarousel();
	expanderList();
	collapseBlock();
	priceSlider();
	compareSlideIni();
	slideColumn();
	backToTop();
	stuckNav();
	blogPostSlider();
	selectpicker();
	cartSlideIni();
	changeInputNameCartPage();
	inputCounter();
	listingModeToggle();

	productCarousel($('#megaMenuCarousel1'),1,1,1,1,1,'productCarousel');
	blogCarousel($('.slider-blog'),1,1,1,1,1,'blogCarousel');

	verticalCarousel($('.vertical-carousel-1'),2,2,2,2,2,'verticalCarousel');
		
	
	// Remove Loader
	$('body').addClass('loaded');
	
	
	
	var timeout1;
	clearTimeout(timeout1);
	timeout1 = setTimeout(function() {
		//setProductSize();
	}, 500);	
	
	var timeout2;
	clearTimeout(timeout2);
	timeout2 = setTimeout(function() {
		// Resize elements	
		setCarouselArrows();
		if ($('#productPrevNext').length) {
			setProductArrows();	
		}
	}, 2000);		

})



// Reinit when resize
$(window).resize(debouncer(function(e) {
	elevateZoom1();
	
}))



if($('.nav-tabs--ys-center').length) {
  initTabsGallery();
 
  $('.nav-tabs--ys-center .active a').trigger('click');
  $(window).resize(function(){
    $('.nav-tabs--ys-center a').unbind();
    initTabsGallery();
  });

}

 function initTabsGallery(){
    $('.nav-tabs--ys-center a').each(function(){
      $(this).click(function(){
        $(this).unbind();
        var tab = $(this).attr("href");
        var clone = tab+"-clone";
        $(tab).empty();
        $(clone).children().clone().appendTo($(tab));
        var $obj = $(tab).find(".carouselTab");
        $obj.css("visibility", "hidden");
        if($obj.length) {
          setTimeout(function(){
            productCarousel($obj,6,4,3,2,1);
            $obj.hide();
            $obj.css("visibility", "visible");
            $obj.fadeIn(500);
          }, 5);
        }
      })
    });
  }


// Listing Gallery
function initListingGalleryEvent() {
  $('.coll-products-js').click(function(){
    $(this).unbind();
    listingGalleryEventHandler();
  });
}
function listingGalleryEventHandler() {
  $('.coll-gallery').empty();
  $('.coll-gallery-clone').children().clone().appendTo('.coll-gallery');
  verticalCarousel($('.coll-gallery .vertical-carousel-2'),2);
};
if($('.coll-products-js').length) {
  if($('.coll-products-js').hasClass('open')) {
    listingGalleryEventHandler();
  } else {
    initListingGalleryEvent();
  }
  $(window).resize(function(){
    $('.coll-products-js').unbind();
    initListingGalleryEvent();
    if($('.coll-products-js').hasClass('open')) {
      listingGalleryEventHandler();
    }
  });
};




// Gallery Popup (gallery-layout.html)

jQuery(function($) {	
    "use strict";
	if ($('.gallery').length) {
		$('.gallery .zomm-gallery').magnificPopup({
			type:'image',
			gallery:{
				enabled:true
			}
			
		});
	}
	
}); 




jQuery(function($) {	
	"use strict";

	var newSelection = "";
	
	$(".filter-nav div").click(function(){
		$("#all-filter-content").hide(0);
	    $("#all-filter-content").fadeIn(500);
	
		$(".filter-nav div").removeClass("current");
		$(this).addClass("current");
		
		newSelection = $(this).attr("rel");
		
		$(".filter-content-item").not("."+newSelection).fadeOut();
		$("."+newSelection).fadeIn();
		
	    $("#all-filter-content").fadeIn(0);
		
	});
	
});


// Gallery (blog-variants.html)
	jQuery(function($) {	
    "use strict";
	if ($('.gallery-isotope').length) {
		$('.gallery-isotope .zomm-gallery').magnificPopup({
			type:'image',
			gallery:{
				enabled:true
			}
			
		});
	}
	
}); 

jQuery(function($) {

    "use strict";
   
    	var gallery = $('.gallery-isotope');
	
	
		gallery.imagesLoaded(function() {
			if (gallery.length) {
				gallery.isotope({
					itemSelector: '.gallery__item',
					isResizeBound: false,
					masonry: {
						isFitWidth: true
					}
				});
				setGallerySize();
			}	
		})		

	    function setGallerySize() {
	        var windowW = window.innerWidth || $(window).width(),
	            itemsInRow = 1;
	        if (windowW > 1199) {
	            itemsInRow = 5;
	        } else if (windowW > 991) {
	            itemsInRow = 4;
	        } else if (windowW > 767) {
	            itemsInRow = 3;
	        } else if (windowW > 480) {
	            itemsInRow = 2;
	        }
	        var containerW = gallery.parent('.container').width(),
	            galleryW = containerW / itemsInRow;
				
			gallery.find('.gallery__item').each(function() {
				if ($(this).hasClass('gallery__item--double') && windowW > 767 ) {
					$(this).css({
						width: galleryW*2 + 'px',
					});
				}
				else {
					$(this).css({
						width: galleryW + 'px'
					});
				}
	        });
			var galleryH = gallery.find('.gallery__item:not(.gallery__item--double)').height();
				gallery.find('.gallery__item').each(function() {
				$(this).css({
						height: ''
				});
				if ($(this).hasClass('gallery__item--double') && windowW > 767) {
					$(this).css({
						height: galleryH*2 + 'px'
					});
				}
	        });
			gallery.isotope('layout');
	    }
		
		
	  
			var prevW = window.innerWidth || $(window).width();
			
			$(window).resize(debouncer(function(e) {
				if (gallery.length) {			
					var currentW = window.innerWidth || $(window).width();
					if (currentW != prevW) {
						// start resize events	
						setGallerySize();
						// end resize events		
					}
					prevW = window.innerWidth || $(window).width();
				}
			}));   
	
})




// logo for retina
jQuery(function($) {	
     if( 'devicePixelRatio' in window && window.devicePixelRatio == 2 ){
		var img_to_replace = jQuery( 'img.replace-2x' ).get();

	    for (var i=0,l=img_to_replace.length; i<l; i++) {
	      var src = img_to_replace[i].src;
	      src = src.replace(/\.(png|jpg|gif)+$/i, '@2x.$1');
	      img_to_replace[i].src = src;
	    };
  	}
	
}); 



// top menu(hover)
	jQuery(function($) {	
    "use strict";
	
	$('.nav.navbar-nav li').hover(function(){
		$(this).addClass('hover');
	},function(){
		$(this).removeClass('hover');
	})
	
}); 




