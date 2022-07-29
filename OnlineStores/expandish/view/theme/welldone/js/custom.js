// WellDone - Responsive HTML5 Template 1.0.4

// Custom scripts



// var $ = jQuery.noConflict();



// Uncommenting these lines if prototype.js is used



// if (Prototype.BrowserFeatures.ElementExtensions) {  

    // var disablePrototypeJS = function (method, pluginsToDisable) {

            // var handler = function (event) {

                // event.target[method] = undefined;

                // setTimeout(function () {

                    // delete event.target[method];

                // }, 0);

            // };

            // pluginsToDisable.each(function (plugin) { 

                // jQuery(window).on(method + '.bs.' + plugin, handler);

            // });

        // },

    // pluginsToDisable = ['collapse', 'dropdown', 'modal', 'tooltip', 'popover', 'tab'];

    // disablePrototypeJS('show', pluginsToDisable);

    // disablePrototypeJS('hide', pluginsToDisable);

// }





// Set Product Size 



function setProductSize(grid,width) {

			productW = width;

            var productCount = 0;

			var marginW = 0;

            if (grid.hasClass('row-view')) {

                var list_width = parseInt(grid.width());

                productW = list_width;

				productCount = 1;

            }

			else {

				var marginW = parseInt(grid.find('.product-preview-wrapper:first-child').css("margin-right"));

				var gridW = parseInt(grid.width());

				while (productW >= width) {

					productCount++;

					productW = gridW / productCount

				}

				if (productCount > 1) productCount = productCount - 1;

			}

            count_visible_items = productCount;

            productW = parseInt(gridW / productCount);

            grid.find('.product-preview-wrapper').css("width", productW - marginW);

			if (!grid.hasClass('row-view')) {

				grid.find('.product-preview').each(function() {

					setProductHeight()

				});

			}

			else {

				grid.find('.product-preview').each(function() {

					$(this).find('.product-preview__image').css({

						'margin-bottom': ''

					})

				});

			}

}

function setProductHeight() {

	$('.product-preview').each(function() {

		var infoH = $(this).find('.product-preview__info').outerHeight();

		$(this).find('.product-preview__image').css({

			'margin-bottom': infoH + 'px'

		})

	});

}



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



//Set product height



jQuery(function($) {



    "use strict";

	

	var windowW = window.innerWidth || $(window).width();



	if (windowW > 767) {

		setProductHeight()

	}

	$('#canvas').on('click',function () {
		$('body').removeClass('modal-open');
	});

});



// MENU HOVER



jQuery(function($) {



  "use strict";

 

  var windowW = window.innerWidth || $(window).width();

   

  function menuIni(windowW) {



	  if (windowW > 768) {

		$('ul.navbar-nav > li').addClass('hovernav');

		$('ul.navbar-nav > li.hovernav').hover(

			function(){

				$(this).addClass("open")

			},

			function(){

				$(this).removeClass("open")

			}

		);

		$('ul.navbar-nav > li li').hover(

			function(){

			  if ($(this).children('ul.level-menu__dropdown').length) {

				$(this).addClass('active');

				$(this).children('ul.level-menu__dropdown').show().css({'opacity' : 0, 'left' : $(this).width()})

				var off = $(this).children('ul.level-menu__dropdown').offset();

				var l = off.left;

				var w = $(this).children('ul.level-menu__dropdown').width();

				var docW = $(".container").width();

				var isEntirelyVisible = (l + w <= docW);

							

				if (!isEntirelyVisible) {

					$(this).children('ul.level-menu__dropdown').show().css({'opacity' : 1, 'right' : $(this).width(), 'left' : 'auto'})

				} else {

					$(this).children('ul.level-menu__dropdown').show().css({'opacity' : 1, 'left' : $(this).width()})

				}

					  

			  }

			},

			function(){

			  if ($(this).children('ul.level-menu__dropdown').length) {

				$(this).removeClass('active');

				$(this).children('ul.level-menu__dropdown').hide().css({'left':'auto', 'right':'auto'})

			  }

			}

	  );

	  } else {

		$('ul.navbar-nav > li').removeClass('hovernav');

		$('ul.navbar-nav > li li').unbind('mouseenter mouseleave');

		$('.touch ul.navbar-nav > li > a').click(function( e ) {

			e.preventDefault();

		})

		$('.touch ul.navbar-nav > li a span.caret').click(function() {

		  $(this).parent().parent('li').toggleClass('open');

		});

		$('.touch ul.navbar-nav > li a span.link-name').click(function() {

		  var  url = $(this).parent('a').attr("href");
		  if(!url)
			return;
		  window.location = url;

		});

		

	  };

  

  }

  

  menuIni(windowW);

  

  $('.no-touch .hovernav a').click(function () {

    window.location = this.href;

  });

  

  var   prevW = windowW;

    

	$(window).resize(debouncer(function(e) {



			var currentW = window.innerWidth || $(window).width();

			if (currentW != prevW) {

				// start resize events					

					menuIni(currentW);

				// end resize events		

			}

			prevW = window.innerWidth || $(window).width();

	}));

		

  

});



// Slide menu small Header



jQuery(function($) {



    "use strict";



    $("#openSlidemenu").click(function() {

        $(".header--small #slidemenu").slideToggle(250, function() {

			$(".header--small #slidemenu").toggleClass('open')

		});

        return false;

    });

	

	var prevW = window.innerWidth || $(window).width();



    $(window).resize(debouncer(function(e) {

			var currentW = window.innerWidth || $(window).width();

			if (currentW != prevW) {

				// start resize events	
				if (currentW < 767) {

					$(".header--small #slidemenu").show().removeClass('open');

				}

				else {

					$(".header--small #slidemenu").hide()

				}

				// end resize events		

			}

			prevW = window.innerWidth || $(window).width();

    }));



});





// Price Slider initialize



jQuery(function($) {



    "use strict";

	

	if ($('.price-slider').length) {

	

	var priceSlider = document.getElementById('priceSlider');



	noUiSlider.create(priceSlider, {

		start: [200, 800],

		connect: true,

		step: 1,

		range: {

			'min': 0,

			'max': 1000

		}

	});

	var tipHandles = priceSlider.getElementsByClassName('noUi-handle'),

		tooltips = [];



	// Add divs to the slider handles.

	for ( var i = 0; i < tipHandles.length; i++ ){

		tooltips[i] = document.createElement('div');

		tipHandles[i].appendChild(tooltips[i]);

	}



	tooltips[0].className += 'tooltip top';

	tooltips[0].innerHTML = '<div class="tooltip-inner"><span></span><div class="tooltip-arrow"></div></div>';

	tooltips[0] = tooltips[0].getElementsByTagName('span')[0];

	tooltips[1].className += 'tooltip top';

	tooltips[1].innerHTML = '<div class="tooltip-inner"><span></span><div class="tooltip-arrow"></div></div>';

	tooltips[1] = tooltips[1].getElementsByTagName('span')[0];



	// When the slider changes, write the value to the tooltips.

	priceSlider.noUiSlider.on('update', function( values, handle ){

		tooltips[handle].innerHTML = Math.round(values[handle]);

	});

	}



});



// Back to top button



jQuery(function($) {



    "use strict";



	var windowH = $(window).height();

	var backPos;

	if ($("footer .back-to-top").length) {

		var backPos = $("footer .back-to-top").offset();

		if (backPos.top < windowH) {

			$("footer .back-to-top").hide();

		}	

	}



    $("a[href='#top']").click(function() {

        $("html, body").animate({

            scrollTop: 0

        }, "slow");

        return false;

    });



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





// If Header line not exists



jQuery(function($) {



    "use strict";

    if (!$('.header-line').length) {

        //$('.header').addClass('header--no-line');

    }



});



// Categories menu mobile



jQuery(function($) {



    "use strict";

	

	var windowW = window.innerWidth || $(window).width();



	$("#categoriesMenu").click(function() {

		$(".navbar-nav--vertical").slideToggle(250, function() {

			$("#categoriesMenu").toggleClass('open');

			$(".navbar-nav--vertical").toggleClass('open')

		});

		return false;

	});	

	

})



// Slide menu mobile



jQuery(function($) {



    "use strict";



    var windowW = window.innerWidth || $(window).width();



    if (windowW < 768) {

        $('#slidemenu').css({

            'height': $(window).height()

        });

    }

    var toggler = '.navbar-toggle';

    var pagewrapper = '#pageContent';

    var footer = '.footer';

    var navigationwrapper = '.navbar-header';

    var menuwidth = '100%';

    var slidewidth = '80%';

    var menuneg = '-100%';



	$("#slidemenu .slidemenu-close").on("click", function(e) {

	

		$('body').removeClass('modal-open');

		

		if ( $('html').css('direction').toLowerCase() == 'rtl' ) {

			$('#slidemenu').stop().animate({

				right: menuneg

			});

		} else {

			$('#slidemenu').stop().animate({

				left: menuneg

			});

		}		

    });

	

    $("#navbar").on("click", toggler, function(e) {

	

		$('body').addClass('modal-open');



        var selected = $(this).hasClass('slide-active');



		

		if ( $('html').css('direction').toLowerCase() == 'rtl' ) {

			$('#slidemenu').stop().show().animate({

				right: selected ? menuneg : '0px'

			});

		} else {

			$('#slidemenu').stop().show().animate({

				left: selected ? menuneg : '0px'

			});

		}	



    });



    var windowW = window.innerWidth || $(window).width();



    var   prevW = windowW;





    $(window).resize(function() {

        var currentW = window.innerWidth || $(window).width();



        if (currentW != prevW) {

            // start resize events

        if (currentW > 767) {

            $('#slidemenu').css({

                'height': ''

            });

			$('body').removeClass('modal-open');

			$('#slidemenu').stop().animate({

				left: menuneg

			});

        } else {

            $('#slidemenu').css({

                'height': $(window).height()

            });

        }			

			

            // end resize events		

        }

        prevW = window.innerWidth || $(window).width();





    });

});





// Listing page - mobile version - Filters slide



jQuery(function($) {



    "use strict";



    var windowW = window.innerWidth || $(window).width();



    if (windowW < 768) {

        $('#filtersCol').css({

            'height': $(window).height()

        });

    }



    var filterneg = '-100%';



    $("#showFilterMobile").click(function() {



        var active = $('#filtersCol').hasClass('filter-active');

		

		$('body').toggleClass('modal-open');



        $('#filtersCol').stop().animate({

            right: active ? filterneg : '0px'

        });



        $('#filtersCol').toggleClass('filter-active');



    });

	

	$("#filtersColClose").click(function() {



        $('#filtersCol').stop().animate({

            right: filterneg

        });

		

		$('body').removeClass('modal-open');

		

        $('#filtersCol').toggleClass('filter-active');



    });

	



});





// Custom select initialized



jQuery(function($) {



    "use strict";

    if ($('.selectpicker').length) {

        $('.selectpicker').selectpicker({

            showSubtext: true

        });

    }

})



// Without zoom previews switcher



jQuery(function($) {

			



	if (!$('.product-zoom').length) {

	

		$('#mainProductImg').css({'min-height': $('#mainProductImg img').height(), 'min-width': $('#mainProductImg img').width() })		

			



		$('#smallGallery a').click(function(e){

			e.preventDefault();

			$('#smallGallery a').removeClass('active');

			$(this).addClass('active');

			var targ = $(this).parent('li').index();

			var curImg = $('#mainProductImg').find('div.product-main-image__item.active');

			var cur = curImg.index();

			if (targ == cur) {

				return false;

			}

			else {

				var newImg = $('#mainProductImg').find('div.product-main-image__item:nth-child('+ (targ+1) +')');

				curImg.removeClass('active');

				newImg.addClass('active')

			}

		})

		

	}



	var prevW = window.innerWidth || $(window).width();

		

		$(window).resize(debouncer(function(e) {

	

				var currentW = window.innerWidth || $(window).width();

				if (currentW != prevW) {

					// start resize events						

					if (!$('.product-zoom').length) {

	

						$('#mainProductImg').css({'min-height': '', 'min-width': '' })	

						$('#mainProductImg').css({'min-height': $('#mainProductImg img').height(), 'min-width': $('#mainProductImg img').width() })		



					}		

					// end resize events		

				}

				prevW = window.innerWidth || $(window).width();

		}));

		

})



// Magnific Popup on Product Image click



jQuery(function($) {



	if ($('#mainProductImg .zoom-link').length) {

		$('#mainProductImg').magnificPopup({

          disableOn: 767,

		  delegate: '.zoom-link',

          type: 'image',

          mainClass: 'mfp-fade',

          preloader: true,

          fixedContentPos: false,

		  gallery: {

            enabled: true,

            navigateByImgClick: true,

            preload: [0,1] // Will preload 0 - before current, and 1 after the current image

          }

        });

	}

})



// Elevate Zoom



jQuery(function($) {



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

                zoomWindowHeight: $('.product-zoom').height(),

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

			

			

            // end resize events		

        }





        prevW = window.innerWidth || $(window).width();





    }));

})



// Show rev slider when page loaded



jQuery(function($) {



    "use strict";



    if ($(".tp-banner-container").length) {



        $(".tp-banner-container").css({

            height: 'auto'

        }).animate({

            "opacity": "1"

        }, 500);



    }



});



jQuery(function($) {



    "use strict";



    $(".scroll-to-content").click(function() {

        $('html, body').animate({

            scrollTop: $(window).height() - $('header .navbar').height() 

        }, 500);

        $(".scroll-to-content").hide(200);

    });



});



jQuery(function($) {



    "use strict";

	

	var slideIndex = 0;

	

	function addToCompare(button) {

		var image = button.closest('.product-preview').find('.product-preview__image img').attr('src');

		var link = button.closest('.product-preview').find('.product-preview__image a').attr('href');

		var list = $('.compare-box__items__list')

		slideIndex++;

		var addRow = '<div class="compare-box__items__list__item__delete"><a href="'+link+'" class="icon icon-clear"></a></div><a href="#"><img src="'+image+'" alt=""/></a>';

		if (list.find('.empty').length){

			list.find('.empty:first').html(addRow).removeClass('empty');

		}

	}



	function removeAllFromCompare() {

		var item = $('.compare-box__items__list__item');

		var list = $('.compare-box__items__list');

		var addEmptyRow = '<li class="compare-box__items__list__item empty"><div class="compare-box__items__list__item__num"></div><img src="./expandish/view/theme/new2/images/products/product-empty.png" alt=""/></li>';

		item.each(function() {

			if (!$(this).hasClass('empty')) {

				$(this).remove()

				list.append(addEmptyRow);

			}

		})

	}

	

	function removeFromCompare(button) {

		var item = button.parent('.compare-box__items__list__item');

		var list = $('.compare-box__items__list');

		item.remove();

		var addEmptyRow = '<li class="compare-box__items__list__item empty"><div class="compare-box__items__list__item__num"></div><img src="./expandish/view/theme/new2/images/products/product-empty.png" alt=""/></li>';

		list.append(addEmptyRow);

		var empty = 0;

		list.find('li').each(function() {

			var thisCount = $(this).index() + 1;

			$(this).find('.compare-box__items__list__item__num').text(thisCount);

			if (!$(this).hasClass('empty')) {

				empty = 1;

			}

		})

		if (empty == 0) {

			$('#compareBox').stop(true, false).removeClass('minimize').removeClass('open');

			$('body').removeClass('compare-minimize');			

		}

	}

	

    $(document).on('click','.compare-box__items__list__item__delete', function(e) {

        e.preventDefault();

		removeFromCompare($(this));

    });



    $(document).on('click','#removeAllCompare', function(e) {

        e.preventDefault();

		removeAllFromCompare($(this));

		$('#compareBox').stop(true, false).removeClass('minimize').removeClass('open');

		$('body').removeClass('compare-minimize');

    });

	

		$('.compare-link').click(function(e) {

			e.preventDefault();

			addToCompare($(this))

			$('#compareBox').stop(true, false).removeClass('minimize').addClass('open');

			$('body').removeClass('compare-minimize');

		});



		$('.hide-compare').click(function(e) {

			e.preventDefault();

			$('#compareBox').stop(true, false).removeClass('open').addClass('minimize');

			$('body').addClass('compare-minimize');

		});



		$('.show-compare').click(function(e) {

			e.preventDefault();

			$('#compareBox').stop(true, false).removeClass('minimize').addClass('open');

			$('body').removeClass('compare-minimize');

		});

		

		$('.close-compare').click(function(e) {

			e.preventDefault();

			$('#compareBox').stop(true, false).removeClass('minimize').removeClass('open');

			$('body').removeClass('compare-minimize');

		});

		

});



// Sticky Header



jQuery(function($) {



    "use strict";

		

    if ($('header').length) {

		if ($(document).scrollTop() > 150) {

			$('header .navbar').addClass('stuck--smaller');

		}



		if ($('header.header--max.header--sticky').length) {

			var sticky = new Waypoint.Sticky({

				element: $('header #slidemenu')[0],

				offset: -1

			})

		}

		else {

			if($('header.header--sticky .navbar').length) {

        var $toSticky = $('.header__dropdowns-container');

				var sticky = new Waypoint.Sticky({

					element: $('header .navbar')[0],

					offset: -1,

          handler: function(direction) {  

            if (direction == 'down'){

               var $toStickyCopy = $toSticky.detach();

               $toStickyCopy.appendTo("#navbar");            

            } else {

              var $toStickyCopy = $toSticky.detach();

               $toStickyCopy.insertBefore(".sticky-wrapper");            

            }

          }

				})

			}

		}

    };       

	

	$("body").on("touchend", function(){

		if ($(document).scrollTop() > 150) {

			if(!$('header .navbar').hasClass('stuck--smaller')) {

				setTimeout(function() {

					$('header .navbar').addClass('stuck--smaller');

				}, 300);

			}

		} else {

			$('header .navbar').removeClass('stuck--smaller');

		}

	});



	

	var waypoints = $('.no-touch header .navbar').waypoint(function(direction) {

        if (direction === 'down') {

			$('.no-touch header .navbar').addClass('stuck--smaller');

        }

    }, {

        offset: -350

    })

	var waypoints = $('.no-touch header .navbar').waypoint(function(direction) {

        if (direction === 'up') {

			$('.no-touch header .navbar').removeClass('stuck--smaller');

        }

    }, {

        offset: -350

    })

	

	var prevW = window.innerWidth || $(window).width();



	$(window).resize(function() {

        var currentW = window.innerWidth || $(window).width();



        if (currentW != prevW) {

            // start resize events

			if ($(document).scrollTop() > 50) {

				$('header .navbar').addClass('stuck--smaller');

			}        

            // end resize events

        }



        prevW = window.innerWidth || $(window).width();



    });

	

	

});



// Product sticky navigation



jQuery(function($) {

	

    "use strict";

	

	var windowW = window.innerWidth || $(window).width();

	

	if (windowW > 767){

		if ($('.nav-product').length){

			$('.nav-product ul li a').click(function(e) {

				e.preventDefault();

				var targ = $(this).attr('href');

				var posTop = $(targ).offset().top - $('.nav-product ul').outerHeight();

				$('body,html').animate({

					'scrollTop': posTop

				});

			});

			var waypoints = $('.product-additional__box').waypoint(function(direction) {

				if (direction === 'down') {

					var panel;

					panel = this.element.id;

					

					$('.nav-product ul li.active').removeClass('active');

					$(".nav-product ul li[data-target ='"+panel+"']").addClass('active');

				}

			}, {

				offset: $('.nav-product').outerHeight() + 1

			})

			var waypoints = $('.product-additional__box').waypoint(function(direction) {

				if (direction === 'up') {

					var panel;

					panel = this.element.id;

					$('.nav-product ul li.active').removeClass('active');

					$('.nav-product ul li[data-target ="'+panel+'"]').addClass('active');

				}

			}, {

				offset: -1

			})

			var stickyProduct = new Waypoint.Sticky({

				element: $('.nav-product')[0]

			})

			if ($('header.header--sticky').length) {

				var waypointsH = $('#navProduct').waypoint(function(direction) {

					if (direction === 'down') {

						$('header .navbar').css({'opacity': 0, 'top': '-100%'});

					}

				}, {

					offset: 50

				})

				var waypointsH = $('#navProduct').waypoint(function(direction) {

					if (direction === 'up') {

						$('header .navbar').css({'opacity': 1, 'top': ''});

					}

				}, {

					offset: 50

				})

			}

		}

	}

});







// Wave effect



jQuery(function($) {



    "use strict";



    Waves.attach('button.wave');

    Waves.attach('.wave-block');

    Waves.init();



});



// Mobile footer collapse



jQuery(function($) {



    "use strict";





    $('.mobile-collapse__title').click(function(e) {

        e.preventDefault;

        $(this).parent('.mobile-collapse').toggleClass('open');

    })





});



// Filter collapse



jQuery(function($) {



    "use strict";





    $('.filters-col__collapse__title').click(function(e) {

        e.preventDefault;

        $(this).parent('.filters-col__collapse').toggleClass('open');

    })





});



// Box collapse - shopping cart



jQuery(function($) {



    "use strict";





    $('.card--collapse .card-title').click(function(e) {

        e.preventDefault;

        $(this).parent('.card').toggleClass('open');

    })





});



jQuery(function($) {



    "use strict";



	// Product carousel Start	

    $('.product-carousel.three-in-row').slick({

        infinite: true,

        dots: false,

        lazyLoad: 'ondemand',

        slidesToShow: 3,

        slidesToScroll: 3,

        responsive: [{

            breakpoint: 992,

            settings: {

                slidesToShow: 3,

                slidesToScroll: 3

            }

        }, {

            breakpoint: 768,

            settings: {

                slidesToShow: 2,

                slidesToScroll: 2

            }

        }, {

            breakpoint: 560,

            settings: {

                slidesToShow: 1,

                slidesToScroll: 1

            }

        }, {

            breakpoint: 321,

            settings: {

                slidesToShow: 1,

                slidesToScroll: 1,

                centerMode: true,

                centerPadding: '20px'

            }

        }]

    });



    $('.product-carousel.four-in-row').slick({

        infinite: true,

        dots: false,

        lazyLoad: 'ondemand',

        slidesToShow: 4,

        slidesToScroll: 4,

        responsive: [{

            breakpoint: 992,

            settings: {

                slidesToShow: 3,

                slidesToScroll: 3

            }

        }, {

            breakpoint: 768,

            settings: {

                slidesToShow: 2,

                slidesToScroll: 2

            }

        }, {

            breakpoint: 560,

            settings: {

                slidesToShow: 1,

                slidesToScroll: 1

            }

        }, {

            breakpoint: 321,

            settings: {

                slidesToShow: 1,

                slidesToScroll: 1,

                centerMode: true,

                centerPadding: '20px'

            }

        }]

    });

	

	$('.product-carousel.five-in-row').slick({

        infinite: true,

        dots: false,

        lazyLoad: 'ondemand',

        slidesToShow: 5,

        slidesToScroll: 5,

        responsive: [{

            breakpoint: 992,

            settings: {

                slidesToShow: 4,

                slidesToScroll: 4

            }

        }, {

            breakpoint: 768,

            settings: {

                slidesToShow: 2,

                slidesToScroll: 2

            }

        }, {

            breakpoint: 560,

            settings: {

                slidesToShow: 1,

                slidesToScroll: 1

            }

        }, {

            breakpoint: 321,

            settings: {

                slidesToShow: 1,

                slidesToScroll: 1,

                centerMode: true,

                centerPadding: '20px'

            }

        }]

    });

	// Product carousel End	

	

	// Category carousel Start	

    $('.product-category-carousel').slick({

        infinite: true,
        dots: false,
        slidesToShow: 5,
        slidesToScroll: 5,
        responsive: [{
            breakpoint: 992,
            settings: {

                slidesToShow: 4,
                slidesToScroll: 4
            }
        }, {

            breakpoint: 768,
            settings: {
                slidesToShow: 3,
                slidesToScroll: 3
            }
        }, {
            breakpoint: 560,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 2
            }
        }, {
            breakpoint: 480,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 1,
                centerMode: true,
                centerPadding: '40px'
            }

        }]

    });

	// Category carousel End		

	

	// Category carousel Start	

    $('.product-category-carousel-aside').slick({

        infinite: true,

        dots: false,

        slidesToShow: 4,

        slidesToScroll: 4,

        responsive: [{

            breakpoint: 992,

            settings: {

                slidesToShow: 3,

                slidesToScroll: 3

            }

        }, {

            breakpoint: 768,

            settings: {

                slidesToShow: 2,

                slidesToScroll: 2

            }

        }, {

            breakpoint: 560,

            settings: {

                slidesToShow: 2,

                slidesToScroll: 2

            }

        }, {

            breakpoint: 480,

            settings: {

                slidesToShow: 1,

                slidesToScroll: 1,

                centerMode: true,

                centerPadding: '40px'

            }

        }]

    });

	// Category carousel End	

	

	// Brand carousel Start	

	if ($('.brands-carousel').closest('.aside-column').length) {

		$('.brands-carousel').slick({

			infinite: true,

			dots: false,

			lazyLoad: 'ondemand',

			slidesToShow: 5,

			slidesToScroll: 5,

			responsive: [{

				breakpoint: 992,

				settings: {

					slidesToShow: 4,

					slidesToScroll: 4

				}

			}, {

				breakpoint: 768,

				settings: {

					slidesToShow: 3,

					slidesToScroll: 3

				}

			}, {

				breakpoint: 560,

				settings: {

					slidesToShow: 2,

					slidesToScroll: 2

				}

			}, {

				breakpoint: 480,

				settings: {

					slidesToShow: 1,

					slidesToScroll: 1,

					centerMode: true,

					centerPadding: '50px'

				}

			}]

		});

	}

	else {

		$('.brands-carousel').slick({

			infinite: true,

			dots: false,

			lazyLoad: 'ondemand',

			slidesToShow: 7,

			slidesToScroll: 7,

			responsive: [{

				breakpoint: 992,

				settings: {

					slidesToShow: 4,

					slidesToScroll: 4

				}

			}, {

				breakpoint: 768,

				settings: {

					slidesToShow: 3,

					slidesToScroll: 3

				}

			}, {

				breakpoint: 560,

				settings: {

					slidesToShow: 2,

					slidesToScroll: 2

				}

			}, {

				breakpoint: 480,

				settings: {

					slidesToShow: 1,

					slidesToScroll: 1,

					centerMode: true,

					centerPadding: '50px'

				}

			}]

		});

	}

	// Brand carousel End	

	

	// Blog carousel Start	

	var blogCarousel = $('.blog-carousel');

	

	if (blogCarousel.hasClass('show-2')) {

		blogCarousel.slick({

			infinite: true,

			dots: false,

			slidesToShow: 2,

			slidesToScroll: 2,

			responsive: [{

				breakpoint: 768,

				settings: {

					dots: true

				}

			}, {

				breakpoint: 480,

				settings: {

					slidesToShow: 1,

					slidesToScroll: 1

				}

			}]

		}); 

	}

	else if (blogCarousel.hasClass('show-3')) {

		blogCarousel.slick({

			infinite: true,

			dots: false,

			slidesToShow: 3,

			slidesToScroll: 2,

			responsive: [{

				breakpoint: 992,

				settings: {

					slidesToShow: 2,

					slidesToScroll: 2,

					dots: true

				}

			}, {

				breakpoint: 480,

				settings: {

					slidesToShow: 1,

					slidesToScroll: 1

				}

			}]

		}); 

	}	

	else if (blogCarousel.hasClass('show-4')) {

		blogCarousel.slick({

			infinite: true,

			dots: false,

			slidesToShow: 4,

			slidesToScroll: 2,

			responsive: [{

				breakpoint: 1200,

				settings: {

					slidesToShow: 3,

					slidesToScroll: 2,

					dots: true

				}

			},{

				breakpoint: 992,

				settings: {

					slidesToShow: 2,

					slidesToScroll: 2,

					dots: true

				}

			}, {

				breakpoint: 480,

				settings: {

					slidesToShow: 1,

					slidesToScroll: 1

				}

			}]

		}); 

	}

	else {

		blogCarousel.slick({

			infinite: true,

			dots: false,

			slidesToShow: 1,

			slidesToScroll: 1,

			responsive: [{

				breakpoint: 768,

				settings: {

					dots: true

				}

			}]

		});

	}

	// Blog carousel End

	

	// Testimonials carousel Start

    $('.testimonials-carousel').slick({

        infinite: true,

        slidesToShow: 1,

        slidesToScroll: 1,

		arrows: false,

		dots: true

    });

	// Testimonials carousel End

	

	// Vertical products carousel Start

	

	if ($('.products-widget-carousel').closest('.filters-col__collapse').length){



		var state = 0;

				

		if (!$('.products-widget-carousel').closest('.filters-col__collapse').hasClass('open')) {

			var state = 1;

			$('.products-widget-carousel').closest('.filters-col__collapse').addClass('open');

		}

		$('.products-widget-carousel').on('init', function(event, slick){

			if (state == 1) {

			setTimeout(function() {

				$('.products-widget-carousel').closest('.filters-col__collapse').removeClass('open');

			}, 1000);

			}

		});

		$('.products-widget-carousel').slick({

			vertical: true,

			infinite: true,

			slidesToShow: 2,

			slidesToScroll: 2,

			verticalSwiping: true,

			arrows: false,

			dots: true

		});

		

	}

	else {

		$('.products-widget-carousel').slick({

			vertical: true,

			infinite: true,

			slidesToShow: 3,

			slidesToScroll: 3,

			verticalSwiping: true,

			arrows: false,

			dots: true,

			responsive: [{

				breakpoint: 992,

				settings: {

					slidesToShow: 3,

					slidesToScroll: 3

				}

			}]

		});

	}

	// Vertical products carousel End

	

	// Product thumbnails carousel Start	

    $('.product-images-carousel ul').slick({

        infinite: false,

        dots: false,

        slidesToShow: 5,

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

	// Product thumbnails carousel End	

	

	// Product mobile slider Start	

    $('#singleGallery').slick({

        infinite: false,

        dots: true,

        arrows: false,

        slidesToShow: 1,

        slidesToScroll: 1

    });

	// Product mobile slider End



	// Product vertical product slider Start

	if ($('#singleGalleryVertical').length) {

	$('#singleGalleryVertical').on('init', function(event, slick){

		$('#singleGalleryVertical').css({'opacity': 1})

	});

    $('#singleGalleryVertical').css({'opacity': 0}).slick({

        infinite: false,

		vertical: true,

		verticalScrolling: true,

        dots: true,

        arrows: false,

        slidesToShow: 1,

        slidesToScroll: 1

    }).mousewheel(function(e) {

		e.preventDefault();



		if (e.deltaY < 0) {

			$(this).slick('slickNext')

		}

		else {

			$(this).slick('slickPrev')

		}

	});

	}

	// Product vertical product slider End	

	

	// Simple slider Start



	window.setTimeout(function() {

        $('.single-slider').css({

			'opacity': '1'

		})

    },500);

	

	$('.single-slider > ul').slick({

        infinite: false,

        dots: false,

        arrows: true,

        slidesToShow: 1,

        slidesToScroll: 1,

		responsive: [{

            breakpoint: 768,

            settings: {

                arrows: false,

                dots: true

            }

        }]

    });

	// Simple slider End



});





// Quick view



jQuery(function($) {



    "use strict";

	

	$('#quickView').on('hidden.bs.modal', function () {

	  $(this).removeData('bs.modal').find('.modal-content').empty();;

	});

	

    $('.btn-quickview').click(function(e) {

		$('#modalLoader-wrapper').show();

        e.preventDefault();

        var $this = $(this),

            url = $this.attr("href");

        $.ajax({

            url: url,

            cache: false,

            success: function(data) {

                var $data = $(data);

                $('#quickview .modal-content').empty().append($data);

            },

            complete: function() {

                setTimeout(function() {

				$('#modalLoader-wrapper').fadeOut();

                    $('.product-images-carousel ul').on('init', function(e) {

                            $('.product-images-carousel').addClass('loaded');

                        })

                        .slick({

                            infinite: false,

                            dots: false,

                            slidesToShow: 5,

                            slidesToScroll: 1

                        });

                }, 1000);

            },

            error: function(jqXHR, textStatus, errorThrown) {

                return false;

            }

        })

    });

});



// Parallax



jQuery(function($) {



    "use strict";

	if ($('.content--parallax').length) {

		$('.content--parallax').each(function() {

			var attr = $(this).attr('data-image');		

			$(this).css({'background-image': 'url('+attr+')'}).parallax("50%", 0.01);

		})

	}

	

	if ($('.parallax').length) {

		$('.parallax').each(function() {

			var attr = $(this).attr('data-image');		

			$(this).css({'background-image': 'url('+attr+')'}).parallax("50%", 0);

		})

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



jQuery(function($) {



    "use strict";



    $('#productOther > li').hover(function() {

        $(this).toggleClass('show-image');

    });



});



jQuery(function($) {



    "use strict";

	

    $('.header__search input').focus(function() {



        $('.search-focus-fade').stop().animate({

            "opacity": "0"

        }, 200);



    }).blur(function() {



        $('.search-focus-fade').stop().animate({

            "opacity": "1"

        }, 200);



    });



})





jQuery(function($) {



    "use strict";



    function onScrollInit(items, container) {

        items.each(function() {

            var element = $(this),

                animationClass = element.attr('data-animation'),

                animationDelay = element.attr('data-animation-delay');



            element.css({

                '-webkit-animation-delay': animationDelay,

                '-moz-animation-delay': animationDelay,

                'animation-delay': animationDelay

            });



            var trigger = (container) ? container : element;



            trigger.waypoint(function() {

                element.addClass('animated').addClass(animationClass);

            }, {

                triggerOnce: true,

                offset: '90%'

            });

        });

    }



    onScrollInit($('.animation'));

    onScrollInit($('.staggered-animation'), $('.staggered-animation-container'));

});



// Countdown ini



jQuery(function ($) {



	if ($("#countdown1").length > 0){$('#countdown1').countdown({until: new Date(2016, 02, 1)});}



});





//Isotope



jQuery(function($) {



    "use strict";

	

	var windowW = window.innerWidth || $(window).width();

	

	if ($('.products-isotope').length){

		$('.products-isotope').imagesLoaded(function() {

			

				$('.products-isotope').isotope({

				  itemSelector: '.product-preview-wrapper',

				  layoutMode: 'fitRows'

				})

		

		});	

	}

	

	

	var minW =  parseInt($('.products-col').find('.product-preview-wrapper:first-child').width());

	if ($('.products-isotope').hasClass('two-in-row')){

			minW = minW-200;

	}

	

	if ($('.products-col').parent().parent().hasClass('open')) {

		

		if ( $('html').css('direction').toLowerCase() == 'rtl' ) {

			$('.products-col').stop(true,false).animate({marginRight: '280px'}, 200, 

				function() { 

					setProductSize($('.products-col'),minW);

					$('.products-isotope').isotope().isotope('layout');

			});

		

		}

		else {

			$('.products-col').stop(true,false).animate({marginLeft: '280px'}, 200, 

				function() { 

					setProductSize($('.products-col'),minW);

					$('.products-isotope').isotope().isotope('layout');

			});

		}

	}



	

	

	$('#showFilter').click(function(e) {

        e.preventDefault();

		

		var minW =  parseInt($('.products-col').find('.product-preview-wrapper:first-child').width());

		if ($('.products-isotope').hasClass('two-in-row')){

				minW = minW-200;

		}

		if (!$('.products-col').parent().parent().hasClass('open')) {

			$('.products-col').parent().parent().addClass('open');

			if ( $('html').css('direction').toLowerCase() == 'rtl' ) {

				$('.products-col').stop(true,false).animate({marginRight: '280px'}, 200, 

					function() { 

						setProductSize($('.products-col'),minW);

						$('.products-isotope').isotope().isotope('layout');

				});

			

			}

			else {

				$('.products-col').stop(true,false).animate({marginLeft: '280px'}, 200, 

					function() { 

						setProductSize($('.products-col'),minW);

						$('.products-isotope').isotope().isotope('layout');

				});

			}

		}

		else {

			$('.products-col').parent().parent().removeClass('open');



			$('.products-col').stop(true,false).animate({marginLeft: '0', marginRight: '0'}, 200, 

				function() {

					

					if ($('.products-isotope').hasClass('two-in-row')){

						if (windowW > 560) {

							minW = $('.products-isotope').width()/2;

						} else {

							minW = $('.products-isotope').width()/1;

						}

					} else if ($('.products-isotope').hasClass('three-in-row')){

						if (windowW > 560) {

							minW = $('.products-isotope').width()/3;

						} else {

							minW = $('.products-isotope').width()/1;

						}

					} else if ($('.products-isotope').hasClass('four-in-row')){

						if (windowW > 767) {

							minW = $('.products-isotope').width()/4;

						} else if (windowW > 560) {

							minW = $('.products-isotope').width()/3;

						} else {

							minW = $('.products-isotope').width()/1;

						}

					} else if ($('.products-isotope').hasClass('five-in-row')){

						if (windowW > 991) {

							minW = $('.products-isotope').width()/5;

						} else if (windowW > 767) {

							minW = $('.products-isotope').width()/4;

						} else if (windowW > 560) {

							minW = $('.products-isotope').width()/3;

						} else {

							minW = $('.products-isotope').width()/1;

						}

					} else if ($('.products-isotope').hasClass('six-in-row')){

						if (windowW > 1199) {

							minW = $('.products-isotope').width()/6;

						} else if (windowW > 991) {

							minW = $('.products-isotope').width()/5;

						} else if (windowW > 767) {

							minW = $('.products-isotope').width()/4;

						} else if (windowW > 560) {

							minW = $('.products-isotope').width()/3;

						} else {

							minW = $('.products-isotope').width()/1;

						}

					} else if ($('.products-isotope').hasClass('seven-in-row')){

						if (windowW > 1600) {

							minW = $('.products-isotope').width()/7;

						} else if (windowW > 1199) {

							minW = $('.products-isotope').width()/6;

						} else if (windowW > 991) {

							minW = $('.products-isotope').width()/5;

						} else if (windowW > 767) {

							minW = $('.products-isotope').width()/4;

						} else if (windowW > 560) {

							minW = $('.products-isotope').width()/2;

						} else {

							minW = $('.products-isotope').width()/1;

						}

					} else if ($('.products-isotope').hasClass('eight-in-row')){

						if (windowW > 1600) {

							minW = $('.products-isotope').width()/8;

						} else if (windowW > 1199) {

							minW = $('.products-isotope').width()/6;

						} else if (windowW > 991) {

							minW = $('.products-isotope').width()/5;

						} else if (windowW > 767) {

							minW = $('.products-isotope').width()/4;

						} else if (windowW > 560) {

							minW = $('.products-isotope').width()/2;

						} else {

							minW = $('.products-isotope').width()/1;

						}

					}

					

					setProductSize($('.products-col'),minW);

					$('.products-isotope.products-col').isotope().isotope('layout');

			});		

		}

	})



	var prevW = window.innerWidth || $(window).width();

	

	$(window).resize(function() {

        var currentW = window.innerWidth || $(window).width();



        if (currentW != prevW) {

            // start resize events

			if($('.products-isotope').length) {

				if ($('.products-col').parent().parent().hasClass('open')) {

					$('.products-col').stop(true,false).animate({marginLeft: '0'}, 0);

					$('.products-col').find('.product-preview-wrapper').css("width", "");

					var minW =  parseInt($('.products-col').find('.product-preview-wrapper:first-child').width());

					if ($('.products-isotope').hasClass('two-in-row')){

						minW = minW-200;

					}

					if ( $('html').css('direction').toLowerCase() == 'rtl' ) {

						$('.products-col').stop(true,false).animate({marginRight: '280px'}, 200, 

							function() { 

								setProductSize($('.products-col'),minW);

								$('.products-isotope').isotope().isotope('layout');

						});

					

					}

					else {

						$('.products-col').stop(true,false).animate({marginLeft: '280px'}, 200, 

							function() { 

								setProductSize($('.products-col'),minW);

								$('.products-isotope').isotope().isotope('layout');

						});

					}	

				}

				else {

					$('.products-col').find('.product-preview-wrapper').css("width", "");

					setProductSize($('.products-col'),minW);

					$('.products-isotope.products-col').isotope().isotope('layout');

				}

			}





            // end resize events

        }



        prevW = window.innerWidth || $(window).width();



    });



});



// Isotope Filters (for index-original.html)



jQuery(function ($) {

    "use strict";

    var hoverfold = $(".products-isotope");

    if (hoverfold.length != 0) {

        var container = hoverfold;

        var optionSets = $(".filters-by-category .option-set"),

            optionLinks = optionSets.find("a");

        optionLinks.click(function () {

            var thisLink = $(this);

            if (thisLink.hasClass("selected")) return false;

            var optionSet = thisLink.parents(".option-set");

            optionSet.find(".selected").removeClass("selected");

            thisLink.addClass("selected");

            var options = {},

                key = optionSet.attr("data-option-key"),

                value = thisLink.attr("data-option-value");

            value = value === "false" ? false : value;

            options[key] = value;

            if (key === "layoutMode" && typeof changeLayoutMode === "function") changeLayoutMode($this, options);

            else container.isotope(options);

            return false

        })

    }

});





// Grid / Row listing view



jQuery(function($) {



    "use strict";

	

	$('.products-isotope.products-col').on( 'layoutComplete',  function() {

		window.setTimeout(function() {

            $('.products-isotope.products-col').removeClass('no-transition');

         },1000);

	})



	$('a.link-row-view').click(function(e) {

		var windowW = window.innerWidth || $(window).width();

        e.preventDefault();

		$(this).addClass('active');

		$('a.link-grid-view').removeClass('active');

		$('.products-listing').addClass('row-view');

		$('.products-col').find('.product-preview-wrapper').css("width", "");

			if ($('.outer').hasClass('open')) {

				$('.products-isotope.products-col').addClass('no-transition');

				$('.products-col').stop(true,false).animate({marginLeft: '0'}, 0);

				var minW =  parseInt($('.products-col').find('.product-preview-wrapper:first-child').width());

				$('.products-col').stop(true,false).animate({marginLeft: '280px'}, 0, 

					function() { 

						setProductSize($('.products-col'),minW);

						$('.products-isotope.products-col').isotope('layout');

				});			

			}

			else {

				$('.products-isotope.products-col').addClass('no-transition').isotope('layout');

			}	})

	

	$('a.link-grid-view').click(function(e) {

		var windowW = window.innerWidth || $(window).width();

        e.preventDefault();

		$(this).addClass('active');

		$('a.link-row-view').removeClass('active');

		$('.products-listing').removeClass('row-view');

		$('.products-col').find('.product-preview-wrapper').css("width", "");

			if ($('.outer').hasClass('open')) {

				$('.products-isotope.products-col').addClass('no-transition');

				$('.products-col').stop(true,false).animate({marginLeft: '0'}, 0);

				$('.products-col').find('.product-preview-wrapper').css("width", "");

				var minW =  parseInt($('.products-col').find('.product-preview-wrapper:first-child').width());

				if ($('.products-isotope').hasClass('two-in-row')){

					minW = minW-200;

				}

				$('.products-col').stop(true,false).animate({marginLeft: '280px'}, 0, 

					function() {

						setProductSize($('.products-col'),minW);

						$('.products-isotope.products-col').isotope('layout');

				});			

			}

			else {

				$('.products-isotope.products-col').addClass('no-transition').isotope('layout');

			}	})	

})





jQuery(function($) {



    "use strict";



    var posts = $('.posts-isotope');

	

	if (posts.length) {

		posts.isotope({

			itemSelector: '.post',

			masonry: {

				isFitWidth: true

			}

		});

		posts.imagesLoaded(function() {

			setPostSize();

		});	

	}

    function setPostSize() {

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

        var containerW = posts.parent('.container').width(),

            postW = Math.floor(containerW / itemsInRow);

        posts.find('.post').each(function() {

            $(this).css({

                width: postW + 'px'

            });

        });

        posts.isotope('layout');

    }

	

	$('.view-more').on( 'click', function() {

  

	  var item;

	  var target =  $(this).attr('data-load');

	  

	  $(this).hide();

  

  	  $.ajax({

		  url: target,

		  success: function( data ) {

		  $('#postPreload').append(data);  	  

		  

		  $('#postPreload > div').each(function() {

			item = $(this);

			posts.append(item).isotope('appended', item);

			setPostSize();

		  });





		 }

	  });

	  

    })

  

    var prevW = window.innerWidth || $(window).width();



    $(window).resize(debouncer(function(e) {

		if (posts.length) {			

			var currentW = window.innerWidth || $(window).width();

			if (currentW != prevW) {

				// start resize events	

				setPostSize();

				// end resize events		

			}

			prevW = window.innerWidth || $(window).width();

		}

    }));

})



jQuery(function($) {



    "use strict";

	var gallery = $('.gallery');

	

	

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

	

	$('.view-more-gallery').on( 'click', function() {

  

	  var item;

	  var target =  $(this).attr('data-load');

	  

	  $(this).hide();

  

  	  $.ajax({

		  url: target,

		  success: function( data ) {

		  $('#galleryPreload').append(data);  	  

		  

		  $('#galleryPreload > div').each(function() {

			item = $(this);

			gallery.append(item).isotope('appended', item);

			setGallerySize();

		  });





		 }

	  });

	  

    })

  

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

// Art Catalogue



jQuery(function($) {



    "use strict";

	var gallery = $('.art-catalogue');

	

	 var windowW = window.innerWidth || $(window).width();

	 var containerW = gallery.width(),

         columnW = containerW / 4;

			

	if (gallery.length) {

		gallery.isotope({

			itemSelector: '.art-catalogue__item',

			isResizeBound: false,

			percentPosition: true,

			masonry: {

			  columnWidth: '.art-catalogue__item:not(.art-catalogue__item--double)'

			}

		});

		gallery.imagesLoaded(function() {

			setGallerySize();

		});	

	}

    function setGallerySize() {

        var windowW = window.innerWidth || $(window).width(),

            itemsInRow = 1;

        if (windowW > 1199) {

            itemsInRow = 4;

        } else if (windowW > 991) {

            itemsInRow = 4;

        } else if (windowW > 767) {

            itemsInRow = 2;

        } else if (windowW > 480) {

            itemsInRow = 2;

        }

        var containerW = gallery.parent('.container').width(),

            galleryW = containerW / itemsInRow;

			gallery.find('.art-catalogue__item').each(function() {

			if ($(this).hasClass('art-catalogue__item--double') && windowW > 767 ) {

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

		gallery.isotope('layout');

    }		

		var prevW = window.innerWidth || $(window).width();

		

		$(window).resize(debouncer(function(e) {

			var gallery = $('.art-catalogue');

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

// 	Vertical Menu



jQuery(function($) {



    "use strict";

	var windowW = window.innerWidth || $(window).width();



	

	function setMenuSize(winW) {

		var vMenu = $('.navbar-nav--vertical'),

		vMenuW = vMenu.innerWidth()-2,

		vMenuH = vMenu.innerHeight()+2,

		containerW = vMenu.closest('div[class^="container"]').width();		

		if (vMenu.length){

			if (winW > 991){

				vMenu.children('li').children('.dropdown-menu').css({'margin-left': vMenuW+'px'});		

				vMenu.find('.dropdown-menu.megamenu').css({"width": (containerW-vMenuW)+'px', "min-height": vMenuH +'px'});		

			}

			else {

				vMenu.children('li').children('.dropdown-menu').css({'margin-left': '', 'width': ''});	

			}

		}

	}

	

	setMenuSize(windowW);

	

	var prevW = windowW;

	

	$(window).resize(debouncer(function(e) {

			var currentW = window.innerWidth || $(window).width();

			if (currentW != prevW) {

				// start resize events

				setMenuSize(currentW);

				// end resize events		

			}

			prevW = window.innerWidth || $(window).width();

	}));

});



// Blog Post button extend



jQuery(function($) {



    "use strict";

	

	$('.btn-plus > a').on('click', function(e){ 

		e.preventDefault() // prevent default action - hash doesn't appear in url

   		$(this).parent().find( 'div' ).toggleClass( 'btn-plus__links--active' );

		$(this).toggleClass('expanded');

    });

  

});



// tooltip ini



jQuery(function($) {



    "use strict";

    $('.tooltip-link').tooltip();



});



// open menu button small Header



jQuery(function($) {



    "use strict";

    $('#openMenu').on('click', function(e){

		

	})



});







// bootstrap minus and plus



jQuery(function($) {



    "use strict";

    

    $('.btn-number').click(function(e) {

        e.preventDefault();

        var type = $(this).attr('data-type');

        var input = $(this).closest('.input-group-qty').find('input.input-qty');

        var currentVal = parseInt(input.val());

        if (!isNaN(currentVal)) {

            if (type == 'minus') {
				$('#qtyplus').attr('disabled' , false);
                if (parseInt(input.val()) == input.attr('min')) {
                    $('#qtyminus').attr('disabled', true);
                }
            } else if (type == 'plus') {
				$('#qtyminus').attr('disabled' , false);
                if (parseInt(input.val()) == input.attr('max')) {
                    $('#qtyplus').attr('disabled', true);
                }

            }

        } else {

            input.val(0);

        }

    });

    $('.input-number').focusin(function() {

        $(this).data('oldValue', $(this).val());

    });

    $('.input-number').change(function() {



		var minValue = parseInt($(this).attr('min'));

		var maxValue = parseInt($(this).attr('max'));

        var valueCurrent = parseInt($(this).val());



        var name = $(this).attr('name');

        if (valueCurrent >= minValue) {

            $(this).closest('.input-group-qty').find(".btn-number[data-type='minus']").removeAttr('disabled')

        } else {

            alert('Sorry, the minimum value was reached');

            $(this).val($(this).data('oldValue'));

        }

        if (valueCurrent <= maxValue) {

            $(this).closest('.input-group-qty').find(".btn-number[data-type='plus']").removeAttr('disabled')

        } else {

            alert('Sorry, the maximum value was reached');

            $(this).val($(this).data('oldValue'));

        }





    });

	

});



// landing category



jQuery(function($) {



    "use strict";

	var categoryImg = $('.category-block__image');

	var categoryList = $('.no-touch .category-block__list');

	var categoryListT = $('.touch .category-block__list');

	

    categoryList.find('a').hover(function(e){

		e.preventDefault();

		$(this).closest('.category-block__list').find('a').removeClass('active');

		$(this).addClass('active');

		var targ = $(this).parent('li').index();

		var curImg = $(this).closest('.category-block').find('.category-block__image > img.active');

		var cur = curImg.index();

		if (targ == cur) {

			return false;

		}

		else {

			var newImg = $(this).closest('.category-block').find('.category-block__image > img:nth-child('+ (targ+1) +')');

			curImg.removeClass('active');

			newImg.addClass('active')

		}

	})

	

	categoryListT.find('a').click(function(e){

		e.preventDefault();

		$(this).closest('.category-block__list').find('a').removeClass('active');

		$(this).addClass('active');

		var targ = $(this).parent('li').index();

		var curImg = $(this).closest('.category-block').find('.category-block__image > img.active');

		var cur = curImg.index();

		if (targ == cur) {

			window.location = this.href;

		}

		else {

			var newImg = $(this).closest('.category-block').find('.category-block__image > img:nth-child('+ (targ+1) +')');

			curImg.removeClass('active');

			newImg.addClass('active')

		}

	})



});



/*!

 * classie - class helper functions

 * from bonzo https://github.com/ded/bonzo

 * 

 * classie.has( elem, 'my-class' ) -> true/false

 * classie.add( elem, 'my-new-class' )

 * classie.remove( elem, 'my-unwanted-class' )

 * classie.toggle( elem, 'my-class' )

 */



/*jshint browser: true, strict: true, undef: true */

/*global define: false */



( function( window ) {



'use strict';



// class helper functions from bonzo https://github.com/ded/bonzo



function classReg( className ) {

  return new RegExp("(^|\\s+)" + className + "(\\s+|$)");

}



// classList support for class management

// altho to be fair, the api sucks because it won't accept multiple classes at once

var hasClass, addClass, removeClass;



if ( 'classList' in document.documentElement ) {

  hasClass = function( elem, c ) {

    return elem.classList.contains( c );

  };

  addClass = function( elem, c ) {

    elem.classList.add( c );

  };

  removeClass = function( elem, c ) {

    elem.classList.remove( c );

  };

}

else {

  hasClass = function( elem, c ) {

    return classReg( c ).test( elem.className );

  };

  addClass = function( elem, c ) {

    if ( !hasClass( elem, c ) ) {

      elem.className = elem.className + ' ' + c;

    }

  };

  removeClass = function( elem, c ) {

    elem.className = elem.className.replace( classReg( c ), ' ' );

  };

}



function toggleClass( elem, c ) {

  var fn = hasClass( elem, c ) ? removeClass : addClass;

  fn( elem, c );

}



var classie = {

  // full names

  hasClass: hasClass,

  addClass: addClass,

  removeClass: removeClass,

  toggleClass: toggleClass,

  // short names

  has: hasClass,

  add: addClass,

  remove: removeClass,

  toggle: toggleClass

};



// transport

if ( typeof define === 'function' && define.amd ) {

  // AMD

  define( classie );

} else {

  // browser global

  window.classie = classie;

}



})( window );







// Modal Search Popup





jQuery(function($) {

	// custom product options collapse
	$('.prod-options-box input').change(function() {
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
			console.log(elValue , selectedValue, elValue - selectedValue)
			$(this).html(calcValue)
		});

	})
	//Product Option Select
		var ProductValue = [];
        $('.optionProduct input[type="radio"]').click(function () {
            ProductValue = [];
            $.each($(".optionProduct input[type='radio']:checked"), function () {
                ProductValue.push($(this).val());
            });
            
			$('.heading_pro .name').text('');
            for(var i=0; i< ProductValue.length; i++){
				const text_value = $('#option-value-' + ProductValue[i]).parent().find('.option-name').text();
				let mainOptionName =  $('#option-value-' + ProductValue[i]).attr('main-option-value')
			    $('.heading_pro .name').append("<p>"+mainOptionName+ " : <span> "+ text_value +"</span></p>");
                
			}
		});
	$('.c-options-group').each(function() {
		if($(this).find('.prod-options-box input[checked]').length > 0) {
			console.log($(this).find('.prod-options-box input[checked]').length)
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
	if ($('.overlay').length && $('.search-open').length) {

	

    var triggerBttn = $('.search-open'),

        overlay = document.querySelector('div.overlay'),

        closeBttn = overlay.querySelector('button.overlay-close');

		transEndEventNames = {

            'WebkitTransition': 'webkitTransitionEnd',

            'MozTransition': 'transitionend',

            'OTransition': 'oTransitionEnd',

            'msTransition': 'MSTransitionEnd',

            'transition': 'transitionend'

        },

        transEndEventName = transEndEventNames[Modernizr.prefixed('transition')],

        support = {

            transitions: Modernizr.csstransitions

        };



    function toggleOverlay() {

        if (classie.has(overlay, 'open')) {

			$('body').removeClass('modal-open');

            classie.remove(overlay, 'open');

            classie.add(overlay, 'close');

            var onEndTransitionFn = function(ev) {

                if (support.transitions) {

                    if (ev.propertyName !== 'visibility') return;

                    this.removeEventListener(transEndEventName, onEndTransitionFn);

                }

                classie.remove(overlay, 'close');

            };

            if (support.transitions) {

                overlay.addEventListener(transEndEventName, onEndTransitionFn);

            } else {

                onEndTransitionFn();

            }

        } else if (!classie.has(overlay, 'close')) {

            classie.add(overlay, 'open');

			$('body').addClass('modal-open');

        }



		return false;

    }



    triggerBttn.on('click', toggleOverlay);

    closeBttn.addEventListener('click', toggleOverlay);

	}

});





// Landing content page



jQuery(function($) {

	

    "use strict";

	function isiPad(){

		return (navigator.platform.indexOf("iPad") != -1);

	}

	

	function setChapterHeight() {

		var windowH = $(window).height();

		if(isiPad()) {

			windowH+=80

		}

		$('.chapter').each(function() {

			$(this).css({'min-height': windowH+'px'});

		})

		$('.chapter__title').each(function() {

			$(this).css({'min-height': windowH+'px'})

		})

		$('.chapter__content').each(function() {

			$(this).css({'min-height': windowH+'px'})

		})

		$('.chapter__content .category-big-banner, .chapter__content .category-big-banner__image').each(function() {

			$(this).css({'min-height': windowH+'px'})

		})

	}

	function addChapterEnd() {

		$('.chapter').each(function() {

			var row_str = '<div class="chapter__end">chapter END</div>';

			$(this).append(row_str);

		})

	}

	function setChapterWaypoint() {

			var waypoints = $('.chapter').waypoint(function(direction) {

				if (direction === 'down') {

					$('.chapter__title').removeClass('title--fixed').removeClass('title--bottom');

					$('.chapter__title').removeClass('title--fixed');				

					$(this.element).find('.chapter__title').addClass('title--fixed');

				}

			}, {

				offset: 0

			})

			var waypoints = $('.chapter').waypoint(function(direction) {

				if (direction === 'up') {

					$('.chapter__title').removeClass('title--fixed').removeClass('title--bottom');				

				}

			}, {

				offset: 0

			})

			var waypointsEnd = $('.chapter__end').waypoint(function(direction) {

				if (direction === 'down') {

					$('.chapter__title').removeClass('title--fixed').removeClass('title--bottom');				

					$(this.element).parent().find('.chapter__title').addClass('title--bottom');

					var panel;

					panel = $(this.element).parent().attr('id');

				}

			}, {

				offset: $(window).height()

			})

			var waypointsEnd = $('.chapter__end').waypoint(function(direction) {

				if (direction === 'up') {

					$('.chapter__title').removeClass('title--fixed').removeClass('title--bottom');				

					$(this.element).parent().find('.chapter__title').addClass('title--bottom');

				}

			}, {

				offset: 0

			})

			var waypointsEndB = $('.chapter__end').waypoint(function(direction) {

				if (direction === 'up') {

					$('.chapter__title').removeClass('title--fixed').removeClass('title--bottom');				

					$(this.element).parent().find('.chapter__title').addClass('title--fixed');

				}

			}, {

				offset: $(window).height()

			})				



	}

		

	var windowW = window.innerWidth || $(window).width();

	

	if ($('#landingContent').length) {

	

	if (windowW > 991){

			

			setChapterHeight();

			addChapterEnd();

			setChapterWaypoint();

			

		}

		else {

			setChapterHeight();

		}

	}



		$(window).resize(debouncer(function(e) {

		var windowW = window.innerWidth || $(window).width();

	

	if ($('#landingContent').length) {

		

		if (windowW > 991){

			

			setChapterHeight();

			addChapterEnd();

			setChapterWaypoint();

			

		}

		else {

			setChapterHeight();

			Waypoint.disableAll()

		}	

	

	}

		

	}));



});







jQuery(function($) {

	

    "use strict";

	

	 $('.ajax-to-cart').click(function(e){

		e.preventDefault();

		 $(this).addClass('btn--wait');

		 $.ajax({url: "ajax.php", success: function(result){

			 $('.ajax-to-cart').removeClass('btn--wait');

			 $('#modalAddToCart').modal("toggle");

			}});

	});

	 // todo:
	 // 1- add to wishlist message already shown by another function/listener in another place we need to remove this or the other function.
	 // 2- this function not work right and cause shaded home page.

	 // $('.ajax-to-wishlist').click(function(e){
	 //
		// e.preventDefault();
		// $('#modalAddToWishlist').modal("toggle");
		// $('#modalAddToWishlist .loading').show();
	 //
		// $.ajax({url: "ajax.php", success: function(result){
		// 	$('#modalAddToWishlist .loading').hide();
		// 	$('#modalAddToWishlist .success').show();
		// }});
	 // });

});





// MD inputs label



jQuery(function($) {

	

    "use strict";



  $('.input-group--wd > input, .input-group--wd > textarea ').blur(function() {

    var $this = $(this);

    if ($this.val())

      $this.addClass('used');

    else

      $this.removeClass('used');

  });

  

 });

 

// Remove Loader



jQuery(function($) {

	

	"use strict";

	$('body').addClass('loaded');

 

});



// Gallery Popup



jQuery(function($) {

	

    "use strict";

	if ($('#gallery').length) {

	$('#gallery a.btn').magnificPopup({

		type:'image',

		gallery:{

			enabled:true

		}

	});

	}

	

});



// Video Popup



jQuery(function($) {

	



    "use strict";



	if ($('.video-link').length) {

		$('.video-link').magnificPopup({

          disableOn: 767,

          type: 'iframe',

          mainClass: 'mfp-fade',

          removalDelay: 160,

          preloader: true,

          fixedContentPos: false

        });

	}

	

});





// Set carousel nav position



jQuery(function($) {



    "use strict";

	

	var prevW = window.innerWidth || $(window).width();



	if ($('body').hasClass('boxed')) {

		$('.aside-column .slick-slider.slick-initialized').each(function() {

			if (!$(this).hasClass('nav-top') && !$(this).hasClass('nav-dot')) {

					$(this).addClass('nav-inside');

			}

		})

	}

	

	if ($('body').hasClass('fullwidth')) {

		$('.slick-slider.slick-initialized').each(function() {

			if (!$(this).hasClass('nav-top') && !$(this).hasClass('nav-dot')) {

					$(this).addClass('nav-inside');

			}

		})

	}

	else {

		$('.slick-slider.slick-initialized').each(function() {

			if (!$(this).closest('.single-slider').length) {

				

				if ($('body').hasClass('boxed') && !$(this).hasClass('blog-carousel')) {

					var outerW = prevW;

				}

				else var outerW = $(this).closest('section').width();

				

				if ((outerW - $(this).width()) * 0.5 < 45) {

					$(this).addClass('nav-inside');

					$('.slick-initialized').slick('setPosition');

				}

				

			}

		});

	}



    $(window).resize(debouncer(function(e) {

        var currentW = window.innerWidth || $(window).width();

        if (currentW != prevW) {

            // start resize events		

		

		$('.slick-slider.slick-initialized').each(function() {

			if (!$(this).closest('.single-slider').length) {

				

				if ($('body').hasClass('boxed') && !$(this).hasClass('blog-carousel')) {

					var outerW = currentW;

				}

				else var outerW = $(this).closest('section').width();

				

				if ((outerW - $(this).width()) * 0.5 < 45) {

					$(this).addClass('nav-inside');

					$('.slick-initialized').slick('setPosition');

				}

				else {

					$(this).removeClass('nav-inside');

				}

				

			}

		});

			

            // end resize events		

        }

        prevW = window.innerWidth || $(window).width();



    }));



});



// double click fo touch devices



// jQuery(function($) {



//     "use strict";

	

// 	var windowW = window.innerWidth || $(window).width();

	

// 	$('.touch category-block__list a').doubleTapToGo();

// 	if (windowW > 767) {

// 		$('.touch ul.navbar-nav > li').each(function() {

// 			if ($(this).find('a').hasClass('dropdown-toggle')) {

// 				$(this).doubleTapToGo()

// 			}

// 		})

// 	}

//     $('.touch .product-preview').doubleTapToGo();

	

// });



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



// Contact page form



jQuery(function($) {

    $('#contactform').validate({

        rules: {

			name: {

                required: true,

                minlength: 2

            },

			message: {

                required: true,

                minlength: 20

            },

            email: {

                required: true,

                email: true

            }



        },

        messages: {

            name: {

                required: "Please enter your name",

                minlength: "Your name must consist of at least 2 characters"

            },

            message: {

                required: "Please enter message",

                minlength: "Your message must consist of at least 20 characters"

            },

			email: {

                required: "Please enter your email"

            }

        },

        submitHandler: function(form) {

            $(form).ajaxSubmit({

                type:"POST",

                data: $(form).serialize(),

                url:"process-contact.php",

                success: function() {

                      $('#success').fadeIn();

					  $('#contactform' ).each(function(){this.reset();});

                },

                error: function() {

						$('#contactform').fadeTo( "slow", 0, function() {

						$('#error').fadeIn();

                    });

                }

            });

        }

    });

});

// Newsletter subscribe form



jQuery(function($) {

	

    "use strict";

	

	if($('#subscribe-form').length){

		$('#subscribe-form').validate({

			rules: {

				subscribemail: {

					required: true,

					email: true

				}

	

			},

			messages: {

				subscribemail: {

					required: "Please enter your email"

				}

			},

			submitHandler: function(form) {

				$(form).ajaxSubmit({

					type:"POST",

					data: $(form).serialize(),

					url:"process-subscribe.php",

					success: function() {

						  $('#subscribeSuccess').fadeIn();

						  $('#subscribe-form').each(function(){this.reset();});

					},

					error: function() {

						$('#subscribe-form').fadeTo( "slow", 0, function() {

							$('#subscribeError').fadeIn();

						});

					}

				});

			}

		});

	}

});
function getItemsHeight(selector) {

	var itemsLength = $(selector).length;
	var itemHeight = 0;
	var highestHeight = 0;
	$(selector).removeAttr("style");
	for (var i = 0; i < itemsLength; i++) {
		itemHeight = $(selector + ':eq(' + i + ')').height();
		if (itemHeight > highestHeight) {
			highestHeight = itemHeight + 20;
		}
	}
	$(selector).css('height', highestHeight);
	$(selector).addClass('mobile-single-item-parent');

}
getItemsHeight('.product-category__info');



// To equal height of all products’info section
$(document).ready(function(){
	$maxHeight = 0;
	$(".product-preview__info").each(function(){
		if($(this).height() > $maxHeight) {
			$maxHeight = $(this).height();
		}
	});
	$(".product-preview__info").height($maxHeight);
});