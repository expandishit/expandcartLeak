<?php 
$d = array(
	'demo_widget_contact_data'=>'
		<p>
			Lorem ipsum dolor sit amet, consectetur adipiscing elit phasellus et lacus ac 
			turpis euismod elementum a sit amet est nulla tincidunt purus aliquam aliquet 
			mauris ante accumsan turpis, non faucibus magna nibh in urna. 
		</p>
		<ul class="pav-contact-us clearfix">
			<li>
				<i class="icon i-street">&nbsp;</i>
				<div class="l-text">Avenue of the American Independent, st. 133/2 New York City, NY <br/> United States</div>
			</li>
			<li>
				<i class="icon i-phone">&nbsp;</i>
				Phone: (000) 354-543-5456
			</li>
			<li>
				<i class="icon i-fax">&nbsp;</i>
				Fax: (000) 354-543-5457
			</li>	
		</ul>
	',

	'username_twitter_module' => 'pavothemes'
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
			</div>
		</div>
		<div class="clearfix clear"></div>	
	</div>

