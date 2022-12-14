<?php
/*
 *	location: expandish/controller/module/d_social_login.php
 */

class ControllerModuleDSocialLogin extends Controller {
	private $route = 'module/d_social_login';
	protected $id = 'd_social_login';
	private $setting = array();
	private $redirect = '';

	protected function index($settings)
	{

		$this->setup();

		$this->language->load_json($this->route);
		$this->load->model($this->route);

		$this->document->addExpandishStyle('stylesheet/d_social_login/styles.css');
		$this->document->addScript('expandish/view/javascript/d_social_login/spin.min.js');

		$setting = $this->config->get('d_social_login_settings');
        $this->data['body_scripts'] = html_entity_decode($this->config->get('config_body_scripts'), ENT_QUOTES, 'UTF-8');

		$data['size'] = $settings['size'];
		$data['islogged'] = $this->customer->isLogged();

		$providers = $setting['providers'];

		// We hide yahoo and linkedIn accounts from backend and frontend
		unset($providers['Yahoo']);
		unset($providers['LinkedIn']);
		$sort_order = array();
		foreach ($providers as $key => $value) {
			if (isset($value['sort_order'])) {
				$sort_order[$key] = $value['sort_order'];
			}
		}
		array_multisort($sort_order, SORT_ASC, $providers);
		$data['providers'] = $providers;
		foreach ($providers as $key => $val) {
			$data['providers'][$key]['heading'] = $this->language->get('text_sign_in_with_' . $val['id']);
		}
		$data['error'] = false;
		if (isset($this->session->data['d_social_login_error'])) {
			$data['error'] = $this->session->data['d_social_login_error'];
			unset($this->session->data['d_social_login_error']);
		}

		$this->session->data['redirect'] = ($setting['return_page_url']) ? $setting['return_page_url'] : $this->getCurrentUrl();

		//facebook fix
		unset($this->session->data['HA::CONFIG']);
		unset($this->session->data['HA::STORE']);

		$this->data = $data;
		$this->data['language'] = $this->session->data['language'];

		if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/d_social_login.expand')) {
			$this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/d_social_login.expand';
		} else {
			$this->template = $this->config->get('config_template') . '/template/module/d_social_login.expand';
		}

		$this->render_ecwig();
	}

	public function login()
	{

		$this->setup();

		require_once(DIR_SYSTEM . 'library/Hybrid/Auth.php');
		$this->language->load_json($this->route);
		$this->load->model($this->route);

		$this->setting = $this->config->get('d_social_login_settings');

		$this->setting['base_url']   = $this->config->get('config_secure') ? HTTPS_SERVER . 'd_social_login.php' : HTTP_SERVER . 'd_social_login.php';
		$this->setting['debug_file'] = DIR_LOGS . $this->setting['debug_file'];
		$this->setting['debug_mode'] = (bool) $this->setting['debug_mode'];

		if (isset($this->request->get['provider'])) {
			$this->setting['provider'] = $this->request->get['provider'];
		} else {

			// Save error to the System Log
			$this->log->write('Missing application provider.');

			// Set Message
			$this->session->data['error'] = sprintf(
				"An error occurred, please <a href=\"%s\">notify</a> the administrator.",
				$this->url->link('information/contact')
			);

			// Redirect to the Login Page
			$this->response->redirect($this->redirect);
		}

		$this->load->language('module/d_social_login');
		
		foreach($this->setting['fields'] as $key=>$field){
			$this->setting['fields'][$key]['text'] = $this->language->get('text_'.$field['id']);
		}

		try {

			$hybridauth = new Hybrid_Auth($this->setting);
			$hybridauth::$logger->info('d_social_login: Start authantication.');
			$adapter = $hybridauth->authenticate($this->setting['provider']);
			$hybridauth::$logger->info('d_social_login: Start getUserProfile.');
			//get the user profile 
			$profile = $adapter->getUserProfile();
			$this->setting['profile'] = (array) $profile;

			$hybridauth::$logger->info('d_social_login: got UserProfile.' . serialize($this->setting['profile']));
			$authentication_data = array(
				'provider' => $this->setting['provider'],
				'identifier' => $this->setting['profile']['identifier'],
				'web_site_url' => $this->setting['profile']['webSiteURL'],
				'profile_url' => $this->setting['profile']['profileURL'],
				'photo_url' => $this->setting['profile']['photoURL'],
				'display_name' => $this->setting['profile']['displayName'],
				'description' => $this->setting['profile']['description'],
				'first_name' => $this->setting['profile']['firstName'],
				'last_name' => $this->setting['profile']['lastName'],
				'gender' => $this->setting['profile']['gender'],
				'language' => $this->setting['profile']['language'],
				'age' => $this->setting['profile']['age'],
				'birth_day' => $this->setting['profile']['birthDay'],
				'birth_month' => $this->setting['profile']['birthMonth'],
				'birth_year' => $this->setting['profile']['birthYear'],
				'email' => $this->setting['profile']['email'],
				'email_verified' => $this->setting['profile']['emailVerified'],
				'phone' => $this->setting['profile']['phone'],
				'address' => $this->setting['profile']['address'],
				'country' => $this->setting['profile']['country'],
				'region' => $this->setting['profile']['region'],
				'city' => $this->setting['profile']['city'],
				'zip' => $this->setting['profile']['zip']
			);

			$hybridauth::$logger->info('d_social_login: set authentication_data ' . serialize($authentication_data));

			//check by identifier
			$customer_id = $this->model_module_d_social_login->getCustomerByIdentifier($this->setting['provider'], $this->setting['profile']['identifier']);

			if ($customer_id) {
				$hybridauth::$logger->info('d_social_login: getCustomerByIdentifier success.');
				//login
				$this->model_module_d_social_login->login($customer_id);

				//redirect
				$this->response->redirect($this->redirect);
			}

			$customer_id = $this->model_module_d_social_login->getCustomerByIdentifierOld($this->setting['provider'], $this->setting['profile']['identifier']);

			//check by email
			if ($this->setting['profile']['email']) {

				$customer_id = $this->model_module_d_social_login->getCustomerByEmail($this->setting['profile']['email']);
				if ($customer_id) {
					$hybridauth::$logger->info('d_social_login: getCustomerByEmail success.');

					//login
					$this->model_module_d_social_login->login($customer_id);

					//redirect
					$this->response->redirect($this->redirect);
				}
			}


			if (!$customer_id) {
				$hybridauth::$logger->info('d_social_login: no customer_id. creating customer_data');
				//prepare customer data
				$address = array();

				if (!empty($this->setting['profile']['address'])) {
					$address[] = $this->setting['profile']['address'];
				}

				if (!empty($this->setting['profile']['region'])) {
					$address[] = $this->setting['profile']['region'];
				}

				if (!empty($this->setting['profile']['country'])) {
					$address[] = $this->setting['profile']['country'];
				}

				$customer_data = array(
					'email'      => $this->setting['profile']['email'],
					'firstname'  => $this->setting['profile']['firstName'],
					'lastname'   => $this->setting['profile']['lastName'],
					'telephone'  => $this->setting['profile']['phone'],
					'fax'        => false,
					'newsletter' => $this->setting['newsletter'],
					'customer_group_id' => (isset($this->setting['customer_group'])) ? $this->setting['customer_group'] : '1',
					'company'    => false,
					'address_1'  => ($address ? implode(', ', $address) : false),
					'address_2'  => false,
					'city'       => $this->setting['profile']['city'],
					'postcode'   => $this->setting['profile']['zip'],
					'country_id' => $this->model_module_d_social_login->getCountryIdByName($this->setting['profile']['country']),
					'zone_id'    => $this->model_module_d_social_login->getZoneIdByName($this->setting['profile']['region']),
					'password'   => ''
				);

				$hybridauth::$logger->info('d_social_login: set customer_data ' . serialize($customer_data));

				//check if form required
				$form = false;
				foreach ($this->setting['fields'] as $field) {
					if ($field['enabled']) {
						//checking if fields required for input
						$form = true;
						break;
					}
				}

				if (!$form) {
					$hybridauth::$logger->info('d_social_login: adding customer with customer_data');
					$customer_data['password'] = $this->password();
					$customer_id = $this->model_module_d_social_login->addCustomer($customer_data);
				} else {
					$hybridauth::$logger->info('d_social_login: need to use form');
					$this->form($customer_data, $authentication_data);
				}
			}

			if ($customer_id) {
				$hybridauth::$logger->info('d_social_login: customer_id found');
				$authentication_data['customer_id'] = (int) $customer_id;

				$this->model_module_d_social_login->addAuthentication($authentication_data);
				$hybridauth::$logger->info('d_social_login: addAuthentication');
				//login
				$this->model_module_d_social_login->login($customer_id);

				//Send email to admin about the new registration
				if ($this->config->get('config_account_mail')) {
					$mail = new Mail();
					$mail->protocol = $this->config->get('config_mail_protocol');
					$mail->parameter = $this->config->get('config_mail_parameter');
					$mail->hostname = $this->config->get('config_smtp_host');
					$mail->username = $this->config->get('config_smtp_username');
					$mail->password = $this->config->get('config_smtp_password');
					$mail->port = $this->config->get('config_smtp_port');
					$mail->timeout = $this->config->get('config_smtp_timeout');
					$mail->setTo($this->config->get('config_email'));
					$mail->setFrom($this->config->get('config_email'));
                    $mail->setReplyTo(
                        $this->config->get('config_mail_reply_to'),
                        $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                        $this->config->get('config_email')
                    );
					$mail->setSender($this->config->get('config_name'));

					$message  = $this->language->get('text_signup') . "\n\n";
					$message .= $this->language->get('text_website') . ' ' . $this->config->get('config_name') . "\n";
					if (isset($authentication_data['first_name']))
						$message .= $this->language->get('text_firstname') . ' ' . $authentication_data['first_name'] . "\n";
					if (isset($authentication_data['last_name']))
						$message .= $this->language->get('text_lastname') . ' ' . $authentication_data['last_name'] . "\n";

					$message .= $this->language->get('text_email') . ' '  .  $authentication_data['email'] . "\n";
					if (isset($data['telephone']))
						$message .= $this->language->get('text_telephone') . ' ' . $authentication_data['phone'] . "\n";

					//$mail->setTo($this->config->get('config_email'));
					$mail->setSubject(html_entity_decode($this->language->get('text_new_customer'), ENT_QUOTES, 'UTF-8'));

					$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
					$mail->send();
					if ($this->config->get('custom_email_templates_status')) {
						$mail->sendBccEmails();
					}

					// Send to additional alert emails if new account email is enabled
					$emails = explode(',', $this->config->get('config_alert_emails'));

					foreach ($emails as $email) {
						if (strlen($email) > 0 && preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $email)) {
							$mail->setTo($email);
							$mail->send();
							if ($this->config->get('custom_email_templates_status')) {
								$mail->sendBccEmails();
							}
						}
					}
				}
				//redirect
				$this->response->redirect($this->redirect);
			}
		} catch (Exception $e) {
			switch ($e->getCode()) {
				case 0:
					$error = "Unspecified error.";
					break;
				case 1:
					$error = "Hybriauth configuration error.";
					break;
				case 2:
					$error = "Provider not properly configured.";
					break;
				case 3:
					$error = "Unknown or disabled provider.";
					break;
				case 4:
					$error = "Missing provider application credentials.";
					break;
				case 5:
					$error = "Authentication failed. The user has canceled the authentication or the provider refused the connection.";
					break;
				case 6:
					$error = "User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again.";
					$adapter->logout();
					break;
				case 7:
					$error = "User not connected to the provider.";
					$adapter->logout();
					break;
				case 8:
					$error = "Provider does not support this feature.";
					break;
			}

			$this->session->data['d_social_login_error'] = $error;

			$error .= "\n\nHybridAuth Error: " . $e->getMessage();
			$error .= "\n\nTrace:\n " . $e->getTraceAsString();

			$this->log->write($error);

			$this->response->redirect($this->redirect);
		}
	}

	private function form($customer_data, $authentication_data)
	{
		$this->session->data['customer_data'] = $customer_data;
		$this->session->data['authentication_data'] = $authentication_data;
		$data['customer_data'] = $customer_data;
		$data['authentication_data'] = $authentication_data;

		$data['background_img'] = $this->setting['background_img'];
		$data['background_color'] = $this->setting['providers'][ucfirst($this->setting['provider'])]['background_color'];
        $data['body_scripts'] = html_entity_decode($this->config->get('config_body_scripts'), ENT_QUOTES, 'UTF-8');

		$sort_order = array();
		foreach ($this->setting['fields'] as $key => $value) {
			if (isset($value['sort_order'])) {
				$sort_order[$key] = $value['sort_order'];
			}
		}
		array_multisort($sort_order, SORT_ASC, $this->setting['fields']);
		$data['fields'] = $this->setting['fields'];

		$this->load->model('localisation/country');
		$data['countries'] = $this->model_localisation_country->getCountries();
		$this->data = $data;
		$this->session->data['language'];
		if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/d_social_login/form.expand')) {
			$this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/d_social_login/form.expand';
		} else {
			$this->template = $this->config->get('config_template') . '/template/d_social_login/form.expand';
		}
		$this->response->setOutput($this->render_ecwig());
	}

	public function register()
	{

		$this->setup();

		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			return false;
		}
		$this->language->load_json($this->route);
		$this->load->model($this->route);

		$json = array();
		$customer_data = array_merge($this->session->data['customer_data'], $this->request->post);
		$authentication_data = $this->session->data['authentication_data'];
		$this->setting = $this->config->get('d_social_login_settings');

		// check email
		if ($this->validate_email($customer_data['email'])) {
			$customer_id = $this->model_module_d_social_login->getCustomerByEmail($customer_data['email']);
			if ($customer_id) {
				$json['error']['email'] = $this->language->get('error_email_taken');
			}
		} else {

			$json['error']['email'] =  $this->language->get('error_email_incorrect');
		}

		// fields
		foreach ($this->setting['fields'] as $field) {
			if ($field['enabled']) {
				if ($field['id'] == 'confirm') {
					if (($customer_data['password'] != $customer_data['confirm'])) {
						$json['error']['confirm'] = $this->language->get('error_password_and_confirm_different');
					}
				}
				if ($this->request->post[$field['id']] == "" && $field['id'] != "company" && $field['id'] != "company_id" && $field['id'] != "tax_id") {
					$json['error'][$field['id']] = $this->language->get('error_fill_all_fields');
				}
			}
		}


		if (empty($json['error'])) {

			if (!$this->setting['fields']['password']['enabled']) {
				$customer_data['password'] = $this->password();
			}

			$customer_id = $this->model_module_d_social_login->addCustomer($customer_data);

			$authentication_data['customer_id'] = (int) $customer_id;
			$this->model_module_d_social_login->addAuthentication($authentication_data);

			//login
			$this->model_module_d_social_login->login($customer_id);

			//redirect
			$json['redirect'] = $this->redirect;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function validate_email($email)
	{
		if (preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $email)) {
			return true;
		} else {
			return false;
		}
	}

	private function password($length = 8)
	{
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$count = strlen($chars);

		for ($i = 0, $result = ''; $i < $length; $i++) {
			$index = rand(0, $count - 1);
			$result .= substr($chars, $index, 1);
		}

		return $result;
	}

	private function setup()
	{
		// correct &amp; in url
		if (isset($this->request->get)) {

			foreach ($this->request->get as $key => $value) {
				$this->request->get[str_replace('amp;', '', $key)] = $value;
			}
		}
		if (isset($_GET)) {

			foreach ($_GET as $key => $value) {
				$_GET[str_replace('amp;', '', $key)] = $value;
			}
		}

		// set redirect address
		if (isset($this->session->data['redirect'])) {
			$this->redirect = $this->session->data['redirect'];
		} else {
			$this->redirect =  $this->url->link('account/account', '', 'SSL');
		}
	}

	private function getCountryId($profile)
	{
		$this->load->model($this->route);

		if ($profile['country']) {
			return $this->model_module_d_social_login->getCountryIdByName($profile['country']);
		}

		if ($profile['region']) {
			return $this->model_module_d_social_login->getCountryIdByName($profile['region']);
		}

		return $this->config->get('config_country_id');
	}

	public static function getCurrentUrl($request_uri = true)
	{
		if (
			isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
			|| 	isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
		) {
			$protocol = 'https://';
		} else {
			$protocol = 'http://';
		}

		$url = $protocol . $_SERVER['HTTP_HOST'];

		if (isset($_SERVER['SERVER_PORT']) && strpos($url, ':' . $_SERVER['SERVER_PORT']) === FALSE) {
			$url .= ($protocol === 'http://' && $_SERVER['SERVER_PORT'] != 80 && !isset($_SERVER['HTTP_X_FORWARDED_PROTO']))
				|| ($protocol === 'https://' && $_SERVER['SERVER_PORT'] != 443 && !isset($_SERVER['HTTP_X_FORWARDED_PROTO']))
				? ':' . $_SERVER['SERVER_PORT']
				: '';
		}

		if ($request_uri) {
			$url .= $_SERVER['REQUEST_URI'];
		} else {
			$url .= $_SERVER['PHP_SELF'];
		}

		// return current url
		return $url;
	}
}
