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

			</div>
			
			<div class="content">
				<div class="box-columns" style="min-height: 250px;">
					
					<div class="column2">
						
						<div class="tool-icons clearfix" style="width:100%">
							<ul>
								<li><a href="<?php echo $menus["create_newsletter"]["link"];?>"><span class="icon-blogs icon"></span><span><?php echo $this->language->get('menu_create_newsletter')?></span></a></li>
								<li><a href="<?php echo $menus["draft"]["link"];?>"><span class="icon-addcategory icon"></span><span><?php echo $this->language->get('menu_manage_draft_newsletters')?></span></a></li>
								<li><a href="<?php echo $menus["subscribes"]["link"];?>"><span class="icon-category icon"></span><span><?php echo $this->language->get('menu_manage_subscribes')?></span></a></li>
								
								<li><a href="<?php echo $menus["templates"]["link"];?>"><span class="icon-blog icon"></span><span><?php echo $this->language->get('menu_templates')?></span></a></li>
								<li><a href="<?php echo $menus["modules"]["link"];?>"><span class="icon-comment icon"></span><span><?php echo $this->language->get('menu_manage_modules')?></span></a></li>
								<li><a href="<?php echo $menus["config"]["link"];?>"><span class="icon-modules icon"></span><span><?php echo $this->language->get('menu_global_config')?></span></a></li>
								
							</ul>
						</div>
					</div>
					
					<div class="column2">
						<?php /* ?>
						<div  class="htabs">
							<a href="#panel-lastest"><?php echo $this->language->get('Latest Blog');?></a>
							<a href="#panel-mostread"><?php echo $this->language->get('Most Read');?></a>
							<a href="#panel-comments"><?php echo $this->language->get('Latest Comment');?></a>	
						</div>
						<?php */ ?>
						<div id="panel-lastest">
							<ul>
							
							</ul>
						</div>
						<div id="panel-mostread">
							<ul>
							
							</ul>
						</div>
						<div id="panel-comments">
							<ul>
								
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>	
		
		
 </div>
 
 <script type="text/javascript">
	$('.htabs a').tabs();

	$("#guide-links a").click( function(){
	
	$('#dialog').remove();
	
	$('#content').prepend('<div id="dialog" style="padding: 3px 0px 0px 0px;"><iframe src="'+$(this).attr('href')+'" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no" scrolling="auto"></iframe></div>');
	
	$('#dialog').dialog({
		title: 'Guide',
		close: function (event, ui) {},	
		bgiframe: false,
		width: 900,
		height: 550,
		resizable: false,
		modal: true
	});
		return false; 
	 });
 </script>
<?php echo $footer; ?>
