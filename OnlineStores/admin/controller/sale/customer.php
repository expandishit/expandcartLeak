<?php

use ExpandCart\Foundation\Exceptions\FileException;

class ControllerSaleCustomer extends Controller
{
	private $error = array();

	public function dtDelete()
	{
        $this->load->model('sale/customer');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $id_s = $this->request->post['id'];


        $this->load->model('loghistory/histories');
        $this->load->model('setting/audit_trail');
        $pageStatus = $this->model_setting_audit_trail->getPageStatus("customer");
        if ( is_array($id_s) )
        {

            foreach ($id_s as $customer_id)
            {
                // get current value for log history
                if($pageStatus) {
                    $oldValue = $this->model_sale_customer->getCustomer((int)$customer_id);
                    // add data to log_history
                    $log_history['action'] = 'delete';
                    $log_history['reference_id'] = (int)$customer_id;
                    $log_history['old_value'] = json_encode($oldValue);
                    $log_history['new_value'] = NULL;
                    $log_history['type'] = 'customer';
                    $this->model_loghistory_histories->addHistory($log_history);
                }

                $this->model_sale_customer->deleteCustomer( (int) $customer_id );
                    // Odoo delete customers if app is installed
                if (\Extension::isInstalled('odoo') && $this->config->get('odoo')['status'] 
                && $this->config->get('odoo')['customers_integrate']) 
                {
                    $this->load->model('module/odoo/customers');
                    $this->model_module_odoo_customers->deleteCustomer($customer_id);
                }
            }
        }
        else
        {
            $customer_id = (int) $id_s;
            if($pageStatus) {
                // get current value for log history
                $oldValue = $this->model_sale_customer->getCustomer($customer_id);
                // add data to log_history
                $log_history['action'] = 'delete';
                $log_history['reference_id'] = $customer_id;
                $log_history['old_value'] = json_encode($oldValue);
                $log_history['new_value'] = NULL;
                $log_history['type'] = 'customer';
                $this->model_loghistory_histories->addHistory($log_history);
            }

            $this->model_sale_customer->deleteCustomer( $customer_id );
        $id_s = is_array($id_s) ? $id_s : [$id_s];
        
        // ZOHO inventory delete customers
        $this->load->model('module/zoho_inventory');
        
        foreach ($id_s as $customer_id)
        {
            $this->model_sale_customer->deleteCustomer( (int) $customer_id );
            $this->model_module_zoho_inventory->deleteCustomer( (int)$customer_id );

           

        }

        $result_json['success'] = '1';
        $result_json['success_msg'] = $this->language->get('text_success');

        $this->response->setOutput(json_encode($result_json));
        return;

	    }
	}

	public function dtHandler() {
        $this->load->model('sale/customer');
        $request = $_REQUEST;
        $request = $_REQUEST;
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            parse_str(html_entity_decode($this->request->post['filter']), $filterData);
            $filterData = $filterData['filter'];
        }
        
        $filterData['search'] = null;
        if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
            $filterData['search'] = $request['search']['value'];
        }

        //Newsletter subscribers group
        if($filterData['customer_group_id'][0] == -1){
            $this->language->load('sale/customer');
            $this->load->model('sale/newsletter/subscriber');
            
            // $unregistered = $this->model_sale_newsletter_subscriber->getUnregisteredSubscribers($this->language->get('btnt_newsletter_subscribers'));        
            // $registered = $this->model_sale_newsletter_subscriber->getRegisteredSubscribers($this->language->get('btnt_newsletter_subscribers'));        
            // $r = array_merge($unregistered,$registered);
            $r = $this->model_sale_newsletter_subscriber->getAllSubscribers($filterData, $this->language->get('btnt_newsletter_subscribers'));
            $json_data = array(
                "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
                "recordsTotal" => count($r), // total number of records
                "recordsFiltered" => count($r), // total number of records after searching, if there is no searching then totalFiltered = totalData
                "data" => $r,   // total data array
            );
            $this->response->setOutput(json_encode($json_data));
            return;
        }
        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => '',
            1 => 'CONCAT(firstname, lastname)',
            2 => 'email',
            3 => 'telephone',
            4 => 'customer_group_id',
            5 => 'ip',
            6 => 'date_added',
            7 => 'status',
            8 => '',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];
        $return = $this->model_sale_customer->dtHandler($start, $length, $filterData, $orderColumn, $orderType);
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        // set new prop [profile_completed] to customer's data based on account settings
        $customerAccountFields = $this->config->get('config_customer_fields')['registration'] ?? [];
        $customerFieldsRequired = array_filter($customerAccountFields, function ($v, $k) {return $k !== 'terms' && (int)$v === 1;}, ARRAY_FILTER_USE_BOTH);
        $isIdentityMode = defined('LOGIN_MODE') && LOGIN_MODE === 'identity' &&$this->identity->isStoreOnWhiteList();
        $records = array_map(function($record) use($customerFieldsRequired, $isIdentityMode) {
            $record['profile_completed'] = true;
            if ($isIdentityMode) {
                foreach ($customerFieldsRequired as $key => $value) {
                    if (isset($record[$key]) && empty($record[$key])) {
                        $record['profile_completed'] = !true;
                        break;
                    }
                }
                
                $record['name'] = $record['firstname'];
            }
            return $record;
        }, $records);
                
        $json_data = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData), // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $records,   // total data array
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function index() {
		$this->language->load('sale/customer');
        $this->language->load('catalog/product_filter');

		$this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/customer');
        $this->load->model('sale/customer_group');

    	$this->getList();
  	}
      
    public function insert()
    {
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) return $this->insertV2();
        
        $this->language->load('sale/customer');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('sale/component/customers', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('breadcrumb_insert'),
            'href'      => $this->url->link('sale/component/customers', '', 'SSL'),
            'separator' => ' :: '
        );

    	$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/customer');
        $this->load->model('module/signup');
        $modSettings = $this->model_module_signup->getModuleSettings();

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            if(isset($modSettings['login_register_phonenumber_enabled']) && $modSettings['login_register_phonenumber_enabled']){
                $this->request->post['login_register_phonenumber_enabled'] = $modSettings['login_register_phonenumber_enabled'];
            }

            if (!isset($this->request->post['status'])) {
                $this->request->post['status'] = '0';
            }

            if (!isset($this->request->post['newsletter'])) {
                $this->request->post['newsletter'] = '0';
            }

		    if (!$this->validateForm()) {
                $response['success'] = '0';
                $response['errors'] = $this->error;

                $this->response->setOutput(json_encode($response));
                return;
            }

      	  	$customer_id = $this->model_sale_customer->addCustomer($this->request->post);


            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("customer");
            if($pageStatus) {
                $log_history['action'] = 'add';
                $log_history['reference_id'] = $customer_id;
                $log_history['old_value'] = NULL;
                $log_history['new_value'] = json_encode($this->request->post);
                $log_history['type'] = 'customer';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

            
            // ZOHO inventory create customer if app is setup
            $this->load->model('module/zoho_inventory');
            $this->model_module_zoho_inventory->createCustomer($customer_id, $this->request->post);

             // Odoo create customer if app is installed
           if (\Extension::isInstalled('odoo') && $this->config->get('odoo')['status'] 
           && $this->config->get('odoo')['customers_integrate']) 
           {
            $this->load->model('module/odoo/customers');
            $this->model_module_odoo_customers->createCustomer($customer_id, $this->request->post);
           }


            /**
             *-----------------------------
             * Update Abandoned Orders
             * ----------------------------
             * Search for all abandoned orders related to this new customer and assign it to him.
             */
		    $this->load->model('sale/order');
            $abandoned_orders = $this->model_sale_order->getOrdersByCustomerEmail($this->request->post['email'], true);
            if ($abandoned_orders) {
                $data = array(
                    "customer_id" => $customer_id,
                    "customer_group_id" => $this->request->post['customer_group_id'],
                    "email" => $this->request->post['email']
                );
                foreach ($abandoned_orders as $abandoned_order) {
                    $result = $this->model_sale_order->updateOrderFields($abandoned_order['order_id'],$data);
                }
            }

            $response['success'] = '1';
            $response['success_msg'] = $this->language->get('notification_inserted_successfully');
            $response['redirect'] = '1';
            $response['to'] = (string) $this->url->link('sale/component/customers' , '', 'SSL');

            $this->response->setOutput(json_encode($response));
            return;
		}

        $this->data['links'] = [
            'submit' => $this->url->link('sale/customer/insert', '', 'SSL'),
            'cancel' => $this->url->link('sale/component/customers', '', 'SSL')
        ];

        $this->load->model('sale/customer_group');

        $this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

        $this->load->model('localisation/country');

        $this->data['countries'] = $this->model_localisation_country->getCountries();
        $this->data['login_register_phonenumber_enabled'] = $modSettings['login_register_phonenumber_enabled'];


        $this->template = 'sale/customer/insert.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
  	}
      
    public function update() {
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) return $this->updateV2();
        
        $customer_id = (int)$this->request->get['customer_id'];
        if(!preg_match("/^[1-9][0-9]*$/", $customer_id)) return false;

		$this->language->load('sale/customer');
        $this->load->model('module/signup');
        $modSettings = $this->model_module_signup->getModuleSettings();

    	$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/customer');

        $this->data['total_balance_points'] = $this->currency->format($this->model_sale_customer->getTransactionTotal($customer_id), $this->config->get('config_currency'));
        $this->data['total_reward_points'] = $this->model_sale_customer->getRewardTotal($customer_id);

		if ( empty($this->model_sale_customer->getCustomer($customer_id)) )
		{
			$this->language->load('common/home');
			$this->data['no_customer'] = true;
			$this->data['title_no_customer_exists'] = $this->language->get('title_no_customer_exists');
		}
        if(\Extension::isInstalled('multiseller') ){
            $this->load->model('multiseller/seller');
            $seller = $this->model_multiseller_seller->getSellerInfo($customer_id);
            if($seller['custom_fields']){
                $custom_fields = unserialize($seller['custom_fields']);
                $ms_seller_files = array();
                $ms_seller_images = array();
                $ms_seller_files_extensions = explode(',',$this->config->get('msconf_seller_allowed_files_types'));
                $pdf = array_search('pdf', $ms_seller_files_extensions);
                if (false !== $pdf) {
                    unset($ms_seller_files_extensions[$pdf]);
                }
                $images_extensions = $ms_seller_files_extensions;
                foreach($custom_fields as $key=>$field){
                    if(end(explode('.',$field)) == 'pdf'){
                        $ms_seller_files[$key]['mask'] =  end(explode('_',$field));
                        $ms_seller_files[$key]['href'] = $this->url->link('multiseller/seller/download', 'download=' . $field, 'SSL');
                    }elseif (in_array(end(explode('.',$field)),$images_extensions)){
                        $ms_seller_images[$key] = $this->MsLoader->MsFile->resizeImage($field, $this->config->get('msconf_preview_product_image_width'), $this->config->get('msconf_preview_product_image_height'));
                    }
                }
                
                $this->data['ms_seller_files'] = $ms_seller_files;
                $this->data['ms_seller_images'] = $ms_seller_images;
            }
        }

    	if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
                
            if(isset($modSettings['login_register_phonenumber_enabled']) && $modSettings['login_register_phonenumber_enabled']){
                $this->request->post['login_register_phonenumber_enabled'] = $modSettings['login_register_phonenumber_enabled'];
            }
            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }
            $oldValue = $this->model_sale_customer->getCustomer($this->request->get['customer_id']);

            $this->model_sale_customer->editCustomer($customer_id, $this->request->post);
            
            // ZOHO inventory edit customer if app is setup
            $this->load->model('module/zoho_inventory');
            $this->model_module_zoho_inventory->updateCustomer($customer_id, $this->request->post);

            // Odoo edit customer if app is installed
           if (\Extension::isInstalled('odoo') && $this->config->get('odoo')['status'] 
           && $this->config->get('odoo')['customers_integrate']) 
           {
            $this->load->model('module/odoo/customers');
            $this->model_module_odoo_customers->updateCustomer($customer_id, $this->request->post);
           }

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("customer");
            if($pageStatus) {
                $log_history['action'] = 'update';
                $log_history['reference_id'] = $this->request->get['customer_id'];
                $log_history['old_value'] = json_encode($oldValue);
                $log_history['new_value'] = json_encode($this->request->post);
                $log_history['type'] = 'customer';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }


			$queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

            if($queryMultiseller->num_rows) {
                $this->load->model('multiseller/seller');

                if (isset($this->request->post['tax_card'])) {
                    $this->model_multiseller_seller->editSellerColumn($customer_id, 'tax_card',$this->request->post['tax_card']);
                }

                if (isset($this->request->post['bank_name'])) {
                    $this->model_multiseller_seller->editSellerColumn($customer_id, 'bank_name', $this->request->post['bank_name']);
                }

                if (isset($this->request->post['bank_iban'])) {
                    $this->model_multiseller_seller->editSellerColumn($customer_id, 'bank_iban', $this->request->post['bank_iban']);
                }

                if (isset($this->request->post['bank_transfer'])) {
                    $this->model_multiseller_seller->editSeller($customer_id, $this->request->post['bank_transfer']);
                }
            }

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
		}

        $this->data['links'] = [
            'submit' => $this->url->link(
                'sale/customer/update',
                'customer_id=' . $customer_id,
                'SSL'
            ),
            'cancel' => $this->url->link('sale/component/customers', '', 'SSL')
        ];
        $this->data['login_register_phonenumber_enabled'] = $modSettings['login_register_phonenumber_enabled'];
    	$this->getForm();
  	}

    public function delete() {
        $this->language->load('sale/customer');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/customer');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            $this->load->model('loghistory/histories');
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("customer");
            // ZOHO inventory delete customers
            $this->load->model('module/zoho_inventory');
            foreach ($this->request->post['selected'] as $customer_id) {
                if ($pageStatus) {
                    // get current value for log history
                    $oldValue = $this->model_sale_customer->getCustomer((int)$customer_id);
                    // add data to log_history
                    $log_history['action'] = 'delete';
                    $log_history['reference_id'] = (int)$customer_id;
                    $log_history['old_value'] = json_encode($oldValue);
                    $log_history['new_value'] = NULL;
                    $log_history['type'] = 'customer';
                    $this->model_loghistory_histories->addHistory($log_history);
                }
                $this->model_sale_customer->deleteCustomer((int)$customer_id);
                $this->model_module_zoho_inventory->deleteCustomer((int)$customer_id);

                // Odoo delete customers if app is installed
                if (\Extension::isInstalled('odoo') && $this->config->get('odoo')['status'] 
                && $this->config->get('odoo')['customers_integrate']) 
                {
                    $this->load->model('module/odoo/customers');
                    $this->model_module_odoo_customers->deleteCustomer($customer_id);
                }

            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['filter_name'])) {
                $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_email'])) {
                $url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_customer_group_id'])) {
                $url .= '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'];
            }

            if (isset($this->request->get['filter_status'])) {
                $url .= '&filter_status=' . $this->request->get['filter_status'];
            }

            if (isset($this->request->get['filter_approved'])) {
                $url .= '&filter_approved=' . $this->request->get['filter_approved'];
            }

            if (isset($this->request->get['filter_ip'])) {
                $url .= '&filter_ip=' . $this->request->get['filter_ip'];
            }

            if (isset($this->request->get['filter_date_added'])) {
                $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->redirect($this->url->link('sale/component/customers', '', 'SSL'));
        }

        $this->getList();
    }

	public function approve() {
        $response = array (
            'status' => 'error',
            'title' => '',
            'errors' => ''
        );
		$this->language->load('sale/customer');
		$this->load->model('sale/customer');

		if (!$this->user->hasPermission('modify', 'sale/customer')) {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors']['error'] = $this->language->get('error_permission');
		} elseif (isset($this->request->post['selected'])) {
            $approved = 0;

            foreach ($this->request->post['selected'] as $customer_id) {
                $customer_info = $this->model_sale_customer->getCustomer($customer_id);


				/**
				 * This code has been commented because when the admin tries to approve a registered user
				 * but this user didn't open or receive confirmation email the system doesn't allow this
				 * and we have to delete the customer and re-create it.
				 * This case happens when the customer information approve field is set to "2"
				 */
                // if ($customer_info && !$customer_info['approved']) {
                //     $this->model_sale_customer->approve($customer_id);

                //     $queryRewardPointInstalled = $this->db->query(
                //         "SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'"
                //     );

                //     if ($queryRewardPointInstalled->num_rows) {

                //         $this->load->model('promotions/reward_points_observer');

                //         $this->model_promotions_reward_points_observer->afterApproveCustomer($customer_id);

                //     }

                //     $approved++;
				// }
				
				if ($customer_info) {
                    $this->model_sale_customer->approve($customer_id);

                    $queryRewardPointInstalled = $this->db->query(
                        "SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'"
                    );

                    if ($queryRewardPointInstalled->num_rows) {

                        $this->load->model('promotions/reward_points_observer');

                        $this->model_promotions_reward_points_observer->afterApproveCustomer($customer_id);

                    }

                    $approved++;
				}
				
            }
            $response['success'] = '1';
            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = sprintf($this->language->get('text_approved'), $approved);
        } else {
            $response['success'] = '0';
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors']['error'] = $this->language->get('notification_unknown_error');
        }

        $this->response->setOutput(json_encode($response));
        return;
	}

    public function changeStatus()
    {
        $response = array (
            'status' => 'error',
            'title' => '',
            'errors' => ''
        );
        $this->load->model("sale/customer");
        if (!$this->user->hasPermission('modify', 'sale/customer')) {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors']['error'] = $this->language->get('error_permission');
        } elseif (isset($this->request->post["customer_id"]) && isset($this->request->post["status"])) {
            $id = $this->request->post["customer_id"];
            $status = $this->request->post["status"];
            $this->load->model('sale/customer');
            $this->load->model('setting/audit_trail');
            $this->load->model('module/zoho_inventory');

            $pageStatus = $this->model_setting_audit_trail->getPageStatus("customer");

            if ( is_array($id) )
            {
                foreach ($id as $customer_id)
                {
                    $customer = $this->model_sale_customer->getCustomer((int)$customer_id);
                    $oldValue= $customer;
                    $customer['status'] = $status;
                    // add data to log_history
                    if($pageStatus) {
                        $log_history['action'] = 'update';
                        $log_history['reference_id'] = $customer_id;
                        $log_history['old_value'] = json_encode($oldValue);
                        $coupon['status'] = $status;
                        $log_history['new_value'] = json_encode($customer);
                        $log_history['type'] = 'customer';
                        $this->load->model('loghistory/histories');
                        $this->model_loghistory_histories->addHistory($log_history);
                    }

                    $this->model_sale_customer->changeStatus($customer_id, $status);

                    // ZOHO inventory change customer status
                    $this->model_module_zoho_inventory->changeCustomerStatus($customer_id, (bool)$status);

                }
            }else{
                $customer = $this->model_sale_customer->getCustomer((int)$id);
                $oldValue= $customer;
                $customer['status'] = $status;
                // add data to log_history
                if($pageStatus) {
                    $log_history['action'] = 'update';
                    $log_history['reference_id'] = $id;
                    $log_history['old_value'] = json_encode($oldValue);
                    $coupon['status'] = $status;
                    $log_history['new_value'] = json_encode($customer);
                    $log_history['type'] = 'customer';
                    $this->load->model('loghistory/histories');
                    $this->model_loghistory_histories->addHistory($log_history);
                }
                $this->model_sale_customer->changeStatus($id, $status);
                // ZOHO inventory change customer status
                $this->model_module_zoho_inventory->changeCustomerStatus($id, (bool)$status);
            }
            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_updated_successfully');
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors']['error'] = $this->language->get('notification_unknown_error');
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

  	protected function getList() {
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}

		if (isset($this->request->get['filter_email'])) {
			$filter_email = $this->request->get['filter_email'];
		} else {
			$filter_email = null;
		}

		if (isset($this->request->get['filter_customer_group_id'])) {
			$filter_customer_group_id = $this->request->get['filter_customer_group_id'];
		} else {
			$filter_customer_group_id = null;
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}

		if (isset($this->request->get['filter_approved'])) {
			$filter_approved = $this->request->get['filter_approved'];
		} else {
			$filter_approved = null;
		}

		if (isset($this->request->get['filter_ip'])) {
			$filter_ip = $this->request->get['filter_ip'];
		} else {
			$filter_ip = null;
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = null;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_customer_group_id'])) {
			$url .= '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_approved'])) {
			$url .= '&filter_approved=' . $this->request->get['filter_approved'];
		}

		if (isset($this->request->get['filter_ip'])) {
			$url .= '&filter_ip=' . $this->request->get['filter_ip'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/component/customers', '', 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['approve'] = $this->url->link('sale/customer/approve', '', 'SSL');
		$this->data['insert'] = $this->url->link('sale/customer/insert', '', 'SSL');
		$this->data['delete'] = $this->url->link('sale/customer/delete', '', 'SSL');
        $this->data['import_port'] = $this->url->link('tool/w_import_tool', '', 'SSL');
        $this->data['export_port'] = $this->url->link('tool/w_export_tool', '', 'SSL');

		$this->data['customers'] = array();

		$data = array(
			'filter_name'              => $filter_name,
			'filter_email'             => $filter_email,
			'filter_customer_group_id' => $filter_customer_group_id,
			'filter_status'            => $filter_status,
			'filter_approved'          => $filter_approved,
			'filter_date_added'        => $filter_date_added,
			'filter_ip'                => $filter_ip,
			'sort'                     => $sort,
			'order'                    => $order,
			'start'                    => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'                    => $this->config->get('config_admin_limit')
		);

        $results = $this->model_sale_customer_group->getCustomerGroups();
        $customer_groups =null;
        foreach ($results as $item){
            $customer_groups[$item['customer_group_id']]=$item;
        }
        $customer_counts = $this->model_sale_customer->getCustomersCountsByGroup();
        foreach ($customer_counts as $customer_count){
            $customer_groups[$customer_count['customer_group_id']]['total'] = $customer_count['total'];
        }

        $this->data['customer_groups'] = $customer_groups;

        $customer_total = $this->model_sale_customer->getTotalCustomers($data);

		$results = $this->model_sale_customer->getCustomers($data);

    	foreach ($results as $result) {
			$action = array();

			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('sale/customer/update', 'customer_id=' . $result['customer_id'] . $url, 'SSL')
			);

			$this->data['customers'][] = array(
				'customer_id'    => $result['customer_id'],
				'name'           => $result['name'],
				'email'          => $result['email'],
				'customer_group' => $result['customer_group'],
				'status'         => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'approved'       => ($result['approved'] ? $this->language->get('text_yes') : $this->language->get('text_no')),
				'ip'             => $result['ip'],
				'date_added'     => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'selected'       => isset($this->request->post['selected']) && in_array($result['customer_id'], $this->request->post['selected']),
				'action'         => $action
			);
		}

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

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_customer_group_id'])) {
			$url .= '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_approved'])) {
			$url .= '&filter_approved=' . $this->request->get['filter_approved'];
		}

		if (isset($this->request->get['filter_ip'])) {
			$url .= '&filter_ip=' . $this->request->get['filter_ip'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['sort_name'] = $this->url->link('sale/component/customers', 'sort=name' . $url, 'SSL');
		$this->data['sort_email'] = $this->url->link('sale/component/customers', 'sort=c.email' . $url, 'SSL');
		$this->data['sort_customer_group'] = $this->url->link('sale/component/customers', 'sort=customer_group' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('sale/component/customers', 'sort=c.status' . $url, 'SSL');
		$this->data['sort_approved'] = $this->url->link('sale/component/customers', 'sort=c.approved' . $url, 'SSL');
		$this->data['sort_ip'] = $this->url->link('sale/component/customers', 'sort=c.ip' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('sale/component/customers', 'sort=c.date_added' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_customer_group_id'])) {
			$url .= '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_approved'])) {
			$url .= '&filter_approved=' . $this->request->get['filter_approved'];
		}

		if (isset($this->request->get['filter_ip'])) {
			$url .= '&filter_ip=' . $this->request->get['filter_ip'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $customer_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/component/customers', 'page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['filter_name'] = $filter_name;
		$this->data['filter_email'] = $filter_email;
		$this->data['filter_customer_group_id'] = $filter_customer_group_id;
		$this->data['filter_status'] = $filter_status;
		$this->data['filter_approved'] = $filter_approved;
		$this->data['filter_ip'] = $filter_ip;
		$this->data['filter_date_added'] = $filter_date_added;

		$this->load->model('sale/customer_group');

    	$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

		$this->load->model('setting/store');

		$this->data['stores'] = $this->model_setting_store->getStores();

		$this->data['sort']  = $sort;
		$this->data['order'] = $order;

        $this->load->model('sale/newsletter/subscriber');
        $this->data['newsletter_subscribers'] = $this->model_sale_newsletter_subscriber->getAllSubscribers([], $this->language->get('btnt_newsletter_subscribers'));

        if ($customer_total == 0){
            $this->template = 'sale/customer/empty.expand';
        }else{
            $this->template = 'sale/customer_list.expand';
        }
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render_ecwig());
  	}

  	protected function getForm() {
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) return $this->getFormV2();
        
        $this->data['cancel'] = $this->url->link('sale/component/customers', '', 'SSL');

        $this->initializer([
            'sale/order'
        ]);

        $this->load->model('setting/setting');
        $this->load->model('localisation/language');
        $localizationSettings = $this->model_setting_setting->getSetting('localization');
        $langs = $this->model_localisation_language->getLanguages();

        $suffix = '';
        if ( $this->config->get('config_admin_language') != 'en' )
        {
            $specifiedLang = $langs[$this->config->get('config_admin_language')];
            $suffix = "_{$specifiedLang['code']}";
        }
    	$this->data['heading_title'] = $this->language->get('heading_title');

    	$this->data['text_enabled'] = $this->language->get('text_enabled');
    	$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_select'] = $this->language->get('text_select');
		$this->data['text_none'] = $this->language->get('text_none');
    	$this->data['text_wait'] = $this->language->get('text_wait');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_add_ban_ip'] = $this->language->get('text_add_ban_ip');
		$this->data['text_remove_ban_ip'] = $this->language->get('text_remove_ban_ip');
		$this->data['text_add_new_address'] = $this->language->get('text_add_new_address');

		$this->data['button_add_new_address'] = $this->language->get('button_add_new_address');

		$this->data['column_ip'] = $this->language->get('column_ip');
		$this->data['column_total'] = $this->language->get('column_total');
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_action'] = $this->language->get('column_action');

    	$this->data['entry_firstname'] = $this->language->get('entry_firstname');
    	$this->data['entry_lastname'] = $this->language->get('entry_lastname');
    	$this->data['entry_email'] = $this->language->get('entry_email');
    	$this->data['entry_telephone'] = ! empty( $localizationSettings['entry_telephone' . $suffix] ) ? $localizationSettings['entry_telephone' . $suffix] : $this->language->get('entry_telephone');
    	$this->data['entry_fax'] = ! empty( $localizationSettings['entry_fax' . $suffix] ) ? $localizationSettings['entry_fax' . $suffix] : $this->language->get('entry_fax');
    	$this->data['entry_password'] = $this->language->get('entry_password');
    	$this->data['entry_confirm'] = $this->language->get('entry_confirm');
		$this->data['entry_newsletter'] = $this->language->get('entry_newsletter');
    	$this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_company'] = ! empty( $localizationSettings['entry_company' . $suffix] ) ? $localizationSettings['entry_company' . $suffix] : $this->language->get('entry_company');
		$this->data['entry_company_id'] = ! empty( $localizationSettings['entry_company_id' . $suffix] ) ? $localizationSettings['entry_company_id' . $suffix] : $this->language->get('entry_company_id');
		$this->data['entry_tax_id'] =  ! empty( $localizationSettings['entry_tax_id' . $suffix] ) ? $localizationSettings['entry_tax_id' . $suffix] : $this->language->get('entry_tax_id');
		$this->data['entry_address_1'] = ! empty( $localizationSettings['entry_address_1' . $suffix] ) ? $localizationSettings['entry_address_1' . $suffix] : $this->language->get('entry_address_1');
		$this->data['entry_address_2'] = ! empty( $localizationSettings['entry_address_2' . $suffix] ) ? $localizationSettings['entry_address_2' . $suffix] : $this->language->get('entry_address_2');
		$this->data['entry_city'] = ! empty( $localizationSettings['entry_city' . $suffix] ) ? $localizationSettings['entry_city' . $suffix] : $this->language->get('entry_city');
		$this->data['entry_postcode'] = ! empty( $localizationSettings['entry_postcode' . $suffix] ) ? $localizationSettings['entry_postcode' . $suffix] : $this->language->get('entry_postcode');
		$this->data['entry_zone'] =  ! empty( $localizationSettings['entry_checkout_zone' . $suffix] ) ? $localizationSettings['entry_checkout_zone' . $suffix] : $this->language->get('entry_zone');
		$this->data['entry_country'] =  ! empty( $localizationSettings['entry_country' . $suffix] ) ? $localizationSettings['entry_country' . $suffix] : $this->language->get('entry_country');
		$this->data['entry_default'] = $this->language->get('entry_default');
		$this->data['entry_comment'] = $this->language->get('entry_comment');
		$this->data['entry_description'] = $this->language->get('entry_description');
		$this->data['entry_amount'] = $this->language->get('entry_amount');
		$this->data['entry_points'] = $this->language->get('entry_points');

		$this->data['entry_payment_telephone'] = $this->language->get('entry_payment_telephone');

		$this->data['button_save'] = $this->language->get('button_save');
    	$this->data['button_cancel'] = $this->language->get('button_cancel');
    	$this->data['button_add_address'] = $this->language->get('button_add_address');
		$this->data['button_add_history'] = $this->language->get('button_add_history');
		$this->data['button_add_transaction'] = $this->language->get('button_add_transaction');
		$this->data['button_add_reward'] = $this->language->get('button_add_reward');
    	$this->data['button_remove'] = $this->language->get('button_remove');

		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['tab_address'] = $this->language->get('tab_address');
		$this->data['tab_history'] = $this->language->get('tab_history');
		$this->data['tab_transaction'] = $this->language->get('tab_transaction');
		$this->data['tab_reward'] = $this->language->get('tab_reward');
		$this->data['tab_ip'] = $this->language->get('tab_ip');
		$this->data['entry_dob'] = $this->language->get('entry_dob');
		$this->data['entry_gender'] = $this->language->get('entry_gender');
		$this->data['entry_gender_m'] = $this->language->get('entry_gender_m');
		$this->data['entry_gender_f'] = $this->language->get('entry_gender_f');

		$this->data['token'] = null;

		if (isset($this->request->get['customer_id'])) {
			$this->data['customer_id'] = $this->request->get['customer_id'];
		} else {
			$this->data['customer_id'] = 0;
		}

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['payment_telephone'])) {
			$this->data['error_payment_telephone'] = $this->error['payment_telephone'];
		} else {
			$this->data['error_payment_telephone'] = '';
		}

 		if (isset($this->error['firstname'])) {
			$this->data['error_firstname'] = $this->error['firstname'];
		} else {
			$this->data['error_firstname'] = '';
		}

 		if (isset($this->error['lastname'])) {
			$this->data['error_lastname'] = $this->error['lastname'];
		} else {
			$this->data['error_lastname'] = '';
		}

 		if (isset($this->error['email'])) {
			$this->data['error_email'] = $this->error['email'];
		} else {
			$this->data['error_email'] = '';
		}

 		if (isset($this->error['telephone'])) {
			$this->data['error_telephone'] = $this->error['telephone'];
		} else {
			$this->data['error_telephone'] = '';
		}

 		if (isset($this->error['password'])) {
			$this->data['error_password'] = $this->error['password'];
		} else {
			$this->data['error_password'] = '';
		}

 		if (isset($this->error['confirm'])) {
			$this->data['error_confirm'] = $this->error['confirm'];
		} else {
			$this->data['error_confirm'] = '';
		}

		if (isset($this->error['address_firstname'])) {
			$this->data['error_address_firstname'] = $this->error['address_firstname'];
		} else {
			$this->data['error_address_firstname'] = '';
		}

 		if (isset($this->error['address_lastname'])) {
			$this->data['error_address_lastname'] = $this->error['address_lastname'];
		} else {
			$this->data['error_address_lastname'] = '';
		}

  		if (isset($this->error['address_tax_id'])) {
			$this->data['error_address_tax_id'] = $this->error['address_tax_id'];
		} else {
			$this->data['error_address_tax_id'] = '';
		}

		if (isset($this->error['address_address_1'])) {
			$this->data['error_address_address_1'] = $this->error['address_address_1'];
		} else {
			$this->data['error_address_address_1'] = '';
		}

		if (isset($this->error['address_city'])) {
			$this->data['error_address_city'] = $this->error['address_city'];
		} else {
			$this->data['error_address_city'] = '';
		}

		if (isset($this->error['address_postcode'])) {
			$this->data['error_address_postcode'] = $this->error['address_postcode'];
		} else {
			$this->data['error_address_postcode'] = '';
		}

		if (isset($this->error['address_country'])) {
			$this->data['error_address_country'] = $this->error['address_country'];
		} else {
			$this->data['error_address_country'] = '';
		}

		if (isset($this->error['address_zone'])) {
			$this->data['error_address_zone'] = $this->error['address_zone'];
		} else {
			$this->data['error_address_zone'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_customer_group_id'])) {
			$url .= '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_approved'])) {
			$url .= '&filter_approved=' . $this->request->get['filter_approved'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/component/customers', '', 'SSL'),
      		'separator' => ' :: '
   		);

        $this->data['breadcrumbs'][] = array(
            'text'      => !isset($this->request->get['customer_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href'      => $this->url->link('sale/component/customers', '', 'SSL'),
            'separator' => ' :: '
        );

		if (!isset($this->request->get['customer_id'])) {
			$this->data['action'] = $this->url->link('sale/customer/insert', '', 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/customer/update', 'customer_id=' . $this->request->get['customer_id'] . $url, 'SSL');
		}

    	$this->data['cancel'] = $this->url->link('sale/component/customers', '', 'SSL');



    	if (isset($this->request->get['customer_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $customer_info = $this->model_sale_customer->getCustomer($this->request->get['customer_id']);
            $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

            if ($queryMultiseller->num_rows) {
                $this->load->model('multiseller/seller');
                $customer_seller_info = $this->model_multiseller_seller->getSellerInfo($this->request->get['customer_id']);

            }
        }
        
        if (!empty($customer_info)) {

            $this->data['buyer_subscription_plan_installed'] = \Extension::isInstalled('buyer_subscription_plan') && $this->config->get('buyer_subscription_plan_status');
            
            if($this->data['buyer_subscription_plan_installed']){
                $this->load->model('module/buyer_subscription_plan/subscription');
                $this->data['customer_subscription_payments_log'] = $this->model_module_buyer_subscription_plan_subscription->getCustomerPaymentsLog($customer_info['customer_id']);
            }
        }

        if (isset($this->request->post['buyer_subscription_plan'])) {
            $this->data['buyer_subscription_plan'] = $this->request->post['buyer_subscription_plan'];
        } elseif ($this->data['buyer_subscription_plan_installed'] && !empty($customer_info)) {
            $plan = $this->model_module_buyer_subscription_plan_subscription->get($customer_info['buyer_subscription_id']);
            $this->data['buyer_subscription_plan'] = ['id' => $plan['subscription_id'], 'title' => $plan['translations'][$this->config->get('config_language_id')]['title']];
        } else {
            $this->data['buyer_subscription_plan'] = '';
        }

        if (isset($this->request->post['tax_card'])) {
            $this->data['tax_card'] = $this->request->post['tax_card'];
        } elseif ($customer_seller_info) {
            $this->data['tax_card'] = $customer_seller_info['tax_card'];
        }
        if (isset($this->request->post['bank_name'])) {
            $this->data['bank_name'] = $this->request->post['bank_name'];
        } elseif ($customer_seller_info) {
            $this->data['bank_name'] = $customer_seller_info['bank_name'];
        }
        if (isset($this->request->post['bank_iban'])) {
            $this->data['bank_iban'] = $this->request->post['bank_iban'];
        } elseif ($customer_seller_info) {
            $this->data['bank_iban'] = $customer_seller_info['bank_iban'];
        }
        if (isset($this->request->post['bank_transfer'])) {
            $this->data['bank_transfer'] = $this->request->post['bank_transfer'];
        } elseif ($customer_seller_info) {
            $this->data['bank_transfer'] = $customer_seller_info['bank_transfer'];
        }

    	if (isset($this->request->post['firstname'])) {
      		$this->data['firstname'] = $this->request->post['firstname'];
		} elseif (!empty($customer_info)) {
			$this->data['firstname'] = $customer_info['firstname'];
		} else {
      		$this->data['firstname'] = '';
    	}

    	if (isset($this->request->post['lastname'])) {
      		$this->data['lastname'] = $this->request->post['lastname'];
    	} elseif (!empty($customer_info)) {
			$this->data['lastname'] = $customer_info['lastname'];
		} else {
      		$this->data['lastname'] = '';
    	}

    	if (isset($this->request->post['email'])) {
      		$this->data['email'] = $this->request->post['email'];
    	} elseif (!empty($customer_info)) {
			$this->data['email'] = $customer_info['email'];
		} else {
      		$this->data['email'] = '';
    	}

    	if (isset($this->request->post['telephone'])) {
      		$this->data['telephone'] = $this->request->post['telephone'];
    	} elseif (!empty($customer_info)) {
			$this->data['telephone'] = $customer_info['telephone'];
		} else {
      		$this->data['telephone'] = '';
    	}

    	if (isset($this->request->post['fax'])) {
      		$this->data['fax'] = $this->request->post['fax'];
    	} elseif (!empty($customer_info)) {
			$this->data['fax'] = $customer_info['fax'];
		} else {
      		$this->data['fax'] = '';
    	}

    	if (isset($this->request->post['newsletter'])) {
      		$this->data['newsletter'] = $this->request->post['newsletter'];
    	} elseif (!empty($customer_info)) {
			$this->data['newsletter'] = $customer_info['newsletter'];
		} else {
      		$this->data['newsletter'] = '';
    	}
        
    	if (isset($this->request->post['dob'])) {
      		$this->data['dob'] = $this->request->post['dob'];
    	} elseif (!empty($customer_info)) {
			$this->data['dob'] = $customer_info['dob'];
		} else {
      		$this->data['dob'] = '';
    	}
        
    	if (isset($this->request->post['gender'])) {
      		$this->data['gender'] = $this->request->post['gender'];
    	} elseif (!empty($customer_info)) {
			$this->data['gender'] = $customer_info['gender'];
		} else {
      		$this->data['gender'] = '';
    	}

		$this->load->model('sale/customer_group');

		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

    	if (isset($this->request->post['customer_group_id'])) {
      		$this->data['customer_group_id'] = $this->request->post['customer_group_id'];
    	} elseif (!empty($customer_info)) {
			$this->data['customer_group_id'] = $customer_info['customer_group_id'];
		} else {
      		$this->data['customer_group_id'] = $this->config->get('config_customer_group_id');
    	}

    	if (isset($this->request->post['status'])) {
      		$this->data['status'] = $this->request->post['status'];
    	} elseif (!empty($customer_info)) {
			$this->data['status'] = $customer_info['status'];
		} else {
      		$this->data['status'] = 1;
    	}

    	if (isset($this->request->post['password'])) {
			$this->data['password'] = $this->request->post['password'];
		} else {
			$this->data['password'] = '';
		}

		if (isset($this->request->post['confirm'])) {
    		$this->data['confirm'] = $this->request->post['confirm'];
		} else {
			$this->data['confirm'] = '';
		}

		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

        if (isset($this->request->get['customer_id'])) {
            $this->data['addresses'] = $this->model_sale_customer->getAddresses($this->request->get['customer_id']);
        } elseif (!empty($customer_info)) {
            $this->data['addresses'] = $this->model_sale_customer->getAddresses($customer_info['customer_id']);
        } else {
            $this->data['addresses'] = array();
        }

//	    if (isset($this->request->get['customer_id'])) {
//			$this->data['addresses'] = $this->model_sale_customer->getAddresses($this->request->get['customer_id']);
//		} else {
//			$this->data['addresses'] = array();
//    	}

    	$customerOrders = $this->order->getCustomerOrdersForCustomerProfile($this->request->get['customer_id']);

        if ( $customerOrders !== false )
        {
            foreach ( $customerOrders as $index => $order )
            {
                $customerOrders[$index]['total'] = $this->currency->format( $order['total'], $order['currency'] );
            }
        }

        $this->data['customer_orders'] = $customerOrders;

        $this->data['ordersCount'] = $customerOrders !== false ? count($customerOrders) : 0;

        $this->load->model('tool/image');

    	if (isset($this->request->post['address_id'])) {
      		$this->data['address_id'] = $this->request->post['address_id'];
    	} elseif (!empty($customer_info)) {
			$this->data['address_id'] = $customer_info['address_id'];
		} else {
      		$this->data['address_id'] = '';
    	}

        $this->data['historyNotes'] = array();

        $results = $this->model_sale_customer->getHistories($this->request->get['customer_id'], 0, 5);

        foreach ($results as $result) {
            $this->data['historyNotes'][] = array(
                'comment'     => $result['comment'],
                'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added']))
            );
        }

		$this->data['combinedHistory'] = $this->getTimeLine();

		$this->template = 'sale/customer/profile.expand';
		$this->base="common/base";

		$this->response->setOutput($this->render_ecwig());
	}

    public function getTimeLine( /*$customer_info*/ )
    {
        $ips = array();

//        if (!empty($customer_info)) {
//            $results = $this->model_sale_customer->getIpsByCustomerId($this->request->get['customer_id']);
//
//            foreach ($results as $result) {
//                $ban_ip_total = $this->model_sale_customer->getTotalBanIpsByIp($result['ip']);
//
//                $ips[] = array(
//                    'ip'         => $result['ip'],
//                    'total'      => $this->model_sale_customer->getTotalCustomersByIp($result['ip']),
//                    'date_added' => $result['date_added'],
//                    'filter_ip'  => $this->url->link('sale/customer', 'filter_ip=' . $result['ip'], 'SSL'),
//                    'ban_ip'     => $ban_ip_total,
//                    'type'       => 'ip'
//                );
//            }
//        }

        $transactions = $this->model_sale_customer->getTimelineTransactions($this->request->get['customer_id']);

        $rewards = $this->model_sale_customer->getTimelineRewards($this->request->get['customer_id']);

        $timeline = array_merge($ips, $transactions, $rewards);

        function date_sort($a, $b) {
            return strtotime($b['date_added']) - strtotime($a['date_added']);
        }
        usort($timeline, "date_sort");

        return $timeline;
    }

	public function addAddress(){
        $this->language->load('sale/customer');

        $json = array();

        if (isset($this->request->post['address']) && isset($this->request->post['customer_id'])) {
            if (!$this->user->hasPermission('modify', 'sale/customer')) {
                $json['errors'] = $this->language->get('error_permission');
            } else {
                
                if (!$this->validateAddressForm()) {
                    $json['success'] = '0';
                    $json['errors'] = $this->error;
                    $this->response->setOutput(json_encode($json));
                    return;
                }
                
                $this->load->model('sale/customer');

                $this->model_sale_customer->addAddress($this->request->post['customer_id'], $this->request->post['address']);

                $json['success'] = '1';
                $json['success_msg'] = $this->language->get('text_success_addresses');
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function updateAddress(){
        $this->language->load('sale/customer');

        $json = array();

        if (isset($this->request->post['address']) && isset($this->request->post['address_id']) && isset($this->request->post['customer_id'])) {
            if (!$this->user->hasPermission('modify', 'sale/customer')) {
                $json['errors'] = $this->language->get('error_permission');
            } else {
                
                if (!$this->validateAddressForm()) {
                    $json['success'] = '0';
                    $json['errors'] = $this->error;
                    $this->response->setOutput(json_encode($json));
                    return;
                }
                
                $this->load->model('sale/customer');
                
                $address = $this->request->post['address'];
                $address['address_id'] = $this->request->post['address_id'];
                $this->model_sale_customer->addAddress($this->request->post['customer_id'], $this->request->post['address']);

                $json['success'] = '1';
                $json['success_msg'] = $this->language->get('text_success_addresses');
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function removeAddress(){
        $this->language->load('sale/customer');

        $json = array();

        if (isset($this->request->post['address_id'])) {
            if (!$this->user->hasPermission('modify', 'sale/customer')) {
                $json['error'] = $this->language->get('error_permission');
            } else {
                $this->load->model('sale/customer');

                $this->model_sale_customer->deleteAddress($this->request->post['address_id'], $this->request->post['customer_id']);

                $json['success'] = $this->language->get('text_success');
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function setDefaultAddress(){
        $this->language->load('sale/customer');

        $json = array();

        if (isset($this->request->post['address_id']) && isset($this->request->post['customer_id'])) {
            if (!$this->user->hasPermission('modify', 'sale/customer')) {
                $json['error'] = $this->language->get('error_permission');
            } else {
                $this->load->model('sale/customer');

                $address = $this->request->post['address'];
                $address['address_id'] = $this->request->post['address_id'];
                $this->model_sale_customer->setDefaultAddress($this->request->post['customer_id'], $this->request->post['address_id']);

                $json['success'] = $this->language->get('text_success');
            }
        }

        $this->response->setOutput(json_encode($json));
    }
  	
    private function validateForm()
    {
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) return $this->validateFormV2();
        
        if (!$this->user->hasPermission('modify', 'sale/customer')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (
            (utf8_strlen($this->request->post['firstname']) < 1) ||
            (utf8_strlen($this->request->post['firstname']) > 32)
        ) {
            $this->error['firstname'] = $this->language->get('error_firstname');
        }

        if (preg_match('#^([\p{Arabic}a-z\-\s]+)$#ui', $this->request->post['firstname']) == false) {
            $this->error['firstname'] = $this->language->get('error_firstname');
        }

        if (
            (utf8_strlen($this->request->post['lastname']) < 1) ||
            (utf8_strlen($this->request->post['lastname']) > 32)
        ) {
            $this->error['lastname'] = $this->language->get('error_lastname');
        }

        if (preg_match('#^([\p{Arabic}a-z\-\s]+)$#ui', $this->request->post['lastname']) == false) {
            $this->error['lastname'] = $this->language->get('error_lastname');
        }

        if (
            ((utf8_strlen($this->request->post['email']) > 96) ||
            !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])) && !$this->request->post['login_register_phonenumber_enabled']
        ) {
            $this->error['email'] = $this->language->get('error_email');
        }

        $customer_info = $this->model_sale_customer->getCustomerByEmail($this->request->post['email']);

        if (!isset($this->request->get['customer_id'])) {
            if ($customer_info) {
                $this->error['error_exists'] = $this->language->get('error_exists');
            }
        } else {
            if ($customer_info && ($this->request->get['customer_id'] != $customer_info['customer_id'])) {
                $this->error['error_exists'] = $this->language->get('error_exists');
            }
        }

        if (
            (utf8_strlen($this->request->post['telephone']) < 3) ||
            (utf8_strlen($this->request->post['telephone']) > 32)
        ) {
            $this->error['telephone'] = $this->language->get('error_telephone');
        }

        if (preg_match('/^\+?[0-9]*$/', $this->request->post['telephone']) == false) {
            $this->error['telephone'] = $this->language->get('error_telephone');
        }

        if ($this->request->post['password'] || (!isset($this->request->get['customer_id']))) {
            if (
                (utf8_strlen($this->request->post['password']) < 4) ||
                (utf8_strlen($this->request->post['password']) > 20)
            ) {
                $this->error['password'] = $this->language->get('error_password');
            }

            if ($this->request->post['password'] != $this->request->post['confirm']) {
                $this->error['confirm'] = $this->language->get('error_confirm');
            }
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }
    
  	protected function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'sale/customer')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}

		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}

	public function login() {
		$json = array();

		if (isset($this->request->get['customer_id'])) {
			$customer_id = $this->request->get['customer_id'];
		} else {
			$customer_id = 0;
		}

		$this->load->model('sale/customer');

		$customer_info = $this->model_sale_customer->getCustomer($customer_id);

		if ($customer_info) {
			$token = md5(mt_rand());

			$this->model_sale_customer->editToken($customer_id, $token);

			if (isset($this->request->get['store_id'])) {
				$store_id = $this->request->get['store_id'];
			} else {
				$store_id = 0;
			}

			$this->load->model('setting/store');

			$store_info = $this->model_setting_store->getStore($store_id);

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("customer");
            if($pageStatus) {
                $log_history['action'] = 'login';
                $log_history['reference_id'] = $customer_id;
                $log_history['old_value'] = json_encode($customer_info);
                $log_history['new_value'] = json_encode($customer_info);
                $log_history['type'] = 'customer';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

            if ($store_info) {
				$this->redirect($store_info['url'] . 'index.php?route=account/login&token=' . $token);
			} else {
				$this->redirect(HTTP_CATALOG . 'index.php?route=account/login&token=' . $token);
			}
		} else {
			$this->language->load('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['heading_title'] = $this->language->get('heading_title');

			$this->data['text_not_found'] = $this->language->get('text_not_found');

			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('error/not_found', '', 'SSL'),
				'separator' => ' :: '
			);

			$this->template = 'error/not_found.expand';
            $this->base = "common/base";

			$this->response->setOutput($this->render_ecwig());
		}
	}

	public function history() {
    	$this->language->load('sale/customer');

		$this->load->model('sale/customer');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'sale/customer')) {

            $this->model_sale_customer->addHistory(
                $this->request->get['customer_id'],
                $this->request->post['comment']
            );

            $this->data['success'] = $this->language->get('text_success');

            $response = [
                'status' => 'success',
                'message' => $this->language->get('text_success'),
                'comment' => $this->request->post['comment'],
                'date_added' => date('Y-d-m', time())
            ];
            $this->response->setOutput(json_encode($response));
            return;
		} else {
			$this->data['success'] = '';
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && !$this->user->hasPermission('modify', 'sale/customer')) {
			$this->data['error_warning'] = $this->language->get('error_permission');
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['text_no_results'] = $this->language->get('text_no_results');

		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_comment'] = $this->language->get('column_comment');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$this->data['histories'] = array();

		$results = $this->model_sale_customer->getHistories($this->request->get['customer_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
        	$this->data['histories'][] = array(
				'comment'     => $result['comment'],
        		'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added']))
        	);
      	}

		$transaction_total = $this->model_sale_customer->getTotalHistories($this->request->get['customer_id']);

		$pagination = new Pagination();
		$pagination->total = $transaction_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/customer/history', 'customer_id=' . $this->request->get['customer_id'] . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->template = 'sale/customer_history.tpl';

		$this->response->setOutput($this->render());
	}

    /**
     * The customer transactions ex.add balance
     * @throws FileException
     */
    public function transaction(): void
    {
        $this->language->load('sale/customer');

        $this->load->model('sale/customer');

        if ($this->request->server['REQUEST_METHOD'] === 'POST') {

            if (!$this->user->hasPermission('modify', 'sale/customer')) {
                $response['success'] = '';
                $response['title'] = 'Warning';
                $response['type'] = 'warning';
                $response['message'] = $this->language->get('error_permission');
                $this->response->setOutput(json_encode($response));
                return;
            }

            $transactionData = $this->request->post['transaction'];

            if (empty($transactionData['description'])) {
                $response['errors']['transaction_description'] = $this->language->get('error_field_cant_be_empty');
            }

            if (empty($transactionData['amount'])) {
                $response['errors']['transaction_amount'] = $this->language->get('error_field_cant_be_empty');
            } else if (!preg_match('/[0-9]+/', $transactionData['amount'])) {
                $response['errors']['transaction_amount'] = $this->language->get('error_points');
            }


            if ($response['errors']) {
                $response['success'] = '';
                $response['title'] = 'Warning';
                $response['type'] = 'warning';
                $this->response->setOutput(json_encode($response));
                return;
            }

            $old_value['old_balance'] = $this->currency->format($this->model_sale_customer->getTransactionTotal($this->request->get['customer_id']), $this->config->get('config_currency'));

            $this->model_sale_customer->addTransaction(
                $this->request->get['customer_id'],
                $transactionData['description'],
                $transactionData['amount']
            );

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("customer");
            if ($pageStatus) {
                $log_history['action'] = 'updateBalance';
                $log_history['reference_id'] = $this->request->get['customer_id'];
                $log_history['old_value'] = json_encode($old_value, JSON_UNESCAPED_UNICODE);
                $log_history['new_value'] = json_encode($transactionData, JSON_UNESCAPED_UNICODE);
                $log_history['type'] = 'customer';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

            $total_balance_points = $this->currency->format($this->model_sale_customer->getTransactionTotal($this->request->get['customer_id']), $this->config->get('config_currency'));
            $total_reward_points = $this->model_sale_customer->getRewardTotal($this->request->get['customer_id']);

            $this->data['combinedHistory'] = $this->getTimeLine();
            $this->template = 'sale/customer/profile/history.expand';
            $history = $this->render_ecwig();

            $response = [
                'total_balance_points' => $total_balance_points,
                'total_reward_points' => $total_reward_points,
                'status' => 'success',
                'history' => $history,
                'message' => $this->language->get('text_success'),
                'title' => 'Success',
                'type' => 'success',
            ];


            $this->response->setOutput(json_encode($response));
            return;
        } else {
            $this->data['success'] = '';
        }

        if (($this->request->server['REQUEST_METHOD'] === 'POST') && !$this->user->hasPermission('modify', 'sale/customer')) {
            $this->data['error_warning'] = $this->language->get('error_permission');
        } else {
            $this->data['error_warning'] = '';
        }

        $this->data['text_no_results'] = $this->language->get('text_no_results');
        $this->data['text_balance'] = $this->language->get('text_balance');

        $this->data['column_date_added'] = $this->language->get('column_date_added');
        $this->data['column_description'] = $this->language->get('column_description');
        $this->data['column_amount'] = $this->language->get('column_amount');
        $this->data['transactions'] = array();
        $page = $this->request->get['page'] ?? 1;

        $results = $this->model_sale_customer->getTransactions($this->request->get['customer_id'], ($page - 1) * 10, 10);

        foreach ($results as $result) {
            $this->data['transactions'][] = array(
                'amount' => $this->currency->format($result['amount'], $this->config->get('config_currency')),
                'description' => $result['description'],
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
            );
        }

        $this->data['balance'] = $this->currency->format($this->model_sale_customer->getTransactionTotal($this->request->get['customer_id']), $this->config->get('config_currency'));
        $transaction_total = $this->model_sale_customer->getTotalTransactions($this->request->get['customer_id']);
        $pagination = new Pagination();
        $pagination->total = $transaction_total;
        $pagination->page = $page;
        $pagination->limit = 10;
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('sale/customer/transaction', 'customer_id=' . $this->request->get['customer_id'] . '&page={page}', 'SSL');
        $this->data['pagination'] = $pagination->render();
        $this->template = 'sale/customer_transaction.tpl';

        $this->response->setOutput($this->render());
    }

	
    public function reward()
    {
    	$this->language->load('sale/customer');

		$this->load->model('sale/customer');

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->user->hasPermission('modify', 'sale/customer') )
            {
                $response['success'] = '';
                $response['title'] = 'Warning';
                $response['type'] = 'warning';
                $response['message'] = $this->language->get('error_permission');
                $this->response->setOutput(json_encode($response));
                return;
            }

            $rewardData = $this->request->post['rewards'];

            if ( empty( $rewardData['description'] ) )
            {
                $response['errors']['rewards_description'] = $this->language->get('error_field_cant_be_empty');
            }

            if ( empty( $rewardData['points'] ) )
            {
                $response['errors']['rewards_points'] = $this->language->get('error_field_cant_be_empty');
            }
            else if ( ! preg_match('/[0-9]+/', $rewardData['points']) )
            {
                $response['errors']['rewards_points'] = $this->language->get('error_points');
            }

            if ( $response['errors'] )
            {
                $response['success'] = '';
                $response['title'] = 'Warning';
                $response['type'] = 'warning';
                $this->response->setOutput(json_encode($response));
                return;
            }
            $old_value['old_reward'] = $this->model_sale_customer->getRewardTotal($this->request->get['customer_id']);

			$this->model_sale_customer->addReward(
			    $this->request->get['customer_id'],
                $rewardData['description'],
                $rewardData['points']
            );
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("customer");
            if($pageStatus) {
                $log_history['action'] = 'updateReward';
                $log_history['reference_id'] = $this->request->get['customer_id'];
                $log_history['old_value'] = json_encode($old_value);
                $log_history['new_value'] = json_encode($rewardData);
                $log_history['type'] = 'customer';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }


            $total_balance_points = $this->currency->format($this->model_sale_customer->getTransactionTotal($this->request->get['customer_id']), $this->config->get('config_currency'));
            $total_reward_points = $this->model_sale_customer->getRewardTotal($this->request->get['customer_id']);


            $this->data['combinedHistory'] = $this->getTimeLine();
            $this->template = 'sale/customer/profile/history.expand';
            $history = $this->render_ecwig();

            $response = [
                'total_balance_points' => $total_balance_points,
                'total_reward_points'  => $total_reward_points,
                'status' => 'success', 
                'history' => $history, 
                'message' => $this->language->get('text_success'),
                'title'   => 'Success',
                'type'    => 'success',
            ];



            $this->response->setOutput(json_encode($response));
            return;
		} else {
			$this->data['success'] = '';
		}

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_balance'] = $this->language->get('text_balance');

		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_description'] = $this->language->get('column_description');
		$this->data['column_points'] = $this->language->get('column_points');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$this->data['rewards'] = array();

		$results = $this->model_sale_customer->getRewards($this->request->get['customer_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
        	$this->data['rewards'][] = array(
				'points'      => $result['points'],
				'description' => $result['description'],
        		'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added']))
        	);
      	}

		$this->data['balance'] = $this->model_sale_customer->getRewardTotal($this->request->get['customer_id']);

		$reward_total = $this->model_sale_customer->getTotalRewards($this->request->get['customer_id']);

		$pagination = new Pagination();
		$pagination->total = $reward_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/customer/reward', 'customer_id=' . $this->request->get['customer_id'] . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->template = 'sale/customer_reward.tpl';

		$this->response->setOutput($this->render());
	}

	public function addBanIP() {
		$this->language->load('sale/customer');

		$json = array();

		if (isset($this->request->post['ip'])) {
			if (!$this->user->hasPermission('modify', 'sale/customer')) {
				$json['error'] = $this->language->get('error_permission');
			} else {
				$this->load->model('sale/customer');

				$this->model_sale_customer->addBanIP($this->request->post['ip']);

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->setOutput(json_encode($json));
	}

	public function removeBanIP() {
		$this->language->load('sale/customer');

		$json = array();

		if (isset($this->request->post['ip'])) {
			if (!$this->user->hasPermission('modify', 'sale/customer')) {
				$json['error'] = $this->language->get('error_permission');
			} else {
				$this->load->model('sale/customer');

				$this->model_sale_customer->removeBanIP($this->request->post['ip']);

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->setOutput(json_encode($json));
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('sale/customer');

			$data = array(
				'search' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 20,
                'skip_group'   => 1
			);

			$results = $this->model_sale_customer->getCustomers($data);

			foreach ($results as $result) {
				$json[] = array(
					'customer_id'       => $result['customer_id'],
					'customer_group_id' => $result['customer_group_id'],
					'name'              => ($result['name'] != " ") ? strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')) : $result['email'],
					'customer_group'    => $result['customer_group'],
					'firstname'         => $result['firstname'],
					'lastname'          => $result['lastname'],
					'email'             => $result['email'],
					'telephone'         => $result['telephone'],
					'fax'               => $result['fax'],
					'address'           => $this->model_sale_customer->getAddresses($result['customer_id'])
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->setOutput(json_encode($json));
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
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id'], $this->request->get['query']),
				'status'            => $country_info['status']
			);
		}

		$this->response->setOutput(json_encode($json));
	}
    
    public function zone()
    {
        $json = array();

        $this->load->model('localisation/zone');

        $zone_info = $this->model_localisation_zone->getZone($this->request->get['zone_id']);

        if ($zone_info) {
            $this->load->model('localisation/area');

            $json = array(
                'zone_id'        => $zone_info['country_id'],
                'name'              => $zone_info['name'],
                'area'              => $this->model_localisation_area->getAreaByZoneId($this->request->get['zone_id'], $this->request->get['query']),
                'status'            => $zone_info['status']
            );
        }

        $this->response->setOutput(json_encode($json));
    }

	public function address() {
		$json = array();

		if (!empty($this->request->get['address_id'])) {
			$this->load->model('sale/customer');

			$json = $this->model_sale_customer->getAddress($this->request->get['address_id']);
		}

		$this->response->setOutput(json_encode($json));
	}

    public function addresses() {
        $json = array();

        if (!empty($this->request->get['customer_id'])) {
            $this->load->model('sale/customer');

            $json['addresses'] = $this->model_sale_customer->getAddresses($this->request->get['customer_id']);
            $json['default_address_id'] = $this->model_sale_customer->getDefaultAddressId($this->request->get['customer_id']);
        }

        $this->response->setOutput(json_encode($json));
    }
    
    public function insertV2()
    {
        $this->language->load('sale/customer');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('sale/component/customers', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('breadcrumb_insert'),
            'href'      => $this->url->link('sale/component/customers', '', 'SSL'),
            'separator' => ' :: '
        );

    	$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/customer');
        $this->load->model('module/signup');
        $modSettings = $this->model_module_signup->getModuleSettings();

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            if(isset($modSettings['login_register_phonenumber_enabled']) && $modSettings['login_register_phonenumber_enabled']){
                $this->request->post['login_register_phonenumber_enabled'] = $modSettings['login_register_phonenumber_enabled'];
            }

            if (!isset($this->request->post['status'])) {
                $this->request->post['status'] = '0';
            }

            if (!isset($this->request->post['newsletter'])) {
                $this->request->post['newsletter'] = '0';
            }

		    if (!$this->validateForm()) {
                $response['success'] = '0';
                $response['errors'] = $this->error;

                $this->response->setOutput(json_encode($response));
                return;
            }

      	  	$customer_id = $this->model_sale_customer->addCustomer($this->request->post);


            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("customer");
            if($pageStatus) {
                $log_history['action'] = 'add';
                $log_history['reference_id'] = $customer_id;
                $log_history['old_value'] = NULL;
                $log_history['new_value'] = json_encode($this->request->post);
                $log_history['type'] = 'customer';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

            
            // ZOHO inventory create customer if app is setup
            $this->load->model('module/zoho_inventory');
            $this->model_module_zoho_inventory->createCustomer($customer_id, $this->request->post);

            /**
             *-----------------------------
             * Update Abandoned Orders
             * ----------------------------
             * Search for all abandoned orders related to this new customer and assign it to him.
             */
		    $this->load->model('sale/order');
            $abandoned_orders = $this->model_sale_order->getOrdersByCustomerEmail($this->request->post['email'], true);
            if ($abandoned_orders) {
                $data = array(
                    "customer_id" => $customer_id,
                    "customer_group_id" => $this->request->post['customer_group_id'],
                    "email" => $this->request->post['email']
                );
                foreach ($abandoned_orders as $abandoned_order) {
                    $result = $this->model_sale_order->updateOrderFields($abandoned_order['order_id'],$data);
                }
            }

            $response['success'] = '1';
            $response['success_msg'] = $this->language->get('notification_inserted_successfully');
            $response['redirect'] = '1';
            $response['to'] = (string) $this->url->link('sale/component/customers' , '', 'SSL');

            $this->response->setOutput(json_encode($response));
            return;
		}

        $this->data['links'] = [
            'submit' => $this->url->link('sale/customer/insert', '', 'SSL'),
            'cancel' => $this->url->link('sale/component/customers', '', 'SSL')
        ];

        $this->load->model('sale/customer_group');

        $this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

        $this->load->model('localisation/country');

        $this->data['customer_group_id'] = $this->config->get('config_customer_group_id') ?: '';
        $this->data['countries'] = $this->model_localisation_country->getCountries();
        $this->data['login_register_phonenumber_enabled'] = $modSettings['login_register_phonenumber_enabled'];

        $this->data['customer_fields'] = $this->config->get('config_customer_fields')['registration'];
        $this->data['customer_fields']['email'] = $this->identity->isLoginByPhone() ? 0 : 1;
        $this->data['customer_fields']['telephone'] = $this->identity->isLoginByPhone() ? 1 : 0;
        
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->template = 'sale/customer/insert_v2.expand';
        
        $this->response->setOutput($this->render_ecwig());
  	}

  	public function updateV2() {

        $customer_id = (int)$this->request->get['customer_id'];
        if(!preg_match("/^[1-9][0-9]*$/", $customer_id)) return false;

		$this->language->load('sale/customer');
        $this->load->model('module/signup');
        $modSettings = $this->model_module_signup->getModuleSettings();

    	$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/customer');

        $this->data['total_balance_points'] = $this->currency->format($this->model_sale_customer->getTransactionTotal($customer_id), $this->config->get('config_currency'));
        $this->data['total_reward_points'] = $this->model_sale_customer->getRewardTotal($customer_id);

		if ( empty($this->model_sale_customer->getCustomer($customer_id)) )
		{
			$this->language->load('common/home');
			$this->data['no_customer'] = true;
			$this->data['title_no_customer_exists'] = $this->language->get('title_no_customer_exists');
		}
        if(\Extension::isInstalled('multiseller') ){
            $this->load->model('multiseller/seller');
            $seller = $this->model_multiseller_seller->getSellerInfo($customer_id);
            if($seller['custom_fields']){
                $custom_fields = unserialize($seller['custom_fields']);
                $ms_seller_files = array();
                $ms_seller_images = array();
                $ms_seller_files_extensions = explode(',',$this->config->get('msconf_seller_allowed_files_types'));
                $pdf = array_search('pdf', $ms_seller_files_extensions);
                if (false !== $pdf) {
                    unset($ms_seller_files_extensions[$pdf]);
                }
                $images_extensions = $ms_seller_files_extensions;
                foreach($custom_fields as $key=>$field){
                    if(end(explode('.',$field)) == 'pdf'){
                        $ms_seller_files[$key]['mask'] =  end(explode('_',$field));
                        $ms_seller_files[$key]['href'] = $this->url->link('multiseller/seller/download', 'download=' . $field, 'SSL');
                    }elseif (in_array(end(explode('.',$field)),$images_extensions)){
                        $ms_seller_images[$key] = $this->MsLoader->MsFile->resizeImage($field, $this->config->get('msconf_preview_product_image_width'), $this->config->get('msconf_preview_product_image_height'));
                    }
                }
                
                $this->data['ms_seller_files'] = $ms_seller_files;
                $this->data['ms_seller_images'] = $ms_seller_images;
            }
        }

    	if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
                
            if(isset($modSettings['login_register_phonenumber_enabled']) && $modSettings['login_register_phonenumber_enabled']){
                $this->request->post['login_register_phonenumber_enabled'] = $modSettings['login_register_phonenumber_enabled'];
            }
            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }
            $oldValue = $this->model_sale_customer->getCustomer($this->request->get['customer_id']);

            $this->model_sale_customer->editCustomer($customer_id, $this->request->post);
            
            // ZOHO inventory edit customer if app is setup
            $this->load->model('module/zoho_inventory');
            $this->model_module_zoho_inventory->updateCustomer($customer_id, $this->request->post);

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("customer");
            if($pageStatus) {
                $log_history['action'] = 'update';
                $log_history['reference_id'] = $this->request->get['customer_id'];
                $log_history['old_value'] = json_encode($oldValue);
                $log_history['new_value'] = json_encode($this->request->post);
                $log_history['type'] = 'customer';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }


			$queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

            if($queryMultiseller->num_rows) {
                $this->load->model('multiseller/seller');

                if (isset($this->request->post['tax_card'])) {
                    $this->model_multiseller_seller->editSellerColumn($customer_id, 'tax_card',$this->request->post['tax_card']);
                }

                if (isset($this->request->post['bank_name'])) {
                    $this->model_multiseller_seller->editSellerColumn($customer_id, 'bank_name', $this->request->post['bank_name']);
                }

                if (isset($this->request->post['bank_iban'])) {
                    $this->model_multiseller_seller->editSellerColumn($customer_id, 'bank_iban', $this->request->post['bank_iban']);
                }

                if (isset($this->request->post['bank_transfer'])) {
                    $this->model_multiseller_seller->editSeller($customer_id, $this->request->post['bank_transfer']);
                }
            }

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
		}

        $this->data['links'] = [
            'submit' => $this->url->link(
                'sale/customer/update',
                'customer_id=' . $customer_id,
                'SSL'
            ),
            'cancel' => $this->url->link('sale/component/customers', '', 'SSL')
        ];
        $this->data['login_register_phonenumber_enabled'] = $modSettings['login_register_phonenumber_enabled'];
    	$this->getForm();
  	}

  	protected function getFormV2() {

        $this->data['cancel'] = $this->url->link('sale/component/customers', '', 'SSL');

        $this->initializer([
            'sale/order'
        ]);

        $this->load->model('setting/setting');
        $this->load->model('localisation/language');
        $localizationSettings = $this->model_setting_setting->getSetting('localization');
        $langs = $this->model_localisation_language->getLanguages();

        $suffix = '';
        if ( $this->config->get('config_admin_language') != 'en' )
        {
            $specifiedLang = $langs[$this->config->get('config_admin_language')];
            $suffix = "_{$specifiedLang['code']}";
        }
    	$this->data['heading_title'] = $this->language->get('heading_title');

    	$this->data['text_enabled'] = $this->language->get('text_enabled');
    	$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_select'] = $this->language->get('text_select');
		$this->data['text_none'] = $this->language->get('text_none');
    	$this->data['text_wait'] = $this->language->get('text_wait');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_add_ban_ip'] = $this->language->get('text_add_ban_ip');
		$this->data['text_remove_ban_ip'] = $this->language->get('text_remove_ban_ip');
		$this->data['text_add_new_address'] = $this->language->get('text_add_new_address');

		$this->data['button_add_new_address'] = $this->language->get('button_add_new_address');

		$this->data['column_ip'] = $this->language->get('column_ip');
		$this->data['column_total'] = $this->language->get('column_total');
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_action'] = $this->language->get('column_action');

        $this->data['entry_name'] = $this->language->get('entry_name');
    	$this->data['entry_firstname'] = $this->language->get('entry_firstname');
    	// $this->data['entry_lastname'] = $this->language->get('entry_lastname');
    	$this->data['entry_email'] = $this->language->get('entry_email');
    	$this->data['entry_telephone'] = ! empty( $localizationSettings['entry_telephone' . $suffix] ) ? $localizationSettings['entry_telephone' . $suffix] : $this->language->get('entry_telephone');
    	$this->data['entry_fax'] = ! empty( $localizationSettings['entry_fax' . $suffix] ) ? $localizationSettings['entry_fax' . $suffix] : $this->language->get('entry_fax');
    	// $this->data['entry_password'] = $this->language->get('entry_password');
    	// $this->data['entry_confirm'] = $this->language->get('entry_confirm');
		$this->data['entry_newsletter'] = $this->language->get('entry_newsletter');
    	$this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_company'] = ! empty( $localizationSettings['entry_company' . $suffix] ) ? $localizationSettings['entry_company' . $suffix] : $this->language->get('entry_company');
		$this->data['entry_company_id'] = ! empty( $localizationSettings['entry_company_id' . $suffix] ) ? $localizationSettings['entry_company_id' . $suffix] : $this->language->get('entry_company_id');
		$this->data['entry_tax_id'] =  ! empty( $localizationSettings['entry_tax_id' . $suffix] ) ? $localizationSettings['entry_tax_id' . $suffix] : $this->language->get('entry_tax_id');
		$this->data['entry_address_1'] = ! empty( $localizationSettings['entry_address_1' . $suffix] ) ? $localizationSettings['entry_address_1' . $suffix] : $this->language->get('entry_address_1');
		$this->data['entry_address_2'] = ! empty( $localizationSettings['entry_address_2' . $suffix] ) ? $localizationSettings['entry_address_2' . $suffix] : $this->language->get('entry_address_2');
		$this->data['entry_city'] = ! empty( $localizationSettings['entry_city' . $suffix] ) ? $localizationSettings['entry_city' . $suffix] : $this->language->get('entry_city');
        $this->data['entry_area'] = ! empty( $localizationSettings['entry_area' . $suffix] ) ? $localizationSettings['entry_area' . $suffix] : $this->language->get('entry_area');
        $this->data['entry_location'] = ! empty( $localizationSettings['entry_location' . $suffix] ) ? $localizationSettings['entry_location' . $suffix] : $this->language->get('entry_location');
		$this->data['entry_postcode'] = ! empty( $localizationSettings['entry_postcode' . $suffix] ) ? $localizationSettings['entry_postcode' . $suffix] : $this->language->get('entry_postcode');
		$this->data['entry_zone'] =  ! empty( $localizationSettings['entry_checkout_zone' . $suffix] ) ? $localizationSettings['entry_checkout_zone' . $suffix] : $this->language->get('entry_zone');
		$this->data['entry_country'] =  ! empty( $localizationSettings['entry_country' . $suffix] ) ? $localizationSettings['entry_country' . $suffix] : $this->language->get('entry_country');
		$this->data['entry_default'] = $this->language->get('entry_default');
		$this->data['entry_comment'] = $this->language->get('entry_comment');
		$this->data['entry_description'] = $this->language->get('entry_description');
		$this->data['entry_amount'] = $this->language->get('entry_amount');
		$this->data['entry_points'] = $this->language->get('entry_points');

		$this->data['entry_payment_telephone'] = $this->language->get('entry_payment_telephone');

		$this->data['button_save'] = $this->language->get('button_save');
    	$this->data['button_cancel'] = $this->language->get('button_cancel');
    	$this->data['button_add_address'] = $this->language->get('button_add_address');
		$this->data['button_add_history'] = $this->language->get('button_add_history');
		$this->data['button_add_transaction'] = $this->language->get('button_add_transaction');
		$this->data['button_add_reward'] = $this->language->get('button_add_reward');
    	$this->data['button_remove'] = $this->language->get('button_remove');

		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['tab_address'] = $this->language->get('tab_address');
		$this->data['tab_history'] = $this->language->get('tab_history');
		$this->data['tab_transaction'] = $this->language->get('tab_transaction');
		$this->data['tab_reward'] = $this->language->get('tab_reward');
		$this->data['tab_ip'] = $this->language->get('tab_ip');
		$this->data['entry_dob'] = $this->language->get('entry_dob');
		$this->data['entry_gender'] = $this->language->get('entry_gender');
		$this->data['entry_gender_m'] = $this->language->get('entry_gender_m');
		$this->data['entry_gender_f'] = $this->language->get('entry_gender_f');

		$this->data['token'] = null;

		if (isset($this->request->get['customer_id'])) {
			$this->data['customer_id'] = $this->request->get['customer_id'];
		} else {
			$this->data['customer_id'] = 0;
		}

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['payment_telephone'])) {
			$this->data['error_payment_telephone'] = $this->error['payment_telephone'];
		} else {
			$this->data['error_payment_telephone'] = '';
		}

        if (isset($this->error['name'])) {
			$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = '';
		}
        
 		if (isset($this->error['firstname'])) {
			$this->data['error_firstname'] = $this->error['firstname'];
		} else {
			$this->data['error_firstname'] = '';
		}

 		// if (isset($this->error['lastname'])) {
		// 	$this->data['error_lastname'] = $this->error['lastname'];
		// } else {
		// 	$this->data['error_lastname'] = '';
		// }

 		if (isset($this->error['email'])) {
			$this->data['error_email'] = $this->error['email'];
		} else {
			$this->data['error_email'] = '';
		}

 		if (isset($this->error['telephone'])) {
			$this->data['error_telephone'] = $this->error['telephone'];
		} else {
			$this->data['error_telephone'] = '';
		}

 		// if (isset($this->error['password'])) {
		// 	$this->data['error_password'] = $this->error['password'];
		// } else {
		// 	$this->data['error_password'] = '';
		// }

 		// if (isset($this->error['confirm'])) {
		// 	$this->data['error_confirm'] = $this->error['confirm'];
		// } else {
		// 	$this->data['error_confirm'] = '';
		// }

		if (isset($this->error['address_firstname'])) {
			$this->data['error_address_firstname'] = $this->error['address_firstname'];
		} else {
			$this->data['error_address_firstname'] = '';
		}

 		if (isset($this->error['address_lastname'])) {
			$this->data['error_address_lastname'] = $this->error['address_lastname'];
		} else {
			$this->data['error_address_lastname'] = '';
		}

  		if (isset($this->error['address_tax_id'])) {
			$this->data['error_address_tax_id'] = $this->error['address_tax_id'];
		} else {
			$this->data['error_address_tax_id'] = '';
		}

		if (isset($this->error['address_address_1'])) {
			$this->data['error_address_address_1'] = $this->error['address_address_1'];
		} else {
			$this->data['error_address_address_1'] = '';
		}

		if (isset($this->error['address_city'])) {
			$this->data['error_address_city'] = $this->error['address_city'];
		} else {
			$this->data['error_address_city'] = '';
		}

		if (isset($this->error['address_postcode'])) {
			$this->data['error_address_postcode'] = $this->error['address_postcode'];
		} else {
			$this->data['error_address_postcode'] = '';
		}

		if (isset($this->error['address_country'])) {
			$this->data['error_address_country'] = $this->error['address_country'];
		} else {
			$this->data['error_address_country'] = '';
		}

		if (isset($this->error['address_zone'])) {
			$this->data['error_address_zone'] = $this->error['address_zone'];
		} else {
			$this->data['error_address_zone'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_customer_group_id'])) {
			$url .= '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_approved'])) {
			$url .= '&filter_approved=' . $this->request->get['filter_approved'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/component/customers', '', 'SSL'),
      		'separator' => ' :: '
   		);

        $this->data['breadcrumbs'][] = array(
            'text'      => !isset($this->request->get['customer_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href'      => $this->url->link('sale/component/customers', '', 'SSL'),
            'separator' => ' :: '
        );

		if (!isset($this->request->get['customer_id'])) {
			$this->data['action'] = $this->url->link('sale/customer/insert', '', 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/customer/update', 'customer_id=' . $this->request->get['customer_id'] . $url, 'SSL');
		}

    	$this->data['cancel'] = $this->url->link('sale/component/customers', '', 'SSL');



    	if (isset($this->request->get['customer_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $customer_info = $this->model_sale_customer->getCustomer($this->request->get['customer_id']);
            $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

            if ($queryMultiseller->num_rows) {
                $this->load->model('multiseller/seller');
                $customer_seller_info = $this->model_multiseller_seller->getSellerInfo($this->request->get['customer_id']);

            }
        }
        
        if (!empty($customer_info)) {

            $this->data['buyer_subscription_plan_installed'] = \Extension::isInstalled('buyer_subscription_plan') && $this->config->get('buyer_subscription_plan_status');
            
            if($this->data['buyer_subscription_plan_installed']){
                $this->load->model('module/buyer_subscription_plan/subscription');
                $this->data['customer_subscription_payments_log'] = $this->model_module_buyer_subscription_plan_subscription->getCustomerPaymentsLog($customer_info['customer_id']);
            }
        }

        if (isset($this->request->post['buyer_subscription_plan'])) {
            $this->data['buyer_subscription_plan'] = $this->request->post['buyer_subscription_plan'];
        } elseif ($this->data['buyer_subscription_plan_installed'] && !empty($customer_info)) {
            $plan = $this->model_module_buyer_subscription_plan_subscription->get($customer_info['buyer_subscription_id']);
            $this->data['buyer_subscription_plan'] = ['id' => $plan['subscription_id'], 'title' => $plan['translations'][$this->config->get('config_language_id')]['title']];
        } else {
            $this->data['buyer_subscription_plan'] = '';
        }

        if (isset($this->request->post['tax_card'])) {
            $this->data['tax_card'] = $this->request->post['tax_card'];
        } elseif ($customer_seller_info) {
            $this->data['tax_card'] = $customer_seller_info['tax_card'];
        }
        if (isset($this->request->post['bank_name'])) {
            $this->data['bank_name'] = $this->request->post['bank_name'];
        } elseif ($customer_seller_info) {
            $this->data['bank_name'] = $customer_seller_info['bank_name'];
        }
        if (isset($this->request->post['bank_iban'])) {
            $this->data['bank_iban'] = $this->request->post['bank_iban'];
        } elseif ($customer_seller_info) {
            $this->data['bank_iban'] = $customer_seller_info['bank_iban'];
        }
        if (isset($this->request->post['bank_transfer'])) {
            $this->data['bank_transfer'] = $this->request->post['bank_transfer'];
        } elseif ($customer_seller_info) {
            $this->data['bank_transfer'] = $customer_seller_info['bank_transfer'];
        }

    	if (isset($this->request->post['firstname'])) {
      		$this->data['firstname'] = $this->request->post['firstname'];
		} elseif (!empty($customer_info)) {
			$this->data['firstname'] = $customer_info['firstname'];
		} else {
      		$this->data['firstname'] = '';
    	}

    	// if (isset($this->request->post['lastname'])) {
      	// 	$this->data['lastname'] = $this->request->post['lastname'];
    	// } elseif (!empty($customer_info)) {
		// 	$this->data['lastname'] = $customer_info['lastname'];
		// } else {
      	// 	$this->data['lastname'] = '';
    	// }

    	if (isset($this->request->post['email'])) {
      		$this->data['email'] = $this->request->post['email'];
    	} elseif (!empty($customer_info)) {
			$this->data['email'] = $customer_info['email'];
		} else {
      		$this->data['email'] = '';
    	}

    	if (isset($this->request->post['telephone'])) {
      		$this->data['telephone'] = $this->request->post['telephone'];
    	} elseif (!empty($customer_info)) {
			$this->data['telephone'] = $customer_info['telephone'];
		} else {
      		$this->data['telephone'] = '';
    	}
        
        if (isset($this->request->post['company'])) {
            $this->data['company'] = $this->request->post['company'];
      } elseif (!empty($customer_info)) {
          $this->data['company'] = $customer_info['company'];
      } else {
            $this->data['company'] = '';
      }

    	if (isset($this->request->post['fax'])) {
      		$this->data['fax'] = $this->request->post['fax'];
    	} elseif (!empty($customer_info)) {
			$this->data['fax'] = $customer_info['fax'];
		} else {
      		$this->data['fax'] = '';
    	}

    	if (isset($this->request->post['newsletter'])) {
      		$this->data['newsletter'] = $this->request->post['newsletter'];
    	} elseif (!empty($customer_info)) {
			$this->data['newsletter'] = $customer_info['newsletter'];
		} else {
      		$this->data['newsletter'] = '';
    	}
        
    	if (isset($this->request->post['dob'])) {
      		$this->data['dob'] = $this->request->post['dob'];
    	} elseif (!empty($customer_info)) {
			$this->data['dob'] = $customer_info['dob'];
		} else {
      		$this->data['dob'] = '';
    	}
        
    	if (isset($this->request->post['gender'])) {
      		$this->data['gender'] = $this->request->post['gender'];
    	} elseif (!empty($customer_info)) {
			$this->data['gender'] = $customer_info['gender'];
		} else {
      		$this->data['gender'] = '';
    	}

		$this->load->model('sale/customer_group');

		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

    	if (isset($this->request->post['customer_group_id'])) {
      		$this->data['customer_group_id'] = $this->request->post['customer_group_id'];
    	} elseif (!empty($customer_info)) {
			$this->data['customer_group_id'] = $customer_info['customer_group_id'];
		} else {
      		$this->data['customer_group_id'] = $this->config->get('config_customer_group_id');
    	}

    	if (isset($this->request->post['status'])) {
      		$this->data['status'] = $this->request->post['status'];
    	} elseif (!empty($customer_info)) {
			$this->data['status'] = $customer_info['status'];
		} else {
      		$this->data['status'] = 1;
        }
        
    	// if (isset($this->request->post['password'])) {
		// 	$this->data['password'] = $this->request->post['password'];
		// } else {
		// 	$this->data['password'] = '';
		// }

		// if (isset($this->request->post['confirm'])) {
    	// 	$this->data['confirm'] = $this->request->post['confirm'];
		// } else {
		// 	$this->data['confirm'] = '';
		// }

		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

        if (isset($this->request->get['customer_id'])) {
            $this->data['addresses'] = $this->model_sale_customer->getAddresses($this->request->get['customer_id']);
        } elseif (!empty($customer_info)) {
            $this->data['addresses'] = $this->model_sale_customer->getAddresses($customer_info['customer_id']);
        } else {
            $this->data['addresses'] = array();
        }

	    // if (isset($this->request->get['customer_id'])) {
		// 	$this->data['addresses'] = $this->model_sale_customer->getAddresses($this->request->get['customer_id']);
		// } else {
		// 	$this->data['addresses'] = array();
       	// }

    	$customerOrders = $this->order->getCustomerOrdersForCustomerProfile($this->request->get['customer_id']);

        if ( $customerOrders !== false )
        {
            foreach ( $customerOrders as $index => $order )
            {
                $customerOrders[$index]['total'] = $this->currency->format( $order['total'], $order['currency'] );
            }
        }

        $this->data['customer_orders'] = $customerOrders;

        $this->data['ordersCount'] = $customerOrders !== false ? count($customerOrders) : 0;

        $this->load->model('tool/image');

    	if (isset($this->request->post['address_id'])) {
      		$this->data['address_id'] = $this->request->post['address_id'];
    	} elseif (!empty($customer_info)) {
			$this->data['address_id'] = $customer_info['address_id'];
		} else {
      		$this->data['address_id'] = '';
    	}

        $this->data['historyNotes'] = array();

        $results = $this->model_sale_customer->getHistories($this->request->get['customer_id'], 0, 5);

        foreach ($results as $result) {
            $this->data['historyNotes'][] = array(
                'comment'     => $result['comment'],
                'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added']))
            );
        }

		$this->data['combinedHistory'] = $this->getTimeLine();

        $this->data['customer_fields'] = $this->config->get('config_customer_fields');
        $this->data['customer_fields']['registration']['email'] = $this->identity->isLoginByPhone() ? 0 : 1;
        $this->data['customer_fields']['registration']['telephone'] = $this->identity->isLoginByPhone() ? 1 : 0;
        $this->load->model("module/google_map");
        $this->data['customer_fields']['map'] = $this->model_module_google_map->getSettings();
        
		$this->template = 'sale/customer/profile_v2.expand';
		$this->base="common/base";

		$this->response->setOutput($this->render_ecwig());
	}
    
    private function validateFormV2()
    {
        $customerFields = $this->config->get('config_customer_fields')['registration'];
        $customerFields['email'] = $this->identity->isLoginByPhone() ? 0 : 1;
        $customerFields['telephone'] = $this->identity->isLoginByPhone() ? 1 : 0;
        
        if (!$this->user->hasPermission('modify', 'sale/customer')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (!utf8_strlen($this->request->post['firstname'])
        ) {
            $this->error['firstname'] = $this->language->get('error_name');
        }

        // check if email field required or has length
        // apply validation for create customer only, for update admin can't update the saved email
        if (!isset($this->request->get['customer_id']) && ((int)$customerFields['email'] === 1 || utf8_strlen($this->request->post['email']))) {
            if (
                (utf8_strlen($this->request->post['email']) > 96) ||
                !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])
            ) {
                $this->error['email'] = $this->language->get('error_email');
            }
    
            $customer_info = $this->model_sale_customer->getCustomerByEmail($this->request->post['email']);
    
            if (!isset($this->request->get['customer_id'])) {
                if ($customer_info) {
                    $this->error['error_exists'] = $this->language->get('error_exists');
                }
            } else {
                if ($customer_info && ($this->request->get['customer_id'] != $customer_info['customer_id'])) {
                    $this->error['error_exists'] = $this->language->get('error_exists');
                }
            }
        }

        // apply validation for create customer only, for update admin can't update the saved telephone
        if (!isset($this->request->get['customer_id']) && ((int)$customerFields['telephone'] === 1 || utf8_strlen($this->request->post['telephone']))) {
            if (
                (utf8_strlen($this->request->post['telephone']) < 3) ||
                (utf8_strlen($this->request->post['telephone']) > 32)
            ) {
                $this->error['telephone'] = $this->language->get('error_telephone');
            }
    
            if (preg_match('/^\+?[0-9]*$/', $this->request->post['telephone']) == false) {
                $this->error['telephone'] = $this->language->get('error_telephone');
            }
        }
        
        if((int)$customerFields['gender'] === 1 && !utf8_strlen($this->request->post['gender'])) {
            $this->error['gender'] = $this->language->get('error_gender');
        }
        
        if((int)$customerFields['company'] === 1 && !utf8_strlen($this->request->post['company'])) {
            $this->error['company'] = $this->language->get('error_company');
        }
        
        if((int)$customerFields['dob'] === 1 && !utf8_strlen($this->request->post['dob'])) {
            $this->error['dob'] = $this->language->get('error_dob');
        }
        
        if (utf8_strlen($this->request->post['dob'])) {
            $now = new DateTime();
            $customerDate = DateTime::createFromFormat('Y-m-d', $this->request->post['dob']);
            $now->setTime(0, 0);
            if ($customerDate >= $now) {
                $this->error['dob'] = $this->language->get('error_dob_invalid');
            } 
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }

    private function validateAddressForm()
    {
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) return $this->validateAddressFormV2();
        
        if (!$this->user->hasPermission('modify', 'sale/customer')) {
            $this->error['error'] = $this->language->get('error_permission');
        }
        
        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }
    
    private function validateAddressFormV2()
    {
        $addressFields = $this->config->get('config_customer_fields')['address'];
        
        if (!$this->user->hasPermission('modify', 'sale/customer')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if($addressFields['country_id'] && !utf8_strlen($this->request->post['address']['country_id'])) {
            $this->error['country'] = $this->language->get('error_country');
        }
        
        if($addressFields['zone_id'] && !utf8_strlen($this->request->post['address']['zone_id'])) {
            $this->error['zone'] = $this->language->get('error_zone');
        }
        
        if($addressFields['area_id'] && !utf8_strlen($this->request->post['address']['area_id'])) {
            $this->error['area'] = $this->language->get('error_area');
        }
        
        if($addressFields['address_1'] && !utf8_strlen($this->request->post['address']['address_1'])) {
            $this->error['address_1'] = $this->language->get('error_address_1');
        }
        
        if($addressFields['address_2'] && !utf8_strlen($this->request->post['address']['address_2'])) {
            $this->error['address_2'] = $this->language->get('error_address_2');
        }
        
        if($addressFields['postcode'] && !utf8_strlen($this->request->post['address']['postcode'])) {
            $this->error['postcode'] = $this->language->get('error_postcode');
        }
        
        // if($addressFields['location'] && !utf8_strlen($this->request->post['address']['location'])) {
        //     $this->error['location'] = $this->language->get('error_location');
        // }
        
        if($addressFields['telephone'] || utf8_strlen($this->request->post['address']['telephone'])) {
            if (
                (utf8_strlen($this->request->post['address']['telephone']) < 3) ||
                (utf8_strlen($this->request->post['address']['telephone']) > 32)
            ) {
                $this->error['shipping_telephone'] = $this->language->get('error_shipping_telephone');
            }
    
            if (preg_match('/^\+?[0-9]*$/', $this->request->post['address']['telephone']) == false) {
                $this->error['shipping_telephone'] = $this->language->get('error_shipping_telephone');
            }
        }
        
        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }
}
