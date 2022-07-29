<?php

class ControllerAmazonAmazonMapAccount extends Controller {
	private $error = array();
	private $route = 'amazon/amazon_map/account';
	private $common = 'amazon/amazon_map/common';
	private $post_fields = array(
		'wk_amazon_connector_store_name',
		'wk_amazon_connector_attribute_group',
		'wk_amazon_connector_marketplace_id',
		'wk_amazon_connector_seller_id',
		'wk_amazon_connector_access_key_id',
		'wk_amazon_connector_secret_key',
		'wk_amazon_connector_country',
		'wk_amazon_connector_currency_rate',
		'wk_amazon_connector_default_store'
		);

	public function __construct($registory) {
		parent::__construct($registory);
		$this->load->model($this->route);
		$this->_amazonAccount = $this->model_amazon_amazon_map_account;

		$this->load->model('localisation/country');
		$this->_countryList = $this->model_localisation_country;
		$this->load->language($this->common);
		$this->load->language($this->route);

    }

    public function index() {
		$this->getList();
	}

	public function dtDelete() {
		
		if ( isset($this->request->post['ids'])) {

			if(!$this->validateDelete()){
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				$this->response->setOutput(json_encode($result_json));
				return;	
			}
			foreach ($this->request->post['ids'] as $account_id) {
				$this->_amazonAccount->deleteAccount($account_id);
			}

			$result_json['success'] = '1';
			$result_json['success_msg']  = $this->language->get('text_success');
			$this->response->setOutput(json_encode($result_json));
            return;
		}

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
			0 => 'id',
            1 => 'store_name',
            2 => 'marketplace_id',
            3 => 'seller_id',
            4 => 'added_date',
        );
        $orderColumn = $columns[$request['order'][0]['column']];

		$results = $this->_amazonAccount->getAllAmazonAccount($start, $length, $filterData, $orderColumn );
		
		$records = $results['data'];
		foreach ($records as $key => $row) {
			# code...
			$records[$key]['account_id'] = $row['id']; 
			$records[$key]['store_name'] = $row['wk_amazon_connector_store_name']; 
			$records[$key]['marketplace_id'] = $row['wk_amazon_connector_marketplace_id']; 
			$records[$key]['seller_id'] = $row['wk_amazon_connector_seller_id']; 
			$records[$key]['added_date'] = $row['wk_amazon_connector_date_added']; 
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

	protected function getList() {
		
		$this->document->setTitle($this->language->get('heading_title'));

		unset($this->session->data['amazon_account_id']);
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', '' , true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->route, '' , true)
		);

		$data['add_account'] = $this->url->link($this->route.'/insert', '' , true);
	
		$this->template = 'amazon/amazon_map/account.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->data=$data;
		$this->response->setOutput($this->render());

	}

	public function insert() {
		
		$this->document->setTitle($this->language->get('heading_title_add'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

			if(!$this->validateAccount()){
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				$this->response->setOutput(json_encode($result_json));
				return;
			}

			$account_id = $this->session->data['amazon_account_id'] = $this->_amazonAccount->__addAmazonAccount($this->request->post);

			$result_json['success'] = '1';
			$result_json['redirect'] = '1';
			$result_json['to'] = (string) $this->url->link($this->route.'/edit?account_id='.$account_id,'',true);
			$result_json['success_msg']  = $this->language->get('text_success_add');
			$this->response->setOutput(json_encode($result_json));
            return;
		
		}

		$this->getForm();
	}


	public function edit() {
		
		$this->document->setTitle($this->language->get('heading_title_edit'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {

			if(!$this->validateAccount()){
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				$this->response->setOutput(json_encode($result_json));
				return;
			}
			
			$this->_amazonAccount->__addAmazonAccount($this->request->post);
		
			$result_json['success'] = '1';
			$result_json['success_msg']  = $this->language->get('text_success_add');
			$this->response->setOutput(json_encode($result_json));
            return;
		}

		$this->getForm();
	}


	public function getForm() {
		
		if (!isset($this->request->get['account_id'])) {
			$this->document->setTitle($this->language->get('heading_title_add'));
			$data['heading_title'] = $this->language->get('heading_title_add');
		}else{
			$this->document->setTitle($this->language->get('heading_title_edit'));
			$data['heading_title'] = $this->language->get('heading_title_edit');
			$data['account_id'] = $this->session->data['amazon_account_id'];
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', '' , true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->route, '' , true)
		);

		$data['breadcrumbs'][] = array(
			'text' =>  $data['heading_title'],
			'href' => $this->url->link($this->route.'/insert', '' , true)
		);

		if (!isset($this->request->get['account_id'])) {
			$data['action'] = $this->url->link($this->route.'/insert', 'token=' . $this->session->data['token'], true);
		} else {
			$data['action'] = $this->url->link($this->route.'/edit', '?account_id=' . $this->request->get['account_id'] , true);
		}

		$data['cancel'] = $this->url->link($this->route, '' , true);


		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['error_account_details'])) {
			$data['error_warning'] = $this->error['error_account_details'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['error_wk_amazon_connector_store_name'])) {
			$data['error_wk_amazon_connector_store_name'] = $this->error['error_wk_amazon_connector_store_name'];
		} else {
			$data['error_wk_amazon_connector_store_name'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		if (isset($this->session->data['delete_action_error'])) {
			$data['error_warning'] = $this->session->data['delete_action_error'];

			unset($this->session->data['delete_action_error']);
		} else {
			$data['error_warning'] = '';
		}

		foreach ($this->post_fields as $key => $error_value) {
			if (isset($this->error['error_'.$error_value])  && $error_value != 'wk_amazon_connector_sites') {
				$data['error_'.$error_value] = $this->error['error_'.$error_value];
			} else {
				$data['error_'.$error_value] = '';
			}
		}

		$data['account_id'] = false;
		if (isset($this->request->get['account_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$account_info = $this->_amazonAccount->getAmazonAccount(array('filter_account_id' => $this->request->get['account_id']));
		}

		if(isset($this->request->get['account_id'])){
			$data['account_id'] = $this->request->get['account_id'];
		}

		foreach ($this->post_fields as $key => $post_value) {
			if (isset($this->request->post[$post_value])) {
				$data[$post_value] = trim($this->request->post[$post_value]);
			} elseif (!empty($account_info[0]) && isset($account_info[0][$post_value])) {
				$data[$post_value] = trim($account_info[0][$post_value]);
			} else {
				$data[$post_value] = '';
			}
		}

		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();
		$this->load->model('catalog/attribute_group');
		$data['attribute_groups'] = $this->model_catalog_attribute_group->getAttributeGroups(array('get_module_list' => true));

		//get all country list
		$data['countries'] = $this->_countryList->getCountries();

		$this->template = 'amazon/amazon_map/account_form.expand';
		$this->children = array(
		'common/header',
		'common/footer'
		);
		$this->data=$data;
		$this->response->setOutput($this->render());
	
	}

	protected function validateAccount() {
		if (!$this->user->hasPermission('modify', 'amazon_map/account')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		//post fields blank check
		foreach ($this->post_fields as $key => $post_value) {
			if(trim($this->request->post[$post_value])==''){
				$this->error[$post_value]	=	$this->language->get('error_field_required');

			}
		}
		
		if(!empty($this->request->post['wk_amazon_connector_currency_rate'])){
				if(!is_numeric($this->request->post['wk_amazon_connector_currency_rate'])) {
						$this->error['error_wk_amazon_connector_currency_rate'] = $this->language->get('error_invalid_currency_rate');
				}
		}

		if(!$this->error){
			if(!isset($this->request->get['account_id']) && $this->request->get['route'] == 'amazon_map/account/add'){
				$getEbayAccount = $this->_amazonAccount->getAmazonAccount(array('filter_store_name' => $this->request->post['wk_amazon_connector_store_name']));
				if(isset($getEbayAccount[0]['id']) && $getEbayAccount[0]['id']){
					$this->error['error_wk_amazon_connector_store_name'] = $this->language->get('error_wk_amazon_connector_store_name');
				}
			}

			if(isset($this->request->get['account_id']) && $this->request->get['route'] == 'amazon_map/account/edit'){
				$getEbayAccount = $this->_amazonAccount->getAmazonAccount(array('filter_store_name' => $this->request->post['wk_amazon_connector_store_name']));

				if(isset($getEbayAccount[0]['id']) && $getEbayAccount[0]['id'] !== $this->request->get['account_id']){
					$this->error['error_wk_amazon_connector_store_name'] = $this->language->get('error_wk_amazon_connector_store_name');
				}
			}
		}

		if(!$this->error){
			$result = array();
			$result = $this->Amazonconnector->getListMarketplaceParticipations($this->request->post);

			if(isset($result['error']) && $result['error']){
				if(isset($result['error_message'])){
						$this->error['error_account_details'] = $result['error_message'];
				}else{
						$this->error['error_account_details'] = $this->language->get('error_account_details');
				}
			}else{
				$this->request->post['wk_amazon_connector_currency_code'] = (string)$result['currency_code'];
			}
		}
		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'amazon_map/account')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
    if(!isset($this->request->post['ids'])) {
        $this->error['warning'] = $this->language->get('error_atleast_one_account');
		}

		return !$this->error;
	}

}
