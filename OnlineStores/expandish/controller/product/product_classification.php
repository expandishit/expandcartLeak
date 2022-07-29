<?php
class ControllerProductProductClassification extends Controller {

	public function index() {

		$this->load->model('catalog/product_classification');

		$isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();

		$url = "";

		if (isset($this->request->get['pc_brand_id'])) {
			$url .= '&pc_brand_id=' . $this->request->get['pc_brand_id'];
		}
		if (isset($this->request->get['pc_model_id'])) {
			$url .= '&pc_model_id=' . $this->request->get['pc_model_id'];
		}
		if (isset($this->request->get['pc_year_id'])) {
			$url .= '&pc_year_id=' . $this->request->get['pc_year_id'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_search'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$defaultSortingColumn = $this->config->get('config_products_default_sorting_column');

			$sort = ! empty( $defaultSortingColumn ) ? "p.{$defaultSortingColumn}" : 'p.date_added' ;
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_catalog_limit');
		}

		if (isset($this->request->get['pc_brand_id'])) {
			$brand_id = $this->request->get['pc_brand_id'];
		} else {
			$brand_id = 0;
		}

		if (isset($this->request->get['pc_model_id'])) {
			$model_id = $this->request->get['pc_model_id'];
		} else {
			$model_id = 0;
		}

		if (isset($this->request->get['pc_year_id'])) {
			$year_id = $this->request->get['pc_year_id'];
		} else {
			$year_id = 0;
		}

		$this->data['sorts'] = array();


		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_default'),
			'value' => 'p.sort_order-ASC',
			'href'  => $this->url->link('product/product_classification', '&sort=p.sort_order&order=ASC' . $url)
		);

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_new_first'),
			'value' => 'p.date_available-DESC',
			'href'  => $this->url->link('product/product_classification', '&sort=p.date_available&order=DESC' . $url)
		);

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_name_asc'),
			'value' => 'pd.name-ASC',
			'href'  => $this->url->link('product/product_classification', '&sort=pd.name&order=ASC' . $url)
		);

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_name_desc'),
			'value' => 'pd.name-DESC',
			'href'  => $this->url->link('product/product_classification',  '&sort=pd.name&order=DESC' . $url)
		);

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_price_asc'),
			'value' => 'p.price-ASC',
			'href'  => $this->url->link('product/product_classification','&sort=p.price&order=ASC' . $url)
		);

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_price_desc'),
			'value' => 'p.price-DESC',
			'href'  => $this->url->link('product/product_classification', '&sort=p.price&order=DESC' . $url)
		);

		if ($this->config->get('config_review_status')) {
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_desc'),
				'value' => 'rating-DESC',
				'href'  => $this->url->link('product/product_classification','&sort=rating&order=DESC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_asc'),
				'value' => 'rating-ASC',
				'href'  => $this->url->link('product/product_classification', '&sort=rating&order=ASC' . $url)
			);
		}




		$this->data['products'] = array();

		$data = array(
			'pc_brand_id' => $brand_id,
			'pc_model_id' => $model_id,
			'pc_year_id'  => $year_id,
			'sort'     => $sort,
			'order'    => $order,
			'start'    => ($page - 1) * $limit,
			'limit'    => $limit
		);

		$results = $this->model_catalog_product_classification->getProducts($data);
		$product_total = $this->model_catalog_product_classification->getTotalProducts($data);
		foreach ($results as $result) {
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

			//

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
			 ///check permissions to view Add to Cart Button
			 $this->data['viewAddToCart'] = true;
			 $hidCartConfig = $this->config->get('config_hide_add_to_cart');
			 if(($result['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart)
			 {
				 $this->data['viewAddToCart'] = false;
			 }	

			$seller = false;
			$queryMultiseller = $this->db->query("SELECT extension_id FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

			if($queryMultiseller->num_rows && $show_seller){
				$seller_id = $this->MsLoader->MsProduct->getSellerId($result['product_id']);
				$seller    = $this->MsLoader->MsSeller->getSeller($seller_id);
			}

			//Check show attribute status
			$show_attribute = false;
			if($show_attr){
				$product_attribute = $this->db->query("SELECT pa.text , ad.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (pa.attribute_id = ad.attribute_id AND ad.language_id ='" . (int)$config_language_id ."')  WHERE pa.product_id = '" . (int)$result['product_id'] . "' AND pa.attribute_id = '" . (int)$show_attr . "' AND pa.language_id = '" . (int)$config_language_id ."' limit 1");

				if($product_attribute->row){
					$show_attribute = $product_attribute->row['name'].': '.$product_attribute->row['text'];
				}
			}
			///

			$this->data['products'][] = array(
				'product_id'  => $result['product_id'],
				'thumb'       => $image,
				'image'       => $result['image'],
				'name'        => $result['name'],
				'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 300) . '..',
				'price'       => $price,
				'special'     => $special,
				'date_available' => $result['date_available'],
				'tax'         => $tax,
				'rating'      => $result['rating'],
				'reviews'     => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
				'href'        => $this->url->link('product/product', 'path=' . $this->request->get['path'] . '&product_id=' . $result['product_id'] . $url),
				// for swap image
				'athumb_swap'  => $this->model_tool_image->resize($images, $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height')),
				'image_swap'   => $images,
				//
				// for saving percentage
				'saving'	=> $savingAmount,
				//
				'stock_status' => $stock_status,
				'stock_status_id' => $result['stock_status_id'],
				'quantity' => $result['quantity'],
				'minimum_limit' => $result['minimum'],

				'manufacturer_id' => $result['manufacturer_id'],
				'manufacturer' => $result['manufacturer'],
				'manufacturerimg' => $result['manufacturerimg'],
				'manufacturer_href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id']),
				'has_seller' => $seller ? 1 : 0,
				'seller_nickname' => $seller ? $seller['ms.nickname'] : '',
				'seller_href' => $seller ? $this->url->link('seller/catalog-seller/profile', 'seller_id=' . $seller['seller_id'] . $url) : '',
				'attribute' => $show_attribute,
			);
		}

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('product/product_classification', $url . '&page={page}');

		$this->data['pagination'] = $pagination->render();


		if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/product/category.expand')) {
			$this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/product/category.expand';
		}
		else {
			$this->template = $this->config->get('config_template') . '/template/product/category.expand';
		}

		$this->children = array(
			'common/footer',
			'common/header'
		);


		$this->response->setOutput($this->render_ecwig());
	}
}



?>
