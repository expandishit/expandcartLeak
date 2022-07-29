<?php

class ControllerUserNotifications extends Controller
{
    public function index()
    {
        $this->notifications->add();

        $this->load->model('user/notifications');

        $notifications = $this->model_user_notifications->getLatestAdminNotifications(20);

        if ($notifications == false) {
            $response = [
                'alert' => 'No Notifications'
            ];
            die(json_encode($response));
        }

        $unread = 0;

        //List used order ids
        $orderIds = [];

        foreach ($notifications as &$notification) {

            $notification['base_url']= $this->url->link('','', 'SSL')->format();

            if ($notification['notification_module']=="orders"){
                $notification['url']= $this->url->link('sale/order', '', 'SSL')->format();
            }

            if ($notification['notification_module_code']=="orders_new" && !in_array($notification['notification_module_id'], $orderIds)){
                $this->load->model('sale/order');
                $orderInfo = $this->model_sale_order->getOrder($notification['notification_module_id']);
                $notification['customer']=$orderInfo['firstname'] ." " .$orderInfo['lastname'];
                $notification['total']=$orderInfo['total'];
                $notification['currency_code']=$orderInfo['currency_code'];
                $notification['url']= $this->url->link('sale/order/info', 'order_id='.$notification['notification_module_id'], 'SSL')->format();
                $orderIds[] = $notification['notification_module_id'];
            }

            if ($notification['notification_module_code']=="return_new"){
                $this->load->model('sale/return');
                $return_info = $this->model_sale_return->getReturn($notification['notification_module_id']);
                $notification['customer']=$return_info['customer'];
                $notification['currency_code']=$return_info['currency_code'];
                $notification['url']= $this->url->link('sale/order/info', 'order_id='.$return_info['order_id'], 'SSL')->format();
                $notification['return_url']= $this->url->link('sale/return/info', 'return_id='.$notification['notification_module_id'], 'SSL')->format();
            }

            if ($notification['notification_module_code']=="customers_one"){
                $notification['url']= $this->url->link('sale/customer/update', 'customer_id=1', 'SSL')->format();
            }

            if ($notification['notification_module_code']=="customers_ten"
                || $notification['notification_module_code']=="orders_ten"
            ){
                $notification['url']= $this->url->link('marketplace/home', 'category[]=marketing', 'SSL')->format();
            }

            if ($notification['notification_module_code']=="customers_registered"){

                $this->load->model('sale/customer');
                $customer_info = $this->model_sale_customer->getCustomer($notification['notification_module_id']);
                $notification['customer']=$customer_info['firstname']." ".$customer_info['lastname'];
                $notification['url']= $this->url->link(
                    'sale/customer/update', 'customer_id='.$notification['notification_module_id'],
                    'SSL'
                )->format();
            }
            if ($notification['notification_module_code']=="paypal_payment_before_onboarding"){
                $notification['email']=BILLING_DETAILS_EMAIL;
            }


            $notification['notification_module_code_link']=$this->language->get($notification['notification_module_code'].'_link');

            if ($notification['notification_module_code']=="orders_new"){
                $notification['notification_module_code_text']= sprintf( $this->language->get($notification['notification_module_code']) ,
                    $notification['customer'],$notification['total'],$notification['currency_code'] );

            }else if ($notification['notification_module_code']=="orders_pending"){
                $notification['notification_module_code_text']= sprintf( $this->language->get($notification['notification_module_code']),$notification['count']);

            }
            else if($notification['notification_module_code']=="return_new" || $notification['notification_module_code']=="customers_registered"){
                $notification['notification_module_code_text']= sprintf( $this->language->get($notification['notification_module_code']),$notification['customer']);

            }
            else if($notification['notification_module_code']=="paypal" || $notification['notification_module_code']=="paypal_payment_before_onboarding"){
                $notification['notification_module_code_text']= sprintf( $this->language->get($notification['notification_module_code']),$notification['email']);
                //$notification['notification_module_code_text']= sprintf( $this->language->get($notification['notification_module_code']),"test");

            }
            else{
                $notification['notification_module_code_text']=$this->language->get($notification['notification_module_code']);
            }

            if ($notification['read_status'] == '0') {
                $unread++;
            }
        }

        $response = [
            'success'=>1,
            'notifications' => $notifications,
            'unread' => $unread,
        ];

        $this->response->setOutput(json_encode($response));
    }

    public function pull()
    {
        $this->initializer([
            'user/notifications'
        ]);

        $response = [];

        $notifications = $this->notifications->getLatestNotifications();

        if ($notifications === false) {
            $response = [
                'alert' => 'No Notifications'
            ];
            die(json_encode($response));
        }

        $unread = 0;

        foreach ($notifications as &$notification) {
            if ($notification['submitter'] == '0') {
                $notification['submitter_info']['name'] = 'Visitor';
            } else {
                $notification['submitter_info']['name'] = $notification['full_name'];
            }

            if ($notification['notification_status'] == '0') {
                $unread++;
            }
        }

        $response = [
            'notifications' => $notifications,
            'unread' => $unread,
        ];

        die(json_encode($response));
    }

    public function markAsRead(){
        if($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('user/notifications');
            $this->model_user_notifications->markAsRead();
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_langstatus_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_langstatus_error');
        }

        $this->response->setOutput(json_encode($result_json));
    }

    /*
     * remove notification from the list by ID
     */
    public function removeNotification()
    {
        $this->load->model("user/user");
        if( $this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post["id"])) {
            $id = $this->request->post["id"];
            $this->load->model('user/notifications');
            $this->model_user_notifications->removeNotification($id);
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_langstatus_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_langstatus_error');
        }

        $this->response->setOutput(json_encode($result_json));
    }

}
