$(document).ready(function() {

        //For small flags inside card flag
        $('.existedClubFlagImage').change(function() {
                var selectFlagOne = $(this).find(':selected').data('flag');

                $('.fifa__card ul li:first-child img').attr('src', selectFlagOne);
        });

        $('.existedClubFlagImage2').change(function() {
                var selectFlagTwo = $(this).find(':selected').data('Secondflag');

                $('.fifa__card ul li:last-child img').attr('src', selectFlagTwo);
        });

        // Swap between Active state
        function swapActiveTab(element, sibil) {
                $(element).each(function() {
                        $(this).on('click', function() {
                                $(this).addClass('active')
                                       .siblings(sibil)
                                       .removeClass('active');
                        });
                });
        }
        swapActiveTab('.fif__form-radios label', 'label');
        swapActiveTab('.customtypeCtrls li', 'li');

        //Swtich active section
        function activeSection(element, dataCustom) {

                $(element).each(function() {
                        let customCtrlsVal = $(this).data(dataCustom);
        
                        $(this).on('click', function() {
                                console.log( $(customCtrlsVal) );
                                $(customCtrlsVal).fadeIn().siblings().fadeOut();
                        });
        
                });

        }
        activeSection('.customCtrls', 'custom');
        activeSection('.statCtrls', 'stat');
        activeSection('.flagCtrls', 'flag');
        activeSection('.clubCtrls', 'club');


        // File Function
        function openFileInput(fakeButton, realFileInput) {
                $(fakeButton).on('click', function() {
                        $(realFileInput).click();
                });
        }
        // Upload Your Image Section
        openFileInput('#upladYourImgWrape', '#inputGroupFile01');
        openFileInput('#uploadYourImgEddit', '#inputGroupFile01');
        
        openFileInput('#upladYourImgWrape2', '#inputGroupFile02');
        openFileInput('#uploadYourImgEddit2', '#inputGroupFile02');

        openFileInput('#upladYourImgWrape3', '#inputGroupFile03');
        openFileInput('#uploadYourImgEddit3', '#inputGroupFile03');


        // Any image to get its real path to replace
        function play(fileId, imgSrcReplaces, displayedUploadYourImgCtrls) {
                $(fileId).change(function() {  
                        readURL(this);    
                });
                
                function readURL(input) {    
                        if (input.files && input.files[0]) {   
                                var reader = new FileReader();
                                var filename = $(fileId).val();
                                filename = filename.substring(filename.lastIndexOf('\\')+1);
                                reader.onload = function(e) {
                                $(imgSrcReplaces).attr('src', e.target.result);          
                                }
                                reader.readAsDataURL(input.files[0]);   
                                $(displayedUploadYourImgCtrls).show().prev('div').hide();
                        } 
                }
        }
        // Upload Your Img Section
        play("#inputGroupFile01", ".imgReplaced", '.uploadYourImgCtrls');

        play("#inputGroupFile02", ".imgReplaced2", '.uploadYourImgCtrls2');

        play("#inputGroupFile03", ".imgReplaced3", '.uploadYourImgCtrls3');

        play("#inputGroupFile04", ".imgReplaced4", '.uploadYourImgCtrls4');



        // Remove Img Src
        function removeUploadedImgSrc(deleteBtn, imgParent, wraperFileInputToShow) {

                $(deleteBtn).on('click', function() {
                        $(this).closest(imgParent).find('img').removeAttr('src', ' ');
                        $(this).closest(wraperFileInputToShow).hide().prev('div').show();
                });

        }
        //Upload Your Image Section 
        removeUploadedImgSrc('#uploadYourImgDelete', 'ul', '.uploadYourImgCtrls');

        removeUploadedImgSrc('#uploadYourImgDelete2', 'ul', '.uploadYourImgCtrls2');
        
        removeUploadedImgSrc('#uploadYourImgDelete3', 'ul', '.uploadYourImgCtrls3');





        //===================================================================================
function showNextTab(nextBtn, closestParent, nextToParent, elementToShow) {
        $(nextBtn).on('click', function() {
                $(this).closest(closestParent)
                       .next(nextToParent)
                       .find(elementToShow).slideDown();

                $(this).closest(closestParent)
                       .find('.again').slideUp();

                $(this).fadeOut();
                console.log($(this).closest().attr('class'))
        });     
}   
//Open phase 2
showNextTab('#nextType', '.fif__form-size', '.fif__form-type', '.d-none');

//Open phase 3
showNextTab('#nextUpload', '.fif__form-type', 'div', '.file1.d-none');

//Open phase 4
showNextTab('#nextMain', '.fif__form-upload', '.fif__form-upload', '.d-none');

//Open phase 5
showNextTab('#nextStat', '.fif__form-upload', '.fif__form-state', '.d-none');

//Open phase 6
showNextTab('#nextReview', '.fif__form-state', '.fif__form-review', '.d-none');

//.customType2 Open phase 4
showNextTab('#nextStat2', '.customType2 .fif__form-player', '.customType2 .fif__form-stats', '.d-none');

showNextTab('#nextReview2', '.customType2 .fif__form-stats', '.customType2 .fif__form-review', '.d-none');
        //===================================================================================

        

$('#nextUpload').on('click', function() {
        if( $(this).prev('div').find('.customCtrls:last-child').hasClass('active') ) {
                $(this).closest('.fif__form').find('.customType2 .fif__form-player .form-group.d-none, .customType2 .fif__form-player .nextStep.d-none').slideDown();
        }
});


//==================================================================
function showEditBtn(nextBtn, closestParent, editBtn) {
        $(nextBtn).each(function() {
                $(this).on('click', function() {
                        
                        $(this).closest(closestParent)
                               .find(editBtn)
                               .fadeIn();
                        });     

                });
}   
showEditBtn('.nextStep', '.fif__form-size', '.dbt-none');
showEditBtn('.nextStep', '.fif__form-type', '.dbt-none');
showEditBtn('.nextStep', '.fif__form-upload', '.dbt-none');
showEditBtn('.nextStep', '.fif__form-upload', '.dbt-none');
showEditBtn('.nextStep', '.fif__form-state', '.dbt-none');
showEditBtn('.nextStep', '.customType2 .fif__form-player', '.dbt-none');
showEditBtn('.nextStep', '.customType2 .fif__form-stats', '.dbt-none');
//==================================================================

//When click on edit button show up its section again with next button
$('.dbt-none').each(function() {
        $(this).on('click', function() {
                $(this).closest('.head__div').next('div').slideDown().next('button').slideDown();
        });
});


//======================

$('.sourceOfVal').each(function() {
        
        $(this).keyup(function() {
                
                let sourceInput    = $(this).data('val');
        
                let sourceInputVal = $(this).val();
                

                $(sourceInput).html(sourceInputVal);

        
        });

});








        $('#basic, #basic2').flagStrap();

        $('.select-country').flagStrap({
                countries: {
                        "US": "USD",
                        "AU": "AUD",
                        "CA": "CAD",
                        "SG": "SGD",
                        "GB": "GBP",
                },
                buttonSize: "btn-sm",
                buttonType: "btn-info",
                labelMargin: "10px",
                scrollable: false,
                scrollableHeight: "350px"
        });
        
        $('#advanced').flagStrap({
                buttonSize: "btn-lg",
                buttonType: "btn-primary",
                labelMargin: "20px",
                scrollable: false,
                scrollableHeight: "350px"
        });

});




