<?php 
class ControllerProductSpecial extends Controller { 

	public function index() { 
    	$this->language->load_json('product/special');
		
		$this->load->model('catalog/product');
		
		$this->load->model('tool/image');

		$isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();
		
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
				    	
		$this->document->setTitle($this->language->get('heading_title'));
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
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('product/special', $url),
      		'separator' => $this->language->get('text_separator')
   		);
		
    	$this->data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
		
		$this->data['compare'] = $this->url->link('product/compare');
		
		$this->data['products'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $limit,
			'limit' => $limit
		);

        if( isset( $this->request->get['category_id'] ) ) {
            $data['filter_category_id'] = (int) $this->request->get['category_id'];
        }
			
		$product_total = $this->model_catalog_product->getTotalProductSpecials($data);
			
		$results = $this->model_catalog_product->getProductSpecials($data);
		
		//Login Display Prices
		$config_customer_price = $this->config->get('config_customer_price');

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
			if ($isCustomerAllowedToViewPrice) {
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
			}
			
			$special = false;
			if ((float)$result['special'] && ($isCustomerAllowedToViewPrice)) {
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
			///check permissions to view Add to Cart Button
			$this->data['viewAddToCart'] = true;
			$hidCartConfig = $this->config->get('config_hide_add_to_cart');
			if(($result['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart)
			{
				$this->data['viewAddToCart'] = false;
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
				'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'] . $url),
				// for swap image
				'thumb_swap'  => $this->model_tool_image->resize($images, $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height')),
				'image_swap'  => $images,
				//
				// for saving percentage
				'saving'	=> $savingAmount,
                'stock_status' => $stock_status,
                'stock_status_id' => $result['stock_status_id'],
                'quantity' => $result['quantity'],
				//

				'manufacturer_id' => $result['manufacturer_id'],
				'manufacturer' => $result['manufacturer'],
				'manufacturerimg' => $result['manufacturerimg'],
				'manufacturer_href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id']),
				'display_price' =>$result['display_price']
			);
		}

		$url = '';

        if( ! empty( $this->request->get['mfp'] ) ) {
            $url .= '&mfp=' . $this->request->get['mfp'];
        }

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}
			
		$this->data['sorts'] = array();
		
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_default'),
			'value' => 'p.sort_order-ASC',
			'href'  => $this->url->link('product/special', 'sort=p.sort_order&order=ASC' . $url)
		);
		
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_name_asc'),
			'value' => 'pd.name-ASC',
			'href'  => $this->url->link('product/special', 'sort=pd.name&order=ASC' . $url)
		); 

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_name_desc'),
			'value' => 'pd.name-DESC',
			'href'  => $this->url->link('product/special', 'sort=pd.name&order=DESC' . $url)
		);  

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_price_asc'),
			'value' => 'ps.price-ASC',
			'href'  => $this->url->link('product/special', 'sort=ps.price&order=ASC' . $url)
		); 

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_price_desc'),
			'value' => 'ps.price-DESC',
			'href'  => $this->url->link('product/special', 'sort=ps.price&order=DESC' . $url)
		); 
		
		if ($this->config->get('config_review_status')) {	
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_desc'),
				'value' => 'rating-DESC',
				'href'  => $this->url->link('product/special', 'sort=rating&order=DESC' . $url)
			); 
				
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_asc'),
				'value' => 'rating-ASC',
				'href'  => $this->url->link('product/special', 'sort=rating&order=ASC' . $url)
			);
		}
		
		$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('product/special', 'sort=p.model&order=ASC' . $url)
		); 

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_model_desc'),
			'value' => 'p.model-DESC',
			'href'  => $this->url->link('product/special', 'sort=p.model&order=DESC' . $url)
		);
		
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
									
		$this->data['limits'] = array();

		$limits = array_unique(array($this->config->get('config_catalog_limit'), 25, 50, 75, 100));
		
		sort($limits);

		foreach($limits as $limitValue){
			$this->data['limits'][] = array(
				'text'  => $limitValue,
				'value' => $limitValue,
				'href'  => $this->url->link('product/special', $url . '&limit=' . $limitValue)
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
		$pagination->url = $this->url->link('product/special', $url . '&page={page}');
			
		$this->data['pagination'] = $pagination->render();
			
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
		$this->data['limit'] = $limit;

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