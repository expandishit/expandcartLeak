<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" );
$themeConfig = $this->config->get('themecontrol');

$DISPLAY_MODE = 'grid';
if( isset($themeConfig['cateogry_display_mode']) ){
$DISPLAY_MODE = $themeConfig['cateogry_display_mode'];
}
$MAX_ITEM_ROW = 3;
if( isset($themeConfig['cateogry_product_row']) && $themeConfig['cateogry_product_row'] ){
$MAX_ITEM_ROW = $themeConfig['cateogry_product_row'];
}
$categoryPzoom = isset($themeConfig['category_pzoom']) ? $themeConfig['category_pzoom']:0;

?>
<?php echo $header; ?>
<div class="col-lg-12">
    <?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/breadcrumb.tpl" ); ?>
</div>
<div>
<?php if( $SPAN[0] ): ?>
<div class="col-lg-<?php echo $SPAN[0];?>">
    <?php echo $column_left; ?>
</div>
<?php endif; ?>
<div class="col-lg-<?php echo $SPAN[1];?>">
<div id="content"><?php echo $content_top; ?>

    <h1><?php echo $heading_title; ?></h1>
    <?php if ($products) { ?>
    <div class="product-filter">
        <div class="display hidden-xs">
            <span><?php echo $text_display; ?></span>
            <span><?php echo $text_list; ?></span>
            <a onclick="display('grid');"><span
                        class="glyphicon glyphicon-align-justify"></span><?php echo $text_grid; ?></a>
        </div>
        <div class="sort"><span class="hidden-text"><?php echo $text_sort; ?></span>
            <select onchange="location = this.value;">
                <?php foreach ($sorts as $sorts) { ?>
                <?php if ($sorts['value'] == $sort . '-' . $order) { ?>
                <option value="<?php echo $sorts['href']; ?>"
                        selected="selected"><?php echo $sorts['text']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $sorts['href']; ?>"><?php echo $sorts['text']; ?></option>
                <?php } ?>
                <?php } ?>
            </select>
        </div>
        <div class="limit"><span class="hidden-text"><?php echo $text_limit; ?></span>
            <select onchange="location = this.value;">
                <?php foreach ($limits as $limits) { ?>
                <?php if ($limits['value'] == $limit) { ?>
                <option value="<?php echo $limits['href']; ?>"
                        selected="selected"><?php echo $limits['text']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $limits['href']; ?>"><?php echo $limits['text']; ?></option>
                <?php } ?>
                <?php } ?>
            </select>
        </div>
       <div class="product-compare"><a href="<?php echo $compare; ?>"
                                        id="compare-total">
                <span class="hidden-xs"><?php echo $text_compare; ?></span><em class="visible-xs icon-retweet"></em>
            </a>
        </div>    </div>

    <div class="product-list">

        <div class="row">
            <div class="brand-wrap products-block">
                <?php
                    $cols = $MAX_ITEM_ROW ;
                        $span = floor(12/$cols);
                    foreach ($products as $i => $product) { ?>
                <?php if( $i++%$cols == 0 ) { ?>
                <div class="row row-product">
                    <?php } ?>
                    <div class="col-lg-<?php echo $span;?>">
                        <div class="product-block">

                            <?php if ($product['thumb']) { ?>
                            <div class="image"><?php if( $product['special'] ) {   ?>
                                <span class="product-label-special label"><?php echo $this->
                                    language->get( 'text_sale' ); ?></span>
                                    <?php } ?>
                                    <a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>"
                                                                                   title="<?php echo $product['name']; ?>"
                                                                                   alt="<?php echo $product['name']; ?>"/></a>
                                    <?php if( $categoryPzoom ) { $zimage = str_replace( "cache/","", preg_replace("#-\d+x\d+#", "",  $product['thumb'] ));  ?>
                                    <a href="<?php echo $zimage;?>" class="colorbox product-zoom" rel="colorbox"
                                       title="<?php echo $product['name']; ?>"><span class="icon-zoom-in"></span></a>
                                    <?php } ?>
                            </div>
                            <?php } ?>
                            <div class="product-meta">
                                <h3 class="name"><a href="<?php echo $product['href']; ?>"><?php echo utf8_substr( strip_tags($product['name']),0,17);?>...</a>
                                </h3>

                                <div class="description"><?php echo utf8_substr( strip_tags($product['description']),0,60);?>...
                                </div>
                                <?php if ($product['rating']) { ?>
                                    <div class="rating"><img
                                            src="catalog/view/theme/<?php echo $this->config->get('config_template');?>/image/stars-<?php echo $product['rating']; ?>.png"
                                            alt="<?php echo $product['reviews']; ?>"/></div>
                                <?php } else { ?>
                                <div class="norating"></div>
                                <?php } ?>

                                <div class="group-item">
                                    <div class="price-cart">
                                        <?php if ($product['price']) { ?>
                                        <div class="price">
                                            <?php if (!$product['special']) { ?>
                                            <?php echo $product['price']; ?>
                                            <?php } else { ?>
                                            <span class="price-old"><?php echo $product['price']; ?></span> <span
                                                    class="price-new"><?php echo $product['special']; ?></span>
                                            <?php } ?>
                                            <?php if ($product['tax']) { ?>
                                            <br/>
                                            <span class="price-tax"><?php echo $text_tax; ?> <?php echo $product['tax']; ?></span>
                                            <?php } ?>
                                        </div>
                                        <?php } ?>

                                        <div class="cart">
                                            <input type="button" value="<?php echo $button_cart; ?>"
                                                   onclick="addToCart('<?php echo $product['product_id']; ?>');"
                                                   class="button"/>
                                        </div>
                                    </div>
                                    </div>
                                <span class="wishlist"><a class="icon-heart" onclick="addToWishList('<?php echo $product_id; ?>');"
                                                          data-placement="top" data-toggle="tooltip"
                                                          data-original-title="<?php echo $button_wishlist; ?>"><span><?php echo $button_wishlist; ?></a></span></span>
                                <span class="compare"><a class="icon-retweet" onclick="addToCompare('<?php echo $product_id; ?>');"
                                                     data-placement="top" data-toggle="tooltip"
                                                     data-original-title="<?php echo $button_compare; ?>"><span><?php echo $button_compare; ?></a></span></span>
                            </div>
                        </div>
                    </div>
                    <?php if( $i%$cols == 0 || $i==count($products) ) { ?>
                </div>
                <?php } ?>

                <?php } ?>
            </div>
        </div>
    </div>

    <div class="pagination"><?php echo $pagination; ?></div>
    <?php } else { ?>
    <div class="content"><?php echo $text_empty; ?></div>
    <div class="buttons">
        <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
    </div>
    <?php }?>
    <?php echo $content_bottom; ?></div>
<script type="text/javascript"><!--
    function display(view) {
        if (view == 'list') {
            $('.product-grid').attr('class', 'product-list');

            $('.products-block  .product-block').each(function (index, element) {

                $(element).parent().addClass("col-fullwidth");
            });

            $('.display').html('<span style="float: left;"><?php echo $text_display; ?></span><a class="list active"><span class="glyphicon glyphicon-align-justify"></span><em><?php echo $text_list; ?></em></a><a class="grid"  onclick="display(\'grid\');"><span class="glyphicon glyphicon-th"></span><em><?php echo $text_grid; ?></em></a>');

            $.totalStorage('display', 'list');
        } else {
            $('.product-list').attr('class', 'product-grid');

            $('.products-block  .product-block').each(function (index, element) {
                $(element).parent().removeClass("col-fullwidth");
            });

            $('.display').html('<span style="float: left;"><?php echo $text_display; ?></span><a class="list" onclick="display(\'list\');"><span class="glyphicon glyphicon-align-justify"></span><em><?php echo $text_list; ?></em></a><a class="grid active"><span class="glyphicon glyphicon-th"></span><em><?php echo $text_grid; ?></em></a>');

            $.totalStorage('display', 'grid');
        }
    }

    view = $.totalStorage('display');

    if (view) {
        display(view);
    } else {
        display('<?php echo $DISPLAY_MODE;?>');
    }
    //--></script>
<?php if( $categoryPzoom ) {  ?>
<script type="text/javascript"><!--
    $(document).ready(function () {
        $('.colorbox').colorbox({
            overlayClose: true,
            opacity: 0.5,
            rel: false,
            onLoad: function () {
                $("#cboxNext").remove(0);
                $("#cboxPrevious").remove(0);
                $("#cboxCurrent").remove(0);
            }
        });

    });
    //--></script>
<?php } ?>
</section>

<?php if( $SPAN[2] ): ?>
<aside class="col-lg-<?php echo $SPAN[2];?> col-sm-<?php echo $SPAN[2];?> col-xs-12">
    <?php echo $column_right; ?>
</aside>
<?php endif; ?>

<?php echo $footer; ?>