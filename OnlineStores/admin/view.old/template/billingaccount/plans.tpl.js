$(document).ready(function() {
    $('.pricing-custom .price-box').click(function () {
        if($(this).hasClass('selected'))
            return;
        else {
            $('.pricing-custom .price-box').removeClass('selected');
            $(this).addClass('selected');
            var pricingPlan = $(this).data('plan');
            $('.pricing-plan-details').removeClass('selected');
            $('.' + pricingPlan).addClass('selected');
        }
    });
});