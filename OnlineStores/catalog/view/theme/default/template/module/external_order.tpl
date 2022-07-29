<?php echo $header; ?>

<?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content">
    <?php echo $content_top; ?>

    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>
    <h1><?php echo $heading_title; ?></h1>
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
        <h2><?php echo $text_your_details; ?></h2>
        <div class="content">
            <table class="form">
                <tr>
                    <td><span class="required">*</span> <?php echo $entry_url; ?></td>
                    <td><input type="text" name="url" value="<?php echo $url; ?>" style="width: 100%;" />
                        <?php if ($error_url) { ?>
                        <span class="error"><?php echo $error_url; ?></span>
                        <?php } ?></td>
                </tr>
                <tr>
                    <td><span class="required">*</span> <?php echo $entry_name; ?></td>
                    <td><input type="text" name="name" value="<?php echo $name; ?>" />
                        <?php if ($error_name) { ?>
                        <span class="error"><?php echo $error_name; ?></span>
                        <?php } ?></td>
                </tr>
                <tr>
                    <td><span class="required">*</span> <?php echo $entry_category; ?></td>
                    <td><select name="category">
                            <option value=""><?php echo $text_select; ?></option>
                            <?php foreach ($categories as $categoryitem) { ?>
                            <?php if ($categoryitem['value'] == $category) { ?>
                            <option value="<?php echo $categoryitem['value']; ?>" selected="selected"><?php echo $categoryitem['text']; ?></option>
                            <?php } else { ?>
                            <option value="<?php echo $categoryitem['value']; ?>"><?php echo $categoryitem['text']; ?></option>
                            <?php } ?>

                            <?php } ?>
                        </select>
                        <?php if ($error_category) { ?>
                        <span class="error"><?php echo $error_category; ?></span>
                        <?php } ?></td>
                </tr>
                <tr>
                    <td><span class="required">*</span> <?php echo $entry_quantity; ?></td>
                    <td><input type="text" name="quantity" value="<?php echo $quantity; ?>" />
                        <?php if ($error_quantity) { ?>
                        <span class="error"><?php echo $error_quantity; ?></span>
                        <?php } ?></td>
                </tr>
                <tr>
                    <td><span class="required">*</span> <?php echo $entry_price; ?></td>
                    <td><input type="text" name="price" value="<?php echo $price; ?>" />
                        <?php if ($error_price) { ?>
                        <span class="error"><?php echo $error_price; ?></span>
                        <?php } ?></td>
                </tr>
                <tr>
                    <td><?php echo $entry_notes; ?></td>
                    <td><textarea rows="6" name="notes" style="width: 100%;"><?php echo $notes; ?></textarea></td>
                </tr>
                <tr>
                    <td><?php echo $entry_captcha; ?></td>
                    <td>
                    <input type="text" name="captcha" value="<?php echo $captcha; ?>" />
                    <br />
                    <img src="index.php?route=common/captcha" alt="" />
                    <?php if ($error_captcha) { ?>
                    <span class="error"><?php echo $error_captcha; ?></span>
                    <?php } ?></td>
                </tr>
            </table>
        </div>

        <div class="buttons">
            <div class="right">
                <input type="submit" value="<?php echo $button_submit; ?>" class="button" />
            </div>
        </div>
    </form>

    <?php echo $content_bottom; ?>
</div>

<?php echo $footer; ?>