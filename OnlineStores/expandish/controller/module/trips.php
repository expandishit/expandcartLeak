<?php 
class ControllerModuleTrips extends Controller { 

	public function __construct($registry){
        parent::__construct($registry);

        if( !(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1)){
	        $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
		}else{

        $this->language->load_json('module/trips');
		$this->load->model('module/trips');
		$this->load->model('catalog/product');
        $language_id = $this->config->get('config_language_id');
        
        $this->document->addStyle('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
        $this->document->addStyle('https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css');
        $this->document->addStyle('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
        $this->document->addStyle('expandish/view/theme/default/css/trips/css/style.css');
        if($language_id==2){$this->document->addStyle('expandish/view/theme/default/css/trips/css/rtl.css');}
        $this->document->addStyle('expandish/view/theme/default/css/trips/css/responsive.css');
        $this->document->addScript('https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js');
        $this->document->addScript('expandish/view/theme/default/js/trips/app.js');
       
        }

    }
    public function index()
    {
        //Page Title
        $this->document->setTitle($this->language->get('title_trips'));
       
        $tripsCategoriesIDs=$this->model_module_trips->getCustomCategoriesIDs('trips_categories');
		$results = $this->model_module_trips->getCustomCategories($tripsCategoriesIDs,$language_id);
        $this->data['trips_categories'] = array();
			$categoryCount = 0;
			$categoryWithIconCount = 0;
			$categoryWithImageCount = 0;

			foreach ($results as $result) {
				$categoryCount++;
				if ($result['image']) {
					$categoryWithImageCount++;
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize($this->config->get('no_image'), $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
				}

				if ($result['icon']) {
					$categoryWithIconCount++;
					$icon = $this->model_tool_image->resize($result['icon'], $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
					$icon_src =  \Filesystem::getUrl('image/' .$result['icon']);
				} else {
					$icon = false;
					$icon_src = \Filesystem::getUrl('image/' .$result['image']);
					if($icon_src == '' || !file_exists($icon_src) ){
						$icon_src = $image;
					}
				}

				$this->data['trips_categories'][] = array(
					'category_id' 	=> $result['category_id'],
					'name'  		=> $result['name'],
					'image' 		=> $result['image'],
					'icon'  		=> $icon,
					'icon_src' 		=> $icon_src,
					'href'  		=>$this->url->link('module/trips/filter_trips', 'sort=pd.name&order=DESC&category_id=' . $result['category_id']),
                    'thumb' 		=> $image
				);
			}
            $this->data['popular_trips']=$this->popular_trips();
            
            $this->template ='default/template/module/trips/home.expand';
            $this->children = [ 'common/footer' , 'common/header' ];
            $this->response->setOutput($this->render_ecwig());

    }

     public function popular_trips()
     {
         //Popular Trips
         $data['trips']=1;
         $data['popular']=1;
         $results = $this->model_catalog_product->getProducts($data);
         foreach ($results as $result) {

             if ($result['image']) {
                 $image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
             } else {
                 $image = false;
             }
             $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
             $trip_data=$this->model_module_trips->getTripProduct((int)$result['product_id']); 
             $popular_trips[] = array(
                 'product_id'  => $result['product_id'],
                 'thumb'       => $image,
                 'image'       => $result['image'],
                 'name'        => $result['name'],
                 'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
                 'price'       => $price,
                 'trip_data'       => $trip_data,
                 'href'        => $this->url->link('module/trips/trip_details', 'product_id=' . $result['product_id'] . $url),
             );
         }
         return $popular_trips;


     }
     public function trip_details()
     {
        //Breadcrumbs
        $this->data['breadcrumbs'] = $this->_createBreadcrumbs("trip_details");
        if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$product_info= $this->model_catalog_product->getProduct($product_id);
        $this->data['trip_price']= $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
        $images= json_decode($product_info['product_images'], true);
        $this->data['main_image']= $this->model_tool_image->resize($product_info['image'],916,1150);
        foreach ($images as $img_result) {

            if ($img_result['image']) {
                $image = $this->model_tool_image->resize($img_result['image'],916,1150);
            } else {
                $image = false;
            }
            $this->data['product_images'][] = array(
                'image'       => $image,
               
            );
        }
        $this->data['tripData']= $this->model_module_trips->getTripProduct($product_id); 
        $this->data['product_info']=$product_info;
        $this->data['options']= $this->model_catalog_product->getProductOptions($product_id); 
        $this->data['popular_trips']=$this->popular_trips();
        $this->document->setTitle($product_info['name']);
        $this->template ='default/template/module/trips/trip_details.expand';
        $this->children = [ 'common/footer' , 'common/header' ];
        $this->response->setOutput($this->render_ecwig());
     }
     public function filter_trips()
     {
       
        if (isset($this->request->get['search'])) {
			$this->document->setTitle($this->language->get('title_trips') .  ' - ' . $this->request->get['filterbyArea']);
		} else {
			$this->document->setTitle($this->language->get('title_trips'));
		}
        //Breadcrumbs
        $this->data['breadcrumbs'] = $this->_createBreadcrumbs('filter_trips');
       
        $defaultSortingColumn = $this->config->get('config_products_default_sorting_column');
		
		$defaultSortingColumnBy = (!empty($this->config->get('config_products_default_sorting_by_column'))) ? $this->config->get('config_products_default_sorting_by_column') : 'ASC';
        if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {

			if($defaultSortingColumn === 'name'){
				$sort  = "pd.name";
				$order = $defaultSortingColumnBy; //From A-to-Z
			}
			elseif($defaultSortingColumn === 'date_added'){
				$sort  = "p.date_added";
				$order = $defaultSortingColumnBy;
			}
		
			else{ //Date_Available
				$sort = ! empty( $defaultSortingColumn ) ? "p.{$defaultSortingColumn}" : 'p.sort_order' ;
			}
            }
            if (isset($this->request->get['filterbyArea'])) {
                $filterbyArea = $this->request->get['filterbyArea'];
            }
            else{
                $filterbyArea='';
            }
            if (isset($this->request->get['from_date'])) {
                $fromDate = $this->request->get['from_date'];
                $from_date = date("Y-m-d", strtotime($fromDate));
            }
            else{
                $from_date='';
            }

            if (isset($this->request->get['to_date'])) {
                $toDate = $this->request->get['to_date'];
                $to_date = date("Y-m-d", strtotime($toDate));
            }
            else{
                $to_date='';
            }
        
            if (isset($this->request->get['category_id'])) {
                $category_id = explode(",",$this->request->get['category_id']);
            } else {
                $category_id = '';
            }
            if (isset($this->request->get['from_price'])) {
                $from_price = $this->request->get['from_price'];
            }
            else{
                $from_price='';
            }
            if (isset($this->request->get['to_price'])) {
                $to_price = $this->request->get['to_price'];
            }
            else{
                $to_price='';
            }
            
            if (isset($this->request->get['order'])) {
                $order = $this->request->get['order'];
            } else if(empty($order)){
                $order =  $defaultSortingColumnBy;
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
            $data = array(
				'trips' => 1,
				'sort'               => $sort,
                'filterbyArea'        => trim($this->request->get['filterbyArea']),
                'categories_ids'  => $category_id,
                'from_date'  => $from_date,
                'to_date'  => $to_date,
                'from_price'  => $from_price,
                'to_price'  => $to_price,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);
            $results = $this->model_catalog_product->getProducts($data);
            $product_total = $this->model_catalog_product->getTotalProducts($data);
    
         foreach ($results as $result) {

            if ($result['image']) {
                $image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
            } else {
                $image = false;
            }
            $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
            $trip_data=$this->model_module_trips->getTripProduct((int)$result['product_id']); 
            $this->data['trips'][] = array(
                'product_id'  => $result['product_id'],
                'thumb'       => $image,
                'image'       => $result['image'],
                'name'        => $result['name'],
                'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
                'price'       => $price,
                'trip_data'       => $trip_data,
                'href'        => $this->url->link('module/trips/trip_details', 'product_id=' . $result['product_id'] . $url),
            );
           }
            $url = '';
        
                
            if (isset($this->request->get['filterbyArea'])) {
                $url .= '&filterbyArea=' . urlencode(html_entity_decode($this->request->get['filterbyArea'], ENT_QUOTES, 'UTF-8'));
            }
            if (isset($this->request->get['category_id'])) {
                $url .= '&category_id=' . $this->request->get['category_id'];
            }
            if (isset($this->request->get['from_date'])) {
                $url .= '&from_date=' . $this->request->get['from_date'];
            }
            if (isset($this->request->get['to_date'])) {
                $url .= '&to_date=' . $this->request->get['to_date'];
            }
            if (isset($this->request->get['from_price'])) {
                $url .= '&from_price=' . $this->request->get['from_price'];
            }
            if (isset($this->request->get['to_price'])) {
                $url .= '&to_price=' . $this->request->get['to_price'];
            }
            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $this->data['sorts'] = array();

            $default_sort = ! empty( $defaultSortingColumn ) ? "p.{$defaultSortingColumn}" : 'p.sort_order' ;
            $this->data['sorts'][] = array(
                'text'  => $this->language->get('text_default'),
                'value' => 'p.sort_order-ASC',
                'href'  => $this->url->link('module/trips/filter_trips', 'sort=p.sort_order&order=ASC' . $url)
            );

            $this->data['sorts'][] = array(
                'text'  => $this->language->get('text_name_asc'),
                'value' => 'pd.name-ASC',
                'href'  => $this->url->link('module/trips/filter_trips', 'sort=pd.name&order=ASC' . $url)
            );

            $this->data['sorts'][] = array(
                'text'  => $this->language->get('text_name_desc'),
                'value' => 'pd.name-DESC',
                'href'  => $this->url->link('module/trips/filter_trips', 'sort=pd.name&order=DESC' . $url)
            );

            $this->data['sorts'][] = array(
                'text'  => $this->language->get('text_price_asc'),
                'value' => 'p.price-ASC',
                'href'  => $this->url->link('module/trips/filter_trips', 'sort=p.price&order=ASC' . $url)
            );

            $this->data['sorts'][] = array(
                'text'  => $this->language->get('text_price_desc'),
                'value' => 'p.price-DESC',
                'href'  => $this->url->link('module/trips/filter_trips', 'sort=p.price&order=DESC' . $url)
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
			$this->data['limits'] = array();
			$limits = array_unique(array($this->config->get('config_catalog_limit'), 25, 50, 75, 100));
			sort($limits);
			foreach($limits as $limits){
				$this->data['limits'][] = array(
					'text'  => $limits,
					'value' => $limits,
                    'href'  => $this->url->link('module/trips/filter_trips', $url . '&limit=' . $limits)
				);
			}
            $pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
            $pagination->text_first = $this->session->data['language'] == 'ar' ? '|&gt;' : '|&lt;' ;
            $pagination->text_last = $this->session->data['language'] == 'ar' ? '|&lt;' : '|&gt;' ;
            $pagination->text_next = $this->session->data['language'] == 'ar' ? '<span><i aria-hidden="true" class="fa fa-angle-left"></i></span>' : '<span><i aria-hidden="true" class="fa fa-angle-right"></i></span>' ;
            $pagination->text_prev = $this->session->data['language'] == 'ar' ? '<span><i aria-hidden="true" class="fa fa-angle-right"></i></span>' : '<span><i aria-hidden="true" class="fa fa-angle-left"></i></span>' ;
			$pagination->text = $this->language->get('text_pagination');
            $pagination->url = $this->url->link('module/trips/filter_trips', $url . '&page={page}');
			$this->data['pagination'] = $pagination->render();

			$this->data['sort'] = $sort;
			$this->data['order'] = $order;
			$this->data['limit'] = $limit;         
            //For getting Trips Categories
            $tripsCategoriesIDs=$this->model_module_trips->getCustomCategoriesIDs('trips_categories');
            $this->data['trips_categories'] = $this->model_module_trips->getCustomCategories($tripsCategoriesIDs,$language_id);
            
            $this->data['filterbyArea'] = $this->request->get['filterbyArea'];
            $this->data['category_id'] =explode(",",$this->request->get['category_id']);
            $this->data['from_date'] =$this->request->get['from_date'];
            $this->data['to_date'] =$this->request->get['to_date'];
            $this->data['from_price'] =$this->request->get['from_price'];
            $this->data['to_price'] =$this->request->get['to_price'];
        
            $this->template ='default/template/module/trips/filter_trips.expand';
            $this->children = [ 'common/footer' , 'common/header' ];
            $this->response->setOutput($this->render_ecwig());
     }
     public function trips_orders()
     {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/order', '', 'SSL');

            $this->redirect($this->url->link('account/login', '', 'SSL'));
         }

        $this->document->setTitle($this->language->get('title_trips_orders'));
        //Breadcrumbs
         $this->data['breadcrumbs'] = $this->_createBreadcrumbs('trips_orders');

         $this->data['upcoming_trips_orders']=$this->myReservedtrips("upcoming");
        
         $this->data['past_trips_orders']=$this->myReservedtrips("past");
         $this->data['canceled_trips_orders']=$this->myReservedtrips("cancel");
       
        $this->template ='default/template/module/trips/trips_orders.expand';
        $this->children = [ 'common/footer' , 'common/header' ];
        $this->response->setOutput($this->render_ecwig());
     }

     public function myReservedtrips($trip_date)
     {
        $this->load->model('account/order');
        $results = $this->model_account_order->getOrders(0, 100);
        foreach ($results as $result) 
         {
            $product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
             $trips = $this->model_module_trips->getOrderProductsTrips($result['order_id'],$trip_date);
             foreach ($trips as $trip) {
                $product_info = $this->model_catalog_product->getProduct($trip['product_id']);
                $tripData= $this->model_module_trips->getTripProduct($product_info['product_id']); 
                $trips_orders[] = array(
                     'order_id'   => $result['order_id'],
                     'name' => $trip['name'],
                     'driver_name' => $product_info['nickname'],
                     'image' => \Filesystem::getUrl('image/'.$product_info['image']),
                     'reservation' => $trip['quantity'],
                     'Date' => $tripData['from_date'],
                     'city' => $tripData['area'],
                     'currency' => $this->currency->getCode(),
                     'price' => $this->currency->format($trip['price'] + ($this->config->get('config_tax') ? $trip['tax'] : 0), $trip['currency_code'], $trip['currency_value']),
                     'total' => $this->currency->format($trip['total'] + ($this->config->get('config_tax') ? ($trip['tax'] * $trip['quantity']) : 0), $trip['currency_code'], $trip['currency_value']),
                     'href'        => $this->url->link('module/trips/reservedTripDetails', 'order_id=' . $result['order_id'] .'&trip_type='.$trip_date. $url),
                 );
             }
         }
         return $trips_orders;
     }
     public function reservedTripDetails()
     {
       
        $this->document->setTitle($this->language->get('title_trips'));
        //Breadcrumbs
        $this->data['breadcrumbs'] = $this->_createBreadcrumbs('reservedTripDetails');
        if (isset($this->request->get['order_id'])) {
			$order_id = (int)$this->request->get['order_id'];
			$trip_type = $this->request->get['trip_type'];
		} else {
			$order_id = 0;
            $trip_type="";
		}
        
        $trips = $this->model_module_trips->getOrderProductsTrips($order_id);
        foreach ($trips as $trip) {
            $this->load->model('account/order');
               
           $product_info = $this->model_catalog_product->getProduct($trip['product_id']);
           $tripData= $this->model_module_trips->getTripProduct($product_info['product_id']); 
           $options = $this->model_account_order->getOrderOptions($order_id,$trip['order_product_id']);
           $cancelTripFlag= $this->model_module_trips->cancelTripConfig($trip['product_id']); 
		   $cancelStatus= $this->model_module_trips->cancelStatus($order_id); 
           $order_data[] = array(
                'order_id'   =>$order_id,
                'product_id'   =>$trip['product_id'],
                'trip_type'   =>$trip_type,
                'cancelTripFlag'   =>$cancelTripFlag,
                'cancelStatus'   =>$cancelStatus,
                'name' => $trip['name'],
                'driver_name' => $product_info['nickname'],
                'driver_phone' => $product_info['mobile'],
                'image' => \Filesystem::getUrl('image/'.$product_info['image']),
                'reservation' => $trip['quantity'],
                'from_date' => $tripData['from_date'],
                'to_date' => $tripData['to_date'],
                'duration' => $tripData['duration'],
                'pickup_point' => $tripData['pickup_point'],
                'destination_point' => $tripData['destination_point'],
                'area' => $tripData['area'],
                'description' => $product_info['description'],
                'currency' => $this->currency->getCode(),
                'questionare'=>$options,
                'price' => $this->currency->format($trip['price'] + ($this->config->get('config_tax') ? $trip['tax'] : 0), $trip['currency_code'], $trip['currency_value']),
                'total' => $this->currency->format($trip['total'] + ($this->config->get('config_tax') ? ($trip['tax'] * $trip['quantity']) : 0), $trip['currency_code'], $trip['currency_value']),
                'href'        => $this->url->link('module/trips/trip_details', 'product_id=' . $trip['product_id'] . $url),
            );
        }
        $this->data['order_data']=$order_data[0];
        $this->template ='default/template/module/trips/reserved_trip_details.expand';
        $this->children = [ 'common/footer' , 'common/header'];
        $this->response->setOutput($this->render_ecwig());
    }

    public function cancel_trip()
    {
        $order_id=$this->request->post['order_id'];
        $product_id=$this->request->post['product_id'];
        $data = array(
            'order_id' => $order_id,
            'product_id' => $product_id, 
            'canceled_by'        =>"rider",
        );
        $is_canceled =  $this->model_module_trips->cancelTrip($data);
        if($is_canceled) return true; else return false;
    }
     
	private function _createBreadcrumbs($page = null, ...$details){

        $breadcrumbs = [
            [
                'text'      => $this->language->get('text_home'),
                'href'      => $this->url->link('module/trips'),
                'separator' => false
            ],
            [
                'text'      => $this->language->get('title_trips'),
                'href'      => $this->url->link('module/trips/filter_trips'),
                'separator' => false
            ],
            [
                'text'      => $this->language->get('title_trips_orders'),
                'href'      => $this->url->link('module/trips/trips_orders'),
                'separator' => false
            ]
        ];

        if($page == "trip_details"){
            $breadcrumbs[] = [
                'text'      => $this->language->get('title_trip_details'),
                'separator' => false
            ];
        }
        if($page == "filter_trips"){
            $breadcrumbs[] = [
                'text'      => $this->language->get('find_trips'),
                'separator' => false
            ];
        }
        if( $page=="reservedTripDetails"){
            $breadcrumbs[] = [
                'text'      => $this->language->get('trip_details'),
                'href'      => $this->url->link('module/trips/trips_orders'),
                'separator' => false
            ];
        }

        return $breadcrumbs;
     }
    


}