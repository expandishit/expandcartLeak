<?php

class ControllerAmazonAmazonMapCustomer extends Controller {
	private $error = array();
	private $route = 'amazon/amazon_map/customer';
	private $common = 'amazon/amazon_map/common';
	

	public function __construct($registory) {
		parent::__construct($registory);
		$this->load->model('amazon/amazon_map/order');
		$this->_amazonMapOrder = $this->model_amazon_amazon_map_order;

		$this->load->language($this->route);
		$this->load->language($this->common);
    }

    public function index() {
		$this->document->setTitle($this->language->get('heading_title'));

		$data['account_id'] = $this->session->data['amazon_account_id'] =$this->request->get['account_id'];
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', '' , true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->route, '' , true)
		);
		
		$this->template = 'amazon/amazon_map/customer.expand';
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
			0 => 'id',
            1 => 'oc_customer_id',
            2 => 'customer_name',
            3 => 'email',
			4 => 'city',
			5 => 'country',
        );
        $orderColumn = $columns[$request['order'][0]['column']];

		$results = $this->_amazonMapOrder->getAllAmazonCustomerList($start, $length, $filterData, $orderColumn );
		
		$records = $results['data'];
		foreach ($records as $key => $row) {
			# code...
			$records[$key]['map_id'] = $row['id']; 
			$records[$key]['oc_customer_id'] = $row['oc_customer_id']; 
			$records[$key]['customer_name'] = $row['customer_name']; 
			$records[$key]['customer_email'] = $row['email']; 
			$records[$key]['city'] = $row['city']; 
			$records[$key]['country'] = $row['country']; 
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
}
