<?php
    $active = 'latest';
    $id = rand(1,9);
    $itemsperpage = isset($itemsperpage)?$itemsperpage:1;
?>
<style>
    .row {
        margin-left: -10px;
        margin-right: -10px;
        margin-top: 0px;
    }

    .box-deals.slide .name {
        margin-bottom: 16px;
    }

    .box-deals .product-block:hover .price {
        display: none;
    }

    .box-deals .product-block:hover .you-save {
        color: #3d3d3d !important;
    }

    .box-deals .product-block .price {
        margin-bottom: 0px;
        margin-top: 0px;
        padding-bottom: 4px;
        padding-top: 10px;

    }

    .detail-deal div.bar_info {
        width: calc(100% - 20px);
        margin-right: 10px;
    }

    .detail-deal div#bar {
        width: calc(100% - 20px);
        padding-top: 0px;
    }

    .detail-deal div#bar span {
        height: 7px;
    }

    .product_discount, .product_discount_small {
        z-index: 999;
        padding-top: 15px;
    }
</style>
<div class="<?php echo isset($prefix)?$prefix:"";?> box ecdeal highlighted">
    <div class="box-heading">
        <span><?php echo isset($heading_title)?$heading_title:""; ?></span>
    </div>
    <div class="box-content">
        <div class="box-deals slide">
            <?php if( isset($module_description)) { ?>
            <div class="box-description"><p><?php echo $module_description;?></p></div>
            <?php } ?>
            <div id="list_product_item">
                 <?php
                  $cols = isset($cols)?$cols:3;
                  $cols_small = isset($cols_small)?$cols_small:2;
                  $cols_mini = isset($cols_mini)?$cols_mini:1;
                  $span = 12/$cols;
                  if ($cols == 5)
		            $span="2-5";
                  $small = floor(12/$cols_small);
                  $mini = floor(12/$cols_mini);
                  $class_button = 'btn-dailydeal';
                  ?>

                <?php
                $deals_active = array();
                foreach ($deals as $deal_active) {
                    $is_deal_expired = isset($deal_active['is_expired'])?$deal_active['is_expired']:false;

                    if (!$is_deal_expired) {
                        $deals_active[] = $deal_active;
                    }
                }
                ?>

                <?php foreach ($deals_active as  $i => $deal ) {   ?>
                     <?php if( $i++%$cols == 0 ) { ?>
                      <div class="row box-product">
                    <?php } ?>
                          <?php $is_expired = isset($deal['is_expired'])?$deal['is_expired']:false; ?>
                          <?php if (!$is_expired) { ?>
                        <div class="col-xs-12 col-md-<?php echo $span;?> col-sm-<?php echo $small;?> product-item" style="margin-top: 20px;">

                            <?php $module_id = $deal['product_id']; ?>
                            <?php require("carousel_item.tpl");?>
                        </div>
                          <?php } ?>
                         <?php if( $i%$cols == 0 || $i==count($deals_active) ) { ?>
                           </div>
                           <?php } ?>
                <?php } ?>
            </div>
     </div>
    </div>
</div>