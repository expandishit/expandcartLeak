<?php echo $header; ?>

<div id="content">
    <ol class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php if ($breadcrumb === end($breadcrumbs)) { ?>
        <li class="active">
            <?php } else { ?>
        <li>
            <?php } ?>
            <a href="<?php echo $breadcrumb['href']; ?>">
                <?php if ($breadcrumb === reset($breadcrumbs)) { ?>
                <?php echo $breadcrumb['text']; ?>
                <?php } else { ?>
                <span><?php echo $breadcrumb['text']; ?></span>
                <?php } ?>
            </a>
        </li>
        <?php } ?>
    </ol>
    <?php if ($error_warning) { ?>
    <script>
        var notificationString = '<?php echo $error_warning; ?>';
        var notificationType = 'warning';
    </script>
    <?php } ?>
    <?php if ($success) { ?>
    <script>
        var notificationString = '<?php echo $success; ?>';
        var notificationType = 'success';
    </script>
    <?php } ?>
    <div class="box">
        <div class="heading">
            <h1>{{ lang('related_products_heading_title') }}</h1>
            <div class="buttons">
                <a onclick="$('#form').submit();"
                   class="button btn btn-primary tab_settings">
                    <span>{{ lang('button_save') }}</span>
                </a>
                <a href="<?= $links['cancel']; ?>"
                   class="button btn btn-primary">
                    <span>{{ lang('button_cancel') }}</span>
                </a>
            </div>
        </div>
        <div class="content">
            <div class="tab-content">
                <form action="<?= $links['submit']; ?>" method="post" id="form">
                    <table class="table table-striped table-bordered">
                        <tr>
                            <td>{{ lang('entry_status') }} :</td>
                            <td>
                                <select class="form-control" name='related_products[rp_status]'>
                                    <option value='1'
                                    <?= ($settings['rp_status'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_enabled') }}</option>
                                    <option value='2'
                                    <?= ($settings['rp_status'] != '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_disabled') }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span>{{ lang('related_products_count') }}</span>
                                <span class="help">{{ lang('related_products_count_note') }}</span>
                            </td>
                            <td><input type="text" name="related_products[products_count]" value="<?= $settings['products_count']; ?>" /></td>
                        </tr>
                        <tr>
                            <td>
                                <span>{{ lang('related_products_source') }}</span>
                                <span class="help">{{ lang('related_products_source_note') }}</span>
                            </td>
                            <td>
                                <div>
                                    <input id="categories" type="checkbox" name="related_products[source][]"
                                           value="categories"
                                           <?= (in_array('categories', $settings['source'])) ? 'checked' : ''; ?>
                                    /> <label for="categories">{{ lang('es_source_categories') }}</label>
                                </div>
                                <div>
                                    <input id="manufacturers" type="checkbox" name="related_products[source][]"
                                           value="manufacturers"
                                           <?= (in_array('manufacturers', $settings['source'])) ? 'checked' : ''; ?>
                                    /> <label for="manufacturers">{{ lang('es_source_manufacturers') }}</label>
                                </div>
                                <div>
                                    <input id="tags" type="checkbox" name="related_products[source][]"
                                           value="tags"
                                           <?= (in_array('tags', $settings['source'])) ? 'checked' : ''; ?>
                                    /> <label for="tags">{{ lang('es_source_tags') }}</label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ lang('enable_random_products') }}
                                <span class="help">{{ lang('enable_random_products_note') }}</span>
                            </td>
                            <td>
                                <select class="form-control" name='related_products[enable_random]'>
                                    <option value='1'
                                    <?= ($settings['enable_random'] == '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_enabled') }}</option>
                                    <option value='2'
                                    <?= ($settings['enable_random'] != '1' ? 'selected' : ''); ?>
                                    >{{ lang('text_disabled') }}</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>
<script>

</script>
<?php echo $footer; ?>
