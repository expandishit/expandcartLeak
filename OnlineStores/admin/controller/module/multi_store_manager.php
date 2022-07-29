<?php

class ControllerModuleMultiStoreManager extends Controller
{
    private $settings;
    protected $errors = [];

    public function index()
    {
        $this->load->model('module/multi_store_manager/settings');
        $this->language->load('module/multi_store_manager');

        $this->document->setTitle($this->language->get('multi_store_manager_heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('multi_store_manager_heading_title'),
            'href'      => $this->url->link('module/multi_store_manager', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_settings'] = $this->language->get('text_settings');


        $this->template = 'module/multi_store_manager/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        // get app settings
        $this->data['settingsData'] = $this->model_module_multi_store_manager_settings->getSettings();


        $this->data['action'] = $this->url->link('module/multi_store_manager/updateSettings', '', 'SSL');
        $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');


        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['errors'] = 'Invalid Request';
        }else{
            $this->load->model('module/multi_store_manager/settings');
            $this->language->load('module/multi_store_manager');

            $data = $this->request->post['multi_store_manager'];

            $this->model_module_multi_store_manager_settings->updateSettings(['multi_store_manager' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }
    public function reports() {
        $this->language->load('report/reports');
        $this->language->load('module/multi_store_manager');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'text'      => $this->language->get('text_home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('multi_store_manager_heading_title'),
            'href'      => $this->url->link('module/multi_store_manager', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->load->model('report/reports');

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['report_sale_order'] = $this->url->link('report/sale_order', 'comming_from=multi_store_manager', 'SSL');
        $this->data['report_sale_tax'] = $this->url->link('report/sale_tax', 'comming_from=multi_store_manager', 'SSL');
        $this->data['report_sale_shipping'] = $this->url->link('report/sale_shipping', 'comming_from=multi_store_manager', 'SSL');
        $this->data['report_sale_return'] = $this->url->link('report/sale_return', 'comming_from=multi_store_manager', 'SSL');
        $this->data['report_sale_coupon'] = $this->url->link('report/sale_coupon', 'comming_from=multi_store_manager', 'SSL');
        $this->data['report_product_viewed'] = $this->url->link('report/product_viewed', 'comming_from=multi_store_manager', 'SSL');
        $this->data['report_product_purchased'] = $this->url->link('report/product_purchased', 'comming_from=multi_store_manager', 'SSL');
        $this->data['report_product_top_ten_purchased'] = $this->url->link('report/product_top_ten_purchased', 'comming_from=multi_store_manager', 'SSL');
        $this->data['report_customer_online'] = $this->url->link('report/customer_online', 'comming_from=multi_store_manager', 'SSL');
        $this->data['report_customer_order'] = $this->url->link('report/customer_order', 'comming_from=multi_store_manager', 'SSL');
        $this->data['report_customer_reward'] = $this->url->link('report/customer_reward', 'comming_from=multi_store_manager', 'SSL');
        $this->data['report_customer_credit'] = $this->url->link('report/customer_credit', 'comming_from=multi_store_manager', 'SSL');
        $this->data['report_affiliate_commission'] = $this->url->link('report/affiliate_commission', 'comming_from=multi_store_manager', 'SSL');
        $this->data['report_analytics_stats'] = $this->url->link('analytics/analytics', 'comming_from=multi_store_manager' , 'SSL');
        $this->data['report_analytics_visitors'] = $this->url->link('analytics/analytics/visitorprofile', 'comming_from=multi_store_manager', 'SSL');
        $this->data['report_brand_purchased'] = $this->url->link('report/brand_purchased', 'comming_from=multi_store_manager', 'SSL');
        $this->data['report_zone_purchased'] = $this->url->link('report/zone_purchased', 'comming_from=multi_store_manager', 'SSL');
        $this->data['report_zone_avg_purchased'] = $this->url->link('report/zone_avg_purchased', 'comming_from=multi_store_manager', 'SSL');

        $this->data['text_report_sale_order'] = $this->language->get('text_report_sale_order');
        $this->data['text_report_sale_tax'] = $this->language->get('text_report_sale_tax');
        $this->data['text_report_sale_shipping'] = $this->language->get('text_report_sale_shipping');
        $this->data['text_report_sale_return'] = $this->language->get('text_report_sale_return');
        $this->data['text_report_sale_coupon'] = $this->language->get('text_report_sale_coupon');
        $this->data['text_report_product_viewed'] = $this->language->get('text_report_product_viewed');
        $this->data['text_report_product_purchased'] = $this->language->get('text_report_product_purchased');
        $this->data['text_report_product_top_ten_purchased'] = $this->language->get('text_report_product_top_ten_purchased');
        $this->data['text_report_customer_online'] = $this->language->get('text_report_customer_online');
        $this->data['text_report_customer_order'] = $this->language->get('text_report_customer_order');
        $this->data['text_report_customer_reward'] = $this->language->get('text_report_customer_reward');
        $this->data['text_report_customer_credit'] = $this->language->get('text_report_customer_credit');
        $this->data['text_report_affiliate_commission'] = $this->language->get('text_report_affiliate_commission');
        $this->data['text_report_analytics_stats'] = $this->language->get('text_report_analytics_stats');
        $this->data['text_report_analytics_visitors'] = $this->language->get('text_report_analytics_visitors');

        $this->data['text_products'] = $this->language->get('text_products');
        $this->data['text_sales'] = $this->language->get('text_sales');
        $this->data['text_customers'] = $this->language->get('text_customers');
        $this->data['text_affiliates'] = $this->language->get('text_affiliates');
        $this->data['text_analytics'] = $this->language->get('text_analytics');



        $this->initializer([
            'abandonedCart' => 'module/abandoned_cart/settings'
        ]);

        $this->data['abandonedCart'] = false;
        if ($this->abandonedCart->isActive() == true) {
            $this->data['abandonedCart'] = true;
            $this->language->load('report/abandoned_cart');
        }

        $this->template = 'module/multi_store_manager/reports.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }



}
