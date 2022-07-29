<?php 
$d = array(
	'demo_widget_contact_data'=>'
		<ul class="contact">
			<li class="contact-1 clearfix">
				<i class="icon">&nbsp;</i>
				<span>We have 152 guests and 14 members online</span>
			</li>
			<li class="contact-2 clearfix">
				<i class="icon">&nbsp;</i>
				<span>Phone: +01 888 (000) </span>
			</li>
			<li class="contact-3 clearfix">
				<i class="icon">&nbsp;</i>
				<span>Fax: +01 888 (000) 1234</span>
			</li>
			<li class="contact-4 clearfix">
				<i class="icon">&nbsp;</i>
				<span>
					Email: 
					<a href="mailto:pavothemes@gmail.com" "pavothemes@gmail.com" title="">pavothemes@gmail.com</a> 
				</span>		
			</li>
		</ul>
	'	,
    'demo_widget_paypal_data'=>'

    <a href="#" title="payment">
        <img src="catalog/view/theme/pav_asenti/image/payment.png" alt="payment">
    </a>

    '
	);

$module = array_merge( $d, $module );

//	echo '<pre>'.print_r( $languages, 1 );die;

?>

<div class="inner-modules-wrap">
	<div class="vtabs mytabs" id="my-tab-innermodules">
		<a onclick="return false;" href="#tab-imodule-footer" class="selected"><?php echo $this->language->get('Footer');?></a>
	</div>

	<div class="page-tabs-wrap">
		<div id="tab-imodule-footer">
			<h4><?php echo $this->language->get( 'Footer Center' ) ; ?></h4>

			<div id="language-widget_contact_data" class="htabs mytabstyle">
				<?php foreach ($languages as $language) { ?>
				<a href="#tab-language-widget_contact_data-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
				<?php } ?>
			</div>

			<?php foreach ($languages as $language) {   ?>
			<div id="tab-language-widget_contact_data-<?php echo $language['language_id']; ?>">

				<table class="form">
		            	<?php $text =  isset($module['widget_contact_data'][$language['language_id']]) ? $module['widget_contact_data'][$language['language_id']] : $module['demo_widget_contact_data'];  ?>

					<tr>
						<td><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $this->language->get('Contact Us Module');?>: </td>
						<td><textarea name="themecontrol[widget_contact_data][<?php echo $language['language_id']; ?>]" id="widget_contact_data-<?php echo $language['language_id']; ?>" rows="5" cols="60"><?php echo $text; ?></textarea></td>
					</tr>
				</table>
			</div>
			<?php } ?>

            <h4><?php echo $this->language->get( 'Paypal Module' ) ; ?></h4>

            <div id="language-widget_paypal_data" class="htabs mytabstyle">
                <?php foreach ($languages as $language) { ?>
                <a href="#tab-language-widget_paypal_data-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
                <?php } ?>
            </div>

            <?php foreach ($languages as $language) {   ?>
            <div id="tab-language-widget_paypal_data-<?php echo $language['language_id']; ?>">

                <table class="form">
                    <?php $text =  isset($module['widget_paypal_data'][$language['language_id']]) ? $module['widget_paypal_data'][$language['language_id']] : $module['demo_widget_paypal_data'];  ?>

                    <tr>
                        <td><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $this->language->get('Paypal');?>: </td>
                        <td><textarea name="themecontrol[widget_paypal_data][<?php echo $language['language_id']; ?>]" id="widget_paypal_data-<?php echo $language['language_id']; ?>" rows="5" cols="60"><?php echo $text; ?></textarea></td>
                    </tr>
                </table>
            </div>
            <?php } ?>

			<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script> 
			<script type="text/javascript"><!--
			$("#language-widget_contact_data a").tabs();
			<?php foreach( $languages as $language )  { ?>
				CKEDITOR.replace('widget_contact_data-<?php echo $language['language_id']; ?>', {
					filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
					filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
					filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
					filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
					filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
					filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
				});
				<?php } ?>
				//--></script>
            <script type="text/javascript"><!--
                $("#language-widget_paypal_data a").tabs();
                <?php foreach( $languages as $language )  { ?>
                    CKEDITOR.replace('widget_paypal_data-<?php echo $language['language_id']; ?>', {
                        filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
                        filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
                    });
                <?php } ?>
                //--></script>
			</div>
		</div>
		<div class="clearfix clear"></div>	
	</div>

