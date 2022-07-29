<?php echo $header; ?>
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
<?php if ($success) { ?>
<script>
    var notificationString = '<?php echo $success; ?>';
    var notificationType = 'success';
</script>
<?php } ?>
  <div class="box">
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
	
	    <div id="tabs" class="htabs">
		<a href="#tab_position"><?php echo $tab_position; ?></a>
		<a href="#tab_settings"><?php echo $tab_settings; ?></a>
		
		</div>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
	  <div id="tab_position">  
        <table id="module" class="table table-hover dataTable no-footer">
          <thead>
            <tr>
              <td class="left"><?php echo $entry_limit; ?></td>
              <td class="left"><?php echo $entry_image; ?></td>
              <td class="left"><?php echo $entry_layout; ?></td>
              <td class="left"><?php echo $entry_position; ?></td>
              <td class="left"><?php echo $entry_status; ?></td>
              <td class="right"><?php echo $entry_sort_order; ?></td>
              <td></td>
            </tr>
          </thead>

          <?php $module_row = 0; ?>
          <?php foreach ($modules as $module) { ?>
          <tbody id="module-row<?php echo $module_row; ?>">		  
            <tr>
              <td class="left"><input type="text" name="special_count_down_module[<?php echo $module_row; ?>][limit]" value="<?php echo $module['limit']; ?>" size="1" /></td>
              <td class="left"><input type="text" name="special_count_down_module[<?php echo $module_row; ?>][image_width]" value="<?php echo $module['image_width']; ?>" size="3" />
                <input type="text" name="special_count_down_module[<?php echo $module_row; ?>][image_height]" value="<?php echo $module['image_height']; ?>" size="3" />
                <?php if (isset($error_image[$module_row])) { ?>
                <span class="error"><?php echo $error_image[$module_row]; ?></span>
                <?php } ?></td>
              <td class="left"><select name="special_count_down_module[<?php echo $module_row; ?>][layout_id]">
                  <?php foreach ($layouts as $layout) { ?>
                  <?php if ($layout['layout_id'] == $module['layout_id']) { ?>
                  <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select></td>
              <td class="left"><select name="special_count_down_module[<?php echo $module_row; ?>][position]">
                  <?php if ($module['position'] == 'content_top') { ?>
                  <option value="content_top" selected="selected"><?php echo $text_content_top; ?></option>
                  <?php } else { ?>
                  <option value="content_top"><?php echo $text_content_top; ?></option>
                  <?php } ?>
                  <?php if ($module['position'] == 'content_bottom') { ?>
                  <option value="content_bottom" selected="selected"><?php echo $text_content_bottom; ?></option>
                  <?php } else { ?>
                  <option value="content_bottom"><?php echo $text_content_bottom; ?></option>
                  <?php } ?>
                  <?php if ($module['position'] == 'column_left') { ?>
                  <option value="column_left" selected="selected"><?php echo $text_column_left; ?></option>
                  <?php } else { ?>
                  <option value="column_left"><?php echo $text_column_left; ?></option>
                  <?php } ?>
                  <?php if ($module['position'] == 'column_right') { ?>
                  <option value="column_right" selected="selected"><?php echo $text_column_right; ?></option>
                  <?php } else { ?>
                  <option value="column_right"><?php echo $text_column_right; ?></option>
                  <?php } ?>
                </select></td>
              <td class="left"><select name="special_count_down_module[<?php echo $module_row; ?>][status]">
                  <?php if ($module['status']) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select></td>
              <td class="right"><input type="text" name="special_count_down_module[<?php echo $module_row; ?>][sort_order]" value="<?php echo $module['sort_order']; ?>" size="3" /></td>
              <td class="left"><a onclick="$('#module-row<?php echo $module_row; ?>').remove();" class="button btn btn-primary"><?php echo $button_remove; ?></a></td>
            </tr>
          </tbody>
          <?php $module_row++; ?>
          <?php } ?>
          <tfoot>
            <tr>
              <td colspan="6"></td>
              <td class="left"><a onclick="addModule();" class="button btn btn-primary"><?php echo $button_add_module; ?></a></td>
            </tr>
          </tfoot>
        </table>
		   </div>
		     <div id="tab_settings">
			  

 <div id="tab-auction">  

 

				<fieldset id="fieldset_1">
				<legend><?php echo $this->language->get('General Settings'); ?></legend>

				<table class="margin-form">            <tr>              <td><?php echo $entry_buy_now; ?></td>              <td><?php if ($config_buy_now) { ?>                <input type="radio" name="config_buy_now" value="1" checked="checked" />                <?php echo $text_yes; ?>                <input type="radio" name="config_buy_now" value="0" />                <?php echo $text_no; ?>                <?php } else { ?>                <input type="radio" name="config_buy_now" value="1" />                <?php echo $text_yes; ?>                <input type="radio" name="config_buy_now" value="0" checked="checked" />                <?php echo $text_no; ?>                <?php } ?></td>            </tr>            <tr>              <td><?php echo $entry_bid; ?></td>              <td><?php if ($config_bid) { ?>                <input type="radio" name="config_bid" value="1" checked="checked" />                <?php echo $text_yes; ?>                <input type="radio" name="config_bid" value="0" />                <?php echo $text_no; ?>                <?php } else { ?>                <input type="radio" name="config_bid" value="1" />                <?php echo $text_yes; ?>                <input type="radio" name="config_bid" value="0" checked="checked" />                <?php echo $text_no; ?>                <?php } ?></td>            </tr>            <tr style="display:none;">              <td><?php echo $entry_autobid; ?></td>              <td><?php if ($config_autobid) { ?>                <input type="radio" name="config_autobid" value="1" checked="checked" />                <?php echo $text_yes; ?>                <input type="radio" name="config_autobid" value="0" />                <?php echo $text_no; ?>                <?php } else { ?>                <input type="radio" name="config_autobid" value="1" />                <?php echo $text_yes; ?>                <input type="radio" name="config_autobid" value="0" checked="checked" />                <?php echo $text_no; ?>                <?php } ?></td>            </tr>          






				</table>  

				</fieldset>

				<br/>

				<fieldset id="fieldset_3">
				<legend>
                    <?php echo $this->language->get('Ending auctions block'); ?>
				</legend>




				<div class="margin-form">

				<?php if($config_display_popular_auctions_block_on){?>

				<input type="checkbox" checked="checked" value="1" class="" id="config_display_popular_auctions_block_on" name="config_display_popular_auctions_block_on">

				<?php } else {?>

				<input type="checkbox"  value="1" class="" id="config_display_popular_auctions_block_on" name="config_display_popular_auctions_block_on">

				<?php } ?>


				<label class="t" for="display_popular_auctions_block_on"><strong><?php echo $this->language->get('Enable'); ?></strong></label><br>


				<p class="preference_description">
                    <?php echo $this->language->get('Indicate, wheather you wish ending auctions block to be displayed in front office'); ?>
				</p>

				</div>
				<div class="clear"></div>

				
				</fieldset>
								
								
								
				<br/>


				<fieldset id="fieldset_4">
				<legend><?php echo $this->language->get('Subscriptions'); ?></legend>
				<div class="margin-form">
				
				<?php if($config_allow_subscriptions_on) {?>

				<input type="checkbox" checked="checked" value="1" class="" id="allow_subscriptions_on" name="config_allow_subscriptions_on">
				
				<?php } else{?>
				
				<input type="checkbox" value="1" class="" id="allow_subscriptions_on" name="config_allow_subscriptions_on">
				
				
				<?php } ?>
				<label class="t" for="allow_subscriptions_on"><strong><?php echo $this->language->get('Enable'); ?></strong></label><br>


				<p class="preference_description">
				<?php echo $entry_auction_allow;?>
				</p>

				</div>
				<div class="clear"></div>
				</fieldset>			
				<br/>							
										
										
	</div>				  
             
                  

</div>			 
			     
		   
      </form>
 
	</div>
  </div>
</div>
<script type="text/javascript"><!--
var module_row = <?php echo $module_row; ?>;

function addModule() {	
	html  = '<tbody id="module-row' + module_row + '">';
	html += '  <tr>';
	html += '    <td class="left"><input type="text" name="special_count_down_module[' + module_row + '][limit]" value="5" size="1" /></td>';
	html += '    <td class="left"><input type="text" name="special_count_down_module[' + module_row + '][image_width]" value="80" size="3" /> <input type="text" name="special_count_down_module[' + module_row + '][image_height]" value="80" size="3" /></td>';	
	html += '    <td class="left"><select name="special_count_down_module[' + module_row + '][layout_id]">';
	<?php foreach ($layouts as $layout) { ?>
	html += '      <option value="<?php echo $layout['layout_id']; ?>"><?php echo addslashes($layout['name']); ?></option>';
	<?php } ?>
	html += '    </select></td>';
	html += '    <td class="left"><select name="special_count_down_module[' + module_row + '][position]">';
	html += '      <option value="content_top"><?php echo $text_content_top; ?></option>';
	html += '      <option value="content_bottom"><?php echo $text_content_bottom; ?></option>';
	html += '      <option value="column_left"><?php echo $text_column_left; ?></option>';
	html += '      <option value="column_right"><?php echo $text_column_right; ?></option>';
	html += '    </select></td>';
	html += '    <td class="left"><select name="special_count_down_module[' + module_row + '][status]">';
    html += '      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
    html += '      <option value="0"><?php echo $text_disabled; ?></option>';
    html += '    </select></td>';
	html += '    <td class="right"><input type="text" name="special_count_down_module[' + module_row + '][sort_order]" value="" size="3" /></td>';
	html += '    <td class="left"><a onclick="$(\'#module-row' + module_row + '\').remove();" class="button btn btn-primary"><?php echo $button_remove; ?></a></td>';
	html += '  </tr>';
	html += '</tbody>';
	
	$('#module tfoot').before(html);
	
	module_row++;
}
//--></script> 
<script type="text/javascript"><!--
$('#tabs a').tabs(); 
$('#languages a').tabs(); 
$('#vtab-option a').tabs();
//--></script> 
<style>.small {    font-size: 0.85em;}legend {    background: none repeat scroll 0 0 #EBEDF4;    border: 1px solid #CCCED7;    font-weight: 700;    margin: 0;    padding: 0.2em 0.5em;    text-align: left;}.margin-form {    color: #7F7F7F;    font-size: 0.85em;    padding: 0 0 1em 260px;}label {    color: #585A69;    text-shadow: 0 1px 0 #FFFFFF;}label:after {    clear: both;}fieldset {    background-color: #EBEDF4;    border: 1px solid #CCCED7;    font-size: 1.1em;    margin: 0;    padding: 1em;}</style>
	
<?php echo $footer; ?>