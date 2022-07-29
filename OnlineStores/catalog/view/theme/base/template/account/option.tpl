<?php echo $header; ?>
<?php if ($success) { ?>
<div class="success"><?php echo $success; ?></div>
<?php } ?>
<?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
     <div class="content">
      <table class="form">
	  
	  <?php if($this->config->get('config_nicknames_on')){ ?>
        <tr>
          <td style="width:200px"><?php echo $entry_firstname; ?></td>
          <td><input type="text" name="nickname" value="<?php echo $nickname; ?>" />
          </td>
        </tr>
	<?php } ?>
        <tr>
          <td><?php echo $entry_lastname; ?></td>
         <td>
		    <?php if($email_bid==1) { ?>
            <input type="checkbox" checked="checked" value="1" name="email_bid">
			<?php }else{ ?>
			<input type="checkbox" value="1" name="email_bid">
			<?php } ?>
      
        </td>
        <tr>
          <td><?php echo $entry_email; ?></td>
          <td>
		  
		    <?php if($email_outbid==1) { ?>
            <input type="checkbox" checked="checked" value="1" name="email_outbid">
			<?php }else{ ?>
			<input type="checkbox" value="1" name="email_outbid">
			<?php } ?>
		  
		  </td>
        </tr>
        <tr>
          <td><?php echo $entry_telephone; ?></td>
          <td>
		     <?php if($email_finish==1){ ?>
            <input type="checkbox" checked="checked" value="1" name="email_finish">
			<?php }else{ ?>
			<input type="checkbox" value="1" name="email_finish">
			<?php } ?>
			
			</td>
        </tr>
		
        <tr>
          <td><?php echo $entry_fax; ?></td>
          <td>
		  
		     <?php if($email_sub==1) { ?>
			 
            <input type="checkbox" checked="checked" value="1" name="email_sub">
			
			<?php }else{ ?>
			<input type="checkbox" value="1" name="email_sub">
			<?php } ?>
			
			</td>
			
			
        </tr>
      </table>
    </div>
    <div class="buttons">
      <div class="left"><a href="<?php echo $back; ?>" class="button"><?php echo $button_back; ?></a></div>
      <div class="right">
        <input type="submit" value="<?php echo $button_continue; ?>" class="button" />
      </div>
    </div>
  </form>
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?>