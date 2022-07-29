<?php echo $header; 

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
        var notificationString = '<?php echo $error_warning; ?>';
        var notificationType = 'warning';
    </script>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
      <div class="buttons">
	  <a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a>
	  <a onclick="__submit('save-edit')" class="button btn btn-primary"><?php echo "Save And Edit"; ?></a>
	  <a onclick="__submit('save-new')" class="button btn btn-primary"><?php echo "Save And New"; ?></a>
	  <a href="<?php echo $cancel; ?>" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
			<div class="megamenu">
			
			
				<div class="tree-megamenu">
					<h4>Tree Megamenu</h4>
					<?php echo $tree; ?>
					<input type="button" name="serialize" id="serialize" value="Update" />
				</div>
				
				<div class="megamenu-form">
					<h3>Module Assignment</h3>
					<table class="form">
						<tr>
						  <td><?php echo $entry_layout; ?></td>
						  <td><select name="pavmegamenu_module[<?php echo $module_row; ?>][layout_id]">
							 <?php foreach ($layouts as $layout) { ?>
							  <?php if ($layout['layout_id'] == $module['layout_id']) { ?>
							  <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
							  <?php } else { ?>
							  <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
							  <?php } ?>
							  <?php } ?>
							</select></td>
						</tr>
						<tr>
						  <td><?php echo $entry_position; ?></td>
						  <td><select name="pavmegamenu_module[<?php echo $module_row; ?>][position]">
							  <?php foreach( $positions as $pos ) { ?>
							  <?php if ($module['position'] == $pos) { ?>
							  <option value="<?php echo $pos;?>" selected="selected"><?php echo $this->language->get('text_'.$pos); ?></option>
							  <?php } else { ?>
							  <option value="<?php echo $pos;?>"><?php echo $this->language->get('text_'.$pos); ?></option>
							  <?php } ?>
							  <?php } ?> 
							</select></td>
						</tr>
						<tr>
						  <td><?php echo $entry_status; ?></td>
						  <td><select name="pavmegamenu_module[<?php echo $module_row; ?>][status]">
							  <?php if ($module['status']) { ?>
							  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
							  <option value="0"><?php echo $text_disabled; ?></option>
							  <?php } else { ?>
							  <option value="1"><?php echo $text_enabled; ?></option>
							  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
							  <?php } ?>
							</select></td>
						</tr>
						<tr>
						  <td><?php echo $entry_sort_order; ?></td>
						  <td><input type="text" name="pavmegamenu_module[<?php echo $module_row; ?>][sort_order]" value="<?php echo $module['sort_order']; ?>" size="3" /></td>
						</tr>
							</table>
							
					<div id="megamenu-form">
						<?php require( "pavmegamenu_info.tpl" );?>
					</div>


				</div>
			</div>
		</div>
			<input type="hidden" value="" name="save_mode" id="save_mode"/>
			
			</form>
    </div>
  </div>
</div>
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
		if( confirm("Are you sure to delete this") ){
			var id = $(this).attr("rel").replace("id_","");
			window.location.href="<?php echo str_replace("&amp;","&",$actionDel);?>&id="+id;
		}
	} );
</script>

<?php echo $footer; ?>