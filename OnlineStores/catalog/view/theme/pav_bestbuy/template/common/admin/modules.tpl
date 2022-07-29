<?php 
	$d = array(
		'demo_widget_return_data' => '
			<div class="box-style free-shipping">
			<h4>Free shipping</h4>
			<p>Morbi accumsan ipsum</p>
			</div>

			<div class="box-style time-delivery">
			<h4>On time delivery</h4>
			<p>Morbi accumsan ipsum</p>
			</div>

			<div class="box-style best-services">
			<h4>Best services</h4>
			<p>Morbi accumsan ipsum</p>
			</div>
		',
		'demo_widget_social_data'=>'
			 <ul>
				<li class="facebook"><a href="http://www.facebook.com/">facebook</a></li>
				<li class="twitter"><a href="https://twitter.com/">twitter</a></li>
				<li class="google-plus"><a href="https://www.google.com">google plus</a></li>
				<li class="youtobe"><a href="http://www.youtobe.com/">youtobe</a></li>
				<li class="skype"><a href="http://www.skype.com/">skype</a></li>
			</ul>
		',
		'demo_widget_about_data' => '
			<p><img alt="" src="image/data/logo-copy.png" /></p>

		',
		'demo_widget_contact_data'=>'
			<h3>Contact Us</h3>

			<p>This is Photoshops version of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet.</p>

			<ul>
				<li class="phone">Phone: +01 888 (000) 1234</li>
				<li class="fax">Fax: +01 888 (000) 1234</li>
				<li class="email">Email: contac@leobestbuy.com</li>
			</ul>
		'

	);
	$module = array_merge( $d, $module );


?>

<div class="inner-modules-wrap">
	<div class="vtabs mytabs" id="my-tab-innermodules">
		<a onclick="return false;" href="#tab-imodule-header" class="selected"><?php echo $this->language->get('Header');?></a>
		<a onclick="return false;" href="#tab-imodule-footer" class="selected"><?php echo $this->language->get('Footer Center');?></a>
	 </div>

	 <div class="page-tabs-wrap">
			<div id="tab-imodule-header">
				<h4><?php echo $this->language->get( 'Shipping Module' ) ; ?></h4>

				<div id="language-widget_return_data" class="htabs mytabstyle">
		            <?php foreach ($languages as $language) { ?>
		            <a href="#tab-language-widget_return_data-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
		            <?php } ?>
		          </div>

				<?php foreach ($languages as $language) {   ?>
		          <div id="tab-language-widget_return_data-<?php echo $language['language_id']; ?>">
		           
		            <table class="form">
		            	<?php $text =  isset($module['widget_return_data'][$language['language_id']]) ? $module['widget_return_data'][$language['language_id']] : $module['demo_widget_return_data'];  ?>

		              <tr>
		                <td><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $this->language->get('Shipping');?>: </td>
		                <td><textarea name="themecontrol[widget_return_data][<?php echo $language['language_id']; ?>]" id="widget_return_data-<?php echo $language['language_id']; ?>" rows="5" cols="60"><?php echo $text; ?></textarea></td>
		              </tr>
		            </table>
		          </div>
		          <?php } ?>

		    </div>      	

		       

	 		<div id="tab-imodule-footer">
				<h4><?php echo $this->language->get( 'About Module' ) ; ?></h4>

				<div id="language-widget_about_data" class="htabs mytabstyle">
		            <?php foreach ($languages as $language) { ?>
		            <a href="#tab-language-widget_about_data-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
		            <?php } ?>
		          </div>

				<?php foreach ($languages as $language) {   ?>
		          <div id="tab-language-widget_about_data-<?php echo $language['language_id']; ?>">
		           
		            <table class="form">
		            	<?php $text =  isset($module['widget_about_data'][$language['language_id']]) ? $module['widget_about_data'][$language['language_id']] : $module['demo_widget_about_data'];  ?>

		              <tr>
		                <td><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $this->language->get('about');?>: </td>
		                <td><textarea name="themecontrol[widget_about_data][<?php echo $language['language_id']; ?>]" id="widget_about_data-<?php echo $language['language_id']; ?>" rows="5" cols="60"><?php echo $text; ?></textarea></td>
		              </tr>
		            </table>
		          </div>
		          <?php } ?>


				<h4><?php echo $this->language->get( 'Contact Module' ) ; ?></h4>

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
		                <td><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $this->language->get('Contact');?>: </td>
		                <td><textarea name="themecontrol[widget_contact_data][<?php echo $language['language_id']; ?>]" id="widget_contact_data-<?php echo $language['language_id']; ?>" rows="5" cols="60"><?php echo $text; ?></textarea></td>
		              </tr>
		            </table>
		          </div>
		          <?php } ?>

			<h4><?php echo $this->language->get( 'Social Module' ) ; ?></h4>

				<div id="language-widget_social_data" class="htabs mytabstyle">
		            <?php foreach ($languages as $language) { ?>
		            <a href="#tab-language-widget_social_data-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
		            <?php } ?>
		          </div>

				<?php foreach ($languages as $language) {   ?>
		          <div id="tab-language-widget_social_data-<?php echo $language['language_id']; ?>">
		           
		            <table class="form">
		            	<?php $text =  isset($module['widget_social_data'][$language['language_id']]) ? $module['widget_social_data'][$language['language_id']] : $module['demo_widget_social_data'];  ?>

		              <tr>
		                <td><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $this->language->get('Social');?>: </td>
		                <td><textarea name="themecontrol[widget_social_data][<?php echo $language['language_id']; ?>]" id="widget_social_data-<?php echo $language['language_id']; ?>" rows="5" cols="60"><?php echo $text; ?></textarea></td>
		              </tr>
		            </table>
		          </div>
		          <?php } ?>



			</div>

				<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script> 
				<script type="text/javascript"><!--
				$("#language-widget_return_data a").tabs();

				<?php foreach( $languages as $language )  { ?>
				CKEDITOR.replace('widget_return_data-<?php echo $language['language_id']; ?>', {
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
				$("#language-widget_about_data a").tabs();
				<?php foreach( $languages as $language )  { ?>
				CKEDITOR.replace('widget_about_data-<?php echo $language['language_id']; ?>', {
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
				$("#language-widget_social_data a").tabs();
				<?php foreach( $languages as $language )  { ?>
				CKEDITOR.replace('widget_social_data-<?php echo $language['language_id']; ?>', {
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
	 <div class="clearfix clear"></div>	
</div>