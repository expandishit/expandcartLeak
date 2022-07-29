<?php

class ControllerModuleWkAmazonConnector extends Controller
{
    private $error = array();
    private $common = 'amazon/amazon_map/common';

    public function __construct($registory)
    {
        parent::__construct($registory);
        $this->load->model('module/wk_amazon_connector');
        $this->_ebayModuleAmazonConnector = $this->model_module_wk_amazon_connector;

        $this->load->language('module/wk_amazon_connector');
        $this->load->language($this->common);
    }

    public function install()
    {
        $this->_ebayModuleAmazonConnector->createTables();

        /**
         ** when install amazon connector
         * add all product to amazon_product_fields table
         * set ASIN for all products for not appear ASIN error when export product amazon
         */
        $this->_ebayModuleAmazonConnector->addASINForProducts();
        $this->load->model('setting/setting');

        $arar = array(
            'wk_amazon_cost' => 1,
            'wk_amazon_total' => .1,
            'wk_amazon_geo_zone_id' => 0,
            'wk_amazon_status' => 1
        );
        $this->model_setting_setting->editSetting('wk_amazon', $arar);
        $this->load->model('user/user_group');
        $controllers = array(
            'amazon/amazon_map/account',
            'amazon/amazon_map/product',
            'amazon/amazon_map/order',
            'amazon/amazon_map/export_product',
            'amazon/price_rules_amazon/price_rules',
            'amazon/amazon_map/map_product_data'
        );

        foreach ($controllers as $key => $controller) {
            $this->model_user_user_group->addPermission($this->user->getId(), 'access', $controller);
            $this->model_user_user_group->addPermission($this->user->getId(), 'modify', $controller);
        }

    }

    public function index()
    {

        $content_url = $this->request->get['content_url'];
        if ($content_url == null || trim($content_url) == "") {
            $content_url = "module/wk_amazon_connector/setting";
        }

        $data = array();
        $data = array_merge($data, $this->load->language('module/wk_amazon_connector'));

        $this->document->setTitle($this->language->get('heading_title'));
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '', true)
        );


        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/wk_amazon_connector', '', true)
        );

        $data['content_url'] = $content_url;

        try {
            $data['content'] = $this->getChild($content_url);
        } catch (\ExpandCart\Foundation\Exceptions\FileException $e) {
            $this->redirect($this->url->link('marketplace/home', '', 'SSL'));
        }
        $this->template = 'amazon/index.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->data = $data;

        $this->response->setOutput($this->render());
    }

    public function setting()
    {
        $data = array();
        $data = array_merge($data, $this->load->language('module/wk_amazon_connector'));

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            if (!$this->validate()) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
            }
            $this->model_setting_setting->editSetting('wk_amazon_connector', $this->request->post);

            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_success');
            $this->response->setOutput(json_encode($result_json));
            return;
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/wk_amazon_connector', '', true)
        );

        $data['action'] = $this->url->link('module/wk_amazon_connector/setting', '', true);

        $data['cancel'] = $this->url->link('extension/module', '', true);

        $post_data = array(
            'wk_amazon_connector_status',
            //general tab
            'wk_amazon_connector_default_category',
            'wk_amazon_connector_default_quantity',
            'wk_amazon_connector_default_weight',
            'wk_amazon_connector_cron_create_product',
            'wk_amazon_connector_cron_update_product',
            'wk_amazon_connector_default_store',
            'wk_amazon_connector_order_status',
            'wk_amazon_connector_default_product_store',
            'wk_amazon_connector_variation',
            'wk_amazon_connector_import_update',
            'wk_amazon_connector_export_update',
            'wk_amazon_connector_price_rules',
            'wk_amazon_connector_import_quantity_rule',
            'wk_amazon_connector_export_quantity_rule'
        );
        foreach ($post_data as $key => $post) {
            if (isset($this->request->post[$post])) {
                $data[$post] = $this->request->post[$post];
            } else {
                $data[$post] = $this->config->get($post);
            }
        }

        $data['getOcParentCategory'] = $this->_ebayModuleAmazonConnector->get_OpencartCategories(array());

        $this->load->model('setting/store');
        $data['stores'] = $this->model_setting_store->getStores();

        $this->load->model('localisation/order_status');
        $data['order_status'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['token'] = $this->session->data['token'];

        //$data['header'] = $this->load->controller('common/header');
        //$data['column_left'] = $this->load->controller('common/column_left');
        //$data['footer'] = $this->load->controller('common/footer');

        $this->template = 'module/wk_amazon_connector.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->data = $data;
        $this->response->setOutput($this->render());

        //	$this->response->setOutput($this->load->view('module/wk_amazon_connector.tpl', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'module/wk_amazon_connector')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (isset($this->request->post['wk_amazon_connector_default_quantity']) && $this->request->post['wk_amazon_connector_default_quantity'] <= 0) {
            $this->error['warning'] = $this->language->get('error_quantity');
        }
        if (isset($this->request->post['wk_amazon_connector_default_weight']) && $this->request->post['wk_amazon_connector_default_weight'] <= 0) {
            $this->error['warning'] = $this->language->get('error_weight');
        }
        return !$this->error;
    }

    public function uninstall()
    {
        $this->_ebayModuleAmazonConnector->removeTables();
    }
}
