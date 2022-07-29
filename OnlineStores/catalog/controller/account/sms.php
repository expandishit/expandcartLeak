<?php
class ControllerAccountSms extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/sms', '', 'SSL');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->language->load('account/sms');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('account/customer');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

			$this->load->model('textplode/textplode');

			$this->model_textplode_textplode->setSmsNotifications($this->session->data['customer_id'], $this->request->post['sms_notifications']);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('account/account', '', 'SSL'));
		}

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

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_sms'),
			'href'      => $this->url->link('account/sms', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		);

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');

		$this->data['entry_sms_notifications'] = $this->language->get('entry_sms_notifications');

		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['button_back'] = $this->language->get('button_back');

		$this->data['action'] = $this->url->link('account/sms', '', 'SSL');

		$this->data['sms'] = $this->model_account_customer->getSMS();

		$this->data['back'] = $this->url->link('account/account', '', 'SSL');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/sms.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/sms.tpl';
		} else {
			$this->template = 'default/template/account/sms.tpl';
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