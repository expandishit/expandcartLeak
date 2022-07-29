<?php 
class ControllerSellerCatalogSeller extends ControllerSellerCatalog {
	
	protected $isCustomerAllowedToViewPrice;

	public function __construct($registry) {
		parent::__construct($registry);
		
		$this->language->load_json('product/category');
		$this->load->model('localisation/country');
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		parent::__construct($registry);
		$this->isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();
	}
	
	public function index() {
		$this->document->addScript('expandish/view/javascript/jquery/jquery.total-storage.min.js');
		// get multi seller settings
		$this->load->model('setting/setting');
		$multiSellerSettings = $this->model_setting_setting->getSetting('multiseller');
		
		$this->data['text_display'] = $this->language->get('text_display');
		$this->data['text_list'] = $this->language->get('text_list');
		$this->data['text_grid'] = $this->language->get('text_grid');
		$this->data['text_sort'] = $this->language->get('text_sort');
		$this->data['text_limit'] = $this->language->get('text_limit');
		
		if (isset($this->request->get['sort'])) {
			$order_by = $this->request->get['sort'];
		} else {
			$order_by = 'ms.nickname';
		}
		
		if (isset($this->request->get['order'])) {
			$order_way = $this->request->get['order'];
		} else {
			$order_way = 'ASC';
		}
		
		$page = isset($this->request->get['page']) ? $this->request->get['page'] : 1;
		
		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_catalog_limit');
		}
		
		$this->data['products'] = array();
		
		// $cache_total_sellers = $this->cache->get('catalog_seller_total');
		
		// if(!$cache_total_sellers || $this->request->get['search']!="") {

			$total_sellers = $this->MsLoader->MsSeller->getTotalSellers(array(
				'seller_status' => array(MsSeller::STATUS_ACTIVE) 
			));

			// $this->cache->set('catalog_seller_total', $total_sellers);
		// } else {
		// 	$total_sellers = $cache_total_sellers;
		// }

		// $cache_seller = $this->cache->get('catalog_seller');

		// if(!$cache_seller || $this->request->get['search']!="") {
			if ($this->config->get('msconf_enable_search_by_seller') && $this->config->get('msconf_enable_search_by_seller') == 1
			&& $this->request->get['search']!="") {
				$filter_array = array(
					'filter' => $this->request->get['search']
				);

				if ($this->config->get('msconf_search_by_city')){
					$this->load->model('localisation/zone');
					$zone_id = $this->model_localisation_zone->getZoneIdByName($this->request->get['search']);
					$filter_array['zone_id'] =$zone_id;
				}
				$results = $this->MsLoader->MsSeller->getSellersByProduct(
					$filter_array
				);
			} else {

                $results = $this->MsLoader->MsSeller->getSellers(
                    array(
                        'seller_status' => array(MsSeller::STATUS_ACTIVE)
                    ),
                    array(
                        'order_by' => $order_by,
                        'order_way' => $order_way,
                        'offset' => ($page - 1) * $limit,
                        'limit' => $limit
                    )
                );
            }

			foreach ($results as $result) {
				if ($result['ms.avatar']) {
					$image = $this->MsLoader->MsFile->resizeImage($result['ms.avatar'], $this->config->get('msconf_seller_avatar_seller_list_image_width'), $this->config->get('msconf_seller_avatar_seller_list_image_height'));
				} else {
					$image = $this->MsLoader->MsFile->resizeImage('ms_no_image.jpg', $this->config->get('msconf_seller_avatar_seller_list_image_width'), $this->config->get('msconf_seller_avatar_seller_list_image_height'));
				}

                if (!$image) {
                    $image = $this->MsLoader->MsFile->resizeImage('no_image.jpg', $this->config->get('msconf_seller_avatar_seller_list_image_width'), $this->config->get('msconf_seller_avatar_seller_list_image_height'));
                }

                // check action page
				if(is_array($multiSellerSettings) && isset($multiSellerSettings['msconf_default_url']) && $multiSellerSettings['msconf_default_url'] == "products")
				{
					$actionUrl = $this->url->link('seller/catalog-seller/products', '&seller_id=' . $result['seller_id']);
				}else{
					$actionUrl = $this->url->link('seller/catalog-seller/profile', '&seller_id=' . $result['seller_id']);
				}

				$country = $this->model_localisation_country->getCountry($result['ms.country_id']);
				$this->data['sellers'][] = array(
					'seller_id'  => $result['seller_id'],
					'thumb'       => $image,
					'nickname'        => $result['ms.nickname'],
					'description' => utf8_substr(strip_tags(html_entity_decode($result['ms.description'], ENT_QUOTES, 'UTF-8')), 0, 200) . (mb_strlen($result['ms.description']) > 200 ? '..' : ''),
					//'rating'      => $result['rating'],
					'country' => ($country ? $country['name'] : NULL),
					'company' => ($result['ms.company'] ? $result['ms.company'] : NULL),
					'website' => ($result['ms.website'] ? $result['ms.website'] : NULL),
					'country_flag' => ($country ? 'image/flags/' . strtolower($country['iso_code_2']) . '.png' : NULL),
					'total_sales' => $this->MsLoader->MsSeller->getSalesForSeller($result['seller_id']),
					'total_products' => $this->MsLoader->MsProduct->getTotalProducts(array(
						'seller_id' => $result['seller_id'],
						'product_status' => array(MsProduct::STATUS_ACTIVE)
					)),
                    'href' => $actionUrl,
					'products' => $this->url->link('seller/catalog-seller/products', '&seller_id=' . $result['seller_id']),
				);
			}
			
			// if($this->request->get['search']==""){
			// 	$this->cache->set('catalog_seller', $this->data['sellers']);
			// }
		// } else {
		// 	$this->data['sellers'] = $cache_seller;
		// }

		$url = '';

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}
						
		$this->data['sorts'] = array();
		
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('ms_sort_nickname_asc'),
			'value' => 'ms.nickname-ASC',
			'href'  => $this->url->link('seller/catalog-seller', '&sort=ms.nickname&order=ASC' . $url)
		);

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('ms_sort_nickname_desc'),
			'value' => 'ms.nickname-DESC',
			'href'  => $this->url->link('seller/catalog-seller', '&sort=ms.nickname&order=DESC' . $url)
		);

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('ms_sort_country_asc'),
			'value' => 'ms.country_id-ASC',
			'href'  => $this->url->link('seller/catalog-seller', '&sort=ms.country_id&order=ASC' . $url)
		); 

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('ms_sort_country_desc'),
			'value' => 'ms.country_id-DESC',
			'href'  => $this->url->link('seller/catalog-seller', '&sort=ms.country_id&order=DESC' . $url)
		); 
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}	

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		
		$this->data['limits'] = array();
		
		$this->data['limits'][] = array(
			'text'  => $this->config->get('config_catalog_limit'),
			'value' => $this->config->get('config_catalog_limit'),
			'href'  => $this->url->link('seller/catalog-seller', $url . '&limit=' . $this->config->get('config_catalog_limit'))
		);
					
		$this->data['limits'][] = array(
			'text'  => 25,
			'value' => 25,
			'href'  => $this->url->link('seller/catalog-seller', $url . '&limit=25')
		);
		
		$this->data['limits'][] = array(
			'text'  => 50,
			'value' => 50,
			'href'  => $this->url->link('seller/catalog-seller', $url . '&limit=50')
		);

		$this->data['limits'][] = array(
			'text'  => 75,
			'value' => 75,
			'href'  => $this->url->link('seller/catalog-seller', $url . '&limit=75')
		);
		
		$this->data['limits'][] = array(
			'text'  => 100,
			'value' => 100,
			'href'  => $this->url->link('seller/catalog-seller', $url . '&limit=100')
		);
		
		$url = '';

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
		$pagination->total = $total_sellers;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('seller/catalog-seller', $url . '&page={page}');
	
		$this->data['pagination'] = $pagination->render();
		
		$this->data['sort'] = $order_by;
		$this->data['order'] = $order_way;
		$this->data['limit'] = $limit;		
		
		$this->data['continue'] = $this->url->link('common/home');

		$lang_id = $this->config->get('config_language_id');
		$seller_title = $this->config->get('msconf_seller_title');

		if($seller_title && $seller_title[$lang_id]['multi']){
			$this->document->setTitle($seller_title[$lang_id]['multi']);
			$this->data['ms_catalog_sellers_heading'] = $seller_title[$lang_id]['multi'];
		}else{
			$this->document->setTitle($this->language->get('ms_catalog_sellers_heading'));
			$this->data['ms_catalog_sellers_heading'] = $this->language->get('ms_catalog_sellers_heading');

		}

		//Sellers paragraph
		$this->data['seller_paragraph'] = '';
		$seller_paragraph = $this->config->get('msconf_seller_paragraph');
		if(count($seller_paragraph)){
			$this->data['seller_paragraph'] = html_entity_decode($seller_paragraph[$lang_id], ENT_QUOTES, 'UTF-8');
		}
		//////////////////////////

		//Number of seller per row
		$this->data['row_class'] = 3; // default col-md-3
		$msconf_sellers_per_row = $this->config->get('msconf_sellers_per_row');
		if($msconf_sellers_per_row){
			$this->data['row_class'] = 12/$msconf_sellers_per_row;
		}
		//////////////////////////

		//Manage show totlas
		$this->data['sellers_totals'] = $this->config->get('msconf_sellers_totals');
		///////////////////

		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => ($seller_title && $seller_title[$lang_id]['multi']) ? $seller_title[$lang_id]['multi'] : $this->language->get('ms_catalog_sellers'),
				'href' => $this->url->link('seller/catalog-seller', '', 'SSL'), 
			)
		));
		
		//Get Seller title from setting table
		$this->load->model('seller/seller');
		$sellers_title = $this->model_seller_seller->getSellersTitle();
		$this->data['ms_catalog_sellers_empty'] = sprintf( $this->language->get('ms_catalog_sellers_empty') , strtolower($sellers_title) );
		list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('catalog-seller');
		$this->response->setOutput($this->render_ecwig());
	}
		
	public function profile() {
		$this->document->addScript('expandish/view/javascript/jquery/tabs.js');
		$this->document->addStyle('expandish/view/theme/default/js/Swiper/Photoswipe/photoswipe.min.css');
		$this->document->addStyle('expandish/view/theme/default/js/Swiper/Photoswipe/default-skin/default-skin.min.css');
		$this->document->addStyle('expandish/view/theme/default/js/Swiper/swiper.min.css');
		$this->document->addScript('expandish/view/theme/default/js/Swiper/Photoswipe/photoswipe.min.js');
		$this->document->addScript('expandish/view/theme/default/js/Swiper/Photoswipe/photoswipe-ui-default.min.js');
		$this->document->addScript('expandish/view/theme/default/js/Swiper/swiper.min.js');
        if ($this->preAction->isActive()) {
            if (isset($this->request->get['slug']) && !isset($this->request->get['seller_id'])) {
                $name = $this->request->get['slug'];
                $data = $this->MsLoader->MsSeller->getSellerIdByNickname($name);

                if ($data) {
                    $this->request->get['seller_id'] = $data['seller_id'];
                }
            }
        }

		if (isset($this->request->get['seller_id'])) {
			$seller = $this->MsLoader->MsSeller->getSeller($this->request->get['seller_id']);
		}

		if (!isset($seller) || empty($seller) || $seller['ms.seller_status'] != MsSeller::STATUS_ACTIVE) {
			$this->redirect($this->url->link('seller/catalog-seller', '', 'SSL'));
			return;
		}

		$seller_id = $this->request->get['seller_id'];

		//Seller Gallery Images
		$this->data['gallery_status'] = $this->config->get('msconf_allow_seller_image_gallery');
		$seller_gallery = $this->MsLoader->MsSeller->getGalleryImgs($seller_id);

		$images_list = $this->data['slider_images'] = [];

		foreach ($seller_gallery as $image) {
			
			$images_list[]     = $this->MsLoader->MsFile->resizeImage($image['image'], 1100, 400);
		}
		
		$videos_list = array();
		$videos_list = $this->MsLoader->MsSeller->getSellerVideos($seller_id);
		// var_dump($videos_list);
		// die;
		$this->data['slider_images'] = $images_list;
		$this->data['slider_videos'] = $videos_list;
		//////////////////////////

		$this->data['language_id'] = $this->config->get('config_language_id');

		//Seller Reviews
		$this->data['review_status'] = $this->config->get('msconf_allow_seller_review');
		$this->data['review_seller_status'] = $this->config->get('review_seller_status');

        $this->data['reviews_count']        = (int)$seller['reviews'];
        $this->data['rating']               = (int)$seller['rating'];
        $this->data['seller_id']            = $seller_id;
        ///////////////////////

		$this->document->addScript('expandish/view/javascript/multimerch/dialog-sellercontact.js');

		if ($seller['ms.avatar']) {
			$image = $this->MsLoader->MsFile->resizeImage($seller['ms.avatar'], $this->config->get('msconf_seller_avatar_seller_profile_image_width'), $this->config->get('msconf_seller_avatar_seller_profile_image_height'));
		} else {
			$image = $this->MsLoader->MsFile->resizeImage('ms_no_image.jpg', $this->config->get('msconf_seller_avatar_seller_profile_image_width'), $this->config->get('msconf_seller_avatar_seller_profile_image_height'));
		}

        $this->data['custom_configs'] = [];
        if (
            is_array($this->config->get('msconf_seller_data_custom')) &&
            count($this->config->get('msconf_seller_data_custom')) > 0
        ) {
            $sellerCustomConfig = unserialize($seller['ms.custom_fields']);
            foreach ($this->config->get('msconf_seller_data_custom') as $key => $customData) {
                if (isset($sellerCustomConfig[$key])) {
                    $customData['value'] = $sellerCustomConfig[$key];
                    $this->data['custom_configs'][] = $customData;
                }
            }
        }

		$this->data['seller']['nickname'] = $seller['ms.nickname'];
		$this->data['seller']['seller_id'] = $seller['seller_id'];
		$this->data['seller']['description'] = html_entity_decode($seller['ms.description'], ENT_QUOTES, 'UTF-8');
		$this->data['seller']['thumb'] = $image;
		$this->data['seller']['href'] = $this->url->link('seller/catalog-seller/products', 'seller_id=' . $seller['seller_id']);

		
		$country = $this->model_localisation_country->getCountry($seller['ms.country_id']);
		
		if (!empty($country)) {			
			$this->data['seller']['country'] = $country['name'];
		} else {
			$this->data['seller']['country'] = NULL;
		}
		
		if (!empty($seller['ms.company'])) {
			$this->data['seller']['company'] = $seller['ms.company'];
		} else {
			$this->data['seller']['company'] = NULL;
		}

		if (!empty($seller['c.email'])) {
			$this->data['seller']['email'] = $seller['c.email'];
		} else {
			$this->data['seller']['email'] = NULL;
		}

		if (!empty($seller['ms.mobile'])) {
			$this->data['seller']['mobile'] = $seller['ms.mobile'];
		} else {
			$this->data['seller']['mobile'] = NULL;
		}

		if (!empty($seller['ms.website'])) {
			$this->data['seller']['website'] = $seller['ms.website'];
		} else {
			$this->data['seller']['website'] = NULL;
		}
		
		$this->data['seller']['total_sales'] = $this->MsLoader->MsSeller->getSalesForSeller($seller['seller_id']);
		$this->data['seller']['total_products'] = $this->MsLoader->MsProduct->getTotalProducts(array(
			'seller_id' => $seller['seller_id'],
			'product_status' => array(MsProduct::STATUS_ACTIVE)
		));
				
		$products = $this->MsLoader->MsProduct->getProducts(
			array(
				'seller_id' => $seller['seller_id'],
				'language_id' => $this->config->get('config_language_id'),
				'product_status' => array(MsProduct::STATUS_ACTIVE)
			),
			array(
				'order_by'	=> 'pd.name',
				'order_way'	=> 'ASC',
				'offset'	=> 0,
				'limit'		=> 5
			)
		);

		$lang_id = $this->config->get('config_language_id');
		
		if (!empty($products)) {
			//Get config once
			$show_attr   = $this->config->get('config_show_attribute');

			//Login Display Prices
			$config_customer_price = $this->config->get('config_customer_price');

			foreach ($products as $product) {

				//Check show attribute status
                $show_attribute = false;
                if($show_attr){
                    $product_attribute = $this->db->query("SELECT pa.text , ad.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (pa.attribute_id = ad.attribute_id AND ad.language_id ='" . (int)$lang_id ."')  WHERE pa.product_id = '" . (int)$product['product_id'] . "' AND pa.attribute_id = '" . (int)$show_attr . "' AND pa.language_id = '" . (int)$lang_id ."' limit 1");
                    
                    if($product_attribute->row){
                       $show_attribute = $product_attribute->row['name'].': '.$product_attribute->row['text'];  
                   }
                }
                ///

				$product_data = $this->model_catalog_product->getProduct($product['product_id']);

				if ( ! $product_data ) continue;

				if ($product_data['image']) {
					$image = $this->MsLoader->MsFile->resizeImage($product_data['image'], $this->config->get('msconf_product_seller_profile_image_width'), $this->config->get('msconf_product_seller_profile_image_height'));
				} else {
					$image = $this->MsLoader->MsFile->resizeImage('no_image.jpg', $this->config->get('msconf_product_seller_profile_image_width'), $this->config->get('msconf_product_seller_profile_image_height'));
				}

				$price = false;
				if ($this->isCustomerAllowedToViewPrice) {
					$price = $this->currency->format($this->tax->calculate($product_data['price'], $product_data['tax_class_id'], $this->config->get('config_tax')));
				}
				
				$special = false;	
				if ((float)$product_data['special'] && ($this->isCustomerAllowedToViewPrice)) {
					$special = $this->currency->format($this->tax->calculate($product_data['special'], $product_data['tax_class_id'], $this->config->get('config_tax')));
				}
				
				if ($this->config->get('config_review_status')) {
					$rating = $product_data['rating'];
				} else {
					$rating = false;
				}
				///check permissions to view Add to Cart Button
				$this->data['viewAddToCart'] = true;
				$hidCartConfig = $this->config->get('config_hide_add_to_cart');
				if(($product_data['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart)
				{
					$this->data['viewAddToCart'] = false;
				}		
							
				$this->data['seller']['products'][] = array(
					'product_id' => $product['product_id'],
					'image' => $product_data['image'],
					'thumb' => $image,
					'name' => $product_data['name'],
					'price' => $price,
					'special' => $special,
					'rating' => $rating,
					'reviews'    => sprintf($this->language->get('text_reviews'), (int)$product_data['reviews']),
					'href'    	 => $this->url->link('product/product', 'product_id=' . $product_data['product_id']),
                    'attribute' => $show_attribute,						
				);				
			}
		} else {
			$this->data['seller']['products'] = NULL;
		}


		$this->data['seller_id'] = $this->request->get['seller_id'];

		$this->data['ms_catalog_seller_profile_view'] = sprintf($this->language->get('ms_catalog_seller_profile_view'), $this->data['seller']['nickname']);
		$this->document->setTitle(sprintf($this->language->get('ms_catalog_seller_profile_heading'), $this->data['seller']['nickname']));
		
		$lang_id = $this->config->get('config_language_id');
		$seller_title = $sellerCustomTitles = $this->config->get('msconf_seller_title');
		$sellerCustomTitles = $sellerCustomTitles[$lang_id];
		
		$productCustomTitles = $this->config->get('msconf_product_title');
		$productCustomTitles = $productCustomTitles[$lang_id];
		$productTitles = [
			'single' => $this->language->get('ms_product_title'),
			'multi' => $this->language->get('ms_products_title'),
		];

		$sellerTitles = [
			'single' => $this->language->get('ms_seller_title'),
			'multi' => $this->language->get('ms_sellers_title'),
		];

		if (isset($productCustomTitles['single']) && strlen($productCustomTitles['single']) > 0) {
			$productTitles['single'] = $productCustomTitles['single'];
		}

		if (isset($productCustomTitles['multi']) && strlen($productCustomTitles['multi']) > 0) {
			$productTitles['multi'] = $productCustomTitles['multi'];
		}

		if (isset($sellerCustomTitles['single']) && strlen($sellerCustomTitles['single']) > 0) {
			$sellerTitles['single'] = $sellerCustomTitles['single'];
		}

		if (isset($sellerCustomTitles['multi']) && strlen($sellerCustomTitles['multi']) > 0) {
			$sellerTitles['multi'] = $productCustomTitles['multi'];
		}

		// This is custom work around because that this string has multiple
		// replacement and using the sprintf will cause some bugs between
		// languages directions ( rtl and ltr )
		$this->data['ms_catalog_seller_profile_view_x'] = str_replace(
			['%sellername', '%product'],
			[$this->data['seller']['nickname'],
			$productTitles['multi']],
			$this->language->get('ms_catalog_seller_profile_view_x')
		);

		$this->data['ms_catalog_seller_profile_totalproducts_x'] = sprintf(
			$this->language->get('ms_catalog_seller_profile_totalproducts_x'),
			$productTitles['multi']
		);

		$this->data['ms_catalog_seller_products_empty_x'] = sprintf(
			$this->language->get('ms_catalog_seller_products_empty_x'),
			$productTitles['multi']
		);

        //Manage show totlas
        $this->data['sellers_totals'] = $this->config->get('msconf_sellers_totals');
        ///////////////////
		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => ($seller_title && $seller_title[$lang_id]['multi']) ? $seller_title[$lang_id]['multi'] : $this->language->get('ms_catalog_sellers'),
				'href' => $this->url->link('seller/catalog-seller', '', 'SSL'), 
			),
			array(
				'text' => sprintf($this->language->get('ms_catalog_seller_profile_breadcrumbs'), $this->data['seller']['nickname']),
				'href' => $this->url->link('seller/catalog-seller/profile', '&seller_id='.$seller['seller_id'], 'SSL'),
			)
		));
		
		$this->data['renderEmailDialog']=$this->url->link('seller/catalog-seller/jxRenderContactDialog','&seller_id='.$seller['seller_id'],'SSL');
		
		//$this->children=$this->jxRenderContactDialog();
		$this->data['ms_sellercontact_sendmessage'] = sprintf($this->language->get('ms_sellercontact_sendmessage'), $seller['ms.nickname']);

		list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('catalog-seller-profile');
		$this->response->setOutput($this->render_ecwig());
	}

	public function products() {
		if (isset($this->request->get['seller_id'])) {
			$seller = $this->MsLoader->MsSeller->getSeller($this->request->get['seller_id']);
		}
			
		if (!isset($seller) || empty($seller) || $seller['ms.seller_status'] != MsSeller::STATUS_ACTIVE) {
			$this->redirect($this->url->link('seller/catalog-seller', '', 'SSL'));
			return;
		}

		$this->document->addScript('expandish/view/javascript/jquery/jquery.total-storage.min.js');
		
		/* seller info part */	
		if ($seller['ms.avatar']) {
			$image = $this->MsLoader->MsFile->resizeImage($seller['ms.avatar'], $this->config->get('msconf_product_seller_products_image_width'), $this->config->get('msconf_product_seller_products_image_height'));
		} else {
			$image = $this->MsLoader->MsFile->resizeImage('ms_no_image.jpg', $this->config->get('msconf_product_seller_products_image_width'), $this->config->get('msconf_product_seller_products_image_height'));
		}
		
		$this->data['seller']['nickname'] = $seller['ms.nickname'];
		$this->data['seller']['description'] = html_entity_decode($seller['ms.description'], ENT_QUOTES, 'UTF-8');
        $this->data['seller']['image'] = $seller['ms.avatar'];
		$this->data['seller']['thumb'] = $image;
		$this->data['seller']['href'] = $this->url->link('seller/catalog-seller/profile', 'seller_id=' . $seller['seller_id']);
		
		$country = $this->model_localisation_country->getCountry($seller['ms.country_id']);
		
		if (!empty($country)) {			
			$this->data['seller']['country'] = $country['name'];
		} else {
			$this->data['seller']['country'] = NULL;
		}
		
		if (!empty($seller['ms.company'])) {
			$this->data['seller']['company'] = $seller['ms.company'];
		} else {
			$this->data['seller']['company'] = NULL;
		}
		
		if (!empty($seller['ms.website'])) {
			$this->data['seller']['website'] = $seller['ms.website'];
		} else {
			$this->data['seller']['website'] = NULL;
		}

		if (!empty($seller['c.email'])) {
			$this->data['seller']['email'] = $seller['c.email'];
		} else {
			$this->data['seller']['email'] = NULL;
		}

		if (!empty($seller['ms.mobile'])) {
			$this->data['seller']['mobile'] = $seller['ms.mobile'];
		} else {
			$this->data['seller']['mobile'] = NULL;
		}
		
		$this->data['seller']['total_sales'] = $this->MsLoader->MsSeller->getSalesForSeller($seller['seller_id']);
		$this->data['seller']['total_products'] = $this->MsLoader->MsProduct->getTotalProducts(array(
			'seller_id' => $seller['seller_id'],
			'product_status' => array(MsProduct::STATUS_ACTIVE)
		));
	
		/* seller products part */
		$this->data['text_display'] = $this->language->get('text_display');
		$this->data['text_list'] = $this->language->get('text_list');
		$this->data['text_grid'] = $this->language->get('text_grid');
		$this->data['text_sort'] = $this->language->get('text_sort');
		$this->data['text_limit'] = $this->language->get('text_limit');
		
		$available_sorts = array('pd.name-ASC', 'pd.name-DESC', 'ms.country_id-ASC', 'ms.country_id-DESC', 'pd.name', 'ms.country_id');
		if (isset($this->request->get['sort'])) {
			$order_by = $this->request->get['sort'];
			if (!in_array($order_by, $available_sorts)) {
				$order_by = 'pd.name';
			}
		} else {
			$order_by = 'pd.name';
		}

		if (isset($this->request->get['order'])) {
			$order_way = $this->request->get['order'];
		} else {
			$order_way = 'ASC';
		}
		
		$page = isset($this->request->get['page']) ? $this->request->get['page'] : 1;	
							
		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_catalog_limit');
		}
		
		$this->data['products'] = array();
		
		$sort = array(
			//'filter_category_id' => $category_id, 
			'order_by'               => $order_by,
			'order_way'              => $order_way,
			'offset'              => ($page - 1) * $limit,
			'limit'              => $limit
		);
		
		$total_products = $this->MsLoader->MsProduct->getTotalProducts(array(
			'seller_id' => $seller['seller_id'],
			'product_status' => array(MsProduct::STATUS_ACTIVE)
		));
		
		$products = $this->MsLoader->MsProduct->getProducts(
			array(
				'seller_id' => $seller['seller_id'],
				'product_status' => array(MsProduct::STATUS_ACTIVE)
			),
			$sort
		);
		
		if (!empty($products)) {

			//Get config once
			$show_attr   = $this->config->get('config_show_attribute');
	        $lang_id = $this->config->get('config_language_id');
	        
	        //Login Display Prices
			$config_customer_price = $this->config->get('config_customer_price');

			foreach ($products as $product) {

				//Check show attribute status
                $show_attribute = false;
                if($show_attr){
                    $product_attribute = $this->db->query("SELECT pa.text , ad.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (pa.attribute_id = ad.attribute_id AND ad.language_id ='" . (int)$lang_id ."')  WHERE pa.product_id = '" . (int)$product['product_id'] . "' AND pa.attribute_id = '" . (int)$show_attr . "' AND pa.language_id = '" . (int)$lang_id ."' limit 1");
                    
                    if($product_attribute->row){
                       $show_attribute = $product_attribute->row['name'].': '.$product_attribute->row['text'];  
                   }
                }
                ///
                
				$product_data = $this->model_catalog_product->getProduct($product['product_id']);

				if ( ! $product_data ) continue;

				if ($product_data['image']) {
					$image = $this->MsLoader->MsFile->resizeImage($product_data['image'], $this->config->get('msconf_product_seller_products_image_width'), $this->config->get('msconf_product_seller_products_image_height'));
				} else {
					$image = $this->MsLoader->MsFile->resizeImage('no_image.jpg', $this->config->get('msconf_product_seller_products_image_width'), $this->config->get('msconf_product_seller_products_image_height'));
				}

				$price = false;
				if ($this->isCustomerAllowedToViewPrice) {
					$price = $this->currency->format($this->tax->calculate($product_data['price'], $product_data['tax_class_id'], $this->config->get('config_tax')));
				}
				
				$special = false;	
				if ((float)$product_data['special'] && ($this->isCustomerAllowedToViewPrice)) {
					$special = $this->currency->format($this->tax->calculate($product_data['special'], $product_data['tax_class_id'], $this->config->get('config_tax')));
				}
				
				if ($this->config->get('config_review_status')) {
					$rating = $product_data['rating'];
				} else {
					$rating = false;
				}
				
				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$product_data['special'] ? $product_data['special'] : $product_data['price']);
				} else {
					$tax = false;
				}
				 	///check permissions to view Add to Cart Button
				$this->data['viewAddToCart'] = true;
				$hidCartConfig = $this->config->get('config_hide_add_to_cart');
				if(($product_data['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart)
				{
					$this->data['viewAddToCart'] = false;
				}	

							
				$this->data['seller']['products'][] = array(
					'product_id' => $product['product_id'],
					'image' => $product_data['image'],
					'thumb' => $image,
					'name' => $product_data['name'],
					'price' => $price,
					'tax' => $tax,
					'special' => $special,
					'rating' => $rating,
					'description' => utf8_substr(strip_tags(html_entity_decode($product_data['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',					
					'reviews'    => sprintf($this->language->get('text_reviews'), (int)$product_data['reviews']),
					'href'    	 => $this->url->link('product/product', 'product_id=' . $product_data['product_id']),
                    'attribute' => $show_attribute,												
				);				
			}
		} else {
			$this->data['seller']['products'] = NULL;
		}
		
		$url = '';

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}
						
		$this->data['sorts'] = array();
		
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('ms_sort_nickname_asc'),
			'value' => 'pd.name-ASC',
			'href'  => $this->url->link('seller/catalog-seller/products', '&sort=pd.name&order=ASC&seller_id=' . $seller['seller_id'] . $url)
		);

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('ms_sort_nickname_desc'),
			'value' => 'pd.name-DESC',
			'href'  => $this->url->link('seller/catalog-seller/products', '&sort=pd.name&order=DESC&seller_id=' . $seller['seller_id'] . $url)
		);

		/*
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('ms_sort_country_asc'),
			'value' => 'ms.country_id-ASC',
			'href'  => $this->url->link('seller/catalog-seller/products', '&sort=ms.country_id&order=ASC' . $url)
		); 

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('ms_sort_country_desc'),
			'value' => 'ms.country_id-DESC',
			'href'  => $this->url->link('seller/catalog-seller/products', '&sort=ms.country_id&order=DESC' . $url)
		); 
		*/
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}	

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		
		$this->data['limits'] = array();
		
		$this->data['limits'][] = array(
			'text'  => $this->config->get('config_catalog_limit'),
			'value' => $this->config->get('config_catalog_limit'),
			'href'  => $this->url->link('seller/catalog-seller/products', $url . '&limit=' . $this->config->get('config_catalog_limit') . '&seller_id=' . $seller['seller_id'])
		);
					
		$this->data['limits'][] = array(
			'text'  => 25,
			'value' => 25,
			'href'  => $this->url->link('seller/catalog-seller/products', $url . '&limit=25&seller_id=' . $seller['seller_id'])
		);
		
		$this->data['limits'][] = array(
			'text'  => 50,
			'value' => 50,
			'href'  => $this->url->link('seller/catalog-seller/products', $url . '&limit=50&seller_id=' . $seller['seller_id'])
		);

		$this->data['limits'][] = array(
			'text'  => 75,
			'value' => 75,
			'href'  => $this->url->link('seller/catalog-seller/products', $url . '&limit=75&seller_id=' . $seller['seller_id'])
		);
		
		$this->data['limits'][] = array(
			'text'  => 100,
			'value' => 100,
			'href'  => $this->url->link('seller/catalog-seller/products', $url . '&limit=100&seller_id=' . $seller['seller_id'])
		);
		
		$url = '';

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
		$pagination->total = $total_products;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('seller/catalog-seller/products', $url . '&page={page}&seller_id=' . $seller['seller_id']);
	
		$this->data['pagination'] = $pagination->render();
		
		$this->data['sort'] = $order_by;
		$this->data['order'] = $order_way;
		$this->data['limit'] = $limit;		
		
		$this->data['ms_catalog_seller_products'] = sprintf($this->language->get('ms_catalog_seller_products_heading'), $seller['ms.nickname']);
		$this->document->setTitle(sprintf($this->language->get('ms_catalog_seller_products_heading'), $seller['ms.nickname']));

        //Manage show totlas
        $this->data['sellers_totals'] = $this->config->get('msconf_sellers_totals');
        ///////////////////

        $lang_id = $this->config->get('config_language_id');
		
		$seller_title = $sellerCustomTitles = $this->config->get('msconf_seller_title');
		$sellerCustomTitles = $sellerCustomTitles[$lang_id];
		
		$productCustomTitles = $this->config->get('msconf_product_title');
		$productCustomTitles = $productCustomTitles[$lang_id];
		$productTitles = [
			'single' => $this->language->get('ms_product_title'),
			'multi' => $this->language->get('ms_products_title'),
		];

		$sellerTitles = [
			'single' => $this->language->get('ms_seller_title'),
			'multi' => $this->language->get('ms_sellers_title'),
		];

		if (isset($productCustomTitles['single']) && strlen($productCustomTitles['single']) > 0) {
			$productTitles['single'] = $productCustomTitles['single'];
		}

		if (isset($productCustomTitles['multi']) && strlen($productCustomTitles['multi']) > 0) {
			$productTitles['multi'] = $productCustomTitles['multi'];
		}

		if (isset($sellerCustomTitles['single']) && strlen($sellerCustomTitles['single']) > 0) {
			$sellerTitles['single'] = $sellerCustomTitles['single'];
		}

		if (isset($sellerCustomTitles['multi']) && strlen($sellerCustomTitles['multi']) > 0) {
			$sellerTitles['multi'] = $productCustomTitles['multi'];
		}

		$this->data['ms_catalog_seller_profile_about_seller_x'] = sprintf(
			$this->language->get('ms_catalog_seller_profile_about_seller_x'),
			$sellerTitles['single']
		);

		$this->data['ms_catalog_seller_profile_totalproducts_x'] = sprintf(
			$this->language->get('ms_catalog_seller_profile_totalproducts_x'),
			$productTitles['multi']
		);

		$this->data['ms_catalog_seller_products_empty_x']=sprintf($this->language->get('ms_catalog_seller_products_empty_x'),$productTitles['multi']);

		if($seller_title && $seller_title[$lang_id]['multi']){
			$this->document->setTitle($seller_title[$lang_id]['multi']);
			$this->data['ms_catalog_sellers_heading'] = $seller_title[$lang_id]['multi'];
		}else{
			$this->document->setTitle($this->language->get('ms_catalog_sellers_heading'));
			$this->data['ms_catalog_sellers_heading'] = $this->language->get('ms_catalog_sellers_heading');

		}
		///
		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => ($seller_title && $seller_title[$lang_id]['multi']) ? $seller_title[$lang_id]['multi'] : $this->language->get('ms_catalog_sellers'),
				'href' => $this->url->link('seller/catalog-seller', '', 'SSL'), 
			),
			array(
				'text' => sprintf($this->language->get('ms_catalog_seller_products_breadcrumbs'), $seller['ms.nickname']),
				'href' => $this->url->link('seller/catalog-seller/profile', '&seller_id='.$seller['seller_id'], 'SSL'),
			)
		));

		list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('catalog-seller-products');
		$this->response->setOutput($this->render_ecwig());
	}

	public function write() {
		$this->language->load_json('multiseller/multiseller');
		
		$this->load->model('catalog/review');
		
		$json = array();
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}
			
			if ((utf8_strlen($this->request->post['text']) < 3) || (utf8_strlen($this->request->post['text']) > 1000)) {
				$json['error'] = $this->language->get('error_text');
			}
	
			if (empty($this->request->post['rating'])) {
				$json['error'] = $this->language->get('error_rating');
			}
	
			if (empty($this->session->data['recaptcha']) || ($this->session->data['recaptcha'] != $this->request->post['captcha'])) {
				$json['error'] = $this->language->get('error_captcha');
			}
				
			if (!isset($json['error'])) {
				$this->model_catalog_review->addSellerReview($this->request->get['seller_id'], $this->request->post);
				$json['success'] = $this->language->get('text_success');
			}
		}
		
		$this->response->setOutput(json_encode($json));
	}

	public function review() {
    	$this->language->load_json('multiseller/multiseller');
		
		$this->load->model('catalog/review');

		//$this->data['text_no_reviews'] = $this->language->get('text_no_reviews');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}  
		
		$this->data['reviews'] = array();
		
		$review_total = $this->model_catalog_review->getTotalReviewsBySeller($this->request->get['seller_id']);
		$results = $this->model_catalog_review->getReviewsBySeller($this->request->get['seller_id'], ($page - 1) * 5, 5);
      		
		foreach ($results as $result) {
        	$this->data['reviews'][] = array(
        		'author'     => $result['author'],
				'text'       => $result['text'],
				'rating'     => (int)$result['rating'],
        		'reviews'    => sprintf($this->language->get('text_reviews'), (int)$review_total),
        		'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
        	);
      	}			
			
		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5; 
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('seller/catalog-seller/review', 'seller_id=' . $this->request->get['seller_id'] . '&page={page}');
			
		$this->data['pagination'] = $pagination->render();
		
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/product/review.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/product/review.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/product/review.expand';
        }
		
		$this->response->setOutput($this->render_ecwig());
	}

	public function jxSubmitContactDialog() {
		if ($this->config->get('msconf_enable_private_messaging') == 1) {
			return $this->_submitEmailDialog();
		} else {
			return false;
		}
	}
	
	private function _submitEmailDialog() {
		$seller_id = $this->request->post['seller_id'];
		$product_id = $this->request->post['product_id'];
		$seller_email = $this->MsLoader->MsSeller->getSellerEmail($seller_id);
		$seller_name = $this->MsLoader->MsSeller->getSellerName($seller_id);
		$message_text = trim($this->request->post['ms-sellercontact-text']);
		$customer_name = mb_substr(trim($this->request->post['ms-sellercontact-name']), 0, 50);
		$customer_email = $this->request->post['ms-sellercontact-email'];
		$mail_type = ($product_id) ? MsMail::SMT_SELLER_ORDER :  MsMail::SMT_SELLER_CONTACT;
		$json = array();

		if (empty($message_text) || empty($customer_name) || empty($customer_email) || empty($this->request->post['ms-sellercontact-captcha'])) {
			$json['errors'][] = $this->language->get('ms_error_contact_allfields');
			$this->response->setOutput(json_encode($json));
			return;
		}

		if (!isset($this->session->data['recaptcha']) || ($this->session->data['recaptcha'] != $this->request->post['ms-sellercontact-captcha'])) {
			$json['errors'][] = $this->language->get('ms_error_contact_captcha');
		}

		if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
			$json['errors'][] = $this->language->get('ms_error_contact_email');			
		}

		if (mb_strlen($message_text) > 2000) {
			$json['errors'][] = $this->language->get('ms_error_contact_text');
		}

		if (!isset($json['errors'])) {	
			$mails[] = array(
				'type' => $mail_type,
				'data' => array(
					'recipients' => $seller_email,
					'customer_name' => $customer_name,
					'customer_email' => $customer_email,
					'customer_message' => $message_text,
					'product_id' => $product_id,
					'addressee' => $seller_name
				)
			);			
			$this->MsLoader->MsMail->sendMails($mails);
			$json['success'] = $this->language->get('ms_sellercontact_success');
		}
		$this->response->setOutput(json_encode($json));
	}

	public function jxRenderContactDialog() {
		if ($this->config->get('msconf_enable_private_messaging') == 1) {
			return $this->_renderEmailDialog();
		} else {
			return false;
		}
	}
	
	private function _renderEmailDialog() {
		if (isset($this->request->get['product_id'])) {
			$seller_id = $this->MsLoader->MsProduct->getSellerId($this->request->get['product_id']);
			$this->data['product_id'] = (int)$this->request->get['product_id'];
		} else {
			$seller_id = $this->request->get['seller_id'];
			$this->data['product_id'] = 0;
		}
		$seller = $this->MsLoader->MsSeller->getSeller($seller_id);
		
		if (empty($seller))
			return false;

		$this->data['seller_id'] = $seller_id;
		$this->data['ms_sellercontact_title']=sprintf($this->language->get('ms_sellercontact_sendmessage'), $seller['ms.nickname']); 
		$this->data['msconf_enable_private_messaging']=$this->config->get('msconf_enable_private_messaging');
		
		$this->data['customer_email'] = $this->customer->getEmail();
		$this->data['customer_name'] = $this->customer->getFirstname() . ' ' . $this->customer->getLastname();
		
		if (!empty($seller['ms.avatar'])) {
			$this->data['seller_thumb'] = $this->MsLoader->MsFile->resizeImage($seller['ms.avatar'], $this->config->get('msconf_seller_avatar_product_page_image_width'), $this->config->get('msconf_seller_avatar_product_page_image_height'));
		} else {
			$this->data['seller_thumb'] = $this->MsLoader->MsFile->resizeImage('ms_no_image.jpg', $this->config->get('msconf_seller_avatar_product_page_image_width'), $this->config->get('msconf_seller_avatar_product_page_image_height'));
		}
			
		$this->data['seller_href'] = $this->url->link('seller/catalog-seller/profile', 'seller_id=' . $seller['seller_id']);
		$this->data['ms_sellercontact_sendmessage'] = sprintf($this->language->get('ms_sellercontact_sendmessage'), $seller['ms.nickname']);
		
		list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('dialog-sellercontact');
		return $this->response->setOutput($this->render_ecwig());
	}
}
?>
