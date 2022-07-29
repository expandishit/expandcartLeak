<?php 
	$d = array(
		'demo_widget_contactus_data' => '
                <div class="pull-left image hidden-xs"><img alt="" src="image/data/banner-footer.jpg" /></div>

                <div class="box">
                    <div class="box-heading">
                        <h4><span>Magazine</span></h4>
                    </div>

                    <ul>
                        <li><a href="#">Customer Service</a></li>
                        <li><a href="#">Subscribe</a></li>
                        <li><a href="#">Buy this issue</a></li>
                    </ul>

                    <p>Mauris in erat justo nullam ac sit amet a augue. Sed non neque elit Sed ut imperdiet nisi. Proin per inceptos himenaeos. Mauris in erat justo. Nullam ac urna eu felis dapibus condentum sit nisi. Proin condimentum fecondi mentum ferm nunc. Etiam pharetra, erat sed fermentum feu Suspendisse orci enim.</p>
                    <a href="#">More ...</a>
                </div>


                ',
		'demo_widget_newsletter_data' => '
                    <ul class="list folow">
                        <li><span class="iconbox"><i class="icon-tumblr">&nbsp;</i></span> <a data-original-title="Tumblr" data-placement="top" data-toggle="tooltip" href="#" title=""> <span>Tumblr</span> </a></li>
                        <li><span class="iconbox"><i class="icon-facebook">&nbsp;</i></span> <a data-original-title="Google-plus" data-placement="top" data-toggle="tooltip" href="#" title=""> <span>Google-plus</span> </a></li>
                        <li><span class="iconbox"><i class="icon-google-plus">&nbsp;</i></span> <a data-original-title="Facebook" data-placement="top" data-toggle="tooltip" href="#" title=""> <span>Facebook</span> </a></li>
                        <li><span class="iconbox"><i class="icon-twitter">&nbsp;</i></span> <a data-original-title="Twitter" data-placement="top" data-toggle="tooltip" href="#" title=""> <span>Twitter</span> </a></li>
                        <li><span class="iconbox"><i class="icon-youtube">&nbsp;</i></span> <a data-original-title="Youtube" data-placement="top" data-toggle="tooltip" href="#" title=""> <span>Youtube</span> </a></li>
                        <li><span class="iconbox"><i class="icon-instagram">&nbsp;</i></span> <a data-original-title="Instagram" data-placement="top" data-toggle="tooltip" href="#" title=""> <span>Instagram</span> </a></li>
                        <li><span class="iconbox"><i class="icon-pinterest">&nbsp;</i></span> <a data-original-title="Pinterest" data-placement="top" data-toggle="tooltip" href="#" title=""> <span>Pinterest</span> </a></li>
                    </ul>

		',

		'demo_widget_paypal_data'=>'
			
			<p><img src="image/data/paypal.png" alt="" /></p>
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
	 				<div id="tab-imodule-header">

		    </div> 
	 		<div id="tab-imodule-footer">
				<h4><?php echo $this->language->get( 'Contact Us' ) ; ?></h4>

				<div id="language-widget_contactus_data" class="htabs mytabstyle">
		            <?php foreach ($languages as $language) { ?>
		            <a href="#tab-language-widget_contactus_data-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
		            <?php } ?>
		          </div>

				<?php foreach ($languages as $language) {   ?>
		          <div id="tab-language-widget_contactus_data-<?php echo $language['language_id']; ?>">
		           
		            <table class="form">
		            	<?php $text =  isset($module['widget_contactus_data'][$language['language_id']]) ? $module['widget_contactus_data'][$language['language_id']] : $module['demo_widget_contactus_data'];  ?>

		              <tr>
		                <td><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $this->language->get('Contact');?>: </td>
		                <td><textarea name="themecontrol[widget_contactus_data][<?php echo $language['language_id']; ?>]" id="widget_contactus_data-<?php echo $language['language_id']; ?>" rows="5" cols="60"><?php echo $text; ?></textarea></td>
		              </tr>
		            </table>
		          </div>
		          <?php } ?>

		          <h4><?php echo $this->language->get( 'Social Network' ) ; ?></h4>

				<div id="language-widget_newsletter_data" class="htabs mytabstyle">
		            <?php foreach ($languages as $language) { ?>
		            <a href="#tab-language-widget_newsletter_data-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
		            <?php } ?>
		          </div>

				<?php foreach ($languages as $language) {   ?>
		          <div id="tab-language-widget_newsletter_data-<?php echo $language['language_id']; ?>">
		           
		            <table class="form">
		            	<?php $text =  isset($module['widget_newsletter_data'][$language['language_id']]) ? $module['widget_newsletter_data'][$language['language_id']] : $module['demo_widget_newsletter_data'];  ?>

		              <tr>
		                <td><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $this->language->get('Social');?>: </td>
		                <td><textarea name="themecontrol[widget_newsletter_data][<?php echo $language['language_id']; ?>]" id="widget_newsletter_data-<?php echo $language['language_id']; ?>" rows="5" cols="60"><?php echo $text; ?></textarea></td>
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
				$("#language-widget_newsletter_data a").tabs();
				<?php foreach( $languages as $language )  { ?>
				CKEDITOR.replace('widget_newsletter_data-<?php echo $language['language_id']; ?>', {
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
				$("#language-widget_contactus_data a").tabs();
				<?php foreach( $languages as $language )  { ?>
				CKEDITOR.replace('widget_contactus_data-<?php echo $language['language_id']; ?>', {
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

