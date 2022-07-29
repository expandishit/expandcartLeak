<?php

use ExpandCart\Foundation\Providers\Extension;

class ControllerAccountIdentity extends Controller
{
    private $requestData;

    private $customerModel;

    private $addressModel;

    private $customerGroupModel;

    private $customerAccountFields;

    private $customerFormFieldsOrder = [
        'name' => 1,
        'firstname' => 2,
        'email' => 3,
        'telephone' => 4,
        'dob' => 5,
        'gender' => 6,
        'company' => 7,
        'customer_group_id' => 8,
        'newsletter' => 9,
    ];

    private $addressFormFieldsOrder = [
        'location' => 1,
        'country_id' => 2,
        'zone_id' => 3,
        'area_id' => 4,
        'address_1' => 5,
        'address_2' => 6,
        'postcode' => 7,
        'telephone' => 8,
        'default' => 9,
    ];

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->requestData = $this->request->post;

        $this->customerModel = $this->load->model('account/customer', ['return' => true]);
        $this->addressModel = $this->load->model('account/address', ['return' => true]);
        $this->customerGroupModel = $this->load->model('account/customer_group', ['return' => true]);

        $this->customerAccountFields = $this->config->get('config_customer_fields');

        $this->language->load_json('account/identity', true);

        // **** set response headers
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Methods: POST');
        $this->response->addHeader('Access-Control-Allow-Headers: X-Requested-With');
        $this->response->addHeader('Content-Type: application/json');
    }

    ###################################### Localization ######################################
    public function getLocalization()
    {
        $this->response->setOutput(json_encode(['success' => true, 'dictionary' => $this->language->getAll()]));
        return;
    }

    ###################################### Identity Props ######################################
    public function getIdentityProps()
    {
        $data = [];
        $this->load->model('localisation/country');
        $this->load->model("module/google_map");
        $this->load->model('setting/extension');
        $data['storeName'] = $this->config->get('config_name');
        $data['lang'] = in_array($this->config->get('config_language'), ['ar', 'en']) ? $this->config->get('config_language') : 'en';
        $data['storeCode'] = STORECODE;
        $data['countryId'] = $this->session->data['shipping_country_id'] ?? $this->config->get('config_country_id');
        
		//this option currently removed 
		//$data['whatsAppAgree'] = (int)(Extension::isInstalled('whatsapp') && $this->config->get('whatsapp_config_customer_allow_receive_messages')) ? 1 : 0;

		$data['whatsAppAgree'] = true;
        $data['enableMultiseller'] = (int)Extension::isInstalled('multiseller');
        $data['loginWithPhone'] = $this->identity->isLoginByPhone();
        $data['customer'] = $this->customer->isLogged() ? ['id' => $this->customer->getExpandId(), 'logged_in' => true] : ['logged_in' => false];
        $data['map'] = $this->model_module_google_map->getSettings();
        $extensions = $this->model_setting_extension->getExtensions('module');
        $socialLogin = ['status' => false];
        foreach ($extensions as $extension) if ($extension['code'] == 'd_social_login') {
            $settings = $this->config->get('d_social_login_settings');
            if ($settings) {
                if ($settings['status']) {
                    $socialLogin['status'] = true;
                    $socialLogin['content'] = $this->getChild('module/' . 'd_social_login');
                }
            }
            break;
        }
        $data['socialLogin'] = $socialLogin;
        $data['countries'] = $this->model_localisation_country->getCountries();
        $data['customerAccountFields'] = $this->config->get('config_customer_fields');
        $data['libraryStatus'] = (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) ? 'on' : 'off';

        $this->response->setOutput(json_encode(['success' => true, 'data' => $data]));
        return;
    }

    ###################################### Customer apis ######################################

    public function sendVerificationCode()
    {
        if (!$this->identityAllowed()) {
            $this->response->setOutput(json_encode(['success' => false,]));
            return;
        }

        // validate identity
        if (empty($this->requestData['identity'])) {
            $this->response->setOutput(json_encode(['success' => false, 'errors' => ['identity' => $this->language->get('error_required_field'),]]));
            return;
        }

        if ($this->identity->isLoginByPhone() && !is_simple_valid_phone($this->requestData['identity'])) {
            $this->response->setOutput(json_encode(['success' => false, 'errors' => ['identity' => $this->language->get('invalid_input_telephone'),]]));
            return;
        }
        
        // check sms balance
        if ($this->identity->isLoginByPhone() && !$this->identity->canSendSms()) {
            $this->response->setOutput(json_encode(['success' => false, 'errors' => ['identity' => $this->language->get('text_no_balance_to_send_messages'),]]));
            return;
        }

        if (!$this->identity->isLoginByPhone() && !is_valid_email($this->requestData['identity'])) {
            $this->response->setOutput(json_encode(['success' => false, 'errors' => ['identity' => $this->language->get('invalid_input_email'),]]));
            return;
        }

        $needActivation = $needApproval = false;

        $identityType = $this->identity->isLoginByPhone() ? 'telephone' : 'email';

        $result = $this->identity->checkCustomer($this->requestData['identity']);

        if ($result['success'] == false) {
            $this->response->setOutput(json_encode($result));
            return;
        }

        $customerExpandId = $result['data'] ? $result['data']['customer_id'] : null;

        if ($customerExpandId && $customer = $this->customerModel->getCustomerBy([
            [$identityType, $this->requestData['identity']],
            ['expand_id', $customerExpandId]
        ])) {
            $needActivation = (int) $customer['status'] === 0;
            $needApproval = (int) $customer['approved'] === 0;
        } elseif ($customer = $this->identity->selectCustomer([$identityType => $this->requestData['identity']])) {
            $needActivation = (int) $customer['status'] === 0;
            $needApproval = (int) $customer['approved'] === 0;
        }

        if ($needActivation) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'message' => $this->language->get('inactive_customer_account'),
                'errors' => ['warning' => [sprintf($this->language->get('inactive_customer_account_massage'), $this->language->get("entry_$identityType"))]],
            ]));
            return;
        }

        if ($needApproval) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'redirect' => $this->url->link('account/success', '', 'SSL')
            ]));
            return;
        }

        $result = $this->identity->sendVerificationCode($this->requestData);

        // var_dump($result);
        // exit;

        $this->response->setOutput(json_encode($result));
        return;
    }

    public function verifyCode()
    {
        if (!$this->identityAllowed()) {
            $this->response->setOutput(json_encode(['success' => false,]));
            return;
        }

        $this->requestData['id'] = $this->requestData['id'] ?: $this->customer->getExpandId(); // this for logged in customers 

        if (
            empty($this->requestData['id']) ||
            empty($this->requestData['code']) ||
            empty($this->requestData['identity']) ||
            empty($this->requestData['verification_provider'])
        ) {
            $this->response->setOutput(json_encode(['success' => false]));
            return;
        }

        $result = $this->identity->verifyCode($this->requestData);

        if ($result['success'] === true) {
            $identityCustomer = $result['data'];
            $is_seller = isset($this->requestData['is_seller']) ? $this->requestData['is_seller'] : false;

            $customer = $this->identity->selectCustomer($identityCustomer);

            // case 1: customer exist in local db
            if ($customer && $customer['expand_id'] == $identityCustomer['id']) {
                $identityCustomer['customer_id'] = $customer['customer_id'];

                // update local customer basic data
                $this->customerModel->updateCustomerBasicData($this->parseCustomerBasicData($customer, $identityCustomer));
                
            }

            return $this->validateCustomer($identityCustomer, $is_seller);
        }

        $this->response->setOutput(json_encode($result));
        return;
    }

    public function registerCustomer()
    {
        if (!$this->identityAllowed()) {
            $this->response->setOutput(json_encode(['success' => false,]));
            return;
        }

        $result = $this->identity->registerCustomer(array_merge($this->requestData, ['name' => $this->requestData['firstname']]));

        if ($result['success']) {
            $identityCustomer = $result['data'];

            $customer = $this->identity->selectCustomer($identityCustomer);

            if ($customer) {
                // case 1: customer exist in local db
                $identityCustomer['customer_id'] = $customer['customer_id'];

                // update local customer basic data
                $this->customerModel->updateCustomerBasicData(array_merge($identityCustomer, $this->requestData));

            }

            if (!$customer) {
                $this->customerModel->addCustomer($this->mergeInputs($this->requestData, $identityCustomer, ['expand_id' => $identityCustomer['id']]));
            }

            $customerMergedData = array_merge($identityCustomer, $this->requestData);
            if (!empty($customerMergedData['email'])){
                $this->initializer([
                    'getResponse' => 'module/get_response/settings',
                    'mailchimp' => 'module/mailchimp/settings',
                ]);

                if ($this->getResponse->isActive() && $this->getResponse->hasTag('register')) {
                    $this->getResponse->addContact($customerMergedData, 'register');
                }

                if ($this->mailchimp->isActive() && $this->mailchimp->hasTag('register')) {

                    $subscriberHash = $this->mailchimp->getSubscriberHash(
                        $customerMergedData['email']
                    );

                    $this->mailchimp->addNewSubscriber($customerMergedData, 'register', $subscriberHash);
                }
            }

            $this->validateCustomer($identityCustomer);
            return;
        }

        $this->response->setOutput(json_encode($result));
        return;
    }

    public function getCustomer()
    {
        if (!$this->identityAllowed()) {
            $this->response->setOutput(json_encode(['success' => false,]));
            return;
        }

        if (!$this->customer->isLogged()) {
            $this->response->setOutput(json_encode(['success' => false, 'session_expired' => true]));
            return;
        }

        $result = $this->identity->getCustomer($this->customer->getExpandId());
        $this->response->setOutput(json_encode($result));
        return;
    }

    public function validateCustomerProfile()
    {
        if (!$this->identityAllowed()) {
            $this->response->setOutput(json_encode(['success' => false,]));
            return;
        }

        if (!$this->customer->isLogged()) {
            $this->response->setOutput(json_encode(['success' => false, 'session_expired' => true]));
            return;
        }

        $errors = [];

        $formFields = $this->customerAccountFields['registration'];

        if (empty($this->requestData['name'])) $errors['name'] = [$this->language->get('error_required_field')];

        if (!$this->identity->isLoginByPhone() && empty($this->requestData['email'])) $errors['email'] = [$this->language->get('error_required_field')];

        $this->identity->isLoginByPhone() && empty($this->requestData['telephone']) && $errors['telephone'] = [$this->language->get('error_required_field')];

        !empty($this->requestData['telephone']) && !is_simple_valid_phone($this->requestData['telephone']) && $errors['telephone'] = [$this->language->get('error_invalid_field')];

        if ($formFields['gender'] == 1 && empty($this->requestData['gender'])) $errors['gender'] = [$this->language->get('error_required_field')];
        if ($formFields['dob'] == 1 && empty($this->requestData['dob'])) $errors['dob'] = [$this->language->get('error_required_field')];
        if ($formFields['company'] == 1 && empty($this->requestData['company'])) $errors['company'] = [$this->language->get('error_required_field')];

        if (!empty($errors)) {
            $this->response->setOutput(json_encode(['success' => false, 'errors' => $errors]));
            return;
        }

        if ($this->customer->getExpandId()) {
            $result = $this->identity->validateCustomerProfile(array_merge($this->requestData, [
                'id' => $this->customer->getExpandId(),
            ]));

            $this->response->setOutput(json_encode($result));
            return;
        }

        $this->response->setOutput(json_encode(['success' => true,]));
    }

    public function updateCustomer()
    {
        if (!$this->identityAllowed()) {
            $this->response->setOutput(json_encode(['success' => false,]));
            return;
        }

        if (!$this->customer->isLogged()) {
            $this->response->setOutput(json_encode(['success' => false, 'session_expired' => true]));
            return;
        }

        $otherFields = array_merge([
            'customer_id' => $this->customer->getId()
        ], isset($this->requestData['customer_group_id']) ? [
            'customer_group_id' => $this->requestData['customer_group_id']
        ] : [], isset($this->requestData['newsletter']) ? [
            'newsletter' => $this->requestData['newsletter']
        ] : [], isset($this->requestData['company']) ? [
            'company' => $this->requestData['company']
        ] : []);

        if ($this->customer->getExpandId()) {
            $result = $this->identity->updateCustomer(array_merge($this->requestData, [
                'id' => $this->customer->getExpandId(),
            ]));

            if ($result['success']) {
                $this->customerModel->updateCustomerBasicData(array_merge($result['data'], $otherFields));
                $this->response->setOutput(json_encode(['success' => true, 'data' => $this->identity->selectCustomer($result['data'])]));
                return;
            }

            $this->response->setOutput(json_encode($result));
            return;
        }

        $this->customerModel->updateCustomerBasicData(array_merge($this->requestData, $otherFields));
        $this->response->setOutput(json_encode(['success' => true, 'data' => $this->requestData]));
        return;
    }

    ###################################### Address apis ######################################

    public function getAddresses()
    {
        if (!$this->identityAllowed()) {
            $this->response->setOutput(json_encode(['success' => false,]));
            return;
        }

        if (!$this->customer->isLogged()) {
            $this->response->setOutput(json_encode(['success' => false, 'session_expired' => true]));
            return;
        }

        $addresses = array();

        foreach ($this->addressModel->getAddresses() as $result) {
            $addresses[] = $this->formatAddress($result);
        }

        $this->response->setOutput(json_encode(['success' => true, 'addresses' => $addresses]));
        return;
    }

    public function getAddressFields()
    {
        if (!$this->identityAllowed()) {
            $this->response->setOutput(json_encode(['success' => false,]));
            return;
        }

        if (!$this->customer->isLogged()) {
            $this->response->setOutput(json_encode(['success' => false, 'session_expired' => true]));
            return;
        }

        $fields = [];


        $formFields = array_filter($this->customerAccountFields['address'], function ($v, $k) {
            return $v >= 0;
        }, ARRAY_FILTER_USE_BOTH);


        foreach ($formFields as $key => $value) {
            $fields[] = [
                'name' => $key,
                'required' => $value == 1,
                'placeholder' => $this->language->get('entry_' . ($key == 'telephone' ? 'shipping_telephone' : $key)),
            ];
        }

        $address = !empty($this->requestData['address_id']) ? $this->addressModel->getAddress($this->requestData['address_id']) : null;

        foreach ($fields as &$field) {
            if ($address) {
                $field['value'] = $address[$field['name']] ?? '';
            } else
                $field['value'] =  '';
        }

        $fields[] = [
            'name' => 'default',
            'required' => false,
            'placeholder' => $this->language->get('entry_default'),
            'value' => $this->customer->getAddressId() == $this->requestData['address_id']
        ];

        $fields = array_unique($fields, SORT_REGULAR);

        $setDisplayOrder = function (&$field) {
            $field['order'] = isset($this->addressFormFieldsOrder[$field['name']]) ? $this->addressFormFieldsOrder[$field['name']] : 100;
        };

        array_walk($fields, $setDisplayOrder);

        usort($fields, function ($f1, $f2) {
            return $f1['order'] <=> $f2['order'];
        });

        $this->response->setOutput(json_encode(['success' => true, 'fields' => $fields]));
        return;
    }

    public function addAddress()
    {
        if (!$this->identityAllowed()) {
            $this->response->setOutput(json_encode(['success' => false,]));
            return;
        }

        if (!$this->customer->isLogged()) {
            $this->response->setOutput(json_encode(['success' => false, 'session_expired' => true]));
            return;
        }

        $data = $this->requestData;

        $validator = $this->validateFormAddress($data);

        if ($validator !== true) {
            $this->response->setOutput(json_encode(['success' => false, 'errors' => $validator]));
            return;
        }

        $address_id = $this->identity->addAddress($data);

        if ($data['default'] == 1) $this->customer->setAddressId($address_id);

        $this->response->setOutput(json_encode(['success' => true, 'data' => $this->formatAddress($this->addressModel->getAddress($address_id))]));
        return;
    }

    public function updateAddress()
    {
        $data = $this->requestData;

        if (!$this->identityAllowed() || empty($data['address_id'])) {
            $this->response->setOutput(json_encode(['success' => false,]));
            return;
        }

        if (!$this->customer->isLogged()) {
            $this->response->setOutput(json_encode(['success' => false, 'session_expired' => true]));
            return;
        }

        $validator = $this->validateFormAddress($data);

        if ($validator !== true) {
            $this->response->setOutput(json_encode(['success' => false, 'errors' => $validator]));
            return;
        }

        $this->identity->updateAddress($data);

        if ($data['default'] == 1) $this->customer->setAddressId($data['address_id']);

        $this->response->setOutput(json_encode(['success' => true, 'data' => $this->formatAddress($this->addressModel->getAddress($data['address_id']))]));
        return;
    }

    public function deleteAddress()
    {
        $data = $this->requestData;

        if (!$this->identityAllowed() || empty($data['address_id'])) {
            $this->response->setOutput(json_encode(['success' => false,]));
            return;
        }


        if (!$this->customer->isLogged()) {
            $this->response->setOutput(json_encode(['success' => false, 'session_expired' => true]));
            return;
        }

        $validator = $this->validateDeleteAddress($data);

        if ($validator !== true) {
            $this->response->setOutput(json_encode(['success' => false, 'errors' => $validator]));
            return;
        }

        $this->addressModel->deleteAddress($data['address_id']);

        // Default Shipping Address
        if (isset($this->session->data['shipping_address_id']) && ($data['address_id'] == $this->session->data['shipping_address_id'])) {
            unset($this->session->data['shipping_address_id']);
            unset($this->session->data['shipping_country_id']);
            unset($this->session->data['shipping_zone_id']);
            unset($this->session->data['shipping_area_id']);
            unset($this->session->data['shipping_location']);
            unset($this->session->data['shipping_postcode']);
            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
        }

        // Default Payment Address
        if (isset($this->session->data['payment_address_id']) && ($data['address_id'] == $this->session->data['payment_address_id'])) {
            unset($this->session->data['payment_address_id']);
            unset($this->session->data['payment_country_id']);
            unset($this->session->data['payment_zone_id']);
            unset($this->session->data['payment_area_id']);
            unset($this->session->data['payment_location']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
        }

        $this->response->setOutput(json_encode(['success' => true]));
        return;
    }

    public function country()
    {
        if (!$this->identityAllowed()) {
            $this->response->setOutput(json_encode(['success' => false,]));
            return;
        }

        $json = ['success' => false];

        $this->load->model('localisation/country');

        $country_info = $this->model_localisation_country->getCountry($this->requestData['country_id']);

        if ($country_info) {
            $this->load->model('localisation/zone');

            $data = array(
                'country_id'        => $country_info['country_id'],
                'name'              => $country_info['name'],
                'iso_code_2'        => $country_info['iso_code_2'],
                'iso_code_3'        => $country_info['iso_code_3'],
                'address_format'    => $country_info['address_format'],
                'postcode_required' => $country_info['postcode_required'],
                'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->requestData['country_id']),
                'status'            => $country_info['status'],
                'phonecode'         => $country_info['phonecode']
            );

            $json['success'] = true;
            $json['data'] = $data;
        }

        $this->response->setOutput(json_encode($json));
    }

    public function zone()
    {
        if (!$this->identityAllowed()) {
            $this->response->setOutput(json_encode(['success' => false,]));
            return;
        }

        $json = ['success' => false];

        $this->load->model('localisation/zone');

        $zone_info = $this->model_localisation_zone->getZone($this->requestData['zone_id']);

        if ($zone_info) {
            $this->load->model('localisation/area');

            $data = array(
                'zone_id'        => $zone_info['country_id'],
                'name'              => $zone_info['name'],
                'area'              => $this->model_localisation_area->getAreaByZoneId($this->requestData['zone_id']),
                'status'            => $zone_info['status']
            );

            $json['success'] = true;
            $json['data'] = $data;
        }

        $this->response->setOutput(json_encode($json));
    }

    ###################################### Helpers ######################################

    private function validateDeleteAddress(array $data)
    {
        $errors = [];

        // if ($this->addressModel->getTotalAddresses() == 1) {
        //     $errors['warning'] = $this->language->get('error_address_delete');
        // }

        if ($this->customer->getAddressId() == $data['address_id']) {
            $errors['warning'] = $this->language->get('error_address_default');
        }

        return empty($errors) ? true : $errors;
    }

    private function validateFormAddress(array &$data)
    {
        $errors = [];
        $formFields = $this->customerAccountFields['address'];

        if ($formFields['country_id'] == 1 && (!isset($data['country_id']) || $data['country_id'] == '' || !is_numeric($data['country_id'])))
            $errors['country_id'] = $this->language->get('required_input_country_id');

        if ($formFields['zone_id'] == 1 && (!isset($data['zone_id']) || $data['zone_id'] == '' || !is_numeric($data['zone_id'])))
            $errors['zone_id'] = $this->language->get('required_input_zone_id');

        if ($formFields['area_id'] == 1 && (!isset($data['area_id']) || $data['area_id'] == '' || !is_numeric($data['area_id'])))
            $errors['area_id'] = $this->language->get('required_input_area_id');

        if ($formFields['address_1'] == 1) {
            if (!utf8_strlen($data['address_1'])) {
                $errors['address_1'] = $this->language->get('required_input_address_1');
            } elseif (utf8_strlen($data['address_1']) < 3 || utf8_strlen($data['address_1']) > 128) {
                $errors['address_1'] = $this->language->get('invalid_input_address_1');
            }
        }

        if ($formFields['address_2'] == 1) {
            if (!utf8_strlen($data['address_2'])) {
                $errors['address_2'] = $this->language->get('required_input_address_2');
            } elseif (utf8_strlen($data['address_2']) < 3 || utf8_strlen($data['address_2']) > 128) {
                $errors['address_2'] = $this->language->get('invalid_input_address_2');
            }
        }

        if ($formFields['telephone'] == 1 && !is_simple_valid_phone($data['telephone'])) {
            $errors['telephone'] = $this->language->get('error_invalid_field');
        }

        // if ($formFields['city'] == 1 && ((utf8_strlen($data['city']) < 2) || (utf8_strlen($data['city']) > 128)))
        //     $errors['city'] = $this->language->get('required_input_city');

        if ($formFields['postcode'] == 1 && (!isset($data['postcode']) || $data['postcode'] == ''))
            $errors['postcode'] = $this->language->get('required_input_postcode');

        // if ($formFields['company'] == 1 && (!isset($data['company']) || $data['company'] == ''))
        //     $errors['company'] = $this->language->get('required_input_company');

        // if ($data['country_id'] == '' || !is_numeric($data['country_id'])) {
        //     $errors['country_id'] = $this->language->get('required_input_country');
        // }

        // if ($formFields['location'] == 1 && (!isset($data['location']) || $data['location'] == ''))
        //     $errors['location'] = $this->language->get('required_input_location');

        if (empty($errors) && $this->addressModel->getTotalAddresses() == 0) {
            $data['default'] = 1;
        }

        return empty($errors) ? true : $errors;
    }

    private function mergeRegisterFormFields($identity_fields = [], $other_fields = [], $seller_fields = [])
    {
        $fields =  array_merge($other_fields, $identity_fields, $seller_fields);
        return $fields;
    }

    private function parseIdentityFields(array $mandatory_fields = [])
    {
        $fields = [];

        foreach ($mandatory_fields as $field) {
            if ($field == 'name') continue;

            $fields[] = [
                'name' => $field,
                'required' => true,
                'placeholder' => $this->language->get('entry_' . $field),
            ];
        }

        return $fields;
    }

    private function parseCustomerAccountFields()
    {
        $formFields = $this->customerAccountFields['registration'];

        if ($this->identity->isLoginByPhone())
            $fields = [[
                'name' => 'email',
                'required' => false,
                'placeholder' => $this->language->get('entry_email'),
                'value' => '',
            ]];
        else
            $fields = [[
                'name' => 'telephone',
                'required' => false,
                'placeholder' => $this->language->get('entry_telephone'),
                'value' => '',
            ]];

        foreach ($formFields as $key => $value) {
            if (-1 == $value) continue;

            if ($key == 'groups') {
                $customerGroups = [];

                if (is_array($this->config->get('config_customer_group_display'))) {
                    $groups = $this->customerGroupModel->getCustomerGroups();
                    foreach ($groups as $group) {
                        if (in_array($group['customer_group_id'], $this->config->get('config_customer_group_display'))) {
                            $customerGroups[] = $group;
                        }
                    }
                }

                if (!empty($customerGroups)) {
                    $fields[] = [
                        'name' => 'customer_group_id',
                        'required' => $value == 1,
                        'placeholder' => $this->language->get('entry_customer_group'),
                        'values' => $customerGroups,
                        'value' => $this->config->get('config_customer_group_id'),
                    ];
                }
            } else if ($key == 'terms') {
                if ($this->config->get('config_account_id')) {
                    $this->load->model('catalog/information');
                    $information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

                    if ($information_info) {
                        $textAgree = sprintf($this->language->get('text_agree'), $this->url->link('information/information', 'information_id=' . trim($this->config->get('config_account_id')), 'SSL'), $information_info['title'], $information_info['title']);
                        $fields[] = [
                            'name' => $key,
                            'required' => $value == 1,
                            'placeholder' => $textAgree,
                            'id' => $information_info['information_id'],
                            'value' => 0,
                        ];
                    }
                }
            } else {
                $fields[] = [
                    'name' => $key,
                    'required' => $value == 1,
                    'placeholder' => $this->language->get('entry_' . $key),
                    'value' => '',
                ];
            }
        }

        return $fields;
    }

    private function filterRegisterFormFields($data, $fields)
    {
        $defaultCustomerGroup = $this->config->get('config_customer_group_id');

        if (isset($data['addresses'])) {
            $data['addresses'] = array_values($data['addresses']);
        }

        $data = static::arrayDot($data);

        foreach ($fields as $k => $field) {
            if (
                isset($data[$field['name']]) &&
                !empty($data[$field['name']]) &&
                ($field['name'] != 'customer_group_id' ||
                    ($field['name'] == 'customer_group_id' &&
                        $field['value'] != $defaultCustomerGroup))
            ) {
                unset($fields[$k]);
            }
        }

        return $this->sortCustomerFields($fields);
    }

    private function fillCustomerFieldsValue($data, $fields)
    {
        $defaultCustomerGroup = $this->config->get('config_customer_group_id');

        $data = static::arrayDot($data);

        foreach ($fields as $k => $field) {
            if (isset($data[$field['name']]) && !empty($data[$field['name']])) {
                $fields[$k]['value'] = $data[$field['name']];
            } elseif ($field['name'] == 'customer_group_id' && isset($data[$field['name']]) && empty($data[$field['name']])) {
                $fields[$k]['value'] = $defaultCustomerGroup;
            }
        }

        return $this->sortCustomerFields($fields);
    }

    private function sortCustomerFields($fields)
    {
        $fieldsRequired = array_filter($fields, function ($v) {
            return (int)$v['required'] === 1;
        });

        $fieldsOptional = array_filter($fields, function ($v) {
            return (int)$v['required'] === 0;
        });

        $fields = array_merge($fieldsRequired, $fieldsOptional);

        $fields = array_unique($fields, SORT_REGULAR);

        $setDisplayOrder = function (&$field) {
            $field['order'] = isset($this->customerFormFieldsOrder[$field['name']]) ? $this->customerFormFieldsOrder[$field['name']] : 100;
        };

        array_walk($fields, $setDisplayOrder);

        usort($fields, function ($f1, $f2) {
            return $f1['order'] <=> $f2['order'];
        });

        return $fields;
    }

    private function validateCustomer($identityCustomer, bool $is_seller = false)
    {
        $customer = $this->identity->selectCustomer($identityCustomer);

        $isSeller = $is_seller && $this->enableMultiseller();

        // check identity required fields value ie: name and check store registration fields;
        $fields = $this->mergeRegisterFormFields(
            $this->parseIdentityFields($identityCustomer['mandatory_fields']),
            $this->parseCustomerAccountFields(),
            $isSeller ? $this->parseSellerAccountFields() : []
        );

        $seller = ($isSeller && $customer && $this->MsLoader->MsSeller->isCustomerSeller($customer['customer_id']))
            ? $this->MsLoader->MsSeller->getSeller((int) $customer['customer_id'])
            : [];

        $seller = $seller ? $seller : [];

        $currentCustomerData = $this->mergeInputs($customer ?: [], $seller, $this->requestData, $identityCustomer);

        $uncompletedFields = $this->filterRegisterFormFields($currentCustomerData, $fields);

        $fieldsRequired = array_values(array_filter($uncompletedFields, function ($v) {
            return (int)$v['required'] === 1;
        }));

        // in case of no required fields except terms field in this case will ignore it
        if (count($fieldsRequired) === 1 && $fieldsRequired[0]['name'] === 'terms') unset($fieldsRequired[0]);

        // try login
        if ($customer && empty($fieldsRequired) && $customer['expand_id'] == $identityCustomer['id']) {
            // check current customer group need approval
            if (isset($customer['approved']) && !(int)$customer['approved']) {
                $this->response->setOutput(json_encode(['success' => true, 'redirect' => $this->url->link('account/success', '', 'SSL')]));
                return;
            }

            // check if customer wasn't created by merchant and he has a valid account
            if ($this->identity->attemptLogin($identityCustomer)) {
                $this->response->setOutput(json_encode([
                    'success' => true,
                    'customer' => $this->identity->selectCustomer($identityCustomer) // fresh customer
                ]));
                return;
            }
        }

        // display complete profile modal to fill data
        $fields = $this->fillCustomerFieldsValue($currentCustomerData, $fields);

        $this->response->setOutput(json_encode([
            'success' => true,
            'fields' => $fields
        ] + ($isSeller ? [
            'is_seller' => true,
            'redirect' => $this->buildSellerRedirectLink($identityCustomer, $customer, $seller),
        ] : [])));
        return;
    }
    
    private function formatAddress(array $address = null)
    {
        if (!$address) return false;

        if ($address['address_format']) {
            $format = $address['address_format'];
        } else {
            $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
        }

        $find = array(
            '{firstname}',
            '{lastname}',
            '{company}',
            '{address_1}',
            '{address_2}',
            '{city}',
            '{postcode}',
            '{zone}',
            '{zone_code}',
            '{country}'
        );

        $replace = array(
            'firstname' => '',
            'lastname'  => '',
            'company'   => '',
            'address_1' => $address['address_1'],
            'address_2' => $address['address_2'],
            'city'      => $address['area'],
            'postcode'  => $address['postcode'],
            'zone'      => $address['zone'],
            'zone_code' => $address['zone_code'],
            'country'   => $address['country']
        );

        return array_merge($address, array(
            'address' => str_replace(array("\r\n", "\r", "\n"), '<span></span>', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<span></span>', trim(str_replace($find, $replace, $format)))),
            'default' => $this->customer->getAddressId() == $address['address_id']
        ));
    }

    // Seller ...  

    private function enableMultiseller()
    {
        return (int)Extension::isInstalled('multiseller');
    }

    private function parseSellerAccountFields()
    {

        $this->load->model('setting/setting');

        $settings = $this->model_setting_setting->getSetting('multiseller');

        $seller_required_fields = $settings['msconf_seller_required_fields'];
        $seller_show_fields     = $settings['msconf_seller_show_fields'];

        $seller_custom_fields = $this->config->get('msconf_seller_data_custom');
        $config_language_id = $this->config->get('config_language_id');

        $fields = [];

        $fields[] = [
            'name' => 'ms.nickname',
            'required' => true,
            'placeholder' => $this->language->get('entry_seller_nickname')
        ];

        foreach ($seller_custom_fields as $key => $field) {
            if ($field['active'] == '0') continue;
            $fields[] = [
                'name' => 'ms.custom_fields.' . $key,
                'required' => $field['required'] == '1',
                'placeholder' => $field['title'][$config_language_id],
            ];
        }

        if (in_array(ucfirst('tax card'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.tax_card',
                'required' => in_array(ucfirst('tax card'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_tax_card')
            ];
        }

        if (in_array(ucfirst('description'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.description',
                'required' => in_array(ucfirst('description'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_description')
            ];
        }

        if (in_array(ucfirst('mobile'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.mobile',
                'required' => in_array(ucfirst('mobile'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_mobile')
            ];
        }

        if (in_array(ucfirst('company'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.company',
                'required' => in_array(ucfirst('company'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_company')
            ];
        }

        if (in_array(ucfirst('website'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.website',
                'required' => in_array(ucfirst('website'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_website')
            ];
        }

        if (in_array(ucfirst('commercial register'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.commercial_reg',
                'required' => in_array(ucfirst('commercial register'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_commercial_reg')
            ];
        }

        if (in_array(ucfirst('record expiration date'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.rec_exp_date',
                'required' => in_array(ucfirst('record expiration date'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_rec_exp_date')
            ];
        }

        if (in_array(ucfirst('industrial license number'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.license_num',
                'required' => in_array(ucfirst('industrial license number'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_license_num')
            ];
        }

        if (in_array(ucfirst('license expiration date'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.lcn_exp_date',
                'required' => in_array(ucfirst('license expiration date'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_lcn_exp_date')
            ];
        }

        if (in_array(ucfirst('personal id'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.personal_id',
                'required' => in_array(ucfirst('personal id'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_personal_id')
            ];
        }

        if (in_array(ucfirst('paypal'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.paypal',
                'required' => in_array(ucfirst('paypal'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_paypal')
            ];
        }

        if (in_array(ucfirst('bank name'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.bank_name',
                'required' => in_array(ucfirst('bank name'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_bank_name')
            ];
        }

        if (in_array(ucfirst('bank iban'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.bank_iban',
                'required' => in_array(ucfirst('bank iban'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_bank_iban')
            ];
        }

        if (in_array(ucfirst('bank transfer'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.bank_transfer',
                'required' => in_array(ucfirst('bank transfer'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_bank_transfer')
            ];
        }

        if (in_array(ucfirst('avatar'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.avatar',
                'required' => in_array(ucfirst('avatar'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_avatar')
            ];
        }

        if (in_array(ucfirst('commercial record image'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.commercial_image',
                'required' => in_array(ucfirst('commercial record image'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_commercial_image')
            ];
        }

        if (in_array(ucfirst('industrial license image'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.license_image',
                'required' => in_array(ucfirst('industrial license image'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_license_image')
            ];
        }

        if (in_array(ucfirst('tax card image'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.tax_image',
                'required' => in_array(ucfirst('tax card image'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_tax_image')
            ];
        }

        if (in_array(ucfirst('image id'), $seller_show_fields)) {
            $fields[] = [
                'name' => 'ms.image_id',
                'required' => in_array(ucfirst('image id'), $seller_required_fields),
                'placeholder' => $this->language->get('entry_seller_image_id')
            ];
        }

        // if (in_array(ucfirst('google map location'), $seller_show_fields)) {
        //     $fields[] = [
        //         'name' => 'ms.google_map_location',
        //         'required' => in_array(ucfirst('google map location'), $seller_required_fields),
        //         'placeholder' => $this->language->get('entry_seller_google_map_location')
        //     ];
        // }

        // seller address 
        $isAddressDataEnabled = $this->config->get('msconf_address_info');

        if ($isAddressDataEnabled) {
            // $fields[] = [
            //     'name' => 'addresses.1.country_id',
            //     'required' => true,
            //     'placeholder' => $this->language->get('entry_country'),
            // ];

            $formFields = array_filter($this->customerAccountFields['address'], function ($v, $k) {
                return $v >= 0;
            }, ARRAY_FILTER_USE_BOTH);

            foreach ($formFields as $key => $value) {
                $fields[] = [
                    'name' => "addresses.0.$key",
                    'required' => $value == 1,
                    'placeholder' => $this->language->get('entry_' . $key),
                ];
            }
        }

        return $fields;
    }

    private function buildSellerRedirectLink($identityCustomer = null, $customer = null, $seller = null)
    {

        if ($identityCustomer) {
            $identityType = $this->identity->isLoginByPhone() ? 'telephone' : 'email';
            $secondaryType = $this->identity->isLoginByPhone() ? 'email' : 'telephone';
            $this->session->data['guest_' . $identityType] = $identityCustomer[$identityType];
            $this->session->data['guest_' . $secondaryType] = $identityCustomer[$secondaryType];
            $this->session->data['guest_name'] = $identityCustomer['name'];
            $this->session->data['guest_dob'] = $identityCustomer['dob'];
            $this->session->data['guest_gender'] = $identityCustomer['gender'];
            $this->session->data['guest_expand_id'] = (int)$identityCustomer['id'];
        }

        $customer & ($this->session->data['guest_customer_id'] = (int)$customer['customer_id']);

        return $this->url->link('seller/register-seller', '', 'SSL');
    }


    private function mergeInputs(...$fields)
    {
        return call_user_func_array('array_merge', array_map(function ($item) {
            return array_filter($item, function ($v) {
                return (!!$v && !empty($v) || $v === "0" || $v === 0);
            });
        }, $fields));
    }

    /**
     * Check if current store enables new login functionalities
     *
     * @return boolean
     */
    private function identityAllowed(): bool
    {
        return defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList();
    }

    public static function arrayDot($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, static::arrayDot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }
    
    private function parseCustomerBasicData(array $customer, array $identityCustomer)
    {
        $fields =   [];
        $fields[] = ['customer_id' => $customer['customer_id']];
        $fields[] = ['expand_id' => $identityCustomer['id']];
        if (empty($customer['firstname']) && !empty($identityCustomer['name'])) $fields[] = ['firstname' => $identityCustomer['name']];
        if (empty($customer['telephone']) && !$this->identity->isLoginByPhone() && !empty($identityCustomer['telephone'])) $fields[] = ['telephone' => $identityCustomer['telephone']];
        if (empty($customer['email']) && $this->identity->isLoginByPhone() && !empty($identityCustomer['email'])) $fields[] = ['email' => $identityCustomer['email']];
        if (empty($customer['dob']) && !empty($identityCustomer['dob'])) $fields[] = ['dob' => $identityCustomer['dob']];
        if (empty($customer['gender']) && !empty($identityCustomer['gender'])) $fields[] = ['gender' => $identityCustomer['gender']];
        
        return $fields;
    }
}
