<?php
class ControllerModuleFeatured extends Controller {
	protected function index($setting) {
		$this->language->load('module/featured'); 

      	$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['button_cart'] = $this->language->get('button_cart');
		
		$this->data['button_wishlist'] = $this->language->get('button_wishlist');

		$this->data['button_compare'] = $this->language->get('button_compare');

		$this->load->model('catalog/product');
		
		$this->load->model('tool/image');

		$this->data['products'] = array();

		$products = explode(',', $this->config->get('featured_product'));		

		if (empty($setting['limit'])) {
			$setting['limit'] = 5;
		}
		
		$products = array_slice($products, 0, (int)$setting['limit']);
		
		foreach ($products as $product_id) {
			$product_info = $this->model_catalog_product->getProduct($product_id);

            $description = substr(strip_tags(htmlspecialchars_decode($product_info['description'])), 0, 100);

            if ($product_info) {
				if ($product_info['image']) {
					$image = $this->model_tool_image->resize($product_info['image'], $setting['image_width'], $setting['image_height']);
				} else {
					$image = false;
				}

				//this for swap image

				$images = $this->model_catalog_product->getProductImages($product_info['product_id']);

            if(isset($images[0]['image']) && !empty($images[0]['image'])){
                  $images =$images[0]['image'];
               }

				//

				if ($this->customer->isCustomerAllowedToViewPrice()) {
					$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$price = false;
				}
						
				if ((float)$product_info['special']) {
					$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$special = false;
				}
				
				if ($this->config->get('config_review_status')) {
					$rating = $product_info['rating'];
				} else {
					$rating = false;
				}

                if ($product_info['price'] > 0) {
                    $savingAmount = round((($product_info['price'] - $product_info['special'])/$product_info['price'])*100, 0);
                }
                else {
                    $savingAmount = 0;
                }

                $model = $product_info['manufacturer'];
                $model_url = $product_info['manufacturer_id'];

				$this->data['products'][] = array(
					'product_id' => $product_info['product_id'],
					'thumb'   	 => $image,
					'name'    	 => $product_info['name'],
					'price'   	 => $price,
					'special' 	 => $special,
                    'model'      => $model,
                    'model_url'  => $this->url->link('product/manufacturer/product', 'manufacturer_id=' . $model_url),
					'rating'     => $rating,
					'description'=> (html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')),
					'reviews'    => sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']),
					'href'    	 => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                    'stock_status' => $product_info['quantity'] <= 0 ? $product_info['stock_status'] : '',
                    // for swap image
				'thumb_swap'  => $this->model_tool_image->resize($images, $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height')),
				//
				// for saving percentage
				'saving'	=> $savingAmount,
				//
				);
			}
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/featured.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/featured.tpl';
		} else {
			$this->template = 'default/template/module/featured.tpl';
		}

		$this->render();
	}
}
?>