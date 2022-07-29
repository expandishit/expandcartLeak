<?php
require_once('jwt_helper.php');
class ControllerApiLogin extends Controller {
	public function index() {


		$this->load->language('api/login');

		// Delete old login so not to cause any issues if there is an error
		unset($this->session->data['api_id']);

		$keys = array(
			'username',
			'password'
		);

		foreach ($keys as $key) {
			if (!isset($this->request->post[$key])) {
				$this->request->post[$key] = '';
			}
		}

		$json = array();
        $params = json_decode(file_get_contents('php://input'));
        $username = "";
        $password = "";
        if($params){
            $username = $params->username;
            $password = $params->password;
        }
        else{
            $username = $this->request->post['username'];
            $password = $this->request->post['password'];
        }
		$this->load->model('account/api');

		$api_info = $this->model_account_api->login($username, $password);

		if ($api_info) {
			$this->session->data['api_id'] = $api_info['api_id'];

			$json['cookie'] = $this->session->getId();
            $token = array();
            $token['api_id'] = $api_info['api_id'];
            $token['uniqueid'] = uniqid();
            $json['token'] = JWT::encode($token, API_ENC_KEY);
            $this->model_account_api->insertToken($json['token']);
			$json['success'] = $this->language->get('text_success');

            $infoPage_id = $this->config->get('mapp_infopage');

            $infoPageName = "";

            if ($infoPage_id != 0) {
                $this->load->model('catalog/information');

                $information_info = $this->model_catalog_information->getInformation($infoPage_id);

                if ($information_info) {
                    $infoPageName = $information_info['title'];
                }
            }

            $settings['MainColor'] = $this->config->get('mapp_main_color');

          //  $logo_image = $this->config->get('mapp_logo_image');
             $logo_image = $this->config->get('config_logo');

            if (!empty($logo_image) && $logo_image && \Filesystem::isExists('image/' . $logo_image)) {
                $settings['LogoURL'] = \Filesystem::getUrl('image/' . $logo_image);
            } else {
                $settings['LogoURL'] = \Filesystem::getUrl('image/no_image.jpg');
            }

            $this->load->model('localisation/language');
            $language_id = $this->config->get('config_language_id');
            $language = $this->model_localisation_language->getLanguage($language_id);
            $languages = $this->model_localisation_language->getLanguages();

            //get enabled languages only
            foreach ($languages as $lang){
                if ($lang['status'] != '1')
                    unset($languages[$lang['code']]);
            }

            $languages_full_info = array();
            foreach ($languages as $key => $language)
            {
                $languages_full_info[$key]['LanguageCode'] = $language['code'];
                $languages_full_info[$key]['LanguageName'] = $language['name'];
            }

            $languages = array_column($languages, 'code');

            $settings['StoreSlogan'] = $this->config->get('mapp_store_slogan');
            $settings['FooterInfo'] = html_entity_decode($this->config->get('mapp_footerinfo'));
            $settings['StoreName'] = $this->config->get('config_name');
            $settings['InfoPageName'] = $infoPageName;
            $settings['LanguageCode'] = $language['code'];
            $settings['languages'] = $languages;
            $settings['languages_full_info'] = $languages_full_info;
            $settings['SocialLoginInfo'] = $this->config->get('d_social_login_settings');
            $settings['identityType'] = $this->identity->getIdentityType();
            $settings['countries'] = $this->getCountriesList();
            $settings['googleMapCredentials'] = $this->getGoogleMapCredentials();

            $json['settings'] = $settings;
		} else {

			$json['error'] = $this->language->get('error_login');
		}

		$this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');
        //$this->response->addHeader('');
		$this->response->setOutput(json_encode($json));
    }
    
    public function socialLogin() {

        $json = array();
        $params = json_decode(file_get_contents('php://input'));

        $this->load->model('account/api');

        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
        } else {
            
            try {
                $data = json_decode(file_get_contents('php://input'));
                
                $authentication_data = array(
                    'provider' => $data->provider,
                    'identifier' => $data->profile->identifier,
                    'web_site_url' => $data->profile->webSiteURL,
                    'profile_url' => $data->profile->profileURL,
                    'photo_url' => $data->profile->photoURL,
                    'display_name' => $data->profile->displayName,
                    'description' => $data->profile->description,
                    'first_name' => $data->profile->firstName,
                    'last_name' => $data->profile->lastName,
                    'gender' => $data->profile->gender,
                    'language' => $data->profile->language,
                    'age' => $data->profile->age,
                    'birth_day' => $data->profile->birthDay,
                    'birth_month' => $data->profile->birthMonth,
                    'birth_year' => $data->profile->birthYear,
                    'email' => $data->profile->email,
                    'email_verified' => $data->profile->emailVerified,
                    'phone' => $data->profile->phone,
                    'address' => $data->profile->address,
                    'country' => $data->profile->country,
                    'region' => $data->profile->region,
                    'city' => $data->profile->city,
                    'zip' => $data->profile->zip
                );

                $this->load->model("module/d_social_login");
                $this->load->model("account/customer");

                //check by identifier
                $customer_id = $this->model_module_d_social_login->getCustomerByIdentifier($data->provider, $data->profile->identifier);
                $customer_info = array();

                if ($customer_id) {
                    $customer_info = $this->model_account_customer->getCustomer($customer_id);
                    $json = [
                        'status' => 'ok',
                        'data' => compact('customer_id')
                    ];
                } else {
                    $customer_id = $this->model_module_d_social_login->getCustomerByIdentifierOld($data->provider, $data->profile->identifier);
                }

                //check by email
                if (!$customer_id && $data->profile->email) {

                    $customer_id = $this->model_module_d_social_login->getCustomerByEmail($data->profile->email);
                    if ($customer_id) {
                        $customer_info = $this->model_account_customer->getCustomer($customer_id);
                        $json = [
                            'status' => 'ok',
                            'data' => compact('customer_id')
                        ];
                    }
                }
                    
                if (!$customer_id) {

                    $address = array();

                    if (!empty($data->profile->address)) {
                        $address[] = $data->profile->address;
                    }

                    if (!empty($data->profile->region)) {
                        $address[] = $data->profile->region;
                    }

                    if (!empty($data->profile->country)) {
                        $address[] = $data->profile->country;
                    }

                    $customer_data = array(
                        'email'      => $data->profile->email,
                        'firstname'  => $data->profile->firstName,
                        'lastname'   => $data->profile->lastName,
                        'telephone'  => $data->profile->phone,
                        'fax'        => false,
                        'newsletter' => $data->newsletter,
                        'customer_group_id' => (isset($data->customer_group)) ? $data->customer_group : '1',
                        'company'    => false,
                        'address_1'  => ($address ? implode(', ', $address) : false),
                        'address_2'  => false,
                        'city'       => $data->profile->city,
                        'postcode'   => $data->profile->zip,
                        'country_id' => $this->model_module_d_social_login->getCountryIdByName($data->profile->country),
                        'zone_id'    => $this->model_module_d_social_login->getZoneIdByName($data->profile->region),
                        'password'   => ''
                    );
                    
                    $customer_data['password'] = $this->password();
                    $customer_id = $this->model_module_d_social_login->addCustomer($customer_data);
                    $customer_info = $this->model_account_customer->getCustomer($customer_id);

                    $authentication_data['customer_id'] = (int) $customer_id;
                    $this->model_module_d_social_login->addAuthentication($authentication_data);

                }

                $json['status'] = "ok";
                $json['data'] = compact('customer_id');
                $json['is_logged'] = true;
                $json['customer'] = $customer_info ? $customer_info : "no_customer_info";

            } catch (Exception $e) {
                http_response_code(500);
                $json['error']['warning'] = 'Something went wrong';
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');
        $this->response->setOutput(json_encode($json));
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
    
    private function getCountriesList()
    {
        $this->load->model('localisation/country');
        return $this->model_localisation_country->getCountries();
    }
    
    private function getGoogleMapCredentials()
    {
        $this->load->model("module/google_map");
        return $this->model_module_google_map->getSettings();
    }

    public function getLoginType()
    {
        $json = array();
        $json['success'] = true;
        $setting = array();
        $setting['identityType'] = $this->identity->getIdentityType();
        $json['setting'] = $setting;
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');
        $this->response->setOutput(json_encode($json));
    }
}
