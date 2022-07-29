<?php

class ControllerSaleStockzonesDropShipOrder extends Controller {

  	private $error = [];

   	public function __construct($registry){
      parent::__construct($registry);

      if (!$this->user->hasPermission('modify', 'module/stockzones/settings')) {
        $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
      }

      if( !\Extension::isInstalled('stockzones') ){
        $this->response->redirect($this->url->link('error/permission', '', 'SSL'));
      }
    }

  	/**
  	* Display the creation form to fill in by the user.
  	*/
  	public function create(){
  	    $this->load->language('module/stockzones');
        $this->document->setTitle($this->language->get('create_heading_title'));

        $this->load->model('module/stockzones/order');
        $has_stockzones_products = $this->model_module_stockzones_order->orderHasStockzonesProducts($this->request->get['order_id']);
        $is_stockzones_order   = $this->model_module_stockzones_order->isStockzonesOrder($this->request->get['order_id']);
        // var_dump($is_stockzones_order);die();
        // var_dump($has_stockzones_products);die();
        if( !$has_stockzones_products || $is_stockzones_order ){
            $this->session->data['error'] = $this->language->get('error_no_stockzones_products');
            $this->response->redirect($this->url->link('sale/order/info?order_id='. $this->request->get['order_id'] , '' , true));
        }
        
        //Breadcrumbs
        $this->data['breadcrumbs'] = $this->_createBreadcrumbs($this->request->get['order_id']);

        //Get config settings
        $this->load->model('sale/order');        
        $order = $this->model_sale_order->getOrder($this->request->get['order_id']);
        $order['shipping_address_location'] = json_decode($order['shipping_address_location'], true);
        $this->data['order'] = $order;

        //Get Stockzones Countries list...
        $this->load->model('module/stockzones/address');
        $this->data['countries'] = $this->model_module_stockzones_address->getStockzonesCountries();

        //render view template
        $this->template = 'module/stockzones/order/create.expand';
        $this->children = [ 'common/header' , 'common/footer' ];
        $this->response->setOutput($this->render());
    }

    /**
    * Store new order.
    */
    public function store(){
        if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

          //Validate form fields
          if ( !empty($this->_validateForm()) ){
            $result_json['success'] = '0';
            $result_json['errors']  = $this->error;
          }
          else{
            $this->load->model('module/stockzones/order');
            $this->load->model('sale/order');

            $order_id = $this->request->post['order_id'];
            $order    = $this->model_sale_order->getOrder($order_id);

            $data = [
                'first_name'              => $this->request->post['stockzones']['first_name'],
                'last_name'               => $this->request->post['stockzones']['last_name'],
                'email'                   => $this->request->post['stockzones']['email'],
                'phone'                   => $this->request->post['stockzones']['phone'],
                'mobile_number'           => $this->request->post['stockzones']['mobile_number'],
                'country_code'            => $this->request->post['stockzones']['country_code'],
                'delivery_country'        => $this->request->post['stockzones']['delivery_country'],
                'delivery_state'          => $this->request->post['stockzones']['delivery_state'],
                'delivery_city'           => $this->request->post['stockzones']['delivery_city'],
                'delivery_zip_code'       => $this->request->post['stockzones']['delivery_zip_code'],
                'delivery_address_line_1' => $this->request->post['stockzones']['delivery_address_line_1'],
                'delivery_address_line_2' => $this->request->post['stockzones']['delivery_address_line_2'],
                'delivery_country_code'   => $this->request->post['stockzones']['delivery_country_code'],
                'delivery_phone'          => $this->request->post['stockzones']['delivery_phone'],
                'delivery_mobile_number'  => $this->request->post['stockzones']['delivery_mobile_number'],
                'delivery_location'       => $this->request->post['stockzones']['delivery_location'],
                'delivery_latitude'       => $this->request->post['stockzones']['delivery_latitude'],
                'delivery_longitude'      => $this->request->post['stockzones']['delivery_longitude'],
                'product_slug'            => ($slugs = $this->model_module_stockzones_order->getProductsSlug($order_id)),
                'billing_country'         => '',
                'billing_state'           => '',
                'billing_city'            => '',
                'billing_zip_code'        => '',
                'billing_address_line_1'  => '',
                'billing_address_line_2'  => '',
                'billing_phone'           => '',
                'billing_mobile_number'   => '',
                'billing_country_code'    => '',
                'billing_location'        => '',
                'billing_latitude'        => '',
                'billing_longitude'       => '',
                'is_billing_address'      => true,
                'product_detail'          => $this->model_module_stockzones_order->getOrderStockzonesProducts($order_id, $this->request->post['stockzones']),
                'slug'          => $slugs,
                'currency'      => $order['currency_code'],
                'api_type'      => 'web',
                'request_from'  => 'api',
            ];

            // echo '<pre>'; print_r($data); die();
            $response = $this->model_module_stockzones_order->addNewStockzonesOrder($data);
            // echo '<pre>'; print_r($response); die();
            
            if($response['data']['status'] == 'success'){
                //add new order in stockzones_order
                $this->model_module_stockzones_order->addNewOrderLocally( $response['data']['order_number'] ?: 'MAKC2SLDY' , $order_id);
                $result_json['success_msg'] = $response['data']['message'];
                $result_json['success']     = '1';

                //redirect
                $result_json['redirect'] = '1';
                $result_json['to'] = "sale/order/info?order_id=".$order_id;
            }
            else{
                $result_json['success'] = '0';
                $result_json['errors']  = 'ERROR: ' . $response['data']['message'];                
            }        
          }
          $this->response->setOutput(json_encode($result_json));
        }
        else{
          $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
        }
    }


    public function getStockzonesStates(){
        //Get Stockzones States list...
        $this->load->model('module/stockzones/address');
        $states = $this->model_module_stockzones_address->getStockzonesStates($this->request->post['country_id']);
        $this->response->setOutput(json_encode($states));
    }


    public function getStockzonesCities(){
        //Get Stockzones Cities list...
        $this->load->model('module/stockzones/address');
        $cities = $this->model_module_stockzones_address->getStockzonesCities($this->request->post['country_id'], $this->request->post['state_id']);
        $this->response->setOutput(json_encode($cities));
    }

    /**
     * Checking if the request coming via ajax request or not.
     */
    private function _isAjax() {

        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * Form the breadcrumbs array.
     *
     * @return Array $breadcrumbs
     */
    private function _createBreadcrumbs($order_id){      
        return [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', '', 'SSL')
            ],
            [
                'text' => $this->language->get('text_orders'),
                'href' => $this->url->link('sale/component/orders', '', 'SSL')
            ],
            [
                'text' => $this->language->get('text_order') . '-' . $order_id,
                'href' => $this->url->link('sale/order/info', 'order_id=' . $order_id, 'SSL')
            ],
            [
                'text' => $this->language->get('create_heading_title'),
                'href' => $this->url->link('module/stockzones/sendOrder', 'order_id=' . $order_id , 'SSL')
            ],                                
        ];
    }

    /**
    * Validate form fields.
    *
    * @return bool TRUE|FALSE
    */
    private function _validateForm(){
        $this->load->language('shipping/stockzones');

        if (!$this->user->hasPermission('modify', 'shipping/stockzones')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['stockzones']['requested_by']) < 1) ) {
          $this->error['receiver_name'] = $this->language->get('error_receiver_name');
        }

        if ((utf8_strlen($this->request->post['stockzones']['receiver_name']) < 1) ) {
          $this->error['receiver_name'] = $this->language->get('error_receiver_name');
        }
    }

}
  
