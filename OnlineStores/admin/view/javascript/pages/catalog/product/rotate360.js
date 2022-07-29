$(document).ready(function () {
    if($("#rotate360-dropzone").length){
    var url = 'common/filemanager/dropzone';

    var prevTemplate = $('#rotate360-template-preview').html();

    $('#rotate360-template-preview').remove();

    var rotate360Dropzone = new Dropzone("#rotate360-dropzone", {
        url: url,
        clickable: '.dropzone-upload-rotate360-image, .rotate360-dropzone',
        uploadMultiple: true,
        parallelUploads: 1,
        previewTemplate: prevTemplate
    });

    var tmpHolder;

    $("#rotate360-dropzone").sortable({
        items: '.rotate360-dz-preview',
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
                    var newName = productImage.attr('name').replace(/rotate360_image\[\d+]\[image]/, 'rotate360_image[' + idx + '][image]');
                }

                productImage.attr('name', newName);

                $(this).val(idx);

                if ($(this).attr('data-type') == 'option') {
                    var newSortName = this.name.replace(/\[images]\[\d+]\[srt]/g, '[images][' + idx + '][srt]');
                } else {
                    var newSortName = this.name.replace(/rotate360_image\[\d+]\[sort_order]/g, 'rotate360_image[' + idx + '][sort_order]');
                }
                $(this).attr('name', newSortName);
            });

        }
    });

    var imagesContainer = $('#dropzone-inputs');

    $('.rotate360-dropzone').css('min-height', '200px');
    $('.rotate360-dropzone').css('border', 'dashed 1px #ccc');

    for (imgId in rotate360Images) {
        var image = rotate360Images[imgId];
        console.log("IMAGE",image);
        var mockFile = {name: image['name'], size: image['size']};

        rotate360Dropzone.emit("addedfile", mockFile);

        rotate360Dropzone.emit("thumbnail", mockFile, image['thumb']);

        $('.rotate360-dz-preview:eq(' + imgId + ')').attr('data-filedata', JSON.stringify(image));
        $('.rotate360-dz-preview:eq(' + imgId + ')').addClass(image['class']);
        $('.rotate360-dz-preview:eq(' + imgId + ') .thumb-option-selector').attr('data-img-id', imgId);
        $('.rotate360-dz-preview:eq(' + imgId + ') .thumb-option-selector').attr('data-img-count', imagesCount);

        var imageInput = {
            'value': image['image'],
            'id': 'rotate360_image_' + imagesCount,
            'type': 'hidden',
            'class': 'rotate360-image'
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


            $('.rotate360-dz-preview:eq(' + imgId + ') .thumb-option-selector').attr('data-img-default', imgOption['value_id']);

            $('.rotate360-dz-preview:eq(' + imgId + ') .image-sort-order').attr('name', sortOrderInput);
            $('.rotate360-dz-preview:eq(' + imgId + ') .image-sort-order').attr('data-type', 'option');

            $('.rotate360-dz-preview:eq(' + imgId + ') .image-sort-order').val(imagesCount);

        } else {
            imageInput['name'] = 'rotate360_image[' + imagesCount + '][image]';
            imageInput['data-type'] = 'general';
            $('.rotate360-dz-preview:eq(' + imgId + ') .thumb-option-selector').attr('data-img-default', '-1');
            $('.rotate360-dz-preview:eq(' + imgId + ') .image-sort-order').val(image['sort_order']);

            $('.rotate360-dz-preview:eq(' + imgId + ') .image-sort-order').attr('name', 'rotate360_image[' + imagesCount + '][sort_order]');
            $('.rotate360-dz-preview:eq(' + imgId + ') .image-sort-order').attr('data-type', 'general');
        }

        $('.rotate360-dz-preview:eq(' + imgId + ') .thumb-option-selector').attr('data-target', '#rotate360_image_' + imagesCount);
        $('.rotate360-dz-preview:eq(' + imgId + ') .image-sort-order').attr('data-target', '#rotate360_image_' + imagesCount);

        imagesContainer.append($('<input>', imageInput));
        imagesContainer.append('<input type="hidden" class="rotate360_image" data-type="general" ' +
            'id="rotate360_image_' + imagesCount + '" name="rotate360_image[' + imagesCount + '][name]" ' +
            'value="' + image['name'] + '">');

        imagesCount++;

        uploadedFiles.push(image['image']);

        $('.rotate360-dz-preview:eq(' + imgId + ') .thumb-option-selector').select2({
            minimumResultsForSearch: Infinity,
            width: 150
        });
    }
    /**
     * Will check if the admin upload files for rotate360
     * if true it will uplaod the images in sub directory
     * for this product.
     */
    rotate360Dropzone.on("sending", function (file, xhr, formData) {
            formData.append("createProductDirectory", true);
            formData.append("product_id", $('#product_id').val());
    });

    rotate360Dropzone.on("success", function (file, resp) {
        console.log();
        $(file.previewElement).find('.image-sort-order').attr('name', 'rotate360_image[' + imagesCount + '][sort_order]');
        $(file.previewElement).find('.image-sort-order').val(imagesCount);
        $('.rotate360-dz-preview:eq(' + imagesCount + ') .image-sort-order').attr('name', 'rotate360_image[' + imagesCount + '][sort_order]');
        $('.rotate360-dz-preview:eq(' + imagesCount + ') .image-sort-order').attr('data-type', 'general');
        $('.rotate360-dz-preview:eq(' + imagesCount + ') .image-sort-order').attr('data-target', '#rotate360_image_' + imagesCount);
        $('.rotate360-dz-preview:eq(' + imagesCount + ') .image-sort-order').val(imagesCount);

        imagesContainer.append('<input type="hidden" class="rotate360-image" data-type="general" ' +
            'id="rotate360_image_' + imagesCount + '" name="rotate360_image[' + imagesCount + '][image]" ' +
            'value="' + resp.fileData.image + '">');
        imagesContainer.append('<input type="hidden" class="rotate360_image" data-type="general" ' +
            'id="rotate360_image_' + imagesCount + '" name="rotate360_image[' + imagesCount + '][name]" ' +
            'value="' + resp.fileData.name + '">');
        imagesCount++;

        $(file.previewElement).attr("file-data", JSON.stringify(resp.fileData));
        $(file.previewElement).addClass("preview_rotate360_image");
        $('.uploadHelp').slideDown("slow");

        uploadedFiles.push(resp.fileData.image);
    });

    rotate360Dropzone.on("removedfile", function (file) {
        var i = 0;

        var fileInfo = $(file.previewElement).data('filedata');

        for (i; i < uploadedFiles.length; i++) {
            if (uploadedFiles[i] == fileInfo.image) {
                $('#rotate360_image_' + i).remove();
            }
        }
    });

    window.addEventListener('ec:imagemanager:thumb-click', function (e) {

        var mockFile = {name: e.detail['fileName'], size: e.detail['fileSize']};

        rotate360Dropzone.emit("addedfile", mockFile);

        rotate360Dropzone.emit("thumbnail", mockFile, e.detail['fileThumb']);

        var tmpImagesCount = $('.rotate360-dz-image').length;
        tmpImagesCount = tmpImagesCount - 1;

        $('.rotate360-dz-preview:eq(' + tmpImagesCount + ') .thumb-option-selector').attr('data-img-id', tmpImagesCount);
        $('.rotate360-dz-preview:eq(' + tmpImagesCount + ') .thumb-option-selector').attr('data-img-count', tmpImagesCount);
        $('.rotate360-dz-preview:eq(' + tmpImagesCount + ') .thumb-option-selector').attr('data-target', '#rotate360_image_' + tmpImagesCount);

        $('.rotate360-dz-preview:eq(' + tmpImagesCount + ') .image-sort-order').attr('name', 'rotate360_image[' + tmpImagesCount + '][sort_order]');
        $('.rotate360-dz-preview:eq(' + tmpImagesCount + ') .image-sort-order').attr('data-type', 'general');
        $('.rotate360-dz-preview:eq(' + tmpImagesCount + ') .image-sort-order').attr('data-target', '#rotate360_image_' + tmpImagesCount);
        $('.rotate360-dz-preview:eq(' + tmpImagesCount + ') .image-sort-order').val(tmpImagesCount);


        imagesContainer.append('<input type="hidden" class="rotate360-image" data-type="general" ' +
            'id="rotate360_image_' + tmpImagesCount + '" name="rotate360_image[' + tmpImagesCount + '][image]" ' +
            'value="' + e.detail['filePath'] + '">');

        imagesContainer.append('<input type="hidden" class="rotate360-image" data-type="general" ' +
            'id="rotate360_image_' + tmpImagesCount + '" name="rotate360_image[' + tmpImagesCount + '][name]" ' +
            'value="' + e.detail['fileName'] + '">');
        imagesCount++;

    });
    }
});
