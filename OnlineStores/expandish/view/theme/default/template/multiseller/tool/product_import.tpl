<!-- Start Form -->
<form action="<?php echo $submit_link;?>" id="ms-import-form" enctype="multipart/form-data" method="post">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-danger" style="display: none;">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <ul>
                </ul>
            </div>
        </div>

        <div class="col-md-12">
            <input type="hidden" name="import_method_name" value="product">

            <!-- File -->
            <div class="form-group col-md-6" id="import-group">
                <label for="import" class="control-label"><?php echo $text_products; ?></label>
                <input type="file" name="import" class="form-control" id="import">
                <span class="help-block"></span>
                <span class="text-muted"></span>
            </div>
            <!-- /File -->

            <!-- Language -->
            <div class="form-group col-md-6" id="language_id-group">
                <label for="language_id" class="control-label"><?php echo $entry_language; ?></label>
                <select name="language_id" id="language_id" class="form-control">
                    <option value="all"><?php echo $text_all;?></option>
                    <?php foreach($languages as $language) {?>
                    <option value="<?php echo $language['language_id'] ?>"
                        <?php echo isset($language_id) && $language_id ==  $language['language_id'] ? 'selected' : ''; ?>>
                        <?php echo $language['name'] ?></option>
                    <?php } ?>
                </select>
                <span class="help-block"></span>
            </div>
            <!-- /Language -->

            <!-- Option -->
            <div class="form-group col-md-6" id="option-group">
                <label for="input-option" class="control-label"><?php echo $entry_option;?></label>
                <select name="option" id="input-option" class="form-control">
                    <option value="0" <?php echo isset($option) && $option ==  0 ? 'selected' : ''; ?>>
                        <?php echo $text_no_options;?></option>
                    <option value="1" <?php echo isset($option) && $option ==  1 ? 'selected' : ''; ?>>
                        <?php echo $text_simple_options;?></option>
                    <option value="2" <?php echo isset($option) && $option ==  2 ? 'selected' : ''; ?>>
                        <?php echo $text_advanced_options;?></option>
                </select>
                <span class="help-block"></span>
            </div>
            <!-- /Option -->
        </div>
    </div>
</form>
