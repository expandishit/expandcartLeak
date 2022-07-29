<?php

class ControllerModuleAbandonedCart extends Controller
{
    /**
    * @var array the validation errors array.
    */
    private $error = [];

    public function index()
    {
        if($this->request->get['content_url'] == "module/abandoned_cart"){
            $this->redirect($this->url->link('sale/component/orders', 'content_url=module/abandoned_cart/list', 'SSL'));
        }

        $this->initializer(['module/abandoned_cart/settings']);
        $this->language->load('module/abandoned_cart');
        $this->document->setTitle($this->language->get('abandoned_cart_heading_title'));
        $data['settings'] = $this->settings->getSettings();
        $data['links'] = [
            'submit' => $this->url->link('module/abandoned_cart/update', '', 'SSL'),
            'cancel' => $this->url->link('marketplace/home', '', 'SSL'),
        ];
        $data['breadcrumbs'] = $this->_createBreadcrumbs();
        $data['list']        = $this->url->link('module/abandoned_cart')->format();
        $this->data = $data;

        $this->template = 'module/abandoned_cart/settings.expand';
        $this->children = [ 'common/header' , 'common/footer' ];
        $this->response->setOutput($this->render_ecwig());
    }

    public function customizeMail(){
        $this->initializer(['module/abandoned_cart/settings']);
        $this->language->load('module/abandoned_cart');
        $this->language->load('marketing/mass_mail_sms');
        $this->document->setTitle($this->language->get('abandoned_cart_heading_title2'));
        $data['settings'] = $this->settings->getSettings();
        $data['links'] = [
            'submit' => $this->url->link('module/abandoned_cart/update', '', 'SSL'),
            'cancel' => $this->url->link('module/abandoned_cart', '', 'SSL'),
        ];
        $data['breadcrumbs'] = $this->_createBreadcrumbs();
        $this->data = $data;
        $this->template = 'module/abandoned_cart/customize_email_template.expand';
        $this->children = [ 'common/header' , 'common/footer' ];
        $this->response->setOutput($this->render_ecwig());
    }
    /**
    * Update Module Settings in DB settings table.
    *
    * @return JSON response.
    */
    public function update(){
     if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

          //Validate form fields
          if ( ! $this->_validateForm() ){
            $result_json['success'] = '0';
            $result_json['errors'] = $this->error;
          }
          else{
            $this->load->model('setting/setting');
            $this->load->language('module/abandoned_cart');
            $this->initializer(['module/abandoned_cart/settings']);

            if($this->request->post['abandoned_cart']['auto_send_mails'] &&
             !$this->db->check(['abandoned_order_cronjob'],'table')){
                $this->model_module_abandoned_cart_settings->createCronJobTable();
            }
            
            $this->model_setting_setting->insertUpdateSetting('abandoned_cart', ['abandoned_cart' => array_merge($this->settings->getSettings(), $this->request->post['abandoned_cart'])] );

            $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success']  = '1';
          }

          $this->response->setOutput(json_encode($result_json));
        }
        else{
          $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
        }
    }

    /**
    * Validate form fields.
    *
    * @return bool TRUE|FALSE
    */
    private function _validateForm(){

        $this->load->language('module/abandoned_cart');

        if (!$this->user->hasPermission('modify', 'module/abandoned_cart')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if( !\Extension::isInstalled('abandoned_cart') ){
          $this->error['not_installed'] = $this->language->get('error_not_installed');
        }

        if( isset($this->request->post['abandoned_cart']['mail_subject']) && utf8_strlen($this->request->post['abandoned_cart']['mail_subject']) < 3){
            $this->error['mail_subject'] = $this->language->get('error_mail_subject');
        }

        if( isset($this->request->post['abandoned_cart']['mail_message']) && utf8_strlen($this->request->post['abandoned_cart']['mail_message']) < 10){
            $this->error['mail_message'] = $this->language->get('error_mail_message');
        }

        if($this->error && !isset($this->error['error']) ){
          $this->error['warning'] = $this->language->get('error_warning');
        }
        return !$this->error;
    }

    /**
    * Form the breadcrumbs array.
    *
    * @return Array $breadcrumbs
    */
    private function _createBreadcrumbs(){

        $breadcrumbs = [
          [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '', 'SSL')
          ],
          [
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('marketplace/home', '', 'SSL')
          ],
          [
            'text' => $this->language->get('abandoned_cart_heading_title'),
            'href' => $this->url->link('module/abandoned_cart', '', 'SSL')
          ],
          [
            'text' => $this->language->get('abandoned_cart_settings'),
            'href' => $this->url->link('module/abandoned_cart', '', 'SSL')
          ]
        ];

        return $breadcrumbs;
    }  

    /**
    * Check if comming response in AJAX or not.
    *
    * @return bool TRUE|FALSE
    */
    private function _isAjax() {

        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }



    /** Old setup **/
    public function updateSettings()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            $this->response->setOutput(json_encode(['errors' => true]));

            return;
        }

        $this->initializer([
            'module/abandoned_cart/settings'
        ]);

        $data = $this->request->post['abandoned_cart'];

        $this->settings->updateSettings(['abandoned_cart' => $data]);

        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_settings_success');
        $result_json['success'] = '1';
        $result_json['redirect'] = '1';
        $result_json['to'] = $this->url->link('module/abandoned_cart')->format();

        $this->response->setOutput(json_encode($result_json));

        return;
    }
    /** /Old setup **/

    public function list()
    {
        if($this->request->get['content_url'] != "module/abandoned_cart/list"){
            $this->redirect($this->url->link('sale/component/orders', 'content_url=module/abandoned_cart/list', 'SSL'));
        }
        $this->language->load('module/abandoned_cart');
        $this->language->load('sale/order');
        $this->language->load('catalog/product_filter');

        $this->load->model('sale/order');

        $this->document->setTitle($this->language->get('abandoned_cart_heading_title'));

        $this->load->model('sale/customer');
        $this->load->model('sale/order');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('abandoned_cart_heading_title'),
            'href'      => $this->url->link('module/abandoned_cart', '', 'SSL'),
            'separator' => ' :: '
        );

        //$customers = $this->model_sale_customer->getCustomers();

        array_unshift($customers, [
            'customer_id' => 0,
            'name' => $this->language->get('text_guest')
        ]);

        $this->initializer([
            'module/abandoned_cart/settings',
            'emailedOrders' => 'module/abandoned_cart/emailed_orders',
        ]);

        $this->data['filterElements'] = [
            //'customers' => $customers,
            'totalRange' => $this->emailedOrders->getOrderMinMaxTotal(),
            'products' => $this->emailedOrders->getAllOrdersProducts()
        ];

        $this->template = 'module/abandoned_cart/list.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
    }

    public function datatable()
    {
        $this->load->model('sale/order');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            parse_str(html_entity_decode($this->request->post['filter']), $filterData);
            $filterData = $filterData['filter'];
        }

        $filterData['search'] = null;
        if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
            $filterData['search'] = $request['search']['value'];
        }

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => '',
            1 => 'order_id',
            2 => 'customer',
            4 => 'total',
            5 => 'date_added',
            6 => 'date_modified',
            7 => '',
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $data = array(
            'filter_model' => $filter_model,
            'filter_price' => $filter_price,
            'filter_quantity' => $filter_quantity,
            'filter_status' => $filter_status,
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $request['start'],
            'limit' => $request['length']
        );
        
        $return = $this->model_sale_order->getAbandonedCartOrders($data, $filterData);

        $this->initializer([
            'module/abandoned_cart/settings',
            'emailedOrders' => 'module/abandoned_cart/emailed_orders',
        ]);

        $results = [];

        foreach ($return['data'] as $key => $set) {
            $results[$key] = $set;
            $results[$key]['emailed'] = $this->emailedOrders->getEmailedOrdersByOrderId($set['order_id']);
        }

        $records = $results;
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = [
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records,
        ];
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function deleteAbandonedOrder()
    {
        $this->language->load('sale/order');

        $this->initializer([
            'sale/order',
            'module/abandoned_cart/settings',
            'emailedOrders' => 'module/abandoned_cart/emailed_orders',
        ]);

        if (isset($this->request->post['selected'])) {
            foreach ($this->request->post['selected'] as $order_id) {
                $this->order->deleteOrder($order_id, null);
                $this->emailedOrders->deleteEmailedOrder($order_id);
            }

            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_deleted_successfully');
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->error;
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

    public function install()
    {
        $this->initializer(['module/abandoned_cart/settings']);
        $this->settings->install();
    }

    public function uninstall()
    {
        $this->initializer(['module/abandoned_cart/settings']);
        $this->settings->uninstall();
    }  
}
