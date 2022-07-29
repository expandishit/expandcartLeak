$(document).ready(function () {
    var googlePlusIconParent;
    if($('.dsl-google-plus').length)
    {
       var googlePlusIconParent = $('.dsl-google-plus').parent();
       $('.dsl-google-plus').remove();
    }
    else if($('.fa-google').length)
    {
        var googlePlusIconParent = $('.fa-google').parent();
        $('.fa-google').remove();
    }
    if($(googlePlusIconParent).length){
        googlePlusIconParent.append('<i class="fa fa-google"></i>');
        googlePlusIconParent.css('padding', '0 5px');
    }
});