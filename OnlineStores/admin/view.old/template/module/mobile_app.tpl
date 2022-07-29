<?php include_lib($header, $footer,'datatables'); ?>
<?php include_jsfile($header, $footer, 'view/template/module/mobile_app.tpl.js'); ?>

<?php echo $header; ?>
<div class="md-modal md-effect-11" id="modal-slide-href">
    <div class="md-content">
        <div class="modal-header emerald-bg">
            <button class="md-close close">×</button>
            <h4 class="modal-title"><?php echo $text_slide_link; ?></h4>
        </div>
        <div class="modal-body" data-srcId="">
            <div class="tabs-wrapper">
                <ul class="nav nav-tabs">
                    <li class="def-tab"><a href="#tab-product" data-toggle="tab"><?php echo $text_product; ?></a></li>
                    <li><a href="#tab-category" data-toggle="tab"><?php echo $text_category; ?></a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade def-tab-data" id="tab-product">
                        <table id="table-products" class="table table-hover">
                            <thead>
                            <tr>
                                <th></th>
                                <th><?php echo $text_name; ?></th>
                                <th><?php echo $text_model; ?></th>
                                <th><?php echo $text_price; ?></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="tab-category">
                        <table id="table-category" class="table table-hover">
                            <thead>
                            <tr>
                                <th></th>
                                <th><?php echo $text_name; ?></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="md-modal md-effect-11" id="modal-pick-plan">
    <div class="md-content">
        <div class="modal-header emerald-bg">
            <button class="md-close close">×</button>
            <h4 class="modal-title"><?php echo $text_choose_plans; ?></h4>
        </div>
        <div class="modal-body" data-srcId="">
            <span><?php echo $text_choose_plans_text; ?></span>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="sign-up" onclick="goToPackages();"><?php echo $text_explore_plans; ?></button>
        </div>
    </div>
</div>

<div class="row" id="content" style="<?php echo $hasMobileApp ? '' : 'display:none;'; ?>">
    <form role="form" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-12">
                    <ol class="breadcrumb">
                        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                        <?php if ($breadcrumb === end($breadcrumbs)) { ?>
                        <li class="active">
                            <?php } else { ?>
                        <li>
                            <?php } ?>
                            <a href="<?php echo $breadcrumb['href']; ?>">
                                <?php if ($breadcrumb === reset($breadcrumbs)) { ?>
                                <?php echo $breadcrumb['text']; ?>
                                <?php } else { ?>
                                <span><?php echo $breadcrumb['text']; ?></span>
                                <?php } ?>
                            </a>
                        </li>
                        <?php } ?>
                    </ol>

                    <div class="clearfix">
                        <h1 class="pull-left"><?php echo $heading_title; ?></h1>
                        <div class="pull-right top-page-ui">
                            <a class="btn btn-primary pull-right" onclick="$('#form').submit();">
                                <i class="fa fa-check-circle fa-lg"></i> <?php echo $text_save; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($error_warning) { ?>
            <script>
                var notificationString = '<?php echo $error_warning; ?>';
                var notificationType = 'warning';
            </script>
            <?php } ?>

            <div class="row" id="user-profile">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="main-box clearfix">
                        <header class="main-box-header clearfix">
                            <h2><?php echo $text_general_settings; ?></h2>
                        </header>

                        <div class="main-box-body clearfix">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label for="main_color"><?php echo $text_app_main_color; ?></label>
                                        <select class="form-control" name="mapp_main_color" id="main_color">
                                            <option <?php echo $mapp_main_color == "red" ? "selected='selected'" : ""; ?> value="red" style="color:#fff; background-color: #ff5252;"><?php echo $text_red; ?></option>
                                            <option <?php echo $mapp_main_color == "pink" ? "selected='selected'" : ""; ?> value="pink" style="color:#fff; background-color: #ff4081;"><?php echo $text_pink; ?></option>
                                            <option <?php echo $mapp_main_color == "purple" ? "selected='selected'" : ""; ?> value="purple" style="color:#fff; background-color: #e040fb;"><?php echo $text_purple; ?></option>
                                            <option <?php echo $mapp_main_color == "deep-purple" ? "selected='selected'" : ""; ?> value="deep-purple" style="color:#fff; background-color: #7c4dff;"><?php echo $text_deep_purple; ?></option>
                                            <option <?php echo $mapp_main_color == "indingo" ? "selected='selected'" : ""; ?> value="indingo" style="color:#fff; background-color: #536dfe;"><?php echo $text_indingo; ?></option>
                                            <option <?php echo $mapp_main_color == "blue" || $mapp_main_color == "" ? "selected='selected'" : ""; ?> value="blue" style="color:#fff; background-color: #448aff;"><?php echo $text_blue; ?></option>
                                            <option <?php echo $mapp_main_color == "light-blue" ? "selected='selected'" : ""; ?> value="light-blue" style="color:#fff; background-color: #40c4ff;"><?php echo $text_light_blue; ?></option>
                                            <option <?php echo $mapp_main_color == "cyan" ? "selected='selected'" : ""; ?> value="cyan" style="background-color: #18ffff;"><?php echo $text_cyan; ?></option>
                                            <option <?php echo $mapp_main_color == "teal" ? "selected='selected'" : ""; ?> value="teal" style="background-color: #64ffda;"><?php echo $text_teal; ?></option>
                                            <option <?php echo $mapp_main_color == "green" ? "selected='selected'" : ""; ?> value="green" style="background-color: #69f0ae;"><?php echo $text_green; ?></option>
                                            <option <?php echo $mapp_main_color == "light-green" ? "selected='selected'" : ""; ?> value="light-green" style="background-color: #b2ff59;"><?php echo $text_light_green; ?></option>
                                            <option <?php echo $mapp_main_color == "lime" ? "selected='selected'" : ""; ?> value="lime" style="background-color: #eeff41;"><?php echo $text_lime; ?></option>
                                            <option <?php echo $mapp_main_color == "yellow" ? "selected='selected'" : ""; ?> value="yellow" style="background-color: #ffff00;"><?php echo $text_yellow; ?></option>
                                            <option <?php echo $mapp_main_color == "amber" ? "selected='selected'" : ""; ?> value="amber" style="background-color: #ffd740;"><?php echo $text_amber; ?></option>
                                            <option <?php echo $mapp_main_color == "orange" ? "selected='selected'" : ""; ?> value="orange" style="background-color: #ffab40;"><?php echo $text_orange; ?></option>
                                            <option <?php echo $mapp_main_color == "deep-orange" ? "selected='selected'" : ""; ?> value="deep-orange" style="background-color: #ff6e40;"><?php echo $text_deep_orange; ?></option>
                                            <option <?php echo $mapp_main_color == "brown" ? "selected='selected'" : ""; ?> value="brown" style="background-color: #bcaaa4;"><?php echo $text_brown; ?></option>
                                            <option <?php echo $mapp_main_color == "grey" ? "selected='selected'" : ""; ?> value="grey" style="background-color: #eeeeee;"><?php echo $text_grey; ?></option>
                                            <option <?php echo $mapp_main_color == "blue-grey" ? "selected='selected'" : ""; ?> value="blue-grey" style="background-color: #b0bec5;"><?php echo $text_blue_grey; ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label for="logo_image"><?php echo $text_app_logo; ?></label>
                                        <div class="image">
                                            <img src="<?php echo $mapp_logo_thumb; ?>" alt="" id="logo_thumb" style="padding-bottom: 5px;" />
                                            <input type="hidden" name="mapp_logo_image" value="<?php echo $mapp_logo_image; ?>" id="logo_image" />
                                            <br />
                                            <div class="image-btns">
                                                <button name="logo_browse" type="button" class="btn btn-primary" onclick="image_upload('logo_image', 'logo_thumb');"><?php echo $text_browse; ?></button>
                                                <button type="button" class="btn btn-default" onclick="$('#logo_thumb').attr('src', '<?php echo $no_image; ?>'); $('#logo_image').attr('value', '');"><?php echo $text_clear; ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label for="store_slogan"><?php echo $text_store_slogan; ?></label>
                                    <input type="text" class="form-control" value="<?php echo $mapp_store_slogan; ?>" name="mapp_store_slogan" id="store_slogan" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="main-box clearfix">
                        <header class="main-box-header clearfix">
                            <h2><?php echo $text_category_settings; ?></h2>
                        </header>

                        <div class="main-box-body clearfix">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label for="available_category_txt"><?php echo $text_available_categories; ?></label>
                                        <input type="text" class="form-control" name="mapp_avacategory" value="" id="available_category_txt"/>
                                        <div id="ava-category" class="scrollbox">
                                            <?php foreach ($mapp_available_categories as $mapp_available_category) { ?>
                                            <div id="ava-category<?php echo $mapp_available_category['category_id']; ?>" class=""><?php echo $mapp_available_category['name']; ?> <i class="fa fa-times"></i>
                                                <input type="hidden" value="<?php echo $mapp_available_category['category_id']; ?>" />
                                            </div>
                                            <?php } ?>
                                        </div>
                                        <input type="hidden" name="mapp_ava_category" value="<?php echo $mapp_ava_category; ?>" />
                                    </div>
                                </div>
                        </div>
                    </div>
                    <div class="main-box clearfix">
                        <header class="main-box-header clearfix">
                            <h2><?php echo $text_about_us_settings; ?></h2>
                        </header>

                        <div class="main-box-body clearfix">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label for="info-page"><?php echo $text_source_information_page; ?></label>
                                        <select class="form-control" name="mapp_infopage" id="info-page">
                                            <option <?php echo $mapp_infopage == "0" || $mapp_infopage == "" ? "selected='selected'" : ""; ?> value="0"><?php echo $text_none_do_not_display; ?></option>
                                            <?php foreach ($info_pages as $info_page) { ?>
                                                <option <?php echo $mapp_infopage == $info_page['page_id'] ? "selected='selected'" : ""; ?> value="<?php echo $info_page['page_id']; ?>"><?php echo $info_page['page_name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="main-box clearfix">
                        <header class="main-box-header clearfix">
                            <h2><?php echo $text_home_page; ?></h2>
                        </header>

                        <div class="main-box-body clearfix">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label for="product-txt"><?php echo $text_featured_products; ?></label>
                                        <input type="text" class="form-control" name="mapp_product" value="" id="product-txt"/>
                                        <div id="featured-product" class="scrollbox">
                                            <?php foreach ($products as $product) { ?>
                                            <div id="featured-product<?php echo $product['product_id']; ?>" class=""><?php echo $product['name']; ?> <i class="fa fa-times"></i>
                                                <input type="hidden" value="<?php echo $product['product_id']; ?>" />
                                            </div>
                                            <?php } ?>
                                        </div>
                                        <input type="hidden" name="mapp_featured_product" value="<?php echo $mapp_featured_product; ?>" />
                                    </div>
                                    <div class="form-group">
                                        <label for="category-txt"><?php echo $text_featured_categories; ?></label>
                                        <input type="text" class="form-control" name="mapp_category" value="" id="category-txt"/>
                                        <div id="featured-category" class="scrollbox">
                                            <?php foreach ($categories as $category) { ?>
                                            <div id="featured-category<?php echo $category['category_id']; ?>" class=""><?php echo $category['name']; ?> <i class="fa fa-times"></i>
                                                <input type="hidden" value="<?php echo $category['category_id']; ?>" />
                                            </div>
                                            <?php } ?>
                                        </div>
                                        <input type="hidden" name="mapp_featured_category" value="<?php echo $mapp_featured_category; ?>" />
                                    </div>
                                    <div class="form-group">
                                        <label for="photo-gallery"><?php echo $text_mobile_slide_show; ?></label>
                                        <div id="gallery-photos-wrapper">
                                            <ul id="gallery-photos" class="clearfix gallery-photos gallery-photos-hover">
                                                <?php foreach ($slides as $slide) { ?>
                                                    <li id="recordsArray_<?php echo $slide['id']; ?>" class="col-md-3 col-sm-3 col-xs-6 slide">
                                                        <div class="photo-box" style="background-image: url('<?php echo $slide["thumbLink"]; ?>');"></div>
                                                        <a href="#" class="remove-photo-link" style="left: 35%;">
                                                                    <span class="fa-stack fa-lg">
                                                                        <i class="fa fa-circle fa-stack-2x"></i>
                                                                        <i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
                                                                    </span>
                                                        </a>
                                                        <a href="#" class="add-href-link md-trigger" data-modal="modal-slide-href" style="right: 35%;left: initial;">
                                                                    <span class="fa-stack fa-lg">
                                                                        <i class="fa fa-circle fa-stack-2x"></i>
                                                                        <i class="fa fa-link fa-stack-1x fa-inverse"></i>
                                                                    </span>
                                                        </a>
                                                        <input type="hidden" name="mapp_mobile_slides[image][]" value="<?php echo $slide["imgLink"]; ?>" />
                                                        <input type="hidden" class="href_type" name="mapp_mobile_slides[href_type][]" value="<?php echo $slide["href_type"]; ?>" />
                                                        <input type="hidden" class="href_id" name="mapp_mobile_slides[href_id][]" value="<?php echo $slide["href_id"]; ?>" />
                                                    </li>
                                                <?php } ?>
                                                <li id="add-more" class="col-md-3 col-sm-3 col-xs-6">
                                                    <div class="photo-box empty"></div>
                                                    <a href="#" class="add-photo-link">
                                                                <span class="fa-stack fa-lg">
                                                                    <i class="fa fa-circle fa-stack-2x"></i>
                                                                    <i class="fa fa-plus fa-stack-1x fa-inverse"></i>
                                                                </span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="first-promo"><?php echo $text_first_promotional_block; ?></label>
                                        <select class="form-control" name="mapp_firstpromo" id="first-promo">
                                            <option <?php echo $mapp_firstpromo == "none" || $mapp_firstpromo == "" ? "selected='selected'" : ""; ?> value="none"><?php echo $text_none_hide_this_block; ?></option>
                                            <option <?php echo $mapp_firstpromo == "latest" ? "selected='selected'" : ""; ?> value="latest"><?php echo $text_latest_products; ?></option>
                                            <option <?php echo $mapp_firstpromo == "special" ? "selected='selected'" : ""; ?> value="special"><?php echo $text_special_products; ?></option>
                                            <option <?php echo $mapp_firstpromo == "bestseller" ? "selected='selected'" : ""; ?> value="bestseller"><?php echo $text_best_selling_products; ?></option>
                                            <option <?php echo $mapp_firstpromo == "featured" ? "selected='selected'" : ""; ?> value="featured"><?php echo $text_featured_products; ?></option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="second-promo"><?php echo $text_second_promotional_block; ?></label>
                                        <select class="form-control" name="mapp_secondpromo" id="second-promo">
                                            <option <?php echo $mapp_secondpromo == "none" || $mapp_secondpromo == "" ? "selected='selected'" : ""; ?> value="none"><?php echo $text_none_hide_this_block; ?></option>
                                            <option <?php echo $mapp_secondpromo == "latest" ? "selected='selected'" : ""; ?> value="latest"><?php echo $text_latest_products; ?></option>
                                            <option <?php echo $mapp_secondpromo == "special" ? "selected='selected'" : ""; ?> value="special"><?php echo $text_special_products; ?></option>
                                            <option <?php echo $mapp_secondpromo == "bestseller" ? "selected='selected'" : ""; ?> value="bestseller"><?php echo $text_best_selling_products; ?></option>
                                            <option <?php echo $mapp_secondpromo == "featured" ? "selected='selected'" : ""; ?> value="featured"><?php echo $text_featured_products; ?></option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="mapp_footerinfo"><?php echo $text_footer_info; ?></label>
                                        <textarea class="form-control" name="mapp_footerinfo" id="footer-info" rows="3"><?php echo $mapp_footerinfo; ?></textarea>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php if (!$hasMobileApp) { ?>
<div class="row" id="promo">
    <div class="col-lg-12">
        <div class="row" id="user-profile">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="main-box no-header clearfix">
                    <div class="main-box-body clearfix promo-box">
                        <div class="col-lg-7 col-md-12 col-sm-12">
                            <h1 class="promo-heading"><?php echo $text_create_mobile_app_heading; ?></h1>
                            <br>
                            <ul class="fa-ul promo-features">
                                <li><i class="fa-li fa fa-check"></i><?php echo $text_getmobileapps; ?>
                                </li>
                                <li><i class="fa-li fa fa-check"></i><?php echo $text_mobile_apps_sales; ?>
                                </li>
                                <?php if (PRODUCTID == 6) { ?>
                                    <li><i class="fa-li fa fa-check"></i><?php echo $text_mobile_price_ultimate; ?></li>
                                <?php } else { ?>
                                    <li><i class="fa-li fa fa-check"></i><?php echo $text_simple_pricing; ?><span class="bold-promo-text"><?php echo $text_mobile_price; ?></span><?php echo $text_per_year; ?>
                                    </li>
                                <?php } ?>
                                <li><i class="fa-li fa fa-check"></i><?php echo $text_we_publish; ?>
                                </li>
                            </ul>
                            <br>
                            <button type="button" class="btn btn-primary promo-btn <?php echo $isTrial == "1" ? "md-trigger" : ""; ?>" data-modal='<?php echo $isTrial == "1" ? "modal-pick-plan" : ""; ?>' onclick="createMyApp();"><?php echo $text_create_my_app; ?></button>
                            <br>
                            <br>
                            <br>
                            <br>
                        </div>
                        <div class="col-lg-5 col-md-12 col-sm-12">
                            <div class="promo-image">
                            <img src="view/image/mobileapp/mobileshot.gif" class="main-image">
                            <h2 class="sub-text"><?php echo $text_try_app; ?></h2>
                            <a href="https://play.google.com/store/apps/details?id=com.expandcart.sampleshop"><img src="view/image/mobileapp/play-store-badge.png" class="sub-image"></a>
                            </div>
                        </div>
                        <br>
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<div class="md-overlay"></div><!-- the overlay element -->

<script type="text/javascript"><!--
    function image_upload(field, thumb) {
        $.startImageManager(field, thumb);
    };

    var products_grid_service = '<?php echo html_entity_decode($productsService); ?>';
    var category_grid_service = '<?php echo html_entity_decode($categoryService); ?>';

    function modalClose() {
        setTimeout(function () {$('#modal-slide-href').find(".active").removeClass("active").removeClass("in")}, 500);
    }

    function modalOpen() {
        $('#modal-slide-href').find(".def-tab").addClass("active");
        $('#modal-slide-href').find(".def-tab-data").addClass("active").addClass("in");
    }

    $(document).ready(function () {
        var overlay = $('.md-overlay');
        var modal = $('#modal-slide-href')
        var close = modal.find('.md-close');


        overlay.on( 'click', modalClose );
        overlay.on( 'click', modalClose );

        close.on( 'click', function( ev ) {
            ev.stopPropagation();
            modalClose();
        });

        //close on escape
        $(document).keyup(function(e) {
            if (e.keyCode == 27) {
                e.stopPropagation();
                modalClose();
            }
        });
    });
//--></script>

<script type="text/javascript"><!--
    $('input[name=\'mapp_product\']').autocomplete({
        delay: 500,
        source: function(request, response) {
            $.ajax({
                url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
                dataType: 'json',
                success: function(json) {
                    response($.map(json, function(item) {
                        return {
                            label: item.name,
                            value: item.product_id
                        }
                    }));
                }
            });
        },
        select: function(event, ui) {
            $('#featured-product' + ui.item.value).remove();

            $('#featured-product').append('<div id="featured-product' + ui.item.value + '">' + ui.item.label + '<i class="fa fa-times"></i><input type="hidden" value="' + ui.item.value + '" /></div>');

            $('#featured-product div:odd').attr('class', 'odd');
            $('#featured-product div:even').attr('class', 'even');

            data = $.map($('#featured-product input'), function(element){
                return $(element).attr('value');
            });

            $('input[name=\'mapp_featured_product\']').attr('value', data.join());

            $('input[name=\'mapp_product\']').val('');

            return false;
        },
        focus: function(event, ui) {
            return false;
        }
    });

    $(document).on('click', '#featured-product div i', function() {
        $(this).parent().remove();

        $('#featured-product div:odd').attr('class', 'odd');
        $('#featured-product div:even').attr('class', 'even');

        data = $.map($('#featured-product input'), function(element){
            return $(element).attr('value');
        });

        $('input[name=\'mapp_featured_product\']').attr('value', data.join());
    });

    $('input[name=\'mapp_category\']').autocomplete({
        delay: 500,
        source: function(request, response) {
            $.ajax({
                url: 'index.php?route=catalog/category/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
                dataType: 'json',
                success: function(json) {
                    response($.map(json, function(item) {
                        return {
                            label: item.name,
                            value: item.category_id
                        }
                    }));
                }
            });
        },
        select: function(event, ui) {
            $('#featured-category' + ui.item.value).remove();

            $('#featured-category').append('<div id="featured-category' + ui.item.value + '">' + ui.item.label + '<i class="fa fa-times"></i><input type="hidden" value="' + ui.item.value + '" /></div>');

            $('#featured-category div:odd').attr('class', 'odd');
            $('#featured-category div:even').attr('class', 'even');

            data = $.map($('#featured-category input'), function(element){
                return $(element).attr('value');
            });

            $('input[name=\'mapp_featured_category\']').attr('value', data.join());

            $('input[name=\'mapp_category\']').val('');

            return false;
        },
        focus: function(event, ui) {
            return false;
        }
    });

    $(document).on('click', '#featured-category div i', function() {
        $(this).parent().remove();

        $('#featured-category div:odd').attr('class', 'odd');
        $('#featured-category div:even').attr('class', 'even');

        data = $.map($('#featured-category input'), function(element){
            return $(element).attr('value');
        });

        $('input[name=\'mapp_featured_category\']').attr('value', data.join());
    });

    $('input[name=\'mapp_avacategory\']').autocomplete({
        delay: 500,
        source: function(request, response) {
            $.ajax({
                url: 'index.php?route=catalog/category/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
                dataType: 'json',
                success: function(json) {
                    response($.map(json, function(item) {
                        return {
                            label: item.name,
                            value: item.category_id
                        }
                    }));
                }
            });
        },
        select: function(event, ui) {
            $('#ava-category' + ui.item.value).remove();

            $('#ava-category').append('<div id="ava-category' + ui.item.value + '">' + ui.item.label + '<i class="fa fa-times"></i><input type="hidden" value="' + ui.item.value + '" /></div>');

            $('#ava-category div:odd').attr('class', 'odd');
            $('#ava-category div:even').attr('class', 'even');

            data = $.map($('#ava-category input'), function(element){
                return $(element).attr('value');
            });

            $('input[name=\'mapp_ava_category\']').attr('value', data.join());

            $('input[name=\'mapp_avacategory\']').val('');

            return false;
        },
        focus: function(event, ui) {
            return false;
        }
    });

    $(document).on('click', '#ava-category div i', function() {
        $(this).parent().remove();

        $('#ava-category div:odd').attr('class', 'odd');
        $('#ava-category div:even').attr('class', 'even');

        data = $.map($('#ava-category input'), function(element){
            return $(element).attr('value');
        });

        $('input[name=\'mapp_ava_category\']').attr('value', data.join());
    });
//--></script>

<script type="text/javascript"><!--
    $(function() {
        $('ul#gallery-photos').sortable({ opacity: 0.6, cursor: 'move', update: function() { }});
    });

    $(document).on('click', '#add-more', function () {
        $.startImageManager("null", "null", "addSlide");
        return false;
    });

    $(document).on('click', '.add-href-link', function () {
        modalOpen();

        $(".modal-body").data("srcId", $(this).parent().attr("id"));

        return false;
    });

    $(document).on('click', ".remove-photo-link", function () {
        $(this).parent().remove();
        return false;
    });

    function addSlide(target, thumb, fullLink, relativeLink, thumbLink) {
        var count = $('ul#gallery-photos li').length;
        $('#add-more').before("<li id='recordsArray_" + count + "' class='col-md-3 col-sm-3 col-xs-6 slide'>" +
                                         "<div class='photo-box' style=\"background-image: url('" + thumbLink + "');\"></div>" +

                                         "<a href='#' class='remove-photo-link' style='left: 35%;'>" +
                                            "<span class='fa-stack fa-lg'>" +
                                               "<i class='fa fa-circle fa-stack-2x'></i>" +
                                               "<i class='fa fa-trash-o fa-stack-1x fa-inverse'></i>" +
                                            "</span>" +
                                         "</a>" +

                                         "<a href='#' class='add-href-link md-trigger' data-modal='modal-slide-href' style='right: 35%;left: initial;'>" +
                                            "<span class='fa-stack fa-lg'>" +
                                                "<i class='fa fa-circle fa-stack-2x'></i>" +
                                                "<i class='fa fa-link fa-stack-1x fa-inverse'></i>" +
                                            "</span>" +
                                         "</a>" +

                                         "<input type='hidden' name='mapp_mobile_slides[image][]' value='" + relativeLink + "' />" +
                                         "<input type='hidden' class='href_type' name='mapp_mobile_slides[href_type][]' value='none' />" +
                                         "<input type='hidden' class='href_id' name='mapp_mobile_slides[href_id][]' value='-1' />" +

                                      "</li>");
    }

    function createMyApp() {
        var isTrial = "<?php echo $isTrial; ?>";

        if (isTrial == "0") {
            window.location = "<?php echo html_entity_decode($buylink); ?>";
        }
    }

    function goToPackages() {
        window.location = "<?php echo html_entity_decode($packageslink); ?>";
        return false;
    }
//--></script>

<style>
    #table-products.table tbody > tr > td {
        padding: 8px !important;
    }

    #table-products .tabs-wrapper .tab-content {
        margin-bottom: 0px !important;
    }

    #table-products.table {
        margin-bottom: 10px !important;
    }

    #table-category.table tbody > tr > td {
        padding: 8px !important;
    }

    #table-category .tabs-wrapper .tab-content {
        margin-bottom: 0px !important;
    }

    #table-category.table {
        margin-bottom: 10px !important;
    }

    .modal-body {
        max-height: calc(100vh - 110px);
        overflow-y: auto;
    }

    .dataTables_filter {
        margin-top: 2px !important;
        margin-bottom: 5px !important;
    }

    .dataTables_processing {
        line-height: 35px;
    }

    table#table-products tr {
        cursor: pointer;
    }

    table#table-category tr {
        cursor: pointer;
    }
</style>

<?php echo $footer; ?>
