<?php

use ExpandCart\Foundation\Providers\Extension;
use ExpandCart\Foundation\Support\Facades\Filesystem;

class ControllerCheckoutCheckoutv2 extends Controller
{
    const VERSION = 2;
    
    const TEMPLATE_PREFIX = 'default/template/checkoutv2';

    /**
     * default checkout mode 3 steps
     */
    const DEFAULT_MODE = 3;
    
    private $settings = [];

    private $checkoutSettings = [];

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->language->load_json('checkout/cart');
        $this->language->load_json('checkout/checkoutv2');
        $this->language->load_json('account/identity', true);
        
        $this->checkoutSettings = $this->config->get('checkoutv2');

        $this->data['title']              = $this->language->get('heading_title');
        $this->data['logged']             = (int)$this->customer->isLogged();
        $this->data['version']            = self::VERSION;
        $this->data['checkout_mode']      = $this->checkoutSettings['checkout_mode'] ?: self::DEFAULT_MODE;
        $this->data['template_prefix']    = self::TEMPLATE_PREFIX;
        $this->data['cart_has_shipping']  = $this->cart->hasShipping() ? 1 : 0;
        $this->data['cart_has_products']  = $this->cart->hasProducts() ? 1 : 0;
    }

    public function index()
    {
        /**
         * Quick checkout app check
         * Check if Quick checkout is installed and enabled
         */
        if (
            !$this->identity->isStoreOnWhiteList() ||
            !defined('THREE_STEPS_CHECKOUT') ||
            (defined('THREE_STEPS_CHECKOUT') && (int)THREE_STEPS_CHECKOUT === 0) ||
            (defined('THREE_STEPS_CHECKOUT') && (int)THREE_STEPS_CHECKOUT === 1 && Extension::isInstalled('quickcheckout') && (int) $this->config->get('quickcheckout')['try_new_checkout'] === 0)
        ) {
            $this->redirect($this->url->link('checkout/checkout'));
            return;
        }

        // set currency & language
        $this->setCurrenciesData($this->request->post['currency_code']);
        $this->setLanguagesData($this->request->post['language_code']);
        $this->setMainColor();
        $this->setAbandonedCart();

        // set store icon
        if ($this->config->get('config_icon')) {
            $this->data['store_icon'] = Filesystem::getUrl('image/' . $this->config->get('config_icon'));
        } else {
            $this->data['store_icon'] = '';
        }

        // set store logo
        if ($this->config->get('config_logo')) {
            $this->data['store_logo'] = Filesystem::getUrl('image/' . $this->config->get('config_logo'));
        } else {
            $this->data['store_logo'] = '';
        }

        $this->data['store_name'] = $this->config->get('config_name');

        if (!empty($this->request->get['token'])) {
            $encodedtoken = $this->request->get['token'];
            $this->load->model('account/api');
            $this->model_account_api->restoreSession($encodedtoken);

            $this->session->data['mobile_api_token'] = $encodedtoken;
        }

        if ($this->request->get['ismobile'] == "1") {
            $this->session->data['ismobile'] = "1";
        }

        $products = $this->cart->getProducts();
        // Knawat Drop shippment api
        // Syncornize product data in checkout.
        $this->load->model('setting/setting');
        if ($this->model_setting_setting->getSetting('knawat_dropshipping_status')['status']) {
            foreach ($products as $product) {

                $app_dir = str_replace('system/', 'expandish/', DIR_SYSTEM);

                require_once $app_dir . "controller/module/knawat_dropshipping.php";
                $this->controller_module_knawat_dropshipping = new ControllerModuleKnawatDropshipping($this->registry);
                $this->controller_module_knawat_dropshipping->before_add_to_cart($product['product_id']);
            }
        }

        // Validate minimum quantity requirements.
        foreach ($products as $product) {
            $product_total = 0;

            foreach ($products as $product_2) {
                if ($product_2['product_id'] == $product['product_id']) {
                    $product_total += $product_2['quantity'];
                }
            }

            if ($product['minimum'] > $product_total) {
                $this->redirect($this->url->link('checkout/cart'));
            }

            if ($this->config->get('enable_order_maximum_quantity') && $product['maximum'] > 0 && $product['maximum'] < $product_total) {
                $this->redirect($this->url->link('checkout/cart'));
            }
        }

        if ($this->MsLoader->isInstalled()) {
            $cart_sellers = [];
            foreach ($products as &$product) {
                $product['seller'] = $this->MsLoader->MsSeller->getSellerByProductId($product['product_id']);

                if ($product['seller']['minimum_order'] > 0) {
                    if (!isset($cart_sellers[$product['seller']['seller_id']])) {
                        $cart_sellers[$product['seller']['seller_id']] = $product['seller'];
                    }

                    $cart_sellers[$product['seller']['seller_id']]['total_cart'] += $product['total'];

                    if ($this->config->get('msconf_enable_seller_name_in_cart_view') == 1) {
                        $product['seller_name'] = $product['seller']['nickname'];
                    }
                }
            }

            foreach ($products as &$product) {
                if (
                    $product['seller']['minimum_order'] > 0 &&
                    $cart_sellers[$product['seller']['seller_id']]['total_cart'] < $product['seller']['minimum_order']
                ) {
                    $this->redirect($this->url->link('checkout/cart'));
                }
            }
        }

        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

        if ($queryRewardPointInstalled->num_rows) {
            $this->load->model('rewardpoints/spendingrule');
            $this->load->model('rewardpoints/shoppingcartrule');
            $this->model_rewardpoints_spendingrule->getSpendingPoints();
            $this->model_rewardpoints_shoppingcartrule->getShoppingCartPoints();
        }

        $this->setLoginScript();

        $this->cache->delete('quickcheckout');
        unset($this->session->data['qc_settings']);

        if (!$this->validateCheckout()) {
            $this->redirect($this->url->link('checkout/cart'));
        }

        if (!isset($this->session->data['order_id'])) {
            unset($this->session->data['shipping_method']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['confirm']['agree']);
        }

        $this->applyCustomerGroupCoupon();
        $this->prepareSectionAddress();
        $this->prepareContactInfoDetails();
        $this->prepareShippingData();
        $this->preparePaymentData();
        $this->prepareOrderSummaryData();
        $this->prepareOrder();
        $this->prepareCurrentStep();

        $this->template = self::TEMPLATE_PREFIX . '/index.expand';
        $this->response->setOutput($this->render_ecwig());
    }

    public function validateCoupon()
    {
        $json = array();
        $this->load->model('checkout/coupon');

        if (!empty($this->request->post['coupon'])) {
            $coupon_info = $this->model_checkout_coupon->getCoupon($this->request->post['coupon']);
            if (!$coupon_info || $coupon_info['status'] == false) {
                $json['errors'] = ['coupon' => $this->language->get('error_coupon')];
            }

            if ($coupon_info &&  $coupon_info['status'] != false  && $this->cart->getSubTotal() < $coupon_info['minimum_to_apply']) {
                $json['errors'] = ['coupon' => sprintf(
                    $this->language->get('error_coupon_less_than_minimum'),
                    $this->currency->format($coupon_info['minimum_to_apply'])
                )];
            }
        } else {
            $json['errors'] = ['coupon' => $this->language->get('error_coupon')];
        }

        if (!isset($json['errors'])) {
            $this->session->data['coupon'] = $this->request->post['coupon'];
            $this->session->data['buyer_subscription_plan_coupon'] = $this->request->post['buyer_subscription_plan_coupon'];
            $json['success'] = true;
        } else {
            $json['success'] = false;
            // If not a valid coupon added by buyer_subscription_plan app
            if ($this->request->post['buyer_subscription_plan_coupon']) unset($this->request->post['coupon']);
            // remove coupon from session
            unset($this->session->data['coupon'], $this->session->data['buyer_subscription_plan_coupon']);
        }

        $this->response->setOutput(json_encode($json));
    }

    public function validateReward()
    {
        $this->load->model('rewardpoints/helper');
        $this->load->model('rewardpoints/spendingrule');
        $this->language->load_json('checkout/cart');
        $json = array();
        $points = $this->customer->getRewardPoints();
        $price_total = 0;

        // get the total price in order
        foreach ($this->cart->getProducts() as $product) {
            $price_total += $product['total'];
        }

        $points_total = 0;

        if (isset($this->request->post['reward']) && (int) $this->request->post['reward'] >= 0) {

            $rules = $this->model_rewardpoints_spendingrule->getRules();
            if (isset($rules)) {
                foreach ($rules as   $role) {
                    $conditions = unserialize(base64_decode($role['conditions_serialized']));

                    foreach ($conditions as $condition) {
                        foreach ($condition as $value) {
                            if (isset($value['type']) && str_contains('sale/reward_points/rule|subtotal-text', $value['type'])) {
                                $result = $price_total . $value['operator'] . $value['value'];
                                $result = htmlspecialchars_decode($result);
                                $result = eval('return ' . $result . ';');
                                if ($result == FALSE) {
                                    $errorRule = str_replace('$', htmlspecialchars_decode($value['operator']), $this->language->get('error_spending_rule'));
                                    $json['errors'] = ['reward' => sprintf($errorRule, $value['value'])];
                                }
                            }
                        }
                    }
                }
            }
            //check if allow_no_product_points_spending option exists
            if (!$this->config->get('allow_no_product_points_spending')) {
                foreach ($this->cart->getProducts() as $product) {
                    if ($product['points']) {
                        $points_total += $product['points'];
                    }
                }
            } else {
                $rule = explode('/', $this->config->get('currency_exchange_rate'));

                foreach ($this->cart->getProducts() as $product) {
                    $points_total += $this->model_rewardpoints_helper->exchangeMoneyToPoint($product['total']);
                }
            }

            if ($this->request->post['reward'] > $points) {
                $json['errors'] = ['reward' => sprintf($this->language->get('error_points'), $this->request->post['reward'])];
            }

            if ($this->request->post['reward'] > $points_total) {
                $json['errors'] = ['reward' => sprintf($this->language->get('error_maximum'), $points_total)];
            }
        } else {
            $json['errors'] = ['reward' => $this->language->get('error_reward')];
        }

        if (!isset($json['errors'])) {
            $this->session->data['reward'] = abs($this->request->post['reward']);
            $json['success'] = true;
        } else {
            $json['success'] = !true;
            unset($this->session->data['reward']);
            // $this->session->data['reward_error'] = $json['error'];
        }

        $this->response->setOutput(json_encode($json));
    }

    public function validateInformation()
    {
        if (!empty($this->session->data['subscription'])) {
            $this->response->setOutput(json_encode(['success' => true,]));
            return;
        }

        $data = $this->request->post;

        $validator = $this->validateFormAddress($data);

        if ($validator !== true) {
            $this->response->setOutput(json_encode(['success' => false, 'errors' => $validator]));
            return;
        }

        $data = $this->storeAddress($data);

        $this->response->setOutput(json_encode(['success' => true, 'data' => $this->setExtraParamToAddress($data)]));
    }

    public function validateShipping()
    {

        if (!$this->data['cart_has_shipping']) {
            $this->response->setOutput(json_encode(['success' => true,]));
            return;
        }

        $quote = $this->request->post['shipping_method'];
        $methods = $this->session->data['shipping_methods'] ?: [];

        if (!$quote || empty($quote)) {
            $this->response->setOutput(json_encode(['success' => false, 'errors' => ['shipping_method' => $this->language->get('required_input_shipping_method')]]));
            return;
        }

        $quote = explode('.', $quote);

        $this->session->data['shipping_method'] = [];

        if (isset($methods[$quote[0]]['quote'][$quote[1]])) {
            $this->session->data['shipping_method'] = $methods[$quote[0]]['quote'][$quote[1]];
        }else{
            $this->session->data['shipping_method'] = $this->session->data['default_shipping_method'];
        }

        if (empty($this->session->data['shipping_method'])) {
            $this->response->setOutput(json_encode(['success' => false, 'errors' => ['shipping_method' => $this->language->get('required_input_shipping_method')]]));
            return;
        }

        $this->updateOrder();


        $this->response->setOutput(json_encode(['success' => true, 'data' => $this->session->data['shipping_method']]));
        return;

        $this->response->setOutput(json_encode(['success' => true,]));
    }

    public function validatePayment()
    {
        $comment = $this->request->post['comment'];
        $orderConfirmed = $this->request->post['order_agree_confirmed'];

        $method = $this->request->post['payment_method'];
        $methods = $this->session->data['payment_methods'] ?: [];

        if (!$method || empty($method)) {
            $this->response->setOutput(json_encode(['success' => false, 'errors' => ['payment_method' => $this->language->get('required_input_payment_method')]]));
            return;
        }

        $this->session->data['payment_method'] = [];

        if (isset($methods[$method])) {
            $this->session->data['payment_method'] = $methods[$method];
        }

        if (empty($this->session->data['payment_method'])) {
            $this->response->setOutput(json_encode(['success' => false, 'errors' => ['payment_method' => $this->language->get('required_input_payment_method')]]));
            return;
        }

        $this->session->data['comment'] = htmlspecialchars($comment);

        // if ((int)$this->checkoutSettings['order_agree']) {
        //     if (!(int)$orderConfirmed === 1) {
        //         $this->response->setOutput(json_encode(['success' => false, 'errors' => ['order_agree_confirmed' => $this->language->get('required_input_order_agree_confirmed')]]));
        //         return;
        //     } else {
        $this->session->data['confirm']['agree'] = (int)$orderConfirmed === 1 ? 1 : 0;
        //     }
        // }

        $this->updateOrder();

        $this->response->setOutput(json_encode(['success' => true,]));
        return;
    }

    private function validateCheckout()
    {
        if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers']) && empty($this->session->data['subscription'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout')))
            return false;

        return true;
    }

    private function validateFormAddress(array &$data)
    {

        $errors = [];

        $formFields = $this->config->get('config_customer_fields')['address'];

        if ((!isset($data['firstname']) || $data['firstname'] == ''))
            $errors['firstname'] = $this->language->get('required_input_contact_name');

        if ($formFields['country_id'] == 1 && (!isset($data['country_id']) || $data['country_id'] == '' || $data['country_id'] == '0' || !is_numeric($data['country_id'])))
            $errors['country_id'] = $this->language->get('required_input_country_id');

        if ($formFields['zone_id'] == 1 && (!isset($data['zone_id']) || $data['zone_id'] == '' || $data['zone_id'] == '0' || !is_numeric($data['zone_id'])))
            $errors['zone_id'] = $this->language->get('required_input_zone_id');

        if ($formFields['area_id'] == 1 && (!isset($data['area_id']) || $data['area_id'] == '' || $data['area_id'] == '0' || !is_numeric($data['area_id'])))
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
            }/* elseif (utf8_strlen($data['address_2']) < 3 || utf8_strlen($data['address_2']) > 128) {
                $errors['address_2'] = $this->language->get('invalid_input_address_2');
            }*/
        }

        if ($formFields['telephone'] == 1 && !is_simple_valid_phone($data['telephone'])) {
            $errors['telephone'] = $this->language->get('invalid_input_contact_phone');
        }

        if ($formFields['postcode'] == 1 && (!isset($data['postcode']) || $data['postcode'] == ''))
            $errors['postcode'] = $this->language->get('required_input_postcode');

        return empty($errors) ? true : $errors;
    }

    public function getZones()
    {
        $json = array();

        $this->load->model('localisation/country');

        $country_info = $this->model_localisation_country->getCountry($this->request->post['country']);

        if ($country_info) {
            $this->load->model('localisation/zone');

            $json = array(
                'country_id'        => $country_info['country_id'],
                'name'              => $country_info['name'],
                'iso_code_2'        => $country_info['iso_code_2'],
                'iso_code_3'        => $country_info['iso_code_3'],
                'address_format'    => $country_info['address_format'],
                'phonecode'            => $country_info['phonecode'],
                'postcode_required' => $country_info['postcode_required'],
                'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->post['country']),
                'status'            => $country_info['status']
            );
        }

        $this->response->setOutput(json_encode($json));
    }

    public function getAreas()
    {
        $json = array();

        $this->load->model('localisation/area');

        $json = array(
            'area' => $this->model_localisation_area->getAreaByZoneId($this->request->post['zone'])
        );

        $this->response->setOutput(json_encode($json));
    }

    public function updateShippingView()
    {
        if ((int) $this->data['checkout_mode'] === 1) {
            if (isset($this->request->post['country_id'])) {
                $this->session->data['payment_address']['country_id'] = $this->request->post['country_id'];
                $this->session->data['shipping_address']['country_id'] = $this->request->post['country_id'];
            }
            
            if (isset($this->request->post['zone_id'])) {
                $this->session->data['payment_address']['zone_id'] = $this->request->post['zone_id'];
                $this->session->data['shipping_address']['zone_id'] = $this->request->post['zone_id'];
            }
            
            if (isset($this->request->post['area_id'])) {
                $this->session->data['payment_address']['area_id'] = $this->request->post['area_id'];
                $this->session->data['shipping_address']['area_id'] = $this->request->post['area_id'];
            }
        }
        
        $json = [
            'success' => true,
            'data' => $this->shippingView(),
        ];

        $this->response->setOutput(json_encode($json));
    }

    public function updatePaymentView()
    {
        $json = [
            'success' => true,
            'data' => $this->paymentView(),
        ];

        $this->response->setOutput(json_encode($json));
    }

    public function updateOrderSummaryView()
    {
        $json = [
            'success' => true,
            'data' => $this->orderSummaryView(),
        ];

        $this->response->setOutput(json_encode($json));
    }

    private function shippingView()
    {
        if (!$this->data['cart_has_shipping']) return;

        $this->prepareShippingData();
        $this->template = self::TEMPLATE_PREFIX . '/_shipping.expand';
        return $this->render_ecwig();
    }

    private function paymentView()
    {
        $this->preparePaymentData();
        $this->template = self::TEMPLATE_PREFIX . '/_payment.expand';
        return $this->render_ecwig();
    }

    private function orderSummaryView()
    {
        $this->prepareOrderSummaryData();
        $this->template = self::TEMPLATE_PREFIX . '/_summary.expand';
        return $this->render_ecwig();
    }

    private function prepareSectionAddress()
    {
        $addresses = [];
        
        if($this->data['logged'] && isset($this->session->data['guest_addresses_book'])) {
            foreach ($this->session->data['guest_addresses_book'] as $key => $value) {
                unset($value['address_id']);
                $address = $this->saveAddress($value);
                
                if ($value['related_to_order'] && isset($this->session->data['payment_address'])) {
                    $this->session->data['payment_address']['address_id'] = $address['address_id'];
                }
            }
            unset($this->session->data['guest_addresses_book']);
        }

        if ($this->data['logged']) {
            $this->load->model('account/address');
            $addresses = array_values($this->model_account_address->getAddresses());
        } else {
            $addresses = isset($this->session->data['guest_addresses_book']) ? array_values($this->session->data['guest_addresses_book']) : [];
        }

        $this->load->model('localisation/country');

        $addresses = array_map(array($this, 'setExtraParamToAddress'), $addresses);

        $default_address = $this->session->data['payment_address'];

        unset($this->session->data['payment_address']);
        unset($this->session->data['shipping_address']);

        foreach ($addresses as &$addr) {
            $country_data = $this->countryData($addr['country_id'], $addr['zone_id']);
            if (is_array($country_data)) $addr = array_merge($addr, $country_data);
            $addr['phonecode']        = $country_data['phonecode'];
            $addr['flag']             = strtolower($country_data['iso_code_2']);
            $addr['country_code']     = $country_data['iso_code_2'] != "" ? $country_data['iso_code_2'] : $country_data['iso_code_3'];
            $addr['related_to_order'] = 0;

            if (isset($addr['address_id']) && $default_address && $addr['address_id'] == $default_address['address_id']) {
                $addr['related_to_order'] = 1;
                $default_address          = $addr;
            }
        }

        if (count($addresses) === 1) {
            $addresses[0]['related_to_order'] = 1;
            $default_address                  = $addresses[0];
        }

        if (
            $this->data['logged'] && count($addresses) > 1 &&
            (!$default_address || !in_array($default_address['address_id'], array_column($addresses, 'address_id')))
        ) {
            $default_address = null;
            $query = $this->db->query("SELECT address_id from `" . DB_PREFIX . "order` WHERE customer_id = " . (int)$this->data['logged']
                . " AND address_id IN (" . implode(',', array_column($addresses, 'address_id')) . ") AND order_status_id != 0 ORDER BY order_id DESC LIMIT 1");
            if ($query->num_rows) {
                $def_addr_id = (int)$query->row['address_id'];
                foreach ($addresses as &$addr) {
                    if (isset($addr['address_id']) && $addr['address_id']  == $def_addr_id) {
                        $addr['related_to_order'] = 1;
                        $default_address          = $addr;
                        break;
                    }
                }
            }
        }

        // finally put default addr to session
        if ($default_address) {
            $this->tax->setPaymentAddress($default_address['country_id'], $default_address['zone_id']);
            $this->session->data['payment_address'] = $default_address;
            $this->session->data['shipping_address'] = $default_address;
        }

        $default_address = $this->session->data['payment_address'];

        $countries = $this->model_localisation_country->getCountries();

        $enable_send_as_gift = $this->checkoutSettings['ship_as_gift'] ?? 1;

        // buyer subscription can't send order as a gift
        if (!empty($this->session->data['subscription'])) {
            $enable_send_as_gift = 0;
            $this->session->data['send_as_gift'] = 0;
        }

        // Google map integration 
        $this->load->model("module/google_map");
        $google_map = $this->model_module_google_map->getSettings();

        if ($google_map && $google_map['status'] == 1) {
            $google_map['local'] = [
                'text_enter_your_location' => $this->language->get('text_enter_your_location'),
                'text_detect_location' => $this->language->get('text_detect_location'),
                'text_confirm_location' => $this->language->get('text_confirm_location'),
            ];
        }

        // form registration & address fields
        $customer_fields = $this->config->get('config_customer_fields');

        $hide_section_address = !empty($this->session->data['subscription']);

        $this->data['addresses'] = $addresses;
        $this->data['countries'] = $countries;
        $this->data['default_address'] = $default_address;
        $this->data['send_as_gift'] = $this->session->data['send_as_gift'] ?: 0;
        $this->data['enable_send_as_gift'] = $enable_send_as_gift;
        $this->data['google_map'] = $google_map;
        $this->data['customer_fields'] = $customer_fields;
        $this->data['hide_section_address'] = $hide_section_address;
        return compact(
            'addresses',
            'countries',
            'default_address',
            'enable_send_as_gift',
            'google_map',
            'customer_fields',
            'hide_section_address'
        );
    }

    private function prepareContactInfoDetails()
    {
        $contact_name = $contact_phone = "";

        $default_address = $this->session->data['payment_address'];

        if ($default_address) {
            $contact_name = $default_address['firstname'];
            $contact_phone = $default_address['telephone'];
        }

        if ($this->data['logged'] && empty($contact_name)) {
            $contact_name = $this->customer->getFirstName();
        }

        if ($this->data['logged'] && empty($contact_phone)) {
            $contact_phone = $this->customer->getTelephone();
        }

        $this->data['contact_name'] = $contact_name;
        $this->data['contact_phone'] = $contact_phone;

        return compact('contact_name', 'contact_phone');
    }

    private function prepareShippingData()
    {
        if (!$this->data['cart_has_shipping']) return;

        $shipping_address = $this->session->data['shipping_address'] ?? [];

        $shipping_methods = [];

        $shipping_method = $this->session->data['shipping_method'] ?? [];

        $this->load->model('setting/extension');

        $results = $this->model_setting_extension->getExtensions('shipping');

        foreach ($results as $result) {

            $settings = $this->config->get($result['code']);

            if ($settings && is_array($settings)) {
                $status = $settings['status'];
            } else {
                $status = $this->config->get($result['code'] . '_status');
            }

            if ($status == 1) {
                $this->load->model('shipping/' . $result['code']);

                $quote = $this->{'model_shipping_' . $result['code']}->getQuote($shipping_address);

                if( $quote['code'] == 'hitdhlexpress' ){
                    foreach ($quote['quote'] as $shipping) {
                        $codes = explode('.',$shipping['code']);
                        $quote['quote'][$codes[1]]['code'] = 'dhl_express.'.$codes[1];
                    }
                }

                if ($quote) {
                    $shipping_methods[$result['code']] = array(
                        'title'      => $quote['title'],
                        'quote'      => $quote['quote'],
                        'sort_order' => $quote['sort_order'],
                        'error'      => $quote['error']
                    );
                }
            }
        }

        $sort_order = array();

        foreach ($shipping_methods as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $shipping_methods);

        $this->data['shipping_methods'] = $this->session->data['shipping_methods'] = $shipping_methods;


        // check shipping slot app
        $this->load->model('module/delivery_slot/settings');
        $this->data['delivery_slot_status'] = $this->model_module_delivery_slot_settings->isActive();
        $this->data['delivery_slot_required'] = $this->model_module_delivery_slot_settings->isRequired();
        $this->data['delivery_slot_cut_off'] = $this->model_module_delivery_slot_settings->isCutOff();
        $ds_settings = $this->config->get('delivery_slot');

        if ($this->data['delivery_slot_status']) {
            $this->data['delivery_slot_calendar_type'] = $ds_settings['delivery_slot_calendar_type'] ?? 'default';
            if ($this->data['delivery_slot_cut_off']) {

                $time_zone = $this->config->get('config_timezone');

                $dateTime = new DateTime('now', new DateTimeZone($time_zone));
                $current_time =  $dateTime->format("h:i A");
                if ($current_time > $ds_settings['slot_time_start'] && $current_time < $ds_settings['slot_time_end']) {
                    $this->data['day_index'] = $ds_settings['slot_day_index'];
                } else {
                    $this->data['day_index'] = $ds_settings['slot_other_time'];
                }
            }

            $this->data['slot_max_day'] = isset($ds_settings['slot_max_day']) ? $ds_settings['slot_max_day'] : 0;
        }

        unset($this->session->data['shipping_method']);

        if (!empty($shipping_method)) {
            $quote =  explode('.', $shipping_method['code']);
            if (isset($shipping_methods[$quote[0]]['quote'][$quote[1]])) {
                $this->session->data['shipping_method'] = $shipping_methods[$quote[0]]['quote'][$quote[1]];
            }
        }

        $shipping_method = $this->data['shipping_method'] = $this->session->data['shipping_method'];

        $this->data['no_shipping_msg'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));

        return compact('shipping_methods', 'shipping_method');
    }

    private function preparePaymentData()
    {
        $payment_address = $this->session->data['payment_address'] ?? [];
        $total_data = $total = $taxes = null;

        $this->totalData($total_data, $total, $taxes);

        $payment_methods = array();

        $payment_method = $this->session->data['payment_method'] ?? [];

        $this->load->model('setting/extension');

        $msIsInstalled = $this->MsLoader->isInstalled();

        if ($msIsInstalled) {
            $productId = array_column($this->cart->getProducts(), 'product_id');
            $seller_id = $this->MsLoader->MsProduct->getSellerId($productId[0]);

            if ($this->config->get('msconf_allowed_payment_methods') == 1 && $seller_id) {
                $this->load->model('seller/allowed_payment_methods');
                $allowedPaymentMethodsForMS = $this->model_seller_allowed_payment_methods->getSellerAllowedPaymentMethods($seller_id);
            }
        }

        $results = $this->model_setting_extension->getExtensions('payment');

        if ($msIsInstalled && $this->config->get('msconf_enable_seller_independent_payments') == 1) {

            if ($seller_id > 0) {
                $seller = $this->MsLoader->MsSeller->getSeller($seller_id);

                $this->session->data['ms_independent_payments']['status'] = true;
                $this->session->data['ms_independent_payments']['bank_transfer'] = $seller['ms.bank_transfer'];
                $this->session->data['ms_independent_payments']['paypal'] = $seller['ms.paypal'];

                $results = $this->MsLoader->MsSeller->getPaymentMethods(
                    $seller_id
                );
            }
        } else {
            unset($this->session->data['ms_independent_payments']);
        }

        // Load custom fees for payment method module
        $this->load->model('module/custom_fees_for_payment_method');

        if ($this->model_module_custom_fees_for_payment_method->is_module_installed()) {
            $cffpm = true;
            $cart_catgs = $this->cart->getCategoriesIds();
            $total_sum = (float) $total;
        }

        foreach ($results as $result) {
            if ($result['code'] == "pp_plus") {
                $this->load->model('setting/setting');
                $settings = $this->model_setting_setting->getSetting('payment')[$result['code']];
            } else {
                $settings = $this->config->get($result['code']);
            }

            if ($settings && is_array($settings) == true) {
                $status = $settings['status'];
            } else {
                $status = $this->config->get($result['code'] . '_status');
            }

            if (isset($allowedPaymentMethodsForMS)) {
                if (!in_array($result['code'], $allowedPaymentMethodsForMS)) {
                    continue;
                }
            }

            if ($status) {
                $this->load->model('payment/' . $result['code']);

                $method = $this->{'model_payment_' . $result['code']}->getMethod($payment_address, $total);

                if ($method) {
                    // if cffpm is set and true.
                    if (isset($cffpm) && $cffpm === true) {

                        $cffpm_settings_for_method = $this->model_module_custom_fees_for_payment_method->get_setting($result['code']);
                        $show_range = explode(':', $cffpm_settings_for_method['show_range']);
                        $show_range_min = (float) isset($show_range[0]) ? $show_range[0] : 0;
                        $show_range_max = isset($show_range[1]) ? $show_range[1] : 'no_max';

                        if ($show_range_max !== 'no_max') {
                            $show_range_max = (float) $show_range[1];
                        }

                        //Check matching categories
                        $catgs_match = true;
                        if ($cffpm_settings_for_method['catgs_ids'] && $cart_catgs) {
                            $pymt_catgs      = unserialize($cffpm_settings_for_method['catgs_ids']);
                            $pymt_catgs_case = $cffpm_settings_for_method['catgs_case'];

                            switch ($pymt_catgs_case) {

                                    //// Payment Category case is 'in'
                                case "in":
                                    foreach ($cart_catgs as $cart_catg) {
                                        $catgs_match = false;
                                        foreach ($cart_catg as $cart_catg_) {
                                            //// Payment Category case is 'in'
                                            if (in_array($cart_catg_, $pymt_catgs)) {
                                                $catgs_match = true;
                                                break;
                                            }
                                        }
                                        if (!$catgs_match)
                                            break;
                                    }
                                    /*foreach ($cart_catgs as $cart_catg) {
                                        //// Payment Category case is 'in'
                                        if(!in_array($cart_catg, $pymt_catgs)){
                                            $catgs_match = false;
                                            break;
                                        }
                                    }*/
                                    /*foreach ($pymt_catgs as $pymt_catg) {
                                        //// Payment Category case is 'in'
                                        if(!in_array($pymt_catg, $cart_catgs)){
                                            $catgs_match = false;
                                            break;
                                        }
                                    }*/
                                    break;

                                    //// Payment Category case is 'notin'
                                case "notin":
                                    foreach ($cart_catgs as $cart_catg) {
                                        $catgs_match = false;
                                        foreach ($cart_catg as $cart_catg_) {
                                            //// Payment Category case is 'in'
                                            if (!in_array($cart_catg_, $pymt_catgs)) {
                                                $catgs_match = true;
                                                break;
                                            }
                                        }
                                        if (!$catgs_match)
                                            break;
                                    }
                                    /*foreach ($cart_catgs as $cart_catg) {
                                        if(in_array($cart_catg, $pymt_catgs)){
                                            $catgs_match = false;
                                            break;
                                        }
                                    }*/
                                    /*foreach ($pymt_catgs as $pymt_catg) {
                                        if(in_array($pymt_catg, $cart_catgs)){
                                            $catgs_match = false;
                                            break;
                                        }
                                    }*/
                                    break;

                                default:
                                    $catgs_match = true;
                            }
                        }
                        ////

                        if ($total_sum >= $show_range_min && ($show_range_max === 'no_max' || $total_sum <= $show_range_max) && $catgs_match) {
                            $payment_methods[$result['code']] = $method;
                        }
                    } else {
                        $payment_methods[$result['code']] = $method;
                    }
                }
            }
        }

        $sort_order = array();
        foreach ($payment_methods as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }
        array_multisort($sort_order, SORT_ASC, $payment_methods);

        if (empty($total) || $total == 0) {
            $this->load->model('payment/free_checkout');
            $payment_methods['free_checkout'] = $this->model_payment_free_checkout->getMethod($payment_address, $total);
        }

        // Remove Cash on Delivery in case of paying for subscription plan...
        if (!empty($this->session->data['subscription']) && isset($payment_methods['cod'])) {
            unset($payment_methods['cod']);
        }

        $this->data['payment_methods'] = $this->session->data['payment_methods'] = $payment_methods;

        $this->session->data['payment_method'] = [];

        if (!empty($payment_method)) {
            $code = $payment_method['code'];
            if (isset($payment_methods[$code])) {
                $this->session->data['payment_method'] = $payment_methods[$code];
            }
        }

        $payment_method = $this->data['payment_method'] = $this->session->data['payment_method'];

        $this->data['no_payment_msg'] = sprintf($this->language->get('error_no_payment'), $this->url->link('information/contact'));

        if (isset($payment_method['code']) && $payment_method['code']) {
            $this->data['confirm_payment'] = $this->getChild('payment/' . $payment_method['code']);
            $this->data['confirm_btn_type'] = $payment_method['confirm_btn_type'];
        }

        $this->data['comment'] = (isset($this->session->data['comment'])) ? $this->session->data['comment'] : '';

        $this->data['order_agree'] = (int)$this->checkoutSettings['order_agree'];
        $this->data['order_agree_confirmed'] = (int)(isset($this->session->data['confirm']['agree']) && $this->session->data['confirm']['agree'] == '1');


        $this->load->model('catalog/information');
        $information_info = $this->model_catalog_information->getInformation($this->checkoutSettings['information_page_id']);
        $this->data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information', 'information_id=' . $information_info['information_id'], 'SSL'), $information_info['title'], $information_info['title']);

        return compact('payment_methods', 'payment_method');
    }

    private function prepareOrderSummaryData()
    {
        $data = [];
        $cart_products = $this->cart->getProducts();
        $total_data = $total = $taxes = null;
        $this->load->model('rewardpoints/helper');
        $this->load->model('tool/image');
        if ($cart_products || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription'])) {

            $this->totalData($total_data, $total, $taxes);

            $points = $this->customer->getRewardPoints();

            $points_total = 0;


            //check if allow_no_product_points_spending option exists
            if (!$this->config->get('allow_no_product_points_spending')) {
                foreach ($cart_products as $product) {
                    if (isset($product['points']) && $product['points']) {
                        $points_total += $product['points'];
                    }
                }
            } else {
                $rule = explode('/', $this->config->get('currency_exchange_rate'));

                foreach ($cart_products as $product) {
                    $points_total += $this->model_rewardpoints_helper->exchangePointToMoney($product['total']);
                }
            }

            if ($this->session->data['min_order'] === false) {
                $data['error']['error_min_order'] = sprintf($this->settings['general']['min_order']['text'][(int)$this->config->get('config_language_id')], $this->currency->format($this->settings['general']['min_order']['value']));
            }

            if ($this->session->data['min_quantity'] === false) {
                $this->load->model('localisation/language');
                $lang_id = $this->model_localisation_language->getLanguageByCode($this->session->data['language']);
                $lang_id = $lang_id['language_id'];
                $data['error']['error_min_quantity'] = sprintf($this->settings['general']['min_quantity']['text'][$lang_id], $this->settings['general']['min_quantity']['value']);
            }

            if (!$this->cart->hasStock() && $this->config->get('config_stock_warning')) {
                if (!$this->config->get('config_stock_checkout')) {
                    $data['error']['error_stock'] = $this->language->get('error_stock');
                }
            }

            if ($this->session->data['min_quantity_product'] === false) {
                $this->language->load_json('checkout/cart');
                $data['error']['error_min_order'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
            }

            if ($this->config->get('enable_order_maximum_quantity') && $this->session->data['max_quantity_product'] === false) {
                $this->language->load_json('checkout/cart');
                $data['error']['error_max_order'] = sprintf($this->language->get('error_maximum_quantity'), $product['name'], $product['maximum']);
            }


            $data['products'] = array();


            foreach ($this->session->data['cart'] as $key => $value) {
                $this->cart->update($key, $value);
            }

            $pd_custom_total_price = 0;
            $this->language->load_json('module/product_designer');

            //warehouses
            $warehouse_setting = $this->config->get('warehouses');
            $warehouses_shipping_setting = $this->config->get('warehouses_shipping');
            $data['warehouses'] = false;

            if (
                $warehouse_setting &&
                $warehouse_setting['status'] == 1 &&
                $warehouses_shipping_setting &&
                $warehouses_shipping_setting['status'] == 1 &&
                isset($this->session->data['warehouses_products']) &&
                count($this->session->data['warehouses_products']['products']) > 0
            ) {
                if (count($this->session->data['warehouses_products']['missing_products']))
                    $this->redirect($this->url->link('checkout/cart'));

                $cartProducts = $this->session->data['warehouses_products']['products'];
                $combined_wrs_costs = $this->session->data['warehouses_products']['wrs_costs'];
                $data['wrs_names']  = $this->session->data['warehouses_products']['wrs_name'];
                $data['wrs_duration']  = $this->session->data['warehouses_products']['wrs_duration'];
                $data['warehouses'] = true;
            } else {
                $cartProducts = $this->cart->getProducts();
            }

            $data['show_quantity_error'] = false;

            //New v 23/02/2020
            $config_customer_price    = $this->config->get('config_customer_price');
            $customer_is_logged       = $this->data['logged'];
            $config_image_cart_width  = $this->config->get('config_image_cart_width');
            $config_image_cart_height = $this->config->get('config_image_cart_height');
            $config_tax               = $this->config->get('config_tax');
            $config_stock_checkout    = $this->config->get('config_stock_checkout');
            $config_stock_warning     = $this->config->get('config_stock_warning');

            /// Prize draw app
            $this->load->model('module/prize_draw');
            $prize_draw_status = $this->model_module_prize_draw->isActive();

            $products_count = 0;

            foreach ($cartProducts as $product) {
                $pd_application = 0;

                //Prize draw app
                $prize_draw = null;
                $consumed_percentage = 0;
                if ($prize_draw_status) {
                    $prize_draw = $this->model_module_prize_draw->getProductPrize($product['product_id']) ?? null;
                    if ($prize_draw['image'])
                        $prize_draw['image'] = $this->model_tool_image->resize($prize_draw['image'], 500, 500);

                    $ordered_count = $this->load->model_module_prize_draw->getOrderedCount($product['product_id']);
                    $consumed_percentage = (((int)$ordered_count * 100) / (int)$product['quantity']) ?? 0;
                }

                $option_data = array();
                foreach ($product['option'] as $option) {
                    if ($option['type'] != 'file') {
                        $value = $option['option_value'];
                    } else {
                        $filename = $this->encryption->decrypt($option['option_value']);

                        $value = utf8_substr($filename, 0, utf8_strrpos($filename, '.'));
                    }
                    if ($option['type'] == 'pd_application') {
                        $pd_application = $option['custom_id'];
                        $pd_tshirtId = $option['tshirtId'];
                    } else {
                        $option_data[] = array(
                            'name'  => $option['name'],
                            'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                        );
                    }
                }

                if ($pd_application) {
                    $product['image'] = 'modules/pd_images/merge_image/' . $pd_application . '__front.png';

                    $pdOpts = $this->db->query(
                        'select * from ' . DB_PREFIX . 'tshirtdesign where did="' . $pd_tshirtId . '"'
                    );
                    if ($pdOpts->num_rows > 0) {
                        $pd_front = json_decode(html_entity_decode($pdOpts->row['front_info']), true)[0]['custom_price'];
                        $pd_back = json_decode(html_entity_decode($pdOpts->row['back_info']), true)[0]['custom_price'];
                    } else {
                        $pd_front = 0;
                        $pd_back = 0;
                    }
                }

                if ($product['image']) {
                    $thumb = $this->model_tool_image->resize($product['image'], $config_image_cart_width, $config_image_cart_height);
                } else {
                    $thumb = '';
                }

                if ($product['image']) {
                    $image = $this->model_tool_image->resize($product['image'], $this->settings['general']['cart_image_size']['width'], $this->settings['general']['cart_image_size']['height']);
                } else {
                    $image = '';
                }

                // Display prices
                if (($config_customer_price && $customer_is_logged) || !$config_customer_price) {
                    if (isset($pd_front)) {
                        //                        $product['price'] += $pd_front;
                        $pd_custom_total_price += $pd_front;
                        $pd_custom_total_price *= $product['quantity'];
                    }

                    if (isset($pd_back)) {
                        //                        $product['price'] += $pd_back;
                        $pd_custom_total_price += $pd_back;
                        $pd_custom_total_price *= $product['quantity'];
                    }
                    $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $config_tax));
                    $price_without_taxes = $this->currency->format($product['price']);
                } else {
                    $price = false;
                }

                // Display prices
                if (($config_customer_price && $customer_is_logged) || !$config_customer_price) {
                    $total = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $config_tax) * $product['quantity']);
                    $total_without_taxes = $this->currency->format($product['price'] * $product['quantity']);
                } else {
                    $total = false;
                }

                if (isset($product['rentData'])) {
                    $product['rentData']['range'] = array_map(function ($value) {
                        return date("Y-m-d", $value);
                    }, $product['rentData']['range']);
                }

                ////////////// Show Quantity Warninig
                if (!$product['stock'])
                    $data['show_quantity_error'] = true;
                /////////////////////////////////////

                $data['products'][] = array(
                    'product_id' => $product['key'],
                    'thumb'         => $thumb,
                    'image'         => $image,
                    'name'       => $product['name'],
                    'model'      => $product['model'],
                    'option'     => $option_data,
                    'quantity'   => $product['quantity'],
                    'stock'         => $product['stock'] ? true : !(!$config_stock_checkout || $config_stock_warning),
                    'subtract'   => $product['subtract'],
                    'price'      => $price_without_taxes,
                    'total'      => $total_without_taxes,
                    'main_price'      => (isset($product['main_price'])) ? $product['main_price'] : NULL,
                    'remaining_amount'      => (isset($product['remaining_amount'])) ? $product['remaining_amount'] : NULL,
                    'reward'     => $product['reward'],
                    'href'       => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                    'rentData' => $product['rentData'],
                    'pricePerMeterData' => $product['pricePerMeterData'],
                    'printingDocument' => $product['printingDocument'],
                    'warehouse' => $product['warehouse'] ? $product['warehouse'] : '',
                    'prize_draw' => $prize_draw,
                    'consumed_percentage' => $consumed_percentage <= 100 ? $consumed_percentage : 100
                );

                $products_count += $product['quantity'];
            }
            $data['products_count'] = $products_count; // count($cart_products) for products count without considering the quantity

            if ($this->MsLoader->isInstalled()) {
                $cart_sellers = [];
                foreach ($data['products'] as &$product) {
                    $product['seller'] = $this->MsLoader->MsSeller->getSellerByProductId($product['product_id']);

                    if ($product['seller']['minimum_order'] > 0) {
                        if (!isset($cart_sellers[$product['seller']['seller_id']])) {
                            $cart_sellers[$product['seller']['seller_id']] = $product['seller'];
                        }

                        $cart_sellers[$product['seller']['seller_id']]['total_cart'] += $product['total'];

                        if ($this->config->get('msconf_enable_seller_name_in_cart_view') == 1) {
                            $product['seller_name'] = $product['seller']['nickname'];
                        }
                    }
                }
            }

            //combined warehouses cost
            if ($data['warehouses']) {
                foreach ($data['products'] as $prd) {
                    $warehouseCostValue = $combined_wrs_costs[$prd['warehouse']];
                    $combined_wrs_costs_format[$prd['warehouse']] = $this->currency->format($this->tax->calculate($warehouseCostValue, $this->config->get('weight_tax_class_id'), $this->config->get('config_tax')));
                    ///////////////////////////////////////////////////////
                }
                $data['combined_wrs_costs'] = $combined_wrs_costs_format;
            } /////////////////////////


            if ($pd_custom_total_price > 0) {
                $this->reArrangeTotalData($total_data, $total, $pd_custom_total_price);
            }

            $data['totals'] = $total_data;
            $data['currency_symbols'] = [
                'left' => $this->currency->getSymbolLeft(),
                'right' => $this->currency->getSymbolRight()
            ];

            $data['data'] =  [
                'sort_order' => "6",
                'column' => "1",
                'row' => "0",
                'display_title' => "1",
                'display_description' => "1",
                'width' => "50",
                'option' => [
                    'voucher' => [
                        'id' => "voucher",
                        'title' => "voucher",
                        'tooltip' => "voucher",
                        'type' => "text",
                        'refresh' => "4",
                        'custom' => "0",
                        'class' => "",
                        'display' => "1",
                        'value' => ""
                    ],
                    'coupon' => [
                        'id' => "coupon",
                        'title' => "coupon",
                        'tooltip' => "coupon",
                        'type' => "text",
                        'refresh' => "4",
                        'custom' => "0",
                        'class' => "",
                        'display' => "1",
                        'value' => ""
                    ],
                    'reward' => [
                        'id' => "reward",
                        'title' => "reward",
                        'tooltip' => "reward",
                        'type' => "text",
                        'refresh' => "4",
                        'custom' => "0",
                        'class' => "",
                        'display' => "1",
                        'value' => ""
                    ]
                ],
                'display' => "1",
                'title' => $this->language->get("text_cart"),
                'description' => '', //"option_logged_cart_description",
                'columns' => [
                    'image' => "1",
                    'name' => "1",
                    'model' => "0",
                    'quantity' => "1",
                    'price' => "1",
                    'total' => "1"
                ]
            ];

            /// Reward points app
            if (Extension::isInstalled('reward_points_pro') && $this->config->get('rwp_enabled_module') == "1") {
                $data['reward_app'] = 1;

                $data['reward_success'] = $this->session->data['reward_success'];
                $data['reward_error'] = $this->session->data['reward_error'];
                unset($this->session->data['reward_success']);
                unset($this->session->data['reward_error']);
            }

            // if (!empty($this->session->data['subscription'])) {
            // $data['subscription'] = array(
            //     'title'       => $this->session->data['subscription']['title'],
            //     'quantity'    => 1,
            //     'amount'      => $this->currency->format($this->session->data['subscription']['amount'])
            // );
            // }
        }

        $this->data = array_merge($this->data, $data);
    }

    private function prepareOrder()
    {
        if (!isset($this->session->data['order_id'])) {
            $this->createOrder();
        } else {
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            if (!$order_info) {
                $this->createOrder();
            }
        }

        $this->updateOrder();

        $this->data['order_id'] = $this->session->data['order_id'];
    }

    private function prepareCurrentStep()
    {
        if (empty($this->session->data['subscription'])) {
            $currentAddress = $this->session->data['payment_address'] ?: [];

            $validator = $this->validateFormAddress($currentAddress);
            if ($validator !== true) {
                $this->data['current_step'] = 'information';
                return 'information';
            }
        }

        if ($this->data['cart_has_shipping'] && (!isset($this->session->data['shipping_method']) || empty($this->session->data['shipping_method']))) {
            $this->data['current_step'] = 'shipping';
            return 'shipping';
        }

        $this->data['current_step'] = 'payment';
        return 'payment';
    }

    private function totalData(&$total_data, &$total, &$taxes)
    {
        // Check if Buyer subscription Plan App is installed
        // If installed, then add discount coupons on the purchased items total...
        $subscriptionAppInstalled = Extension::isInstalled('buyer_subscription_plan') && $this->config->get('buyer_subscription_plan_status');
        if ($subscriptionAppInstalled) $this->addCustomerSubscriptionPlanCoupons();

        $total_data = array();
        $total = 0;
        $taxes = $this->cart->getTaxes();
        $sort_order = array();

        $this->load->model('setting/extension');

        $results = $this->model_setting_extension->getExtensions('total');

        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {

            $settings = $this->config->get($result['code']);

            if ($settings && is_array($settings)) {
                $status = $settings['status'];
            } else {
                $status = $this->config->get($result['code'] . '_status');
            }

            if ($status) {
                $this->load->model('total/' . $result['code']);

                $this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
            }
        }
        $sort_order = array();

        foreach ($total_data as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $total_data);

        foreach ($total_data as &$total) {
            if ($total['code'] == 'coupon' && (int) $total['automatic_apply'] === 0) {
                $total['title'] = $this->language->get('entry_discount_coupon');
            }

            if ($total['code'] == 'coupon' && (int) $total['automatic_apply'] === 1) {
                $total['title'] = $this->language->get('entry_automatic_discount');
            }
        }

        return $total_data;
    }

    private function reArrangeTotalData(&$total_data, &$total, $custom_price)
    {
        array_unshift($total_data, [
            'code' => 'pd_custom_design',
            'title' => $this->language->get('pd_custom_design_cart_title'),
            'text' => $this->currency->format($custom_price),
            'value' => $custom_price,
            'sort_order' => 5
        ]);

        $tmp = array_pop($total_data);
        $tmp['text'] = $this->currency->format($custom_price + $tmp['value']);
        $tmp['value'] = $custom_price + $tmp['value'];

        array_push($total_data, $tmp);
        $total += $custom_price;
    }

    private function addCustomerSubscriptionPlanCoupons()
    {
        $this->load->model('account/subscription');

        $customer_buyer_subscription_plan = $this->model_account_subscription->getCustomerSubscriptionPlan($this->customer->getId());

        //if customer on free subscription plan
        if (empty($customer_buyer_subscription_plan)) return;

        $expired = $this->model_account_subscription->checkIfSubscriptionExpired($customer_buyer_subscription_plan);

        if (!$expired) {
            //Get subscription coupons...
            $coupons = $this->model_account_subscription->getCoupons($customer_buyer_subscription_plan['id']);

            //Validate all coupons codes (Only one of them (or none) should be applied)
            foreach ($coupons as $coupon) {
                $this->request->post['coupon'] = $coupon;
                $this->request->post['buyer_subscription_plan_coupon'] = true;
                $this->validateCoupon();
            }
        }
    }

    public function applyCustomerGroupCoupon()
    {
        if ($this->data['logged']) {
            // check if session already has coupon
            if (isset($this->session->data['coupon'])) {
                // If true, check that coupon is correct
                $this->request->post['coupon'] = $this->session->data['coupon'];
                $this->request->post['buyer_subscription_plan_coupon'] = $this->session->data['buyer_subscription_plan_coupon'];
                $this->validateCoupon(); // if the coupon is a valid add it to session else delete it               
            }

            if (isset($this->session->data['coupon'])) return;

            // if session has not coupon! check customer group coupons
            $this->load->model('checkout/coupon');
            $customerGroup = $this->customer->getCustomerGroupId();
            $coupons = $this->model_checkout_coupon->getCouponsByGroupId($customerGroup);

            foreach ($coupons as $coupon) {
                $this->request->post['coupon'] = $coupon['code'];
                $coupon_info = $this->model_checkout_coupon->getCoupon($coupon['code']);

                if (!$coupon_info) {
                    $json['error'] = $this->language->get('error_coupon');
                }

                if ($this->cart->getSubTotal() < $coupon_info['minimum_to_apply']) {
                    $json['error'] = sprintf(
                        $this->language->get('error_coupon_less_than_minimum'),
                        $this->currency->format($coupon_info['minimum_to_apply'])
                    );
                }
            }
            if (!isset($json['error'])) {
                $this->session->data['coupon'] = $this->request->post['coupon'];
                $json['success'] = $this->language->get('text_coupon');
            }
            $this->response->setOutput(json_encode($json));
        }
    }

    private function setExtraParamToAddress(array $address = null)
    {
        if (!$address) return $address;

        if ($address['address_format']) {
            $format = $address['address_format'];
        } else {
            $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{area} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
        }

        $find = array(
            '{firstname}',
            '{lastname}',
            '{company}',
            '{address_1}',
            '{address_2}',
            '{area}',
            '{postcode}',
            '{zone}',
            '{zone_code}',
            '{country}'
        );

        $replace = array(
            'firstname' => '', //$address['firstname'],
            'lastname'  => '', //$address['lastname'],
            'company'   => $address['company'],
            'address_1' => $address['address_1'],
            'address_2' => $address['address_2'],
            'area'      => $address['area'],
            'postcode'  => $address['postcode'],
            'zone'      => $address['zone'],
            'zone_code' => $address['zone_code'],
            'country'   => $address['country']
        );

        $address['address'] = str_replace(array("\r\n", "\r", "\n"), ' ', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), ' ', trim(str_replace($find, $replace, $format))));

        // if (strlen($address['address'])) {
        //     $addrParts = explode(' ', preg_replace(["/,/"], '', $address['address']));
        //     $shortAddr = $addrParts[0];
        //     $shortAddr .= (isset($addrParts[1]) ? (" " . $addrParts[1]) : '');
        //     $shortAddr .= (isset($addrParts[2]) ? (" " . $addrParts[2]) : '');
        //     $shortAddr .= (count($addrParts) > 3 ? (" ... " . end($addrParts)) : '');
        //     $address['short_address'] = $shortAddr;
        // }

        $address['short_address'] = $address['address'];

        if ($this->data['logged'] && empty($address['firstname'])) {
            $address['firstname'] = $this->customer->getFirstName();
        }

        if ($this->data['logged'] && empty($address['telephone'])) {
            $address['telephone'] = $this->customer->getTelephone();
        }

        return $address;
    }

    private function countryData($country_id, $zone_id = 0)
    {
        $address = array();

        $this->load->model('localisation/country');
        $country_info = $this->model_localisation_country->getCountry($country_id);

        if ($country_info) {
            $address['country'] = $country_info['name'];
            $address['iso_code_2'] = $country_info['iso_code_2'];
            $address['iso_code_3'] = $country_info['iso_code_3'];
            $address['address_format'] = $country_info['address_format'];
            $address['phonecode'] = $country_info['phonecode'];
        } else {
            $address['country'] = '';
            $address['iso_code_2'] = '';
            $address['iso_code_3'] = '';
            $address['address_format'] = '';
            $address['phonecode'] = '';
        }

        $this->load->model('localisation/zone');
        $zone_info = $this->model_localisation_zone->getZone($zone_id);

        if ($zone_info) {
            $address['zone'] = $zone_info['name'];
            $address['zone_code'] = $zone_info['code'];
        } else {
            $address['zone'] = '';
            $address['zone_code'] = '';
        }
        return $address;
    }

    private function setAbandonedCart(): void
    {
        $abandonedCartSettings = json_encode([
            'isInstalled' => Extension::isInstalled('abandoned_cart'),
            'status' => $this->config->get('abandoned_cart')['status'],
            'auto_send_mails' => $this->config->get('abandoned_cart')['auto_send_mails'],
            'userLogged' => $this->customer->isLogged(),
            'url' => $this->url->link("module/abandoned_cart/addRetentionMailCronJob")
        ]);

        $this->document->addInlineScript(function() use ($abandonedCartSettings){
            return "<script> window.abandonedCartSettings = {$abandonedCartSettings}; </script>";
        });
    }

    private function setLoginScript()
    {
        $this->load->model('localisation/country');
        $this->load->model("module/google_map");

        $storeCode = STORECODE;

        $customer = json_encode($this->data['logged'] ? ['id' => $this->customer->getExpandId(), 'logged_in' => true] : ['logged_in' => false]);
        $lang = $this->config->get('config_language') ?: 'en';
        $countryId = $this->session->data['shipping_country_id'] ?? $this->config->get('config_country_id');
        $countries = json_encode($this->model_localisation_country->getCountries());
        $whatsAppAgree = (int)(Extension::isInstalled('whatsapp') && $this->config->get('whatsapp_config_customer_allow_receive_messages')) ? 1 : 0;
        $enableMultiseller = (int)Extension::isInstalled('multiseller');
        $loginWithPhone = $this->identity->isLoginByPhone();

        $googleMap = $this->model_module_google_map->getSettings();

        $this->data['lang'] = $lang;
        $this->data['google_map'] = $googleMap;

        $loginSelectors = json_encode([
            'customer' => [
                'login' => [
                    // A list of links that the library will replace with the login pop-up modal
                    // When the user presses one of them
                    'a[href*="' . $this->url->link('account/account') . '"]',
                    'a[href*="' . $this->url->link('account/login') . '"]',
                    'a[href*="' . $this->url->link('account/register') . '"]',
                    'a[href*="' . $this->url->link('account/wishlist') . '"]',
                ],
            ],
            'seller' => [
                'login' => [
                    'a[href*="' . $this->url->link('seller/register-seller') . '"]',
                ]
            ],
            'checkout' => [
                'login' => [
                    // '#option_login_popup_trigger_wrap [name="account"]#register',
                    // '#option_login_popup_trigger_wrap #option_login_popup_trigger',
                    // '#payment_address_wrap #signin_action'
                    '#sign__in'
                ],
            ],
        ]);

        $storeName = $this->config->get('config_name');

        // social login app
        $this->load->model('setting/extension');
        $extensions = $this->model_setting_extension->getExtensions('module');
        $socialLogin = ['status' => false];
        foreach ($extensions as $extension) {
            if ($extension['code'] == 'd_social_login') {
                $settings = $this->config->get('d_social_login_settings');
                if ($settings) {
                    if ($settings['status']) {
                        $socialLogin['status'] = true;
                        $socialLogin['content'] = $this->getChild('module/' . 'd_social_login');
                    }
                }
                break;
            }
        }
        $socialLogin = json_encode($socialLogin);
        $customerAccountFields = json_encode($this->config->get('config_customer_fields'));
        $isGetRequest = !($this->request->server['REQUEST_METHOD'] == 'POST');
        $libraryStatus = (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) ? 'on' : 'off';
        $this->document->addInlineScript(function () use ($storeName, $lang, $storeCode, $countryId, $whatsAppAgree, $enableMultiseller, $loginWithPhone, $customer, $googleMap, $loginSelectors, $socialLogin, $countries, $customerAccountFields, $isGetRequest, $libraryStatus) {
            $noCache = "v1"; // bin2hex(random_bytes(4));
            $return = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/expandish/view/javascript/loginjs/dist/loginjs.css?nocache=$noCache\"/>";

            // if ($isGetRequest && (int)$googleMap['status'] === 1) {
            //     $googleMapApiKey = $googleMap['api_key'];
            //     $return .= "<script type=\"text/javascript\" id=\"google-maps-sdk\" src=\"https://maps.googleapis.com/maps/api/js?key=$googleMapApiKey&libraries=places&language=$lang\"></script>";
            // }

            $googleMap = json_encode($googleMap);

            $return .= "<script type=\"text/javascript\" defer src=\"/expandish/view/javascript/loginjs/dist/loginjs.js?nocache=$noCache\"></script><script id=\"loginjs-plugin-$noCache\">window.addEventListener('DOMContentLoaded', (event) => {window.Loginjs !== undefined && (window.login = new Loginjs({storeName: '$storeName', lang: '$lang', storeCode: '$storeCode', countryId: '$countryId', whatsAppAgree: $whatsAppAgree, enableMultiseller: $enableMultiseller, loginWithPhone: $loginWithPhone, customer: $customer, map: $googleMap, loginSelectors: $loginSelectors, socialLogin: $socialLogin, countries: $countries, customerAccountFields: $customerAccountFields, libraryStatus: '$libraryStatus'}).render());});</script>";

            return $return;
        });

        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            $this->identity->syncCustomerProfile();
        }

        return false;
    }

    private function setLanguagesData($code = null)
    {

        if ($code) {
            $this->language->changeLanguage($code);
        }

        $this->load->model('localisation/language');
        $this->data['language_code'] = $this->session->data['language'];
        $this->data['languages'] = array();
        $this->data['current_language'] = array();

        $this->session->data['languages'] = $this->session->data['languages'] ?: $this->model_localisation_language->getLanguages();

        foreach ($this->session->data['languages'] as $result) {
            if ($result['status']) {
                $this->data['languages'][] = array(
                    'name'  => $result['name'],
                    'code'  => $result['code'],
                    'image' => $result['image']
                );

                if ($this->data['language_code'] == $result['code']) {
                    $this->data['current_language'] = array(
                        'name' => $result['name'],
                        'code' => $result['code'],
                        'image' => $result['image']
                    );
                }
            }
        }
    }

    private function setCurrenciesData($code = null)
    {

        if ($code) {
            $this->currency->set($code);
        }

        $this->load->model('localisation/currency');
        $this->data['currency_code'] = $this->currency->getCode();
        $this->data['currencies'] = array();
        $this->data['current_currency'] = array();

        if (!isset($this->session->data['currencies'])) {
            $this->session->data['currencies'] = [];
            if (
                !$this->dedicatedDomains->isActive() || ($this->dedicatedDomains->isActive() && $this->dedicatedDomains->options['changeCurrency'])
            ) {
                $this->session->data['currencies'] = $this->model_localisation_currency->getCurrencies();
            }
        }

        foreach ($this->session->data['currencies'] as $result) {
            if ($result['status']) {
                $this->data['currencies'][] = array(
                    'title'        => $result['title'],
                    'code'         => $result['code'],
                    'symbol_left'  => $result['symbol_left'],
                    'symbol_right' => $result['symbol_right'],
                    'symbol'       => $result['symbol_left'] ? $result['symbol_left'] : $result['symbol_right']
                );

                if ($this->data['currency_code'] == $result['code']) {
                    $this->data['current_currency'] = array(
                        'title'        => $result['title'],
                        'code'         => $result['code'],
                        'symbol_left'  => $result['symbol_left'],
                        'symbol_right' => $result['symbol_right'],
                        'symbol'       => $result['symbol_left'] ? $result['symbol_left'] : $result['symbol_right']
                    );
                }
            }
        }
    }

    private function setMainColor()
    {
        $template = $this->config->get('config_template');
        $lang = $this->config->get('config_language') ?: 'en';

        $mainColor = function () use ($template, $lang) {
            $raw = "SELECT `ectemplate`.`id` AS template_id, `ecpage`.`id` AS page_id, `ecregion`.`id` AS region_id, `ecsection`.`id` AS section_id, `eccollection`.`id` AS collection_id, `ecobjectfield`.`id` AS obj_field_id, `ecobjectfieldval`.`Value` AS obj_field_value FROM `ectemplate` 
        LEFT JOIN `ecpage` ON `ecpage`.`TemplateId` = `ectemplate`.`id`
        LEFT JOIN `ecregion` ON `ecregion`.`PageId` = `ecpage`.`id`
        LEFT JOIN `ecsection` ON `ecsection`.`RegionId` = `ecregion`.`id`
        LEFT JOIN `eccollection` ON `eccollection`.`SectionId` = `ecsection`.`id`
        LEFT JOIN `ecobjectfield` ON `ecobjectfield`.`ObjectId` = `eccollection`.`id`
        LEFT JOIN `ecobjectfieldval` ON `ecobjectfieldval`.`ObjectFieldId` = `ecobjectfield`.`id`
        WHERE `ectemplate`.`CodeName` = '$template'
        AND `ecpage`.`CodeName` = 'templatesettings'
        AND `ecregion`.`CodeName` = 'styling'
        AND (`ecsection`.`CodeName` = 'colors' AND `ecsection`.`Type` = 'live')
        AND (`ecobjectfield`.`CodeName` = 'MainColor' AND `ecobjectfield`.`ObjectType` = 'ECCOLLECTION')
        AND `ecobjectfieldval`.`Lang` = '$lang'";
            $mainColor = $this->db->query($raw)->row['obj_field_value'] ?: null;
            $this->session->data['main_color_' . $template . '_' . $lang] = $mainColor;
            return $mainColor;
        };

        $this->data['main_color'] = $this->session->data['main_color_' . $template . '_' . $lang] ?: $mainColor();

        return $this->data['main_color'];
    }

    private function storeAddress($data)
    {
        $data = $this->saveAddress($data);

        $this->session->data['payment_address'] = $data;
        $this->session->data['shipping_address'] = $data;

        $this->session->data['send_as_gift'] = (isset($this->request->post['send_as_gift']) && (int)$this->request->post['send_as_gift'] === 1) ? 1 : 0;

        $this->updateOrder();

        return $data;
    }
    
    private function saveAddress($data)
    {
        if ($this->data['logged']) {
            if (isset($data['address_id']) && (int) $data['address_id'] > 0) {
                $this->identity->updateAddress($data);
            } else {
                $data['address_id'] = $this->identity->addAddress($data);
            }

            if ($data['default'] == 1) $this->customer->setAddressId($data['address_id']);

            $addressModel = $this->load->model('account/address', ['return' => true]);

            $data = $addressModel->getAddress($data['address_id']);
        } else {
            $guestAddressesBook = $this->session->data['guest_addresses_book'] ?: [];

            if (!isset($data['address_id']) || (int) $data['address_id'] == 0 || !array_key_exists($data['address_id'], $guestAddressesBook)) {
                $data['address_id'] = count($guestAddressesBook) + 1;
            }

            $guestAddressesBook[$data['address_id']] = $data;

            $this->session->data['guest_addresses_book'] = $guestAddressesBook;
        }
        
        return $data;
    }

    private function createOrder($args = 0)
    {
        $total_data = $total = $taxes = null;

        $this->totalData($total_data, $total, $taxes);
        $data = array();
        $data['store_id'] = $this->config->get('config_store_id');
        $data['store_name'] = $this->config->get('config_name');

        if ($data['store_id']) {
            $data['store_url'] = $this->config->get('config_url');
        } else {
            $data['store_url'] = HTTP_SERVER;
        }
        // set affiliate id and commission to 0
        $data['affiliate_id'] = 0;
        $data['commission'] = 0;

        if (isset($this->request->cookie['tracking'])) {
            $this->load->model('affiliate/affiliate');

            $affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);
            $subtotal = $this->cart->getSubTotal();

            if ($affiliate_info) {
                // check if customer is approved by admin
                if ($affiliate_info['approved'] == 1) {
                    $data['affiliate_id'] = $affiliate_info['affiliate_id'];
                    $data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
                }
            }
        }

        $data['language_id'] = $this->config->get('config_language_id');
        $data['currency_id'] = $this->currency->getId();
        $data['currency_code'] = $this->currency->getCode();
        $data['currency_value'] = $this->currency->getValue($this->currency->getCode());
        $data['ip'] = $this->request->server['REMOTE_ADDR'];

        if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
            $data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
            $data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
        } else {
            $data['forwarded_ip'] = '';
        }

        if (isset($this->request->server['HTTP_USER_AGENT'])) {
            $data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
        } else {
            $data['user_agent'] = '';
        }

        if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
            $data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
        } else {
            $data['accept_language'] = '';
        }

        $data['total'] = $total;

        $this->load->model('quickcheckout/order');

        if (preg_match("/1.5.1/i", VERSION)) {
            $this->session->data['order_id'] = $this->model_quickcheckout_order->addOrder151($data);
        } else {
            $this->session->data['order_id'] = $this->model_quickcheckout_order->addOrder($data);
        }
    }

    private function updateOrder($args = 0)
    {
        $total_data = $total = $taxes = null;

        $this->totalData($total_data, $total, $taxes);
        $data = array();

        $data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
        $data['store_id'] = $this->config->get('config_store_id');
        $data['store_name'] = $this->config->get('config_name');

        if ($data['store_id']) {
            $data['store_url'] = $this->config->get('config_url');
        } else {
            $data['store_url'] = HTTP_SERVER;
        }

        $data['customer_id'] = $this->customer->getId();
        $data['customer_group_id'] = $this->customer->getCustomerGroupId();
        $data['firstname'] = $this->session->data['payment_address']['firstname'];
        $data['lastname'] = $this->customer->getLastName();
        $data['email'] = $this->customer->getEmail();
        $data['telephone'] =  $this->session->data['payment_address']['telephone'];
        $data['fax'] = $this->customer->getFax();

        if (empty($data['telephone'])) {
            $data['telephone'] = $this->customer->getTelephone();
        }

        if (empty($data['firstname'])) {
            $data['firstname'] = $this->customer->getFirstName();
        }

        if (empty($data['lastname'])) {
            $data['lastname'] = $this->session->data['payment_address']['lastname'];
        }

        if (empty($data['email'])) {
            $data['email'] = $this->session->data['payment_address']['email'];
        }

        $payment_address = $this->session->data['payment_address'];

        $data['payment_firstname'] = $data['firstname'];
        $data['payment_lastname'] = $data['lastname'];

        if (empty($this->session->data['payment_address']['telephone'])) {
            $data['payment_telephone'] = $data['telephone'];
        } else {
            $data['payment_telephone'] = $this->session->data['payment_address']['telephone'];
        }

        $data['payment_company'] = $payment_address['company'];
        $data['payment_company_id'] = (isset($payment_address['company_id'])) ? $payment_address['company_id'] : '';
        $data['payment_tax_id'] = (isset($payment_address['tax_id'])) ? $payment_address['tax_id'] : '';
        $data['payment_address_1'] = $payment_address['address_1'];
        $data['payment_address_2'] = $payment_address['address_2'];
        $data['payment_city'] = $payment_address['city'];
        $data['payment_postcode'] = $payment_address['postcode'];
        $data['payment_zone'] = $payment_address['zone'];
        $data['payment_zone_id'] = $payment_address['zone_id'];
        $data['payment_country'] = $payment_address['country'];
        $data['payment_country_id'] = $payment_address['country_id'];
        $data['payment_address_format'] = $payment_address['address_format'];
        $data['payment_address_location'] = $this->location($payment_address);
        $data['payment_area'] = $payment_address['area'];
        $data['payment_area_id'] = $payment_address['area_id'];

        if ($this->data['logged']) {
            $data['address_id'] = $payment_address['address_id'];
        }

        if (isset($this->session->data['payment_method']['title'])) {
            $data['payment_method'] = $this->session->data['payment_method']['title'];
        } else {
            $data['payment_method'] = '';
        }

        if (isset($this->session->data['payment_method']['code'])) {
            $data['payment_code'] = $this->session->data['payment_method']['code'];
        } else {
            $data['payment_code'] = '';
        }

        if ($this->cart->hasShipping()) {
            $shipping_address = $this->session->data['shipping_address'];
            $data['shipping_firstname'] = $shipping_address['firstname'];
            $data['shipping_lastname'] = $shipping_address['lastname'];
            $data['shipping_company'] = $shipping_address['company'];
            $data['shipping_address_1'] = $shipping_address['address_1'];
            $data['shipping_address_2'] = $shipping_address['address_2'];
            $data['shipping_telephone'] = $shipping_address['telephone'];
            $data['shipping_city'] = $shipping_address['city'];
            $data['shipping_postcode'] = $shipping_address['postcode'];
            $data['shipping_zone'] = $shipping_address['zone'];
            $data['shipping_zone_id'] = $shipping_address['zone_id'];
            $data['shipping_country'] = $shipping_address['country'];
            $data['shipping_country_id'] = $shipping_address['country_id'];
            $data['shipping_address_format'] = $shipping_address['address_format'];
            $data['shipping_address_location'] = $this->location($shipping_address);
            $data['shipping_area'] = $shipping_address['area'];
            $data['shipping_area_id'] = $shipping_address['area_id'];

            if ($this->data['logged']) {
                $data['address_id'] = $payment_address['address_id'];
            }

            if (isset($this->session->data['shipping_method']['title'])) {
                $data['shipping_method'] = $this->session->data['shipping_method']['title'];
            } else {
                $data['shipping_method'] = '';
            }

            if (isset($this->session->data['shipping_method']['code'])) {
                $data['shipping_code'] = $this->session->data['shipping_method']['code'];
            } else {
                $data['shipping_code'] = '';
            }
        } else {
            $data['shipping_firstname'] = '';
            $data['shipping_lastname'] = '';
            $data['shipping_telephone'] = '';
            $data['shipping_company'] = '';
            $data['shipping_address_1'] = '';
            $data['shipping_address_2'] = '';
            $data['shipping_city'] = '';
            $data['shipping_postcode'] = '';
            $data['shipping_zone'] = '';
            $data['shipping_zone_id'] = '';
            $data['shipping_country'] = '';
            $data['shipping_country_id'] = '';
            $data['shipping_address_format'] = '';
            $data['shipping_method'] = '';
            $data['shipping_code'] = '';
        }

        $product_data = array();
        $pd_custom_price = 0;
        $pd_custom_total_price = 0;
        //New v 23/02/2020
        $cart_product = $this->cart->getProducts();

        foreach ($cart_product as $productKey => $product) {
            $pd_application = null;
            $pd_module_status = false;
            $option_data = array();

            if (isset($product['option'])) {
                foreach ($product['option'] as $option) {
                    if ($option['type'] != 'file') {
                        $value = $option['option_value'];
                    } else if ($option['type'] == "pd_application") {
                        $pd_application = array(
                            'product_option_id' => $option['product_option_id'],
                            'custom_id' => $option['custom_id'],
                            'productId' => $option['productId'],
                            'type' => $option['product_option_id'],
                            'tshirtId' => $option['tshirtId'],
                        );
                        $pd_module_status = true;
                    } else {
                        $value = $this->encryption->decrypt($option['option_value']);
                    }

                    $option_data[] = array(
                        'product_option_id'       => $option['product_option_id'],
                        'product_option_value_id' => $option['product_option_value_id'],
                        'option_id'               => $option['option_id'],
                        'option_value_id'         => $option['option_value_id'],
                        'name'                    => $option['name'],
                        'value'                   => $value,
                        'type'                    => $option['type'],
                        'pd_application'          => !empty($pd_application) ? $pd_application : null
                    );
                }
            }
            // to add product designer module's custom price to the total price
            if ($pd_module_status) {
                $pdOpts = $this->db->query(
                    'select * from ' . DB_PREFIX . 'tshirtdesign where did="' . $pd_application['tshirtId'] . '"'
                );
                if ($pdOpts->num_rows > 0) {
                    $pd_front = json_decode(html_entity_decode($pdOpts->row['front_info']), true)[0]['custom_price'];
                    $pd_back = json_decode(html_entity_decode($pdOpts->row['back_info']), true)[0]['custom_price'];
                } else {
                    $pd_front = 0;
                    $pd_back = 0;
                }

                $pd_custom_total_price = $pd_custom_price + $pd_front + $pd_back;
                $pd_custom_total_price *= $product['quantity'];

                $product['total'] += $pd_custom_total_price;
            }

            $product_data[$productKey] = array(
                'product_id' => $product['product_id'],
                'name'       => $product['name'],
                'model'      => (isset($product['model'])) ? $product['model'] : '',
                'option'     => $option_data,
                'download'   => (isset($product['download'])) ? $product['download'] : '',
                'quantity'   => $product['quantity'],
                'subtract'   => (isset($product['subtract'])) ? $product['subtract'] : '',
                'price'      => $product['price'],
                'total'      => $product['total'],
                'main_price'      => (isset($product['main_price'])) ? $product['main_price'] : NULL,
                'remaining_amount'      => (isset($product['remaining_amount'])) ? $product['remaining_amount'] : NULL,
                'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
                'reward'     => (isset($product['reward'])) ? $product['reward'] : '',
                'pd_application' => (is_array($pd_application) ? $pd_application : null),
                'curtain_seller'    => isset($product['curtain_seller']) ? $product['curtain_seller'] : null
            );

            // check if product has codes from code generator app
            // 1 - check if app Installed
            $productCodeAppSettings = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'product_code_generator'");;
            if ($productCodeAppSettings->num_rows) {
                // check if product used code generator app
                $productCodeApp = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_code_generator  WHERE 	product_id = '" . (int)$product['product_id'] . "' ");
                if (is_object($productCodeApp) && $productCodeApp->num_rows > 0) {
                    // get  product codes
                    $productCodesData = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_code_generator  WHERE product_id = '" . (int)$product['product_id'] . "' AND is_used = 0 LIMIT " . $product['quantity'] . " ");
                    if ($productCodesData->num_rows > 0) {
                        $productCodesArrayData = array();
                        foreach ($productCodesData->rows as $key => $productCode) {
                            $productCodesArrayData[$key]['product_code_generator_id'] = $productCode['product_code_generator_id'];
                            $productCodesArrayData[$key]['code'] = $productCode['code'];
                        }
                        $product_data[$productKey]['codeGeneratorData'][$product['product_id']] = json_encode($productCodesArrayData);
                    }
                }
            }

            if (isset($product['rentData'])) {
                $product_data[$productKey]['rentData'] = json_encode([
                    'diff' => $product['rentData']['diff'],
                    'range' => $product['rentData']['range'],
                ]);
            }

            if (isset($product['pricePerMeterData'])) {
                $product_data[$productKey]['pricePerMeterData'] = json_encode([
                    'underlaymen' => $product['pricePerMeterData']['underlaymen'],
                    'skirtings' => $product['pricePerMeterData']['skirtings'],
                    'metalProfiles' => $product['pricePerMeterData']['metalProfiles'],
                    'main_status' => $product['pricePerMeterData']['main_status'],
                    'metalprofile_status' => $product['pricePerMeterData']['metalprofile_status'],
                    'skirtings_status' => $product['pricePerMeterData']['skirtings_status'],
                ]);
            }

            if (isset($product['printingDocument'])) {
                $product_data[$productKey]['printingDocument'] = json_encode($product['printingDocument']);
            }
        }

        // Gift Voucher
        $voucher_data = array();

        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $voucher) {
                $v_quantity = $voucher['quantity'] ? $voucher['quantity'] : 1;
                $voucher_data[] = array(
                    'description'      => $voucher['description'],
                    'code'             => substr(md5(mt_rand()), 0, 10),
                    'to_name'          => $voucher['to_name'],
                    'to_email'         => $voucher['to_email'],
                    'from_name'        => $voucher['from_name'],
                    'from_email'       => $voucher['from_email'],
                    'voucher_theme_id' => $voucher['voucher_theme_id'],
                    'message'          => $voucher['message'],
                    'quantity'         => $v_quantity,
                    'amount'           => ($voucher['amount'] * $v_quantity)
                );
            }
        }

        // Buyer Subscription Plan
        $subscription_data = [];

        if (!empty($this->session->data['subscription'])) {
            $subscription_data = array(
                'description'      => $this->session->data['subscription']['title'],
                'code'             => substr(md5(mt_rand()), 0, 10),
                'title'            => $this->session->data['subscription']['title'],
                'id'               => $this->session->data['subscription']['id'],
                'amount'           => $this->session->data['subscription']['amount']
            );
        }

        $data['products'] = $product_data;
        $data['vouchers'] = $voucher_data;
        $data['subscription'] = $subscription_data;


        if ($pd_custom_total_price > 0) {
            $this->reArrangeTotalData($total_data, $total, $pd_custom_total_price);
        }

        $data['totals'] = $total_data;
        $data['comment'] = (isset($this->session->data['comment'])) ? $this->session->data['comment'] : '';
        $data['total'] = $total;

        // compatibility
        if (preg_match("/1.5.1/i", VERSION)) {
            $data['reward'] = $this->cart->getTotalRewardPoints();
        }

        if (isset($this->request->cookie['tracking'])) {
            $this->load->model('affiliate/affiliate');

            $affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);
            $subtotal = $this->cart->getSubTotal();

            if ($affiliate_info) {
                $data['affiliate_id'] = $affiliate_info['affiliate_id'];
                $data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
            } else {
                $data['affiliate_id'] = 0;
                $data['commission'] = 0;
            }
        } else {
            $data['affiliate_id'] = 0;
            $data['commission'] = 0;
        }

        $data['language_id'] = $this->config->get('config_language_id');
        $data['currency_id'] = $this->currency->getId();
        $data['currency_code'] = $this->currency->getCode();
        $data['currency_value'] = $this->currency->getValue($this->currency->getCode());
        $data['ip'] = $this->request->server['REMOTE_ADDR'];

        if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
            $data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
            $data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
        } else {
            $data['forwarded_ip'] = '';
        }

        if (isset($this->request->server['HTTP_USER_AGENT'])) {
            $data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
        } else {
            $data['user_agent'] = '';
        }

        if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
            $data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
        } else {
            $data['accept_language'] = '';
        }

        $data['gift_product'] = $this->session->data['send_as_gift'];

        ///////////// save warehouses data
        if (isset($this->session->data['warehouses_products'])) {
            $product_ids_wrs = [];

            $order_wr_products['wrs_name'] = $this->session->data['warehouses_products']['wrs_name'];
            $order_wr_products['wrs_duration'] = $this->session->data['warehouses_products']['wrs_duration'];
            $order_wr_products['wrs_costs'] = $this->session->data['warehouses_products']['wrs_costs'];
            foreach ($this->session->data['warehouses_products']['products'] as $key => $product) {
                $product_ids_wrs[$product['product_id']] = $product['warehouse'];
            }
            $order_wr_products['products'] = $product_ids_wrs;

            $data['warehouse_products'] = json_encode($order_wr_products);
        } else {
            $data['warehouse_products'] = '';
        }

        $this->initializer([
            'getResponse' => 'module/get_response/settings',
            'mailchimp' => 'module/mailchimp/settings',
        ]);

        if (
            $this->getResponse->isActive() &&
            $this->getResponse->hasTag('abandoned') &&
            isset($this->session->data['get_response_module']['update_order']) == false
        ) {
            if ($getResponseContact = $this->getResponse->accountExists($data['email'])) {

                $getResponseContact['unique_tags'] = array_column($getResponseContact['tags'], 'name');
                $getResponseContact['unique_tags'] = array_flip($getResponseContact['unique_tags']);

                if (isset($getResponseContact['unique_tags']['abandoned']) == false) {
                    $this->getResponse->updateContactTags($getResponseContact['contactId'], 'abandoned');
                    $this->session->data['get_response_module']['update_order'] = true;
                }
            } else {
                $this->getResponse->addContact($data, 'abandoned');
                $this->session->data['get_response_module']['update_order'] = true;
            }
        }

        if (
            $this->mailchimp->isActive() &&
            $this->mailchimp->hasTag('abandoned') &&
            isset($this->session->data['mailchimp_module']['update_order']) == false
        ) {
            if ($mailChimpSubscriber = $this->mailchimp->subscriberExists($data['email'])) {

                $mailChimpSubscriber['unique_tags'] = array_column($mailChimpSubscriber['tags'], 'name');
                $mailChimpSubscriber['unique_tags'] = array_flip($mailChimpSubscriber['unique_tags']);

                if (isset($mailChimpSubscriber['unique_tags']['abandoned']) == false) {
                    $this->mailchimp->updateSubscriberTags([
                        ['name' => 'abandoned', 'status' => 'active']
                    ], $data['email']);
                    $this->session->data['mailchimp_module']['update_order'] = true;
                }
            } else {
                $this->mailchimp->addNewSubscriber($data, 'abandoned');
                $this->session->data['mailchimp_module']['update_order'] = true;
            }
        }

        // check delivery slot app
        $this->load->model('module/delivery_slot/settings');
        $delivery_slot_status = $this->model_module_delivery_slot_settings->isActive();
        if ($delivery_slot_status == true) {
            $data['slot']['slot_date'] = $this->request->post['slot']['entry_Delivery_slot_date'];
            $data['slot']['id_slot'] = $this->request->post['slot']['delivery_slot'];
            if (isset($data['slot']['slot_date']) && !empty($data['slot']['slot_date']))
                $data['slot']['slot_date_dmy_format'] = DateTime::createFromFormat('m-d-Y', $data['slot']['slot_date'], new DateTimeZone($this->config->get('config_timezone') ?? 'UTC'))->format('Y-m-d');
            else
                $data['slot']['slot_date_dmy_format'] = "now";

            $data['slot']['dayes'] = [
                $this->language->get('entry_sunday'),
                $this->language->get('entry_monday'),
                $this->language->get('entry_tuesday'),
                $this->language->get('entry_wednesday'),
                $this->language->get('entry_thursday'),
                $this->language->get('entry_friday'),
                $this->language->get('entry_saturday')
            ];
        }

        $this->load->model('quickcheckout/order');

        if (preg_match("/1.5.2/i", VERSION)) {
            $this->model_quickcheckout_order->updateOrder152($this->session->data['order_id'], $data);
        } elseif (preg_match("/1.5.1/i", VERSION)) {
            $this->model_quickcheckout_order->updateOrder151($this->session->data['order_id'], $data);
        } else {
            $this->model_quickcheckout_order->updateOrder($this->session->data['order_id'], $data);
        }
    }

    private function location($address)
    {
        if (isset($address['location']) && count($latlng = explode(',', $address['location'])) === 2) {
            return [
                'lat' => $latlng[0],
                'lng' => $latlng[1],
            ];
        }

        if (isset($address['lat']) &&  $address['lat']) {
            return  [
                'location' => $address['location'],
                'lat' => $address['lat'],
                'lng' => $address['lng'],
            ];
        } else if (isset($address['location'])) {
            return  [
                'location' => $address['location']
            ];
        }
        return null;
    }
}
