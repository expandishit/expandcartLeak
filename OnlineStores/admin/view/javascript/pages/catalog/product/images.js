$(document).ready(function () {
    
    var url = 'common/filemanager/dropzone';

    var prevTemplate = $('#template-preview').html();

    $('#template-preview').remove();

    var productDropzone = new Dropzone("#product-dropzone", {
        url: url,
        clickable: '.dropzone-upload-image, .product-dropzone',
        uploadMultiple: true,
        parallelUploads: 1,
        previewTemplate: prevTemplate,
        autoDiscover: false
    });
    productDropzone.on("addedfile", function(file) {
        console.log(this.files.length, $('.dz-preview'))
        if($('.dz-preview').length > 0) {
            $('.drop-img-component').addClass('grid')
        } else {
            $('.drop-img-component').removeClass('grid');
        }
    });
    productDropzone.on("removedfile", function(file) {
        console.log(this.files.length, $('.dz-preview'))
        if($('.dz-preview').length > 0) {
            $('.drop-img-component').addClass('grid')
        } else {
            $('.drop-img-component').removeClass('grid');
        }
    });
    var tmpHolder;

    $("#product-dropzone").sortable({
        items: '.dz-preview',
        cursor: 'move',
        opacity: 0.5,
        containment: "parent",
        distance: 20,
        tolerance: 'pointer',
        start: function (e, ui) {
            var index = ui.item.index() - 1;
            tmpHolder = index;
        },
        stop: function (e, ui) {

            var inputs = $('input.image-sort-order');
            $('input.image-sort-order').each(function (idx) {

                var productImage = $($(this).attr('data-target'));

                if (productImage.attr('data-type') == 'option') {
                    var newName = productImage.attr('name').replace(/\[images]\[\d+]\[image]/, '[images][' + idx + '][image]');
                } else {
                    var newName = productImage.attr('name').replace(/product_image\[\d+]\[image]/, 'product_image[' + idx + '][image]');
                }

                productImage.attr('name', newName);

                $(this).val(idx);

                if ($(this).attr('data-type') == 'option') {
                    var newSortName = this.name.replace(/\[images]\[\d+]\[srt]/g, '[images][' + idx + '][srt]');
                } else {
                    var newSortName = this.name.replace(/product_image\[\d+]\[sort_order]/g, 'product_image[' + idx + '][sort_order]');
                }
                $(this).attr('name', newSortName);
            });

        }
    });

    var imagesContainer = $('#dropzone-inputs');

    $('.product-dropzone').css('min-height', '400px');
    $('.product-dropzone').css('border', 'dashed 1px #ccc');

    for (imgId in images) {
        var image = images[imgId];

        var mockFile = {name: image['name'], size: image['size']};

        productDropzone.emit("addedfile", mockFile);

        productDropzone.emit("thumbnail", mockFile, image['thumb']);

        $('.dz-preview:eq(' + imgId + ')').attr('data-filedata', JSON.stringify(image));
        $('.dz-preview:eq(' + imgId + ')').addClass(image['class']);
        $('.dz-preview:eq(' + imgId + ') .thumb-option-selector').attr('data-img-id', imgId);
        $('.dz-preview:eq(' + imgId + ') .thumb-option-selector').attr('data-img-count', imagesCount);

        var imageInput = {
            'value': image['image'],
            'id': 'product_image_' + imagesCount,
            'type': 'hidden',
            'class': 'product-image'
        };

        if (typeof image['isOption'] != 'undefined' && image['isOption'] == true) {

            var imgOption = image['option'];

            var inputName = ('product_option' +
                '[' + imgOption['id'] + ']' +
                '[product_option_value]' +
                '[' + imgOption['value_id'] + ']' +
                '[images]' +
                '[' + imagesCount + ']' +
                '[image]');

            var sortOrderInput = ('product_option' +
                '[' + imgOption['id'] + ']' +
                '[product_option_value]' +
                '[' + imgOption['value_id'] + ']' +
                '[images]' +
                '[' + imagesCount + ']' +
                '[srt]');

            imageInput['name'] = inputName;
            imageInput['data-type'] = 'option';


            $('.dz-preview:eq(' + imgId + ') .thumb-option-selector').attr('data-img-default', imgOption['value_id']);

            $('.dz-preview:eq(' + imgId + ') .image-sort-order').attr('name', sortOrderInput);
            $('.dz-preview:eq(' + imgId + ') .image-sort-order').attr('data-type', 'option');

            $('.dz-preview:eq(' + imgId + ') .image-sort-order').val(imagesCount);

        } else {
            imageInput['name'] = 'product_image[' + imagesCount + '][image]';
            imageInput['data-type'] = 'general';

            $('.dz-preview:eq(' + imgId + ') .thumb-option-selector').attr('data-img-default', '-1');
            $('.dz-preview:eq(' + imgId + ') .image-sort-order').val(image['sort_order']);

            $('.dz-preview:eq(' + imgId + ') .image-sort-order').attr('name', 'product_image[' + imagesCount + '][sort_order]');
            $('.dz-preview:eq(' + imgId + ') .image-sort-order').attr('data-type', 'general');
        }

        $('.dz-preview:eq(' + imgId + ') .thumb-option-selector').attr('data-target', '#product_image_' + imagesCount);
        $('.dz-preview:eq(' + imgId + ') .image-sort-order').attr('data-target', '#product_image_' + imagesCount);

        imagesContainer.append($('<input>', imageInput));

        imagesCount++;

        uploadedFiles.push(image['image']);

        $('.dz-preview:eq(' + imgId + ') .thumb-option-selector').select2({
            minimumResultsForSearch: Infinity,
            width: 150
        });
    }


    productDropzone.on("success", function (file, resp) {

        $('.dz-preview:eq(' + imagesCount + ') .thumb-option-selector').attr('data-img-id', imagesCount);
        $('.dz-preview:eq(' + imagesCount + ') .thumb-option-selector').attr('data-img-count', imagesCount);

        $('.dz-preview:eq(' + imagesCount + ') .thumb-option-selector').attr('data-target', '#product_image_' + imagesCount);

        $('.dz-preview:eq(' + imagesCount + ') .image-sort-order').attr('name', 'product_image[' + imagesCount + '][sort_order]');
        $('.dz-preview:eq(' + imagesCount + ') .image-sort-order').attr('data-type', 'general');
        $('.dz-preview:eq(' + imagesCount + ') .image-sort-order').attr('data-target', '#product_image_' + imagesCount);
        $('.dz-preview:eq(' + imagesCount + ') .image-sort-order').val(imagesCount);

        imagesContainer.append('<input type="hidden" class="product-image" data-type="general" ' +
            'id="product_image_' + imagesCount + '" name="product_image[' + imagesCount + '][image]" ' +
            'value="' + resp.fileData.image + '">');
        imagesContainer.append('<input type="hidden" class="product-image" data-type="general" ' +
            'id="product_image_' + imagesCount + '" name="product_image[' + imagesCount + '][name]" ' +
            'value="' + resp.fileData.name + '">');
        imagesCount++;

        $(file.previewElement).attr("file-data", JSON.stringify(resp.fileData));
        $(file.previewElement).addClass("preview_product_image");
        $('.uploadHelp').slideDown("slow");

        uploadedFiles.push(resp.fileData.image);

        thumbOptionSelectorBootstrap(file.previewElement);

        triggerImageOptionChange();

        /*for (optId in selectedOptionValues) {
            var selectedOptionValue = selectedOptionValues[optId];


        }*/
    });

    productDropzone.on("removedfile", function (file) {
        var i = 0;
        var fileInfo = $(file.previewElement).data('filedata');
        if (fileInfo === undefined) {
            fileInfo = JSON.parse($(file.previewElement).attr('file-data'))
        }

        for (i; i < uploadedFiles.length; i++) {
            if (uploadedFiles[i] == fileInfo.image) {
                $('#product_image_' + i).remove();
                // $.post('common/filemanager/delete', {path: [uploadedFiles[i]]}, function (data, status) {
                //     alert('file deleted');
                // });
            }
        }
    });

    window.addEventListener('ec:imagemanager:thumb-click', function (e) {

        var mockFile = {name: e.detail['fileName'], size: e.detail['fileSize']};

        productDropzone.emit("addedfile", mockFile);

        productDropzone.emit("thumbnail", mockFile, e.detail['fileThumb']);

        var tmpImagesCount = $('.dz-image').length;
        tmpImagesCount = tmpImagesCount - 1;

        $('.dz-preview:eq(' + tmpImagesCount + ') .thumb-option-selector').attr('data-img-id', tmpImagesCount);
        $('.dz-preview:eq(' + tmpImagesCount + ') .thumb-option-selector').attr('data-img-count', tmpImagesCount);
        $('.dz-preview:eq(' + tmpImagesCount + ') .thumb-option-selector').attr('data-target', '#product_image_' + tmpImagesCount);

        $('.dz-preview:eq(' + tmpImagesCount + ') .image-sort-order').attr('name', 'product_image[' + tmpImagesCount + '][sort_order]');
        $('.dz-preview:eq(' + tmpImagesCount + ') .image-sort-order').attr('data-type', 'general');
        $('.dz-preview:eq(' + tmpImagesCount + ') .image-sort-order').attr('data-target', '#product_image_' + tmpImagesCount);
        $('.dz-preview:eq(' + tmpImagesCount + ') .image-sort-order').val(tmpImagesCount);

        thumbOptionSelectorBootstrap($('.dz-preview:eq(' + tmpImagesCount + ')'));

        imagesContainer.append('<input type="hidden" class="product-image" data-type="general" ' +
            'id="product_image_' + tmpImagesCount + '" name="product_image[' + tmpImagesCount + '][image]" ' +
            'value="' + e.detail['filePath'] + '">');

        imagesContainer.append('<input type="hidden" class="product-image" data-type="general" ' +
            'id="product_image_' + tmpImagesCount + '" name="product_image[' + tmpImagesCount + '][name]" ' +
            'value="' + e.detail['fileName'] + '">');
        imagesCount++;

        triggerImageOptionChange();

    });
});

function thumbOptionSelectorBootstrap(selector) {
    $(".thumb-option-selector", selector).select2({
        minimumResultsForSearch: Infinity,
        width: 150
    });

    for (optId in optionValues) {

        for (optValId in allOptions[optId]['product_values']) {

            var optionValueData = allOptions[optId]['product_values'][optValId];

            var optionValueId = optionValueData['option_value_id'];

            if (
                typeof optionValues[optId] != 'undefined' &&
                typeof optionValues[optId][optValId] != 'undefined'
            ) {
                $(".thumb-option-selector", selector).append($('<option></option>', {
                    'text': allOptions[optId]['name'] + ' > ' + optionValues[optId][optValId]['name'],
                    'value': optionValueData['option_value_id'],
                    'class': 'thumb-option-selector-' + optionValueData['option_value_id'],
                    'data-option-id': optId,
                    'data-option-value-id': optValId,
                }));
            }
        }
    }

    for (optId in selectedOptionValues) {

        for (optValId in allOptions[optId]['values']) {

            var optionValueData = allOptions[optId]['values'][optValId];

            var option_value_id = optionValueData['option_value_id'];

            var optionValueId = optionValueData['option_value_id'];

            if (
                typeof selectedOptionValues[optId] != 'undefined' &&
                typeof selectedOptionValues[optId][option_value_id] != 'undefined'
            ) {
                newThumbOptionSelector({
                    selector: $(".thumb-option-selector", selector),
                    data: optionValueData,
                    optionId: optId,
                    optionValueId: option_value_id,
                    valueName: optionValueData['name'],
                    optionName: allOptions[optId]['name']
                });
            }
        }
    }
}