$(document).ready(function () {

    advancedattributeautocomplete();

    $('.advanced-attributes-group').click(function () {
    	$(this).children("option").prop("selected", "selected");
	});

    $("#advanced-attribute-autocomplete").on('select2:select', function (event) {
        var data = event.params.data;
        addAdvancedAttribute({
            'name': data['text'],
            'id': data['id'],
            'type': data['type'],
            'group_name': data['group_name']
        }, function (template) {
            $('#advancedAttributeTable tbody').append(template);
            $('#advancedAttributeTable').removeClass('hidden');
        });
    });

    $("#advanced-attribute-autocomplete").on('select2:unselect', function (event) {
        var data = event.params.data;

        $('#advanced-attribute-row-' + data.id).remove();

        if($('#advancedAttributeTable tr').length < 2){
            $('#advancedAttributeTable').addClass('hidden');
        }
    });

});




function advancedattributeautocomplete(row) {
    $("#advanced-attribute-autocomplete").select2({
        minimumResultsForSearch: 5,
        width: '100%',
        ajax: {
            url: links['advanced_attributes_autocomplete'] + '?filter_name=',
            dataType: 'json',
            type: 'GET',
            delay: 250,
            data: function (params) {
                return {
                    filter_name: params.term
                };
            },
            cache: true
        }
    });
}

function addAdvancedAttribute(data, callback) {

    html = '<tr id="advanced-attribute-row-' + data.id + '">';
    html += '<td class="col-md-4">';
    html += data['name'];
    html += '<input type="hidden" name="product_advanced_attribute[' + data.id + '][advanced_attribute_id]" ' +
        'value="' + data['id'] + '" />';
    html += '<input type="hidden" name="product_advanced_attribute[' + data.id + '][type]" ' +
        'value="' + data['type'] + '" />';
    html += '</td>';

    html += '<td class="col-md-2">';
    html += data['group_name'];
    html += '</td>';

    html += '<td class="col-md-4">';

    if(data['type'] == 'text'){
        html += '<div class="tabbable nav-tabs-vertical nav-tabs-right">' +
            '<div class="tab-content">';

        var first = true;

        for (lngId in languages) {

            var lng = languages[lngId];

            html += '<div class="tab-pane has-padding' + (first ? ' active' : '') + '" ' +
                'id="advancedAttributeLangTab-' + data.id + '-' + lng['language_id'] + '">' +
                '<div class="form-group" ' +
                'id="opt-name-group_' + lng['language_id'] + '">' +
                '<label class="control-label"></label>' +
                '<input type="text" class="form-control" ' +
                'name="product_advanced_attribute[' + data.id + '][product_attribute_description][' + lng['language_id'] + '][text]" />' +
                '<span class="help-block"></span>' +
                '<span class="text-muted"></span>' +
                '</div>' +
                '</div>';

            first = false;
        }
        html += '</div>' +
            '<ul class="nav nav-tabs nav-tabs-highlight nav-tabs-lang">';

        first = true;

        for (lngId in languages) {

            var lng = languages[lngId];

            html += '<li class="' + (first ? 'active' : '') + '">' +
                '<a href="#advancedAttributeLangTab-' + data.id + '-' + lng['language_id'] + '" data-toggle="tab" ' +
                'aria-expanded="false">' +
                '<img src="view/image/flags/' + lng['image'] + '" ' +
                'title="' + lng['name'] + '" class="pull-right">' +
                '<div> ' + lng['name'] + '</div>' +
                '</a>' +
                '</li>';

            first = false;
        }

        html += '</ul>' +
            '</div>';
    }
    else{
        if(data['type'] == 'multi_select'){
            html += '<select class="attribute-value-select" name="product_advanced_attribute[' + data['id'] + '][values][]" multiple>';
        }
        else if(data['type'] == 'single_select'){
            html += '<select class="attribute-value-select" name="product_advanced_attribute[' + data['id'] + '][values][]">';
        }

        $.ajax({
            url: links['get_advanced_attribute_values'] + '?advanced_attribute_id=' + data['id'] ,
            success: function(result){
                values = JSON.parse(result);

                for (let i = 0; i < values.length; i++) {
                    html += '<option value="' + values[i].id +'">'+ values[i].name +'</option>';
                }
            },
            async: false
        });
        html += '</select>';
    }


    html += '</td>';
    html += '<td class="col-md-2">' +
        '<a onclick="$(\'#advanced-attribute-row-' + data.id + '\').remove(); " ' +
        'class="button btn btn-danger"><i class="icon-trash"></i></a></td>';
    html += '</tr>';

    if (typeof callback != 'undefined' && typeof callback == 'function') {
        callback(html);
    }

    $('.attribute-value-select').select2({
        minimumResultsForSearch: 500
    });

    return html;
}
