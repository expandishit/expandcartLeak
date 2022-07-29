<?php

use \Firebase\JWT\JWT;
use ExpandCart\Foundation\Support\Hubspot;
class ControllerCommonLogin extends Controller
{
	private $error = array();
	          
	public function index()
    {
    	$this->language->load('common/login');


        $this->initializer([
            'security/throttling',
            'module/google_captcha/settings'
        ]);

        $this->data['recaptcha'] = [
            'status' => $this->settings->isActive(),
            'site-key' => $this->settings->reCaptchaSiteKey(),
            "page_enabled_status"=>$this->settings->getPageStatus("admin_login")
        ];



        if ($this->throttling->throttlingStatus()) {
            if ($this->throttling->isBanned($this->user->getRealIp())) {
                if ($this->request->get['route'] != 'error/not_found') {
                    $this->redirect($this->fronturl->link('index.php?route=error/not_found'));
                }
            }
        }

		$this->document->setTitle($this->language->get('heading_title'));
        if(isset($this->request->get['isextend'])) {
            $this->session->data['isextend'] = $this->request->get['isextend'];
        }
		if ($this->user->isLogged()) {
            if (isset($this->request->get['brand_id'])) {
                $billingAccess = '0';

                if ($this->user->hasPermission('access', 'billingaccount/plans') && ENABLE_BILLING == '1') {
                    $billingAccess = '1';
                }

                if ($billingAccess == '1') {
                    $this->load->model('billingaccount/common');

                    //Zendesk link
                    $zd_key = ZENDESK_SHAREDKEY;
                    $zd_subdomain = ZENDESK_SUBDOMAIN;
                    $zd_now = time();
                    $zd_token = array(
                        "jti" => md5($zd_now . rand()),
                        "iat" => $zd_now,
                        "name" => BILLING_DETAILS_NAME,
                        "email" => BILLING_DETAILS_EMAIL
                    );
                    $zd_jwt = JWT::encode($zd_token, $zd_key);
                    $zd_location = "https://" . $zd_subdomain . ".zendesk.com/access/jwt?jwt=" . $zd_jwt;

                    if (isset($this->request->get["return_to"])) {
                        $zd_location .= "&return_to=" . urlencode($this->request->get["return_to"]);
                    }

                    // Redirect
                    header("Location: " . $zd_location);
                    exit;
                }
            }

            if (isset($this->request->post['redirect'])) {
                $this->redirect($this->request->post['redirect'] . '&token=' . $this->session->data['token']);
            } else {
                if (PRODUCTID == 3)
                    $this->redirect($this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'));
                else
                    $this->redirect($this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'));
            }
		}

		if ((
            $this->request->server['REQUEST_METHOD'] == 'POST' ||
            isset($this->request->get['logintoken']) ||
            isset($this->request->get['remote_token']) ||
            isset($this->request->get['webview_token'])
        )) {

            if (!$this->validate()) {
                if (
                    $this->throttling->throttlingStatus() == true &&
                    (
                        $this->request->post['username'] != THE_USERNAME ||
                        $this->request->post['password'] != THE_PASSWORD
                    )
                ) {

                    $throttlingSettings = $this->throttling->getSettings();

                    $throttleCache = [];

                    $user = $this->user->getRealIp();

                    $resource = 'admin/common/login';

                    $throttleCache[$user][$resourse][] = time();

                    if (isset($this->session->data['throttling']) == false) {
                        $this->session->data['throttling'] = [];
                        $this->session->data['throttling']['count'] = 1;
                    } else {
                        $this->session->data['throttling']['count']++;
                    }

                    $this->session->data['throttling']['data'][$user][$resource][] = time();

                    if ($this->session->data['throttling']['count'] >= 5) {
                        $bannedData = [
                            'ipv4' => $user,
                            'resource' => $resource,
                            'attempts' => $throttlingSettings['throttling_limit'],
                            'recaptcha_status' => $throttlingSettings['enable_recaptcha'],
                        ];

                        $this->throttling->banIp($bannedData);

                        unset($this->session->data['throttling']);
                    }
                }
            } else {
                $this->session->data['token'] = null;

                /***************** Start ExpandCartTracking #347688  ****************/

                // send mixpanel increment login event
                $this->load->model('setting/mixpanel');
                $this->model_setting_mixpanel->incrementProperty('$login count');
                $this->model_setting_mixpanel->trackEvent('Login');

                // send amplitude increment login event
                $this->load->model('setting/amplitude');
                $this->model_setting_amplitude->trackEvent('Login');

                // update user default data in mixpanel and amplitude
                $this->model_setting_amplitude->updateUser(null,'default');
                $this->model_setting_mixpanel->updateUser(null,'default');

                /***************** End ExpandCartTracking #347688  ****************/


                if (isset($this->request->get['brand_id'])) {
                    $billingAccess = '0';

                    if ($this->user->hasPermission('access', 'billingaccount/plans') && ENABLE_BILLING == '1') {
                        $billingAccess = '1';
                    }

                    if ($billingAccess == '1') {
                        $this->load->model('billingaccount/common');

                        //Zendesk link
                        $zd_key = ZENDESK_SHAREDKEY;
                        $zd_subdomain = ZENDESK_SUBDOMAIN;
                        $zd_now = time();
                        $zd_token = array(
                            "jti" => md5($zd_now . rand()),
                            "iat" => $zd_now,
                            "name" => BILLING_DETAILS_NAME,
                            "email" => BILLING_DETAILS_EMAIL
                        );
                        $zd_jwt = JWT::encode($zd_token, $zd_key);
                        $zd_location = "https://" . $zd_subdomain . ".zendesk.com/access/jwt?jwt=" . $zd_jwt;

                        if (isset($this->request->get["return_to"])) {
                            $zd_location .= "&return_to=" . urlencode($this->request->get["return_to"]);
                        }

                        // Redirect
                        header("Location: " . $zd_location);
                        exit;
                    }
                }

                if (isset($this->request->get['pid']) && isset($this->request->get['cycle'])) {
                    $this->session->data['checkout_pid'] = $this->request->get['pid'];
                    $this->session->data['checkout_cycle'] = $this->request->get['cycle'];
                }

                if (isset($this->request->post['redirect'])) {
                    $this->redirect($this->request->post['redirect'] . '&token=' . $this->session->data['token']);
                } else if (isset($this->request->post['redirect_route'])) {
                    $this->redirect($this->url->link($this->request->post['redirect_route']));
                } else if (isset($this->request->get['redirect_route'])){
                    $this->session->data['redirect_route'] = $this->request->get['redirect_route'];
                    $this->redirect($this->url->link($this->request->get['redirect_route']));
                }
                else {
                    if (PRODUCTID == 3)
                        $this->redirect($this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'));
                    else
                        $this->redirect($this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'));
                }
            }
		}
		
    	$this->data['heading_title'] = $this->language->get('heading_title');

        //################### Freshsales Start #####################################
        try {
            $eventName = "Opened Backend Login Page";

            //FreshsalesAnalytics::init(array('domain'=>'https://' . FRESHSALES_SUBDOMAIN . '.freshsales.io','app_token'=>FRESHSALES_TOKEN));

            //FreshsalesAnalytics::trackEvent(array(
            //    'identifier' => BILLING_DETAILS_EMAIL,
            //    'name' => $eventName
            //));
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
            $intercomData['event_name'] = 'common-login';
            $intercomData['created_at'] = time();
            $intercomData['user_id'] = STORECODE;
            curl_setopt($cURL, CURLOPT_POSTFIELDS, $intercomData);
            $result = curl_exec($cURL);
            curl_close($cURL);
        }
        catch (Exception $e) {  }
        //################### Intercom End #######################################

         //################### Hubspot Start #####################################
            
         Hubspot ::tracking('pe25199511_os_dashboard_login',
         ["ec_os_dli_storecode"=>STORECODE,
         "ec_os_dli_user_id" =>WHMCS_USER_ID 
         ]);

         //################### Hubspot End #####################################
		
		$this->data['text_login'] = $this->language->get('text_login');
		$this->data['text_forgotten'] = $this->language->get('text_forgotten');
		
		$this->data['entry_username'] = $this->language->get('entry_username');
    	$this->data['entry_password'] = $this->language->get('entry_password');
        $this->data['direction'] = $this->language->get('direction');
        $this->data['lang'] = $this->language->get('code');

    	$this->data['button_login'] = $this->language->get('button_login');
		
		if ((isset($this->session->data['token']) && !isset($this->request->get['token'])) || ((isset($this->request->get['token']) && (isset($this->session->data['token']) && ($this->request->get['token'] != $this->session->data['token']))))) {
			//$this->error['warning'] = $this->language->get('error_token');
		}
		
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
				
    	$this->data['action'] = $this->url->link('common/login', '', 'SSL');

		if (isset($this->request->post['username'])) {
			$this->data['username'] = $this->request->post['username'];
		} else {
			$this->data['username'] = '';
		}
		
		if (isset($this->request->post['password'])) {
			$this->data['password'] = $this->request->post['password'];
		} else {
			$this->data['password'] = '';
		}

		if (isset($this->request->get['route'])) {
			$route = $this->request->get['route'];
			
			unset($this->request->get['route']);
			
			if (isset($this->request->get['token'])) {
				unset($this->request->get['token']);
			}
			
			$url = '';
						
			if ($this->request->get) {
				$url .= http_build_query($this->request->get);
			}
			
			$this->data['redirect'] = $this->url->link($route, $url, 'SSL');
		} else {
            $this->data['redirect'] = '';
        }

        if (isset($this->request->get['redirect_route'])){
			$this->data['redirect_route'] = $this->request->get['redirect_route'];	
		} 
		
		if ($this->config->get('config_password')) {
			$this->data['forgotten'] = $this->url->link('common/forgotten', '', 'SSL');
		} else {
			$this->data['forgotten'] = '';
		}

        $logoPath = 'LogoEC.png';

        if (PARTNER_CODE != '') {
            $logoPath = 'partners/' . PARTNER_CODE . '/logo-login.png';
        }

		$this->data['logoPath'] = $logoPath;
        $this->data['PARTNER_CODE'] = PARTNER_CODE;

		$this->template = 'common/login.expand';
		$this->base = 'common/base';

		$this->response->setOutput($this->render_ecwig());
  	}

	protected function validate()
    {
		if (
		    isset($this->request->post['username']) &&
            isset($this->request->post['password']) &&
            !$this->user->login($this->request->post['username'], $this->request->post['password'])
        ) {
			$this->error['warning'] = $this->language->get('error_login');
		} elseif (isset($this->request->get['logintoken']) && !$this->user->initLogin($this->request->get['logintoken'])) {
            $this->error['warning'] = $this->language->get('error_login');
        } elseif (isset($this->request->get['remote_token']) && !$this->user->initRemoteLogin($this->request->get['remote_token'])) {
            $this->error['warning'] = $this->language->get('error_login');
        } elseif (isset($this->request->get['webview_token']) && !$this->user->webViewLogin($this->request->get['webview_token'], true)) {
            $this->error['warning'] = $this->language->get('error_login');
        }

		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
