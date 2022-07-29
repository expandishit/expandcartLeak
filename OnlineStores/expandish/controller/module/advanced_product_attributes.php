<?php 
class ControllerModuleAdvancedProductAttributes extends Controller { 

	public function jxRenderAttributes() {
		$this->language->load_json('multiseller/multiseller');
		$this->language->load_json('module/advanced_product_attributes');
		$this->load->model('module/advanced_product_attributes');
		$this->data['advanced_attributes'] = $this->model_module_advanced_product_attributes->getAttributes();
		

		if(!empty($this->request->get['product_id'])){
			$product_attributes = $this->model_module_advanced_product_attributes->getProductAttributesCustom($this->request->get['product_id']);
			$this->data['selected_values'] = array_column($product_attributes, 'attribute_id');
		}
	

		$this->template = $this->checkTemplate('module/advanced_product_attributes/account-product-form-attributes.expand');
		$this->response->setOutput($this->render_ecwig());
	}

	public function jxRenderAttributesValues() {
		$this->load->model('module/advanced_product_attributes');

		$this->data['advanced_attribute'] = $this->model_module_advanced_product_attributes->getAttributes(
			[
				'advanced_attribute_id' => 	$this->request->get['advanced_attribute_id'],
				'single' => 1
			]
		);
		// var_dump($this->data['advanced_attributes']); die();
		$this->data['advanced_attribute']['values'] = $this->model_module_advanced_product_attributes->getAttributeValuesCurrentLanguage($this->request->get['advanced_attribute_id']);
		// var_dump($this->data['advanced_attributes']); die();

		$this->data['attribute_index'] = 0;

		$this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		$this->template = $this->checkTemplate('module/advanced_product_attributes/account-product-form-attributes-values.expand');
		$this->response->setOutput($this->render_ecwig());
	}

	public function jxRenderProductAttributes() {
		$this->load->model('module/advanced_product_attributes');
		$product_attributes = $this->model_module_advanced_product_attributes->getProductAttributesCustom($this->request->get['product_id']);

		$this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();


		$output = '';

		if ($product_attributes) {
			foreach ($product_attributes as $product_attribute) {
				$attribute_info = $this->model_module_advanced_product_attributes->getAttribute($product_attribute['attribute_id']);

				if ($attribute_info) {
					$this->data['product_attributes'][] = array(
						'advanced_attribute_id'    => $product_attribute['attribute_id'],
						'name'            => $attribute_info['name'],
                    	'representation_type' => $attribute_info['type'],
						'product_attribute_description' => $product_attribute['product_attribute_description'],
						'values'          => $this->model_module_advanced_product_attributes->getAttributeValuesCurrentLanguage($product_attribute['attribute_id']),
                        'selected_values' => $product_attribute['product_attribute_values'] ?: []
					);

					$product_attributes_ids[$product_attribute['attribute_id']] = $product_attribute['attribute_id'];					
				}
			}


			$this->template = $this->checkTemplate('module/advanced_product_attributes/account-product-attributes-values.expand');
			// $this->response->setOutput($this->render_ecwig());

			$output .= $this->render_ecwig();
			$this->data['attribute_index']++;
		}


		$this->response->setOutput($output);		
	}

}
