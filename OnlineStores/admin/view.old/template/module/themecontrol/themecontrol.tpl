<?php echo $header; ?>

<?php if ($success) { ?>
<script>
    var notificationString = '<?php echo $success; ?>';
    var notificationType = 'success';
</script>
<?php } ?>
<?php if ($error) { ?>
<script>
    var notificationString = '<?php echo $error; ?>';
    var notificationType = 'warning';
</script>
<?php } ?>

<?php 
	$adminModuleViewDir = DIR_TEMPLATE . 'module/themecontrol/';

	$modules_tpl = '';
	$modules_tpl2 = $adminModuleViewDir.'modules_'.trim($this->getTheme()).'.tpl';

	$modules_tpl1 = $adminModuleViewDir.'modules.tpl';

	$modules_tpl3 = DIR_CATALOG.'view/theme/'.$this->getTheme().'/template/common/admin/modules.tpl';
	if( file_exists($modules_tpl3) ){
		 $modules_tpl = $modules_tpl3;
	} else if( file_exists($modules_tpl2) ){
		$modules_tpl = $modules_tpl2;
	}elseif( file_exists($modules_tpl1) ){
		$modules_tpl = $modules_tpl1;
	} 


?>
<div id="content">

 <div class="button-fixed "><a href="<?php echo $ajax_clearcache; ?>" class="ajax-action"><?php echo $this->language->get('text_clear_jscss_cache');?></a></div>

<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="sform">
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
  <div class="box"  id="themepanel">
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
	  
      <div class="buttons">
	  <a class="button button-action btn-save" rel=""><?php echo $button_save; ?></a>

	  </div>
    </div>
    <div class="content">
		
	 <div class="entry-theme" style="display: none;">
		<b class="label"> <?php echo $this->getLang("text_default_theme");?></b>
		<select name="themecontrol[default_theme]">
			<?php foreach( $templates as $template ): ?>
			<?php  $selected= $template == $module['default_theme']?'selected="selected"':'';	?>
			<option value="<?php echo $template;?>" <?php echo $selected; ?>><?php echo $template; ?></option>
			<?php endforeach; ?>
		</select>
		
		 - <a rel="" class="green custom-popup" href="https://www.expandcart.com/guides/<?php echo $module['default_theme']?>" id="btn-guide"><?php echo $this->language->get('UserGuide');?></a>

		<?php if( isset($first_installation) )  { ?>
			<div class="label" style="float:right"><?php echo $this->language->get("text_first_installation"); ?></div>
		<?php } ?>
	  </div>
	  
		<div class="ibox">
      
	  
	
		 <div id="tabs" class="htabs">
			
			<a href="#tab-general"><?php echo $tab_general; ?></a>
			<a href="#tab-pages-layout"><?php echo $this->language->get('tab_modules_pages');?></a>


             <?php if(  $modules_tpl ){ ?>
             <a href="#tab-imodules"><?php echo $this->language->get('tab_internal_modules');?></a>
             <?php } ?>
             <a href="#tab-modules"><?php echo $this->language->get('tab_modules_layouts');?></a>
             <?php if( isset($samples) && $samples )  { ?>
             <a href="#tab-datasample"><?php echo $this->language->get('tab_datasample');?></a>
             <?php } ?>

			<a href="#tab-customcode"><?php echo $this->language->get('tab_customcode');?></a>

		 </div>
		 <input type="hidden" name="themecontrol[layout_id]" value="1">
		 <input type="hidden" name="themecontrol[position]" value="1">


		<div id="tab-contents">
				
				<div id="tab-pages-layout">
		  			 <?php include('tab/pages-setting.tpl'); ?>
				</div>  

				<div id="tab-general">
					<?php include('tab/general-setting.tpl'); ?>

					<?php if( isset($theme_customizations) && is_array($theme_customizations) && isset($theme_customizations['layout']) ) { ?>
					<h3><?php echo $this->language->get('text_template_layouts_setting'); ?></h3> 
		  			 <div class="theme-customizations">
		  			 		<table class="form">
		  			 		<?php foreach($theme_customizations['layout'] as $bhead => $bcustoms ) {  
		  			 			$ckey = trim(strtolower($bhead));
		  			 			$default = isset($bcustoms['default'])?trim($bcustoms['default']):"";
		  			 			$selected = isset($module[$ckey])?$module[$ckey]:$default;
		  			 		 ?>
		  			 			<div class="theme-custom-block">
		  			 				<tr>
			  			 				<td><label><?php echo $this->language->get( $bhead ); ?></label></td>
			  			 				<td>
			  			 					<?php if( isset($bcustoms['type']) && ($bcustoms['type']) == 'text') { ?>
			  			 						<input value="<?php echo $selected; ?>" name="themecontrol[layout_<?php echo trim(strtolower($bhead)); ?>]">
			  			 					<?php } else { ?>
			  			 					<select name="themecontrol[<?php echo $ckey; ?>]">
			  			 						<?php foreach( $bcustoms['option'] as $okey => $ovalue ) {  ?>
			  			 						<option <?php if($ovalue['value']==$selected) { ?> selected="selected" <?php } ?> value="<?php echo $ovalue['value']; ?>"><?php echo $this->language->get($ovalue['text']); ?></option>
			  			 						<?php } ?>
			  			 					</select>
			  			 					<?php } ?>
			  			 				</td>
			  			 			</tr>

		  			 			</div>
		  			 		<?php } ?>
		  			 		</table>
		  			 </div>
		  			 <?php } ?>


				</div>
				
				<?php if(  $modules_tpl ){ ?>
				<div id="tab-imodules">
					<p><?php echo $this->language->get('text_explain_internal_modules'); ?></p>
					<?php require( $modules_tpl );?>
				</div>
				<?php } ?>
				<div id="tab-font" style="display: none;">
					<?php include( "tab/font-setting.tpl" ); ?>
					
				</div>
	
				<div id="tab-modules">
					<?php include( "tab/layout-setting.tpl" ); ?>
				</div>
				
				
				
				<input type="hidden" name="action_type" id="action_type" value="new">


				<div id="tab-customcode">
					<?php include( $adminModuleViewDir.'tab/customize-setting.tpl' ); ?>
				</div>




	   </div>
    </div></div>
  </div>
  
  
  </form>
  
</div>
 <script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script> 
 <script type="text/javascript"><!--

 	<?php foreach ($languages as $language) { ?>
	CKEDITOR.replace('customtab-content-<?php echo $language['language_id']; ?>', {
		filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
	});  
	CKEDITOR.replace('contact_customhtml<?php echo $language['language_id']; ?>', {
		filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
	});  
	<?php } ?>

$('#tabs a').tabs(); 
$('.mytabs a').tabs();
$('#languages a').tabs();
$('#tab-pages-layout a').tabs();
$('#tabs a').click( function(){
	$.cookie("actived_tab", $(this).attr("href") );
} );

if( $.cookie("actived_tab") !="undefined" ){
	$('#tabs a').each( function(){
		if( $(this).attr("href") ==  $.cookie("actived_tab") ){
			$(this).click();
			return ;
		}
	} );
	
}
$(document).ready( function(){		
		$(".button-action").click( function(){
			$('#action_type').val( $(this).attr("rel") );
			var string = 'rand='+Math.random();
			var hook = '';
			$(".ui-sortable").each( function(){
				if( $(this).attr("data-position") && $(".module-pos",this).length>0) {
					var position = $(this).attr("data-position");
					$(".module-pos",this).each( function(){
						if( $(this).attr("data-id") != "" ){
							hook += "modules["+position+"][]="+$(this).attr("data-id")+"&";
						}				
					} );
					string = string.replace(/\,$/,"");
					hook = hook.replace(/\,$/,"");
				}	
			} );
			var unhook = '';

			$.ajax({
			  type: 'POST',
			  url: '<?php echo str_replace("&amp;","&",$ajax_modules_position);?>',
			  data: string+"&"+hook+unhook,
			  success: function(){
				$('#sform').submit();
				// 	window.location.reload(true);
			  }
			});
		return false; 
	} );

	$("a.ajax-action").click( function(){
		$(this).append('<span class="ajax-loading">Procesing...</span>');
		var _a = this;
		var url = $(this).attr('href');
		$.ajax({
			  type: 'POST',
			  url: url,
			  data: 'rand='+Math.random(),
			  success: function(){
			  	$(".ajax-loading",_a).remove();
			 }
		});	 
		return false; 
	} );
} );

$(".group-change").each( function(){
	var $this = this;
	$(".items-group-change",$this).hide();  //  alert( $(".type-fonts",this).val() );
	$(".group-"+$(".type-fonts",$this).val(), this).show();
	
	$(".type-fonts", this).change( function(){
		$(".items-group-change",$this).hide();
		$(".group-"+$(this).val(), $this).show();
	} );
});


$(".custom-popup").click( function(){
	$('#dialog').remove();
	
	$('#content').prepend('<div id="dialog" style="padding: 3px 0px 0px 0px;"><iframe src="'+$(this).attr('href')+'" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no" scrolling="auto"></iframe></div>');
	
	$('#dialog').dialog({
		title: 'Guide For Theme: <?php echo $module["default_theme"]; ?>',
		close: function (event, ui) {},	
		bgiframe: false,
		width: 980,
		height: 560,
		resizable: false,
		modal: true
	});
	return false;
} );
//--></script> 
<script type="text/javascript"><!--
function image_upload(field, thumb) {
    $.startImageManager(field, thumb);
};
//--></script> 
<?php echo $footer; ?>