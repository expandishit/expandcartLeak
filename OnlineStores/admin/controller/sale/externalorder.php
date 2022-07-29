<?php
class ControllerSaleExternalOrder extends Controller {
	private $error = array();

  	public function index() {
		$this->language->load('sale/externalorder');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/externalorder');

    	$this->getList();
  	}
	
  	public function updateStatus() {

		$this->load->model('sale/externalorder');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->model_sale_externalorder->updateOrderStatus($this->request->post['orderId'],$this->request->post['statusValue']);
		}
  	}

  	protected function getList() {

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/externalorder', '', 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['orders'] = array();


		$results = $this->model_sale_externalorder->getOrders();

    	foreach ($results as $result) {
			
			$this->data['orders'][] = array(
				'order_id'      => $result['id'],
				'customer'      => $result['firstname'] . ' ' . $result['lastname'],
				'url'			=> $result['url'],
				'name'			=> $result['name'],
				'category'			=> $result['categoryvalue'],
				'quantity'			=> $result['quantity'],
				'price'			=> $this->currency->format($result['price'], "$", "1.0000"),
				'notes'			=> $result['notes'],
				'statusvalue'        => $result['statusvalue'],
				'total'         => $this->currency->format($result['price'] * $result['quantity'], "$", "1.0000"),
				'createdon'    => date($this->language->get('date_format_short'), strtotime($result['createdon'])),
				'action'	=> $this->url->link('sale/externalorder/updatestatus', '&id=' . $result['id'], 'SSL')
			);
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_missing'] = $this->language->get('text_missing');

		$this->data['column_order_id'] = $this->language->get('column_order_id');
    	$this->data['column_customer'] = $this->language->get('column_customer');
		$this->data['column_url'] = $this->language->get('column_url');
		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_category'] = $this->language->get('column_category');
		$this->data['column_quantity'] = $this->language->get('column_quantity');
		$this->data['column_price'] = $this->language->get('column_price');
		$this->data['column_notes'] = $this->language->get('column_notes');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_total'] = $this->language->get('column_total');
		$this->data['column_createdon'] = $this->language->get('column_createdon');

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

		$this->load->model('localisation/order_status');

    	$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->template = 'sale/externalorder_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		
		$this->response->setOutput($this->render());
  	}

}
?>