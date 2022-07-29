<?php

class ControllerSellerCommissionPriceList extends ControllerSellerAccount {
	public function index() {
		$this->document->setTitle( $this->language->get('ms_sale_commission_pricelist_heading') );
		$this->data['link_back'] = $this->url->link('seller/account-dashboard', '', 'SSL');
		$this->data['currency']  = $this->config->get("config_currency");
 		$this->data['items'] = $this->MsLoader->MsCommission->getPriceList( $this->customer->getId());
		$this->data['th_alignment'] = $this->config->get('config_language') == 'ar' ? 'text-align: right;' : 'text-align: left;';
		// var_dump($this->data['th_alignment']);die();
		list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('commission-price-list');
		$this->response->setOutput($this->render());
	}
}
