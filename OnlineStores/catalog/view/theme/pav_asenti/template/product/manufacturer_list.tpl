<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" ); ?>
<?php echo $header; ?>

<!--Left-->
<?php if( $SPAN[0] ): ?>
	<aside class="span<?php echo $SPAN[0];?>">
		<?php echo $column_left; ?>
	</aside>
<?php endif; ?>

<!--Content-->
<section class="span<?php echo $SPAN[1];?>">
	<div id="content">
		<?php echo $content_top; ?>
		<div class="breadcrumb">
			<?php foreach ($breadcrumbs as $breadcrumb) { ?>
			<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
			<?php } ?>
		</div>
		
		<h1><?php echo $heading_title; ?></h1>
		
		<?php if ($categories) { ?>
			<p class="pav-brand-title">
				<b><?php echo $text_index; ?></b>
				<?php foreach ($categories as $category) { ?>
				&nbsp;&nbsp;&nbsp;<a href="index.php?route=product/manufacturer#<?php echo $category['name']; ?>"><b><?php echo $category['name']; ?></b></a>
				<?php } ?>
			</p>
			<?php foreach ($categories as $category) { ?>
			<div class="manufacturer-list">
				<div class="manufacturer-heading"><?php echo $category['name']; ?><a id="<?php echo $category['name']; ?>"></a></div>
				<nav class="manufacturer-content">
					<?php if ($category['manufacturer']) { ?>
					<?php for ($i = 0; $i < count($category['manufacturer']);) { ?>
					<ul>
						<?php $j = $i + ceil(count($category['manufacturer']) / 4); ?>
						<?php for (; $i < $j; $i++) { ?>
						<?php if (isset($category['manufacturer'][$i])) { ?>
						<li><a href="<?php echo $category['manufacturer'][$i]['href']; ?>"><?php echo $category['manufacturer'][$i]['name']; ?></a></li>
						<?php } ?>
						<?php } ?>
					</ul>
					<?php } ?>
					<?php } ?>
				</nav>
			</div>
			<?php } ?>
		<?php } else { ?>
		<div class="content"><?php echo $text_empty; ?></div>
		<div class="buttons clearfix">
			<div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
		</div>
		<?php } ?>
		<?php echo $content_bottom; ?>
	</div>
</section> 

<!--Right-->
<?php if( $SPAN[2] ): ?>
<aside class="span<?php echo $SPAN[2];?>">	
	<?php echo $column_right; ?>
</aside>
<?php endif; ?>

<?php echo $footer; ?>