<form action="<?php echo $submit_link;?>" id="ms-import-form" method="post">
    <div class="row">
        <div class="col-md-12">

            <?php if(isset($form_return_status)) { ?>
            <div class="alert alert-info">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <?php echo $form_return_status; ?>
            </div>

            <?php } ?>

            <input type="hidden" name="mapping_form" value="true">
            
            <?php foreach($unmatching_fields as $key => $field) { ?>

            <div class="form-group col-md-6" id="<?php echo $field; ?>-group">
                <label for="<?php echo $key ;?>" class="control-label"><?php echo $field; ?></label>
                <select name="product[<?php echo $key ;?>]" class="form-control">
                    <option value="0"><?php echo $not_supported_text; ?></option>

                    <?php foreach($upload_file_fields as $k => $f) { ?>

                    <option value="<?php echo $k; ?>" <?php foreach ($fields_uploaded_file as $i => $s) {
                        
                        if($i == $key and $s == $k) {
                            echo "selected";
                            break;
                        }
                        
                    }?>>
                        <?php echo $f;?>

                    </option>

                    <?php } ?>
                </select>
            </div>

            <?php } ?>
        </div>
    </div>
</form>
