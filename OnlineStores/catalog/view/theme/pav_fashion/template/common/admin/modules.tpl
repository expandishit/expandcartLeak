<?php 
	$d = array(
		'widget_footer_logo_data' => '
			<div class="box logo-ft">
				<img alt="logo-footer" src="image/data/demo/logo_footer.png" />
			</div>		
		',

		'widget_about_data' => '
			<p>
				Sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. 
				Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan ipsum velit.
				Nam nec tellus a odio tincidunt auctor a ornare odio. 
			</p>		
		',	

		'widget_social_data' => '
			<ul class="social">
				<li><a class="facebook" href="#"><i class="fa fa-facebook stack"><span>Facebook</span></i></a></li>
				<li><a class="twitter" href="#"><i class="fa fa-twitter stack"><span>Twitter</span></i></a></li>
				<li><a class="google" href="#"><i class="fa fa-google-plus stack"><span>Google Plus</span></i></a></li>
				<li><a class="youtube" href="#"><i class="fa fa-youtube stack"><span>Youtube</span></i></a></li>
				<li><a class="skype" href="#"><i class="fa fa-skype stack"><span>Skype</span></i></a></li>
			</ul>
		',		

		'widget_call_us_data' => '
			<div class="box-services">
				<p>Monday - Friday .................. 8.00 to 18.00</p>

				<p>Saturday ......................... 9.00 to 21.00</p>

				<p>Sunday ........................... 10.00 to 21.00</p>
				<span class="iconbox pull-left"><i class="fa fa-phone">&nbsp;</i></span>

				<div class="media-body">
					<span>Call us: <strong> 0123 456 789</strong></span>
				</div>
			</div>

		',		

		'widget_contact_us_data' => '
			<address><strong>Warehouse &amp; Offices</strong><br />
				12345 Street name, California, USA<br />
				0123 456 789 / 0123 456 788
			</address>

			<address><strong>Retail Store</strong><br />
				54321 Street name, California, USA<br />
				0123 456 789 / 0123 456 788
			</address>

		',	
											
		
		'username_twitter_module' => 'pavothemes'
	);
	$module = array_merge( $d, $module );

//	echo '<pre>'.print_r( $languages, 1 );die;
?>

<div class="inner-modules-wrap">	 
	<div class="vtabs mytabs" id="my-tab-innermodules">		
		<a onclick="return false;" href="#tab-imodule-footercenter1" class="selected"><?php echo $this->language->get('Footer Center 1');?></a>
		<a onclick="return false;" href="#tab-imodule-footercenter2" class="selected"><?php echo $this->language->get('Footer Center 2');?></a>
	</div>

	<div class="page-tabs-wrap">
			<div id="tab-imodule-footercenter1">					
				<h4><?php echo $this->language->get( 'Footer Top' ) ; ?></h4> 
				<div id="language-footer_logo_data" class="htabs mytabstyle">
					<?php foreach ($languages as $language) { ?>
					<a href="#tab-language-footer_logo_data-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
					<?php } ?>
				  </div>

				<?php foreach ($languages as $language) {   ?>
				  <div id="tab-language-footer_logo_data-<?php echo $language['language_id']; ?>">
				   
					<table class="form">
						<?php $text =  isset($module['footer_logo_data'][$language['language_id']]) ? $module['footer_logo_data'][$language['language_id']] : $module['widget_footer_logo_data'];  ?>

					  <tr>
						<td><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $this->language->get('Logo Store');?>: </td>
						<td><textarea name="themecontrol[footer_logo_data][<?php echo $language['language_id']; ?>]" id="footer_logo_data-<?php echo $language['language_id']; ?>" rows="5" cols="60" class="form-control"><?php echo $text; ?></textarea></td>
					  </tr>
					</table>
				  </div>
				  <?php } ?>


				  <div id="language-about_data" class="htabs mytabstyle">
					<?php foreach ($languages as $language) { ?>
					<a href="#tab-language-about_data-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
					<?php } ?>
				  </div>

				<?php foreach ($languages as $language) {   ?>
				  <div id="tab-language-about_data-<?php echo $language['language_id']; ?>">
				   
					<table class="form">
						<?php $text =  isset($module['about_data'][$language['language_id']]) ? $module['about_data'][$language['language_id']] : $module['widget_about_data'];  ?>

					  <tr>
						<td><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $this->language->get('About Us');?>: </td>
						<td><textarea name="themecontrol[about_data][<?php echo $language['language_id']; ?>]" id="about_data-<?php echo $language['language_id']; ?>" rows="5" cols="60" class="form-control"><?php echo $text; ?></textarea></td>
					  </tr>
					</table>
				  </div>
				  <?php } ?>		


				   <div id="language-social_data" class="htabs mytabstyle">
					<?php foreach ($languages as $language) { ?>
					<a href="#tab-language-social_data-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
					<?php } ?>
				  </div>

				<?php foreach ($languages as $language) {   ?>
				  <div id="tab-language-social_data-<?php echo $language['language_id']; ?>">
				   
					<table class="form">
						<?php $text =  isset($module['social_data'][$language['language_id']]) ? $module['social_data'][$language['language_id']] : $module['widget_social_data'];  ?>

					  <tr>
						<td><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $this->language->get('Social');?>: </td>
						<td><textarea name="themecontrol[social_data][<?php echo $language['language_id']; ?>]" id="social_data-<?php echo $language['language_id']; ?>" rows="5" cols="60" class="form-control"><?php echo $text; ?></textarea></td>
					  </tr>
					</table>
				  </div>
				  <?php } ?>	
			</div>
			
			<div id="tab-imodule-footercenter2">	
				<h4><?php echo $this->language->get( 'Footer Center' ) ; ?></h4> 
				<div id="language-call_us_data" class="htabs mytabstyle">
					<?php foreach ($languages as $language) { ?>
					<a href="#tab-language-call_us_data-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
					<?php } ?>
				  </div>

				<?php foreach ($languages as $language) {   ?>
				  <div id="tab-language-call_us_data-<?php echo $language['language_id']; ?>">
				   
					<table class="form">
						<?php $text =  isset($module['call_us_data'][$language['language_id']]) ? $module['call_us_data'][$language['language_id']] : $module['widget_call_us_data'];  ?>

					  <tr>
						<td><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $this->language->get('Call Us');?>: </td>
						<td><textarea name="themecontrol[call_us_data][<?php echo $language['language_id']; ?>]" id="call_us_data-<?php echo $language['language_id']; ?>" rows="5" cols="60" class="form-control"><?php echo $text; ?></textarea></td>
					  </tr>
					</table>
				  </div>
				  <?php } ?>


				<div id="language-contact_us_data" class="htabs mytabstyle">
					<?php foreach ($languages as $language) { ?>
					<a href="#tab-language-contact_us_data-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
					<?php } ?>
				  </div>

				<?php foreach ($languages as $language) {   ?>
				  <div id="tab-language-contact_us_data-<?php echo $language['language_id']; ?>">
				   
					<table class="form">
						<?php $text =  isset($module['contact_us_data'][$language['language_id']]) ? $module['contact_us_data'][$language['language_id']] : $module['widget_contact_us_data'];  ?>

					  <tr>
						<td><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $this->language->get('Contact Us');?>: </td>
						<td><textarea name="themecontrol[contact_us_data][<?php echo $language['language_id']; ?>]" id="contact_us_data-<?php echo $language['language_id']; ?>" rows="5" cols="60" class="form-control"><?php echo $text; ?></textarea></td>
					  </tr>
					</table>
				  </div>
				  <?php } ?>
			</div>
	 		
			
			<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script> 
			<script type="text/javascript"><!--
			$("#language-footer_logo_data a").tabs();
			<?php foreach( $languages as $language )  { ?>
			CKEDITOR.replace('footer_logo_data-<?php echo $language['language_id']; ?>', {
				filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
				filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
				filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
				filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
				filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
				filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
			});
			<?php } ?>
			
			$("#language-about_data a").tabs();
			<?php foreach( $languages as $language )  { ?>
			CKEDITOR.replace('about_data-<?php echo $language['language_id']; ?>', {
				filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
				filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
				filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
				filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
				filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
				filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
			});
			<?php } ?>
			
			$("#language-social_data a").tabs();
				<?php foreach( $languages as $language )  { ?>
				CKEDITOR.replace('social_data-<?php echo $language['language_id']; ?>', {
					filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
					filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
					filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
					filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
					filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
					filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
				});
				<?php } ?>

			$("#language-call_us_data a").tabs();
				<?php foreach( $languages as $language )  { ?>
				CKEDITOR.replace('call_us_data-<?php echo $language['language_id']; ?>', {
					filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
					filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
					filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
					filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
					filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
					filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
				});
				<?php } ?>


				$("#language-contact_us_data a").tabs();
				<?php foreach( $languages as $language )  { ?>
				CKEDITOR.replace('contact_us_data-<?php echo $language['language_id']; ?>', {
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

