<?php 
	$d = array(
		'advertising_top'=>'<img src="image/data/adv-top.png" src="saleoff" />',
		'delivery_data_module' => '
		
			<div id="online">
				<div class="heading">
					<h4>Online</h4>
					<span>0 123 456 789</span>
				</div>
			</div>
		',
		'footer_columns_2'=>'
			<h3>Newsletter</h3>
				<p>Send your email a newsletter.</p>
				<div class="email"><input type="text" value="" placeholder="email" name="email">
					<div class="button-email">&nbsp;</div>
				</div>
				
				<h3>Order online</h3>
				<p>Phone: +123 456 789</p>
				<p>Fax: +123 456 789</p>
				<p>Email: admin@admin.com</p>
		',
		'footer_columns_3'=>'
			<h3>Stay Connected</h3>
			<ul>
				<li class="facebook"><a href="#">Triads Facebook</a></li>
				<li class="twitter"><a href="#">Triads Twitter</a></li>
				<li class="google"><a href="#">Triads Google</a></li>
				<li class="youtube"><a href="#">Triads Youtube</a></li>
				<li class="rss"><a href="#">Triads RSS Feed</a></li>
			</ul>
		',
		'username_facebook_module' => 'http://www.facebook.com/PavoThemes'
		
	);
	$module = array_merge( $d, $module );
?>
<h4><?php echo $this->language->get( 'Internal Modules' ) ; ?></h4>
<table class="form">
		<tr>
			<td><?php echo $this->language->get('Advertising Module');?></td>
			<td>
				<h4><label><?php echo $this->language->get('Module HTML Content');?></label></h4>
				<textarea name="themecontrol[advertising_top]" id="advertising_top" rows="5" cols="60"><?php echo $module['advertising_top'];?></textarea>
				<p><i><?php echo $this->language->get('this module appear in header top position');?></i></p>
			</td>
		</tr>
		<tr>
			<td><?php echo $this->language->get('Delivery Module');?></td>
			<td>
				<h4><label><?php echo $this->language->get('Module HTML Content');?></label></h4>
				<textarea name="themecontrol[delivery_data_module]" id="delivery_data_module" rows="5" cols="60"><?php echo $module['delivery_data_module'];?></textarea>
				<p><i><?php echo $this->language->get('this module appear in header right position');?></i></p>
			</td>
		</tr>
		<tr>
			<td><?php echo $this->language->get('Facebook Module');?></td>
			<td>
				<h4><label><?php echo $this->language->get('Url Facebook');?></label></h4>
				<input name="themecontrol[username_facebook_module]"  value="<?php echo $module['username_facebook_module'];?>"/>
				<p><i><?php echo $this->language->get('this module appear in Footer [1] position');?></i></p>
			</td>
		</tr>
		<tr>
			<td><?php echo $this->language->get('Footer Columns 2');?></td>
			<td>
				<h4><label><?php echo $this->language->get('Module HTML Content');?></label></h4>
				<textarea name="themecontrol[footer_columns_2]" id="footer_columns_2" rows="5" cols="60"><?php echo $module['footer_columns_2'];?></textarea>
				<p><i><?php echo $this->language->get('this module appear in Footer [2] position');?></i></p>
			</td>
		</tr>
		<tr>
			<td><?php echo $this->language->get('Footer Columns 3');?></td>
			<td>
				<h4><label><?php echo $this->language->get('Module HTML Content');?></label></h4>
				<textarea name="themecontrol[footer_columns_3]" id="footer_columns_3" rows="5" cols="60"><?php echo $module['footer_columns_3'];?></textarea>
				<p><i><?php echo $this->language->get('this module appear in Footer [3] position');?></i></p>
			</td>
		</tr>		
</table>
<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script> 
<script type="text/javascript"><!--

CKEDITOR.replace('advertising_top', {
	filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
});

CKEDITOR.replace('delivery_data_module', {
	filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
});

CKEDITOR.replace('footer_columns_2', {
	filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
});
CKEDITOR.replace('footer_columns_3', {
	filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
	filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
});

//--></script> 