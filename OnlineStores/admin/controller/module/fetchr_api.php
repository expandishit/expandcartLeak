<?php
/*
  @Controller: Fetchr Api Controller.
  @Author: Moath Mobarak.
  @Version: 1.1.0
*/
class ControllerModuleFetchrApi extends Controller 
{
    private $error = array();

    public function index()
    {
        $this->load->model('marketplace/common');
        $market_appservice_status = $this->model_marketplace_common->hasApp('fetchr_api');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

        $this->load->language('module/fetchr_api');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->getForm();
    }

    public function getForm()
    {
        //Get title in form using fetchr language.
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_form'] = $this->language->get('text_config');

        $data['text_delivery'] = $this->language->get('text_delivery');
        $data['text_fulfildelivery'] = $this->language->get('text_fulfildelivery');
        $data['text_live'] = $this->language->get('text_live');
        $data['text_staging'] = $this->language->get('text_staging');
        $data['entry_fetchr_username'] = $this->language->get('entry_fetchr_username');
        $data['entry_fetchr_password'] = $this->language->get('entry_fetchr_password');
        $data['entry_fetchr_servicetype'] = $this->language->get('entry_fetchr_servicetype');
        $data['entry_fetchr_ready_shipping_status'] = $this->language->get('entry_fetchr_ready_shipping_status');
        $data['entry_fetchr_being_shipped_status'] = $this->language->get('entry_fetchr_being_shipped_status');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_push'] = $this->language->get('button_push');

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        //Show error msg.
        if (isset($this->session->data['api_error'])) {
            $data['api_error'] = $this->session->data['api_error'];
            unset($this->session->data['api_error']);
        } else {
            $data['api_error'] = '';
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->error['fetchr_token'])) {
            $data['error_fetchr_token'] = $this->error['fetchr_token'];
        } else {
            $data['error_fetchr_token'] = '';
        }

        if (isset($this->error['fetchr_address_id'])) {
            $data['error_fetchr_address_id'] = $this->error['fetchr_address_id'];
        } else {
            $data['error_fetchr_address_id'] = '';
        }

        //Show breadcrumbs.
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '', 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/fetchr_api', '', 'SSL')
        );

        //Push, Save & Cancel button.
        $data['push'] = $this->url->link('module/fetchr_api/orderList', '', 'SSL');
        $data['action'] = $this->url->link('module/fetchr_api/add', '', 'SSL');
        $data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');

        $data['fetchr_callback_url'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'];
        $data['fetchr_callback_url'] .= '/index.php?route=shipping/fetchr/callback';

        $data['fetchr_token'] = $this->config->get('fetchr_token');
        $data['fetchr_address_id'] = $this->config->get('fetchr_address_id');
        $data['fetchr_servicetype'] = $this->config->get('fetchr_servicetype');
        $data['fetchr_ready_shipping_status'] = $this->config->get('fetchr_ready_shipping_status');
        $data['fetchr_being_shipped_status'] = $this->config->get('fetchr_being_shipped_status');
        $data['fetchr_already_shipped_status'] = $this->config->get('fetchr_already_shipped_status');

        $this->data = $data;

        $this->template = 'module/fetchr_api_form.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    public function add()
    {
        $this->load->language('module/fetchr_api');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('fetchrapi/fetchr');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if ( !$this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->error;
            }
            else
            {
                $this->load->model('setting/setting');
                $this->model_setting_setting->editSetting('fetchr', $this->request->post);
                $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';
            }

            $this->response->setOutput(json_encode($result_json));
            return;
        }
        
        $this->getForm();
    }

    public function orderList()
    {
        unset($this->session->data['api_error']);
                    
        $mylog = new Log('orderlist_' . date('Ymd') . '.log');
        $datalist = [];
        $this->load->model('fetchrapi/fetchr');
        $this->load->language('module/fetchr_api');

        $fetchr_servicetype = $this->config->get('fetchr_servicetype');
        $fetchr_accounttype = 1;
        $fetchr_token = $this->config->get('fetchr_token');
        $fetchr_address_id = $this->config->get('fetchr_address_id');

        //Fetch orders based status 'Ready for Pick up'.
        $orders = $this->model_fetchrapi_fetchr->getOrders();

        $client_info = array(
            'accounttype' => $fetchr_accounttype,
            'servicetype' => $fetchr_servicetype,
            'address_id' => $fetchr_address_id,
            'token' => $fetchr_token
        );
        $mylog->write('client_info: ' . json_encode($client_info));
        if (!empty($orders)) {
            //Service type = 1 (Fulfilment+Delivery), Service type = 0 (Delivery).
            if ($fetchr_servicetype == 1) {
                foreach($orders as $key => $order)
                {
                    $mylog->write('fulfillment_order_from_db: '.json_encode($order));
                    $item_list = [];

                    $products = $this->model_fetchrapi_fetchr->getOrderProducts($order['order_id']);
                    $this->load->model('setting/setting');
                    $store_info = $this->model_setting_setting->getSetting('flat', $order['store_id']);
                    $extra_fee = $store_info['flat_cost'];
                    
                    for($i=0;$i<=count($products)-1;$i++)
                    {
                        $item_list[] = array(
                        'name'                    => $products[$i]['name'],
                        'sku'                     => $products[$i]['sku'],
                        'quantity'            => $products[$i]['quantity'],
                        'price_per_unit'        => $products[$i]['price'],
                        'processing_fee'        => ''
                        );
                    }
                    $data = array('data' => array(array(
                        'items' => $item_list,
                                'warehouse_location' => array(
                                    'id' => $fetchr_address_id
                                ),
                        'details' => array(
                            'discount'              => '',
                            'extra_fee'             => $extra_fee,
                            'payment_type'          => ($order['payment_code'] == 'COD') ? 'cod' : $order['payment_code'],
                            'order_reference'       => $order['order_id'],
                            'customer_name'         => $order['firstname'].' ' .$order['lastname'],
                            'customer_phone'        => $order['telephone'],
                            'customer_email'        => $order['email'],
                            'customer_address'      => $order['payment_address_1'],
                            'customer_latitude'     => '',
                            'customer_longitude'    => '',
                            'customer_city'         => $order['zone_name'],
                            'customer_country'      => $order['payment_country'],
                            'comments'              => $order['comment']
                        )
                    )));
                    
                    $mylog->write('fulfillment_origin_request: '.json_encode($data));
                    $this->send_fulfillment_xapi($data, $order['order_id'], $fetchr_token,$fetchr_address_id,$mylog,$fetchr_accounttype);
                    unset($data);
                }

                //If count order = 1 go view order else go order list.
                if (count($orders) == 1) {
                    $this->redirect($this->url->link('sale/order/info', 'order_id=' . $orders[0]['order_id'], 'SSL'));
                } else {
                    $this->redirect($this->url->link('sale/order', '', 'SSL'));
                }
            }
            else {
                $post_data['client_address_id']=$fetchr_address_id;
                
                foreach($orders as $key => $order)
                {
                    $mylog->write('dropship_order_from_db: '.json_encode($order));
                    $data[] = array(
                            'order_reference'    => $order['order_id'],
                            'name'    => $order['firstname'] . ' ' . $order['lastname'],
                            'email'    => $order['email'],
                            'phone_number'    => $order['telephone'],
                            'alternate_phone' => '',
                            'receiver_country' => $order['payment_country'],
                            'receiver_city' => $order['zone_name'],
                            'address' => $order['payment_address_1'],
                            'payment_type' => (strcasecmp($order['payment_code'],'COD') == 0 ) ? 'COD' : 'Credit Card',
                            'total_amount' => (float)$order['total'],
                            'order_package_type' => '',
                            'bag_count' => '',
                            'weight' => '',
                            'description' => 'NA',
                            'comments' => $order['comment'],
                            'latitude' => '',
                            'longitude' => ''
                    );
                }
                $post_data['data']=$data;
                $datalist = array(
                    'token' => $fetchr_token,
                    'address_od' => $fetchr_address_id,
                    'accounttype' => $fetchr_accounttype,
                    'data' => $post_data,
                    'mylog'=> $mylog
                );
                $mylog->write('dropship_origin_request: '.json_encode($data));
                
                $this->send_dropship_xapi($datalist);
                //If count order = 1 go view order else go order list.
                if (count($orders) == 1) {
                    $this->redirect($this->url->link('sale/order/info', 'order_id=' . $orders[0]['order_id'], 'SSL'));
                } else {
                    $this->redirect($this->url->link('sale/order', '', 'SSL'));
                }
            }
        }
        else {
            $this->session->data['api_error'] = $this->language->get('api_no_orders');
            $this->redirect($this->url->link('module/fetchr_api', '', 'SSL'));
        }
    }

    //Fulfilment+Delivery send To ERP.
    protected function send_fulfillment_xapi($data, $orderId, $token, $address_id, $mylog, $accounttype)
    {
        $response = null;
        try {
            $ch = curl_init();
            $url = 'https://api.order.fetchr.us';
            
            $url = $url . '/fulfillment';
            $data_string = json_encode($data, JSON_UNESCAPED_UNICODE);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $headers[] = 'Authorization: Bearer '.$token;
            $headers[] = 'Content-Type: application/json';

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            $results = curl_exec($ch);
            $mylog->write('fulfillment_request_body_to_xapi: '.json_encode($data_string));
            curl_close($ch);
            // validate response
            $decoded_response = json_decode($results, true);
            $mylog->write('fulfillment_response_from_xapi: '.json_encode($decoded_response));

            $decoded_response = $this->build_fulfillment_response_from_xapi($decoded_response);
            $mylog->write('fulfillment_origin_response: '.json_encode($decoded_response));

            //IF Tracking num found Save in order and change status to 'Fetchr Shipping'.
            // Change: ['response']->tracking_no  to ['tracking_no']
            if (!empty($decoded_response['tracking_no']) && $decoded_response['success']) {
                $save_track = $this->model_fetchrapi_fetchr->saveTrackingStatus($orderId, $decoded_response['tracking_no'], null, $accounttype);

            }else{
                if(!empty($decoded_response['error_message'])) {
                    $this->session->data['api_error'] = $decoded_response['error_message'];
                } else {
                    $this->session->data['api_error'] = $decoded_response['awb'];
                }

            $this->response->redirect($this->url->link('module/fetchr_api', 'token=' . $this->session->data['token'], 'SSL'));
            }

        }catch (Exception $e) {
            echo (string) $e->getMessage();
            $log->write($e->getMessage(), 'exception');
        }

        return $results;

    }

    protected function build_fulfillment_request_to_xapi($v1_request, $address_id) {
        foreach($v1_request as $key => $data) {
            $items = null;
            $extra_fee = null;
            foreach($data['order']['items'] as $key => $item) {
                $items[] = array(
                  'name' => $item['name'],
                  'sku' => $item['sku'],
                  'quantity' => $item['quantity'],
                  'price_per_unit' => $item['price']
                );
                if ($extra_fee == null) {
                    $extra_fee = $item['COD'];
                }
            }
            $details = $data['order']['details'];
            $datalist[] = array(
              'items' => $items,
              'warehouse_location' => array(
                  'id' => $warehouse_id
              ),
              'details' => array(
                  'discount' => $details['discount'],
                  'extra_fee' => $extra_fee,
                  'payment_type' => $details['payment_method'],
                  'order_reference' => $details['order_id'],
                  'customer_name' => $details['customer_firstname'] . ' ' . $details['customer_lastname'],
                  'customer_phone' => $details['customer_mobile'],
                  'customer_email' => $details['customer_email'],
                  'customer_address' => $details['order_address'],
                  'customer_city' => $details['customer_city'],
                  'customer_country' => $details['customer_country']
              )
            );
        }
  
        $request = array(
            'data' => $datalist
        );
  
        return $request;
    }

    protected function build_fulfillment_response_from_xapi($xapi_response) {
        $success = $xapi_response['status'] == 'success';
        $order_success = $xapi_response['data'][0]['status'] == 'success';
        $message = $xapi_response['data'][0]['message'];
        if(empty($message)) {
            $message = $xapi_response['message'];
        }
        $error_code = $xapi_response['data'][0]['error_code'];
        if(empty($error_code)) {
            $error_code = $xapi_response['error_code'];
        }
  
        if(substr($message, 0, 13) == 'SKU NOT FOUND') {
            $awb = 'SKU not found';
        } else {
            $awb = $message;
        }
  
        if($success) {
            $success_response = array(
                'tracking_no' => $xapi_response['data'][0]['tracking_no'],
                // error message?
                'awb' => $awb,
                'success' => $order_success
            );
            return $success_response;
        } else {
            $error_response = array(
                'status' => 'error',
                'error_code' => $error_code,
                'error_message' => $message
            );
            return $error_response;
        }
    }
  
/*
    //Fulfilment+Delivery send To ERP.
    protected function send_Fulfilment_Delivery_ToErp($data, $orderId)
    {
        $response = null;
        try {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);

            $ch = curl_init('https://menavip.com/api/fulfilmentsorders/');                                                                      
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);                                                                  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                'Content-Type: application/json',                                                                                
                'Content-Length: ' . strlen($data))                                                                       
            );   
            $response = curl_exec($ch);
            curl_close($ch);
            // validate response
            $decoded_response = (array) json_decode($response);
            
            if(!is_array($decoded_response))
                return $response;

            if ($decoded_response['error_message'] == 'SKU is Invalid!') {

                //Add error occurrd in error.log
                $this->log->write('Fetcher Shipping Error: ' . $decoded_response['error_message']);
                //Show error msg for clint.
                $this->session->data['api_error'] = 'Error occurrd in <strong>Order ID: </strong>' . $orderId . ' <strong> Message: </strong>' . $decoded_response['error_message'];
                $this->redirect($this->url->link('module/fetchr_api', '', 'SSL'));
            }

            //If Tracking num found Save in order and change status to 'Fetchr Shipping'.
            if (!empty($decoded_response["tracking_no"]) && $decoded_response['tracking_no']) {
                $save_track = $this->model_fetchrapi_fetchr->saveTrackingStatus($orderId, $decoded_response["tracking_no"], $this->language->get('text_tracking_url'));
            } else {
                $this->session->data['api_error'] = $this->errorCode();
                $this->redirect($this->url->link('module/fetchr_api', '', 'SSL'));
            }
        }
        catch (Exception $e) {
            echo (string) $e->getMessage();
        }

        return $response;
    }
*/
      //Delivery Data Send to XAPI.
  protected function send_dropship_xapi($datalist)
  {
    $results = null;
    try {
      $ch = curl_init();

      $url = 'https://api.order.fetchr.us';

      $datalist['mylog']->write('dropship_request_url_to_xapi: '. $url);
      $url = $url . '/order';
      
      $data_string = json_encode($datalist['data']);

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST' );
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $headers[] = 'Authorization: Bearer '.$datalist['token'];
      $headers[] = 'Content-Type: application/json';
    
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

      $results = curl_exec($ch);
      $datalist['mylog']->write('dropship_request_body_to_xapi: '.json_encode($data_string));
      curl_close($ch);
      // validate response
      $decoded_response = json_decode($results, true);
      $datalist['mylog']->write('dropship_response_from_xapi: '.json_encode($decoded_response));
      $decoded_response = $this->build_dropship_response_from_xapi($decoded_response);
      $datalist['mylog']->write('dropship_origin_response: '.json_encode($decoded_response));

      $results = json_decode($results);
      
      // Error from api
      if($results->status == "error"){
        $this->session->data['api_error'] = $results->message;
        $this->response->redirect($this->url->link('module/fetchr_api'));
      }
      //IF Tracking number found Save in order and change status to 'Fetchr Shipping'.
      foreach ($datalist['data']['data'] as $key => $value) {
        if (!empty($decoded_response[$value['order_reference']]) && $results->data['0']->status == "success") {
            $order_reference = $value['order_reference'];
            $tracking_no = $results->data['0']->tracking_no;
            $awb_link = $results->data['0']->awb_link;
            
            $this->model_fetchrapi_fetchr->saveTrackingStatus($order_reference, $tracking_no, $awb_link);

        }else{
          $this->session->data['api_error'] = $decoded_response['error_message'];
          $this->response->redirect($this->url->link('module/fetchr_api', 'token=' . $this->session->data['token'], 'SSL'));

        }
      }

    }catch (Exception $e) {
        $datalist['mylog']->write('exception: '. $e->getMessage());
        $this->session->data['api_error']=$e->getMessage();
    }

    return $response;
  }

/*
    //Delivery send To ERP.
    protected function send_Delivry_ToErp($data)
    {
        $response = null;

        try {
            $ERPdata = 'args='.json_encode($data, JSON_UNESCAPED_UNICODE);
            $ch = curl_init();
            $url = 'http://www.menavip.com/client/api/';
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $ERPdata);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            // validate response
            $decoded_response = json_decode($response, true);

            if(!is_array($decoded_response))
                return $response;

            //If Tracking number found Save in order and change status to 'Fetchr Shipping'.
            foreach ($data['data'] as $key => $value) {
                if (!empty($decoded_response[$value['order_reference']]) && $decoded_response['status'] == 'success') {
                    $save_track = $this->model_fetchrapi_fetchr->saveTrackingStatus($value['order_reference'], $decoded_response[$value['order_reference']], $this->language->get('text_tracking_url'));
                } else {
                    $this->session->data['api_error'] = $this->errorCode($decoded_response['error_code']);
                    $this->redirect($this->url->link('module/fetchr_api', '', 'SSL'));
                }
            }
        }
        catch (Exception $e) {
            echo (string) $e->getMessage();
        }

        return $response;
    }
*/
    public function uninstall() {

    }

    public function install() {
        $fetchrStatuses = [
            [1 => ["name" => "Fetchr Shipping"] ,2 => ["name" => "شحن فيتشر"] ],
            [1 => ["name" => "Delivery Scheduled"] ,2 => ["name" => "تحديد استلام فيتشر"] ],
            [1 => ["name" => "Scheduled for Delivery"] ,2 => ["name" => "تم تحديد استلام فيتشر"] ],
            [1 => ["name" => "Held at Fetchr" ] ,2 => ["name" => "تم الاستلام فيتشر" ] ],
            [1 => ["name" => "In Transit" ] ,2 => ["name" => "جاري التحضير فيتشر" ] ],
            [1 => ["name" => "Ready for Pick up" ] ,2 => ["name" => "جاهزه للتسليم فيتشر" ] ],
            [1 => ["name" => "Delivered" ] ,2 => ["name" => "تم التسليم فيتشر" ] ]
        ];

        $query = $this->db->query("SELECT COUNT(*) colcount
                                    FROM INFORMATION_SCHEMA.COLUMNS
                                    WHERE  table_name = 'order'
                                    AND table_schema = DATABASE()
                                    AND column_name = 'tracking'");
        $result = $query->row;
        $colcount = $result['colcount'];

        if($colcount <=0 ) {
            $this->db->query("ALTER TABLE `order` ADD COLUMN `tracking`  varchar(3000) NULL");
        }
        
        $exist = $this->db->query("
            SELECT * FROM `" . DB_PREFIX  . "order_status` WHERE name IN ('Fetchr Shipping', 'Ready for Pick up');
        ");

        if ($exist->num_rows == 0) {
            $this->load->model('localisation/order_status');
            foreach($fetchrStatuses as $status){
                $status = ["order_status" => $status];
                $this->model_localisation_order_status->addOrderStatus($status);
            }
        }
    }

    protected function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'module/fetchr_api')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if ((utf8_strlen($this->request->post['fetchr_token']) < 34) ) {
            $this->error['fetchr_token'] = $this->language->get('error_fetchr_token');
        }
    
        if ($this->request->post['fetchr_address_id']) {
            if ((utf8_strlen($this->request->post['fetchr_address_id']) < 10) ) {
                $this->error['fetchr_address_id'] = $this->language->get('error_fetchr_address_id');
            }
        }
        return !$this->error;
    }

    //Fuction handle error code return API and show error msg.
    public function errorCode($code = '')
    {
        $msg = '';

        switch ($code) {
            case 1001:
                $msg = 'Error: Order reference is missing in one of the posted orders.';
                break;
            case 1002:
                $msg = 'Error: Name is missing in one of the posted orders.';
                break;
            case 1003:
                $msg = 'Error: Email is missing in one of the posted orders.';
                break;
            case 1004:
                $msg = 'Error: Phone number is missing in one of the posted orders.';
                break;
            case 1005:
                $msg = 'Error: Address is missing in one of the posted orders.';
                break;
            case 1006:
                $msg = 'Error: City is missing in one of the posted orders.';
                break;
            case 1007:
                $msg = 'Error: Payment type is missing in one of the posted orders.';
                break;
            case 1008:
                $msg = 'Error: Amount is missing in one of the posted orders.';
                break;
            case 1009:
                $msg = 'Error: Description is missing in one of the posted orders.';
                break;
            case 1011:
                $msg = 'Error: Method name not found.';
                break;
            case 1012:
                $msg = 'Error: Client phone number of one of the posted orders does not belong to any of the previously provided clients.';
                break;
            case 1013:
                $msg = 'Error: The posted data contains non ascii characters.';
                break;
            case 1014:
                $msg = 'Error: Internal server error.';
                break;
            case 1015:
                $msg = 'Error: Invalid username or password.';
                break;
            default:
                $msg = 'Error: Access denied.';
                break;
        }

        $this->log->write('Fetcher Shipping Error: ' . $msg);

        return $msg;
    }
    protected function build_dropship_response_from_xapi($xapi_response) {
        $success_response = array();
        $error_response = array();
        $success = $xapi_response['status'] == 'success';
        $error_code = $xapi_response['error_code'];
        $error_message = $xapi_response['message'];
        foreach ($xapi_response['data'] as $key => $item) {
            $success = $success && ($item['status'] == 'success');
            $success_response[$item['order_reference']] = array(
                'tracking_no' => $item['tracking_no'],
                'awb_link' => $item['awb_link']
              );
            if($item['status'] != 'success' && empty($error_code)) {
                $error_code = $item['error_code'];
                $error_message = $item['message'];
            }
        }
        if($success) {
            $success_response['status'] = 'success';
            // shipment_data is not used
            return $success_response;
        } else {
            $error_response['status'] = 'error';
            $error_response['error_code'] = $error_code;
            $error_response['error_message'] = $error_message;
            return $error_response;
        }
    }
}
