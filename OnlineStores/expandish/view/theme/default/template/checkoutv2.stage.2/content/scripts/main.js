//  $(document).ready(function (){

//      $('.step').click(function (){
//         var _this = $(this);
//         var dataStep = $(this).attr('data-step');

//         var childrenInputs =  $('.tap.active').children('input');

//         $(childrenInputs).each(function (){
//            if($(this).val().trim() == ''){
//                $(this).addClass('tap--invalid');
//            } else {
//                $(this).removeClass('tap--invalid');
//            }
//         });

//         if(_this.hasClass('active')){
//             return false
//         } else {
//             if(childrenInputs.hasClass('tap--invalid')){
//                 return false
//              } else {
//                  $('.taps-container .tap').fadeOut(0).removeClass('active');
//                  $('.taps-container .tap').each(function (){
//                      var dataTap = $(this).attr('data-tap');
//                      if(dataStep == dataTap){
//                          $('.step').removeClass('active');
//                          $(_this).addClass('active');
//                          $(this).fadeIn('fast').addClass('active');
//                      }
//                  });
//              }

//         }
//      });

//      $('.taps__next:not(.taps--one-page)').click(function (){
//      var childrenInputs =  $('.tap.active').children('input');

//      $(childrenInputs).each(function (){
//         if($(this).val().trim() == ''){
//             $(this).addClass('tap--invalid');
//         } else {
//             $(this).removeClass('tap--invalid');
//         }
//      });

//      if(childrenInputs.hasClass('tap--invalid')){
//         return false
//      } else {
//             $('.step.active').next().addClass('active').prev().removeClass('active');
//             var dataStep = $('.step.active').attr('data-step');
//             $('.taps-container .tap').each(function (){
//                 var dataTap = $(this).attr('data-tap');
//                 if(dataStep == dataTap){
//                     $('.taps-container .tap').fadeOut(0).removeClass('active');
//                     $(this).fadeIn('fast').addClass('active');
//                 }
//             });
//         }
//      });
//  });
$(document).ready(function () {
    // $('.summary__input__label').click(function (){
    //     $(this).next().slideToggle(function (){
    //       $(this).stop(1000);
    //     });
    // });

    // $('.btn-plus, .btn-minus').on('click', function(e) {
    //     const isNegative = $(e.target).closest('.btn-minus').is('.btn-minus');
    //     const input = $(e.target).closest('.input-group').find('input');
    //     if (input.is('input')) {
    //         input[0][isNegative ? 'stepDown' : 'stepUp']()
    //     }
    //     if(input.val() == 0){
    //        $('.reward__confirm').addClass('disabled');
    //     } else{
    //         $('.reward__confirm').removeClass('disabled');
    //     }
    // });
    if ($(" .input-reward__number").val() == 0) {
        $(".reward__confirm").addClass("disabled");
    } else {
        $(".reward__confirm").removeClass("disabled");
    }
    $(" .input-reward__number").on("input", function (e) {
        if ($(this).val() == 0) {
            $(".reward__confirm").addClass("disabled");
        } else {
            $(".reward__confirm").removeClass("disabled");
        }
    });

    $(".main-nav__logo .btn-summary").click(function () {
        $(".main-checkout__order-summary").addClass("open--summary");
        $("body").addClass("hidden--body");
    });

    $(".close--summary").click(function () {
        $(".main-checkout__order-summary").removeClass("open--summary");
        $("body").removeClass("hidden--body");
    });
    // $(".saved-address__info")
    //     .fadeOut(0)
    //     .closest(".saved-address-container.active")
    //     .find(".saved-address__info")
    //     .fadeIn();
    // $(".shipping__add-address").click(function (e) {
    //     e.preventDefault();
    //     $(".shipping__address-info").stop().slideToggle();
    // });
    //   $(".saved-address__control").click(function (e) {
    //         $(this).find(".form-check-input").prop("checked", "true");
    //     });

    // $("body").on("click", ".saved-address__control", function () {
    //     $(this)
    //         .find(".form-check-input")
    //         .prop("checked", "true")
    //         .trigger("change");
    // });

    $("body").on("click", ".comment_input__open", function () {
        $(".comment_input__open--leave-comment").slideToggle(function () {
            $(this).stop(1000);
        });
    });

    // $(".saved-address__control").click(function (e) {
    //     $(this).find(".form-check-input").prop("checked", "true");
    //     $(".saved-address__info").stop().slideUp();
    //     if ($(this).closest(".saved-address-container").hasClass("active")) {
    //         $(this)
    //             .next()
    //             .slideUp()
    //             .closest(".saved-address-container")
    //             .removeClass("active");
    //     } else {
    //         $(".saved-address-container").removeClass("active");
    //         e.preventDefault();
    //         $(this)
    //             .next()
    //             .stop()
    //             .slideToggle()
    //             .closest(".saved-address-container")
    //             .addClass("active");
    //     }
    // });
    // $(".info__details__edit").click(function () {
    //     $(this)
    //         .closest(".saved-address-container")
    //         .find(".saved-address__control , .saved-address__info")
    //         .fadeOut()
    //         .closest(".saved-address-container")
    //         .find(".info__container.edit--address")
    //         .fadeIn();
    // });
    // $(".data__save").click(function () {
    //     $(this)
    //         .closest(".info__container.edit--address")
    //         .fadeOut("fast")
    //         .closest(".saved-address-container")
    //         .find(".saved-address__control , .saved-address__info")
    //         .fadeIn();
    // });
    
    // $('.js-select').select2({});
});
