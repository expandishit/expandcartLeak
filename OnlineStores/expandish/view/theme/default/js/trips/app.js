$(document).ready(function() {

  $('.trips--label-toggle').on('click', function() {
    $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
    $('.product-options-wrapper-q, .block-product-info-toggle').slideToggle();
  });

    $("#carouselLinks").owlCarousel({
        autoplay: true,
        rewind: true,
        margin: 20,
        responsiveClass: true,
        autoplayTimeout: 1000000000,
        smartSpeed: 0,
        nav: false,
        dots: false,
        responsive: {
          0: {
            items: 2
          },
      
          600: {
            items: 4
          },
      
          1024: {
            items: 6
          },
      
          1366: {
            items: 6
          }
        }
      });
    
      $("#cards").owlCarousel({
        autoplay: true,
        rewind: true,
        margin: 20,
        responsiveClass: true,
        autoplayTimeout: 1000000000,
        smartSpeed: 0,
        nav: false,
        dots: false,
        responsive: {
          0: {
            items: 1,
            margin: 30
          },
      
          600: {
            items: 3
          },
      
          1024: {
            items: 4
          },
      
          1366: {
            items: 4
          }
        }
      });

      $('.trips-ctrls button').each(function() {
          $(this).on('click', function(e) {
              e.preventDefault();
              $(this).toggleClass('active');
          });
      });


});

$("#demo_1").ionRangeSlider({
    type: "double",
    grid: true,
    min: 0,
    max: 1000,
    from: 200,
    to: 700,
    prefix: "$"
});