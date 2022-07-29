<?php
/******************************************************
 * @package Pav Megamenu module for Opencart 1.5.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2013 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/

 echo $header; 

	$module_row=0; 

?>
<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script>
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
        var notificationString = '<?php
        if( is_array($error_warning) ) {
            echo implode("<br>",$error_warning);
        } else {
            echo $error_warning;
        }
        ?>';
        var notificationType = 'warning';
    </script>
    <?php } ?>

  <div id="ajaxloading" class="hide">
	<div class="warning"><b>processing request...</b></div>
  </div>
  <div class="box">
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
      <div class="buttons">
	  <a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a>
	  <a onclick="__submit('save-edit')" class="button btn btn-primary"><?php echo $this->language->get('button_save_edit'); ?></a>
	  <a onclick="__submit('save-new')" class="button btn btn-primary"><?php echo $this->language->get('button_save_new'); ?></a>
	  <a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
			<div class="megamenu">
				<div class="tree-megamenu">

					<h4><?php echo $this->language->get('text_tree_category_menu');?></h4>
					<?php echo $tree; ?>
					<input type="button" name="serialize" id="serialize" value="<?php echo $this->language->get("Update");?>" />

					<p class="note"><i><?php echo $this->language->get("text_explain_drapanddrop");?></i></p>
				</div>

				<div class="megamenu-form">
					<div id="megamenu-form">
						<?php require( "category_form.tpl" );?>
					</div>
				</div>
			</div>
		</form>
		<input type="hidden" value="" name="save_mode" id="save_mode"/>
    </div>
  </div>
</div>
<script type="text/javascript"><!--

$(".htabs a").tabs();
function image_upload(field, thumb) {
    $.startImageManager(field, thumb);
};
//--></script> 
 <script type="text/javascript" class="source below">
 function __submit( m ){
	$("#save_mode").val( m );
	$('#form').submit();
 }
	$('ol.sortable').nestedSortable({
			forcePlaceholderSize: true,
			handle: 'div',
			helper:	'clone',
			items: 'li',
			opacity: .6,
			placeholder: 'placeholder',
			revert: 250,
			tabSize: 25,
			tolerance: 'pointer',
			toleranceElement: '> div',
			maxLevels: 4,

			isTree: true,
			expandOnHover: 700,
			startCollapsed: true
		});
	
	$('#serialize').click(function(){
			var serialized = $('ol.sortable').nestedSortable('serialize');
			 $.ajax({
			async : false,
			type: 'POST',
			dataType:'json',
			url: "<?php echo str_replace("&amp;","&",$updateTree);?>",
			data : serialized, 
			success : function (r) {
				 
			}
		});
	})
	$(".quickedit").click( function(){
		
		var id = $(this).attr("rel").replace("id_","");
		$.post( "<?php echo str_replace("&amp;","&",$actionGetInfo);?>", {
			"id":id,	
			"rand":Math.random()},
			function(data){
				$("#megamenu-form").html( data );
			});
	} );
	$(".quickdel").click( function(){
		if( confirm("<?php echo $this->language->get("message_delete");?>") ){
			var id = $(this).attr("rel").replace("id_","");
			window.location.href="<?php echo str_replace("&amp;","&",$actionDel);?>&id="+id;
		}
	} );
	$(document).ajaxSend(function() {
		$("#ajaxloading").show();
	});
	$(document).ajaxComplete(function() {
		$("#ajaxloading").hide();
	});
</script>

<?php echo $footer; ?>