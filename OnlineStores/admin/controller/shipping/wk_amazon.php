<?php
class ControllerShippingWkAmazon extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('shipping/wk_amazon');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->model_setting_setting->editSetting('wk_amazon', $this->request->post);

            
            $this->tracking->updateGuideValue('SHIPPING');

            $this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension', 'token=' . $this->session->data['token'] . '&type=shipping', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_none'] = $this->language->get('text_none');

		$data['entry_total'] = $this->language->get('entry_total');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_cost'] = $this->language->get('entry_cost');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['help_total'] = $this->language->get('help_total');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension', 'token=' . $this->session->data['token'] . '&type=shipping', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/shipping/wk_amazon', 'token=' . $this->session->data['token'], true)
		);

		$data['action'] = $this->url->link('extension/shipping/wk_amazon', 'token=' . $this->session->data['token'], true);

		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=shipping', true);

		if (isset($this->request->post['wk_amazon_total'])) {
			$data['wk_amazon_total'] = $this->request->post['wk_amazon_total'];
		} else {
			$data['wk_amazon_total'] = $this->config->get('wk_amazon_total');
		}
//wk_amazon_cost
		if (isset($this->request->post['wk_amazon_cost'])) {
			$data['wk_amazon_cost'] = $this->request->post['wk_amazon_cost'];
		} else {
			$data['wk_amazon_cost'] = $this->config->get('wk_amazon_cost');
		}
		if (isset($this->request->post['wk_amazon_geo_zone_id'])) {
			$data['wk_amazon_geo_zone_id'] = $this->request->post['wk_amazon_geo_zone_id'];
		} else {
			$data['wk_amazon_geo_zone_id'] = $this->config->get('wk_amazon_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['wk_amazon_status'])) {
			$data['wk_amazon_status'] = $this->request->post['wk_amazon_status'];
		} else {
			$data['wk_amazon_status'] = $this->config->get('wk_amazon_status');
		}


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('shipping/wk_amazon.tpl', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/wk_amazon')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
