<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use ExpandCart\Foundation\Providers\Extension;
use ExpandCart\Foundation\Support\Facades\Filesystem;


class Identity
{

    private const CONFIG_KEY = 'identity_client_credential';
    private const CONFIG_WHITELIST_KEY = 'login_checkout_whitelisted';

    /**
     * The white list of stores that's enabled new login criteria
     */
    const WHITE_LIST_STORES = [
        'vphamc3554',
        'mlvetk9394',
        'iofawm1705',
        'xedsgz6598',
        'nnbglg7075',
        'dpfrus2600',
        'jzeisf4335',
        'kdguns2819',
        'acphjl9995',
        'svvptm4587',
        'ewzqxq3469',
        'kwfqxr9295',
        'admdqq6039',
        'akrntt5754',
        'unlmpp0991',
        'asrrjc8765',
        'vhlbjn1550',
        'psmdtk0351'
    ];

    const SUBSCRIPTION_PLANS_MAPPER = [
        6 => 'ultimate',
        53 => 'professional',
        3 => 'free',
        8 => 'enterprise',
    ];

    protected $registry;
    protected $config;
    protected $load;
    protected $request;
    protected $customer;
    protected $session;
    protected $db;
    protected $ecusersdb;

    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->config = $registry->get('config');
        $this->load = $registry->get('load');
        $this->request = $registry->get('request');
        $this->customer = $registry->get('customer');
        $this->session = $registry->get('session');
        $this->db = $registry->get('db');
        $this->ecusersdb = $registry->get('ecusersdb');
    }

    public function isStoreOnWhiteList(): bool
    {
        return \defined('LOGIN_MODE') && \trim(\strtolower(LOGIN_MODE)) === 'identity' && $this->storeInWhiteList();
    }

    private function storeInWhiteList(): bool
    {
        // for development process
        if (\in_array(\trim(\strtolower(STORECODE)), ['qaz123', 'jzeisf4335', 'axqddn2038'])) return true;

        // check store in white list
        //if (!in_array(trim(strtolower(STORECODE)), self::WHITE_LIST_STORES))  return false;

        // check setting key exists
        $whiteListKeyExists = $this->config->get(self::CONFIG_WHITELIST_KEY);
        if (!\is_null($whiteListKeyExists)) return (int)$whiteListKeyExists === 1;

        // check store plan id
        if (!\defined('PRODUCTID') || !\in_array((int)PRODUCTID, array_keys(self::SUBSCRIPTION_PLANS_MAPPER))) return false;

        // check current template 
        // $currentTemplate = \trim(\strtolower($this->config->get('config_template') ?: ''));
        // if (!\strlen($currentTemplate) || !\in_array($currentTemplate, ['wonder', 'souq', 'welldone', 'manymore'])) return false;

        // check if custom registration app installed 
        // and merchant enabled setting login & register by mobile
        if ($this->merchantEnableSignUpByPhone()) return false;

        // check if merchant have mobile app 
        if ($this->merchantHasMobileApp()) return false;

        $settingModel = $this->load->model('setting/setting', ['return' => true]);

        // add white list flag
        $settingModel->insertUpdateSetting('config', [self::CONFIG_WHITELIST_KEY => 1]);
        $this->config->set(self::CONFIG_WHITELIST_KEY, 1);

        if (!$this->isEnterprisePlan()) {
            // enable new checkout experience
            $quickCheckoutConf = $this->config->get('quickcheckout') ?: [];
            $quickCheckoutConf['try_new_checkout'] = 1;
            $settingModel = $this->load->model('setting/setting', ['return' => true]);
            $settingModel->insertUpdateSetting('quickcheckout', ['quickcheckout' => $quickCheckoutConf]);
            $this->config->set('quickcheckout', $quickCheckoutConf);
        }

        return true;
    }

    private function isEnterprisePlan(): bool
    {
        return defined('PRODUCTID') && isset(self::SUBSCRIPTION_PLANS_MAPPER[PRODUCTID]) && self::SUBSCRIPTION_PLANS_MAPPER[PRODUCTID] === "enterprise"; 
    }

    private function merchantEnableSignUpByPhone(): bool
    {
        return Extension::isInstalled('signup') &&
            $this->db->query("SELECT * FROM `" . DB_PREFIX . "signupkw` WHERE `enablemod`=1 AND `login_register_phonenumber_enabled`=1")->num_rows > 0;
    }

    private function merchantHasMobileApp(): bool
    {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "ectemplate` WHERE `CodeName`='mobile-app'")->num_rows > 0;
    }

    #################################################### Customer apis ####################################################

    public function checkCustomer($identity)
    {
        return $this->makeRequest('POST', '/api/checkCustomer', [], compact('identity'));
    }

    public function sendVerificationCode($data)
    {
        $data = array_merge($data, ['ip' =>  $this->request->ip()]);

        if ($this->config->has('config_logo') && Filesystem::isExists('image/' . $this->config->get('config_logo'))) {
            $data['store_logo'] = $this->load->model('tool/image', ['return' => true])->resize($this->config->get('config_logo'), 97, 97);
            $data['store_logo'] = str_replace(' ', '%20', $data['store_logo']);
        }

        return $this->makeRequest('POST', '/api/sendVerificationCode', [], $data, [], true, function ($request, $response) {
            if ($response['success']) {
                // log client info
                // $this->logVerificationCodeRequest($request, $response);

                if ($request['form_params']['identity_type'] === "telephone") {
                    // increment the sms sent messages
                    $this->updateSmsBalance($request, $response);
                    // log sms to ectools app
                    $this->logSmsInfoToEcTools($request, $response);
                }
            }

            return $response;
        });
    }

    /**
     * verifyCode
     *
     * @param string $code the verification code
     * @return response
     */
    public function verifyCode($data)
    {
        return $this->makeRequest('POST', '/api/verifyCode', [], $data);
    }

    public function getCustomer(int $id)
    {
        $result =  $this->makeRequest('GET', '/api/getCustomer', compact('id'));

        if ($result['success']) $result['data']['identity_type'] = $this->isLoginByPhone() ? 'telephone' : 'email';

        return $result;
    }

    public function validateCustomerProfile(array $attributes)
    {
        $attributes = array_merge($attributes, ['ip' =>  $this->request->ip()]);
        return $this->makeRequest('POST', '/api/validateCustomerProfile', [], $attributes);
    }

    public function registerCustomer(array $attributes)
    {
        return $this->makeRequest('POST', '/api/registerCustomer', [], $attributes);
    }

    public function updateCustomer(array $attributes)
    {
        return $this->makeRequest('PUT', '/api/updateCustomer', [], $attributes);
    }

    public function addCustomer(array $attributes)
    {
        return $this->makeRequest('POST', '/api/addCustomer', [], $attributes);
    }

    #################################################### Address apis ####################################################

    public function addAddress(array $data)
    {
        if ($identity_id = $this->customer->getExpandId()) {
            $data = array_merge($data, ['customer_id' => $identity_id,]);
            $result = $this->makeRequest('POST', '/api/addAddress', [], $data);
            if ($result['success']) {
                $data = array_merge($data, ['address_expand_id' => $result['data']['id']]);
            }
        }

        $addressModel = $this->load->model('account/address', ['return' => true]);

        $address_id = $addressModel->addAddress($data);

        $data['address_id'] = $address_id;

        $this->storeAddressToSession($data);

        return $address_id;
    }

    public function updateAddress(array $data)
    {
        $addressModel = $this->load->model('account/address', ['return' => true]);

        $address = $addressModel->getAddress($data['address_id']);

        if ($this->customer->getExpandId()) {

            $data = array_merge($data, [
                'id' => $address['address_expand_id'] ?? null,
                'customer_id' => $this->customer->getExpandId(), // expand customer ID
                'default' => !empty($data['default']),
            ]);

            $result = $this->makeRequest('PUT', '/api/updateAddress', [], $data);

            if ($result['success']) {
                $data['address_expand_id'] = $result['data']['id'];
            }
        }

        $addressModel->editAddress($data['address_id'], array_merge($address, $data));

        $this->storeAddressToSession($data);

        return $data['address_id'];
    }

    public function deleteAddress(array $data)
    {
        return $this->makeRequest('DELETE', '/api/deleteAddress', [], $data);
    }

    #################################################### Helpers ####################################################

    private function makeRequest($method, $requestUrl, $queryParams = [], $formParams = [], $headers = [], $isAuthorized = true, $next = null)
    {
        $response = "";

        $client = new Client([]);

        $fullUri = rtrim(IDENTITY_BASE_URI, '/') . $requestUrl;

        $isAuthorized && $this->resolveAuthorization($queryParams, $formParams, $headers);

        $request = [
            'form_params' => $formParams,
            'headers' => $headers,
            'query' => $queryParams,
            'debug' => false,
        ];

        try {
            $response = $client->request($method, $fullUri, $request);

            $response = $response->getBody()->getContents();
        } catch (RequestException | \Exception $e) {
            $msg = "";
            // Catch all 4XX errors 
            if ($e instanceof RequestException && $e->hasResponse())
                $msg = " the response given " . $e->getResponse()->getBody()->getContents();
            throw new Exception('Unable to complete request: ' . $method . '/ ' . $fullUri . $msg);
        }

        $response = $this->decodeResponse($response);

        if (is_callable($next)) return $next($request, $response);

        return $response;
    }

    private function getAuthParams()
    {
        $authParams = $this->config->get(self::CONFIG_KEY);

        // add $authParams['client_id'] != IDENTITY_CLIENT_ID to check if client id change then regenerate new access token
        if (!$authParams || time() >= $authParams['expires_in'] || $authParams['client_id'] != IDENTITY_CLIENT_ID) {
            $authParams = $this->makeRequest('POST', '/oauth/token', [], [
                'grant_type' => 'client_credentials',
                'client_id' => IDENTITY_CLIENT_ID,
                'client_secret' => IDENTITY_CLIENT_SECRET,
                'scope' => '*'
            ], [], false);

            $settingsModel = $this->load->model('setting/setting', ['return' => true]);

            $authParams = (array)$authParams;
            $authParams['client_id'] = IDENTITY_CLIENT_ID;
            $authParams['expires_in'] = (time() + $authParams['expires_in']);
            $settingsModel->insertUpdateSetting(self::CONFIG_KEY, [self::CONFIG_KEY => $authParams]);

            // set configuration on fly
            $this->config->set(self::CONFIG_KEY, $authParams);
        }

        return "Bearer " . $authParams['access_token'];
    }

    private function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        $headers['Authorization'] = $this->getAuthParams();
        $queryParams['store_code'] = $formParams['store_code'] = STORECODE;
        $queryParams['request_datetime'] = $formParams['request_datetime'] = date('Y-m-d h:i:s');
        $queryParams['store_name'] = $formParams['store_name'] = str_replace(":", "", $this->config->get('config_name'));
        $queryParams['identity_type'] = $formParams['identity_type'] = $this->isLoginByPhone() ? 'telephone' : 'email';
        $queryParams['locale'] = $formParams['locale'] = $this->config->get('config_language') ?: 'en';
    }

    private function decodeResponse($response)
    {
        return json_decode($response, true);
    }

    #################################################### Others ####################################################

    public function allowTelephoneType()
    {
        return $this->canSendSms();
    }

    /**
     * check if admin allow logged in by phone
     *
     * @return integer
     */
    public function isLoginByPhone(): int
    {
        // $tableExistQuery = $this->db->query('SHOW TABLES LIKE "signupkw"');

        // if (!$tableExistQuery->num_rows) return 0;

        // $phoneQuery = $this->db->query("SELECT `login_register_phonenumber_enabled` FROM " . DB_PREFIX . "signupkw LIMIT 1");

        // if (!$phoneQuery->num_rows || !(int)$phoneQuery->row['login_register_phonenumber_enabled']) return 0;

        // return 1;

        $config = $this->config->get('config_customer_fields');
        return (int)(isset($config['identity']) && $config['identity']['type'] == 'telephone');
    }

    /**
     * sync customer profile every day
     * or if profile change
     *
     * @return bool
     */
    public function syncCustomerProfile(): bool
    {
        $expand_id         = (int)$this->customer->getExpandId();
        $customer_id       = (int)$this->customer->getId();
        $updated_at        = strtotime($this->customer->getUpdatedAt());
        $expand_updated_at = strtotime($this->customer->getExpandUpdatedAt());
        $customerModel     = $this->load->model('account/customer', ['return' => true]);
        $addressModel      = $this->load->model('account/address', ['return' => true]);

        if (!$customer_id || !$expand_id) return false;

        // push local profile to identity
        if ($updated_at > $expand_updated_at) {

            $customer = $this->selectCustomer(['id' => $expand_id, 'email' => $this->customer->getEmail(), 'telephone' => $this->customer->getTelephone(),]);

            if (!$customer) return false;

            $result = $this->updateCustomer(array_merge($customer, ['id' => $expand_id, 'name' => $customer['firstname'],]));

            if (!$result['success']) return false;

            // get all addresses not synced to identity
            $addresses = array_filter(array_values($customer['addresses'] ?? []), function ($address) {
                return $address['address_expand_id'] == null;
            });

            // push addresses to identity server
            foreach ($addresses as $address) {
                $address = array_merge($address, ['customer_id' => $expand_id, 'default' => $customer['address_id'] == $address['address_id']]);
                $result  = $this->makeRequest('POST', '/api/addAddress', [], $address);

                if ($result['success']) {
                    $addressModel->setExpandId($address['address_id'], $result['data']['id']);
                }
            }

            $customerModel->updateExpandUpdatedAt($customer_id);

            return true;
        }

        // pull identity profile every day
        $date_now = date('Y-m-d');
        $expand_updated_at_date = date('Y-m-d', $expand_updated_at ? $expand_updated_at : strtotime('-1 year', strtotime(date('Y-m-d'))));

        if ($date_now == $expand_updated_at_date) return true; // profile is updated

        $result = $this->makeRequest('GET', '/api/getCustomer', ['id' => (int)$expand_id, 'last_update' => $this->customer->getExpandUpdatedAt()]);

        if (!$result['success'] || !$result['data']) return false;

        $result = $result['data'];

        $customerModel->updateCustomerBasicData(array_merge($result, ['expand_id' => (int)$expand_id, 'customer_id' => (int)$customer_id]));

        foreach ($result['addresses'] as $address) {
            $address = array_merge($address, ['address_expand_id' => $address['id']]);

            $addressExistsInStore = $addressModel->getAddressByExpandId((int)$address['id'], (int)$customer_id);

            if ($addressExistsInStore) {
                $addressModel->editAddress($addressExistsInStore['address_id'], $address);
            } else {
                $addressModel->addAddress($address);
            }
        }

        $customerModel->updateExpandUpdatedAt($customer_id);

        return true;
    }

    // Helpers

    public function loginWith($customer)
    {
        if ($this->isLoginByPhone())
            return ['telephone', $customer['telephone']];
        else
            return ['email', $customer['email']];
    }

    public function selectCustomer($identityCustomer)
    {
        list($key, $value) = $this->loginWith($identityCustomer);

        $value = $this->db->escape(utf8_strtolower($value));

        $query = $this->db->query(sprintf("SELECT * FROM %s WHERE LOWER(%s) = '%s'", DB_PREFIX . 'customer', $key, $value));

        $expandId = $identityCustomer['id'] ?: null;

        $customer = null;

        if ($query->num_rows == 1) {
            $tmpCustomer = $query->row;
            if ((int)$tmpCustomer['expand_id'] === (int)$expandId || (int)$tmpCustomer['expand_id'] === 0) {
                $customer = $tmpCustomer;
            }
        } elseif ($query->num_rows > 1) {
            foreach ($query->rows as $tmpCustomer) {
                if ((int)$tmpCustomer['expand_id'] === (int)$expandId) {
                    $customer = $tmpCustomer;
                    break;
                }
            }

            if (!$customer) {
                // $customersCount = (int)$query->num_rows;
                // $customersCountHasNullExpandId = 0;

                foreach ($query->rows as $tmpCustomer) {
                    if ((int)$tmpCustomer['expand_id'] === 0) {
                        // $customersCountHasNullExpandId++;
                        $customer = $tmpCustomer;
                        break;
                    }
                }

                // if ($customersCount === $customersCountHasNullExpandId) {
                //     $customer = reset($query->rows);
                // }
            }
        }

        if ($customer) {
            $addressModel = $this->load->model('account/address', ['return' => true]);

            $customer['addresses'] = $addressModel->getAddresses($customer['customer_id']);

            if (!empty($customer['cart']) && is_string($customer['cart'])) $customer['cart'] = unserialize($customer['cart']);
            if (!empty($customer['wishlist']) && is_string($customer['wishlist'])) $customer['wishlist'] = unserialize($customer['wishlist']);

            unset($customer['password'], $customer['salt'], $customer['security_code'], $customer['token']);
        }

        return $customer;
    }

    public function attemptLogin($identityCustomer)
    {
        if (!$this->customer->attemptLogin($this->loginWith($identityCustomer), $identityCustomer['id'])) return false;

        $this->checkIfIdentityTypeWillChange();

        unset($this->session->data['guest']);
        unset($this->session->data['guest_expand_id']);
        unset($this->session->data['guest_customer_id']);
        unset($this->session->data['guest_name']);
        unset($this->session->data['guest_email']);
        unset($this->session->data['guest_telephone']);
        unset($this->session->data['guest_dob']);
        unset($this->session->data['guest_gender']);

        // Default Shipping Address
        $addressModel = $this->load->model('account/address', ['return' => true]);

        $address = $addressModel->getAddress($this->customer->getAddressId());

        if ($address) {
            if ($this->config->get('config_tax_customer') == 'shipping') {
                $this->session->data['shipping_country_id'] = $address['country_id'];
                $this->session->data['shipping_zone_id'] = $address['zone_id'];
                $this->session->data['shipping_area_id'] = $address['area_id'];
                $this->session->data['shipping_postcode'] = $address['postcode'];
            }

            if ($this->config->get('config_tax_customer') == 'payment') {
                $this->session->data['payment_country_id'] = $address['country_id'];
                $this->session->data['payment_zone_id'] = $address['zone_id'];
                $this->session->data['payment_area_id'] = $address['area_id'];
            }
        } else {
            unset(
                $this->session->data['shipping_country_id'],
                $this->session->data['shipping_zone_id'],
                $this->session->data['shipping_area_id'],
                $this->session->data['shipping_postcode'],
                $this->session->data['payment_country_id'],
                $this->session->data['payment_zone_id'],
                $this->session->data['payment_area_id']
            );
        }

        return true;
    }

    public function storeAddressToSession(array $data)
    {
        if (!(int) $data['default']) return false;

        // Default Shipping Address
        if ($this->config->get('config_tax_customer') == 'shipping') {
            $this->session->data['shipping_address_id'] = (int)$data['address_id'];
            $this->session->data['shipping_country_id'] = (int)$data['country_id'];
            $this->session->data['shipping_zone_id'] = (int)$data['zone_id'];
            $this->session->data['shipping_area_id'] = (int)$data['area_id'];
            $this->session->data['shipping_location'] = $data['location'];
            $this->session->data['shipping_postcode'] = $data['postcode'];
            $this->session->data['shipping_telephone'] = $data['telephone'];
            $this->session->data['shipping_firstname'] = $data['firstname'];

            // unset($this->session->data['shipping_method']);
            // unset($this->session->data['shipping_methods']);
        }

        // Default Payment Address
        if ($this->config->get('config_tax_customer') == 'payment') {
            $this->session->data['payment_address_id'] = (int)$data['address_id'];
            $this->session->data['payment_country_id'] = (int)$data['country_id'];
            $this->session->data['payment_zone_id'] = (int)$data['zone_id'];
            $this->session->data['payment_area_id'] = (int)$data['area_id'];
            $this->session->data['payment_location'] = $data['location'];
            $this->session->data['payment_postcode'] = $data['postcode'];
            $this->session->data['payment_telephone'] = $data['telephone'];
            $this->session->data['payment_firstname'] = $data['firstname'];

            // unset($this->session->data['payment_method']);
            // unset($this->session->data['payment_methods']);
        }
    }

    public function getIdentityType()
    {
        return $this->isLoginByPhone() ? 'telephone' : 'email';
    }

    // public function requestToAllowSignInByTelephone()
    // {
    //     $config = $this->config->get('config_customer_fields');

    //     if (!isset($config['identity'])) $config['identity'] = ['type' => 'email'];

    //     $config['identity']['allow_telephone'] = 'pending';

    //     $settingModel = $this->load->model('setting/setting', ['return' => true]);

    //     $settingModel->insertUpdateSetting('config', ['config_customer_fields' => $config]);

    //     $queryRequestExist = $this->ecusersdb->query(sprintf('SELECT * FROM `expand_identity_phone_requests` WHERE `store_code` = "%s"', STORECODE));

    //     if ($queryRequestExist->num_rows) {
    //         $this->ecusersdb->query(sprintf('UPDATE `expand_identity_phone_requests` SET `status` = "%s", `updated_at` = "%s" WHERE `store_code` = "%s"', 'pending', date('Y-m-d h:i:s'), STORECODE));
    //     } else {
    //         $this->ecusersdb->query(sprintf('INSERT INTO `expand_identity_phone_requests` SET `store_code` = "%s", `status` = "%s", `created_at` = "%s"', STORECODE, 'pending', date('Y-m-d h:i:s')));
    //     }
    //     return true;
    // }

    public function logSmsInfoToEcTools($request, $response)
    {
        $this->ecusersdb->query(sprintf(
            'INSERT INTO `expand_identity_phone_sms_logs` SET 
            `store_code` = "%s", 
            `phone_number` = "%s", 
            `sended_at` = "%s", 
            `sended_by` = "%s"',
            $request['form_params']['store_code'],
            $request['form_params']['identity'],
            $request['form_params']['request_datetime'],
            $response['verification_provider']
        ));
    }

    private function updateSmsBalance($request, $response)
    {
        $config = $this->config->get('config_customer_fields');

        if (!isset($config['identity'])) $config['identity'] = [];

        if (!isset($config['identity']['sms_balance'])) $config['identity']['sms_balance'] = 0;
        if (!isset($config['identity']['sms_sent_msg'])) $config['identity']['sms_sent_msg'] = 0;

        $currentBalance = ($config['identity']['sms_balance'] == 0 ? 0 : --$config['identity']['sms_balance']);

        $sentMsgs = ++$config['identity']['sms_sent_msg'];

        $config['identity']['sms_balance'] = $currentBalance;
        $config['identity']['sms_sent_msg'] = $sentMsgs;


        $settingModel = $this->load->model('setting/setting', ['return' => true]);

        $settingModel->insertUpdateSetting('config', ['config_customer_fields' => $config]);
    }

    private function checkIfIdentityTypeWillChange()
    {
        if (!$this->canSendSms()) {
            $config = $this->config->get('config_customer_fields');

            if (!isset($config['identity'])) $config['identity'] = [];

            $config['identity']['type'] =  'email';

            $settingModel = $this->load->model('setting/setting', ['return' => true]);
            $settingModel->insertUpdateSetting('config', ['config_customer_fields' => $config]);
        }
    }

    public function canSendSms()
    {
        $config = $this->config->get('config_customer_fields');

        if (isset($config['identity']) && ((int) $config['identity']['sms_balance'] > 0)) {
            return true;
        }

        return false;
    }

    // private function logVerificationCodeRequest($request, $response)
    // {
    //     $postData = $request['form_params'];

    //     $file = rtrim(DIR_LOGS, '/') . '/../' .  'identity_verification_' . $postData['identity_type'] . '_requests.txt';

    //     $data = [
    //         $postData['ip'], // client ip send request 
    //         $postData['store_code'], // to store code
    //         $postData['request_datetime'], // at time
    //         $postData['identity'], // with identity 
    //         $response['verification_provider'], // by provider 
    //     ];

    //     $handle = fopen($file, 'a+');

    //     fwrite($handle, date('Y-m-d G:i:s') . ' - ' . implode('|', $data) . "\n");

    //     fclose($handle);
    // }
}
