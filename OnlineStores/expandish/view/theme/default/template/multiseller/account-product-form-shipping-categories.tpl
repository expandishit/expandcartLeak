<?php if (!$msconf_allow_multiple_categories) { ?>

<select name="product_category">
    <option value=""><?php echo ''; ?></option>
    <?php foreach ($categories as $category) { ?>
    <?php if($msconf_enable_categories && $msconf_enable_shipping == 2) { ?>
    <?php if($product['shipping'] == 1 || $product['shipping'] == NULL) { ?>
    <?php if(in_array($category['category_id'],$msconf_physical_product_categories)) { ?>
    <option value="<?php echo $category['category_id']; ?>" <?php if (in_array($category['category_id'], explode(',',$product['category_id'])) && !$category['disabled']) { ?>selected="selected"<?php } ?> <?php echo ($category['disabled'] ? 'disabled' : ''); ?>><?php echo $category['name']; ?></option>
    <?php }} else { ?>
    <?php if(in_array($category['category_id'],$msconf_digital_product_categories)) { ?>
    <option value="<?php echo $category['category_id']; ?>" <?php if (in_array($category['category_id'], explode(',',$product['category_id'])) && !$category['disabled']) { ?>selected="selected"<?php } ?> <?php echo ($category['disabled'] ? 'disabled' : ''); ?>><?php echo $category['name']; ?></option>
    <?php }} ?>
    <?php } else { ?>
    <option value="<?php echo $category['category_id']; ?>" <?php if (in_array($category['category_id'], explode(',',$product['category_id'])) && !$category['disabled']) { ?>selected="selected"<?php } ?> <?php echo ($category['disabled'] ? 'disabled' : ''); ?>><?php echo $category['name']; ?></option>
    <?php }} ?>
</select>

<?php } else { ?>

<div class="product_category_block">
    <select name="product_category<?= $msconf_allow_multiple_categories ? '[]' : ''; ?>" <?= $msconf_allow_multiple_categories ? 'multiple' : '' ?> class="select2 form-control">
            <!-- <option value=""><?php echo ''; ?></option> -->
            <?php foreach ($categories as $category) { ?>
            <?php if($msconf_enable_categories && $msconf_enable_shipping == 2) { ?>
            <?php if($product['shipping'] == 1 || $product['shipping'] == NULL) { ?>
            <?php if(in_array($category['category_id'],$msconf_physical_product_categories)) { ?>
            <option value="<?php echo $category['category_id']; ?>" <?php if (in_array($category['category_id'], explode(',',$product['category_id'])) && !$category['disabled']) { ?>selected="selected"<?php } ?> <?php echo ($category['disabled'] ? 'disabled' : ''); ?>><?php echo $category['name']; ?></option>
            <?php }} else { ?>
            <?php if(in_array($category['category_id'],$msconf_digital_product_categories)) { ?>
            <option value="<?php echo $category['category_id']; ?>" <?php if (in_array($category['category_id'], explode(',',$product['category_id'])) && !$category['disabled']) { ?>selected="selected"<?php } ?> <?php echo ($category['disabled'] ? 'disabled' : ''); ?>><?php echo $category['name']; ?></option>
            <?php }} ?>
            <?php } else { ?>
            <option value="<?php echo $category['category_id']; ?>" <?php if (in_array($category['category_id'], explode(',',$product['category_id'])) && !$category['disabled']) { ?>selected="selected"<?php } ?> <?php echo ($category['disabled'] ? 'disabled' : ''); ?>><?php echo $category['name']; ?></option>
            <?php }} ?>
    </select>
</div>

<?php } ?>

<p class="ms-note"><?php echo $ms_account_product_category_note; ?></p>
<p class="error" id="error_product_category"></p>