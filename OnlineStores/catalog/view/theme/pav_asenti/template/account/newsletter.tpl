<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" ); ?>
<?php echo $header; ?>

<?php if( $SPAN[0] ): ?>
	<aside class="span<?php echo $SPAN[0];?>">
		<?php echo $column_left; ?>
	</aside>
<?php endif; ?> 

<section class="span<?php echo $SPAN[1];?>">
	<div id="content">
		<?php echo $content_top; ?>
		<div class="breadcrumb">
			<?php foreach ($breadcrumbs as $breadcrumb) { ?>
			<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
			<?php } ?>
		</div>
		<h1><?php echo $heading_title; ?></h1>
		
		<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
			<div class="inner">
				<div class="content">
				  <table class="form table">
					<tr>
					    <td><?php echo $entry_newsletter; ?></td>
						<td>
							<?php if ($newsletter) { ?>
							<input type="radio" name="newsletter" value="1" checked="checked" />
							<?php echo $text_yes; ?>&nbsp;
							<input type="radio" name="newsletter" value="0" />
							<?php echo $text_no; ?>
							<?php } else { ?>
							<input type="radio" name="newsletter" value="1" />
							<?php echo $text_yes; ?>&nbsp;
							<input type="radio" name="newsletter" value="0" checked="checked" />
							<?php echo $text_no; ?>
							<?php } ?>
						</td>
					</tr>
				  </table>
				</div>				
			</div>
			<div class="buttons clearfix">
				<div class="left"><a href="<?php echo $back; ?>" class="button"><?php echo $button_back; ?></a></div>
				<div class="right"><input type="submit" value="<?php echo $button_continue; ?>" class="button" /></div>
			</div>
		</form>  
  
		<?php echo $content_bottom; ?>
	</div>
</section> 

<?php if( $SPAN[2] ): ?>
<aside class="span<?php echo $SPAN[2];?>">	
	<?php echo $column_right; ?>
</aside>
<?php endif; ?>

<?php echo $footer; ?>