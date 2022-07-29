if (jQuery('.top-banner-app .deal-palallax-app').length > 0) {
    jQuery(".top-banner-app .deal-palallax-app").TimeCircles({
        fg_width: 0.05,
        bg_width: 0,
        text_size: 0,
        circle_bg_color: "transparent",
        time: {
            Days: {
                show: true,
                text: days,
                color: "#fff"
            },
            Hours: {
                show: true,
                text: hours,
                color: "#fff"
            },
            Minutes: {
                show: true,
                text: minutes,
                color: "#fff"
            },
            Seconds: {
                show: true,
                text: seconds,
                color: "#fff"
            }
        }
    }).addListener(countdownComplete);
	
    function countdownComplete(unit, value, total){
        if(total <= 0) {
            $('.top-banner-app').remove()
        }
    };
    // toggle collapse top banner
    $('.top-banner-app .top-banner-arrow').click(function() {
        let topBannerCollapsed = localStorage.getItem('topBannerCollapsed');
    
        if(topBannerCollapsed == 'false') {
            localStorage.setItem('topBannerCollapsed', 'true');
            $('.top-banner-app .container').stop().slideUp();
            $('.top-banner-app .top-banner-arrow').removeClass('active');
        } else {
            localStorage.setItem('topBannerCollapsed', 'false');
            $('.top-banner-app .container').stop().slideDown();
            $('.top-banner-app .top-banner-arrow').addClass('active');
        }
    })
    if(localStorage.getItem('topBannerCollapsed') == 'true') {
        $('.top-banner-app .container').stop().slideUp();
        $('.top-banner-app .top-banner-arrow').removeClass('active');
    } else {
        $('.top-banner-app .container').stop().slideDown();
        $('.top-banner-app .top-banner-arrow').addClass('active');
    }
    
    // show and hide top banner based on banner version
    let localBannerVersion = localStorage.getItem('bannerVersion');
    let localBannerHide = localStorage.getItem('hideTopBanner');
    
    if(isLogged) {
        if(localBannerVersion != bannerVersion) {
            localStorage.setItem('bannerVersion', bannerVersion);
            localStorage.setItem('hideTopBanner', false);
        } else if(localBannerHide == 'true') {
            $('.top-banner-app').remove();
        }
    }
    
    $('.top-banner-app .close-top-banner').click(function() {
        localStorage.setItem('hideTopBanner', true);
        $('.top-banner-app').remove();
    })
}
