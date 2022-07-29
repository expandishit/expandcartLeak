<?php 
class ControllerAccountAccount extends Controller { 
	public function index(){
		if (!$this->customer->isLogged()) {
	  		$this->session->data['redirect'] = $this->url->link('account/account', '', 'SSL');
	  
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
         
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            return $this->renderContent();
        }
        
        $this->data['return_type'] = $this->config->get('config_return_type') ? $this->config->get('config_return_type') : "return";

		$this->language->load_json('account/account');

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
		

        //Check success, warning, error variables and add them to data array (if any).
        $this->_setAlertVariables();
        

        //Checking Product Video Links App is installed to show my videos page link
        $this->_addProductVideoLinksAppData();


        //Checking Auctions App is installed to show my Auctions page link
        $this->_addAuctionsAppData();

    	//$this->data['edit'] = $this->url->link('account/edit', '', 'SSL');
    	//$this->data['password'] = $this->url->link('account/password', '', 'SSL');
		//$this->data['address'] = $this->url->link('account/address', '', 'SSL');
		//$this->data['wishlist'] = $this->url->link('account/wishlist');
    	//$this->data['order'] = $this->url->link('account/order', '', 'SSL');
    	//$this->data['download'] = $this->url->link('account/download', '', 'SSL');
		//$this->data['return'] = $this->url->link('account/return', '', 'SSL');
		//$this->data['transaction'] = $this->url->link('account/transaction', '', 'SSL');
		//$this->data['newsletter'] = $this->url->link('account/newsletter', '', 'SSL');
		
		//if ($this->config->get('reward_status')) {
		//	$this->data['reward'] = $this->url->link('account/reward', '', 'SSL');
		//} else {
		//	$this->data['reward'] = '';
		//}

        //Check if MS & MS Messaging is installed
        $this->load->model('multiseller/status');
        $multiseller = $this->model_multiseller_status->is_installed();


        $this->data['ms_is_active'] = false;
        $this->data['ms_messaging_is_active'] = false;
        if($multiseller) 
        {
            //get seller title
            $this->load->model('seller/seller');
            $seller_title = $this->model_seller_seller->getSellerTitle();

            $multisellerMessaging = $this->model_multiseller_status->is_messaging_installed();
            if($multisellerMessaging)
                $this->data['ms_messaging_is_active'] = true;
            $this->data['ms_seller_created'] = $this->MsLoader->MsSeller->isCustomerSeller($this->customer->getId());
            $this->data['ms_seller_active'] = ($this->MsLoader->MsSeller->getStatus($this->customer->getId()) == MsSeller::STATUS_ACTIVE);
            $this->data['ms_is_active'] = true;
            // $this->data = array_merge($this->data, $this->language->load_json('multiseller/multiseller'));
            $this->language->load_json('multiseller/multiseller');
            $this->data['ms_account_sellerinfo_new'] = sprintf($this->language->get('ms_account_sellerinfo_new') , $seller_title );
            $this->data['ms_account_sellerinfo'] = sprintf($this->language->get('ms_account_sellerinfo') , $seller_title );
        
            $this->load->model('account/messagingseller');
            $this->data['unread_messages_count'] = $this->model_account_messagingseller->getTotalUnreadForUser($this->customer->getId())['unread_count'] ?? 0;
        }
        ///////////////////////////////////////////


        /*$queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        $this->data['ms_is_active'] = false;

        if($queryMultiseller->num_rows) {
            $this->data['ms_seller_created'] = $this->MsLoader->MsSeller->isCustomerSeller($this->customer->getId());
            $this->data['ms_seller_active'] = ($this->MsLoader->MsSeller->getStatus($this->customer->getId()) == MsSeller::STATUS_ACTIVE);
            $this->data['ms_is_active'] = true;
            $this->data = array_merge($this->data, $this->language->load_json('multiseller/multiseller'));
        }*/

        $networkMarketingApplication = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'network_marketing'"
        );

        $this->data['networkMarketing']['installed'] = false;

        if($networkMarketingApplication->num_rows) {

            $this->data['networkMarketing']['installed'] = true;

            $this->load->model('network_marketing/settings');

            $networkMarketingSettings = $this->model_network_marketing_settings->getSettings();

            $this->data['networkMarketing']['status'] = $networkMarketingSettings['nm_status'];
        }

        //Buyer Subscription Plan App
        $this->data['buyer_subscription_plan_is_installed'] = \Extension::isInstalled('buyer_subscription_plan') && $this->config->get('buyer_subscription_plan_status');
        
        if($this->data['buyer_subscription_plan_is_installed']){
            $this->load->model('account/subscription');
            $buyer_subscription_plan  = $this->model_account_subscription->getCustomerSubscriptionPlan($this->customer->getId()); //if empty, then it's free plan
            
            //paid subscription plan
            if(!empty($buyer_subscription_plan)){
                $this->data['buyer_subscription_plan_expiration_date'] = $this->model_account_subscription->getSubsciptionExpirationDate($buyer_subscription_plan)->format('d/m/Y');
                $this->data['buyer_subscription_plan'] = $buyer_subscription_plan;
            }
        }

        //render view template
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/account.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/account.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/account/account.expand';
        }
        
        $this->children = array(
            'common/footer',
            'common/header'
        );

		$this->response->setOutput($this->render_ecwig());
  	}

    public function country() {
        $json = array();

        $this->load->model('localisation/country');

        $country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

        if ($country_info) {
            $this->load->model('localisation/zone');

            $json = array(
                'country_id'        => $country_info['country_id'],
                'name'              => $country_info['name'],
                'iso_code_2'        => $country_info['iso_code_2'],
                'iso_code_3'        => $country_info['iso_code_3'],
                'address_format'    => $country_info['address_format'],
                'postcode_required' => $country_info['postcode_required'],
                'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
                'status'            => $country_info['status']
            );
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function zone()
    {
        $json = ['success' => false];

        $this->load->model('localisation/zone');

        $zone_info = $this->model_localisation_zone->getZone($this->request->get['zone_id']);

        if ($zone_info) {
            $this->load->model('localisation/area');

            $data = array(
                'zone_id'        => $zone_info['country_id'],
                'name'              => $zone_info['name'],
                'area'              => $this->model_localisation_area->getAreaByZoneId($this->request->get['zone_id']),
                'status'            => $zone_info['status']
            );

            $json['success'] = true;
            $json['data'] = $data;
        }

        $this->response->setOutput(json_encode($json));
    }

    /**
    * Check if Product Video Links App is installed to show videos,
    * Then append required data to $this->data[] array to render in view.
    *
    * @return void
    */
    private function _addProductVideoLinksAppData(){

        $this->load->model('module/product_video_links');
        
        $product_video_links_installed =  $this->model_module_product_video_links->isInstalled();

        $this->data['show_videos'] = $product_video_links_installed;

        if($product_video_links_installed){
            //Get Seller title from setting table 
            $this->load->model('seller/seller');
            $products_title = $this->model_seller_seller->getProductsTitle();
            $this->data['text_my_products'] = sprintf($this->language->get('text_my_products'), $products_title);
        }
    }



    /**
    * Check if Auctions App is installed to show auctions,
    * Then append required data to $this->data[] array to render in view.
    *
    * @return void
    */
    private function _addAuctionsAppData(){

        $this->load->model('module/auctions/auction');
        
        $auctions_app_installed = \Extension::isInstalled('auctions') && $this->config->get('auctions_status');
        
        $this->data['auctions_app_installed'] = $auctions_app_installed;
    }
    
    /**
     * Set session alert variables in data array
     * @return void
     */
    private function _setAlertVariables():void{
        $this->data['success'] = $this->data['warning'] = $this->data['error'] = '';
        
        //success
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];            
            unset($this->session->data['success']);
        }

        //warning
        if (isset($this->session->data['warning'])) {
            $this->data['warning'] = $this->session->data['warning'];            
            unset($this->session->data['warning']);
        }

        //error
        if (isset($this->session->data['error'])) {
            $this->data['error'] = $this->session->data['error'];            
            unset($this->session->data['error']);
        }
    }


    private function renderContent()
    {
        $this->data['taps'] = $this->getChild('account/taps/renderTaps', 'account-edit');
            
        $this->language->load_json('account/edit', 1);
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
          'text'      => $this->language->get('text_home'),
          'href'      => $this->url->link('common/home'),     	
          'separator' => false
        ); 

        $this->data['breadcrumbs'][] = array(
          'text'      => $this->language->get('text_edit'),
          'href'      => $this->url->link('account/edit', '', 'SSL'),       	
          'separator' => $this->language->get('text_separator')
        );
        
        $this->data['entry_name'] = $this->language->get('entry_name');
        $this->data['entry_email'] = $this->language->get('entry_email');
        $this->data['entry_telephone'] = $this->language->get('entry_telephone');
        $this->data['entry_company'] = $this->language->get('entry_company');
        $this->data['entry_fax'] = $this->language->get('entry_fax');
        $this->data['entry_dob'] = $this->language->get('entry_dob');
        $this->data['entry_gender'] = $this->language->get('entry_gender');
        $this->data['entry_newsletter'] = $this->language->get('text_newsletter');
        $this->data['entry_gender_m'] = $this->language->get('entry_gender_m');
        $this->data['entry_gender_f'] = $this->language->get('entry_gender_f');
        $this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
        
        $this->document->addScript("expandish/view/javascript/iti/js/intlTelInput.min.js");
        
        if (is_array($this->config->get('config_customer_group_display'))) {
            $customerGroupModel = $this->load->model('account/customer_group', ['return' => true]);
            
            $groups = $customerGroupModel->getCustomerGroups();
            $customerGroups = [];
            foreach ($groups as $group) if (in_array($group['customer_group_id'], $this->config->get('config_customer_group_display'))) {
                $customerGroups[] = $group;
            }
            
            if(!empty($customerGroups)) $this->data['customer_groups'] = $customerGroups;
        }
        
        // $result = $this->identity->getCustomer($this->customer->getExpandId());
        
        // if ($result['success']) {
            // $this->data['customer'] = $result['data'];
            
        $customerModel = $this->load->model('account/customer', ['return' => true]);
        $customer = $customerModel->getCustomer($this->customer->getId());
        $this->data['customer'] = $customer;

        $this->data['customer']['customer_group_id'] = ($customer && isset($customer['customer_group_id'])) ? $customer['customer_group_id'] : $this->config->get('config_customer_group_id');
        $this->data['customer']['newsletter'] = ($customer && isset($customer['newsletter'])) ? $customer['newsletter'] : 0;
        $this->data['customer_fields'] = $this->config->get('config_customer_fields')['registration'];
        $this->data['customer_fields']['email'] = $this->identity->isLoginByPhone() ? 0 : 1;
        $this->data['customer_fields']['telephone'] = $this->identity->isLoginByPhone() ? 1 : 0;
        // }

        //This is to handle new template structure using extend
        $this->data['include_page'] = 'edit.expand';
        if(USES_TWIG_EXTENDS == 1)
            $this->template = 'default/template/account/layout_extend.expand';
        else
            $this->template = 'default/template/account/layout_default.expand';
        ///////////

        $this->response->setOutput($this->render_ecwig());
    }
    
}
