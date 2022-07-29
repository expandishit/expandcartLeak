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
            <h1><?php echo $heading_title; ?></h1>
            <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><span><?php echo $button_save; ?></span></a><a href="<?php echo $cancel; ?>" class="button btn btn-primary"><span><?php echo $button_cancel; ?></span></a></div>
        </div>
        <div class="content">
            <div id="tabs" class="htabs"><a href="#tab-settings"><?php echo $tab_settings; ?></a><a href="#tab-account-register"><?php echo $tab_account_register; ?></a><a href="#tab-checkout-register"><?php echo $tab_checkout_register; ?></a><a href="#tab-checkout-guest"><?php echo $tab_checkout_guest; ?></a></div>
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                <div id="tab-settings">
                    <h2><?php echo $title_enable; ?></h2>
                    <table class="form">
                        <tr>
                            <td><?php echo $text_enable; ?></td>
                            <td><?php if ($config_autocomplete_address_enable) { ?>
                                <input type="radio" name="config_autocomplete_address_enable" value="1" checked="checked" />
                                <?php echo $text_yes; ?>
                                <input type="radio" name="config_autocomplete_address_enable" value="0" />
                                <?php echo $text_no; ?>
                                <?php } else { ?>
                                <input type="radio" name="config_autocomplete_address_enable" value="1" />
                                <?php echo $text_yes; ?>
                                <input type="radio" name="config_autocomplete_address_enable" value="0" checked="checked" />
                                <?php echo $text_no; ?>
                                <?php } ?></td>
                        </tr>
                    </table>
                </div>
                <div id="tab-account-register">
                    <table class="form">
                        <tr>
                            <td><?php echo $text_use_account_register; ?></td>
                            <td><?php if ($config_autocomplete_address_enable_ar) { ?>
                                <input type="radio" name="config_autocomplete_address_enable_ar" value="1" checked="checked" />
                                <?php echo $text_yes; ?>
                                <input type="radio" name="config_autocomplete_address_enable_ar" value="0" />
                                <?php echo $text_no; ?>
                                <?php } else { ?>
                                <input type="radio" name="config_autocomplete_address_enable_ar" value="1" />
                                <?php echo $text_yes; ?>
                                <input type="radio" name="config_autocomplete_address_enable_ar" value="0" checked="checked" />
                                <?php echo $text_no; ?>
                                <?php } ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $text_map; ?></td>
                            <td><?php if ($config_autocomplete_address_map_ar) { ?>
                                <input type="radio" name="config_autocomplete_address_map_ar" value="1" checked="checked" />
                                <?php echo $text_yes; ?>
                                <input type="radio" name="config_autocomplete_address_map_ar" value="0" />
                                <?php echo $text_no; ?>
                                <?php } else { ?>
                                <input type="radio" name="config_autocomplete_address_map_ar" value="1" />
                                <?php echo $text_yes; ?>
                                <input type="radio" name="config_autocomplete_address_map_ar" value="0" checked="checked" />
                                <?php echo $text_no; ?>
                                <?php } ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $text_map_size; ?>
                            <br />
                            <span class="help"><?php echo $text_map_default_ar; ?></span>
                            </td>
                            <td><input type="text" name="config_autocomplete_address_map_width_ar" value="<?php echo $config_autocomplete_address_map_width_ar; ?>" size="3" />
                              x
                              <input type="text" name="config_autocomplete_address_map_height_ar" value="<?php echo $config_autocomplete_address_map_height_ar; ?>" size="3" />
                        </tr>
                    </table>
                </div>
                <div id="tab-checkout-register">
                    <table class="form">
                        <tr>
                            <td><?php echo $text_use_checkout_register; ?></td>
                            <td><?php if ($config_autocomplete_address_enable_cr) { ?>
                                <input type="radio" name="config_autocomplete_address_enable_cr" value="1" checked="checked" />
                                <?php echo $text_yes; ?>
                                <input type="radio" name="config_autocomplete_address_enable_cr" value="0" />
                                <?php echo $text_no; ?>
                                <?php } else { ?>
                                <input type="radio" name="config_autocomplete_address_enable_cr" value="1" />
                                <?php echo $text_yes; ?>
                                <input type="radio" name="config_autocomplete_address_enable_cr" value="0" checked="checked" />
                                <?php echo $text_no; ?>
                                <?php } ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $text_map; ?></td>
                            <td><?php if ($config_autocomplete_address_map_cr) { ?>
                                <input type="radio" name="config_autocomplete_address_map_cr" value="1" checked="checked" />
                                <?php echo $text_yes; ?>
                                <input type="radio" name="config_autocomplete_address_map_cr" value="0" />
                                <?php echo $text_no; ?>
                                <?php } else { ?>
                                <input type="radio" name="config_autocomplete_address_map_cr" value="1" />
                                <?php echo $text_yes; ?>
                                <input type="radio" name="config_autocomplete_address_map_cr" value="0" checked="checked" />
                                <?php echo $text_no; ?>
                                <?php } ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $text_map_size; ?>
                            <br />
                            <span class="help"><?php echo $text_map_default_cr; ?></span>
                            </td>
                            <td><input type="text" name="config_autocomplete_address_map_width_cr" value="<?php echo $config_autocomplete_address_map_width_cr; ?>" size="3" />
                              x
                              <input type="text" name="config_autocomplete_address_map_height_cr" value="<?php echo $config_autocomplete_address_map_height_cr; ?>" size="3" />
                        </tr>
                    </table>
                </div>
                <div id="tab-checkout-guest">
                    <table class="form">
                        <tr>
                            <td><?php echo $text_use_checkout_guest; ?></td>
                            <td><?php if ($config_autocomplete_address_enable_cg) { ?>
                                <input type="radio" name="config_autocomplete_address_enable_cg" value="1" checked="checked" />
                                <?php echo $text_yes; ?>
                                <input type="radio" name="config_autocomplete_address_enable_cg" value="0" />
                                <?php echo $text_no; ?>
                                <?php } else { ?>
                                <input type="radio" name="config_autocomplete_address_enable_cg" value="1" />
                                <?php echo $text_yes; ?>
                                <input type="radio" name="config_autocomplete_address_enable_cg" value="0" checked="checked" />
                                <?php echo $text_no; ?>
                                <?php } ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $text_map; ?></td>
                            <td><?php if ($config_autocomplete_address_map_cg) { ?>
                                <input type="radio" name="config_autocomplete_address_map_cg" value="1" checked="checked" />
                                <?php echo $text_yes; ?>
                                <input type="radio" name="config_autocomplete_address_map_cg" value="0" />
                                <?php echo $text_no; ?>
                                <?php } else { ?>
                                <input type="radio" name="config_autocomplete_address_map_cg" value="1" />
                                <?php echo $text_yes; ?>
                                <input type="radio" name="config_autocomplete_address_map_cg" value="0" checked="checked" />
                                <?php echo $text_no; ?>
                                <?php } ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $text_map_size; ?>
                            <br />
                            <span class="help"><?php echo $text_map_default_cg; ?></span>
                            </td>
                            <td><input type="text" name="config_autocomplete_address_map_width_cg" value="<?php echo $config_autocomplete_address_map_width_cg; ?>" size="3" />
                              x
                              <input type="text" name="config_autocomplete_address_map_height_cg" value="<?php echo $config_autocomplete_address_map_height_cg; ?>" size="3" />
                        </tr>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript"><!--
    $('#tabs a').tabs();
    //--></script> 
<?php echo $footer; ?>