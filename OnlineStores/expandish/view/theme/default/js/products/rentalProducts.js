$(document).ready(function () {
    $('.dateRangeFrom, .dateRangeTo').css({
        "position": "relative","z-index": "100000000"
    });

    $('.dateRangeTo').datepicker({
        dateFormat: 'yy/mm/dd',
        minDate: 0,
        maxDate: maxRentDays,
        onClose: function(selectedDate) {
            var startDate = $('.dateRangeFrom').datepicker("getDate");
            var endDate = $('.dateRangeTo').datepicker("getDate");
            var days = (endDate - startDate) / (1000 * 60 * 60 * 24);
            days = days ? days : 1;

            var specialPrice = specialNumber * days;
            var totalPrice = priceNumber * days;

            var productPrice = $('.product-price ins span').html();

            $('.product-price .special-price ins span').html(
                currencySymbols['left'] + specialPrice + currencySymbols['right']
            );
            $('.product-price .old-price del span').html(
                currencySymbols['left'] + totalPrice + currencySymbols['right']
            );
        },
        beforeShowDay: function(date){
            var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
            return [ disabledDates.indexOf(string) == -1 ]
        }
    });
    $('.dateRangeFrom').datepicker({
        dateFormat: 'yy/mm/dd',
        minDate: 0,
        maxDate: maxRentDays,

        onClose: function(selectedDate) {

            var date = new Date( Date.parse( selectedDate ) );

            date.setDate( date.getDate());

            var newDate = date.toDateString();
            newDate = new Date( Date.parse( newDate ) );

            $('.dateRangeTo').datepicker("option", "minDate", newDate);
            $('.dateRangeTo').datepicker("show");
        },
        beforeShowDay: function(date){
            var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
            return [ disabledDates.indexOf(string) == -1 ]
        }
    });
   
    $(".dateRangeFrom").datepicker("setDate", "0");
    $(".dateRangeTo").datepicker("setDate", "0");

});