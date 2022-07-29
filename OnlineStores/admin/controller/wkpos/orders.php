<?php
class ControllerWkposOrders extends Controller {
	private $error = array();

	public function index() {

		$this->language->load('sale/order');
		$this->language->load('catalog/product_filter');
		
		$this->load->model('wkpos/wkpos');
		if(!$this->model_wkpos_wkpos->is_installed())
			$this->response->redirect($this->url->link('wkpos/main', '', true));
		
		$this->data = $this->load->language('sale/order');
		$this->data = array_merge($this->data, $this->load->language('wkpos/orders'));

		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_heading_main'),
			'href' => $this->url->link('wkpos/main', 'token=' . $this->session->data['token'], true)
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('wkpos/orders', 'token=' . $this->session->data['token'], 'SSL')
		);

		$this->load->model('sale/customer');
		$this->load->model('sale/order');
		$this->load->model('wkpos/user');

		$this->load->model('localisation/order_status');
        $order_statuses = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['filterElements'] = [
            'customers' => $this->model_sale_customer->getCustomers(),
            'users'    => $this->model_wkpos_user->getUsers(),
            'statuses' => $order_statuses,
            'totalRange' => $this->model_sale_order->getOrderMinMaxTotal(),
            'products' => $this->model_sale_order->getAllOrdersProducts()
        ];

		$this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = 'wkpos/orders.expand';
		$this->response->setOutput($this->render(TRUE));

		/*$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('wkpos/orders.tpl', $data));*/
	}

	public function loadOrders() {
		$json = array();
		$this->load->model('wkpos/orders');

		if (isset($this->request->post['start']) && $this->request->post['start']) {
			$start = $this->request->post['start'];
		} else {
			$start = 0;
		}

		if (isset($this->request->post['order']) && $this->request->post['order']) {
			$order = $this->request->post['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->post['sort']) && $this->request->post['sort']) {
			$sort = $this->request->post['sort'];
		} else {
			$sort = 'o.order_id';
		}

		if (isset($this->request->post['filter_order_id'])) {
			$filter_order_id = $this->request->post['filter_order_id'];
		} else {
			$filter_order_id = null;
		}

		if (isset($this->request->post['filter_txn_id'])) {
			$filter_txn_id = $this->request->post['filter_txn_id'];
		} else {
			$filter_txn_id = null;
		}

		if (isset($this->request->post['filter_customer'])) {
			$filter_customer = $this->request->post['filter_customer'];
		} else {
			$filter_customer = null;
		}

		if (isset($this->request->post['filter_user'])) {
			$filter_user = $this->request->post['filter_user'];
		} else {
			$filter_user = null;
		}

		if (isset($this->request->post['filter_order_status'])) {
			$filter_order_status = $this->request->post['filter_order_status'];
		} else {
			$filter_order_status = null;
		}

		if (isset($this->request->post['filter_total'])) {
			$filter_total = $this->request->post['filter_total'];
		} else {
			$filter_total = null;
		}

		if (isset($this->request->post['filter_date_added'])) {
			$filter_date_added = $this->request->post['filter_date_added'];
		} else {
			$filter_date_added = null;
		}

		if (isset($this->request->post['filter_date_modified'])) {
			$filter_date_modified = $this->request->post['filter_date_modified'];
		} else {
			$filter_date_modified = null;
		}

		if (isset($this->request->post['filter_pos_status']) && !($this->request->post['filter_pos_status'] == '')) {
			$filter_pos_status = $this->request->post['filter_pos_status'];
		} else {
			$filter_pos_status = null;
		}

		$json['orders'] = array();

		$filter_data = array(
			'filter_order_id'      => $filter_order_id,
			'filter_txn_id'        => $filter_txn_id,
			'filter_customer'	     => $filter_customer,
			'filter_user'          => $filter_user,
			'filter_order_status'  => $filter_order_status,
			'filter_total'         => $filter_total,
			'filter_date_added'    => $filter_date_added,
			'filter_date_modified' => $filter_date_modified,
			'sort'                 => $sort,
			'order'                => $order,
			'start'                => $start,
			'limit'                => $this->config->get('config_limit_admin')
		);

		$json['order_total'] = $this->model_wkpos_orders->getTotalOrders($filter_data);

		$results = $this->model_wkpos_orders->getOrders($filter_data);

		foreach ($results as $result) {
			$json['orders'][] = array(
				'order_id'      => $result['order_id'],
				'txn_id'        => $result['txn_id'] ? $result['txn_id'] : 'Online Order',
				'customer'      => $result['customer'],
				'user'          => $result['user'],
				'order_status'  => $result['order_status'],
				'total'         => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
				'view'          => 'sale/order/info?order_id=' . $result['order_id'],
				'edit'          => 'sale/order/update?order_id=' . $result['order_id'],
			);
		}

		if (count($json['orders'])) {
			$json['success'] = 'Success';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function loadOrdersDt() {
		$json = array();
		$this->load->model('wkpos/orders');

		$request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            parse_str(html_entity_decode($this->request->post['filter']), $filterData);
            $filterData = $filterData['filter'];
        }

        $filterData['search'] = null;
        if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
            $filterData['search'] = $request['search']['value'];
        }

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'order_id',
			1 => 'txn_id',
			2 => 'customer',
			3 => 'user',
			4 => 'order_status',
            5 => 'total',
            6 => 'date_added',
            7 => 'date_modified',
            8 => '',
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $data = array(
            'filter_name' => $filter_name,
            'filter_model' => $filter_model,
            'filter_price' => $filter_price,
            'filter_quantity' => $filter_quantity,
            'filter_status' => $filter_status,
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $request['start'],
            'limit' => $request['length']
        );

		$json['orders'] = array();

		//$json['order_total'] = $this->model_wkpos_orders->getTotalOrders($data);

		$results = $this->model_wkpos_orders->getOrdersToFilter($data, $filterData);

		$records = $results['data'];
        $totalData = $results['total'];
        $totalFiltered = $results['totalFiltered'];

		foreach ($records as $result) {
			$json['orders'][] = array(
				'order_id'      => $result['order_id'],
				'txn_id'        => $result['txn_id'] ? $result['txn_id'] : 'Online Order',
				'customer'      => $result['customer'],
				'user'          => $result['user'],
				'order_status'  => $result['order_status'],
				'total'         => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified']))
			);
		}

		$json_data = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData), // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $json['orders'],   // total data array
        );
        $this->response->setOutput(json_encode($json_data));
        return;
	}
}
