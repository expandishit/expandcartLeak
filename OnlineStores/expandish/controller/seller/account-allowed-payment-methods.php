<?php

class ControllerSellerAccountAllowedPaymentMethods extends ControllerSellerAccount {
	public function index() {
        
        $this->load->model('seller/allowed_payment_methods');

        $sellerId = $this->customer->getId();
        
        if (isset($this->request->post['ms_allowed_payment_methods']))
        {
            $this->model_seller_allowed_payment_methods->save($sellerId, $this->request->post['ms_allowed_payment_methods']);
            $this->redirect($this->url->link('seller/account-allowed-payment-methods', '', 'SSL'));
            return;
        }

		$this->data['link_back'] = $this->url->link('account/account', '', 'SSL');
		
		$this->document->setTitle($this->language->get('ms_account_dashboard_heading'));
		
		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_account_dashboard_nav_allowed_payment_methods'),
				'href' => $this->url->link('seller/account-allowed-payment-methods', '', 'SSL'),
			)
		));
		
        $this->data['lang'] = $this->config->get('config_language');
        $this->data['payment_methods'] = $this->model_seller_allowed_payment_methods->getActiveMethods();
        $this->data['ms_seller_allowed_payment_methods'] = $this->model_seller_allowed_payment_methods->getSellerAllowedPaymentMethods($sellerId);
        $this->data['form_action'] = $this->url->link('seller/account-allowed-payment-methods', '', 'SSL');
        $this->data['ms_allowed_payment_methods'] = $this->language->get('ms_account_dashboard_nav_allowed_payment_methods');
        $this->data['ms_button_save'] = $this->language->get('ms_button_save');

        list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('account-allowed-payment-methods');
        
		$this->response->setOutput($this->render());
	}

}
