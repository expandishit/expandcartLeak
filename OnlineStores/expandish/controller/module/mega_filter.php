<?php  
class ControllerModuleMegaFilter extends Controller {
	
	private function _keysByAttribs( $attributes ) {
		$keys = array();
		
		foreach( $attributes as $key => $attribute ) {
			$keys[$attribute['seo_name']] = $key;
		}
		
		return $keys;
	}
	
	private function _setCache( $name, $value ) {
		if( ! is_dir( DIR_CACHE . 'cache_mfp' ) ) {
			@mkdir(DIR_CACHE . 'cache_mfp/');
		}

		if(! is_writable( DIR_CACHE . 'cache_mfp')) return false;

		file_put_contents( DIR_CACHE . 'cache_mfp/' . $name, serialize( $value ) );
		file_put_contents( DIR_CACHE . 'cache_mfp/' . $name . '.time', time() + 60 * 60 * 24 );
		
		return true;
	}
	
	private function _getCache( $name ) {
		$dir		= DIR_CACHE . 'cache_mfp/';
		$file		= $dir . $name;
		$file_time	= $file . '.time';
		
		if( ! file_exists( $file ) ) {
			return NULL;
		}
		
		if( ! file_exists( $file_time ) ) {
			return NULL;
		}
		
		$time = (float) file_get_contents( $file_time );
		
		if( $time < time() ) {
			@ unlink( $file );
			@ unlink( $file_time );
			
			return false;
		}
		
		return unserialize( file_get_contents( $file ) );
	}
	
	protected function index( $setting ) {
		if( empty( $setting['base_attribs'] ) )
			$setting['base_attribs'] = array();
		
		if( empty( $setting['attribs'] ) )
			$setting['attribs'] = array();
		
		if( empty( $setting['options'] ) )
			$setting['options'] = array();
		
		if( empty( $setting['filters'] ) )
			$setting['filters'] = array();




		/**
		 * Ustawienia
		 */
		$settings	= $this->config->get('mega_filter_settings');
		/**
		 * Sprawdź szablon
		 */
		if( isset( $setting['layout_id'] ) && is_array( $setting['layout_id'] ) ) {
			/**
			 * Sprawdź czy zdefiniowano kategorię 
			 */
			if( in_array( $settings['layout_c'], $setting['layout_id'] ) && isset( $this->request->get['path'] ) ) {				
				/**
				* Pokaż w kategoriach 
				*/
				if( ! empty( $setting['category_id'] ) ) {
					$categories		= explode( '_', $this->request->get['path'] );
					
					if( ! empty( $setting['category_id_with_childs'] ) ) {
						$is = false;
						$category_id = end( $categories );
						
						foreach( $this->db->query( "SELECT * FROM `" . DB_PREFIX . "category_path` WHERE `category_id`='" . $category_id . "'" )->rows as $row ) {
							if( isset( $row['path'] ) ) {
								$categories[] = $row['path'];
							} else if( isset( $row['path_id'] ) ) {
								$categories[] = $row['path_id'];
							}
						}
						
						foreach( $categories as $category_id ) {
							if( in_array( $category_id, $setting['category_id'] ) ) {
								$is = true; break;
							}
						}
						
						if( ! $is )
							return;
					} else {
						$category_id	= end( $categories );
						
						if( ! in_array( $category_id, $setting['category_id'] ) )
							return false;
					}
				}
				
				/**
				 * Ukryj w kategoriach 
				 */
				if( ! empty( $setting['hide_category_id'] ) ) {
					$categories		= explode( '_', $this->request->get['path'] );
					
					if( ! empty( $setting['hide_category_id_with_childs'] ) ) {						
						foreach( $categories as $category_id ) {
							if( in_array( $category_id, $setting['hide_category_id'] ) ) {
								return;
							}
						}
					} else {
						$category_id	= array_pop( $categories );

						if( in_array( $category_id, $setting['hide_category_id'] ) ) {
							return;
						}
					}
				}
			}
		}
		
		/**
		 * Sprawdź sklep 
		 */
		if( isset( $setting['store_id'] ) && is_array( $setting['store_id'] ) && ! in_array( $this->config->get('config_store_id'), $setting['store_id'] ) ) {
			return;
		}
		
		/**
		 * Sprawdź grupę
		 */
		if( ! empty( $setting['customer_groups'] ) ) {
			$customer_group_id = $this->customer->isLogged() ? $this->customer->getCustomerGroupId() : $this->config->get( 'config_customer_group_id' );
			
			if( ! in_array( $customer_group_id, $setting['customer_groups'] ) ) {
				return;
			}
		}
		
		/**
		 * Załaduj język 
		 */
		$this->data = array_merge($this->data, $this->language->load_json('module/mega_filter'));
		
		/**
		 * Ustaw tytuł 
		 */
		if( isset( $setting['title'][$this->config->get('config_language_id')] ) ) {
			$this->data['heading_title'] = $setting['title'][$this->config->get('config_language_id')];
		}
		
		/**
		 * Załaduj modele 
		 */
		$this->load->model('module/mega_filter');
		//$t=microtime(true);
		$core = MegaFilterCore::newInstance( $this, NULL );
		$cache = NULL;
		
		if( ! empty( $settings['cache_enabled'] ) ) {
			$cache = 'idx.' . $setting['_idx'] . '.' . $core->cacheName();
		}
		/**
		 * Lista atrybutów 
		 */
		if( ! $cache || NULL == ( $attributes = $this->_getCache( $cache ) ) ) {
			$attributes	= $this->model_module_mega_filter->getAttributes( 
				$core,
				$setting['_idx'],
				$setting['base_attribs'], 
				$setting['attribs'], 
				$setting['options'], 
				$setting['filters'],
				empty( $setting['categories'] ) ? array() : $setting['categories']
			);
			
			if( ! empty( $settings['cache_enabled'] ) ) {
				$this->_setCache( $cache, $attributes );
			}
		}
		//echo microtime(true)-$t;
		/**
		 * Pobierz klucze wg nazw 
		 */
		$keys		= $this->_keysByAttribs( $attributes );
		
		/**
		 * Aktualna trasa 
		 */
		$route		= isset( $this->request->get['route'] ) ? $this->request->get['route'] : NULL;
		
		/**
		 * Usuń listę branż dla widoku branż 
		 */
		if( in_array( $route, array( 'product/manufacturer', 'product/manufacturer/info' ) ) && isset( $keys['manufacturers'] ) ) {
			unset( $attributes[$keys['manufacturers']] );
		}
		
		if( in_array( $route, array( 'product/search' ) ) && empty( $this->request->get['search'] ) && empty( $this->request->get['tag'] ) && empty( $this->request->get['filter_name'] ) ) {
			$attributes = array();
		}


		 $priceRange = $core->getMinMaxPrice();
		if(isset( $keys['price']) && $priceRange['min'] == 0 && $priceRange['max']== 0 && !isset($this->request->get['mfp'])) {
			unset( $attributes[$keys['price']] );
		}
		
		if( ! $attributes ) {
			return;
		}
		
		$mijo_shop = class_exists( 'MijoShop' ) ? true : false;
		$ace_shop = class_exists( 'AceShop' ) ? true : false;
		$is_mobile = Mobile_Detect_MFP::create()->isMobile();

		if( ( $setting['position'] == 'content_top' || empty($setting['position']) ) && ! empty( $settings['change_top_to_column_on_mobile'] ) && $is_mobile ) {
			$setting['position'] = 'column_left';
			$this->data['hide_container'] = true;
		}
		
		$this->data['ajaxInfoUrl']		= $this->parseUrl( $this->url->link( 'module/mega_filter/ajaxinfo', '', 'SSL' ) );
		$this->data['ajaxResultsUrl']	= $this->parseUrl( $this->url->link( 'module/mega_filter/results', '', 'SSL' ) );
		$this->data['ajaxCategoryUrl']	= $this->parseUrl( $this->url->link( 'module/mega_filter/categories', '', 'SSL' ) );
		
		$this->data['is_mobile']		= $is_mobile;
		$this->data['mijo_shop']		= $mijo_shop;
		$this->data['ace_shop']			= $ace_shop;
		$this->data['filters']			= $attributes;
		$this->data['settings']			= $settings;
		$this->data['params']			= $core->getParseParams();
		$this->data['price']			= $priceRange;
		$this->data['_idx']				= $setting['_idx'];
		$this->data['_route']			= base64_encode( $core->route() );
		$this->data['_routeProduct']	= base64_encode( 'product/product' );
		$this->data['_routeHome']		= base64_encode( 'common/home' );
		$this->data['_routeInformation']= base64_encode( 'information/information' );
		$this->data['_routeManufacturerList']= base64_encode( 'product/manufacturer' );
		$this->data['_position']		= $setting['position'];
		$this->data['_displayOptionsAs']=
			$setting['position'] == 'content_top' && 
			! empty( $setting['display_options_as'] ) ? $setting['display_options_as'] : false;
		$this->data['smp']				= array(
			'isInstalled'			=> $this->config->get( 'smp_is_install' ),
			'disableConvertUrls'	=> $this->config->get( 'smp_disable_convert_urls' )
		);
		
		$this->data['_v'] = $this->config->get('mfilter_version') ? $this->config->get('mfilter_version') : '1';

		$lang = $this->config->get('config_language');

		if( $mijo_shop ) {
			MijoShop::getClass('base')->addHeader(JPATH_MIJOSHOP_OC . '/expandish/view/javascript/mf/iscroll.js', false);
			MijoShop::getClass('base')->addHeader(JPATH_MIJOSHOP_OC . '/expandish/view/javascript/mf/mega_filter.js', false);

			if( file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' .  CURRENT_TEMPLATE . '/stylesheet/mf/style.css') ) {
				MijoShop::get()->addHeader(JPATH_MIJOSHOP_OC.'/expandish/view/theme/customtemplates/' . STORECODE . '/' .  CURRENT_TEMPLATE . '/stylesheet/mf/style.css');
			} else {
				MijoShop::get()->addHeader(JPATH_MIJOSHOP_OC.'/expandish/view/theme/' . CURRENT_TEMPLATE . '/stylesheet/mf/style.css');
			}
			
			if( file_exists(DIR_CACHE . 'view/theme/default/stylesheet/mf/style-2.css') ) {
				MijoShop::get()->addHeader(JPATH_MIJOSHOP_OC.'/system/cache/'.STORECODE.'/view/theme/default/stylesheet/mf/style-2.css');
			} else {
				MijoShop::get()->addHeader(JPATH_MIJOSHOP_OC.'/expandish/view/theme/default/stylesheet/mf/style-2.css');
			}
			
		} else if( $ace_shop ) {
			AceShop::getClass('base')->addHeader(JPATH_ACESHOP_OC . '/expandish/view/javascript/mf/iscroll.js', false);
			AceShop::getClass('base')->addHeader(JPATH_ACESHOP_OC . '/expandish/view/javascript/mf/mega_filter.js', false);

			if( file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' .  CURRENT_TEMPLATE . '/stylesheet/mf/style.css') ) {
				AceShop::get()->addHeader(JPATH_ACESHOP_OC.'/expandish/view/theme/customtemplates/' . STORECODE . '/' .  CURRENT_TEMPLATE . '/stylesheet/mf/style.css');
			} else {
				AceShop::get()->addHeader(JPATH_ACESHOP_OC.'/expandish/view/theme/' . CURRENT_TEMPLATE . '/stylesheet/mf/style.css');
			}

			if( file_exists(DIR_CACHE . 'view/theme/default/stylesheet/mf/style-2.css') ) {
				AceShop::get()->addHeader(JPATH_ACESHOP_OC.'/system/cache/'.STORECODE.'/view/theme/default/stylesheet/mf/style-2.css');
			} else {
				AceShop::get()->addHeader(JPATH_ACESHOP_OC.'/expandish/view/theme/default/stylesheet/mf/style-2.css');
			}
			
		} else {
			$this->document->addScript('expandish/view/javascript/mf/iscroll.js?v'.$this->data['_v']);
			$this->document->addScript('expandish/view/javascript/mf/mega_filter.js?v'.$this->data['_v']);

			$this->document->addExpandishStyle('stylesheet/mf/style.css?v'.$this->data['_v']);
			if($lang === "ar"){
				$this->document->addExpandishStyle('stylesheet/mf/style-RTL.css?v'.$this->data['_v']);
			}

			if( file_exists(DIR_CACHE . 'view/theme/default/stylesheet/mf/style-2.css') ) {
				$this->document->addStyle('system/cache/'.STORECODE.'/view/theme/default/stylesheet/mf/style-2.css');
			} else {
				$this->document->addStyle('expandish/view/theme/default/stylesheet/mf/style-2.css');
			}
		}

        $button_template = '<div class="mfilter-button mfilter-button-%s">%s</div>';
        $button_temp = '<a href="#" class="%s">%s</a>';
        $button_temp_refersh = '<a href="#" class="button">%s</a>';
        $buttons = array( 'top' => array(), 'bottom' => array() );

        if( ! empty( $settings['show_reset_button'] ) ) {
            $buttons['bottom'][] = sprintf( $button_temp, 'mfilter-button-reset', '<i class="mfilter-reset-icon"></i>' . $text_reset_all );
        }

        if( ! empty( $settings['show_top_reset_button'] ) ) {
            $buttons['top'][] = sprintf( $button_temp, 'mfilter-button-reset', '<i class="mfilter-reset-icon"></i>' . $text_reset_all );
        }

        if( ! empty( $settings['refresh_results'] ) && $settings['refresh_results'] == 'using_button' && ! empty( $settings['place_button'] ) ) {
            $place_button = explode( '_', $settings['place_button'] );

            if( in_array( 'top', $place_button ) ) {
                $buttons['top'][] = sprintf( $button_temp_refersh, '<i class="fa fa-refresh" aria-hidden="true"></i>', $text_button_apply );
            }

            if( in_array( 'bottom', $place_button ) ) {
                $buttons['bottom'][] = sprintf( $button_temp_refersh, '<i class="fa fa-refresh" aria-hidden="true"></i>', $text_button_apply );
            }
        }

        foreach( $buttons as $bKey => $bVal ) {
            $buttons[$bKey] = $bVal ? sprintf( $button_template, $bKey, implode( '', $bVal ) ) : '';
        }

        $this->data['buttons'] = $buttons;

        $_p = array();
        foreach( $this->request->get as $k => $v ) {
            if (is_array($v) || !in_array($k, array('path', 'category_id', 'manufacturer_id', 'filter', 'search', 'sub_category', 'description', 'filter_tag')))
                continue;
            $_p[$k] = addslashes( $v );
        }
        $this->data['_p'] = $_p;

        $this->data['currency_symbol_left'] = $this->currency->getSymbolLeft();
        $this->data['currency_symbol_right'] = $this->currency->getSymbolRight();
		/**
		 * Szablon 
		 */
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/mega_filter.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/mega_filter.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/module/mega_filter.expand';
        }
		
		$this->config->set('mfp_is_activated','1');

		$this->render_ecwig();
	}
	
	private function parseUrl( $url ) {
		$scheme		= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
		$host		= isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
		$parse		= parse_url( $url );
		
		return $scheme . '://' . $host . $parse['path'] . ( empty( $parse['query'] ) ? '' : '?' . str_replace( '&amp;', '&', $parse['query'] ) );
	}
	
	public function ajaxinfo() {
		$this->load->model('module/mega_filter');
		
		$idx = 0;
		
		if( isset( $this->request->get['mfilterIdx'] ) )
			$idx = (int) $this->request->get['mfilterIdx'];
		
		$baseTypes = array( 'stock_status', 'manufacturers', 'rating', 'attributes', 'options', 'filters' );
		
		if( isset( $this->request->get['mfilterBTypes'] ) ) {
			$baseTypes = explode( ',', $this->request->get['mfilterBTypes'] );
		}
		
		if( false !== ( $idx2 = array_search( 'categories:tree', $baseTypes ) ) ) {
			unset( $baseTypes[$idx2] );
		}
		
		echo '<div id="mfp-json-encode">' . base64_encode( json_encode( MegaFilterCore::newInstance( $this, NULL )->getJsonData($baseTypes, $idx) ) ) . '</div>';
	}
	
	public function categories() {
		$cats = array();
		
		if( ! empty( $this->request->post['cat_id'] ) ) {
			$this->load->model('catalog/category');
			
			foreach( $this->model_catalog_category->getCategories( $this->request->post['cat_id'] ) as $cat ) {
				$cats[] = array(
					'id' => $cat['category_id'],
					'name' => $cat['name']
				);
			}
		}
		
		echo json_encode( $cats );
	}
	
	public function results() {
    	$this->data = array_merge($this->data, $this->language->load_json('product/search'));
		
		$this->load->model('catalog/category');		
		$this->load->model('catalog/product');		
		$this->load->model('tool/image');

		$isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();
		
		$keys	= array( 'sort' => 'p.sort_order', 'order' => 'ASC', 'page' => 1, 'limit' => $this->config->get('config_catalog_limit') );
		
		$url = '';

        if( ! empty( $this->request->get['mfp'] ) ) {
            $url .= '&mfp=' . $this->request->get['mfp'];
        }
		
		foreach( $keys as $key => $keyDef ) {
			${$key} = isset( $this->request->get[$key] ) ? $this->request->get[$key] : $keyDef;
			
			if( isset( $this->request->get[$key] ) ) {
				$url .= '&' . $key . '=' . $this->request->get[$key];
			}
			
		}
		
		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addScript('expandish/view/javascript/jquery/jquery.total-storage.min.js');

		/**
		 * Breadcrumb 
		 */
		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array( 
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
      		'separator' => false
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/mega_filter/results', $url),
      		'separator' => $this->language->get('text_separator')
   		);
		
		$this->data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
		$this->data['compare'] = $this->url->link('product/compare');
		
		$this->data['products'] = array();
		
		$data = array(
			'sort'                => $sort,
			'order'               => $order,
			'start'               => ($page - 1) * $limit,
			'limit'               => $limit
		);
		
		if( empty( $this->request->get['path'] ) && ! empty( $this->request->get['mfilterPath'] ) ) {
			$this->request->get['path'] = $this->request->get['mfilterPath'];
		}
		
		if( ! empty( $this->request->get['path'] ) ) {
			$data['filter_category_id'] = explode( '_', $this->request->get['path'] );
			$data['filter_category_id'] = end( $data['filter_category_id'] );
		}
					
		$product_total = $this->model_catalog_product->getTotalProducts($data);								
		$results = $this->model_catalog_product->getProducts($data);
		
		$this->data['config_image_width'] = $this->config->get('config_image_product_width') ?? 300;
		$this->data['config_image_height'] = $this->config->get('config_image_product_height') ?? 300;

		//Login Display Prices
		$config_customer_price = $this->config->get('config_customer_price');
		foreach ($results as $result) {
			if ($result['image']) {
				$image = $result['image'];
			} else {
				$image = false;
			}
			
			$price = false;
			if ($isCustomerAllowedToViewPrice) {
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
			}
			
			$special = false;
			if ((float)$result['special'] && $isCustomerAllowedToViewPrice) {
				$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')));
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
			///check permissions to view Add to Cart Button and view products
			$this->data['viewAddToCart'] = true;
			$hidCartConfig = $this->config->get('config_hide_add_to_cart');		
			if(($result['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart )
			{
				$this->data['viewAddToCart'] = false;
			}
			
			$this->data['products'][] = array(
				'product_id'  => $result['product_id'],
				'thumb'       => $image,
				'image'       => $image,
				'name'        => $result['name'],
				'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
				'price'       => $price,
				'special'     => $special,
				'tax'         => $tax,
				'rating'      => $result['rating'],
				'reviews'     => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
				'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'] . $url)
			);
		}
					
		$url = '';
        if( ! empty( $this->request->get['mfp'] ) ) {
            $url .= '&mfp=' . $this->request->get['mfp'];
        }
			
		if( ! empty( $this->request->get['mfp'] ) ) {
			$url .= '&mfp=' . $this->request->get['mfp'];
		}
						
		$this->data['sorts'] = array();
			
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_default'),
			'value' => 'p.sort_order-ASC',
			'href'  => $this->url->link('module/mega_filter/results', 'sort=p.sort_order&order=ASC' . $url)
		);
			
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_name_asc'),
			'value' => 'pd.name-ASC',
			'href'  => $this->url->link('module/mega_filter/results', 'sort=pd.name&order=ASC' . $url)
		); 
	
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_name_desc'),
			'value' => 'pd.name-DESC',
			'href'  => $this->url->link('module/mega_filter/results', 'sort=pd.name&order=DESC' . $url)
		);
	
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_price_asc'),
			'value' => 'p.price-ASC',
			'href'  => $this->url->link('module/mega_filter/results', 'sort=p.price&order=ASC' . $url)
		); 
	
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_price_desc'),
			'value' => 'p.price-DESC',
			'href'  => $this->url->link('module/mega_filter/results', 'sort=p.price&order=DESC' . $url)
		); 
			
		if ($this->config->get('config_review_status')) {
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_desc'),
				'value' => 'rating-DESC',
				'href'  => $this->url->link('module/mega_filter/results', 'sort=rating&order=DESC' . $url)
			); 
				
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_asc'),
				'value' => 'rating-ASC',
				'href'  => $this->url->link('module/mega_filter/results', 'sort=rating&order=ASC' . $url)
			);
		}
			
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_model_asc'),
			'value' => 'p.model-ASC',
			'href'  => $this->url->link('module/mega_filter/results', 'sort=p.model&order=ASC' . $url)
		); 
	
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_model_desc'),
			'value' => 'p.model-DESC',
			'href'  => $this->url->link('module/mega_filter/results', 'sort=p.model&order=DESC' . $url)
		);
	
		$url = '';

        if( ! empty( $this->request->get['mfp'] ) ) {
            $url .= '&mfp=' . $this->request->get['mfp'];
        }
			
		if( ! empty( $this->request->get['mfp'] ) ) {
			$url .= '&mfp=' . $this->request->get['mfp'];
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
				'href'  => $this->url->link('module/mega_filter/results', $url . '&limit=' . $limits)
			);
		}
					
		$url = '';

        if( ! empty( $this->request->get['mfp'] ) ) {
            $url .= '&mfp=' . $this->request->get['mfp'];
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
		$pagination->url = $this->url->link('module/mega_filter/results', $url . '&page={page}');
			
		$this->data['pagination'] = $pagination->render();
				
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
		$this->data['limit'] = $limit;
		
		/**
		 * Szablon 
		 */
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/product/special.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/product/special.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/product/special.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);

        if( isset( $this->request->get['mfilterAjax'] ) ) {
            $settings	= $this->config->get('mega_filter_settings');
            $baseTypes	= array( 'stock_status', 'manufacturers', 'rating', 'attributes', 'price', 'options', 'filters' );

            if( isset( $this->request->get['mfilterBTypes'] ) ) {
                $baseTypes = explode( ',', $this->request->get['mfilterBTypes'] );
            }

            if( ! empty( $settings['calculate_number_of_products'] ) || in_array( 'categories:tree', $baseTypes ) ) {
                if( empty( $settings['calculate_number_of_products'] ) ) {
                    $baseTypes = array( 'categories:tree' );
                }

                $this->load->model( 'module/mega_filter' );

                $idx = 0;

                if( isset( $this->request->get['mfilterIdx'] ) )
                    $idx = (int) $this->request->get['mfilterIdx'];

                $this->data['mfilter_json'] = json_encode( MegaFilterCore::newInstance( $this, NULL )->getJsonData($baseTypes, $idx) );
            }

            foreach( $this->children as $mf_child ) {
                $mf_child = explode( '/', $mf_child );
                $mf_child = array_pop( $mf_child );
                $this->data[$mf_child] = '';
            }

            $this->children=array();
            $this->data['header'] = $this->data['footer'] = '';
        }

        if( ! empty( $this->data['breadcrumbs'] ) && ! empty( $this->request->get['mfp'] ) ) {

            foreach( $this->data['breadcrumbs'] as $mfK => $mfBreadcrumb ) {
                $mfReplace = preg_replace( '/path\[[^\]]+\],?/', '', $this->request->get['mfp'] );
                $mfFind = ( mb_strpos( $mfBreadcrumb['href'], '?mfp=', 0, 'utf-8' ) !== false ? '?mfp=' : '&mfp=' );

                $this->data['breadcrumbs'][$mfK]['href'] = str_replace(array(
                    $mfFind . $this->request->get['mfp'],
                    '&amp;mfp=' . $this->request->get['mfp'],
                    $mfFind . urlencode( $this->request->get['mfp'] ),
                    '&amp;mfp=' . urlencode( $this->request->get['mfp'] )
                ), $mfReplace ? $mfFind . $mfReplace : '', $mfBreadcrumb['href'] );
            }
        }
				
		$this->response->setOutput($this->render_ecwig());
	}
}
?>
