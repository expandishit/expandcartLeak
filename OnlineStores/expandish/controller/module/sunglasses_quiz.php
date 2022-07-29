<?php
class ControllerModuleSunglassesQuiz extends Controller { 
	

	/**
	 * @var string $module (module name) 
	 */
	private $module = 'sunglasses_quiz';
	

	/**
	 * inde function, returns the quiz page
	 * 
	 * @return temlate
	 */
	public function index() {

		if (!\Extension::isInstalled($this->module))
        {
			return $this->redirect( $this->config->get('config_url'));
        }

		$this->load->model('setting/setting');

		$this->language->load_json("module/{$this->module}");
		
		// $app_settings = $this->model_setting_setting->getSetting($this->module);
		$app_settings = $this->config->get($this->module);
        $this->reziseImageAndGetPath($app_settings);

		$this->data["{$this->module}_data"] = $app_settings;
		
		$this->data['lang_id'] = $this->config->get('config_language_id');
		$this->data['result_url'] = $this->config->get('config_url') . 'index.php?route=module/sunglasses_quiz/results&result=';

		$this->data['left_title'] = $this->language->get('text_left_title');
		$this->data['left_description'] = $this->language->get('text_left_description');

		if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/sunglasses_quiz/sunglasses_quiz.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/sunglasses_quiz/sunglasses_quiz.expand';
        } else {
            $this->template = 'default/template/module/sunglasses_quiz/sunglasses_quiz.expand';
        }
           
        $this->response->setOutput($this->render_ecwig());
	}



	/**
	 * get quiz results 
	 * 
	 * @return template 
	 */
	public function results() {
		
		$this->load->model('setting/setting');
		$app_settings = $this->config->get($this->module);
		$product_ids = $app_settings['result' . $this->request->get['result'] . '_products'];

		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();
		$results = $this->model_catalog_product->getProductsByIds($product_ids);

		//Login Display Prices
		$config_customer_price = $this->config->get('config_customer_price');

		$this->data['show_prices_login'] = true;
		if((($config_customer_price && $this->customer->isLogged()) || !$config_customer_price)){
			$this->data['show_prices_login'] = false;
		}
		
		foreach ($results as $result) {
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
			} else {
				$image = false;
			}

			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
			} else {
				$image = false;
			}

			//this for swap image

			$images = $this->model_catalog_product->getProductImages($result['product_id']);

			if(isset($images[0]['image']) && !empty($images[0]['image'])){
			  	$images =$images[0]['image'];
		    }


			$price = false;
			if ($isCustomerAllowedToViewPrice) {
				if ( $this->config->get('config_tax') == "price" ) {
					$price = $this->currency->format( $result['price'] );
				} else {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
				}
			}

			$special = false;
			if ((float)$result['special'] && $isCustomerAllowedToViewPrice) {
				if ( $this->config->get('config_tax') == "price" ) {
					$special = $this->currency->format( $result['special'] );
				} else {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')));
				}
			}

			if ($this->config->get('config_tax')) {
				$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price']);
			} else {
				$tax = false;
			}

			$this->data['review_status'] = $this->config->get('config_review_status');

			if ($this->config->get('config_review_status')) {
				$rating = (int)$result['rating'];
			} else {
				$rating = false;
			}

			if ($result['price'] > 0) {
				$savingAmount = round((($result['price'] - $result['special'])/$result['price'])*100, 0);
			}
			else {
				$savingAmount = 0;
			}

			$stock_status = '';
			if ($result['quantity'] <= 0) {
				$stock_status = $result['stock_status'];
			}

			$seller = false;
			$seller_messaging = FALSE;
			$this->load->model('multiseller/status');

			$queryMultiseller = $this->db->query("SELECT extension_id FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

			if($queryMultiseller->num_rows && $show_seller){
				$seller_id = $this->MsLoader->MsProduct->getSellerId($result['product_id']);
				$seller    = $this->MsLoader->MsSeller->getSeller($seller_id);

				if (!$seller) {
					$seller_messaging = FALSE;
				} else {
					//Check if MS Messaging seller, Replace Add To Cart installed
					$multisellerMessaging = $this->model_multiseller_status->is_messaging_installed();
					$multisellerReplcAddtoCart = $this->model_multiseller_status->is_replace_addtocart();

					if($multisellerMessaging && $multisellerReplcAddtoCart && $seller_id != (int)$this->customer->getId() )
						$seller_messaging= true;
				}
			}

			//Check show attribute status
			$show_attribute = false;
			if($show_attr){
				$product_attribute = $this->db->query("SELECT pa.text , ad.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (pa.attribute_id = ad.attribute_id AND ad.language_id ='" . (int)$config_language_id ."')  WHERE pa.product_id = '" . (int)$result['product_id'] . "' AND pa.attribute_id = '" . (int)$show_attr . "' AND pa.language_id = '" . (int)$config_language_id ."' limit 1");

				if($product_attribute->row){
				   $show_attribute = $product_attribute->row['name'].': '.$product_attribute->row['text'];
			   }
			}


			$this->data['products'][] = array(
				'product_id'  => $result['product_id'],
				'thumb'       => $image,
				'image'       => $result['image'],
				'name'        => $result['name'],
				'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
				'price'       => $price,
				'special'     => $special,
				'tax'         => $tax,
				'rating'      => $result['rating'],
				'reviews'     => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
				'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id']),
				// for swap image
				'thumb_swap'  => $this->model_tool_image->resize($images, $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height')),
				'image_swap'  => $images,
				//
				// for saving percentage
				'saving'	=> $savingAmount,
				'stock_status' => $stock_status,
				'stock_status_id' => $result['stock_status_id'],
				'quantity' => $result['quantity'],

				'manufacturer_id' => $result['manufacturer_id'],
				'manufacturer' => $result['manufacturer'],
				'manufacturerimg' => $result['manufacturerimg'],
				'manufacturer_href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id']),
			);
		}
		
		if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/sunglasses_quiz/quiz_results.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/sunglasses_quiz/quiz_results.expand';
        } else {
            $this->template = 'default/template/module/sunglasses_quiz/quiz_results.expand';
		}

		$this->response->setOutput($this->render_ecwig());
	}



	/**
	 * resize images and append full path
	 * 
	 * @param array $fields
	 * @return array $fields
	 */
	private function reziseImageAndGetPath(&$fields)
    {
        
        $this->load->model('tool/image');

        foreach ($fields as $key => $value) {

            if ($this->endsWith($key, '_img')) {
                $fields[$key] = $this->model_tool_image->resize($value, 150, 150);
            }
        }

        return $fields;
	}
	
	
	/**
     * check if string ends with substr
     * 
     * @param string $str
     * @param string $needle
     */
    private function endsWith($str, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($str, -$length) === $needle);
    }
}
