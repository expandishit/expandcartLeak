<?php 
class ControllerEcflashsaleFlashsale extends Controller {  
	public function index() {
        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'advanced_deals'");

        if(!$queryMultiseller->num_rows) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
            return;
        }

		$this->language->load('product/category');
		$this->language->load('module/ecflashsale');
		
		$this->load->model('catalog/category');

		$this->load->model('ecflashsale/product');

		$this->load->model('ecflashsale/flashsale');
		
		$this->load->model('catalog/product');
		
		$this->load->model('tool/image'); 
		
		if (isset($this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}

		if (isset($this->request->get['filter'])) {
			$filter = $this->request->get['filter'];
		} else {
			$filter = '';
		}
				
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else { 
			$page = 1;
		}	
							
		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_catalog_limit');
		}
							
		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
       		'separator' => false
   		);
   		$settings = $this->config->get("ecflashsale_setting");
   		$status = isset($settings['status'])?$settings['status']:1;
   		$ecflashsale_id = isset($this->request->get['ecflashsale_id'])?(int)$this->request->get['ecflashsale_id']:0;
   		$flashsale_info = false;
   		if($status)	{
   			
			$flashsale_info = $this->model_ecflashsale_flashsale->getFlashsale($ecflashsale_id);
		}

		$image_width = isset($settings['flashsale_detail_image_width'])?$settings['flashsale_detail_image_width']:$this->config->get('config_image_category_width');
		$image_height = isset($settings['flashsale_detail_image_height'])?$settings['flashsale_detail_image_height']:$this->config->get('config_image_category_height');
		$settings['show_saleoff'] = isset($settings['show_saleoff'])?$settings['show_saleoff']:1;
		$settings['show_expire_date'] = isset($settings['show_expire_date'])?$settings['show_expire_date']:1;

		$lang_id = $this->config->get('config_language_id');
		if ($flashsale_info) {

			$this->model_ecflashsale_flashsale->updateHits($ecflashsale_id);

			$params = isset($flashsale_info['params'])?unserialize($flashsale_info['params']):array();
			$this->data['ecflashsale_id'] = $ecflashsale_id;
			$this->data['date_end'] = $flashsale_info['date_end'];
		    $this->data['discount_percent'] = $flashsale_info['percent'];
		    $this->data['discount_amount'] = $flashsale_info['amount'];
		    $this->data['name'] = $flashsale_info['name'];
		    $this->data['description'] = $flashsale_info['description'];
		    $this->data['description'] = html_entity_decode($this->data['description'], ENT_QUOTES, 'UTF-8');
		    $this->data['description'] = isset($this->data['description'])?str_replace(array("&lt;","&gt;","&quot;"),array('<','>','"'),$this->data['description']):"";
		    
		    $date_diff = $this->model_ecflashsale_flashsale->dateDiff( $this->data['date_end'] );

		    $this->data["is_expired"] = ($date_diff <= 0) ? true:false ;

		    $this->data['show_image'] = true;
		    $this->data['show_name'] = true;
		    $this->data['show_sale_off'] = true;
		    $this->data['show_viewmore'] = false;
		    $this->data['show_countdown'] = isset($settings['show_countdown'])?$settings['show_countdown']:1;
		    $this->data['show_social'] = isset($settings['flashsale_show_social'])?$settings['flashsale_show_social']:1;
		    $this->data['prefix'] = "";
		    $this->data['enable_message'] = true;
		    $this->data['show_sale_off'] = $settings['show_saleoff'];
			$this->data['show_expire_date'] = $settings['show_expire_date'];

		    $this->data['text_discount'] = $this->language->get("text_discount");
		    $this->data['no_border']	= isset($settings['no_border'])?$settings['no_border']:1;

		    if(!empty($this->data['discount_percent']) && $this->data['discount_percent'] > 0){
	    		$discount_value = $this->data['discount_percent']."%";
		    }else{
		    	$discount_value = $this->currency->format( $this->data['discount_amount'] );
		    }
		    $this->data['text_discount'] = sprintf($this->data['text_discount'], $discount_value);


		    $this->document->setTitle($flashsale_info['name']);
			$this->document->setDescription($flashsale_info['meta_description']);
			$this->document->setKeywords($flashsale_info['meta_keyword']);
			$this->document->addScript('catalog/view/javascript/jquery/jquery.total-storage.min.js');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/stylesheet/ecflashsale.css')) {
				$this->document->addStyle('catalog/view/theme/'.$this->config->get('config_template').'/stylesheet/ecflashsale.css');
			} else {
				$this->document->addStyle('catalog/view/theme/default/stylesheet/ecflashsale.css');
			}
			$this->document->addScript('catalog/view/javascript/ecflashsale/countdown.js');

			if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
	         	$this->data['base'] = $this->config->get('config_ssl');
		    } else {
		        $this->data['base'] = $this->config->get('config_url');
		    }
			
			$this->data['heading_title'] = $flashsale_info['name'];

			$this->data['text_refine'] = $this->language->get('text_refine');
			$this->data['text_empty'] = $this->language->get('text_empty');			
			$this->data['text_quantity'] = $this->language->get('text_quantity');
			$this->data['text_manufacturer'] = $this->language->get('text_manufacturer');
			$this->data['text_model'] = $this->language->get('text_model');
			$this->data['text_price'] = $this->language->get('text_price');
			$this->data['text_tax'] = $this->language->get('text_tax');
			$this->data['text_points'] = $this->language->get('text_points');
			$this->data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
			$this->data['text_display'] = $this->language->get('text_display');
			$this->data['text_list'] = $this->language->get('text_list');
			$this->data['text_grid'] = $this->language->get('text_grid');
			$this->data['text_sort'] = $this->language->get('text_sort');
			$this->data['text_limit'] = $this->language->get('text_limit');
					
			$this->data['button_cart'] = $this->language->get('button_cart');
			$this->data['button_wishlist'] = $this->language->get('button_wishlist');
			$this->data['button_compare'] = $this->language->get('button_compare');
			$this->data['button_continue'] = $this->language->get('button_continue');
			$this->data['module_id'] = $ecflashsale_id;
			
			// Set the last category breadcrumb		
			$url = '';
			
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
									
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get("heading_title"),
				'href'      => $this->url->link('ecflashsale/list', ''),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->data['name'],
				'href'      => $this->url->link('ecflashsale/flashsale', 'ecflashsale_id='.$ecflashsale_id),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['image'] = $flashsale_info['image'];

			if ($flashsale_info['image']) {
				$this->data['thumb'] = $this->model_tool_image->resize($flashsale_info['image'], $image_width, $image_height);

			} else {
				$this->data['thumb'] = '';
			}

			$this->data['compare'] = $this->url->link('product/compare');
			
			$url = '';
			
			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}	
						
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}	

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}	
			
			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$this->data['search'] = $search;

			$this->data['products'] = array();
			
			$data = array(
				'filter_filter'      => $filter, 
				'filter_name'		 => $search,
				'filter_product_id'  => "",
				'filter_category_id' => '',
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);
			$products = array();
			if($flashsale_info['source_from'] == 'category'){
				$data['filter_category_id'] = $flashsale_info['category'];
			}elseif($flashsale_info['source_from'] == 'product'){
				$products = is_array($flashsale_info['products'])?$flashsale_info['products']:explode(",", $flashsale_info['products']);
				$tmp = array();
				if(!empty($products)){
					foreach($products as $product){
						$tmp[] = is_array($product)?(int)$product['product_id']:(int)$product;
					}
					$data['filter_product_id'] = $tmp;
					unset($tmp);
				}
			}else{
				$data['filter_category_id'] = 0;
			}

			$product_total = $this->model_ecflashsale_product->getTotalProducts($data); 
			
			$results = $this->model_ecflashsale_product->getProducts($data);
			
			$url = "";

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
				} else {
					$image = false;
				}
				
				if ($this->customer->isCustomerAllowedToViewPrice()) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$price = false;
				}
				
				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$special = false;
				}	
				
				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price']);
				} else {
					$tax = false;
				}				
				
				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}
								
				$this->data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 100),
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $result['rating'],
					'reviews'     => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
					'href'        => $this->url->link('product/product', '&product_id=' . $result['product_id'] . $url)
				);
			}
			
			$url = '';
			
			$url .= '&ecflashsale_id='.$ecflashsale_id;

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}
				
			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
										
			$this->data['sorts'] = array();
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('ecflashsale/flashsale', '&sort=p.sort_order&order=ASC' . $url)
			);
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('ecflashsale/flashsale', '&sort=pd.name&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('ecflashsale/flashsale',  '&sort=pd.name&order=DESC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('ecflashsale/flashsale',  '&sort=p.price&order=ASC' . $url)
			); 

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('ecflashsale/flashsale',  '&sort=p.price&order=DESC' . $url)
			); 
			
			if ($this->config->get('config_review_status')) {
				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('ecflashsale/flashsale',  '&sort=rating&order=DESC' . $url)
				); 
				
				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('ecflashsale/flashsale',  '&sort=rating&order=ASC' . $url)
				);
			}
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('ecflashsale/flashsale',  '&sort=p.model&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('ecflashsale/flashsale',  '&sort=p.model&order=DESC' . $url)
			);
			
			$url = '';
			
			$url .= '&ecflashsale_id='.$ecflashsale_id;

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}
				
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}	

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			
			$this->data['limits'] = array();
	
			$limits = array_unique(array($this->config->get('config_catalog_limit'), 25, 50, 75, 100));
			
			sort($limits);
	
			foreach($limits as $limits){
				$this->data['limits'][] = array(
					'text'  => $limits,
					'value' => $limits,
					'href'  => $this->url->link('ecflashsale/flashsale', $url . '&limit=' . $limits)
				);
			}
			
			$url = '';
			
			$url .= '&ecflashsale_id='.$ecflashsale_id;

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}
				
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}	

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
	
			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
					
			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->url->link('ecflashsale/flashsale',  $url . '&page={page}');
		
			$this->data['pagination'] = $pagination->render();
		
			$this->data['sort'] = $sort;
			$this->data['order'] = $order;
			$this->data['limit'] = $limit;
		
			$this->data['continue'] = $this->url->link('common/home');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/ecflashsale/flashsale.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/ecflashsale/flashsale.tpl';
			} else {
				$this->template = 'default/template/ecflashsale/flashsale.tpl';
			}
			
			$this->children = array(
				'module/ecflashsale/ecflashsale',
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);
				
			$this->response->setOutput($this->render());										
    	} else {
			$url = '';
			
			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}
			
			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
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
						
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_error'),
				'href'      => $this->url->link('ecflashsale/list', $url),
				'separator' => $this->language->get('text_separator')
			);


				
			$this->document->setTitle($this->language->get('text_error'));

      		$this->data['heading_title'] = $this->language->get('text_error');

      		$this->data['text_error'] = $this->language->get('text_error');

      		$this->data['button_continue'] = $this->language->get('button_continue');

      		$this->data['continue'] = $this->url->link('common/home');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/error/not_found.tpl';
			} else {
				$this->template = 'default/template/error/not_found.tpl';
			}
			
			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);
					
			$this->response->setOutput($this->render());
		}
  	}
  
}
?>