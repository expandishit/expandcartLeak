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
    <div class="box">
        <div class="heading">
            <h1>{{ lang('heading_title_smsa') }}</h1>
            <div class="buttons">
                <?php if ($data['smsa_status'] != null) { ?>
                    <a href="<?= $viewOrders; ?>" class="button btn btn-primary">{{ lang('button_viewOrders') }}</a>
                <?php } ?>
                <a onclick="$('#form').submit();" class="button btn btn-primary">{{ lang('button_save') }}</a>
                <a href="<?php echo $cancel_url; ?>" class="button btn btn-primary">{{ lang('button_cancel') }}</a>
            </div>
        </div>
        <div class="content">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                <table class="form">
                    <tr>
                        <td colspan="2">
                            <b>{{ lang('client_information') }}</b>
                        </td>
                    </tr>

                    <tr>
                        <td><span class="required">*</span> {{ lang('api_url') }}</td>
                        <td>
                            <input type="text" name="smsa_wsdl" value="<?php echo $data['smsa_wsdl']; ?>" />
                        </td>
                    </tr>

                    <tr>
                        <td><span class="required">*</span> {{ lang('smsa_passkey') }}</td>
                        <td>
                            <input type="text" name="smsa_passkey" value="<?php echo $data['smsa_passkey']; ?>" />
                            <?php if ($errors['error_passkey']) { ?>
                            <span class="error"><?php echo $translatoin['error_passkey']; ?></span>
                            <?php } ?>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <span class="required">*</span> {{ lang('smsa_first15') }}
                            <span class="help">{{ lang('smsa_first15_note') }}</span>
                        </td>
                        <td>
                            <input type="text" name="smsa_first15" value="<?php echo $data['smsa_first15']; ?>" />
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <span class="required">*</span> {{ lang('smsa_after15') }}
                            <span class="help">{{ lang('smsa_after15_note') }}</span>
                        </td>
                        <td>
                            <input type="text" name="smsa_after15" value="<?php echo $data['smsa_after15']; ?>" />
                        </td>
                    </tr>

                    <tr>
                        <td>{{ lang('entry_status') }}</td>
                        <td>
                            <select name="smsa_status">
                                <?php if ($data['smsa_status'] === "0") { ?>
                                    <option value="1">{{ lang('text_enabled') }}</option>
                                    <option value="0" selected="selected">{{ lang('text_disabled') }}</option>
                                <?php } else { ?>
                                    <option value="1" selected="selected">{{ lang('text_enabled') }}</option>
                                    <option value="0">{{ lang('text_disabled') }}</option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td>{{ lang('entry_geo_zone') }}</td>
                        <td><select name="smsa_geo_zone_id">
                                <option value="0">{{ lang('text_all_zones') }}</option>
                                <?php foreach ($geo_zones as $geo_zone) { ?>
                                <?php if ($geo_zone['geo_zone_id'] == $data['smsa_geo_zone_id']) { ?>
                                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select></td>
                    </tr>

                    <tr>
                        <td>{{ lang('entry_sort_order') }}</td>
                        <td><input type="text" name="smsa_sort_order" value="<?php echo $data['smsa_sort_order']; ?>" size="1" /></td>
                    </tr>

                </table>
            </form>
        </div>
    </div>
</div>
<?php echo $footer; ?>