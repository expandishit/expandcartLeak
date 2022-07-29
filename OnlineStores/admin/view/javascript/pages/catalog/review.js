$(document).ready(function () {
    $('input[name=\'product\']').autocomplete({
        delay: 500,
        source: function(request, response) {
            $.getJSON("catalog/product/autocomplete", { filter_name: request.term }, function (json) {
                response($.map(json, function(item) {
                    return {
                        label: item.name,
                        value: item.product_id
                    }
                }));
            });
        },
        select: function(event, ui) {
            $('input[name=\'product\']').val(ui.item.label);
            $('input[name=\'product_id\']').val(ui.item.value);

            return false;
        },
        focus: function(event, ui) {
            return false;
        }
    });

    $('#submitForm').click(function () {

        var $form = $('#form');

        var $formData = $form.serialize();

        $.ajax({
            url: links['submit'],
            data: $formData,
            dataType: 'json',
            method: 'post',
            success: function (response) {

                if (response['status'] === 'fail') {
                    var errors = response['errors'];

                    for (error in errors) {
                        var $parent = $('#' + error).parents('.form-group');
                        $parent.addClass('has-error has-feedback');
                        $('.help-block', $parent).text(errors[error]);
                    }
                } else {
                    $('.modal').modal('hide');
                }

            }
        });
    });

    $('select').select2();
    $('.touchspinney').TouchSpin({
        'postfix': '{{ current_currency_code }}',
        'decimals': 2,
    });

    $(document).on('click', '.rating_star', function () {
        // Change Images
        $(this).nextAll().attr('src', 'view/image/rating_star_off.png');
        // Change Classes
        $(this).nextAll().addClass('rating_star_off');
        $(this).nextAll().removeClass('rating_star');
        // Change the rating values
        $(this).siblings('input').val($(this).attr('alt'));
        // $('input[name=rating]').val($(this).attr('alt'));
        $(this).siblings('.text-muted').find('.current_rating').text($(this).attr('alt'));
    });

    $(document).on('click', '.rating_star_off', function () {
        // Change Images
        $(this).attr('src', 'view/image/rating_star.png');
        $(this).prevAll().attr('src', 'view/image/rating_star.png');
        console.log($(this).siblings('.text-muted').find('.current_rating'))
        // Change Classes
        $(this).addClass('rating_star');
        $(this).removeClass('rating_star_off');
        $(this).prevAll().addClass('rating_star');
        $(this).prevAll().removeClass('rating_star_off');
        // Change the rating value
        $(this).siblings('input').val($(this).attr('alt'));
        // $('input[name=rating]').val($(this).attr('alt'));
        $(this).siblings('.text-muted').find('.current_rating').text($(this).attr('alt'));
    });
});