<?php echo $header; ?>

<div id="content" class="ms-account-profile row">
	<?php echo $content_top; ?>
	
	<div class="breadcrumb col-md-12">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?>
		<a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	
	<h2 class="heading-profile"> {{ lang('ms_sale_commission_pricelist_heading') }} :</h2>
	
	<?php if (isset($success) && ($success)) { ?>
		<div class="success"><?php echo $success; ?></div>
	<?php } ?>
	
	<?php if (isset($statustext) && ($statustext)) { ?>
		<div class="<?php echo $statusclass; ?>"><?php echo $statustext; ?></div>
	<?php } ?>

	<p class="warning main"></p>
	
	<form id="ms-sellerinfo" class="ms-form">
		<input type="hidden" name="action" id="ms_action" />
		
		<div class="content">
			<table class="table table-hover">
				<thead>
					<th style="width: 25%; <?php echo $th_alignment ?>">{{ lang('text_category_name') }}</th>
					<th style="<?php echo $th_alignment ?>">{{ lang('text_value') }}</th>
				</thead>
				<tbody>
					<?php foreach ($items as $item) { ?>
					<tr>
						<td><?php echo $item['category_name']; ?></td>
						<td><?php echo $item['value'] . ($item['value_type'] == 2 ? '%': ' (' . $currency . ')'); ?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		</form>
		

		
		<div class="buttons">
			<div class="left">
				<a href="<?php echo $link_back; ?>" class="button">
					<span><?php echo $button_back; ?></span>
				</a>
			</div>			
		</div>
	<?php echo $content_bottom; ?>
</div>

<?php echo $footer; ?>
