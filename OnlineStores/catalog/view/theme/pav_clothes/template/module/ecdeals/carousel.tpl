<?php
    $cols = isset($cols)?$cols:3;
    $span = 12/$cols; 
    $active = 'latest';
    $id = rand(1,9);
    $itemsperpage = isset($itemsperpage)?$itemsperpage:1;
    if(isset($deals) && $deals) {
?>
<style>
    .row {
        margin-left: -10px;
        margin-right: -10px;
        margin-top: 0px;
    }

    .caroufredsel_wrapper .name {
        margin-bottom: 16px;
    }

    .caroufredsel_wrapper .product-block {
        margin-bottom: 0px;
    }

    .caroufredsel_wrapper li.product-block {
        margin-top:20px !important;
    }

    .caroufredsel_wrapper .product-block:hover .price {
        display: none;
    }

    .caroufredsel_wrapper .product-block:hover .you-save {
        color: #3d3d3d !important;
    }

    .caroufredsel_wrapper .product-block .price {
        margin-bottom: 0px;
        margin-top: 0px;
        padding-bottom: 5px;
        padding-top: 10px;

    }

    .caroufredsel_wrapper div.bar_info {
        width: calc(100% - 20px);
        margin-right: 10px;
    }

    .caroufredsel_wrapper div#bar {
        width: calc(100% - 20px);
        padding-top: 0px;
    }

    .caroufredsel_wrapper div#bar span {
        height: 7px;
    }

    .product_discount, .product_discount_small {
        z-index: 999;
    }
</style>
<div class="<?php echo isset($prefix)?$prefix:"";?> box productcarousel highlighted">
    <div class="box-heading">
        <span><?php echo isset($heading_title)?$heading_title:""; ?></span>
    </div>
    <div class="box-content">
        <div class="box-products slide">
            <?php if( isset($module_description)) { ?>
            <div class="box-description"><p><?php echo $module_description;?></p></div>
            <?php } ?>

                <div class="carousel-controls">
                    <a id="ecprev<?php echo $id;?>" class="carousel-control left" href="#" data-slide="prev">‹</a>
                    <a id="ecnext<?php echo $id;?>" class="carousel-control right" href="#" data-slide="next">›</a>
                </div>

                <ul id="dealcarousel<?php echo $id;?>">
                    <?php foreach ($deals as  $k => $deal ) {   ?>
                    <li class="item<?php if($k==0) {?>active<?php } ?> product-block product-item" style="float: left;margin: 0 10px;">
                        <?php $is_expired = isset($deal['is_expired'])?$deal['is_expired']:false; ?>
                        <?php $module_id = $id.$deal['product_id']; ?>
                        <?php require("carousel_item.tpl");?>
                    </li>
                    <?php } ?>
                </ul>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(document).ready(function() {
        //  Scrolled by user interaction
                $('#dealcarousel<?php echo $id;?>').carouFredSel({
                    auto: <?php echo $carousel_auto == 1?'true':'false';?>,
                    prev: '#ecprev<?php echo $id;?>',
                    next: '#ecnext<?php echo $id;?>',
                    mousewheel: <?php echo $carousel_mousewhell == 1?'true':'false';?>,
                    responsive: <?php echo $carousel_responsive == 1?'true':'false';?>,
                    width: '100%',
                    scroll : {
                        items           : <?php echo $number_scroll; ?>,
                        fx              : '<?php echo (isset($scroll_effect) && $scroll_effect) ?$scroll_effect:'fade';?>',
                        duration        : <?php echo (isset($duration) && $duration) ?$duration:1000;?>,
                        pauseOnHover    : true
                    },
                    items: {
                        width: <?php echo $carousel_item_width;?>,
                        visible: {
                            min: <?php echo $carousel_min_items;?>,
                            max: <?php echo $carousel_max_items;?>
                        }
                    },
                    swipe: {
                        onMouse: true,
                        onTouch: true
                    }
                });
    });

</script>
<?php } ?>