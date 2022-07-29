<?php echo $header; ?>

<style>
    .ms-heading {
        display: flex;
        flex-direction: row;
        align-items: center;
    }

    .ms-heading__title {
        flex: 1;
    }

    .ms-heading__actions a {
        color: #38B0E3;
        text-decoration: none;
        font-size: 12px;
        padding: 0px 0px 0px 7px;
    }

    .ms-heading__actions a+a {
        margin-left: 8px;
        /* border-left: 1px solid #CCC; */
    }

    .modal-dialog {
        position: relative;
        width: auto;
        margin: .5rem;
        pointer-events: none;
    }

    .modal-dialog-centered {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        min-height: calc(100% - (.5rem * 2));
    }

    .modal-title {
        display: inline-block;
    }

    .modal-header .close {
        margin: 0;
    }

    .modal-dialog {
        pointer-events: all;
    }

    .modal-content {
        min-width: 100%;
    }

    .ms-modal__loader-container {
        position: relative;
        display: block;
        width: 100%;
        height: 100%;
        margin-top: 20px;
        margin-bottom: 20px;
    }

    .ms-modal__loader {
        background-image: url(expandish/view/theme/default/image/spinners/spinner_1.gif);
        display: inline-block;
        width: 32px;
        height: 32px;
        background-repeat: no-repeat;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translateX(-50%) translateY(-30%);
    }

    @media screen and (min-width: 576px) {
        .modal-dialog {
            max-width: 500px;
            min-width: 500px;
            margin: 1.75rem auto;
        }

        .modal-dialog-centered {
            min-height: calc(100% - (1.75rem * 2));
            right: auto;
            left: auto;
        }
    }

    @media screen and (min-width: 992px) {
        .modal-lg {
            max-width: 900px;
            min-width: 900px;
        }
    }
</style>

<div id="content" class="ms-account-product">
    <?php echo $content_top; ?>

    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a
            href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>

    <div class="ms-heading">
        <h1 class="ms-heading__title"><?php echo $ms_account_products_heading; ?></h1>
        <div class="ms-heading__actions">
            <a href="" class="ms-heading__import" data-toggle="modal" data-target="#ms-import-modal">
                <?php echo $ms_account_products_heading_import ?: 'Import'; ?>
            </a>
            <a href="" class="ms-heading__export" data-toggle="modal" data-target="#ms-export-modal">
                <?php echo $ms_account_products_heading_export ?: 'Export'; ?>
            </a>
        </div>
    </div>

    <?php if (isset($error_warning) && ($error_warning)) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>

    <?php if (isset($success) && ($success)) { ?>
    <div class="success"><?php echo $success; ?></div>
    <?php } ?>

    <form id="search-form">
        <input type="search" name="search" placeholder="<?=$search_for_product?>" class="form-control"
            style="width:240px;display:inline" />
        <input type="submit" value="<?=$search?>" class="btn btn-primary" style="height:40px" />
    </form>
    <hr />

    <table class="list" id="list-products">
        <thead>
            <tr>
                <td><?php echo $ms_account_products_image; ?></td>
                <td><?php echo $ms_account_products_product; ?></td>
                <td><?php echo $ms_account_product_price; ?></td>
                <td><?php echo $ms_account_products_sales; ?></td>
                <td><?php echo $ms_account_products_earnings; ?></td>
                <td><?php echo $ms_account_products_status; ?></td>
                <td><?php echo $ms_account_products_date; ?></td>
                <td><?php echo $ms_account_products_listing_until; ?></td>
                <td class="large"><?php echo $ms_account_products_action; ?></td>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="buttons">
        <div class="left">
            <a href="<?php echo $link_back; ?>" class="button">
                <span><?php echo $button_back; ?></span>
            </a>
        </div>
        <div class="right">
            <?php if ($maximum_products > 0 && $total_seller_products < $maximum_products) { ?>
            <a href="<?php echo $link_create_product; ?>" class="button">
                <span><?php echo $ms_create_product; ?></span>
            </a>
            <?php } ?>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="ms-export-modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
        data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">
                        <?php echo $ms_products_export; ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="ms-modal__loader-container">
                        <div class="ms-modal__loader"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <?php echo $close; ?>
                    </button>
                    <button type="button" disabled class="btn btn-primary" id="ms-run-export">
                        <?php echo $export; ?>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- import Modal -->
    <div class="modal fade" id="ms-import-modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
        data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">
                        <?php echo $ms_products_import; ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="ms-modal__loader-container">
                        <div class="ms-modal__loader"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <?php echo $close; ?>
                    </button>
                    <button type="button" disabled class="btn btn-primary" id="ms-run-import">
                        <?php echo $import; ?>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <?php echo $content_bottom; ?>
</div>

<script>
    $(function () {
        $('#list-products').DataTable({
            "sDom": '<"wrapper"irlfptip>',
            "sAjaxSource": $('base').attr('href') + "index.php?route=seller/account-product/getTableData<?=isset($_GET['search']) ? '&search=' . $_GET['search'] : ''?>",
            "aoColumns": [
                { "mData": "image" },
                { "mData": "product_name" },
                { "mData": "product_price" },
                { "mData": "number_sold" },
                { "mData": "product_earnings" },
                { "mData": "product_status" },
                { "mData": "date_created" },
                { "mData": "list_until" },
                { "mData": "actions", "bSortable": false, "sClass": "right" }
            ],
            'iDisplayStart': '<?php echo $iDisplayStart; ?>',
        });

        $(document).on('click', '.ms-button-delete', function () {
            if (!confirm('<?php echo $ms_account_products_confirmdelete; ?>')) return false;
        });

        $("#search-form").on('submit', function (e) {
            e.preventDefault();
            window.location.href = $('base').attr('href') + "index.php?route=seller/account-product&search=" + this.search.value
        });

        // start export products
        $('#ms-export-modal').on('show.bs.modal	', function (e) {
            var formExist = $('#ms-export-form').length;
            if (!formExist) {
                var formParams = decodeURIComponent($.param($('#ms-export-form').serializeArray().filter(a => a.value.length && a.value != "*")));
                $.get($('base').attr('href') + 'index.php?route=seller/tool/product_export' + (formParams.length ? ('&' + formParams) : ''), function (html) {
                    $('#ms-export-modal .modal-body').html(html);
                    $('#ms-run-export').removeAttr('disabled');
                });
            } else {
                $('#ms-run-export').removeAttr('disabled');
            }
        });

        $("#ms-run-export").on('click', function (e) {
            e.preventDefault();
            $('#ms-run-export').attr('disabled', 1);
            var formParams = decodeURIComponent($.param($('#ms-export-form').serializeArray().filter(a => a.value.length && a.value != "*")));
            var fullPath = $('base').attr('href') + "index.php?route=seller/tool/product_export/export&" + formParams;
            $('#ms-export-modal').modal('hide');
            location = fullPath;
        });
        // end export products

        // start import products
        $('#ms-import-modal').on('show.bs.modal	', function (e) {
            $('#ms-import-modal .modal-title').text('<?php echo $ms_products_import;?>');
            $('#ms-import-modal .modal-body').html('<div class="ms-modal__loader-container"><div class="ms-modal__loader"></div></div>');
            $.get($('base').attr('href') + 'index.php?route=seller/tool/product_import', function (html) {
                $('#ms-import-modal .modal-body').html(html);
                $('#ms-run-import').removeAttr('disabled');
            });
        });

        $("#ms-run-import").on('click', function (e) {
            e.preventDefault();
            $('#ms-run-import').attr('disabled', 1);
            var form = $('#ms-import-form'), fd;

            // render upload file form
            if (!form.find('input[name=mapping_form]').length) {
                fd = new FormData();

                var files = form.find('[name=import]').get(0).files[0];
                fd.append('import', files);

                var language_id = form.find('[name=language_id]').get(0).value;
                fd.append('language_id', language_id);

                var option = form.find('[name=option]').get(0).value;
                fd.append('option', option);

                $.ajax({
                    type: "POST",
                    url: form.attr('action'),
                    xhr: function () {
                        var myXhr = $.ajaxSettings.xhr();
                        if (myXhr.upload) {
                            myXhr.upload.addEventListener('progress', function (event) {

                            }, false);
                        }
                        return myXhr;
                    },
                    success: function (data) {
                        data = JSON.parse(data);

                        if (data.error.length) {
                            data.error.forEach(function (errorMsg) {
                                $(form).find('.alert-danger ul').append('<li>' + errorMsg + '</li>');
                            });
                            $(form).find('.alert-danger').show();
                            $('#ms-run-import').removeAttr('disabled');
                        } else {
                            $(form).find('.alert-danger ul').html('');
                            $(form).find('.alert-danger').hide();

                            // start mapping form
                            $.get($('base').attr('href') + 'index.php?route=seller/tool/product_import/mapping_form', function (result) {
                                try {
                                    result = JSON.parse(result);
                                    location.reload();
                                } catch (error) {
                                    $('#ms-import-modal .modal-title').text('<?php echo $ms_mapping_products;?>');
                                    $('#ms-import-modal .modal-body').html(result);
                                    $('#ms-run-import').removeAttr('disabled');
                                }
                            });

                        }
                    },
                    error: function (error) {
                        // handle error
                    },
                    async: true,
                    data: fd,
                    cache: false,
                    contentType: false,
                    processData: false,
                    timeout: 60000
                });

            } else {
                // submit mapping form
                fd = $('#ms-import-form').serializeArray();

                $.ajax({
                    type: "POST",
                    url: form.attr('action'),
                    success: function (data) {
                        console.log(data);

                        if (data.status == 1) {
                            $('#ms-import-modal').modal('hide');
                            setTimeout(() => location.reload(), 0);
                        }
                    },
                    error: function (error) {
                        // handle error
                    },
                    data: fd,
                    dataType: 'json',
                });
            }

        });
    });
</script>
<?php echo $footer; ?>
