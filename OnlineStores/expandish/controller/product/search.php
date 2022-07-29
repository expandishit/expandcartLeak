<?php
class ControllerProductSearch extends Controller {

	public function index() {
    	$this->language->load_json('product/search');

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');


		if (\Extension::isInstalled('lableb')&& $this->config->get('lableb')['status'])
        {
            $this->data['lableb_search_installed']= true;
			if(isset($this->request->get['search'])){
				$this->request->get['lableb_search'] = $this->request->get['search'];
				unset($this->request->get['search']);
			}
        }

		if (isset($this->request->get['search'])) {
			// $search = $this->request->get['search'];
            $expand_seo = $this->config->get('expand_seo');

            if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
                $routeKey = $this->request->get['_route_'];
                
                /*
                 * when customer using expand_seo application all urls display as freindly url and the new product search page be /search/productName
                 * so we explode slashes and get last element in array to use it as search key word but when customer change limit products pre page or 
                 * change page the last key be empty and in this case we get our search key word from element before
                */
                
                $routeKeyArray = explode("/", $routeKey);
                $lastKey = end($routeKeyArray);
                $beforLastKey = prev($routeKeyArray);
                
                $keyWord = (!empty($lastKey)) ? $lastKey : $beforLastKey;
				if(!empty($keyWord)){
					$this->request->get['search'] = $keyWord;
				}
            }

		} else {
			// $search = '';
			$this->request->get['search'] = '';
		}

		if (isset($this->request->get['tag'])) {
			$tag = $this->request->get['tag'];
		} elseif (isset($this->request->get['search'])) {
			$tag = $this->request->get['search'];
		} else {
			$tag = '';
		}

		if (isset($this->request->get['description'])) {
			$description = $this->request->get['description'];
		} else {
			$description = '';
		}

		if (isset($this->request->get['category_id'])) {
			$category_id = $this->request->get['category_id'];
		} else {
			$category_id = 0;
		}

		if (isset($this->request->get['sub_category'])) {
			$sub_category = $this->request->get['sub_category'];
		} else {
			$sub_category = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = (!empty($this->config->get('config_products_default_sorting_by_column'))) ? $this->config->get('config_products_default_sorting_by_column') : 'ASC';
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

        if (isset($this->request->get['pc_target'])) {
            $pc_target = $this->request->get['pc_target'];
        } else {
            $pc_target = 0;
        }

		if (isset($this->request->get['search'])) {
			$this->document->setTitle($this->language->get('heading_title') .  ' - ' . $this->request->get['search']);
		} else {
			$this->document->setTitle($this->language->get('heading_title'));
		}
		// most search keywords
		// number of products per category to to be shown at the view
		$max_cat_products_num = 10;
		// get old most_searched cookies if exist
		$stored_searches = isset($_COOKIE['most_searched']) ? unserialize(base64_decode($_COOKIE['most_searched'])) : null;
		if( isset($this->request->get['search']) && isset($this->request->get['category_id'])  && $this->request->get['category_id'] > 0 ){
			$cat_name = $this->model_catalog_category->getCategory($this->request->get['category_id'])['name'];
			$search_word = $this->request->get['search'];
			if(!empty($search_word))
				$stored_searches[$cat_name][$search_word]++;
			// resort the array
			foreach ($stored_searches as $cat_name => $cat_data) {
				arsort($cat_data);
				//$cat_data = array_slice($cat_data,0,$max_cat_products_num);
				$stored_searches[$cat_name] = $cat_data;
			}
			// update cookies
			setcookie('most_searched', base64_encode(serialize($stored_searches)), time()+30*24*60*60);
		}
		// $most_searched = unserialize(base64_decode($_COOKIE['most_searched']));
		// handle data to return to the view
		foreach ($stored_searches as $cat_name => $cat_data) {
			$cat_data = array_slice($cat_data,0,$max_cat_products_num);
			$stored_searches[$cat_name] = $cat_data;
		}
		//$this->document->addScript('expandish/view/javascript/jquery/jquery.total-storage.min.js');
		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
      		'separator' => false
   		);

		$url = '';

        if( ! empty( $this->request->get['mfp'] ) ) {
            $url .= '&mfp=' . $this->request->get['mfp'];
        }

		if (isset($this->request->get['search'])) {
			$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['tag'])) {
			$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['description'])) {
			$url .= '&description=' . $this->request->get['description'];
		}

		if (isset($this->request->get['category_id'])) {
			$url .= '&category_id=' . $this->request->get['category_id'];
		}

		if (isset($this->request->get['sub_category'])) {
			$url .= '&sub_category=' . $this->request->get['sub_category'];
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

        if (isset($this->request->get['pc_brand_id'])) {
            $url .= '&pc_brand_id=' . $this->request->get['pc_brand_id'];
        }
        if (isset($this->request->get['pc_model_id'])) {
            $url .= '&pc_model_id=' . $this->request->get['pc_model_id'];
        }
        if (isset($this->request->get['pc_year_id'])) {
            $url .= '&pc_year_id=' . $this->request->get['pc_year_id'];
        }
        if (isset($this->request->get['pc_target'])) {
            $url .= '&pc_target=' . $this->request->get['pc_target'];
        }

        if (isset($this->request->get['city'])) {
            $this->data['city_id'] = $this->request->get['city'];
            $url .= '&city=' . $this->request->get['city'];
        } else {
            $this->data['city_id'] = null;
        }

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('product/search', $url),
      		'separator' => $this->language->get('text_separator')
   		);

		/*if (isset($this->request->get['search'])) {
    		$this->data['heading_title'] = $this->language->get('heading_title') .  ' - ' . $this->request->get['search'];
		} else {
			$this->data['heading_title'] = $this->language->get('heading_title');
		}*/

		$this->data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

		$this->data['compare'] = $this->url->link('product/compare');

		$this->load->model('catalog/category');

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

		$this->data['products'] = array();

		if (
            ( isset($this->request->get['search'])  && $this->request->get['search'] != '')
            || isset($this->request->get['tag'])
            || isset($this->request->get['city'])
        ) {
			$data = array(
				'filter_name'         => trim($this->request->get['search']),
				'filter_tag'          => $tag,
				'filter_description'  => $description,
				'filter_category_id'  => $category_id,
				'filter_sub_category' => $sub_category,
				'sort'                => $sort,
				'order'               => $order,
				'start'               => ($page - 1) * $limit,
				'limit'               => $limit,
                'pc_brand_id' => $brand_id,
                'pc_model_id' => $model_id,
                'pc_year_id'  => $year_id,
                'pc_target' => $pc_target
			);

			if (isset($this->request->get['city'])) {
                if ($this->model_catalog_product->checkMSModule()) {
                    $data['city_id'] = $this->request->get['city'];
                }
            }

            $data['ignore_lang'] = true;
            $product_total = $this->model_catalog_product->getTotalProducts($data);

            $results = $this->model_catalog_product->getProducts($data);

			//Login Display Prices
			$config_customer_price = $this->config->get('config_customer_price');

            if (empty($results) && \Extension::isInstalled('multiseller') && $this->config->get('msconf_enable_search_by_seller') && $this->config->get('msconf_enable_search_by_seller') == 1) {
                if (isset($this->request->get['search'])) {
                    $this->redirect($this->url->link('seller/catalog-seller', 'search=' . $this->request->get['search'], 'SSL'));
                }
            }

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

				$price = false;
                $isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();

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
				///check permissions to view Add to Cart Button and view products
				$viewAddToCart = true;
				$hidCartConfig = $this->config->get('config_hide_add_to_cart');		
				if(($result['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart )
				{
					$viewAddToCart = false;
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
                    'viewAddToCart' => $viewAddToCart,
                    'general_use' => $result['general_use'] ?? '',
                    'display_price' =>$result['display_price']
				);
			}

			$url = '';

            if( ! empty( $this->request->get['mfp'] ) ) {
                $url .= '&mfp=' . $this->request->get['mfp'];
            }

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

            if (isset($this->request->get['city'])) {
                $url .= '&city=' . $this->request->get['city'];
            }

			$this->data['sorts'] = array();

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('product/search', 'sort=p.sort_order&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/search', 'sort=pd.name&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/search', 'sort=pd.name&order=DESC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/search', 'sort=p.price&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/search', 'sort=p.price&order=DESC' . $url)
			);

			if ($this->config->get('config_review_status')) {
				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/search', 'sort=rating&order=DESC' . $url)
				);

				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('product/search', 'sort=rating&order=ASC' . $url)
				);
			}

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('product/search', 'sort=p.model&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('product/search', 'sort=p.model&order=DESC' . $url)
			);

			$url = '';

            if( ! empty( $this->request->get['mfp'] ) ) {
                $url .= '&mfp=' . $this->request->get['mfp'];
            }

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

            if (isset($this->request->get['city'])) {
                $url .= '&city=' . $this->request->get['city'];
            }

			$this->data['limits'] = array();

			$limits = array_unique(array($this->config->get('config_catalog_limit'), 25, 50, 75, 100));

			sort($limits);

			foreach($limits as $limits){
				$this->data['limits'][] = array(
					'text'  => $limits,
					'value' => $limits,
					'href'  => $this->url->link('product/search', $url . '&limit=' . $limits)
				);
			}

			$url = '';

            if( ! empty( $this->request->get['mfp'] ) ) {
                $url .= '&mfp=' . $this->request->get['mfp'];
            }

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['filter_tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
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

            if (isset($this->request->get['city'])) {
                $url .= '&city=' . $this->request->get['city'];
            }

			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->url->link('product/search', $url . '&page={page}');

			$this->data['pagination'] = $pagination->render();
		}else if(isset($this->request->get['lableb_search'])){ 

			$this->data['limits'] = array();
			$limits = array_unique(array($this->config->get('config_catalog_limit'), 25, 50, 75, 100));
			sort($limits);
			foreach($limits as $limit){
				$this->data['limits'][] = array(
					'text'  => $limit,
					'value' => $limit,
					'href'  => $this->url->link('product/search&lableb_search='.$this->request->get['lableb_search'].'&limit=' . $limit)
				);
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

			$this->data['sorts'] = array();

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'default',
				'href'  => $this->url->link('product/search&lableb_search='.$this->request->get['lableb_search'].'&limit='.$limit)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_date_desc'),
				'value' => 'sort_date_desc',
				'href'  => $this->url->link('product/search&lableb_search='.$this->request->get['lableb_search'].'&sort=date_desc&limit='.$limit)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_date_asc'),
				'value' => 'sort_date_asc',
				'href'  => $this->url->link('product/search&lableb_search='.$this->request->get['lableb_search'].'&sort=date_asc&limit='.$limit)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('sort_text_price'),
				'value' => 'sort_price',
				'href'  => $this->url->link('product/search&lableb_search='.$this->request->get['lableb_search'].'&sort=price&limit='.$limit)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('sort_text_quantity'),
				'value' => 'sort_quantity',
				'href'  => $this->url->link('product/search&lableb_search='.$this->request->get['lableb_search'].'&sort=quantity&limit='.$limit)
			);

			$lableb_data_with_count = $this->lableb_search($limit,$page);
			$this->data['products'] = $lableb_data_with_count['products'];
			$pagination = new Pagination();
			$pagination->total = $lableb_data_with_count['count'];
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->url->link('product/search','&lableb_search='.$this->request->get['lableb_search'].'&limit='.$limit.'&page={page}');
			$this->data['pagination'] = $pagination->render();
		}

		if ($this->model_catalog_product->checkMSModule()) {
            $this->data['search_by_city']['status'] = 1;

            $this->data['search_by_city']['zones'] = $this->model_catalog_product->get_zones();
        }

		$this->data['search'] = $this->request->get['search'];
		$this->data['description'] = $description;
		$this->data['category_id'] = $category_id;
		$this->data['sub_category'] = $sub_category;

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
		$this->data['limit'] = $limit;

		//Login Display Prices
         $config_customer_price = $this->config->get('config_customer_price');

         $this->data['show_prices_login'] = true;
         if((($config_customer_price && $this->customer->isLogged()) || !$config_customer_price)){
            $this->data['show_prices_login'] = false;
         }

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/product/search.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/product/search.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/product/search.expand';
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
        
        // here we set the link that customer will redirect to after click on it
        end($this->data['breadcrumbs']); //get last element in breadcrumbs array
        $this->data['backUrl'] = prev($this->data['breadcrumbs']); //get prev element link
		
        $this->response->setOutput($this->render_ecwig());
  	}

	// search autofill
	//<![CDATA[
	public function ajax()
	{
		// Contains results
		$data = array();
		if( isset($this->request->get['keyword']) ) {
			// Parse all keywords to lowercase
			$keywords = strtolower( $this->request->get['keyword'] );
			// Perform search only if we have some keywords
			if( strlen($keywords) >= 2 ) {
				$parts = explode( ' ', $keywords );
				$add = '';
				// Generating search
				foreach( $parts as $part ) {
					$add .= ' AND (LOWER(pd.name) LIKE "%' . $this->db->escape($part) . '%"';
					$add .= ' OR LOWER(p.model) LIKE "%' . $this->db->escape($part) . '%")';
				}
				$add = substr( $add, 4 );
				$sql  = 'SELECT pd.product_id, pd.name, p.model FROM ' . DB_PREFIX . 'product_description AS pd ';
				$sql .= 'LEFT JOIN ' . DB_PREFIX . 'product AS p ON p.product_id = pd.product_id ';
				$sql .= 'LEFT JOIN ' . DB_PREFIX . 'product_to_store AS p2s ON p2s.product_id = pd.product_id ';
				$sql .= 'WHERE ' . $add . ' AND p.status = 1 ';
				$sql .= 'AND pd.language_id = ' . (int)$this->config->get('config_language_id');
				$sql .= ' AND p2s.store_id =  ' . (int)$this->config->get('config_store_id');
				$sql .= ' ORDER BY p.sort_order ASC, LOWER(pd.name) ASC, LOWER(p.model) ASC';
				$sql .= ' LIMIT 15';
				$res = $this->db->query( $sql );
				if( $res ) {
					$data = ( isset($res->rows) ) ? $res->rows : $res->row;

					// For the seo url stuff
					$basehref = 'product/product&keyword=' . $this->request->get['keyword'] . '&product_id=';
					foreach( $data as $key => $values ) {
						$data[$key] = array(
							'name' => htmlspecialchars_decode($values['name'] . ' (' . $values['model'] . ')', ENT_QUOTES),
							'href' => $this->url->link($basehref . $values['product_id'])
						);
					}
				}
			}
		}
		echo json_encode( $data );
	}
	public function lableb_search($limit,$page)
    { 
	
        $this->load->model('catalog/product');
		
        $searchText = '';
		$search_options = [];
		
		
		if(isset($this->request->get['lableb_search'])){
			$searchText = $this->request->get['lableb_search'];
		}else if(isset($this->request->get['search_text'])){
			$searchText = $this->request->get['search_text'];
		}
		$search_options['search_text'] = $searchText;

        if($this->request->get['category_id']){
			$this->load->model('catalog/category');
			$category_data = $this->model_catalog_category->getCategory($this->request->get['category_id']);
			$search_options['category'] =  urlencode('"' . $category_data['name'] . '"');
        }
		
		if($this->request->get['sort']){
			if($this->request->get['sort'] == 'date_desc'){
				$search_options['sort'] =  "date_available desc";
			}else if($this->request->get['sort'] == 'date_asc'){
				$search_options['sort'] =  "date_available asc";
			}else if($this->request->get['sort'] == 'price'){
				$search_options['sort'] =  "price desc";
			}else if($this->request->get['sort'] == 'quantity'){
				$search_options['sort'] =  "quantity desc";
			}
		}
		
		$search_options['limit'] = $limit;  
		$search_options['page']  = $page;  
		
		$this->load->model('module/lableb');
		$lableb_returned_data = $this->model_module_lableb->search($search_options);
		
		return $lableb_returned_data;
    }
    //]]>
	// end search auto fill
}
?>