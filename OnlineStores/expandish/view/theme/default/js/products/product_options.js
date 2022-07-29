function updateURLQueryParams( params , product_id)
{
    if ( history.pushState )
    {

        var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?route=product/product&product_id='+product_id+'&';

        if ( params != undefined )
        {

            for ( i in params )
            {
                if ( params[i].key == undefined || params[i].value == undefined )
                {
                    params.splice(i, 1);
                }
            }

            for (var i = params.length - 1; i >= 0; i--)
            {
                newurl += params[i].key + '=' + params[i].value;

                if ( i > 0 )
                {
                    newurl += '&';
                }
            }
        }

        window.history.pushState({path:newurl},'',newurl);
    }
}
$(document).ready(function(){
    $(document).on('change', '.options select, .options input[type=radio]', function (e) {
        e.preventDefault();
        var product_id = $("#product_id").val();
        var form_data_array = $('.product-add-form').serializeArray();
        var datas = [];

        for ( i in form_data_array )
        {
            temp_obj = {};

            temp_obj.key = form_data_array[i].name;
            temp_obj.value = form_data_array[i].value;

            datas.push(temp_obj);
        }

        updateURLQueryParams(datas,product_id);
    });
});
