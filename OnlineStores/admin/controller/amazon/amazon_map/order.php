<?php

class ControllerAmazonAmazonMapOrder extends Controller {
	private $error = array();
	private $route = 'amazon/amazon_map/order';
	private $common = 'amazon/amazon_map/common';

	public function __construct($registory) {
		parent::__construct($registory);
		$this->load->model($this->route);
		$this->_amazonMapOrder = $this->model_amazon_amazon_map_order;

		$this->load->language($this->common);
		$this->load->language($this->route);

    }

    public function index() {
		
		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->addScript('view/javascript/amazon_connector/webkul_amazon_connector.js');

		$data['account_id'] = $this->session->data['amazon_account_id'] =$this->request->get['account_id'];
	
		$data['import_from_ebay'] 	= $this->url->link('amazon_map/order', 'token=' . $this->session->data['token'] . $url, true);
		$data['import_order_tab'] = $this->url->link($this->route.'/import?account_id='.$data['account_id'], '', true);

		$data['import_order'] = array();

		$filter_data = array(
			'account_id' 					=> $account_id,
			'oc_order_id'					=> $filter_oc_order_id,
			'amazon_order_id'			=> $filter_amazon_order_id,
			'filter_buyer_name'		=> $filter_buyer_name,
			'filter_buyer_email'  => $filter_buyer_email,
			'filter_order_total'	=> $filter_order_total,
			'filter_date_added'		=> $filter_date_added,
			'filter_order_status'	=> $filter_order_status,
			'sort'  							=> $sort,
			'order' 							=> $order,
			'start' 							=> ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' 							=> $this->config->get('config_limit_admin')
		);

		$getAccountEntry = $this->Amazonconnector->getAccountDetails(array('account_id' => $account_id));
		$data['total_imported_order'] = $this->_amazonMapOrder->getOrderTempData(array('account_id' => $account_id));

		$amazonOrderTotal = $this->_amazonMapOrder->getTotalOcAmazonOrderMap($filter_data);

		$results 					= $this->_amazonMapOrder->getOcAmazonOrderMap($filter_data);

		if($results){
			foreach ($results as $result) {
				/**
				* Get product Variations
				*/
				$ordered_product 				= array();
				// $result['option_id'] 	= $getGlobalOption['option_id'];
				// $product_option 			= $this->_amazonMapProduct->getProductOptions($result);
				//
				// if(!empty($product_option)){
				// 	foreach ($product_option as $key => $opt_value) {
				// 		$ordered_product[] 	= array(
				// 			'name' => $opt_value['value_name'],
				// 			'asin' => $opt_value['id_value'],
				// 		);
				// 	}
				// }

				$data['import_orders'][] = array(
					'map_id' 							=> $result['map_id'],
					'oc_order_id' 				=> $result['oc_order_id'],
					'amazon_order_id' 		=> $result['amazon_order_id'],
					'ordered_products'		=> $ordered_product,
					'customer_name'	 			=> $result['firstname'].' '.$result['lastname'],
					'customer_email'	 		=> $result['email'],
					'total'	 							=> $result['total'],
					'amazon_order_status' => $result['amazon_order_status'],
					'view'								=> $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id='.$result['oc_order_id'], true),
				);
			}
		}

		$data['token'] 	= $this->session->data['token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		$url .= '&status=account_order_map';

		if (isset($this->request->get['filter_oc_order_id'])) {
			$url .= '&filter_oc_order_id=' . $this->request->get['filter_oc_order_id'];
		}

		if (isset($this->request->get['filter_amazon_order_id'])) {
			$url .= '&filter_amazon_order_id=' . $this->request->get['filter_amazon_order_id'];
		}

		if (isset($this->request->get['filter_buyer_name'])) {
			$url .= '&filter_buyer_name=' . urlencode(html_entity_decode($this->request->get['filter_buyer_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_buyer_email'])) {
			$url .= '&filter_buyer_email=' . urlencode(html_entity_decode($this->request->get['filter_buyer_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_total'])) {
			$url .= '&filter_order_total=' . $this->request->get['filter_order_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . urlencode(html_entity_decode($this->request->get['filter_order_status'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['account_id'])) {
			$url .= '&account_id=' . $this->request->get['account_id'];
			$data['clear_order_filter'] 	= $this->url->link('amazon_map/account/edit', 'token=' . $this->session->data['token'] .'&account_id=' . $this->request->get['account_id']. '&status=account_order_map', true);
		}


		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_order_map')) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_map_id'] = $this->url->link('amazon_map/account/edit', 'token=' . $this->session->data['token'] . '&sort=id' . $url, true);
		$data['sort_oc_product_id'] = $this->url->link('amazon_map/account/edit', 'token=' . $this->session->data['token'] . '&sort=apm.oc_product_id' . $url, true);
		$data['sort_oc_name'] = $this->url->link('amazon_map/account/edit', 'token=' . $this->session->data['token'] . '&sort=product_name' . $url, true);
		$data['sort_oc_price'] = $this->url->link('amazon_map/account/edit', 'token=' . $this->session->data['token'] . '&sort=p.price' . $url, true);
		$data['sort_oc_quantity'] = $this->url->link('amazon_map/account/edit', 'token=' . $this->session->data['token'] . '&sort=p.quantity' . $url, true);

		$url = '';

		$url .= '&status=account_order_map';

		if (isset($this->request->get['filter_oc_order_id'])) {
			$url .= '&filter_oc_order_id=' . $this->request->get['filter_oc_order_id'];
		}

		if (isset($this->request->get['filter_amazon_order_id'])) {
			$url .= '&filter_amazon_order_id=' . $this->request->get['filter_amazon_order_id'];
		}

		if (isset($this->request->get['filter_buyer_name'])) {
			$url .= '&filter_buyer_name=' . urlencode(html_entity_decode($this->request->get['filter_buyer_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_buyer_email'])) {
			$url .= '&filter_buyer_email=' . urlencode(html_entity_decode($this->request->get['filter_buyer_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_total'])) {
			$url .= '&filter_order_total=' . $this->request->get['filter_order_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . urlencode(html_entity_decode($this->request->get['filter_order_status'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['account_id'])) {
			$url .= '&account_id=' . $this->request->get['account_id'];
		}

		if (isset($this->request->get['sort']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_order_map')) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_order_map')) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['action_delete'] = $this->url->link('amazon_map/order/deleteMapOrder', 'token=' . $this->session->data['token'] .$url, true);

		$pagination = new Pagination();
		$pagination->total = $amazonOrderTotal;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('amazon_map/account/edit', 'token=' . $this->session->data['token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($amazonOrderTotal) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($amazonOrderTotal - $this->config->get('config_limit_admin'))) ? $amazonOrderTotal : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $amazonOrderTotal, ceil($amazonOrderTotal / $this->config->get('config_limit_admin')));

		$data['filter_oc_order_id'] 		= $filter_oc_order_id;
		$data['filter_amazon_order_id'] = $filter_amazon_order_id;
		$data['filter_buyer_name'] 			= $filter_buyer_name;
		$data['filter_buyer_email'] 		= $filter_buyer_email;
		$data['filter_order_total'] 		= $filter_order_total;
		$data['filter_date_added'] 			= $filter_date_added;
		$data['filter_order_status'] 		= $filter_order_status;
		$data['sort'] 									= $sort;
		$data['order'] 									= $order;

		$this->template = 'amazon/amazon_map/order.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->data=$data;
		$this->response->setOutput($this->render());

		//return $this->load->view('amazon_map/order.tpl', $data);
	}

	public function import()
	{
		# code...
		$this->document->setTitle($this->language->get('heading_title_import'));

		$data['account_id'] = $this->session->data['amazon_account_id'] =$this->request->get['account_id'];
		$data['filter_date_start'] = date('y/m/d', strtotime(date('Y') . '-' . date('m') . '-01'));
        $data['filter_date_end'] = date('y/m/d');
        
		$data['button_back_link'] = $this->url->link($this->route.'?account_id='.$data['account_id'], '' , true);

		$this->template = 'amazon/amazon_map/import_order.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->data=$data;
		$this->response->setOutput($this->render());
		
	}
	public function dtHandler()
    {
        $request = $_REQUEST;
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            parse_str(html_entity_decode($request['filter']), $filterData);
            $filterData = $filterData['filter'];
        }
        
        $filterData['search'] = null;
        if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
            $filterData['search'] = $request['search']['value'];
        }

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
			0 => 'map_id',
            1 => 'oc_order_id',
            2 => 'amazon_order_id',
            3 => 'customer_name',
			4 => 'customer_email',
			5 => 'amazon_order_status',
            6 => 'total',
        );
        $orderColumn = $columns[$request['order'][0]['column']];

		$results = $this->_amazonMapOrder->getAllOcAmazonOrderMap($start, $length, $filterData, $orderColumn );

		$records = $results['data'];

        $totalData = $results['total'];
        $totalFiltered = $results['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records
        );
        $this->response->setOutput(json_encode($json_data));
        return;
	}
	
	public function deleteMapOrder() {
		$result = array();
		unset($this->session->data['order_delete_result']);
		if (isset($this->request->post['selected']) ) {

			if(!$this->validateDelete()){
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				$this->response->setOutput(json_encode($result_json));
				return;
			}
			foreach ($this->request->post['selected'] as $map_id) {
				$result[] = $this->_amazonMapOrder->deleteMapOrders($map_id, $this->request->get['account_id']);
			}
			if(!empty($result)){
				$result_json['success'] = '1';
				$result_json['success_msg']  = $result;
				$this->response->setOutput(json_encode($result_json));
				return;
			}
		}
	}

	public function validateDelete() {
		if (!$this->user->hasPermission('modify', 'amazon_map/order')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

  public function getOrderList(){
      $json = $amazonOrderLists = array();
      $this->load->language('amazon_map/order');
      $accountId = $this->request->get['account_id'];

	  //$checkRecord = $this->validateOrder($this->request->post);
      if(empty($checkRecord)){

        $getAccountEntry = $this->Amazonconnector->getAccountDetails(array('account_id' => $accountId));
        if(isset($getAccountEntry['wk_amazon_connector_marketplace_id']) && $getAccountEntry['wk_amazon_connector_marketplace_id']) {
						$orderLists = $this->_amazonMapOrder->getFinalOrderReport($this->request->post, $getAccountEntry);

            if (isset($orderLists['success'])) {
								$json['success'] = $orderLists['success'];
            }
						if(isset($orderLists['error'])){
								$json['error'] = $orderLists['error'];
						}
						if(isset($orderLists['error_exception'])){
								$json['warning']['error'] = $orderLists['error_exception'];
						}
						if(isset($orderLists['next_token'])){
								$json['next_token'] = $orderLists['next_token'];
						}
						$json['total_order'] = count($this->_amazonMapOrder->getOrderTempData(array('account_id' => $accountId)));
        }else{
          	$json['warning']['error'] = $this->language->get('error_no_account_details');
        }
      }else{
        		$json['warning'] = $checkRecord;
      }
      $this->response->addHeader('Content-Type: application/json');
  		$this->response->setOutput(json_encode($json));
  }

	public function createOrder(){
			$json = $response = array();
			if(isset($this->request->post['account_id'])  && $this->request->post['account_id']){
					$account_id 			= $this->request->post['account_id'];
					$count 						= $this->request->post['count'];
					$accountDetails 	= $this->Amazonconnector->getAccountDetails(array('account_id' => $account_id));
					if(isset($accountDetails['wk_amazon_connector_marketplace_id']) && $accountDetails['wk_amazon_connector_marketplace_id']){

							if(isset($this->request->post['amazon_order_id']) && $this->request->post['amazon_order_id']){
									$amazon_order_id = $this->request->post['amazon_order_id'];
									$getFirstOrder = $this->_amazonMapProduct->getImportedOrder($account_id, 'single', $amazon_order_id);
							}else{
									$getFirstOrder = $this->_amazonMapOrder->getImportedOrder($account_id, 'single');
							}

							if(isset($getFirstOrder['item_id'])){
									$response = $this->_amazonMapOrder->createOrderToOC($getFirstOrder, $accountDetails);
									foreach ($response as $key => $ordValue) {
											if(isset($ordValue['error']) && $ordValue['error']){
													$json['error'] = $ordValue['message'];
											}
											if(isset($ordValue['error']) && !$ordValue['error']){
													$json['success'] = $ordValue['message'];
											}
									}
							}
					}else{
							$json['error_failed'] = $this->language->get('error_no_account_details');
					}
			}else{
					$json['error_failed'] = $this->language->get('error_wrong_selection');
			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
	}

	public function importSingleOrder() {
			$this->load->language('amazon_map/order');
			$json = $response = array();
			if(isset($this->request->post['account_id']) && $this->request->post['account_id']){
					$account_id 			= $this->request->post['account_id'];
					$getAccountEntry 	= $this->Amazonconnector->getAccountDetails(array('account_id' => $account_id));

					if(isset($getAccountEntry['wk_amazon_connector_marketplace_id']) && $getAccountEntry['wk_amazon_connector_marketplace_id']){

							if(isset($this->request->post['amazon_order_id']) && $this->request->post['amazon_order_id']){
								$amazonOrderId = $this->request->post['amazon_order_id'];
								// get single order details
								$getOrderList = $this->Amazonconnector->GetOrder($amazonOrderId, $account_id);

								if(isset($getOrderList['GetOrderResult']['Orders']['Order']) && $getOrderList['GetOrderResult']['Orders']['Order']){
										$getOrderArray = $getOrderList['GetOrderResult']['Orders']['Order'];
										if(isset($getOrderArray[0]) && $getOrderArray[0]){
											$getAllOrderArrays = $getOrderArray;
										}else{
											$getAllOrderArrays = [$getOrderArray];
										}
										$getSaveResult = $this->_amazonMapOrder->saveOrderReportData($getAllOrderArrays, $getAccountEntry);
										if(isset($getSaveResult['amazonOrders']) && in_array($amazonOrderId, $getSaveResult['amazonOrders'])){
												$getOrderTempData = $this->_amazonMapOrder->getOrderTempData(array('item_id' => $amazonOrderId, 'account_id' => $account_id));
												if(isset($getOrderTempData[0]['item_id']) && $getOrderTempData[0]['item_id'] === $amazonOrderId){
														$response = $this->_amazonMapOrder->createOrderToOC($getOrderTempData[0], $getAccountEntry);
														foreach ($response as $key => $syncResult) {
																if(isset($syncResult['error']) && !$syncResult['error']){
																		$json['success'] = $syncResult['message'];
																}
																if(isset($syncResult['error']) && $syncResult['error']){
																		$json['error_failed'] = $syncResult['message'];
																}
														}
												}
										}else{
												if(isset($getSaveResult['error']) && !empty($getSaveResult['error'])){
														$json['error_failed'] = $getSaveResult['error'];
												}
										}
								}else{
										if(isset($getOrderList['error'])){
												$json['error_failed'] = 'Warning: '.$getOrderList['error'];
										}else{
												$json['error_failed'] = $this->language->get('error_no_order_found');
										}
								}
							}else{
		              $json['error_failed'] = $this->language->get('error_order_required');
		          }
					}else{
							$json['error_failed'] = $this->language->get('error_no_account_details');
					}
			}else{
					$json['error_failed'] = $this->language->get('error_wrong_selection');
			}
    	$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
	}

  public function validateOrder(){
    $error_data = array();

      if(utf8_strlen(trim($this->request->post['amazon_order_from'])) != 10){
          $error_data['error_date_from'] = $this->language->get('error_date_from');
      }

      if(utf8_strlen(trim($this->request->post['amazon_order_to'])) != 10){
          $error_data['error_date_to'] = $this->language->get('error_date_to');
      }

      if(trim($this->request->post['amazon_order_from']) > trim($this->request->post['amazon_order_to'])){
          $error_data['error_date_from'] = $this->language->get('error_invalid_date');
      }

      if(trim($this->request->post['amazon_order_from']) > date("Y-m-d")){
          $error_data['error_date_from'] = $this->language->get('error_lessthan_date');
      }
      if(trim($this->request->post['amazon_order_to']) > date("Y-m-d")){
          $error_data['error_date_to'] = $this->language->get('error_lessthan_date');
      }
      return $error_data;
  }

	public function autocomplete(){
		$json = array();

			if(isset($this->request->get['account_id']) && (isset($this->request->get['filter_buyer_name']) || isset($this->request->get['filter_buyer_email']))){
					$getFilter = '';
					if(isset($this->request->get['filter_buyer_name'])){
						$getFilter = 'oc_name';
						$filter_buyer_name = $this->request->get['filter_buyer_name'];
					}else{
						$filter_buyer_name = '';
					}

					if(isset($this->request->get['filter_buyer_email'])){
						$getFilter = 'oc_email';
						$filter_buyer_email = $this->request->get['filter_buyer_email'];
					}else{
						$filter_buyer_email = '';
					}

					$filter_data = array(
						'account_id' 						=> $this->request->get['account_id'],
						'filter_buyer_name' 		=> $filter_buyer_name,
						'filter_buyer_email' 		=> $filter_buyer_email,
						'order'       					=> 'ASC',
						'start'       					=> 0,
						'limit'       					=> 5
					);

					$results = $this->_amazonMapOrder->getOcAmazonOrderMap($filter_data);

					foreach ($results as $result) {
							if($getFilter == 'oc_name'){
									$json[] = array(
										'item_id' 		=> $result['oc_customer_id'],
										'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
									);
							}else if($getFilter == 'oc_email'){
									$json[] = array(
										'item_id' 		=> $result['oc_customer_id'],
										'name'        => strip_tags(html_entity_decode($result['email'], ENT_QUOTES, 'UTF-8'))
									);
							}
					}

				$sort_order = array();

				foreach ($json as $key => $value) {
					$sort_order[$key] = $value['name'];
				}

				array_multisort($sort_order, SORT_ASC, $json);

				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($json));
			}
	}

}
