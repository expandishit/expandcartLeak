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
    <?php if ($warning) { ?>
    <script>
        var notificationString = '<?php echo $warning; ?>';
        var notificationType = 'warning';
    </script>
    <?php } ?>
<div class="box">
<div class="heading">
    <h1><?php echo $heading_title; ?></h1>
    <div class="buttons">
	    <a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $this->language->get('button_save_configuration'); ?></a>
    </div>
</div>
<div class="content">
<div id="tabs" class="htabs">
	<a href="#tab-general"><?php echo $this->language->get('tab_general'); ?></a>
	<a href="#tab-display"><?php echo $this->language->get('tab_display'); ?></a>
	<a href="#tab-module-status" style="<?php echo (!$extensions['status'] ? 'color:red' : '')?>"><?php echo $this->language->get('Module Status'); ?></a>
</div>
<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
	<div id="tab-general">
	    <table class="form">
	        <tr>
	            <td><?php echo $this->language->get('text_opt_enabled_module'); ?></td>
	            <td>
		            <select name="rwp_enabled_module" id="rwp_enabled_module">
			            <option value="0" <?php echo ($rwp_enabled_module == "0" ? 'selected="selected"' : '')?>><?php echo $this->language->get('No'); ?></option>
			            <option value="1" <?php echo ($rwp_enabled_module == "1" ? 'selected="selected"' : '')?>><?php echo $this->language->get('Yes'); ?></option>
		            </select>
	            </td>
	        </tr>
		    <tr>
			    <td><?php echo $this->language->get('text_point_exchange_rate')?></td>
			    <td>
				    <input type="text" name="currency_exchange_rate" value="<?php echo $currency_exchange_rate?>"/>
				    <p class="note"><span><?php echo $this->language->get('text_tip_exchange_rate')?></span></p>
			    </td>
		    </tr>
            <tr>
                <td><?php echo $this->language->get('Update reward points when order status is'); ?></td>
                <td>
                    <select name="update_based_order_status[]" multiple style="width:223px; height:120px;">
                        <?php foreach ($order_statuses as $order_status) { ?>
                        <?php if (in_array($order_status['order_status_id'], $update_based_order_status)) { ?>
                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                        <?php } else { ?>
                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                        <?php } ?>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <!-- DISPATCH_EVENT:CONFIGURATION_AFTER_RENDER_GENERAL_FIELDS -->
	    </table>
	</div>
	<div id="tab-display">
		<table class="form">
			<tr>
                <td><?php echo $this->language->get('text_unit_name_point'); ?></td>
				<td>
                    <div id="tabs-point" class="htabs">
                        <?php foreach ($languages as $language) { ?>
                            <a data-value="<?php echo $language['language_id']; ?>" id="rwp_language_point" href="#language<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
                        <?php } ?>
                    </div>
                    <?php foreach ($languages as $language) { ?>
                        <?php $var = "text_points_".$language['code']; ?>
                        <div id="language<?php echo $language['language_id']; ?>">
                            <input type="text" name="text_points_<?php echo $language['code']; ?>" value="<?php echo ${$var}?>"/>
                        </div>
                    <?php } ?>
                </td>
			</tr>
            <tr>
                <td><?php echo $this->language->get('Show point on listing page'); ?></td>
                <td>
                    <select name="show_point_listing">
                        <option value="1" <?php echo ($show_point_listing == '1' ? 'selected="selected"' : '')?>><?php echo $this->language->get('Yes'); ?></option>
                        <option value="0" <?php echo ($show_point_listing == '0' ? 'selected="selected"' : '')?>><?php echo $this->language->get('No'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><?php echo $this->language->get('Show point on product detail page'); ?></td>
                <td>
                    <select name="show_point_detail">
                        <option value="1" <?php echo ($show_point_detail == '1' ? 'selected="selected"' : '')?>><?php echo $this->language->get('Yes'); ?></option>
                        <option value="0" <?php echo ($show_point_detail == '0' ? 'selected="selected"' : '')?>><?php echo $this->language->get('No'); ?></option>
                    </select>
                </td>
            </tr>
            <!-- DISPATCH_EVENT:CONFIGURATION_AFTER_RENDER_DISPLAY_FIELDS -->
		</table>
	</div>
    <div id="tab-module-status">
        <i style="margin: 0 10px;"><?php echo $this->language->get('text_config_guide1'); ?></i>
        <table class="form">
            <tr>
                <td><?php echo $this->language->get('text_config_allow_earn'); ?></td>
                <td><div style="background: <?php echo ($extensions['earn_point']['status'] ? 'green' : 'red')?>; width: 15px;height: 15px"></div></td>
                <td><?php echo $this->language->get('text_config_sort_order'); ?><b><?php echo $extensions['earn_point']['sort_order']?></b></td>
            </tr>
            <tr>
                <td><?php echo $this->language->get('text_config_allow_redeem'); ?></td>
                <td><div style="background: <?php echo ($extensions['redeem_point']['status'] ? 'green' : 'red')?>; width: 15px;height: 15px"></div></td>
                <td><?php echo $this->language->get('text_config_sort_order'); ?><b><?php echo $extensions['redeem_point']['sort_order']?></b><?php echo $this->language->get('text_config_note'); ?></td>
            </tr>
        </table>
    </div>
</form>
</div>
</div>
</div>

<script type="text/javascript"><!--
    $('#tabs a').tabs();
    $('#tabs-point a').tabs();
    //--></script>
<?php echo $footer; ?>
