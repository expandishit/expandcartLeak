<?php

class ControllerAccountActivation extends Controller
{
    public function activate()
    {
        if (!isset($this->request->get['token'])) {
            $this->redirect($this->url->link('error/not_found', 'msg='."token_is_not_set", 'SSL'));
        }

        $tokenString = $this->request->get['token'];

        $this->initializer([
            'customerActivation' => 'account/activation',
            'customerAccount' => 'account/customer',
            'customerGroup' => 'account/customer_group',
            'smshare' => 'module/smshare',
        ]);

        $this->language->load_json('account/activation');   

//        if (false === $this->customerActivation->validateToken($tokenString)) {
//            
//            $this->redirect($this->url->link('error/not_found', 'msg=token_is_not_valid', 'SSL'));
//        }
        
        $token = $this->customerActivation->getCustomerActivationByToken($tokenString, 1);

        if (!$token) {
            $this->redirect($this->url->link('error/not_found', 'msg=token_is_not_existed', 'SSL'));
        }


        $customerId = $token['customer_id'];

        $customer = $this->customerAccount->getCustomer($customerId);

        if (time() > (strtotime($token['created_at']) + 3600)) {
            $tokenLangExpired =  $this->language->get('token_expired');
            $sendAnotherActivationMail = $this->customerActivation->sendAnotherActivationMail($customer);
            $this->redirect($this->url->link('error/not_found', 'msg='.$tokenLangExpired.'', 'SSL'));

        }

        if (!$customer) {
            $this->redirect($this->url->link('error/not_found', 'msg=customer_is_not_existed', 'SSL'));
        }

        $customerGroup = $this->customerGroup->getCustomerGroup($customer['customer_group_id']);

        if ($customerGroup['email_activation'] != 1) {
            $this->redirect($this->url->link('error/not_found', 'msg=activation_is_not_set', 'SSL'));
        }

        if ($customer['approved'] == 2) {
            $approval = 1;
        }

        if ($customerGroup['approval'] == 1) {
            $approval = 0;
        }

        if ($customerGroup['sms_activation'] == 1) {

            $smsToken = $this->customerActivation->generateSmsToken();

            $this->customerActivation->insertNewActivationCode([
                'customer_id' => $customerId,
                'token' => $smsToken,
                'activation_type' => 2,
            ]);

            $approval = 3;

            $this->smshare->sendActivationSms($customer, $smsToken);
			
			//whatsapp 
			if (\Extension::isInstalled('whatsapp')) {
				$this->load->model('module/whatsapp');
				$this->model_module_whatsapp->sendActivationMessage($customer, $smsToken);			
			}
			
			//whatsapp-v2
			if (\Extension::isInstalled('whatsapp_cloud')) {
				$this->load->model('module/whatsapp_cloud');
				$this->model_module_whatsapp_cloud->sendActivationMessage($customer, $smsToken);			
			}
        }

        $this->customerActivation->updateActivationApprovalStatus($token['id'], 1);

        $this->customerAccount->updateCustomerApprovalByCustomerId($customerId, $approval);

        $this->session->data['success'] = $this->language->get('activatation_success');

        $this->customer->login($customer['email'], $customer['password']);

        if ($customerGroup['sms_activation'] == 1) {
            $this->redirect($this->url->link('account/activation/status'));
        }

        $this->redirect($this->url->link('account/account'));
    }

    public function status()
    {
        $this->initializer([
            'customerActivation' => 'account/activation',
        ]);

        $this->language->load_json('account/activation');    

        $this->data['admin_mail'] = (!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email'));

        $approvalStatus = $this->customer->getApprovalStatus();

        $this->data['approvalStatus'] = $approvalStatus;
        // check if admin choose customer must activate his account get customer id from url
        $customer_id = (int)$this->request->get['customer_id'];
        if($customer_id != 0) {
            $customerActivationData =  $this->customerActivation->getCustomerActivationByCustomerId($customer_id);
            if(is_array($customerActivationData))
            {
                if ($customerActivationData['activation_type'] == 1) {
                    $this->template = $this->checkTemplate('account/activation/status.expand');
                } else if ($customerActivationData['activation_type']  == 2) {

                    $this->data['smsActivateAction'] = $this->url->link('account/activation/smsActivation');

                    $this->template = $this->checkTemplate('account/activation/sms_activation.expand');
                } else {
                    $this->redirect($this->url->link('account/login', '', 'SSL'));
                }
            }else{
                $this->redirect($this->url->link('account/login', '', 'SSL'));
            }
        }else{
            if ($approvalStatus == 2) {
                $this->template = $this->checkTemplate('account/activation/status.expand');
            } else if ($approvalStatus == 3) {

                $this->data['smsActivateAction'] = $this->url->link('account/activation/smsActivation');

                $this->template = $this->checkTemplate('account/activation/sms_activation.expand');
            } else {
                $this->redirect($this->url->link('account/login', '', 'SSL'));
            }
        }




        $this->response->setOutput($this->render_ecwig());
    }

    public function smsActivation()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($this->request->post)
        ) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        if (!isset($this->request->post['smsToken'])) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        $this->initializer([
            'customerActivation' => 'account/activation',
            'customerAccount' => 'account/customer',
            'customerGroup' => 'account/customer_group',
        ]);

        $tokenString = $this->request->post['smsToken'];

        if (false === $this->customerActivation->getCustomerActivationByToken($tokenString, 2)) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        $token = $this->customerActivation->getCustomerActivationByToken($tokenString, 2);

        if (!$token) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        if (time() > (strtotime($token['created_at']) + 3600)) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        $customerId = $token['customer_id'];

        $customer = $this->customerAccount->getCustomer($customerId);

        if (!$customer) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        $customerGroup = $this->customerGroup->getCustomerGroup($customer['customer_group_id']);

        if ($customerGroup['sms_activation'] != 1) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        if ($customer['approved'] == 3) {
            $approval = 1;
        }

        if ($customerGroup['approval'] == 1) {
            $approval = 0;
        }

        $this->customerActivation->updateActivationApprovalStatus($token['id'], 1);

        $this->customerAccount->updateCustomerApprovalByCustomerId($customerId, $approval);

        if ($this->config->get('smshare_cfg_ntfy_admin_by_sms_on_reg') == 1)
        {
            $this->load->model('module/smshare');
            $this->model_module_smshare->notify_or_not_customer_or_admins_on_registration($customer);
        }
		if (\Extension::isInstalled('whatsapp')) {
			//whatsapp		
			//if ($this->config->get('whatsapp_cfg_ntfy_admin_by_WhatsApp_on_reg') == 1)
			//{
				$this->load->model('module/whatsapp');
				$this->model_module_whatsapp->notify_or_not_customer_or_admins_on_registration($customer);			
			//}
		}
		
		//whatsapp-v2
		if (\Extension::isInstalled('whatsapp_cloud')) {
			$this->load->model('module/whatsapp_cloud');
			$this->model_module_whatsapp_cloud->registrationNotification($data);			
		}
			
        $this->redirect($this->url->link('account/activation/status'));
    }
}
