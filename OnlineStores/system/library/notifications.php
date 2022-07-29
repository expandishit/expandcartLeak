<?php
class Notifications extends Model
{
    public const CUSTOMER_COUNT_NOTIFICATION = 1;

    public $orders_count = null;

    public $customer_total = null;

    public function add()
    {
        $this->load->model('sale/order');
        $this->load->model('user/notifications');
        $this->load->model('setting/setting');

        $this->addOrdersNotification();
        //$this->addProductsNotification();
        $this->addCustomersNotification();
    }

    protected function addOrdersNotification()
    {
        //$data= null;
        //$sign_up_date = new \DateTime(STORE_CREATED_AT);
        //$now = new \DateTime();

        // notify admin if the order reached specific number


//        if ($sign_up_date->diff($now)->days == 7 && $orders_count == 0 && !$this->config->get('orders_zero_in_week')){
//            $data['notification_module']="orders";
//            $data['notification_module_code']="orders_zero_in_week";
//            $this->addAdminNotification($data);
//            $settings['orders_zero_in_week']=1;
//            $this->model_setting_setting->insertUpdateSetting('notifications', $settings);
//        }
//
//        if ($sign_up_date->diff($now)->days == 7 && $orders_count >= 1 && ! $this->config->get('orders_one_in_week')){
//            $data['notification_module']="orders";
//            $data['notification_module_code']="orders_one_in_week";
//            $this->addAdminNotification($data);
//            $settings['orders_one_in_week']=1;
//            $this->model_setting_setting->insertUpdateSetting('notifications', $settings);
//        }

//        if ($sign_up_date->diff($now)->days == 7 && $orders_count >= 5
//            && ! $this->config->get('orders_five_week')
//        ){
//            $data['notification_module']="orders";
//            $data['notification_module_code']="orders_five_week";
//            $this->addAdminNotification($data);
//            $settings['orders_five_week']=1;
//            $this->model_setting_setting->insertUpdateSetting('notifications', $settings);
//        }

//        if ($sign_up_date->diff($now)->days == 30 && $orders_count == 0
//            && ! $this->config->get('orders_zero_month')
//        ){
//            $data['notification_module']="orders";
//            $data['notification_module_code']="orders_zero_month";
//            $this->addAdminNotification($data);
//            $settings['orders_zero_month']=1;
//            $this->model_setting_setting->insertUpdateSetting('notifications', $settings);
//        }

        if ( ! $this->config->get('orders_five')){

            if (!$this->orders_count){
                $this->orders_count = $this->model_sale_order->getTotalOrders();
            }
            if ($this->orders_count >= 5 ){
                $data['notification_module']="orders";
                $data['notification_module_code']="orders_five";
                $this->addAdminNotification($data);
                $settings['orders_five']=1;
                $this->model_setting_setting->insertUpdateSetting('notifications', $settings);
            }

        }

        if ( ! $this->config->get('orders_ten')){
            if (!$this->orders_count){
                $this->orders_count = $this->model_sale_order->getTotalOrders();
            }

            if ($this->orders_count >= 10) {
                $data['notification_module'] = "orders";
                $data['notification_module_code'] = "orders_ten";
                $this->addAdminNotification($data);
                $settings['orders_ten'] = 1;
                $this->model_setting_setting->insertUpdateSetting('notifications', $settings);
            }
        }

        // add pending order notification after 9 am every day
        if (
            date('H') > 9
            && $this->config->get('pending_orders')['day'] != date('l')
        ){
            $this->load->model('report/home');
            $pending_orders_count = $this->model_report_home->unhandledOrdersCount();

            if ($pending_orders_count > 1){
                $data['notification_module']="orders";
                $data['notification_module_code']="orders_pending";
                $data['count']=$pending_orders_count;
                $this->addAdminNotification($data);

                $settings['pending_orders']['day']=date('l');
                $this->model_setting_setting->insertUpdateSetting('notifications', $settings);
            }

        }

    }

    protected function addProductsNotification()
    {
        $data= null;
        $sign_up_date = new \DateTime(STORE_CREATED_AT);
        $now = new \DateTime();

        // notify admin if the order reached specific number
        $this->load->model('catalog/product');

        $products_count = $this->model_catalog_product->getTotalProductsCount();

        if ($sign_up_date->diff($now)->days == 7 && $products_count == 0 && !$this->config->get('products_zero_in_week')){
            $data['notification_module']="products";
            $data['notification_module_code']="products_zero_in_week";
            $this->addAdminNotification($data);
            $settings['products_zero_in_week']=1;
            $this->model_setting_setting->insertUpdateSetting('notifications', $settings);
        }

        if ($sign_up_date->diff($now)->days == 7 && $products_count >= 1 && ! $this->config->get('products_one_in_week')){
            $data['notification_module']="products";
            $data['notification_module_code']="products_one_in_week";
            $this->addAdminNotification($data);
            $settings['products_one_in_week']=1;
            $this->model_setting_setting->insertUpdateSetting('notifications', $settings);
        }

        if ($sign_up_date->diff($now)->days == 7 && $products_count >= 10 && ! $this->config->get('products_ten_in_week')){
            $data['notification_module']="products";
            $data['notification_module_code']="products_ten_in_week";
            $this->addAdminNotification($data);
            $settings['products_ten_in_week']=1;
            $this->model_setting_setting->insertUpdateSetting('notifications', $settings);
        }

        if ($sign_up_date->diff($now)->days == 14 && $products_count >= 15 && ! $this->config->get('products_fifteen_in_two_week')){
            $data['notification_module']="products";
            $data['notification_module_code']="products_fifteen_in_two_week";
            $this->addAdminNotification($data);
            $settings['products_fifteen_in_two_week']=1;
            $this->model_setting_setting->insertUpdateSetting('notifications', $settings);
        }

        if ($products_count == 100
            && ! $this->config->get('products_hundred')
        ){
            $data['notification_module']="products";
            $data['notification_module_code']="products_hundred";
            $this->addAdminNotification($data);
            $settings['products_hundred']=1;
            $this->model_setting_setting->insertUpdateSetting('notifications', $settings);
        }
    }

    protected function addCustomersNotification()
    {
        $filter_data['create_by_admin']=0;
        if ( ! $this->config->get('customers_one')){

            if (!$this->customer_total){
                $this->load->model('sale/customer');
                $this->customer_total = $this->model_sale_customer->getTotalCustomers($filter_data);
            }

            if ($this->customer_total == self::CUSTOMER_COUNT_NOTIFICATION){
                $data['notification_module']="customers";
                $data['notification_module_code']="customers_one";
                $this->addAdminNotification($data);

                $settings['customers_one']=1;
                $this->model_setting_setting->insertUpdateSetting('notifications', $settings);
            }

        }

        if ( ! $this->config->get('customers_ten')
        ){

            if (!$this->customer_total){
                $this->load->model('sale/customer');
                $this->customer_total = $this->model_sale_customer->getTotalCustomers($filter_data);
            }
            if ($this->customer_total == 10 ){
                $data['notification_module']="customers";
                $data['notification_module_code']="customers_ten";
                $this->addAdminNotification($data);

                $settings['customers_ten']=1;
                $this->model_setting_setting->insertUpdateSetting('notifications', $settings);
            }
        }
    }

    public function addAdminNotification($data){
        $this->db->query(
            "INSERT INTO " . DB_PREFIX . "admin_notifications SET
            `count` = '" . $this->db->escape($data['count']) . "',
            notification_module = '" . $this->db->escape($data['notification_module']) . "',
            notification_module_id = '" . $this->db->escape($data['notification_module_id']) . "',
            notification_module_code = '" . $this->db->escape($data['notification_module_code']) . "'"

        );
    }

}