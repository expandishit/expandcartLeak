<?php
use ExpandCart\Foundation\Support\Hubspot;
class ControllerCommonForgotten extends Controller {
	private $error = array();

	public function index() {
		if ($this->user->isLogged() && isset($this->request->get['token']) && ($this->request->get['token'] == $this->session->data['token'])) {
			$this->redirect($this->url->link('common/home', '', 'SSL'));
		}
		
		if (!$this->config->get('config_password')) {
			$this->redirect($this->url->link('common/login', '', 'SSL'));
		}

        //################### Freshsales Start #####################################
        try {
            $eventName = "Opened Password Forgot Page";

            FreshsalesAnalytics::init(array('domain'=>'https://' . FRESHSALES_SUBDOMAIN . '.freshsales.io','app_token'=>FRESHSALES_TOKEN));

            FreshsalesAnalytics::trackEvent(array(
                'identifier' => BILLING_DETAILS_EMAIL,
                'name' => $eventName
            ));
        }
        catch (Exception $e) {  }
        //################### Freshsales End #####################################

                //################### Intercom Start #####################################
        try {
            $url = 'https://api.intercom.io/events';
            $authid = INTERCOM_AUTH_ID;

            $cURL = curl_init();
            curl_setopt($cURL, CURLOPT_URL, $url);
            curl_setopt($cURL, CURLOPT_USERPWD, $authid);
            curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($cURL, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($cURL, CURLOPT_POST, true);
            curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept: application/json'
            ));
            $intercomData['event_name'] = 'common-forgotten';
            $intercomData['created_at'] = time();
            $intercomData['user_id'] = STORECODE;
            curl_setopt($cURL, CURLOPT_POSTFIELDS, json_encode($intercomData));
            $result = curl_exec($cURL);
            curl_close($cURL);
        }
        catch (Exception $e) {  }
        //################### Intercom End #######################################

         //################### Hubspot Start #####################################
            
         Hubspot ::tracking('pe25199511_os_forget_password',
                ["ec_os_fpi_storecode"=>STORECODE,
                 "ec_os_fpi_user_id" =>WHMCS_USER_ID 
                ]);

          //################### Hubspot End #####################################
		
		$this->language->load('common/forgotten');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('user/user');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->language->load('mail/forgotten');
			
			$code = sha1(uniqid(mt_rand(), true));
			
			$this->model_user_user->editCode($this->request->post['email'], $code);
            $lang = $this->config->get('config_admin_language');
			$subject = sprintf($this->language->get('text_subject'), ($this->config->get('config_name')[$lang] ?: "Expandcart"));
			
			$message  = sprintf($this->language->get('text_greeting'), ($this->config->get('config_name')[$lang] ?: "Expandcart")) . "\n\n";
			$message .= sprintf($this->language->get('text_change'), ($this->config->get('config_name')[$lang] ?: "Expandcart")) . "\n\n";
			$message .= $this->url->link('common/reset', 'code=' . $code, 'SSL') . "\n\n";
			$message .= sprintf($this->language->get('text_ip'), $this->request->server['REMOTE_ADDR']) . "\n\n";

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

            $sender = 'ExpandCart';
			if(is_array($this->config->get('config_name')))
                $sender = $this->config->get('config_name')[$this->config->get('config_admin_language')] ?? 'ExpandCart';
			else if($this->config->get('config_name'))
                $sender = $this->config->get('config_name');

			$mail->setSender($sender);
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));

            if ($this->config->get('custom_email_templates_status') && !isset($cet)) {
                include_once(DIR_SYSTEM . 'library/custom_email_templates.php');

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

                    if ((isset($this->request->post['attach_invoicepdf']) && $this->request->post['attach_invoicepdf'] == 1) || $cet_result['invoice'] == 1) {
                        $path_to_invoice_pdf = $cet->invoice($order_info, $cet_result['history_id']);

                        $mail->addAttachment($path_to_invoice_pdf);
                    }

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

            if (!$mail->isValidEmailConfig())
                $mail->configureExpandMail();

			$mail->send();
            if ($this->config->get('custom_email_templates_status')) {
                $mail->sendBccEmails();
            }
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('common/login', '', 'SSL'));
		}

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),        	
        	'separator' => false
      	); 
		
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_forgotten'),
			'href'      => $this->url->link('common/forgotten', '', 'SSL'),       	
        	'separator' => $this->language->get('text_separator')
      	);
		
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_your_email'] = $this->language->get('text_your_email');
		$this->data['text_email'] = $this->language->get('text_email');
        $this->data['text_reset_password'] = $this->language->get('text_reset_password');
        $this->data['text_back_login'] = $this->language->get('text_back_login');
        $this->data['text_enter_email'] = $this->language->get('text_enter_email');
        $this->data['direction'] = $this->language->get('direction');
        $this->data['lang'] = $this->language->get('code');

		$this->data['entry_email'] = $this->language->get('entry_email');

		$this->data['button_reset'] = $this->language->get('button_reset');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		$this->data['action'] = $this->url->link('common/forgotten', '', 'SSL');
 
		$this->data['cancel'] = $this->url->link('common/login', '', 'SSL');
    	
		if (isset($this->request->post['email'])) {
      		$this->data['email'] = $this->request->post['email'];
		} else {
      		$this->data['email'] = '';
    	}
				
		$this->template = 'common/forgotten.expand';
        $this->base = 'common/base';

								
		$this->response->setOutput($this->render_ecwig());
	}

	protected function validate() {
		if (!isset($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_email');
		} elseif (!$this->model_user_user->getTotalUsersByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_email');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}


}
?>