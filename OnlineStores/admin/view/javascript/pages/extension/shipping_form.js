var requiredSpanDom="<span class='required' style='color:red'>*</span>";

function fillFields( arrays )
{
    for (var i = arrays.length - 1; i >= 0; i--)
    {
        $( '#' + arrays[i].destID ).val( $('#' + arrays[i].sourceID).val() );
    }
}

$(document).ready(function ()
{

    // Saving wizard state
    $(".steps-state-saving").steps({
        headerTag: "h6",
        bodyTag: "fieldset",
        titleTemplate: '<span class="number">#index#</span> #title#',
        autoFocus: true,
        labels: {
            next: locales['btn_next'],
            previous: locales['btn_previous'],
            finish: locales['btn_finish']
        },
        onStepChanging: function (event, current, next) {
            if (current > next) {
                return true;
            }

            var $parent = $(".steps-state-saving fieldset:eq(" + current + ")");

            if (current == 0) {

                validation = syncValidation('personal', $parent);

                if ( validation == true )
                {
                    fillFields([
                    ]);
                }

                return validation;

            } else if (current == 1) {

                validation = syncValidation('', $parent);

                if ( validation == true )
                {
                    fillFields([
                    ]);
                }

                return validation;

            } else if (current == 2) {

                validation = syncValidation('', $parent);

                if ( validation == true )
                {
                    fillFields([
                    ]);
                }

                return validation;

            } else if (current == 3) {
                validation = syncValidation('', $parent);

                if ( validation == true )
                {
                    fillFields([
                    ]);
                }

                return validation;

            }

        },
        onFinished: function (event, currentIndex) {
            return submit();
        }
    });

     var switchery_after = document.querySelector('.switchery-after');
     if(isset(switchery_after)){
         var switchery = new Switchery(switchery_after);
     }

    $('select').select2();


});

function submit(){

}

function syncValidation($target, $parent) {
    state = true;
    $parent.find('.has-error').removeClass('has-error');
    // $.ajax({
    //     url: "",
    //     data:
    //         $parent.find('select,input,textarea,radio,checkbox').serialize(),
    //     method:
    //         'POST',
    //     dataType:
    //         'JSON',
    //     async:
    //         false,
    //     success:
    //
    //         function (response) {
    //
    //         }
    // });
    return state;
}

