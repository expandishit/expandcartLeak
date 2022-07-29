<?php
class ControllerAccountForgotten extends Controller {

	private $error = array();
	
	public function index() {
		$this->data['error_warning'];

		//If the user is logged in, redirect to his account
		if ($this->customer->isLogged()) {
			$this->redirect($this->url->link('account/account', '', 'SSL'));
		}

		//load language files
		$this->language->load_json('account/forgotten');
		$this->language->load_json('mail/forgotten');

		//set page title
		$this->document->setTitle($this->language->get('heading_title'));

		//Get the method of password reset
		$validateMode = $this->setModePhoneOrEmail();
        $this->data['validation_mode'] = $validateMode;

		//first check if the store's password reset mode is by phone
		if($validateMode == 'validatePhone'){
			//check for the request type=post
			if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
				//First check if phone number exists in DB
				$customer = $this->model_account_customer->getCustomerByPhone($this->request->post['phone']);
				
				if($customer){
					//Generate a random password to send to the user
					$password = substr(sha1(uniqid(mt_rand(), true)), 0, 10);
					
					//set the message subject
					$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));
					
					//set the message
					$message  = sprintf($this->language->get('text_greeting'), $this->config->get('config_name')) . "\n\n";
					$message .= $this->language->get('text_password') . "\n\n";
					$message .= $password;
	
					//check the password reset method
					if($this->isLoginRegisterByPhonenumber()){
						$this->model_account_customer->editPasswordByPhone(trim($this->request->post['phone']), $password);
						
						$result = $this->forwardSMS($message);
						
						$this->handleSmsReponse($result);
					}else{
						//reset password by email
						$this->model_account_customer->editPassword($this->request->post['email'], $password);
						$this->forwardEmail($subject, $message);
						$this->session->data['success'] = $this->language->get('text_success');
					}
	
					$this->redirect($this->url->link('account/login', '', 'SSL'));
				}else{
					$this->session->data['success'] = $this->language->get('error_phone');
					$this->redirect($this->url->link('account/login', '', 'SSL'));
				}
			}
		}else{
		//if the password reset mode is by email
			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateEmail()) {
				$this->language->load_json('mail/forgotten');
				
				$password = substr(sha1(uniqid(mt_rand(), true)), 0, 10);
				
				$this->model_account_customer->editPassword($this->request->post['email'], $password);
				
				$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));
				
				$message  = sprintf($this->language->get('text_greeting'), $this->config->get('config_name')) . "\n\n";
				$message .= $this->language->get('text_password') . "\n\n";
				$message .= $password;
				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->hostname = $this->config->get('config_smtp_host');
				$mail->username = $this->config->get('config_smtp_username');
				$mail->password = $this->config->get('config_smtp_password');
				$mail->port = $this->config->get('config_smtp_port');
				$mail->timeout = $this->config->get('config_smtp_timeout');
				$mail->setReplyTo(
	                $this->config->get('config_mail_reply_to'),
	                $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
	                $this->config->get('config_email')
	            );
				$mail->setTo($this->request->post['email']);
				$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
				$mail->setSender($this->config->get('config_name'));
				$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
				if ($this->config->get('custom_email_templates_status') && !isset($cet)) {
					include_once(DIR_SYSTEM . 'library/custom_email_templates.php');
					$this->registry->customerLanguageId = $this->model_account_customer->getCustomerLanguageByEmail($this->request->post['email'])["language_id"];
					$cet = new CustomEmailTemplates($this->registry);
					$cet_result = $cet->getEmailTemplate(array('type' => 'admin', 'class' => __CLASS__, 'function' => __FUNCTION__, 'vars' => get_defined_vars()));
					if ($cet_result) {
						if ($cet_result['subject']) {
							$mail->setSubject(html_entity_decode($cet_result['subject'], ENT_QUOTES, 'UTF-8'));
						}
						if ($cet_result['html']) {
							$mail->setNewHtml($cet_result['html']);
						}
						if ($cet_result['text']) {
							$mail->setNewText($cet_result['text']);
						}
						if ($cet_result['bcc_html']) {
							$mail->setBccHtml($cet_result['bcc_html']);
						}

						// order_info is not defined

//						if ((isset($this->request->post['attach_invoicepdf']) && $this->request->post['attach_invoicepdf'] == 1) || $cet_result['invoice'] == 1) {
//							$path_to_invoice_pdf = $cet->invoice($order_info, $cet_result['history_id']);
//							$mail->addAttachment($path_to_invoice_pdf);
//						}
						if (isset($this->request->post['fattachments'])) {
							if ($this->request->post['fattachments']) {
								foreach ($this->request->post['fattachments'] as $attachment) {
									foreach ($attachment as $file) {
										$mail->addAttachment($file);
									}
								}
							}
						}
						$mail->setBccEmails($cet_result['bcc_emails']);
					}
				}
				$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();
				if ($this->config->get('custom_email_templates_status')) {
					$mail->sendBccEmails();
				}
				
				$this->session->data['success'] = $this->language->get('text_success');
				$this->redirect($this->url->link('account/login', '', 'SSL'));
			}
		}

		//If the request is GET then show the normal behavior
		//Load the breadcrumbs
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
        	'text'      => $this->language->get('text_forgotten'),
			'href'      => $this->url->link('account/forgotten', '', 'SSL'),       	
        	'separator' => $this->language->get('text_separator')
		);
		  
		//if there are any errors set error data
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		//$this->data['action'] = $this->url->link('account/forgotten', '', 'SSL');
 
		//$this->data['back'] = $this->url->link('account/login', '', 'SSL');
		
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/forgotten.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/forgotten.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/account/forgotten.expand';
		}

		// die($this->template);
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
								
		$this->response->setOutput($this->render_ecwig());
	}


	function handleSmsReponse($result){
		if(preg_match("/^OK/",$result,$matches)){
			$this->session->data['success'] = $this->language->get('text_success_phone');
			return;
		}

		if(preg_match("/^ERR/",$result,$matches)){
			// ATH session error doesn't show as expected so WE  stick with success for now to display fail_phone
			// $this->session->data['error'] = $this->language->get('text_fail_phone');
			$this->session->data['success'] = $this->language->get('text_fail_phone') . ' '. $result ;
			return;
		}
		return;
	}

	protected function isLoginRegisterByPhonenumber(){
		return (bool) $this->data['isLoginRegisterByPhonenumber'];
	}

	protected function validatePhone() {
		
		if (!isset($this->request->post['phone'])) {
		
			$this->error['warning'] = $this->language->get('error_phone');
		
		} elseif (!$this->model_account_customer->getTotalCustomersByPhone(trim($this->request->post['phone']))) {
		
			$this->error['warning'] = $this->language->get('error_phone');
		
		}
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	protected function validateEmail() {
		if (!isset($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_email');
		} elseif (!$this->model_account_customer->getTotalCustomersByEmail(trim($this->request->post['email']))) {
			$this->error['warning'] = $this->language->get('error_email');
		}
		if(!$this->error){
			return true;
		}else{
			return false;
		}
	}

	function setModePhoneOrEmail(){
		$this->load->model('account/customer');
		$this->load->model('account/signup');
		$this->data['isLoginRegisterByPhonenumber'] = $this->model_account_signup->isLoginRegisterByPhonenumber();
		if( !$this->data['isLoginRegisterByPhonenumber'] ){
			return "validateEmail";
		}
		return "validatePhone";
	}

	public function forwardSMS($message){			
		$smshareCommons = new SmshareCommons();
		// $sms_to = "0096477271887"; // example
		$sms_to =  trim($this->request->post['phone']);
		
		$sms_body = $message;
		return $smshareCommons->sendSMS($sms_to, $sms_body, $this->config);
	}

	function forwardEmail($subject, $message){
		$mail = new Mail();
		$mail->protocol  = $this->config->get('config_mail_protocol');
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->hostname  = $this->config->get('config_smtp_host');
		$mail->username  = $this->config->get('config_smtp_username');
		$mail->port      = $this->config->get('config_smtp_port');
		$mail->password  = $this->config->get('config_smtp_password');
		$mail->timeout   = $this->config->get('config_smtp_timeout');
		$mail->setReplyTo(
            $this->config->get('config_mail_reply_to'),
            $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
            $this->config->get('config_email')
        );
		$mail->setTo($this->request->post['email']);
		$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
		$mail->setSender($this->config->get('config_name'));
		$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
	
		if ($this->config->get('custom_email_templates_status') && !isset($cet)) {
			
			include_once(DIR_SYSTEM . 'library/custom_email_templates.php');
			$cet = new CustomEmailTemplates($this->registry);
			$cet_result = $cet->getEmailTemplate(array('type' => 'admin',
			
			
				'class' => __CLASS__,
				
				'function' => __FUNCTION__,
				
				'vars' => get_defined_vars())
			
			);
			if ($cet_result) {
			
				if ($cet_result['subject']) {
			
					$mail->setSubject(html_entity_decode($cet_result['subject'], ENT_QUOTES, 'UTF-8'));
			
				}
				if ($cet_result['html']) {
					$mail->setNewHtml($cet_result['html']);
				}
				if ($cet_result['text']) {
					$mail->setNewText($cet_result['text']);
				}
				if ($cet_result['bcc_html']) {
					$mail->setBccHtml($cet_result['bcc_html']);
				}

				// order_info is not defined

//				if ((isset($this->request->post['attach_invoicepdf']) && $this->request->post['attach_invoicepdf'] == 1) || $cet_result['invoice'] == 1)
//				{
//					$path_to_invoice_pdf = $cet->invoice($order_info, $cet_result['history_id']);
//					$mail->addAttachment($path_to_invoice_pdf);
//				}
				if (isset($this->request->post['fattachments'])) {
					if ($this->request->post['fattachments']) {
						foreach ($this->request->post['fattachments'] as $attachment) {
							foreach ($attachment as $file) {
								$mail->addAttachment($file);
							}
						}
					}
				}
				$mail->setBccEmails($cet_result['bcc_emails']);
			}
		}
	
	
		$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
		$mail->send();
	
	
	
		if ($this->config->get('custom_email_templates_status')) {
			
			$mail->sendBccEmails();
		}
	}
}
?>