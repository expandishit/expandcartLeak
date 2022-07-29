<?php  echo $header;  ?>
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
		<div class="box">

		  
			<div class="heading">
			  <h1><?php echo $heading_title; ?></h1>
				<div class="buttons">
				  <a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $this->language->get("button_save"); ?></a>
				</div>  
			</div>
			
			<div class="content"><form action="<?php echo $action;?>" method="post" id="form">
				<div class="box-columns">
					

					<div class="module-setting-tabs htabs">
						<div id="tabs" class="nav-setting pavhtabs">
							<?php
								if(!empty($layout_modules)){
									foreach($layout_modules as $key=>$modules){
										$key = trim($key);
										?>
										<a href="#tab-module-<?php echo $key ?>"><?php echo $this->language->get("tab_".strtolower($key)); ?></a>
										<?php
									}
								}
							?>
						</div>
					</div>	
						<div class="clearfix clear"></div>						
					 <?php
						if(!empty($layout_modules)){
							foreach($layout_modules as $key=>$item_module){
								//check the module is not installed
								if(!is_array($item_module)){
									$install_link = $this->url->link('extension/module/install', "extension=$key&token=" . $this->session->data['token'], 'SSL');
									?>
									<div id="tab-module-<?php echo $key;?>">
										<div class="tab-inner">
											<p><?php echo $this->language->get("module_not_installed"); ?><a  class="install_button" href="<?php echo $install_link; ?>"><?php echo $this->language->get("install");?></a></p>
											<div class="clear"></div>
										</div>
									</div>
									<?php
								}else{
									$modules_tpl = DIR_TEMPLATE.'module/pavblog/modules/'.trim($key).'.tpl';
									$modules = $item_module;
									$module_key = $key;
									?>
									<div id="tab-module-<?php echo $key;?>">
										<div class="tab-inner" style="min-height: 300px;">
											<input type="hidden" name="pavblog_frontmodules[modules][]" value="<?php echo $key;?>"/>
											<?php
											if( file_exists($modules_tpl) ){
												require_once($modules_tpl);
											}
											?>
											<div class="clear"></div>
										</div>
									</div>
									
									<?php
								}
								
							}
						}
					?>
					
				</div>
				
				
			</div>
		</div>	
		
		
 </div>
  
 <script type="text/javascript">
	$(".pavhtabs a").tabs();
	$(".pavmodshtabs a").tabs();
	function __submit( val ){
		$("#action_mode").val( val );
		$("#form").submit();
	}
	var split = location.search.replace('?', '').split('&').map(function(val){
		  return val.split('=');
		});
	var mod_key = "";
	if(split.length){
		for(i=0;i<split.length;i++){
			if(split[i][0] == "mod"){
				mod_key = "#tab-module-"+split[i][1];
				break;
			}
		}
	}

	$('#tabs a').each( function(){
		if( $(this).attr("href") ==  mod_key ){
			$(this).click();
			return ;
		}
	} );
 </script>
 
<?php echo $footer; ?>
