<?php
class ControllerSaleAramexBulkSchedulePickup extends Controller {
	private $error = array();

	public function index() {
		$this->language->load('sale/order');
		$this->language->load('sale/aramex');

		$this->document->setTitle($this->language->get('heading_title_bulk_shedule'));

		$this->load->model('sale/order');
		$this->load->model('sale/aramex');
		
		$this->getList();
	}
	protected function getList() {
		if (isset($this->request->get['filter_order_id'])) {
			$filter_order_id = $this->request->get['filter_order_id'];
		} else {
			$filter_order_id = null;
		}

		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = null;
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$filter_order_status_id = $this->request->get['filter_order_status_id'];
		} else {
			$filter_order_status_id = null;
		}

		if (isset($this->request->get['filter_total'])) {
			$filter_total = $this->request->get['filter_total'];
		} else {
			$filter_total = null;
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = null;
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$filter_date_modified = $this->request->get['filter_date_modified'];
		} else {
			$filter_date_modified = null;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'o.order_id';
		}

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

		$url = '';

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}

		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
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

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title_bulk_shedule'),
			'href'      => $this->url->link('sale/aramex_bulk_schedule_pickup', '', 'SSL'),
			'separator' => ' :: '
		);

		$this->data['create'] = $this->url->link('sale/aramex_bulk_schedule_pickup/create', '', 'SSL');
		
		
		$this->data['orders'] = array();
		
		$AllAWB = $this->model_sale_aramex->getAllAWB();
		$AllPickup = $this->model_sale_aramex->getAllPickup();
		
		if(isset($AllAWB) & !empty($AllAWB))
		{
			if(isset($AllPickup) & !empty($AllPickup))
			{
				$tobegenrate = array_diff($AllAWB,$AllPickup);
			}else{
				$tobegenrate = $AllAWB;
			}
		}else{
			$tobegenrate = array();
		}
		
		$data = array(
			'filter_order_id'        => $filter_order_id,
			'filter_customer'	     => $filter_customer,
			'filter_order_status_id' => $filter_order_status_id,
			'filter_total'           => $filter_total,
			'filter_date_added'      => $filter_date_added,
			'filter_date_modified'   => $filter_date_modified,
			'sort'                   => $sort,
			'order'                  => $order,
			'tobegenrate'			 => $tobegenrate,
			'start'                  => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'                  => $this->config->get('config_admin_limit')
		);

		$order_total = $this->model_sale_aramex->getTotalOrders($data);

		$results = $this->model_sale_order->getOrders($data);
		
		//print_r($AllAWB);
		//print_r($AllPickup);
		
		//print_r($tobegenrate);
		foreach ($results as $result) {
			
		if(in_array($result['order_id'],$tobegenrate))
		{
			$action = array();
			$action[] = array(
				'text' => $this->language->get('text_view'),
				'href' => $this->url->link('sale/order/info', 'order_id=' . $result['order_id'] . $url, 'SSL')
			);

			if (strtotime($result['date_added']) > strtotime('-' . (int)$this->config->get('config_order_edit') . ' day')) {
			/*$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('sale/order/update', 'order_id=' . $result['order_id'] . $url, 'SSL')
				);*/
			}

			$this->data['orders'][] = array(
				'order_id'      => $result['order_id'],
				'customer'      => $result['customer'],
				'status'        => $result['status'],
				'total'         => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
				'selected'      => isset($this->request->post['selected']) && in_array($result['order_id'], $this->request->post['selected']),
				'action'        => $action
			);
		}	
		
		}

		$this->data['heading_title'] = $this->language->get('heading_title_bulk_shedule');

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_missing'] = $this->language->get('text_missing');

		$this->data['column_order_id'] = $this->language->get('column_order_id');
		$this->data['column_customer'] = $this->language->get('column_customer');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_total'] = $this->language->get('column_total');
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_date_modified'] = $this->language->get('column_date_modified');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['text_create'] = $this->language->get('text_create');
		$this->data['button_filter'] = $this->language->get('button_filter');

		$this->data['token'] = null;

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}

		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['sort_order'] = $this->url->link('sale/aramex_bulk_schedule_pickup', 'sort=o.order_id' . $url, 'SSL');
		$this->data['sort_customer'] = $this->url->link('sale/aramex_bulk_schedule_pickup', 'sort=customer' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('sale/aramex_bulk_schedule_pickup', 'sort=status' . $url, 'SSL');
		$this->data['sort_total'] = $this->url->link('sale/aramex_bulk_schedule_pickup', 'sort=o.total' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('sale/aramex_bulk_schedule_pickup', 'sort=o.date_added' . $url, 'SSL');
		$this->data['sort_date_modified'] = $this->url->link('sale/aramex_bulk_schedule_pickup', 'sort=o.date_modified' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}

		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/aramex_bulk_schedule_pickup', 'page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['filter_order_id'] = $filter_order_id;
		$this->data['filter_customer'] = $filter_customer;
		$this->data['filter_order_status_id'] = $filter_order_status_id;
		$this->data['filter_total'] = $filter_total;
		$this->data['filter_date_added'] = $filter_date_added;
		$this->data['filter_date_modified'] = $filter_date_modified;

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'sale/aramex_bulk_schedule_pickup.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	public function create() {
		
		
		$this->language->load('sale/aramex');
		$this->load->model('sale/order');
		$this->load->model('sale/aramex');
		$this->load->model('shipping/aramex');
		$this->document->addScript('view/javascript/jquery.chained.js');
		
		if (isset($this->request->post['selected'])) {
			$orders = $this->request->post['selected'];
		} elseif (isset($this->request->post['order_id'])) {
			$orders = $this->request->post['order_id'];
		}
		
		
		
		############ button ########## 
		   $this->data['text_back_to_order'] = $this->language->get('text_back_to_order');
		   $this->data['back_to_order'] = $this->url->link('sale/aramex_bulk_schedule_pickup', '', 'SSL');

        $this->data['text_schedule_pickup'] = $this->language->get('text_schedule_pickup');
        $this->data['text_pickup_details'] = $this->language->get('text_pickup_details');
        $this->data['entry_location'] = $this->language->get('entry_location');
        $this->data['entry_vehicle_type'] = $this->language->get('entry_vehicle_type');
        $this->data['text_small_vehicle'] = $this->language->get('text_small_vehicle');
        $this->data['text_medim_vehicle'] = $this->language->get('text_medim_vehicle');
        $this->data['text_large_vehicle'] = $this->language->get('text_large_vehicle');
        $this->data['entry_date'] = $this->language->get('entry_date');
        $this->data['entry_ready_time'] = $this->language->get('entry_ready_time');
        $this->data['entry_closing_time'] = $this->language->get('entry_closing_time');
        $this->data['entry_shipment_dest'] = $this->language->get('entry_shipment_dest');
        $this->data['entry_company'] = $this->language->get('entry_company');
        $this->data['entry_contact'] = $this->language->get('entry_contact');
        $this->data['entry_phone'] = $this->language->get('entry_phone');
        $this->data['entry_mobile'] = $this->language->get('entry_mobile');
        $this->data['entry_address'] = $this->language->get('entry_address');
        $this->data['entry_country'] = $this->language->get('entry_country');
        $this->data['entry_state'] = $this->language->get('entry_state');
        $this->data['entry_city'] = $this->language->get('entry_city');
        $this->data['entry_postal_code'] = $this->language->get('entry_postal_code');
        $this->data['entry_email'] = $this->language->get('entry_email');
        $this->data['entry_comment'] = $this->language->get('entry_comment');

        $this->data['entry_schedule_pickup'] = $this->language->get('entry_schedule_pickup');
        $this->data['entry_order_id'] = $this->language->get('entry_order_id');
        $this->data['entry_reference1'] = $this->language->get('entry_reference1');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['entry_ready'] = $this->language->get('entry_ready');
        $this->data['entry_pending'] = $this->language->get('entry_pending');
        $this->data['entry_product_group'] = $this->language->get('entry_product_group');
        $this->data['text_inter_express'] = $this->language->get('text_inter_express');
        $this->data['text_domestic'] = $this->language->get('text_domestic');
        $this->data['entry_product_type'] = $this->language->get('entry_product_type');
        $this->data['entry_payment_type'] = $this->language->get('entry_payment_type');
        $this->data['text_prepaid'] = $this->language->get('text_prepaid');
        $this->data['text_collect'] = $this->language->get('text_collect');
        $this->data['text_third_party'] = $this->language->get('text_third_party');
        $this->data['entry_total_weight'] = $this->language->get('entry_total_weight');
        $this->data['text_kg'] = $this->language->get('text_kg');
        $this->data['text_lb'] = $this->language->get('text_lb');
        $this->data['entry_no_of_pieces'] = $this->language->get('entry_no_of_pieces');
        $this->data['entry_no_of_shipments'] = $this->language->get('entry_no_of_shipments');
        $this->data['error_no_orders'] = $this->language->get('error_no_orders');
        $this->data['error_pickup_date'] = $this->language->get('error_pickup_date');
        $this->data['error_ready_time'] = $this->language->get('error_ready_time');
        $this->data['error_ready_time_pick'] = $this->language->get('error_ready_time_pick');
        $this->data['error_ready_time_close'] = $this->language->get('error_ready_time_close');
        $this->data['text_submit'] = $this->language->get('text_submit');
		############### label #############
					
					
					
					$this->data['heading_title'] = $this->language->get('heading_title_shedule');
                    $this->document->setTitle($this->language->get('heading_title_shedule'));
					
					$this->data['breadcrumbs'] = array();

					$this->data['breadcrumbs'][] = array(
						'text'      => $this->language->get('text_home'),
						'href'      => $this->url->link('common/home', '', 'SSL'),
						'separator' => false
					);

					$this->data['breadcrumbs'][] = array(
						'text'      => $this->language->get('heading_title_shedule'),
						'href'      => $this->url->link('sale/aramex_bulk_schedule_pickup', '', 'SSL'),
						'separator' => ' :: '
					);
	
		
		############ button ##########
		$AllAWB = $this->model_sale_aramex->getAllAWB();
		$AllPickup = $this->model_sale_aramex->getAllPickup();
		if(isset($orders) && !empty($orders))
		{
				if(isset($AllAWB) & !empty($AllAWB))
				{
					if(isset($AllPickup) & !empty($AllPickup))
					{
						$tobegenrate = array_diff($AllAWB,$AllPickup);
					}else{
						$tobegenrate = $AllAWB;
					}
				}else{
					$tobegenrate = array();
				}
			
				foreach($orders as $id)
				{
				if(in_array($id,$tobegenrate))
				{	
				$order_id = $id;
				$order_info = $this->model_sale_order->getOrder($order_id);
				
				if ($order_info) {
					
					$this->data['firstname'][$order_id] = $order_info['firstname'];
					$this->data['lastname'][$order_id] = $order_info['lastname'];
					$this->document->setTitle($this->language->get('heading_title_shedule'));
			
								
			##################### config shipper details ################
	if(isset($this->request->post['contact']) && !empty($this->request->post['contact'])) {
			$this->data['contact'] = $this->request->post['contact'];
	}else{
			$this->data['contact'] = ($this->config->get('aramex_shipper_name'))?$this->config->get('aramex_shipper_name'):'';
	}
	
	if(isset($this->request->post['company']) && !empty($this->request->post['company'])) {
			$this->data['company'] = $this->request->post['company'];
	}else{
			$this->data['company'] = ($this->config->get('aramex_shipper_company'))?$this->config->get('aramex_shipper_company'):'';
	}
	
	if(isset($this->request->post['phone']) && !empty($this->request->post['phone'])) {
			$this->data['phone'] = $this->request->post['phone'];
	}else{
			$this->data['phone']   = ($this->config->get('aramex_shipper_phone'))?$this->config->get('aramex_shipper_phone'):'';
	}
	
	if(isset($this->request->post['address']) && !empty($this->request->post['address'])) {
			$this->data['address'] = $this->request->post['address'];
	}else{
			$this->data['address'] = ($this->config->get('aramex_shipper_address'))?$this->config->get('aramex_shipper_address'):'';
	}
	
	if(isset($this->request->post['country']) && !empty($this->request->post['country'])) {
			$this->data['country'] = $this->request->post['country'];
	}else{
			$this->data['country'] = ($this->config->get('aramex_shipper_country_code'))?$this->config->get('aramex_shipper_country_code'):'';
	}
	
	if(isset($this->request->post['city']) && !empty($this->request->post['city'])) {
			$this->data['city'] = $this->request->post['city'];
	}else{
			$this->data['city']    = ($this->config->get('aramex_shipper_city'))?$this->config->get('aramex_shipper_city'):'';
	}
	
	if(isset($this->request->post['zip']) && !empty($this->request->post['zip'])) {
			$this->data['zip'] = $this->request->post['zip'];
	}else{
			$this->data['zip']     = ($this->config->get('aramex_shipper_postal_code'))?$this->config->get('aramex_shipper_postal_code'):'';
	}
	
	if(isset($this->request->post['state']) && !empty($this->request->post['state'])) {
			$this->data['state'] = $this->request->post['state'];
	}else{
			$this->data['state']   = ($this->config->get('aramex_shipper_state'))?$this->config->get('aramex_shipper_state'):'';
	}
	
	if(isset($this->request->post['email']) && !empty($this->request->post['email'])) {
			$this->data['email'] = $this->request->post['email'];
	}else{
			$this->data['email']   = ($this->config->get('aramex_shipper_email'))?$this->config->get('aramex_shipper_email'):'';
	}
	
	if(isset($this->request->post['date']) && !empty($this->request->post['date'])) {
			$this->data['date'] = $this->request->post['date'];
	}else{
			$this->data['date']   = date('Y-m-d');
	}		##################### customer shipment details ################
			
			$shipment_receiver_name ='';
			$shipment_receiver_street ='';
			

			$this->data['destination_country'] = ($order_info['shipping_iso_code_2'])?$order_info['shipping_iso_code_2']:'';
			$this->data['destination_city']    = ($order_info['shipping_city'])?$order_info['shipping_city']:'';
			$this->data['destination_zipcode']  = ($order_info['shipping_postcode'])?$order_info['shipping_postcode']:'';
			$this->data['destination_state']   = ($order_info['shipping_zone'])?$order_info['shipping_zone']:'';
			
				
			##################  Additional ###########
			
			$this->load->model('localisation/country');
			$this->data['countries'] = $this->model_localisation_country->getCountries();
			$this->data['reference'] = $order_id;
			
			$this->data['aramex_shipment_shipper_account'] = ($this->config->get('aramex_account_number'))?$this->config->get('aramex_account_number'):'';
			
			
			$this->data['aramex_allowed_domestic_methods'] = ($this->config->get('aramex_allowed_domestic_methods'))?$this->config->get('aramex_allowed_domestic_methods'):'';	
			$this->data['aramex_allowed_domestic_additional_services'] = ($this->config->get('aramex_allowed_domestic_additional_services'))?$this->config->get('aramex_allowed_domestic_additional_services'):'';			
			$this->data['aramex_allowed_international_methods'] = ($this->config->get('aramex_allowed_international_methods'))?$this->config->get('aramex_allowed_international_methods'):'';					
			$this->data['aramex_allowed_international_additional_services'] = ($this->config->get('aramex_allowed_international_additional_services'))?$this->config->get('aramex_allowed_international_additional_services'):'';							
			
				
			$this->data['all_allowed_domestic_methods'] = $this->model_shipping_aramex->domesticmethods();
			$this->data['all_allowed_domestic_additional_services'] = $this->model_shipping_aramex->domesticAdditionalServices();		
			$this->data['all_allowed_international_methods'] = $this->model_shipping_aramex->internationalmethods();
			$this->data['all_allowed_international_additional_services'] = $this->model_shipping_aramex->internationalAdditionalServices();	
			
			
			
			if(isset($this->request->post['product_group']) && !empty($this->request->post['product_group'])) {
					$this->data['group'] = $this->request->post['product_group'];
			}else{
					$this->data['group'] = "";
			}
			if(isset($this->request->post['product_type']) && !empty($this->request->post['product_type'])) {
					$this->data['type'] = $this->request->post['product_type'];
			}else{
					$this->data['type'] = "";
			}
			if(isset($this->request->post['payment_type']) && !empty($this->request->post['payment_type'])) {
					$this->data['pay_type'] = $this->request->post['payment_type'];
			}else{
					$this->data['pay_type'] = '';
			}
			if(isset($this->request->post['comments']) && !empty($this->request->post['comments'])) {
					$this->data['comments'] = $this->request->post['comments'];
			}else{
					$this->data['comments'] = '';
			}
			if(isset($this->request->post['mobile']) && !empty($this->request->post['mobile'])) {
					$this->data['mobile'] = $this->request->post['mobile'];
			}else{
					$this->data['mobile'] = '';
			}
			
			
		
			if(isset($this->request->post['weight_unit']) && !empty($this->request->post['weight_unit'])) {
					$getunit_classid = $this->model_sale_aramex->getWeightClassId($this->request->post['weight_unit'][$order_id]);
					$this->data['weight_unit'][$order_id] = $getunit_classid->row['unit'];
					$config_weight_class_id = $getunit_classid->row['weight_class_id'];
			}else{
					$this->data['weight_unit'][$order_id] = $this->weight->getUnit($this->config->get('config_weight_class_id'));
					$config_weight_class_id = $this->config->get('config_weight_class_id');
			}
			##################       
					
				$this->data['total'] = ($order_info['total'])?number_format($order_info['total'],2):'';

				########### product list ##########
				if (isset($this->request->post['order_product'])) {
					$order_products = $this->request->post['order_product'];
				} elseif (isset($order_id)) {
					$order_products = $this->model_sale_order->getOrderProducts($order_id);
				} else {
					$order_products = array();
				}
				$this->data['order_products'] = array();
				$weighttot = 0;
				$i = 0;
				foreach ($order_products as $order_product) {
					if (isset($order_product['order_option'])) {
						$order_option = $order_product['order_option'];
					} elseif (isset($order_id)) {
						$order_option = $this->model_sale_order->getOrderOptions($order_id, $order_product['order_product_id']);
						$product_weight_query = $this->db->query("SELECT weight, weight_class_id FROM " . DB_PREFIX . "product WHERE product_id = '" . $order_product['product_id'] . "'");
						$weight_class_query = $this->db->query("SELECT wcd.unit FROM " . DB_PREFIX . "weight_class wc LEFT JOIN " . DB_PREFIX . "weight_class_description wcd ON (wc.weight_class_id = wcd.weight_class_id) WHERE wcd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND wc.weight_class_id = '" . $product_weight_query->row['weight_class_id'] . "'");
					} else {
						$order_option = array();
					}

					if (isset($order_product['order_download'])) {
						$order_download = $order_product['order_download'];
					} elseif (isset($order_id)) {
						$order_download = $this->model_sale_order->getOrderDownloads($order_id, $order_product['order_product_id']);
					} else {
						$order_download = array();
					}
					$prodweight = $this->weight->convert($product_weight_query->row['weight'], $product_weight_query->row['weight_class_id'], $config_weight_class_id);
					$prodweight = ($prodweight * $order_product['quantity']); 
					$weighttot = ($weighttot + $prodweight);
					$i = $i + $order_product['quantity'];
				}
				$this->data['create_order_id'][] = $order_id;
				$this->data['no_of_item'][$order_id] = $i;
				$this->data['weighttot'][$order_id] = number_format($weighttot,2);
				$this->data['total'][$order_id] = number_format($order_info['total'],2);

			} 
			} // if end	
			} // if foreach end
			
						
				################## create shipment ###########
$shedule_order_id = (isset($this->request->post['order_id']))?$this->request->post['order_id']:'';
			if ($this->request->post && $shedule_order_id) 
			{ 


							$account=($this->config->get('aramex_account_number'))?$this->config->get('aramex_account_number'):'';	
							$country_code=($this->config->get('aramex_account_country_code'))?$this->config->get('aramex_account_country_code'):'';		        $response=array();
									
								$clientInfo = $this->model_sale_aramex->getClientInfo();
$date = (isset($this->request->post['date']))?$this->request->post['date']:'';
							
							$pickupDate=strtotime($date);		
							$readyTimeH=(isset($this->request->post['ready_hour']))?$this->request->post['ready_hour']:'';
							$readyTimeM=(isset($this->request->post['ready_minute']))?$this->request->post['ready_minute']:'';		
							//$readyTime=mktime(($readyTimeH-2),$readyTimeM,0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));	
							$readyTime=gmmktime(($readyTimeH),$readyTimeM,0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));
							
							$closingTimeH=(isset($this->request->post['latest_hour']))?$this->request->post['latest_hour']:'';
							$closingTimeM=(isset($this->request->post['latest_minute']))?$this->request->post['latest_minute']:'';
							//$closingTime=mktime(($closingTimeH-2),$closingTimeM,0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));
							$closingTime=gmmktime(($closingTimeH),$closingTimeM,0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));
							
							$text_weight = (isset($this->request->post['text_weight']))?$this->request->post['text_weight']:'';
							$weight_unit = (isset($this->request->post['weight_unit']))?$this->request->post['weight_unit']:'';
							
$contact =(isset($this->request->post['contact']))?html_entity_decode($this->request->post['contact']):'';
$company =(isset($this->request->post['company']))?html_entity_decode($this->request->post['company']):'';
$phone =(isset($this->request->post['phone']))?html_entity_decode($this->request->post['phone']):'';
$ext =(isset($this->request->post['ext']))?html_entity_decode($this->request->post['ext']):'';
$mobile =(isset($this->request->post['mobile']))?html_entity_decode($this->request->post['mobile']):'';
$email =(isset($this->request->post['email']))?html_entity_decode($this->request->post['email']):'';
$address =(isset($this->request->post['address']))?html_entity_decode($this->request->post['address']):'';$city = (isset($this->request->post['city']))?html_entity_decode($this->request->post['city']):'';
$state = (isset($this->request->post['state']))?html_entity_decode($this->request->post['state']):'';
$zip = (isset($this->request->post['zip']))?html_entity_decode($this->request->post['zip']):'';
$CountryCode = (isset($this->request->post['country']))?html_entity_decode($this->request->post['country']):'';
$location = (isset($this->request->post['location']))?html_entity_decode($this->request->post['location']):'';	
$comments = (isset($this->request->post['comments']))?html_entity_decode($this->request->post['comments']):'';	
$reference = (isset($this->request->post['reference']))?$this->request->post['reference']:'';	
$vehicle = (isset($this->request->post['vehicle']))?$this->request->post['vehicle']:'';
$product_group = (isset($this->request->post['product_group']))?$this->request->post['product_group']:'';
$product_type = (isset($this->request->post['product_type']))?$this->request->post['product_type']:'';$payment_type = (isset($this->request->post['payment_type']))?$this->request->post['payment_type']:'';$total_count = (isset($this->request->post['total_count']))?$this->request->post['total_count']:'';	
$total_count = (isset($this->request->post['total_count']))?$this->request->post['total_count']:'';
$status = (isset($this->request->post['status']))?$this->request->post['status']:'';
$no_shipments = (isset($this->request->post['no_shipments']))?$this->request->post['no_shipments']:'';		
		
				foreach($shedule_order_id as $id)
				{	
						$lreference  = $reference[$id];
						$lstatus 	= $status[$id];
						$items[] = array(
											'ProductGroup'	=>$product_group[$id],
											'ProductType'	=>$product_type[$id],
											'Payment'		=>$payment_type[$id],									
											'NumberOfShipments'=>$no_shipments[$id],
											'NumberOfPieces'=>$total_count[$id],									
											'ShipmentWeight'=>array('Value'=>$text_weight[$id],'Unit'=>$weight_unit[$id]),
																
										);
				} // foreach end	
                					
						try {
						
							$params = array(
							'ClientInfo'  	=> $clientInfo,
													
							'Transaction' 	=> array(
								'Reference1'			=> $lreference,
													),
													
							'Pickup'		=>array(
													'PickupContact'				=>array(
														'PersonName'			=>$contact,
														'CompanyName'			=>$company,
														'PhoneNumber1'			=>$phone,
														'PhoneNumber1Ext'		=>$ext,
														'CellPhone'				=>$mobile,
														'EmailAddress'			=>$email
													),
													'PickupAddress'				=>array(
														'Line1'					=>$address,
														'City'					=>$city,
														'StateOrProvinceCode'	=>$state,
														'PostCode'				=>$zip,
														'CountryCode'			=>$CountryCode,
													),
													
													'PickupLocation'		=>$location,
													'PickupDate'			=>$readyTime,
													'ReadyTime'				=>$readyTime,
													'LastPickupTime'		=>$closingTime,
													'ClosingTime'			=>$closingTime,
													'Comments'				=>$comments,
													'Reference1'			=>$lreference,
													'Reference2'			=>'',
													'Vehicle'				=>$vehicle,
													'Shipments'				=>array(
														'Shipment'					=>array()
													),
													'PickupItems'			=>array(
															'PickupItemDetail'=>$items,
													),
													'Status'				=>$lstatus,
												
													
												)
						);
						
		
						$baseUrl = $this->model_sale_aramex->getWsdlPath();
						$soapClient = new SoapClient($baseUrl.'/shipping.wsdl', array('trace' => 1));

						try{
						$results = $soapClient->CreatePickup($params);
						
		//print_r($results);
		if($results->HasErrors){
							if(count($results->Notifications->Notification) > 1){
								$error="";
								foreach($results->Notifications->Notification as $notify_error){
									$this->data['eRRORS'][0] = 'Aramex: ' . $notify_error->Code .' - '. $notify_error->Message."<br>";				
								}
							}else{
									$this->data['eRRORS'][0] = 'Aramex: ' . $results->Notifications->Notification->Code . ' - '. $results->Notifications->Notification->Message;
							}
							$flag = false;

						}else{

							$comment="Pickup reference number ( <strong>".$results->ProcessedPickup->ID."</strong> ).";
							$message = array(
												'notify' => 1,
												'comment' => $comment
											);
							$flag = true;
							foreach($shedule_order_id as $id)
							{				
								$this->model_sale_aramex->addOrderHistory($id, $message);
								if(($key = array_search($id, $this->data['create_order_id'])) !== false) 
								{
									unset($this->data['create_order_id'][$key]);
								}
							}
							$shipmenthistory = "<p class='amount'>Pickup reference number ( <strong>".$results->ProcessedPickup->ID."</strong> ).</p>";
							$this->session->data['success_html'][0] = $shipmenthistory;

						}
						} catch (Exception $e) {
								
								$this->data['eRRORS'][0] = $e->getMessage();
							$flag = false;								
						}
						}
						catch (Exception $e) {
								$this->data['eRRORS'][0] = $e->getMessage();
							$flag = false;
						}
					

				
			} // post end here
				
				
				
				################## create shipment end ###########
				

				if (isset($this->session->data['success_html'])) {
					$this->data['success_html'] = $this->session->data['success_html'];

					unset($this->session->data['success_html']);
				} else {
					$this->data['success_html'] = '';
				}
					$this->template = 'sale/aramex_create_bulk_schedule_pickup.tpl';
					$this->children = array(
						'common/header',
						'common/footer'
					);

					$this->response->setOutput($this->render());
		
		}
		else {
			$this->language->load('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['heading_title'] = $this->language->get('heading_title');

			$this->data['text_not_found'] = $this->language->get('text_not_found');

			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('error/not_found', '', 'SSL'),
				'separator' => ' :: '
			);

			$this->template = 'error/not_found.expand';
            $this->base = "common/base";

			$this->response->setOutput($this->render_ecwig());
		}
	}
	
}
?>