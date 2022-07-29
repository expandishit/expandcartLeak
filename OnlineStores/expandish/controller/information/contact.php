<?php 
class ControllerInformationContact extends Controller {
	private $error = array(); 
	    
  	public function index() {

  		//// Redirect to messaging seller
		if(($this->request->server['REQUEST_METHOD'] == 'POST') && $this->request->post['product_id']){
			$pr_id = $this->request->post['product_id'];

			$this->load->model('multiseller/status');
	        $multiseller = $this->model_multiseller_status->is_installed();
            if($multiseller) 
            {
            	//Check if MS Messaging seller
            	$multisellerReplcContactForm = $this->model_multiseller_status->is_replace_contactform();
            	
            	$seller_id = $this->MsLoader->MsProduct->getSellerId($pr_id);
                $seller = $this->MsLoader->MsSeller->getSeller($seller_id);

            	if($multisellerReplcContactForm && $seller_id != (int)$this->customer->getId() ){           		
                	if ($seller) {
                		$redirect = $this->url->link('account/messagingseller', 'seller_id='.$seller_id.'&product_id=' . $pr_id);
				        $this->redirect($redirect);
				        return; 
                	}
            	}
 
            }
		}
		/////////////////////////////////

		$this->language->load_json('information/contact');

    	$this->document->setTitle($this->language->get('heading_title'));


        $this->initializer([
            'security/throttling',
            'setting/setting',
            'module/google_captcha/settings'
        ]);

        $this->data['recaptcha'] = [
            'status' => $this->settings->isActive(),
            'site-key' => $this->settings->reCaptchaSiteKey(),
            "page_enabled_status"=>$this->settings->getPageStatus("client_contactus")
        ];


        if ($this->data['recaptcha']['status'] == 1 AND $this->data['recaptcha']['page_enabled_status'] == 1) {

            $this->data['languageCode'] = $this->config->get('config_language');

            $this->data['recaptchaFormSelector'] = 'contactForm';

            $this->data['recaptchaAction'] = 'login';

            $this->document->addInlineScript(function () {
                return $this->renderDefaultTemplate('template/security/recaptcha.expand');
            });
        }

		// Check if client phone number is configured to show and send 
		// with client email message or not
		
		$settings = $this->setting->getSetting('config');

        /**
         * Prevent sending emails if expandcart SMTP enabled
         */
        $expandcartEnabled = false;
        if ($this->config->get('config_mail_protocol') == 'expandcart_relay' || $this->config->get('config_mail_protocol') == 'mail') {
		
            $expandcartEnabled = true;
        }else{
	
            $expandcartEnabled = false;
        }


    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate() && !$expandcartEnabled) {

		
			$this->sendEmailToSender();
			$this->sendEmailToAdmin();
	  		$this->redirect($this->url->link('information/contact/success'));
    	}
	
      	$this->data['breadcrumbs'] = array();
        if($this->request->get['ismobile'] != 1) {
            $this->data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home'),
                'separator' => false
            );

            $this->data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('information/contact'),
                'separator' => $this->language->get('text_separator')
            );
        }
		
		if (isset($this->error['name'])) {
    		$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = '';
		}
		
		if (isset($this->error['email'])) {
			$this->data['error_email'] = $this->error['email'];
		} else {
			$this->data['error_email'] = '';
		}		
		
		if (isset($this->error['enquiry'])) {
			$this->data['error_enquiry'] = $this->error['enquiry'];
		} else {
			$this->data['error_enquiry'] = '';
		}		
		
 		if (isset($this->error['captcha'])) {
			$this->data['error_captcha'] = $this->error['captcha'];
		} else {
			$this->data['error_captcha'] = '';
		}	
		
		if(isset($settings['config_show_contact_us_client_phone']) &&
		$settings['config_show_contact_us_client_phone'] == "1")
		{
			$this->data['client_phone_number_show'] = true;
			if(isset($settings['config_contact_us_client_phone_required']) &&
			$settings['config_contact_us_client_phone_required'] == "1")
			if (isset($this->error['phone'])) {
				$this->data['error_phone'] = $this->error['phone'];
			} else {
				$this->data['error_phone'] = '';
			}	
		}
		
		//$this->data['action'] = $this->url->link('information/contact');
		$this->data['store'] = $this->config->get('config_name');
    	$this->data['address'] = nl2br($this->config->get('config_address'));
    	$this->data['telephone'] = $this->config->get('config_telephone');
    	$this->data['fax'] = $this->config->get('config_fax');
    	
		if (isset($this->request->post['name'])) {
			$this->data['name'] = $this->request->post['name'];
		} else {
			$this->data['name'] = $this->customer->getFirstName();
		}

		if (isset($this->request->post['email'])) {
			$this->data['email'] = $this->request->post['email'];
		} else {
			$this->data['email'] = $this->customer->getEmail();
		}
		
		if (isset($this->request->post['enquiry'])) {
			$this->data['enquiry'] = $this->request->post['enquiry'];
		} elseif(isset($this->request->get['enquiry'])) {
            $this->data['enquiry'] = $this->request->get['enquiry'] . ": ";
        } else {
			$this->data['enquiry'] = '';
		}
		
		if (isset($this->request->post['captcha'])) {
			$this->data['captcha'] = $this->request->post['captcha'];
		} else {
			$this->data['captcha'] = '';
		}		
		if (isset($this->request->post['phone'])) {
			$this->data['phone'] = $this->request->post['phone'];
		} else {
			$this->data['phone'] = '';
		}		

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/information/contact.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/information/contact.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/information/contact.expand';
        }
		
		
		if($this->request->get['ismobile'] != 1) {
            $this->children = array(
                'common/footer',
                'common/header'
            );
        }
				
 		$this->response->setOutput($this->render_ecwig());
  	}

  	public function success() {
		$this->language->load_json('information/contact');

		$this->document->setTitle($this->language->get('heading_title')); 

      	$this->data['breadcrumbs'] = array();

        if($this->request->get['ismobile'] != 1) {
            $this->data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home'),
                'separator' => false
            );

            $this->data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('information/contact'),
                'separator' => $this->language->get('text_separator')
            );
        }
		
    	$this->data['continue'] = $this->url->link('common/home');

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/success.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/success.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/common/success.expand';
        }
        if($this->request->get['ismobile'] != 1) {
            $this->children = array(
                'common/footer',
                'common/header'
            );
        }
				
 		$this->response->setOutput($this->render_ecwig());
	}
	
	/**
	 * Validate the user inputs
	 */
  	protected function validate() {

		if(\Extension::isInstalled('google_captcha')){
			$this->initializer([
				'module/google_captcha/settings'
			]);
			$this->data['recaptcha'] = [
				'status' => $this->settings->isActive(),
				'site-key' => $this->settings->reCaptchaSiteKey(),
				"page_enabled_status"=>$this->settings->getPageStatus("client_contactus")
			];
		//
			if( $this->data['recaptcha']['status'] == 1 && $this->data['recaptcha']['page_enabled_status'] == 1){
				$recaptcha_url  = 'https://www.google.com/recaptcha/api/siteverify';
				$recaptcha_secret_key = $this->settings->reCaptchaSecreteKey();
				$captcha_token = $_POST['g-recaptcha-response'];
				if($captcha_token){
					$captchaResponse = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret_key . '&response=' . $captcha_token);
					$captchaResponse = json_decode($captchaResponse);
					// Score less than 0.5 indicates suspicious activity. Return an error
					if(is_array($captchaResponse));
					{
						$captchaResponse=(object) $captchaResponse;
					}
					if ($captchaResponse->success != '1' || $captchaResponse->score < 0.5 || $captchaResponse->hostname != $_SERVER['SERVER_NAME']) {
						return false;
					}
					
				}else{
			
						return false;
				}
			}
	   }

    	if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
      		$this->error['name'] = $this->language->get('error_name');
    	}

    	if (!preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])) {
      		$this->error['email'] = $this->language->get('error_email');
    	}

    	if ((utf8_strlen($this->request->post['enquiry']) < 10) || (utf8_strlen($this->request->post['enquiry']) > 3000)) {
      		$this->error['enquiry'] = $this->language->get('error_enquiry');
    	}

		$settings = $this->setting->getSetting('config');
		
		if(isset($settings['config_show_contact_us_client_phone']) &&
		$settings['config_show_contact_us_client_phone'] == "1")
		{
			if(isset($settings['config_contact_us_client_phone_required']) &&
			$settings['config_contact_us_client_phone_required'] == "1")
			{
			  if ( empty($this->request->post['phone']) ) {
				$this->error['phone'] = $this->language->get('error_phone');
			  }
			}
		}
		if (!$this->error) {
	
	  		return true;
		} else {
	
	  		return false;
		}  	  
	}

    public function contactWithAjax(){

        if ( $this->request->post )
        {
            $this->language->load_json('information/contact');

            $this->sendEmailToAdmin();

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_message');
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_message');

            $this->response->setOutput(json_encode($result_json));

            return;
        }
    }
	
	/**
	 * Send the confirmation email to the sender (visitor)
	 */
	protected function sendEmailToSender()	
	{
		//Create the Mail object and set it's settings
		$mail = new Mail();
		$mail->protocol = $this->config->get('config_mail_protocol');
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->hostname = $this->config->get('config_smtp_host');
		$mail->username = $this->config->get('config_smtp_username');
		$mail->password = $this->config->get('config_smtp_password');
		$mail->port = $this->config->get('config_smtp_port');
		$mail->timeout = $this->config->get('config_smtp_timeout');
		
		if($this->config->get('config_smtp_host') === 'smtp.office365.com' || $this->config->get('config_smtp_host') == 'zoho.smtp.com' ){
			$from = $this->config->get('config_smtp_username');
		}else{
			$from = (!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email'));
		}

        $mail->setReplyTo(
            $this->config->get('config_mail_reply_to'),
            $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
            $this->config->get('config_email')
        );

		$mail->setFrom($from);
		$mail->setTo($this->request->post['email']);
		$mail->setSender($this->request->post['name']);
		$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_subject'), $this->request->post['name']), ENT_QUOTES, 'UTF-8'));

		if ($this->config->get('custom_email_templates_status') && !isset($cet)) {
			include_once(DIR_SYSTEM . 'library/custom_email_templates.php');

			$cet = new CustomEmailTemplates($this->registry);

			$cet_result = $cet->getEmailTemplate(array('type' => 'admin', 'class' => __CLASS__, 'function' => __FUNCTION__, 'vars' => get_defined_vars()));

			if ($cet_result) {
				if ($cet_result['subject']) {
					$mail->setSubject(html_entity_decode(strip_tags($cet_result['subject']), ENT_QUOTES, 'UTF-8'));
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
			} else {
				$mail->setSubject(
					html_entity_decode(strip_tags($this->language->get('text_message')), ENT_QUOTES, 'UTF-8')
				);
	
				$mail->setText(strip_tags($this->request->post['enquiry']));
			}
		}else{
			$mail->setSubject(
				html_entity_decode(strip_tags($this->language->get('text_message')), ENT_QUOTES, 'UTF-8')
			);

			$mail->setText(strip_tags($this->request->post['enquiry']));
		}

		if(strpos($this->config->get('config_smtp_host'),"zoho") !== false){
			$mail->send('zoho');
		}else{
			$mail->send();
		}

		if ($this->config->get('custom_email_templates_status')) {
			$mail->sendBccEmails();
		}

		unset($mail);
	}

	/**
	 * Send the email to the store admin 
	 */
	protected function sendEmailToAdmin()
	{
		//Create the Mail object and set it's settings
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


		if($this->config->get('config_smtp_host') === 'smtp.office365.com' || $this->config->get('config_smtp_host') == 'zoho.smtp.com' ){
			$from = $this->config->get('config_smtp_username');
		}else{
			$from = (!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email'));
		}

		$mail->setFrom($from);
		$mail->setTo($this->config->get('config_email'));
		$mail->setSender($this->request->post['name']);
		$mail->setSubject(html_entity_decode(sprintf($this->language->get('admin_new_message'), $this->request->post['name']), ENT_QUOTES, 'UTF-8'));

		$message = $this->language->get('admin_new_message_heading') . "\n";

		if(isset($this->request->post['name']) && !empty($this->request->post['name'])){
			$message .= $this->language->get('entry_name')." ".$this->request->post['name'] . "\n";
		}

        if(isset($this->request->post['first_name']) && !empty($this->request->post['first_name'])){
            $message .= $this->language->get('entry_first_name')." ".$this->request->post['first_name'] . "\n";
        }

        if(isset($this->request->post['last_name']) && !empty($this->request->post['last_name'])){
            $message .= $this->language->get('entry_last_name')." ".$this->request->post['last_name'] . "\n";
        }

		if(isset($this->request->post['email']) && !empty($this->request->post['email'])){
			$message .= $this->language->get('entry_email')." ".$this->request->post['email'] . "\n";
		}

		if(isset($this->request->post['phone']) && !empty($this->request->post['phone'])){
			$message .= $this->language->get('entry_phone')." ".$this->request->post['phone'] . "\n";
		}

		if(isset($this->request->post['enquiry']) && !empty($this->request->post['enquiry'])){
			$message .= $this->language->get('entry_enquiry')." ".$this->request->post['enquiry'] . "\n";
		}

        if(isset($this->request->post['address']) && !empty($this->request->post['address'])){
            $message .= $this->language->get('entry_address')." ".$this->request->post['address'] . "\n";
        }

		if(isset($this->request->post['msg']) && !empty($this->request->post['msg'])){
			$message .= $this->request->post['msg'] . "\n";
		}

		$mail->setText("(${$this->request->post['qty']})" . strip_tags($message));
		$mail->setHtml(nl2br($message));
		
		if(strpos($this->config->get('config_smtp_host'),"zoho") !== false){
			$mail->send('zoho');
		}else{
			$mail->send();
		}

		if ($this->config->get('custom_email_templates_status')) {
			$mail->sendBccEmails();
		}

		unset($mail);
	}



	// join us
	public function join() {
		$this->language->load_json('information/contact');
	  	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateJoin()) {
		    $requestData = $this->request->post;
		    $requestFiles = '';
		    if (isset($this->request->files['fattachments'])) {
		      	$requestFiles = $this->request->files['fattachments'];
		    }
		  	$this->sendJoinMail($requestData,$requestFiles);
		  	$json['success']  = $this->language->get('text_success');
	  	}
	  	else{
	  		// errors
	  		$json['error'] = $this->error;
	  	} 
		$this->response->setOutput(json_encode($json));
		
 	}

  	protected function validateJoin() {	
    	if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 60)) {
      		$this->error['name'] = $this->language->get('error_name');
    	}

    	if (!preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])) {
      		$this->error['email'] = $this->language->get('error_email');
    	}

		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}  	  
	}

	private function sendJoinMail($params,$files = null){
	    $message  = $this->language->get('text_greetings')."\n\n";
	    foreach ($params as $key => $value) {
	        $message.= $this->language->get('text_'.$key)." ".$value."\n\n";
	    }

	    $message  .= $this->language->get('text_attachments')."\n\n";
	    $mail = new Mail(); 
	    $mail->protocol = $this->config->get('config_mail_protocol');
	    $mail->parameter = $this->config->get('config_mail_parameter');
	    $mail->hostname = $this->config->get('config_smtp_host');
	    $mail->username = $this->config->get('config_smtp_username');
	    $mail->password = $this->config->get('config_smtp_password');
	    $mail->port = $this->config->get('config_smtp_port');
	    $mail->timeout = $this->config->get('config_smtp_timeout');     
	    $mail->setTo($this->config->get('config_email'));
		$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
	    $mail->setSender($this->config->get('config_name'));
	    $mail->setSubject(html_entity_decode($this->language->get('text_join_title'), ENT_QUOTES, 'UTF-8'));
	    $mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
	    // attachments 
	    $allowed_filetypes = "png,pdf,jpeg,jpg,docx,gif,xlsx,csv,doc";
	    $total = count($files['name']);
	    // Loop through each file
	    for( $i=0 ; $i < $total ; $i++ ) {
		    $file_data['name'] = $_FILES['fattachments']['name'][$i];
		    $file_data['type'] = $_FILES['fattachments']['type'][$i];
		    $file_data['tmp_name'] = $_FILES['fattachments']['tmp_name'][$i];
		    $file_data['size'] = $_FILES['fattachments']['size'][$i];
		    //Make sure we have a file path
		    if ($file_data != ""){
		      	$errors = $this->MsLoader->MsFile->checkFile($file_data, $allowed_filetypes);
		        if(empty($errors)){
		          	$fileName = $this->MsLoader->MsFile->uploadImage($file_data);
		          	$path = \Filesystem::resolvePath("image/".$fileName);
		          	$path = $_SERVER['DOCUMENT_ROOT']."/".$path;
		          	$mail->AddAttachment($path);
		        }
		    }
	    }
		$mail->send();
  	}
}
