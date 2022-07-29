<?php
require_once('jwt_helper.php');
class ControllerApiModule extends Controller {
	

    public function getQuiz()
    {

        $module = 'sunglasses_quiz';
        $json = [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');

        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            return $this->response->setOutput(json_encode($json));
        }

		$this->load->model('setting/setting');

		$this->language->load_json("module/{$module}");
		
        $app_settings = $this->model_setting_setting->getSetting($module);
        
        $this->reziseImageAndGetPath($app_settings);

		$json["{$module}_data"] = $app_settings;
		
		$json['lang_id'] = $this->config->get('config_language_id');

        $this->response->setOutput(json_encode($json));
    }

    /**
	 * get quiz results 
	 * 
	 */
	public function quizResults() {
        
        $module = 'sunglasses_quiz';
        $json = [];

        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
		$this->response->addHeader('Access-Control-Allow-Credentials: true');
		
		$isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();
		
        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            return $this->response->setOutput(json_encode($json));
        }

		$this->load->model('setting/setting');
		$app_settings = $this->model_setting_setting->getSetting($module);
		$product_ids = $app_settings[$module]['result' . $this->request->get['result'] . '_products'];

		$this->load->model('catalog/product');
		$this->load->model('tool/image');

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

			//Check show attribute status
			$show_attribute = false;
			if($show_attr){
				$product_attribute = $this->db->query("SELECT pa.text , ad.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (pa.attribute_id = ad.attribute_id AND ad.language_id ='" . (int)$config_language_id ."')  WHERE pa.product_id = '" . (int)$result['product_id'] . "' AND pa.attribute_id = '" . (int)$show_attr . "' AND pa.language_id = '" . (int)$config_language_id ."' limit 1");

				if($product_attribute->row){
				   $show_attribute = $product_attribute->row['name'].': '.$product_attribute->row['text'];
			   }
			}
			 ///check permissions to view Add to Cart Button
			 $viewAddToCart = true;
			 $hidCartConfig = $this->config->get('config_hide_add_to_cart');
			 if(($result['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart)
			 {
				 $viewAddToCart = false;
			 }	

			$json['products'][] = array(
				'product_id'  => $result['product_id'],
				'thumb'       => \Filesystem::getUrl('image/' . $image),
				'image'       => \Filesystem::getUrl('image/' . $result['image']),
				'name'        => $result['name'],
				'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
				'price'       => $price,
				'special'     => $special,
				'tax'         => $tax,
				'viewAddToCartBtn'=>$viewAddToCart,
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
		
		$this->response->setOutput(json_encode($json));
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

	public function getStoreLocations() {

        $json = [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');

        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            return $this->response->setOutput(json_encode($json));
        }

		try {

			if (!\Extension::isInstalled('store_locations')) {
				http_response_code(500);
				$json['error']['warning'] = 'Something went wrong';
				return $this->response->setOutput(json_encode($json));
			}

			$this->load->model('module/store_locations');

			$Locations = $this->model_module_store_locations->getList(0,$this->config->get('store_locations_per_page'));
			$json['Locations'] = array();
			$ctr = 0;
			foreach($Locations as $Location) {
				$Location['href'] = $this->url->link('common/location_details&loc_id=' . $Location['ID']);
				$json['Locations'][$ctr] = $Location;
				$ctr++;
			}

		} catch (Exception $exception) {
			http_response_code(500);
			$json['error']['warning'] = 'Something went wrong';
		}
		
        $this->response->setOutput(json_encode($json));
	}
}