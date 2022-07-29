<?php
class ControllerAccountExternalOrder extends Controller {
	private $error = array();

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/externalorder', '', 'SSL');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->language->load('account/externalorder');

		$this->load->model('account/externalorder');


		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		);

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/externalorder', $url, 'SSL'),
			'separator' => $this->language->get('text_separator')
		);

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_order_id'] = $this->language->get('text_order_id');
		$this->data['text_status'] = $this->language->get('text_status');
		$this->data['text_date_added'] = $this->language->get('text_date_added');
		$this->data['text_customer'] = $this->language->get('text_customer');
		$this->data['text_products'] = $this->language->get('text_products');
		$this->data['text_total'] = $this->language->get('text_total');
		$this->data['text_empty'] = $this->language->get('text_empty');
		$this->data['text_comment'] = $this->language->get('text_comment');

		$this->data['button_view'] = $this->language->get('button_view');
		$this->data['button_reorder'] = $this->language->get('button_reorder');
		$this->data['button_continue'] = $this->language->get('button_continue');


		$this->data['orders'] = array();

		//$order_total = $this->model_account_order->getTotalOrders();

		$results = $this->model_account_externalorder->getCustomerExternalOrders();

		foreach ($results as $result) {
			//$product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
			//$voucher_total = $this->model_account_order->getTotalOrderVouchersByOrderId($result['order_id']);

			$this->data['orders'][] = array(
				'order_id'   => $result['id'],
				'name'       => $result['name'] ,
				'status'     => $result['statusname'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['createdon'])),
				'products'   => $result['quantity'],
				'total'      => $this->currency->format($result['price'] * $result['quantity'], "$", "1.0000"),
				'notes'		=>$result['notes']
				//'href'       => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], 'SSL'),
				//'reorder'    => $this->url->link('account/order', 'order_id=' . $result['order_id'], 'SSL')
			);
		}

		//$pagination = new Pagination();
		//$pagination->total = $order_total;
		//$pagination->page = $page;
		//$pagination->limit = 10;
		//$pagination->text = $this->language->get('text_pagination');
		//$pagination->url = $this->url->link('account/order', 'page={page}', 'SSL');

		//$this->data['pagination'] = $pagination->render();

		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/externalorder_list.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/externalorder_list.tpl';
		} else {
			$this->template = 'default/template/account/externalorder_list.tpl';
		}

		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);

		$this->response->setOutput($this->render());
	}
}
?>