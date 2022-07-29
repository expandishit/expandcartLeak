<div class="option-sku">
	<table class="list" id="optionValuesTable">
        <thead>
        <tr>
            <td>#</td>
            <?php foreach ($optionsHeaders as $oh) {?>
                <td><?php echo $oh ?></td>
            <?php } ?>
            <td>SKU</td>
            <td>{{ lang('ms_quantity') }}</td>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($optionsVariationValues as $key=>$ovv) {?>
            <?php $quantityError = false ?>
            <tr>
                <td>#</td>
                <?php $show_error_block = true; ?>
                <?php foreach ($ovv as $cKey=>$combination) { ?>
                    <td>
                        <?php if(isset($combination['input']) && $combination['input'] == true){ ?>
                            <input type="hidden"
                                   name="product_options_variations[<?php echo $key?>][parent_id]"
                                   value="<?php echo $combination['id']?>"/>
                            <?php if($combination['name'] == 'quantity' && $quantityError == true){ ?>
                                <input type="text"
                                       name="product_options_variations[<?php echo $key?>][<?php echo $combination['name']?>]"
                                       value="<?php echo $combination['value']?>" />
                                <span class="error"><?php echo $quantityError?></span>
                            <?php }else{ ?>
                                <input type="text"
                                       name="product_options_variations[<?php echo $key?>][<?php echo $combination['name']?>]"
                                       value="<?php echo $combination['value']?>" />
                            <?php } ?>
                        <?php }else{ ?>
                            <?php echo $combination['value']?>
                            <input type="hidden"
                                   name="product_options_variations[<?php echo $key?>][options][<?php echo $cKey?>]"
                                   value="<?php echo $combination['option_value_id']?>" />
                            <?php if($show_error_block){
                            $show_error_block = false;
                            ?>
                            <p class="error" id="error_option_sku_<?php echo $combination['option_value_id']?>"></p>
                            <?php } ?>
                        <?php } ?>

                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>