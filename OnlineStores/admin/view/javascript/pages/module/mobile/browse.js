var $dth;

$(document).ready(() => {

    $('#form-submit').click((e) => {

        $('#form-submit').attr('disabled', true);

        $('.modal-errors').html('');

        let $data = $('#notification-form').serialize();

        $.ajax({
            url: links['authLink'],
            method: 'POST',
            dataType: 'JSON',
            data: $data,
            success: (response) => {

                $dth.ajax.reload();

                $('#new-notification').modal('toggle');

                $('#form-submit').attr('disabled', false);

            }
        });
    });

    var $types = [
        "All", "Android", "IOS"
    ];

    $('#details-modal').on('show.bs.modal', (e) => {

        let $this = $(e.currentTarget),
            $that = $(e.relatedTarget);

        $('.type', $this).html($types[$that.data('type')]);
        $('.body', $this).html($that.data('body'));
        // $('.topic', $this).html($that.data('topic'));
        $('.name', $this).html($that.data('name'));
        $('.title', $this).html($that.data('title'));
    });

    $dth = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        stateSave: true,
        autoWidth: false,
        dom: '<"datatable-header"f><"datatable-scroll"t><"datatable-footer"lip>',
        language: locales['dt_language'],
        lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        ajax: {
            url: links['dtHandler'],
            type: 'post',
            error: function (e, m, l) {
                $(".datatables_country-error").html("");
                $("#datatables_country").append('<tbody class="datatables_country-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                $("#datatables_country_processing").css("display", "none");
            }
        },
        select: {
            style: 'multi',
            selector: 'td:first-child'
        },
        order: [[1, "asc"]],
        columns: [
            {data: 'id'},
            {data: 'name'},
            /*{
                data: 'type',
                render: (d, t, r) => {
                    return $types[d];
                }
            },*/
            {
                data: 'body',
                render: (d, t, r) => {
                    return shortText = jQuery.trim(d).substring(0, 10).trim(this) + "...";
                }
            }
            /*{
                data: 'id',
                render: (d, t, r) => {
                    return `<a class="btn btn-info btn-sm" data-type="${r.type}"
                            data-body="${r.body}" data-topic="${r.topic}"
                            data-name="${r.name}" data-title="${r.title}"
                            data-toggle="modal" data-target="#details-modal">${locales['view_details']}</a>`;
                }
            }*/
        ]
    });

    let dataSelector = $('#data-selector-group');
    let productData = $('#product-data');
    let categoryData = $('#category-data');

    $('#data-selector').on('select2:select', function (_d) {
        let d = _d.params.data;
        let isv = $(d.element).data('has-attr');

        dataSelector.find('.hidable').hide();
        dataSelector.find('.n-fillable').val('').trigger('change');

        if (d.id == 'product') {
            productData.show();
        } else if (d.id == 'category') {
            categoryData.show();
        }
    });

    $('#product-id').on('select2:select', (_d) => {
        let d = _d.params.data;

        dataSelector.find('.n-fillable').val('');
        productData.find('.product_id').val(d.id);
        productData.find('.product_name').val(d.text);
        productData.find('.product_image').val(d.image);
    });

    $('#product-id').select2({
        dropdownParent: $("#new-notification"),
        ajax: {
            url: links['productsListUri'],
            dataType: 'JSON',
            delay: 500,
            data: (params) => {

                let q = {
                    filter_name: params.term
                };

                return q;

            },
            processResults: (data) => {

                var res = $.map(data, function (obj) {
                    return {
                        id: obj.product_id,
                        text: obj.name,
                        image: obj.image
                    };
                });

                return {
                    results: res
                };
            }
        },
    });

    $('#category-id').on('select2:select', (_d) => {
        let d = _d.params.data;

        dataSelector.find('.n-fillable').val('');
        categoryData.find('.category_id').val(d.id);
        categoryData.find('.category_name').val(d.text);
        categoryData.find('.category_image').val(d.image);
    });

    $('#category-id').select2({
        dropdownParent: $("#new-notification"),
        ajax: {
            url: links['categoriesListUri'],
            dataType: 'JSON',
            delay: 500,
            data: (params) => {

                let q = {
                    filter_name: params.term
                };

                return q;

            },
            processResults: (data) => {

                var res = $.map(data, function (obj) {
                    return {
                        id: obj.category_id,
                        text: obj.name,
                        image: obj.image
                    };
                });

                return {
                    results: res
                };
            }
        },
    });
});