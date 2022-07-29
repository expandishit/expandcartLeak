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
		
		<div class="inner">
			<p><?php echo $text_balance; ?><b> <?php echo $balance; ?></b>.</p>
			<table class="list table">
				<thead class="hidden-phone">
					<tr>
						<td class="left"><?php echo $column_date_added; ?></td>
						<td class="left"><?php echo $column_description; ?></td>
						<td class="right"><?php echo $column_amount; ?></td>
					</tr>
				</thead>
				<tbody>
					<?php if ($transactions) { ?>
					<?php foreach ($transactions  as $transaction) { ?>
					<tr>
						<td class="left"><?php echo $transaction['date_added']; ?></td>
						<td class="left"><?php echo $transaction['description']; ?></td>
						<td class="right"><?php echo $transaction['amount']; ?></td>
					</tr>
					<?php } ?>
					<?php } else { ?>
					<tr>
						<td class="center" colspan="5"><?php echo $text_empty; ?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
  
		<div class="pagination"><?php echo $pagination; ?></div>		
		<div class="buttons clearfix">
			<div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
		</div>
		
		<?php echo $content_bottom; ?>
	</div>
</section> 

<?php if( $SPAN[2] ): ?>
<aside class="span<?php echo $SPAN[2];?>">	
	<?php echo $column_right; ?>
</aside>
<?php endif; ?>

<?php echo $footer; ?>