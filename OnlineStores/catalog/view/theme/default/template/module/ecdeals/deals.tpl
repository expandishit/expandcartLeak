<?php
    $active = 'latest';
    $id = rand(1,9);
    $itemsperpage = isset($itemsperpage)?$itemsperpage:1;
?>
<div class="<?php echo isset($prefix)?$prefix:"";?> box ecdeal">
    <h3 class="box-heading" style="    margin-bottom: 0px;"><span><?php echo isset($heading_title)?$heading_title:""; ?></span></h3>
    <div class="box-content">
        <div class="box-deals slide">
            <?php if( isset($module_description)) { ?>
            <div class="box-description"><?php echo $module_description;?></div>
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
                      <div class="row-fluid box-product">
                    <?php } ?>
                          <?php $is_expired = isset($deal['is_expired'])?$deal['is_expired']:false; ?>
                          <?php if (!$is_expired) { ?>
                        <div class="span-xs-12 span<?php echo $span;?> span-sm-<?php echo $small;?> span-mm-<?php echo $mini;?> product-item" style="    margin-top: 20px;">

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