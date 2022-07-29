<?php echo $header; ?>

<script>
    var catalog_link = '<?php echo HTTP_SERVER; ?>';
    var langCode = '<?php echo $direction == "rtl" ? "ar" : "en"; ?>';
</script>

<div id="content" class="ms-account-product-form">
	<?php echo $content_top; ?>

	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>

	<h1><?php echo $heading; ?></h1>

	<p class="warning main"></p>
	<form id="ms-new-product" method="post" enctype="multipart/form-data">
		<input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>" />
		<input type="hidden" name="action" id="ms_action" />

		<input type="hidden" name="list_until" value="<?php echo $list_until; ?>" />

		<div class="content">
     	<div id="general-tabs" class="htabs" style="display: inline-block;">
     		<a href="#tab-general"><?php echo $ms_account_product_tab_general; ?></a>

			<?php
			$data_tab_fields = array('model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'manufacturer', 'taxClass', 'subtract', 'stockStatus', 'dateAvailable','weight','cost');
			$intersection_fields = array_intersect($data_tab_fields, $this->config->get('msconf_product_included_fields'));
			?>
     		<?php if (!empty($intersection_fields)) { ?>
     		<a href="#tab-data"><?php echo $ms_account_product_tab_data; ?></a>
     		<?php } ?>

     		<a href="#tab-options"><?php echo $ms_account_product_tab_options; ?></a>

     		<a href="#tab-options-sku" id="id-options-sku" style="display: none;"><?php echo $ms_account_product_tab_options_sku; ?></a>
			
			<?php if ($advanced_product_attributes){ ?>
			<a href="#tab-advanced-attributes"><?php echo $ms_account_product_tab_advanced_attributes; ?></a>
			<?php } ?>
			<!--
			<?php if ($this->config->get('msconf_allow_specials')) { ?>
     		<a href="#tab-specials"><?php echo $ms_account_product_tab_specials; ?></a>
     		<?php } ?>
			-->
     		<?php if ($this->config->get('msconf_allow_discounts')) { ?>
     		<a href="#tab-discounts"><?php echo $ms_account_product_tab_discounts; ?></a>
     		<?php } ?>

     		<?php if ($warehouses) { ?>
     		<a href="#tab-warehouses"><?php echo $ms_account_product_tab_warehouses; ?></a>
     		<?php } ?>
     	</div>

     	<!-- general tab -->
     	<div id="tab-general">
     		<?php if (count($languages) > 1) { ?>
			<div class="htabs" id="language-tabs" style="display: inline-block">
				<?php foreach ($languages as $language) { ?>
				<a class="lang" href="#language<?php echo $language['language_id']; ?>"><img src="admin/view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
				<?php } ?>
			</div>
			<?php } ?>

			<?php
			reset($languages); $first = key($languages);
			foreach ($languages as $k => $language) {
				$langId = $language['language_id'];
				?>

				<div class="ms-language-div" id="language<?php echo $langId; ?>">
				<table class="ms-product">
					<tr><td colspan="2"><h3><?php echo $ms_account_product_name_description; ?></h3></td></tr>

					<tr>
						<td><span class="required">*</span> <?php echo $ms_account_product_name; ?></td>
						<td>
							<input type="text" name="languages[<?php echo $langId; ?>][product_name]" value="<?php echo $product['languages'][$langId]['name']; ?>" />
							<p class="ms-note"><?php echo $ms_account_product_name_note; ?></p>
							<p class="error" id="error_product_name_<?php echo $langId; ?>"></p>
						</td>
					</tr>

					<tr>
						<td><span class="required">*</span> <?php echo $ms_account_product_description; ?></td>
						<td>
							<!-- todo strip tags if rte disabled -->
							<textarea name="languages[<?php echo $langId; ?>][product_description]" class="<?php echo $this->config->get('msconf_enable_rte') ? "ckeditor" : ''; ?>"><?php echo $this->config->get('msconf_enable_rte') ? htmlspecialchars_decode($product['languages'][$langId]['description']) : strip_tags(htmlspecialchars_decode($product['languages'][$langId]['description'])); ?></textarea>
							<p class="ms-note"><?php echo $ms_account_product_description_note; ?></p>
							<p class="error" id="error_product_description_<?php echo $langId; ?>"></p>
						</td>
					</tr>

                    <?php if (in_array('metaDescription', $this->config->get('msconf_product_included_fields'))) { ?>
					<tr>
						<td><?=in_array('metaDescription', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_meta_description; ?></td>
						<td>
							<!-- todo strip tags if rte disabled -->
							<textarea name="languages[<?php echo $langId; ?>][product_meta_description]"><?php echo strip_tags(htmlspecialchars_decode($product['languages'][$langId]['meta_description'])); ?></textarea>
							<p class="ms-note"><?php echo $ms_account_product_meta_description_note; ?></p>
							<p class="error" id="error_product_meta_description_<?php echo $langId; ?>"></p>
							<p class="error" id="error_product_meta_description_required_<?=$langId?>"></p>
						</td>
					</tr>
                    <?php } ?>

                    <?php if (in_array('metaKeywords', $this->config->get('msconf_product_included_fields'))) { ?>
					<tr>
						<td><?=in_array('metaKeywords', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_meta_keyword; ?></td>
						<td>
							<!-- todo strip tags if rte disabled -->
							<textarea name="languages[<?php echo $langId; ?>][product_meta_keyword]"><?php echo strip_tags(htmlspecialchars_decode($product['languages'][$langId]['meta_keyword'])); ?></textarea>
							<p class="ms-note"><?php echo $ms_account_product_meta_keyword_note; ?></p>
							<p class="error" id="error_product_meta_keyword_<?php echo $langId; ?>"></p>
							<p class="error" id="error_product_meta_keyword_required_<?=$langId?>"></p>
						</td>
					</tr>
                    <?php } ?>

					<tr>
						<td><?php echo $ms_account_product_tags; ?></td>
						<td>
							<input type="text" name="languages[<?php echo $langId; ?>][product_tags]" value="<?php echo $product['languages'][$langId]['tags']; ?>" />
							<p class="ms-note"><?php echo $ms_account_product_tags_note; ?></p>
							<p class="error" id="error_product_tags_<?php echo $langId; ?>"></p>
						</td>
					</tr>



					<!-- Adding Seller Notes -->
					<tr>
						<td><?php echo $ms_account_product_seller_notes; ?></td>
						<td>
							<input type="text" name="languages[<?php echo $langId; ?>][seller_notes]" value="<?php echo $product['languages'][$langId]['seller_notes']; ?>" />
							<p class="ms-note"><?php echo $ms_account_product_seller_notes_note; ?></p>
							<p class="error" id="error_product_name_<?php echo $langId; ?>"></p>
						</td>
					</tr>



					<!-- /Adding Seller Notes -->



					<?php if (isset($multilang_attributes) && !empty($multilang_attributes)) { ?>
					<?php foreach ($multilang_attributes as &$attr) { ?>
					<tr>
						<td><?php if ($attr['required'] && $k == $first) { ?><span class="required">*</span> <?php } ?><?php echo $attr['mad.name']; ?></td>
						<td>
							<?php if ($attr['attribute_type'] == MsAttribute::TYPE_TEXT) { ?>
								<input type="text" name="languages[<?php echo $langId; ?>][product_attributes][<?php echo $attr['attribute_id']; ?>][value]" value="<?php echo isset($multilang_attribute_values[$attr['attribute_id']][$langId]) ? $multilang_attribute_values[$attr['attribute_id']][$langId]['value'] : '' ?>" />
								<input type="hidden" name="languages[<?php echo $langId; ?>][product_attributes][<?php echo $attr['attribute_id']; ?>][value_id]" value="<?php if (isset($normal_attribute_values[$attr['attribute_id']])) { echo $multilang_attribute_values[$attr['attribute_id']][$langId]['value_id']; } ?>" />
							<?php } ?>

							<?php if ($attr['attribute_type'] == MsAttribute::TYPE_TEXTAREA) { ?>
								<textarea name="languages[<?php echo $langId; ?>][product_attributes][<?php echo $attr['attribute_id']; ?>][value]"><?php echo isset($multilang_attribute_values[$attr['attribute_id']][$langId]) ? $multilang_attribute_values[$attr['attribute_id']][$langId]['value'] : '' ?></textarea>
								<input type="hidden" name="languages[<?php echo $langId; ?>][product_attributes][<?php echo $attr['attribute_id']; ?>][value_id]" value="<?php if (isset($normal_attribute_values[$attr['attribute_id']])) { echo $multilang_attribute_values[$attr['attribute_id']][$langId]['value_id']; } ?>" />
							<?php } ?>
							<p class="ms-note"><?php echo $attr['description']; ?></p>
							<p class="error"></p>
						</td>
					</tr>
					<?php } ?>
					<?php } ?>
				</table>
				</div>
			<?php } ?>

			<?php if ($codGeneratorAppStatus == true){ ?>
			<table class="ms-product">
				<tr><td colspan="2"><h3><?php echo $ms_account_product_codes; ?></h3></td></tr>

				<tr>
					<td><span class="required">*</span> <?php echo $ms_account_product_codes; ?></td>
					<td>
						<textarea name="product_codes" class="form-control"><?php foreach($product['codes'] as $code){echo $code['code']."\r\n";}?></textarea>
						<p class="ms-note"><?php echo $ms_account_product_codes_note; ?></p>
						<p class="error" id="error_product_codes"></p>
					</td>
				</tr>
			</table>
			<?php
			}
			?>


			<table class="ms-product">
				<tr><td colspan="2"><h3><?php echo $ms_account_product_price_attributes; ?></h3></td></tr>

				<tr>
					<td><span class="required">*</span> <?php echo $ms_account_product_category; ?></td>

					<td id="product_category_block">
						<select name="product_category<?= $msconf_allow_multiple_categories ? '[]' : ''; ?>" <?= $msconf_allow_multiple_categories ? 'multiple' : '' ?> class="select2 form-control">
						<!-- <option value=""><?php echo ''; ?></option> -->
						<?php foreach ($categories as $category) { ?>
						<?php if($msconf_enable_categories && $msconf_enable_shipping == 2) { ?>
						<?php if($product['shipping'] == 1 || $product['shipping'] == NULL) { ?>
						<?php if(in_array($category['category_id'],$msconf_physical_product_categories)) { ?>
						<option value="<?php echo $category['category_id']; ?>" <?php if (in_array($category['category_id'], explode(',',$product['category_id'])) && !$category['disabled']) { ?>selected="selected"<?php } ?> <?php echo ($category['disabled'] ? 'disabled' : ''); ?>><?php echo $category['name']; ?></option>
						<?php }} else { ?>
						<?php if(in_array($category['category_id'],$msconf_digital_product_categories)) { ?>
						<option value="<?php echo $category['category_id']; ?>" <?php if (in_array($category['category_id'], explode(',',$product['category_id'])) && !$category['disabled']) { ?>selected="selected"<?php } ?> <?php echo ($category['disabled'] ? 'disabled' : ''); ?>><?php echo $category['name']; ?></option>
						<?php }} ?>
						<?php } else { ?>
						<option value="<?php echo $category['category_id']; ?>" <?php if (in_array($category['category_id'], explode(',',$product['category_id'])) && !$category['disabled']) { ?>selected="selected"<?php } ?> <?php echo ($category['disabled'] ? 'disabled' : ''); ?>><?php echo $category['name']; ?></option>
						<?php }} ?>
						</select>

						<p class="ms-note"><?php echo $ms_account_product_category_note; ?></p>
						<p class="error" id="error_product_category"></p>
					</td>

				</tr>

				<tr>
					<td><span class="required">*</span> <?php echo $ms_account_product_price; ?></td>

					<td>
						<span class="vertical-align: auto"><?php echo $this->currency->getSymbolLeft($this->config->get('config_currency')); ?></span>
						<input type="text" name="product_price" value="<?php echo $product['price']; ?>" <?php if (isset($seller['commissions']) && $seller['commissions'][MsCommission::RATE_LISTING]['percent'] > 0) { ?>class="ms-price-dynamic"<?php } ?> />
						<span class="vertical-align: auto"><?php echo $this->currency->getSymbolRight($this->config->get('config_currency')); ?></span>
						<p class="ms-note"><?php echo $ms_account_product_price_note; ?></p>
						<p class="error" id="error_product_price"></p>
					</td>
				</tr>

				<?php if ($msconf_enable_shipping == 2) { ?>
				<tr>
					<td><?php echo $ms_account_product_enable_shipping; ?></td>
					<td>
						<input type="radio" name="product_enable_shipping" value="1" <?php if($product['shipping'] == 1) { ?> checked="checked" <?php } ?>/>
						<?php echo $text_yes; ?>
						<input type="radio" name="product_enable_shipping" value="0" <?php if($product['shipping'] == 0) { ?> checked="checked" <?php } ?>/>
						<?php echo $text_no; ?>
						<p class="ms-note"><?php echo $ms_account_product_enable_shipping_note; ?></p>
						<p class="error" id="error_product_enable_shipping"></p>
					</td>
				</tr>
				<?php } ?>

				<tr <?php if ($msconf_enable_quantities == 0 || ($msconf_enable_shipping != 1 && $msconf_enable_quantities == 2 && isset($product['shipping']) && $product['shipping'] == 0) || (isset($seller_group['product_quantity']) && $seller_group['product_quantity'] != 0)) { ?>style="display: none"<?php } ?>>
					<td><?=in_array('quantity', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_quantity; ?> </td>
					<td>
						<input type="text" name="product_quantity" value="<?php echo $product['quantity']; ?>" <?php if ($msconf_enable_quantities < 2 || (isset($seller_group['product_quantity']) && $seller_group['product_quantity'] != 0)) { ?>class="ffUnchangeable"<?php } ?> />
						<p class="ms-note"><?php echo $ms_account_product_quantity_note; ?></p>
						<p class="error" id="error_product_quantity"></p>
					</td>
				</tr>

				<tr>
				<td><?=in_array('min_quantity', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_min_quantity; ?></td>
				<td>
					<input type="text" name="product_minimum" value="<?php echo $product['minimum']; ?>" <?php if ($msconf_enable_quantities < 2 || (isset($seller_group['product_quantity']) && $seller_group['product_quantity'] != 0)) { ?>class="ffUnchangeable"<?php } ?> />
					<p class="ms-note"><?php echo $ms_account_product_min_quantity_note; ?></p>
					<p class="error" id="error_product_min_quantity"></p>
				</td>
				</tr>

				<?php if (isset($normal_attributes) && !empty($normal_attributes)) { ?>
				<?php foreach ($normal_attributes as $attr) { ?>
				<tr>
					<td>
						<?php if ($attr['required']) { ?> <span class="required">*</span> <?php } ?><?php echo $attr['name']; ?>
					</td>

					<td>
						<?php if ($attr['attribute_type'] == MsAttribute::TYPE_SELECT) { ?>
							<select name="product_attributes[<?php echo $attr['attribute_id']; ?>]">
							<option value=""><?php echo $text_select; ?></option>
							<?php foreach ($attr['values'] as $attr_value) { ?>
							<option value="<?php echo $attr_value['attribute_value_id']; ?>" <?php if (isset($normal_attribute_values[$attr['attribute_id']]) && array_key_exists($attr_value['attribute_value_id'], $normal_attribute_values[$attr['attribute_id']])) { ?>selected="selected"<?php } ?>><?php echo $attr_value['name']; ?></option>
							<?php } ?>
							</select>
						<?php } ?>

						<?php if ($attr['attribute_type'] == MsAttribute::TYPE_RADIO) { ?>
						<?php foreach ($attr['values'] as $attr_value) { ?>
							<input type="radio" name="product_attributes[<?php echo $attr['attribute_id']; ?>]" value="<?php echo $attr_value['attribute_value_id']; ?>" <?php if (isset($normal_attribute_values[$attr['attribute_id']]) && array_key_exists($attr_value['attribute_value_id'], $normal_attribute_values[$attr['attribute_id']])) { ?>checked="checked"<?php } ?> />
							<label><?php echo $attr_value['name']; ?></label>
							<br />
						<?php } ?>
						<?php } ?>

						<?php if ($attr['attribute_type'] == MsAttribute::TYPE_IMAGE) { ?>
						<?php foreach ($attr['values'] as $attr_value) { ?>
							<input type="radio" name="product_attributes[<?php echo $attr['attribute_id']; ?>]" value="<?php echo $attr_value['attribute_value_id']; ?>" <?php if (isset($normal_attribute_values[$attr['attribute_id']]) && array_key_exists($attr_value['attribute_value_id'], $normal_attribute_values[$attr['attribute_id']])) { ?>checked="checked"<?php } ?> style="vertical-align: middle"/>
							<label><?php echo $attr_value['name']; ?></label>
							<img src="<?php echo $attr_value['image']; ?>" style="vertical-align: middle; padding: 1px; border: 1px solid #DDDDDD; margin-bottom: 10px" />
							<br />
						<?php } ?>
						<?php } ?>

						<?php if ($attr['attribute_type'] == MsAttribute::TYPE_CHECKBOX) { ?>
						<?php foreach ($attr['values'] as $attr_value) { ?>
							<input type="checkbox" name="product_attributes[<?php echo $attr['attribute_id']; ?>][]" value="<?php echo $attr_value['attribute_value_id']; ?>" <?php if (isset($normal_attribute_values[$attr['attribute_id']]) && array_key_exists($attr_value['attribute_value_id'], $normal_attribute_values[$attr['attribute_id']])) { ?>checked="checked"<?php } ?> />
							<label><?php echo $attr_value['name']; ?></label>
							<br />
						<?php } ?>
						<?php } ?>

						<?php if ($attr['attribute_type'] == MsAttribute::TYPE_TEXT) { ?>
							<input type="text" name="product_attributes[<?php echo $attr['attribute_id']; ?>][value]" value="<?php if (isset($normal_attribute_values[$attr['attribute_id']])) { echo current(reset($normal_attribute_values[$attr['attribute_id']])); } ?>" />
							<input type="hidden" name="product_attributes[<?php echo $attr['attribute_id']; ?>][value_id]" value="<?php if (isset($normal_attribute_values[$attr['attribute_id']])) { echo key($normal_attribute_values[$attr['attribute_id']]); } ?>" />
						<?php } ?>

						<?php if ($attr['attribute_type'] == MsAttribute::TYPE_TEXTAREA) { ?>
							<textarea name="product_attributes[<?php echo $attr['attribute_id']; ?>][value]" cols="40" rows="5"><?php if (isset($normal_attribute_values[$attr['attribute_id']])) { echo current(reset($normal_attribute_values[$attr['attribute_id']])); } ?></textarea>
							<input type="hidden" name="product_attributes[<?php echo $attr['attribute_id']; ?>][value_id]" value="<?php if (isset($normal_attribute_values[$attr['attribute_id']])) { echo key($normal_attribute_values[$attr['attribute_id']]); } ?>" />
						<?php } ?>

						<?php if ($attr['attribute_type'] == MsAttribute::TYPE_DATE) { ?>
							<input type="text" name="product_attributes[<?php echo $attr['attribute_id']; ?>][value]" value="<?php if (isset($normal_attribute_values[$attr['attribute_id']])) { echo current(reset($normal_attribute_values[$attr['attribute_id']])); } ?>" class="date" />
							<input type="hidden" name="product_attributes[<?php echo $attr['attribute_id']; ?>][value_id]" value="<?php if (isset($normal_attribute_values[$attr['attribute_id']])) { echo key($normal_attribute_values[$attr['attribute_id']]); } ?>" />
						<?php } ?>

						<?php if ($attr['attribute_type'] == MsAttribute::TYPE_DATETIME) { ?>
							<input type="text" name="product_attributes[<?php echo $attr['attribute_id']; ?>][value]" value="<?php if (isset($normal_attribute_values[$attr['attribute_id']])) { echo current(reset($normal_attribute_values[$attr['attribute_id']])); } ?>" class="datetime" />
							<input type="hidden" name="product_attributes[<?php echo $attr['attribute_id']; ?>][value_id]" value="<?php if (isset($normal_attribute_values[$attr['attribute_id']])) { echo key($normal_attribute_values[$attr['attribute_id']]); } ?>" />
						<?php } ?>

						<?php if ($attr['attribute_type'] == MsAttribute::TYPE_TIME) { ?>
							<input type="text" name="product_attributes[<?php echo $attr['attribute_id']; ?>][value]" value="<?php if (isset($normal_attribute_values[$attr['attribute_id']])) { echo current(reset($normal_attribute_values[$attr['attribute_id']])); } ?>" class="time" />
							<input type="hidden" name="product_attributes[<?php echo $attr['attribute_id']; ?>][value_id]" value="<?php if (isset($normal_attribute_values[$attr['attribute_id']])) { echo key($normal_attribute_values[$attr['attribute_id']]); } ?>" />
						<?php } ?>

						<p class="ms-note"><?php echo $attr['description']; ?></p>
						<p class="error"></p>
					</td>
				</tr>
				<?php } ?>
				<?php } ?>


				<tr><td colspan="2"><h3><?php echo $ms_account_product_files; ?></h3></td></tr>

				<!-- <tr>
					<td><?php if ($msconf_images_limits[0] > 0) { ?><span class="required">*</span><?php } ?> <?php echo $ms_account_product_image; ?></td>
					<td>
						<a name="ms-file-addimages" id="ms-file-addimages" class="button"><span><?php echo $ms_button_select_images; ?></span></a>
						<p class="ms-note"><?php echo $ms_account_product_image_note; ?></p>
						<div class="error" id="error_product_image"></div>
						<div class="image progress"></div>
						<div class="product_image_files">
						<?php if (isset($product['images'])) { ?>
						<?php $i = 0; ?>
						<?php foreach ($product['images'] as $image) { ?>
							<div class="ms-image">
								<input type="hidden" name="product_images[]" value="<?php echo $image['name']; ?>" />
								<img src="<?php echo $image['thumb']; ?>" />
								<img class="ms-remove" src="expandish/view/theme/default/image/remove.png" />
							</div>
						<?php $i++; ?>
						<?php } ?>
						<?php } ?>
						</div>
					</td>
                </tr> -->
                
                
                
                
                
                
                
                <!-- TEST IMAGES -->
                <tr>
					<td><?php if ($msconf_images_limits[0] > 0) { ?><span class="required">*</span><?php } ?> <?php echo $ms_account_product_image; ?></td>
					<td>
						<a name="ms-file-addimages" id="ms-file-addimages" class="button">
                            <span><?php echo $ms_button_select_images; ?></span>
                        </a>
						<p class="ms-note"><?php echo $ms_account_product_image_note; ?></p>
						<div class="error" id="error_product_image"></div>
						<div class="image progress"></div>
						<div class="product_image_files">
						<?php if (isset($product_images)) { ?>
						<?php $i = 0; ?>
                        <?php foreach ($product_images as $image) { ?>
                            <?php $imgHasOpt = isset($image['isOption']) && $image['isOption'] == true; ?>
							<div class="ms-image">
								<input type="hidden" class="product_image" value="<?php echo $image['image']; ?>" />
                                <input type="hidden" class="product_option_id" value="<?php echo $imgHasOpt ? $image['option']['id'] : 0; ?>" />
                                <input type="hidden" class="product_option_value_id" value="<?php echo $imgHasOpt ? $image['option']['value_id'] : 0; ?>" />
								<img src="<?php echo $image['thumb']; ?>" />
                                <?php if(isset($poip_installed) && $poip_installed == true && isset($product['product_id']) && (int)$product['product_id'] > 0) { ?>
                                    <select class="thumb-option-selector" onchange="handleImageOptionChange(this)">
                                        <option value="0-0">
                                            <?php echo isset($text_general_image) ? $text_general_image : "General Image";?>
                                        </option>
                                        
                                        <?php foreach($product_options as $option) { ?>
                                            <?php foreach($option['product_option_value'] as $value) { ?>
                                                <option 
                                                    <?php echo ($imgHasOpt && $option['option_id'] == $image['option']['id'] && $value['option_value_id'] == $image['option']['value_id']) ? 'selected' : '';?>
                                                    value="<?php echo $option['option_id'] . '-' . $value['option_value_id']; ?>">
                                                    <?php echo $option['name'] . ' > ' . $value['name']; ?>
                                                </option>
                                            <?php } ?>
                                        <?php } ?>
                                        
                                        
                                    </select>
                                <?php } ?>
                                <img class="ms-remove" src="expandish/view/theme/default/image/remove.png" />
							</div>
						<?php $i++; ?>
						<?php } ?>
						<?php } ?>
						</div>
					</td>
				</tr>
                <!-- END TEST IMAGES -->
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
				<?php if (isset($msconfig_allow_download) && $msconfig_allow_download == 1) { ?>
				<tr>
					<td><?php if ($msconf_downloads_limits[0] > 0) { ?><span class="required">*</span><?php } ?> <?php echo $ms_account_product_download; ?></td>
					<td>
						<!--<input type="file" name="ms-file-addfiles" id="ms-file-addfiles" />-->
						<a name="ms-file-addfiles" id="ms-file-addfiles" class="button"><span><?php echo $ms_button_select_files; ?></span></a>
						<p class="ms-note"><?php echo $ms_account_product_download_note; ?></p>
						<div class="error" id="error_product_download"></div>
						<div class="download progress"></div>
						<div class="product_download_files">
						<?php if (isset($product['downloads'])) { ?>
						<?php $i = 0; ?>
						<?php foreach ($product['downloads'] as $download) { ?>
							<div class="ms-download">
								<input type="hidden" name="product_downloads[<?php echo $i; ?>][download_id]" value="<?php echo isset($clone) ? '' : $download['id']; ?>" />
								<input type="hidden" name="product_downloads[<?php echo $i; ?>][filename]" value="<?php echo (isset($clone)) ? $download['src'] : ''; ?>" />
								<span class="ms-download-name"><?php echo $download['name']; ?></span>
								<div class="ms-buttons">
									<a href="<?php echo $download['href']; ?>" class="ms-button-download" title="<?php echo $ms_download; ?>"></a>
										<!--<input id="ms-update-<?php echo $download['id']; ?>" name="ms-update-<?php echo $download['id']; ?>" class="ms-file-updatedownload" type="file" multiple="false" />-->
									<a id="ms-update-<?php echo $download['id']; ?>" name="ms-update-<?php echo $download['id']; ?>" class="ms-file-updatedownload ms-button-update" title="<?php echo $ms_update; ?>"></a>
									<a class="ms-button-delete" title="<?php echo $ms_delete; ?>"></a>
								</div>
							</div>
						<?php $i++; ?>
						<?php } ?>
						<?php } ?>
						</div>

						<div style="display: none">
							<input type="checkbox" name="push_downloads" id="push_downloads" />
							<label><?php echo $ms_account_product_push; ?></label>
							<p class="ms-note"><?php echo $ms_account_product_push_note; ?></p>
						</div>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td><?php echo $ms_account_product_file; ?></td>
					<td>
						<!--<input type="file" name="ms-file-addfiles" id="ms-file-addfiles" />-->
						<a name="ms-product-addfiles" id="ms-product-addfiles" class="button"><span><?php echo $ms_button_select_files; ?></span></a>
						<p class="ms-note"><?php echo $ms_product_file_download_note; ?></p>
						<div class="error" id="error_product_file_download"></div>
						<div class="download progress product_file"></div>
						<div class="ms_product_download_files">
						<?php if (isset($product['product_file'])) { ?>
							<div class="ms-download">
								<input type="hidden" name="product_file" value="<?php echo $product['product_file']['name'] ?>" />
								<span class="ms-download-name"><?php echo $product['product_file']['mask']; ?></span>
								<div class="ms-buttons">
									<a class="ms-button-delete" title="<?php echo $ms_delete; ?>"></a>
								</div>
							</div>
						<?php } ?>
						</div>
					</td>
				</tr>
				<?php if ($seller['ms.product_validation'] == MsProduct::MS_PRODUCT_VALIDATION_APPROVAL) { ?>
				<tr><td colspan="2"><h3><?php echo $ms_account_product_message_reviewer; ?></h3></td></tr>

				<tr>
					<td><?php echo $ms_account_product_message; ?></td>
					<td>
						<textarea name="product_message"></textarea>
						<p class="ms-note"><?php echo $ms_account_product_message_note; ?></p>
						<p class="error" id="error_product_message"></p>
					</td>
				</tr>
				<?php } ?>
			</table>
		</div>

        <!-- data tab -->
        <div id="tab-data">
            <table class="ms-product">
                <?php if (in_array('model', $this->config->get('msconf_product_included_fields'))) { ?>
				<?php if($product_classification){ ?>
				<tr>
					<td><?=in_array('model', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_model; ?></td>
					<td><select name="product_model" class="form-control" >
							<?php foreach ($product_classification_models as $model) { ?>
							<option value="<?php echo $model['name']; ?>" ><?php echo $model['name']; ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
				<?php }else{ ?>
                <tr>
                    <td><?=in_array('model', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_model; ?></td>
                    <td>
                        <input type="text" name="product_model" value="<?php echo $product['model']; ?>" />
                        <p class="error" id="error_product_model"></p>
                    </td>
                </tr>
                <?php } } ?>
				<?php if (in_array('cost', $this->config->get('msconf_product_included_fields'))) { ?>
                <tr>
                    <td><?=in_array('cost', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_cost; ?></td>
                    <td>
                        <input type="text" name="product_cost" value="<?php echo $product['cost_price']; ?>" />
                        <p class="error" id="error_product_cost"></p>
                    </td>
                </tr>
                <?php } ?>
                <?php if (in_array('sku', $this->config->get('msconf_product_included_fields'))) { ?>
                <tr>
                    <td><?=in_array('sku', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_sku; ?></td>
                    <td>
                        <input type="text" name="product_sku" value="<?php echo $product['sku']; ?>" />
						<p class="ms-note"><?php echo $ms_account_product_sku_note; ?></p>
						<p class="error" id="error_product_sku"></p>
                    </td>
                </tr>
                <?php } ?>
                <?php /*if (in_array('upc', $this->config->get('msconf_product_included_fields'))) { ?>
                <tr>
                    <td><?=in_array('upc', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_upc; ?></td>
                    <td>
                        <input type="text" name="product_upc" value="<!php echo $product['upc']; ?>" />
						<p class="ms-note"><!?php echo $ms_account_product_upc_note; ?></p>
						<p class="error" id="error_product_upc"></p>
                    </td>
                </tr>
                <?php } ?>
                <?php if (in_array('ean', $this->config->get('msconf_product_included_fields'))) { ?>
                <tr>
                    <td><?=in_array('ean', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_ean; ?></td>
                    <td>
                        <input type="text" name="product_ean" value="<!?php echo $product['ean']; ?>" />
						<p class="ms-note"><?php echo $ms_account_product_ean_note; ?></p>
						<p class="error" id="error_product_ean"></p>
                    </td>
                </tr>
                <?php } ?>
                <?php if (in_array('jan', $this->config->get('msconf_product_included_fields'))) { ?>
                <tr>
                    <td><?=in_array('jan', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_jan; ?></td>
                    <td>
                        <input type="text" name="product_jan" value="<!?php echo $product['jan']; ?>" />
						<p class="ms-note"><?php echo $ms_account_product_jan_note; ?></p>
						<p class="error" id="error_product_jan"></p>
                    </td>
                </tr>
                <?php } ?>
                <?php if (in_array('isbn', $this->config->get('msconf_product_included_fields'))) { ?>
                <tr>
                    <td><?=in_array('isbn', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_isbn; ?></td>
                    <td>
                        <input type="text" name="product_isbn" value="<!?php echo $product['isbn']; ?>" />
						<p class="ms-note"><?php echo $ms_account_product_isbn_note; ?></p>
						<p class="error" id="error_product_isbn"></p>
                    </td>
                </tr>
                <?php } ?>
                <?php if (in_array('mpn', $this->config->get('msconf_product_included_fields'))) { ?>
                <tr>
                    <td><?=in_array('mpn', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_mpn; ?></td>
                    <td>
                        <input type="text" name="product_mpn" value="<!?php echo $product['mpn']; ?>" />
						<p class="ms-note"><?php echo $ms_account_product_mpn_note; ?></p>
						<p class="error" id="error_product_mpn"></p>
                    </td>
                </tr>
                <?php } */?>
                <?php if (in_array('manufacturer', $this->config->get('msconf_product_included_fields'))) { ?>
				<?php if($product_classification){ ?>
				<tr>
					<td><?=in_array('model', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_model; ?></td>
					<td><select name="product_manufacturer" class="form-control" >
							<?php foreach ($product_classification_brands as $brand) { ?>
							<option value="<?php echo $brand['name']; ?>" ><?php echo $brand['name']; ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
				<?php }else{ ?>
                <tr>
                    <td><?=in_array('manufacturer', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_manufacturer; ?></td>
                    <td>
                        <input type="text" name="product_manufacturer" value="<?php echo $product['manufacturer'] ?>" />
                        <input type="hidden" name="product_manufacturer_id" value="<?php echo $product['manufacturer_id']; ?>" />
                        <p class="ms-note"><?php echo $ms_account_product_manufacturer_note; ?></p>
                        <p class="error" id="error_product_manufacturer"></p>
                    </td>
                </tr>
                <?php } } ?>

				<?php if (in_array('weight', $this->config->get('msconf_product_included_fields'))) { ?>
                <tr>
                    <td><?=in_array('weight', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_weight_type; ?></td>
                    <td>
						<select name="product_weight_class_id">
                            <?php foreach ($weight_classes as $weight_class) { ?>
                            <?php if ($weight_class['weight_class_id'] == $product['weight_class_id']) { ?>
                            <option value="<?php echo $weight_class['weight_class_id']; ?>" selected="selected"><?php echo $weight_class['title']; ?></option>
                            <?php } else { ?>
                            <option value="<?php echo $weight_class['weight_class_id']; ?>"><?php echo $weight_class['title']; ?></option>
                            <?php } ?>
                            <?php } ?>
                        </select>
					</td>
                </tr>
				<?php } ?>
				<?php if (in_array('weight', $this->config->get('msconf_product_included_fields'))) { ?>
                <tr>
                    <td><?=in_array('weight', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_weight; ?></td>
					<td>
						<input type="text" name="product_weight" value="<?php echo $product['weight']; ?>" />
						<p class="error" id="error_product_weight"></p>
                    </td>
                </tr>
                <?php } ?>

				<?php if (in_array('dimensions', $this->config->get('msconf_product_included_fields'))) { ?>
				<?php $required = in_array('dimensions', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?>
                <tr>
                    <td><?php echo $ms_account_product_dimensions_type; ?></td>
                    <td>
						<select name="product_length_class_id">
                            <?php foreach ($length_classes as $length_class) { ?>
                            <?php if ($length_class['length_class_id'] == $product['length_class_id']) { ?>
                            <option value="<?php echo $length_class['length_class_id']; ?>" selected="selected"><?php echo $length_class['title']; ?></option>
                            <?php } else { ?>
                            <option value="<?php echo $length_class['length_class_id']; ?>"><?php echo $length_class['title']; ?></option>
                            <?php } ?>
                            <?php } ?>
                        </select>
					</td>
                </tr>
                <tr>
                    <td><?= $required . ' ' . $ms_account_product_length ?></td>
					<td>
						<input type="number" name="product_length" value="<?php echo floatval($product['length']); ?>" />
						<p class="error" id="error_product_length"></p>
                    </td>
                </tr>
                <tr>
                    <td><?= $required . ' ' .  $ms_account_product_width ?></td>
					<td>
						<input type="number" name="product_width" value="<?php echo floatval($product['width']); ?>" />
						<p class="error" id="error_product_width"></p>
                    </td>
                </tr>
                <tr>
                    <td><?= $required . ' ' .  $ms_account_product_height ?></td>
					<td>
						<input type="number" name="product_height" value="<?php echo floatval($product['height']); ?>" />
						<p class="error" id="error_product_height"></p>
                    </td>
                </tr>
				<?php } ?>

                <?php if (in_array('taxClass', $this->config->get('msconf_product_included_fields'))) { ?>
                <tr>
                    <td><?=in_array('taxClass', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_tax_class; ?></td>
                    <td>
                        <select name="product_tax_class_id">
                            <option value="0"><?php echo $text_none; ?></option>
                            <?php foreach ($tax_classes as $tax_class) { ?>
                            <?php if ($tax_class['tax_class_id'] == $product['tax_class_id']) { ?>
                            <option value="<?php echo $tax_class['tax_class_id']; ?>" selected="selected"><?php echo $tax_class['title']; ?></option>
                            <?php } else { ?>
                            <option value="<?php echo $tax_class['tax_class_id']; ?>"><?php echo $tax_class['title']; ?></option>
                            <?php } ?>
                            <?php } ?>
						</select>
						<p class="error" id="error_product_tax_class_id"></p>
                    </td>
                </tr>
                <?php } ?>
                <?php if (in_array('subtract', $this->config->get('msconf_product_included_fields'))) { ?>
                <tr>
                    <td><?=in_array('subtract', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_subtract; ?></td>
                    <td>
                        <select name="product_subtract">
                            <?php if ($product['subtract']) { ?>
                            <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                            <option value="0"><?php echo $text_no; ?></option>
                            <?php } else { ?>
                            <option value="1"><?php echo $text_yes; ?></option>
                            <option value="0" selected="selected"><?php echo $text_no; ?></option>
                            <?php } ?>
						</select>
						<p class="error" id="error_product_subtract"></p>
                    </td>
                </tr>
                <?php } ?>
                <?php if (in_array('stockStatus', $this->config->get('msconf_product_included_fields'))) { ?>
                <tr>
                    <td><?=in_array('stockStatus', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_stock_status; ?></td>
                    <td>
                        <select name="product_stock_status_id">
                            <?php foreach ($stock_statuses as $stock_status) { ?>
                            <?php if ($stock_status['stock_status_id'] == $product['stock_status_id']) { ?>
                            <option value="<?php echo $stock_status['stock_status_id']; ?>" selected="selected"><?php echo $stock_status['name']; ?></option>
                            <?php } else { ?>
                            <option value="<?php echo $stock_status['stock_status_id']; ?>"><?php echo $stock_status['name']; ?></option>
                            <?php } ?>
                            <?php } ?>
						</select>
						<p class="error" id="error_product_stock_status_id"></p>
                    </td>
                </tr>
                <?php } ?>
                <?php if (in_array('dateAvailable', $this->config->get('msconf_product_included_fields'))) { ?>
                <tr>
                    <td><?=in_array('dateAvailable', $this->config->get('msconf_product_mandatory_fields')) ? '<span class="required">*</span>' : ''?> <?php echo $ms_account_product_date_available; ?></td>
                    <td>
						<input type="text" name="product_date_available" value="<?php echo $date_available; ?>" size="12" class="date" />
						<p class="error" id="error_product_date_available"></p>
					</td>
                </tr>
                <?php } ?>
            </table>
        </div>

		<!-- options tab -->
		<div id="tab-options"></div>

		<!-- options sku tab -->
		<div id="tab-options-sku"></div>

		<!-- attributes tab -->
		<div id="tab-attributes"></div>

		<?php if ($advanced_product_attributes){ ?>
		<!-- attributes tab -->
		<div id="tab-advanced-attributes"></div>
		<?php } ?>



			<!-- specials tab
		<?php if ($this->config->get('msconf_allow_specials')) { ?>
		<div id="tab-specials">
			<h3><?php echo $ms_account_product_tab_specials; ?></h3>
			<p class="error" id="error_specials"></p>

			<table class="list">
				<thead>
				<tr>
					<td><span class="required">*</span><?php echo $ms_account_product_priority; ?></td>
					<td><span class="required">*</span><?php echo $ms_account_product_price; ?></td>
					<td><span class="required">*</span><?php echo $ms_account_product_date_start; ?></td>
					<td><span class="required">*</span><?php echo $ms_account_product_date_end; ?></td>
					<td></td>
				</tr>
				</thead>

				<tbody>

				<tr class="ffSample">
					<td><input type="text" name="product_specials[0][priority]" value="" size="2" /></td>
					<td><input type="text" name="product_specials[0][price]" value="" /></td>
					<td><input type="text" name="product_specials[0][date_start]" value="" class="date" /></td>
					<td><input type="text" name="product_specials[0][date_end]" value="" class="date" /></td>
					<td><a class="ms-button-delete" title="<?php echo $ms_delete; ?>"></a></td>
				</tr>

				<?php if (isset($product['specials'])) { ?>
				<?php $special_row = 1; ?>
				<?php foreach ($product['specials'] as $product_special) { ?>
				<tr>
					<td><input type="text" name="product_specials[<?php echo $special_row; ?>][priority]" value="<?php echo $product_special['priority']; ?>" size="2" /></td>
					<td><input type="text" name="product_specials[<?php echo $special_row; ?>][price]" value="<?php echo $this->MsLoader->MsHelper->uniformDecimalPoint($product_special['price']); ?>" /></td>
					<td><input type="text" name="product_specials[<?php echo $special_row; ?>][date_start]" value="<?php echo $product_special['date_start']; ?>" class="date" /></td>
					<td><input type="text" name="product_specials[<?php echo $special_row; ?>][date_end]" value="<?php echo $product_special['date_end']; ?>" class="date" /></td>
					<td><a class="ms-button-delete" title="<?php echo $ms_delete; ?>"></a></td>
				</tr>
				<?php $special_row++; ?>
				<?php } ?>
				<?php } ?>
				</tbody>

				<tfoot>
				<tr>
				<td colspan="5"><a class="button ffClone"><?php echo $ms_button_add_special; ?></a></td>
				</tr>
				</tfoot>
			</table>
		</div>
		<?php } ?>
		-->
		<!-- Quantity Discounts tab -->
		<?php if ($this->config->get('msconf_allow_discounts')) { ?>
			<div id="tab-discounts">
				<h3><?php echo $ms_account_product_tab_discounts; ?></h3>
				<p class="error" id="error_quantity_discounts"></p>

				<table class="list">
					<thead>
					<tr>
						<td><span class="required">*</span><?php echo $ms_account_product_priority; ?></td>
						<td><span class="required">*</span><?php echo $ms_account_product_quantity; ?></td>
						<td><span class="required">*</span><?php echo $ms_account_product_price; ?></td>
						<td><span class="required">*</span><?php echo $ms_account_product_date_start; ?></td>
						<td><span class="required">*</span><?php echo $ms_account_product_date_end; ?></td>
						<td></td>
					</tr>
					</thead>

					<tbody>

					<!-- sample row -->
					<tr class="ffSample">
						<td><input type="text" name="product_discounts[0][priority]" value="" size="2" /></td>
						<td><input type="text" name="product_discounts[0][quantity]" value="" size="2" /></td>
						<td><input type="text" name="product_discounts[0][price]" value="" /></td>
						<td><input type="text" name="product_discounts[0][date_start]" value="" class="date" /></td>
						<td><input type="text" name="product_discounts[0][date_end]" value="" class="date" /></td>
						<td><a class="ms-button-delete" title="<?php echo $ms_delete; ?>"></a></td>
					</tr>

					<?php if (isset($product['discounts'])) { ?>
					<?php $discount_row = 1; ?>
					<?php foreach ($product['discounts'] as $product_discount) { ?>
					<tr>
						<td><input type="text" name="product_discounts[<?php echo $discount_row; ?>][priority]" value="<?php echo $product_discount['priority']; ?>" size="2" /></td>
						<td><input type="text" name="product_discounts[<?php echo $discount_row; ?>][quantity]" value="<?php echo $product_discount['quantity']; ?>" size="2" /></td>
						<td><input type="text" name="product_discounts[<?php echo $discount_row; ?>][price]" value="<?php echo $this->MsLoader->MsHelper->uniformDecimalPoint($product_discount['price']); ?>" /></td>
						<td><input type="text" name="product_discounts[<?php echo $discount_row; ?>][date_start]" value="<?php echo $product_discount['date_start']; ?>" class="date" /></td>
						<td><input type="text" name="product_discounts[<?php echo $discount_row; ?>][date_end]" value="<?php echo $product_discount['date_end']; ?>" class="date" /></td>
						<td><a class="ms-button-delete" title="<?php echo $ms_delete; ?>"></a></td>
					</tr>
					<?php $discount_row++; ?>
					<?php } ?>
					<?php } ?>


					<?php if (isset($product['specials'])) { ?>
					<?php $special_row = 1; ?>
					<?php foreach ($product['specials'] as $product_special) { ?>
					<tr>
						<td><input type="text" name="product_specials[<?php echo $special_row; ?>][priority]" value="<?php echo $product_special['priority']; ?>" size="2" /></td>
						<td><input type="text" value="1" ></td>
						<td><input type="text" name="product_specials[<?php echo $special_row; ?>][price]" value="<?php echo $this->MsLoader->MsHelper->uniformDecimalPoint($product_special['price']); ?>" /></td>
						<td><input type="text" name="product_specials[<?php echo $special_row; ?>][date_start]" value="<?php echo $product_special['date_start']; ?>" class="date" /></td>
						<td><input type="text" name="product_specials[<?php echo $special_row; ?>][date_end]" value="<?php echo $product_special['date_end']; ?>" class="date" /></td>
						<td><a class="ms-button-delete" title="<?php echo $ms_delete; ?>"></a></td>
					</tr>
					<?php $special_row++; ?>
					<?php } ?>
					<?php } ?>
					</tbody>

					<tfoot>
					<tr>
						<td colspan="6"><a class="button ffClone"><?php echo $ms_button_add_discount; ?></a></td>
					</tr>
					</tfoot>
				</table>
			</div>
		<?php } ?>

		<?php if ($warehouses) { ?>
			<div id="tab-warehouses">
				<h3><?php echo $ms_account_product_tab_warehouses; ?></h3>
				<div>
				<?php asort($product_warehouses) ?>
				<input type="hidden" name="currentWarehouses" value="<?php echo implode(',', $product_warehouses); ?>">
				<?php foreach ($warehouses as $warehouse) { ?>
					<div>
						<?php if(($product && in_array($warehouse['id'], $product_warehouses)) || (!$product && $warehouse['seller_id'] == $seller['seller_id'])){ ?>
							<input type="checkbox" name="prodWarehouses[]" value="<?php echo $warehouse['id']?>" checked=''>
						<?php }else{ ?>
							<input type="checkbox" name="prodWarehouses[]" value="<?php echo $warehouse['id']?>">
						<?php } ?>
						<?php echo $warehouse['name']?> <?php echo $warehouse['seller_id'] ? '('.$ms_warehouse_yours.')' : ''?>
					</div>
				<?php } ?>
				</div>
			</div>
		<?php } ?>
		</div>
		</form>

		<?php if (isset($seller['commissions']) && ($seller['commissions'][MsCommission::RATE_LISTING]['percent'] > 0 || $seller['commissions'][MsCommission::RATE_LISTING]['flat'] > 0)) { ?>
			<?php if ($seller['commissions'][MsCommission::RATE_LISTING]['percent'] > 0) { ?>
			<p class="attention ms-commission">
				<?php echo sprintf($this->language->get('ms_account_product_listing_percent'),$this->currency->format($seller['commissions'][MsCommission::RATE_LISTING]['flat'], $this->config->get('config_currency'))); ?>
				<?php echo $ms_commission_payment_type; ?>
			</p>
			<?php } else if ($seller['commissions'][MsCommission::RATE_LISTING]['flat'] > 0) { ?>
			<p class="attention ms-commission">
				<?php echo sprintf($this->language->get('ms_account_product_listing_flat'),$this->currency->format($seller['commissions'][MsCommission::RATE_LISTING]['flat'], $this->config->get('config_currency'))); ?>
				<?php echo $ms_commission_payment_type; ?>
			</p>
			<?php } ?>

			<?php if(isset($payment_form)) { ?><div class="ms-payment-form"><?php echo $payment_form; ?></div><?php } ?>
		<?php } ?>

		<?php if (isset($list_until) && $list_until != NULL) { ?>
			<p class="attention">
				<?php echo sprintf($this->language->get('ms_account_product_listing_until'), date($this->language->get('date_format_short'), strtotime($list_until))); ?>
			</p>
		<?php } ?>

		<div class="buttons">
			<div class="left">
				<a href="<?php echo $back; ?>" class="button">
					<span><?php echo $ms_button_cancel; ?></span>
				</a>
			</div>
			<div class="right">
				<a class="button" id="ms-submit-button">
					<span><?php echo $ms_button_submit; ?></span>
				</a>
			</div>
		</div>

	<?php echo $content_bottom; ?>
</div>

<?php $timestamp = time(); ?>
<script>
	var msGlobals = {
		timestamp: '<?php echo $timestamp; ?>',
		token : '<?php echo md5($salt . $timestamp); ?>',
		session_id: '<?php echo session_id(); ?>',
		product_id: '<?php echo $product["product_id"]; ?>',
		button_generate: '<?php echo htmlspecialchars($ms_button_generate, ENT_QUOTES, "UTF-8"); ?>',
		text_delete: '<?php echo htmlspecialchars($ms_delete, ENT_QUOTES, "UTF-8"); ?>',
		uploadError: '<?php echo htmlspecialchars($ms_error_file_upload_error, ENT_QUOTES, "UTF-8"); ?>',
		formError: '<?php echo htmlspecialchars($ms_error_form_submit_error, ENT_QUOTES, "UTF-8"); ?>',
		formNotice: '<?php echo htmlspecialchars($ms_error_form_notice, ENT_QUOTES, "UTF-8"); ?>',
        config_enable_rte: '<?php echo $this->config->get("msconf_enable_rte"); ?>',
        langTextGeneralImage: '<?php echo isset($text_general_image) ? $text_general_image : "General Image";?>',
        poip_installed: '<?php echo $poip_installed; ?>',
	};
</script>

<script>
    function handleImageOptionChange(e) {
        const [optionId, optionValueId] = e.value.slice('0').split('-');
        var imageContainer = e.parentNode;
        imageContainer.querySelector('.product_option_id').value = optionId;
        imageContainer.querySelector('.product_option_value_id').value = optionValueId;
        return false;
    };
    
    function getAllAvailableOptions() {
        return Array.from(document.querySelectorAll('.option')).map(option => {
            var returnOption = {};
            var optionName = option.querySelector('.o-heading').innerText.trim();
            var optionId = Array.from(option.querySelectorAll('input[type=hidden]'))
                .filter(input => {
                var rgx = input.name.match(/product_option\[[0-9]]\[option_id]+/g);
                    return rgx && rgx.length > 0;
            })
            .reduce((a, b) => b.value, '');
            ;
            var optionValues = Array.from(option.querySelectorAll('.option_value'))
                .filter(opt => opt.querySelector('.option_name').innerText.trim().length)
                .reduce((a, b) => {
                    optValName = b.querySelector('.option_name').innerText.trim();
                    optValId = Array.from(b.querySelectorAll('input[type=hidden]'))
                    .filter(input => {
                        var rgx = input.name.match(/product_option_value]\[[0-9]]\[option_value_id]+/g);
                            return rgx && rgx.length > 0;
                    }).reduce((a, b) => b.value, '');

                    return a.concat({id: optValId, name: optValName})
                    
                }, []).filter(op => op.name && op.id)
            return {
                name: optionName,
                id: optionId,
                values: optionValues
            }
        }).filter(o => o.name && o.id && o.values.length);
    };
    
    function updateImageOptionsSelect() {
        var poipInstalled = parseInt(msGlobals.poip_installed) === 1 && parseInt(msGlobals.product_id) > 0,
            allOptions = poipInstalled ? getAllAvailableOptions() : [],
            imageSelectList = $('.thumb-option-selector'),
            defaultOptions = ('<option value="0-0" selected>' + msGlobals.langTextGeneralImage + '</option>' +
                // Render all options
                (function () {
                    var options = '';
                    allOptions.forEach(opt => {
                        opt.values.forEach(value => {
                            options += '<option ';
                            options += ('value ="' + opt['id'] + '-' + value['id'] +'">');
                            options += (opt['name'] + ' > ' + value['name']);
                            options += '</option>';
                        });
                    });
                    return options;
                })()),
                selectList = $('.thumb-option-selector'),
                optionValues = allOptions.reduce((a,b) => a.concat(b.values.reduce((c,d) => c.concat([b.id+'-'+d.id]), [])), [])
        
        selectList.each(function(i, select) {
            var val = (select.value).slice(0);
            $(this).html(defaultOptions);
            select.value = ['0-0', ...optionValues].indexOf(val) > -1 ? val : '0-0';
        });
    };
    
    $(document).ready(function() {
        $('body').delegate('.option_delete,.option_value_delete', 'click', updateImageOptionsSelect);
        $('body').delegate('.select_option_value', 'change', updateImageOptionsSelect);
    });

</script>
<?php echo $footer; ?>
