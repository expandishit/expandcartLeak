<?php echo $header; ?>

<?php // echo '<pre>'.print_r( $this->getLang("tab_general"),1 ); // die;?>
<div id="content">
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
  <div class="box">
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
	  
      <div class="buttons">
	  <a class="button button-action" rel=""><?php echo $button_save; ?></a>
	  <a id="button_save_keep" class="button button-action" rel="save-edit"><?php echo $button_save_keep; ?></a>
	  
	  <a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
     
       <div style="padding:12px;">
		<b><?php echo $this->getLang("text_default_theme");?></b>
		<select name="themecontrol[default_theme]">
			<?php foreach( $templates as $template ): ?>
			<?php  $selected= $template == $module['default_theme']?'selected="selected"':'';	?>
			<option value="<?php echo $template;?>" <?php echo $selected; ?>><?php echo $template; ?></option>
			<?php endforeach; ?>
		</select>
	  </div>
	  
	  
		 <div id="tabs" class="htabs">
			
			<a href="#tab-general"><?php echo $tab_general; ?></a>
			<a href="#tab-font"><?php echo $tab_font; ?></a>
			<a href="#tab-modules">Modules - Layouts</a>
			<a href="#tab-support">Information</a>
		 </div>
		 <input type="hidden" name="themecontrol[layout_id]" value="1">
		  <input type="hidden" name="themecontrol[position]" value="1">
		<div id="tab-contents">
				<div id="tab-general">
					<div class="tab-inner">
						<table class="form">
							<tr>
								<td><?php echo 'Default Theme'; ?></td>
								<td>
									<div class="group-options theme-skins clear">
										<select name="themecontrol[skin]">
											<option value="">default</option>
										<?php foreach( $skins as $skin ): ?>
											<option value="<?php echo $skin;?>" <?php if( $skin==$module['skin']){ ?> selected="selected" <?php } ?>><?php echo $skin;?></option>
										<?php endforeach;?>
										</select>
										
										<div class="clear"></div>
									</div>
						
								</td>
							</tr>
						
							<tr>
								<td><?php echo $this->getLang('entry_theme_width');?></td>
								<td>
									<input  name="themecontrol[theme_width]" value="<?php echo $module['theme_width'];?>">
									<p><i><?php echo $this->language->get('text_explain_theme_width');?></i></p>
								</td>
							</tr>
							<tr class="highlight">
							<td><?php echo $this->getLang('entry_enable_copyright');?></td>
							<td>
								<select name="themecontrol[enable_custom_copyright]">
								
								<?php foreach( $yesno as $v=>$op ): ?>
									<option value="<?php echo $v;?>" <?php if( $v==$module['enable_custom_copyright']){ ?> selected="selected" <?php } ?>><?php echo $op;?></option>
								<?php endforeach;?>
								</select>
							</td>
						</tr>
							<tr>
								<td><?php echo $this->getLang('copyright');?></td>
								<td>
									<textarea cols="40" rows="3" name="themecontrol[copyright]"><?php echo $module['copyright'];?></textarea>
								</td>
							</tr>
							<tr>
								<td><?php echo $this->getLang('entry_responsive');?></td>
								<td>
									<select name="themecontrol[responsive]">
										<option value="0" <?php if( $module['responsive'] == 0 ){ echo 'selected="selected"';} ;?>><?php echo $this->getLang('no');?></option>
										<option value="1" <?php if( $module['responsive'] == 1 ){ echo 'selected="selected"';} ;?>><?php echo $this->getLang('yes');?></option>
									</select>
								</td>
							</tr>
							<tr>
								<td><?php echo $this->getLang('entry_enable_footer_center');?></td>
								<td>
									<select name="themecontrol[enable_footer_center]">
										<option value="0" <?php if( $module['enable_footer_center'] == 0 ){ echo 'selected="selected"';} ;?>><?php echo $this->getLang('no');?></option>
										<option value="1" <?php if( $module['enable_footer_center'] == 1 ){ echo 'selected="selected"';} ;?>><?php echo $this->getLang('yes');?></option>
									</select>
								</td>
							</tr>
							
							<tr>
								<td><?php echo $this->getLang('entry_enable_paneltool');?></td>
								<td>
									<select name="themecontrol[enable_paneltool]">
										<option value="0" <?php if( $module['enable_paneltool'] == 0 ){ echo 'selected="selected"';} ;?>><?php echo $this->getLang('no');?></option>
										<option value="1" <?php if( $module['enable_paneltool'] == 1 ){ echo 'selected="selected"';} ;?>><?php echo $this->getLang('yes');?></option>
									</select>
								</td>
							</tr>
							
							<tr>
								<td>
									<label>Body Pattern</label>	
								</td>
								<td>
									<div class="group-input">
										<div class="box-patterns clearfix">	
											<div class="none" style="background:#FFF"></div>
											<?php foreach( $patterns as $pattern )  { ?>
											<div class="<?php echo str_replace(".png","",$pattern);?>" style="background:url(<?php echo $theme_url."image/pattern/".$pattern; ?>)"></div>
											<?php } ?>
										</div>
										<input name="themecontrol[body_pattern]" type="hidden" id="userparams_body_pattern" value="<?php echo $module['body_pattern'];?>"/>
										<script type="text/javascript">
											$( ".box-patterns div").click( function(){
												$("#userparams_body_pattern").val(  $(this).attr("class") );
												$( ".box-patterns div").removeClass( "active" );
												$(this).addClass( "active" );
											} );
											if( $("#userparams_body_pattern").val() ){ 
												$( ".box-patterns").find("."+ $("#userparams_body_pattern").val() ).addClass( "active" );
											}
										</script>
									</div>
									
								</td>
									
							</tr>
						</table>
						
							
						
					</div>
				</div>
				
				
				<div id="tab-font">
					<table class="form">
						<tr>
							<td><?php echo $this->getLang("fontsize");?></td>
							<td>
								<select name="themecontrol[fontsize]">
								<?php foreach ( $fontsizes as $key => $value ): ?>
									<?php  $selected = $value == $module['fontsize']?'selected="selected"':'';	?>	
									<option value="<?php echo $value;?>" <?php echo $selected; ?>><?php echo  $value; ?></option>
								<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr class="highlight">
							<td><?php echo $this->getLang('entry_enable_customfont');?></td>
							<td>
								<select name="themecontrol[enable_customfont]">
								
								<?php foreach( $yesno as $v=>$op ): ?>
									<option value="<?php echo $v;?>" <?php if( $v==$module['enable_customfont']){ ?> selected="selected" <?php } ?>><?php echo $op;?></option>
								<?php endforeach;?>
								</select>
							</td>
						</tr>
						
						
					</table>
						<?php  //  echo '<pre>'.print_r( $module,1 );die;?>
					<table class="form">
						<?php for( $i=1; $i<=3;$i++ ){ ?>	
						<tr>
							<td><b><?php echo $this->language->get('entry_font_setting');?></b></td>
							<td>
								<div  class="group-change">
									<select name="themecontrol[type_fonts<?php echo $i;?>]" class="type-fonts">
										<?php foreach( $type_fonts as $font ) {   ?>
										<option value="<?php echo $font[0];?>"<?php if( $module['type_fonts'.$i] == $font[0]) { ?> selected="selected"<?php } ?>><?php echo $font[1];?></option>
										<?php } ?>
									</select>
									
									<table class="form">
											<tr class="items-group-change group-standard">
												<td><?php echo $this->language->get('entry_normal_font');?></td>
												<td>
													<select name="themecontrol[normal_fonts<?php echo $i;?>]">
														<?php foreach( $normal_fonts as $font ) {   ?>
														<option value="<?php echo htmlspecialchars($font[0]);?>"<?php if( $module['normal_fonts'.$i] == htmlspecialchars($font[0])) { ?> selected="selected"<?php } ?>><?php echo $font[1];?></option>
														<?php } ?>
													</select>
												</td>
											</tr>
											<tr class="items-group-change group-google">
												<td><?php echo $this->language->get('entry_body_google_url');?>
												
												</td>
												<td>
													<input type="text" name="themecontrol[google_url<?php echo $i;?>]" size="65" value="<?php echo $module['google_url'.$i];?>"/>
													<p><i><?php echo $this->language->get('text_explain_google_url')?></i></p>
												</td>
											</tr>
											<tr class="items-group-change group-google">
												<td>Google Family:</td>
												<td><input type="text" name="themecontrol[google_family<?php echo $i?>]" size="65" value="<?php echo $module['google_family'.$i];?>"/>
												<p><i><?php echo $this->language->get('text_explain_google_family');?></i></p>
												</td>
											</tr>
									</table>
								</div>
								
							</td>
						</tr>
						
						<tr>
							<td><?php echo $this->language->get('entry_body_selector');?></td>
							<td>
								<textarea name="themecontrol[body_selector<?php echo $i?>]" rows="5" cols="50"><?php echo $module['body_selector'.$i];?></textarea>
								<p><i><?php echo $this->language->get('text_explain_body_selector');?></i></p>
							</td>
						</tr>
					<?php } ?>
					</table>
					
				</div>
	
				<div id="tab-modules">
					<?php include( "themecontrol_layout.tpl" ); ?>
				</div>
				
				
				
				<input type="hidden" name="action_type" id="action_type" value="new">
				

	   </div>
    </div>
  </div></form>
  
</div>
 <script type="text/javascript"><!--
$('#tabs a').tabs(); 
$('.mytabs a').tabs();
$('#languages a').tabs();
 
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
//--></script> 
<?php echo $footer; ?>