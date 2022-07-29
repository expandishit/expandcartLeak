<!-- Start Form -->
<form action="<?php echo $submit_link;?>" id="ms-export-form">
    <div class="row">
        <div class="col-md-12">
            <!-- Language -->
            <div class="form-group col-md-6" id="filter_language-group">
                <label for="filter_language" class="control-label"><?php echo $entry_language; ?></label>
                <select name="filter_language" id="filter_language" class="form-control">
                    <option value="all"><?php echo $text_all;?></option>
                    <?php foreach($languages as $language) {?>
                    <option value="<?php echo $language['language_id'] ?>"
                        <?php echo isset($filter_language) && $filter_language ==  $language['language_id'] ? 'selected' : ''; ?>>
                        <?php echo $language['name'] ?></option>
                    <?php } ?>
                </select>
                <span class="help-block"></span>
            </div>
            <!-- /Language -->

            <!-- Categories -->
            <div class="form-group col-md-6" id="filter_categories-group">
                <label for="filter_categories" class="control-label"><?php echo $entry_categories;?></label>
                <select name="filter_categories" id="filter_categories" class="form-control">
                    <option value=""><?php echo $text_all?></option>
                    <?php foreach($categories as $category) {?>
                    <option value="<?php echo $category['category_id'];?>"
                        <?php echo isset($filter_categories) && $filter_categories ==  $category['category_id'] ? 'selected' : ''; ?>>
                        <?php echo $category['name'];?></option>
                    <?php }?>
                </select>
                <span class="help-block"></span>
            </div>
            <!-- /Categories -->

            <!-- Manufacturers -->
            <div class="form-group col-md-6" id="filter_manufacturer-group">
                <label for="filter_manufacturer" class="control-label"><?php echo $entry_manufacturer;?></label>
                <select name="filter_manufacturer" id="filter_manufacturer" class="form-control">
                    <option value=""><?php echo $text_all?></option>
                    <?php foreach($manufacturers as $manufacturer) {?>
                    <option value="<?php echo $manufacturer['manufacturer_id'];?>"
                        <?php echo isset($filter_manufacturer) && $filter_manufacturer ==  $manufacturer['manufacturer_id'] ? 'selected' : ''; ?>>
                        <?php echo $manufacturer['name']; ?>
                    </option>
                    <?php }?>
                </select>
                <span class="help-block"></span>
            </div>
            <!-- /Manufacturers -->

            <!-- Name -->
            <div class="form-group col-md-6" id="filter_name-group">
                <label for="filter_name" class="control-label"><?php echo $entry_product_name; ?></label>
                <input type="text" class="form-control" value="<?php echo $filter_name;?>" name="filter_name"
                    placeholder="<?php echo $entry_product_name; ?>" id="filter_name">
                <span class="help-block"></span>
            </div>
            <!-- /Name -->

            <!-- Model -->
            <div class="form-group col-md-6" id="filter_model-group">
                <label for="filter_model" class="control-label"><?php echo $entry_model;?></label>
                <input type="text" class="form-control" name="filter_model" value="<?php echo $filter_model?>"
                    placeholder="<?php echo $entry_model;?>" id="filter_model">
                <span class="help-block"></span>
            </div>
            <!-- /Model -->

            <!-- Price -->
            <div class="form-group col-md-6" id="filter_price-group">
                <label for="filter_price" class="control-label"><?php echo $entry_price;?></label>
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" name="filter_price_from" value="<?php echo $filter_price_from;?>"
                            placeholder="<?php echo $from_limit;?>" id="filter_price" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="filter_price_to"
                            value="<?php echo $filter_price_to;?>" placeholder="<?php echo $to_limit;?>"
                            id="filter_price_to" class="form-control">
                    </div>

                </div>
                <span class="help-block"></span>
            </div>
            <!-- /Price -->

            <!-- Quantity -->
            <div class="form-group col-md-6" id="filter_quantity-group">
                <label for="filter_quantity_from" class="control-label"><?php echo $entry_quantity;?></label>
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" name="filter_quantity_from" value="<?php echo $filter_quantity_from;?>"
                            placeholder="<?php echo $from_limit;?>" id="filter_quantity_from" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="filter_quantity_to" value="<?php echo $filter_quantity_to;?>"
                            placeholder="<?php echo $to_limit;?>" id="filter_quantity_to" class="form-control">
                    </div>
                </div>
                <span class="help-block"></span>
            </div>
            <!-- /Quantity -->

            <!-- Limit -->
            <div class="form-group col-md-6" id="filter_model-group">
                <label for="filter_model" class="control-label"><?php echo $entry_limit;?></label>
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" name="filter_start" value="<?php echo $filter_start;?>"
                            placeholder="<?php echo $from_limit;?>" id="input-start" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="filter_limit" value="<?php echo $filter_limit;?>"
                            placeholder="<?php echo $to_limit;?>" id="input-limit" class="form-control">
                    </div>
                </div>
                <span class="help-block"></span>
            </div>
            <!-- /Limit -->

            <!-- Stock Statuses -->
            <!-- <input type="hidden" name="filter_stock_status" value="*"> -->
            <!-- /Stock Statuses -->

            <!-- Status -->
            <div class="form-group col-md-6" id="filter_status-group">
                <label for="filter_status" class="control-label"><?php echo $entry_status;?></label>
                <select name="filter_status" id="filter_status" class="form-control">
                    <option value=""><?php echo $text_all;?></option>
                    <option value="1" <?php echo isset($filter_status) && $filter_status ==  1 ? 'selected' : ''; ?>>
                        <?php echo $text_enabled;?></option>
                    <option value="0" <?php echo isset($filter_status) && $filter_status ==  0 ? 'selected' : ''; ?>>
                        <?php echo $text_disabled;?></option>
                </select>
                <span class="help-block"></span>
            </div>
            <!-- /Status -->

            <!-- Option -->
            <div class="form-group col-md-6" id="option-group">
                <label for="input-option" class="control-label"><?php echo $entry_option;?></label>
                <select name="filter_option" id="input-option" class="form-control">
                    <option value="0" <?php echo isset($filter_option) && $filter_option ==  0 ? 'selected' : ''; ?>>
                        <?php echo $text_no_options;?></option>
                    <option value="1" <?php echo isset($filter_option) && $filter_option ==  1 ? 'selected' : ''; ?>>
                        <?php echo $text_simple_options;?></option>
                    <option value="2" <?php echo isset($filter_option) && $filter_option ==  2 ? 'selected' : ''; ?>>
                        <?php echo $text_advanced_options;?></option>
                </select>
                <span class="help-block"></span>
            </div>
            <!-- /Option -->

            <!-- File Format -->
            <input type="hidden" name="filter_file_format" value="expand_format">
            <!-- /File Format -->

            <!-- Image Path -->
            <div class="form-group col-md-6" id="image_path-group">
                <label for="input-image_path" class="control-label"><?php echo $entry_image_path;?></label>
                <select name="filter_image_path" id="input-image_path" class="form-control">
                    <option value="0"
                        <?php echo isset($filter_image_path) && $filter_image_path ==  0 ? 'selected' : ''; ?>>
                        <?php echo $text_absolute;?></option>
                    <option value="1"
                        <?php echo isset($filter_image_path) && $filter_image_path ==  1 ? 'selected' : ''; ?>>
                        <?php echo $text_relative;?></option>
                </select>
                <span class="help-block"></span>
            </div>
            <!-- /Image Path -->

            <!-- Format -->
            <div class="form-group col-md-6" id="filter_file_format-group">
                <label for="filter_file_format" class="control-label"><?php echo $entry_file_format;?></label>
                <select name="filter_eformat" class="form-control">
                    <option value="xls"
                        <?php echo isset($filter_eformat) && $filter_eformat ==  'xls' ? 'selected' : ''; ?>>XLS
                    </option>
                    <option value="csv"
                        <?php echo isset($filter_eformat) && $filter_eformat ==  'csv' ? 'selected' : ''; ?>>CSV
                    </option>
                    <option value="xlsx"
                        <?php echo isset($filter_eformat) && $filter_eformat ==  'xlsx' ? 'selected' : ''; ?>>XLSX
                    </option>
                </select>
                <span class="help-block"></span>
            </div>
            <!-- /Format -->

        </div>
    </div>
</form>
