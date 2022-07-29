$(document).ready(function() {
    $('.pricing-custom.monthly .price-box').click(function () {
        if($(this).hasClass('selected'))
            return;
        else {
            $('.pricing-custom.monthly .price-box').removeClass('selected');
            $(this).addClass('selected');
            var pricingPlan = $(this).data('plan');
            $('.pricing-plan-details.monthly-detail').removeClass('selected');
            $('.' + pricingPlan).addClass('selected');
        }
    });

    $('.pricing-custom.yearly .price-box').click(function () {
        if($(this).hasClass('selected'))
            return;
        else {
            $('.pricing-custom.yearly .price-box').removeClass('selected');
            $(this).addClass('selected');
            var pricingPlan = $(this).data('plan');
            $('.pricing-plan-details.yearly-detail').removeClass('selected');
            $('.' + pricingPlan).addClass('selected');
        }
    });

    $('.pricing-tabs_link').click(function () {
        if($(this).hasClass('selected'))
            return false;
        else {
            $('.pricing-tabs_link').removeClass('selected');
            $(this).addClass('selected');
            var pricingTerm = $(this).data('term');
            $('.pricing-tabs__tab').removeClass('active');
            $('.' + pricingTerm).addClass('active');

            if (pricingTerm == 'yearly-term') {
                $('.pricing-custom.monthly').hide();
                $('.pricing-plan-details.monthly-detail').addClass('hidePlan');
                $('.pricing-custom.yearly').show();
                $('.pricing-plan-details.yearly-detail').removeClass('hidePlan');
            } else {
                $('.pricing-custom.yearly').hide();
                $('.pricing-plan-details.yearly-detail').addClass('hidePlan');
                $('.pricing-custom.monthly').show();
                $('.pricing-plan-details.monthly-detail').removeClass('hidePlan');
            }

            return false;
        }
    });


    /**
     ** Start Price
     **/
    // Navigation 
    var Individual_Plans   = $('#individual, .mob-Slider .Individual-router'),
    Business_Plans     = $('#business, .mob-Slider .Business-router'),
    Platform_Plans     = $('#platform, .mob-Slider .Platform-router'),
    // boxPrice 
    PackagePriceOne    = $('.pricing-package-one'),
    PackagePriceTwo    = $('.pricing-package-two'),
    PackagePriceThree  = $('.pricing-package-three'),

    ListNavigation     = $('.list-navigation li'),
    boxPricing         = $('.price .box-pricing'),
    // Button Show All Feature && More Less
    BottomToggleMore   = $(".price-business .compare-details strong"),
    SectionPriceTop = $('.price.table-price').offset().top,
    body            = $("html, body");

    Individual_Plans.on('click', function() {
        RemoveNavigation();
        $('#individual,#individual_table').addClass('active');
        HiddenBoxPrice();                    
        // Two
        HiddenTablePrice();
        PackagePriceOne.add(FeatureOne).add(BusinessRouter).addClass('active');            
        BusinessRouter.addClass("bs-price-left");
    });
    Business_Plans.on('click', function() {
        RemoveNavigation();
        $('#business, #business_table').addClass('active');
        HiddenBoxPrice();            
        //Two
        HiddenTablePrice();
        PackagePriceTwo.add(FeatureTwo).add(IndividualRouter).add(PlatformRouter).addClass('active');            
        BusinessRouter.removeClass("bs-price-left, bs-price-right");
    });
    Platform_Plans.on('click', function() {
        RemoveNavigation();
        $('#platform,#platform_table').addClass('active');
        HiddenBoxPrice();            
        //Two        
        HiddenTablePrice();
        PackagePriceThree.add(FeatureThree).add(BusinessRouterRight).addClass('active');
        BusinessRouter.removeClass("bs-price-left").addClass('bs-price-right');
    });

    // Event To Show All Feature 
    BottomToggleMore.on('click', function(){
        // Active 
        $('.price.table-price').addClass('active');
        //animation Scroll                 
        if($(window).width() >= 991){
            body.stop().animate({scrollTop:(SectionPriceTop - 50) }, 500, 'swing');                
        }else{
            body.stop().animate({scrollTop:(SectionPriceTop) }, 500, 'swing');            
        }
        
    });
    // Event To Hide All Feature 
    // BottomShowLess.on('click', function(){
    //     $(this).parent().removeClass('active');            
    //     $(this).parent().prev().show();
    // });       
    // Function Remove All active Navigation
    function RemoveNavigation(){
        ListNavigation.removeClass('active');
    }
    function HiddenBoxPrice(){
        boxPricing.children().removeClass('active');
    }  
        // Start Two
        var Individual_Plans_table   = $('.table-price #individual_table, .table-price .mob-Slider .Individual-router'),
            Business_Plans_table     = $('.table-price #business_table, .table-price .mob-Slider .Business-router, .table-price  .mob-Slider .Business-router-right'),
            Platform_Plans_table    = $(' .table-price #platform_table, .table-price .mob-Slider .Platform-router'),            
            //router || Slider
            IndividualRouter   = $(' .table-price .mob-Slider .Individual-router'),
            BusinessRouter     = $(' .table-price .mob-Slider .Business-router'),
            BusinessRouterRight     = $(' .table-price .mob-Slider .Business-router-right'),
            PlatformRouter     = $(' .table-price .mob-Slider .Platform-router'),
            BoxRouter          = $(' .table-price .mob-Slider'),
            // boxPrice 
            PackagePriceOne    = $(' .pricing-package-one'),
            PackagePriceTwo    = $(' .pricing-package-two'),
            PackagePriceThree  = $(' .pricing-package-three'),            
            //table Toggle
            FeatureOne         = $(" .table-price .pricing-features .feature-one"),
            FeatureTwo         = $(" .table-price .pricing-features .feature-two"),
            FeatureThree       = $(" .table-price .pricing-features .feature-three"),

            ListNavigation     = $(' .list-navigation li'),
            boxPricing         = $(' .price .box-pricing, .pricing-features .header-table'),
            BoxFeaturePrice    = $(' .pricing-features .feature-price');        
        
        Individual_Plans_table.on('click', function() {
            RemoveNavigation();
            $(' .table-price #individual_table, #individual').addClass('active');
            HiddenBoxPrice();
            HiddenTablePrice();
            PackagePriceOne.add(FeatureOne).add(BusinessRouter).addClass('active');            
            BusinessRouter.addClass("bs-price-left");
        });
        Business_Plans_table.on('click', function() {
            console.log('test');
            RemoveNavigation();
            $(' .table-price #business_table, #business').addClass('active');
            HiddenBoxPrice();
            HiddenTablePrice();
            PackagePriceTwo.add(FeatureTwo).add(IndividualRouter).add(PlatformRouter).addClass('active');            
            BusinessRouter.removeClass("bs-price-left, bs-price-right");
            
        });
        Platform_Plans_table.on('click', function() {
            RemoveNavigation();
            $(' .table-price #platform_table,#platform ').addClass('active');
            HiddenBoxPrice();
            HiddenTablePrice();
            PackagePriceThree.add(FeatureThree).add(BusinessRouterRight).addClass('active');
            BusinessRouter.removeClass("bs-price-left").addClass('bs-price-right');
            
        });        
        // Function Remove All active Navigation
        function RemoveNavigation(){
            ListNavigation.removeClass('active');
        }
        function HiddenBoxPrice(){
            boxPricing.children().removeClass('active');
        }  
        function HiddenTablePrice(){
            BoxFeaturePrice.add(BoxRouter).children().removeClass('active');            
        }    
        /** Script Price Two **/

        $('body').on('change','.switch-years', function(){            
            console.log('dffd');
            if($(this).is(':checked')){
                //years
                $('.month-switch, .switch-price-years .monthly').removeClass('active');
                $('.years-switch, .switch-price-years .yearly').addClass('active');  
                $('.btn-box > .btn-primary.full.monthly').removeClass('active'); 
                $('.btn-box > .btn-primary.full.yearly').addClass('active'); 
                console.log('k.dfdf');
                // To Connect All Switch In Page                
                $('.switch-custom input').prop( "checked", true );
            }else{
                //mouth
                $('.month-switch, .switch-price-years .monthly').addClass('active');                
                $('.years-switch, .switch-price-years .yearly').removeClass('active');    
                $('.btn-box > .btn-primary.full.monthly').addClass('active'); 
                $('.btn-box > .btn-primary.full.yearly').removeClass('active');             
                // To Connect All Switch In Page 
                $('.switch-custom input').prop( "checked", false );
            }
        });
        $('.switch-price-years .monthly').on('click', function(){           
            $('.switch-custom input').prop( "checked", false );   
            //mouth
            $('.month-switch, .switch-price-years .monthly').addClass('active');                
            $('.years-switch, .switch-price-years .yearly').removeClass('active');   
            $('.btn-box > .btn-primary.full.monthly').addClass('active'); 
            $('.btn-box > .btn-primary.full.yearly').removeClass('active');              
            // To Connect All Switch In Page         
        })
        $(' .switch-price-years .yearly').on('click', function(){
            $('.switch-custom input').prop( "checked", true );
            //years
            $('.month-switch, .switch-price-years .monthly').removeClass('active');
            $('.years-switch, .switch-price-years .yearly').addClass('active');    
            $('.btn-box > .btn-primary.full.monthly').removeClass('active'); 
            $('.btn-box > .btn-primary.full.yearly').addClass('active');       
            // To Connect All Switch In Page                               
        });
});