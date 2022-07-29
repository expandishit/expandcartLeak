<?php 
class ControllerEcflashsaleList extends Controller {  
	public function index() {
        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'advanced_deals'");

        if(!$queryMultiseller->num_rows) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
            return;
        }

		$this->language->load('product/category');
		$this->language->load('module/ecflashsale');
		
		$this->load->model('catalog/category');

		$this->load->model('ecflashsale/flashsale');
		
		$this->load->model('catalog/product');
		
		$this->load->model('tool/image'); 
		
		if (isset($this->request->get['tag'])) {
			$tag = $this->request->get['tag'];
		} else {
			$tag = '';
		}

		if (isset($this->request->get['featured'])) {
			$featured = $this->request->get['featured'];
		} else {
			$featured = '';
		}

		if (isset($this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'sort_order';
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
		
		$this->data['search'] = $search;

		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
       		'separator' => false
   		);

   		
		$settings = $this->config->get("ecflashsale_setting");
		$status = isset($settings['status'])?$settings['status']:1;

		$settings['flashsale_image_width'] = isset($settings['flashsale_listing_image_width'])?$settings['flashsale_listing_image_width']:$this->config->get('config_image_product_width');
		$settings['flashsale_image_height'] = isset($settings['flashsale_listing_image_height'])?$settings['flashsale_listing_image_height']:$this->config->get('config_image_product_height');

		$settings['description_max_chars'] = isset($settings['flashsale_desc_maxchars'])?$settings['flashsale_desc_maxchars']:100;
		$settings['show_saleoff'] = isset($settings['show_saleoff'])?$settings['show_saleoff']:1;
		$settings['show_expire_date'] = isset($settings['show_expire_date'])?$settings['show_expire_date']:1;

		$data = array("filter_name" => $search,
						'filter_tag' => $tag,
						'filter_featured' => $featured,
					    'sort'               => $sort,
						'order'              => $order,
						'start'              => ($page - 1) * $limit,
						'limit'              => $limit);
		$flashsales = false;
		if($status) {
			$flashsales = $this->model_ecflashsale_flashsale->getFlashsales($data);

			$flashsale_total = $this->model_ecflashsale_flashsale->getTotalFlashsales($data); 
		}
		
	    /*Check module enabled*/
		$lang_id = $this->config->get('config_language_id');

		if ($flashsales) {
			$settings['meta_description'] = isset($settings['flashsale_meta_description'])?$settings['flashsale_meta_description']:"";
			$settings['meta_keyword'] = isset($settings['flashsale_meta_keyword'])?$settings['flashsale_meta_keyword']:"";

		    $this->data['heading_title'] = $this->language->get("heading_title");
		    $this->document->setTitle($this->data['heading_title']);
			$this->document->setDescription($settings['meta_description']);
			$this->document->setKeywords($settings['meta_keyword']);
			$this->document->addScript('catalog/view/javascript/jquery/jquery.total-storage.min.js');
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/stylesheet/ecflashsale.css')) {
				$this->document->addStyle('catalog/view/theme/'.$this->config->get('config_template').'/stylesheet/ecflashsale.css');
			} else {
				$this->document->addStyle('catalog/view/theme/default/stylesheet/ecflashsale.css');
			}
			$this->document->addScript('catalog/view/javascript/ecflashsale/countdown.js');

			$this->data['show_countdown'] = isset($settings['show_countdown'])?$settings['show_countdown']:1;
		    $this->data['show_social'] = isset($settings['flashsale_show_social'])?$settings['flashsale_show_social']:1;
		    
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

			$this->data['show_sale_off'] = $settings['show_saleoff'];
			$this->data['show_expire_date'] = $settings['show_expire_date'];

			$this->data['description'] = isset($settings['flashsale_description'])?$settings['flashsale_description']:array();
			$this->data['description'] = isset($this->data['description'][$lang_id])?html_entity_decode($this->data['description'][$lang_id], ENT_QUOTES, 'UTF-8'):"";
			
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
				'text'      => $this->data['heading_title'],
				'href'      => $this->url->link('ecflashsale/list', ''),
				'separator' => $this->language->get('text_separator')
			);
			
			$this->data['compare'] = $this->url->link('product/compare');
			
			$url = '';
			
			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['featured'])) {
				$url .= '&featured=' . $this->request->get['featured'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
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
			$this->data['flashsales'] = array();
			
			foreach ($flashsales as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $settings['flashsale_image_width'], $settings['flashsale_image_height']);
				} else {
					$image = false;
				}
				$params = isset($result['params'])?unserialize($result['params']):array();
				$date_end = $result['date_end'];
		    	$discount_percent = $result['percent'];
		    	$discount_amount = $result['amount'];
		    
		    	$date_diff = $this->model_ecflashsale_flashsale->dateDiff( $date_end );

		    	$is_expired = ($date_diff <= 0) ? true:false ;

		   		$text_discount = $this->language->get("text_discount");

			    if(!empty($discount_percent) && $discount_percent > 0){
		    		$discount_value = $discount_percent."%";
			    }else{
			    	$discount_value = $this->currency->format( $discount_amount );
			    }
			    $text_discount = sprintf($text_discount, $discount_value);

				$this->data['flashsales'][] = array(
					'ecflashsale_id'  => $result['ecflashsale_id'],
					'thumb'       => $image,
					'text_discount' => $text_discount,
					'date_start'	=> $result['date_start'],
					'date_end'		=> $date_end,
					'discount_value'	=> $discount_value,
					'discount_percent' => $discount_percent,
					'discount_amount' => $discount_amount,
					'is_expired'	=> $is_expired,
					'image'		  => $result['image'],
					'featured'	 => $result['featured'],
					'tags'		=> $result['tag'],
					'name'        => $result['name'],
					'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $settings['description_max_chars']),
					'href'        => $this->url->link('ecflashsale/flashsale', '&ecflashsale_id=' . $result['ecflashsale_id'])
				);
			}
		
			$url = '';
			
			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}
				
			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
										
			$this->data['sorts'] = array();
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'sort_order-ASC',
				'href'  => $this->url->link('ecflashsale/list', '&sort=sort_order&order=ASC' . $url)
			);
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'fd.name-ASC',
				'href'  => $this->url->link('ecflashsale/list', '&sort=fd.name&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'fd.name-DESC',
				'href'  => $this->url->link('ecflashsale/list',  '&sort=fd.name&order=DESC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_date_end_asc'),
				'value' => 'date_end-ASC',
				'href'  => $this->url->link('ecflashsale/list',  '&sort=date_end&order=ASC' . $url)
			); 

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_date_end_desc'),
				'value' => 'date_end-DESC',
				'href'  => $this->url->link('ecflashsale/list',  '&sort=date_end&order=DESC' . $url)
			);
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_featured_asc'),
				'value' => 'featured-ASC',
				'href'  => $this->url->link('ecflashsale/list',  '&sort=featured&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_featured_desc'),
				'value' => 'featured-DESC',
				'href'  => $this->url->link('ecflashsale/list',  '&sort=featured&order=DESC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_most_viewed'),
				'value' => 'hits-ASC',
				'href'  => $this->url->link('ecflashsale/list',  '&sort=hits&order=DESC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_latest'),
				'value' => 'date_modified-DESC',
				'href'  => $this->url->link('ecflashsale/list',  '&sort=date_modified&order=DESC' . $url)
			);

			
			$url = '';
			
			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['featured'])) {
				$url .= '&featured=' . $this->request->get['featured'];
			}

			if (isset($this->request->get['keyword'])) {
				$url .= '&keyword=' . $this->request->get['keyword'];
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
					'href'  => $this->url->link('ecflashsale/list', $url . '&limit=' . $limits)
				);
			}
			
			$url = '';

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['featured'])) {
				$url .= '&featured=' . $this->request->get['featured'];
			}

			if (isset($this->request->get['keyword'])) {
				$url .= '&keyword=' . $this->request->get['keyword'];
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
			$pagination->total = $flashsale_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->url->link('ecflashsale/list',  $url . '&page={page}');
		
			$this->data['pagination'] = $pagination->render();

			$this->data['sort'] = $sort;
			$this->data['order'] = $order;
			$this->data['limit'] = $limit;
		
			$this->data['continue'] = $this->url->link('common/home');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/ecflashsale/list.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/ecflashsale/list.tpl';
			} else {
				$this->template = 'default/template/ecflashsale/list.tpl';
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
    	} else {
    		$settings['meta_description'] = isset($settings['flashsale_meta_description'])?$settings['flashsale_meta_description']:"";
			$settings['meta_keyword'] = isset($settings['flashsale_meta_keyword'])?$settings['flashsale_meta_keyword']:"";

		    $this->data['heading_title'] = $this->language->get("heading_title");
		    $this->document->setTitle($this->data['heading_title']);
			$this->document->setDescription($settings['meta_description']);
			$this->document->setKeywords($settings['meta_keyword']);
			$this->document->addScript('catalog/view/javascript/jquery/jquery.total-storage.min.js');
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/stylesheet/ecflashsale.css')) {
				$this->document->addStyle('catalog/view/theme/'.$this->config->get('config_template').'/stylesheet/ecflashsale.css');
			} else {
				$this->document->addStyle('catalog/view/theme/default/stylesheet/ecflashsale.css');
			}

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

			$this->data['description'] = isset($settings['flashsale_description'])?$settings['flashsale_description']:array();
			$this->data['description'] = isset($this->data['description'][$lang_id])?html_entity_decode($this->data['description'][$lang_id], ENT_QUOTES, 'UTF-8'):"";

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
				'text'      => $this->data['heading_title'],
				'href'      => $this->url->link('ecflashsale/list', ''),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['sorts'] = array();
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'sort_order-ASC',
				'href'  => $this->url->link('ecflashsale/list', '&sort=sort_order&order=ASC' . $url)
			);
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'fd.name-ASC',
				'href'  => $this->url->link('ecflashsale/list', '&sort=fd.name&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'fd.name-DESC',
				'href'  => $this->url->link('ecflashsale/list',  '&sort=fd.name&order=DESC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_date_end_asc'),
				'value' => 'date_end-ASC',
				'href'  => $this->url->link('ecflashsale/list',  '&sort=date_end&order=ASC' . $url)
			); 

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_date_end_desc'),
				'value' => 'date_end-DESC',
				'href'  => $this->url->link('ecflashsale/list',  '&sort=date_end&order=DESC' . $url)
			);
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_featured_asc'),
				'value' => 'featured-ASC',
				'href'  => $this->url->link('ecflashsale/list',  '&sort=featured&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_featured_desc'),
				'value' => 'featured-DESC',
				'href'  => $this->url->link('ecflashsale/list',  '&sort=featured&order=DESC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_most_viewed'),
				'value' => 'hits-ASC',
				'href'  => $this->url->link('ecflashsale/list',  '&sort=hits&order=DESC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_latest'),
				'value' => 'date_modified-DESC',
				'href'  => $this->url->link('ecflashsale/list',  '&sort=date_modified&order=DESC' . $url)
			);

			$this->data['limits'] = array();
	
			$limits = array_unique(array($this->config->get('config_catalog_limit'), 25, 50, 75, 100));
			
			sort($limits);
	
			foreach($limits as $limits){
				$this->data['limits'][] = array(
					'text'  => $limits,
					'value' => $limits,
					'href'  => $this->url->link('ecflashsale/list', $url . '&limit=' . $limits)
				);
			}

			$this->data['flashsales'] =  array();

			$this->data['sort'] = $sort;

			$this->data['order'] = $order;

			$this->data['limit'] = $limit;

      		$this->data['text_error'] = $this->language->get('text_error');

      		$this->data['text_empty'] = $this->language->get('text_empty_flashsales');

      		$this->data['button_continue'] = $this->language->get('button_continue');

      		$this->data['continue'] = $this->url->link('common/home');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/ecflashsale/list.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/ecflashsale/list.tpl';
			} else {
				$this->template = 'default/template/ecflashsale/list.tpl';
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