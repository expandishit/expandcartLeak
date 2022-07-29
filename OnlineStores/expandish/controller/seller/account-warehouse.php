<?php
class ControllerSellerAccountWarehouse extends ControllerSellerAccount {	
	public function jxSaveWarehouseInfo() {
		
		$data = $this->request->post['warehouse'];
		$json = array();
		$json['redirect'] = $this->url->link('seller/account-dashboard');

		if(empty($data['name'])){
			$json['errors']['warehouse[name]'] = $this->language->get('ms_error_warehouse_name_empty');			
		}
		
		foreach ($data['rates'] as $rate) {
			if(empty($rate)){
				$json['errors']['warehouse[rates]'] = $this->language->get('ms_account_sellerinfo_rates_empty');	
				break;
			}
		}

		/*if (empty($data['charge']) || !is_numeric($data['charge'])) {
			$json['errors']['warehouse[charge]'] = $this->language->get('ms_error_warehouse_charge_empty');		
		}

		if ( $data['country'] == 0 ) {
			$json['errors']['warehouse[country]'] = $this->language->get('ms_error_warehouse_country_empty');		
		}

		if ( $data['zone'] == 0 ) {
			$json['errors']['warehouse[zone]'] = $this->language->get('ms_error_warehouse_city_empty');		
		}*/

		if (empty($json['errors'])) {
			///// Warehouses
			$this->load->model('module/warehouses');
			$seller_id = $this->customer->getId();
			$seller_warehouse = $this->model_module_warehouses->seller_warehouse($seller_id);
			////////////////
			$data['rates'] = json_encode($data['rates']);
			$data['duration'] = json_encode($data['duration']);
			
			if (empty($seller_warehouse)) {
				$this->model_module_warehouses->create_warehouse($seller_id, $data);
				$this->session->data['success'] = $this->language->get('ms_account_warehouse_created');
			} else {
				$this->model_module_warehouses->update_warehouse($seller_id, $seller_warehouse['id'], $data);
				$this->session->data['success'] = $this->language->get('ms_account_warehouse_updated');
			}
		}
		
		$this->response->setOutput(json_encode($json));
	}

	public function index() {
		$seller = $this->MsLoader->MsSeller->getSeller($this->customer->getId());
		
		$this->data['link_back'] = $this->url->link('seller/account-dashboard', '', 'SSL');
		$this->data['seller_required_fields'] = $this->config->get('msconf_seller_required_fields');
		$this->data['seller_show_fields'] = $this->config->get('msconf_seller_show_fields');

		$this->document->addScript('expandish/view/javascript/jquery/tabs.js');

		$this->document->setTitle($this->language->get('ms_account_warehouse_heading'));
		
		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_account_dashboard_breadcrumbs'),
				'href' => $this->url->link('seller/account-dashboard', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_account_warehouse_heading'),
				'href' => $this->url->link('seller/account-warehouse', '', 'SSL'),
			)
		));

		$this->load->model('localisation/country');
		$this->data['countries'] = $this->model_localisation_country->getCountries();

		///// Warehouses
			$this->data['warehouse'] = [];
			$this->load->model('module/warehouses');
			if($this->model_module_warehouses->is_installed()){
				$seller_id = $this->customer->getId();
				$seller_warehouse = $this->model_module_warehouses->seller_warehouse($seller_id);
				$this->data['warehouse'] = $seller_warehouse;
			}else{
				$this->redirect($this->url->link('seller/account-dashboard', '', 'SSL'));
			}
		////////////////

		if (!empty($seller_warehouse)) {
			$this->data['rates'] = json_decode($seller_warehouse['rates'], true);
		} else {
			$this->data['rates'] = '';
		}

		if (!empty($seller_warehouse)) {
			$this->data['duration'] = json_decode($seller_warehouse['duration'], true);
		} else {
			$this->data['duration'] = '';
		}

		$this->load->model('localisation/geo_zone');
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['currency'] = $this->config->get('config_currency');
		
		list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('account-warehouse');
		$this->response->setOutput($this->render());
	}
}
?>
