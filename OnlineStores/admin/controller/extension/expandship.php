<?php

class ControllerExtensionExpandShip extends Controller
{
    private $route = 'extension/expandship';
    private $result_json = ['success' => '0', 'errors' => []];

    public function brief() {
        $this->load->model($this->route);
		$this->language->load($this->route);
        $this->template = 'extension/expandship/ExpandShipBrief.expand';
        $this->children = array('common/header', 'common/footer');
        $response = $this->model_extension_expandship->getAppBrief();
        $this->data['brief'] = ($response->status_code == 200) ? $response->brief : '';
        $this->data['register_url'] = $this->url->link('extension/expandship/register', '', 'SSL');
        $this->response->setOutput($this->render());
	}

    public function index()
    {
//        $this->language->load('shipping/bosta');
        $this->load->model($this->route);
        $this->language->load($this->route);
        $this->load->model('localisation/order_status');

        $expand_ship = $this->model_extension_expandship->isInstalled() ? $this->model_extension_expandship->getSettings() : [];

        //redirect user to register if not register
        if (empty($expand_ship))
            return $this->redirect($this->url->link('extension/expandship/register'));

        //check if service id disabled for this merchant
//        if (isset ($expand_ship['merchant']) && $expand_ship['merchant']->service_status == false)
//            return $this->redirect($this->url->link('extension/expandship/serviceDisable'));

//        //check if user is pending then redirect him to settings
//        if (isset($expand_ship['status']) && $expand_ship['merchant']->status == 'pending')
//            return $this->redirect($this->url->link('extension/expandship/inReview'));

        //get Top Up packages
        $this->data['packages'] = $this->model_extension_expandship->getTopUpPackages();
        $indexPageData = $this->model_extension_expandship->getIndexPageData();
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $this->data['expandship'] = $expand_ship;
        $this->data['update_setting_url'] = $this->url->link('extension/expandship/updateSetting', '', 'SSL');
        $this->data['topup_url'] = $this->url->link('extension/expandship/topup', '', 'SSL');
        $this->data['balance_history_url'] = $this->url->link('extension/expandship/getBalanceHistory', '', 'SSL');
        $time_zone 	 = $this->config->get('config_timezone') ?: 'UTC';
        $balance_history=(isset($indexPageData->balance_history))?$indexPageData->balance_history:[];
        foreach ($balance_history as &$balance){
            if (isset($balance->created_at)){
                $balance->created_at =
                    $this->__convertToTimeZone($balance->created_at , $time_zone);
            }

        }
        $this->data['data'] = $indexPageData;

        $this->load->model('localisation/geo_zone');
        $this->data['geo_zones']  = $this->model_localisation_geo_zone->getGeoZones();


        //continue to index page
        $this->document->setTitle($this->language->get('heading_title_settings'));
        $this->template = 'extension/expandship/index/index.expand';
        $this->children = array('common/header', 'common/footer');

        // Set Error
        if (isset($this->session->data['error'])) {
            $this->data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        }

        // Set Success.
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }
        $this->response->setOutput($this->render());
    }

    /////////////////////////////////////////////////////////////
    ///                 Payment And Balance                    //
    /////////////////////////////////////////////////////////////

    /***************** make topup request ***************/
    public function topup()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            $this->initializer(['account/stripe', 'extension/expandship']);
            $data = $this->request->post;

            \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
            $card = [
                'number' => str_replace(' ', '', $data['card_number']),
                'exp_month' => $data['exp_month'],
                'exp_year' => '20' . $data['exp_year'],
                'cvc' => $data['cvc'],
            ];

            //validate card
            if (!$this->stripe->validateCard($card)) {
                $response = [
                    'success' => '0',
                    'error' => 'INVALID_CREDENTIALS',
                    'errors' => $this->stripe->getErrors()
                ];
                return $this->response->json($response);
            }

            //create payment method with card information
            $payment_method = \Stripe\PaymentMethod::create(['type' => 'card', 'card' => $card]);

            //get stripe customer id if exist and create it if not
            $customerId = $this->expandship->getSettings()['stripe_customer_id'] ?? null;

            if (!$customerId) {
                $customer = $this->stripe->createCustomer($data['merchant_id'], $data['name'], $data['email']);
                if ($customer) {
                    $customerId = $customer->id;
                    //save customer data to database
                    $input = ['stripe_customer_id' => $customerId];
                    $this->model_extension_expandship->updateSettings($input);
                } else {
                    $response = [
                        'success' => '0',
                        'error' => 'CUSTOMER_ERROR',
                        'errors' => $this->stripe->getErrors()
                    ];
                    return $this->response->json($response);
                }
            }


            //create intent and confirm it in case of none 3d secure
            $intent = \Stripe\PaymentIntent::create([
                'customer' => $customerId,
                'amount' => $data['amount'] * 100,
                'currency' => 'usd',
                'confirm' => true,
                'payment_method_types' => ['card'],
                'payment_method' => $payment_method->id,
                'return_url' => $this->url->link('extension/expandship/stripe3DSecureReturn', '', 'SSL'),
                'description' => "Expandship package charge",
                'metadata' => [
                    "email" => $data['email'],
                    "name" => $data['name'],
                    "phone" => $data['phone'],
                    'package_id'=>$data['package_id']
                ]
            ]);

            //if card support 3D secure
            if ($intent->status == 'requires_action') {
                $response = ['success' => 2, 'location' => $intent->next_action->redirect_to_url->url,];
                return $this->response->json($response);
            } elseif ($intent->status == 'succeeded') {
                $topup =  $this->addToBalance(['package_id'=>$data['package_id'],'receipt_url'=>$intent->receipt_url ??'']);
                return $this->response->json($topup);
            }

        }
        $response = ['success' => 0, 'message' => "Invalid payment operation",];
        return $this->response->json($response);
    }

    public function stripe3DSecureReturn(){
        if( $_GET['payment_intent']){

            \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

            $intentObject = \Stripe\PaymentIntent::retrieve($_GET['payment_intent']);


            if($intentObject->status == 'succeeded'){
                $topup =$this->addToBalance([
                    'package_id'=>$intentObject->metadata->package_id,
                    'receipt_url'=>$intentObject->receipt_url ?? ''
                ]);
                if($topup['success'] == 1)
                    $this->session->data['esuccess'] = "Successful payment operation" ;
                else
                    $this->session->data['error'] = 'Payment Operation has failed,  please check with support';
            }else{
                $this->session->data['error'] = 'Payment Operation has canceled';
            }
        }
        else{
            $this->session->data['error'] = 'Payment Operation has canceled';
        }
        return  $this->response->redirect($this->url->link('extension/expandship/','',true));
    }

    function addToBalance($data=[]){
        $this->load->model($this->route);
        $response = $this->model_extension_expandship->topupBalance($data);
        if ($response->status_code == 200) {
            $result_json['success'] = '1';
            $this->session->data['success'] = "Successful payment operation";
        } else {
            $result_json['success'] = '0';
            $result_json['errors'] = $response->errors;
        }
        return $result_json;
    }

    /***************** Get Balance history ***************/
    public function getBalanceHistory()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'GET') {
            $this->load->model($this->route);
            $this->language->load($this->route);
            $response = $this->model_extension_expandship->getBalanceHistory($this->request->get);
            if ($response->status_code == 200) {
                $this->result_json['history'] = $response->balance_history;
                $this->result_json['success'] = '1';
                $this->session->data['success'] = $this->result_json['success_msg'] = $this->language->get('text_success');
            } else {
                $this->result_json['success'] = '0';
                $this->result_json['errors'] = $response->errors;
            }
        }
        return $this->response->setOutput(json_encode($this->result_json));
    }

    /////////////////////////////////////////////////////////////
    ///                        Order                           //
    /////////////////////////////////////////////////////////////

    /***************** create shipment order ***************/
    public function createShipment()
    {
        $this->load->model($this->route);
        $this->load->model('sale/order');
        $this->load->model('localisation/country');
        $this->language->load($this->route);
        $this->document->setTitle($this->language->get('heading_title_expandship'));
        $this->template = 'extension/expandship/shipment/create.expand';
        $this->children = array('common/header', 'common/footer');

        //get order info
        $order_info         = $this->model_sale_order->getOrder($this->request->get['order_id']);
        $products_category  = $this->model_sale_order->getOrderProductsCategoryName($this->request->get['order_id']);
        $this->data['order'] = $order_info ? [
            'id'            => $order_info['order_id'],
            'total'         => $order_info['total'],
            'full_name'     => $order_info['firstname'] . ' ' . $order_info['lastname'],
            'email'         => $order_info['email'],
            'phone'         => $order_info['telephone'],
            'address'       => $order_info['shipping_address_1'],
            'postal_code'   => $order_info['shipping_postcode'],
            'pay_type'      => $order_info['payment_code'] == 'cod' ? 'cod' : 'online',
            'country_code'  => $this->model_localisation_country->getCountryData($order_info['shipping_country_id'])['iso_code_2'],
            'shipment_type' => implode(' - ', array_column($products_category,'name')) ?? 'Other'
        ] : [];

        //get provider (voo, ..etc) countries
        $this->data['countries'] = $this->model_extension_expandship->getProviderCountries();

        //get merchant data for expandship
        $this->data['expandship'] = $this->model_extension_expandship->getSettings();
        $this->data['merchant'] = $this->model_extension_expandship->getMerchantInfo()->merchant ?? [];
        //set urls
        $this->data['provider_states_url'] = $this->url->link('extension/expandship/getProviderStates', '', 'SSL');
        $this->data['provider_cities_url'] = $this->url->link('extension/expandship/getProviderCities', '', 'SSL');
        $this->data['shipping_price_url'] = $this->url->link('extension/expandship/getShippingPrice', '', 'SSL');
        $this->data['store_shipment_url'] = $this->url->link('extension/expandship/storeShipment', '', 'SSL');
        $this->data['index_url'] = $this->url->link('sale/order/info?order_id='.$this->request->get['order_id'], '', 'SSL');
        $this->response->setOutput($this->render());
    }


    /***************** get provider states ***************/
    public function getProviderStates()
    {
        $this->load->model($this->route);
        $this->result_json['states'] = $this->model_extension_expandship->getProviderStates($this->request->get['country']);
        $this->result_json['success'] = '1';
        return $this->response->setOutput(json_encode($this->result_json));
    }

    /***************** get provider cities ***************/
    public function getProviderCities()
    {
        $this->load->model($this->route);
        $this->result_json['cities'] = $this->model_extension_expandship->getProviderCities($this->request->get['country'], $this->request->get['state']);
        $this->result_json['success'] = '1';
        return $this->response->setOutput(json_encode($this->result_json));
    }


    /***************** get shipping price  ***************/
    public function getShippingPrice()
    {
        $this->load->model($this->route);
        $this->language->load($this->route);
        $response = $this->model_extension_expandship->getShippingPrice($this->request->post);

        if ($response->status_code == 200 && !empty($response->price)) {
            $this->result_json['success'] = '1';
            $this->result_json['price'] = $response->price;
        } else {
            $this->result_json['success'] = '0';
            $this->result_json['errors'] = $this->language->get('text_shipping_not_available');
        }

        return $this->response->setOutput(json_encode($this->result_json));
    }


    /***************** get shipping price  ***************/
    public function storeShipment()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model($this->route);
            $this->language->load($this->route);
            $response = $this->model_extension_expandship->storeShipment($this->request->post);
            if ($response->status_code == 200) {

                //insert order shipping provider data
                $this->load->model('sale/order');
                $this->model_sale_order->createProviderOrder(['order_id'=>$this->request->post['expandcart_order_id'],'provider_name'=>'expandship','paid_to_merchant'=>"",'courier_name'=>$this->request->post['courier']]);

                //update order shipping method if not expandship
                $order = $this->model_sale_order->getOrder($this->request->post['expandcart_order_id']);
                $shipping_method = explode('.',$order['shipping_code']);
                $all_comments = ($order["comment"])?:"";
                if(!empty($this->request->post['notes'])){
                    $notes =$this->db->escape($this->request->post['notes']);
                    $all_comments = $this->db->escape(implode("<br>",[$all_comments,$notes]));
                }

                if(!isset($shipping_method[0]) || $shipping_method[0]!='expandship' || !empty($this->request->post['notes']))
                    $this->db->query("UPDATE " . DB_PREFIX . "`order` 
                    SET shipping_method = 'expandship', 
                    shipping_code= 'expandship.expandship',
                    comment ='". $all_comments."' ".
                    " WHERE order_id = '" . $this->request->post['expandcart_order_id'] . "'");

                /***************** Start ExpandCartTracking #347758  ****************/

                // send mixpanel shipped by expandship  event
                $this->load->model('setting/mixpanel');
                $this->model_setting_mixpanel->trackEvent('Shipped orders by ExpandShip');

                // send amplitude shipped by expandship event
                $this->load->model('setting/amplitude');
                $this->model_setting_amplitude->trackEvent('Shipped orders by ExpandShip');

                /***************** End ExpandCartTracking #347758  ****************/

                $this->result_json['success'] = '1';
                $this->session->data['success'] = $this->result_json['success_msg'] = $this->language->get('text_success');
            } else {
                $this->result_json['success'] = '0';
                $this->result_json['errors'] = $response->errors;
                $this->result_json['message'] = $response->message;
            }
        }
        return $this->response->setOutput(json_encode($this->result_json));
    }


    /***************** Reverse order ***************/
    public function reverseOrder()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model($this->route);
            $this->language->load($this->route);
            $response = $this->model_extension_expandship->reverseOrder($this->request->post);
            if ($response->status_code == 200) {

                //update order shipping method
                $this->result_json['success'] = '1';
                $this->session->data['success'] = $this->result_json['success_msg'] = $this->language->get('text_success');
            } else {
                $this->result_json['success'] = '0';
                $this->result_json['errors'] = $response->errors;
            }
        }
        return $this->response->setOutput(json_encode($this->result_json));
    }

    /////////////////////////////////////////////////////////////
    //                     Authentication                      //
    /////////////////////////////////////////////////////////////

    /***************** Show login Page ***************/

    public function login()
    {

        $this->load->model($this->route);
        $this->language->load($this->route);

        //get setting
        $merchant = $this->model_extension_expandship->isInstalled() ? $this->model_extension_expandship->getSettings() : [];

        //redirect user to register if not register
        if (empty($merchant))
            return $this->redirect($this->url->link('extension/expandship/register'));


        $this->document->setTitle($this->language->get('heading_title_expandship'));
        $this->template = 'extension/expandship/login.expand';
        $this->children = array('common/header', 'common/footer');
        $this->data['login_url'] = $this->url->link('extension/expandship/doLogin', '', 'SSL');
        $this->data['index_url'] = $this->url->link('extension/expandship/', '', 'SSL');
        $this->response->setOutput($this->render());
    }

    /***************** Do login Operation ***************/
    public function doLogin()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model($this->route);
            $response = $this->model_extension_expandship->login($this->request->post);
            if ($response->status_code == 200) {
                $this->result_json['success'] = '1';
                $this->session->data['success'] = $this->result_json['success_msg'] = $this->language->get('text_success');
            } else {
                $this->result_json['success'] = '0';
                $this->result_json['errors'] = $response->errors;
            }
        }
        return $this->response->setOutput(json_encode($this->result_json));
    }


    /***************** Register  ***************/
    public function register()
    {
        $this->load->model('sale/order');
        $total_orders = $this->model_sale_order->getTotalOrders();
        $this->load->model($this->route);
        $this->language->load($this->route);
        $lastOrderDate = $this->model_extension_expandship->lastOrderDate();
        $this->language->load('common/installation');

        //redirect merchant if installed
        if ($this->model_extension_expandship->isInstalled())
            return $this->redirect($this->url->link('extension/shipping/'));

        //make register operation
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            //send register request
            $response = $this->model_extension_expandship->register($this->request->post);
            if ($response->status_code == 200) {
                $this->result_json['success'] = '1';
                $this->session->data['success'] = $this->result_json['success_msg'] = $this->language->get('text_success');
            } else {
                $this->result_json['success'] = '0';
                $this->result_json['errors'] = $response->errors;
            }

            return $this->response->setOutput(json_encode($this->result_json));
        }


        $this->document->setTitle($this->language->get('heading_title_expandship'));
        $this->template = 'extension/expandship/NewRegister.expand';
        $this->children = array('common/header', 'common/footer');

        $total_products = $this->model_extension_expandship->getTotalProducts();
        //get register required data from api
        $this->data['data'] = json_decode(json_encode($this->model_extension_expandship->getRegisterDate()));
        $this->data['cancel_url']    = $this->url->link('extension/shipping/', '', 'SSL');
        $this->data['setting_url']    = $this->url->link('extension/expandship/', '', 'SSL');
        $this->data['no_order'] = 0;
        $this->data['product_source'] = $this->config->get('product_source');
        $this->data['total_products'] = $total_products;
        $this->data['joining_date'] = STORE_CREATED_AT;
        $this->data['free_or_paid'] = PRODUCTID;
        $this->data['total_orders'] = $total_orders;
        $this->data['last_order_date'] = $lastOrderDate;

        /***************** Start ExpandCartTracking #347758  ****************/

        // send mixpanel try register expandship event
        $this->load->model('setting/mixpanel');
        $this->model_setting_mixpanel->trackEvent('Try To Activate ExpandShip');

        // send amplitude try register expandship event
        $this->load->model('setting/amplitude');
        $this->model_setting_amplitude->trackEvent('Try To Activate ExpandShip');

        /***************** End ExpandCartTracking #347758  ****************/

        $this->response->setOutput($this->render());
    }

    /***************** get all states in country  ***************/
    public function getStatesByCountry()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->result_json['success'] = '0';
            $this->result_json['error'] = $this->error;
        } else {
            $this->load->model($this->route);
            $this->result_json['states'] = $this->model_extension_expandship->getStatesByCountry($this->request->post['country_id']);
            $this->result_json['success'] = '1';
        }

        return $this->response->setOutput(json_encode($this->result_json));
    }


    /***************** get all cities in state  ***************/
    public function getCitiesByState()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->result_json['success'] = '0';
            $this->result_json['error'] = $this->error;
        } else {
            $this->load->model($this->route);
            $this->result_json['cities'] = $this->model_extension_expandship->getCitiesByState($this->request->post['state_id']);
            $this->result_json['success'] = '1';
        }

        return $this->response->setOutput(json_encode($this->result_json));
    }

    /***************** Send Code ***************/
    public function sendCode()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model($this->route);
            $response = $this->model_extension_expandship->sendCode($this->request->post);
            if ($response->status_code == 200) {
                $this->result_json['success'] = '1';
                $this->session->data['success'] = $this->result_json['success_msg'] = $this->language->get('text_success');
            } else {
                $this->result_json['success'] = '0';
                $this->result_json['errors'] = $response->errors;
            }
        }
        return $this->response->setOutput(json_encode($this->result_json));
    }

    /***************** Check Code ***************/
    public function checkCode()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model($this->route);
            $response = $this->model_extension_expandship->checkCode($this->request->post);
            if ($response->status_code == 200) {
                $this->result_json['success'] = '1';
                $this->session->data['success'] = $this->result_json['success_msg'] = $this->language->get('text_success');
            } else {
                $this->result_json['success'] = '0';
                $this->result_json['errors'] = $response->errors;
            }
        }
        return $this->response->setOutput(json_encode($this->result_json));
    }



    /////////////////////////////////////////////////////////////
    //                   Service Unavailable Pages             //
    /////////////////////////////////////////////////////////////
    public function inReview()
    {

        $this->language->load('extension/expandship');
        $this->template = 'extension/expandship/extra_pages/review.expand';
        $this->document->setTitle($this->language->get('heading_title_settings'));
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    public function serviceDisable()
    {

        $this->language->load('extension/expandship');
        $this->document->setTitle($this->language->get('heading_title_expandship'));
        $this->template = 'extension/expandship/extra_pages/serviceDisable.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    public function settings()
    {

        $this->load->model($this->route);
        $this->language->load($this->route);
        $this->template = 'extension/expandship/index/index.expand';
        $this->document->setTitle($this->language->get('heading_title_settings'));
        $this->children = array('common/header', 'common/footer');
        $this->response->setOutput($this->render());
    }

    /////////////////////////////////////////////////////////////
    //                   Update App Settings                   //
    /////////////////////////////////////////////////////////////

    public function updateSetting()
    {

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            $this->load->model($this->route);
            $this->language->load($this->route);

            //update app configuration
            $input=[
                'status'=>$this->request->post['status'] ?? '0',
                'price'=>$this->request->post['price'],
                'checkout_text'=>$this->request->post['checkout_text']
            ];
            $this->model_extension_expandship->updateSettings($input);

            //update merchant profile
            $response = $this->model_extension_expandship->updateMerchantProfile($this->request->post);
            if ($response->status_code == 200) {
                $this->result_json['success'] = '1';
//                $this->session->data['success'] = $this->result_json['success_msg'] = $this->language->get('text_success');
            } else {
                $this->result_json['success'] = '0';
                $this->result_json['errors'] = $response->errors;
            }
        }
        return $this->response->setOutput(json_encode($this->result_json));
    }

    /**
     * @param $orderId
     * @return mixed
     */
    public function getOrderBill($orderId)
    {
        $this->load->model($this->route);
        return $this->model_extension_expandship->getOrderBill($orderId);
    }


    // print expandship order bill
    public function orderBill()
    {
        $order_id = $this->request->get['order_id'];
        $response = $this->getOrderBill($order_id);
        $billUrl = (isset($response->bill)) ? $response->bill : "";
        $content = ($billUrl) ? file_get_contents($billUrl) : "";
        try {
            $dom = new DOMDocument();
            if ($content) {
                $dom->loadHTML($content);
                $script = $dom->getElementsByTagName('script');
                $xpath = new DOMXPath($dom);
                $nlist = $xpath->query("//input[@id='homeUrl']");
                $node = $nlist->item(0);
                $node->parentNode->removeChild($node);

                $remove = [];
                foreach ($script as $item) {
                    $remove[] = $item;
                }
                foreach ($remove as $item) {
                    $item->parentNode->removeChild($item);
                }
            } else {
                $content = '<html><head></head><body><center>Page Not Found</center></body></html>';
                $dom->loadHTML($content);
            }
        } catch (Exception $e) {
            return false;
        }

        $html = $dom->saveHTML();
        $this->response->setOutput($html);
    }

    /**
     * @param string $time
     * @param string $toTz
     * @param string $fromTz
     * @return false|string
     */
    private function __convertToTimeZone($time = "", $toTz = 'UTC', $fromTz = 'UTC')
    {
        try {
            $date = new DateTime($time, new DateTimeZone($fromTz));
            $date->setTimezone(new DateTimeZone($toTz));
            $time = $date->format('d/m/Y h:i:s a');
        } catch (Exception $e) {
            return false;
        }
        return $time;
    }
}