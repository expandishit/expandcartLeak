<?php if (count($languages) > 1) { ?>
	<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
		<div id="language" class="pull-right">		
			<?php /* <?php echo $text_language; ?> */ ?>
		
			<?php foreach ($languages as $language) { ?>
				<img src="admin/view/image/flags/<?php echo $language['image']; ?>" alt="<?php echo $language['name']; ?>" title="<?php echo $language['name']; ?>" onclick="$('input[name=\'language_code\']').attr('value', '<?php echo $language['code']; ?>'); $(this).parent().parent().submit();" />
			<?php } ?>
			<input type="hidden" name="language_code" value="" />
			<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
		</div>
	</form>
<?php } ?>
