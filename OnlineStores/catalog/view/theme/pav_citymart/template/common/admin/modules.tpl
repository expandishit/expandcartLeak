<?php 
	$d = array(
		'demo_advertising_top'=>'<img src="image/data/adv-top.png" src="saleoff" />',
		'demo_delivery_data_module' => '
		
			<div class="box-services"><span class="iconbox pull-left"><i class="fa fa-truck">&nbsp;</i></span>
			<div class="media-body">
			<h4>Free shipping</h4>
			<span>all order over $150 </span></div>
			</div>

			<div class="box-services"><span class="iconbox pull-left"><i class="fa fa-refresh">&nbsp;</i></span>
			<div class="media-body">
			<h4>Return &amp; Exchange</h4>
			<span>in 3 working days </span></div>
			</div>

			<div class="box-services"><span class="iconbox pull-left"><i class="fa fa-phone">&nbsp;</i></span>
			<div class="media-body">
			<h4>04 123 456 789</h4>
			<span>Sed ullamcorper mattis sit</span></div>
			</div>

		',
		'demo_footer_columns_2'=>'
			<h3>Newsletter</h3>
				<p>Send your email a newsletter.</p>
				<div class="email"><input type="text" value="" placeholder="email" name="email">
					<div class="button-email">GO</div>
				</div>
				
				<h3>Order online</h3>
				<p>Phone: +123 456 789</p>
				<p>Fax: +123 456 789</p>
				<p>Email: admin@admin.com</p>
		',
		'demo_footer_columns_3'=>'
			<h3>Stay Connected</h3>
			<ul>
				<li class="facebook"><span class="fa fa-facebook">&nbsp;</span><a href="#">Triads Facebook</a></li>
				<li class="twitter"><span class="fa fa-twitter">&nbsp;</span><a href="#">Triads Twitter</a></li>
				<li class="google"><span class="fa fa-google-plus">&nbsp;</span><a href="#">Triads Google</a></li>
				<li class="youtube"><span class="fa fa-youtube">&nbsp;</span><a href="#">Triads Youtube</a></li>
				<li class="rss"><span class="fa fa-rss">&nbsp;</span><a href="#">Triads RSS Feed</a></li>
			</ul>
		',
		'demo_username_facebook_module' => 'http://www.facebook.com/expandcart' ,
        'demo_widget_paypal_data'=>'
            <img src="catalog/view/theme/pav_citymart/image/icon/payment.png" alt="paymethods">
        '
		
	);
	$module = array_merge( $d, $module );
?>

<div class="inner-modules-wrap clearix">
	<div class="vtabs mytabs" id="my-tab-innermodules">
		<a onclick="return false;" href="#tab-imodule-topbar"><?php echo $this->language->get('Top Bar');?></a>
		<a onclick="return false;" href="#tab-imodule-header"><?php echo $this->language->get('Header');?></a>
		<a onclick="return false;" href="#tab-imodule-footer"><?php echo $this->language->get('Footer');?></a>
	 </div>

	  <div class="page-tabs-wrap clearfix">
	  		<div id="tab-imodule-topbar">
                <div id="language-advertising_top" class="htabs mytabstyle">
                    <?php foreach ($languages as $language) { ?>
                    <a href="#tab-language-advertising_top-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
                    <?php } ?>
                </div>

                <?php foreach ($languages as $language) {   ?>
                <div id="tab-language-advertising_top-<?php echo $language['language_id']; ?>">
                    <table class="form">
                        <?php
                                $text = '';
                                if (isset($module['advertising_top'])) {
                                    if (is_array($module['advertising_top'])) {
                                        $text = $module['advertising_top'][$language['language_id']];
                                    }
                                    else {
                                        $text = $module['advertising_top'];
                                    }
                                }
                                else {
                                    $text = $module['demo_advertising_top'];
                                }
                        ?>

                        <tr>
                            <td><?php echo $this->language->get('Advertising Widget');?></td>
                            <td><textarea name="themecontrol[advertising_top][<?php echo $language['language_id']; ?>]" id="advertising_top-<?php echo $language['language_id']; ?>" rows="5" cols="60"><?php echo $text; ?></textarea>
                                <p><i><?php echo $this->language->get('this module appear in header top position');?></i></p>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php } ?>
	  		</div>

	  		<div id="tab-imodule-header">
                <div id="language-delivery_data_module" class="htabs mytabstyle">
                    <?php foreach ($languages as $language) { ?>
                    <a href="#tab-language-delivery_data_module-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
                    <?php } ?>
                </div>

                <?php foreach ($languages as $language) {   ?>
                <div id="tab-language-delivery_data_module-<?php echo $language['language_id']; ?>">
                    <table class="form">
                        <?php
                                $text = '';
                                if (isset($module['delivery_data_module'])) {
                                    if (is_array($module['delivery_data_module'])) {
                                        $text = $module['delivery_data_module'][$language['language_id']];
                                    }
                                    else {
                                        $text = $module['delivery_data_module'];
                                    }
                                }
                                else {
                                    $text = $module['demo_delivery_data_module'];
                                }
                        ?>

                        <tr>
                            <td><?php echo $this->language->get('Online Phone Widget');?></td>
                            <td><textarea name="themecontrol[delivery_data_module][<?php echo $language['language_id']; ?>]" id="delivery_data_module-<?php echo $language['language_id']; ?>" rows="5" cols="60"><?php echo $text; ?></textarea>
                                <p><i><?php echo $this->language->get('this module appear in header right position');?></i></p>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php } ?>
	  		</div>

	  		<div id="tab-imodule-footer">
	  				<table class="form">
							<tr>
								<td><?php echo $this->language->get('Facebook Module');?></td>
								<td>
									<h4><label><?php echo $this->language->get('Url Facebook');?></label></h4>
									<input name="themecontrol[username_facebook_module]"  value="<?php echo $module['username_facebook_module'];?>" size="60"/>
									<p><i><?php echo $this->language->get('this module appear in Footer [1] position');?></i></p>
								</td>
							</tr>
							<tr>
                                <td><?php echo $this->language->get('Footer Columns 2');?></td>
                                <td>
                                    <div id="language-footer_columns_2" class="htabs mytabstyle">
                                        <?php foreach ($languages as $language) { ?>
                                        <a href="#tab-language-footer_columns_2-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
                                        <?php } ?>
                                    </div>

                                    <?php foreach ($languages as $language) {   ?>
                                    <div id="tab-language-footer_columns_2-<?php echo $language['language_id']; ?>">
                                        <table>
                                            <?php
                                                $text = '';
                                                if (isset($module['footer_columns_2'])) {
                                                    if (is_array($module['footer_columns_2'])) {
                                                        $text = $module['footer_columns_2'][$language['language_id']];
                                                    }
                                                    else {
                                                        $text = $module['footer_columns_2'];
                                                    }
                                                }
                                                else {
                                                    $text = $module['demo_footer_columns_2'];
                                                }
                                            ?>

                                            <tr>
                                                <td><textarea name="themecontrol[footer_columns_2][<?php echo $language['language_id']; ?>]" id="footer_columns_2-<?php echo $language['language_id']; ?>" rows="5" cols="60"><?php echo $text; ?></textarea>
                                                    <p><i><?php echo $this->language->get('this module appear in Footer [2] position');?></i></p>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <?php } ?>
                                </td>
							</tr>
							<tr>
                                <td><?php echo $this->language->get('Footer Columns 3');?></td>
                                <td>
                                    <div id="language-footer_columns_3" class="htabs mytabstyle">
                                        <?php foreach ($languages as $language) { ?>
                                        <a href="#tab-language-footer_columns_3-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
                                        <?php } ?>
                                    </div>

                                    <?php foreach ($languages as $language) {   ?>
                                    <div id="tab-language-footer_columns_3-<?php echo $language['language_id']; ?>">
                                        <table>
                                            <?php
                                                $text = '';
                                                if (isset($module['footer_columns_3'])) {
                                                    if (is_array($module['footer_columns_3'])) {
                                                        $text = $module['footer_columns_3'][$language['language_id']];
                                                    }
                                                    else {
                                                        $text = $module['footer_columns_3'];
                                                    }
                                                }
                                                else {
                                                    $text = $module['demo_footer_columns_3'];
                                                }
                                            ?>

                                            <tr>
                                                <td><textarea name="themecontrol[footer_columns_3][<?php echo $language['language_id']; ?>]" id="footer_columns_3-<?php echo $language['language_id']; ?>" rows="5" cols="60"><?php echo $text; ?></textarea>
                                                    <p><i><?php echo $this->language->get('this module appear in Footer [3] position');?></i></p>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <?php } ?>
                                </td>
							</tr>
                            <tr>
                                <td><?php echo $this->language->get('Paypal Module');?></td>
                                <td>
                                    <div id="language-widget_paypal_data" class="htabs mytabstyle">
                                        <?php foreach ($languages as $language) { ?>
                                        <a href="#tab-language-widget_paypal_data-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
                                        <?php } ?>
                                    </div>

                                    <?php foreach ($languages as $language) {   ?>
                                    <div id="tab-language-widget_paypal_data-<?php echo $language['language_id']; ?>">
                                        <table>
                                            <?php
                                                $text = '';
                                                if (isset($module['widget_paypal_data'])) {
                                                    if (is_array($module['widget_paypal_data'])) {
                                                        $text = $module['widget_paypal_data'][$language['language_id']];
                                                    }
                                                    else {
                                                        $text = $module['widget_paypal_data'];
                                                    }
                                                }
                                                else {
                                                    $text = $module['demo_widget_paypal_data'];
                                                }
                                            ?>

                                            <tr>
                                                <td><textarea name="themecontrol[widget_paypal_data][<?php echo $language['language_id']; ?>]" id="widget_paypal_data-<?php echo $language['language_id']; ?>" rows="5" cols="60"><?php echo $text; ?></textarea></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <?php } ?>
                                </td>
                            </tr>
                    </table><div class="clearfix clear"></div>
	  		</div>
	  </div>
	<div class="clearfix clear"></div>
</div>


<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script> 
<script type="text/javascript"><!--
$("#my-tab-innermodules a").tabs();

    $("#language-advertising_top a").tabs();
    <?php foreach( $languages as $language )  { ?>
        CKEDITOR.replace('advertising_top-<?php echo $language["language_id"]; ?>', {
            filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
        });
    <?php } ?>

    $("#language-delivery_data_module a").tabs();
    <?php foreach( $languages as $language )  { ?>
        CKEDITOR.replace('delivery_data_module-<?php echo $language["language_id"]; ?>', {
            filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
        });
    <?php } ?>

    $("#language-footer_columns_2 a").tabs();
    <?php foreach( $languages as $language )  { ?>
        CKEDITOR.replace('footer_columns_2-<?php echo $language["language_id"]; ?>', {
            filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
        });
    <?php } ?>

    $("#language-footer_columns_3 a").tabs();
    <?php foreach( $languages as $language )  { ?>
        CKEDITOR.replace('footer_columns_3-<?php echo $language["language_id"]; ?>', {
            filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
        });
    <?php } ?>

    $("#language-widget_paypal_data a").tabs();
    <?php foreach( $languages as $language )  { ?>
        CKEDITOR.replace('widget_paypal_data-<?php echo $language["language_id"]; ?>', {
            filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
            filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
        });
    <?php } ?>

//--></script>