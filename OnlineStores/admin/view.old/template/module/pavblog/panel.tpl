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

            <div class="row">
                <div class="col-lg-12">
                    <div class="main-box show-grid clearfix">
                        <div class="clearfix">
				            <div class="box-columns">
					
                            <div class="column2">

                                <div class="tool-icons clearfix" style="width:100%">

                                    <ul>
                                                                        <li><a href="<?php echo $manage_category_link;?>"><span class="icon-category icon"></span><span><?php echo $this->language->get('databroad_categories')?></span></a></li>
                                        <li><a href="<?php echo $add_category_link;?>"><span class="icon-addcategory icon"></span><span><?php echo $this->language->get('databroad_add_category')?></span></a></li>
                                        <li><a href="<?php echo $manage_blog_link;?>"><span class="icon-blogs icon"></span><span><?php echo $this->language->get('databroad_blogs')?></span></a></li>
                                        <li><a href="<?php echo $add_blog_link;?>"><span class="icon-blog icon"></span><span><?php echo $this->language->get('databroad_add_blog')?></span></a></li>
                                        <li><a href="<?php echo $manage_comment_link;?>"><span class="icon-comment icon"></span><span><?php echo $this->language->get('databroad_comment')?></span></a></li>
                                        <li><a href="<?php echo $modules_setting_link;?>"><span class="icon-modules icon"></span><span><?php echo $this->language->get('databroad_modules_setting')?></span></a></li>
                                        <li><a href="<?php echo $frontend_modules_link;?>"><span class="icon-front-modules icon"></span><span><?php echo $this->language->get('menu_frontend_module_setting')?></span></a></li>

                                    </ul>
                                </div>
                            </div>

                            <div class="column2">

                            <div  class="htabs">
                                <a href="#panel-lastest"><?php echo $this->language->get('latest_blog');?></a>
                                <a href="#panel-mostread"><?php echo $this->language->get('most_read');?></a>
                                <a href="#panel-comments"><?php echo $this->language->get('latest_comment');?></a>
                            </div>
                            <div id="panel-lastest" style="background-color: white; margin-top: 12px;">
                                <ul>
                                <?php foreach( $newest as $blog ) { ?>
                                    <li>
                                        <a href="<?php echo $this->url->link("module/pavblog/blog","id=".$blog['blog_id']."&token=".$token);?>"><?php echo $blog['title'];?></a>
                                    </li>
                                <?php } ?>
                                </ul>
                            </div>
                            <div id="panel-mostread" style="background-color: white; margin-top: 12px;">
                                <ul>
                                <?php foreach( $mostread as $blog ) { ?>
                                    <li>
                                        <a href="<?php echo $this->url->link("module/pavblog/blog","id=".$blog['blog_id']."&token=".$token);?>"><?php echo $blog['title'];?></a>
                                    </li>
                                <?php } ?>
                                </ul>
                            </div>
                            <div id="panel-comments" style="background-color: white; margin-top: 12px;">
                                <ul>
                                    <?php foreach( $comments as $comment ) { ?>
                                    <li><a href="<?php echo $this->url->link("module/pavblog/comment","id=".$comment['comment_id']."&token=".$token);?>"><?php echo utf8_substr($comment['comment'],0,100);?></a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
				        </div>
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
