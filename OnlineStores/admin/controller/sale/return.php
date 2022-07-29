<?php    

class ControllerSaleReturn extends Controller
{
	private $error = array();
   
	public function getKanbanCards()
    {
        $this->load->model('sale/return');
        $this->load->model('localisation/return_status');

        $order_statuses = $this->model_localisation_return_status->getReturnStatuses();
        $orders = $this->model_sale_return->getReturns();

        foreach ( $order_statuses as $index => $order_status )
        {
            $order_statuses[$index]['orders'] = array();

            foreach ( $orders as $order )
            {
                if ( $order['return_status_id'] == $order_status['return_status_id'] )
                {
                    $order_statuses[$index]['orders'][] = $order;
                }
            }
        }

        $data['order_statuses'] = $order_statuses;
        $data['orders'] = $orders;

        $this->response->setOutput( json_encode( $data ) );
        return;
    }

    public function dtDelete()
    {
		$this->load->model('sale/order');
		$this->load->model('sale/return');
		$this->load->model('catalog/product');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

		$id_s = $this->request->post['id'];
		
		if (! is_array($id_s)) {
			$id_s = [$id_s];
		}

		foreach ($id_s as $return_id) {
			$return_details = $this->model_sale_return->getReturn($return_id);

			if (! empty($return_details['product_id']) && ! empty($return_details['quantity'])) {
				$this->model_catalog_product->deductQty($return_details['product_id'], $return_details['quantity']);
			}

			if ($seller_id = $this->model_catalog_product->isProductByAMutliSeller($return_details['product_id'])) {
				if ($test = $this->MsLoader->MsBalance->sellerBalanceExistsForOrderProduct($return_details['order_id'], $seller_id, $return_details['product_id'])) {
					$order_product = $this->model_sale_order->getOrderProductById($return_details['order_id'], $return_details['product_id']);

					$ms_balance_data = [
						"order_id"		=> $return_details['order_id'],
						"product_id"	=> $return_details['product_id'],
						"balance_type"	=> '1',
						"amount"		=> $order_product['total'],
						"description"	=> "Removed Return # {$return_id}"
					];
	
					$this->MsLoader->MsBalance->addBalanceEntry($seller_id, $ms_balance_data);
				}
			}

			if ( $this->model_sale_return->deleteReturn( (int) $return_id ) ) {
				$result_json['success'] = '1';
				$result_json['success_msg'] = ':)';
			} else {
				$result_json['success'] = '0';
				$result_json['error'] = ':(';
				break;
			}
		}

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function dtHandler() {
        $this->load->model('sale/return');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            1 => 'return_id',
            2 => 'order_id',
            3 => 'customer',
            4 => 'product',
            5 => 'model',
            6 => 'status',
            7 => 'date_added',
            8 => 'date_modified',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_sale_return->dtHandler($start, $length, $search, $orderColumn, $orderType);
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData), // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $records,   // total data array
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

  	public function index() {
		$this->language->load('sale/return');
		 
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/return');
		
    	$this->getList();
  	}
  
  	public function insert() {
		$this->language->load('sale/return');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/return');
		$this->load->model('sale/order');
		$this->load->model('catalog/product');
			
		if ( $this->request->server['REQUEST_METHOD'] == 'POST')
        {

            if ( ! $this->validateForm() )
            {
                $response['success'] = '0';
                $response['errors'] = $this->error;

                $this->response->setOutput(json_encode($response));
                return;
			}
		
			$products = $this->request->post['return_products'];

			$config_product_quantity_update_checker = $this->config->get('config_product_quantity_update_checker');
			$quantity_product_update_status_selector = $this->config->get('product_quantity_update_status_selector');

			if($config_product_quantity_update_checker){
				//Return quantity back to product stock quantity
				foreach ($products as $product) {
					//check if return status matches product update status selector
					if($quantity_product_update_status_selector == $this->request->post['return_status_id']){
						$this->model_catalog_product->addQty($product['id'], $product['quantity']);
						$this->request->post['is_product_quantity_added'] = '1' ;
					}
					/** if the product is by a multiseller, deduct the total from his balance if it exists. */
					if ($seller_id = $this->model_catalog_product->isProductByAMutliSeller($product['id'])) {
						if ($this->MsLoader->MsBalance->sellerBalanceExistsForOrderProduct($this->request->post['order_id'], $seller_id, $product['id']) ) {
							$order_product = $this->model_sale_order->getOrderProductById($this->request->post['order_id'], $product['id'] );

							$ms_balance_data = [
								"order_id"		=> $this->request->post['order_id'],
								"product_id"	=> $product['id'],
								"balance_type"	=> '2',
								"amount"		=> "-{$order_product['total']}",
								"description"	=> "Returned Product # {$product['id']} Of Order # {$this->request->post['order_id']}"
							];
			
							$this->MsLoader->MsBalance->addBalanceEntry($seller_id, $ms_balance_data);
						}
					}
				}
			}else{
				$response['success'] = '0';
                $response['errors']  = $this->language->get('error_quantity_update_checker');

                $this->response->setOutput(json_encode($response));
                return;
			}
			

			//Add return data to DB
			$return_id = $this->model_sale_return->addReturn($this->request->post);
                        
                        //make refund in order paid using PayPal
                        $orderDetails = $this->model_sale_order->getOrder($this->request->post['order_id']);

            if (\Extension::isInstalled('paypal') && $orderDetails['payment_method'] == 'PayPal') {

                if($this->request->post["return_action_id"] == 1) {
                    $refundAmount = 0;
                    foreach ($products as $product) {
                        $order_product = $this->model_sale_order->getOrderProductById($this->request->post['order_id'], $product['id']);
                        $productPrice = $order_product['price'] * $product['quantity'];
                        $refundAmount += $productPrice;
                    }

                    $this->load->model('payment/paypal');

                    $refundArray['order_id'] = $orderDetails['order_id'];
                    $refundArray['amount'] = $refundAmount;
                    $refundArray['currency_code'] = $orderDetails['currency_code'];

                    $this->model_payment_paypal->handelRefund($refundArray);

                }
            }


            $this->session->data['success'] = $this->language->get('text_success');
			
            $response['success'] = '1';
            $response['success_msg'] = $this->language->get('text_success');

            $response['redirect'] = '1';
            $response['to'] = (string) $this->url->link('sale/return/info', 'return_id=' . $return_id, 'SSL');

            $this->response->setOutput(json_encode($response));
            return;
		}

		//le/return
		$this->data['links'] = [
		    'submit' => $this->url->link('sale/return/insert', '', 'SSL'),
		    'cancel' => $this->url->link('sale/return', '', 'SSL'),
        ];
    	
    	$this->getForm();
  	} 
   
  	public function update() {

        $this->language->load('sale/return');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/return');
		$this->load->model('sale/order');
		$this->load->model('catalog/product');
		
    	if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{
            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                return;
			}
			$quantity_product_update_status_selector = $this->config->get('product_quantity_update_status_selector');
			$products = $this->request->post['return_products'];
			if(count($products) > 0){
				foreach($products as $product){
					if($quantity_product_update_status_selector == $this->request->post['return_status_id']){
						$check_product_quantity_added = $this->model_sale_return->checkProdQuantityAdded($product['id'],$this->request->get['return_id']);
						if(!$check_product_quantity_added){
							$this->model_catalog_product->addQty($product['id'], $product['quantity']);
							$this->request->post['is_product_quantity_added'] = '1' ;
						}
					}
				}
			}
			
            $this->model_sale_return->editReturn($this->request->get['return_id'], $this->request->post);

            $orderDetails = $this->model_sale_order->getOrder($this->request->post['order_id']);

            if (\Extension::isInstalled('paypal') && $orderDetails['payment_method'] == 'PayPal') {

                if($this->request->post["return_action_id"] == 1) {

                    $refundAmount = 0;
                    foreach ($products as $product) {
                        $order_product = $this->model_sale_order->getOrderProductById($this->request->post['order_id'], $product['id']);
                        $productPrice = $order_product['price'] * $product['quantity'];
                        $refundAmount += $productPrice;
                    }

                    $this->load->model('payment/paypal');

                    $refundArray['order_id'] = $orderDetails['order_id'];
                    $refundArray['amount'] = $refundAmount;
                    $refundArray['currency_code'] = $orderDetails['currency_code'];

                    $this->model_payment_paypal->handelRefund($refundArray);
                }
            }
            
            // microsoft dynamics create sales order return
            $this->load->model('module/microsoft_dynamics');
            if ($this->model_module_microsoft_dynamics->isActive()) {
                $this->model_module_microsoft_dynamics->createReturnOrder($this->request->get['return_id'], $this->request->post['return_status_id']);
            }
	  		
			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
	
            $result_json['success'] = '1';
            $result_json['redirect'] = '1';
            $result_json['to'] = (string) $this->url->link('sale/return/info', 'return_id='. $this->request->get['return_id'], 'SSL');

            $this->response->setOutput(json_encode($result_json));
            
            return;

		}

        $this->data['links'] = [
            'submit' => $this->url->link('sale/return/update', 'return_id=' . $this->request->get['return_id'], 'SSL'),
            'cancel' => $this->url->link('sale/return', '', 'SSL'),
        ];
    
    	$this->getForm();
  	}   

  	public function delete() {
		$this->language->load('sale/return');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/return');
			
    	if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $return_id) {
				$this->model_sale_return->deleteReturn($return_id);
			}
			
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_return_id'])) {
				$url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
			}
			
			if (isset($this->request->get['filter_order_id'])) {
				$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
			}
			
			if (isset($this->request->get['filter_customer'])) {
				$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_product'])) {
				$url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}
												
			if (isset($this->request->get['filter_return_status_id'])) {
				$url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
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
			
			$this->redirect($this->url->link('sale/return', '', 'SSL'));
    	}
    
    	$this->getList();
  	}  
    
  	protected function getList() {

  		$this->load->model('localisation/return_status');
  		$this->data['return_statuses'] = $this->model_localisation_return_status->getReturnStatuses();

		if (isset($this->request->get['filter_return_id'])) {
			$filter_return_id = $this->request->get['filter_return_id'];
		} else {
			$filter_return_id = null;
		}
		
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

		if (isset($this->request->get['filter_product'])) {
			$filter_product = $this->request->get['filter_product'];
		} else {
			$filter_product = null;
		}

		if (isset($this->request->get['filter_model'])) {
			$filter_model = $this->request->get['filter_model'];
		} else {
			$filter_model = null;
		}
		
		if (isset($this->request->get['filter_return_status_id'])) {
			$filter_return_status_id = $this->request->get['filter_return_status_id'];
		} else {
			$filter_return_status_id = null;
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
			$sort = 'r.return_id'; 
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

		if (isset($this->request->get['filter_return_id'])) {
			$url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
		}
		
		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}
		
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_product'])) {
			$url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}
													
		if (isset($this->request->get['filter_return_status_id'])) {
			$url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
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
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/return', '', 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['insert'] = $this->url->link('sale/return/insert', '', 'SSL');
		$this->data['delete'] = $this->url->link('sale/return/delete', '', 'SSL');

		$this->data['returns'] = array();

		$data = array(
			'filter_return_id'        => $filter_return_id, 
			'filter_order_id'         => $filter_order_id, 
			'filter_customer'         => $filter_customer, 
			'filter_product'          => $filter_product, 
			'filter_model'            => $filter_model, 
			'filter_return_status_id' => $filter_return_status_id, 
			'filter_date_added'       => $filter_date_added,
			'filter_date_modified'    => $filter_date_modified,
			'sort'                    => $sort,
			'order'                   => $order,
			'start'                   => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'                   => $this->config->get('config_admin_limit')
		);
		
		$return_total = $this->model_sale_return->getTotalReturns($data);
	
		$results = $this->model_sale_return->getReturns($data);
 
    	foreach ($results as $result) {
			$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_view'),
				'href' => $this->url->link('sale/return/info', 'return_id=' . $result['return_id'] . $url, 'SSL')
			);
					
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('sale/return/update', 'return_id=' . $result['return_id'] . $url, 'SSL')
			);
						
			$this->data['returns'][] = array(
				'return_id'     => $result['return_id'],
				'order_id'      => $result['order_id'],
				'customer'      => $result['customer'],
				'product'       => $result['product'],
				'model'         => $result['model'],
				'status'        => $result['status'],
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),	
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),				
				'selected'      => isset($this->request->post['selected']) && in_array($result['return_id'], $this->request->post['selected']),
				'action'        => $action
			);
		}	
		
		$this->data['token'] = null;

		if (isset($this->session->data['error'])) {
			$this->data['error_warning'] = $this->session->data['error'];
			
			unset($this->session->data['error']);
		} elseif (isset($this->error['warning'])) {
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

		if (isset($this->request->get['filter_return_id'])) {
			$url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
		}
		
		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}
		
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_product'])) {
			$url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}
											
		if (isset($this->request->get['filter_return_status_id'])) {
			$url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
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
		
		$this->data['sort_return_id'] = $this->url->link('sale/return?sort=r.return_id' . $url, 'SSL');
		$this->data['sort_order_id'] = $this->url->link('sale/return?sort=r.order_id' . $url, 'SSL');
		$this->data['sort_customer'] = $this->url->link('sale/return?sort=customer' . $url, 'SSL');
		$this->data['sort_product'] = $this->url->link('sale/return?sort=product' . $url, 'SSL');
		$this->data['sort_model'] = $this->url->link('sale/return?sort=model' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('sale/return?sort=status' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('sale/return?sort=r.date_added' . $url, 'SSL');
		$this->data['sort_date_modified'] = $this->url->link('sale/return?sort=r.date_modified' . $url, 'SSL');
		
		$url = '';

		if (isset($this->request->get['filter_return_id'])) {
			$url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
		}
		
		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}
		
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_product'])) {
			$url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}
											
		if (isset($this->request->get['filter_return_status_id'])) {
			$url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
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
		$pagination->total = $return_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/return?page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();

		$this->data['filter_return_id'] = $filter_return_id;
		$this->data['filter_order_id'] = $filter_order_id;
		$this->data['filter_customer'] = $filter_customer;
		$this->data['filter_product'] = $filter_product;
		$this->data['filter_model'] = $filter_model;
		$this->data['filter_return_status_id'] = $filter_return_status_id;
		$this->data['filter_date_added'] = $filter_date_added;
		$this->data['filter_date_modified'] = $filter_date_modified;

		$this->load->model('localisation/return_status');
		
    	$this->data['return_statuses'] = $this->model_localisation_return_status->getReturnStatuses();
		
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

        $total = $this->model_sale_return->getTotalReturns();

        if ($total == 0){
            $this->template = 'sale/return/empty.expand';
        }else{
            $this->template = 'sale/return_list.expand';
        }
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
  	}

  	protected function getForm()
    {
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/return', '', 'SSL'),
      		'separator' => ' :: '
   		);
		  
        $this->data['breadcrumbs'][] = array(
            'text'      => ! isset($this->request->get['return_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href'      => $this->url->link('sale/return', '', 'SSL'),
            'separator' => ' :: '
        );

    	if (isset($this->request->get['return_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$return_info = $this->model_sale_return->getReturn($this->request->get['return_id']);
      		//echo '<pre>'; print_r($return_info); die();
    	}

    	if (isset($this->request->post['order_id'])) {
      		$this->data['order_id'] = $this->request->post['order_id'];
		} elseif (!empty($return_info)) { 
			$this->data['order_id'] = $return_info['order_id'];
		} else {
      		$this->data['order_id'] = '';
    	}	
		
    	if (isset($this->request->post['date_ordered'])) {
      		$this->data['date_ordered'] = $this->request->post['date_ordered'];
		} elseif (!empty($return_info)) { 
			$this->data['date_ordered'] = $return_info['date_ordered'];
		} else {
      		$this->data['date_ordered'] = '';
    	}	

		if (isset($this->request->post['customer'])) {
			$this->data['customer'] = $this->request->post['customer'];
		} elseif (!empty($return_info)) {
			$this->data['customer'] = $return_info['customer'];
		} else {
			$this->data['customer'] = '';
		}
				
		if (isset($this->request->post['customer_id'])) {
			$this->data['customer_id'] = $this->request->post['customer_id'];
		} elseif (!empty($return_info)) {
			$this->data['customer_id'] = $return_info['customer_id'];
		} else {
			$this->data['customer_id'] = '';
		}
			
    	if (isset($this->request->post['firstname'])) {
      		$this->data['firstname'] = $this->request->post['firstname'];
		} elseif (!empty($return_info)) { 
			$this->data['firstname'] = $return_info['firstname'];
		} else {
      		$this->data['firstname'] = '';
    	}	
		
    	if (isset($this->request->post['lastname'])) {
      		$this->data['lastname'] = $this->request->post['lastname'];
		} elseif (!empty($return_info)) { 
			$this->data['lastname'] = $return_info['lastname'];
		} else {
      		$this->data['lastname'] = '';
    	}
		
    	if (isset($this->request->post['email'])) {
      		$this->data['email'] = $this->request->post['email'];
		} elseif (!empty($return_info)) { 
			$this->data['email'] = $return_info['email'];
		} else {
      		$this->data['email'] = '';
    	}
		
    	if (isset($this->request->post['telephone'])) {
      		$this->data['telephone'] = $this->request->post['telephone'];
		} elseif (!empty($return_info)) { 
			$this->data['telephone'] = $return_info['telephone'];
		} else {
      		$this->data['telephone'] = '';
    	}
		
		if ( !empty($this->request->post['return_products']) ) {
			$this->data['return_products'] = $this->request->post['return_products'];
		} elseif (!empty($return_info['products'])) {
			$this->data['return_products'] = $return_info['products'];
		} else {
			$this->data['return_products'] = [];
		}

		// if (isset($this->request->post['product'])) {
		// 	$this->data['product'] = $this->request->post['product'];
		// } elseif (!empty($return_info)) {
		// 	$this->data['product'] = $return_info['product'];
		// } else {
		// 	$this->data['product'] = '';
		// }	
			
		// if (isset($this->request->post['product_id'])) {
		// 	$this->data['product_id'] = $this->request->post['product_id'];
		// } elseif (!empty($return_info)) {
		// 	$this->data['product_id'] = $return_info['product_id'];
		// } else {
		// 	$this->data['product_id'] = '';
		// }	
		
		// if (isset($this->request->post['model'])) {
		// 	$this->data['model'] = $this->request->post['model'];
		// } elseif (!empty($return_info)) {
		// 	$this->data['model'] = $return_info['model'];
		// } else {
		// 	$this->data['model'] = '';
		// }

		// if (isset($this->request->post['quantity'])) {
		// 	$this->data['quantity'] = $this->request->post['quantity'];
		// } elseif (!empty($return_info)) {
		// 	$this->data['quantity'] = $return_info['quantity'];
		// } else {
		// 	$this->data['quantity'] = '';
		// }
		
		if (isset($this->request->post['opened'])) {
			$this->data['opened'] = $this->request->post['opened'];
		} elseif (!empty($return_info)) {
			$this->data['opened'] = $return_info['opened'];
		} else {
			$this->data['opened'] = '';
		}
		
		if (isset($this->request->post['return_reason_id'])) {
			$this->data['return_reason_id'] = $this->request->post['return_reason_id'];
		} elseif (!empty($return_info)) {
			$this->data['return_reason_id'] = $return_info['return_reason_id'];
		} else {
			$this->data['return_reason_id'] = '';
		}
							
		$this->load->model('localisation/return_reason');
		
		$this->data['return_reasons'] = $this->model_localisation_return_reason->getReturnReasons();
	
		if (isset($this->request->post['return_action_id'])) {
			$this->data['return_action_id'] = $this->request->post['return_action_id'];
		} elseif (!empty($return_info)) {
			$this->data['return_action_id'] = $return_info['return_action_id'];
		} else {
			$this->data['return_action_id'] = '';
		}				
				
		$this->load->model('localisation/return_action');
		
		$this->data['return_actions'] = $this->model_localisation_return_action->getReturnActions();

		if (isset($this->request->post['comment'])) {
			$this->data['comment'] = $this->request->post['comment'];
		} elseif (!empty($return_info)) {
			$this->data['comment'] = $return_info['comment'];
		} else {
			$this->data['comment'] = '';
		}
						
		if (isset($this->request->post['return_status_id'])) {
			$this->data['return_status_id'] = $this->request->post['return_status_id'];
		} elseif (!empty($return_info)) {
			$this->data['return_status_id'] = $return_info['return_status_id'];
		} else {
			$this->data['return_status_id'] = '';
		}
		
		$this->load->model('localisation/return_status');
		
		$this->data['return_statuses'] = $this->model_localisation_return_status->getReturnStatuses();
						
		$this->template = 'sale/return_form.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render_ecwig());
	}
	
	public function info() {
		$this->load->model('sale/return');
    	
		if (isset($this->request->get['return_id'])) {
			$return_id = $this->request->get['return_id'];
		} else {
			$return_id = 0;
		}
				
		$return_info = $this->model_sale_return->getReturn($return_id);
		
		if ($return_info) {
			$this->language->load('sale/return');
		
			$this->document->setTitle($this->language->get('heading_title'));
			
			$this->data['heading_title'] = $this->language->get('heading_title');

			
			$url = '';
			
			if (isset($this->request->get['filter_return_id'])) {
				$url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
			}
			
			if (isset($this->request->get['filter_order_id'])) {
				$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
			}
			
			if (isset($this->request->get['filter_customer'])) {
				$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_product'])) {
				$url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}
												
			if (isset($this->request->get['filter_return_status_id'])) {
				$url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
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
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('sale/return', '', 'SSL'),
				'separator' => ' :: '
			);
			  
            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('breadcrumb_info'),
                'href'      => $this->url->link('sale/return', '', 'SSL'),
                'separator' => ' :: '
            );

			$this->data['cancel'] = $this->url->link('sale/return', '', 'SSL');
			
			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($return_info['order_id']);
			$order_products = $this->model_sale_order->getOrderProducts($return_info['order_id']);

			$this->data['token'] = null;
			
			$this->data['return_id'] = $return_info['return_id'];
			$this->data['order_id'] = $return_info['order_id'];
									
			if ($return_info['order_id'] && $order_info) {
				$this->data['order'] = $this->url->link('sale/order/info', 'order_id=' . $return_info['order_id'], 'SSL');
			} else {
				$this->data['order'] = '';
			}
			
			$this->data['date_ordered'] = date($this->language->get('date_format_short'), strtotime($return_info['date_ordered']));
			$this->data['firstname'] = $return_info['firstname'];
			$this->data['lastname'] = $return_info['lastname'];
						
			if ($return_info['customer_id']) {
				$this->data['customer'] = $this->url->link('sale/customer/update', 'customer_id=' . $return_info['customer_id'], 'SSL');
			} else {
				$this->data['customer'] = '';
			}
			
			$this->data['email'] = $return_info['email'];
			$this->data['telephone'] = $return_info['telephone'];
			
			$this->load->model('localisation/return_status');

			$return_status_info = $this->model_localisation_return_status->getReturnStatus($return_info['return_status_id']);

			if ($return_status_info) {
				$this->data['return_status'] = $return_status_info['name'];
			} else {
				$this->data['return_status'] = '';
			}		

			$this->load->model('catalog/product');
			
			$querySMSModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'smshare'");
			if ( $querySMSModule->num_rows )
			{
				$this->data['smsapp'] = true;
			}

			$this->data['date_added'] = date($this->language->get('date_format_short'), strtotime($return_info['date_added']));
            $this->data['original_date_added'] = $return_info['date_added'];
			$this->data['date_modified'] = date($this->language->get('date_format_short'), strtotime($return_info['date_modified']));
            $this->data['product_object'] = $product_object = $this->model_catalog_product->getProduct($return_info['product_id']);
            $this->data['product_image'] = \Filesystem::getUrl('image/' . $product_object['image']);
			$this->data['product'] = $return_info['product'];
			$this->data['model'] = $return_info['model'];
			$this->data['quantity'] = $return_info['quantity'];


			//Check if old version - use only return table..
			if( !empty($return_info['product_id']) ) {
				foreach ($order_products as $order_product) {
					if($return_info['product_id'] == $order_product['product_id']){
						$this->data['price'] = $order_product['price'];
						break;
					}	
				}
			}
			else{ // if empty then this is the new version - use return_product table
				foreach ($return_info['products'] as $key => $product) {
					foreach ($order_products as $order_product) {
						if($product['product_id'] == $order_product['product_id']){
							$return_info['products'][$key]['price'] = $order_product['price'];
							break;
						}	
					}
				}
			}

			//For new DB Structure - return_product table
			$this->data['products'] = $return_info['products'];

            $this->data['return_histories'] = $this->model_sale_return->getReturnHistories( $return_info['return_id'] , 0, 6 );

			$this->load->model('localisation/return_reason');

			$return_reason_info = $this->model_localisation_return_reason->getReturnReason($return_info['return_reason_id']);

			if ($return_reason_info) {
				$this->data['return_reason'] = $return_reason_info['name'];
			} else {
				$this->data['return_reason'] = '';
			}

			$this->data['opened_state'] = $return_info['opened'];
			$this->data['opened'] = $return_info['opened'] ? $this->language->get('text_yes') : $this->language->get('text_no');
			$this->data['comment'] = nl2br($return_info['comment']);
			
			$this->load->model('localisation/return_action');
			
			$this->data['return_actions'] = $this->model_localisation_return_action->getReturnActions(); 
			
			$this->data['return_action_id'] = $return_info['return_action_id'];

			$this->data['return_statuses'] = $this->model_localisation_return_status->getReturnStatuses();				
			
			$this->data['return_status_id'] = $return_info['return_status_id'];
		
			$this->template = 'sale/return_info.expand';
			$this->children = array(
				'common/header',
				'common/footer'
			);
					
			$this->response->setOutput($this->render());		
		} else {
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
		
  	
    private function validateForm()
    {
    	if ( ! $this->user->hasPermission('modify', 'sale/return') )
        {
      		$this->error['error'] = $this->language->get('error_permission');
    	}

    	if ((utf8_strlen($this->request->post['firstname']) < 1) || (utf8_strlen($this->request->post['firstname']) > 32)) {
      		$this->error['firstname'] = $this->language->get('error_firstname');
    	}

    	if ((utf8_strlen($this->request->post['lastname']) < 1) || (utf8_strlen($this->request->post['lastname']) > 32)) {
      		$this->error['lastname'] = $this->language->get('error_lastname');
    	}

    	if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])) {
      		$this->error['email'] = $this->language->get('error_email');
    	}
		
    	if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
      		$this->error['telephone'] = $this->language->get('error_telephone');
    	}
		
    	//Check if product to be returned Array is here & not Empty
		if ( !isset($this->request->post['return_products']) || empty($this->request->post['return_products']) ) {
			$this->error['product'] = $this->language->get('error_product');
		}	
		else{
			$products = $this->request->post['return_products'];
			
			//Check quantity for each product:   not Zero and less than quantity in Order
			foreach ($products as $key => $product) {
				if( !empty($product['quantity']) && ! empty($product['id']) ){
					$orderProductQuantity = $this->model_sale_order->getOrderProductById($this->request->post['order_id'], $product['id'])['quantity'] ?? 0;
					if ((int) $product['quantity'] > $orderProductQuantity) {
						$this->error['product'.$product['id']] = sprintf( $this->language->get('error_quantity2') , $product['name'] ) ;
					}
				}else{
					$this->error['product'.$product['id']] = sprintf( $this->language->get('error_quantity') , $product['name'] ) ;
				}
			}			
		}

		// if ((utf8_strlen($this->request->post['product']) < 1) || (utf8_strlen($this->request->post['product']) > 255)) {
		// 	$this->error['product'] = $this->language->get('error_product');
		// }	
		/*
		if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
			$this->error['model'] = $this->language->get('error_model');
		}	*/

		if (empty($this->request->post['return_reason_id'])) {
			$this->error['reason'] = $this->language->get('error_reason');
		}	
				
		if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
  	}    

  	protected function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'sale/return')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}	
	  	 
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}  
  	} 
	
	public function action() {
		$this->language->load('sale/return');
		
		$json = array();
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (!$this->user->hasPermission('modify', 'sale/return')) {
				$json['error'] = $this->language->get('error_permission');
			}
		
			if (!$json) { 
				$this->load->model('sale/return');
			
				$json['success'] = $this->language->get('text_success');
				
				$this->model_sale_return->editReturnAction($this->request->get['return_id'], $this->request->post['return_action_id']);
			}
		}
		
		$this->response->setOutput(json_encode($json));	
	}
		
	public function history() {
    	$this->language->load('sale/return');

		$this->data['error'] = '';
		$this->data['success'] = '';
				
		$this->load->model('sale/return');
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST')
        {

            if ( ! $this->request->get['return_id'] )
            {
                return false;
            }
			
            if ( !$this->user->hasPermission('modify', 'sale/return') )
            { 
				$this->data['error'] = $this->language->get('error_permission');

			}
			
			if ( ! $this->data['error'])
            { 
				$return_info = $this->model_sale_return->getReturn($this->request->get['return_id'] );
				if ($return_info) {
					$this->load->model('catalog/product');
					$this->load->model('sale/order');
					$return_products = $return_info['products'];
					$quantity_product_update_status_selector = $this->config->get('product_quantity_update_status_selector');
					if(count($return_products) > 0){
					    $totalBalanceWillAddedToCustomer = 0;
						foreach($return_products as $product){
							if($quantity_product_update_status_selector == $this->request->post['return_status_id']){
								$check_product_quantity_added = $product['is_product_quantity_added'];
								if(!$check_product_quantity_added){
									$this->model_catalog_product->addQty($product['product_id'], $product['quantity']);
									$this->model_sale_return->updateQtyAdded(1,$this->request->get['return_id'],$product['product_id']);
								}
							}
							// check if admin choose add amount to customer balance
							if($this->request->post['add_amount_to_customer'] == 1){
                                // check if amount not added to customer before
                                if(!$product['is_amount_added_to_customer']){
                                    // get product amount from order product table
                                    $product_price_for_one_pice = $this->model_sale_order->getOrderProductPrice($return_info['order_id'],$product['product_id']);
                                    $totalBalanceWillAddedToCustomer +=  ($product_price_for_one_pice * $product['quantity']);
                                    // update balance added to customer
                                    $this->model_sale_return->updateCustomerBalanceAdded(1,$this->request->get['return_id'],$product['product_id']);
                                }
                            }

						}
                        // check if admin choose add amount to customer balance
                        if($this->request->post['add_amount_to_customer'] == 1){
                            $this->load->model('sale/customer');
                            $this->model_sale_customer->addTransaction(
                                $return_info['customer_id'],
                                sprintf( $this->language->get('text_balance_added') , $this->request->get['return_id'] ),
                                $totalBalanceWillAddedToCustomer
                            );
                        }

					}
					
				}
                $this->model_sale_return->addReturnHistory($this->request->get['return_id'], $this->request->post);
                
                // microsoft dynamics create sales order return
                $this->load->model('module/microsoft_dynamics');
                if ($this->model_module_microsoft_dynamics->isActive()) {
                    $this->model_module_microsoft_dynamics->createReturnOrder($this->request->get['return_id'], $this->request->post['return_status_id']);
                }
            
				$this->data['success'] = $this->language->get('text_success');
			}

            $this->load->model('sale/return');
            $this->data['return_histories'] = $this->model_sale_return->getReturnHistories($this->request->get['return_id'], 0, 6);
            $this->data['date_added'] = $this->request->post['date_added'];
            $this->template = 'sale/return_histories_snippet.expand';


            $output = $this->render_ecwig();
            ob_clean();

            $result_json['histories'] = $output;

            $this->response->setOutput(json_encode($result_json));
            return;

		}
				
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_notify'] = $this->language->get('column_notify');
		$this->data['column_comment'] = $this->language->get('column_comment');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}  
		
		$this->data['histories'] = array();
			
		$results = $this->model_sale_return->getReturnHistories($this->request->get['return_id'], ($page - 1) * 10, 10);
      		
		foreach ($results as $result) {
        	$this->data['histories'][] = array(
				'notify'     => $result['notify'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
				'status'     => $result['status'],
				'comment'    => nl2br($result['comment']),
        		'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))

        	);
      	}			
		
		$history_total = $this->model_sale_return->getTotalReturnHistories($this->request->get['return_id']);
			
		$pagination = new Pagination();
		$pagination->total = $history_total;
		$pagination->page = $page;
		$pagination->limit = 10; 
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/return/history', 'return_id=' . $this->request->get['return_id'] . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
		
		$this->template = 'sale/return_history.tpl';		
		
		$this->response->setOutput($this->render());
  	}		
}
