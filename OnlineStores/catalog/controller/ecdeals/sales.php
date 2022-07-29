<?php 
class ControllerEcdealsSales extends Controller {  
	public function index() {
        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'advanced_deals'");

        if(!$queryMultiseller->num_rows) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
            return;
        }

		$this->language->load('product/category');
		$this->language->load('module/ecdeals');
		
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('ecdeals/product');

        $this->load->model('ecflashsale/product');

		$this->load->model('tool/image');
		
		if (isset($this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}

		if (isset($this->request->get['status'])) {
			$filter_deal_status = $this->request->get['status']; /*status: active, today, past, upcomming*/
		} else {
			$filter_deal_status = 'active';
		}

		if (isset($this->request->get['category_id'])) {
			$category_id = $this->request->get['category_id'];
		} else {
			$category_id = '';
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

   		/*Set categories to filter*/
   		$this->setCategoriesFilter();
   		
   		$this->data['category_id'] = $category_id;

		$deals_setting = $this->config->get("ecdeals_setting");

		$deals_setting = is_array($deals_setting)?$deals_setting:unserialize($deals_setting);
		
		$this->data['status'] = $filter_deal_status;

		$this->data['text_refine'] = $this->language->get("text_refine");

        $flashsale_setting = $this->config->get("ecflashsale_setting");
		
	    /*Check module enabled*/
	    $show_expired = isset($deals_setting["show_expired_deal"])?$deals_setting['show_expired_deal']: 1;
	    $enabled = isset($deals_setting["mini_mode"])?$deals_setting['mini_mode']: 1;
		$lang_id = $this->config->get('config_language_id');

		if ($deals_setting && $enabled) {
			$deals_setting['deal_meta_description'] = isset($deals_setting['deal_meta_description'])?$deals_setting['deal_meta_description']:"";

			if(!$category_id && isset($deals_setting['show_category_id']) && $deals_setting['show_category_id']) {
				$category_id = $deals_setting['show_category_id'];
			}

			$this->data['category_id'] = $category_id;
			

			$deals_setting['deal_meta_keyword'] = isset($deals_setting['deal_meta_keyword'])?$deals_setting['deal_meta_keyword']:"";
			$image_width = isset($deals_setting['meta_deal_image_width'])?$deals_setting['meta_deal_image_width']:300;
			$image_height = isset($deals_setting['meta_deal_image_height'])?$deals_setting['meta_deal_image_height']:200;

		    $this->data['image'] = $this->data['thumb'] = false;
		    if (isset($deals_setting['deal_image']) && file_exists(DIR_IMAGE . $deals_setting['deal_image'])) {
				$image = $deals_setting['deal_image'];
				$this->data['image'] = $image;
				$this->data['thumb'] = $this->model_tool_image->resize($image, $image_width , $image_height, "w");
			} 
			$this->data['description'] = isset($deals_setting['deal_description'][$lang_id])?$deals_setting['deal_description'][$lang_id]:"";
		    $this->data['description'] = html_entity_decode($this->data['description'], ENT_QUOTES, 'UTF-8');
		    $this->data['description'] = isset($this->data['description'])?str_replace(array("&lt;","&gt;","&quot;"),array('<','>','"'),$this->data['description']):"";
		    

		    $this->data['heading_title'] = $this->language->get("text_on_sales");
		    $this->document->setTitle($this->data['heading_title']);
			$this->document->setDescription($deals_setting['deal_meta_description']);
			$this->document->setKeywords($deals_setting['deal_meta_keyword']);
			$this->document->addScript('catalog/view/javascript/jquery/jquery.total-storage.min.js');
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/stylesheet/ecdeals.css')) {
				$this->document->addStyle('catalog/view/theme/'.$this->config->get('config_template').'/stylesheet/ecdeals.css');
			} else {
				$this->document->addStyle('catalog/view/theme/default/stylesheet/ecdeals.css');
			}
			$this->document->addScript('catalog/view/javascript/ecdeals/countdown.js');

			$this->data['date_format'] = isset($deals_setting["date_format"])?$deals_setting['date_format']: "Y-m-d H:i:s";

			$this->data['cols'] = isset($deals_setting['cols'])?$deals_setting['cols']:3;
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
			
			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
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
			
			$this->setStatusLink( $deals_setting, $url );

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->data['heading_title'],
				'href'      => $this->url->link('ecdeals/list', ''),
				'separator' => $this->language->get('text_separator')
			);
			
			$this->data['compare'] = $this->url->link('product/compare');
			
			$url = '';

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
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

            $this->data['categories'] = array();

            $this->data['categories'][] = array(
                'name' => $this->language->get('text_all_categories_deals'),
                'category_id' => '',
                'href' => $this->url->link('ecdeals/sales', $url)
            );

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

                if ($cate_product_total > 0) {
                    $this->data['categories'][] = array(
                        'name' => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $cate_product_total . ')' : ''),
                        'category_id' => $result['category_id'],
                        'href' => $this->url->link('ecdeals/sales', 'category_id=' . $result['category_id'])
                    );
                }
            }

			$this->data['products'] = array();
			$data = array(
				'filter_name'      => $search, 
				'filter_category_id'	=> $category_id,
				'filter_description'  => true,
				'filter_deal_status'  => $filter_deal_status,
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);
			$products = $results = array();
			$product_total = 0;

			$allow_show_deals = true;
			if(!$show_expired && $filter_deal_status == "past") {
		  		$allow_show_deals = false;
		  	}

		  	if($allow_show_deals) {
				$product_total = $this->model_ecdeals_product->getTotalProductSpecials($data);
				$results = $this->model_ecdeals_product->getProductSpecials($data);
			}
			
			$this->data['total'] = $product_total;

			$this->data['products'] = $results;

			$url = '';
			
			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['status'])) {
				$url .= '&status=' . $this->request->get['status'];
			}

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
				'href'  => $this->url->link('ecdeals/sales', '&sort=p.sort_order&order=ASC' . $url)
			);
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('ecdeals/sales', '&sort=pd.name&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('ecdeals/sales',  '&sort=pd.name&order=DESC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('ecdeals/sales',  '&sort=p.price&order=ASC' . $url)
			); 

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('ecdeals/sales',  '&sort=p.price&order=DESC' . $url)
			); 
			
			if ($this->config->get('config_review_status')) {
				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('ecdeals/sales',  '&sort=rating&order=DESC' . $url)
				); 
				
				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('ecdeals/sales',  '&sort=rating&order=ASC' . $url)
				);
			}
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('ecdeals/sales',  '&sort=p.model&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('ecdeals/sales',  '&sort=p.model&order=DESC' . $url)
			);
			
			$url = '';
			
			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['status'])) {
				$url .= '&status=' . $this->request->get['status'];
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
			
			$this->data['limits'] = array();
	
			$limits = array_unique(array($this->config->get('config_catalog_limit'), 25, 50, 75, 100));
			
			sort($limits);
	
			foreach($limits as $limits){
				$this->data['limits'][] = array(
					'text'  => $limits,
					'value' => $limits,
					'href'  => $this->url->link('ecdeals/sales', $url . '&limit=' . $limits)
				);
			}
			
			$url = '';
			
			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['status'])) {
				$url .= '&status=' . $this->request->get['status'];
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
					
			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->url->link('ecdeals/sales',  $url . '&page={page}');
		
			$this->data['pagination'] = $pagination->render();
		
			$this->data['sort'] = $sort;
			$this->data['order'] = $order;
			$this->data['limit'] = $limit;
			$this->data['search'] = $search;
		
			$this->data['continue'] = $this->url->link('common/home');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/ecdeals/sales.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/ecdeals/sales.tpl';
			} else {
				$this->template = 'default/template/ecdeals/sales.tpl';
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
				'href'      => $this->url->link('ecdeals/sales', $url),
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

  	function setCategoriesFilter( ) {
  		// Menu
		$this->load->model('catalog/category');
		
		$this->load->model('catalog/product');
		
		// 3 Level Category Search
		$this->data['categories'] = array();
					
		$categories_1 = $this->model_catalog_category->getCategories(0);
		
		foreach ($categories_1 as $category_1) {
			$level_2_data = array();
			
			$categories_2 = $this->model_catalog_category->getCategories($category_1['category_id']);
			
			foreach ($categories_2 as $category_2) {
				$level_3_data = array();
				
				$categories_3 = $this->model_catalog_category->getCategories($category_2['category_id']);
				
				foreach ($categories_3 as $category_3) {
					$level_3_data[] = array(
						'category_id' => $category_3['category_id'],
						'name'        => $category_3['name'],
					);
				}
				
				$level_2_data[] = array(
					'category_id' => $category_2['category_id'],	
					'name'        => $category_2['name'],
					'children'    => $level_3_data
				);					
			}
			
			$this->data['categories'][] = array(
				'category_id' => $category_1['category_id'],
				'name'        => $category_1['name'],
				'children'    => $level_2_data
			);
		}
  	}
  	function setStatusLink($settings = array(), $url ="" ) {

  		/*$url = !empty($url)?"&".$url:"";*/
  		$url = "";
  		$this->data["deal_types"] = array("active" => array("name" => $this->language->get("text_active_deals"),
															"link" => $this->url->link('ecdeals/sales', "status=active".$url)),
										  "today" => array("name" => $this->language->get("text_today_deals"), 
										  					"link" => $this->url->link('ecdeals/sales', "status=today".$url) ),
										  "past" => array("name" => $this->language->get("text_past_deals"),
										  					"link" => $this->url->link('ecdeals/sales', "status=past".$url)),
										  "upcomming" => array("name" => $this->language->get("text_upcomming_deals"),
										  					 "link" => $this->url->link('ecdeals/sales', "status=upcomming".$url)));

		$show_tab_active = isset($settings['show_tab_active'])?$settings['show_tab_active']:1;
		$show_tab_past = isset($settings['show_tab_past'])?$settings['show_tab_past']:1;
		$show_tab_today = isset($settings['show_tab_today'])?$settings['show_tab_today']:1;
		$show_tab_upcomming = isset($settings['show_tab_upcomming'])?$settings['show_tab_upcomming']:1;

		if(!$show_tab_active) {
			unset($this->data['deal_types']['active']);
		}
		if(!$show_tab_past) {
			unset($this->data['deal_types']['past']);
		}
		if(!$show_tab_today) {
			unset($this->data['deal_types']['today']);
		}
		if(!$show_tab_upcomming) {
			unset($this->data['deal_types']['upcomming']);
		}

		return true;
  	}
}
?>