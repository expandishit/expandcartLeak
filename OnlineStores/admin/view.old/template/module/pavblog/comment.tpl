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
				  <a onclick="__submit('save-edit')" class="button btn btn-primary"><?php echo $this->language->get('button_save_edit'); ?></a>
				  <a onclick="__submit('save-new')" class="button btn btn-primary"><?php echo $this->language->get('button_save_new'); ?></a>
			
					<a  href="<?php echo $action_delete;?>" class="button action-delete"><?php echo $this->language->get("button_delete"); ?></a>	
			
				</div>  
			</div>
			
			<div class="content">
				<div class="box-columns">
					
					 
					<form id="form" enctype="multipart/form-data" method="post" action="<?php echo $action;?>">
							<input type="hidden" name="action_mode" id="action_mode" value=""/>
							<input type="hidden" name="pavblog_comment[comment_id]"  value="<?php echo $comment['comment_id'];?>"/>
							 <table class="form">
								<tr>
									<td><?php echo $this->language->get('entry_created');?></td>
									<td><?php echo $comment['created'];?></td>
								</tr>
								<tr>
									<td><?php echo $this->language->get('text_username');?></td>
									<td><?php echo $comment['user'];?></td>
								</tr>
								<tr>
									<td><?php echo $this->language->get('text_email');?></td>
									<td><?php echo $comment['email'];?></td>
								</tr>
								<tr>
									<td><?php echo $this->language->get('entry_status');?></td>
									<td>
										<select name="pavblog_comment[status]">
											<?php foreach( $yesno as $k=>$v ) { ?>
											<option value="<?php echo $k;?>"<?php if( $k==$comment['status']) { ?>selected="selected"<?php } ?>><?php echo $this->language->get($v);?></option>
											<?php } ?>
										</select>
									</td>
								</tr>
								<tr>
									<td><?php echo $this->language->get('text_comment');?></td>
									<td><textarea rows="6" cols="90" name="pavblog_comment[comment]"><?php echo $comment['comment'];?></textarea></td>
								</tr>
							 </table>
					</form>
				 
				</div>
				
				
			</div>
		</div>	
		
		
 </div>
  
 <script type="text/javascript">
	$(".action-delete").click( function(){ 
		return confirm( "<?php echo $this->language->get("text_confirm_delete");?>" );
	} );
	function __submit( val ){
		$("#action_mode").val( val );
		$("#form").submit();
	}
	
 </script>
<?php echo $footer; ?>