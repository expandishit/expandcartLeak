<?php

class ControllerAmazonAmazonMapProduct extends Controller {
	private $error = array();
	private $route = 'amazon/amazon_map/product';
	private $common = 'amazon/amazon_map/common';

	public function __construct($registory) {
		parent::__construct($registory);
		$this->load->model($this->route);
		$this->_amazonMapProduct = $this->model_amazon_amazon_map_product;
		$this->load->language($this->route);
		$this->load->language($this->common);
    }

    public function index() {
		
		$data['text_currently_sync'] 		= $this->language->get('text_currently_sync');
		$data['text_currently_import'] 	= $this->language->get('text_currently_import');

		$data['account_id'] = $this->session->data['amazon_account_id'] =$this->request->get['account_id'];
		
		$this->document->addScript('view/javascript/amazon_connector/webkul_amazon_connector.js');

		if(isset($this->request->get['account_id'])) {
			$data['account_id'] = $account_id = $this->request->get['account_id'];
		}else{
			$data['account_id'] = $account_id = 0;
		}

		if (isset($this->request->get['filter_oc_product_id'])) {
			$filter_oc_product_id = $this->request->get['filter_oc_product_id'];
		} else {
			$filter_oc_product_id = '';
		}

		if (isset($this->request->get['filter_oc_product_name'])) {
			$filter_oc_product_name = $this->request->get['filter_oc_product_name'];
		} else {
			$filter_oc_product_name = '';
		}

		if (isset($this->request->get['filter_amazon_product_id'])) {
			$filter_amazon_product_id = $this->request->get['filter_amazon_product_id'];
		} else {
			$filter_amazon_product_id = '';
		}

		if (isset($this->request->get['filter_source_sync'])) {
			$filter_source_sync = $this->request->get['filter_source_sync'];
		} else {
			$filter_source_sync = null;
		}

		if (isset($this->request->get['filter_price'])) {
			$filter_price = $this->request->get['filter_price'];
		} else {
			$filter_price = null;
		}

		if (isset($this->request->get['filter_quantity'])) {
			$filter_quantity = $this->request->get['filter_quantity'];
		} else {
			$filter_quantity = null;
		}

		if(isset($this->request->get['tab'])) {
			$data['tab'] = $this->request->get['tab'];
		}else{
			$data['tab'] = false;
		}

		if (isset($this->request->get['sort']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'id';
		}

		if (isset($this->request->get['order']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if(isset($this->session->data['product_delete_result'])){
				$data['product_delete_result'] = $this->session->data['product_delete_result'];

				unset($this->session->data['product_delete_result']);
		}else{
				$data['product_delete_result'] = array();
		}

		$url = '';

		$url .= '&status=account_product_map';


		if (isset($this->request->get['filter_oc_product_id'])) {
			$url .= '&filter_oc_product_id=' . $this->request->get['filter_oc_product_id'];
		}

		if (isset($this->request->get['filter_oc_product_name'])) {
			$url .= '&filter_oc_product_name=' . urlencode(html_entity_decode($this->request->get['filter_oc_product_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_amazon_product_id'])) {
			$url .= '&filter_amazon_product_id=' . $this->request->get['filter_amazon_product_id'];
		}

		if (isset($this->request->get['filter_source_sync'])) {
			$url .= '&filter_source_sync=' . $this->request->get['filter_source_sync'];
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['account_id'])) {
			$url .= '&account_id=' . $this->request->get['account_id'];
		}

		if (isset($this->request->get['sort']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['import_from_ebay'] 	= $this->url->link('amazon_map/product', 'token=' . $this->session->data['token'] . $url, true);
		$data['delete'] 			= $this->url->link('amazon_map/product/delete', 'token=' . $this->session->data['token'] . $url, true);
		$data['import_product_tab'] = $this->url->link($this->route.'/import?account_id='.$data['account_id'], '' , true);
		$data['export_product_tab'] = $this->url->link($this->route.'/export?account_id='.$data['account_id'], '' , true);

		$data['import_products'] = array();

		$filter_data = array(
			'account_id' 								=> $account_id,
			'filter_oc_product_id'	  	=> $filter_oc_product_id,
			'filter_oc_product_name'	  => $filter_oc_product_name,
			'filter_amazon_product_id'	=> $filter_amazon_product_id,
			'sync_source' 							=> $filter_source_sync,
			'filter_price'							=> $filter_price,
			'filter_quantity' 					=> $filter_quantity,
			'sort'  										=> $sort,
			'order' 										=> $order,
			'start' 										=> ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' 										=> $this->config->get('config_limit_admin')
		);

		$getGlobalOption = $this->Amazonconnector->__getOcAmazonGlobalOption();

		$data['total_import_product'] = $this->_amazonMapProduct->getProductTempData(array('account_id' => $account_id));

		$amazonProductTotal = $this->_amazonMapProduct->getTotalProductMappedEntry($filter_data);

		$results = $this->_amazonMapProduct->getProductMappedEntry($filter_data);

		if($results){
			foreach ($results as $result) {
				/**
				* Get product Variations
				*/
				$option_values 				= array();
				$result['option_id'] 	= $getGlobalOption['option_id'];
				$product_option 			= $this->_amazonMapProduct->getProductOptions($result);

				if(!empty($product_option)){
					foreach ($product_option as $key => $opt_value) {
						$option_values[] 	= array(
							'name' => $opt_value['value_name'],
							'asin' => $opt_value['id_value'],
						);
					}
				}

				$data['import_products'][] = array(
					'map_id' 							=> $result['map_id'],
					'oc_product_id' 			=> $result['oc_product_id'],
					'amazon_product_asin' => $result['amazon_product_id'],
					'option_values'				=> $option_values,
					'product_name'	 			=> $result['product_name'],
					'feed_id'							=>$result['feed_id'],
					'price'								=> $result['price'],
					'quantity'						=> $result['quantity'],
					'source'							=> $result['sync_source'],
				);
			}
		}

		$data['productCombinations'] = $this->Amazonconnector->getOcProductWithCombination();

		$exportProductArray = $this->_amazonMapProduct->getProductMappedEntry(array('export_sync_source' => 'Opencart Item'));

		if(!empty($exportProductArray)){
				foreach ($exportProductArray as $key => $product) {
					  $combinations = array();
						if($getCombinations = $this->Amazonconnector->_getProductVariation($product['oc_product_id'], $type = 'amazon_product_variation_value')){
								foreach ($getCombinations as $option_id => $combination_array) {
										 foreach ($combination_array['option_value'] as $key1 => $combination_value) {
											 		$exportProductArray[$key]['combinations'][] = array('name' => $combination_value['name'], 'id_value' => $combination_value['id_value']);
										 }
								 }
						}
				}
		}

		$data['updateproductData'] = $exportProductArray;

		$data['token'] 	= $this->session->data['token'];

		if (isset($this->session->data['warning'])) {
			$data['error_warning'] = $this->session->data['warning'];

			unset($this->session->data['warning']);
		} else if (isset($this->error['warning'])) {
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

		$url .= '&status=account_product_map';

		if (isset($this->request->get['filter_oc_product_id'])) {
			$url .= '&filter_oc_product_id=' . $this->request->get['filter_oc_product_id'];
		}

		if (isset($this->request->get['filter_oc_product_name'])) {
			$url .= '&filter_oc_product_name=' . urlencode(html_entity_decode($this->request->get['filter_oc_product_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_amazon_product_id'])) {
			$url .= '&filter_amazon_product_id=' . $this->request->get['filter_amazon_product_id'];
		}

		if (isset($this->request->get['filter_source_sync'])) {
			$url .= '&filter_source_sync=' . $this->request->get['filter_source_sync'];
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['account_id'])) {
			$url .= '&account_id=' . $this->request->get['account_id'];
			$data['clear_product_filter'] 	= $this->url->link('amazon_map/account/edit', 'token=' . $this->session->data['token'] . '&account_id=' . $this->request->get['account_id']. '&status=account_product_map', true);
		}

		$data['button_back_link'] = $this->url->link('amazon_map/account/edit', 'token=' . $this->session->data['token'] .$url, true);

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_map_id'] = $this->url->link('amazon_map/account/edit', 'token=' . $this->session->data['token'] . '&sort=id' . $url, true);
		$data['sort_oc_product_id'] = $this->url->link('amazon_map/account/edit', 'token=' . $this->session->data['token'] . '&sort=apm.oc_product_id' . $url, true);
		$data['sort_oc_name'] = $this->url->link('amazon_map/account/edit', 'token=' . $this->session->data['token'] . '&sort=product_name' . $url, true);
		$data['sort_oc_price'] = $this->url->link('amazon_map/account/edit', 'token=' . $this->session->data['token'] . '&sort=p.price' . $url, true);
		$data['sort_oc_quantity'] = $this->url->link('amazon_map/account/edit', 'token=' . $this->session->data['token'] . '&sort=p.quantity' . $url, true);

		$url = '';

		$url .= '&status=account_product_map';

		if (isset($this->request->get['filter_oc_product_id'])) {
			$url .= '&filter_oc_product_id=' . $this->request->get['filter_oc_product_id'];
		}

		if (isset($this->request->get['filter_oc_product_name'])) {
			$url .= '&filter_oc_product_name=' . urlencode(html_entity_decode($this->request->get['filter_oc_product_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_amazon_product_id'])) {
			$url .= '&filter_amazon_product_id=' . $this->request->get['filter_amazon_product_id'];
		}

		if (isset($this->request->get['filter_source_sync'])) {
			$url .= '&filter_source_sync=' . $this->request->get['filter_source_sync'];
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['account_id'])) {
			$url .= '&account_id=' . $this->request->get['account_id'];
		}

		if (isset($this->request->get['sort']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['action_product'] = $this->url->link('amazon_map/product/deleteMapProduct', 'token=' . $this->session->data['token'] .$url, true);

		$pagination = new Pagination();
		$pagination->total = $amazonProductTotal;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('amazon_map/account/edit', 'token=' . $this->session->data['token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($amazonProductTotal) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($amazonProductTotal - $this->config->get('config_limit_admin'))) ? $amazonProductTotal : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $amazonProductTotal, ceil($amazonProductTotal / $this->config->get('config_limit_admin')));

		$data['filter_oc_product_id'] 			= $filter_oc_product_id;
		$data['filter_oc_product_name'] 		= $filter_oc_product_name;
		$data['filter_amazon_product_id'] 	= $filter_amazon_product_id;
		$data['filter_source_sync'] 				= $filter_source_sync;
		$data['filter_price'] 							= $filter_price;
		$data['filter_quantity'] 						= $filter_quantity;
		$data['sort'] 											= $sort;
		$data['order'] 											= $order;

		$this->template = 'amazon/amazon_map/product.expand';
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
            1 => 'oc_product_id',
            2 => 'product_name',
            3 => 'amazon_product_asin',
			4 => 'source',
			5 => 'price',
			6 => 'quantity',
        );
        $orderColumn = $columns[$request['order'][0]['column']];

		$results = $this->_amazonMapProduct->getAllProductMappedEntry($start, $length, $filterData, $orderColumn );
		
		$records = $results['data'];
		foreach ($records as $key => $row) {
			# code...
			/**
			* Get product Variations
			*/
			$option_values 				= array();
			$row['option_id'] 	= $getGlobalOption['option_id'];
			$product_option 			= $this->_amazonMapProduct->getProductOptions($row);

			if(!empty($product_option)){
				foreach ($product_option as $opt_value) {
					$option_values[] 	= array(
						'name' => $opt_value['value_name'],
						'asin' => $opt_value['id_value'],
					);
				}
			}
			$records[$key]['map_id']=$row['map_id'];
			$records[$key]['oc_product_id']=$row['oc_product_id'];
			$records[$key]['amazon_product_asin']=$row['amazon_product_id'];
			$records[$key]['option_values'] = $option_values;
			$records[$key]['product_name'] = $row['product_name']; 
			$records[$key]['feed_id']      = $row['feed_id'];
			$records[$key]['price'] = $row['price'];
			$records[$key]['quantity'] = $row['quantity'];
			$records[$key]['source'] = $row['sync_source'];
		}
		
		
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
	
	public function dtUpdatedProduct(){
		$request = $_REQUEST;
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            parse_str(html_entity_decode($request['filter']), $filterData);
            $filterData = $filterData['filter'];
        }
        
        $filterData['search'] = null;
        if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
            $filterData['search'] = $request['search']['value'];
        }
		$filterData['export_sync_source'] = 'Opencart Item';

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
			0 => 'oc_product_id',
            1 => 'main_product_type_value',
            2 => 'product_name',
            3 => 'combinations',

        );
        $orderColumn = $columns[$request['order'][0]['column']];

		$results = $this->_amazonMapProduct->getAllProductMappedEntry($start, $length, $filterData, $orderColumn );

		$records = $results['data'];

		foreach ($records as $key => $product) {
			$combinations = array();
			if($getCombinations = $this->Amazonconnector->_getProductVariation($product['oc_product_id'], $type = 'amazon_product_variation_value')){
				foreach ($getCombinations as $option_id => $combination_array) {
					foreach ($combination_array['option_value'] as $combination_value) {
							$records[$key]['combinations'][] = array('name' => $combination_value['name'], 'id_value' => $combination_value['id_value']);
					}
				}
			}
		}

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
	public function deleteMapProduct() {
		$result = array();

		if (isset($this->request->post['selected'])) {
			if(!$this->validateDelete()){
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				$this->response->setOutput(json_encode($result_json));
				return;	
			}
			foreach ($this->request->post['selected'] as $map_id) {
				$result[] = $this->_amazonMapProduct->deleteMapProducts($map_id, $this->request->post['account_id']);
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
		if (!$this->user->hasPermission('modify', $this->route)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function import()
	{
		# code...
		$this->document->setTitle($this->language->get('heading_title_import'));

		$data['account_id'] = $this->session->data['amazon_account_id'] =$this->request->get['account_id'];
		$data['error_wrong_asinformat'] = $this->language->get('error_wrong_asinformat');
		$data['button_back_link'] = $this->url->link($this->route.'?account_id='.$data['account_id'], '' , true);

		$this->template = 'amazon/amazon_map/import_product.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->data=$data;
		$this->response->setOutput($this->render());
		
	}

	public function export()
	{
		# code...
		$this->document->setTitle($this->language->get('heading_title_export'));

		$data['account_id'] = $this->session->data['amazon_account_id'] =$this->request->get['account_id'];
	    $data['error_wrong_asinformat'] = $this->language->get('error_wrong_asinformat');
		$data['button_back_link'] = $this->url->link($this->route.'?account_id='.$data['account_id'], '' , true);

		$this->template = 'amazon/amazon_map/product_export.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->data=$data;
		$this->response->setOutput($this->render());
		
	}
	// ********** function to generate the report list id **********
	public function generate_report_id() {
		$json = array();
		$this->load->language($this->route);

		if (isset($this->request->get['account_id']) && $this->request->get['account_id']) {
			$getAccountEntry = $this->Amazonconnector->getAccountDetails(array('account_id' => $this->request->get['account_id']));

			if(isset($getAccountEntry) && !empty($getAccountEntry)){
				try{
						$result =  $this->_amazonMapProduct->updateAccountReportEntry($this->request->get['account_id']);
						if(isset($result['status']) && $result['status']){
							if(isset($this->request->get['status']) && $this->request->get['status'] == 'order'){
									$json['success'] = array('message' => sprintf($this->language->get('success_report_order_added'), $result['report_id']), 'report_id' => $result['report_id']);
							}else{
								if(isset($result['status']) && $result['status'] && isset($result['report_id']) && $result['report_id'] == 0){
									$this->session->data['warning'] = $this->language->get('error_report_id');
									$json['redirect'] = html_entity_decode($this->url->link('amazon_map/account/edit', 'token=' . $this->session->data['token'] .'&tab=import_product&status=account_product_map&account_id='.$this->request->get['account_id'], true));
								}else{
									$json['success'] = array('message' => $result['message'], 'report_id' => $result['report_id']);
								}
							}
						}else{
							$json['error'] = $result['message'];
						}
				} catch (\Exception $e) {
						$json['error'] = $e->getMessage();
				}
			}else{
				$json['error'] = $this->language->get('error_account_not_exist');
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	// ********** amazon product(s) import (save to opencart amazon_tempdata table) function **********
	public function getProductReport(){
			$json = array();
			$this->load->language('amazon_map/product');
			if(isset($this->request->post['account_id']) && $this->request->post['account_id']){
					$account_id = $this->request->post['account_id'];
					$getAccountEntry = $this->Amazonconnector->getAccountDetails(array('account_id' => $account_id));
					if($getAccountEntry['wk_amazon_connector_listing_report_id']){
							$finalReport = $this->_amazonMapProduct->getFinalProductReport($getAccountEntry);

							$json = ['data' => $finalReport, 'message' => sprintf($this->language->get('text_success_import_only'), count($finalReport)), 'total_product' => count($this->_amazonMapProduct->getProductTempData(array('account_id' => $account_id)))];
					}else{
							$json['error'] = $this->language->get('error_report__id_error');
					}
			}else{
					$json['error'] = $this->language->get('error_account_not_exist');
			}
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
	}

	// ********** to create the amazon imported(already saved in amazon_tempdata table) product(s) function **********
	public function createProduct(){
			$json = $response = array();
			$this->load->language('amazon_map/product');
			$this->load->model('setting/store');
			$this->load->model('catalog/attribute');
			$this->load->model('catalog/option');
			$this->load->model('localisation/language');

			if(isset($this->request->post['account_id'])  && $this->request->post['account_id']){
					$account_id 			= $this->request->post['account_id'];
					$count 						= $this->request->post['count'];
					$accountDetails 	= $this->Amazonconnector->getAccountDetails(array('account_id' => $account_id));
					
					if(isset($accountDetails['wk_amazon_connector_marketplace_id']) && $accountDetails['wk_amazon_connector_marketplace_id']){
							if(isset($this->request->post['product_asin']) && $this->request->post['product_asin']){
									$product_asin = $this->request->post['product_asin'];
									$getFirstItem = $this->_amazonMapProduct->getImportedProduct($account_id, 'single', $product_asin);
							}else{
									$getFirstItem = $this->_amazonMapProduct->getImportedProduct($account_id, 'single');
							}
							
							if(isset($getFirstItem['item_id'])){
									$response = $this->_amazonMapProduct->createProductToOC($getFirstItem, $accountDetails);
									var_dump($response);
									die();
									foreach ($response as $key => $report) {
											if(isset($report['error']) && $report['error']){
													$json['error'] = $report['message'];
											}
											if(isset($report['error']) && !$report['error']){
													$json['success'] = $report['message'];
											}
									}
							}
					}else{
							$json['error_failed'] = $this->language->get('error_account_not_exist');
					}
			}else{
					$json['error_failed'] = $this->language->get('error_wrong_selection');
			}
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
	}

// ********** To update/delete exported product to amazon store from opencart store **********
	public function opration_export_product($data = array()){
		$json = $product_array = $exportProductArray = $updateQty = $updatePrice = $deleteArray = array();
			if(isset($this->request->post['account_id']) && $this->request->post['account_id']){
				$account_id 		= $this->request->post['account_id'];
				$startPage 			= $this->request->get['page'];
				$export_option 	= $this->request->post['export_option'];

				if(isset($this->request->post['selected']) && !empty($this->request->post['selected'])){
						$updatedProducts = $this->request->post['selected'];
						$accountDetails = $this->Amazonconnector->getAccountDetails(array('account_id' => $account_id));
						if(isset($accountDetails['wk_amazon_connector_marketplace_id']) && $accountDetails['wk_amazon_connector_marketplace_id']){

								if(isset($startPage) && $startPage == 1){
									unset($this->session->data['updateDelete_result']);
									$this->session->data['updateDelete_result'] = array();
								}

								if(count($updatedProducts) <= 40){
										$totalPages = 1;
								}else{
										$totalPages = ceil(count($updatedProducts)/40);
								}
								$product_ids = array_slice($updatedProducts, ($startPage - 1) * 40, 40);
								$exportProductArray = $this->_amazonMapProduct->getProductMappedEntry(array('sync_source' => 'Opencart Item', 'product_ids' => implode(",", $product_ids)));

								if(count($exportProductArray)){
										foreach ($exportProductArray as $key => $product_details) {
												$getCombinations = $getUpdateDeleteArray = array();

												if ((isset($product_details['main_product_type']) && $product_details['main_product_type']) && (isset($product_details['main_product_type_value']) && $product_details['main_product_type_value'])) {
														$getCombinations = $this->Amazonconnector->_getProductVariation($product_details['oc_product_id'], $type = 'amazon_product_variation_value');

														if(!empty($getCombinations)){ // variations found
																$getUpdateDeleteArray = $this->makeVariationsArrays($getCombinations, $product_details, $export_option, $accountDetails);
																if(count($getUpdateDeleteArray['updateQty'])){
																	$updateQty = array_merge($updateQty, $getUpdateDeleteArray['updateQty']);
																}
																if(count($getUpdateDeleteArray['updatePrice'])){
																	$updatePrice = array_merge($updatePrice, $getUpdateDeleteArray['updatePrice']);
																}
																if(count($getUpdateDeleteArray['delete'])){
																	$deleteArray = array_merge($deleteArray, $getUpdateDeleteArray['delete']);
																}
														}else{ // simple product
																$getUpdateDeleteArray = $this->makeSimpleArrays($product_details, $export_option, $accountDetails);
																if(count($getUpdateDeleteArray['updateQty'])){
																	$updateQty = array_merge($updateQty, $getUpdateDeleteArray['updateQty']);
																}
																if(count($getUpdateDeleteArray['updatePrice'])){
																	$updatePrice = array_merge($updatePrice, $getUpdateDeleteArray['updatePrice']);
																}
																if(count($getUpdateDeleteArray['delete'])){
																	$deleteArray = array_merge($deleteArray, $getUpdateDeleteArray['delete']);
																}
														}
														//final data of submit feed data
														$product_array[] = array(
																			'product_id' 	=> $product_details['oc_product_id'],
																			'account_id' 	=> $accountDetails['id'],
																			'name' 			 	=> $product_details['product_name'],
																			'id_value'	 	=> $product_details['main_product_type_value'],
																		);
												} else {
														$json['error'][$product_details['oc_product_id']] = sprintf($this->language->get('error_update_export_to_amazon'), $product_details['product_name'].', Id : '.$product_details['oc_product_id']);
												}
										}

							if(!empty($product_array)){
								if ($export_option == 'update') {
									$this->Amazonconnector->product['ActionType']  = 'UpdateQuantity';
									$this->Amazonconnector->product['ProductData'] = $updateQty;

									$feedType = '_POST_INVENTORY_AVAILABILITY_DATA_';

								} else if ($export_option == 'delete') {
									$this->Amazonconnector->product['ActionType']  = 'DeleteProduct';
									$this->Amazonconnector->product['ProductData'] = $deleteArray;

									$feedType = '_POST_PRODUCT_DATA_';
								}
								$product_updated = $this->Amazonconnector->submitFeed($feedType, $account_id);

								if (isset($product_updated['success']) && $product_updated['success']) {
									if ($export_option == 'update') {
										$this->Amazonconnector->product['ActionType']  = 'UpdatePrice';
										$this->Amazonconnector->product['ProductData'] = $updatePrice;
										$this->Amazonconnector->submitFeed($feedType = '_POST_PRODUCT_PRICING_DATA_', $account_id);
										foreach ($product_array as $productDetail) {
											$json['success'][] = sprintf($this->language->get('success_update_export_to_amazon'), $productDetail['name'].', Id : '.$productDetail['product_id']). $productDetail['id_value'];
										}
									} else if ($export_option == 'delete') {
										foreach ($product_array as $productDetail) {
											$this->_amazonMapProduct->deleteProductMapEntry($productDetail);
											$json['success'][] = sprintf($this->language->get('success_delete_export_to_amazon'), $productDetail['name'].', Id : '.$productDetail['product_id']);
										}
									}
								} else {
									$json['error'][] = $this->language->get('error_occurs');
								}
							}else{
								$json['error'][] = $this->language->get('error_no_product_found');
							}
										$json['totalpages'] = $totalPages;
								}else{ // if exported product(s) found to update/delete
							$json['error_failed'] = $this->language->get('error_no_product_found');
						}
						}else{
								$json['error_failed'] = $this->language->get('error_account_not_exist');
						}
				}else{
						$json['error_failed'] = $this->language->get('error_no_selection');
				}
			}else{
					$json['error_failed'] = $this->language->get('error_wrong_selection');
			}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

// ********** To make variations array for updateQTY & updatePrice & deleteArray for exported product to amazon store from opencart store **********
	public function makeVariationsArrays($data = array(), $product, $type = 'update', $accountDetails){
			$response = $UpdateQuantityArray = $UpdatePriceArray = $DeleteProductArray = array();
			foreach ($data as $option_id => $combination_array) {
				//  $total_combinations = count($combination_array);
				 foreach ($combination_array['option_value'] as $key => $combination_value) {
						 	$product_data = array();
						 	$product_data['sku'] 				 = $combination_value['sku'];
							if(isset($combination_value['price_prefix']) && $combination_value['price_prefix'] == '+'){
									$product_data['price'] = (float)$product['price'] + (float)$combination_value['price'];
							}else{
									$product_data['price'] = (float)$product['price'] - (float)$combination_value['price'];
							}

							if(isset($combination_value['quantity']) && $combination_value['quantity']){
									$product_data['quantity'] = $combination_value['quantity'];
							}else{
									$product_data['quantity'] = $this->config->get('wk_amazon_connector_default_quantity');
							}

							if($type == 'update'){
									//Update qty of amazon product
									$UpdateQuantityArray[] = array(
																						'sku' => $product_data['sku'],
																						'qty' => $product_data['quantity'],
																					);

									//Update price of amazon product
									$UpdatePriceArray[] = array(
																				 'sku' 							=> $product_data['sku'],
																				 'currency_symbol' 	=> $accountDetails['wk_amazon_connector_currency_code'],
																				 'price' 						=> (float)$product_data['price'] * $accountDetails['wk_amazon_connector_currency_rate'],
																			 );
							}else if($type == 'delete'){
									//delete amazon product
									$DeleteProductArray[] = array(
																							'sku' => $product_data['sku'],
																					);
							}
					}
			 }
			 $response = array(
				 							'updateQty' 	=> $UpdateQuantityArray,
											'updatePrice' => $UpdatePriceArray,
											'delete' 			=> $DeleteProductArray,
								);
			return $response;
	}

	// ********** To make Simple Array for updateQTY & updatePrice & deleteArray for exported product to amazon store from opencart store **********
	public function makeSimpleArrays($data = array(), $type = 'update', $accountDetails){
			$response = $UpdateQuantityArray = $UpdatePriceArray = $DeleteProductArray = array();
			$price 		= (float)$data['price'];
			$quantity = (!empty($data['quantity']) ? $data['quantity'] : $this->config->get('wk_amazon_connector_default_quantity'));
			$sku 			= (!empty($data['sku']) ? $data['sku'] : 'oc_prod_'.$data['product_id']);

			if($type == 'update'){
				 //Update qty of amazon product
				 $UpdateQuantityArray[] = array(
																	 'sku' => $sku,
																	 'qty' => $quantity,
																 );

				 //Update price of amazon product
				 $UpdatePriceArray[] = array(
																'sku' 							=> $sku,
																'currency_symbol' 	=> $accountDetails['wk_amazon_connector_currency_code'],
																'price' 						=> $price * $accountDetails['wk_amazon_connector_currency_rate'],
															);
			}else if($type == 'delete'){
					//delete amazon product
					$DeleteProductArray[] = array(
																			'sku' => $sku,
																	);
			}
			$response = array(
										 'updateQty' 		=> $UpdateQuantityArray,
										 'updatePrice' 	=> $UpdatePriceArray,
										 'delete' 			=> $DeleteProductArray,
							 );
		 return $response;
	}

	public function attributeAutocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->_amazonMapProduct->getAmazonAttributes($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'attribute_id'    => $result['attribute_id'],
					'name'            => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'attribute_group' => $result['attribute_group']
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

	public function autocomplete(){
		$json = array();

			if(isset($this->request->get['account_id']) && isset($this->request->get['filter_oc_product_name'])){
					$filter_data = array(
						'account_id' 								=> $this->request->get['account_id'],
						'filter_oc_product_name' 		=> $this->request->get['filter_oc_product_name'],
						'order'       							=> 'ASC',
						'start'       							=> 0,
						'limit'       							=> 5
					);

					$results = $this->_amazonMapProduct->getProductMappedEntry($filter_data);

					foreach ($results as $result) {
							$json[] = array(
								'item_id' 		=> $result['oc_product_id'],
								'name'        => strip_tags(html_entity_decode($result['product_name'], ENT_QUOTES, 'UTF-8'))
							);
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

	public function get_map_product_list() {
		$json = array();

			if(isset($this->request->get['account_id']) && isset($this->request->get['opencart_product_name'])){
					$filter_data = array(
						'account_id' 								=> $this->request->get['account_id'],
						'opencart_product_name' 		=> $this->request->get['opencart_product_name'],
						'opencart_map_product_id'   => $this->request->get['opencart_map_product_id'],
						'order'       							=> 'ASC',
						'start'       							=> 0,
						'limit'       							=> 5
					);

					$results = $this->_amazonMapProduct->get_product_record($filter_data);

					foreach ($results as $result) {
							$json[] = array(
								'item_id' 		=> $result['product_id'],
								'name'        => strip_tags(html_entity_decode($result['product_name'], ENT_QUOTES, 'UTF-8')).'-Price-'.$result['price']
							);
					}



				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($json));
			}
	}
	public function map_product_with_existing_product() {
		$json = array();
		 $this->load->language('amazon_map/product');
		if(isset($this->request->post['opencart_map_product_id']) && isset($this->request->post['product_map_id']) && isset($this->request->post['opencart_product_id'])){

			$this->_amazonMapProduct->map_product($this->request->post);
			$this->session->data['success'] =sprintf($this->language->get('text_map_product_success'),$this->request->post['opencart_product_id']);
       		$json= 1;
		} else {
			$json= 0;
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


}
