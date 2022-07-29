<?php 
class ControllerEcflashsaleSales extends Controller {  
	public function index() {
        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'advanced_deals'");

        if(!$queryMultiseller->num_rows) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
            return;
        }

		$this->language->load('product/category');
		$this->language->load('module/ecflashsale');
		
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('ecflashsale/product');

		$this->load->model('tool/image'); 
		
		if (isset($this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}

		if (isset($this->request->get['category_id'])) {
			$category_id = $this->request->get['category_id'];
		} else {
			$category_id = '0';
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

   		
		$flashsale_setting = $this->config->get("ecflashsale_setting");
	    /*Check module enabled*/
	    $enabled = isset($flashsale_setting["status"])?$flashsale_setting['status']: 1;
		$lang_id = $this->config->get('config_language_id');

		if ($enabled && $flashsale_setting && isset($flashsale_setting['mini_mode']) && $flashsale_setting['mini_mode']) {
			$flashsale_setting['deal_meta_description'] = isset($flashsale_setting['deal_meta_description'])?$flashsale_setting['deal_meta_description']:"";
			$flashsale_setting['deal_meta_keyword'] = isset($flashsale_setting['deal_meta_keyword'])?$flashsale_setting['deal_meta_keyword']:"";
			$image_width = isset($flashsale_setting['meta_deal_image_width'])?$flashsale_setting['meta_deal_image_width']:300;
			$image_height = isset($flashsale_setting['meta_deal_image_height'])?$flashsale_setting['meta_deal_image_height']:200;

		    $this->data['image'] = $this->data['thumb'] = false;
		    if (isset($flashsale_setting['deal_image']) && file_exists(DIR_IMAGE . $flashsale_setting['deal_image']) && $flashsale_setting['deal_image']!="no_image.jpg") {
				$image = $flashsale_setting['deal_image'];
				$this->data['image'] = $image;
				$this->data['thumb'] = $this->model_tool_image->resize($image, $image_width , $image_height, "w");
			} 
			$this->data['description'] = isset($flashsale_setting['deal_description'][$lang_id])?$flashsale_setting['deal_description'][$lang_id]:"";
		    $this->data['description'] = html_entity_decode($this->data['description'], ENT_QUOTES, 'UTF-8');
		    $this->data['description'] = isset($this->data['description'])?str_replace(array("&lt;","&gt;","&quot;"),array('<','>','"'),$this->data['description']):"";

		    $this->data['title'] = isset($flashsale_setting['deal_title'][$lang_id])?$flashsale_setting['deal_title'][$lang_id]:$this->language->get("text_on_sales");
		    $this->data['title'] = html_entity_decode($this->data['title'], ENT_QUOTES, 'UTF-8');

		    $this->data['show_filter_category'] = isset($flashsale_setting['show_filter_category'])?(int)$flashsale_setting['show_filter_category']:1;
		    

		    $this->data['heading_title'] = $this->data['title'];
		    $this->document->setTitle($this->data['heading_title']);
			$this->document->setDescription($flashsale_setting['deal_meta_description']);
			$this->document->setKeywords($flashsale_setting['deal_meta_keyword']);
			$this->document->addScript('catalog/view/javascript/jquery/jquery.total-storage.min.js');

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
			
			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
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

			$this->data['products'] = array();
			
			$data = array(
				'filter_name'         => $search, 
				'filter_description'  => true,
				'filter_category_id'  => $category_id,
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);
			$products = array();

			$product_total = $this->model_ecflashsale_product->getTotalProductSpecials($data); 

			$this->data['total'] = $product_total;
			
			$results = $this->model_ecflashsale_product->getProductSpecials($data);

			$this->data['products'] = $results;

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
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('ecflashsale/sales', '&sort=p.sort_order&order=ASC' . $url)
			);
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('ecflashsale/sales', '&sort=pd.name&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('ecflashsale/sales',  '&sort=pd.name&order=DESC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('ecflashsale/sales',  '&sort=p.price&order=ASC' . $url)
			); 

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('ecflashsale/sales',  '&sort=p.price&order=DESC' . $url)
			); 
			
			if ($this->config->get('config_review_status')) {
				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('ecflashsale/sales',  '&sort=rating&order=DESC' . $url)
				); 
				
				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('ecflashsale/sales',  '&sort=rating&order=ASC' . $url)
				);
			}
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('ecflashsale/sales',  '&sort=p.model&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('ecflashsale/sales',  '&sort=p.model&order=DESC' . $url)
			);
			
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
			
			$this->data['categories'] = array();

			$show_categories = isset($flashsale_setting['show_category_id'])?$flashsale_setting['show_category_id']:"";

			if(is_array($show_categories)) {
				$show_categories = implode(",", $show_categories);
			}
			$results = $this->model_ecflashsale_product->getAllCategories($show_categories);

			foreach ($results as $result) {
				$data = array(
					'filter_category_id'  => $result['category_id']
				);

				$cate_product_total = $this->model_ecflashsale_product->getTotalProductSpecials($data);				

				$this->data['categories'][] = array(
					'name'  => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $cate_product_total . ')' : ''),
					'category_id' =>$result['category_id'],
					'href'  => $this->url->link('ecflashsale/sales', 'category_id=' . $result['category_id'] . $url)
				);
			}

			$this->data['limits'] = array();
	
			$limits = array_unique(array($this->config->get('config_catalog_limit'), 25, 50, 75, 100));
			
			sort($limits);
	
			foreach($limits as $limits){
				$this->data['limits'][] = array(
					'text'  => $limits,
					'value' => $limits,
					'href'  => $this->url->link('ecflashsale/sales', $url . '&limit=' . $limits)
				);
			}
			
			$url = '';
			
			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
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
			$pagination->url = $this->url->link('ecflashsale/sales',  $url . '&page={page}');
		
			$this->data['pagination'] = $pagination->render();
		
			$this->data['sort'] = $sort;
			$this->data['order'] = $order;
			$this->data['limit'] = $limit;
			$this->data['search'] = $search;
			$this->data['category_id'] = $category_id;
		
			$this->data['continue'] = $this->url->link('common/home');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/ecflashsale/sales.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/ecflashsale/sales.tpl';
			} else {
				$this->template = 'default/template/ecflashsale/sales.tpl';
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
			$url = '';
			
			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
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
				
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
						
			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
						
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_error'),
				'href'      => $this->url->link('ecflashsale/sales', $url),
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