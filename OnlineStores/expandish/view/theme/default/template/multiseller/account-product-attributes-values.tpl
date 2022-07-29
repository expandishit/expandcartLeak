<?php
if(isset($product_attributes) && count($product_attributes) > 0)
{
	foreach($product_attributes as $key=>$value)
	{

?>
<div class="attribute">
	<input type="hidden" name="product_attribute_data[<?php echo $value['attribute_id']; ?>][attribute_id]" value="<?php echo $value['attribute_id']; ?>"></td>
	<div class="o-heading"><?php echo $value['name']; ?><a class="ms-button-delete attribute_delete" title="<?php echo $ms_delete; ?>"></a></div>
	<div class="o-content">
		<?php if (count($languages) > 1) { ?>
		<div class="htabs attributes_language-tabs" id="attributes_language-tabs" >
			<?php foreach ($languages as $key=>$language) { ?>
			<a class="lang" href="#language_attr-<?php echo $language['language_id'].'-'.$value['attribute_id']; ?>"><img src="admin/view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
			<?php } ?>
		</div>
		<?php } ?>

		<?php
			reset($languages); $first = key($languages);
			foreach ($languages as $k => $language) {
		$langId = $language['language_id'];
		?>
		<div class="ms-language-div" id="language_attr-<?php echo $language['language_id'].'-'.$value['attribute_id']; ?>">
			<table class="ms-product">

				<tr>
					<td>
						<input type="text" name="product_attribute_data[<?php echo $value['attribute_id']; ?>][product_attribute_description][<?php echo $langId; ?>][text]" value="<?php echo $value['product_attribute_description'][$langId]['text']; ?>" />
						<p class="ms-note"><?php echo $ms_attributes_add_value ?></p>
					</td>
				</tr>
			</table>
		</div>
		<?php } ?>
	</div>
</div>
<script type="text/javascript">
	$('.attributes_language-tabs a').tabs();

</script>
<?php
	}
}
?>


