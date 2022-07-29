$(document).ready(function () {


  /// short description 
  $('.detail-on-head > *').filter(function () {
    return $(this).text().trim().length == 0;
  }).remove();

  /// product rate  
  $('.ratestars li').click(function () {
    $(this).addClass('active-star').removeClass('norate').siblings().removeClass('active-star norate');

    if ($(this).hasClass('active-star')) {
      $(this).closest('.ratestars ').find('input').prop('checked', false);
      $(this).find('input').prop('checked', true);
    } else {
      $(this).closest('.ratestars').find('input').prop('checked', false);
    }
  });

});