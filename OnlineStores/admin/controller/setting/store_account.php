<?php
class ControllerSettingStoreAccount extends Controller {
    private $error = array();
    
    public function index() {
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) return $this->indexV2();
        
        $this->language->load('setting/setting'); 

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['direction'] = $this->language->get('direction');
        
        $this->load->model('setting/setting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $result_json = array(
                'success' => '0',
                'errors' => array(),
                'success_msg' => ''
            );

            if ($this->validate())
            {
                $this->request->post['config_customer_group_id'] = $this->request->post['config_customer_group_display'][0];
                $this->model_setting_setting->insertUpdateSetting('config', $this->request->post);

                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            } else {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
            }

            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->data['breadcrumbs'] = array(
            array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')),
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/store_account'))
        );

        $this->data['action'] = $this->url->link('setting/store_account');
        
        $this->data['cancel'] = $this->url->link('setting/store_account');

        // Loads
        $this->load->model('sale/customer_group');
        $this->load->model('catalog/information');

        // Datas
        $this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();
        $this->data['informations'] = $this->model_catalog_information->getInformations();

        $this->value_from_post_or_config([
            'config_customer_group_id',
            'config_customer_group_display',
            'config_customer_price',
            'config_account_id',
            'config_externalorder',
            'config_customer_online'
        ]);

        $this->template = 'setting/store_account.expand';
        $this->base = "common/base";
                
        $this->response->setOutput($this->render_ecwig());
    }

    private function value_from_post_or_config($array)
    {
        foreach ($array as $elem)
        {
            $this->data[$elem] = $this->request->post[$elem] ?: $this->config->get($elem);
        }
    }

    
    private function validate()
    {
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) return $this->validateV2();
        
        if (!$this->user->hasPermission('modify', 'setting/store_account')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ( empty($this->request->post['config_customer_group_display']) )
        {
            $this->error['config_customer_group_display'] = $this->language->get('error_customer_group_display');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }

    private $signupModel;

    protected $withStoreCodeUrl = false;

    public function __construct($registry)
    {
        parent::__construct($registry);
        
        $this->signupModel = $this->load->model('module/signup', ['return' => true]);
        
        if (strpos($_SERVER['SERVER_NAME'],strtolower(STORECODE)) !== false && strpos($_SERVER['SERVER_NAME'], 'expandcart') !== false) {
            $this->withStoreCodeUrl=true;
        }
    }


    public function indexV2()
    {
        $this->language->load('setting/setting');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['direction'] = $this->language->get('direction');

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $result_json = array(
                'success' => '0',
                'errors' => array(),
                'success_msg' => ''
            );

            if ($this->validate()) {
                $config = $this->config->get('config_customer_fields');

                if (!$this->identity->allowTelephoneType()) {
                    $this->request->post['config_customer_fields']['identity']['type'] = 'email';
                }
                
                $requestIdentity = $this->request->post['config_customer_fields']['identity']['type'];
                
                // check if identity type change if true should kill all open sessions for all customers
                $identityTypeChanged = $requestIdentity !== $this->identity->getIdentityType();
                
                if ($identityTypeChanged) {
                    $this->applyForceLogOutForAllCustomers();
                    // $this->signupModel->setLoginByPhoneStatus((int)$this->request->post['login_register_phonenumber_enabled']);
                }
                
                // check if any registration fields changed from optional to required
                foreach ($config['registration'] as $key => $value) {
                    if (in_array($key, ['gender', 'dob', 'company']) && (int)$value !== 1 && array_key_exists($key, $this->request->post['config_customer_fields']['registration']) && 
                        (int)$this->request->post['config_customer_fields']['registration'][$key] === 1
                    ) {
                        $this->applyForceLogOutForCustomersMissingColumn($key);
                    }
                }
                
                $setting = array_merge($config['identity'] ?? [], $this->request->post['config_customer_fields']['identity']);
                
                $this->request->post['config_customer_fields']['identity'] = $setting;

                $this->model_setting_setting->insertUpdateSetting('config', array_only_keys($this->request->post, [
                    'config_customer_group_id',
                    'config_customer_group_display',
                    'config_customer_price',
                    'config_account_id',
                    // 'config_externalorder',
                    'config_customer_online',
                    'config_customer_fields'
                ]));

                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['data'] = $this->request->post['config_customer_fields'];
            } else {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
            }

            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->data['breadcrumbs'] = array(
            array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')),
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/store_account'))
        );

        $this->data['action'] = $this->url->link('setting/store_account');

        $this->data['cancel'] = $this->url->link('setting/store_account');

        // Loads
        $this->load->model('sale/customer_group');
        $this->load->model('catalog/information');

        $this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();
        $this->data['informations'] = $this->model_catalog_information->getInformations();

        $this->fillFormData([
            'config_customer_group_id',
            'config_customer_group_display',
            'config_customer_price',
            'config_account_id',
            'config_externalorder',
            'config_customer_online',
            'config_customer_fields'
        ]);
        // $this->data['login_register_phonenumber_enabled'] = (int)$this->identity->isLoginByPhone();
        $this->data['sign_in_confirmation_text'] =  $this->language->get('sign_in_confirmation_text');
        $this->data['sign_in_confirmation_title'] =  $this->language->get('sign_in_confirmation_title');
        
        if (isset( $this->session->data['charge'] )) {
            $this->data['flash_message'] = [
                'status' => $this->session->data['charge']['status'],
                'message' => $this->session->data['charge']['status'] == 'OK' 
                    ? $this->language->get('text_sms_credit_has_been_successfully_charged') 
                    : $this->language->get('text_sms_credit_has_not_been_charged')
            ];
            unset($this->session->data['charge']);
        }
        
        $this->template = 'setting/store_account_v2.expand';
        
        $this->base = "common/base";

        $this->response->setOutput($this->render_ecwig());
    }

    public function getCountCustomersMissingColumn()
    {
        $column = $this->request->post['column'];
        
        if ($column) {
            $query = $this->db->query(sprintf("SELECT COUNT(customer_id) as customers_count FROM " . DB_PREFIX . "customer WHERE %s IS NULL OR %s = ''", $column, $column));
        }
        
        $this->response->setOutput(json_encode(['customers_count' => (isset($query) && $query->num_rows) ? (int)$query->row['customers_count'] : 0]));
    }
        
    public function getCountIdentityCustomers()
    {
        $query = $this->db->query("SELECT COUNT(customer_id) as customers_count FROM " . DB_PREFIX . "customer WHERE `expand_id` IS NOT NULL AND `expand_id` != '0';");
        $this->response->setOutput(json_encode(['customers_count' => (isset($query) && $query->num_rows) ? (int)$query->row['customers_count'] : 0]));
    }
    
    // public function requestToAllowSignInByTelephone()
    // {
    //     $this->language->load('setting/setting'); 
        
    //     if (!$this->user->hasPermission('modify', 'setting/store_account')) {
    //         $result_json['success'] = '0';
    //         $result_json['errors'] = ['error' => $this->language->get('error_permission')];
    //         $this->response->setOutput(json_encode($result_json));
    //         return;
    //     }
        
    //     $this->identity->requestToAllowSignInByTelephone();
        
    //     $config = $this->config->get('config_customer_fields');
        
    //     $this->response->setOutput(json_encode([
    //         'status' => $config['identity']['allow_telephone'],
    //     ]));
    // }
    
    
    // public function dismissSignInAlert()
    // {
    //     $status = $this->request->post['status'];
    //     if (in_array($status, ['approved', 'rejected', 'suspend', 'active'])) {
    //         $config = $this->config->get('config_customer_fields');
    //         $config['identity']['allow_telephone'] = "{$status}_and_notified";
    //         $settingModel = $this->load->model('setting/setting', ['return' => true]);
    //         $settingModel->insertUpdateSetting('config', ['config_customer_fields' => $config]);
    //     }
        
    //     $this->response->setOutput(json_encode(['status' => 1,]));
    // }

    private function fillFormData($array)
    {
        foreach ($array as $elem) $this->data[$elem] = $this->request->post[$elem] ?: $this->config->get($elem);
    }


    private function validateV2()
    {
        if (!$this->user->hasPermission('modify', 'setting/store_account')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ($this->request->post['config_customer_fields']['registration']['groups'] == 0 && empty($this->request->post['config_customer_group_display'])) {
            $this->error['config_customer_group_display'] = $this->language->get('error_customer_group_display');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }

    /**
     * Set all customers status to inactive 
     * and set force_logout flag to true
     *
     * @return void
     */
    private function applyForceLogOutForAllCustomers(): void
    {
        // force log out for all customers
        $this->db->query(sprintf('UPDATE `%s` SET `force_logout` = 1;', DB_PREFIX . 'customer'));
        // inactive all customers they have expand id
        $this->db->query(sprintf(
            "UPDATE `%s` SET `status` = 0 WHERE `expand_id` IS NOT NULL AND `expand_id` != '0';",
            DB_PREFIX . 'customer'
        ));
    }
    
    /**
     * apply force log out for all customers
     * missing required field
     *
     * @return void
     */
    private function applyForceLogOutForCustomersMissingColumn(string $column=""): void
    {
        $column && $this->db->query(sprintf("UPDATE `%s` SET `force_logout` = 1 WHERE %s IS NULL OR %s = ''", DB_PREFIX . 'customer', $column, $column));
    }
    
    /**
     * SMS packages map [id => No. of messages]
     */
    const WHMCS_SMS_PRODUCT_IDS = [
        123 => 1000,
        124 => 2500,
        125 => 5000
    ];
    
    public function getSmsProducts()
    {
        if (!defined('LOGIN_MODE') || LOGIN_MODE !== 'identity' || !$this->identity->isStoreOnWhiteList()) {
            $this->response->setOutput(json_encode(['status' => 0]));
            return;
        }
        
        $this->load->model('account/invoice');
        // $this->load->model('marketplace/appservice');
        
        $products = array_reduce(array_keys(self::WHMCS_SMS_PRODUCT_IDS), function($products, $pid) {
            
            $whmcsResult = $this->model_account_invoice->getProducts($pid);
            
            if (!$whmcsResult || (is_object($whmcsResult) && property_exists($whmcsResult, 'error'))) {
                return $products;
            }
            
            $product = $whmcsResult->products->product[0];
            
            $price = $product->pricing->USD->prefix . $product->pricing->USD->monthly;
            
            $products[] = [
                'id' => $product->pid,
                'number_of_msgs' => self::WHMCS_SMS_PRODUCT_IDS[$pid],
                'price' => $price,
            ];
            
            return $products;
            
        }, []);
        
        $this->response->setOutput(json_encode(['status' => (int)!!count($products), 'data' => $products]));
    }
    
    public function stripeCharge()
    {
        if (!defined('LOGIN_MODE') || LOGIN_MODE !== 'identity' || !$this->identity->isStoreOnWhiteList()) {
            $this->response->setOutput(json_encode(['status' => 0]));
            return;
        }
     
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

        $this->initializer(['account/transaction', 'marketplace/appservice', 'account/stripe', 'account/account', 'account/invoice']);

        $account = $this->account->selectByStoreCode(STORECODE);

        $customerId = null;
        
        if (isset($account['stripe_customer_id']) && substr($account['stripe_customer_id'], 0, 3) == "cus") {
            $customerId = $account['stripe_customer_id'];
        }

        $geoipCountryCode = null;
        
        try {
            $GeoIP2 = new ModulesGarden\Geolocation\Submodules\GeoIP2();
            $geoipCountryCode = $GeoIP2->getCountry();
        } catch (\Exception $ex) {
        }

        // list($month, $year) = explode('/' ,$_POST['card_date']);
        
        // $_POST['month'] = $month;
        // $_POST['year'] = $year;
        $_POST['cvc'] = $_POST['card_cvc'];
        
        $card = [
            'number' => str_replace(' ', '', $_POST['card_number']),
            'exp_month' => $_POST['month'],
            'exp_year' => '20' . $_POST['year'],
            'cvc' => $_POST['cvc'],
            'address_country' => $geoipCountryCode
        ];
        
        if (!$this->stripe->validateCard($card)) {
            $response = [
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => $this->stripe->getErrors()
            ];

            $this->response->json($response);
            return;
        }
        
        if (!$token = $this->stripe->createToken($card)) {
            $response = [
                'status' => 'ERR',
                'error' => 'TOKEN_ERROR',
                'errors' => $this->stripe->getErrors()
            ];

            $this->response->json($response);
            return;
        }
        
        $sourceData = [
            'type' => 'card',
            'token' => $token->id,
            'usage' => 'reusable'
        ];
        
        if (!$source = $this->stripe->createSource($sourceData)) {
            $response = [
                'status' => 'ERR',
                'error' => 'SOURCE_ERROR',
                'errors' => $this->stripe->getErrors()
            ];

            $this->response->json($response);
            return;
        }
        
        if (!$customerId) {
            if ($customer = $this->stripe->createCustomer(WHMCS_USER_ID, $this->user->getFullName(), EMAIL, $token->card->country)) {
                $customerId = $customer->id;
                $this->invoice->updateClient(['gatewayid' => $customerId]);
            } else {
                $response = [
                    'status' => 'ERR',
                    'error' => 'CUSTOMER_ERROR',
                    'errors' => $this->stripe->getErrors()
                ];

                $this->response->json($response);
                return;
            }
        } else {
            $customer = $this->stripe->retrieveCustomer($customerId);
            $customer->source = $source->id;
            if (method_exists(get_class($customer), "save")) {
                try {
                    $customer = $customer->save();
                } catch (\Exception $e) {
                    $response = [
                        'status' => 'ERR',
                        'error' => 'CUSTOMER_ERROR',
                        'errors' => [$e->getMessage()]
                    ];
                    $this->response->json($response);
                    return;
                }
            } else {
                if ($customer = $this->stripe->createCustomer(WHMCS_USER_ID, $this->user->getFullName(), EMAIL, $token->card->country)) {
                    $customerId = $customer->id;
                    $this->invoice->updateClient(['gatewayid' => $customerId]);
                } else {
                    $response = [
                        'status' => 'ERR',
                        'error' => 'CUSTOMER_ERROR',
                        'errors' => $this->stripe->getErrors()
                    ];
                    $this->response->json($response);
                    return;
                }
            }
        }
        
        $currency = 'USD';
        
        $paymentMethod = "stripe";
        
        $productId = $_POST['product_id'];
            
        $whmcsResult = $this->model_account_invoice->getProducts($productId);
        
        if (!$whmcsResult || (is_object($whmcsResult) && property_exists($whmcsResult, 'error'))) {
            $response = [
                'status' => 'ERR',
                'error' => 'UNDEFINED_PRODUCT_ID',
            ];
            $this->response->json($response);
            return;
        }
        
        $product = $whmcsResult->products->product[0];
        
        $amount = $product->pricing->USD->monthly;
        
        $order = $this->createOrder($paymentMethod, $productId);
        
        if (!$order) {
            $this->response->json($this->session->data['charge']);
            unset($this->session->data['charge']);
            return;
        }
        
        $orderId = $order->orderid;
        $serviceId = $order->serviceid;
        $invoiceId = $order->invoiceid;
        
        // stripe payment
        if ($source->card->three_d_secure != 'required') {
            $charge = $this->stripe->charge($customerId, $amount * 100, $currency, $this->user->getFullName(), STORECODE, '', (string)$invoiceId);

            if (!$charge) {
                $response = [
                    'status' => 'ERR',
                    'error' => 'CHARGE_ERROR',
                    'errors' => $this->stripe->getErrors()
                ];
                $this->response->json($response);
                return;
            }
            
        } elseif ($source->card->three_d_secure == 'required') {
            
            $return_url = $this->url->link(
                'setting/store_account/stripeChargeThreeDSecure',
                'customer_id=' . $customerId 
                . '&currency=' . $currency 
                . '&product_id=' . $productId 
                . '&order_id=' . $orderId 
                . '&service_id=' . $serviceId 
                . '&invoice_id=' . $invoiceId,
                true,
                0,
                $this->withStoreCodeUrl
            )->format();

            // $return_url = $this->url->link('', '', true, 0, $this->withStoreCodeUrl) . 'setting/store_account/stripeChargeThreeDSecure?customer_id=' . $customerId 
            // . '&currency=' . $currency . '&product_id=' . $productId . '&order_id=' . $orderId . '&service_id=' . $serviceId . '&invoice_id=' . $invoiceId  ;
            
            $sourceData = [
                'type' => 'three_d_secure',
                'currency' => $currency,
                'amount' => $amount * 100,
                'three_d_secure' => [
                    'card' => $source->id,
                    'customer' => $customerId
                ],
                'redirect' => [
                    'return_url' => $return_url
                ],
            ];

            $three_D_secure_source = $this->stripe->createSource($sourceData);

            if ($three_D_secure_source) {
                $response = [
                    'status' => 'redirect',
                    'location' => $three_D_secure_source->redirect->url,
                ];
                $this->response->json($response);
                return;
            } else {
                $response = [
                    'status' => 'ERR',
                    'error' => 'CHARGE_ERROR',
                    'errors' => $this->stripe->getErrors()
                ];
                $this->response->json($response);
                return;
            }
        }

        $balanceTransaction = $this->stripe->retrieveTransaction($charge->balance_transaction);

        $paymentOptions = [
            'service_id' => $serviceId,
            'invoice_id' => $invoiceId,
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => $currency,
            'product_id' => $productId,
            'charge_id' => $charge->balance_transaction,
            'fees' => (float)($balanceTransaction->fee / 100),
            'net' => (float)($balanceTransaction->net / 100),
            'customer_id' => $customerId,
        ];

        $authToken = $this->stripeChargeSuccessCallback($paymentOptions);

        // return response to user

        $response = [
            'status' => 'OK',
            'charge' => $paymentMethod,
            'location' => $this->url->link(
                'setting/store_account/invoiceStore',
                sprintf('auth_token=%s', urlencode($authToken)),
                true,
                0,
                $this->withStoreCodeUrl
            )->format(),
        ];

        $this->response->json($response);
    }
    
    public function stripeChargeThreeDSecure()
    {
        $productId = $_GET['product_id'];
        
        if (!defined('LOGIN_MODE') || LOGIN_MODE !== 'identity' || !$this->identity->isStoreOnWhiteList()) {
            return $this->finishStripeCharge(false);
        }
        
        if (!isset($_GET['source']) || !isset($_GET['customer_id'])) {
            return $this->finishStripeCharge(false, $productId);
        }
  
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
            
        $this->initializer(['account/stripe']);

        $sourceId = $_GET['source'];
            
        $source = $this->stripe->retrieveSource($sourceId, ['client_secret' => $_GET['client_secret']]);

        //check 3D secure verified
        if ($source->status != 'chargeable') {
            return $this->finishStripeCharge(false, $productId);
        }

        $customerId = $_GET['customer_id'];
        $invoiceId = $_GET['invoice_id'];
        $serviceId = $_GET['service_id'];
        $planType = $_GET['plan_type'];
        $planId = $_GET['plan_id'];
        $orderId = $_GET['order_id'];
        $due_date = $_GET['due_date'];
        $discount_code = $_GET['discount_code'];
        $recurring_amount = $_GET['recurring_amount'];
        
        $amount = $source->amount;
        $currency = $source->currency;

        if (!$charge = $this->stripe->sourceCharge(
            $sourceId,
            $customerId,
            $amount,
            $currency,
            STORECODE,
            (string)$invoiceId
        )) {
            return $this->finishStripeCharge(false, $productId);
        }

        $balanceTransaction = $this->stripe->retrieveTransaction($charge->balance_transaction);

        $paymentOptions = [
            'service_id' => $serviceId,
            'invoice_id' => $invoiceId,
            'order_id' => $orderId,
            'amount' => $amount / 100,
            'currency' => $currency,
            'product_id' => $productId,
            'charge_id' => $charge->balance_transaction,
            'fees' => (float)($balanceTransaction->fee / 100),
            'net' => (float)($balanceTransaction->net / 100),
            'customer_id' => $customerId,
        ];
        
        $authToken = $this->stripeChargeSuccessCallback($paymentOptions);

        $this->response->redirect(
            $this->url->link('setting/store_account/invoiceStore', 
                sprintf('auth_token=%s&3dsecure=true', urlencode($authToken)), 
                true, 
                0, 
                $this->withStoreCodeUrl
            )
        );
    }
    
    private function createOrder($paymentMethod, $pid, $planType = null, $serviceId = null)
    {

        $this->initializer(['account/transaction', 'account/invoice']);
        
        $order = $this->invoice->createOrder(
            $pid,
            $paymentMethod,
            $planType,
            $serviceId
        );

        $debugId = function () {
            $data = random_bytes(16);
            assert(strlen($data) == 16);

            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

            $data = (array)str_split(bin2hex($data), 4);
            
            $id = vsprintf('%s%s-%s-%s-%s-%s%s%s-%s', array_merge($data, [time()]));

            return $id;
        };

        // check if invoice created on WHMCS"
        if (!$order || $order->error) {
            
            $debugId = $debugId();
            
            $this->transaction->insert([
                'store_code' => STORECODE,
                'debug_id' => $debugId
            ]);

            $this->session->data['charge'] = [
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['unable to create order'],
                'debug_id' => $debugId
            ];
            
            return false;
        }
        
        return $order;
    }
    
    private function stripeChargeSuccessCallback($options)
    {

        $this->initializer(['account/account', 'account/invoice', 'account/transaction']);

        $paymentMethod = 'stripe';
        
        $orderId = $options['order_id'];
        $invoiceId = $options['invoice_id'];
        $serviceId = $options['service_id'];
        $productId = $options['product_id'];
        $amount = $options['amount'];
        $recurringAmount = $options['recurring_amount'];
        $currency = $options['currency'];
        $planId = $options['plan_id'];
        $appServiceId = $options['app_service_id'];
        $planType = $options['plan_type'];
        $dueDate = $options['due_date'];
        $chargeId = $options['charge_id'];
        $chargeFees = $options['fees'];
        $customerId = $options['customer_id'];
        $discountCode = $options['discount_code'];

        // create a payment token

        $encrypt = function ($plaintext, $key = null) {
            return  base64_encode($plaintext);
            $key = $key ?: 'EC_' . STORECODE;
            $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
            $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
            $ciphertext = bin2hex($iv . $hmac . $ciphertext_raw);
            return $ciphertext;
        };

        $authToken = $encrypt(json_encode([
            'product_id' => $productId,
            'transaction_id' => $chargeId,
            'timestamp' => time(),
            'fees' => $chargeFees
        ]), 'EC_' . STORECODE);

        // update the related tables

        $accountId = $this->rememberStripeAccount($customerId, $serviceId, $paymentMethod, $planType);

        $this->invoice->acceptOrder($orderId);

        $this->transaction->insert([
            'store_code' => STORECODE,
            'amount' => $amount,
            'currency' => strtoupper($currency),
            'status' => 1,
            'transaction_id' => $chargeId,
            'payment_method' => $paymentMethod,
            'plan_id' => $planId ? $planId : $appServiceId,
            'plan_type' => $planType,
            'auth_token' => $authToken,
            'account_id' => $accountId,
            'order_id' => $orderId,
            'invoice_id' => $invoiceId,
        ]);
        
        $this->updateSmsBalance($productId);

        return $authToken;
    }
    
    private function rememberStripeAccount($customerId = null, $serviceId = null, $paymentMethod = null, $planType = null)
    {
        $this->initializer(['account/account']);
    
        $account = $this->account->selectByStoreCode(STORECODE);

        if (!$account) {
            $accountId = $this->account->insert([
                'storecode' => STORECODE,
                'stripe_customer_id' => $customerId,
                'service_id' => $serviceId,
                'payment_method' => $paymentMethod,
                'plan_type' => $planType,
                'stripe_subscription_id' => $customerId
            ]);
        } else {
            $accountId = $account['id'];
            
            if ($account['payment_method'] == 'paypal') {
                $this->initializer(['account/paypal']);
                $this->paypal->cancelSubscription($account['paypal_subscription_id']);
            }
            
            $this->account->update([
                'stripe_customer_id' => $customerId,
                'payment_method' => $paymentMethod,
                'plan_type' => $planType,
                'stripe_subscription_id' => $customerId
            ]);
        }
        
        return $accountId;
    }
    
    private function updateSmsBalance($pid = null)
    {
        if (!array_key_exists($pid, self::WHMCS_SMS_PRODUCT_IDS)) {
            return false;    
        }
        
        // $this->load->model('account/invoice');
        $this->load->model('setting/setting');
        
        // $whmcsResult = $this->model_account_invoice->getProducts($pid);
        
        // if (!$whmcsResult || (is_object($whmcsResult) && property_exists($whmcsResult, 'error'))) {
        //     return false;
        // }
            
        $config = $this->config->get('config_customer_fields');

        $currentSmsBalance = $config['identity']['sms_balance'] ?: 0;
       
        $setting = array_merge($config['identity'] ?? [], [
            'allow_telephone' => 1,
            'sms_balance' => ($currentSmsBalance + self::WHMCS_SMS_PRODUCT_IDS[$pid])
        ]);
        
        $config['identity'] = $setting;

        $this->model_setting_setting->insertUpdateSetting('config', ['config_customer_fields' => $config]);
    }
    
    public function invoiceStore()
    {
        $this->initializer([
            'account/transaction',
            'account/invoice',
            'account/account'
        ]);
        
        if (!isset($_GET['auth_token']) || strlen($_GET['auth_token']) < 5) {
            return $this->finishStripeCharge(false);
        }

        $decrypt = function ($ciphertext, $key = null) {
            return  base64_decode($ciphertext);
        };

        $token = json_decode($decrypt($_GET['auth_token']), true);

        if (!isset($token['transaction_id']) || !isset($token['timestamp'])) {
            return $this->finishStripeCharge(false, $token['product_id']);
        }

        $transaction = $this->transaction->selectByTransactionId($token['transaction_id']);

        if (!$transaction) {
            return $this->finishStripeCharge(false, $token['product_id']);
        }

        if ($transaction['transaction_status'] == 0) {
            $this->transaction->update(['transaction_status' => '1']);
        }

        return $this->finishStripeCharge(true, $token['product_id']);
    }
    
    public function finishStripeCharge($charge = true, $productId = null)
    {
        $this->session->data['charge'] = [
            'status' => ($charge ? 'OK' : 'ERR'),
        ];
        
        $this->response->redirect($this->url->link('setting/store_account', '', true, 0, $this->withStoreCodeUrl));
    }
}
